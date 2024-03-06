<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Login_model extends Admin_Common_Model {

    public $pathToFolder;

    public function __construct() {
        parent::__construct();
        $urlArr = explode($_SERVER['HTTP_HOST'] . '/', base_url());
        if (isset($urlArr[1]) && !empty($urlArr[1])) {
            $this->pathToFolder = $urlArr[1];
        } else {
            $this->pathToFolder = '/';
        }
    }


    function recoveryPassword($type='Url',$Value){
        if($type=='Url'){
            $this->db->select(USERS.'.Email,'.USERS.'.FirstName,'.USERS.'.LastName,'.USERS.'.UserID,'.USERLOGINS.'.SourceID');
            $this->db->from(USERS);
            $this->db->join(USERLOGINS,USERLOGINS.'.UserID='.USERS.'.UserID','inner');
            if($type == 'Url'){
                $this->db->where(USERS.'.Email',$Value);
            }
            $this->db->limit(1);
            $Query = $this->db->get();

            if($Query->num_rows()){
                $Userdata = $Query->row_array();
            
                /* Create One Time Password Link */
                $BaseUrl = base_url('signup/setPassword/');
                $RandomGUID = random_string('alnum', 8);
                $OneTimeLink = $BaseUrl.'/'.$RandomGUID;
                /* Save Data into UserResetPassword Table */
                $UserResetPasswordData = array(
                                                   'UserID' => $Userdata['UserID'],
                                                   'UserGUID' => $RandomGUID,
                                                   'IsPasswordReset' => 0,
                                                   'CreatedDate' =>  date("Y-m-d H:i:s"),
                                                   'ModifiedDate' => date("Y-m-d H:i:s")
                                              );
                
                
                $this->db->insert(USERRESETPASSWORDS, $UserResetPasswordData);
                
                /* Send One Time PasswordF Link Email Templates - Starts */
                $Subject = SITE_NAME . " Password Assistance";
                $Template = THEAM_PATH . "email/reset-password-usinglink.html"; /* Custom email template */
                $values = array("##FIRST_LAST_NAME##" => $Userdata['FirstName'] . ' ' . $Userdata['LastName'], '##ONE_TIME_LINK##' => $OneTimeLink);
                //sendMail(EMAIL_NOREPLY_FROM, EMAIL_NOREPLY_NAME, $Template, $values, $Userdata['Email'], $Subject);
            } 
            return true;
        } else {
            $RandomPassword = random_string('alnum', 8);
            /* Query to check and select userData - starts   */
            $this->db->select(USERS.'.Email,'.USERS.'.FirstName,'.USERS.'.LastName,'.USERS.'.UserID,'.USERLOGINS.'.SourceID');
            $this->db->from(USERS);
            $this->db->join(USERLOGINS,USERS.'.UserID='.USERLOGINS.'.UserID','inner');
            if($type=='Username'){
                $this->db->where(USERLOGINS.'.LoginKeyword',$Value);
            }
            if($type=='Email'){
                $this->db->where(USERS.'.Email',$Value);
            }
            if($type=='PhoneNumber'){
                $this->db->where("CONCAT(".USERS.".CountryCode, ".USERS.".MobileNumber)='$Value' and ".USERS.".MobileNumberStatus=5",NULL,FALSE);
            }
            $this->db->limit(1);
            $Query = $this->db->get();
            if ($Query->num_rows() > 0) {
                $Userdata = $Query->row_array();
                // Update directly user password here 
                $this->db->where('UserID',$Userdata['UserID']);
                $this->db->update(USERLOGINS,array('Password'=>generate_password($RandomPassword),'IsPasswordChange'=>'1'));
                //Send Password Recovery Email - Starts
                $Subject = SITE_NAME . " Password Assistance";
                $Template = THEAM_PATH . "email/reset-password.html"; /* Custom email template */
                $values = array("##FIRST_LAST_NAME##" => $Userdata['FirstName'] . ' ' . $Userdata['LastName'], '##PASSWORD##' => $RandomPassword);
                //sendMail(EMAIL_NOREPLY_FROM, EMAIL_NOREPLY_NAME, $Template, $values, $Userdata['Email'], $Subject);
                $Return['Data'] = array('NewPassword' => $RandomPassword);
                return $Return['Data'];
            }
        }
            return false;     
    }


    function verifyLogin($Data) {
        error_reporting(0);
        $Return['Data'] = array();
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        
        $only_right_id = isset($Data['OnlyRightsID']) ? $Data['OnlyRightsID'] : 0;
        $Username = $Data['Username'];
        $Username = isset($Data['UserName']) ? $Data['UserName'] : $Username;
        $Password = $Data['Password'];
        $SourceID = $Data['SourceID'];
        $IPAddress = $Data['IPAddress'];
        $DeviceID = $Data['DeviceID'];
        $DeviceTypeID = $Data['DeviceTypeID'];
        $Latitude = $Data['Latitude'];
        $Longitude = $Data['Longitude'];
        $WeekdayID = $this->getWeekDayID(date('l'));
        $TimeSlotID = $this->getTimeSlot();
        $socialSignup = 0;
        $FirstVisit = 0;
        $createAccount = '0';

        if (!isset($Data['CityID']) || $Data['CityID'] == '')
            $Data['CityID'] = NULL;
        if ($SourceID == 1) {
            $Query = $this->db->select(USERS.'.UserID,'.USERS.'.UserGUID,'.USERLOGINS.'.LoginKeyword,'.USERS.'.FirstName,'.USERS.'.LastName,'.USERS.'.Email,'.USERS.'.ProfilePicture,'.USERS.'.StatusID,'.USERLOGINS.'.IsPasswordChange, '.USERLOGINS.'.Password')
                             ->from(USERS)
                             ->join(USERLOGINS,USERLOGINS.'.UserID='.USERS.'.UserID','left')
                            // ->where(USERLOGINS.'.Password',md5($Password))
                             ->where('('.USERLOGINS.'.LoginKeyword="'.EscapeString($Username).'" OR '.USERS.'.Email="'.EscapeString($Username).'")',NULL,FALSE)
                             ->order_by(USERS . '.UserID', 'DESC')
                             ->limit(1)                             
                             ->get();
        } else {
            $Query = $this->db->select(USERLOGINS.'.*,'.USERS.'.*')
                              ->from(USERLOGINS)
                              ->join(USERS,USERLOGINS.'.UserID='.USERS.'.UserID','left')
                              ->where(USERLOGINS.'.SourceID',$SourceID)
                              ->where(USERLOGINS.'.LoginKeyword',$Username)
                              ->order_by(USERS . '.UserID', 'DESC')
                              ->limit(1)
                              ->get();
            $socialSignup = 1;
        }
        //echo $this->db->last_query();die;
        if (($Query->num_rows() >= 1)) {/* Login Success */
            $UserData = $Query->row_array();
            if ($SourceID == 1) {
                $existing_password = $UserData['Password'];
                if (!password_verify($Password, $existing_password)) {
                    $UserData = array();
                }
            }
        } else {
            
            if (isset($Data['Email']) && !empty($Data['Email'])) {
                $q = $this->db->get_where(USERS,array('Email'=>$Data['Email']));
                $this->load->model('users/signup_model');

                if ($q->num_rows() > 0) {
                    $rw = $q->row();
                    $SetPassword = 0;
                    if ($Password != '') {
                        $SetPassword = 1;
                    }
                    
                    $insertData['UserID'] = $rw->UserID;
                    $insertData['LoginKeyword'] = EscapeString($Data['UserName']);
                    $insertData['Password'] = generate_password($Data['Password']);
                    $insertData['SourceID'] = $Data['SourceID'];
                    $insertData['CreatedDate'] = date('Y-m-d H:i:s');
                    $insertData['ModifiedDate'] = date('Y-m-d H:i:s');
                    $insertData['SetPassword'] = $SetPassword;
                    $this->db->insert(USERLOGINS,$insertData);
                }

                if ($SourceID == 1) {
                    $Query = $this->db->select(USERS.'.UserID,'.USERS.'.UserGUID,'.USERLOGINS.'.LoginKeyword,'.USERS.'.FirstName,'.USERS.'.LastName,'.USERS.'.Email,'.USERS.'.ProfilePicture,'.USERS.'.StatusID,'.USERLOGINS.'.IsPasswordChange, '.USERLOGINS.'.Password')
                             ->from(USERS)
                             ->join(USERLOGINS,USERLOGINS.'.UserID='.USERS.'.UserID','left')
                            // ->where(USERLOGINS.'.Password',md5($Password))
                             ->where('('.USERLOGINS.'.LoginKeyword="'.EscapeString($Username).'" OR '.USERS.'.Email="'.EscapeString($Username).'")',NULL,FALSE)
                             ->get();
                } else {
                    $Query = $this->db->select(USERLOGINS.'.*,'.USERS.'.*')
                              ->from(USERLOGINS)
                              ->join(USERS,USERLOGINS.'.UserID='.USERS.'.UserID','left')
                              ->where(USERLOGINS.'.SourceID',$SourceID)
                              ->where(USERLOGINS.'.LoginKeyword',$Username)
                              ->get();
                    $socialSignup = 1;
                }
                $UserData = $Query->row_array();
                if ($SourceID == 1) {
                    $existing_password = $UserData['Password'];
                    if (!password_verify($Password, $existing_password)) {
                        $UserData = array();
                    }
                }
            }
        }

        if (isset($UserData) && !empty($UserData)) {

            if ($UserData['StatusID'] == 2) {
                /* Login Success */
                /* Multisession handling - starts */
                if (!MULTISESSION) {
                    $this->db->delete(ACTIVELOGINS,array('UserID'=>$UserData['UserID']));
                }
                /* Multisession handling - ends */
                $AdminLoginSessionKey = random_string('unique', 8);

                $activelogin['UserID'] = $UserData['UserID'];
                $activelogin['LoginSessionKey'] = $AdminLoginSessionKey;
                $activelogin['DeviceID'] = $DeviceID;
                $activelogin['IPAddress'] = $IPAddress;
                $activelogin['ResolutionID'] = '1';
                $activelogin['LoginSourceID'] = $SourceID;
                $activelogin['DeviceTypeID'] = $DeviceTypeID;
                $activelogin['Latitude'] = $Latitude;
                $activelogin['Longitude'] = $Longitude;
                $activelogin['CreatedDate'] = date('Y-m-d H:i:s');
                $this->db->insert(ACTIVELOGINS,$activelogin);
                /* Save session to activelogins table - ends */

                $Query = $this->db->select('RoleID')
                                  ->from(USERROLES)
                                  ->where('UserID',$UserData['UserID'])
                                  ->get();
                $RoleData = $Query->row_array();
                $UserData['RoleID'] = $RoleData['RoleID'];
                /* SAVE LOGIN SESSION OF USER */
                $loginCount = $this->SaveLoginHistory($UserData['UserID'], $AdminLoginSessionKey);
                /* Set Data for return output */
                $Redirect = site_url('admin/users');
                if ($loginCount <= 1) {
                    $Redirect = site_url('admin/users');
                }
                if ($UserData['IsPasswordChange'] == 1)//redirect to change password
                    $Redirect = site_url('admin/users');
                $FirstVisit = '1';
                $AQuery = $this->db->select('UserID')->from(ANALYTICLOGINS)->where('UserID',$UserData['UserID'])->limit(1)->get();
                if ($AQuery->num_rows() > 0) {
                    $FirstVisit = 0;
                }
                if ($SourceID == '1') {
                    $IsEmailLogin = 1;
                } else {
                    $IsEmailLogin = 0;
                }

                $analyticsSQL['UserID'] = $UserData['UserID'];
                $analyticsSQL['IPAddress'] = $IPAddress;
                $analyticsSQL['CityID'] = $Data['CityID'];
                $analyticsSQL['LoginSessionKey'] = $AdminLoginSessionKey;
                $analyticsSQL['LoginSourceID'] = $SourceID;
                $analyticsSQL['DeviceTypeID'] = $DeviceTypeID;
                $analyticsSQL['WeekdayID'] = $WeekdayID;
                $analyticsSQL['TimeSlotID'] = $TimeSlotID;
                $analyticsSQL['IsLoginSuccessfull'] = 1;
                $analyticsSQL['IsFirstVisit'] = $FirstVisit;
                $analyticsSQL['CreatedDate'] = date('Y-m-d H:i:s');
                $analyticsSQL['ModifiedDate'] = date('Y-m-d H:i:s');
                $analyticsSQL['IsEmail'] = $IsEmailLogin;

                $this->db->insert(ANALYTICLOGINS,$analyticsSQL);
                
                
                //For update login time
                $userLoginData = array();
                $userLoginData['LastLoginDate'] = date("Y-m-d H:i:s");
                $this->db->where('UserID',$UserData['UserID']);
                $this->db->update(USERS,$userLoginData);

                //Set Response data
                $Return['Data'] = array('LoginKeyword' => $UserData['LoginKeyword'], 'AdminLoginSessionKey' => $AdminLoginSessionKey, 'Latitude' => '', 'Longitude' => '', 'UserID' => $UserData['UserID'], 'UserGUID' => $UserData['UserGUID'], 'FirstName' => $UserData['FirstName'], 'LastName' => $UserData['LastName'], 'Email' => $UserData['Email'], 'ProfilePicture' => $UserData['ProfilePicture'], 'RoleID' => $UserData['RoleID'], "RedirectTo" => $Redirect);

                
            } elseif ($UserData['StatusID'] == 1) {
                $Return['ResponseCode'] = 501;
                $Return['Message'] = lang('account_not_activate');
            } elseif ($UserData['StatusID'] == 4) {
                $Return['ResponseCode'] = 508;
                $Return['Message'] = lang('user_blocked');
            } else {
                $Return['ResponseCode'] = 504;
                $Return['Message'] = lang('invalid_login_credentials');
            }
        } else {
            
            if ($SourceID == 1) {

                $Query = $this->db->select(USERS.'.UserID,'.USERS.'.UserGUID,'.USERLOGINS.'.LoginKeyword,'.USERS.'.FirstName,'.USERS.'.LastName,'.USERS.'.Email,'.USERS.'.ProfilePicture,'.USERS.'.StatusID,'.USERLOGINS.'.IsPasswordChange')
                                  ->from(USERS)
                                  ->join(USERLOGINS,USERLOGINS.'.UserID='.USERS.'.UserID','left')
                                  ->where('('.USERLOGINS.'.LoginKeyword="'.EscapeString($Username).'" OR '.USERS.'.Email="'.EscapeString($Username).'")',NULL,FALSE)
                                  ->get();
                if ($Query->num_rows() > 0) {
                    $UserData = $Query->row_array();

                    $FirstVisit = 1;
                    $AQuery = $this->db->select('UserID')->from(ANALYTICLOGINS)->where('UserID',$UserData['UserID'])->limit(1)->get();
                    if ($AQuery->num_rows() > 0) {
                        $FirstVisit = 0;
                    }
                    if ($SourceID == '1') {
                        $IsEmailLogin = 1;
                    } else {
                        $IsEmailLogin = 0;
                    }
                    $analyticsSQL['UserID'] = $UserData['UserID'];
                    $analyticsSQL['IPAddress'] = $IPAddress;
                    $analyticsSQL['CityID'] = $Data['CityID'];
                    $analyticsSQL['LoginSessionKey'] = $AdminLoginSessionKey;
                    $analyticsSQL['LoginSourceID'] = $SourceID;
                    $analyticsSQL['DeviceTypeID'] = $DeviceTypeID;
                    $analyticsSQL['WeekdayID'] = $WeekdayID;
                    $analyticsSQL['TimeSlotID'] = $TimeSlotID;
                    $analyticsSQL['IsLoginSuccessfull'] = 0;
                    $analyticsSQL['IsFirstVisit'] = $FirstVisit;
                    $analyticsSQL['CreatedDate'] = date('Y-m-d H:i:s');
                    $analyticsSQL['ModifiedDate'] = date('Y-m-d H:i:s');
                    $analyticsSQL['IsEmail'] = $IsEmailLogin;

                    $this->db->insert(ANALYTICLOGINS,$analyticsSQL);
                }
            }
            $Return['ResponseCode'] = 504;
            $Return['Message'] = lang('invalid_login_credentials');
            $errorLogin = 1;
        }

        if (isset($errorLogin)) {
            $checkError = $this->db->get_where(ANALYTICLOGINERRORS,array('DATE(`CreatedDate`)'=>date('Y-m-d'),'ClientErrorId'=>5));
            if ($checkError->num_rows()) {
                $this->db->set('ErrorCount','ErrorCount+1',FALSE)
                         ->where('DATE(`CreatedDate`)="'.date('Y-m-d').'"',NULL,FALSE)
                         ->where('ClientErrorId','5')
                         ->update(ANALYTICLOGINERRORS);
            } else {
                $this->db->insert(ANALYTICLOGINERRORS, array('ClientErrorId' => 5, 'CreatedDate' => date('Y-m-d'), 'ErrorCount' => 1));
            }
        }
        
        //For get loggedin user rights and set in session
        if(isset($Return['Data']['UserID']) && $Return['Data']['UserID'] != ""){
            $user_id = $Return['Data']['UserID'];
            $this->load->model(array('admin/roles_model'));
            if($only_right_id == 1) {
                $rightsArr = $this->roles_model->getUserRightsByUserId($user_id);
            } else {
                $rightsArr = $this->roles_model->get_user_rights($user_id);
            }
            
            $Return['UserRights'] = $rightsArr;
        }
        
        return $Return;
    }


    function isPasswordChanged($AdminLoginSessionKey) {
        $query = $this->db->select(USERLOGINS.'.IsPasswordChange as IsPasswordChange')
                 ->from(USERLOGINS)
                 ->join(ACTIVELOGINS,ACTIVELOGINS.'.UserID='.USERLOGINS.'.UserID','left')
                 ->where(ACTIVELOGINS.'.LoginSessionKey',$AdminLoginSessionKey)
                 ->get();
        if ($query->num_rows()) {
            return $query->row()->IsPasswordChange;
        } else {
            return 0;
        }
    }

    function checkRecoveryToken($Token){
        $query = $this->db->get_where(USERRESETPASSWORDS,array('IsPasswordReset'=>'0','UserGuid'=>$Token));
        if($query->num_rows()){
            return true;
        } else {
            return false;
        }
    }

    function active_login_auth($Input) {
        $sql = '';
        /* Define variables - starts */
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        /* Define variables - ends */

        /* Gather Inputs - starts */
        if (isset($Input['DeviceTypeID']))
            $DeviceTypeID = trim($Input['DeviceTypeID']);
        else
            $DeviceTypeID = '';
        if (isset($Input['AdminLoginSessionKey']))
            $AdminLoginSessionKey = trim($Input['AdminLoginSessionKey']);
        else
            $AdminLoginSessionKey = '';
        /* Gather Inputs - ends */

        /* Validation - starts */
        if ($this->form_validation->required($AdminLoginSessionKey) == '') {
            $Return['ResponseCode'] = 501;
            $Return['Message'] = lang('not_authorized');
        } else {
            $query = $this->db->select(USERS.'.UserID,'.USERS.'.UserTypeID,'.ACTIVELOGINS.'.DeviceTypeID')
                              ->from(USERS)
                              ->join(ACTIVELOGINS,ACTIVELOGINS.'.UserID='.USERS.'.UserID','inner')
                              ->where('LoginSessionKey',$AdminLoginSessionKey)
                              ->get();
            if ($query->num_rows() > 0) {
                $Return['Data'] = $query->row_array();
            } else {
                $Return['ResponseCode'] = 502;
                $Return['Message'] = lang('invalid_key');
            }
        }
        return $Return;
    }
    
    /**
     * Girish Patidar
     * For check and authenticate admin active login
     * @param array $Input
     * @return array
     */
    function activeAdminLoginAuth($Input) {
        $sql = '';
        /* Define variables - starts */
        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        $Return['Data'] = array();
        /* Define variables - ends */

        /* Gather Inputs - starts */
        if (isset($Input['DeviceTypeID']))
            $DeviceTypeID = trim($Input['DeviceTypeID']);
        else
            $DeviceTypeID = '1';
        if (isset($Input['AdminLoginSessionKey']))
            $AdminLoginSessionKey = trim($Input['AdminLoginSessionKey']);
        else
            $AdminLoginSessionKey = '';
        /* Gather Inputs - ends */

        /* Validation - starts */
        if ($this->form_validation->required($AdminLoginSessionKey) == '') {
            $Return['ResponseCode'] = 501;
            $Return['Message'] = lang('not_authorized');
        } else {
            $quz = isset($Input['quz']) ? $Input['quz'] : 0;
            $query = $this->db->select(USERS.'.UserID,'.USERS.'.UserTypeID,'.ACTIVELOGINS.'.DeviceTypeID,'.USERROLES.'.RoleID')
                              ->from(USERS)
                              ->join(ACTIVELOGINS,ACTIVELOGINS.'.UserID='.USERS.'.UserID','inner')
                              ->join(USERROLES,USERROLES.'.UserID='.USERS.'.UserID','inner')
                              ->where('LoginSessionKey',$AdminLoginSessionKey)
                              //->where(USERROLES.'.RoleID',ADMIN_ROLE_ID)
                              ->get();
            
            //Check logged in user access right and allow/denied access
            $result = $query->row_array();
            if($quz == 1) {
                $Return['Data'] = $result;
            } else if(!empty($result) && in_array(getRightsId('admin_site_view'), getUserRightsData($DeviceTypeID,$result['UserID']))){
                $Return['Data'] = $result;
            } else {
                $Return['ResponseCode'] = 502;
                $Return['Message'] = lang('invalid_key');
            }            
        }
        return $Return;
    }

    function setRememberMe($user_id) {
        $cookie = array(
            'name' => 'remember_me',
            'value' => $user_id,
            'expire' => '7776000'// 90 days expiration time
        );
        $this->input->set_cookie($cookie);
    }


    function GetPostID($PostGuID, $user_id = '', $StatusID = '') {
        $this->db->select('PostID');
        $this->db->from(POST);
        $this->db->where('PostGuID',$PostGuID);
        if ($user_id != '')
            $this->db->where('UserID',$user_id);
        if ($StatusID != '')
            $this->db->where('StatusID',$StatusID);
        $query = $this->db->get();
        if ($query->num_rows() == 0) {
            return FALSE;
        } else {
            $data = $query->row_array();
            return $data['PostID'];
        }
    }

    function GetProfilePic($user_id, $Resolution = '') {
        $query = $this->db->get_where(USERS,array('UserID'=>$user_id));
        if ($query->num_rows() != 0) {
            $row = $query->row_array();
            if ($Resolution == 'Small') {
                return $this->pathToFolder . PROFILE_IMAGE_SMALL . $row['ProfilePicture'];
            } elseif ($Resolution == 'Smedium') {
                return $this->pathToFolder . PROFILE_IMAGE_SMEDIUM . $row['ProfilePicture'];
            } elseif ($Resolution == 'Medium') {
                return $this->pathToFolder . PROFILE_IMAGE_MEDIUM . $row['ProfilePicture'];
            } elseif ($Resolution == 'High') {
                return $this->pathToFolder . PROFILE_IMAGE_MEDIUM . $row['ProfilePicture'];
            } else {
                return $this->pathToFolder . PROFILE_IMAGE_MEDIUM . $row['ProfilePicture'];
            }
        } else { /* Default Image */
            return $Path = base_url() . THEAM_PATH . 'img/avatar.png';
        }
    }

    function SaveLoginHistory($user_id, $AdminLoginSessionKey) {
        $count = 1;
        $insert = array();
        $insert['UserID'] = $user_id;
        $insert['LoginSessionKey'] = $AdminLoginSessionKey;
        $insert['StartDate'] = date("Y-m-d H:i:s");
        $this->db->insert(SESSIONLOGS, $insert);
        return $count;
    }

    function getLoginCount($user_id) {
        $countSql = $this->db->get_where(SESSIONLOGS, array('UserID' => $user_id));
        $count = $countSql->num_rows();
        return $count;
    }


    function _CheckEmailExist($Email) {
        $this->db->select('UserID, StatusID');
        $this->db->where('Email', EscapeString($Email));
        $Query = $this->db->get(USERS);
        if ($Query->num_rows() > 0) {
            $result = $Query->row_array();
            if ($result['StatusID'] == 3) {
                $return = 'deleted';
            } else {
                $return = 'exist';
            }
        } else {
            $return = 'notexist';
        }
        return $return;
    }

    function CheckUserLogin($UserName, $Email) {
        $result = $this->db->where('LoginKeyword',EscapeString($Email))
                           ->or_where('LoginKeyword',EscapeString($UserName))
                           ->get(USERLOGINS);
        if ($result->num_rows < 1) {
            return false;
        } else {
            return true;
        }
    }    


    function CheckPassword($user_id, $Password) {
        $Query = $this->db->select(USERS.'.UserID,'.USERS.'.FirstName,'.USERS.'.Email,'.USERLOGINS.'.Password')
                          ->from(USERS)
                          ->join(USERLOGINS,USERS.'.UserID='.USERLOGINS.'.UserID','left')
                          ->where(USERS.'.UserID',$user_id)
                          ->get();

        if ($Query->num_rows() == 1) {
            $UserData = $Query->row_array();
            $existing_password = $UserData['Password'];
            if (!password_verify($Password, $existing_password)) {
                return false;
            }
            return $UserData;  
                
        } else {
            return false;
        }
    }

    function UpdatePassword($UserData, $PasswordNew) {
        $this->db->query("UPDATE " . USERLOGINS . " SET Password='" . generate_password($PasswordNew) . "', ModifiedDate='" . date("Y-m-d H:i:s") . "', IsPasswordChange = '0' WHERE UserID='" . $UserData['UserID'] . "' and SourceID=1 ");
        $this->db->where('UserID',$UserData['UserID'])
                 ->where('SourceID','1')
                 ->update(USERLOGINS,array('Password'=>generate_password($PasswordNew),'ModifiedDate'=>date('Y-m-d H:i:s'),'IsPasswordChange'=>'0'));
        /* Send notification email to user for password change - starts */
        $Subject = SITE_NAME . " Password Assistance";
        $Template = THEAM_PATH . "email/emailer-change-password.html"; /* Custom email template */
        $values = array("##FIRST_LAST_NAME##" => $UserData['FirstName']);
        //sendMail(EMAIL_NOREPLY_FROM, EMAIL_NOREPLY_NAME, $Template, $values, $UserData['Email'], $Subject);
        /* Send notification email to user for password change - ends */
    }

    function SetPassword($user_id, $Password) {
        $this->db->query("UPDATE " . USERLOGINS . " SET Password='" . generate_password($Password) . "', ModifiedDate='" . date("Y-m-d H:i:s") . "', IsPasswordChange = '0',SetPassword='1' WHERE UserID='" . $user_id . "'");
        $this->db->where('UserID',$user_id)
                 ->update(USERLOGINS,array('Password'=>generate_password($Password),'ModifiedDate'=>date('Y-m-d H:i:s'),'IsPasswordChange'=>'0','SetPassword'=>'1'));
    }

    function confirmEmail($UserGUID) {
        $this->db->where('UserGUID', $UserGUID);
        $this->db->update(USERS, array('StatusID' => '2'));
    }

    function signout($AdminLoginSessionKey) {
        $this->db->where(array('LoginSessionKey' => $AdminLoginSessionKey));
        $this->db->limit(1);
        $this->db->delete(ACTIVELOGINS);
        $this->session->userdata('AdminLoginSessionKey', '');
        $this->session->sess_destroy();
    }

    function checkSocialUserExists($SocialID, $SocialTypeID) {
        $logged_user_data = $this->active_login_auth(array('AdminLoginSessionKey'=>$this->session->userdata('AdminLoginSessionKey')));
        $this->db->where('SourceID', $SocialTypeID);
        $this->db->where('LoginKeyword', $SocialID);
        $query = $this->db->get(USERLOGINS);
        if ($query->num_rows()) {
            return 1;
        } else {

            $this->db->where('UserSocialId',$SocialID);
            $this->db->where('InviteType',$SocialTypeID);
            $this->db->where('UserID',$logged_user_data['Data']['UserID']);
            $qnew = $this->db->get('FriendInvitation');
            if($qnew->num_rows()){
                return 2;
            } else {
                return false;
            }
        }
    }

    function getEmailFromToken($token) {
        $this->db->where('InviteType', 0);
        $this->db->where('Token', $token);
        $query = $this->db->get(INVITATION);
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->UserSocialId;
        } else {
            return '';
        }
    }

    function CheckEmailAdd($Email) {
        $Query = $this->db->select('UserID')
                          ->from(USERS)
                          ->where('Email',$Email)
                          ->limit(1)
                          ->get();
        if ($Query->num_rows() == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }


    function CheckPhoneVerified($MobileNumber) {
        $Query = $this->db->select('UserID')
                          ->from(USERS)
                          ->where('CONCAT(CountryCode,MobileNumber)="'.$MobileNumber.'"',NULL,FALSE)
                          ->where('MobileNumberStatus','5')
                          ->limit(1)
                          ->get();
        if ($Query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function getUserResetData($UserGUID){
        $Query = $this->db->get_where(USERRESETPASSWORDS,array('IsPasswordReset'=>'0','UserGUID'=>$UserGUID));
        if($Query->num_rows()){
            return $Query->row_array();
        } else {
            return false;
        }
                
    }

    function recoveryOneTimePasswordLink($Password,$UserResetdata,$UserGUID){
        /* Update directly user password here */
        $this->db->where('UserID',$UserResetdata['UserID'])
                 ->update(USERLOGINS,array('Password'=>generate_password($Password),'IsPasswordChange'=>'0'));

        /* Now Update UserResetPassword Table -- to change field IsPasswordReset to 1, DO NOT DELETE*/
        $this->db->where('UserID',$UserResetdata['UserID']);
        $this->db->where('UserGUID',$UserGUID);
        $this->db->update(USERRESETPASSWORDS, array('IsPasswordReset' => '1'));

        /* Change User Status if Status is 1 */
        $this->db->where('UserID',$UserResetdata['UserID']);
        $this->db->where('StatusID','1');
        $this->db->update(USERS, array('StatusID' => '2'));
        
        /* Now Get User information for send email */
        $Query = $this->db->select(USERS.'.Email,'.USERS.'.FirstName,'.USERS.'.LastName')
                 ->from(USERS)
                 ->where('UserID',$UserResetdata['UserID'])
                 ->limit(1)
                 ->get();
        $Userdata = $Query->row_array();
        
        /* Send New Password Email - Starts */
        $Subject = SITE_NAME . lang('set_new_password_assis');
        $Template = THEAM_PATH . "email/set-new-password.html"; /* Custom email template */
        $values = array("##FIRST_LAST_NAME##" => $Userdata['FirstName'] . ' ' . $Userdata['LastName'], '##PASSWORD##' => $Password);
        //sendMail(EMAIL_NOREPLY_FROM, EMAIL_NOREPLY_NAME, $Template, $values, $Userdata['Email'], $Subject);
        $Return['Data'] = array('NewPassword' => $Password);
        /* Send Password Recovery Email - Ends */
    }

    /*
    |--------------------------------------------------------------------------
    | Use get DeviceTypeID
    |@Inputs: (Defined in devicetypes DB Table)
    |--------------------------------------------------------------------------
    */  
    function GetDeviceTypeID($DeviceType)
    {
        $DeviceTypeID='';
        $this->db->select('DeviceTypeID');
        $this->db->from(DEVICETYPES);
        $this->db->where('Name',$DeviceType);
        $this->db->limit(1);

        $Query = $this->db->get();
        
        if($Query->num_rows()>0)
        {
            $Data=$Query->row_array();
            $DeviceTypeID=$Data['DeviceTypeID'];
        }
            return $DeviceTypeID;
    }

    
    /*
    |--------------------------------------------------------------------------
    | Use get SourceID
    |@Inputs: (Defined in sources DB Table)
    |--------------------------------------------------------------------------
    */  
    function GetSourceID($SocialType)
    {
        $SourceID='';
        $this->db->select('SourceID');
        $this->db->from(SOURCES);
        $this->db->where('Name',$SocialType);
        $this->db->limit(1);
       
        $Query = $this->db->get();
        
        if($Query->num_rows()>0)
        {
            $Data=$Query->row_array();
            $SourceID=$Data['SourceID'];
        }
            return $SourceID;
    }


    /*
    |--------------------------------------------------------------------------
    | Use get Resolution
    |@Inputs: (Defined in resolution DB Table)
    |--------------------------------------------------------------------------
    */      
    function GetResolutionID($Resolution)
    {
        $ResolutionID='';
        $this->db->select('ResolutionID');
        $this->db->from(RESOLUTION);
        $this->db->where('Name',$Resolution);
        $this->db->limit(1);
       
        $Query = $this->db->get();
        if($Query->num_rows()>0)
        {
            $Data=$Query->row_array(); 
            $ResolutionID=$Data['ResolutionID']; 
        }
            return $ResolutionID;
    }


    public function getLoginDashboardAnalytics($FromDate,$ToDate,$Filter='Custom',$type=''){
        $data = array('post'=>array(),'page'=>array(),'group'=>array(),'event'=>array(),'user'=>array(),'media'=>array(),'engagement_score'=>array(),'top_countries'=>array(),'user_engagement'=>array());
        
        if($Filter == 'Custom'){
            $diff=dateDiff($FromDate,$ToDate);  
            $PreviousFromDate = date('Y-m-d',strtotime($FromDate."-".$diff." days"));
            $PreviousToDate = date('Y-m-d',strtotime($FromDate."-1 days"));
        }


        //time_calculate('start'); 
        //Get Activity
        $this->db->select("(SELECT COUNT(ActivityID) FROM ".ACTIVITY." WHERE CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as total_post",false);
        $this->db->select("(SELECT COUNT(ActivityID) FROM ".ACTIVITY." WHERE CreatedDate BETWEEN '".$PreviousFromDate."' AND '".$PreviousToDate."') as previous_total_post",false);
        //$this->db->select("DATE_FORMAT(DATE(CreatedDate),'%d %b, %y') as date,COUNT(ActivityID) as total",false);
        $this->db->from(ACTIVITY);
        $this->db->where("CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $query = $this->db->get();  //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $row = $query->row(); 
            $d['TotalPost'] = $row->total_post;
            $d['PreviousTotalPost'] = $row->previous_total_post;
        } else {
            $d['TotalPost'] = 0;
            $d['PreviousTotalPost'] = 0;
        }  
        
        //time_calculate('start'); 
        $this->db->select("'".$d['TotalPost']."' as total_post,'".$d['PreviousTotalPost']."' as previous_total_post",false);
        $this->db->select("DATE_FORMAT(DD.full_date,'%d %b, %y') as date,COUNT(A.ActivityID) as total",false);
        $this->db->from(DIMDATE.' DD');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(ACTIVITY.' A','A.CreatedDate=DD.full_date','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where("DD.full_date BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->group_by('DD.full_date');
        $this->db->order_by('DD.full_date','ASC');
        $q = $this->db->get();  //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($q->num_rows()){    
            $data['post'] = $q->result();
        }


        //time_calculate('start'); 
        //Get Pages
        $this->db->select("(SELECT COUNT(PageID) FROM ".PAGES." WHERE DATE(CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."') as total_pages",false);
        $this->db->select("(SELECT COUNT(PageID) FROM ".PAGES." WHERE DATE(CreatedDate) BETWEEN '".$PreviousFromDate."' AND '".$PreviousToDate."') as previous_total_pages",false);
        //$this->db->select("DATE_FORMAT(DATE(CreatedDate),'%d %b, %y') as date,COUNT(PageID) as total",false);
        $this->db->from(PAGES);
        $this->db->where("DATE(CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $query = $this->db->get();   //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $row = $query->row();
            $d['TotalPage'] = $row->total_pages;
            $d['PreviousTotalPage'] = $row->previous_total_pages;
        } else {
            $d['TotalPage'] = 0;
            $d['PreviousTotalPage'] = 0;
        }
        
        //time_calculate('start'); 
        $this->db->select("'".$d['TotalPage']."' as total_pages,'".$d['PreviousTotalPage']."' as previous_total_pages",false);
        $this->db->select("DATE_FORMAT(DD.full_date,'%d %b, %y') as date,COUNT(A.PageID) as total",false);
        $this->db->from(DIMDATE.' DD');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PAGES.' A','A.CreatedDate=DD.full_date','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where("DD.full_date BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->group_by('DD.full_date');
        $this->db->order_by('DD.full_date','ASC');
        $q = $this->db->get();  //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($q->num_rows()){    
            $data['page'] = $q->result();
        }

        //time_calculate('start'); 
        //Get Groups
        $this->db->select("(SELECT COUNT(GroupID) FROM ".GROUPS." WHERE DATE(CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."') as total_groups",false);
        $this->db->select("(SELECT COUNT(GroupID) FROM ".GROUPS." WHERE DATE(CreatedDate) BETWEEN '".$PreviousFromDate."' AND '".$PreviousToDate."') as previous_total_groups",false);
        //$this->db->select("DATE_FORMAT(DATE(CreatedDate),'%d %b, %y') as date,COUNT(GroupID) as total",false);
        $this->db->from(GROUPS);
        $this->db->where("CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $query = $this->db->get();  //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $row = $query->row();
            $d['TotalGroup'] = $row->total_groups;
            $d['PreviousTotalGroup'] = $row->previous_total_groups;
        } else {
            $d['TotalGroup'] = 0;
            $d['PreviousTotalGroup'] = 0;
        }
        
        //time_calculate('start'); 
        $this->db->select("'".$d['TotalGroup']."' as total_groups,'".$d['PreviousTotalGroup']."' as previous_total_groups",false);
        $this->db->select("DATE_FORMAT(DD.full_date,'%d %b, %y') as date,COUNT(A.GroupID) as total",false);
        $this->db->from(DIMDATE.' DD');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(GROUPS.' A','A.CreatedDate=DD.full_date','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where("DD.full_date BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->group_by('DD.full_date');
        $this->db->order_by('DD.full_date','ASC');
        $q = $this->db->get();  //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($q->num_rows()){    
            $data['group'] = $q->result();
        }

       //time_calculate('start'); 
        //Get Events
        $this->db->select("(SELECT COUNT(EventID) FROM ".EVENTS." WHERE DATE(CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."') as total_events",false);
        $this->db->select("(SELECT COUNT(EventID) FROM ".EVENTS." WHERE DATE(CreatedDate) BETWEEN '".$PreviousFromDate."' AND '".$PreviousToDate."') as previous_total_events",false);
        //$this->db->select("DATE_FORMAT(DATE(CreatedDate),'%d %b, %y') as date,COUNT(EventID) as total",false);
        $this->db->from(EVENTS);
        $this->db->where("CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $query = $this->db->get();  //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $row = $query->row();
            $d['TotalEvent'] = $row->total_events;
            $d['PreviousTotalEvent'] = $row->previous_total_events;
        } else {
            $d['TotalEvent'] = 0;
            $d['PreviousTotalEvent'] = 0;
        }
        //time_calculate('start'); 
        $this->db->select("'".$d['TotalEvent']."' as total_events,'".$d['PreviousTotalEvent']."' as previous_total_events",false);
        $this->db->select("DATE_FORMAT(DD.full_date,'%d %b, %y') as date,COUNT(A.EventID) as total",false);
        $this->db->from(DIMDATE.' DD');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(EVENTS.' A','A.CreatedDate=DD.full_date','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where("DD.full_date BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->group_by('DD.full_date');
        $this->db->order_by('DD.full_date','ASC');
        $q = $this->db->get();   //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($q->num_rows()){    
            $data['event'] = $q->result();
        }

        //time_calculate('start'); 
        //Get Users
        $this->db->select("(SELECT COUNT(UserID) FROM ".USERS." WHERE CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as total_users",false);
        $this->db->select("(SELECT COUNT(UserID) FROM ".USERS." WHERE CreatedDate BETWEEN '".$PreviousFromDate."' AND '".$PreviousToDate."') as previous_total_users",false);
        //$this->db->select("DATE_FORMAT(DATE(CreatedDate),'%d %b, %y') as date,COUNT(UserID) as total",false);
        $this->db->from(USERS);
        $this->db->where("CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $query = $this->db->get();   //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $row = $query->row();
            $d['TotalUser'] = $row->total_users;
            $d['PreviousTotalUser'] = $row->previous_total_users;
        } else {
            $d['TotalUser'] = 0;
            $d['PreviousTotalUser'] = 0;
        }
        
        //time_calculate('start'); 
        $this->db->select("'".$d['TotalUser']."' as total_users,'".$d['PreviousTotalUser']."' as previous_total_users",false);
        $this->db->select("DATE_FORMAT(DD.full_date,'%d %b, %y') as date,COUNT(A.UserID) as total",false);
        $this->db->from(DIMDATE.' DD');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(USERS.' A','A.CreatedDate=DD.full_date','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where("DD.full_date BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->group_by('DD.full_date');
        $this->db->order_by('DD.full_date','ASC');
        $q = $this->db->get();   //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($q->num_rows()){    
            $data['user'] = $q->result();
        }

        //time_calculate('start'); 
        //Get Media
        $this->db->select("(SELECT COUNT(MediaID) FROM ".MEDIA." WHERE StatusID IN('2','3') AND CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as total_media",false);
        $this->db->select("(SELECT COUNT(MediaID) FROM ".MEDIA." WHERE StatusID IN('2','3') AND CreatedDate BETWEEN '".$PreviousFromDate."' AND '".$PreviousToDate."') as previous_total_media",false);
       // $this->db->select("DATE_FORMAT(DATE(CreatedDate),'%d %b, %y') as date,COUNT(MediaID) as total",false);
        $this->db->from(MEDIA);
        $this->db->where("CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $query = $this->db->get();  //echo '<br/>'; echo time_calculate('end'); echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $row = $query->row();
            $d['TotalMedia'] = $row->total_media;
            $d['PreviousTotalMedia'] = $row->previous_total_media;
        } else {
            $d['TotalMedia'] = 0;
            $d['PreviousTotalMedia'] = 0;
        }
        
        //time_calculate('start'); 
        $this->db->select("'".$d['TotalMedia']."' as total_media,'".$d['PreviousTotalMedia']."' as previous_total_media",false);
        $this->db->select("DATE_FORMAT(DD.full_date,'%d %b, %y') as date,COUNT(A.MediaID) as total",false);
        $this->db->from(DIMDATE.' DD');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(MEDIA.' A','A.CreatedDate=DD.full_date','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where("DD.full_date BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->group_by('DD.full_date');
        $this->db->order_by('DD.full_date','ASC');
        $q = $this->db->get();   //echo '<br/>'; echo time_calculate('end');  echo $this->db->last_query(); echo '===========================================';
        if($q->num_rows()){    
            $data['media'] = $q->result();
        }

        //time_calculate('start'); 
        //Get Engagement Score
        $this->db->select("(SELECT IFNULL(SUM(Points),0) FROM ".ENGAGEMENT." WHERE CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as total_engagement",false);
        $this->db->select("(SELECT IFNULL(SUM(Points),0) FROM ".ENGAGEMENT." WHERE CreatedDate BETWEEN '".$PreviousFromDate."' AND '".$PreviousToDate."') as previous_total_engagement",false);
        //$this->db->select("DATE_FORMAT(DATE(CreatedDate),'%d %b, %y') as date,IFNULL(SUM(Points),0) as total",false);
        $this->db->from(ENGAGEMENT);
        $this->db->where("CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $query = $this->db->get();   //echo '<br/>'; echo time_calculate('end');  echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $row = $query->row();
            $d['TotalEng'] = $row->total_engagement;
            $d['PreviousTotalEng'] = $row->previous_total_engagement;
        } else {
            $d['TotalEng'] = 0;
            $d['PreviousTotalEng'] = 0;
        }
        //time_calculate('start'); 
        $this->db->select("'".$d['TotalEng']."' as total_engagement,'".$d['PreviousTotalEng']."' as previous_total_engagement",false);
        $this->db->select("DATE_FORMAT(DD.full_date,'%d %b, %y') as date,IFNULL(SUM(A.Points),0) as total",false);
        $this->db->from(DIMDATE.' DD');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(ENGAGEMENT.' A','A.CreatedDate=DD.full_date','left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->where("DD.full_date BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->group_by('DD.full_date');
        $this->db->order_by('DD.full_date','ASC');
        $q = $this->db->get();  //echo '<br/>'; echo time_calculate('end');  echo $this->db->last_query(); echo '===========================================';
        if($q->num_rows()){    
            $data['engagement_score'] = $q->result();
        }   
    
    // Experiment Starts
       /* $d1 = array();
        $d2 = array();
        $d3 = array();
        $d4 = array();
        $d5 = array();
        $d6 = array();
        $d7 = array();
        $date = $FromDate;
        if($data['post']){
            $TotalR1 = $data['post'][0]->total_post;
            $TotalPR1 = $data['post'][0]->previous_total_post;
        } else {
            $TotalR1 = 0;
            $TotalPR1 = 0;
        }
        if($data['page']){
            $TotalR2 = $data['page'][0]->total_pages;
            $TotalPR2 = $data['page'][0]->previous_total_pages;
        } else {
            $TotalR2 = 0;
            $TotalPR2 = 0;
        }
        if($data['group']){
            $TotalR3 = $data['group'][0]->total_groups;
            $TotalPR3 = $data['group'][0]->previous_total_groups;
        } else {
            $TotalR3 = 0;
            $TotalPR3 = 0;
        }
        if($data['event']){
            $TotalR4 = $data['event'][0]->total_events;
            $TotalPR4 = $data['event'][0]->previous_total_events;
        } else {
            $TotalR4 = 0;
            $TotalPR4 = 0;
        }
        if($data['user']){
            $TotalR5 = $data['user'][0]->total_users;
            $TotalPR5 = $data['user'][0]->previous_total_users;
        } else {
            $TotalR5 = 0;
            $TotalPR5 = 0;
        }
        if($data['media']){
            $TotalR6 = $data['media'][0]->total_media;
            $TotalPR6 = $data['media'][0]->previous_total_media;
        } else {
            $TotalR6 = 0;
            $TotalPR6 = 0;
        }
        if($data['engagement_score']){
            $TotalR7 = $data['engagement_score'][0]->total_engagement;
            $TotalPR7 = $data['engagement_score'][0]->previous_total_engagement;
        } else {
            $TotalR7 = 0;
            $TotalPR7 = 0;
        }
        while (strtotime($date) <= strtotime($ToDate)) {
            $hasDate = false;
            if($data['post']){
                foreach ($data['post'] as $key => $value) {
                    if(date('d M, y',strtotime($date)) == $value->date){
                        $hasDate = $key;
                    }
                }
            }
            if($hasDate){
                $d1[] = $data['post'][$key];
            } else {
                $d1[] = array('total_post'=>$TotalR1,'previous_total_post'=>$TotalPR1,'total'=>'0','date'=>date('d M, y',strtotime($date)));
            }
            $hasDate = false;
            if($data['page']){
                foreach ($data['page'] as $key => $value) {
                    if(date('d M, y',strtotime($date)) == $value->date){
                        $hasDate = $key;
                    }
                }
            }
            if($hasDate){
                $d2[] = $data['page'][$key];
            } else {
                $d2[] = array('total_pages'=>$TotalR2,'previous_total_pages'=>$TotalPR2,'total'=>'0','date'=>date('d M, y',strtotime($date)));
            }
            $hasDate = false;
            if($data['group']){
                foreach ($data['group'] as $key => $value) {
                    if(date('d M, y',strtotime($date)) == $value->date){
                        $hasDate = $key;
                    }
                }
            }
            if($hasDate){
                $d3[] = $data['group'][$key];
            } else {
                $d3[] = array('total_groups'=>$TotalR3,'previous_total_groups'=>$TotalPR3,'total'=>'0','date'=>date('d M, y',strtotime($date)));
            }
            $hasDate = false;
            if($data['event']){
                foreach ($data['event'] as $key => $value) {
                    if(date('d M, y',strtotime($date)) == $value->date){
                        $hasDate = $key;
                    }
                }
            }
            if($hasDate){
                $d4[] = $data['event'][$key];
            } else {
                $d4[] = array('total_events'=>$TotalR4,'previous_total_events'=>$TotalPR4,'total'=>'0','date'=>date('d M, y',strtotime($date)));
            }
            $hasDate = false;
            if($data['user']){
                foreach ($data['user'] as $key => $value) {
                    if(date('d M, y',strtotime($date)) == $value->date){
                        $hasDate = $key;
                    }
                }
            }
            if($hasDate){
                $d5[] = $data['user'][$key];
            } else {
                $d5[] = array('total_users'=>$TotalR5,'previous_total_users'=>$TotalPR5,'total'=>'0','date'=>date('d M, y',strtotime($date)));
            }
            $hasDate = false;
            if($data['media']){
                foreach ($data['media'] as $key => $value) {
                    if(date('d M, y',strtotime($date)) == $value->date){
                        $hasDate = $key;
                    }
                }
            }
            if($hasDate){
                $d6[] = $data['media'][$key];
            } else {
                $d6[] = array('total_media'=>$TotalR6,'previous_total_media'=>$TotalPR6,'total'=>'0','date'=>date('d M, y',strtotime($date)));
            }
            $hasDate = false;
            if($data['engagement_score']){
                foreach ($data['engagement_score'] as $key => $value) {
                    if(date('d M, y',strtotime($date)) == $value->date){
                        $hasDate = $key;
                    }
                }
            }
            if($hasDate){
                $d7[] = $data['engagement_score'][$key];
            } else {
                $d7[] = array('total_engagement'=>$TotalR7,'previous_total_engagement'=>$TotalPR7,'total'=>'0','date'=>date('d M, y',strtotime($date)));
            }
            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
        $data['post']               = $d1;
        $data['page']               = $d2;
        $data['group']              = $d3;
        $data['event']              = $d4;
        $data['user']               = $d5;
        $data['media']              = $d6;
        $data['engagement_score']   = $d7;*/
    // Experiment Ends
        
        
        //time_calculate('start'); 
        //Get Top Countries
        $this->db->select('CM.CountryCode,CM.CountryName');
        $this->db->select('COUNT(E.EngagementID) as Logins',false);
        $this->db->select("(SELECT COUNT(EngagementID) FROM ".ENGAGEMENT." WHERE DATE(CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."' AND EntityType='Login') as TotalLogins",false);
        $this->db->from(COUNTRYMASTER.' CM');
        $this->db->join(USERDETAILS.' UD','UD.CountryID=CM.CountryID','left');
        $this->db->join(ENGAGEMENT.' E','E.UserID=UD.UserID','left');
        $this->db->where("E.CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        $this->db->where('E.EntityType','Login');
        $this->db->group_by('CM.CountryID');
        $this->db->order_by('Logins','DESC');
        $this->db->limit(5);
        $query = $this->db->get();   //echo '<br/>'; echo time_calculate('end');  echo $this->db->last_query(); echo '===========================================';
        if($query->num_rows()){
            $data['top_countries'] = $query->result();
        }
        
        
        
        //time_calculate('start');
        //Get User Engagement
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL WHERE CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as TotalUsers",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL) as TotalUsersCount",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL,".ENGAGEMENT." E WHERE AL.UserID=E.UserID and E.EntityType='Login' AND E.CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as ActiveUsers",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL LEFT JOIN ".ENGAGEMENT." E ON AL.UserID=E.UserID WHERE E.EntityType!='Login' AND E.CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as EngageUsers",false);
        $query = $this->db->get();   //echo '<br/>'; echo time_calculate('end');  echo $this->db->last_query(); echo '===========================================';
        //echo $this->db->last_query();
        if($query->num_rows()){
            $data['user_engagement'] = $query->result();
        }
        
        
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL WHERE CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as TotalUsers",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL) as TotalUsersCount",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL,".ENGAGEMENT." E WHERE AL.UserID=E.UserID and E.EntityType='Login' AND E.CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as ActiveUsers",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL LEFT JOIN ".ENGAGEMENT." E ON AL.UserID=E.UserID WHERE E.EntityType!='Login' AND E.CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as EngageUsers",false);
        $query = $this->db->get();   //echo '<br/>'; echo time_calculate('end');  echo $this->db->last_query(); echo '===========================================';
        //echo $this->db->last_query();
        if($query->num_rows()){
            $data['user_engagement'] = $query->result();
        }

        if($type){
            return $data[$type];
        }
        return $data;
        
        
        
        
        
        
    }
    
    public function getLoginDashboardAnalyticsExtra($FromDate,$ToDate,$Filter='Custom',$type='') {
        $data = [];
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL WHERE CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as TotalUsers",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL) as TotalUsersCount",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL,".ENGAGEMENT." E WHERE AL.UserID=E.UserID and E.EntityType='Login' AND E.CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as ActiveUsers",false);
        $this->db->select("(SELECT COUNT(DISTINCT AL.UserID) FROM ".ACTIVELOGINS." AL LEFT JOIN ".ENGAGEMENT." E ON AL.UserID=E.UserID WHERE E.EntityType!='Login' AND E.CreatedDate BETWEEN '".$FromDate."' AND '".$ToDate."') as EngageUsers",false);
        $query = $this->db->get();   //echo '<br/>'; echo time_calculate('end');  echo $this->db->last_query(); echo '===========================================';
        //echo $this->db->last_query();
        if($query->num_rows()){
            $data['user_engagement'] = $query->result();
        }
        
        
        return $data;
    }
    
    

    public function getUsageData($FromDate,$ToDate,$Filter='Custom',$type='',$UserID=''){
        $data = array('Desktop'=>array(),'Tablet'=>array(),'Mobile'=>array());
        
        $user_condition = '';
        if($UserID) {
            $user_condition = " AND SAL.UserID = $UserID ";
        }
        
        
        if($FromDate && $ToDate)
        {
            $this->db->select('COUNT(AL.UserID) as Count,(SELECT COUNT(SAL.UserID) FROM '.ANALYTICLOGINS.' SAL LEFT JOIN '.BROWSERS.' SB ON SB.BrowserID=SAL.BrowserID WHERE SAL.DeviceTypeID=1 '.$user_condition.' AND  IF(SB.Name is NULL,"",true) AND DATE(SAL.CreatedDate) BETWEEN "'.$FromDate.'" AND "'.$ToDate.'") as TotalCount',false);
        }
        else
        {
            $this->db->select('COUNT(AL.UserID) as Count,(SELECT COUNT(SAL.UserID) FROM '.ANALYTICLOGINS.' SAL LEFT JOIN '.BROWSERS.' SB ON SB.BrowserID=SAL.BrowserID WHERE SAL.DeviceTypeID=1 '.$user_condition.' AND IF(SB.Name is NULL,"",true)) as TotalCount',false);
        }
        $this->db->select('B.Name as BrowserName');
        $this->db->from(ANALYTICLOGINS.' AL');
        $this->db->join(BROWSERS.' B','B.BrowserID=AL.BrowserID','left');
        $this->db->where('IF(B.Name is NULL,"",true)',NULL,false);
        $this->db->where('AL.DeviceTypeID','1');
        if($FromDate && $ToDate)
        {
            $this->db->where("DATE(AL.CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        }
        if($UserID)
        {
            $this->db->where('AL.UserID',$UserID);
        }
        $this->db->group_by('AL.BrowserID');
        $query = $this->db->get();
        $array = array('Firefox','Safari','Chrome','Internet Explorer');
        if($query->num_rows()){
            $TotalCount = 0;
            $TotalCountYet = 0;
            foreach($query->result() as $result){
                $TotalCount = $result->TotalCount;
                if(in_array($result->BrowserName,$array)){
                    $data['Desktop'][] = $result;
                    unset($array[array_search($result->BrowserName,$array)]);
                    $TotalCountYet = $TotalCountYet+$result->Count;
                }
            }
            if($array){
                foreach($array as $arr){
                    $data['Desktop'][] = array('Count'=>0,'TotalCount'=>$TotalCount,'BrowserName'=>$arr);
                }
            }

            $data['Desktop'][] = array('Count'=>($TotalCount-$TotalCountYet),'TotalCount'=>$TotalCount,'BrowserName'=>'Other');
        } else {
            if($array){
                foreach($array as $arr){
                    $data['Desktop'][] = array('Count'=>0,'TotalCount'=>0,'BrowserName'=>$arr);
                }
            }

            $data['Desktop'][] = array('Count'=>0,'TotalCount'=>0,'BrowserName'=>'Other');
        }
        
        
        
        $user_condition = '';
        if($UserID) {
            $user_condition = " AND UserID = $UserID ";
        }
        if($FromDate && $ToDate)
        {
            $this->db->select('COUNT(AL.UserID) as Count,(SELECT COUNT(UserID) FROM '.ANALYTICLOGINS.' WHERE DeviceTypeID IN (4,5,8) '.$user_condition.' AND DATE(CreatedDate) BETWEEN "'.$FromDate.'" AND "'.$ToDate.'") as TotalCount',false);
        }
        else
        {
            $this->db->select('COUNT(AL.UserID) as Count,(SELECT COUNT(UserID) FROM '.ANALYTICLOGINS.' WHERE DeviceTypeID IN (4,5,8) '.$user_condition.'  ) as TotalCount',false);
        }
        $this->db->select('DT.Name as BrowserName');
        $this->db->from(ANALYTICLOGINS.' AL');
        $this->db->join(DEVICETYPES.' DT','DT.DeviceTypeID=AL.DeviceTypeID','left');
        if($FromDate && $ToDate)
        {
            $this->db->where("DATE(AL.CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        }
        if($UserID)
        {
            $this->db->where('AL.UserID',$UserID);
        }
        $this->db->where_in('AL.DeviceTypeID',array(4,5,8));
        $this->db->group_by('AL.DeviceTypeID');
        $query = $this->db->get();
        $array = array('Ipad','AndroidTablet','WindowsTablet');
        if($query->num_rows()){
            $TotalCount = 0;
            $TotalCountYet = 0;
            foreach($query->result() as $result){
                //$data['Tablet'][] = $result;
                //unset($array[array_search($result->BrowserName,$array)]);
                $TotalCount = $result->TotalCount;
                if(in_array($result->BrowserName,$array)){
                    $data['Tablet'][] = $result;
                    unset($array[array_search($result->BrowserName,$array)]);
                    $TotalCountYet = $TotalCountYet+$result->Count;
                }
            }
            if($array){
                foreach($array as $arr){
                    $data['Tablet'][] = array('Count'=>0,'TotalCount'=>$TotalCount,'BrowserName'=>$arr);
                }
            }

            $data['Tablet'][] = array('Count'=>($TotalCount-$TotalCountYet),'TotalCount'=>$TotalCount,'BrowserName'=>'Other');
        } else {
            if($array){
                foreach($array as $arr){
                    $data['Tablet'][] = array('Count'=>0,'TotalCount'=>0,'BrowserName'=>$arr);
                }
            }

            $data['Tablet'][] = array('Count'=>0,'TotalCount'=>0,'BrowserName'=>'Other');
        }
        
        
        $user_condition = '';
        if($UserID) {
            $user_condition = " AND UserID = $UserID ";
        }
        if($FromDate && $ToDate)
        {
            $this->db->select('COUNT(AL.UserID) as Count,(SELECT COUNT(UserID) FROM '.ANALYTICLOGINS.' WHERE DeviceTypeID IN (2,3,7) '.$user_condition.' AND DATE(CreatedDate) BETWEEN "'.$FromDate.'" AND "'.$ToDate.'") as TotalCount',false);
        }
        else
        {
            $this->db->select('COUNT(AL.UserID) as Count,(SELECT COUNT(UserID) FROM '.ANALYTICLOGINS.' WHERE DeviceTypeID IN (2,3,7) '.$user_condition.' ) as TotalCount',false);
        }
        $this->db->select('DT.Name as BrowserName');
        $this->db->from(ANALYTICLOGINS.' AL');
        $this->db->join(DEVICETYPES.' DT','DT.DeviceTypeID=AL.DeviceTypeID','left');
        if($FromDate && $ToDate)
        {
            $this->db->where("DATE(AL.CreatedDate) BETWEEN '".$FromDate."' AND '".$ToDate."'",NULL,FALSE);
        }
        if($UserID)
        {
            $this->db->where('AL.UserID',$UserID);
        }
        $this->db->where_in('AL.DeviceTypeID',array(2,3,7));
        $this->db->group_by('AL.DeviceTypeID');
        $query = $this->db->get();
        //echo $this->db->last_query();
        $array = array('IPhone','AndroidPhone','WindowsPhone');
        if($query->num_rows()){
            $TotalCount = 0;
            $TotalCountYet = 0;
            foreach($query->result() as $result){
                //$data['Mobile'][] = $result;
                //unset($array[array_search($result->BrowserName,$array)]);
                $TotalCount = $result->TotalCount;
                if(in_array($result->BrowserName,$array)){
                    $data['Mobile'][] = $result;
                    unset($array[array_search($result->BrowserName,$array)]);
                    $TotalCountYet = $TotalCountYet+$result->Count;
                }
            }
            if($array){
                foreach($array as $arr){
                    $data['Mobile'][] = array('Count'=>0,'TotalCount'=>$TotalCount,'BrowserName'=>$arr);
                }
            }

            $data['Mobile'][] = array('Count'=>($TotalCount-$TotalCountYet),'TotalCount'=>$TotalCount,'BrowserName'=>'Other');
        } else {
            if($array){
                foreach($array as $arr){
                    $data['Mobile'][] = array('Count'=>0,'TotalCount'=>0,'BrowserName'=>$arr);
                }
            }

            $data['Mobile'][] = array('Count'=>0,'TotalCount'=>0,'BrowserName'=>'Other');
        }

        return $data;

    }

    public function cronEngagementScore(){
        $query = $this->db->get(CRONUPDATE);  
        if($query->num_rows()){
            foreach($query->result() as $result){
                $Points = 0;
                switch ($result->EngagementName) {
                    case 'Login':
                        $this->db->group_by('UserID');
                        $Table = ACTIVELOGINS;
                        $Points = 2;
                        $user_id = 'UserID';
                        $entity_id = 'ActiveLoginID';
                        $entity_type = 'Login';
                        $CreatedDate = 'CreatedDate';
                    break;
                    case 'Post':
                        $this->db->where_in('ActivityTypeID',array(1,7,8,11,12));
                        $Table = ACTIVITY;
                        $Points = 10;
                        $user_id = 'UserID';
                        $entity_id = 'ActivityID';
                        $entity_type = 'Post';
                        $CreatedDate = 'CreatedDate';
                    break;
                    case 'Comment':
                        $Table = POSTCOMMENTS;
                        $Points = 5;
                        $user_id = 'UserID';
                        $entity_id = 'PostCommentID';
                        $entity_type = 'Comment';
                        $CreatedDate = 'CreatedDate';
                        $this->db->group_by('EntityType');
                        $this->db->group_by('EntityID');
                    break;
                    case 'Like':
                        $Table = POSTLIKE;
                        $Points = 1;
                        $user_id = 'UserID';
                        $entity_id = 'PostLikeID';
                        $entity_type = 'Like';
                        $CreatedDate = 'CreatedDate';
                    break;
                    case 'Share':
                        $Table = ACTIVITY;
                        $Points = 10;
                        $user_id = 'UserID';
                        $entity_id = 'ActivityID';
                        $entity_type = 'Share';
                        $CreatedDate = 'CreatedDate';
                        $this->db->where_in('ActivityTypeID',array(9,10));
                    break;
                    case 'Group Created':
                        $Table = GROUPS;
                        $Points = 15;
                        $user_id = 'CreatedBy';
                        $entity_id = 'GroupID';
                        $entity_type = 'Group Created';
                        $CreatedDate = 'CreatedDate';
                    break;
                    case 'Page Created':
                        $Table = PAGES;
                        $Points = 15;
                        $user_id = 'UserID';
                        $entity_id = 'PageID';
                        $entity_type = 'Page Created';
                        $CreatedDate = 'CreatedDate';
                    break;
                    case 'Event Created':
                        $Table = EVENTS;
                        $Points = 15;
                        $user_id = 'CreatedBy';
                        $entity_id = 'EventID';
                        $entity_type = 'Event Created';
                        $CreatedDate = 'CreatedDate';
                    break;
                    case 'FRA':
                        $Table = FRIENDS;
                        $Points = 2;
                        $user_id = 'UserID';
                        $entity_id = 'FriendID';
                        $entity_type = 'FRA';
                        $CreatedDate = 'CreatedDate';
                        $this->db->where('Status','1');
                    break;
                    case 'GRA':
                        $Table = GROUPMEMBERS;
                        $Points = 2;
                        $user_id = 'ModuleEntityID';
                        $entity_id = 'GroupID';
                        $entity_type = 'GRA';
                        $CreatedDate = 'JoinedAt';
                    break;
                    case 'ERA':
                        $Table = EVENTUSERS;
                        $Points = 2;
                        $user_id = 'UserID';
                        $entity_id = 'EventID';
                        $entity_type = 'ERA';
                        $CreatedDate = 'CreatedDate';
                    break;
                    default:
                        break;
                }
                if($Points){
                    $this->db->where('DATE('.$CreatedDate.') BETWEEN DATE("'.$result->LastEngagementDate.'") AND "'.get_current_date('%Y-%m-%d',1).'"',NULL,FALSE);
                    $qry = $this->db->get($Table);
                    if($qry->num_rows()){
                        foreach($qry->result() as $row){
                            if($entity_type == 'GRA' && $row->ModuleID == 1)
                            {
                                continue;
                            }
                            $EngagementData = array('UserID'=>$row->$user_id,'EntityID'=>$row->$entity_id,'EntityType'=>$entity_type,'CreatedDate'=>$row->$CreatedDate,'Points'=>$Points);
                            $check = $this->db->get_where(ENGAGEMENT,$EngagementData);
                            if(!$check->num_rows()){
                                $this->db->insert(ENGAGEMENT,$EngagementData);                                
                            }
                        }
                        $this->db->set('LastEngagementDate',get_current_date('%Y-%m-%d',1));
                        $this->db->where('EngagementName',$result->EngagementName);
                        $this->db->update(CRONUPDATE);
                    }
                }
            }
        }
    }
}

?>
