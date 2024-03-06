<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Roles_model extends Admin_Common_Model
{
    public function __construct(){
        parent::__construct();
    }

    /**
     * Function for get roles listing
     * Parameters : start_offset, end_offset, start_date, end_date
     * Return : Roles array
     */
    public function getRolesList($start_offset=0, $end_offset=""){  
        
        $this->db->select('R.RoleID AS roleid', FALSE);
        $this->db->select('R.Name AS rolename', FALSE);
        $this->db->select('R.Description AS description', FALSE);
        $this->db->select('R.RoleStatusID AS rolestatusid', FALSE);
        $this->db->select('(CASE R.RoleStatusID WHEN 1 THEN "Inactive" WHEN 2 THEN "Active" ELSE "Pending" END) AS status', FALSE);

        $this->db->from(ROLES."  R ");
        $this->db->where('R.RoleStatusID != ', '3');

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        /* Start_offset, end_offset */
        if(isset($start_offset) && $end_offset !='')
             $this->db->limit($end_offset,$start_offset);

        $query = $this->db->get();            
        $results['results'] = $query->result_array();
        return $results;
    }
    
    /**
     * Function for check if user is super admin or not
     * Parameters : $UserID
     * Return : Boolean
     */
    public function isSuperAdmin($UserID){  
        
        $this->db->select('UR.UserID');
        $this->db->from(USERROLES." UR");
        $this->db->where('UR.RoleID','1');                
        $this->db->where('UR.UserID', $UserID);
        
        $query = $this->db->get();
        if($query->num_rows())
        {
            return true;
        }          
        return false;
    }

    /**
     * Function for get roles listing
     * Parameters : $RoleId
     * Return : Roles array
     */
    public function getRoleListExceptSelected($RoleId){  
        
        $this->db->select('R.*', FALSE);
        $this->db->from(ROLES."  R ");
        $this->db->where('R.RoleStatusID != ', '3');                
        $this->db->where('R.RoleId != ', $RoleId);
        
        $query = $this->db->get();            
        $results = $query->result_array();
        return $results;
    }
    
    /**
     * Function for get roles listing
     * Parameters : $RoleId
     * Return : Roles array
     */
    public function getRoleAssignedUserCountByRoleId($RoleId){  
        
        $this->db->select('UR.*', FALSE);
        $this->db->from(USERROLES."  UR ");
        $this->db->where('UR.RoleId', $RoleId);
        
        $query = $this->db->get();            
        $results = $query->num_rows();
        return $results;
    }
    
    /**
     * Function for create role
     * @param array $dataArr
     * @return integer
     */
    function createRole($dataArr){
        
        $this->db->insert(ROLES, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    } 
    
    /**
     * Function for update role details
     * @param array $dataArr
     * @param integer $RoleId
     * @return integer
     */
    function updateRoleDetails($dataArr,$RoleId){
        
        $this->db->where('RoleID', $RoleId);
        $this->db->update(ROLES, $dataArr);
        return $this->db->affected_rows();        
    }
    
    /**
     * Function for update user roles
     * @param array $dataArr
     * @param integer $RoleId
     * @return integer
     */
    function updateUsersRole($dataArr,$RoleId){
        
        $this->db->where('RoleID', $RoleId);
        $this->db->update(USERROLES, $dataArr);
        return $this->db->affected_rows();        
    }
    
    /**
     * Function for check role exist or not
     * @param string $RoleName
     * @param integer $RoleId
     * @return integer
     */
    function checkRoleExist($RoleName,$RoleId = ''){
        
        $this->db->select('*');
        $this->db->from(ROLES);
        $this->db->where('Name',$RoleName);
        $this->db->where('RoleStatusID != ', '3');
                
        $query = $this->db->get();
        $dataArr = $query->row_array();
        if ($query->num_rows()) {
            
            if($RoleId != "" && $dataArr['RoleID'] == $RoleId){
                $return = 'notexist';
            }else{
                $return = 'exist';
            }
            
        } else {
            $return = 'notexist';
        }
        
        return $return;
    } 
    
    /**
     * Function for get roles listing
     * Parameters : $RoleId
     * Return : Roles array
     */
    public function getApplicationPermissionsList($RoleId){  
        $RootRoleRightArr = array();
        
        $this->db->select('AP.ApplicationID AS ApplicationID', FALSE);
        $this->db->select('AP.ApplicationName AS ApplicationName', FALSE);
        $this->db->from(APPLICATIONS."  AP ");
        $query = $this->db->get();        
        $app_results = $query->result_array();
        
        foreach($app_results as $application){            
            $this->db->select('AR.RightID AS RightID', FALSE);
            $this->db->select('RS.Name AS Name', FALSE);
            
            $this->db->from(APPLICATIONRIGHTS."  AR ");
            $this->db->join(RIGHTS." AS RS", ' RS.RightID = AR.RightID','inner');
            $this->db->where('RS.IsActive',1);
            $this->db->where('AR.ApplicationID',$application['ApplicationID']);
            $rights_query = $this->db->get();   
            $rights_results = $rights_query->result_array();
            
            $roleRightsArr = array();
            foreach($rights_results as $rolerights){
                
                $this->db->select('RR.RoleRightID AS RoleRightID', false);
                $this->db->from(ROLERIGHTS . " AS RR ");
                $this->db->where('RR.RoleID', $RoleId);
                $this->db->where('RR.ApplicationID', $application['ApplicationID']);
                $this->db->where('RR.RightID = ' . $rolerights['RightID']);
                $role_query = $this->db->get();
                $role_results = $role_query->row();
                $role_right_id = (!empty($role_results)) ? $role_results->RoleRightID : '';
                $rolerights['RoleRightID'] = $role_right_id;
                
                $SubRoleArr = array();
                $this->db->select('RS.RightID AS RightID', FALSE);
                $this->db->select('RS.Name AS Name', FALSE);                
                $this->db->from(RIGHTS."  RS ");
                $this->db->where('RS.ParentRightID',$rolerights['RightID']);
                $this->db->where('RS.IsActive',1);
                
                $subrole_query = $this->db->get();
                $subrole_results = $subrole_query->result_array();
                
                foreach($subrole_results as $subrole){    
                    $role_right_id = '';
                    $this->db->select('RR.RoleRightID AS RoleRightID',FALSE);
                    $this->db->from(ROLERIGHTS." AS RR ");
                    $this->db->where('RR.RoleID',$RoleId);
                    $this->db->where('RR.ApplicationID',$application['ApplicationID']);
                    $this->db->where('RR.RightID', $subrole['RightID']);
                    $role_right_query = $this->db->get();
                    $role_right_results = $role_right_query->row();
                    $role_right_id = (!empty($role_right_results)) ? $role_right_results->RoleRightID : '';
                    $subrole['RoleRightID'] = $role_right_id;
                    $SubRoleArr[] = $subrole;
                }
                
                $rolerights['RoleRights'] = $SubRoleArr;
                $roleRightsArr[] = $rolerights;
            }
            
            $application['RoleRights'] = $roleRightsArr;
            $RootRoleRightArr[] = $application;
        }
        //echo "<pre>";print_r($RootRoleRightArr);die;
        return $RootRoleRightArr;
    }
    
    /**
     * Function for delete role permissions
     * @param integer $RoleId
     * @return integer
     */
    function deleteRolePermissions($RoleId){ 
        if (CACHE_ENABLE) {
            /* Initiate job for delete user right cache file */
            initiate_worker_job('user_rights', array(), '', 'user_rights');
        }       
        $this->db->where('RoleID', $RoleId);
        $this->db->delete(ROLERIGHTS);
        return $this->db->affected_rows();        
    }
    
    /**
     * Function for save role permissions
     * @param array $dataArr
     * @return integer
     */
    function saveBatchRolePermissions($dataArr){
        $rtn = 0;
        if(!empty($dataArr)){
            $this->db->insert_batch(ROLERIGHTS, $dataArr);      
            $rtn = 1;
        }
        return $rtn;
    } 
    
    
    /**
     * Function for get permissions listing
     * Parameters : $ApplicationID
     * Return : Roles array
     */
    public function getPermissionsList($ApplicationID){
        $PermissionDataArr = array();
        //For get applications list
        $this->db->select('AP.ApplicationID AS ApplicationID', FALSE);
        $this->db->select('AP.ApplicationName AS ApplicationName', FALSE);
        $this->db->from(APPLICATIONS."  AP ");
        $this->db->where('AP.ApplicationID',$ApplicationID);
        $query = $this->db->get();        
        $app_results = $query->row_array();
        
        if(is_array($app_results) && !empty($app_results)){
        
            //$PermissionDataArr[] = array('application_id' => $app_results['ApplicationID'], 'right_id' => '', 'application_text' => $app_results['ApplicationName'], 'application' => $app_results['ApplicationName'],'action_group' => '','action' => '','permission_roles' => '');
            
            //for get parent role rights
            $this->db->select('AR.RightID AS RightID', FALSE);
            $this->db->select('RS.Name AS Name', FALSE);
            $this->db->from(APPLICATIONRIGHTS."  AR ");
            $this->db->join(RIGHTS." AS RS", ' RS.RightID = AR.RightID','inner');
            $this->db->where('AR.ApplicationID',$app_results['ApplicationID']);
            $this->db->where('RS.IsActive',1);
            $rights_query = $this->db->get();   
            //echo $this->db->last_query();die;
            $rights_results = $rights_query->result_array();            
            
            foreach($rights_results as $rolerights){
                //For get child role rigths
                $this->db->select('RS.RightID AS RightID', FALSE);
                $this->db->select('RS.Name AS Name', FALSE);
                $this->db->from(RIGHTS."  RS ");
                $this->db->where('RS.ParentRightID',$rolerights['RightID']);   
                $this->db->where('RS.IsActive',1);
                $subrole_query = $this->db->get();
                $subrole_results = $subrole_query->result_array();
                
                $parent_rights = 0;
                foreach($subrole_results as $subrole){
                    $action_group = '';
                    if($parent_rights == 0 || $parent_rights != $rolerights['RightID']){
                        $action_group = $rolerights['Name'];                    
                        $parent_rights = $rolerights['RightID'];
                    }
                    
                    $action = $subrole['Name'];
                    $right_id = $subrole['RightID'];
                    
                    //For get child rights permission roles
                    $this->db->select('R.Name AS Name', FALSE);
                    $this->db->from(ROLERIGHTS."  RR ");
                    $this->db->join(ROLES." AS R", ' R.RoleID = RR.RoleID','inner');
                    $this->db->where('RR.RightID',$subrole['RightID']);
                    $this->db->where("R.RoleStatusID",2);
                    $this->db->order_by('R.RoleID ASC');
                    $rights_query = $this->db->get();
                    $rights_results = $rights_query->result_array();
                    
                    $PermissionRoles = '';
                    foreach($rights_results as $rights){
                        $PermissionRoles.= $rights['Name'].' | ';
                    }
                    $PermissionRoles = trim($PermissionRoles," | ");
                    $PermissionRolesStr = trim($PermissionRoles,', ');
                    $PermissionDataArr[] = array('application_id' => $app_results['ApplicationID'], 'right_id' => $right_id, 'application_text' => $app_results['ApplicationName'], 'application' => '','action_group' => $action_group,'action' => $action,'permission_roles' => $PermissionRolesStr);
                }
            }            
        }
        //echo "<pre>";print_r($PermissionDataArr);die;
        return $PermissionDataArr;
    }
    
    /**
     * Function for get applications listing
     * Parameters : 
     * Return : applications list array
     */
    public function getApplicationsList(){
        
        //For get applications list
        $this->db->select('AP.ApplicationID AS ApplicationID', FALSE);
        $this->db->select('AP.ApplicationName AS ApplicationName', FALSE);
        $this->db->from(APPLICATIONS."  AP ");
        $query = $this->db->get();        
        $results = $query->result_array();        
        
        return $results;
    }
    
    
    /**
     * Function for get rights role permissions listing
     * Parameters : $RightID
     * Return : Rights permission array
     */
    public function getRightsPermissionsList($ApplicationID,$RightID){  
        $this->db->select('R.RoleID AS RoleID', FALSE);
        $this->db->select('R.Name AS Name', FALSE);
        $this->db->from(ROLES."  R ");
        $this->db->where('R.RoleStatusID',2);
        $query = $this->db->get();
        $results = $query->result_array();
        foreach ($results as $index => $result) {
            $this->db->select('RR.RoleRightID AS RoleRightID', false);
            $this->db->from(ROLERIGHTS . " AS RR ");
            $this->db->where('RR.RightID', $RightID);
            $this->db->where('RR.ApplicationID', $ApplicationID);
            $this->db->where('RR.RoleID = ' . $result['RightID']);
            $role_query = $this->db->get();
            $role_right_results = $role_query->row();
            $role_right_id = (!empty($role_right_results)) ? $role_right_results->RoleRightID : '';
            $results[$index]['RoleRightID'] = $role_right_id;
        }
        
        return $results;
    }
    
    /**
     * Function for delete permission roles
     * @param integer $RightID
     * @param integer $ApplicationID
     * @return integer
     */
    function deletePermissionRoles($RightID,$ApplicationID){ 
        if (CACHE_ENABLE) {
            /* Initiate job for delete user right cache file */
            initiate_worker_job('user_rights', array(), '', 'user_rights');
        }       
        $this->db->where('RightID', $RightID);
        $this->db->where('ApplicationID', $ApplicationID);
        $this->db->delete(ROLERIGHTS);
        return $this->db->affected_rows();        
    }
    
    
    /**
     * Function for get all role list
     * Parameters : 
     * Return : role list array
     */
    public function getRoleList(){
        
        $this->db->select('R.RoleID AS RoleID', FALSE);
        $this->db->select('R.Name AS Name', FALSE);
        $this->db->from(ROLES."  R ");
        $this->db->where("R.RoleStatusID != ",3);
        $query = $this->db->get();        
        $results = $query->result_array();        
        
        return $results;
    }    
    
    /**
     * Function for get user role list
     * Parameters : $user_id
     * Return : user role list array
     */
    public function getUserRoles($user_id){
        
        $this->db->select('UR.UserID AS UserID', FALSE);
        $this->db->select('R.RoleID AS RoleID', FALSE);
        $this->db->select('R.Name AS Name', FALSE);
        $this->db->from(USERROLES."  UR ");
        $this->db->join(ROLES." AS R", ' R.RoleID = UR.RoleID','inner');
        $this->db->where('UR.UserID', $user_id);
        $query = $this->db->get();        
        $results = $query->result_array();
        
        return $results;
    }  
    
    /**
     * Function for get role users listing
     * Parameters : start_offset, end_offset, role_id
     * Return : Roles array
     */
    public function getRoleUsersList($role_id,$start_offset=0, $end_offset=""){  
        
        $this->db->select('U.UserID AS UserID', FALSE);
        $this->db->select('U.UserGUID AS UserGUID', FALSE);
        $this->db->select('U.FirstName AS FirstName', FALSE);
        $this->db->select('U.LastName AS LastName', FALSE);
        $this->db->select('U.Email AS Email', FALSE);
        //$this->db->select('UL.LoginKeyword AS Username', FALSE);
        
        $this->db->from(ROLES."  R ");
        $this->db->join(USERROLES." AS UR", ' UR.RoleID = R.RoleID','inner');
        $this->db->join(USERS." AS U", ' U.UserID = UR.UserID','inner');
       // $this->db->join(USERLOGINS." AS UL", ' UL.UserID = UR.UserID','inner');
        $this->db->where('R.RoleStatusID != ', '3');
        $this->db->where('U.StatusID != ', '3');
        $this->db->where('R.RoleID', $role_id);
        $this->db->group_by('U.UserID');
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Start_offset, end_offset */
        if(isset($start_offset) && $end_offset !='')
             $this->db->limit($end_offset,$start_offset);

        $query      = $this->db->get();   
        $users   = $query->result_array();
                 
        $results['results'] = array();

        foreach ($users as $userdata) {      
            $this->db->select('GROUP_CONCAT(R1.Name SEPARATOR ",") as UserRoles',FALSE);
            $this->db->from(ROLES." AS R1 ");
            $this->db->join(USERROLES." AS UR1", ' UR1.RoleID = R1.RoleID','inner');
            $this->db->where('UR1.UserID', $userdata['UserID']);
            $role_query = $this->db->get();
            $role_results = $role_query->row();
            $userdata['UserRoles'] = (!empty($role_results)) ? $role_results->UserRoles : '';

            $userdata['Username'] = '';
            $this->db->select('LoginKeyword as Username,SetPassword,SourceID');
            $this->db->from(USERLOGINS);
            $this->db->where('UserID', $userdata['UserID']);
            $this->db->order_by('SourceID', 'ASC');
            $this->db->limit(1);
            $Qry = $this->db->get();
            if ($Qry->num_rows())
            {
                 $QryRow = $Qry->row();
                 if ($QryRow->SourceID == 1)
                 {
                    $userdata['Username'] = $QryRow->Username;
                 }                
            }
            $results['results'][] = $userdata;
        }
        return $results;
    }
    
    /**
     * Function for create new users
     * Parameters : Data array
     * Return : inteher
     */
    public function createRoleUser($Data) {
        $UserGUID = get_guid(); /* Create new User GuID */
        $CreatedDate = date("Y-m-d H:i:s");
        $StatusID = 2;
        $UserTypeID = 2;
        $SourceID = 1;
        
        //Get weekday id and time slot id
        $WeekDayID = $this->getWeekDayID(date('l'));
        $TimeSlot = $this->getTimeSlot();
        
        $dataArr = array();
        $dataArr['UserGUID']             = $UserGUID;
        $dataArr['UserTypeID']           = $UserTypeID;
        $dataArr['FirstName']            = EscapeString($Data['FirstName']);
        $dataArr['LastName']             = EscapeString($Data['LastName']);
        $dataArr['Email']                = EscapeString($Data['Email']);
        $dataArr['WeekDayID']            = $WeekDayID;
        $dataArr['TimeSlotID']           = $TimeSlot;
        $dataArr['CreatedDate']          = $CreatedDate;
        $dataArr['StatusID']             = $StatusID;
        $dataArr['LastLoginDate']        = $CreatedDate;
        $dataArr['EmailNotification']    = 0;
        $dataArr['ReferrerTypeID']       = 0;
        $dataArr['PhoneNumber']          = '';
        $dataArr['ProfilePicture']       = '';
        $dataArr['BusinessUnitID']       = $Data['BusinessUnitID'];
        $dataArr['SourceID']             = $SourceID;
        //Add new user
        if ($this->db->insert(USERS,$dataArr)) {
            // Get UserID of last inserted user
            $user_id = $this->db->insert_id();
            $Password = mt_rand( 10000000, 99999999);
            $SetPassword = 1;

            if(empty($Data['Username'])) {
                $Data['Username']=$UserGUID;
            }
            
            $UserLoginData['UserID']        = $user_id;
            $UserLoginData['LoginKeyword']  = EscapeString($Data['Username']);
            $UserLoginData['Password']      = generate_password($Password);
            $UserLoginData['CreatedDate']   = $CreatedDate;
            $UserLoginData['ModifiedDate']  = $CreatedDate;
            $UserLoginData['SetPassword']   = $SetPassword;
            $UserLoginData['SourceID']      = $SourceID;

            $this->db->insert(USERLOGINS,$UserLoginData);

            /* Insert login information - ends */

            /*  User Detail entry start here */
            //$profilename=$UserName;
            $profilename = '';
            $this->db->insert(USERDETAILS,array('UserID'=>$user_id,'ProfileName'=>$profilename,'UserWallStatus'=>'','TimeZoneID'=>419));
            /*  User role entry end here */

            $this->load->model(array('notification_model', 'privacy/privacy_model'));
            // Add privacy settings to low
            $this->privacy_model->save($user_id,'low');            
            $this->notification_model->set_all_notification_on($user_id);

            /* Insert User Profile URL Start*/
            $profileUrlData[] = array(
                                    'EntityType' => 'User', 
                                    'EntityID' => $user_id, 
                                    'Url' => EscapeString($Data['Username']),  
                                    'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
                                );
            $this->db->insert_on_duplicate_update_batch(PROFILEURL,$profileUrlData); 
            /* Insert User Profile URL End*/
            //Create Default album
            create_default_album($user_id, 3, $user_id);
                                        
            $emailDataArr = array();
            $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
            $emailDataArr['IsResend'] = 0;
            $emailDataArr['Subject'] = "Thank you for Your Registration";
            $emailDataArr['TemplateName'] = "emailer/admin_create_account_email";
            $emailDataArr['Email'] = $Data['Email'];
            $emailDataArr['EmailTypeID'] = COMMUNICATION_EMAIL_TYPE_ID;
            $emailDataArr['UserID'] = $user_id;
            $emailDataArr['StatusMessage'] = "Communication";        
            $FullName = stripslashes($Data['FirstName']." ".$Data['LastName']);
            $emailDataArr['Data'] = array("FirstLastName" => $FullName, "Password" => $Password);
            sendEmailAndSave($emailDataArr);
            
            /* Send Registration Email - Ends */
            
            return $user_id;
            
        }else{
            return false;
        }
    }
    
    /**
     * Function for update role user details
     * Parameters : Data,UserID
     * Return : true
     */
    public function updateRoleUserInfo($Data, $user_id)
    {
        if($user_id){

            if (CACHE_ENABLE) {
                $this->cache->delete('user_rights_'.$user_id);
                $this->cache->delete('user_profile_'.$user_id);
            }
            //For update user details
            $dataArr = array();
            $dataArr['FirstName'] = EscapeString($Data['FirstName']);
            $dataArr['LastName'] = EscapeString($Data['LastName']);
            $dataArr['Email'] = EscapeString($Data['Email']);            
            $this->db->where('UserID', $user_id);
            $this->db->update(USERS, $dataArr);
            
            //For update user name 
            $UserLoginData = array();
            $UserLoginData['LoginKeyword']  = EscapeString($Data['Username']);
            $UserLoginData['ModifiedDate']  = date("Y-m-d H:i:s");;
            $this->db->where('UserID', $user_id);
            $this->db->update(USERLOGINS, $UserLoginData);

            
           /* Insert User Profile URL Start*/
            $profileUrlData[] = array(
                                    'EntityType' => 'User', 
                                    'EntityID' => $user_id, 
                                    'Url' => EscapeString($Data['Username']),  
                                    'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
                                );
            $this->db->insert_on_duplicate_update_batch(PROFILEURL,$profileUrlData);  
            
            
            return $user_id;
        }else{
            return false;
        }
    }
    
    /**
     * Function for delete user all roles
     * @param integer $user_id
     * @return integer
     */
    function deleteUserRoles($user_id){        
        $this->db->where('UserID', $user_id);
        $this->db->delete(USERROLES);
        return $this->db->affected_rows();        
    }
    
    /**
     * Function for save user roles
     * @param array $dataArr
     * @return integer
     */
    function saveBatchUserRoles($dataArr){
        $rtn = 0;
        if(!empty($dataArr)){
            $this->db->insert_batch(USERROLES, $dataArr);      
            $rtn = 1;
        }
        return $rtn;
    }
    
    /**
     * Function for get user rights list
     * Parameters : $user_id
     * Return : user role list array
     */
    public function getUserRightsByUserId($user_id){
        
        $this->db->select('RR.RightID AS RightID', FALSE);
        $this->db->from(USERROLES."  UR ");
        $this->db->join(ROLERIGHTS." AS RR", ' RR.RoleID = UR.RoleID','inner');
        $this->db->where('UR.UserID', $user_id);
        $this->db->group_by('RR.RightID');
        $query = $this->db->get();
        $results = $query->result_array();
        
        $rightsArr = array();
        foreach($results as $right){
            $rightsArr[] = $right['RightID'];
        }
        return $rightsArr;
    } 


    public function get_user_rights($user_id) {
        $rights_id = $this->getUserRightsByUserId($user_id);
        $rights['a_q'] = 1;
        $rights['q_l'] = 0;
        $rights['q_c'] = 0;
        $rights['q_d'] = 0;
        if(in_array(161, $rights_id)) {
            $rights['q_l'] = 1;
        }
        if(in_array(162, $rights_id)) {
            $rights['q_c'] = 1;
        }
        if(in_array(163, $rights_id)) {
            $rights['q_d'] = 1;
        }
        return $rights;
    }
    
                
}//End of file roles_model.php
