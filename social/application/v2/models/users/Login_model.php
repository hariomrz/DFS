<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Login_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    function is_user_profile($profile_url) {
        if (in_array(strtolower($profile_url), ['post', 'article'])) {
            return true;
        }
        $this->db->select('Url');
        $this->db->from(PROFILEURL);
        $this->db->where('StatusID', '2');
        $this->db->where('EntityType', 'User');
        $this->db->where('Url', $profile_url);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    function calculate_activity($user_guid) {
        $user_id = get_detail_by_guid($user_guid, 3, 'UserID', 1);
        $this->db->select('Rank');
        $this->db->from(USERACTIVITYRANK);
        $this->db->where('UserID', $user_id);
        $this->db->where("DATE(CreatedTime)='" . get_current_date('%Y-%m-%d') . "'", null, false);
        $query = $this->db->get();
    }

    /**
     * Function Name: forgot_password
     * @param type
     * @param value
     * Description: recovery password if any case user forgot his password 
     */
    function forgot_password($type, $value, $response_type) {
        //If recovery password type is URL then send user link to reset his password
        $value = $this->db->escape_str($value);
        $this->db->select(USERS . '.Email,' . USERS . '.FirstName,' . USERS . '.LastName,' . USERS . '.UserID,' . USERLOGINS . '.SourceID');
        $this->db->from(USERS);
        $this->db->join(USERLOGINS, USERLOGINS . '.UserID=' . USERS . '.UserID', 'inner');
        if ($type == 'Mobile') {
            $this->db->where(USERS . '.PhoneNumber', $value);
        } else {
            $value = strtolower($value);
            $this->db->where(USERS . '.Email', $value);
        }
        $this->db->order_by(USERS . '.UserID', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $user_data = $query->row_array();

            if ($response_type == "EmailWithResetCode" || $response_type == "SMSWithResetCode") {
                $random_guid = unique_random_string(USERRESETPASSWORDS, 'UserGUID', ['IsPasswordReset' => 0], 'nozero', 4);
                $TemplateName = "emailer/reset_password_usingcode";
            } elseif ($response_type == "EmailWithResetUrl") {
                $random_guid = unique_random_string(USERRESETPASSWORDS, 'UserGUID', ['IsPasswordReset' => 0], 'alnum', 8);
                //$random_guid = unique_random_string('alnum', 8);
                $TemplateName = "emailer/reset_password_usinglink";
            } elseif ($response_type == "EmailWithResetUrlAndCode") {
                //#todo new template which has both code as well as link
                $random_guid = unique_random_string(USERRESETPASSWORDS, 'UserGUID', ['IsPasswordReset' => 0], 'alnum', 8);
                //$random_guid = random_string('alnum', 8);
                $TemplateName = "emailer/reset_password_usinglink";
            }

            /* Save Data into UserResetPassword Table */
            $user_reset_password_data = array(
                'UserID' => $user_data['UserID'],
                'UserGUID' => $random_guid,
                'IsPasswordReset' => 0,
                'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
                'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
            );

            $this->db->insert(USERRESETPASSWORDS, $user_reset_password_data);
            if ($type == 'Mobile') {
                if(ACTIVE_SMS_GATEWAY == "msg91") {
                    $sms_data = array();
                    $sms_data['otp'] = $random_guid;
                    $sms_data['mobile'] = $value;
                    $sms_data['phone_code'] = DEFAULT_PHONE_CODE;
                    $sms_data['message'] = "OTP for Bhopu is ".$random_guid.". Please use this to verify your mobile number.";
                    send_msg91_sms($sms_data);
                } else {    
                    $this->load->library('TwoFactorSMS');
                    $TwoF = new TwoFactorSMS(TWO_FACTOR_SMS_API_KEY, TWO_FACTOR_SMS_API_ENDPOINT);        
                    $result = $TwoF->SendSMSOTPCustomWithTemplate(DEFAULT_PHONE_CODE . $value, $random_guid, "bhopuu");
                    //$result = $TwoF->SendSMSOTPCustom($value, $random_guid);
                }
            } else {
                /* Send One Time Password Link Email Templates - Starts */
                $email_data = array();
                $email_data['IsResend'] = 0;
                $email_data['Subject'] = SITE_NAME . " Password Assistance";
                $email_data['TemplateName'] = $TemplateName;
                $email_data['Email'] = $user_data['Email'];
                $email_data['EmailTypeID'] = FORGOT_PASSWORD_EMAIL_TYPE_ID;
                $email_data['UserID'] = $user_data['UserID'];
                $email_data['StatusMessage'] = "Forgot Password";
                $email_data['Data'] = array(
                    "FirstLastName" => $user_data['FirstName'] . ' ' . $user_data['LastName'],
                    "Link" => base_url('signup/setPassword/' . $random_guid),
                    "RandomToken" => $random_guid,
                );
                sendEmailAndSave($email_data);
            }
            return true;
        }
        return false;
    }

    /**
     * [check_user_auto_login check if user exists or not(Call for autologin)]
     * @param  [string] $Username [User name]
     * @return [type]           [description]
     */
    function check_user_auto_login($user_name, $user_id = '') {
        $user_name = strtolower(trim($user_name));
        $this->db->select(USERS . '.UserID,' . USERS . '.Language,' . USERS . '.UserGUID,' . USERLOGINS . '.LoginKeyword,' . USERS . '.FirstName,' . USERS . '.LastName,' . USERS . '.Email,' . USERS . '.ProfilePicture,' . USERS . '.StatusID,' . USERLOGINS . '.IsPasswordChange,' . USERS . '.IsProfileSetup,' . USERS . '.CanCreatePoll');
        $this->db->from(USERS);
        $this->db->join(USERLOGINS, USERLOGINS . '.UserID=' . USERS . '.UserID', 'left');
        if (!empty($user_id)) {
            $this->db->where(USERS . ".UserID", $user_id);
        } else {
            $this->db->where('(' . USERS . '.PhoneNumber="' . EscapeString($user_name) . '")', NULL, FALSE);
        }
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    /**
     * [check_user_exist check if user exists or not]
     * @param  [type] $source_id [Source ID]
     * @param  [type] $user_name [User Name]
     * @param  [type] $password  [User Password]
     * @return [bool/array]      [if exist then return user details else return false.]
     */
    function check_user_exist($source_id, $user_name, $password) {
        $user_name = strtolower(trim($user_name));

        if ($source_id == 1) {
            $this->db->select(USERS . '.UserID,' . USERS . '.Language,' . USERS . '.UserGUID,' . USERLOGINS . '.LoginKeyword,' . USERS . '.FirstName,' . USERS . '.LastName,' . USERS . '.Email,' . USERS . '.ProfilePicture,' . USERS . '.StatusID,' . USERLOGINS . '.IsPasswordChange,' . USERS . '.IsProfileSetup,' . USERLOGINS . '.Password,' . USERS . '.CanCreatePoll');
            $this->db->from(USERS);
            $this->db->join(USERLOGINS, USERLOGINS . '.UserID=' . USERS . '.UserID', 'left');
            $this->db->where(USERLOGINS . '.Password !=', '');
            $this->db->where('(LOWER(' . USERLOGINS . '.LoginKeyword)="' . EscapeString($user_name) . '" OR LOWER(' . USERS . '.Email)="' . EscapeString($user_name) . '")', NULL, FALSE);
            $this->db->order_by(USERS . '.UserID', 'DESC');
        } else {
            $this->db->select(USERLOGINS . '.*,' . USERS . '.*');
            $this->db->from(USERLOGINS);
            $this->db->join(USERS, USERLOGINS . '.UserID=' . USERS . '.UserID', 'left');
            $this->db->where(USERLOGINS . '.SourceID', $source_id);
            $this->db->where('LOWER(' . USERLOGINS . '.LoginKeyword)', $user_name);
            $this->db->order_by(USERS . '.UserID', 'DESC');
        }
        $this->db->limit(1);
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $UserData = $query->row_array();
            if ($source_id == 1) {
                $existing_password = $UserData['Password'];
                if (!password_verify($password, $existing_password)) {
                    return false;
                }
            }
            return $UserData;
        } else {
            return false;
        }
    }

    /**
     * Function Name: valid_email
     * @param str
     * Description: Check if string is valid email or not  
     */
    public function valid_email($str) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    /**
     * [add_analytics Used to add login analytics]
     * @param [type] $analytics_data [Analytics Data]
     * @param [type] $source_id     [Source ID]
     * @param [type] $user_name     [User Name]
     * @param [type] $user_id       [User ID]
     */
    public function add_analytics($analytics_data, $source_id, $user_name, $user_id) {
        $is_first_visit = '1';
        $is_email_login = '0';
        $query = $this->db->select('UserID')->from(ANALYTICLOGINS)->where('UserID', $user_id)->limit(1)->get();
        if ($query->num_rows() > 0) {
            $is_first_visit = '0';
        }
        if ($source_id == '1') {
            if ($this->valid_email($user_name)) {
                $is_email_login = '1';
            }
        }

        $analytics_data['IsEmail'] = $is_email_login;
        $analytics_data['IsFirstVisit'] = $is_first_visit;

        $this->db->insert(ANALYTICLOGINS, $analytics_data);
        return $is_first_visit;
    }

    /**
     * [verify_login check if user login credentials is correct or not]
     * @param  [type] $data [User details like Username, Password, SourceID, IPAddress, DeviceID, DeviceTypeID, Latitude, Longitude, CityID]
     * @return [array]       [User details]
     */
    function verify_login($data, $is_from_signup = 0) {
        $return['Data'] = array();
        $return['ResponseCode'] = 200;
        $return['Message'] = lang('success');
        //Get values and Set variables
        //$user_name              = $data['Username'];
        $user_name = $data['PhoneNumber'];
        $password = @$data['Password'];
        $source_id = $data['SourceID'];
        $ip_address = $data['IPAddress'];
        $device_id = $data['DeviceID'];
        $DeviceToken = $data['DeviceToken'];
        $device_type_id = $data['DeviceTypeID'];
        $latitude = $data['Latitude'];
        $longitude = $data['Longitude'];
        $isApp = isset($data['IsApp']) ? $data['IsApp'] : 0;
        $social_signup = 0;
        $is_first_visit = '0';
        $create_account = '0';
        $login_session_key = get_guid();

        // Check if user is already exists or not
        if (!empty($data['AutoLogin'])) {
            $user_id = isset($data['UserID']) ? $data['UserID'] : '';
            $user_data = $this->check_user_auto_login($user_name, $user_id);
        } else {
            $user_data = $this->check_user_exist($source_id, $user_name, $password);
        }

        if (!$user_data && isset($data['Email']) && !empty($data['Email'])) {
            $user_data = $this->check_user_exist($source_id, $user_name, $password);
        }

        $login_data = $data;
        $login_data['LoginSessionKey'] = $login_session_key;
        $login_data['Username'] = $user_name;
        $login_data['IPAddress'] = $ip_address;
        $login_data['BrowserID'] = check_browser();
        $device_info = isset($data['DeviceInfo']) ? json_encode($data['DeviceInfo']) : '';
        $login_data['DeviceInfo'] = $device_info;
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
        if (isset($user_data) && !empty($user_data)) {
            if ($is_from_signup == 1) {
                $return['ResponseCode'] = 512;
                $return['Message'] = lang('email_exists');
            } else if ($user_data['StatusID'] == 2 || $user_data['StatusID'] == 1 || $user_data['StatusID'] == 6 || $user_data['StatusID'] == 7) {
                $login_data['UserID'] = $user_data['UserID'];
                $login_data['IsLoginSuccessfull'] = 1;
                initiate_worker_job('update_login_analytics', $login_data);
                //$this->update_login_analytics($login_data);                

                /* Login Success */
                /* Multisession handling - starts */
                if (!MULTISESSION) {
                    $this->db->delete(ACTIVELOGINS, array('UserID' => $user_data['UserID']));
                }
                /* Multisession handling - ends */
                $data['ResolutionID'] = $this->get_resolution_id($data['Resolution']);

                if(!empty($DeviceToken)) {
                    $this->db->delete(ACTIVELOGINS, array('DeviceToken' => $DeviceToken));
                }
                

                $active_login['UserID'] = $user_data['UserID'];
                $active_login['LoginSessionKey'] = $login_session_key;
                $active_login['DeviceID'] = $device_id;
                $active_login['DeviceToken'] = $DeviceToken;
                $active_login['IPAddress'] = $ip_address;
                $active_login['ResolutionID'] = $data['ResolutionID'];
                $active_login['LoginSourceID'] = $source_id;
                $active_login['DeviceTypeID'] = $device_type_id;
                $active_login['Latitude'] = $latitude;
                $active_login['Longitude'] = $longitude;
                $active_login['CreatedDate'] = $created_date;
                $active_login['IsApp'] = $isApp;
                $active_login['BrowserID'] = check_browser();
                $this->db->insert(ACTIVELOGINS, $active_login);

                $time_zone = 'Asia/Calcutta';
                $this->db->select('TZ.StandardTime');
                $this->db->select('IFNULL(UD.AndroidAppVersion,"") as AndroidAppVersion', FALSE);
                $this->db->select('IFNULL(UD.IOSAppVersion,"") as IOSAppVersion', FALSE);                
                $this->db->from(USERDETAILS . ' UD');
                $this->db->join(TIMEZONES . ' TZ', 'TZ.TimeZoneID=UD.TimeZoneID', 'left');                
                $this->db->where('UD.UserID', $user_data['UserID']);
                $this->db->limit(1);
                $query = $this->db->get();
                $app_version = '';
                if ($query->num_rows()) {
                    $row = $query->row();
                    $time_zone = $row->StandardTime;
                    if($device_type_id == 2) {
                        $app_version = $row->IOSAppVersion;
                    }
                    if($device_type_id == 3) {
                        $app_version = $row->AndroidAppVersion;
                    }
                }

                $is_first_visit = '1';
                $query = $this->db->select('UserID')->from(ANALYTICLOGINS)->where('UserID', $user_data['UserID'])->limit(1)->get();
                if ($query->num_rows() > 0) {
                    $is_first_visit = '0';
                }

                $this->load->model('timezone/timezone_model');
                $this->load->model('users/user_model');
                $this->load->model('locality/locality_model');
                //Set response data
                $return['Data'] = array(
                    'LoginSessionKey' => $login_session_key,
                    'Latitude' => $data['Latitude'],
                    'Longitude' => $data['Longitude'],
                    'IPAddress' => $data['IPAddress'],
                    'UserGUID' => $user_data['UserGUID'],
                    'FirstName' => $user_data['FirstName'],
                    'LastName' => $user_data['LastName'],
                    'Email' => $user_data['Email'],
                    'CanCreatePoll' => $user_data['CanCreatePoll'],
                    'ProfilePicture' => $user_data['ProfilePicture'],
                    'IsPasswordChange' => $user_data['IsPasswordChange'],
                    'IsFirstLogin' => $is_first_visit,
                    'TimeZoneOffset' => $this->timezone_model->get_time_zone_offset($time_zone),
                    'StatusID' => $user_data['StatusID'],
                    'Language' => $user_data['Language'],
                    'UserID' => $user_data['UserID'],
                    'IsProfileSetup' => $user_data['IsProfileSetup'],
                    'Username' => $user_data['LoginKeyword'],
                    'AppVersion' => $app_version,
                    'IsSuperAdmin' => $this->user_model->is_super_admin($user_data['UserID']),
                    'IsAdmin' => $this->user_model->is_super_admin($user_data['UserID'], 1),
                    'Locality' => $this->locality_model->get_user_locality($user_data['UserID'])
                );


                $this->db->select('PR.Url', FALSE);
                $this->db->from(PROFILEURL . ' PR');
                $this->db->where('PR.EntityType', 'User');
                $this->db->where('PR.EntityID', $user_data['UserID']);
                $this->db->where('PR.StatusID', 2);
                $this->db->limit(1);
                $query = $this->db->get();
                $row = $query->row_array();

                $return['Data']['ProfileURL'] = (!empty($row['Url'])) ? $row['Url'] : '';
            }
            /* added by gautam - starts */ elseif ($user_data['StatusID'] == 1) {
                $this->load->model('users/signup_model');
                $this->signup_model->send_activation_link($user_data['UserGUID']);
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('account_not_activate');
                $return['Data']['UserGUID'] = $user_data['UserGUID'];
                $return['Data']['StatusID'] = $user_data['StatusID'];
            } elseif ($user_data['StatusID'] == 20) {
                $return['ResponseCode'] = 511;
                $return['Data']['StatusID'] = $user_data['StatusID'];
                $return['Message'] = lang('user_deactivated');
            }
            /* added by gautam - end */ elseif ($user_data['StatusID'] == 4) {
                $return['ResponseCode'] = 511;
                $return['Data']['StatusID'] = $user_data['StatusID'];
                $return['Message'] = lang('user_blocked');
            } elseif ($user_data['StatusID'] == 23) {
                $return['ResponseCode'] = 511;
                $return['Data']['StatusID'] = $user_data['StatusID'];
                $return['Message'] = lang('user_suspended');
            } else {
                $return['ResponseCode'] = 511;
                $return['Data']['StatusID'] = 0;
                $return['Message'] = lang('invalid_login_credentials');
            }
        } else {
            /* Error - Email ID or Password is incorrect. */
            if ($source_id == 1) {
                $query = $this->db->select(USERS . '.UserID')
                        ->from(USERS)
                        ->join(USERLOGINS, USERLOGINS . '.UserID=' . USERS . '.UserID', 'left')
                        ->where('(' . USERLOGINS . '.LoginKeyword="' . EscapeString($user_name) . '" OR ' . USERS . '.Email="' . EscapeString($user_name) . '")', NULL, FALSE)
                        ->limit(1)
                        ->get();
                if ($query->num_rows() > 0) {
                    $user_data = $query->row_array();
                    $login_data['UserID'] = $user_data['UserID'];
                    $login_data['IsLoginSuccessfull'] = 0;
                    initiate_worker_job('update_login_analytics', $login_data);
                    //$this->update_login_analytics($login_data);                    
                }
            }
            $return['ResponseCode'] = 511;
            $return['Data']['StatusID'] = 0;
            $return['Message'] = lang('invalid_login_credentials');
            $error_login = 1;
            //}
        }
        if (isset($error_login)) {
            //If theres error in login then add entry in analytic login errors only if entry is not exists yet otherwise update
            $checkError = $this->db->get_where(ANALYTICLOGINERRORS, array('DATE(`CreatedDate`)' => get_current_date('%Y-%m-%d'), 'ClientErrorId' => '5'));
            if ($checkError->num_rows()) {
                $this->db->set('ErrorCount', 'ErrorCount+1', FALSE)
                        ->where('DATE(`CreatedDate`)="' . get_current_date('%Y-%m-%d') . '"', NULL, FALSE)
                        ->where('ClientErrorId', '5')
                        ->update(ANALYTICLOGINERRORS);
            } else {
                $this->db->insert(ANALYTICLOGINERRORS, array('ClientErrorId' => '5', 'CreatedDate' => get_current_date('%Y-%m-%d'), 'ErrorCount' => '1'));
            }
        }

        if ($return['ResponseCode'] == 200) {
            if (isset($data['Token'])) {
                $this->load->model('users/friend_model');
                $this->friend_model->addFriendByToken($data['Token'], $user_data['UserID']);
            }
        }
        return $return;
    }

    function update_login_analytics($data) {
        $this->load->helper('location');
        $location_details = get_location_details($data['IPAddress'], $data['Latitude'], $data['Longitude']);
        $data['CityID'] = $location_details['CityID'];
        $data['Latitude'] = $location_details['Latitude'];
        $data['Longitude'] = $location_details['Longitude'];
        $data['IPAddress'] = $location_details['IPAddress'];

        $data['WeekdayID'] = $this->get_week_day_id(date('l'));
        $data['TimeSlotID'] = $this->get_time_slot();
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');

        if (!empty($data['IsLoginSuccessfull'])) {
            $login_count = $this->save_login_history($data['UserID'], $data['LoginSessionKey']);

            //For update login time
            $user_login_data = array();
            $user_login_data['LastLoginDate'] = $created_date;
            $this->db->where('UserID', $data['UserID']);
            $this->db->update(USERS, $user_login_data);
        }

        //Set variables and insert data into analytic login table
        $analytics['UserID'] = $data['UserID'];
        $analytics['IPAddress'] = $data['IPAddress'];
        $analytics['CityID'] = $data['CityID'];
        $analytics['LoginSessionKey'] = $data['LoginSessionKey'];
        $analytics['LoginSourceID'] = $data['SourceID'];
        $analytics['DeviceTypeID'] = $data['DeviceTypeID'];
        $analytics['WeekdayID'] = $data['WeekdayID'];
        $analytics['TimeSlotID'] = $data['TimeSlotID'];
        $analytics['IsLoginSuccessfull'] = $data['IsLoginSuccessfull'];
        $analytics['IsFirstVisit'] = '0';
        $analytics['CreatedDate'] = $created_date;
        $analytics['ModifiedDate'] = $created_date;
        $analytics['IsEmail'] = '0';
        $analytics['BrowserID'] = $data['BrowserID'];
        $analytics['AgeGroupID'] = $this->get_age_group_id($data['UserID']);
        $analytics['DeviceInfo'] = $data['DeviceInfo'];
        ;

        $this->add_analytics($analytics, $data['SourceID'], $data['Username'], $data['UserID']);
    }

    function copy_login_analytics($login_session_key) {
        $login_user_data = get_analytics_id($login_session_key, 0, '*', 2);

        if (!empty($login_user_data) && isset($login_user_data['UserID'])) {
            $created_date = get_current_date('%Y-%m-%d %H:%i:%s');

            $analytics['UserID'] = $login_user_data['UserID'];
            $analytics['CityID'] = $login_user_data['CityID'];
            $analytics['IPAddress'] = $login_user_data['IPAddress'];
            $analytics['WeekdayID'] = $this->get_week_day_id(date('l'));
            $analytics['TimeSlotID'] = $this->get_time_slot();
            $analytics['LoginSessionKey'] = $login_user_data['LoginSessionKey'];
            $analytics['DeviceTypeID'] = $login_user_data['DeviceTypeID'];
            $analytics['IsLoginSuccessfull'] = $login_user_data['IsLoginSuccessfull'];
            $analytics['IsFirstVisit'] = $login_user_data['IsFirstVisit'];
            $analytics['CreatedDate'] = $created_date;
            $analytics['ModifiedDate'] = $created_date;
            $analytics['IsEmail'] = $login_user_data['IsEmail'];
            $analytics['BrowserID'] = $login_user_data['BrowserID'];
            $analytics['AgeGroupID'] = $this->get_age_group_id($analytics['UserID']);
            $analytics['DeviceInfo'] = $login_user_data['DeviceInfo'];
            $analytics['LoginSourceID'] = $login_user_data['LoginSourceID'];

            if (!empty($analytics['IsLoginSuccessfull'])) {
                $login_count = $this->save_login_history($analytics['UserID'], $analytics['LoginSessionKey']);

                //For update login time
                $user_login_data = array();
                $user_login_data['LastLoginDate'] = $created_date;
                $this->db->where('UserID', $analytics['UserID']);
                $this->db->update(USERS, $user_login_data);
            }
            $this->db->insert(ANALYTICLOGINS, $analytics);
        }
    }

    /**
     * [is_password_changed check if user password is changed or not]
     * @param [type] $login_session_key [Login Session Key]
     * @return boolean                  [true/false]
     */
    function is_password_changed($login_session_key) {
        $query = $this->db->select(USERLOGINS . '.IsPasswordChange as IsPasswordChange')
                ->from(USERLOGINS)
                ->join(ACTIVELOGINS, ACTIVELOGINS . '.UserID=' . USERLOGINS . '.UserID', 'left')
                ->where(ACTIVELOGINS . '.LoginSessionKey', $login_session_key)
                ->limit(1)
                ->get();
        if ($query->num_rows()) {
            return $query->row()->IsPasswordChange;
        } else {
            return 0;
        }
    }

    /**
     * Function Name: check_forgot_password_token
     * @param token
     * Description: check if user recovery Token is valid or not 
     */
    function check_forgot_password_token($token) {
        $query = $this->db->get_where(USERRESETPASSWORDS, array('IsPasswordReset' => '0', 'UserGUID' => $token));
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [active_login_auth get user details from login_session_key]
     * @param  [type] $data [User data like login_session_key, DeviceTypeID]
     * @return [type]       [description]
     */
    function active_login_auth($data) {
        $return = $this->return;
        $login_session_key = isset($data[AUTH_KEY]) ? $data[AUTH_KEY] : '';
        
        if ($this->form_validation->required($login_session_key) == '') {
            $return['ResponseCode'] = 412;
            $return['Message'] = lang('not_authorized');
        } else {
            $query = $this->db->select(USERS . '.UserID,' . USERS . '.UserGUID as LoggedInGUID,' . USERS . '.UserTypeID, CONCAT(' . USERS . '.FirstName," ",' . USERS . '.LastName) as LoggedInName,if(' . USERS . '.ProfilePicture="","user_default.jpg",' . USERS . '.ProfilePicture) as LoggedInProfilePicture,' . ACTIVELOGINS . '.DeviceTypeID,' . ACTIVELOGINS . '.IsApp,' . ACTIVELOGINS . '.LoginSourceID as SourceID, ' . USERROLES . '.RoleID, ' . USERDETAILS . '.LocalityID, ' . USERS . '.LastLoginDate,' . USERS . '.CanCreatePoll', false)
                    ->from(USERS)
                    ->join(ACTIVELOGINS, ACTIVELOGINS . '.UserID=' . USERS . '.UserID', 'inner')
                    ->join(USERROLES, USERROLES . '.UserID=' . USERS . '.UserID', 'left')
                    ->join(USERDETAILS, USERDETAILS . '.UserID=' . USERS . '.UserID')
                    ->where('LoginSessionKey', $login_session_key)
                    ->where_not_in(USERS . '.StatusID', array(3, 4, 23))
                    ->limit(1)
                    ->get();
            //echo $this->db->last_query();
            if ($query->num_rows() > 0) {
                $row = $query->row_array();

                $current_date = get_current_date('%Y-%m-%d');
                $logged_in_date = $row['LastLoginDate'];
                $logged_in_date = date('Y-m-d', strtotime($logged_in_date));

                if ($logged_in_date < $current_date) {
                    $this->copy_login_analytics($login_session_key);
                    /* $user_login_data = array();
                      $user_login_data['LastLoginDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                      $this->db->where('UserID', $row['UserID']);
                      $this->db->update(USERS, $user_login_data); */
                }
                $return['Data'] = $row;
            } else {
                $return['ResponseCode'] = 502;
                $return['Message'] = lang('invalid_key');
            }
        }
        return $return;
    }

    /**
     * Function Name: set_remember_me
     * @param user_id
     * Description: Remember user login
     */
    public function set_remember_me($user_id) {
        $cookie = array(
            'name' => 'remember_me',
            'value' => $user_id,
            'expire' => '7776000'  // 90 days expiration time
        );
        $this->input->set_cookie($cookie);
    }

    /**
     * Function Name: save_login_history
     * @param UserID
     * @param LoginSessionKey
     * Description: Save login history in session log table
     */
    function save_login_history($user_id, $LoginSessionKey) {
        $count = 1;
        $insert = array();
        $insert['UserID'] = $user_id;
        $insert['LoginSessionKey'] = $LoginSessionKey;
        $insert['StartDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $this->db->insert(SESSIONLOGS, $insert);
        return $count;
    }

    /**
     * Function Name: get_login_count
     * @param user_id
     * Description: Get login count of user
     */
    function get_login_count($user_id) {
        $query = $this->db->get_where(SESSIONLOGS, array('UserID' => $user_id));
        $count = $query->num_rows();
        return $count;
    }

    /**
     * Function Name: is_email_exist
     * @param Email
     * Description: Check if email address is already exists
     */
    function is_email_exist($email, $type = 'Mobile') {
        $this->db->select('UserID, StatusID');
        if ($type == 'Mobile') {
            $this->db->where('PhoneNumber', EscapeString($email));
        } else {
            $email = strtolower($email);
            $this->db->where('Email', EscapeString($email));
        }

        $this->db->order_by('UserID', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get(USERS);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            if ($result['StatusID'] == 1 || $result['StatusID'] == 6) {
                $return = 'inactive';
            } else if ($result['StatusID'] == 3) {
                $return = 'deleted';
            } else if ($result['StatusID'] == 4) {
                $return = 'blocked';
            } else {
                $return = 'exist';
            }
        } else {
            $return = 'notexist';
        }
        return $return;
    }

    /**
     * Function Name: check_password
     * @param user_id
     * @param password
     * Description: Check user password is valid or not
     */
    function check_password($user_id, $password) {
        $query = $this->db->select(USERS . '.UserID,' . USERS . '.FirstName,' . USERS . '.LastName,' . USERS . '.Email,' . USERLOGINS . '.Password')
                ->from(USERS)
                ->join(USERLOGINS, USERS . '.UserID=' . USERLOGINS . '.UserID', 'left')
                ->where(USERLOGINS . '.Password !=', '')
                ->where(USERS . '.UserID', $user_id)
                ->limit(1)
                ->get();

        if ($query->num_rows() >= 1) {
            $UserData = $query->row_array();
            $existing_password = $UserData['Password'];
            if (!password_verify($password, $existing_password)) {
                return false;
            }
            return $UserData;
        } else {
            return false;
        }
    }

    /**
     * Function Name: update_password
     * @param user_data
     * @param new_password
     * Description: Update password of user
     */
    function update_password($user_data, $new_password, $is_email = TRUE) {
        $this->db->where('UserID', $user_data['UserID'])
                ->update(USERLOGINS, array('Password' => generate_password($new_password), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'IsPasswordChange' => '0'));
        /* Send notification email to user for password change - starts */
        if ($is_email) {
            $email_data = array();
            $email_data['IsResend'] = 0;
            $email_data['Subject'] = SITE_NAME . " Password Assistance";
            $email_data['TemplateName'] = "emailer/change_password";
            $email_data['Email'] = $user_data['Email'];
            $email_data['EmailTypeID'] = CHANGE_PASSWORD_EMAIL_TYPE_ID;
            $email_data['UserID'] = $user_data['UserID'];
            $email_data['StatusMessage'] = "Change Password";
            $email_data['Data'] = array("FirstLastName" => $user_data['FirstName'] . ' ' . $user_data['LastName']);
            sendEmailAndSave($email_data);
        }
    }

    /**
     * Function Name: set_password
     * @param user_id
     * @param password
     * Description: Set user password for first time
     */
    function set_password($user_id, $password) {
        $this->db->where('UserID', $user_id)
                ->update(USERLOGINS, array('Password' => generate_password($password), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'IsPasswordChange' => '0', 'SetPassword' => '1'));
        if (CACHE_ENABLE) {
            $this->cache->delete('user_profile_' . $user_id);
        }
    }

    /**
     * [confirm_email Update user status if he confirm his email]
     * @param  [string] $user_guid [User GUID]
     * @return [array]             [confirmation information]
     */
    public function confirm_email($user_guid) {
        $return = array('msg' => '1', 'email' => '');
        $this->db->select('UserID, Email, StatusID');
        $this->db->from(USERS);
        $this->db->where(array('ActivationCode' => $user_guid));
        $this->db->limit(1);
        $query = $this->db->get();
        $return['msg'] = 2;
        if ($query->num_rows()) {
            $row = $query->row();
            $status = $row->StatusID;
            if ($status == 2) {
                $return['msg'] = 3;
            } else {
                $status = ($status == 1) ? 2 : 7;
                $this->db->where('ActivationCode', $user_guid);
                $this->db->update(USERS, array('StatusID' => $status, 'ActivationCode' => NULL));
                $return['email'] = $row->Email;
                $return['msg'] = 1;
                $this->cache->delete('user_profile_' . $row->UserID);
            }
        }
        return $return;
    }

    /**
     * [sign_out Destroy user session]
     * @param  [type] $login_session_key [description]
     */
    public function sign_out($login_session_key) {
        $this->db->where(array('LoginSessionKey' => $login_session_key));
        $this->db->limit(1);
        $this->db->delete(ACTIVELOGINS);
        $this->session->userdata('LoginSessionKey', '');
        $this->session->sess_destroy();
    }

    /**
     * [check_social_user_exists Check if social signup user is exists or not]
     * @param  [string] $social_id     [Social ID]
     * @param  [int] $social_type_id [Social Type ID]
     * @return [type]               [description]
     */
    public function check_social_user_exists($social_id, $social_type_id, $user_id = 0) {
        $this->db->select('U.UserGUID');
        if ($social_type_id == 5 || $social_type_id == 6) { // $SocialTypeID == 5 (Yahoo)
            $this->db->from(USERS . ' U');
            $this->db->where('Email', $social_id);
        } else {
            $this->db->from(USERS . ' U');
            $this->db->join(USERLOGINS . ' UL', 'UL.UserID=U.UserID', 'left');
            $this->db->where('UL.SourceID', $social_type_id);
            $this->db->where('UL.LoginKeyword', $social_id);
        }
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return array('UserGUID' => $row->UserGUID, 'Status' => 1);
        } else {
            $this->db->where('UserSocialID', $social_id);
            $this->db->where('InviteType', $social_type_id);
            $this->db->where('UserID', $user_id);
            $this->db->limit(1);
            $query = $this->db->get(INVITATION);
            if ($query->num_rows()) {
                return array('UserGUID' => '', 'Status' => 2);
            } else {
                return false;
            }
        }
    }

    /**
     * [get_email_from_token Get user email from Token]
     * @param  [string] $token [Invited Token]
     * @return [string]        [user social account id or email address]
     */
    public function get_email_from_token($token) {
        $this->db->where('InviteType', '0');
        $this->db->where('Token', $token);
        $this->db->limit(1);
        $query = $this->db->get(INVITATION);
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->UserSocialID;
        } else {
            return '';
        }
    }

    /**
     * [get_password_reset_data get data of user who submits password reset request]
     * @param  [type] $user_guid [User GUID]
     * @return [array/bool]      [password reset data or false]
     */
    function get_password_reset_data($user_guid) {
        $this->db->select('*');
        $this->db->from(USERRESETPASSWORDS);
        $this->db->where(array('IsPasswordReset' => '0', 'UserGUID' => $user_guid));
        $this->db->limit(1);
        $query = $this->db->get();                
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    /**
     * Function Name: forgot_password_link
     * @param Password
     * @param UserResetdata
     * @param UserGUID
     * Description: One time link for password reset
     */
    function forgot_password_link($password, $user_reset_data, $user_guid, $type = "Email") {

        /* Update directly user password here */
        $this->db->where('UserID', $user_reset_data['UserID'])
                ->update(USERLOGINS, array('Password' => generate_password($password), 'IsPasswordChange' => '0'));

        /* Now Update UserResetPassword Table -- to change field IsPasswordReset to 1, DO NOT DELETE */
        $this->db->where('UserID', $user_reset_data['UserID']);
        $this->db->where('UserGUID', $user_guid);
        $this->db->update(USERRESETPASSWORDS, array('IsPasswordReset' => '1'));

        /* Change User Status if Status is 1 */
        $this->db->where('UserID', $user_reset_data['UserID']);
        $this->db->where('StatusID', '1');
        $this->db->update(USERS, array('StatusID' => '2'));

        /* Now Get User information for send email */

        $query = $this->db->select(USERS . '.Email,' . USERS . '.FirstName,' . USERS . '.LastName')
                ->from(USERS)
                ->where('UserID', $user_reset_data['UserID'])
                ->limit(1)
                ->get();
        $user_data = $query->row_array();
        if ($type == "Email") {
            /* Send New Password Email - Starts */
            $email_data = array();
            $email_data['IsResend'] = 0;
            $email_data['Subject'] = SITE_NAME . ' ' . lang('set_new_password_assis');
            $email_data['TemplateName'] = "emailer/set_new_password";
            $email_data['Email'] = $user_data['Email'];
            $email_data['EmailTypeID'] = CHANGE_PASSWORD_EMAIL_TYPE_ID;
            $email_data['UserID'] = $user_reset_data['UserID'];
            $email_data['StatusMessage'] = "Change Password";
            $email_data['Data'] = array("FirstLastName" => $user_data['FirstName'] . ' ' . $user_data['LastName'], "Password" => $password);

            sendEmailAndSave($email_data);
        }
        $return['Data'] = array('NewPassword' => $password);
        /* Send Password Recovery Email - Ends */
    }

    /**
     * [is_user_details_exists : Check if user filled appropriate details or not
     */
    function is_user_details_exists($entity_id, $entity_type = 'UserID') {
        $this->db->select('U.FirstName,U.LastName,U.ProfilePicture,UD.CountryID,UD.CityID,UD.HomeCountryID,UD.HomeCityID');
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        if ($entity_type == 'UserID') {
            $this->db->where('U.UserID', $entity_id);
        } else if ($entity_type == 'UserGUID') {
            $this->db->where('U.UserGUID', $entity_id);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            if (!empty($row->FirstName) && !empty($row->LastName) && !empty($row->ProfilePicture) && !empty($row->CountryID) && !empty($row->CityID)) {
                return true;
            } else {
                return false;
            }
        }
    }

    function expire_password_reset_tokens() {
        $this->db->where('IsPasswordReset !=', 1);
        $this->db->where('CreatedDate < DATE_SUB("' . get_current_date('%Y-%m-%d %H:%i:%s') . '", INTERVAL ' . PASSWORD_RESETTOKEN_EXPIRE_SECONDS . ' SECOND);', NULL, FALSE);
        $this->db->update(USERRESETPASSWORDS, [
            'IsPasswordReset' => 1
        ]);
    }

    /**
     * [get_user_rights : Used to get user right ids
     */
    public function get_user_rights($user_id) {
        $rightsArr = array();
        $roleArr = array(0);
        if (CACHE_ENABLE) {
            $rightsArr = $this->cache->get('user_rights_' . $user_id);
            $rightsArr = json_decode($rightsArr);
        }
        if (empty($rightsArr)) {
            $this->db->select('UR.RoleID,RR.RightID AS RightID', FALSE);
            $this->db->from(USERROLES . "  UR ");
            $this->db->join(ROLERIGHTS . " AS RR", ' RR.RoleID = UR.RoleID', 'inner');
            $this->db->where('UR.UserID', $user_id);
            $this->db->group_by('RR.RightID');
            $query = $this->db->get();
            $results = $query->result_array();

            foreach ($results as $right) {
                $rightsArr[] = $right['RightID'];
                if (!in_array($right['RoleID'], $roleArr)) {
                    $roleArr[] = $right['RoleID'];
                }
            }

            if (CACHE_ENABLE) {
                $jsonrightsArr = json_encode($rightsArr);
                $this->cache->save('user_rights_' . $user_id, $jsonrightsArr, CACHE_EXPIRATION);
                $this->cache->save('user_roles_' . $user_id, $roleArr, CACHE_EXPIRATION);
            }
        }
        return $rightsArr;
    }

    /**
     * Function Name: update_city
     * @param Latitude
     * @param Longitude
     * Description: update city after login
     */
    public function update_city($data) {
        $this->load->helper('location');

        $location_details = get_location_details($data['IPAddress'], $data['Latitude'], $data['Longitude']);
        $data['CityID'] = $location_details['CityID'];
        $return = array();
        if (isset($data['CityID'])) {
            $this->db->set('CityID', $data['CityID']);
            $this->db->where('LoginSessionKey', $data[AUTH_KEY]);
            $this->db->update(ANALYTICLOGINS);
        }
        $return['ResponseCode'] = 200;
        $return['Message'] = lang('success');

        return $return;
    }

    public function send_otp($phone_no) {
        $otpData = array();
        $condition = array("PhoneNumber" => $phone_no);
        $otpData["CreatedDate"] = get_current_date('%Y-%m-%d %H:%i:%s');
        $otpData["ModifiedDate"] = get_current_date('%Y-%m-%d %H:%i:%s');
        $otpData["PhoneNumber"] = $phone_no;
        
        $this->db->trans_start();        
        $otp = unique_random_string(MANAGEOTP, 'OtpCode', ['PhoneNumber' => $phone_no], 'nozero', 4);
        $otpData["OtpCode"] = $otp;
        
        //check for phone number existed
        $record = $this->get_single_row("*", MANAGEOTP, $condition);

        if (empty($record)) {
            $this->db->insert(MANAGEOTP, $otpData);
        } else {
            unset($otpData['PhoneNumber']);
            unset($otpData['CreatedDate']);
            $now = strtotime(get_current_date('%Y-%m-%d %H:%i:%s'));
            $created_date = $record['CreatedDate'];
            $future_time = strtotime($created_date . ' +10 minutes');

            if ($future_time > $now) {
                $otp = $record['OtpCode'];
                $this->db->where(array('PhoneNumber' => $phone_no));
                $this->db->update(MANAGEOTP, array('ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
            } else {
                $this->db->where(array('PhoneNumber' => $phone_no));
                $this->db->update(MANAGEOTP, $otpData);
            }
        }
        $this->db->trans_complete();
        
        if(ACTIVE_SMS_GATEWAY == "msg91") {
            $sms_data = array();
            $sms_data['otp'] = $otp;
            $sms_data['mobile'] = $phone_no;
            $sms_data['phone_code'] = DEFAULT_PHONE_CODE;
            $sms_data['message'] = "OTP for Bhopu is ".$otp.". Please use this to verify your mobile number.";
            send_msg91_sms($sms_data);
        } else {    
            $this->load->library('TwoFactorSMS');
            $TwoF = new TwoFactorSMS(TWO_FACTOR_SMS_API_KEY, TWO_FACTOR_SMS_API_ENDPOINT);        
            $result = $TwoF->SendSMSOTPCustomWithTemplate(DEFAULT_PHONE_CODE . $phone_no, $otp, "bhopuu");
        }
    }

    public function check_otp($otp_code, $mobile) {
        $this->db->select('MO.OtpCode,MO.ModifiedDate as CreatedDate,MO.PhoneNumber');
        $this->db->from(MANAGEOTP . ' MO');
        $this->db->where('MO.OtpCode', $otp_code);
        $this->db->where('MO.PhoneNumber', $mobile);
        $this->db->limit(1);

        $user_record = $this->db->get()->row_array();
        if (!$user_record) {
            return FALSE;
        }

        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $created_date = $user_record['CreatedDate'];
        $now = strtotime($current_date);
        $time = strtotime($created_date . ' +5 minutes');

        if ($otp_code == $user_record['OtpCode'] && $time > $now) {
            $phone_no = $user_record['PhoneNumber'];
            //valid code
            $updateData = array();
            $updateData["PhoneVerified"] = 1;
            $updateData["StatusID"] = 2;
            $this->db->where(array('PhoneNumber' => $phone_no));
            $this->db->where_not_in("StatusID", array(3, 4));
            $this->db->update(USERS, $updateData);

            $this->db->where(array('PhoneNumber' => $phone_no));
            $this->db->delete(MANAGEOTP);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function resend_otp($phone_no) {
        $condition = array("PhoneNumber" => $phone_no);
        //check for user existed        
        $this->db->select('PhoneVerified');
        $this->db->from(USERS);
        $this->db->where($condition);
        $this->db->where_not_in("StatusID", array(3, 4));
        $this->db->limit(1);
        $record = $this->db->get()->row_array();

        if (isset($record["PhoneVerified"]) && $record["PhoneVerified"] == 1) {
            return 1; //already verified
        } else {
            //check for phone number existed
            $record = $this->get_single_row("*", MANAGEOTP, $condition);
            if (empty($record)) {
                return 2; // Phone number not exist
            } else {
                $created_date = $record['ModifiedDate'];
                $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
                $now = strtotime($current_date);
                $time = strtotime($created_date . ' +5 minutes');
                if ($time > $now) {
                    return 3; // send multiple request at same time
                }
                $this->send_otp($phone_no);
                return 4;
            }
        }
    }
}
?>
