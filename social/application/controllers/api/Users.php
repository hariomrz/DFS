<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for User module 
 * @category     Controller
 * @author       Vinfotech Team
 */
class Users extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->check_module_status(3);
        $this->load->model(array('users/user_model', 'users/friend_model', 'subscribe_model', 'notification_model', 'activity/activity_model'));
    }

    /** added by gautam
     * [Used to update device id and device token]
     * @return [json] [return json boject]
     */
    public function update_device_id_post()
    {
        /* Define variables - starts */
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
        /* Define variables - ends */
        /* Update data*/
        $this->db->where('LoginSessionKey',$data[AUTH_KEY]);
        $this->db->update(ACTIVELOGINS,array('DeviceID'=>$data['DeviceID'], 'DeviceToken'=>$data['DeviceToken']) );
        $this->db->limit(1);
        //echo $this->db->last_query();
        $this->response($return);
    }

    /**
     * [used to deactivate self account by user]
     * @return [json] [json object]
     */
    function deactivated_account_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /*Call methos to deactive account*/
        $this->user_model->deactivated_account($this->UserID);

        /* Delete LoginSessionKey from DB - starts */
        $this->db->where('UserID', $this->UserID);
        $this->db->delete(ACTIVELOGINS);
        /* Delete LoginSessionKey from DB - ends */

        $this->response($return);
    }

    /* public function save_introduction_post()
      {
      $user_id = $this->UserID;
      $data = $this->post_data;
      $return = $this->return;

      $why_you_here = $data['WhyYouHere'];
      $connect_with = $data['ConnectWith'];
      $connect_with = $data['ConnectFrom'];

      $this->response($return);
      } */

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
     * [get_age_group_list_post Used to get Age group list]
     * @return [json] [age group list]
     */
    function get_age_group_list_post()
    {
        $return = $this->return;
        $return['Data'] = $this->user_model->get_age_group_list();
        $this->response($return);
    }

    function get_page_user_list_get()
    {
        $user_id = $this->UserID;
        $data = $this->post_data;
        $search = isset($data['Search']) ? $data['Search'] : '';
        $return = $this->user_model->get_page_user_list($user_id, $search);
        $this->response($return);
    }

    function suggestion_list_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        if (isset($data))
        {
            $config = array(
                array(
                    'field' => 'Search',
                    'label' => 'search keyword',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'Type',
                    'label' => 'type',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $search = $data['Search'];
                $type = $data['Type'];
                $return['Data'] = $this->user_model->suggestion_list($user_id, $search, $type);
            }
        }
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
        if ($this->form_validation->run('api/users/previous_profile_pictures') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $module_id = $data['ModuleID'];
            $module_entity_id = get_detail_by_guid($data['ModuleEntityGUID'], $module_id);
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : 16;
            $return['Data'] = $this->user_model->get_previous_profile_pictures($module_id, $module_entity_id, $page_no, $page_size);
        }
        $this->response($return);
    }

    /**
     * [profile_post Used to get user prfile data]
     * @return [json] [user prfile data]
     */
    function profile_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $Data = $this->post_data;
        $current_user_id = $this->UserID;
        //$IsSettings = isset($Data['IsSettings']) ? $Data['IsSettings'] : 0 ;
        if (isset($Data))
        {   
            $user_id = $current_user_id;
            if (isset($Data['UserID']) && $Data['UserID']!='')
            {
                $user_id = $Data['UserID'];
            }/*added by gautam*/elseif(isset($Data['UserGUID']) && $Data['UserGUID']!='')
            {
              $user_id  = get_detail_by_guid($Data['UserGUID'],3);
            }  
            
            $profile_url = !empty($Data['ProfileURL']) ? $Data['ProfileURL'] : '';
            if($profile_url) {
                $row = $this->login_model->check_profile_url($profile_url);
                if(!empty($row['EntityType']) && $row['EntityType'] == 'User' && $row['StatusID'] == 2) {
                    $user_id = $Data['UserID'] = $_POST['UserID'] = $row['EntityID'];
                }
            }
            
            
            
            if ($this->form_validation->required($user_id) == FALSE)
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('not_authorized');
            } else
            {
                /* $view_as=1;
                  if($view_as){
                  $current_user_id=2;
                  $user_id=18;
                  } */
                $is_super_admin = $this->user_model->is_super_admin($user_id);
                $is_sub_admin = $this->user_model->is_super_admin($current_user_id, 1);
                
                $return['Data'] = $this->user_model->profile($user_id, $current_user_id, $is_sub_admin);
               /* $return['Data']['IsAdmin'] = false;
                if ($user_id == $current_user_id)
                {
                    $return['Data']['IsAdmin'] = true;
                }
               */ 
                $return['Data']['UserID'] = $user_id;
                $return['Data']['IsAdmin'] = $is_sub_admin;
                $return['Data']['IsSuperAdmin'] = $is_super_admin;
                $return['Data']['LoggedInUserDefaultPrivacy'] = $this->privacy_model->get_default_privacy($this->UserID);

               /* $return['Data']['CoverImageState'] = get_cover_image_state($current_user_id, $user_id, 3);
                $login_count = $this->login_model->get_login_count($this->session->userdata('UserID'));
                $return['LoginCount'] = $login_count;
                * 
                */
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        //echo json_encode($return);
        $this->response($return); /* Final Output */
    }

    /**
     * [update_profile_post Update user profile and set session]
     * @return [json] [success / error message and response code]
     */
    function update_profile_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data) {
            
            $Validation = $this->form_validation->run('api/users/update_profile');
            
            if ($Validation == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } /* elseif (!empty($data['Mobile']) && !$this->is_unique_value($data['Mobile'], USERS . '.PhoneNumber.StatusID.UserID.PhoneNumber', $user_id)) {                
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('mobile_exists');
            } */ else {
                //Define Variables
                $data['FullName'] = isset($data['FullName']) ? ucfirst(strtolower($data['FullName'])) : '';
                if(!empty($data['FullName'])){
                    $first_name = strtok($data['FullName'], ' ');
                    $last_name = strstr($data['FullName'], ' ');
                }else{
                    $first_name = isset($data['FirstName']) ? ucfirst(strtolower($data['FirstName'])) : '';
                    $last_name = isset($data['LastName']) ? ucfirst(strtolower($data['LastName'])) : '';
                }
                
                $update_user_id = isset($data['UserID']) ? $data['UserID'] : '';
                $email = isset($data['Email']) ? $data['Email'] : '';
                $about_me = isset($data['AboutMe']) ? $data['AboutMe'] : '';
                $introduction     = isset($data['Introduction'])  ? $data['Introduction']  : '' ;
                $expertise = isset($data['Expertise']) ? $data['Expertise'] : '';
                $user_name = isset($data['Username']) ? $data['Username'] : '';
                $facebook = isset($data['Facebook']) ? $data['Facebook'] : '';
                $linkedin = isset($data['LinkedIn']) ? $data['LinkedIn'] : '';
                $gmail_plus = isset($data['GPlus']) ? $data['GPlus'] : '';
                $twitter = isset($data['Twitter']) ? $data['Twitter'] : '';
                $work_experience = isset($data['WorkExperience']) ? $data['WorkExperience'] : '';
                $education = isset($data['Education']) ? $data['Education'] : '';
                $gender = isset($data['Gender']) ? $data['Gender'] : 0;
                $martial_status = isset($data['MartialStatus']) ? $data['MartialStatus'] : 0;
                $relation_with_guid = isset($data['RelationWithGUID']) ? $data['RelationWithGUID'] : '';
                $relation_with_name = isset($data['RelationWithName']) ? $data['RelationWithName'] : '';
                $timezone_id = isset($data['TimeZoneID']) ? $data['TimeZoneID'] : 0;
                $tagline = isset($data['Tagline']) ? $data['Tagline'] : '' ;

                $relation_with_id = 0;
                if (!empty($relation_with_guid)) {
                    $relation_with_id = get_detail_by_guid($relation_with_guid, 3);
                }

                $dob = isset($data['DOB']) ? $data['DOB'] : '';
                $dob = !empty($dob) ? $dob : '';
                if ($martial_status == "") {
                    $martial_status = 0;
                }
                if ($gender == "") {
                    $gender = 0;
                }
                if (!empty($dob)) {
                    $dob2 = explode('/', $dob);
                    $dob = $dob2[2] . '-' . $dob2[0] . '-' . $dob2[1];

                    $date2=date("Y-m-d");//today's date

                    $date1=new DateTime($dob);
                    $date2=new DateTime($date2);
                    $interval = $date1->diff($date2);

                    $myage= $interval->y;
                    if ($myage < 18) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('invalid_dob');
                        $this->response($return);
                    }
                }
                if (empty($dob)) {
                    $dob = '0000-00-00';
                }
                $location['City'] = isset($data['City']) ? $data['City'] : '';
                $location['State'] = isset($data['State']) ? $data['State'] : '';
                $location['Country'] = isset($data['Country']) ? $data['Country'] : '';
                $location['CountryCode'] = isset($data['CountryCode']) ? $data['CountryCode'] : '';
                $location['StateCode'] = isset($data['StateCode']) ? $data['StateCode'] : '';

                $home_location['City'] = isset($data['HCity']) ? $data['HCity'] : '';
                $home_location['State'] = isset($data['HState']) ? $data['HState'] : '';
                $home_location['Country'] = isset($data['HCountry']) ? $data['HCountry'] : '';
                $home_location['CountryCode'] = isset($data['HCountryCode']) ? $data['HCountryCode'] : '';
                $home_location['StateCode'] = isset($data['HStateCode']) ? $data['HStateCode'] : '';
                $house_number  = isset($data['HouseNumber']) ? $data['HouseNumber'] : '';
                $address  = isset($data['Address']) ? $data['Address'] : '';
                $occupation  = isset($data['Occupation']) ? $data['Occupation'] : '';
                
                if(!empty($update_user_id) && $update_user_id != $user_id) {
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if(!$is_super_admin){
                        $this->return['ResponseCode']= self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message']= "You are not authorized to perform this action";
                        $this->response($return);
                    }
                    $user_id = $update_user_id;
                }

                //$this->load->model('users/user_model');
                $this->user_model->update_profile(
                        $first_name, $last_name, $email, $about_me, $location, $user_id, $expertise, 
                        $user_name, $gender, $martial_status, $dob, $timezone_id, $relation_with_id, 
                        $home_location, $relation_with_name,$introduction,'',$tagline,$house_number,$address,$occupation
                );
                
               // $return['email_updated_and_link_sent'] = !empty($this->user_model->email_updated_and_link_sent) ? 1 : 0;

               // $this->user_model->update_work_experience($user_id, $work_experience);
               // $this->user_model->update_education($user_id, $education);

                //$this->user_model->updateSocialMediaLinks($user_id, $facebook, $twitter, $gmail_plus, $linkedin);
                $this->session->set_userdata('FirstName', $first_name);
                $this->session->set_userdata('LastName', $last_name);

                //Update Logins Analytic
                if($data['Loginsessionkey']) {
                    $age_group_id = get_age_group_id($user_id);
                    $this->db->where('UserID', $user_id);
                    $this->db->where('LoginSessionKey', $data['Loginsessionkey']);
                    $this->db->order_by('AnalyticLoginID','DESC');
                    $this->db->limit(1);                    
                    $this->db->update(ANALYTICLOGINS, array('AgeGroupID' => $age_group_id));
                    if (CACHE_ENABLE) {
                        $this->cache->delete('rule_user_' . $user_id);
                    }
                }
                
                
                //synch community user
               // $data['UserID'] = $user_id;
               // $this->load->model(array('community_server/community_users_model'));
              //  $this->community_users_model->update_user_profile($data);
                
                $return['Message'] = lang('profile_updated');
                
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [remove_profile_picture_post remove current profile picture of user]
     * @return [json] [json object]
     */
    function remove_profile_picture_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $this->load->model('upload_file_model');
        $data = $this->post_data;
        $user_id = $this->UserID;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '';
        $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
        if (!empty($module_id) && !empty($module_entity_guid))
        {
            $return['Data']['ProfilePicture'] = $this->upload_file_model->remove_profile_picture($user_id, $module_id, $module_entity_guid);
        } else
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = sprintf(lang('valid_value'), "module entity GUID");
        }

        $this->response($return);
    }

    /**
     * [update_collapse]
     */
    public function update_collapse_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        $data = $this->post_data;
        $user_id = $this->UserID;

        $is_collapse = isset($data['IsCollapse']) ? $data['IsCollapse'] : 0 ;

        $this->user_model->update_collapse($user_id,$is_collapse);

        $this->response($return);
    }

    /**
     * [list_post Get list of following / followers / friends / users]
     * @return [json] [list of users]
     */
    public function list_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        $data = $this->post_data;
        $user_id = $this->UserID;
        $uid = $user_id;
        $type = 'Users';
        if (isset($data['Type']))
        {
            $type = trim($data['Type']);
            if ($type == 'Request')
            {
                $type = 'Request';
            } else
            {
                $uid = isset($data['UID']) ? $data['UID'] : $user_id;
            }
        }

        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = isset($data['ModuleEntityID']) ? get_detail_by_guid($data['ModuleEntityID'], $module_id) : 0;

        $exclude_ids = isset($data['ExcludeID']) ? $data['ExcludeID'] : array() ;

        $search_key = '';
        if (isset($data['SearchKey']))
        {
            $search_key = $data['SearchKey'];
        }

        if ($type == 'Users' && $search_key == '')
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Search Key is required.';
        } else
        {
            $page_no = PAGE_NO;
            $page_size = PAGE_SIZE;
            if (isset($data['PageNo']) && $data['PageNo'] != '')
            {
                $page_no = $data['PageNo'];
            }
            if (isset($data['PageSize']) && $data['PageSize'] != '')
            {
                $page_size = $data['PageSize'];
            }
            $return['TotalRecords'] = 0;
            if ($type == 'NewsFeedTagging')
            {
                $return['Data'] = $this->friend_model->get_newsfeed_tagging_records($search_key, $user_id, $type, $page_no, $page_size, '', $uid, $module_id, $module_entity_id,array(),0,$exclude_ids);
                //$return['TotalRecords'] = $this->friend_model->get_newsfeed_tagging_records($search_key, $user_id, $type, $page_no, $page_size, '', $uid, $module_id, $module_entity_id, array(), 1);
            } 
            else
            {
                $return['Data'] = $this->friend_model->get_all_user($search_key, $user_id, $type, $page_no, $page_size, '', $uid, $module_id, $module_entity_id,array(),0,'',$exclude_ids);
                $return['TotalRecords'] = $this->friend_model->get_total_users_count($search_key, $user_id, $type, $uid, $module_id, $module_entity_id);
            }

            $return['PageNo'] = $page_no;
            $return['PageSize'] = $page_size;
        }
        $this->response($return);
    }

    /**
     * [action_button_status Get action button information about other user with his userid]
     * @return [json] [Action information about user]
     */
    public function action_button_status_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $data = $this->post_data;
        $current_user_id = $this->UserID;
                
        $user_id = '';
        if (isset($data['UserID']) && $data['UserID']!='') {
            $user_id = $data['UserID'];
        } elseif(isset($data['UserGUID']) && $data['UserGUID']!='') {
          $user_id  = get_detail_by_guid($data['UserGUID'],3);
        } 
            
        $return['Data'] = $this->friend_model->action_button_status($current_user_id, $user_id);
        if($current_user_id == $user_id)
        {
            $return['Data']['ShowFriendsBtn'] = 0;
        }
        $this->response($return);
    }

    /**
     * [get_user_list_get similar to allUser but for handling jquery autocomplete request]
     * @return [json] [Json object]
     */
    public function get_user_list_get()
    {
        /* Define variables - starts */
        $return = $this->return;
        $search_key = '';
        $user_id = $this->UserID;
        $selected_users = array();
        /* Define variables - ends */

        $search_key = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
        $show_friend = isset($_REQUEST['showFriend']) ? $_REQUEST['showFriend'] : 0;
        $selected_users = isset($_REQUEST['selectedUsers']) ? explode(',', $_REQUEST['selectedUsers']) : array();

        $this->load->model('users/friend_model');
        $return['Data'] = $this->friend_model->get_user_list($search_key, $user_id, $show_friend, $selected_users);
        $this->response($return);
    }

    /**
     * [check_social_accounts_post Get a list of attached social accounts for current user]
     * @return [type] [description]
     */
    public function check_social_accounts_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $user_id = isset($data['UserID']) ? $data['UserID'] : $user_id;
        $return['Data'] = $this->user_model->check_social_accounts($user_id);
        $this->response($return);
    }

    /**
     * [attach_social_account_post Attach new social account for logged in user]
     * @return [type] [description]
     */
    public function attach_social_account_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $social_type = $data['SocialType'];
        $source_id = $this->login_model->get_source_id($social_type);
        if ($this->form_validation->run('api/users/attach_social_account') == FALSE && $source_id == 1)
        { // for web
            $error = $this->form_validation->rest_first_error_string();
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error; //Shows all error messages as a string
        } else
        {
            $social_id = $data['SocialID'];
            $profile_url = $data['profileUrl'];
            $profile_picture = $data['ProfilePicture'];
            $return['ResponseCode'] = $this->user_model->attach_social_account($user_id, $social_type, $social_id, $profile_url, $profile_picture);
            $return['Data']['SocialType'] = $social_type;
            $return['Data']['SocialID'] = $social_id;
            $return['Data']['profileUrl'] = $profile_url;
            $return['Data']['ProfilePicture'] = $profile_picture;
        }
        $this->response($return);
    }

    /**
     * [detach_social_account_post Detach user social account]
     * @return [json] [success / error message and response code]
     */
    public function detach_social_account_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        if ($this->form_validation->run('api/users/detach_social_account') == FALSE)
        { // for web
            $error = $this->form_validation->rest_first_error_string();
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error; //Shows all error messages as a string
        } else
        {
            $data = $this->post_data;
            $social_type = $data['SocialType'];
            $source_id = $this->login_model->get_source_id($social_type);
            $return['ResponseCode'] = $this->user_model->detach_social_account($user_id, $source_id);
            if ($return['ResponseCode'] == 501)
            {
                $return['Message'] = lang('atleast_one_userlogin');
            } else
            {
                $return['Message'] = lang('successfully_detached');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: connections
     * @param Type[Request Type eg. IncommingRequest,OutgoingRequest],UserGUID[String],PageNo[Int],PageSize[Int],SectionID[1-Friend, 2-Requests],SearchKey[String]
     * @return JSON[response of user list based on type and given userguid]
     * Description: To get friend connections / requests / followers / following
     */
    function connections_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $type = isset($data['Type']) ? ucfirst($data['Type']) : 'Friends';
        $uid = isset($data['UserGUID']) ? get_detail_by_guid($data['UserGUID'], 3) : $user_id;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 24;
        $search_key = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $viewingUserID = isset($data['ViewingUserID']) ? $data['ViewingUserID'] : 0;

        $is_friends_disabled    = $this->settings_model->isDisabled(10);
        $is_follow_disabled     = $this->settings_model->isDisabled(11);

        $this->load->model('users/friend_model');

        $return['Data']['SelfProfile'] = 0;
        if ($user_id == $uid)
        {
            $return['Data']['SelfProfile'] = 1;
        }
        switch (strtolower($type))
        {
            case 'connections':
                if($is_friends_disabled)
                {
                    $return['Data']['Friends'] = array();
                    $return['Data']['IncomingRequestCount'] = array();
                }
                else
                {
                    $return['Data']['Friends'] = $this->friend_model->connections($user_id, 'Friends', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                    $return['Data']['IncomingRequestCount'] = $this->friend_model->incoming_request_count($user_id, $uid);
                }
                if($is_follow_disabled)
                {
                    $return['Data']['Following'] = array();
                    $return['Data']['Followers'] = array();
                }
                else
                {
                    $return['Data']['Following'] = $this->friend_model->connections($user_id, 'Following', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                    $return['Data']['Followers'] = $this->friend_model->connections($user_id, 'Followers', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                }
                break;
            case 'requests':
                if($is_friends_disabled)
                {
                    $return['Data']['OutgoingRequest'] = array();
                    $return['Data']['IncomingRequest'] = array();
                }
                else
                {
                    $return['Data']['OutgoingRequest'] = $this->friend_model->connections($user_id, 'OutgoingRequest', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                    $return['Data']['IncomingRequest'] = $this->friend_model->connections($user_id, 'IncomingRequest', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                }
                break;
            case 'all':
                if($is_friends_disabled)
                {
                    $return['Data']['Friends'] = array();
                    $return['Data']['OutgoingRequest'] = array();
                    $return['Data']['IncomingRequest'] = array();
                }
                else
                {
                    $return['Data']['Friends'] = $this->friend_model->connections($user_id, 'Friends', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                    $return['Data']['OutgoingRequest'] = $this->friend_model->connections($user_id, 'OutgoingRequest', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                    $return['Data']['IncomingRequest'] = $this->friend_model->connections($user_id, 'IncomingRequest', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                }
                if($is_follow_disabled)
                {
                    $return['Data']['Following'] = array();
                    $return['Data']['Followers'] = array();
                }
                else
                {
                    $return['Data']['Following'] = $this->friend_model->connections($user_id, 'Following', $uid, $search_key, $page_no, $page_size, $viewingUserID);
                    $return['Data']['Followers'] = $this->friend_model->connections($user_id, 'Followers', $uid, $search_key, $page_no, $page_size, $viewingUserID);   
                }
                
                break;
            default:
                $return['Data'] = $this->friend_model->connections($user_id, $type, $uid, $search_key, $page_no, $page_size, $viewingUserID);
                break;
        }
        $this->response($return);
    }

    /**
     * [interest_post Used to get user interest]
     * @return [json] [json object]
     */
    public function interest_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;


        /* Edited by Gautam - starts */
        $Data = $this->post_data;
        /* Edited by Gautam - ends */

        /* if($this->friend_model->check_low_connection($user_id))
          { */
        $return['Data'] = $this->user_model->get_interest($user_id,false,false,$Data);
        /* }
          else
          {
          $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
          $return['Message'] = 'You already have many connections.';
          } */
        $this->response($return);
    }

    /**
     * [save_interest_post Used to save user interest]
     * @return [json] [json object]
     */
    function save_interest_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $this->load->model('category/category_model');

        if ($this->form_validation->run('api/users/save_interest') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $categories = $data['CategoryIDs'];
            $interestUserType = isset($data['InterestUserType']) ? $data['InterestUserType'] : 1;
            //$this->category_model->insert_update_category($categories, 3, $user_id,'insert');
            $this->category_model->insert_update_interest($categories, 3, $user_id, $interestUserType);
            //$this->user_model->save_interest($user_id,$categories);
            $return['Message'] = "Interests have been saved successfully";
        }
        $this->cache->delete('user_profile_' . $user_id);
        $this->response($return);
    }

    /**
     * [save_interest_post Used to save user interest]
     * @return [json] [json object]
     */
    function remove_interest_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $this->load->model('category/category_model');

        if ($this->form_validation->run('api/users/remove_interest') == FALSE) 
        {
            $error = $this->form_validation->rest_first_error_string();         
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error; //Shows all error messages as a string
        } 
        else 
        {
            $interestUserType = isset($data['InterestUserType']) ? $data['InterestUserType'] : 1;
            //$this->category_model->remove_entity_category($data['CategoryID'],3,$user_id);
            $this->category_model->insert_update_interest(array($data['CategoryID']), 3, $user_id, $interestUserType, false, true);
            //$this->user_model->save_interest($user_id,$categories);
        }
        $this->response($return);
    }


    /**
     * [mute_source_post Used to mute source]
     * @return [JSON] [Response Object]
     */
    function mute_source_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $config = array(
                array(
                    'field' => 'ModuleID',
                    'label' => 'module id',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ModuleEntityGUID',
                    'label' => 'module entity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $user_id = $this->UserID;
                $module_id = $data['ModuleID'];
                $module_entity_guid = $data['ModuleEntityGUID'];
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                if (empty($module_entity_id))
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "module entity GUID");
                    return $return;
                }

                $this->user_model->mute_source($user_id, $module_entity_id, $module_id);
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [un_mute_source_post Used to unmute source]
     * @return [JSON] [Response Object]
     */
    function un_mute_source_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $config = array(
                array(
                    'field' => 'ModuleID',
                    'label' => 'module id',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ModuleEntityGUID',
                    'label' => 'module entity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $user_id = $this->UserID;
                $module_id = $data['ModuleID'];
                $module_entity_guid = $data['ModuleEntityGUID'];
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                if (empty($module_entity_id))
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "module entity GUID");
                    return $return;
                }

                $this->user_model->un_mute_source($user_id, $module_entity_id, $module_id);
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [mute_source_list_post Used to get the mute source list]
     * @return [JSON] [Response object]
     */
    function mute_source_list_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $user_id = $this->UserID;
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $keyword = isset($data['Keyword']) ? $data['Keyword'] : '';
            $return['Data'] = $this->user_model->mute_source_list($user_id, $keyword, 0, $page_no, $page_size);
            $return['TotalRecords'] = $this->user_model->mute_source_list($user_id, $keyword, 1, $page_no, $page_size);
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [prioritize_source_post Used to prioritize source]
     * @return [JSON] [Response Object]
     */
    function prioritize_source_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $config = array(
                array(
                    'field' => 'ModuleID',
                    'label' => 'module id',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ModuleEntityGUID',
                    'label' => 'module entity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $user_id = $this->UserID;
                $module_id = $data['ModuleID'];
                $module_entity_guid = $data['ModuleEntityGUID'];
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                if (empty($module_entity_id))
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "module entity GUID");
                    return $return;
                }

                $this->user_model->prioritize_source($user_id, $module_entity_id, $module_id);
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [un_prioritize_source_post Used to unmute source]
     * @return [JSON] [Response Object]
     */
    function un_prioritize_source_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $config = array(
                array(
                    'field' => 'ModuleID',
                    'label' => 'module id',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ModuleEntityGUID',
                    'label' => 'module entity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $user_id = $this->UserID;
                $module_id = $data['ModuleID'];
                $module_entity_guid = $data['ModuleEntityGUID'];
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                if (empty($module_entity_id))
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "module entity GUID");
                } else
                {
                    $this->user_model->un_prioritize_source($user_id, $module_entity_id, $module_id);
                }
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [prioritize_source_list_post Used to get the mute source list]
     * @return [JSON] [Response object]
     */
    function prioritize_source_list_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $user_id = $this->UserID;
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $keyword = isset($data['Keyword']) ? $data['Keyword'] : '';
            $return['Data'] = $this->user_model->prioritize_source_list($user_id, $keyword, 0, $page_no, $page_size);
            $return['TotalRecords'] = $this->user_model->prioritize_source_list($user_id, $keyword, 1, $page_no, $page_size);
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [save_cover_image_state Used to save cover image state]
     * @return [JSON] [Response Object]
     */
    function save_cover_image_state_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $config = array(
                array(
                    'field' => 'ModuleID',
                    'label' => 'module id',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ModuleEntityGUID',
                    'label' => 'module entity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else
            {
                $user_id = $this->UserID;
                $module_id = $data['ModuleID'];
                $module_entity_guid = $data['ModuleEntityGUID'];
                $status = isset($data['Status']) ? $data['Status'] : 1;
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                if (empty($module_entity_id))
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "module entity GUID");
                    return $return;
                }
                $this->user_model->cover_image_state($user_id, $module_entity_id, $module_id, $status);
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [follow_post Used to follow an entity]
     * @return [json] [Success / Failure]
     */
    function follow_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($data != NULL && isset($data))
        {
            $entity_id = '';
            $login_session_key = '';
            $user_id = $this->UserID;
            $entity_type = 'user';
            if (isset($data) && !empty($data['MemberID']))
            {
                $entity_id = $data['MemberID'];
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'MemberID should be provided';
                $this->response($return);
            }
            $module_id = !empty($data['ModuleID']) ? $data['ModuleID'] : 3;
            if (isset($data) && !empty($data[AUTH_KEY]))
            {
                $login_session_key = $data[AUTH_KEY];
            }

            if (isset($data) && !empty($data['Type']))
            {
                $entity_type = $data['Type'];
            }

            if ($entity_type == 'page')
            {
                $module_id = 18;
            }

            if ($entity_type == 'category')
            {
                $module_id = 27;
            }

            if (isset($data['GUID']) && $data['GUID'] == '1')
            {
                if ($module_id == 3)
                {
                    $field = 'UserID';
                } else if ($module_id == 27)
                {
                    $field = 'CategoryID';
                } else
                {
                    $field = 'PageID';
                }

                if($module_id != 27){
                    $entity_id = get_detail_by_guid($entity_id, $module_id, $field, 1);
                }
            } elseif (is_string($entity_id) && $entity_type == 'page')
            {
                $my_entity_id = get_detail_by_guid($entity_id, 18, 'PageID', 1);
                if ($my_entity_id)
                {
                    $entity_id = $my_entity_id;
                }
            }
            if(empty($entity_id))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'MemberID should be provided';
                $this->response($return);
            }
            $data = array('TypeEntityID' => $entity_id, 'UserID' => $user_id, 'Type' => $entity_type);
            $this->load->model('follow/follow_model');
            $result = $this->follow_model->follow($data);
            //$return['Data']['result'] = $res['result'];
            $return['Message'] = $result['msg'];
        }
        $this->response($return);
    }

    /**
     * [remove_follow_post Used to remove follow user]
     * @return [json] [json object]
     */
    function remove_follow_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        if ($this->form_validation->run('api/users/remove_follow') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $user_guid = $data['UserGUID'];
            $remove_user_id = get_detail_by_guid($user_guid, 3);
            $return['Data'] = $this->user_model->remove_follow($user_id, $remove_user_id);
        }
        $this->response($return);
    }

    /**
     * [accept_post Accept/Reject users follow request]
     * @return [json] [Success / Failure]
     */
    function accept_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($data != NULL && isset($data))
        {
            if (isset($data) && !empty($data['EntityID']))
            {
                $entity_id = $data['EntityID'];
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'EntityID should be provided';
                $this->response($return);
            }

            if (isset($data) && !empty($data['RequestType']))
            {
                $request_type = $data['RequestType'];
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'RequestStatus should be provided';
                $this->response($return);
            }

            if (isset($data) && !empty($data['RequesterID']))
            {
                $requester_id = $data['RequesterID'];
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'RequesterID should be provided';
                $this->response($return);
            }

            if (isset($data) && !empty($data['EntityOwnerID']))
            {
                $entity_owner_id = $data['EntityOwnerID'];
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'EntityOwnerID should be provided';
                $this->response($return);
            }

            if (isset($data) && !empty($data['RequestType']))
            {
                $request_type = $data['RequestType'];
            } else
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'RequestStatus should be provided';
                $this->response($return);
            }

            $user_id = $this->UserID;
            $entity_type = 'user';

            if (isset($data) && !empty($data['Type']))
            {
                $entity_type = $data['Type'];
            }

            $data = array('TypeEntityID' => $entity_id, 'UserID' => $user_id, 'Type' => $entity_type, 'RequestType' => $request_type, 'RequesterID' => $requester_id, 'EntityOwnerID' => $entity_owner_id);

            $this->load->model('follow/follow_model');
            $result = $this->follow_model->action_request($data);
            //$return['Data']['result'] = $res['result'];

            if ($res['ResponseCode'])
            {
                $return['ResponseCode'] = $result['ResponseCode'];
            }
            $return['Message'] = $result['msg'];
        }
        $this->response($return);
    }

    /**
     * @Summary: get users unfollow
     * @create_date: Thursday, July 11, 2014
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    function unfollow_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $Data = $this->post_data;
        if ($Data != NULL && isset($Data))
        {
            //check incoming data 
            if (isset($Data['UserID']) && !empty($Data['UserID']))
            {
                $user_id = $Data['UserID'];
            } else
            {
                $user_id = '';
            }

            if (isset($Data['TypeEntityID']) && !empty($Data['TypeEntityID']))
            {
                $TypeEntityID = $Data['TypeEntityID'];
            } else
            {
                $TypeEntityID = '';
            }

            if (isset($Data['Type']) && !empty($Data['Type']))
            {
                $Type = $Data['Type'];
            } else
            {
                $Type = '';
            }

            //check for validation on data
            if ($this->form_validation->required($user_id) == FALSE)
            {/* Error -  Userid  is required. */
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('userid_required');
            } else
            {
                $res = $this->user_model->unfollow($user_id, $Type, $TypeEntityID);
                $return['Data'] = $res;
            }
        }
        $this->response($return);
    }

    /**
     * [following_post Get following user list]
     * @return [json] [following user list]
     */
    function following_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data) && !empty($data))
        {
            $login_session_key = '';
            $user_id = $this->UserID;
            if (isset($data[AUTH_KEY]) && !empty($data[AUTH_KEY]))
            {
                $login_session_key = $data[AUTH_KEY];
            }

            if (isset($data['UserID']) && !empty($data['UserID']))
            {
                $user_id = $data['UserID'];
            }
            //validate the incoming data.
            if ($this->form_validation->required($login_session_key) == FALSE) /* Error -  Login session key is mandatory. */
            {
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('not_authorized');
            } else
            {
                $page_no = PAGE_NO;
                $page_size = PAGE_SIZE;
                if (isset($data['PageNo']) && $data['PageNo'] != '')
                {
                    $page_no = $data['PageNo'];
                }
                if (isset($data['PageSize']) && $data['PageSize'] != '')
                {
                    $page_size = $data['PageSize'];
                }
                $return['Data'] = $this->user_model->following($user_id, $page_no, $page_size);
            }
        }
        $this->response($return); /* Final Output */
    }

    /**
     * [followers_post Get followers list]
     * @return [json] [followers list]
     */
    function followers_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data) && !empty($data))
        {
            $login_session_key = "";
            $user_id = $this->UserID;
            if (isset($data[AUTH_KEY]) && !empty($data[AUTH_KEY]))
            {
                $login_session_key = $data[AUTH_KEY];
            }

            if (isset($data['UserID']) && !empty($data['UserID']))
            {
                $user_id = $data['UserID'];
            }

            if ($this->form_validation->required($login_session_key) == FALSE)
            {
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('not_authorized');
            } elseif ($this->form_validation->required($user_id) == FALSE)
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('userid_required');
            } else
            {
                $page_no = PAGE_NO;
                $page_size = PAGE_SIZE;
                $type = '';
                if (isset($data['PageNo']) && $data['PageNo'] != '')
                {
                    $page_no = $data['PageNo'];
                }
                if (isset($data['PageSize']) && $data['PageSize'] != '')
                {
                    $page_size = $data['PageSize'];
                }
                if (isset($data['Type']) && $data['Type'] != '')
                {
                    $type = $data['Type'];
                }
                $result = $this->user_model->followers($user_id, $page_no, $page_size, $type);
                if ($result)
                {
                    $return['Data'] = $result;
                } else
                {
                    $return['Data'] = lang('no_followers');
                }
            }
        }
        $this->response($return); /* Final Output */
    }

    /**
     * @Summary: Deny user's follow request
     * @create_date: Thu, Jan 01, 2015
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    public function cancelFollowRequest_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $Data = $this->post_data;

        if ($Data != NULL && isset($Data))
        {
            //check incoming data 
            if (isset($Data) && !empty($Data['MemberID']))
            {
                $TypeEntityID = $Data['MemberID'];
            } else
            {
                $TypeEntityID = '';
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'MemberID should be provided';
                $this->response($return);
            }

            if (isset($Data) && !empty($Data[AUTH_KEY]))
            {
                $LoginSessionKey = $Data[AUTH_KEY];
            } else
            {
                $LoginSessionKey = '';
            }
            $user_id = $this->UserID;

            if (isset($Data) && !empty($Data['Type']))
            {
                $Type = $Data['Type'];
            } else
            {
                $Type = 'user';
            }

            $data = array('TypeEntityID' => $TypeEntityID, 'UserID' => $user_id, 'Type' => $Type);

            $this->load->model('follow/follow_model');
            $res = $this->login_model->cancelFollowRequest($data);
            //$return['Data']['result'] = $res['result'];

            if ($res['ResponseCode'])
                $return['ResponseCode'] = $res['ResponseCode'];

            $return['Message'] = $res['msg'];
        }

        $this->response($return);
    }

    /**
     * @Summary: get users role
     * @create_date: Thursday, July 11, 2014
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    function userRole_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $Data = $this->post_data;
        if ($Data != NULL && isset($Data))
        {
            ///check incoming data 
            if (isset($Data['UserID']) && !empty($Data['UserID']))
            {
                $user_id = $Data['UserID'];
            } else
            {
                $user_id = '';
            }

            if (isset($Data[AUTH_KEY]) && !empty($Data[AUTH_KEY]))
            {
                $LoginSessionKey = $Data[AUTH_KEY];
            } else
            {
                $LoginSessionKey = '';
            }

            ///validate the incoming data .
            if ($this->form_validation->required($user_id) == FALSE)
            {/* Error -  User id is required. */
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('userid_required');
            } elseif ($this->form_validation->required($LoginSessionKey) == FALSE)
            {/* Error -  User id is required. */
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('not_authorized');
            } else
            {
                $res = $this->user_model->getUserRole($user_id);
                if ($res)
                {
                    $return['Data'] = $res;
                } else
                {
                    $return['Message'] = lang('invalid_userid');
                }
            }
        }
        $this->response($return);
    }

    /**
     * @Summary: get users role
     * @create_date: Thursday, July 11, 2014
     * @last_update_date:
     * @access: public
     * @param:
     * @return:
     */
    function roleRights_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $Data = $this->post_data;
        if ($Data != NULL && isset($Data))
        {
            ///check incoming data 
            if (isset($Data['RoleID']) && !empty($Data['RoleID']))
            {
                $RoleID = $Data['RoleID'];
            } else
            {
                $RoleID = '';
            }

            if (isset($Data[AUTH_KEY]) && !empty($Data[AUTH_KEY]))
            {
                $LoginSessionKey = $Data[AUTH_KEY];
            } else
            {
                $LoginSessionKey = '';
            }
            ///validate the incoming data .
            if ($this->form_validation->required($RoleID) == FALSE)
            {/* Error -  User id is required. */
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('roleid_required');
            }
            if ($this->form_validation->required($LoginSessionKey) == FALSE)
            {/* Error -  User id is required. */
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('not_authorized');
            } else
            {
                $res = $this->user_model->getRoleRights($RoleID);
                if ($res)
                {
                    $return['Data'] = $res;
                } else
                {
                    $return['Message'] = lang('roleid_required');
                }
            }
        }
        $this->response($return);
    }

    /**
     * [reportMedia_post report media]
     * @return [json] [success / error message and response code]
     */
    function report_media_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data['Type']))
        {
            $description = '';
            if (isset($data['Description']))
            {
                $description = $data['Description'];
            }

            if ($data['Type'] == 'Activity')
            {
                $activity_id = '';
                if (isset($data['ActivityID']))
                {
                    $activity_id = $data['ActivityID'];
                }

                if ($this->form_validation->required($activity_id) == FALSE)
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = 'ActivityID is required';
                } else
                {
                    $this->user_model->reportActivityMedia($activity_id, $user_id, $description);
                }
            } elseif ($data['Type'] == 'Group')
            {
                $group_id = '';
                if (isset($data['GroupID']))
                {
                    $group_id = $data['GroupID'];
                }

                if ($this->form_validation->required($group_id) == FALSE)
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = 'GroupID is required';
                } else
                {
                    $this->user_model->reportGroupMedia($group_id, $user_id, $description);
                }
            } elseif ($data['Type'] == 'User')
            {
                $uid = '';
                if (isset($data['UID']))
                {
                    $uid = $data['UID'];
                }

                if ($this->form_validation->required($uid) == FALSE)
                {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = 'UID is required';
                } else
                {
                    $this->user_model->report_user_media($uid, $user_id, $description);
                }
            }
        } else
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Media Type is mandatory';
        }
        $this->response($return);
    }

    /**
     * Function Name : deleteAccount
     * @param  LoginSessionKey, UserGUID
     * @return success / error message and response code
     * Description : delete user account
     */
    function deleteAccount_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data))
        {
            if (isset($data['UserGUID']))
                $user_guid = $data['UserGUID'];
            else
                $user_guid = '';
            if (isset($data[AUTH_KEY]))
                $LoginSessionKey = $data[AUTH_KEY];
            else
                $LoginSessionKey = '';

            if ($this->form_validation->required($user_guid) == FALSE)
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('user_guid_required');
            } elseif ($this->form_validation->required($LoginSessionKey) == FALSE)
            {
                $return['ResponseCode'] = 501;
                $return['Message'] = lang('not_authorized');
            } else
            {
                $this->user_model->deleteAccount($user_guid);
            }
        } else
        {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    public function getProfileSections_get()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        if (!empty($data['Section']) && !empty($data['Column']))
        {
            $return['Data'] = $this->user_model->getProfileSections($data['Section'], $data['Column'], $data['term']);
        } else
        {
            $return['Data'] = array();
        }
        $this->response($return);
    }

    /**
     * Function Name : userSessionHistory
     * @param  LoginSessionKey, UserID
     * @return Data[]
     * Description : get user session history
     */
    function userSessionHistory_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $Data = $this->post_data;

        ///Check the incoming data.
        if ($Data != NULL && isset($Data))
        {
            if (isset($Data[AUTH_KEY]) && !empty($Data[AUTH_KEY]))
            {
                $LoginSessionKey = $Data[AUTH_KEY];
            } else
            {
                $LoginSessionKey = '';
            }

            if ($Data != NULL && isset($Data['UserID']))
            {
                if (isset($Data['UserID']) && !empty($Data['UserID']))
                {
                    $user_id = $Data['UserID'];
                } else
                {
                    $user_id = '';
                }
                /// check the data for validation. 

                if ($this->form_validation->required($user_id) == FALSE)
                {/* Error -  UserID is required. */
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('userid_required');
                } elseif ($this->form_validation->required($LoginSessionKey) == FALSE)
                {/* Error -  UserID is required. */
                    $return['ResponseCode'] = 501;
                    $return['Message'] = lang('not_authorized');
                } else
                {
                    $history_res = $this->user_model->getUserSessionHistory($user_id);
                    $return['Data'] = $history_res;
                }
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: search_user_n_group
     
     * @param SearchKeyword,Hide
     * Description: Search list of friends/joined groups
     */
    public function search_user_n_group_get()
    {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* Define variables - ends */

        $validation_rule[] = array(
            'field' => 'SearchKeyword',
            'label' => 'Search Keyword',
            'rules' => 'trim|required'
        );

        $this->form_validation->set_data($data); // It is require for get method in CI3
        $this->form_validation->set_rules($validation_rule);

        /* Validation - starts */
        if ($this->form_validation->run() == FALSE)
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else
        {
            $search_keyword = $data['SearchKeyword'];
            $group_id = !empty($data['GroupGUID']) ? get_detail_by_guid($data['GroupGUID'], 1) : "";
            $formal = isset($data['Formal']) ? 0 : 1;
            $hide = isset($data['Hide']) ? $data['Hide'] : array();
            $remove_users = array();
            $remove_groups = array();

            if (!empty($hide))
            {
                foreach ($hide as $usr)
                {
                    if ($usr['ModuleID'] == 3)
                    {
                        $remove_users[] = $usr['ModuleEntityGUID'];
                    }
                    if ($usr['ModuleID'] == 1)
                    {
                        $remove_groups[] = $usr['ModuleEntityGUID'];
                    }
                }
            }
            $return['Data'] = $this->user_model->search_user_n_group($search_keyword, $user_id, $remove_users, $remove_groups, $group_id, $formal);
        }
        $this->response($return);
    }

    public function get_recent_conversations_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* Define variables - ends */

        $post_as_module_id = $data['PostAsModuleID'];
        $post_as_module_entity_guid = $data['PostAsModuleEntityGUID'];
        $search = isset($data['Search']) ? $data['Search'] : '';
        $post_as_module_entity_id = get_detail_by_guid($post_as_module_entity_guid, $post_as_module_id);

        $Filter                     = isset($data['Filter']) ? $data['Filter'] : 'ALL';        
        /*$Exclude                    = isset($data['Exclude']) ? $data['Exclude'] : array() ;            
        $Exclude[]                  = $post_as_module_entity_guid;                    */
        
        $Page = isset($data['PageNo']) ? $data['PageNo'] : '1' ;
        $Limit= isset($data['PageSize']) ? $data['PageSize'] : '10' ;//there must be a default limit 

        $return['Data'] = $this->user_model->get_recent_conversation($user_id, $post_as_module_id, $post_as_module_entity_id, $search);

        $this->response($return);
    }

    public function get_friends_and_group_for_invite_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 16;
        /* Define variables - ends */

        /* $post_as_module_id          = $data['PostAsModuleID'];
          $post_as_module_entity_guid = $data['PostAsModuleEntityGUID'];
          $search                     = isset($data['Search']) ? $data['Search'] : '' ;
          $post_as_module_entity_id   = get_detail_by_guid($post_as_module_entity_guid,$post_as_module_id); */

        $return['Data'] = $this->user_model->get_friends_and_group_for_invite($user_id, $page_no, $page_size);
        $this->response($return);
    }

    /**
     * [save_profile_post Update user profile ]
     * @return [json] [success / error message and response code]
     */
    function save_user_info_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data)
        {            
            unset($data[AUTH_KEY]);
            if (isset($data['Type']))
            {
                if ($data['Type'] == 'FirstName')
                {
                    $name['FirstName'] = $data['FirstName'];
                    $name['LastName'] = $data['LastName'];
                    $this->user_model->save_user_info($user_id, $name, USERS);
                    $this->session->set_userdata('FirstName', $name['FirstName']);
                    $this->session->set_userdata('LastName', $name['LastName']);
                    $display_name = '';
                    if ($name['FirstName'] != '')
                    {
                        $display_name = $name['FirstName'];
                        if ($name['LastName'] != '')
                        {
                            $display_name.=" " . $name['LastName'];
                        }
                    }
                    $this->session->set_userdata('DisplayName', $display_name);
                }
                //print_r(@$data);die;
                if ($data['Type'] == 'Username')
                {
                    $this->form_validation->set_rules('Username', 'Username', 'alpha_numeric');
                    if ($this->form_validation->run() == FALSE)
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
                    } 
                    else if (!$this->is_unique_value(@$data['Username'], PROFILEURL . '.Url.EntityID.EntityID.Username', $user_id))
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('username_already_exists');
                    } 
                    else
                    {
                        $username = $data['Username'];
                        $this->user_model->update_username($user_id, $username);
                    }
                }
                if ($data['Type'] == 'Email')
                {
                    if (!$this->is_unique_value(@$data['Email'], USERS . '.Email.StatusID.UserID.Email', $user_id))
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('email_exists');
                    } else
                    {
                        $email['Email'] = $data['Email'];
                        $this->user_model->save_user_info($user_id, $email, USERS);
                    }
                }
                if ($data['Type'] == 'DOB')
                {
                    $dob1 = $data['DOB'];

                     if (!empty($dob1))
                    {
                        $dob2 = explode('/', $dob1);
                        $dob['DOB'] = $dob2[2] . '-' . $dob2[0] . '-' . $dob2[1];
                    }
                    if (!isset($dob) && empty($dob))
                    {
                        $dob['DOB'] = '0000-00-00';
                    } 
                    $this->user_model->save_user_info($user_id, $dob);
                }
                if ($data['Type'] == 'Gender')
                {
                    $gender['Gender'] = $data['Gender'];
                    $this->user_model->save_user_info($user_id, $gender, USERS);
                }
                if ($data['Type'] == 'Location')
                {
                    $location = array();
                    $location['City'] = $data['City'];
                    $location['Country'] = $data['Country'];
                    $location['State'] = $data['State'];
                    $location['StateCode'] = $data['StateCode'];
                    $location['CountryCode'] = $data['CountryCode'];
                    $location_data = update_location($location);
                    $loc['CityID'] = $location_data['CityID'];
                    $loc['CountryID'] = $location_data['CountryID'];
                    $this->user_model->save_user_info($user_id, $loc);
                }
                if ($data['Type'] == 'HomeLocation')
                {
                    $location = array();
                    $location['City'] = $data['City'];
                    $location['Country'] = $data['Country'];
                    $location['State'] = $data['State'];
                    $location['StateCode'] = $data['StateCode'];
                    $location['CountryCode'] = $data['CountryCode'];
                    $location_data = update_location($location);
                    $loc['HomeCityID'] = $location_data['CityID'];
                    $loc['HomeCountryID'] = $location_data['CountryID'];
                    $this->user_model->save_user_info($user_id, $loc);
                }
                if ($data['Type'] == 'TimeZoneID')
                {
                    $timezone['TimeZoneID'] = $data['TimeZoneID'];
                    $this->user_model->save_user_info($user_id, $timezone);
                }
                if ($data['Type'] == 'RelationWithName')
                {
                    $relation['MartialStatus'] = $data['MartialStatus'];
                    $this->user_model->save_user_info($user_id, $relation);
                }
                if ($data['Type'] == 'UserWallStatus')
                {
                    $about['UserWallStatus'] = $data['UserWallStatus'];
                    $this->user_model->save_user_info($user_id, $about);
                }
                if ($data['Type'] == 'WorkExperience') {
                    $work_exp = array();
                    $work_exp['OrganizationName'] = $data['Organisation'];
                    $work_exp['Designation'] = $data['Designation'];
                    $work_exp['StartMonth'] = $data['StartMonth'];
                    $work_exp['StartYear'] = $data['StartYear'];
                    $work_exp['EndMonth'] = $data['EndMonth'];
                    $work_exp['EndYear'] = $data['EndYear'];
                    $work_exp['CurrentlyWorkHere'] = isset($data['CurrentlyWorkHere']) ? $data['CurrentlyWorkHere'] : '0';
                    if(isset($data['CurrentlyWorkingHere'])) {
                        $work_exp['CurrentlyWorkHere'] = $data['CurrentlyWorkingHere'];
                    }
                    $result = $this->user_model->update_work_experience($user_id, array($work_exp));
                }
                if ($data['Type'] == 'Education') {
                    $education = array();
                    $education['University'] = $data['University'];
                    $education['CourseName'] = $data['Course'];
                    $education['StartYear'] = $data['StartYear'];
                    $education['EndYear'] = $data['EndYear'];
                    $this->user_model->update_education($user_id, array($education));
                }
                if ($data['Type'] == 'SocialProfile') {
                    $social['FacebookUrl'] = $data['FB'];
                    $social['TwitterUrl'] = $data['Twitter'];
                    $social['LinkedinUrl'] = $data['LinkedIn'];
                    $social['GplusUrl'] = $data['GooglePlus'];
                    $this->user_model->save_user_info($user_id, $social);
                }
                if ($data['Type'] == 'UpdateProfile') {
                    $user['FirstName'] = $data['FirstName'];
                    $user['LastName'] = $data['LastName'];
                    $user['Email'] = $data['Email'];
                    $user['Gender'] = $data['Gender'];
                    
                    $userdetail['DOB'] = $data['DOB'];
                    $this->user_model->save_user_info($user_id, $userdetail);
                    if(isset($userdetail)){
                        $dob1 = $userdetail['DOB'];
                        if (!empty($dob1)) {
                           $dob2 = explode('/', $dob1);
                           $dob['DOB'] = $dob2[2] . '-' . $dob2[0] . '-' . $dob2[1];
                        }
                        if (!isset($dob) && empty($dob)) {
                           $dob['DOB'] = '0000-00-00';
                        } 
                        $this->user_model->save_user_info($user_id, $dob);
                    }
                }
            } 
            else {
                $this->user_model->save_user_info($user_id, $data);
                $return['Message'] = lang('profile_updated');
            }
            
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [interest_post Used to get user interest]
     * @return [json] [json object]
     */
    public function get_user_interest_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        if (isset($data['UserGUID'])) {
            $user_id = get_detail_by_guid($data['UserGUID'], 3);
        }

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : "";
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : "";
        $interestUserType = isset($data['InterestUserType']) ? $data['InterestUserType'] : 0 ;
        $return['Data'] = $this->user_model->get_user_interest($user_id, $page_no, $page_size, false, $interestUserType);

        $this->response($return);
    }

    /**
     * [get_profile_fields Used to get profile fields]
     * @return [json] [json object]
     */
    public function get_profile_fields_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $return['Data'] = $this->user_model->get_profile_fields();
        $this->response($return);
    }

    /**
     * [get_profile_field_questions Used to get profile fields question]
     * @return [json] [json object]
     */
    public function get_profile_field_questions_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $return['Data'] = $this->user_model->get_empty_profile_fields($user_id);
        $this->response($return);
    }

    public function update_last_date_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $type = $data['Type'];
        $this->user_model->update_last_date($user_id, $type);
        initiate_worker_job('profile_cache', array('user_id'=>$user_id ) );
        $this->response($return);
    }

    /**
     * [update_single_interest Used to update user interest]
     * @return [json] [json object]
     */
    public function update_single_interest_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $category_id = (int)$data['CategoryID'];
        $only_add = true;
        $only_remove = false;
        $hard_delete = FALSE;
        $user_id = !empty($data['UserID'])?$data['UserID']:'';
        if(empty($user_id)) {
            $user_id = $this->UserID;
        }
        else {
            $hard_delete = TRUE;
            $only_add = true;
            $only_remove = false;
        }       
        
        $action = $data['Action'];        
        if($action == 'add') {
            $only_add = true;
            $only_remove = false;
        } else if($action == 'remove') {
            $only_add = false;
            $only_remove = true;
        }
        
        $this->load->model('category/category_model');
        $interestUserType = isset($data['InterestUserType']) ? $data['InterestUserType'] : 1;
        $return['Data'] = $this->category_model->insert_update_interest(array($category_id), 3, $user_id, $interestUserType, $only_add, $only_remove, $hard_delete);
        $this->response($return);
    }

    public function get_interest_suggestions_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $keyword = $data['Keyword'];
        
        $user_id = !empty($data['UserID'])?$data['UserID']:'';
        if(empty($user_id)) {
            $user_id = $this->UserID;
        }
        
        $return['Data'] = $this->user_model->get_interest_suggestions($user_id, $keyword);
        $this->response($return);
    }

    public function get_city_suggestions_get()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $keyword = $data['Keyword'];
        $return['Data'] = $this->user_model->get_city_suggestions($user_id, $keyword);
        $this->response($return);
    }

    public function get_popular_interest_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 12;
        $keyword = isset($data['Keyword']) ? $data['Keyword'] : '' ;
        $exclude = isset($data['Exclude']) ? $data['Exclude'] : array() ;

        $return['Data'] = $this->user_model->get_popular_interest($user_id, $page_no, $page_size,$keyword,$exclude, 1);
        $this->response($return);
    }

    public function get_location_id_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $return['Data'] = update_location($data);
        $this->response($return);
    }

    public function entities_i_follow_post()
    {
        $Return = $this->return;
        $Data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 5;
        $user_id = isset($data['UserGUID']) ? get_detail_by_guid($data['UserGUID'], 3) : $user_id;        
        $Return['Data'] = $this->user_model->entities_i_follow($user_id, $page_no, $page_size);        
        
       // $Return['Data'] = $this->activity_model->sharePost($ModuleID, $ModuleEntityID, $UserID, $entity_type, $EntityID, $PostContent, $Commentable, $Visibility);
        $this->response($Return);
    }


    /**
     * [profile_new_post Used to get user prfile data]
     * @return [json] [user prfile data]
     */
    function profile_new_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $Data = $this->post_data;
        $current_user_id = $this->UserID;
        //$IsSettings = isset($Data['IsSettings']) ? $Data['IsSettings'] : 0 ;
        if (isset($Data))
        {
            $user_id = $current_user_id;
            if (isset($Data['UserID'])) {
                $user_id = $Data['UserID'];
            }

            if ($this->form_validation->required($user_id) == FALSE) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('not_authorized');
            } else {
                $return['Data'] = $this->user_model->profile_new($user_id, $current_user_id);
            }
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        //echo json_encode($return);
        $this->response($return); /* Final Output */
    }

    /**
     * [update_profile_new_post Updates user profile for workhigh which is a 4 step signup process]
     * @return [json] [success / error message and response code]
     */
    public function update_profile_new_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $error = FALSE;
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
                    $first_name = isset($data['FirstName']) ? trim($data['FirstName']) : '';
                    $last_name = isset($data['LastName']) ? trim($data['LastName']) : '';
                    $dob = isset($data['DOB']) ? $data['DOB'] : '';
                    $gender = isset($data['Gender']) ? $data['Gender'] : '';

                    if($profile_setup_step == '4' && (empty(trim($first_name)) || empty(trim($last_name))))
                    {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('input_invalid_format');
                    }
                    else
                    {
                        $official_email = isset($data['OfficialEmail']) ? $data['OfficialEmail'] : '';
                        $landline_office = isset($data['LandlineOffice']) ? $data['LandlineOffice'] : '';
                        if (!preg_match("/^[0-9-]+$/", $landline_office)) 
                        {
                            //echo 'only numbers and hyphens';
                            $error = TRUE;
                        }
                        $extention_number = isset($data['ExtensionNumber']) ? $data['ExtensionNumber'] : '';
                        $office_location = isset($data['OfficeLocation']) ? $data['OfficeLocation'] : '';
                        $employee_id = isset($data['EmployeeID']) ? $data['EmployeeID'] : '';
                        $date_of_joining = isset($data['DateOfJoining']) ? $data['DateOfJoining'] : '';
                        $skills = isset($data['Skills']) ? $data['Skills'] : array();
                        $about_me = isset($data['AboutMe']) ? $data['AboutMe'] : '';
                        $personal_email = isset($data['PersonalEmail']) ? $data['PersonalEmail'] : '';
                        $mobile_number = isset($data['MobileNumber']) ? $data['MobileNumber'] : '';
                        $home_landline = isset($data['HomeLandline']) ? $data['HomeLandline'] : '';
                        if (!preg_match("/^[0-9-]+$/", $home_landline)) 
                        {
                            //echo 'only numbers and hyphens';
                            $error = TRUE;
                        }
                        $pan = isset($data['PAN']) ? $data['PAN'] : '';
                        $blood_group = isset($data['BloodGroup']) ? $data['BloodGroup'] : '';
                        $permanent_address = isset($data['PermanentAddress']) ? $data['PermanentAddress'] : '';
                        $present_address = isset($data['PresentAddress']) ? $data['PresentAddress'] : '';
                        $emergency_contact_person = isset($data['EmergencyContactPerson']) ? $data['EmergencyContactPerson'] : '';
                        $emergency_contact_number = isset($data['EmergencyContactNumber']) ? $data['EmergencyContactNumber'] : '';
                        $relation_with_contact_person = isset($data['RelationWithContactPerson']) ? $data['RelationWithContactPerson'] : '';
                        
                        if($error)
                        {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = lang('input_invalid_format');
                        }
                        else
                        {
                            $this->user_model->update_profile_new($first_name, $last_name,$user_id,'3',$user_id,$profile_setup_step,$official_email,$landline_office,$extention_number,$office_location,$employee_id,$date_of_joining,$skills,$about_me,$personal_email,$mobile_number,$home_landline,$pan,$blood_group,$permanent_address,$present_address,$emergency_contact_person,$emergency_contact_number,$relation_with_contact_person,$gender,$dob);
                        }
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
     * [get_dummy_user_list Get the list of users of type dummy with their notification count ]
     * @return [json] [Json Array]
     */
    public function get_dummy_user_list_post()
    {
        $return = $this->return;
        
        $data = $this->post_data;
        $page_no = !empty($data['page_no']) ? $data['page_no'] : 1;
        $page_size = !empty($data['page_size']) ? $data['page_size'] : 11;
        $superAdminID = !empty($data['superAdminID']) ? $data['superAdminID'] : 0;
        $selectedUser = !empty($data['selectedUser']) ? $data['selectedUser'] : 0;
        
        $return['Data'] = $this->user_model->get_dummy_users($page_no, $page_size, false, $superAdminID, $selectedUser);
        
        $this->response($return);
    }
    
    
    /**
     * [get_latest_users Get the list of users who signed up recently ]
     * @return [json] [Json Array]
     */
    public function get_latest_users_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $return['Data'] = $this->user_model->get_latest_users($data, $user_id);
        $this->response($return);
    }
    
       /**
    * [update_user_email_post Update User's email and send activation link on it]
    * @return [json] [success or failure]
    */
    function update_user_email_post() {
        $data = $this->post_data;    
        $user_id = $this->UserID;
        $data['UserID'] = $user_id;
        
        $config = array(
            array(
                'field' => 'Email',
                'label' => 'email',
                'rules' => 'trim|required|valid_email'
            )
        );
        $this->form_validation->set_rules($config);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();         
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = $error; //Shows all error messages as a string
        } else if(!$this->is_unique_value($data['Email'], USERS . '.Email.StatusID.UserID.Email', $user_id)) {
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = lang('email_exists');
        } else  {
            $this->load->model('users/signup_model');
            $result = $this->signup_model->update_user_email($data);
            if($result) {
                $this->return['ResponseCode']   = self::HTTP_OK;
                $this->return['Message']        = lang('email_update_activation_email_success'); 
            } else {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = lang('input_invalid_format'); 
            }
        }
        $this->response($this->return); /* Final Output */
    }
    public function directory_post() {
        $data = $this->post_data;    
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 100;
        $search_keyword = isset($data['Keyword']) ? $data['Keyword'] : '';   
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'Recent';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'DESC';
        $is_admin = $this->user_model->is_super_admin($user_id, 1);
        if ($page_no == '1') {
            $total_records = $this->user_model->directory($page_no, $page_size, $search_keyword, $is_admin, 1, $user_id);
            $this->return['TotalRecords'] = $total_records; //number_format(intval($total_records));
            $this->return['IsAdmin'] = $is_admin;
        }
        
        $this->return['Data'] = $this->user_model->directory($page_no, $page_size, $search_keyword, $is_admin, 0, $user_id, $order_by, $sort_by);
               
        $this->response($this->return); 
    }
    
    /**
     * Function for change status of particular user.
     * Parameters : 3-delete, 4-block
     */
    public function change_status_post() {
        $data = $this->post_data;    
        $current_user_id = $this->UserID;
        if ($data) {
            
            $config = array(
                array(
                    'field' => 'UserID',
                    'label' => 'User ID',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'Status',
                    'label' => 'status',
                    'rules' => 'trim|required|in_list[3,4,2]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                //Set rights id by action(register,delete,blocked,waiting for approval users)                
                if(isset($data['Status']))  $status=$data['Status']; else $status= '';
               
                $is_super_admin = $this->user_model->is_super_admin($current_user_id, 1);
                if(!$is_super_admin){
                    $this->return['ResponseCode']= self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message']= lang('permission_denied');
                } else {
                    $user_id= $data['UserID'];
                    //Change status query for a user
                    $this->user_model->changeStatus($user_id, $status, $data);
                }
            }
                        
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
    
    /**
     * Function for make sub admin.
     * Parameters : 3-delete, 4-block
     */
    public function make_admin_post() {
        $data = $this->post_data;    
        $current_user_id = $this->UserID;
        if ($data) {
            
            $config = array(
                array(
                    'field' => 'UserID',
                    'label' => 'User ID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $is_super_admin = $this->user_model->is_super_admin($current_user_id, 1);
                $user_id= $data['UserID'];

                if(!$is_super_admin){
                    $this->return['ResponseCode']= self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message']= lang('permission_denied');
                } else {
                    //Change status query for a user
                    $this->user_model->make_admin($user_id);
                }
            }
                        
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
    
        /**
     * Function for make sub admin.
     * Parameters : 3-delete, 4-block
     */
    public function remove_admin_post() {
        $data = $this->post_data;    
        $current_user_id = $this->UserID;
        if ($data) {
            
            $config = array(
                array(
                    'field' => 'UserID',
                    'label' => 'User ID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE)
            {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $is_super_admin = $this->user_model->is_super_admin($current_user_id, 1);
                $user_id= $data['UserID'];

                if(!$is_super_admin){
                    $this->return['ResponseCode']= self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message']= lang('permission_denied');
                } else {
                    //Change status query for a user
                    $this->user_model->remove_admin($user_id);
                }
            }
                        
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
    
    /**
     * Used to update App version
     */
    public function update_app_version_post() {
        /* Define variables - starts */
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id        = $this->UserID;  
         if ($data) {            
            $config = array(
                array(
                    'field' => 'AppVersion',
                    'label' => 'App version',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'DeviceType',
                    'label' => 'Device type',
                    'rules' => 'trim|required|in_list[AndroidPhone,IPhone]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $device_type = $data['DeviceType'];
                $app_version = $data['AppVersion'];
                $device_type_id = $this->login_model->get_device_type_id($device_type);
                if($device_type_id) {
                    $this->user_model->update_app_version($user_id, $device_type_id, $app_version);
                }                
            }
         } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
}
