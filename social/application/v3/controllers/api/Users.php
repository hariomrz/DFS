<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for User module 
 * @category     Controller
 * @author       Vinfotech Team
 */
class Users extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->check_module_status(3);
    }

    /** added by gautam
     * [Used to update device id and device token]
     * @return [json] [return json boject]
     */
    public function update_device_id_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* Define variables - ends */
        /* Update data */
        $this->db->where('LoginSessionKey', $data[AUTH_KEY]);
        $this->db->update(ACTIVELOGINS, array('DeviceID' => $data['DeviceID'], 'DeviceToken' => $data['DeviceToken']));
        $this->db->limit(1);
        //echo $this->db->last_query();
        $this->response($return);
    }

    /**
     * check unique value
     * @access public
     * @param null
     */
    function is_unique_value($str, $fields, $user_id = 0) {
        list($table, $field, $select_field1, $where, $entity) = explode('.', $fields);

        $this->db->select($select_field1);
        $this->db->where(array($field => EscapeString($str)));
        $this->db->where($where . '!=' . $user_id, NULL, FALSE);

        if ($entity == 'Email' || $entity == 'PhoneNumber') {
            $this->db->where('StatusID!=3', NULL, FALSE);
        } else if ($entity == 'Username') {
            $this->db->join(USERS, USERS . '.UserID=' . PROFILEURL . '.EntityID', 'left');
            $this->db->where(USERS . '.StatusID!=3', NULL, FALSE);
        }

        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {
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
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {
                return TRUE;
            }
        }
    }

    /**
     * [profile_post Used to get user prfile data]
     * @return [json] [user prfile data]
     */
    function profile_post() {
        $return = $this->return;
        $data = $this->post_data;
        $current_user_id = $this->UserID;
        if (isset($data)) {
            $user_id = $current_user_id;
            if (isset($data['UserID']) && $data['UserID'] != '') {
                $user_id = $data['UserID'];
            } elseif (isset($data['UserGUID']) && $data['UserGUID'] != '') {
                $user_id = get_detail_by_guid($data['UserGUID'], 3);
            }

            $profile_url = !empty($data['ProfileURL']) ? $data['ProfileURL'] : '';
            if ($profile_url) {
                $this->load->model(array('users/login_model'));
                $row = $this->login_model->check_profile_url($profile_url);
                if (!empty($row['EntityType']) && $row['EntityType'] == 'User' && $row['StatusID'] == 2) {
                    $user_id = $_POST['UserID'] = $row['EntityID'];
                }
            }

            if ($this->form_validation->required($user_id) == FALSE) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('not_authorized');
            } else {
                $data['UserID'] = $user_id;
                $this->load->model(array('users/user_model','privacy/privacy_model','ward/ward_model'));
                $is_super_admin = $this->user_model->is_super_admin($user_id);
                $is_sub_admin = $this->user_model->is_super_admin($current_user_id, 1);
                
                if($is_super_admin || $is_sub_admin) {
                    $can_create_all_ward_post = 1;
                } else {
                    $can_create_all_ward_post = $this->user_model->is_super_admin($current_user_id, 2);
                }
                $return['Data'] = $this->user_model->profile($user_id, $current_user_id, $is_sub_admin);
                $return['Data']['IsFeatured'] = $this->ward_model->is_featured_user($data);
                $return['Data']['sd'] = 0;
                if(in_array($current_user_id, array(1, 1554, 10662, 11003, 12255, 13557, 14145, 14151, 14155))) {
                    //$return['Data']['sd'] = 1;
                }
                
                
                $return['Data']['UserID'] = $user_id;
                $return['Data']['IsAdmin'] = $is_sub_admin;
                $return['Data']['IsSuperAdmin'] = $is_super_admin;
                $return['Data']['aawp'] =  $can_create_all_ward_post;
                $return['Data']['cmsg'] =  $this->user_model->check_message_button_status($current_user_id, $user_id);
                $return['Data']['IsProfileSetup'] = $this->is_profile_setup;
                $return['Data']['sbdg'] =  0;

                $this->user_model->set_top_contributors(0);
                $top_contributors = $this->user_model->get_top_contributors();
                if(in_array($user_id, $top_contributors)) {
                    $return['Data']['sbdg'] =  1;
                }

                if(!$this->settings_model->isDisabled(11)) {
                    $this->load->model(array('follow/follow_model'));
                    $return['Data']['TFollowers'] =  $this->follow_model->followers(array('CountOnly' =>1, 'UserID' => $user_id));
                    $return['Data']['TFollowing'] =  $this->follow_model->following(array('CountOnly' =>1, 'UserID' => $user_id));
                    $return['Data']['IsFollow'] =   $this->follow_model->is_follow($current_user_id, $user_id);
                    $IsAdmin = $this->user_model->is_super_admin($user_id);
                    $this->load->model('activity/activity_model');
                    $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID));
                    if($IsAdmin == "1" || $user_id == $IsAdminGuid)
                        {
                            $return['Data']['IsFollow'] = 2;
                        }
                }

                //$return['Data']['LoggedInUserDefaultPrivacy'] = $this->privacy_model->get_default_privacy($this->UserID);
            }
        } else {
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
    function update_profile_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data) {

            $validation_rule = $this->form_validation->run('api/users/update_profile');

            if ($validation_rule == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->model(array('users/user_model'));
                //Define Variables
                $data['FullName'] = isset($data['FullName']) ? ucfirst(strtolower($data['FullName'])) : '';
                if (!empty($data['FullName'])) {
                    $first_name = strtok($data['FullName'], ' ');
                    $last_name = strstr($data['FullName'], ' ');
                } else {
                    $first_name = isset($data['FirstName']) ? ucfirst(strtolower($data['FirstName'])) : '';
                    $last_name = isset($data['LastName']) ? ucfirst(strtolower($data['LastName'])) : '';
                }

                $update_user_id = isset($data['UserID']) ? $data['UserID'] : '';
                $email          = isset($data['Email']) ? $data['Email'] : '';
                $about_me       = isset($data['AboutMe']) ? $data['AboutMe'] : '';
                $introduction   = isset($data['Introduction']) ? $data['Introduction'] : '';
                $expertise      = isset($data['Expertise']) ? $data['Expertise'] : '';
                $user_name      = isset($data['Username']) ? $data['Username'] : '';
                $facebook       = isset($data['Facebook']) ? $data['Facebook'] : '';
                $linkedin       = isset($data['LinkedIn']) ? $data['LinkedIn'] : '';
                $gmail_plus     = isset($data['GPlus']) ? $data['GPlus'] : '';
                $twitter        = isset($data['Twitter']) ? $data['Twitter'] : '';
                $work_experience = isset($data['WorkExperience']) ? $data['WorkExperience'] : '';
                $education      = isset($data['Education']) ? $data['Education'] : '';
                $gender         = isset($data['Gender']) ? $data['Gender'] : 0;
                $martial_status = isset($data['MartialStatus']) ? $data['MartialStatus'] : 0;
                $relation_with_guid = isset($data['RelationWithGUID']) ? $data['RelationWithGUID'] : '';
                $relation_with_name = isset($data['RelationWithName']) ? $data['RelationWithName'] : '';
                $timezone_id = isset($data['TimeZoneID']) ? $data['TimeZoneID'] : 0;
                $tagline = isset($data['Tagline']) ? $data['Tagline'] : '';

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

                    $date2 = date("Y-m-d"); //today's date

                    $date1 = new DateTime($dob);
                    $date2 = new DateTime($date2);
                    $interval = $date1->diff($date2);

                    $myage = $interval->y;
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
                $house_number = isset($data['HouseNumber']) ? $data['HouseNumber'] : '';
                $address = isset($data['Address']) ? $data['Address'] : '';
                $occupation = isset($data['Occupation']) ? $data['Occupation'] : '';

                if (!empty($update_user_id) && $update_user_id != $user_id) {
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if (!$is_super_admin) {
                        $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $this->return['Message'] = "You are not authorized to perform this action";
                        $this->response($return);
                    }
                    $user_id = $update_user_id;
                }

                //$this->load->model('users/user_model');
                $this->user_model->update_profile(
                        $first_name, $last_name, $email, $about_me, $location, $user_id, $expertise, $user_name, $gender, $martial_status, $dob, $timezone_id, $relation_with_id, $home_location, $relation_with_name, $introduction, '', $tagline, $house_number, $address, $occupation
                );

                //update profession
                $profession_id = safe_array_key($data, 'ProfessionID', 0);
                if(!empty($profession_id)) {
                    $this->user_model->save_user_profession($profession_id, $user_id);
                } else {
                    $this->user_model->remove_user_profession($profession_id,  $user_id);
                }
                
                

                //Update Logins Analytic
                if ($data['Loginsessionkey']) {
                    $age_group_id = $this->user_model->get_age_group_id($user_id);
                    $this->db->where('UserID', $user_id);
                    $this->db->where('LoginSessionKey', $data['Loginsessionkey']);
                    $this->db->order_by('AnalyticLoginID', 'DESC');
                    $this->db->limit(1);
                    $this->db->update(ANALYTICLOGINS, array('AgeGroupID' => $age_group_id));
                    if (CACHE_ENABLE) {
                        $this->cache->delete('rule_user_' . $user_id);
                    }
                }
                $return['Message'] = lang('profile_updated');
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [remove_profile_picture_post remove current profile picture of user]
     * @return [json] [json object]
     */
    function remove_profile_picture_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data) {            
            $this->load->model('upload_file_model');
            $module_id = 3;
            $module_entity_guid =  $this->LoggedInGUID;
            $this->upload_file_model->remove_profile_picture($user_id, $module_id, $module_entity_guid);               
            
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * [list_post Get list of following / followers / friends / users]
     * @return [json] [list of users]
     */
    public function list_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        $data = $this->post_data;
        $user_id = $this->UserID;
        $uid = $user_id;
        $type = 'Users';
        if (isset($data['Type'])) {
            $type = trim($data['Type']);
            if ($type == 'Request') {
                $type = 'Request';
            } else {
                $uid = isset($data['UID']) ? $data['UID'] : $user_id;
            }
        }

        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = isset($data['ModuleEntityID']) ? get_detail_by_guid($data['ModuleEntityID'], $module_id) : 0;

        $exclude_ids = isset($data['ExcludeID']) ? $data['ExcludeID'] : array();

        $search_key = '';
        if (isset($data['SearchKey'])) {
            $search_key = $data['SearchKey'];
        }

        if ($type == 'Users' && $search_key == '') {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Search Key is required.';
        } else {
            $page_no = PAGE_NO;
            $page_size = 500;
            if (isset($data['PageNo']) && $data['PageNo'] != '') {
                $page_no = $data['PageNo'];
            }
            if (isset($data['PageSize']) && $data['PageSize'] != '') {
                //$page_size = $data['PageSize'];
            }
            $this->load->model(array('users/friend_model'));
            $return['TotalRecords'] = 0;
            $ward_id = safe_array_key($data, 'WID', 0);
            if(empty($ward_id) && $this->LocalityID) {
                $this->load->model(array('locality/locality_model'));
                $localty = $this->locality_model->get_locality($this->LocalityID);
                if(!empty($localty["WID"])) {
                    $ward_id = $localty["WID"];
                }
            }
            if ($type == 'NewsFeedTagging') {                
                $return['Data'] = $this->friend_model->get_newsfeed_tagging_records($search_key, $user_id, $type, $page_no, $page_size, '', $uid, $module_id, $module_entity_id, array(), 0, $exclude_ids, $ward_id);
                //$return['TotalRecords'] = $this->friend_model->get_newsfeed_tagging_records($search_key, $user_id, $type, $page_no, $page_size, '', $uid, $module_id, $module_entity_id, array(), 1);
            } else {
                $return['Data'] = $this->friend_model->get_all_user($search_key, $user_id, $type, $page_no, $page_size, '', $uid, $module_id, $module_entity_id, array(), 0, '', $exclude_ids);
                $return['TotalRecords'] = $this->friend_model->get_total_users_count($search_key, $user_id, $type, $uid, $module_id, $module_entity_id);
            }

            $return['PageNo'] = $page_no;
            $return['PageSize'] = $page_size;
        }
        $this->response($return);
    }

    /**
     * [get_user_list_get similar to allUser but for handling jquery autocomplete request]
     * @return [json] [Json object]
     */
    public function get_user_list_get() {
        /* Define variables - starts */
        $return = $this->return;
        $search_key = '';
        $user_id = $this->UserID;
        $uid = $user_id;
        $selected_users = array();
        /* Define variables - ends */

        $search_key = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
        $show_friend = isset($_REQUEST['showFriend']) ? $_REQUEST['showFriend'] : 0;
        $selected_users = isset($_REQUEST['selectedUsers']) ? explode(',', $_REQUEST['selectedUsers']) : array();
        $type = 'Users';
        $page_no = PAGE_NO;
        $page_size = 500;
        $module_id = 3;
        $module_entity_id = $user_id;
        $ward_id = isset($_REQUEST['ward_id']) ? $_REQUEST['ward_id'] : 0;

        $exclude_ids = isset($data['ExcludeID']) ? $data['ExcludeID'] : array();


        $this->load->model('users/friend_model');
        $result = $this->friend_model->get_newsfeed_tagging_records($search_key, $user_id, $type, $page_no, $page_size, '', $uid, $module_id, $module_entity_id, array(), 0, $exclude_ids, $ward_id);
        if(isset($result['Members'])) {
            $return['Data'] = $result['Members'];
        }
         
        //$return['Data'] = $this->friend_model->get_user_list($search_key, $user_id, $show_friend, $selected_users);
        $this->response($return);
    }

    /**
     * [save_profile_post Update user profile ]
     * @return [json] [success / error message and response code]
     */
    function save_user_info_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        
        if ($data) {
            unset($data[AUTH_KEY]);
            $type  = $data['Type']  = safe_array_key($data, 'Type', 'BasicDetails');    
            if (isset($data['Type'])) {
                $this->load->model(array('users/user_model'));
                if ($data['Type'] == 'FirstName') {
                    $name['FirstName'] = $data['FirstName'];
                    $name['LastName'] = $data['LastName'];
                    $this->user_model->save_user_info($user_id, $name, USERS);
                    $this->session->set_userdata('FirstName', $name['FirstName']);
                    $this->session->set_userdata('LastName', $name['LastName']);
                    $display_name = '';
                    if ($name['FirstName'] != '') {
                        $display_name = $name['FirstName'];
                        if ($name['LastName'] != '') {
                            $display_name .= " " . $name['LastName'];
                        }
                    }
                    $this->session->set_userdata('DisplayName', $display_name);
                } elseif ($data['Type'] == 'Username') {
                    $this->form_validation->set_rules('Username', 'Username', 'alpha_numeric');
                    if ($this->form_validation->run() == FALSE) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
                    } else if (!$this->is_unique_value(@$data['Username'], PROFILEURL . '.Url.EntityID.EntityID.Username', $user_id)) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('username_already_exists');
                    } else {
                        $username = $data['Username'];
                        $this->user_model->update_username($user_id, $username);
                    }
                } elseif ($data['Type'] == 'Email') {
                    if (!$this->is_unique_value(@$data['Email'], USERS . '.Email.StatusID.UserID.Email', $user_id)) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('email_exists');
                    } else {
                        $email['Email'] = $data['Email'];
                        $this->user_model->save_user_info($user_id, $email, USERS);
                    }
                } elseif ($data['Type'] == 'DOB') {
                    $dob1 = $data['DOB'];
                    $dob['DOB'] = $this->user_model->format_dob($dob1);
                    $this->user_model->save_user_info($user_id, $dob);
                } elseif ($data['Type'] == 'Gender') {
                    $gender['Gender'] = $data['Gender'];
                    $this->user_model->save_user_info($user_id, $gender, USERS);
                } elseif ($type == 'Location') {
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
                } elseif ($data['Type'] == 'HomeLocation') {
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
                } elseif ($data['Type'] == 'TimeZoneID') {
                    $timezone['TimeZoneID'] = $data['TimeZoneID'];
                    $this->user_model->save_user_info($user_id, $timezone);
                } elseif ($data['Type'] == 'RelationWithName') {
                    $relation['MartialStatus'] = $data['MartialStatus'];
                    $this->user_model->save_user_info($user_id, $relation);
                } elseif ($data['Type'] == 'UserWallStatus') {
                    $this->form_validation->set_rules('UserWallStatus', 'about', 'required');
                    if ($this->form_validation->run() == FALSE) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
                    } else {
                        $about['UserWallStatus'] = $data['UserWallStatus'];
                        $this->user_model->save_user_info($user_id, $about);
                    }
                } elseif ($data['Type'] == 'TagLine') {
                    $this->form_validation->set_rules('TagLine', 'Tag Line', 'trim|required|max_length[60]');
                    if ($this->form_validation->run() == FALSE) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
                    } else {
                        $about['TagLine'] = $data['TagLine'];
                        $this->user_model->save_user_info($user_id, $about);
                    }
                } elseif ($data['Type'] == 'WorkExperience') {
                    $validation_rule = array(
                        array(
                            'field' => 'OrganizationName',
                            'label' => 'organization',
                            'rules' => 'trim|required|max_length[100]'
                        ),
                        array(
                            'field' => 'Designation',
                            'label' => 'designation',
                            'rules' => 'trim|required|max_length[100]'
                        ),
                        array(
                            'field' => 'StartYear',
                            'label' => 'start year',
                            'rules' => 'trim|required|integer|max_length[4]'
                        ),
                        array(
                            'field' => 'StartMonth',
                            'label' => 'start month',
                            'rules' => 'trim|required|integer|max_length[2]'
                        )
                    );
                    $currently_work_here = safe_array_key($data, 'CurrentlyWorkHere', 0);
                    /* if(!in_array($currently_work_here, array(0,1))) {
                      $currently_work_here = 0;
                      $data['CurrentlyWorkHere'] = $currently_work_here;
                      }
                     * 
                     */
                    if (empty($currently_work_here)) {
                        $validation_rule[] = array(
                            'field' => 'EndYear',
                            'label' => 'end year',
                            'rules' => 'trim|required|integer|max_length[4]'
                        );
                        $validation_rule[] = array(
                            'field' => 'EndMonth',
                            'label' => 'end month',
                            'rules' => 'trim|required|integer|max_length[2]'
                        );
                    }

                    $this->form_validation->set_rules($validation_rule);
                    if ($this->form_validation->run() == FALSE) {
                        $error = $this->form_validation->rest_first_error_string();
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = $error; //Shows all error messages as a string
                    } else {
                        $work_exp = array();
                        $work_exp['WorkExperienceGUID'] = safe_array_key($data, 'WorkExperienceGUID');
                        $work_exp['OrganizationName'] = $data['OrganizationName'];
                        $work_exp['Designation'] = $data['Designation'];
                        $work_exp['StartMonth'] = $data['StartMonth'];
                        $work_exp['StartYear'] = $data['StartYear'];
                        $work_exp['EndMonth'] = $data['EndMonth'];
                        $work_exp['EndYear'] = $data['EndYear'];
                        $work_exp['CurrentlyWorkHere'] = $currently_work_here;
                        $result = $this->user_model->update_work_experience($user_id, array($work_exp));
                    }
                } elseif ($data['Type'] == 'Education') {
                    $validation_rule = array(
                        array(
                            'field' => 'University',
                            'label' => 'university',
                            'rules' => 'trim|required|max_length[100]'
                        ),
                        array(
                            'field' => 'CourseName',
                            'label' => 'course',
                            'rules' => 'trim|required|max_length[100]'
                        ),
                        array(
                            'field' => 'StartYear',
                            'label' => 'start year',
                            'rules' => 'trim|required|integer|max_length[4]'
                        ),
                        array(
                            'field' => 'EndYear',
                            'label' => 'end year',
                            'rules' => 'trim|required|integer|max_length[4]'
                        )
                    );

                    $this->form_validation->set_rules($validation_rule);
                    if ($this->form_validation->run() == FALSE) {
                        $error = $this->form_validation->rest_first_error_string();
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = $error; //Shows all error messages as a string
                    } else {
                        $education = array();
                        $education['EducationGUID'] = safe_array_key($data, 'EducationGUID');
                        $education['University'] = $data['University'];
                        $education['CourseName'] = $data['CourseName'];
                        $education['StartYear'] = $data['StartYear'];
                        $education['EndYear'] = $data['EndYear'];
                        $this->user_model->update_education($user_id, array($education));
                    }
                } elseif ($data['Type'] == 'SocialProfile') {
                    $social['FacebookUrl'] = $data['FB'];
                    $social['TwitterUrl'] = $data['Twitter'];
                    $social['LinkedinUrl'] = $data['LinkedIn'];
                    $social['GplusUrl'] = $data['GooglePlus'];
                    $this->user_model->save_user_info($user_id, $social);
                } elseif ($data['Type'] == 'UpdateProfile') {
                    $user['FirstName'] = $data['FirstName'];
                    $user['LastName'] = $data['LastName'];
                    $user['Email'] = $data['Email'];
                    $user['Gender'] = $data['Gender'];

                    $userdetail['DOB'] = $data['DOB'];
                    $this->user_model->save_user_info($user_id, $userdetail);
                    if (isset($userdetail)) {
                        $dob1 = $userdetail['DOB'];
                        $dob['DOB'] = $this->user_model->format_dob($dob1);
                        $this->user_model->save_user_info($user_id, $dob);
                    }
                } elseif ($type == 'BasicDetails') {                 

                    $validation_rule = array(   
                        array(
                            'field' => 'UserGUID',
                            'label' => 'user guid',
                            'rules' => 'trim|required'
                        ),                     
                        array(
                            'field' => 'Gender',
                            'label' => 'gender',
                            'rules' => 'trim|required|in_list[1,2]'
                        ),
                        array(
                            'field' => 'DOB',
                            'label' => 'dob',
                            'rules' => 'trim|required|validate_date[m/d/Y]'
                        ),
                        array(
                            'field' => 'IsDOBApprox',
                            'label' => 'dob approx',
                            'rules' => 'trim|in_list[0,1]'
                        ),
                        array(
                            'field' => 'IncomeLevel',
                            'label' => 'income level',
                            'rules' => 'trim|required|in_list[1,2,3]'
                        )
                    );


                    $this->form_validation->set_rules($validation_rule);
                    if ($this->form_validation->run() == FALSE) {
                        $error = $this->form_validation->rest_first_error_string();
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = $error; //Shows all error messages as a string
                    } else {
                        $user_id = get_detail_by_guid($data['UserGUID'], 3);
                        if($user_id) {
                            $user_data['Gender']            = $data['Gender']; 
                            $user_data['AdminGender']       = $user_data['Gender'];
                        
                            $dob = $data['DOB'];
                            $dob = $this->user_model->format_dob($dob);

                            $profile_data['IncomeLevel']    = $data['IncomeLevel']; 
                            $profile_data['DOB']            = $dob;  
                            $profile_data['AdminDOB']       = $profile_data['DOB'];                      
                            $profile_data['IsDOBApprox']    = safe_array_key($data, 'IsDOBApprox', 0);    
                            
                            $this->user_model->save_user_info($user_id, $user_data, USERS);
                            $this->user_model->save_user_info($user_id, $profile_data, USERDETAILS);
                        
                            if (CACHE_ENABLE) {
                                $this->cache->delete('user_profile_' . $user_id);
                            }
                        } else {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = sprintf(lang('valid_value'), "user guid");
                        }
                    }
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
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
        } else if (!$this->is_unique_value($data['Email'], USERS . '.Email.StatusID.UserID.Email', $user_id)) {
            $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->return['Message'] = lang('email_exists');
        } else {
            $this->load->model('users/signup_model');
            $result = $this->signup_model->update_user_email($data);
            if ($result) {
                $this->return['ResponseCode'] = self::HTTP_OK;
                $this->return['Message'] = lang('email_update_activation_email_success');
            } else {
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = lang('input_invalid_format');
            }
        }
        $this->response($this->return); /* Final Output */
    }

    function directory_post() {
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 200);
        $search_keyword = safe_array_key($data, 'Keyword', '');
        $order_by = safe_array_key($data, 'OrderBy', 'Recent');
        $sort_by = safe_array_key($data, 'SortBy', 'DESC');
        $ward_id = safe_array_key($data, 'WID', 0);
        
        $this->load->model(array('users/user_model'));
        $is_admin = $this->user_model->is_super_admin($user_id, 1);
        if ($page_no == '1') {
            $total_records = $this->user_model->directory($page_no, $page_size, $search_keyword, $is_admin, 1, $user_id, $order_by, $sort_by, $ward_id);
            $this->return['TotalRecords'] = $total_records;
            $this->return['IsAdmin'] = $is_admin;
        }

        $this->return['Data'] = $this->user_model->directory($page_no, $page_size, $search_keyword, $is_admin, 0, $user_id, $order_by, $sort_by, $ward_id);

        $this->response($this->return);
    }


    function directory_tc_post() {
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 200);
        $search_keyword = safe_array_key($data, 'Keyword', '');
        $order_by = safe_array_key($data, 'OrderBy', 'Recent');
        $sort_by = safe_array_key($data, 'SortBy', 'DESC');
        $ward_id = safe_array_key($data, 'WID', 0);

        if($ward_id == 1) {
            $ward_id = 0;
        }
        
        $this->load->model(array('users/user_model'));
        $is_admin = $this->user_model->is_super_admin($user_id, 1);
        if ($page_no == '1') {
            $total_records = $this->user_model->directory_tc($page_no, $page_size, $search_keyword, $is_admin, 1, $user_id, $order_by, $sort_by, $ward_id);
            $this->return['TotalRecords'] = $total_records;
            $this->return['IsAdmin'] = $is_admin;
        }

        $this->return['Data'] = $this->user_model->directory_tc($page_no, $page_size, $search_keyword, $is_admin, 0, $user_id, $order_by, $sort_by, $ward_id);

        $this->response($this->return);
    }

    /**
     * Function for change status of particular user.
     * Parameters : 3-delete, 4-block
     */
    function change_status_post() {
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
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $this->load->model(array('users/user_model'));
                //Set rights id by action(register,delete,blocked,waiting for approval users)                
                if (isset($data['Status']))
                    $status = $data['Status'];
                else
                    $status = '';

                $is_super_admin = $this->user_model->is_super_admin($current_user_id, 1);
                if (!$is_super_admin) {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = lang('permission_denied');
                } else {
                    $user_id = $data['UserID'];
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
    function make_admin_post() {
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
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $this->load->model(array('users/user_model'));
                $is_super_admin = $this->user_model->is_super_admin($current_user_id, 1);
                $user_id = $data['UserID'];

                if (!$is_super_admin) {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = lang('permission_denied');
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
    function remove_admin_post() {
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
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $this->load->model(array('users/user_model'));
                $is_super_admin = $this->user_model->is_super_admin($current_user_id, 1);
                $user_id = $data['UserID'];

                if (!$is_super_admin) {
                    $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $this->return['Message'] = lang('permission_denied');
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
    function update_app_version_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
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
                $this->load->model(array('users/user_model', 'users/login_model'));
                $device_type = $data['DeviceType'];
                $app_version = $data['AppVersion'];
                $device_type_id = $this->login_model->get_device_type_id($device_type);
                if ($device_type_id) {
                    $this->user_model->update_app_version($user_id, $device_type_id, $app_version);
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
    
    /**
     * Used to update locality
     */
    function change_locality_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
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
            } else {                
                $locality_id = $data['LocalityID'];
                if($locality_id != $this->LocalityID) {
                    $this->load->model(array('users/user_model', 'locality/locality_model'));
                    $is_exist = $this->locality_model->is_locality_exist($locality_id);
                    if ($is_exist) {
                        $this->user_model->change_locality($user_id, $locality_id);
                        $this->return['Data'] = $this->locality_model->get_locality($locality_id);

                        $this->user_model->delete_mongo_db_record('active_user_login', array('UserID' => $user_id));
                    } else {
                        $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
                        $this->return['Message'] = lang('invalid_locality');
                    }
                }
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }

    function get_device_token_post() {
        $data = $this->post_data;
        $login_session_key = isset($data[AUTH_KEY]) ? $data[AUTH_KEY] : '';
        $this->return['Data']['DeviceToken'] = ''; 
        if(!empty($login_session_key)) {
            $this->load->model(array('users/user_model'));
            $row = $this->user_model->get_single_row("DeviceToken", ACTIVELOGINS, array('LoginSessionKey' => $login_session_key, 'IsValidToken' => 1));
            $this->return['Data']['DeviceToken'] = '';    
            if($row['DeviceToken']) {
                $this->return['Data']['DeviceToken'] = $row['DeviceToken'];
            }
        }
        $this->response($this->return);
    }

    function update_device_token_post() {
         $data = $this->post_data;
         if ($data) {
             $config = array(
                array(
                    'field' => 'DeviceToken',
                    'label' => 'device token',
                    'rules' => 'trim|required'
                )
             );
             $this->form_validation->set_rules($config);
             if ($this->form_validation->run() == FALSE) {
                 $error = $this->form_validation->rest_first_error_string();
                 $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                 $this->return['Message'] = $error;
             } else {   
                $login_session_key = isset($data[AUTH_KEY]) ? $data[AUTH_KEY] : '';
                $device_token =  $data['DeviceToken'];
                if(!empty($login_session_key)) {
                    $this->db->set('DeviceToken', $device_token);     
                    $this->db->set('IsValidToken', 1);                                  
                    $this->db->where('LoginSessionKey', $login_session_key);
                    $this->db->update(ACTIVELOGINS);
                }    
             }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }

    /**
     * [get list of vip user]
     * @return [array] [list of feature user]
     */
    function get_vip_user_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            /* $validation_rule = array(
                array(
                    'field' => 'WID',
                    'label' => 'ward ID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else { */
                $user_id = $this->UserID;
                $this->load->model(array('users/user_model'));
                $this->user_model->set_friend_followers_list($user_id);
                $data['UserID'] = $user_id;
                $return['Data'] = $this->user_model->get_vip_user($data);
           // }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [get list of association user]
     * @return [array] [list of feature user]
     */
    function get_association_user_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            /* $validation_rule = array(
                array(
                    'field' => 'WID',
                    'label' => 'ward ID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else { */
                $user_id = $this->UserID;
                $this->load->model(array('users/user_model'));
                $this->user_model->set_friend_followers_list($user_id);
                $data['UserID'] = $user_id;
                $return['Data'] = $this->user_model->get_association_user($data);
           // }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }


    /**
     * toggle_block_user used to block/unblock user
     */
    public function toggle_block_user_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if ($data) {
            $config = array(
                            array(
                                'field' => 'UserGUID',
                                'label' => 'user GUID',
                                'rules' => 'trim|required'
                            )
                        );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {
                $module_id = 3;
                $user_detail = get_detail_by_guid($data['UserGUID'], 3, 'UserID, FirstName, LastName', 2);
                
                if($user_detail && isset($user_detail['UserID'])) {
                    $block_user_name = $user_detail['FirstName'].' '.$user_detail['LastName'];
                    $block_user_id = $user_detail['UserID'];
                    $module_entity_id = $user_id;

                    $return['Message'] = sprintf(lang('block_user'), $block_user_name);
                    $this->load->model(array('users/user_model'));
                    $flag = $this->user_model->toggle_block_user($user_id, $block_user_id, $module_id, $module_entity_id);
                    if($flag == 1) {
                        $return['Message'] = sprintf(lang('unblock_user'), $block_user_name);
                    }        
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "user guid");
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: blocked_user_list_post
     * @param term
     * Description: Get list of blocked users
     */
    public function blocked_user_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $search_key = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 0;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $this->load->model(array('users/user_model'));
        $Data = $this->user_model->get_blocked_user_list($user_id, $search_key, $page_no, $page_size);
        $return['Data'] = $Data['Data'];
        $return['TotalRecords'] = $Data['total_records'];
        $this->response($return);
    }

    /**
     * Used to get preferred category
     */
    public function get_preferred_category_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $this->load->model(array('users/user_model'));
        $category_data = $this->user_model->get_preferred_category($user_id);
        $return['Data'] = $category_data;
        $this->response($return);
    }


    function save_preferred_categories_post() {
        
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $this->load->model(array('users/user_model'));
        $category_ids = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : [];
        if(API_VERSION == "v4"){
            $this->user_model->save_preferred_tags($user_id, $category_ids);
        } else {                        
            $this->user_model->save_preferred_categories($user_id, $category_ids);
        }
        
       
        $this->response($return);    
    } 
    
    function profession_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $this->load->model(array('users/user_model'));
        $return['Data'] = $this->user_model->profession_list($data);
        $this->response($return);  
    }
    
}