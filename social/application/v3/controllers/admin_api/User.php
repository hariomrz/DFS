<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* All user related process like : getprofiledata, editprofile, settings, changepassword
* @package    User
* @author     ashwin kumar soni(25-09-2014)
* @version    1.0
*/

//require APPPATH.'/libraries/REST_Controller.php';

class User extends Admin_API_Controller
{
        
    public function __construct()
	{
            parent::__construct();
            $this->load->model(array('admin/login_model','admin/users_model','admin/media_model'));
            $this->lang->load('api');
            $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
            if ($logged_user_data['ResponseCode'] != 200) {
                $this->response($logged_user_data);
            }
            $this->UserID = $logged_user_data['Data']['UserID'];
                      
	}
	
	public function index()
	{
	}
        
    /**
     * Function for get user profile data.
     * Parameters : $user_id, $start_date, $end_date
     * Return : Array of user data
     */
    public function profile_info_post()
	{
            $Return['ResponseCode']='200';
            $Return['Message']= lang('success');
            $Return['ServiceName']='admin_api/user/profile_info';
            $Return['Data']=array();
            $Data = $this->post_data;
            
            if(isset($Data) && $Data!=NULL )
            {
                if(isset($Data['UserID'])) $user_id = $Data['UserID']; else $user_id = 0;
                if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
                if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
                
                /* Get User data from users_model for user */
                //$Return['Data'] = $this->users_model->get_profile_info($user_id, $start_date, $end_date);
               
                $userTemp = $this->users_model->getProfileInfo($user_id, $start_date, $end_date);
                $userTemp['username'] = stripslashes($userTemp['username']);
                $userTemp['firstname'] = stripslashes($userTemp['firstname']);
                $userTemp['lastname'] = stripslashes($userTemp['lastname']);
                
                $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
                $userTemp['profilepicture'] = get_image_path($profileSection, $userTemp['profilepicture'],ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);

                $Return['Data'] = $userTemp;

                if(empty($Return['Data']))
                {
                    /* If user does not exist */
                    $Return['ResponseCode']='672';
                    $Return['Message']=lang('not_valid_user');
                }
                
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
     * Function for get login data for show in graph.
     * Like : SOURCESOFLOGINS, DEVICES Graph
     * Parameters : $user_id, $start_date, $end_date
     * Return : Array of grpah data
     */
    public function login_graph_info_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/login_graph_info';
        $Return['Data']=array();
        $Data = $this->post_data;
       
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['UserID'])) $user_id = $Data['UserID']; else $user_id = 0;
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get User Login data from users_model for user*/
            $Return['Data'] = $this->users_model->getLoginGraphInfo($user_id, $start_date, $end_date);
            
            if(empty($Return['Data']))
            {
                /* If user does not exist */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('not_valid_user');
            }
            
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
     * Function for get IP's data for show in IP's section.
     * Like : IPs
     * Parameters : $user_id, $start_date, $end_date
     * Return : Array of IPS data
     */
    public function ips_info_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/ips_info';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['UserID'])) $user_id = $Data['UserID']; else $user_id = 0;
            if(isset($Data['StartDate'])) $start_date = $Data['StartDate']; else $start_date = '';
            if(isset($Data['EndDate'])) $end_date = $Data['EndDate']; else $end_date = '';
            
            /* Get User IPS data from users_model for user*/
            
            $ipTemp = $this->users_model->getIpsInfo($user_id, $start_date, $end_date);
            $Return['Data'] = $ipTemp['ips_results'];
            $Return['total_records'] = $ipTemp['total_records'];            
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
     * Function for change status of a single user
     * Parameters : $UserID, $Status : 1-waitingforApproval, 2-unblock,approve, 3-delete, 4-block
     * Return : Status : success/error
     */
    public function change_user_status_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/change_user_status';
        $Return['Data']=array(); 
        $Data=$this->post_data;
        
        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(isset($Data['status_action'])) $status_action = $Data['status_action']; else $status_action = '';
        
