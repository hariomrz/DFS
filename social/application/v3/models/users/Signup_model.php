<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Signup_model extends Common_Model {

    public $client_error = 0;

    public function __construct() {
        parent::__construct();
    }

    /**
     * [add_analytics Add analytics for signup]
     * @param [type] $data [Data for analytics]
     */
    public function add_analytics($data) {
        //Get Browser Session ID
        if (isset($data['SessionID']) && $data['SessionID'] != "") {
            $session_id = $data['SessionID'];
        } else {
            $session_id = session_id();
        }

        if (isset($data['IPAddress']) && $data['IPAddress'] != "") {
            $analytics_data['IPAddress'] = $data['IPAddress'];
        } else {
            $analytics_data['IPAddress'] = getRealIpAddr();
        }

        $return['ResponseCode'] = 200;
        $return['Message'] = "Success";
        $return['ServiceName'] = "signup/add_analytics";
        $return['Data'] = array("SessionID" => $session_id);

        //Check if entry is already exists in database if not add new entry
        $this->db->select('SessionID');
        $this->db->where('SessionID', $session_id);
        $this->db->limit(1);
        $query = $this->db->get(SIGNUPANALYTICLOGS);        
        if ($query->num_rows() == 0) {
            $analytics_data['SessionID'] = $session_id;
            $this->load->helper('location');
            if (isset($data['Latitude']) && isset($data['Longitude']) && $data['Latitude'] != "" && $data['Longitude'] != "") {
                $location_data = geocoding_location_details($data['Latitude'], $data['Longitude']);
            } else if ($analytics_data['IPAddress'] != '') {
                $location_data = get_ip_location_details($analytics_data['IPAddress']);
            }

            if (isset($location_data['CityID']) && $location_data['CityID'] != "") {
                $analytics_data['CityID'] = $location_data['CityID'];
            } else {
                $analytics_data['CityID'] = NULL;
            }

            $device_info = isset($data['DeviceInfo']) ? json_encode($data['DeviceInfo']) : '';
            $analytics_data['DeviceInfo'] = $device_info;
            $analytics_data['WeekdayID'] = $this->get_week_day_id(date('l'));
            $analytics_data['TimeSlotID'] = $this->get_time_slot();
            $analytics_data['IsSignup'] = 0;
            $analytics_data['CreatedDate'] = date('Y-m-d H:i:s');
            $analytics_data['ModifiedDate'] = date('Y-m-d H:i:s');
            $analytics_data['BrowserID'] = check_browser();
            $result = $this->db->insert(SIGNUPANALYTICLOGS, $analytics_data);
            if (!$result) {
                $return['ResponseCode'] = 519;
                $return['Message'] = "Invalid Data";
            }
        }
        return $return;
    }

    /**
     * [get_sent_email_count Get number of emails sent for registration]
     * @param  [string] $user_guid [User GUID]
     * @return [int]            [Sent email count]
     */
    function get_sent_email_count($user_guid) {
        $this->db->select('C.CommunicationID');
        $this->db->from(COMMUNICATIONS . ' C');
        $this->db->join(USERS . ' U', 'U.Email=C.EmailTo', 'left');
        $this->db->where('U.UserGUID', $user_guid);
        $this->db->where('C.EmailTypeID', '2');
        $this->db->where("DATE(C.CreatedDate)='" . get_current_date('%Y-%m-%d') . "'", NULL, FALSE);
        $query = $this->db->get();
        return $query->num_rows();
    }

    /**
     * [update_analytics Update analytics for signup]
     * @param  [int] $source_id [description]
     * @param  [int] $device_type_id   [description]
     * @param  [int] $is_sign_up       [description]
     * @param  [int] $client_error    [description]
     * @param  string $session_id      [description]
     * @return [type]                 [description]
     */
    public function update_analytics($source_id, $device_type_id, $is_sign_up, $client_error, $session_id = "", $user_id = 0) {
        // Get Browser Session ID
        $sid = 0;
        if (isset($session_id) && !empty($session_id)) {
            $sid = $session_id;
        } else {
            $session_id = $this->session->userdata('session_id');
            $sid = $session_id;
        }

        // Check if entry exists or not in database if exists then update entry
        $this->db->select('SignupAnalyticLogID, CreatedDate');
        $this->db->where('SessionID', $sid);
        $this->db->limit(1);
        $query = $this->db->get(SIGNUPANALYTICLOGS);
        
        $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
        if ($query->num_rows()) {
            $row = $query->row();
            //Set parameters which we have to update
            $params['SignupSourceID'] = $source_id;
            $params['DeviceTypeID'] = $device_type_id;
            $params['IsSignup'] = $is_sign_up;
            $params['ModifiedDate'] = $current_date_time;
            $params['TimeTaken'] = ((strtotime($current_date_time) - strtotime($row->CreatedDate)) / 60);
            $this->db->where('SessionID', $sid);
            $this->db->update(SIGNUPANALYTICLOGS, $params);

            if (!empty($user_id)) {
                $analytic_params['SignupAnalyticLogID'] = $row->SignupAnalyticLogID;
                $this->db->where('UserID', $user_id);
                $this->db->update(USERS, $analytic_params);
            }
            $c_query = $this->db->where('ClientErrorID', $client_error)
                    ->where('DATE(`CreatedDate`)', get_current_date('%Y-%m-%d'), NULL, FALSE)
                    ->where('SignupAnalyticLogID', $row->SignupAnalyticLogID)
                    ->limit(1)
                    ->get(SIGNUPANALYTICLOGERRORS);

            if ($c_query->num_rows()) {
                $result = $c_query->row();
                $this->db->set('ErrorCount', 'ErrorCount+1', FALSE)
                        ->where('SignupAnalyticLogErrorID', $result->SignupAnalyticLogErrorID)
                        ->update(SIGNUPANALYTICLOGERRORS);
            } else {
                if ($client_error != '0') {
                    $this->db->insert(SIGNUPANALYTICLOGERRORS, array('SignupAnalyticLogID' => $row->SignupAnalyticLogID, 'ClientErrorID' => $client_error, 'ErrorCount' => '1', 'CreatedDate' => get_current_date('%Y-%m-%d')));
                }
            }
            $this->session->sess_time_to_update = 0;
        }
    }

    function getInvitation($SourceID, $UserName, $Email, $Token) {
        $inviteData = array();

        $trr['Token'] = $Token;
        if ($SourceID == 2) {
            $trr['UserSocialID'] = $UserName;
        }

        $tqry = $this->db->get_where(INVITATION, $trr);
        $trownum = $tqry->num_rows();
        if ($trownum) {
            $trow = $tqry->row();
        }
        if ($SourceID == 1) {
            //Check if user is referred / invite by any user before
            $inviteData['StatusID'] = 6;
            $inviteData['ReferralID'] = 0;
            if ($trownum) {
                $mqry = $this->db->where("(UserSocialID='" . $trow->UserSocialID . "' AND UserSocialID!='') OR UserSocialID='" . $Email . "'", NULL, FALSE)
                        ->where("UserID!='" . $trow->UserID . "'", NULL, FALSE)
                        ->group_by('UserID')
                        ->get(INVITATION);
                $inviteData['ruid'] = array();
                $inviteData['tusid'] = $trow->UserID;
                if ($mqry->num_rows()) {
                    foreach ($mqry->result() as $reqUid) {
                        $inviteData['ruid'][] = $reqUid->UserID;
                    }
                }
                $this->db->where('Token', $Token)
                        ->or_where('UserSocialID', $trow->UserSocialID)
                        ->or_where('UserSocialID', $Email)
                        ->update(INVITATION, array('IsRegistered' => '1'));
                $inviteData['addFriend'] = '1';
            }
        } else {
            $inviteData['StatusID'] = 2;
            //Check if user is referred / invite by any user before
            if ($trownum) {
                $inviteData['tusid'] = $trow->UserID;
                $inviteData['addFriend'] = '1';
                $inviteData['ReferralID'] = $trow->InvitationID;
            } else {
                $inviteData['ReferralID'] = 0;
            }
            $q = $this->db->where('InviteType', $SourceID)
                    ->where("Token!='" . $Token . "'", NULL, FALSE)
                    ->where('UserSocialID', EscapeString($UserName))
                    ->get(INVITATION);

            $this->db->where('Token', $Token)
                    ->or_where('UserSocialID', EscapeString($UserName))
                    ->or_where('UserSocialID', $Email)
                    ->update(INVITATION, array('IsRegistered' => '1'));

            if ($q->num_rows()) {
                $inviteData['addFriend'] = '1';
                foreach ($q->result() as $reqUid) {
                    $inviteData['ruid'][] = $reqUid->UserID;
                }
            }
        }

        return $inviteData;
    }

    /**
     * Function Name: createAccount
     * @param SourceID
     * @param Email
     * @param Token
     * @param UserTypeID
     * @param FirstName
     * @param LastName
     * @param DeviceTypeID
     * @param Latitude
     * @param Location
     * @param ReferrerTypeID
     * @param PhoneNumber
     * @param Longitude
     * @param ProfilePicture
     * Description: Register new acccount
     */
    public function createAccount($data) {
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        $CreatedDate = get_current_date('%Y-%m-%d %H:%i:%s');

        /* Registration insert to user table (StatusID can be changed according to project requirements)-starts */
        $UserGUID = get_guid(); /* Create new User GuID */

        $data['Email'] = strtolower(trim($data['Email']));
        $data['FirstName'] = ucwords(trim($data['FirstName']));
        $data['LastName'] = ucwords(trim($data['LastName']));
        $data['Username'] = strtolower(trim($data['Username']));
        $mobile = trim($data['Mobile']);
        if (empty($data['LastName'])) {
            $data['LastName'] = $data['FirstName'];
        }

        if (empty($data['Username'])) {
            if (!empty($data['FirstName']) && !empty($data['LastName'])) {
                $data['Username'] = random_username($data['FirstName'] . $data['LastName']);
            } else {
                $email_before = explode('@', $data['Email']);
                $data['Username'] = random_username($email_before[0]);
            }
        }
        
        //Check if user is using social accounts for signup or native login form
        $inviteData = $this->getInvitation($data['SourceID'], $data['Username'], $data['Email'], $data['Token']);

        extract($inviteData);

        $ResolutionID = $this->login_model->get_resolution_id($data['Resolution']);

        $data['ResolutionID'] = $ResolutionID;
        //Get data and set variablesLongitude
        $this->load->helper('location');
        $locationDetails = get_location_details($data['IPAddress'], $data['Latitude'], $data['Longitude']);
        $data['CityID'] = $locationDetails['CityID'];
        $data['Latitude'] = $locationDetails['Latitude'];
        $data['Longitude'] = $locationDetails['Longitude'];
        $data['IPAddress'] = $locationDetails['IPAddress'];

        $this->load->model('timezone/timezone_model');
        $TimeZoneID = $this->timezone_model->get_time_zone_id($data['Latitude'], $data['Longitude']);
        //Get weekday id and time slot id
        $WeekDayID = $this->get_week_day_id(date('l'));
        $TimeSlot = $this->get_time_slot();
        if (isset($data['UserTypeID']) && $data['UserTypeID'] != '')
            $UserTypeID = $data['UserTypeID'];
        else
            $UserTypeID = $data['UserTypeID'] = '3';


        //If user registered from social login then grab his profile picture from his account
        $ProfilePicture = '';

        $InsertUser['UserGUID'] = $UserGUID;
        $InsertUser['UserTypeID'] = $data['UserTypeID'];
        $InsertUser['FirstName'] = EscapeString($data['FirstName']);
        $InsertUser['LastName'] = EscapeString($data['LastName']);
        $InsertUser['Email'] = EscapeString($data['Email']);
        $InsertUser['Gender'] = EscapeString($data['Gender']);
        $InsertUser['SourceID'] = $data['SourceID'];
        $InsertUser['DeviceTypeID'] = $data['DeviceTypeID'];
        $InsertUser['WeekDayID'] = $WeekDayID;
        $InsertUser['TimeSlotID'] = $TimeSlot;
        $InsertUser['CreatedDate'] = $CreatedDate;
        $InsertUser['StatusID'] = 2;
        $InsertUser["PhoneVerified"] = 1;
        $InsertUser['LastLoginDate'] = $CreatedDate;
        $InsertUser['EmailNotification'] = 0;
        $InsertUser['Latitude'] = $data['Latitude'];
        $InsertUser['IPAddress'] = $data['IPAddress'];
        $InsertUser['ReferrerTypeID'] = $ReferralID;
        $InsertUser['PhoneNumber'] = $mobile;
        $InsertUser['Longitude'] = $data['Longitude'];
        $InsertUser['ProfilePicture'] = $ProfilePicture;

        if ($this->IsApp == 1) {
            $extra_where = [
                'StatusID' => 1
            ];
            $InsertUser['ActivationCode'] = unique_random_string(USERS, 'ActivationCode', $extra_where, 'numeric', 6);
        } else {
            $InsertUser['ActivationCode'] = get_guid();
        }

        //Add new user
        if ($this->db->insert(USERS, $InsertUser)) {
            // Get UserID of last inserted user
            $user_id = $this->db->insert_id();

            $MediaID = NULL;
            if (isset($data['Picture']) && !empty($data['Picture'])) {
                $ImageData = @file_get_contents($data['Picture']);
                if ($ImageData !== FALSE) {
                    $MediaData = array();
                    $MediaData['Type'] = 'profile';
                    $MediaData['DeviceType'] = $data['DeviceType'];
                    $MediaData['SourceID'] = $data['SourceID'];
                    $MediaData['ImageData'] = $ImageData;
                    $MediaData['ModuleID'] = 3;
                    $MediaData['UserID'] = $user_id;
                    $MediaData['ModuleEntityGUID'] = $UserGUID;
                    $this->load->model(array('upload_file_model'));
                    $Result = $this->upload_file_model->saveFileFromUrl($MediaData);
                    if ($Result['ResponseCode'] == 200 && isset($Result['Data']['MediaGUID'])) {
                        $MediaGUID = $Result['Data']['MediaGUID'];
                        $MediaID = $Result['Data']['MediaID'];
                        $ProfilePicture = $Result['Data']['ImageName'];
                        $this->upload_file_model->updateProfilePicture($MediaGUID, $ProfilePicture, $user_id, 3, $UserGUID);
                    }
                }
            }                      

            /* Insert login information - starts */
            $SetPassword = 1;
            if ($data['Password'] == '') {
                $SetPassword = 0;
            }

            $UserLoginData['UserID'] = $user_id;
            $UserLoginData['LoginKeyword'] = EscapeString($mobile);
            $UserLoginData['Password'] = generate_password($data['Password']);
            $UserLoginData['SourceID'] = $data['SourceID'];
            $UserLoginData['CreatedDate'] = $CreatedDate;
            $UserLoginData['ModifiedDate'] = $CreatedDate;
            $UserLoginData['SetPassword'] = $SetPassword;
            $UserLoginData['ProfileURL'] = isset($data['profileUrl']) ? $data['profileUrl'] : '';
            $UserLoginData['MediaID'] = $MediaID;

            $this->db->insert(USERLOGINS, $UserLoginData);
            /* Insert login information - ends */

            /*  User role entry start here */
            $this->db->insert(USERROLES, array('UserID' => $user_id, 'RoleID' => $data['Role']));
            /*  User role entry end here */

            /*  User Detail entry start here */
            //$profilename=$UserName;
            $profilename = '';
            $app_version = isset($data['AppVersion']) ? $data['AppVersion'] : '';
            $locality_id = $this->LocalityID;
            if (empty($locality_id)) {
                $locality_id = 1;
            }
            $user_details = array(
                'RelationWithName' => '',
                'UserID' => $user_id,
                'ProfileName' => $profilename,
                'UserWallStatus' => '',
                'CityID' => $data['CityID'],
                'TimeZoneID' => $TimeZoneID,
                'DOB' => $data['DOB'],
                'LocalityID' => $locality_id,
                'HouseNumber' => $data['HouseNumber']);
            if ($this->IsApp == 1) {
                switch ($data['DeviceTypeID']) {
                    case '2':
                        $user_details['IOSAppVersion'] = $app_version;
                        break;
                    case '3':
                        $user_details['AndroidAppVersion'] = $app_version;
                        break;
                    default:
                        # code...
                        break;
                }
            }
            $this->db->insert(USERDETAILS, $user_details);
            /*  User role entry end here */
            
            $this->load->model(array('notification_model','privacy/privacy_model'));
            // Add privacy settings to low
            $this->privacy_model->save($user_id, 'low');            
            $this->notification_model->set_all_notification_on($user_id);

            $data['Username'] = EscapeString($data['Username']);
            
            $user_name = trim(str_replace("-","",$data['Username']));
            if(is_numeric($user_name)) {
                $data['Username'] = $UserGUID;
            }


            if (!$this->is_unique_value($data['Username'], PROFILEURL . '.Url.EntityID.EntityID.Username', $user_id)) {
                $data['Username'] = $UserGUID;
                $this->load->model('users/user_model');
                $this->user_model->update_username($user_id, $data['Username']);
            }

            $profileUrlData[] = array(
                'EntityType' => 'User',
                'EntityID' => $user_id,
                'Url' => ($data['SourceID'] != 1) ? $UserGUID : EscapeString($data['Username']),
                'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
            );
            $this->set_profile_url($profileUrlData);
            /* Insert User Profile URL End */

            //Create Default album
            create_default_album($user_id, 3, $user_id);
            $Return['Data']['UserID'] = $user_id;
            if ($this->IsApp == 1) {
                $Return['ResponseCode'] = 201;
            }
        } else {
            $Return['ResponseCode'] = 513;
            $Return['Message'] = lang('record_not_added');
        }
        return $Return;
    }

    /**
     * [update_user_email Update User's email and send activation link on it]
     * @param  array  $data [User details]
     * @return [type]       [description]
     */
    function update_user_email($data = array()) {
        if (!empty($data)) {
            $activation_code = get_guid();
            $this->db->where('UserID', $data['UserID']);
            $this->db->update(USERS, array('Email' => $data['Email'], 'ActivationCode' => $activation_code, 'StatusID' => '1'));
            $this->session->set_userdata('UserStatusID', 1);
            $this->send_activation_link($data['UserGUID'], $activation_code, true);

            if (CACHE_ENABLE) {
                $this->cache->delete('user_profile_' . $data['UserID']);
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * [send_activation_link Used to send account activation link in mail]
     * @param  [type] $user_guid      [User GUID]
     * @param  string $activation_code [Activation Code]
     */
    function send_activation_link($user_guid, $activation_code = "", $update = false) {
        if (empty($activation_code)) {
            $activation_code = $this->get_activation_code($user_guid);
        }
        /* Send Registration Email - Starts */
        if ($this->IsApp == 1) { /* For Mobile */
            $url = $activation_code;
        } else {
            $url = site_url('confirm/email') . '/' . $activation_code;
        }

        $user = get_detail_by_guid($user_guid, 3, '*', 2);
        $user_name = $user['FirstName'] . " " . $user['LastName'];

        if ($update) {
            $email_data = array();
            $email_data['IsResend'] = 0;
            $email_data['Subject'] = "Email changed request for " . SITE_NAME;
            $email_data['TemplateName'] = "emailer/email_change";
            $email_data['Email'] = $user['Email'];
            $email_data['EmailTypeID'] = REGISTRATION_EMAIL_TYPE_ID;
            $email_data['UserID'] = $user['UserID'];
            $email_data['StatusMessage'] = "Registration";
            $email_data['Data'] = array("FirstLastName" => $user_name, "Link" => $url);
        } else {
            $email_data = array();
            $email_data['IsResend'] = 0;
            $email_data['Subject'] = "Thank you for Registration on " . SITE_NAME;
            $email_data['TemplateName'] = "emailer/registration";
            $email_data['Email'] = $user['Email'];
            $email_data['EmailTypeID'] = REGISTRATION_EMAIL_TYPE_ID;
            $email_data['UserID'] = $user['UserID'];
            $email_data['StatusMessage'] = "Registration";
            $email_data['Data'] = array("FirstLastName" => $user_name, "Link" => $url);
        }

        sendEmailAndSave($email_data);
    }

    /**
     * [get_activation_code Get user account activation code]
     * @param  [string] $user_guid [User GUID]
     * @return [string]           [account activation code]
     */
    function get_activation_code($user_guid) {
        $this->db->select('ActivationCode');
        $this->db->from(USERS);
        $this->db->where('UserGUID', $user_guid);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->ActivationCode;
        } else {
            return '';
        }
    }

    /**
     * check unique value
     * @access public
     * @param null
     */
    function is_unique_value($str, $fields) {
        list($table, $field, $select_field1, $entitiy) = explode('.', $fields);

        $this->db->select($select_field1);
        $this->db->where(array($field => EscapeString($str)));
        if ($entitiy == 'Email' || $entitiy == 'PhoneNumber') {
            $this->db->where('StatusID!=3', NULL, FALSE);
        } else if ($entitiy == 'Username') {
            $this->db->join(USERS, USERS . '.UserID=' . PROFILEURL . '.EntityID', 'left');
            $this->db->where(USERS . '.StatusID!=3', NULL, FALSE);
        }
        $this->db->limit(1);
        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            if ($field == "Email") {
                $this->client_error = 2;
                if ($result['StatusID'] == 3) {
                    $this->form_validation->set_message('is_unique_value', lang('email_registered'));
                } else {
                    $this->form_validation->set_message('is_unique_value', lang('email_exists'));
                }
            } else if ($field == "PhoneNumber") {
                $this->client_error = 2;
                if ($result['StatusID'] == 3) {
                    $this->form_validation->set_message('is_unique_value', lang('mobile_registered'));
                } else {
                    $this->form_validation->set_message('is_unique_value', lang('mobile_exists'));
                }
            } else {
                $this->form_validation->set_message('is_unique_value', lang('username_already_exists'));
                $this->client_error = 3;
            }
            return FALSE;
        } else {
            if ($table == "ProfileUrl") {
                $controllers = array();
                $route = $this->router->routes;
                if ($handle = opendir(APPPATH . '/controllers')) {
                    while (false !== ($controller = readdir($handle))) {
                        if ($controller != '.' && $controller != '..' && strstr($controller, '.') == '.php') {
                            $controllers[] = strstr($controller, '.', true);
                        }
                    }
                    closedir($handle);
                }
                $reserved_routes = array_merge($controllers, array_keys($route));
                $reserved_routes[] = 'post';
                $reserved_routes[] = 'article';
                if (in_array(EscapeString(strtolower($str)), array_map('strtolower', $reserved_routes))) {
                    $this->form_validation->set_message('is_unique_value', lang('username_already_exists'));
                    $this->client_error = 3;
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {
                return TRUE;
            }
        }
    }

}
