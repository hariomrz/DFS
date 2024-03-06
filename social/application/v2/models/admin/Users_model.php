<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Users_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    public function set_user_value($user_id, $key, $value) {
        $this->db->set($key, $value);
        $this->db->where('UserID', $user_id);
        $this->db->update(USERDETAILS);
        if (CACHE_ENABLE) {
                    $this->cache->delete('user_profile_' . $user_id);
        }
    }

    /**
     * Function for get users detail for Users listing
     * Parameters : start_offset, end_offset, start_date, end_date, user_status, search_keyword, sort_by, order_by
     * Return : Users array
     */
    public function getUsers($start_offset = 0, $end_offset = "", $start_date = "", $end_date = "", $user_status = "", $search_keyword = "", $sort_by = "", $order_by = "") {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $time_format = dateformat_php_to_mysql($global_settings['time_format']);

        $this->db->select('U.UserID AS userid', FALSE);
        $this->db->select('U.FirstName AS firstname', FALSE);
        $this->db->select('U.LastName AS lastname', FALSE);
        $this->db->select('U.Email AS email', FALSE);
        $this->db->select('CONCAT(U.FirstName, " ", U.LastName)AS username', FALSE);
        $this->db->select('U.ProfilePicture AS profilepicture', FALSE);
        //$this->db->select('UR.RoleID AS userroleid', FALSE);
        $this->db->select('UT.Name AS type', FALSE);
        $this->db->select('S1.IconUrl AS sourceicon', FALSE);
        $this->db->select('S2.Name AS sourcetype', FALSE);
        $this->db->select('DATE_FORMAT(U.CreatedDate, "' . $mysql_date . '") AS resgisdate', FALSE);
        $this->db->select('DATE_FORMAT(U.ModifiedDate, "' . $mysql_date . '") AS modifieddate', FALSE);
        $this->db->select('DATE_FORMAT(U.LastLoginDate, "' . $mysql_date . ' ' . $time_format . '") AS lastlogindate', FALSE);
        //$this->db->select('U.LastLoginDate AS lastlogindate', FALSE);
        $this->db->select('DATE_FORMAT(U.CreatedDate, "' . $mysql_date . '") AS membersince', FALSE);
        $this->db->select('U.UserGUID AS userguid', FALSE);
        $this->db->select('U.StatusID AS statusid', FALSE);

        /*$sub = $this->subquery->start_subquery('select');
       // $subquery = "(SELECT GROUP_CONCAT(UR.RoleID) FROM " . USERROLES . " UR WHERE UR.UserID = U.UserID ) as userroleid";
       // $this->db->select($subquery, FALSE);
                    
        $sub = $this->subquery->start_subquery('select');
        $sub->select('GROUP_CONCAT(UR.RoleID)', FALSE)->from(USERROLES . ' AS UR');
        $sub->where('UR.UserID = U.UserID');
        $this->subquery->end_subquery('userroleid');*/

        $this->db->join(USERTYPES . " AS UT", ' UT.UserTypeID = U.UserTypeID', 'right');
        $this->db->join(SOURCES . " AS S1", ' S1.SourceID = U.SourceID', 'right');
        $this->db->join(SOURCES . " AS S2", ' S2.SourceID = U.SourceID', 'right');
        //$this->db->join(USERROLES." AS UR", ' UR.UserID = U.UserID','inner');
        $this->db->from(USERS . "  U ");


        /* start_date, end_date for filters */
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(U.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
        }

        if (!empty($user_status)) {
            if ($user_status == 1) {
                $this->db->where_in('U.StatusID', array(1, 6));
            } else if ($user_status == 2) {
                $this->db->where_in('U.StatusID', array(2, 7));
            } else {
                $this->db->where('U.StatusID', $user_status);
            }
        }

        if (isset($search_keyword) && $search_keyword != '')
            $this->db->like('CONCAT(U.FirstName," ", U.LastName)', $search_keyword);


        $this->db->group_by('U.UserID');

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if ($sort_by == 'username' || $sort_by == '')
            $sort_by = 'FirstName';

        if ($order_by == false || $order_by == '')
            $order_by = 'ASC';

        if ($order_by == 'true')
            $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);


        $query = $this->db->get();
        //Here we are putting last query in session variable. Useful for download button
        /* $last_running_query = $this->db->last_query();
          $this->session->unset_userdata('download_query');
          $last_query_data = array('download_query' => $last_running_query);
          $this->session->set_userdata($last_query_data);
         */
        //Code End : Useful for download button

        $results['results'] = $query->result_array();
        
        foreach ($results['results'] as $key => $value) {
            # code...
            if(!empty($value['userid'])){
                $results['results'][$key]['userroleid'] = $this->getUserRoles($value['userid']);
            }

        }
        return $results;
    }
     

    /**
     * Function for get user detail for User Profile
     * Parameters : $user_id, $end_date, $end_date
     * Return : User array
     */
    public function getProfileInfo($user_id, $start_date, $end_date) {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);

        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
        }

        $this->db->select('U.UserID AS userid', FALSE);
        $this->db->select('U.FirstName AS firstname', FALSE);
        $this->db->select('U.LastName AS lastname', FALSE);
        $this->db->select('U.Email AS email', FALSE);
        $this->db->select('CONCAT(U.FirstName, " ", U.LastName)AS username', FALSE);
        $this->db->select('U.ProfilePicture AS profilepicture', FALSE);
        $this->db->select('DATE_FORMAT(U.CreatedDate, "' . $mysql_date . '") AS membersince', FALSE);
        $this->db->select('U.Location AS location', FALSE);
        $this->db->select('U.StatusID', FALSE);

        /* $sub = $this->subquery->start_subquery('select');
        $sub->select('COUNT(AL.AnalyticLoginID)', FALSE)->from(ANALYTICLOGINS . ' AS AL');
        $sub->where('AL.UserID = U.UserID and DATE(AL.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        $this->subquery->end_subquery('totallogincount');

        $sub_abuse = $this->subquery->start_subquery('select');
        $sub_abuse->select('COUNT(DISTINCT(MA.MediaID))', FALSE)->from(MEDIAABUSE . ' AS MA');
        $sub_abuse->join(MEDIA . " AS M", ' M.MediaID = MA.MediaID', 'inner');
        $sub_abuse->where('M.UserID = U.UserID and DATE(MA.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        $this->subquery->end_subquery('totalabusereport');

        $sub_image = $this->subquery->start_subquery('select');
        $sub_image->select('COUNT(M.MediaID)', FALSE)->from(MEDIA . ' AS M');
        $sub_image->where('M.UserID = U.UserID and StatusID !=3 and DATE(M.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        $this->subquery->end_subquery('totalpictures');
         * 
         */

        $this->db->from(USERS . " AS U ");
        $this->db->where('U.UserID', $user_id);

        $query = $this->db->get();
        $results = $query->row_array();
        
        
        $this->db->select('COUNT(AL.AnalyticLoginID) as totallogincount', FALSE)->from(ANALYTICLOGINS . ' AS AL');
        $this->db->where('AL.UserID = '.$user_id.' and DATE(AL.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        $query = $this->db->get();
        $analytics = $query->row_array();
        $results['totallogincount'] = $analytics['totallogincount'];
        
        
        $this->db->select('COUNT(DISTINCT(MA.MediaID)) as totalabusereport', FALSE)->from(MEDIAABUSE . ' AS MA');
        $this->db->join(MEDIA . " AS M", ' M.MediaID = MA.MediaID', 'inner');
        $this->db->where('M.UserID = '.$user_id.' and DATE(MA.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        $query = $this->db->get();
        $abusereport = $query->row_array();
        $results['totalabusereport'] = $abusereport['totalabusereport'];
        
        
        $this->db->select('COUNT(M.MediaID) as totalpictures', FALSE)->from(MEDIA . ' AS M');
        $this->db->where('M.UserID = '.$user_id.' and StatusID !=3 and DATE(M.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        $query = $this->db->get();
        $pictures = $query->row_array();
        $results['totalpictures'] = $pictures['totalpictures'];
        
        
        return $results;
    }

    /**
     * Function for get Login Graph Info and Device Type Info on User Profile
     * Parameters : $user_id, $start_date, $end_date
     * Return : Login Graph array
     */
    public function getLoginGraphInfo($user_id, $start_date, $end_date) {
        $graph_result = array();

        $this->db->select('COUNT(A.AnalyticLoginID) AS LoginCount', FALSE);
        $this->db->select('S.Name AS SourceName', FALSE);

        $this->db->join(SOURCES . " AS S", ' S.SourceID = A.LoginSourceID', 'left');
        $this->db->from(ANALYTICLOGINS . " AS A");

        /* start_date, end_date for filters */
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(A.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        }

        $this->db->where('A.UserID', $user_id);
        $this->db->group_by('A.LoginSourceID');

        $query = $this->db->get();
        $login_count_results = $query->result_array();


        /* SQL query for get DeviceTypesCount and DeviceName */
        $this->db->select('COUNT(A.DeviceTypeID) AS DeviceTypeCount', FALSE);
        $this->db->select('D.Name AS DeviceName', FALSE);

        $this->db->join(DEVICETYPES . " AS D", ' D.DeviceTypeID = A.DeviceTypeID', 'left');
        $this->db->from(ANALYTICLOGINS . " AS A");

        /* start_date, end_date for filters */
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(A.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        }

        $this->db->where('A.UserID', $user_id);
        $this->db->group_by('A.DeviceTypeID');

        $query = $this->db->get();
        $device_count_results = $query->result_array();

        //Assign Result in : GraphResult array
        $graph_result['LoginCount'] = $login_count_results;
        $graph_result['DeviceCount'] = $device_count_results;

        return $graph_result;
    }

    /**
     * Function for get IP's info for user profile page
     * Parameters : $user_id, $start_date, $end_date
     * Return : IPS array
     */
    public function getIpsInfo($user_id, $start_date, $end_date) {
        /* SQL query for get IP Addresses and it's count */
        $this->db->select('A.IPAddress', FALSE);
        
        $this->db->select('Count(A.IPAddress) AS IPAddressCount', FALSE);

//        $sub = $this->subquery->start_subquery('select');
//        $sub->select('COUNT(AL.IPAddress)', FALSE)->from(ANALYTICLOGINS . ' AS AL');
//        $sub->where('AL.IPAddress = A.IPAddress');
//        $sub->where('AL.UserID', $user_id);
//        $this->subquery->end_subquery('IPAddressCount');

        $this->db->from(ANALYTICLOGINS . " AS A");

        /* start_date, end_date for filters */
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(A.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '" ');
        }

        $this->db->where('A.UserID', $user_id);
        $this->db->group_by('A.IPAddress');

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        $query = $this->db->get();

        $ips_results = $query->result_array();
        $results['ips_results'] = $ips_results;
        return $results;
    }

    /**
     * Function for get download_query from session and return csv array for download file
     * Parameters : 
     * Return : Download file csv array
     */
    public function downloadCsv() {
        $SQL = $this->session->userdata('download_query');
        $results = $this->db->query($SQL)->result_array();

        /* Make Table Header */
        $csv = 'User Name, User Type, Email, Registered Date, Last Login, Type' . "\n";

        /* BUILD CSV ROWS */
        foreach ($results as $result) {
            $csv .= stripslashes($result['firstname'] . ' ' . $result['lastname']);
            $csv .= ',' . $result['type'];
            $csv .= ',' . $result['email'];
            $csv .= ',' . $result['resgisdate'];
            $csv .= ',' . $result['lastlogindate'];
            $csv .= ',' . $result['sourcetype'] . "\n";
        }
        return $csv;
    }

    /**
     * Function for get profile_info for a user
     * Parameters : userGUID
     * Return : User Information array
     */
    public function getSingleProfileInfo($user_guid) {
        $this->db->select('U.UserID, U.StatusID, GROUP_CONCAT(UR.RoleID) as RoleID, U.FirstName');
        $this->db->join(USERROLES . " AS UR", ' UR.UserID = U.UserID', 'inner');
        $this->db->from(USERS . " AS U");
        $this->db->where('U.UserGUID', $user_guid);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Function for change status of a user
     * Parameters : UserID, Status
     * Return : true
     */
    public function changeStatus($user_id, $status, $posted_data = array()) {
        $data = array('StatusID' => $status, 'ModifiedDate' => date('Y-m-d H:i:s'));
        
        $crm_filter = (!empty($posted_data['CRM_Filter'])) ? $posted_data['CRM_Filter'] : 0;
        $this->load->model(array('admin/users/crm_model'));
        if($crm_filter) $crm_query = $this->crm_model->get_users($posted_data, true);
        
        if($crm_filter) {
            $this->db->where("UserID IN ($crm_query) ", NULL, FALSE);
        } else {
            $this->db->where('UserID', $user_id);
        }
        
        if($status == 4) {
            $this->db->where('StatusID != 3', NULL, FALSE);
        }
        
        $this->db->update(USERS, $data);

        if ($status == 3) {
            $data = array('StatusID' => $status, 'ModifiedDate' => date('Y-m-d H:i:s'));
            
            $user_to_be_delete = $user_id;
            if($crm_filter) {
                $user_to_be_delete = $crm_query;
                $this->db->where("EntityID IN ($crm_query) ", NULL, FALSE);
            } else {
                $this->db->where('EntityID', $user_id);
            }
           
            $this->db->where('EntityType', 'User');
            $this->db->update(PROFILEURL, $data);
            
            $this->on_update_user_status($user_to_be_delete);
        }

        /* added by gautam starts */
        if ($status == 4) {
            if($crm_filter) {
                $this->db->where("UserID IN ($crm_query) ", NULL, FALSE);
                $this->db->delete(ACTIVELOGINS);
            } else {
                $this->db->delete(ACTIVELOGINS, array('UserID' => $user_id));
            }
        }
        /* added by gautam ends */
        return true;
    }
    
    public function on_update_user_status($user_to_be_delete) { 
        
        $this->db->select('GM.GroupID, GM.StatusID, GM.ModuleEntityID, GM.ModuleRoleID');
        $this->db->from(GROUPMEMBERS.' GM');        
        $this->db->where_not_in('GM.StatusID', array(3,14));
        $this->db->where('GM.ModuleID', 3);
        $this->db->where("GM.ModuleEntityID IN ($user_to_be_delete) ", NULL, FALSE);
        
        $query = $this->db->get();
        $groups = $query->result_array();
        
        $set_field = 'MemberCount';
        $count = -1;
                
        foreach ($groups as $group) {
            $group_id = $group['GroupID'];
            $module_entity_id = $group['ModuleEntityID'];
            $member_status_id = $group['StatusID'];
            $member_role = $group['ModuleRoleID'];
            
            //delete user from group
            $this->db->set('StatusID',3);
            $this->db->set('ModuleRoleID',6);
            $this->db->where('GroupID',$group_id);
            $this->db->where('ModuleID', 3);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->update(GROUPMEMBERS);
            $this->cache->delete('my_group_' . $module_entity_id);
            
            //delete user activity from this group
            $this->db->where('ActivityTypeID', '4');
            $this->db->where('ModuleID', '1');
            $this->db->where('ModuleEntityID', $group_id);
            $this->db->where('PostAsModuleEntityID', $module_entity_id);
            $this->db->where('PostAsModuleID', 3);
            $this->db->update(ACTIVITY, array('StatusID' => '3'));
            
            if($member_status_id == 2) {                                
                $this->db->set($set_field, "$set_field+($count)", FALSE);                
                $this->db->where("GroupID" ,$group_id);
                $this->db->where("MemberCount >" ,0);
                $this->db->update(GROUPS);
            }
            
            if ($member_role == 4) {
                $this->db->select_max('JoinedAt');
                $this->db->select('ModuleID,ModuleEntityID');
                $this->db->where('GroupID', $group_id);
                $this->db->where('StatusID', 2);
                $this->db->where('ModuleRoleID !=', 4);
                $this->db->where('ModuleID', 3);
                $this->db->having('ModuleEntityID IS NOT NULL');
                $query = $this->db->get(GROUPMEMBERS);

                // Make admin to second member who joined after creater
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $this->db->where('GroupID', $group_id);
                    $this->db->where('ModuleEntityID', $result['ModuleEntityID']);
                    $this->db->where('ModuleID', $result['ModuleID']);
                    $this->db->update(GROUPMEMBERS, array('ModuleRoleID' => 4));

                    $this->db->set('CreatedBy', $result['ModuleEntityID']);
                    $this->db->where('GroupID', $group_id);
                    $this->db->update(GROUPS);
                } else {
                    $this->db->where('GroupID', $group_id);
                    $this->db->update(GROUPS, array('StatusID' => 3));
                }
            }
        }
    }

    /**
     * Function for change password of a user
     * Parameters : UserID, Password
     * Return : true
     */
    public function changeUserPassword($user_id, $password) {
        $data = array('Password' => generate_password($password), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'IsPasswordChange' => '0');
        $this->db->where('UserID', $user_id);
        $this->db->update(USERLOGINS, $data);
        return true;
    }

    /**
     * Function for check admin record is exist or not
     * Parameters : UserID, Password
     * Return : true
     */
    public function checkAdminExist($old_password, $user_id) {
        $this->db->select('UserLoginID, Password');
        $this->db->from(USERLOGINS);
        $this->db->where('UserID', $user_id);
        //$this->db->where('Password', md5($old_password));
        $query = $this->db->get();
        if($query->num_rows()) {
            $UserData = $query->row_array();
            $existing_password = $UserData['Password'];
            if (!password_verify($old_password, $existing_password)) {
                return array();
            }
            return $UserData;
        }
        return array();
    }

    /**
     * Function for get user details by user id
     * Parameters : UserID
     * Return : array
     */
    public function getValueById($field_array, $user_id) {
        $this->db->select($field_array);
        $this->db->from(USERS);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Update multiple Users profile info
     * @param type $data
     * @param type $key
     */
    function updateMultipleUsersInfo($data, $key, $posted_data = array()) {
       
        
        
        $crm_filter = (!empty($posted_data['CRM_Filter'])) ? $posted_data['CRM_Filter'] : 0;
        $this->load->model(array('admin/users/crm_model'));
        if($crm_filter) $crm_query = $this->crm_model->get_users($posted_data, true);
        
        if($crm_filter) {
            $status = isset($posted_data['userstatus']) ? $posted_data['userstatus'] : 2;
            $data = array('StatusID' => $status, 'ModifiedDate' => date('Y-m-d H:i:s'));

            $this->db->where("UserID IN ($crm_query) ", NULL, FALSE);
            $this->db->update(USERS, $data);
        } else {
            $this->db->update_batch(USERS, $data, $key);
        }
        
    }

    /**
     * Function for update user profile information
     * Parameters : $data_arr, $user_id
     * Return : true
     */
    public function updateUserProfileInfo($data_arr, $user_id) {
        $data = $data_arr;
        $this->db->where('UserID', $user_id);
        $this->db->update(USERS, $data);
        return true;
    }

    /**
     * Function for get most active users detail for most active Users analytics
     * Parameters : start_offset, end_offset, start_date, end_date, sort_by, order_by
     * Return : Users array
     */
    public function getMostActiveUsers($start_offset = 0, $end_offset = "", $start_date = "", $end_date = "", $sort_by = "", $order_by = "") {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $activitypercentile = 100;

        $this->db->select('ROUND((((COUNT(SL.LoginSessionKey) * SUM(SL.Duration))/COUNT(DISTINCT DATE_FORMAT(SL.StartDate,"%Y-%m-%d")))/100),2) AS activitypercentile', FALSE);
        $this->db->join(SESSIONLOGS . " AS SL", ' U.UserID = SL.UserID', 'inner');
        $this->db->from(USERS . " AS U ");
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where(' DATE(SL.StartDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
        }
        $this->db->having('SUM(SL.Duration) > ', 0, FALSE);
        $this->db->group_by('U.UserID');
        $this->db->order_by('activitypercentile DESC');
        $this->db->limit('1');
        $sub_query = $this->db->get();
        $percentileArr = $sub_query->row_array();
        if (isset($percentileArr['activitypercentile'])) {
            $activitypercentile = $percentileArr['activitypercentile'];
        }

        $this->db->select('U.UserID AS userid', FALSE);
        $this->db->select('U.FirstName AS firstname', FALSE);
        $this->db->select('U.LastName AS lastname', FALSE);
        $this->db->select('U.Email AS email', FALSE);
        $this->db->select('CONCAT(U.FirstName, " ", U.LastName)AS username', FALSE);
        $this->db->select('U.ProfilePicture AS profilepicture', FALSE);
        $this->db->select('U.UserGUID AS userguid', FALSE);
        $this->db->select('U.StatusID AS statusid', FALSE);
        $this->db->select('U.Location AS location', FALSE);
        $this->db->select('COUNT(SL.LoginSessionKey) AS sessioncounts', FALSE);
        $this->db->select('SUM(SL.Duration) AS minutes', FALSE);
        $this->db->select('COUNT(DISTINCT DATE_FORMAT(SL.StartDate,"%Y-%m-%d")) AS days', FALSE);
        $this->db->select('ROUND((((COUNT(SL.LoginSessionKey) * SUM(SL.Duration))/COUNT(DISTINCT DATE_FORMAT(SL.StartDate,"%Y-%m-%d")))/' . $activitypercentile . '),2) AS activitypercentile', FALSE);
        $this->db->select('DATE_FORMAT(U.CreatedDate, "' . $mysql_date . '") AS membersince', FALSE);

        $this->db->join(SESSIONLOGS . " AS SL", ' U.UserID = SL.UserID', 'inner');
        $this->db->from(USERS . " AS U ");
        //$this->db->where("U.StatusID !=",3);


        /* start_date, end_date for filters */
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where(' DATE(SL.StartDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
        }

        $this->db->having('SUM(SL.Duration) > ', 0, FALSE);

        $this->db->group_by('U.UserID');

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();
        //$results['total_records'] = $tempdb->count_all_results();        

        /* Sort_by, Order_by */
        if ($sort_by == 'username' || $sort_by == '')
            $sort_by = 'FirstName';

        if ($order_by == false || $order_by == '')
            $order_by = 'ASC';

        if ($order_by == 'true')
            $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);


        /* Start_offset, end_offset */
        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);


        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results['results'] = $query->result_array();
        return $results;
    }

    /**
     * Function for change update_profile_field
     * Parameters : $field_guid, $data
     * Return : true
     */
    public function update_profile_field($field_guid, $data) {

        $this->db->where('FieldGUID', $field_guid);
        $this->db->update(PROFILEFIELDS, $data);
        return true;
    }

    /**
     * Function for change status of a user
     * Parameters : UserID, Status
     * Return : true
     */
    public function set_profile_field_priority_order($field_guid = array()) {
        foreach ($field_guid as $key => $value) {
            $this->db->where('FieldGUID', $value['FieldGUID']);
            $this->db->update(PROFILEFIELDS, array('PriorityOrder' => $key + 1));
        }

        return true;
    }
    
    /**
     * [get_week_day_id Used to get week day id]
     * @param  [string] $day [day name]
     * @return [int]         [day id]
     */
    function get_week_day_id($day) {
        $this->db->where('Name', $day);
        $this->db->limit('1');
        $week = $this->db->get(WEEKDAYS);
        if ($week->num_rows()) {
            $result = $week->row();
            return $result->WeekdayID;
        }
        return '';
    }

    function get_time_slot() {
        $d = date('H i');
        $d = explode(" ", $d);
        $min = $d[1] / 60;
        $dec = $d[0] + $min;

        $query = $this->db->query("SELECT TimeSlotID FROM TimeSlots WHERE '" . $dec . "' BETWEEN ValueRangeFrom AND ValueRangeTo");
        if ($query->num_rows()) {
            return $query->row()->TimeSlotID;
        } else {
            return '';
        }
    }

    function set_profile_url($profile_url_data) {
        if (!empty($profile_url_data)) {
            $this->db->insert_on_duplicate_update_batch(PROFILEURL, $profile_url_data);
        }
    }

    function get_users($page_no = 1, $page_size = 11, $only_users = false, $search = '', $is_dummy = true) {
        $users = [];
        $this->load->model(array(
            'notification_model',
            'messages/messages_model'
        ));

        $this->db->select("CONCAT(U.FirstName,' ',U.LastName) as Name, U.UserGUID as ModuleEntityGUID, '3' as ModuleID", false);
        $this->db->select("U.UserID as ModuleEntityID, U.ProfilePicture, PU.Url AS ProfileURL", false);
        $this->db->from(USERS . ' U');
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID AND PU.EntityType = 'User' ", "LEFT");

        if ($is_dummy) {
            $this->db->where('U.UserTypeID', 4);
            $this->db->where('U.StatusID', 2);
        }


        if ($search) {
            $search = $this->db->escape_like_str($search);
            $this->db->where("(U.FirstName LIKE '%" . $search . "%' OR U.LastName LIKE '%" . $search . "%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%" . $search . "%')", NULL, FALSE);
        }

        if ($page_no && $page_size) {
            $this->db->limit($page_size, get_pagination_offset($page_no, $page_size));
        }



        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($query->num_rows()) {
            $users = $query->result_array();
        }

        if ($only_users) {
            return $users;
        }

        foreach ($users as $index => $user) {
            $user['TotalNotificationRecords'] = (int) $this->notification_model->get_new_notifications($user['ModuleEntityID'], 0, 0, true);
            $user['TotalMessageRecords'] = (int) $this->messages_model->get_total_unseen_count($user['ModuleEntityID']);

            $user['TotalNotificationRecords'] = $user['TotalNotificationRecords'] + $user['TotalMessageRecords'];
            $users[$index] = $user;
        }

        return $users;
    }

    /**
     * [dummy_uses Used to get dummy user list]
     * @param  [int]    $page_no   [Page number]
     * @param  [int]    $page_size [Page Size]
     * @param  [string] $sort_by   [Sort by]
     * @param  [string] $order_by  [Order By]
     * @return [array]             [User details]
     */
    public function dummy_users($page_no = 1, $page_size = 10, $sort_by = 'U.CreatedDate', $order_by = 'DESC', $count_only = false) {
        $this->load->model('notification_model');
        $this->db->select("U.FirstName,U.LastName,U.Email,U.Gender,U.UserID,U.UserGUID,U.CreatedDate");

        $this->db->select('IF(U.ProfilePicture="","",U.ProfilePicture) as ProfilePicture', FALSE);
        $this->db->select('IFNULL(UD.DOB,"") as DOB', FALSE);
        $this->db->select('P.Url as ProfileURL', FALSE);

        $this->db->select("(SELECT COUNT(FR.FriendID) FROM " . FRIENDS . " FR WHERE FR.UserID=U.UserID AND FR.Status='1') as TotalFriends", false);
        $this->db->select("(SELECT COUNT(F.UserID) FROM " . FOLLOW . " F WHERE F.TypeEntityID=U.UserID AND F.Type='User' AND F.StatusID='2') as TotalFollowers", false);
        $this->db->select("(SELECT COUNT(A.UserID) FROM " . ACTIVITY . " A WHERE A.PostAsModuleID='3' AND U.UserID=A.PostAsModuleEntityID AND A.StatusID='2' AND A.ActivityTypeID NOT IN (3, 4, 6, 13, 17, 18, 19, 20, 21, 22)) as TotalPosts", false);
        $this->db->select("(SELECT COUNT(PC.UserID) FROM " . POSTCOMMENTS . " PC WHERE PC.PostAsModuleID='3' AND U.UserID=PC.PostAsModuleEntityID AND PC.StatusID='2') as TotalComments", false);
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        if (!$count_only) {
            $this->db->limit($page_size, get_pagination_offset($page_no, $page_size));
        }
        $this->db->where('U.UserTypeID', '4');
        $this->db->where('U.StatusID!=', '3');
        $this->db->order_by($sort_by, $order_by);
        $query = $this->db->get();
        if ($count_only) {
            return $query->num_rows();
        }
        $user_data = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $user) {
                $dob = $user->DOB;
                if (!empty($dob) && $dob != "0000-00-00") {
                    $dob = explode('-', $dob);
                    $user->DOB = $dob[1] . '/' . $dob[2] . '/' . $dob[0];
                }
                $user->CreatedDateFormat = get_current_date("%d %M %Y", 0, 0, strtotime($user->CreatedDate));
                $user->Location = $this->get_user_location($user->UserID);
                $user->TotalNotificationRecords = (int) $this->notification_model->get_new_notifications($user->UserID, 0, 0, true);
                $user->ContentTypes = $this->get_post_permission_for_newsfeed($user->UserID);
                $user_data[] = $user;
            }
        }
        return $user_data;
    }

    public function update_profile_picture($user_id, $media_guid, $is_frontend = true, $UserGUID = "") {
        $this->db->select('ImageName');
        $this->db->from(MEDIA);
        $this->db->where('MediaGUID', $media_guid);
        $media = $this->db->get();
        if ($media->num_rows()) {
            $row = $media->row_array();
            if (!$is_frontend)
                $this->db->set('AdminProfilePicture', $row['ImageName']);
            else
                $this->db->set('ProfilePicture', $row['ImageName']);
            $this->db->where('UserID', $user_id);
            $this->db->update(USERS);

            $this->db->set('MediaSectionID', '1');
            $this->db->set('MediaSectionReferenceID', $user_id);
            $this->db->where('MediaGUID', $media_guid);
            $this->db->update(MEDIA);

            if (isset($UserGUID) && $UserGUID != "") {
                $this->load->model(array('upload_file_model'));
                $this->upload_file_model->updateProfilePicture($media_guid, $row['ImageName'], $user_id, 3, $UserGUID);
            }
        }
    }

    /**
     * [createAccount Create dummy User]
     * @param  [array] $Data [User details]
     * @return [array]       [Response details]
     */
    public function createAccount($Data) {
        $return['ResponseCode'] = 200;
        $return['Message'] = lang('success');
        $return['Data'] = array();
        $CreatedDate = get_current_date('%Y-%m-%d %H:%i:%s');

        /* Registration insert to user table (StatusID can be changed according to project requirements)-starts */
        $UserGUID = get_guid(); /* Create new User GuID */

        if ($Data['Password'] == '') {
            $Data['Password'] = strrev($UserGUID);
        }

        if ($Data['Username'] == '') {
            $Data['Username'] = $UserGUID;
        }

        $StatusID = 2;
        $ReferralID = 0;

        $ResolutionID = 1;

        $Data['ResolutionID'] = $ResolutionID;
        //Get data and set variables
        $this->load->helper('location');
        $LocationData = update_location($Data['Location']);
        $Data['CityID'] = $LocationData['CityID'];
        $Data['CountryID'] = $LocationData['CountryID'];
        $Data['Latitude'] = '';
        $Data['Longitude'] = '';


        $this->load->model('timezone/timezone_model');
        $TimeZoneID = $this->timezone_model->get_time_zone_id($Data['Latitude'], $Data['Longitude']);
        //Get weekday id and time slot id
        $WeekDayID = $this->get_week_day_id(date('l'));
        $TimeSlot = $this->get_time_slot();
        if (isset($Data['UserTypeID']) && $Data['UserTypeID'] != '')
            $UserTypeID = $Data['UserTypeID'];
        else
            $UserTypeID = $Data['UserTypeID'] = '3';


        //If user registered from social login then grab his profile picture from his account
        $ProfilePicture = '';

        $InsertUser['UserGUID'] = $UserGUID;
        $InsertUser['UserTypeID'] = 4;
        $InsertUser['FirstName'] = EscapeString($Data['FirstName']);
        $InsertUser['LastName'] = EscapeString($Data['LastName']);
        $InsertUser['Email'] = EscapeString($Data['Email']);
        $InsertUser['SourceID'] = $Data['SourceID'];
        $InsertUser['DeviceTypeID'] = $Data['DeviceTypeID'];
        $InsertUser['WeekDayID'] = $WeekDayID;
        $InsertUser['TimeSlotID'] = $TimeSlot;
        $InsertUser['CreatedDate'] = $CreatedDate;
        $InsertUser['StatusID'] = $StatusID;
        $InsertUser['LastLoginDate'] = $CreatedDate;
        $InsertUser['EmailNotification'] = 0;
        $InsertUser['Latitude'] = $Data['Latitude'];
        $InsertUser['IPAddress'] = $Data['IPAddress'];
        $InsertUser['ReferrerTypeID'] = $ReferralID;
        $InsertUser['PhoneNumber'] = '';
        $InsertUser['Longitude'] = $Data['Longitude'];
        $InsertUser['ProfilePicture'] = $ProfilePicture;
        $InsertUser['Gender'] = $Data['Gender'];

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
            $return['Data']['UserID'] = $user_id;
            $return['Data']['UserGUID'] = $UserGUID;

            if (isset($Data['UserMediaGUID']) && $Data['UserMediaGUID']) {
                $this->update_profile_picture($user_id, $Data['UserMediaGUID'], true, $UserGUID);
            } else {
                $this->update_profile_picture($user_id, $Data['MediaGUID'], False, $UserGUID);
            }

            /* Insert login information - starts */
            $SetPassword = 1;
            if ($Data['Password'] == '') {
                $SetPassword = 0;
            }

            $UserLoginData['UserID'] = $user_id;
            $UserLoginData['LoginKeyword'] = EscapeString($Data['Username']);
            $UserLoginData['Password'] = generate_password($Data['Password']);
            $UserLoginData['SourceID'] = $Data['SourceID'];
            $UserLoginData['CreatedDate'] = $CreatedDate;
            $UserLoginData['ModifiedDate'] = $CreatedDate;
            $UserLoginData['SetPassword'] = $SetPassword;
            $UserLoginData['ProfileURL'] = isset($Data['profileUrl']) ? $Data['profileUrl'] : '';
            $UserLoginData['MediaID'] = isset($MediaID) ? $MediaID : NULL;

            $this->db->insert(USERLOGINS, $UserLoginData);
            /* Insert login information - ends */

            /*  User role entry start here */
            $this->db->insert(USERROLES, array('UserID' => $user_id, 'RoleID' => $Data['Role']));
            /*  User role entry end here */

            /*  User Detail entry start here */
            //$profilename=$UserName;
            $profilename = '';
            $this->db->insert(USERDETAILS, array('RelationWithName' => '', 'UserID' => $user_id, 'ProfileName' => $profilename, 'UserWallStatus' => '', 'CityID' => $Data['CityID'], 'TimeZoneID' => $TimeZoneID, 'DOB' => $Data['DOB'], 'CountryID' => $Data['CountryID']));

            /*  User role entry end here */

            // Add privacy settings to low
            $this->privacy_model->save($user_id, 'low');

            $this->load->model('notification_model');
            $this->notification_model->set_all_notification_on($user_id);

            /* Insert User Profile URL Start */
            if (empty($Data['Username'])) {
                $Data['Username'] = $UserGUID;
            }

            $profileUrlData[] = array(
                'EntityType' => 'User',
                'EntityID' => $user_id,
                'Url' => ($Data['SourceID'] != 1) ? $UserGUID : EscapeString($Data['Username']),
                'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
            );
            $this->set_profile_url($profileUrlData);
            /* Insert User Profile URL End */
            //Create Default album
            create_default_album($user_id, 3, $user_id);

            // If dummy user then assign tags to this user.
            if ($Data['UserTypeID'] == 4) {
                $this->load->model(array('tag/tag_model'));
                $this->tag_model->assign_or_update_tags_to_dummy_users(NULL, [array(
                'ModuleEntityID' => $user_id
                )]);
            }
        } else {
            $return['ResponseCode'] = 513;
            $return['Message'] = lang('record_not_added');
        }
        return $return;
    }

    /**
     * [updateAccount Update dummy user list]
     * @param  [int] $user_id [User ID]
     * @param  [array] $data    [User details]
     * @return [array]          [response details]
     */
    public function updateAccount($user_id, $data) {
        $return['ResponseCode'] = 200;
        $return['Message'] = lang('success');
        $return['Data'] = array();

        $this->load->helper('location');
        $LocationData = update_location($data['Location']);
        $data['CityID'] = $LocationData['CityID'];
        $data['CountryID'] = $LocationData['CountryID'];

        $this->db->set('FirstName', $data['FirstName']);
        $this->db->set('LastName', $data['LastName']);
        $this->db->set('Gender', $data['Gender']);
        $this->db->set('Email', $data['Email']);
        $this->db->where('UserID', $user_id);
        $this->db->update(USERS);

        $this->db->set('DOB', $data['DOB']);
        $this->db->set('CityID', $data['CityID']);
        $this->db->set('CountryID', $data['CountryID']);
        $this->db->where('UserID', $user_id);
        $this->db->update(USERDETAILS);

        if (isset($data['UserMediaGUID']) && $data['UserMediaGUID']) {
            $this->update_profile_picture($user_id, $data['UserMediaGUID'], true, $data['UserGUID']);
        } else {
            $this->update_profile_picture($user_id, $data['MediaGUID'], False, $data['UserGUID']);
        }

        if (CACHE_ENABLE) {
            $this->cache->delete('user_profile_' . $user_id);
        }
        $return['Data']['UserID'] = $user_id;
        return $return;
    }

    /**
     * [get_user_location Used to get user location information]
     * @param  [int] $user_id [User ID]
     * @return [array]       [Array of User location information]
     */
    function get_user_location($user_id, $home = 0) {
        $this->db->select('IFNULL(S.Name,"") as StateName', FALSE);
        $this->db->select('IFNULL(S.ShortCode,"") as StateCode', FALSE);
        $this->db->select('IFNULL(CT.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(C.CountryName,"") as CountryName', FALSE);
        $this->db->select('IFNULL(C.CountryCode,"") as CountryCode', FALSE);
        $this->db->select('IFNULL(UD.Address,"") as Address', FALSE); /* added by gautam */
        $this->db->from(USERDETAILS . ' UD');
        if ($home) {
            $this->db->join(CITIES . ' CT', 'CT.CityID = UD.HomeCityID', 'left');
            $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.HomeCountryID', 'left');
        } else {
            $this->db->join(CITIES . ' CT', 'CT.CityID = UD.CityID', 'left');
            $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = UD.CountryID', 'left');
        }
        $this->db->join(STATES . ' S', 'CT.StateID = S.StateID', 'left');
        $this->db->where('UD.UserID', $user_id);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $row = $query->row();
            $city = trim($row->CityName);
            $State = trim($row->StateName);
            $StateCode = trim($row->StateCode);
            $Country = trim($row->CountryName);
            $CountryCode = trim($row->CountryCode);
            $Address = trim($row->Address);
            $Location = '';
            if (!empty($city) && $city != null) {
                $city = ucfirst(strtolower($city));
                $Location .= $city . ', ';
            }
            if (!empty($State) && $State != null) {
                $Location .= $State . ', ';
            } else if (!empty($StateCode) && $StateCode != null) {
                $StateCode = strtoupper($StateCode);
                $Location .= $StateCode . ', ';
            }
            if (!empty($Country) && $Country != null) {
                $Country = ucfirst(strtolower($Country));
                $Location .= $Country . ', ';
            }
            if ($Location) {
                $Location = substr($Location, 0, -2);
                if ($Location == '-') {
                    $Location = '';
                }
            }
            return array('City' => $city, 'State' => $State, 'Country' => $Country, 'Location' => $Location, 'StateCode' => $StateCode, 'CountryCode' => $CountryCode, 'Address' => $Address);
        }
    }

    function getUserDetails($user_id) {
        $details = get_detail_by_id($user_id, 3, 'UserGUID,FirstName,LastName,ProfilePicture', 2);

        $data = array('FirstName' => '', 'LastName' => '', 'ProfilePicture' => '', 'ProfileURL' => '', 'ModuleID' => 0, 'ModuleEntityGUID' => 0);

        $data['FirstName'] = $details['FirstName'];
        $data['LastName'] = $details['LastName'];
        $data['Name'] = $details['FirstName'] . ' ' . $details['LastName'];
        $data['ProfilePicture'] = $details['ProfilePicture'];
        $data['ProfileURL'] = get_entity_url($user_id, "User", 1);
        $data['UserID'] = $user_id;
        $data['UserGUID'] = $details['UserGUID'];

        return $data;
    }

    function delete_dummy_user($user_id) {
        $this->db->set('StatusID', '3');
        $this->db->where('UserID', $user_id);
        $this->db->update(USERS);
    }

    /**
     * [get_previous_profile_pictures Used to get all the Previously uploaded Profile Pictures of given module_entity_id based on module_id]
     * @param  [int] $module_id         [Module ID]
     * @param  [int] $module_entity_id  [ModuleEntity ID]
     * @param  [int] $page_no           [Page Number]
     * @param  [int] $page_size         [Page Size]
     * @return [array]                  [array of Profile Pictures]
     */
    function get_previous_profile_pictures($module_id, $module_entity_id, $page_no = 1, $page_size = 16) {
        $image = array();
        $this->db->select('MediaGUID,ImageName');
        $this->db->where('MediaSectionID', '1');
        $this->db->where('ModuleID', $module_id);
        $this->db->where('MediaSectionReferenceID', $module_entity_id);
        $this->db->where('StatusID', '2');
        $this->db->order_by('MediaID', 'DESC');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get(MEDIA);
        if ($query->num_rows()) {            
            foreach ($query->result_array() as $img) {
                if (!empty($img)) {
                    $image[] = $img;
                }
            }
        }
        return $image;
    }

    function get_all_interests($user_id = 0, $interestUserType = 0) {
        
        $interestUserType = $this->db->escape_str($interestUserType);

        if ($user_id) {
            $interestUserTypeWhere = '';
            if ($interestUserType) {
                $interestUserTypeWhere = " AND ModuleEntityUserType = $interestUserType ";
            }
            $select_interested = "IF((SELECT CategoryID FROM " . ENTITYCATEGORY . " WHERE  ModuleID='3' $interestUserTypeWhere AND ModuleEntityID='" . $user_id . "' AND CategoryID=C.CategoryID) is not NULL,1,0) as IsInterested";
        } else {
            $select_interested = "'0' as IsInterested";
        }

        $this->db->select('C.CategoryID,C.Name');
        $this->db->select('IF(MD.ImageName="" || MD.ImageName IS NULL || MD.ImageName=0,"",MD.ImageName) as ImageName', FALSE);
        $this->db->select($select_interested, false);
        $this->db->from(CATEGORYMASTER . ' C');
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');
        $this->db->where('C.ModuleID', '31');
        $this->db->where('C.StatusID', '2');

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
    }

    function save_all_interests($user_id, $interests) {
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->delete(ENTITYCATEGORY);
        if ($interests) {
            $insert_data = array();
            foreach ($interests as $interest) {
                $insert_data[] = array('CategoryID' => $interest, 'EntityCategoryGUID' => get_guid(), 'ModuleID' => '3', 'ModuleEntityID' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
            }
            $this->db->insert_batch(ENTITYCATEGORY, $insert_data);
        }
    }

    /*
      | Function to toggle user's account's suspend status
      | @param : user_id(int),account_suspend_till(date)(string)
      | @output: Boolean
     */

    function suspend_account_toggle($user_id, $account_suspend_till) {
        $this->db->select('StatusID');
        $res = $this->db->get_where(USERS, array('UserID' => $user_id))->row_array();
        $return_status = 2;
        if (!empty($res)) {
            if ($res['StatusID'] == 2) {
                $this->db->set('StatusID', 23);
                if (!empty($account_suspend_till)) {
                    $this->db->set('AccountSuspendTill', $account_suspend_till);
                } else {
                    $this->db->set('AccountSuspendTill', NULL);
                }

                $return_status = 1;
            } else {
                $this->db->set('StatusID', 2);
                $this->db->set('AccountSuspendTill', '');
            }
            $this->db->where('UserID', $user_id);
            $this->db->update(USERS);
        }
        return $return_status;
    }

    //Function to update user's network details
    function update_network_details($user_id, $update_data) {
        $this->db->where('UserID', $user_id);
        $this->db->update(USERDETAILS, $update_data);
        return TRUE;
    }

    /**
     * Function for get User Profile
     * Parameters : $user_id
     * Return : User array
     */
    public function getProfile($user_id) {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");
        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $time_format = dateformat_php_to_mysql($global_settings['time_format']);

        $this->db->select('U.FirstName, U.LastName, U.Email,U.UserID ,U.UserGUID,U.PhoneNumber AS PhoneNumber,UD.UserWallStatus,IFNULL(UD.Designation,"") AS Designation ,IFNULL(UD.OfficialEmailID,"") AS OfficialEmailID,IFNULL(UD.LandlineNumberOffice,"") AS LandlineNumberOffice,IFNULL(UD.DepartmentID,"") AS DepartmentID,IFNULL(UD.PresentAddress,"") AS PresentAddress,IFNULL(UD.PermanentAddress,"") AS PermanentAddress,AdminDOB AS DOB,IFNULL(UD.LandlineNumberHome,"") AS LandlineNumberHome,IFNULL(UD.PersonalEmail,"") AS PersonalEmail,IFNULL(UD.BloodGroup,"") AS BloodGroup,IFNULL(UD.PermanentAccountNumber,"") AS PermanentAccountNumber,IFNULL(UD.EmergencyContactPerson,"") AS EmergencyContactPerson,IFNULL(UD.EmergencyContactNumber,"") AS EmergencyContactNumber,IFNULL(UD.RelationWithContactPerson,"") AS RelationWithContactPerson,UD.RelationWithName,AdminRelationWithID,UD.ConnectWith,UD.ConnectFrom');
        //
        //$this->db->select('IF(U.ProfilePicture, U.ProfilePicture, "user_default.jpg") as ProfilePicture,U.StatusID', FALSE);

        $this->db->select('IF(AdminProfilePicture, AdminProfilePicture, IF(U.ProfilePicture != "", U.ProfilePicture, "user_default.jpg") ) as ProfilePicture,U.StatusID', FALSE);
        $this->db->select('IF( AdminGender IS NULL, IF(U.Gender = "1", "M", IF(U.Gender = "2", "F", "Other")) ,IF(AdminGender = "1", "M", IF(AdminGender = "2", "F", "Other"))  ) as Gender', FALSE);
        $this->db->select('IFNULL(TIMESTAMPDIFF(YEAR, IF(`AdminDOB`, AdminDOB , UD.DOB), NOW()),"") AS Age', FALSE);
        $this->db->select('NoOfFriendsFB,NoOfConnectionsIn,NoOfFollowersFB,NoOfFriendGp,AdminNoOfFollowersTw,NoOfFollowersTw,ReasonOfJoining,ProblemsNComplaints,Admin_Facebook_profile_URL,Admin_Twitter_profile_URL,Admin_Linkedin_profile_URL', FALSE);


        $this->db->select('AdminGender, U.Gender UserGender ,AdminRelationWithName, U.AccountSuspendTill', FALSE);


        $this->db->select('IFNULL(U.ProfileCover,"") as ProfileCover', FALSE);
        $this->db->select('IFNULL(UD.AdminMartialStatus,"") as MartialStatus,IFNULL(TIMESTAMPDIFF(YEAR, `RelationWithDOB`, NOW()),"") AS RelationWithAge,RelationWithDOB', FALSE);
        $this->db->select('P.Url as ProfileURL', FALSE);
        $this->db->select('U.Location AS location', FALSE);

        $user_comment_count_query = "(SELECT COUNT(PC.UserID)  FROM " . POSTCOMMENTS . " PC WHERE PC.UserID = $user_id) AS CommentCount";

        $user_like_count_query = " ((SELECT SUM(M.NoOfLikes)  FROM " . MEDIA . " M WHERE M.UserID = $user_id) "
                . " + ( SELECT SUM(A.NoOfLikes) FROM " . ACTIVITY . " A WHERE A.UserID = $user_id)) AS LikeRecievedCount ";

        $user_activity_count = " (SELECT COUNT(A.ActivityID) FROM " . ACTIVITY . " A WHERE A.UserID = $user_id AND A.ActivityTypeID IN (1, 5, 7, 8, 9, 10, 11, 12, 14, 15, 26, 36, 37, 40)) AS ActivityCount";

        $this->db->select($user_activity_count, FALSE);
        //$this->db->select('SUM(A.NoOfComments) AS CommentCount', FALSE);
        $this->db->select($user_comment_count_query, FALSE);
        $this->db->select($user_like_count_query, FALSE);
        $this->db->select('DATE_FORMAT(U.CreatedDate, "' . $mysql_date . '") AS membersince', FALSE);
        $this->db->select('DATE_FORMAT(U.LastLoginDate, "' . $mysql_date . ' ' . $time_format . '") AS lastlogindate', FALSE);
        $this->db->select('UD.HighlyActivePercentage', FALSE);

        $this->db->from(USERS . " AS U ");
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
        $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
        $this->db->where('U.UserID', $user_id);
        $this->db->group_by('U.UserID');

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results = $query->row_array();

        if (!empty($results)) {
            $this->load->model('users/user_model');
            $this->load->model('admin/dashboard/dashboard_activity_model');
            $results['family_details'] = $this->get_family_details($user_id);
            $results['member_tags'] = $this->dashboard_activity_model->get_entity_tags($user_id, 'USER');
            //$results['member_interest_tags']= $this->user_model->get_user_interest($user_id, '', '', false, 0);
            $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
            $results['ProfilePicture'] = get_image_path($profileSection, $results['ProfilePicture'], 220, 220);
            $results['friends_n_followers'] = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, FALSE, TRUE);


            $results['Location'] = $this->user_model->get_user_location_admin($user_id, 1);
            $results['WorkExperience'] = $this->user_model->getWorkExperience($user_id);

            //Customize Relation With Data
            $results['RelationWithName'] = $results['RelationWithName'];
            $results['RelationWithGUID'] = "";
            $results['RelationWithURL'] = "";
            $results['ConnectWith'] = $this->user_model->get_connect_with($results['ConnectWith']);
            $results['ConnectFrom'] = $this->user_model->get_connect_from($results['ConnectFrom']);
            $InterestPercentage = $this->dashboard_activity_model->user_interest_data($user_id);
            $results['member_interest_tags'] = $InterestPercentage['interests'];
            $results['InterestPercentage'] = $InterestPercentage['new_interests_data'];
            if (!empty($results['RelationWithID'])) {
                $RelationWithDetail = $this->get_relation_user_detail($userdata['RelationWithID']);
                $results['RelationWithName'] = trim($RelationWithDetail['FirstName'] . ' ' . $RelationWithDetail['LastName']);
                $results['RelationWithGUID'] = $RelationWithDetail['UserGUID'];
                //$results['RelationWithAge']  = $RelationWithDetail['Age'];
                $results['RelationWithURL'] = $this->user_model->get_profile_link($results['RelationWithID']);
            }
            unset($results['RelationWithID']);

            $this->load->model(array('log/user_activity_log_score_model'));
            $results['NowScore'] = $this->user_activity_log_score_model->get_user_score_comparision('CONTRIBUTION', 30, 0);
            $results['BeforeScore'] = $this->user_activity_log_score_model->get_user_score_comparision('CONTRIBUTION', 60, 30);

            $results['MartialStatusTxt'] = "----";
            if ($results['MartialStatus'] == 1) {
                $results['MartialStatusTxt'] = 'Single';
            }

            if ($results['MartialStatus'] == 2) {
                $results['MartialStatusTxt'] = 'In a relationship';
            }

            if ($results['MartialStatus'] == 3) {
                $results['MartialStatusTxt'] = 'Engaged';
            }

            if ($results['MartialStatus'] == 4) {
                $results['MartialStatusTxt'] = 'Married';
            }

            if ($results['MartialStatus'] == 5) {
                $results['MartialStatusTxt'] = 'Its complicated';
            }

            if ($results['MartialStatus'] == 6) {
                $results['MartialStatusTxt'] = 'Separated';
            }

            if ($results['MartialStatus'] == 7) {
                $results['MartialStatusTxt'] = 'Divorced';
            }
        }

        return $results;
    }

    public function get_relation_user_detail($RelationWithID) {
        $this->db->select('FirstName,LastName,UserGUID,TIMESTAMPDIFF(YEAR, `DOB`, NOW()) AS Age');
        $this->db->from(USERS . ' AS U');
        $this->db->join(USERDETAILS . ' AS UD', 'UD.UserID=U.UserID', 'LEFT');
        $this->db->where('UserID', $RelationWithID);
        return $this->db->get()->row_array();
    }

    public function get_family_details($user_id) {
        $this->db->select('IFNULL(TIMESTAMPDIFF(YEAR, `DOB`, NOW()),"") AS Age,DOB AS BirthYear,IF(Gender = "1", "M", IF(Gender = "2", "F", "Other")) as Gender,Gender AS FGender', FALSE);
        $this->db->from(USERFAMILYDETAILS);
        $this->db->where('UserID', $user_id);
        return $this->db->get()->result_array();
    }

    public function update_user_exp_persona($user_id, $OrganizationName) {
        $this->db->where('UserID', $user_id);
        $this->db->where('AddedByAdmin', 1);
        $res = $this->db->get(WORKEXPERIENCE)->row_array();
        if (!empty($res)) {
            $this->db->where('UserID', $user_id);
            $this->db->where('AddedByAdmin', 1);
            $update_data = array('OrganizationName' => $OrganizationName);
            $this->db->update(WORKEXPERIENCE, $update_data);
            return TRUE;
        } else {
            $NewWorkExperience['WorkExperienceGUID'] = get_guid();
            $NewWorkExperience['UserID'] = $user_id;
            $NewWorkExperience['OrganizationName'] = $OrganizationName;
            $NewWorkExperience['Designation'] = "";
            $NewWorkExperience['StartMonth'] = "";
            $NewWorkExperience['StartYear'] = "";
            $NewWorkExperience['EndMonth'] = "";
            $NewWorkExperience['EndYear'] = "";
            $NewWorkExperience['CurrentlyWorkHere'] = 1;
            $NewWorkExperience['AddedByAdmin'] = 1;
            $NewWorkExperience['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $NewWorkExperience['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->db->insert(WORKEXPERIENCE, $NewWorkExperience);
            return TRUE;
        }
    }

    function save_family_details($family_details, $user_id) {
        $this->db->where('UserID', $user_id);
        $this->db->delete(USERFAMILYDETAILS);
        if (!empty($family_details)) {
            $insert_arr = array();
            foreach ($family_details as $key => $value) {
                if (!empty($value)) {
                    $insert_arr[$key]['DOB'] = date('Y-m-d', strtotime('-' . $value['Age'] . ' years'));
                    $insert_arr[$key]['Gender'] = $value['FGender'];
                    $insert_arr[$key]['UserID'] = $user_id;
                }
            }
            if (!empty($insert_arr)) {
                $this->db->where('UserID', $user_id);
                $this->db->insert_batch(USERFAMILYDETAILS, $insert_arr);
            }
            return TRUE;
        }
    }

    //Function to save user details
    function save_user_details($user_id, $user_data, $profile_data) {
        $this->db->where('UserID', $user_id);
        $this->db->update(USERS, $user_data);

        $this->db->where('UserID', $user_id);
        $this->db->update(USERDETAILS, $profile_data);

        return TRUE;
    }

    function get_post_permission_for_newsfeed($user_id = 0) {
        $data = array();

        $roles = $this->cache->get('user_roles_' . $user_id);
        if ($roles && in_array(1, $roles)) {
            $data[] = array('Value' => '7', 'Label' => 'Announcement');
        } else {
            $this->db->select('RoleID');
            $this->db->from(USERROLES);
            $this->db->where('UserID', $user_id);
            $this->db->where('RoleID', '1');
            $query = $this->db->get();
            if ($query->num_rows()) {
                $data[] = array('Value' => '7', 'Label' => 'Announcement');
            }
        }

        $data[] = array('Value' => '1', 'Label' => 'Discussion');
        $data[] = array('Value' => '2', 'Label' => 'Q & A');

        return $data;
    }

    function get_user_interest($user_id) {
        $this->load->model('admin/dashboard/dashboard_activity_model');
        //return $this->dashboard_activity_model->user_interest_data($user_id);
        $results = [];
        $InterestPercentage = $this->dashboard_activity_model->user_interest_data($user_id);
        $results['InterestPercentage'] = $InterestPercentage['new_interests_data'];

        return $results;
    }

    //get All profile pictures uploaded by admin for dummy user
    public function get_admin_uploaded_user_images($dummy_user_id, $login_user_id, $page_no = 1, $page_size = 6) {
        //get All profile pictures uploaded by admin for dummy user
        $this->db->select('M.MediaGUID,M.MediaSectionID,M.OriginalName,M.ImageName,M.Size,M.MediaSectionReferenceID,M.Caption,M.CreatedDate,MT.Name as MediaType,IFNULL(MS.MediaSectionAlias, "") as MediaSectionAlias, U.ProfilePicture,U.AdminProfilePicture,IF(U.AdminProfilePicture=M.ImageName,1,0) as IsChecked', FALSE);
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID = M.MediaSectionID', 'LEFT');
        $this->db->join(USERS . ' U', 'U.UserID=M.MediaSectionReferenceID', 'LEFT');
        $this->db->where('M.AddedBy', 0);
        $this->db->where('M.StatusID', 2);
        $this->db->where('M.MediaSectionReferenceID', $dummy_user_id);
        $this->db->where('M.UserID', $login_user_id);
        $this->db->limit($page_size, get_pagination_offset($page_no, $page_size));
        $this->db->order_by('M.CreatedDate', 'DESC');
        $query = $this->db->get(MEDIA . ' M');
        //echo $this->db->last_query();die;
        return $query->result_array();
    }

    public function get_user_communication($user_id, $page_no, $page_size, $count_only = false) {
        $result = [];
        //$this->db->select('M.Subject,DATE_FORMAT(M.CreatedDate,"%d %b %Y") as CreatedDate,M.State');
        //$this->db->from(MANDRILLMESSAGES . ' M');
        //$this->db->join(USERS . ' U', 'U.Email=M.Email', 'left');
        
        $this->db->select('M.Subject,DATE_FORMAT(M.CreatedDate,"%d %b %Y") as CreatedDate,M.StatusID State');
        $this->db->from(COMMUNICATIONS . ' M');
        $this->db->join(USERS . ' U', 'U.Email=M.EmailTo', 'left');
        
        $this->db->where('U.UserID', $user_id);
        $this->db->where_in('M.EmailTypeID', array(2, 3, 5, 37));
        if (!$count_only) {
            $this->db->limit($page_size, get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();
        if (!$count_only) {
            if ($query->num_rows()) {
                $result = $query->result_array();
            }
        } else {
            return $query->num_rows();
        }
        return $result;
    }

    public function get_dummy_user_manager_suggestion($search_keyword, $page_no, $page_size) {
        //for get already added managers id
        $dummy_managers = array();
        $this->db->select('DISTINCT UserID', FALSE);
        $this->db->from(ROLES . ' R');
        $this->db->join(USERROLES . ' UR', 'UR.RoleID = R.RoleID', 'inner');
        $this->db->where('R.RoleKey', "dummy_user_manager");
        $dummy_query = $this->db->get();
        $dummy_result = $dummy_query->result_array();
        foreach ($dummy_result as $user) {
            $dummy_managers[] = $user['UserID'];
        }

        $result = array();
        $this->db->select('U.UserID,CONCAT(U.FirstName," ",U.LastName) as Name', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->where('U.UserTypeID !=', 4);
        $this->db->where('U.StatusID', 2);
        $this->db->where('CONCAT(U.FirstName,"",U.LastName) != ', "");
        if (!empty($dummy_managers)) {
            $this->db->where_not_in('U.UserID', $dummy_managers);
        }

        if ($search_keyword) {
            $this->db->like('CONCAT(U.FirstName," ",U.LastName)', $search_keyword);
        }

        if (isset($page_size) && isset($page_no)) {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $this->db->group_by("U.UserID");
        $this->db->order_by("U.FirstName", "ASC");
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $result = $query->result_array();
        }
        return $result;
    }

    public function save_users_roles($user_ids, $only_users = 0) {

        $this->db->select("RoleID");
        $this->db->from(ROLES);
        $this->db->where('RoleKey', 'dummy_user_manager');
        $query = $this->db->get();
        $role_row = $query->row_array();
        $role_id = isset($role_row['RoleID']) ? $role_row['RoleID'] : 0;
        if (!$role_id) {
            return [];
        }

        // Get all user who have this role id
        if ($only_users) {
            $this->db->select("CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.UserID");
            $this->db->from(USERROLES . "  UR");
            $this->db->join(USERS . ' U', 'U.UserID = UR.UserID');
            $this->db->where('UR.RoleID', $role_id);

            $query = $this->db->get();
            $data = $query->result_array();

            return $data;
        }



        // Get all existing users with role dummy user manager
        $this->db->select("UserRoleID, UserID, RoleID");
        $this->db->from(USERROLES);
        $this->db->where('RoleID', $role_id);
        //$this->db->where_in('UserID', $user_ids);
        $query = $this->db->get();
        $user_role_result = $query->result_array();

        $deleted_users = [];
        $new_users = [];
        $db_user_ids = [];

        // Get database users and deleted users
        foreach ($user_role_result as $user_role_row) {
            $db_user_id = (int) isset($user_role_row['UserID']) ? $user_role_row['UserID'] : 0;
            $db_user_ids[] = $db_user_id;
            if (!in_array($db_user_id, $user_ids)) {
                $deleted_users[] = $db_user_id;
            }
        }

        // Get new users
        foreach ($user_ids as $user_id) {
            if (!in_array($user_id, $db_user_ids)) {
                $new_users[] = array(
                    'UserID' => $user_id,
                    'RoleID' => $role_id,
                    'BusinessUnitID' => 1
                );
            }
        }

        if (!empty($deleted_users)) {
            $this->db->where('RoleID', $role_id);
            $this->db->where_in('UserID', $deleted_users);
            $this->db->delete(USERROLES);
        }

        if (!empty($new_users)) {
            $data = $this->db->insert_batch(USERROLES, $new_users);
        }

        // Get all user who have this role id
        $this->db->select("CONCAT(U.FirstName, ' ', U.LastName) AS Name, U.UserID");
        $this->db->from(USERROLES . "  UR");
        $this->db->join(USERS . ' U', 'U.UserID = UR.UserID');
        $this->db->where('UR.RoleID', $role_id);

        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    function get_random_location() {
        $this->db->select('IFNULL(S.Name,"") as StateName', FALSE);
        $this->db->select('IFNULL(S.ShortCode,"") as StateCode', FALSE);
        $this->db->select('IFNULL(CT.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(C.CountryName,"") as CountryName', FALSE);
        $this->db->select('IFNULL(C.CountryCode,"") as CountryCode', FALSE);
        $this->db->select('IFNULL(L.FormattedAddress,"") as Address', FALSE); /* added by gautam */
        $this->db->from(LOCATIONS . ' L');
        $this->db->join(CITIES . ' CT', 'CT.CityID = L.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' C', 'C.CountryID = L.CountryID', 'left');
        $this->db->join(STATES . ' S', 'S.StateID = L.StateID', 'left');

        $this->db->order_by("RAND()", FALSE);
        $this->db->limit("1");
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $row = $query->row();
            $city = trim($row->CityName);
            $State = trim($row->StateName);
            $StateCode = trim($row->StateCode);
            $Country = trim($row->CountryName);
            $CountryCode = trim($row->CountryCode);
            $Address = trim($row->Address);
            $Location = '';
            if (!empty($city) && $city != null) {
                $city = ucfirst(strtolower($city));
                $Location .= $city . ', ';
            }
            if (!empty($State) && $State != null) {
                $Location .= $State . ', ';
            } else if (!empty($StateCode) && $StateCode != null) {
                $StateCode = strtoupper($StateCode);
                $Location .= $StateCode . ', ';
            }
            if (!empty($Country) && $Country != null) {
                $Country = ucfirst(strtolower($Country));
                $Location .= $Country . ', ';
            }
            if ($Location) {
                $Location = substr($Location, 0, -2);
                if ($Location == '-') {
                    $Location = '';
                }
            }
            return array('City' => $city, 'State' => $State, 'Country' => $Country, 'Location' => $Location, 'StateCode' => $StateCode, 'CountryCode' => $CountryCode, 'Address' => $Address);
        }
    }
}

//End of file users_model.php
