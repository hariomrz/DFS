<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Crm_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model(array(
            'admin/media_model',
            'users/user_model',
            'util/util_location_model'
        ));
    }

    /**
     * Function for get users detail for Users listing for crm 
     * Parameters : Data array
     * Return : Users array + total count
     */
    public function get_users($post_data, $is_return_query = false, $is_real_query = false, $select_field='') {

        $page_no = (int) isset($post_data['PageNo']) ? $post_data['PageNo'] : 1;
        $page_size = (int) isset($post_data['PageSize']) ? $post_data['PageSize'] : 20;
        $Locations = isset($post_data['Locations']) ? $post_data['Locations'] : [];
        $count_only = (int) isset($post_data['OnlyCount']) ? $post_data['OnlyCount'] : 0;
        $citi_ids = [];
        foreach($Locations as $Location) {
            $city_id = $this->util_location_model->get_city_id($Location);
            $citi_ids[] = $city_id;
        }

        if (!$page_no)
            $page_no = 1;
        $offset = ($page_no - 1) * $page_size;

        $this->query_select_users($is_return_query, $select_field);

        $this->db->from(USERS . "  U ");
        $this->db->join(PROFILEURL . ' PU ', "U.UserID=PU.EntityID AND PU.EntityType='User'", "left");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left');
        //$this->db->join(CITIES . ' CT ', 'CT.CityID = UD.HomeCityID', 'left');

        $this->query_filter_users($post_data, $citi_ids);

        //$this->db->where_not_in('U.StatusID', array(3, 4));
        $this->db->group_by('U.UserID');

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $total_users = $temp_q->num_rows();
        if($count_only) {
            return array(
                'total' => $total_users,
                'users' => array()
            );
        }
        $this->query_order_users($post_data);

        /* Start_offset, end_offset */
        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }
        
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query;die;
        //log_message("error", "compiled_query => ".$compiled_query);
        $this->db->reset_query();
        
        if($is_real_query) {
            return $compiled_query;
        }
        
        $query = $this->db->query($compiled_query);
        //$query = $this->db->get();
        $users = $query->result_array();  //echo $this->db->last_query(); echo '======================================';die;
        
        if($is_return_query) {
            $user_ids = [0];
            foreach ($users as $user){
                $user_ids[] = $user['UserID'];
            }            
            return implode(',', $user_ids);
        }

        if(!empty($select_field)) {
            return $users;
        }

        $users = $this->result_set_filter_users($users, $post_data);
        
        return array(
            'total' => $total_users,
            'users' => $users
        );
    }


    public function get_users_notification_popup($post_data, $is_return_query = false, $is_real_query = false, $select_field='') {

        $page_no = (int) isset($post_data['PageNo']) ? $post_data['PageNo'] : 1;
        $page_size = (int) isset($post_data['PageSize']) ? $post_data['PageSize'] : 20;
        $Locations = isset($post_data['Locations']) ? $post_data['Locations'] : [];
        $count_only = (int) isset($post_data['OnlyCount']) ? $post_data['OnlyCount'] : 0;
        $citi_ids = [];
        foreach($Locations as $Location) {
            $city_id = $this->util_location_model->get_city_id($Location);
            $citi_ids[] = $city_id;
        }

        if (!$page_no)
            $page_no = 1;
        $offset = ($page_no - 1) * $page_size;

        $this->query_select_users($is_return_query, $select_field);
        $this->db->select("ACL.DeviceToken, ACL.DeviceTypeID");
        $this->db->from(ACTIVELOGINS . ' ACL');
        $this->db->join(USERS . ' U', 'U.UserID=ACL.UserID AND U.StatusID NOT IN (3,4)');

       // $this->db->from(USERS . "  U ");
        $this->db->join(PROFILEURL . ' PU ', "U.UserID=PU.EntityID AND PU.EntityType='User'", "left");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left');
        //$this->db->join(CITIES . ' CT ', 'CT.CityID = UD.HomeCityID', 'left');

        $this->query_filter_users($post_data, $citi_ids);

        $this->db->where('ACL.IsValidToken', 1);
        $this->db->where('ACL.DeviceToken!=', '');
        $this->db->where('ACL.DeviceTypeID!=', 1);
        $this->db->group_by('ACL.DeviceToken');
        $this->db->group_by('ACL.DeviceTypeID');
        $this->db->group_by('U.UserID');

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $total_users = $temp_q->num_rows();
        if($count_only) {
            return array(
                'total' => $total_users,
                'users' => array()
            );
        }
        $this->query_order_users($post_data);

        /* Start_offset, end_offset */
        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }
        
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query;die;
        //log_message("error", "compiled_query => ".$compiled_query);
        $this->db->reset_query();
        
        if($is_real_query) {
            return $compiled_query;
        }
        
        $query = $this->db->query($compiled_query);
        //$query = $this->db->get();
        $users = $query->result_array();  //echo $this->db->last_query(); echo '======================================';die;
        
        if($is_return_query) {
            $user_ids = [0];
            foreach ($users as $user){
                $user_ids[] = $user['UserID'];
            }            
            return implode(',', $user_ids);
        }

        if(!empty($select_field)) {
            return $users;
        }

        $users = $this->result_set_filter_users($users, $post_data);
        
        return array(
            'total' => $total_users,
            'users' => $users
        );
    }

    public function query_order_users($order_data) {

        $order_by_field = isset($order_data['OrderByField']) ? $order_data['OrderByField'] : 'FirstName';
        $order_by = isset($order_data['OrderBy']) ? $order_data['OrderBy'] : 'DESC';
        
        $allowed_order_by_fields = [
            'FirstName' => 'U.FirstName',
            'UserID' => 'U.UserID',
            'RECENT_ACTIVE' => 'UD.LastActivityDate',
            'AverageScore' => 'UD.AverageScore'
        ];
        
        if(!in_array($order_by_field, array_keys($allowed_order_by_fields))) {
            $order_by_field_db = $allowed_order_by_fields['UserID'];
        } else {
            $order_by_field_db = $allowed_order_by_fields[$order_by_field];
        }
        
        if(!in_array($order_by, ['ASC', 'DESC'])) {
            $order_by = 'DESC';
        } 
        
        if($order_by_field == 'RECENT_ACTIVE') {
            //$this->db->join(USERSACTIVITYLOG . ' UAL', 'U.UserID = UAL.UserID', 'left');  
        }
        
        
        $this->db->order_by($order_by_field_db, $order_by);
    }

    public function query_select_users($is_return_query, $select_field='') {
        
        if($is_return_query) {
            $select_array[] = "U.UserID";
            $this->db->select(implode(',', $select_array), FALSE);
            return;
        }

        if(!empty($select_field)) {
            $select_array[] = $select_field;
            $this->db->select(implode(',', $select_array), FALSE);
            return;
        }
                
       // $today_date = get_current_date('%Y-%m-%d %H:%i:%s');

        /* Load Global settings */
        /* Change date_format into mysql date_format */
        $global_settings = $this->config->item("global_settings");
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);

       // $select_array[] = " IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') AS Age";
        //$select_array[] = "IFNULL(CT.Name, '') AS City";
       // $select_array[] = "IFNULL(U.Gender, '') Gender";
        
        $select_array[] = "DATE_FORMAT(U.CreatedDate, '" . $mysql_date . "') AS CreatedDate";
        $select_array[] = "U.UserID";
        $select_array[] = "U.IsVIP, U.IsAssociation";
        $select_array[] = "U.PhoneNumber";
        $select_array[] = "CONCAT(U.FirstName, ' ', U.LastName) AS Name";
        $select_array[] = "U.ProfilePicture AS ProfilePicture";
        $select_array[] = "U.StatusID, U.UserGUID";
        $select_array[] = "UD.AverageScore";
        $select_array[] = "IFNULL(UD.AndroidAppVersion, '') AndroidAppVersion";
        $select_array[] = "IFNULL(UD.IOSAppVersion, '') IOSAppVersion";
        $select_array[] = "IFNULL(UD.UserWallStatus, '') UserWallStatus";
       // $select_array[] = 'DATE_FORMAT(U.CreatedDate, "' . $mysql_date . '") AS Membersince';

        $this->db->select(implode(',', $select_array), FALSE);
        
        /* $sub = $this->subquery->start_subquery('select');
        $sub->select('GROUP_CONCAT(UR.RoleID)', FALSE)->from(USERROLES . ' AS UR');
        $sub->where('UR.UserID = U.UserID');
        $this->subquery->end_subquery('userroleid');
         * 
         */
    }

    public function query_filter_users($filter, $citi_ids) {

        $age_group_id = isset($filter['AgeGroupID']) ? $filter['AgeGroupID'] : 0;
        $gender = isset($filter['Gender']) ? $filter['Gender'] : 0;

        $android_app_version = isset($filter['AndroidAppVersion']) ? $filter['AndroidAppVersion'] : 0;
        $ios_app_version = isset($filter['IOSAppVersion']) ? $filter['IOSAppVersion'] : 0;
        
        $search_key = isset($filter['SearchKey']) ? $filter['SearchKey'] : '';

        $tag_user_type = isset($filter['TagUserType']) ? $filter['TagUserType'] : [];
        $tag_user_search_type = isset($filter['TagUserSearchType']) ? $filter['TagUserSearchType'] : 0;  //TagUserSearchType (0=> AND 1=> OR)
        
        $tag_tag_type = isset($filter['TagTagType']) ? $filter['TagTagType'] : [];
        $tag_tag_search_type = isset($filter['TagTagSearchType']) ? $filter['TagTagSearchType'] : 0;  //TagTagSearchType (0=> AND 1=> OR)
        
        $user_ids = isset($filter['UserIDs']) ? $filter['UserIDs'] : [];
        $user_status = (int) isset($filter['StatusID']) ? $filter['StatusID'] : 0;
        $userExcludeList = isset($filter['userExcludeList']) ? $filter['userExcludeList'] : [];
        
       
        $ward_id = (int) safe_array_key($filter, 'WID', 1);
        $locality_id = (int) safe_array_key($filter, 'LocalityID', 0);
        $income_level = safe_array_key($filter, 'IncomeLevel', array());

        $last_login = (int) isset($filter['LastLogin']) ? $filter['LastLogin'] : 0;
        if($ward_id > 1) {
            $this->db->join(LOCALITY . ' L', 'L.LocalityID=UD.LocalityID');
            $this->db->join(WARD . ' W', 'W.WardID=L.WardID AND W.WardID='.$ward_id);
        }
        if($locality_id > 0) {
            $this->db->where('UD.LocalityID', $locality_id);
        }

        if($last_login > 0) {
            $from_date = get_current_date('%Y-%m-%d');
            $to_date = get_current_date('%Y-%m-%d', $last_login);
            $time_zone = 'Asia/Calcutta';
            $this->db->where("DATE_FORMAT(CONVERT_TZ(U.LastLoginDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') < '" . $to_date . "'", NULL, FALSE);
            //$this->db->where("DATE_FORMAT(CONVERT_TZ(U.LastLoginDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $to_date . "'", NULL, FALSE);        
            //$this->db->where("DATE_FORMAT(CONVERT_TZ(U.LastLoginDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') < '" . $from_date . "'", NULL, FALSE);                       
        }

        $start_date = isset($filter['StartDate']) ? $filter['StartDate'] : '';
        $end_date = isset($filter['EndDate']) ? $filter['EndDate'] : '';        
        $age_start = isset($filter['AgeStart']) ? $filter['AgeStart'] : '';
        $age_end = isset($filter['AgeEnd']) ? $filter['AgeEnd'] : '';

        /* start_date, end_date for filters */
        /* if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            if(in_array($user_status, array(3,4))){
                $this->db->where('DATE(U.ModifiedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
            } else {
                $this->db->where('DATE(U.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
            }          

        }
        */
        $time_zone = 'Asia/Calcutta';
        if(in_array($user_status, array(3,4))){
            if ($start_date) {
                $start_date = date("Y-m-d", strtotime($start_date));
                $this->db->where("DATE_FORMAT(CONVERT_TZ(U.ModifiedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
            }
            if ($end_date) {
                $end_date = date("Y-m-d", strtotime($end_date));
                $this->db->where("DATE_FORMAT(CONVERT_TZ(U.ModifiedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
            }
        } else {
            if ($start_date) {
                $start_date = date("Y-m-d", strtotime($start_date));
                $this->db->where("DATE_FORMAT(CONVERT_TZ(U.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
            }
            if ($end_date) {
                $end_date = date("Y-m-d", strtotime($end_date));
                $this->db->where("DATE_FORMAT(CONVERT_TZ(U.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
            }
        }

        if (!empty($user_status) && $user_status != 500) {
            if ($user_status == 1 || $user_status == 2) {
                $this->db->where_in('U.StatusID', array(1, 2, 7, 6));
            //} else if ($user_status == 2) {
                //$this->db->where_in('U.StatusID', array(2, 7));
            } else {
                $this->db->where('U.StatusID', $user_status);
            }
        }
        
       if ($search_key) {
            $this->db->group_start();
            $this->db->like('CONCAT(U.FirstName," ", U.LastName)', $search_key);
            $this->db->or_like('U.PhoneNumber', $search_key);
            $this->db->or_like('U.Email', $search_key);
            $this->db->group_end();
        }

        if ($gender) {
            $gender = ($gender == 3) ? 0 : $gender;
            $this->db->where('U.Gender', $gender);
        }

        if (count($citi_ids)) {
            $string_str_ids = implode(',', $citi_ids);
            $this->db->where(" (UD.CityID IN ( $string_str_ids ) ) ", NULL, FALSE);
        }

        if(!empty($income_level)) {
            $this->db->where_in('UD.IncomeLevel', $income_level);
        }

        if(!empty($android_app_version)) {
            $this->db->where_in('UD.AndroidAppVersion', $android_app_version);
        }
        if(!empty($ios_app_version)) {
            $this->db->where_in('UD.IOSAppVersion', $ios_app_version);
        }
        
        if(count($user_ids)) {
             $this->db->where_in('U.UserID', $user_ids);
        }
        
        if(count($userExcludeList)) {
             $this->db->where_not_in('U.UserID', $userExcludeList);
        }

        if ($age_group_id) {
            //$this->db->join(ANALYTICLOGINS . ' AL ', 'AL.AnalyticLoginID = UAL.AnalyticLoginID', 'left');
            $ana_table = ANALYTICLOGINS;
            $select_analytics = "U.UserID = ( Select AL.UserID From $ana_table AL WHERE AL.UserID = U.UserID AND AL.AgeGroupID = $age_group_id ORDER BY AL.AnalyticLoginID DESC LIMIT 1 )";

            $this->db->where($select_analytics, NULL, FALSE);
        }
        
        
        if(!$age_start) {
            $age_start = 0;
        }

        if(!$age_end) {
            $age_end = 0;
        }
            
        if($age_start  || $age_end ) {
            $today_date = get_current_date('%Y-%m-%d %H:%i:%s');
            if($age_start  && $age_end ) {
                //$this->db->having("Age >= $age_start AND Age <= $age_end", NULL, FALSE);
                $this->db->where("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') >= $age_start AND IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') <= $age_end", NULL, FALSE);
            }
            
            else if($age_start ) {
                //$this->db->having("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') >= $age_start", NULL, FALSE);
                $this->db->where("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') >= $age_start", NULL, FALSE);
            }
            
            else  if($age_end ) {
                //$this->db->having("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') <= $age_end", NULL, FALSE);
                $this->db->where("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') <= $age_end", NULL, FALSE);
            }
        }

        
        
        $tag_user_type_tag_ids = [];
        foreach ($tag_user_type as $tag_user_type_tag ){
            $tag_user_type_tag_ids[] = $tag_user_type_tag['TagID'];
        }
        
        $tag_tag_type_tag_ids = [];
        foreach ($tag_tag_type as $tag_tag_type_tag ){
            $tag_tag_type_tag_ids[] = $tag_tag_type_tag['TagID'];
        }
        
        $this->tag_select_query($tag_user_type_tag_ids, $tag_user_search_type, $this, 0);
        $this->tag_select_query($tag_tag_type_tag_ids, $tag_tag_search_type, $this, 1);
    }

    function tag_select_query($tag_ids, $searchType, $objCtx, $tagSerachType = 1) {
        //1- Normal Tag, 2- Hash Tag, 3- Activity Mood, 4- Activity Classification, 5- User/Reader Tag, 6- User Profession, 7- Brand
        if (!$tag_ids) {
            return;
        }

        $tbl_als_et = ($tagSerachType) ? 'ET' : 'ET1';
        $tbl_als_tg = ($tagSerachType) ? 'TG' : 'TG1';

        $objCtx->db->join(ENTITYTAGS . " $tbl_als_et", "$tbl_als_et.EntityID = U.UserID AND $tbl_als_et.EntityType = 'USER'  AND $tbl_als_et.StatusID = 2", 'left');
        $objCtx->db->join(TAGS . " $tbl_als_tg", "$tbl_als_tg.TagID = $tbl_als_et.TagID", 'left');
        
        if ($searchType) { //Match any
            $objCtx->db->where_in("$tbl_als_et.TagID", $tag_ids);
        } else if(count($tag_ids)){
            // Match all
            $in_tag_ids = implode(',', $tag_ids);
            $total_tag_ids = count($tag_ids);
            $and_conditions = "(select COUNT(DISTINCT ET_COUNT.`TagID`) from `EntityTags` ET_COUNT 
            Where ET_COUNT.`EntityID` = `U`.`UserID` 
            AND `ET_COUNT`.`EntityType` = 'USER'  
            AND `ET_COUNT`.`StatusID` = 2
            AND  ET_COUNT.`TagID` IN ($in_tag_ids) ) = $total_tag_ids  ";
            
            $objCtx->db->where($and_conditions, NULL, false);
            
        }
    }

    public function result_set_filter_users($users, $post_data) {
        $entity_ids = [];
        $newKeyedUsers = [];
        $this->load->model(array('ward/ward_model'));
        //$ward_id = (int) isset($post_data['WID']) ? $post_data['WID'] : 0;
        $ward_id = 0;
        $make_browser_compatible_obj = [];
        foreach ($users as $user) {
            $ward_feature_user = $this->ward_model->is_featured_user(array('WID' => $ward_id, 'UserID' => $user['UserID']), 1);
            $user['IsFeatured'] = 0;
            $user['IsPinned'] = 0;
            
            $user['wf_uid'] = safe_array_key($ward_feature_user, 'WardFeatureUserID', 0);
            $user['IsPinned'] = safe_array_key($ward_feature_user, 'IsPinned', 0);
            if(!empty($user['wf_uid'])) {
                $user['IsFeatured'] = 1;
            }
            
            $user['Name'] = stripslashes($user['Name']);
            $user['userroleid'] = $this->getUserRoles($user['UserID']);
            if(empty($user['ProfilePicture'])) {
                $user['ProfilePicture'] = 'user_default.jpg';
            }
           // $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
            //$user['ProfilePictureUrl'] = get_image_path($profileSection, $user['ProfilePicture'], ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
            
          /*  $user['Location'] = $this->user_model->get_user_location_admin($user['UserID'], 0, 1);
            if(empty($user['Location']['City'])) {
                $user['Location'] = $this->user_model->get_user_location_admin($user['UserID'], 1);
            }
           */ 

            $user['Tags'] = [];
            //$user['TagsMore'] = [];
            $user['UserTypeTags'] = [];
            //$user['UserTypeTagsMore'] = [];
            

            $entity_ids[] = $user['UserID'];
            $newKeyedUsers[$user['UserID']] = $user;

            $make_browser_compatible_obj['pre'.$user['UserID']] = $user;
        }

        /* 
        $users = $newKeyedUsers;
        $users = $this->set_entity_tags($newKeyedUsers, $entity_ids, 'USER');
        
        $make_browser_compatible_obj = [];
        foreach($users as $user_id => $user) {
            $make_browser_compatible_obj['pre'.$user_id] = $user;
        }
        */

        return $make_browser_compatible_obj;
    }

    public function set_entity_tags($users, $entity_ids, $entity_type, $added_by = NULL) {

        if (!$entity_ids) {
            return $users;
        }

        $entity_ids_chunk = array_chunk($entity_ids,3000);

        $select_array[] = "TG.TagID, TG.Name, TG.TagType, ET.AddedBy,TG.Name AS text, ET.EntityID";

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(ENTITYTAGS . ' ET');
        $this->db->join(TAGS . ' TG', 'TG.TagID = ET.TagID', 'left');

        $this->db->where('ET.EntityType', $entity_type);
        $this->db->where('ET.StatusID', 2);
        foreach($entity_ids_chunk as $entity_ids) {
            $this->db->or_where_in('ET.EntityID', $entity_ids);
        }

        //$this->db->order_by('TG.TagType');
        //$this->db->group_by('ET.EntityID');

        if ($added_by !== NULL) {
            $this->db->where('ET.AddedBy', $added_by);
        }

        $query = $this->db->get();  //echo $this->db->last_query(); die;
        $tag_entities = $query->result_array();

        //1- Normal Tag, 2- Hash Tag, 3- Activity Mood, 4- Activity Classification, 5- User/Reader Tag, 6- User Profession, 7- Brand
        $allowed_number_of_tags = 5;

        foreach ($tag_entities as $tag_entity) {
            $user_id = $tag_entity['EntityID'];

            if (in_array($tag_entity['TagType'], [6, 7])) {
                
                $users[$user_id]['Tags'][] = $tag_entity;
                
//                if (count($users[$user_id]['Tags']) >= $allowed_number_of_tags) {
//                    $users[$user_id]['Tags'][] = $tag_entity;
//                } else {
//                    $users[$user_id]['TagsMore'][] = $tag_entity;
//                }
            } else if (in_array($tag_entity['TagType'], [5])) {
                
                $users[$user_id]['UserTypeTags'][] = $tag_entity;
                
//                if (count($users[$user_id]['UserTypeTags']) >= $allowed_number_of_tags) {
//                    $users[$user_id]['UserTypeTags'][] = $tag_entity;
//                } else {
//                    $users[$user_id]['UserTypeTagsMore'][] = $tag_entity;
//                }
            }
        }

        return $users;
    }

    public function download_users($users) {
        
        $file_arr = [];
        
        $fields_with_alies = array(
            'Name' => 'Name',
            'PhoneNumber' => 'Mobile', 
            /*'Age' => 'Age',
            'Gender' => 'Gender',
            'UserTypeTags' => 'User Types',
            'Tags' => 'Tags',
            */
            'AndroidAppVersion' => 'Android App Version ('.ANDROID_VERSION.')',
            'IOSAppVersion' => 'iOS App Version ('.IOS_VERSION.')',
            //'Location' => 'Location',
            'CreatedDate' => 'Registered Date'
        );
        
        function formatUserObjectField($user, $field_key) {
            if(is_array($user[$field_key])) {
                
                
                if($field_key == 'Location') {
                    $locationStr = '';
                    $locationStr = $user[$field_key]['City'];
                    $locationStr .= ($locationStr) ? ', '.$user[$field_key]['State'] : '';
                    $locationStr .= ($locationStr) ? ', '.$user[$field_key]['Country'] : '';
                    
                    return stripslashes($locationStr);
                }
                
                
                $tags = $user[$field_key];
                $tags_str = '';
                $tagsArr = [];
                foreach ($tags as $tag) {
                    $tagsArr[] = $tag['Name'];
                }
                $tags_str = implode(', ', $tagsArr);
                return stripslashes($tags_str);
            } 
            
            if($field_key == 'Gender') {
                $genderArr = array(
                    0 => 'Other',
                    1 => 'Male',
                    2 => 'Female',
                );
                if($user[$field_key] && $user[$field_key] < 3) {
                    $user[$field_key] = $genderArr[$user[$field_key]];
                } else {
                    $user[$field_key] = 'Other';
                }
            }
            
            return stripslashes($user[$field_key] );
        }
        
        $excelInput = array();
        foreach ($users as $user) {            
            foreach ($fields_with_alies as $field_key => $field_val) {
                $userArr[$field_key] = formatUserObjectField($user, $field_key);
                
            }
            $excelInput[] = $userArr;
        }

        $sheetTitle = 'Users';
        $dateFilterText = '';

        $excelArr = array();
        $excelArr['headerArray'] = $fields_with_alies;
        $excelArr['sheetTitle'] = $sheetTitle;
        $excelArr['fileName'] = "UsersListtagtypes.xls";
        $excelArr['folderPath'] = DOC_PATH . ROOT_FOLDER . '/' . PATH_IMG_UPLOAD_FOLDER . "csv_file/";
        $excelArr['inputData'] = $excelInput;
        $excelArr['ReportHeader'] = "";//array("ReportName" => $sheetTitle, "dateFilterText" => $dateFilterText);

         
        if ($this->downloadExcelFile($excelArr)) {
            $csv_url = base_url() . 'admin/users/downloaduserlisttagtypes';
            $file_arr['csv_url'] = $csv_url;
        }
        
        return $file_arr;
    }
    
    function send_notification($data) {

        $user_ids = array();

        $source = !empty($data['Source']) ? $data['Source'] : 1;  //1 - CMS PAGE, 2 - Notification Popup, 3 - Possible Solution, 4 - Solution, 5 - Ready, 6 - Activity Dashboard, 7 - Edit Post
        $notification_text = !empty($data['notification_text']) ? $data['notification_text'] : '';   
        $notification_text = trim(strip_tags($notification_text));
        $notification_title = !empty($data['notification_title']) ? $data['notification_title'] : '';
        $title = trim(strip_tags($notification_title));
        $isSms = !empty($data['isSms']) ? $data['isSms'] : 0;
        $all_user_selected = isset($data['allUserSelected']) ? $data['allUserSelected'] : 0;
        $activity_guid  = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        $activity_id  = isset($data['ActivityID']) ? $data['ActivityID'] : 0;
        $quiz_id  = isset($data['QuizID']) ? $data['QuizID'] : 0;
        $user_ids = isset($data['UserIDs']) ? $data['UserIDs'] : array();   
        
        $data['LocalityID'] = 0;
        $data['PageSize'] = 0;
        $data['OnlyCount'] = 0;
        if ($isSms == 0) {
            $push_notification = $data['PushNotification'];
            
            if($all_user_selected == 1) {  
                if(in_array($source, array(2, 6, 8))) {
                    $user_id_str =  $this->get_users_notification_popup($data, true);
                } else {
                    $user_id_str =  $this->get_users($data, true);
                }                     
                //log_message('error', "user: ".$user_id_str);
                $user_ids = explode(',', $user_id_str);
            }
                    
            
            $user_ids_chunk = array_chunk($user_ids,500);            
            $this->db->select("AL.UserID, AL.DeviceToken, AL.DeviceTypeID");
            $this->db->from(ACTIVELOGINS . ' AL');
            foreach($user_ids_chunk as $user_ids) {
                $this->db->or_where_in('AL.UserID', $user_ids);
            }
            $this->db->where('AL.IsValidToken', 1);
            $this->db->where('AL.DeviceToken!=', '');
            $this->db->group_by('AL.DeviceToken');
            $this->db->group_by('AL.DeviceTypeID');
            $this->db->order_by('AL.ActiveLoginID', 'DESC');
            $query = $this->db->get();
            
            //log_message('error', "user: ".$this->db->last_query());
            
            if ($query->num_rows() > 0) {
                $total_notification = $query->num_rows();
                $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');  
                $this->load->model('admin/communication/communication_model');
                $communication_history_data = $data['CommunicationHistory'];
                  
                $communication_history_data['Source'] = $source;              
                $communication_history_data['Content'] = json_encode(array('PushNotificationTitle' => $title, 'PushNotificationText' => $notification_text), JSON_UNESCAPED_UNICODE);
                
                $notification_data['CommunicationHistory'] = $communication_history_data;
                $notification_data['ActivityID'] = $activity_id;
                $notification_data['QuizID'] = $quiz_id;
                $notification_data['PushNotification'] = $push_notification;
                $notification_data['NotificationText'] = $notification_text;  
                $notification_data['NotificationTitle'] = $title; 

                $results = $query->result_array();
                $results = array_chunk($results, 200);
                
                $queue_name = 'send_custom_notification';
                $i = 0;
                foreach ($results as $Notifications) {
                    $notification_data['Result'] = $Notifications;   
                    if($i == 0 || $i == 3) {
                        $i = 0;
                        $queue_name = 'send_custom_notification';
                    } else {
                        $queue_name = 'send_custom_notification'.$i;
                    }
    
                    initiate_worker_job('send_custom_notification', $notification_data, '', $queue_name);
    
                    ++$i;

                    //$this->process_notification($notification_data);                    
                }
            }
        } else {   
            // $short_url = DOMAIN;
            $short_url = '';
            $activity_id = 0;
            if(!empty($activity_guid)) {
                $activity_details = get_detail_by_guid($activity_guid, 0, 'ActivityID,ActivityGUID,PostTitle,PostContent,UserID', 2);
                if($activity_details) {
                    $activity_details['ActivityOwnerProfileURL'] = get_entity_url($activity_details['UserID'], "User", 1);
                    $short_url = get_seo_friendly_activity_url($activity_details);
                    $short_url = DOMAIN."/".$short_url;
                }
            } else if(!empty($quiz_id)) {
                $quiz = get_detail_by_id($quiz_id, 47, 'QuizGUID, Title, Description, SponsorID', 2);
                if($quiz) {
                    $quiz['SponsorProfileURL'] = get_entity_url($quiz['SponsorID'], "User", 1);
                    $this->load->model(array('quiz/quiz_model'));
                    $short_url = $this->quiz_model->get_short_url($quiz);
                    $short_url = DOMAIN."/".$short_url;               
                }
            }         
            
            if(ENVIRONMENT=="production") {
                if(in_array($source, array(2, 6, 8))) {
                    $users =  $this->get_users_notification_popup($data, false, false, 'U.PhoneNumber, U.UserID');
                } else {
                    $users =  $this->get_users($data, false, false, 'U.PhoneNumber, U.UserID');
                } 
                
            } else {
                $users = array(
                    array('PhoneNumber' => '7771030003', 'UserID'=>'10527'),
                    array('PhoneNumber' => '9424595392', 'UserID'=>'563'),
                    array('PhoneNumber' => '7354820106', 'UserID'=>'145'),
                    array('PhoneNumber' => '9406683631', 'UserID'=>'1441'),
                    array('PhoneNumber' => '8839261872', 'UserID'=>'10404'),
                    array('PhoneNumber' => '9424051781', 'UserID'=>'10409')
                );
            }
            $total_sms = count($users);               
            $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');  
            $this->load->model('admin/communication/communication_model');
            $communication_history_data = $data['CommunicationHistory'];

            $communication_history_data['Source'] = $source;            
            $communication_history_data['Content'] = json_encode(array('SmsText' => $notification_text), JSON_UNESCAPED_UNICODE);
                
            $notification_data['CommunicationHistory'] = $communication_history_data;
            $notification_data['ShortURL'] = $short_url;
            $notification_data['NotificationText'] = $notification_text;
            
            $results = array_chunk($users, 200);

            $queue_name = 'send_custom_sms';
            $i = 0;
            foreach ($results as $Notifications) {
                $notification_data['Result'] = $Notifications;
                if($i == 0 || $i == 3) {
                    $i = 0;
                    $queue_name = 'send_custom_sms';
                } else {
                    $queue_name = 'send_custom_sms'.$i;
                }

                initiate_worker_job('send_custom_sms', $notification_data, '', $queue_name);

                ++$i;  
                //$this->process_sms_notification($notification_data);                
            }            
        }
    }

    function process_notification($data) {       
        $communication_history_data = $data['CommunicationHistory'];
        
        $push_notification  = $data['PushNotification'];
        $notification_text  = $data['NotificationText'];    
        $title              = $data['NotificationTitle'];          
        $result = $data['Result'];
        $this->load->model('admin/communication/communication_model');
        foreach ($result as $notifications) {
            $token = $notifications['DeviceToken'];
            $notification_data = array();
            $notification_data['PushNotification'] = $push_notification;
            $notification_data['ToUserID'] = $notifications['UserID'];
            if(empty($title)) {
                $notification_data['Summary'] = '';
                $message = $notification_text;
            } else {     
                $notification_data['Summary'] = $notification_text; 
                $message = $title;                 
            }

            $notification_data['DeviceTypeID'] = $notifications['DeviceTypeID'];            
            $communication_history_data['UserID'] = $notifications['UserID'];
            $communication_history_data['DeviceTypeID'] = $notifications['DeviceTypeID'];
            //$communication_history_data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $flag = $this->communication_model->add_histrory($communication_history_data);
            if($flag) {
                send_push_notification($token, $message, 1, $notification_data);
            }
            
            /* if ($notifications['DeviceTypeID'] == 2) {
                push_notification_iphone($token, $message, 0, $notification_data);
            } elseif ($notifications['DeviceTypeID'] == 3) {
                push_notification_android(array($token), $message, 0, $notification_data);
            }
            */
        }       
        return true;        
    }


    function process_sms_notification($data) {       
        $communication_history_data = $data['CommunicationHistory'];
        $short_url = $data['ShortURL'];
        $notification_text = $data['NotificationText'];             
        $result = $data['Result'];
        $this->load->model('admin/communication/communication_model');
        foreach ($result as $notifications) {
            $phone_no = $notifications['PhoneNumber'];                    
            if($phone_no) {
                $communication_history_data['UserID'] = $notifications['UserID'];
                $communication_history_data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $flag = $this->communication_model->add_histrory($communication_history_data);
                if($flag) {
                    //send sms using sms library 	 
                    if(ACTIVE_SMS_GATEWAY == "msg91") {
                        $sms_data = array();
                        $sms_data['mobile'] = $phone_no;
                        $sms_data['phone_code'] = DEFAULT_PHONE_CODE;
                        $sms_data['message'] = $notification_text.' '.$short_url;
                        send_msg91_sms($sms_data);
                    } else {    
                        $this->load->library('TwoFactorSMS');
                        $TwoF = new TwoFactorSMS(TWO_FACTOR_SMS_API_KEY, TWO_FACTOR_SMS_API_ENDPOINT);
                        $TwoF->SendPromotionalSMS("PROMOS", DEFAULT_PHONE_CODE . $phone_no, $notification_text, "");
                    } 
                }
            }
        }       
        return true;        
    }


    /**
     * Function for get top following user list
     * Parameters : Data array
     * Return : Users array + total count
     */
    public function get_top_following($post_data) {

        $page_no = (int) isset($post_data['PageNo']) ? $post_data['PageNo'] : 1;
        $page_size = (int) isset($post_data['PageSize']) ? $post_data['PageSize'] : 25;       
        $count_only = (int) isset($post_data['OnlyCount']) ? $post_data['OnlyCount'] : 0;
        
        if (!$page_no)
            $page_no = 1;
        $offset = ($page_no - 1) * $page_size;

        $select_array[] = "U.UserID";
        $select_array[] = "U.PhoneNumber";
        $select_array[] = "CONCAT(U.FirstName, ' ', U.LastName) AS Name";
        $select_array[] = "U.StatusID, U.UserGUID";
        $select_array[] = "UD.TotalFollowing";

        $this->db->select(implode(',', $select_array), FALSE);
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);

        $this->db->from(USERS . "  U ");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID');

        $this->db->where_not_in('U.StatusID', array(3, 4));      
        $this->db->where('UD.TotalFollowing > ', 0);       

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $total_users = $temp_q->num_rows();
        if($count_only) {
            return array(
                'total' => $total_users,
                'users' => array()
            );
        }
        
        $order_by_field = isset($post_data['OrderByField']) ? $post_data['OrderByField'] : 'FirstName';
        $order_by = isset($post_data['OrderBy']) ? $post_data['OrderBy'] : 'DESC';
        
        $allowed_order_by_fields = [
            'FirstName' => 'U.FirstName',
            'TotalFollowing' => 'UD.TotalFollowing'
        ];
        
        if(!in_array($order_by_field, array_keys($allowed_order_by_fields))) {
            $order_by_field_db = $allowed_order_by_fields['UserID'];
        } else {
            $order_by_field_db = $allowed_order_by_fields[$order_by_field];
        }
        
        if(!in_array($order_by, ['ASC', 'DESC'])) {
            $order_by = 'DESC';
        } 
        
        
        $this->db->order_by($order_by_field_db, $order_by);

        /* Start_offset, end_offset */
        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }
        
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query;die;
        //log_message("error", "compiled_query => ".$compiled_query);
        $this->db->reset_query();
        
        
        $query = $this->db->query($compiled_query);
        //$query = $this->db->get();
        $users = $query->result_array();  //echo $this->db->last_query(); echo '======================================';die;
        
        
        //$users = $this->result_set_filter_users($users, $post_data);
        
        return array(
            'total' => $total_users,
            'users' => $users
        );
    }

    /**
     * Function for get most followed user list
     * Parameters : Data array
     * Return : Users array + total count
     */
    public function get_top_followed($post_data) {

        $page_no = (int) isset($post_data['PageNo']) ? $post_data['PageNo'] : 1;
        $page_size = (int) isset($post_data['PageSize']) ? $post_data['PageSize'] : 25;       
        $count_only = (int) isset($post_data['OnlyCount']) ? $post_data['OnlyCount'] : 0;
        
        if (!$page_no)
            $page_no = 1;
        $offset = ($page_no - 1) * $page_size;

        $select_array[] = "U.UserID";
        $select_array[] = "U.PhoneNumber";
        $select_array[] = "CONCAT(U.FirstName, ' ', U.LastName) AS Name";
        $select_array[] = "U.StatusID, U.UserGUID";
        $select_array[] = "UD.TotalFollowers";

        $this->db->select(implode(',', $select_array), FALSE);
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);
        $this->db->from(USERS . "  U ");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID');

        $this->db->where_not_in('U.StatusID', array(3, 4));      
        $this->db->where('UD.TotalFollowers > ', 0);       

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $total_users = $temp_q->num_rows();
        if($count_only) {
            return array(
                'total' => $total_users,
                'users' => array()
            );
        }

        $order_by_field = isset($post_data['OrderByField']) ? $post_data['OrderByField'] : 'FirstName';
        $order_by = isset($post_data['OrderBy']) ? $post_data['OrderBy'] : 'DESC';
        
        $allowed_order_by_fields = [
            'FirstName' => 'U.FirstName',
            'TotalFollowers' => 'UD.TotalFollowers'
        ];
        
        if(!in_array($order_by_field, array_keys($allowed_order_by_fields))) {
            $order_by_field_db = $allowed_order_by_fields['UserID'];
        } else {
            $order_by_field_db = $allowed_order_by_fields[$order_by_field];
        }
        
        if(!in_array($order_by, ['ASC', 'DESC'])) {
            $order_by = 'DESC';
        } 
        
        
        $this->db->order_by($order_by_field_db, $order_by);

        /* Start_offset, end_offset */
        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }
        
        $compiled_query = $this->db->_compile_select();  // echo $compiled_query;die;
        //log_message("error", "compiled_query => ".$compiled_query);
        $this->db->reset_query();
        
        
        $query = $this->db->query($compiled_query);
        //$query = $this->db->get();
        $users = $query->result_array();  //echo $this->db->last_query(); echo '======================================';die;
        
        
        //$users = $this->result_set_filter_users($users, $post_data);
        
        return array(
            'total' => $total_users,
            'users' => $users
        );
    }

    public function top_contributor_notification($data) {
        $this->load->model(array('users/user_model'));
        $tag_id = 0;
        $this->user_model->set_top_contributors($tag_id);
        $user_ids = $this->user_model->get_top_contributors();
        
        if(!empty($user_ids)) {
            $push_notification  = $data['PushNotification'];
            
            $notification_text  = $data['NotificationText'];    
            $title              = $data['NotificationTitle']; 

            $user_ids_chunk = array_chunk($user_ids,500);            
            $this->db->select("AL.UserID, AL.DeviceToken, AL.DeviceTypeID");
            $this->db->from(ACTIVELOGINS . ' AL');
            foreach($user_ids_chunk as $user_ids) {
                $this->db->or_where_in('AL.UserID', $user_ids);
            }
            $this->db->where('AL.IsValidToken', 1);
            $this->db->where('AL.DeviceToken!=', '');
            $this->db->group_by('AL.DeviceToken');
            $this->db->group_by('AL.DeviceTypeID');
            $this->db->order_by('AL.ActiveLoginID', 'DESC');
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $results = $query->result_array();    
               
                $notification_data = array();
                $notification_data['PushNotification']  = $push_notification;
                if(empty($title)) {
                    $notification_data['Summary'] = '';
                    $message = $notification_text;
                } else {     
                    $notification_data['Summary'] = $notification_text; 
                    $message = $title;                 
                }
                foreach ($results as $notifications) {
                    $token = $notifications['DeviceToken'];                   
                    $notification_data['ToUserID']     = $notifications['UserID'];
                    $notification_data['DeviceTypeID'] = $notifications['DeviceTypeID'];
                    send_push_notification($token, $message, 1, $notification_data);
                }
            }
        }
        
    }
    
}

//End of file users_model.php