        if($status_action==1)//Status 1 for Approve user
            $RightsId = getRightsId('approve_user_event');
        else if($status_action==2)//Status 2 for unblock user
            $RightsId = getRightsId('unblock_user_event');
        else if($status_action==3)//Status 3 for Delete user
            $RightsId = getRightsId('delete_user_event');
        else if($status_action==4)//Status 4 for Block user
            $RightsId = getRightsId('block_user_event');
        else
            $RightsId = 0;

        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['UserID'])) $UserID= $Data['UserID']; else $UserID=0;
            if(isset($Data['Status']))  $Status=$Data['Status']; else $Status= '';
            
            /* Change status of user */
            $this->users_model->changeStatus($UserID,$Status);
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
     * Function for change password for a user
     * Parameters : $UserID, $NewPassword 
     * Return : Status : success/error
     */
    public function change_user_password_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/change_user_password';
        $Return['Data']=array(); 
        $Data=$this->post_data;
        
        $RightsId = getRightsId('change_password_event');
        if(!in_array($RightsId, getUserRightsData($this->DeviceType))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }
        
        $config = array(
            array(
                'field' => 'NewPassword',
                'label' => 'new password',
                'rules' => 'trim|required|min_length[6]|max_length[15]|callback_validate_password'
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();         
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
            $this->response($Return);
        } 
        
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['UserID'])) {
                $UserID = $Data['UserID']; 
            } else {$UserID=0;}
            if(isset($Data['NewPassword']))  $Password=$Data['NewPassword']; else $Password= '';
            
            /* Update Password of user */
            $this->users_model->changeUserPassword($UserID, $Password);
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
     * Function for change password for admin
     * Parameters : oldpassword, new password
     * Return : Status : success/error
     */
    public function change_admin_password_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/change_admin_password';
        $Return['Data']=array(); 
        $Data=$this->post_data;
        
        if(isset($Data) && $Data!=NULL ){
            
            if(isset($Data['OldPassword'])) $OldPassword= $Data['OldPassword']; else $OldPassword='';
            if(isset($Data['NewPassword']))  $NewPassword=$Data['NewPassword']; else $NewPassword= '';
            
            $user_id = $this->session->userdata['AdminUserID'];
            
            /* First check Oldpassword is exist for Admin. If not exist then show error */
            $admin_exist = $this->users_model->checkAdminExist($OldPassword,$user_id);

            if(empty($admin_exist))
            {
                /* IF admin Not exist */
                $Return['ResponseCode']='510';
                $Return['Message']= lang('old_pass_not_match');
                
            }else{
                /* Update NewPassword for Admin */                
                if($user_id){
                    $this->users_model->changeUserPassword($user_id, $NewPassword);
                }
            }

        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }
 
    /**
     * Function for update_profile_field question
     * Parameters : post data
     * Return : Status : success/error
     */
    public function update_profile_field_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/update_profile_field';
        $Return['Data']=array(); 
        $Data=$this->post_data; 

        if(!in_array($Data['StatusID'], array(2,10))){
            $Return['ResponseCode']='598';
            $Return['Message']= lang('permission_denied');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        }elseif($Data['FieldGUID']==''){
                   $Return['ResponseCode']='598';
           $Return['Message']= lang('input_invalid_format');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        } 
        if(isset($Data) && $Data!=NULL ){ 
                //$update_data['Title']          = !empty($Data['Title'])?$Data['Title']:''; 
                $update_data['Description']    = !empty($Data['Description'])?$Data['Description']:'';
                $update_data['StatusID']         = !empty($Data['StatusID'])?$Data['StatusID']:'10';  
                $update_data['ModifiedDate']         = date('Y-m-d H:i:s');  
                $FieldGUID         = !empty($Data['FieldGUID'])?$Data['FieldGUID']:'';    
            $this->users_model->update_profile_field($FieldGUID,$update_data);
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
     * Function for update_profile_field question
     * Parameters : post data
     * Return : Status : success/error
     */
    public function set_profile_field_priority_order_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/set_profile_field_priority_order';
        $Return['Data']=array(); 
        $Data=$this->post_data; 
   if(empty($Data['FieldGUID'])){
                   $Return['ResponseCode']='598';
            $Return['Message']= lang('input_invalid_format');
            /* Final Output */
            $Outputs=$Return;
            $this->response($Outputs);
        } 
        if(isset($Data) && $Data!=NULL ){ 
                    $FieldGUID         = !empty($Data['FieldGUID'])?$Data['FieldGUID']:array();    
                    $this->users_model->set_profile_field_priority_order($FieldGUID);
        }else{
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']= lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs=$Return;
        $this->response($Outputs);
    }

/*** FOR WORKHIGH ***/


    /**
     * [update_profile_new_post Updates user profile for workhigh which is a 4 step signup process]
     * @return [json] [success / error message and response code]
     */
    public function update_profile_new_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data)
        {
            if ($this->form_validation->run('api/users/update_profile_new') == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            }
            else
            {
                //4 step sign up process
                $profile_setup_step = isset($data['ProfileSetupStep']) ? $data['ProfileSetupStep'] : '';
                if($profile_setup_step)
                {
                    $first_name = isset($data['FirstName']) ? $data['FirstName'] : '';
                    $last_name = isset($data['LastName']) ? $data['LastName'] : '';
                    $dob = isset($data['DOB']) ? $data['DOB'] : '';
                    $gender = isset($data['gender']) ? $data['gender'] : '';

                    if($profile_setup_step == '4' && (empty($first_name) || empty($last_name)))
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('input_invalid_format');
                    }
                    else
                    {
                        $official_email = isset($data['OfficialEmail']) ? $data['OfficialEmail'] : '';
                        $landline_office = isset($data['LandlineOffice']) ? $data['LandlineOffice'] : '';
                        $extention_number = isset($data['ExtensionNumber']) ? $data['ExtensionNumber'] : '';
                        $office_location = isset($data['OfficeLocation']) ? $data['OfficeLocation'] : '';
                        $employee_id = isset($data['EmployeeID']) ? $data['EmployeeID'] : '';
                        $date_of_joining = isset($data['DateOfJoining']) ? $data['DateOfJoining'] : '';
                        $skills = isset($data['Skills']) ? $data['Skills'] : array();
                        $about_me = isset($data['AboutMe']) ? $data['AboutMe'] : '';
                        $personal_email = isset($data['PersonalEmail']) ? $data['PersonalEmail'] : '';
                        $mobile_number = isset($data['MobileNumber']) ? $data['MobileNumber'] : '';
                        $home_landline = isset($data['HomeLandline']) ? $data['HomeLandline'] : '';
                        $pan = isset($data['PAN']) ? $data['PAN'] : '';
                        $blood_group = isset($data['BloodGroup']) ? $data['BloodGroup'] : '';
                        $permanent_address = isset($data['PermanentAddress']) ? $data['PermanentAddress'] : '';
                        $present_address = isset($data['PresentAddress']) ? $data['PresentAddress'] : '';
                        $emergency_contact_person = isset($data['EmergencyContactPerson']) ? $data['EmergencyContactPerson'] : '';
                        $emergency_contact_number = isset($data['EmergencyContactNumber']) ? $data['EmergencyContactNumber'] : '';
                        $relation_with_contact_person = isset($data['RelationWithContactPerson']) ? $data['RelationWithContactPerson'] : '';
                        
                        $this->user_model->update_profile_new($first_name, $last_name,$user_id,'3',$user_id,$profile_setup_step,$official_email,$landline_office,$extention_number,$office_location,$employee_id,$date_of_joining,$skills,$about_me,$personal_email,$mobile_number,$home_landline,$pan,$blood_group,$permanent_address,$present_address,$emergency_contact_person,$emergency_contact_number,$relation_with_contact_person,$gender,$dob);                                            
                    }
                }
                else
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('input_invalid_format');
                }
                //End of 4 step sign up process
            }        
        } 
        else
        {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }
   
    /**
    * [Function to suspend user's account]
    * @input : UserID,AccountSuspendTill
    * @return [json] [success / error message and response code]
    */
    public function suspend_account_toggle_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if ($this->form_validation->run('admin_api/users/suspend') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $is_error = FALSE;
        }
        else
        {
            $user_id                = isset($data['UserID']) ? $data['UserID'] : '';
            $account_suspend_till   = isset($data['AccountSuspendTill']) ? $data['AccountSuspendTill'] : '';
            $status                 = $this->users_model->suspend_account_toggle($user_id,$account_suspend_till);
            $return['Data']         = array();
            $message =  "User Resumed";
            if($status==1)
            {
               $message = "User Suspended";
            }
            $return['Message']      = $message;
        }
        $this->response($return);
    }

    /**
    * [Function to update network details]
    * @input : UserID,network data
    * @return [json] [success / error message and response code]
    */
    public function update_network_details_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if ($this->form_validation->run('admin_api/users/update_network_details') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $is_error = FALSE;
        }
        else
        {
            $user_id                                    = isset($data['UserID']) ? $data['UserID'] : '';
            $update_data['Admin_Facebook_profile_URL']  = isset($data['Admin_Facebook_profile_URL']) ? $data['Admin_Facebook_profile_URL'] : '';
            $update_data['NoOfFriendsFB']               = isset($data['NoOfFriendsFB']) ? $data['NoOfFriendsFB'] : '';
            $update_data['NoOfFollowersFB']             = isset($data['NoOfFollowersFB']) ? $data['NoOfFollowersFB'] : '';
            $update_data['Admin_Linkedin_profile_URL']  = isset($data['Admin_Linkedin_profile_URL']) ? $data['Admin_Linkedin_profile_URL'] : '';
            $update_data['NoOfConnectionsIn']           = isset($data['NoOfConnectionsIn']) ? $data['NoOfConnectionsIn'] : '';
            $update_data['Admin_Twitter_profile_URL']   = isset($data['Admin_Twitter_profile_URL']) ? $data['Admin_Twitter_profile_URL'] : '';
            $update_data['NoOfFollowersTw']             = isset($data['NoOfFollowersTw']) ? $data['NoOfFollowersTw'] : '';
            $status                                     = $this->users_model->update_network_details($user_id,$update_data);
            $return['Data']                             = array();
            $message =  lang('success_active');
            if($status==1)
            {
               $message =  lang('success_suspend');
            }
            $return['Message']      = "Network Details Successfully Updated !!";
        }
        $this->response($return);
    }

    function validate_url($url)
    {
        $pattern = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
        if (!preg_match($pattern, $url) && !empty($url))
        {
         $this->form_validation->set_message('validate_url', 'The URL you entered is not correctly formatted.'); 
         return false;
        }

        return true;
    }

    /**
     * Function for get user profile data.
     * Parameters : $user_id
     * Return : Array of user data
     */
    public function profile_post()
    {
        $Return['ResponseCode']='200';
        $Return['Message']= lang('success');
        $Return['ServiceName']='admin_api/user/profile_info';
        $Return['Data']=array();
        $Data = $this->post_data;
        
        if(isset($Data) && $Data!=NULL )
        {
            if(isset($Data['UserID'])) $user_id = $Data['UserID']; else $user_id = 0;
            /* Get User data from users_model for user */
            $Return['Data'] = $this->users_model->getProfile($user_id);
            if(empty($Return['Data']))
            {
                /* If user does not exist */
                $Return['ResponseCode']='672';
                $Return['Message']=lang('not_valid_user');
            }
            
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
    * Function to get user interest
    * Parameters : 
    * Return : Array of user data
    */
    public function get_user_interest_post()
    {
        $Return['ResponseCode'] = '200';
        $Return['Message']      = lang('success');
        $Return['ServiceName']  = 'admin_api/user/get_user_interest';
        $Return['Data']         = array();
        $Data                   = $this->post_data;
        
        if(isset($Data) && $Data!=NULL)
        {
            if(isset($Data['UserID'])) $user_id = $Data['UserID']; else $user_id = 0;
            /* Get User data from users_model for user */
            $Return['Data'] = $this->users_model->get_user_interest($user_id);
        }
        else
        {
            /* Error - Invalid JSON format */
            $Return['ResponseCode']='519';
            $Return['Message']=lang('input_invalid_format');
        }
        /* Final Output */
        $Outputs = $Return;
        $this->response($Outputs);
    }

    /**
     * check unique value
     * @access public
     * @param null
     */
    function is_unique_value($str, $fields, $user_id=0) 
    {
        list($table, $field, $select_field1, $where, $entity) = explode('.', $fields);

        $this->db->select($select_field1);
        $this->db->where(array($field => EscapeString($str)));
        $this->db->where($where . '!=' . $user_id, NULL, FALSE);

        if ($entity == 'Email' || $entity == 'PhoneNumber')
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


    /**
    * [Function to update personal details]
    * @input : UserID,personal user data
    * @return [json] [success / error message and response code]
    */
    public function update_personal_details_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if ($data) {
            $config = array(
               array(
                   'field' => 'UserID',
                   'label' => 'user id',
                   'rules' => 'trim|required'
               ),
               array(
                    'field' => 'FullName',
                    'label' => 'full name',
                    'rules' => "trim|required|max_length[50]|regex_match[/^[a-zA-Z.@' \u{0900}-\u{097F}]+$/]"
               ),
               array(
                    'field' => 'Email',
                    'label' => 'email',
                    'rules' => 'trim|valid_email'
               ),
               array(
                    'field' => 'LocalityID',
                    'label' => 'locality',
                    'rules' => 'trim|required|integer'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            }  elseif (!empty($data['Email']) && !$this->is_unique_value($data['Email'], USERS . '.Email.StatusID.UserID.Email', $data['UserID'])) {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = lang('email_exists');
            }  else {
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($this->UserID, 1);
                if($is_super_admin) {
                    $user_id                        = $data['UserID'];
                    //User Data
                    $data['FullName'] = $data['FullName'];
                    if(!empty($data['FullName'])){
                        $user_data['FirstName'] = strtok($data['FullName'], ' ');
                        $user_data['LastName'] = strstr($data['FullName'], ' ');
                        $user_data['FirstName'] = trim($user_data['FirstName']);
                        $user_data['LastName'] = trim($user_data['LastName']);
                        if(empty($user_data['LastName'])) {
                            $user_data['LastName'] = '';
                        }
                    }

                    $user_data['AdminGender']       = safe_array_key($data, 'AdminGender', '');
                    $user_data['Email']             = safe_array_key($data, 'Email', '');
                    $user_data['Gender']            = safe_array_key($data, 'Gender',  $user_data['AdminGender']); 
                    /* $user_data['FirstName']         = isset($data['FirstName']) ? $data['FirstName'] : '';
                    $user_data['LastName']          = isset($data['LastName']) ? $data['LastName'] : '';                
                    $user_data['AdminPhoneNumber']  = isset($data['PhoneNumber']) ? $data['PhoneNumber'] : '';
                    $user_data['PhoneNumber']       = isset($data['PhoneNumber']) ? $data['PhoneNumber'] : '';
                    */

                    //User Profile Data
                    $profile_data['LocalityID']     = $data['LocalityID'];
                    $profile_data['IncomeLevel']    = safe_array_key($data, 'IncomeLevel', ''); 
                    $profile_data['DOB']            = safe_array_key($data, 'DOB', '');  
                    $profile_data['AdminDOB']       = $profile_data['DOB'];  
                    $profile_data['IsDOBApprox']    = safe_array_key($data, 'IsDOBApprox', 0);          
                    $relation_with_dob              = safe_array_key($data, 'RelationWithDOB', 'NA'); 
                    if($relation_with_dob!='NA')
                    {
                        $profile_data['RelationWithDOB']    = $relation_with_dob;
                    }
                    $profile_data['AdminMartialStatus']  = safe_array_key($data, 'MaritalStatus', '');   
                    $profile_data['AdminRelationWithName']  = safe_array_key($data, 'AdminRelationWithName', '');  
                    $profile_data['AdminRelationWithID']  = isset($data['AdminRelationWithGUID']) ? get_detail_by_guid($data['AdminRelationWithGUID'],3) : '';
                    
                    $WorkExperience                     = safe_array_key($data, 'WorkExperience', '');
                    $Family                             = safe_array_key($data, 'family', array()); 
                    /* $profile_data['TagLine']        = isset($data['TagLine']) ? $data['TagLine'] : '';
                    $Location                           = safe_array_key($data, 'Location', '');   
                    if(!empty($Location)) {
                        $this->load->helper('location');
                        $updated_location = update_location($Location);
                        $profile_data['AdminHomeCityID'] = $updated_location['CityID'];
                        $profile_data['AdminHomeCountryID'] = $updated_location['CountryID'];
                        $profile_data['AdminRelationWithID'] = "";
                    }
                    */
                    if(!empty($WorkExperience)) {
                        $this->users_model->update_user_exp_persona($user_id,$WorkExperience);
                    }
                    $this->users_model->save_family_details($Family,$user_id);
                    $this->users_model->save_user_details($user_id,$user_data,$profile_data);     
                    
                    if (CACHE_ENABLE) {
                        $this->cache->delete('user_profile_' . $user_id);
                    }
                    $return['Message']      = "Profile Successfully Updated";  
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }


    public function update_basic_details_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if ($data) {
            $config = array(
               array(
                   'field' => 'UserID',
                   'label' => 'user id',
                   'rules' => 'trim|required'
               )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            }  else {
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($this->UserID, 1);
                if($is_super_admin) {
                    $user_id                        = $data['UserID'];
                    //User Data
                    $user_data['AdminGender']       = safe_array_key($data, 'AdminGender', '');
                    $user_data['Gender']            = safe_array_key($data, 'Gender',  $user_data['AdminGender']); 
                    
                    $profile_data['IncomeLevel']    = safe_array_key($data, 'IncomeLevel', ''); 
                    $profile_data['DOB']            = safe_array_key($data, 'DOB', '');  
                    $profile_data['AdminDOB']       = $profile_data['DOB'];                      
                    $profile_data['IsDOBApprox']    = safe_array_key($data, 'IsDOBApprox', 0);          
                    
                    $this->users_model->save_user_details($user_id,$user_data,$profile_data);     
                    
                    if (CACHE_ENABLE) {
                        $this->cache->delete('user_profile_' . $user_id);
                    }
                    $return['Message']      = "Profile Successfully Updated";  
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
/*** FOR WORKHIGH ***/

}//End of file user.php
