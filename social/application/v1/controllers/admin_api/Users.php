<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : users_listing,users_profile, users_edit
* @package    Users
* @author     Ashwin soni (01-10-2014)
* @version    1.0
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require APPPATH.'/libraries/REST_Controller.php';

class Users extends Admin_API_Controller 
{
    function __construct()
    { 
        parent::__construct();
        $this->load->model(array('admin/users_model','admin/login_model','admin/media_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];

    }
    

    public function set_user_value_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $key = $data['Key'];
        $value = $data['Value'];
        $user_id = $data['UserID'];

        $this->users_model->set_user_value($user_id,$key,$value);

        $this->response($return);
    }

    /**
     * Function for show user listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/users';
        $Return['Data']=array();
        $Data = $this->post_data;

        /*$RightsId = getRightsId('deleted_user');

        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output * /
            $Outputs=$Return;
            $this->response($Outputs);
        }*/
        
        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(isset($Data['UserStatus']))  $user_status = !empty($Data['UserStatus']) ? $Data['UserStatus'] : 2; else $user_status= '2';
        $RightsId='';
        if($user_status==2)//Status 2 for Register users
            $RightsId = getRightsId('registered_user');
        else if($user_status==3)//Status 3 for Deleted users
            $RightsId = getRightsId('deleted_user');
        else if($user_status==4)//Status 4 for Blocked users
            $RightsId = getRightsId('blocked_user');
        else if($user_status==1)//Status 2 for Waiting for Approval users
            $RightsId = getRightsId('waiting_for_approval');
        else if($user_status==23)//Status 2 for Waiting for Approval users
            $RightsId = getRightsId('suspended_user');

        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
            
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SearchKey']))  $search_keyword=$Data['SearchKey']; else $search_keyword='';
            $search_keyword=str_replace("_"," ",$search_keyword);

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';

            if(isset($Data['UserStatus']))  $user_status=$Data['UserStatus']; else $user_status= '2';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';            
            
            $tempResults = array();
            $userTemp = $this->users_model->getUsers($start_offset, $end_offset, $start_date, $end_date, $user_status, $search_keyword, $sort_by, $order_by);                

            foreach ($userTemp['results'] as $temp)
            {
                $temp['username'] = stripslashes($temp['username']);
                $temp['firstname'] = stripslashes($temp['firstname']);
                $temp['lastname'] = stripslashes($temp['lastname']);

                $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
                $temp['profilepicture'] = get_image_path($profileSection, $temp['profilepicture'],ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
                $tempResults[] = $temp;
            }
            $Return['Data']['total_records'] = $userTemp['total_records'];
            $Return['Data']['results'] = $tempResults;
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
    }
    
            
    /**
     * Function for change status of particular user.
     * Parameters : 1-waitingforApproval, 2-unblock,approve, 3-delete, 4-block
     */
    public function change_status_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/users/change_status';
        $Return['Data']=array(); 
        $Data=$this->post_data;
        
        //Set rights id by action(register,delete,blocked,waiting for approval users)

        if(isset($Data['Status'])) $Status = $Data['Status']; else $Status = '';
        $Status=1;
        if(isset($Data['permissiontype'])) $PermissionType = $Data['permissiontype']; else $PermissionType = '';
        
        if($PermissionType == 'roleusers'){
            if($Status==3)//Status 3 for Delete role user
                $RightsId = getRightsId('deleteusers');
            else
                $RightsId = 0;
        }else{
            if($Status==1)//Status 1 for Approve user
                $RightsId = getRightsId('approve_user_event');
            else if($Status==2)//Status 2 for unblock user
                $RightsId = getRightsId('unblock_user_event');
            else if($Status==3)//Status 3 for Delete user
                $RightsId = getRightsId('delete_user_event');
            else if($Status==4)//Status 4 for Block user
                $RightsId = getRightsId('block_user_event');
            else
                $RightsId = 0;
        }

        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['UserId'])) $UserId= $Data['UserId']; else $UserId=0;
            if(isset($Data['Status']))  $Status=$Data['Status']; else $Status= '';
            
            //Change status query for a user
            $this->users_model->changeStatus($UserId, $Status, $Data);
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for show user listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function most_active_users_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/users';
        $Return['Data']=array();
        $Data = $this->post_data;

        $RightsId = getRightsId('most_active_users');

        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';

            $tempResults = array();
            $userTemp = $this->users_model->getMostActiveUsers($start_offset, $end_offset, $start_date, $end_date, $sort_by, $order_by);                
           
            foreach ($userTemp['results'] as $temp)
            {
                $temp['username'] = stripslashes($temp['username']);
                $temp['firstname'] = stripslashes($temp['firstname']);
                $temp['lastname'] = stripslashes($temp['lastname']);
                $temp['minutes'] = NumberFormat($temp['minutes']);
                $temp['activitypercentile'] = NumberFormat($temp['activitypercentile']);
                $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
                $temp['profilepicture'] = get_image_path($profileSection, $temp['profilepicture'],ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
                $tempResults[] = $temp;
            }
            $Return['Data']['total_records'] = $userTemp['total_records'];
            $Return['Data']['results'] = $tempResults;
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
    }
    
    /**
    * Function for Upate users status : delete, approve
    * Parameters : userstatus, user_id(s)
    * Return : Array
    */
    public function update_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/users/update_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(isset($Data['userstatus'])) $Status = $Data['userstatus']; else $Status = '';
        
        if(isset($Data['permission']) && $Data['permission'] == "Analytics"){
            if($Status==3)//Status 3 for Delete user
                $RightsId = getRightsId('delete_user_event');
            else if($Status==4)//Status 4 for Block user
                $RightsId = getRightsId('block_user_event');
            else
                $RightsId = 0;
        }else{
            if($Status==1)//Status 1 for Approve user
                $RightsId = getRightsId('approve_user_event');
            else if($Status==2)//Status 2 for unblock user
                $RightsId = getRightsId('unblock_user_event');
            else if($Status==3)//Status 3 for Delete user
                $RightsId = getRightsId('delete_user_event');
            else if($Status==4)//Status 4 for Block user
                $RightsId = getRightsId('block_user_event');
        }

        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL)
        {
            $userstatus = isset($Data['userstatus']) ? $Data['userstatus'] : NULL;
            $users = isset($Data['users']) ? $Data['users'] : array();
            
            if (!empty($users))
            {
                $userData = array();
                
                foreach ($users as $user) {
                    $userData[] = array('StatusID' => $userstatus, 'UserID' => $user,'ModifiedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));
                }
                
                /* update Users Status */
                $this->users_model->updateMultipleUsersInfo($userData, 'UserID', $Data);                
            }
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
    * Function for Upate users status : delete, approve
    * Parameters : userstatus, user_id(s)
    * Return : Array
    */
    public function autologin_user_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/users/autologin_user';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        $RightsId = getRightsId('login_as_user_event');
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL)
        {
            $usersid = isset($Data['userid']) ? $Data['userid'] : NULL;
            
            if ($usersid)
            {
                $LoginSessionKey = random_string('unique', 8);
                /* update Users Status */
                $userArr = $this->users_model->getValueById(array('UserID','UserGUID','FirstName','LastName','Email'),$usersid);
                
                if(!empty($userArr)){
                    $userArr['LoginSessionKey'] = $LoginSessionKey;
                    $this->SetAutoLoginUserSession($userArr);
                }
            }else{
                $Return['ResponseCode'] = 500;
                $Return['Message'] = lang('input_invalid_format');
            }
        }else{
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
     * Function Name: SetSession
     * @param LoginSessionKey
     * @param UserID
     * @param UserGUID
     * @param FirstName
     * @param Email
     * @param ProfilePicture
     * @param RoleID
     * Description: Set Session for Current User
     */
    public function SetAutoLoginUserSession($Data) {
        $this->session->set_userdata('LoginSessionKey', $Data['LoginSessionKey']);
        $this->session->set_userdata('UserID', $Data['UserID']);
        $this->session->set_userdata('UserGUID', $Data['UserGUID']);
        $this->session->set_userdata('FirstName', $Data['FirstName']);
        $this->session->set_userdata('LastName', $Data['LastName']);
        $this->session->set_userdata('Email', $Data['Email']);
        if ($Data['FirstName'] != '') {
            $DisplayName = $Data['FirstName'];
            if ($Data['LastName'] != '') {
                $DisplayName.=" " . $Data['LastName'];
            }
        } else {
            $DisplayName = $Data['Email'];
        }
        $this->session->set_userdata('DisplayName', $DisplayName);
         
    }
    
    
    /**
     * Function for download users
     * Parameters : From services.js(Angular file)
     * 
     */
    public function download_users_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/users/download_users';
        $Return['Data']=array();
        $Data = $this->post_data;

        $RightsId = getRightsId('download_users_event');
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['SearchKey']))  $search_keyword=$Data['SearchKey']; else $search_keyword='';
            $search_keyword=str_replace("_"," ",$search_keyword);

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';
            if(isset($Data['dateFilterText'])) $dateFilterText = $Data['dateFilterText']; else $dateFilterText = "All";

            if(isset($Data['UserStatus']))  $user_status=$Data['UserStatus']; else $user_status= '2';

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';
            
            $userTemp = $this->users_model->getUsers($start_offset, $end_offset, $start_date, $end_date, $user_status, $search_keyword, $sort_by, $order_by);                
            
            $excelInput = array();
            foreach($userTemp['results'] as $user){
                $userArr['username'] = stripslashes($user['firstname'].' '.$user['lastname']);
                $userArr['usertype'] = $user['type'];
                $userArr['email'] = $user['email'];
                $userArr['register_date'] = $user['resgisdate'];
                $userArr['last_login'] = $user['lastlogindate'];
                $userArr['type'] = $user['sourcetype'];
                
                $excelInput[] = $userArr;
            }
            
            if($user_status==2)//Status 2 for Register users
                $sheetTitle = 'Registered User';
            else if($user_status==3)//Status 3 for Deleted users
                $sheetTitle = 'Deleted User';
            else if($user_status==4)//Status 4 for Blocked users
                $sheetTitle = 'Blocked User';
            else if($user_status==1)//Status 2 for Waiting for Approval users
                $sheetTitle = 'Waiting for Approval';
            
            $excelArr = array();
            $excelArr['headerArray'] = array('username'=>'User Name','usertype'=>'User Type','email'=>'Email', 'register_date'=>'Registered Date', 'last_login'=>'Last Login', 'type'=>'Type');
            $excelArr['sheetTitle'] = $sheetTitle;
            $excelArr['fileName'] = "UsersList.xls";
            $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
            $excelArr['inputData'] = $excelInput;
            $excelArr['ReportHeader'] = array("ReportName" => $sheetTitle, "dateFilterText" => $dateFilterText);
            
            $result = downloadExcelFile($excelArr);            
            if($result){
                $csv_url = base_url().'/admin/users/downloaduserlist';
                $Return['csv_url'] = $csv_url;
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for download most avctive users
     * Parameters : From services.js(Angular file)
     * 
     */
    public function download_most_active_users_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/users/download_most_active_users';
        $Return['Data']=array();
        $Data = $this->post_data;

        $RightsId = getRightsId('analytic_download_event');
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['Begin'])) $start_offset= $Data['Begin']; else $start_offset=0;
            if(isset($Data['End']))  $end_offset=$Data['End']; else $end_offset= 10;

            if(isset($Data['StartDate'])) $start_date= $Data['StartDate']; else $start_date='';
            if(isset($Data['EndDate']))  $end_date=$Data['EndDate']; else $end_date= '';
            if(isset($Data['dateFilterText'])) $dateFilterText = $Data['dateFilterText']; else $dateFilterText = "All";

            if(isset($Data['SortBy']))  $sort_by=$Data['SortBy']; else $sort_by= '';
            if(isset($Data['OrderBy']))  $order_by=$Data['OrderBy']; else $order_by= '';

            $userTemp = $this->users_model->getMostActiveUsers($start_offset, $end_offset, $start_date, $end_date, $sort_by, $order_by);                
           
            $excelInput = array();
            foreach($userTemp['results'] as $user){
                $userArr['name'] = stripslashes($user['username']);
                $userArr['sessions'] = $user['sessioncounts'];
                $userArr['minutes'] = $user['minutes'];
                $userArr['activity_percentile'] = $user['activitypercentile'];
                
                $excelInput[] = $userArr;
            }
            
            $excelArr = array();
            $excelArr['headerArray'] = array('name'=>'Name','sessions'=>'Sessions','minutes'=>'Minutes', 'activity_percentile'=>'Activity Percentile');
            $excelArr['sheetTitle'] = 'Most Active Users';
            $excelArr['fileName'] = "MostActiveUsers.xls";
            $excelArr['folderPath'] = DOC_PATH.ROOT_FOLDER.'/'.PATH_IMG_UPLOAD_FOLDER."csv_file/";            
            $excelArr['inputData'] = $excelInput;
            $excelArr['ReportHeader'] = array("ReportName" => "Most Active Users", "dateFilterText" => $dateFilterText);
            
            $result = downloadExcelFile($excelArr);            
            if($result){
                $csv_url = base_url().'/admin/users/downloadactiveuserlist';
                $Return['csv_url'] = $csv_url;
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
    }

    public function dummy_user_list_post()
    {
        $return['ResponseCode']='200';
        $return['Message']= lang('success');
        $return['ServiceName']='admin_api/users/dummy_user_list';
        $return['Data']=array();        
        $Data = $this->post_data;

        $RightsId = getRightsId('dummy_user_manager');
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }

        $page_no 	= isset($Data['PageNo']) ? $Data['PageNo'] : 1 ;
        $page_size 	= isset($Data['PageSize']) ? $Data['PageSize'] : 10 ;
        $sort_by 	= isset($Data['SortBy']) ? $Data['SortBy'] : 'U.CreatedDate' ;
        $order_by 	= isset($Data['OrderBy']) ? $Data['OrderBy'] : 'DESC' ;

        $return['Data'] = $this->users_model->dummy_users($page_no,$page_size,$sort_by,$order_by);
        $return['TotalRecords'] = $this->users_model->dummy_users($page_no,$page_size,$sort_by,$order_by,1);

        $this->response($return);
    }

    public function create_account_post()
    {
        $is_sign_up = 0;    

        $data = $this->post_data;
        $this->load->model(array('users/login_model','users/signup_model','upload_file_model'));
   
        if (isset($data)) 
        {
            $social_type 		= (isset($data['SocialType']) && !empty($data['SocialType'])) ? $data['SocialType'] : DEFAULT_SOCIAL_TYPE;
            $this->SourceID 	= 1;      
            $this->DeviceTypeID = 1;
            $device_type 		= 'Native';

            $user_id			= isset($data['UserID']) ? $data['UserID'] : '';

            $validation_rule 	= $this->form_validation->_config_rules['admin_api/users/create_account'];
            $is_device = isset($data['IsDevice']) ? $data['IsDevice'] : "0";

            $validation = $this->form_validation->run();

            if ($validation == FALSE) 
            {
                $error = $this->form_validation->rest_first_error_string();         
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } 
            elseif (!empty($data['Email']) && !$this->is_unique_value($data['Email'], USERS . '.Email.StatusID.UserID.Email', $user_id))
            {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = lang('email_exists');
            } 
            else 
            {
                $account_return = $this->return;
                $account_return['ResponseCode'] = 504;
                
                $user_guid = isset($data['UserGUID']) ? $data['UserGUID'] : '' ;
                $data['FirstName'] 	= isset($data['FirstName']) ? ucfirst(strtolower($data['FirstName'])) : '';
                $data['LastName'] 	= isset($data['LastName']) ? ucfirst(strtolower($data['LastName'])) : '';
                $data['Email'] 		= isset($data['Email']) ? strtolower($data['Email']) : '';
                $data['Username'] 	= isset($data['Username']) ? strtolower($data['Username']) : '';
                $data['Password'] 	= isset($data['Password']) ? $data['Password'] : '';             
                $data['Role'] 		= isset($data['Role']) ? $data['Role'] : DEFAULT_ROLE;
                $data['Gender']		= isset($data['Gender']) ? $data['Gender'] : 0;
                $data['UserMediaGUID'] = isset($data['UserMediaGUID']) ? $data['UserMediaGUID'] : "" ;

                $location['City'] 	= isset($data['City']) ? $data['City'] : '';
                $location['State'] 	= isset($data['State']) ? $data['State'] : '';
                $location['Country'] = isset($data['Country']) ? $data['Country'] : '';
                $location['CountryCode'] = isset($data['CountryCode']) ? $data['CountryCode'] : '';
                $location['StateCode'] = isset($data['StateCode']) ? $data['StateCode'] : '';

                $data['Location'] 	= $location;

                $dob = isset($data['DOB']) ? $data['DOB'] : '';
                $dob = !empty($dob) ? $dob : '';
                
                if (!empty($dob))
                {
                    $dob2 = explode('/', $dob);
                    if(isset($dob2) && count($dob2)>2)
                    {
                        $dob = $dob2[2] . '-' . $dob2[0] . '-' . $dob2[1];
                    }
                }
                if (empty($dob))
                {
                    $dob = '0000-00-00';
                }

                $data['DOB'] = $dob;

                $data['UserSocialID'] = '';
                $data['Latitude'] = '';
                $data['Longitude'] = '';
                $data['IPAddress'] = '';
                $data['Resolution'] = DEFAULT_RESOLUTION;
                $data['Picture'] = isset($data['Picture']) ? $data['Picture'] : '';
                $data['Token'] = '';
                $data['UserTypeID'] = 4;
                $data['DeviceID'] = DEFAULT_DEVICE_ID;
                $data['DeviceToken'] = '';
                $data['BetaInviteGuId'] = '';

                $data['SourceID']       = $this->SourceID;
                $data['SocialType']     = $social_type;
                $data['DeviceType']     = $device_type;
                $data['DeviceTypeID']   = $this->DeviceTypeID;
                $data['ConfirmPassword'] = $data['Password'];
                
                $is_sign_up = 0;      
                if($user_id)
                {
                    //$user_id = get_detail_by_guid($user_guid,3);
                    $account_return = $this->users_model->updateAccount($user_id,$data);
                }
                else
                {
                    $account_return = $this->users_model->createAccount($data); 
                    $is_sign_up = 1;   

                    if(!empty($account_return['Data']['UserGUID']) && $account_return['Data']['UserGUID'] != "" && (!isset($data['UserMediaGUID']) || $data['UserMediaGUID'] == "")){
                        $image_data = array('DeviceType' => 'Native', 'ImageData' => file_get_contents($data['MediaUrl']), 'ImageURL' => $data['MediaUrl'], 'ModuleID' => '3', 'ModuleEntityGUID' => $account_return['Data']['UserGUID'], "UserID" => $account_return['Data']['UserID'], 'Type' => 'profile');
                        $mediaData = $this->upload_file_model->saveFileFromURL($image_data);
                        if(isset($mediaData['Data']['ImageName']) && $mediaData['Data']['ImageName'] != ""){
                            $this->upload_file_model->updateProfilePicture($mediaData['Data']['MediaGUID'],$mediaData['Data']['ImageName'],$account_return['Data']['UserID'],3,$account_return['Data']['UserGUID']);
                        }
                    }
                }              
                
                if(!empty($account_return['Data']['UserGUID']))
                {
                    $this->return['UserGUID'] = $account_return['Data']['UserGUID'];    
                }        
                $this->return['ResponseCode'] = $account_return['ResponseCode'];
                $this->return['Message'] = $account_return['Message'];
                $this->return['Data'] = $account_return['Data'];     
            	if(!empty($is_sign_up))
            	{
            		$session_id = isset($data['SessionID']) ? $data['SessionID'] : session_id() ; 
            		$this->signup_model->update_analytics($this->SourceID, $this->DeviceTypeID, $is_sign_up, $this->return['ClientError'],$session_id);
            	}
            }
        } 
        else 
        {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('input_invalid_format');
            $device_type_id = '';
        }
        $this->response($this->return);
    }

    /**
     * check unique value
     * @access public
     * @param null
     */
    function is_unique_value($str, $fields, $user_id) 
    {
        list($table, $field, $select_field1, $where, $entity) = explode('.', $fields);

        $this->db->select($select_field1);
        $this->db->where(array($field => EscapeString($str)));
        if(!empty($user_id))
        {
        	$this->db->where($where . '!=' . $user_id, NULL, FALSE);
        }

        if ($entity == 'Email')
        {
            $this->db->where('StatusID!=3', NULL, FALSE);
        } 
        else if ($entity == 'Username')
        {
            $this->db->join(USERS, USERS . '.UserID=' . PROFILEURL . '.EntityID', 'left');
            $this->db->where(USERS . '.StatusID!=3', NULL, FALSE);
        }

        $query = $this->db->get($table);
        if ($query->num_rows() > 0)
        {
            return FALSE;
        } 
        else
        {
            if ($table == "ProfileUrl")
            {
                $controllers = array();
                $route = $this->router->routes;
                if ($handle = opendir(APPPATH . '/controllers'))
                {
                    while (false !== ($controller = readdir($handle)))
                    {
                        if ($controller != '.' && $controller != '..' && strstr($controller, '.') == '.php')
                        {
                            $controllers[] = strstr($controller, '.', true);
                        }
                    }
                    closedir($handle);
                }
                $reserved_routes = array_merge($controllers, array_keys($route));
                $reserved_routes[] = 'post';
                $reserved_routes[] = 'article';
                
                if (in_array(EscapeString(strtolower($str)), array_map('strtolower',$reserved_routes)))
                {
                    return FALSE;
                } 
                else
                {
                    return TRUE;
                }
            } 
            else
            {
                return TRUE;
            }
        }
    }

    /**
     * delete_dummy_user
     * @access public
     * @param user_id
     */
    function delete_dummy_user_post() 
    {
        $return = $this->return;
        $data = $this->post_data;

        $user_id = isset($data['UserID']) ? $data['UserID'] : 0 ;

        $this->users_model->delete_dummy_user($user_id);

        $this->response($return);
    }
    
    /**
     * [previous_profile_pictures_post Used to get previous profile picture]]
     * @return [json] [pictures json object]
     */
    function previous_profile_pictures_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        if ($this->form_validation->run('admin_api/users/previous_profile_pictures') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error;
        } else
        {
            $module_id = 3;
            $module_entity_id = get_detail_by_guid($data['ModuleEntityGUID'], $module_id);
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : 16;
            $return['Data'] = $this->users_model->get_previous_profile_pictures($module_id, $module_entity_id, $page_no, $page_size);
        }
        $this->response($return);
    }

    function get_all_interests_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $user_id = isset($data['UserID']) ? $data['UserID'] : 0 ;
        $interestUserType = isset($data['InterestUserType']) ? $data['InterestUserType'] : 1;
        $return['Data'] = $this->users_model->get_all_interests($user_id, $interestUserType);

        $this->response($return);
    }

    function save_all_interests_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $user_id = $data['UserID'];

        $interest = isset($data['Interest']) ? $data['Interest'] : array() ;
        $new_interests = isset($data['NewInterests']) ? $data['NewInterests'] : array() ;
        $is_only_add = isset($data['IsOnlyAdd']) ? $data['IsOnlyAdd'] : 0 ;
        
        $this->load->model('category/category_model');
        
        // Add new interests and merge it with user interests
        $new_interest_ids = array();
        if(!empty($new_interests) && is_array($new_interests)) {
            foreach ($new_interests as $new_interest){
                $new_interest_ids[] = $this->category_model->insert_interest_category($new_interest);
            }
        }
        $interest = array_merge($interest, $new_interest_ids);  
        
        
        $interestUserType = isset($data['InterestUserType']) ? $data['InterestUserType'] : 1;
        $this->category_model->insert_update_interest($interest, 3, $user_id, $interestUserType, $is_only_add);
        //$this->users_model->save_all_interests($user_id,$interest);
        
        $this->load->model('admin/dashboard/dashboard_activity_model');
        $InterestPercentage = $this->dashboard_activity_model->user_interest_data($user_id);
        $return['Data'] = $InterestPercentage['interests'];

        $this->response($return);
    }

    public function dummy_user_search_post()
    {
        $return = $this->return;
        
        $data = $this->post_data;
        $page_no = !empty($data['page_no']) ? $data['page_no'] : 1;
        $page_size = !empty($data['page_size']) ? $data['page_size'] : 11;
        $search = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '' ;
        
        $return['Data'] = $this->users_model->get_users($page_no, $page_size, false,$search);
        
        $this->response($return);
    }
    
    public function user_search_post()
    {
        $return = $this->return;
        
        $data = $this->post_data;
        $page_no = !empty($data['page_no']) ? $data['page_no'] : 1;
        $page_size = !empty($data['page_size']) ? $data['page_size'] : 11;
        $search = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '' ;
        
        $return['Data'] = $this->users_model->get_users($page_no, $page_size, true, $search, false);
        
        $this->response($return);
    }

    /**
     * Function for show user listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_user_profile_pictures_post()
    {
        $return = $this->return;       
        $data = $this->post_data;
        $login_user_id = $this->UserID;
        $page_no = !empty($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = !empty($data['PageSize']) ? $data['PageSize'] : 6;        
        $validation_rule =array(
            array(
                'field' => 'UserGUID',
                'label' => 'UserGUID',
                'rules' => 'trim|required|validate_guid[3]',
            )
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        else
        {
            $dummy_user_id = get_detail_by_guid($data['UserGUID'],3);
            $return['Data'] = $this->users_model->get_admin_uploaded_user_images($dummy_user_id,$login_user_id,$page_no,$page_size);

        }        
        $this->response($return);        
    }

    public function get_user_communication_post()
    {
        $return = $this->return;       
        $data = $this->post_data;
        $UserID = $this->UserID;
        $user_id = isset($data['UserID']) ? $data['UserID'] : $UserID ;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1 ;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 1 ;

        $return['Data'] = $this->users_model->get_user_communication($user_id,$page_no,$page_size);
        $return['TotalRecords'] = $this->users_model->get_user_communication($user_id,$page_no,$page_size,true);
        
        $this->response($return);
    }
    
    public function get_dummy_user_manager_suggestion_get()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data))
        {  
            if(!isset($data['SearchKeyword'])){
                $data['SearchKeyword'] = $this->get('SearchKeyword');
            }
            $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '' ;        
            $page_no        = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1' ;
            $page_size      = isset($data['PageSize']) ? $data['PageSize'] : '10' ;
            $return['Data'] = $this->users_model->get_dummy_user_manager_suggestion($search_keyword, $page_no, $page_size);
            
        }
        else
        {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);   
    }
    
    public function get_random_dummy_details_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data))
        {  
            $start_date = date('Y-m-d', strtotime('-50 years'));
            $end_date = date('Y-m-d', strtotime('-18 years'));
            $final_date_str = mt_rand(strtotime($start_date), strtotime($end_date));
            //$genderArr = array("1"=>"Male","2"=>"Female");
            $genderArr = array("1","2");
            $media_arr = array(
                            "avatar-01.jpg","avatar-02.jpg","avatar-03.jpg","avatar-04.jpg","avatar-05.jpg","avatar-06.jpg","avatar-07.jpg","avatar-08.jpg","avatar-09.jpg","avatar-10.jpg",
                            "avatar-11.jpg","avatar-12.jpg","avatar-13.jpg","avatar-14.jpg","avatar-15.jpg","avatar-16.jpg","avatar-17.jpg","avatar-18.jpg","avatar-19.jpg","avatar-20.jpg",
                            "avatar-21.jpg","avatar-22.jpg","avatar-23.jpg","avatar-24.jpg","avatar-25.jpg","avatar-26.jpg","avatar-27.jpg","avatar-28.jpg","avatar-29.jpg","avatar-30.jpg",
                            "avatar-31.jpg","avatar-32.jpg","avatar-33.jpg","avatar-34.jpg","avatar-35.jpg","avatar-36.jpg","avatar-37.jpg","avatar-38.jpg","avatar-39.jpg","avatar-40.jpg",
                            "avatar-41.jpg","avatar-42.jpg","avatar-43.jpg","avatar-44.jpg","avatar-45.jpg","avatar-46.jpg","avatar-47.jpg","avatar-48.jpg","avatar-49.jpg","avatar-50.jpg",
                            "avatar-51.jpg","avatar-52.jpg","avatar-53.jpg","avatar-54.jpg","avatar-55.jpg","avatar-56.jpg","avatar-57.jpg","avatar-58.jpg","avatar-59.jpg","avatar-60.jpg"
            );
            
            $user_info = $this->users_model->get_single_row("FirstName,Gender,City,State,Country", SAMPLEDUMMYUSERS, array(""),"rand()");
            $last_name = $this->users_model->get_single_row("LastName", SAMPLEDUMMYUSERS, array(""),"rand()");
            if(isset($user_info['Gender']) && $user_info['Gender'] != "0"){
                $Gender = $user_info['Gender'];
            }else{
                $Gender = $genderArr[array_rand($genderArr,1)];
            }
            
            $media_image = $media_arr[array_rand($media_arr,1)];
            
            $FirstName = trim($user_info['FirstName']);
            $LastName = trim($last_name['LastName']);
            $Location = array("Address" => "", "CountryCode" => "", "StateCode" => "");
            $Location['City'] = trim($user_info['City']);
            $Location['State'] = trim($user_info['State']);
            $Location['Country'] = trim($user_info['Country']);
            $Location['Location'] = $Location['City'].", ".$Location['State'].", ".$Location['Country'];
            $Location['Location'] = trim(trim($Location['Location'],","));            
            
            $user_arr = array();
            $user_arr['FirstName'] = $FirstName;
            $user_arr['LastName'] = $LastName;
            $user_arr['Email'] = trim(strtolower($user_arr['FirstName'].$user_arr['LastName']))."@mailinator.com";
            $user_arr['Gender'] = $Gender;
            $user_arr['Location'] = $Location;
            $user_arr['City'] = $Location['City'];
            $user_arr['State'] = $Location['State'];
            $user_arr['Country'] = $Location['Country'];
            $user_arr['DOB'] = date("m/d/Y",$final_date_str);
            $user_arr['MediaUrl'] = base_url("assets/img/dummyUser/".$media_image);
            $user_arr['MediaName'] = $media_image;
            
            $return['Data'] = $user_arr;
        }
        else
        {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);   
    }

}//End of file users.php