<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* All process like : roles_listing,role_add,role_edit
* @package    Roles
* @author     Girish Patidar : 18-02-2015
* @version    1.0
*/

class Roles extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/roles_model','admin/login_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];

    }
        
    /************************ Roles Page Services **********************************/
    
    /**
     * Function for show roles listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function index_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('list_roles'), getUserRightsData($this->DeviceType))){
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

            $rolesTemp = $this->roles_model->getRolesList($start_offset, $end_offset);
            
            $Return['Data']['total_records'] = $rolesTemp['total_records'];
            $Return['Data']['results'] = $rolesTemp['results'];
            
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
     * Function for show roles listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function rolelistexceptselected_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles/rolelistexceptselected';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['RoleId'])) $RoleId = $Data['RoleId']; else $RoleId = '';

            $rolesTemp = $this->roles_model->getRoleListExceptSelected($RoleId);
            $userCount = $this->roles_model->getRoleAssignedUserCountByRoleId($RoleId);
            $Return['Data']['result'] = $rolesTemp;
            $Return['Data']['userCount'] = $userCount;
            
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
     * Function for create roles.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function save_roles_info_post()
    {
        $Return['ResponseCode']='200';
        $Return['ServiceName']='admin_api/roles/save_roles_info';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data['RoleId'])) $RoleId = $Data['RoleId']; else $RoleId= '';
        
        if($RoleId)//For Edit/update role detail rights
            $RightsId = getRightsId('editroles');
        else
            $RightsId = getRightsId('addroles');
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {   
            /* Validation - starts */            
            if ($this->form_validation->run('api/cretae_roles') == FALSE) { // for web
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error; //Shows all error messages as a string
            } else {
                
                if(isset($Data['RoleName'])) $RoleName = $Data['RoleName']; else $RoleName = '';
                if(isset($Data['RoleStatus'])) $RoleStatus = $Data['RoleStatus']; else $RoleStatus = '2';
                if(isset($Data['RoleId'])) $RoleId = $Data['RoleId']; else $RoleId = '';
                
                if ($this->roles_model->checkRoleExist($RoleName,$RoleId) == 'exist') {
                    $Return['ResponseCode'] = 512;
                    $Return['Message'] = lang('role_exists');                    
                }else{
                    $dataArr = array();
                    $dataArr['Name'] = $RoleName;
                    $dataArr['Description'] = $RoleName;
                    $dataArr['RoleStatusID'] = $RoleStatus;
                    $dataArr['BusinessUnitID'] = '1';
                    $dataArr['ParentRoleID'] = '0';
                    $dataArr['IsVisible'] = 1;

                    if(is_numeric($RoleId)){
                        $role_id = $this->roles_model->updateRoleDetails($dataArr,$RoleId);
                    }else{
                        $role_id = $this->roles_model->createRole($dataArr);
                    }
                    
                    if(!is_numeric($role_id)){
                        $Return['ResponseCode']='519';
                        $Return['Message'] = lang('try_again');
                    }
                }
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
    * Function for Upate role status like : delete
    * Parameters : status, roleid
    * Return : 
    */
    public function update_status_post() {
        $Return['ResponseCode'] = '200';
        $Return['Message'] = lang('success');
        $Return['ServiceName'] = 'admin_api/roles/update_status';
        $Return['Data'] = array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('deleteroles'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if (isset($Data) && $Data != NULL)
        {
            if(isset($Data['RoleStatus'])) $RoleStatus = $Data['RoleStatus']; else $RoleStatus = '2';
            if(isset($Data['RoleId'])) $RoleId = $Data['RoleId']; else $RoleId = '';
            
            if(isset($Data['NewRole']) && $Data['NewRole'] != ""){
                $NewRole = $Data['NewRole'];
            }else{
                $NewRole = '2';
            }
                        
            if ($RoleId != ""){
                $dataArr = array();
                $dataArr['RoleStatusID'] = $RoleStatus;
                $role_id = $this->roles_model->updateRoleDetails($dataArr,$RoleId);
                if(is_numeric($role_id)){
                    $roleArr = array('RoleID' => $NewRole);
                    $this->roles_model->updateUsersRole($roleArr,$RoleId);
                }else{
                    $Return['ResponseCode']='519';
                    $Return['Message'] = lang('try_again');
                }
            }else{
                $Return['ResponseCode']='519';
                $Return['Message'] = lang('try_again');
            }
            
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }
    
    /**
     * Function for get role permissions.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_role_permissions_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
            
            if(isset($Data['RoleId']))  $RoleId = $Data['RoleId']; else $RoleId = 1;
            
            $rolesTemp = $this->roles_model->getApplicationPermissionsList($RoleId);
                        
            $Return['Data'] = $rolesTemp;
            
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
     * Function for role permisssions.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function save_roles_permissions_post()
    {
        $Return['ResponseCode']='200';
        $Return['ServiceName']='admin_api/roles/save_roles_permissions';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('managerolepermissions'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {   
            
            if(isset($Data['RoleId'])) $RoleId = $Data['RoleId']; else $RoleId = '';
            
            if(is_numeric($RoleId)){
                $result = $this->roles_model->deleteRolePermissions($RoleId);
                
                    $roleRightsArr = array();
                    foreach($Data['PermissionsArr'] as $rolerights){
                        $roleRightsArr[] = array('RoleID' => $RoleId, 'RightID' => $rolerights['RightID'], 'ApplicationID' => $rolerights['ApplicationID']);
                        if(($rolerights['RightID'] == 50 || $rolerights['RightID'] == 51 || $rolerights['RightID'] == 52) && !checkValueKeyExistInArray($roleRightsArr,'RightID','49')){
                            $roleRightsArr[] = array('RoleID' => $RoleId, 'RightID' => 49, 'ApplicationID' => $rolerights['ApplicationID']);
                        }
                    }
               
                if(!empty($roleRightsArr)){
                    $this->roles_model->saveBatchRolePermissions($roleRightsArr);
                }
                
            }else{
                $Return['ResponseCode']='519';
                $Return['Message'] = lang('try_again');            
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
    
    /************************ Roles Page Services end **********************************/
    
    
    /************************ Permissions Page Services **********************************/   
    /**
     * Function for show roles listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_applications_list_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles/get_applications_list';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
            
            $result = $this->roles_model->getApplicationsList();            
            $Return['Data'] = $result;
            
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
     * Function for get permissions list.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_permission_list_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('list_permissions'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['ApplicationID'])) $ApplicationID = $Data['ApplicationID']; else $ApplicationID = '';
            if(is_numeric($ApplicationID)){
                
                $permissionTemp = $this->roles_model->getPermissionsList($ApplicationID);
                $Return['Data'] = $permissionTemp;
                
            }else{
                $Return['ResponseCode']='519';
                $Return['Message'] = lang('try_again');            
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
     * Function for get right role permissions.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_right_permission_roles_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles/get_right_permission_roles';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {            
            if(isset($Data['RightID']))  $RightID = $Data['RightID']; else $RightID = '';
            if(isset($Data['ApplicationID']))  $ApplicationID = $Data['ApplicationID']; else $ApplicationID = 1;
            if($RightID){
                $result = $this->roles_model->getRightsPermissionsList($ApplicationID,$RightID);
            }else{
                $result = array();
            }
                        
            $Return['Data'] = $result;
            
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
     * Function for role permisssions.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function save_permissions_roles_post()
    {
        $Return['ResponseCode']='200';
        $Return['ServiceName']='admin_api/roles/save_permissions_roles';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('changepermsissions'), getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {   
            
            if(isset($Data['RightID'])) $RightID = $Data['RightID']; else $RightID = '';
            if(isset($Data['ApplicationID'])) $ApplicationID = $Data['ApplicationID']; else $ApplicationID = '1';
            
            if(is_numeric($RightID)){
                $result = $this->roles_model->deletePermissionRoles($RightID,$ApplicationID);
                if(is_numeric($result)){
                    $roleRightsArr = array();
                    foreach($Data['RoleArr'] as $role){
                        $roleRightsArr[] = array('RoleID' => $role['RoleID'], 'RightID' => $RightID, 'ApplicationID' => $ApplicationID);
                    }
                    
                    if(!empty($roleRightsArr)){
                        $this->roles_model->saveBatchRolePermissions($roleRightsArr);
                    }
                }
                
            }else{
                $Return['ResponseCode']='519';
                $Return['Message'] = lang('try_again');            
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
    
    /************************ Permissions Page Services end **********************************/
        
    
    /************************ Manage Users Page Services **********************************/   
    
    /**
     * Function for show role listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_role_list_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles/get_role_list';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
            
            $result = $this->roles_model->getRoleList();            
            $Return['Data'] = $result;
            
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
     * Function for get user role listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_user_roles_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles/get_user_roles';
        $Return['Data']=array();
        $Data = $this->post_data;

        if(isset($Data) && $Data!=NULL )
        {
            
            if(isset($Data['UserID'])) $UserID= $Data['UserID']; else $UserID='';
            if($UserID != ""){
                $result = $this->roles_model->getUserRoles($UserID);
                $roleArr = array();
                foreach($result as $role){
                    $roleArr[] = $role['RoleID'];
                }                
                $Return['Data'] = $roleArr;
            }else{
                $Return['ResponseCode']='519';
                $Return['Message']= lang('input_invalid_format');
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
     * Function for show role users listings.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function get_role_users_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/roles/get_role_users';
        $Return['Data']=array();
        $Data = $this->post_data;

        //Check logged in user access right and allow/denied access
        if(!in_array(getRightsId('list_role_users'), getUserRightsData($this->DeviceType))){
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
            if(isset($Data['RoleID'])) $RoleID = $Data['RoleID']; else $RoleID = '';
            
            if(is_numeric($RoleID)){
                
                $usersTemp = $this->roles_model->getRoleUsersList($RoleID,$start_offset, $end_offset);
                
                $tempResults = array();
                foreach ($usersTemp['results'] as $temp)
                {
                    $temp['FirstName'] = stripslashes($temp['FirstName']);
                    $temp['LastName'] = stripslashes($temp['LastName']);
                    $temp['RoleID'] = $RoleID;
                    $tempResults[] = $temp;
                }
                $Return['Data']['total_records'] = $usersTemp['total_records'];
                $Return['Data']['results'] = $tempResults;
            }else{
                $Return['ResponseCode']='519';
                $Return['Message'] = lang('try_again');   
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
     * Function for create new user.
     * Parameters : From services.js(Angular file)
     * 
     */
    public function save_user_info_post()
    {
        $Return['ResponseCode']='200';
        $Return['ServiceName']='admin_api/roles/save_user_info';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data['UserID'])) $UserID = $Data['UserID']; else $UserID = '';
        
        if($UserID)//For Edit/update role users detail rights
            $RightsId = getRightsId('editusers');
        else
            $RightsId = getRightsId('addusers');
        
        //Check logged in user access right and allow/denied access
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL )
        {   
            /* Validation - starts */            
            if ($this->form_validation->run('api/validate_role_user') == FALSE) { // for web
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = 511;
                $Return['Message'] = $error; //Shows all error messages as a string
            }
            elseif (!empty($Data['Email']) && !$this->is_unique_value($Data['Email'], USERS . '.Email.StatusID.UserID.Email', $UserID))
            {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('email_exists');
            } elseif (!empty($Data['Username']) && !$this->is_unique_value($Data['Username'], PROFILEURL . '.Url.EntityID.EntityID.Username', $UserID))
            {
                $Return['ResponseCode'] = 511;
                $Return['Message'] = lang('username_already_exists');
            } 
            else 
            {                
                if(isset($Data['BusinessUnitID'])) $Data['BusinessUnitID'] = $Data['BusinessUnitID']; else $Data['BusinessUnitID'] = 1;
                            
                $is_super_admin = $this->roles_model->isSuperAdmin($UserID);
                $is_super_role = false;

                if(is_numeric($UserID))
                {                    
                    $result = $this->roles_model->updateRoleUserInfo($Data,$UserID);                    
                    if($result){
                        $delete_result = $this->roles_model->deleteUserRoles($UserID);
                        //var_dump($delete_result);
                        $userRolesArr = array();
                        foreach($Data['RoleArr'] as $role){
                            if($role['RoleID'] == '1')
                            {
                                $is_super_role = true;
                            }
                            $userRolesArr[] = array('UserID' => $UserID, 'RoleID' => $role['RoleID'], 'BusinessUnitID' => $Data['BusinessUnitID']);
                        }
                        //var_dump($is_super_admin);
                        //var_dump($is_super_role);
                        if($is_super_admin && !$is_super_role)
                        {
                            //$this->db->query("call updateForumCat(".$UserID.",1,0,0,3)");
                            initiate_worker_job('remove_supper_admin_permission', array('Members'=>array('ModuleID'=>3,'ModuleEntityID'=>$UserID),'CreatedBy'=>$this->UserID));  
                        }
                        else if(!$is_super_admin && $is_super_role)
                        {
                           // $this->db->query("call updateForumCat(".$UserID.",0,1,0,3)");
                            initiate_worker_job('assign_supper_admin_permission', array('Members'=>array('ModuleID'=>3,'ModuleEntityID'=>$UserID),'CreatedBy'=>$this->UserID));  
                        }

                        if(!empty($userRolesArr) && $delete_result){
                            $this->roles_model->saveBatchUserRoles($userRolesArr);
                        }
                    }else{
                        $Return['ResponseCode']='519';
                        $Return['Message'] = lang('try_again');
                    }
                }
                else
                {
                    $result = $this->roles_model->createRoleUser($Data);
                    if($result)
                    {
                        $userRolesArr = array();
                        foreach($Data['RoleArr'] as $role){
                            $userRolesArr[] = array('UserID' => $result, 'RoleID' => $role['RoleID'], 'BusinessUnitID' => $Data['BusinessUnitID']);
                            
                            if($role['RoleID']==1)
                            {
                                initiate_worker_job('assign_supper_admin_permission', array('Members'=>array('ModuleID'=>3,'ModuleEntityID'=>$result),'CreatedBy'=>$this->UserID));  
                            }
                            
                        }
                        
                        if(!empty($userRolesArr)){
                            $this->roles_model->saveBatchUserRoles($userRolesArr);
                        }
                    }
                    else
                    {
                        $Return['ResponseCode']='519';
                        $Return['Message'] = lang('try_again');
                    }
                }                          
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
     * check unique value
     * @access public
     * @param null
     */
    function is_unique_value($str, $fields, $user_id) 
    {
        list($table, $field, $select_field1, $where, $entity) = explode('.', $fields);

        $this->db->select($select_field1);
        $this->db->where(array($field => EscapeString($str)));
        $this->db->where($where . '!=' . $user_id, NULL, FALSE);

        if ($entity == 'Email')
        {
            $this->db->where('StatusID!=3', NULL, FALSE);
        } else if ($entity == 'Username')
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
    
    /************************ Manage Users Page Services end **********************************/
        
}//End of file roles.php