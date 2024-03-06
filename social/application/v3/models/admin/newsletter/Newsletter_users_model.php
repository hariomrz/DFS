<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Newsletter_users_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model(array(
            'admin/media_model',
            'users/user_model',
            'util/util_location_model'
        ));
    }
    
    
    public function search_users($post_data) {
        
        $name = isset($post_data['Name']) ? $post_data['Name'] : 0;
        $userIncludeList = isset($post_data['userIncludeList']) ? $post_data['userIncludeList'] : [];
        
        $select_array[] = "CONCAT(NLS.FirstName, ' ', NLS.LastName) AS Name";
        $select_array[] = "NLS.FirstName,  NLS.LastName";
        $select_array[] = "U.ProfilePicture AS ProfilePicture";        
        $select_array[] = "NLS.NewsLetterSubscriberID, NLS.Email";
        
        $this->db->select(implode(',', $select_array), FALSE);
        
        
        $this->db->from(NEWSLETTERSUBSCRIBER . "  NLS ");
        $this->db->join(USERS . " U ", "U.UserID = NLS.UserID ", "left");
        
        if(!empty($userIncludeList)) {
            $this->db->where_in("NLS.NewsLetterSubscriberID", $userIncludeList);
        } else {
            $name = $this->db->escape_like_str($name);
            $this->db->where(" CONCAT(NLS.FirstName, ' ', NLS.LastName) LIKE '%$name%' ", NULL, FALSE);
        }
        
        
        
        
        //$this->db->group_by('NLS.NewsLetterSubscriberID');
        
        
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query; die;
        $this->db->reset_query();
        
        $query = $this->db->query($compiled_query);
        $users = $query->result_array();
        
        foreach ($users as $key => $user) {            
            $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
            $user['ProfilePictureUrl'] = get_image_path($profileSection, $user['ProfilePicture'], ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);    
            
            $users[$key] = $user;
        }
        
        
        return $users;
    }
    

    /**
     * Function for get users detail for Users listing for crm 
     * Parameters : Data array
     * Return : Users array + total count
     */
    public function get_users($post_data, $is_return_query = false, $is_real_query = false) {

        $page_no = (int) isset($post_data['PageNo']) ? $post_data['PageNo'] : 1;
        $page_size = (int) isset($post_data['PageSize']) ? $post_data['PageSize'] : 20;

        $Locations = isset($post_data['Locations']) ? $post_data['Locations'] : [];
        $citi_ids = [];
        foreach ($Locations as $Location) {
            $city_id = $this->util_location_model->get_city_id($Location);
            $citi_ids[] = $city_id;
        }

        if (!$page_no)
            $page_no = 1;
        $offset = ($page_no - 1) * $page_size;

        $this->query_select_users($is_return_query);

        $this->db->from(NEWSLETTERSUBSCRIBER . "  NLS ");
        $this->db->join(USERS . " U ", "U.UserID = NLS.UserID ", "left");
        $this->db->join(PROFILEURL . ' PU ', "U.UserID=PU.EntityID AND PU.EntityType='User'", "left");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left');

        $this->db->join(CITIES . ' CT ', 'CT.CityID = NLS.CityID', 'left');
        
        
        
        

        $this->query_filter_users($post_data, $citi_ids);

        //$this->db->where_not_in('U.StatusID', array(3, 4));
        $this->db->group_by('NLS.NewsLetterSubscriberID');
        
        
        
        $check_group_members = (int) isset($post_data['CheckGrpMembers']) ? $post_data['CheckGrpMembers'] : 0;
        $check_group_members_excluded = (int) isset($post_data['CheckGrpMembersExcluded']) ? $post_data['CheckGrpMembersExcluded'] : 0;    
        $deletedIncludedUsers = isset($post_data['deletedIncludedUsers']) ? $post_data['deletedIncludedUsers'] : [];
        if($check_group_members || $deletedIncludedUsers) {            
            $check_group_member_cnd = ($check_group_members_excluded || $deletedIncludedUsers) ? 'NLGM.NewsLetterSubscriberID = NLS.NewsLetterSubscriberID' : 'NLGM.NewsLetterSubscriberID != NLS.NewsLetterSubscriberID';            
            $NewsLetterGroupID = (int) isset($post_data['NewsLetterGroupIDNew']) ? $post_data['NewsLetterGroupIDNew'] : 0;
            $this->db->join(NEWSLETTERGROUPMEMBER . ' NLGM ', $check_group_member_cnd, 'left');
            $this->db->where('NLGM.NewsLetterSubscriberID IS NOT NULL', NULL, false);
            $this->db->where('NLGM.NewsLetterGroupID', $NewsLetterGroupID);       
                        
        }                
        

        //Here we clone the DB object for get all Count rows
        $tempdbR = clone $this->db;          
        $tempdbR->where('U.UserID IS NOT NULL');        
        $temp_q_R = $tempdbR->get();
        $total_users_R = $temp_q_R->num_rows();
        
        // Total unregistered users
        $tempdbUR = clone $this->db;  
        $tempdbUR->where('U.UserID IS NULL');        
        $temp_q_UR = $tempdbUR->get();  //echo $this->db->last_query(); echo '======================================';
        $total_users_UR = $temp_q_UR->num_rows();
        
        $totalShowing = $total_users = $total_users_R + $total_users_UR;
        
        $user_type = isset($post_data['UserType']) ? $post_data['UserType'] : 0;
        if($user_type) {
            if($user_type == 1) { // For registered users
                $total_users = $total_users_R;
                $this->db->where('U.UserID IS NOT NULL');
            } elseif ($user_type == 2) { // For only subscribers
                $total_users = $total_users_UR;
                $this->db->where('U.UserID IS NULL');
            }
        }
        
        $this->query_order_users($post_data);

        /* Start_offset, end_offset */
        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }

              
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query; echo '======================================';
        $this->db->reset_query();

        if ($is_real_query) {
            return $compiled_query;
        }

        //echo $compiled_query; echo '======================================'; //die;

        $query = $this->db->query($compiled_query);
        //$query = $this->db->get();
        $users = $query->result_array();  //echo $this->db->last_query(); echo '======================================';

        if ($is_return_query) {
            $user_ids = [0];
            foreach ($users as $user) {
                $user_ids[] = $user['NewsLetterSubscriberID'];
            }

            return $user_ids;
        }

        $users = $this->result_set_filter_users($users);
        
        
        
        
        return array(
            'total' => $total_users,
            'totalRecordShowing' => $totalShowing,
            'total_r' => $total_users_R,
            'total_ur' => $total_users_UR,
            'users' => $users
        );
    }

    public function query_order_users($order_data) {

        $order_by_field = isset($order_data['OrderByField']) ? $order_data['OrderByField'] : 'FirstName';
        $order_by = isset($order_data['OrderBy']) ? $order_data['OrderBy'] : 'DESC';

        $allowed_order_by_fields = [
            'FirstName' => 'NLS.FirstName',                        
            'RECENT_ACTIVE' => 'NLS.NewsLetterSubscriberID',
            'UserID' => 'NLS.NewsLetterSubscriberID',            
            'AverageScore' => 'UD.AverageScore'
        ];

        if (!in_array($order_by_field, array_keys($allowed_order_by_fields))) {
            $order_by_field_db = $allowed_order_by_fields['UserID'];
        } else {
            $order_by_field_db = $allowed_order_by_fields[$order_by_field];
        }

        if (!in_array($order_by, ['ASC', 'DESC'])) {
            $order_by = 'DESC';
        }

        if ($order_by_field == 'RECENT_ACTIVE') {
            //$this->db->join(USERSACTIVITYLOG . ' UAL', 'U.UserID = UAL.UserID', 'left');  
        }


        $this->db->order_by($order_by_field_db, $order_by);
    }

    public function query_select_users($is_return_query) {

        if ($is_return_query) {
            $select_array[] = "NLS.NewsLetterSubscriberID";
            $this->db->select(implode(',', $select_array), FALSE);
            return;
        }


        $today_date = get_current_date('%Y-%m-%d %H:%i:%s');

        /* Load Global settings */
        /* Change date_format into mysql date_format */
        $global_settings = $this->config->item("global_settings");
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $time_format = dateformat_php_to_mysql($global_settings['time_format']);

        $select_array[] = " IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', NLS.DOB)), '%Y')+0, '') AS Age";
        $select_array[] = "IFNULL(CT.Name, '') AS City";
        $select_array[] = "IFNULL(NLS.Gender, '') Gender";
        $select_array[] = "IFNULL(NLS.Email, '') Email";
        //$select_array[] = "DATE_FORMAT(NLS.CreatedDate, '" . $mysql_date . "') AS CreatedDate";
        $select_array[] = "IFNULL(U.UserID, 0) UserID";
        $select_array[] = "CONCAT(NLS.FirstName, ' ', NLS.LastName) AS Name";
        $select_array[] = "NLS.FirstName,  NLS.LastName, NLS.DOB, NLS.TotalEmailSent";
        $select_array[] = "U.ProfilePicture AS ProfilePicture";
        $select_array[] = "NLS.Status";
        $select_array[] = "NLS.NewsLetterSubscriberID";
        $select_array[] = 'DATE_FORMAT(NLS.CreatedDate, "' . $mysql_date . '") AS CreatedDate';
        $select_array[] = 'DATE_FORMAT(NLS.ModifiedDate, "' . $mysql_date . '") AS ModifiedDate';

        $this->db->select(implode(',', $select_array), FALSE);

        $sub = $this->subquery->start_subquery('select');
        $sub->select('GROUP_CONCAT(UR.RoleID)', FALSE)->from(USERROLES . ' AS UR');
        $sub->where('UR.UserID = U.UserID');
        $this->subquery->end_subquery('userroleid');
    }

    public function query_filter_users($filter, $citi_ids) {

        $age_group_id = isset($filter['AgeGroupID']) ? $filter['AgeGroupID'] : 0;
        $gender = isset($filter['Gender']) ? $filter['Gender'] : 0;
        $search_key = isset($filter['SearchKey']) ? $filter['SearchKey'] : '';
        $tag_user_type = isset($filter['TagUserType']) ? $filter['TagUserType'] : [];
        $tag_user_search_type = isset($filter['TagUserSearchType']) ? $filter['TagUserSearchType'] : 0;  //TagUserSearchType (0=> AND 1=> OR)
        $tag_tag_type = isset($filter['TagTagType']) ? $filter['TagTagType'] : [];
        $tag_tag_search_type = isset($filter['TagTagSearchType']) ? $filter['TagTagSearchType'] : 0;  //TagTagSearchType (0=> AND 1=> OR)
        $NewsLetterSubscriberID = isset($filter['NewsLetterSubscriberID']) ? $filter['NewsLetterSubscriberID'] : [];
        $user_status = (int) isset($filter['StatusID']) ? $filter['StatusID'] : 0;
        $userExcludeList = isset($filter['userExcludeList']) ? $filter['userExcludeList'] : [];

        $start_date = isset($filter['StartDate']) ? $filter['StartDate'] : '';
        $end_date = isset($filter['EndDate']) ? $filter['EndDate'] : '';

        $age_start = isset($filter['AgeStart']) ? $filter['AgeStart'] : '';
        $age_end = isset($filter['AgeEnd']) ? $filter['AgeEnd'] : '';
        $NewsLetterGroupID = isset($filter['NewsLetterGroupID']) ? $filter['NewsLetterGroupID'] : 0;        
        $IncompleteProfileDays = isset($filter['IncompleteProfileDays']) ? $filter['IncompleteProfileDays'] : 0;        
        $InactiveProfileDays = isset($filter['InactiveProfileDays']) ? $filter['InactiveProfileDays'] : 0;        
        $IncompleteProfile = isset($filter['IncompleteProfile']) ? $filter['IncompleteProfile'] : 0;        
        $InactiveProfile = isset($filter['InactiveProfile']) ? $filter['InactiveProfile'] : 0;
        
        $userIncludeList = isset($filter['userIncludeList']) ? $filter['userIncludeList'] : [];
        $deletedIncludedUsers = isset($filter['deletedIncludedUsers']) ? $filter['deletedIncludedUsers'] : [];
        
        
        
        // For getting rules excluded users
        $check_group_members_excluded_query = isset($filter['CheckGrpMembersExcludedQuery']) ? $filter['CheckGrpMembersExcludedQuery'] : '';                
        if($check_group_members_excluded_query || $deletedIncludedUsers) {  
            $select_array = [];
            $select_array[] = 'NLGM.MailchimpSubscriberID';
            $this->db->select(implode(',', $select_array), FALSE); 
            if($deletedIncludedUsers) {
                $this->db->where_in("NLS.NewsLetterSubscriberID", $deletedIncludedUsers);  
                return;
            }
            $this->db->where("NLS.NewsLetterSubscriberID IN($check_group_members_excluded_query)", NULL, FALSE);  
            return;
        }
        
        
        if (count($userIncludeList)) {
            $this->db->where('(( 1  ', NULL, FALSE);
        }
        

        /* start_date, end_date for filters */
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            if (in_array($user_status, array(3, 4))) {
                $this->db->where('DATE(NLS.ModifiedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
            } else {
                $this->db->where('DATE(NLS.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
            }
        }
        
        
        /* days filters for inactive profile */
        if ($InactiveProfile && $InactiveProfileDays) {            
            $InactiveProfileDays_date = get_current_date('%Y-%m-%d', intval($InactiveProfileDays));
            $todays_date = get_current_date('%Y-%m-%d');     
            
            $this->db->where(" (UD.LastLoginDate <= '$InactiveProfileDays_date' )", NULL, FALSE);            
            //$this->db->where('DATE(NLS.CreatedDate) BETWEEN "' . $InactiveProfileDays_date . '"  AND "' . $todays_date . '"', NULL, FALSE);
        }
        
        /* days filters for incomplete profile */
        if ($IncompleteProfile && $IncompleteProfileDays) {            
            $IncompleteProfileDays_date = get_current_date('%Y-%m-%d', intval($IncompleteProfileDays));
            $todays_date = get_current_date('%Y-%m-%d');     
            $this->db->where('U.IsProfileSetup', 0);
            $this->db->where('DATE(NLS.CreatedDate) BETWEEN "' . $IncompleteProfileDays_date . '"  AND "' . $todays_date . '"', NULL, FALSE);
        }
        

//        if (!empty($user_status) && $user_status != 500) {
//            if ($user_status == 1) {
//                $this->db->where_in('U.StatusID', array(1, 6));
//            } else if ($user_status == 2) {
//                $this->db->where_in('U.StatusID', array(2, 7));
//            } else {
//                $this->db->where('U.StatusID', $user_status);
//            }
//        }

        $this->db->where('NLS.Status', 2);


        if ($NewsLetterGroupID) {
            $this->db->join(NEWSLETTERGROUPMEMBER . " NGM ", "NGM.NewsLetterSubscriberID = NLS.NewsLetterSubscriberID ", "inner");
            $this->db->where('NGM.NewsLetterGroupID', $NewsLetterGroupID);
        }

        if ($search_key) {
            $this->db->like('CONCAT(NLS.FirstName," ", NLS.LastName)', $search_key);
        }

        if ($gender) {
            $gender = ($gender == 3) ? 0 : $gender;
            $this->db->where('NLS.Gender', $gender);
        }

        if (count($citi_ids)) {
            $string_str_ids = implode(',', $citi_ids);
            $this->db->where(" (NLS.CityID IN ( $string_str_ids ) ) ", NULL, FALSE);
        }

        if (count($NewsLetterSubscriberID)) {
            $this->db->where_in('NLS.NewsLetterSubscriberID', $NewsLetterSubscriberID);
        }

        if (count($userExcludeList)) {
            $this->db->where_not_in('NLS.NewsLetterSubscriberID', $userExcludeList);
        }

        if ($age_group_id) {
            //$this->db->join(ANALYTICLOGINS . ' AL ', 'AL.AnalyticLoginID = UAL.AnalyticLoginID', 'left');
            $ana_table = ANALYTICLOGINS;
            $select_analytics = "U.UserID = ( Select AL.UserID From $ana_table AL WHERE AL.UserID = U.UserID AND AL.AgeGroupID = $age_group_id ORDER BY AL.AnalyticLoginID DESC LIMIT 1 )";

            $this->db->where($select_analytics, NULL, FALSE);
        }


        if (!$age_start) {
            $age_start = 0;
        }

        if (!$age_end) {
            $age_end = 0;
        }

        if ($age_start || $age_end) {
            $today_date = get_current_date('%Y-%m-%d %H:%i:%s');
            if ($age_start && $age_end) {
                //$this->db->having("Age >= $age_start AND Age <= $age_end", NULL, FALSE);
                $this->db->where("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', NLS.DOB)), '%Y')+0, '') >= $age_start AND IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', NLS.DOB)), '%Y')+0, '') <= $age_end", NULL, FALSE);
            } else if ($age_start) {
                //$this->db->having("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') >= $age_start", NULL, FALSE);
                $this->db->where("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', NLS.DOB)), '%Y')+0, '') >= $age_start", NULL, FALSE);
            } else if ($age_end) {
                //$this->db->having("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') <= $age_end", NULL, FALSE);
                $this->db->where("IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', NLS.DOB)), '%Y')+0, '') <= $age_end", NULL, FALSE);
            }
        }
        
        if(!function_exists('tag_select_query')) {
            
        

            function tag_select_query($tag_ids, $tag_types, $searchType, $objCtx, $tagSerachType = 1) {
            //1- Normal Tag, 2- Hash Tag, 3- Activity Mood, 4- Activity Classification, 5- User/Reader Tag, 6- User Profession, 7- Brand
            if (!$tag_ids) {
                return;
            }

            $tbl_als_et = ($tagSerachType) ? 'ET' : 'ET1';
            $tbl_als_tg = ($tagSerachType) ? 'TG' : 'TG1';

            $join_conds = "( "
                    . "( $tbl_als_et.EntityID = U.UserID AND $tbl_als_et.EntityType = 'USER' ) "
                    . " OR "
                    . "( $tbl_als_et.EntityID = NLS.NewsLetterSubscriberID AND $tbl_als_et.EntityType = 'NEWSLETTER_SUBSCRIBER' ) "
                    . " ) ";

            $objCtx->db->join(ENTITYTAGS . " $tbl_als_et", "  $join_conds  AND $tbl_als_et.StatusID = 2", 'left', FALSE);
            $objCtx->db->join(TAGS . " $tbl_als_tg", "$tbl_als_tg.TagID = $tbl_als_et.TagID", 'left');

//            $eJA = 'eJA'; // Extra Join alias
//            $tbl_als_et = $tbl_als_et.$eJA;
//            $tbl_als_tg = 
//            
//            $objCtx->db->join(ENTITYTAGS . " $tbl_als_et", "  $join_conds  AND $tbl_als_et.StatusID = 2", 'left', FALSE);
//            $objCtx->db->join(TAGS . " $tbl_als_tg", "$tbl_als_tg.TagID = $tbl_als_et.TagID", 'left');

            if ($tagSerachType) {
                //$objCtx->db->where_in("$tbl_als_tg.TagType", $tag_types);
            } else {
                //$objCtx->db->where_not_in("$tbl_als_tg.TagType", $tag_types);
            }

            if ($searchType) { //Match any
                $objCtx->db->where_in("$tbl_als_et.TagID", $tag_ids);
            } else if (count($tag_ids)) {
                // Match all
                $in_tag_ids = implode(',', $tag_ids);
                $total_tag_ids = count($tag_ids);
                $and_conditions = "(select COUNT(DISTINCT ET_COUNT.`TagID`) from `EntityTags` ET_COUNT 
                Where 
                
                (
                (ET_COUNT.`EntityID` = `U`.`UserID` 
                AND `ET_COUNT`.`EntityType` = 'USER')
                
                OR

                (ET_COUNT.`EntityID` = `NLS`.`NewsLetterSubscriberID` 
                AND `ET_COUNT`.`EntityType` = 'NEWSLETTER_SUBSCRIBER')
                )

                AND `ET_COUNT`.`StatusID` = 2
                AND  ET_COUNT.`TagID` IN ($in_tag_ids) ) >= $total_tag_ids  ";

                $objCtx->db->where($and_conditions, NULL, false);
            }
        }
        
        }

        $tag_user_type_tag_ids = [];
        foreach ($tag_user_type as $tag_user_type_tag) {
            $tag_user_type_tag_ids[] = $tag_user_type_tag['TagID'];
        }

        $tag_tag_type_tag_ids = [];
        foreach ($tag_tag_type as $tag_tag_type_tag) {
            $tag_tag_type_tag_ids[] = $tag_tag_type_tag['TagID'];
        }

        tag_select_query($tag_user_type_tag_ids, [6, 7], $tag_user_search_type, $this, 0);
        tag_select_query($tag_tag_type_tag_ids, [6, 7], $tag_tag_search_type, $this, 1);
        
        
        if (count($userIncludeList)) {
            $this->db->where(' 1 ) ', NULL, FALSE);
        }
        
        if (count($userIncludeList)) {
            $userIncludeListStr = implode(', ', $userIncludeList);
            $this->db->or_where("( NLS.NewsLetterSubscriberID IN ($userIncludeListStr) ))", NULL, FALSE);
        }
        
    }

    public function set_location_and_tags($user) {

        $user['Tags'] = [];
        $user['UserTypeTags'] = [];
        $user['LocationStr'] = '';

        // Set user location
        if ($user['UserID']) {
            $user['Location'] = $this->user_model->get_user_location_admin($user['UserID'], 0, 1);
            if (empty($user['Location']['City'])) {
                $user['Location'] = $this->user_model->get_user_location_admin($user['UserID'], 1);
            }
        } else {
            $user['Location'] = $this->user_model->get_user_location_admin($user['NewsLetterSubscriberID'], 0, 1, 1);
        }


        // Set location String
        if (!empty($user['Location']['City']) && !empty($user['Location']['State']) && !empty($user['Location']['Country'])) {
            $user['LocationStr'] = $user['Location']['City'] . ', ' . $user['Location']['State'] . ', ' . $user['Location']['Country'];
        }

        // set user tags
        $user_tag_identity = 0;
        if (empty($user['UserID'])) {
            $user_tag_identity = $user['NewsLetterSubscriberID'];
            $userTags = $this->set_entity_tags([], [$user['NewsLetterSubscriberID']], 'NEWSLETTER_SUBSCRIBER');
        } else {
            $user_tag_identity = $user['UserID'];
            $userTags = $this->set_entity_tags([], [$user['UserID']], 'USER');
        }

        $user['Tags'] = isset($userTags[$user_tag_identity]['Tags']) ? $userTags[$user_tag_identity]['Tags'] : [];
        $user['UserTypeTags'] = isset($userTags[$user_tag_identity]['UserTypeTags']) ? $userTags[$user_tag_identity]['UserTypeTags'] : [];

        // Convert to string tags
        $tags = [];
        foreach ($user['Tags'] as $tag) {
            $tags[] = $tag['Name'];
        }
        $user['TagsStr'] = implode(',', $tags);

        $tags = [];
        foreach ($user['UserTypeTags'] as $tag) {
            $tags[] = $tag['Name'];
        }
        $user['UserTypeTagsStr'] = implode(',', $tags);

        return $user;
    }

    public function result_set_filter_users($users) {
        $entity_ids = [];
        $newKeyedUsers = [];
        foreach ($users as $user) {
            $user['Name'] = stripslashes($user['Name']);
            $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
            $user['ProfilePictureUrl'] = get_image_path($profileSection, $user['ProfilePicture'], ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
            $user = $this->set_location_and_tags($user);
            //$entity_ids[] = $user['UserID'];
            $newKeyedUsers[$user['NewsLetterSubscriberID']] = $user;
        }

        $users = $newKeyedUsers;
        $make_browser_compatible_obj = [];
        foreach ($users as $user_id => $user) {
            $make_browser_compatible_obj['pre' . $user_id] = $user;
        }

        return $make_browser_compatible_obj;
    }

    public function set_entity_tags($users, $entity_ids, $entity_type, $added_by = NULL) {

        if (!$entity_ids) {
            return $users;
        }

        $select_array[] = "TG.TagID, TG.Name, TG.TagType, ET.AddedBy,TG.Name AS text, ET.EntityID";

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(ENTITYTAGS . ' ET');
        $this->db->join(TAGS . ' TG', 'TG.TagID = ET.TagID', 'left');

        $this->db->where('ET.EntityType', $entity_type);
        $this->db->where('ET.StatusID', 2);
        $this->db->where_in('ET.EntityID', $entity_ids);

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
            } else if (in_array($tag_entity['TagType'], [5])) {
                $users[$user_id]['UserTypeTags'][] = $tag_entity;
            }
        }
        //print_r($tag_entities); print_r($users);
        return $users;
    }

    public function download_users($users) {

        $file_arr = [];

        $fields_with_alies = array(
            'FirstName' => 'FirstName',
            'LastName' => 'LastName',
            'Email' => 'Email',
            'Gender' => 'Gender',
            'DOB' => 'DOB',
            'City' => 'City',
            'State' => 'State',
            'Country' => 'Country',
            'UserTypeTags' => 'UserType',
            'Tags' => 'UserTags',
        );

        function formatUserObjectField($user, $field_key) {
            if (isset($user[$field_key]) && is_array($user[$field_key])) {
                $tags = $user[$field_key];
                $tags_str = '';
                $tagsArr = [];
                foreach ($tags as $tag) {
                    $tagsArr[] = $tag['Name'];
                }
                $tags_str = implode(', ', $tagsArr);
                return stripslashes($tags_str);
            }


            if ($field_key == 'City' || $field_key == 'State' || $field_key == 'Country') {
                $locationStr = '';
                $locationStr = isset($user['Location'][$field_key]) ? $user['Location'][$field_key] : '';
                return stripslashes($locationStr);
            }


            if ($field_key == 'Gender') {
                $genderArr = array(
                    0 => 'Other',
                    1 => 'Male',
                    2 => 'Female',
                );
                if ($user[$field_key]) {
                    $user[$field_key] = $genderArr[$user[$field_key]];
                } else {
                    $user[$field_key] = 'Other';
                }
            }

            return stripslashes($user[$field_key]);
        }

        $excelInput = array();
        foreach ($users as $user) {
            foreach ($fields_with_alies as $field_key => $field_val) {
                $userArr[$field_key] = formatUserObjectField($user, $field_key);
            }
            $excelInput[] = $userArr;
        }

        $sheetTitle = 'Subscribers';
        $dateFilterText = '';

        $excelArr = array();
        $excelArr['headerArray'] = $fields_with_alies;
        $excelArr['sheetTitle'] = $sheetTitle;
        $excelArr['fileName'] = "Subscribers_list.xls";
        $excelArr['folderPath'] = DOC_PATH . ROOT_FOLDER . '/' . PATH_IMG_UPLOAD_FOLDER . "csv_file/";
        $excelArr['inputData'] = $excelInput;
        $excelArr['ReportHeader'] = ""; //array("ReportName" => $sheetTitle, "dateFilterText" => $dateFilterText);


        if ($this->downloadExcelFile($excelArr)) {
            $csv_url = base_url() . 'admin/newsletter/downloadsubscribers';
            $file_arr['csv_url'] = $csv_url;
        }

        return $file_arr;
    }

    public function change_user_status($posted_data) {
        
        $userListObj = isset($posted_data['userListReqObj']) ? $posted_data['userListReqObj'] : [];
        $newsletter_query = $this->get_users($userListObj, true);   //print_r($newsletter_query); die;
        if (!$newsletter_query) {
            return;
        }                        
        $newsletter_query_str = $newsletter_query;
        $newsletter_query_str = implode(',', $newsletter_query_str);
        $data = array('Status' => (int) $posted_data['Status'], 'ModifiedDate' => date('Y-m-d H:i:s'));
        $this->db->where("NewsLetterSubscriberID IN ($newsletter_query_str) ", NULL, FALSE);
        $this->db->update(NEWSLETTERSUBSCRIBER, $data);
        
        initiate_worker_job('change_newsletter_user_status_process', $posted_data);                    
        //$this->change_user_status_process($posted_data);
    }
    
    public function change_user_status_process($posted_data) {
        $userListObj = isset($posted_data['userListReqObj']) ? $posted_data['userListReqObj'] : [];
        $newsletter_query = $this->get_users($userListObj, true);   //print_r($newsletter_query); die;
        if (!$newsletter_query) {
            return;
        }

        $this->load->model(array( 'admin/newsletter/newsletter_mailchimp_model', 'admin/newsletter/newsletter_model'));
        
        
//        $newsletter_query_str = $newsletter_query;
//        $newsletter_query_str = implode(',', $newsletter_query_str);
//        $data = array('Status' => (int) $posted_data['Status'], 'ModifiedDate' => date('Y-m-d H:i:s'));
//        $this->db->where("NewsLetterSubscriberID IN ($newsletter_query_str) ", NULL, FALSE);
//        $this->db->update(NEWSLETTERSUBSCRIBER, $data);
        
        
        // update all groups for these users
        $newsletter_subsribers_groups = $this->newsletter_model->get_user_groups(array('NewsLetterSubscriberID' => $newsletter_query));
        $MemberDeleteBatch = array();        
        foreach($newsletter_subsribers_groups as $newsletter_subsribers_group) {
            // Remove subscriber from groups
            $WhereCondition = array('NewsLetterGroupID' => $newsletter_subsribers_group['NewsLetterGroupID']);
            $this->newsletter_model->remove_subscribers_from_group($WhereCondition, $newsletter_subsribers_group['NewsLetterSubscriberID']);
            $resp = $this->newsletter_model->update_group_total_member($newsletter_subsribers_group['NewsLetterGroupID']);
                                                
            $MailchimpListID = $newsletter_subsribers_group['MailchimpListID'];            
            $mailchimp_subscriber_id = !empty($newsletter_subsribers_group['MailchimpSubscriberID']) ? $newsletter_subsribers_group['MailchimpSubscriberID'] : md5(strtolower($newsletter_subsribers_group['Email']));  
                        
            $operationsArr = array(
                'method' => "DELETE",
                'path' => "lists/" . $MailchimpListID . "/members/" . $mailchimp_subscriber_id
            );
            array_push($MemberDeleteBatch, $operationsArr);                        
            
        }
        
        
        //call mailchimp batch operation for delete selected subscribers from list
        if (!empty($MemberDeleteBatch)) {
            $batchResponse = $this->newsletter_mailchimp_model->remove_group_subscribers($MemberDeleteBatch);
        }
                    
    }
    
    


    public function add_subscribers($users = array()) {
        $inserting_users = [];

        foreach ($users as $index => $user) {
            
        }

        // $this->db->insert_batch(NEWSLETTERGROUPMEMBER, $GroupSubscriberData); 
        $this->db->insert_on_duplicate_update_batch(NEWSLETTERGROUPMEMBER, $inserting_users);
    }

    public function update_newsletter_user_data($email) {

        $this->db->select('U.*, NLS.NewsLetterSubscriberID, NLS.UserID AS NLS_UserID, UD.DOB', FALSE);
        $this->db->from(NEWSLETTERSUBSCRIBER . "  NLS ");
        $this->db->join(USERS . " U ", "U.Email = NLS.Email ", "left");
        $this->db->join(USERDETAILS . " UD ", "UD.UserID = U.UserID ", "left");
        $this->db->where('NLS.Status', 2);
        $this->db->where('NLS.Email', $email);
        $compiled_query = $this->db->_compile_select();
        $this->db->reset_query();
        $query = $this->db->query($compiled_query);
        $user = $query->row_array();


        $this->update_newsletter_subscriber_details($user, array(
            'NewsLetterSubscriberID' => $user['NewsLetterSubscriberID']
        ));


        // Update entity tags newsletter type to user for this user

        $this->db->select('ET.EntityTagID', FALSE);
        $this->db->from(ENTITYTAGS . "  ET ");
        $this->db->where('ET.EntityType', 'NEWSLETTER_SUBSCRIBER');
        $this->db->where('ET.EntityID', $user['NewsLetterSubscriberID']);
        $compiled_query = $this->db->_compile_select();
        $this->db->reset_query();
        $query = $this->db->query($compiled_query);
        $entity_tags = $query->result_array();

        $entity_tag_ids = [];
        foreach ($entity_tags as $entity_tag) {
            $entity_tag_ids[] = $entity_tag['EntityTagID'];
        }

        // If no tags ids  than return;
        if (!$entity_tag_ids) {
            return;
        }

        $entity_tags_update_arr = array(
            'EntityType' => 'USER',
            'EntityID' => $user['UserID']
        );

        $this->db->where('EntityType', 'NEWSLETTER_SUBSCRIBER');
        $this->db->where('EntityID', $user['NewsLetterSubscriberID']);
        $this->db->where_in('EntityTagID', $entity_tag_ids);
        $this->db->update(ENTITYTAGS, $entity_tags_update_arr);
    }

    /**
     * [save_introduction Used to SAVE introduction of the user]
     * @param  [int] $user_id            [Logged in User ID]
     * @param  [array] $user_details 	 [array of user introduction]
     */
    public function save_user_info($user_id, $user_details) {

        $subscriber_details = $this->db->select("NewsLetterSubscriberID")
                        ->from(NEWSLETTERSUBSCRIBER)
                        ->where('UserID', $user_id)
                        ->get()->row_array();

        if (empty($subscriber_details['NewsLetterSubscriberID'])) {
            return;
        }

        $allowed_fields = array(
            'FirstName',
            'LastName',
            'DOB',
            'Gender',
            'CityID',
            'Email',
        );

        $data = array();
        foreach ($user_details as $key => $val) {
            if (!in_array($key, $allowed_fields)) {
                continue;
            }

            $data[$key] = $val;
        }

        if (empty($data)) {
            return;
        }

        $this->db->where('UserID', $user_id);
        $this->db->update(NEWSLETTERSUBSCRIBER, $data);
    }

    /**
     * Function for update newsletter subscriber's details by userid in newsletter 
     * Parameters : Data array
     * Return : newly added subscriber's id
     */
    public function update_newsletter_subscriber_details($UpdateDetails, $UpdateWhere) {
        if (empty($UpdateWhere) || empty($UpdateDetails)) {
            return false;
        }

        $UpdateSubscriber = array();

        if (!empty($UpdateDetails['Location']['City'])) {
            $this->load->helper('location');
            $UpdateDetails['Location'] = update_location($UpdateDetails['Location']);
            $UpdateDetails['CityID'] = !empty($UpdateDetails['Location']['CityID']) ? $UpdateDetails['Location']['CityID'] : 0;
        }


        // Update tags data
        if (isset($UpdateDetails['Tags']) && isset($UpdateDetails['UserTypeTags']) && is_array($UpdateDetails['Tags']) && is_array($UpdateDetails['UserTypeTags'])) {
            $this->load->model(array('tag/tag_model'));

            $entity_id = $UpdateWhere['NewsLetterSubscriberID'];
            $user_id = $UpdateWhere['NewsLetterSubscriberID'];


            // user type tags
            $tags_data = $UpdateDetails['UserTypeTags'];
            foreach ($tags_data as $index => $tag) {
                if (empty($tag['text'])) {
                    continue;
                    ;
                }
                $tags_data[$index] = array(
                    'Name' => $tag['text']
                );
            }
            $tag_type_id = $this->tag_model->get_tag_type_id('USER');
            $tags_data_user_types = $this->tag_model->save_tag($tags_data, $tag_type_id, $user_id);

            if ($tags_data_user_types === NULL) {
                $tags_data_user_types = [];
            }


            // user tags 
            $tags_data = $UpdateDetails['Tags'];
            foreach ($tags_data as $index => $tag) {
                if (empty($tag['text'])) {
                    continue;
                    ;
                }
                $tags_data[$index] = array(
                    'Name' => $tag['text']
                );
            }
            $tag_type_id = $this->tag_model->get_tag_type_id('PROFESSION');
            $tags_data = $this->tag_model->save_tag($tags_data, $tag_type_id, $user_id);

            if ($tags_data === NULL) {
                $tags_data = [];
            }


            // Update entity tags
            $tags_data = array_merge($tags_data, $tags_data_user_types);



            //$tags_data = $this->tag_model->save_tag($UpdateDetails['Tags'], $tag_type_id, $user_id);
            $return['Data'] = $this->tag_model->save_entity_tag($tags_data, 'NEWSLETTER_SUBSCRIBER', $entity_id, $user_id, false, true, FALSE, TRUE);
        }



        if (isset($UpdateDetails['FirstName']))
            $UpdateSubscriber['FirstName'] = $UpdateDetails['FirstName'];

        if (isset($UpdateDetails['LastName']))
            $UpdateSubscriber['LastName'] = $UpdateDetails['LastName'];

        if (!empty($UpdateDetails['DOB']))
            $UpdateSubscriber['DOB'] = date("Y-m-d", strtotime($UpdateDetails['DOB']));

        if (isset($UpdateDetails['Gender']))
            $UpdateSubscriber['Gender'] = $UpdateDetails['Gender'];

        if (isset($UpdateDetails['CityID']))
            $UpdateSubscriber['CityID'] = $UpdateDetails['CityID'];

        if (isset($UpdateDetails['UserID']) && !empty($UpdateDetails['UserID']))
            $UpdateSubscriber['UserID'] = $UpdateDetails['UserID'];

        if (!empty($UpdateSubscriber)) {
            $UpdateSubscriber['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->db->where($UpdateWhere);
            $this->db->update(NEWSLETTERSUBSCRIBER, $UpdateSubscriber);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function for add newly registered user to newsletter subscription list
     * Parameters : $SubscriberDetail
     * Return : Status : true/false
     */
    public function add_newsletter_subscription($SubscriberDetail = array()) {
        $return = TRUE;
        if (empty($SubscriberDetail['UserID'])) {
            return $return;
        }

        $user_details = $this->db->select("UserID,Longitude,Latitude,IPAddress,Email,FirstName,LastName")
                        ->from(USERS)
                        ->where('UserID', $SubscriberDetail['UserID'])
                        ->get()->row_array();
        if (empty($user_details)) {
            return $return;
        } else {
            $SubscriberDetail = $user_details;
        }

        $this->load->model('admin/newsletter/newsletter_model');
        $InsertSubscriber = array();
        $InsertSubscriber['FirstName'] = $SubscriberDetail['FirstName'];
        $InsertSubscriber['LastName'] = $SubscriberDetail['LastName'];
        $InsertSubscriber['UserID'] = $SubscriberDetail['UserID'];
        $this->load->helper('location');
        $locationDetails = get_location_details($SubscriberDetail['IPAddress'], $SubscriberDetail['Latitude'], $SubscriberDetail['Longitude']);
        $InsertSubscriber['CityID'] = (!empty($locationDetails['CityID'])) ? $locationDetails['CityID'] : NULL;

        //check Email already exist in subscription list or not
        $where = array('Email' => EscapeString($SubscriberDetail['Email']));
        $already_subscribed = $this->newsletter_model->is_valid_subscriber($where);

        //if already subscribed then update infomation
        if ($already_subscribed) {
            $this->update_newsletter_subscriber_details($InsertSubscriber, $where);
        } else { //else create new subscription
            $InsertSubscriber['Email'] = strtolower($SubscriberDetail['Email']);
            $InsertSubscriber['NewsLetterSubscriberGUID'] = get_guid();
            $InsertSubscriber['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $InsertSubscriber['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $subscribed = $this->newsletter_model->add_newsletter_subscriber($InsertSubscriber);
            if (!$subscribed) {
                $return = FALSE;
            }
        }

        return $return;
    }

    public function validate_file_row($row, $values, $actual_row_index, $file_data, $errorReport) {

        $errors = [];

        $mandatory_check[$row] = array(
            'A' => array('value' => 'FirstName', 'exists' => 0, 'required' => 1),
            'B' => array('value' => 'LastName', 'exists' => 0, 'required' => 1),
            'C' => array('value' => 'Email', 'exists' => 0, 'required' => 1),
            'D' => array('value' => 'Gender', 'exists' => 0, 'required' => 0),
            'E' => array('value' => 'DOB', 'exists' => 0, 'required' => 0),
            'F' => array('value' => 'City', 'exists' => 0, 'required' => 0),
            'G' => array('value' => 'State', 'exists' => 0, 'required' => 0),
            'H' => array('value' => 'Country', 'exists' => 0, 'required' => 0),
            'I' => array('value' => 'UserType', 'exists' => 0, 'required' => 0),
            'J' => array('value' => 'UserTags', 'exists' => 0, 'required' => 0)
        );

        //try validation on each field
        foreach ($values as $key => $value) {
            switch ($key) {
                case 'A' : //First Name
                    if (trim($file_data['header'][1][$key]) == 'FirstName' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;
                        //$errorReport[$row][$key]['exists'] = 1;
                        if (preg_match("/[^A-Za-z '.-]/", $value) || strlen($value) > 50) { // '/[^a-z\d]/i' should also work.
                            $errors[] = "Row $row Col $key- Only alphabets, space, and apostrophe allowed in First Name (max 50 characters).";
                        }
                        $parameters[$row]['FirstName'] = $value;
                    }
                    break;
                case 'B' : //Last_Name
                    if (trim($file_data['header'][1][$key]) == 'LastName' && $value != '') {
                        //$mandatory_check[$key]['exists'] = 1;//confirm value exists
                        $mandatory_check[$row][$key]['exists'] = 1;

                        if (preg_match("/[^A-Za-z '.-]/", $value) || strlen($value) > 50) {// '/[^a-z\d]/i' should also work.
                            $errors[] = "Row $row Col $key- Only alphabets, space, and apostrophe allowed in Last Name (max 50 characters).";
                        }
                        $parameters[$row]['LastName'] = $value;
                    }
                    break;
                case 'C' : //offical_email
                    if (trim($file_data['header'][1][$key]) == 'Email' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errorReport[$row][$key]['Invalid'] = 1;
                            $errors[] = "Row $row Col $key- Invalid Email ID.";
                        }
                        //check if the value exists in DB (validation for unique value)
//                                        if ($this->check_user_attribute_value_exists('Email', $value, NEWSLETTERSUBSCRIBER)) {
//                                            $errors[] = "Row $row Col $key- Email already exists.";
//                                            $unique_field_error[$row][$key]['unique'] = 0;
//                                            $errorReport[$row][$key]['NotUnique'] = 1;
//                                        } elseif (in_array($value, $emails_array)) {
//                                            $errors[] = "Row $row Col $key- Duplicate record inserted.";
//                                        }
                        $emails_array[] = $value;
                        $parameters[$row]['Email'] = $value;
                    }
                    break;
                case 'D' : //Gender
                    if (trim($file_data['header'][1][$key]) == 'Gender' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;

                        if (!in_array(strtolower($value), array('male', 'female', 'other'))) {
                            $errors[] = "Row $row Col $key- Gender value can only be Male, Female or Other.";
                        } else {
                            $parameters[$row]['Gender'] = (isset($value)) ? ($value == 'Male') ? '1' : ($value == 'Female') ? '2' : '0' : '0';
                        }
                    }
                    break;
                case 'E' : //Date_of_Birth
                    if (trim($file_data['header'][1][$key]) == 'DOB' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;
                        // $date_regex = '/^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/';
                        $date_regex = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
                        if (!preg_match($date_regex, $value)) {


                            $values[$key] = '';

                            //$errors[] = "Row $row Col $key- DOB should be in YYYY-MM-DD format.";
                        } else {
                            $birthdate = explode('-', $value);
                            if (checkdate($birthdate[1], $birthdate[2], $birthdate[0])) {
                                $parameters[$row]['DOB'] = isset($value) ? $value : '';
                            } else {
                                $values[$key] = '';

                                //$errors[] = "Row $row Col $key- Incorrect DOB.";                            
                            }
                        }
                    }
                    break;
                case 'F' : //City
                    $parameters[$row]['City'] = $value;
                    break;
                case 'G' : //State
                    $parameters[$row]['State'] = $value;
                    break;
                case 'H' : //Country
                    $parameters[$row]['Country'] = $value;
                    break;
                case 'I' : //UserType
                    if (trim($file_data['header'][1][$key]) == 'UserType' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;

                        $user_type = explode(',', $value); //make sure seperator is should be , in excel
                        $user_type = array_filter($user_type);

                        $parameters[$row]['UserType'] = $user_type;
                    }
                    break;
                case 'J' : //UserTags
                    if (trim($file_data['header'][1][$key]) == 'UserTags' && $value != '') {
                        $mandatory_check[$row][$key]['exists'] = 1;

                        $user_tags = explode(',', $value); //make sure seperator is should be , in excel
                        $user_tags = array_filter($user_tags);

                        $parameters[$row]['UserTags'] = $user_tags;
                    }
                    break;

                default :
                    //default condition
                    break;
            }
            //echo '<br>'.$key . '=>' . $value;                    
        }
        //check if the row is missing
        if ($actual_row_index != $row) {
            $missing_rows[] = $actual_row_index;
            $actual_row_index = $row;
        }
        $actual_row_index++;
        $is_row_deleted = 1;
        foreach ($mandatory_check[$row] as $k => $v) {
            if ($v['required'] == 1 && $v['exists'] == 0) {
                $required_fields[$row] = $v;
                // $return['Error']['requiredFieldsError'][$row] = $v['value']." is mandatory in row $row";
                $mandatory_err = "Row $row Col $k- " . $v['value'] . " is mandatory";
                array_unshift($errors, $mandatory_err);
            }
            if (isset($return['Error']['duplicateRecordError'][$row])) {
                $return['Data']['duplicateRecordData'][$row] = $parameters[$row];
            }
        }

        //delete record of row having any kind of specified error
        foreach ($mandatory_check[$row] as $k => $v) {
            //find data to overwrite
            if (isset($errorReport[$row][$k]['NotUnique']) && $errorReport[$row][$k]['NotUnique'] == 1) {
                $overwrite_data[$row] = $parameters[$row];
            }
            //find fields which failed required validation
            if ($v['required'] == 1 && $v['exists'] == 0) {
                //fields need to be filled in the excel
                //$required_fields[$row][$k] = $v;
            }
            //delete all data from list having any kind of error
            if (isset($errorReport[$row]) || ($v['required'] == 1 && $v['exists'] == 0)) {
                unset($parameters[$row]);
                $is_row_deleted = 0;
                break;
            }
        }


        $returingValues = [];
        $genderMappingArr = array(
            'male' => 1,
            'female' => 2,
        );
        
        //try validation on each field
        foreach ($values as $key => $value) {
            switch ($key) {
                case 'A' : //First Name
                    if (trim($file_data['header'][1][$key]) == 'FirstName' && $value != '') {
                        $returingValues['FirstName'] = $value;
                    }
                    break;
                case 'B' : //Last_Name
                    if (trim($file_data['header'][1][$key]) == 'LastName' && $value != '') {
                        $returingValues['LastName'] = $value;
                    }
                    break;
                case 'C' : //email
                    if (trim($file_data['header'][1][$key]) == 'Email' && $value != '') {
//                                        if ($this->check_user_attribute_value_exists('Email', $value, NEWSLETTERSUBSCRIBER)) {
//                                            $error_in_rows[] = $row;
//                                        }
                        $returingValues['Email'] = $value;
                    }
                    break;
                case 'D' : //Gender
                    if (trim($file_data['header'][1][$key]) == 'Gender' && $value != '') {
                        //$returingValues['Gender'] = (isset($value)) ? ($value == 'Male') ? '1' : '2' : '';
                        $returingValues['Gender'] = 0;                        
                        if(isset($genderMappingArr[strtolower($value)])) {
                             $returingValues['Gender'] = $genderMappingArr[strtolower($value)];
                        }
                        
                    }
                    break;
                case 'E' : //Date_of_Birth
                    if (trim($file_data['header'][1][$key]) == 'DOB' && $value != '') {
                        $date_regex = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
                        if (preg_match($date_regex, $value)) {
                            $birthdate = explode('-', $value);
                            if (checkdate($birthdate[1], $birthdate[2], $birthdate[0]))
                                $returingValues['DOB'] = isset($value) ? $value : '';
                        }
                    }
                    break;
                case 'F' : //City
                    $returingValues['City'] = $value;
                    break;
                case 'G' : //State
                    $returingValues['State'] = $value;
                    break;
                case 'H' : //Country
                    $returingValues['Country'] = $value;
                    break;
                case 'I' : //UserType
                    if (trim($file_data['header'][1][$key]) == 'UserType' && $value != '') {
                        $user_type = explode(',', $value); //make sure seperator is should be , in excel
                        $user_type = array_filter($user_type);

                        $returingValues['UserType'] = $user_type;
                    }
                    break;
                case 'J' : //UserTags
                    if (trim($file_data['header'][1][$key]) == 'UserTags' && $value != '') {

                        $user_tags = explode(',', $value); //make sure seperator is should be , in excel
                        $user_tags = array_filter($user_tags);

                        $returingValues['UserTags'] = $user_tags;
                    }
                    break;
                default :
                    //default condition
                    break;
            }
        }


        if (count($errors)) {
            
        }

        return array(
            'is_error' => count($errors),
            'error' => $errors,
            'values' => $values,
            'returingValues' => $returingValues
        );
    }

    /**
     * [run_uploaded_profile check for all valid entries in file and register users for right entry]
     * @return [json] [success / error message and response code]
     */
    public function run_uploaded_profile($filename) {
        $return['ResponseCode'] = 200;
        $return['Message'] = lang('success');
        $current_user_id = isset($this->UserID) ? $this->UserID : 0;
        $return['Data'] = array();
        //Check if file headers are valid        
        $header_validation = $this->check_file_headers($filename['upload_data']['full_path']);
        if (!$header_validation['Status']) {
            $return['Error'] = (isset($header_validation['ErrorMessages']) && !empty($header_validation['ErrorMessages'])) ? $header_validation['ErrorMessages'] : array("Invalid File Format");
            return $return;
        }
        $filename = $filename['upload_data']['file_name'];
        $file_name = $filename;
        $filename = PATH_IMG_UPLOAD_FOLDER . $filename; //'User_Upload_data51.xls';//$Data['filename']
        //check if the file exists or not        
        if (file_exists($filename)) {
            $file_data = $this->get_file_data($filename);
            $errorReport = array();
            $required_fields = $overwrite_data = $rows_updated = $missing_rows = $parameters = $errors = array();
            $mandatory_check = $employeeid_array = $emails_array = array();
            $unique_field_error = array('C' => array('value' => 'Email', 'unique' => 1));
            try {
                if (!empty($file_data['values'])) {

                    $insertingRecords = 0;
                    $updatingRecords = 0;

                    $actual_row_index = 2; //this value will decide if any row is missing
                    $excel_errors_fixes = [];


                    foreach ($file_data['values'] as $row => $values) {
                        $row_validation_arr = $this->validate_file_row($row, $values, $actual_row_index, $file_data, $errorReport);                        
                        if ($row_validation_arr['is_error']) {
                            $excel_errors_fixes[] = $row_validation_arr['error'];
                            continue;
                        }

                        $isSubscriberExists = $this->isSubscriberExists($row_validation_arr['returingValues']);

                        if ($isSubscriberExists) {
                            $updatingRecords++;
                        } else {
                            $insertingRecords++;
                        }
                    }
                    
                    //initiate_worker_job('add_subscriber_from_excel_job', array('file_name' => $file_name, 'current_user_id' => $current_user_id));                    
                    $this->add_subscriber_from_excel_job($file_name, $current_user_id);
                    
                    $response_msg = "";
                    $return['ResponseCode'] = 200;
                    $return['excel_errors_fixes'] = $excel_errors_fixes;
                    $return['Message'] = "Upload subscriber is in progress! $updatingRecords will be updated and $insertingRecords will be inserted."; // You will be notified by email after completion
                }
            } catch (Exception $e) {
                $return['Error'] = array($e->getMessage());
                $return['ResponseCode'] = 412;
                ;
            }
            //create Reply for error
        } else {
            $return['ResponseCode'] = 404;
            $return['Message'] = "File does not exists";
        }
        /* Final Output */
        return $return; //$this->response($return);
    }

    /**
     * [check_file_headers check file's format with specified format given in sample and returns true if file is perfect]
     * @return [json] [success / error message and response code]
     */
    public function check_file_headers($file) {
        $sourceFile = ROOT_PATH . '/upload/csv_file/SubscriberDataSampleFormat.xls';
        $baseFile = $this->get_file_data($sourceFile);
        $userFile = $this->get_file_data($file);

        $diff = array_diff_assoc($baseFile['header'][1], $userFile['header'][1]);
        $header_validation = $this->validate_file_header_format($userFile['header']);
        if (count($baseFile['header']) == count($userFile['header']) && empty($diff) && !$header_validation['Status']) {
            return array('Status' => true);
        } else if (isset($header_validation['ErrorMessages']) && !empty($header_validation['ErrorMessages'])) {
            return array('Status' => false, 'ErrorMessages' => $header_validation['ErrorMessages']);
        }
        return array('Status' => false);
    }

    /**
     * [get_file_data returns file's data]
     * @return [json] [success / error message and response code]
     */
    public function get_file_data($file) {
        $this->load->library('excel');
        //read file from path
        $objPHPExcel = PHPExcel_IOFactory::load($file);
        //get only the Cell Collection
        $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
        //extract to a PHP readable array format
        foreach ($cell_collection as $cell) {
            $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
            $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
            if ($row > 1 && in_array($column, array('E'))) { //array('K','O')
                //$data_value = PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell($cell)->getValue());
                $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getFormattedValue();
            } else {
                $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
            }
            //header will/should be in row 1 only. of course this can be modified to suit your need.
            if ($row == 1) {
                $header[$row][$column] = $data_value;
            } else {
                $arr_data[$row][$column] = $data_value;
            }
        }
        //send the data in an array format
        $data['header'] = isset($header) ? $header : array();
        $data['values'] = isset($arr_data) ? $arr_data : array();
        // echo '<pre>';print_r($data);die;
        return $data;
    }

    /**
     * [validate_file_header_format
     * @return [json] [success / error message and response code]
     */
    public function validate_file_header_format($fileHeaders) {
        //check if the file headers are in correct format 
        $error = FALSE;
        $error_msg = array();
        foreach ($fileHeaders[1] as $key => $value) {
            switch ($key) {
                case 'A' : //Last_Name
                    if ($value != 'FirstName') {
                        $error = true;
                        $error_msg[] = "Row 1 Col A- $value is an invalid header";
                    }
                    break;
                case 'B' : //LastName 
                    if ($value != 'LastName') {
                        $error = true;
                        $error_msg[] = "Row 1 Col B- $value is an invalid header";
                    }
                    break;
                case 'C' : //Email
                    if ($value != 'Email') {
                        $error = true;
                        $error_msg[] = "Row 1 Col C- $value is an invalid header";
                    }
                    break;
                case 'D' : //Gender
                    if ($value != 'Gender') {
                        $error = true;
                        $error_msg[] = "Row 1 Col D- $value is an invalid header";
                    }
                    break;
                case 'E' : //DateOfBirth
                    if ($value != 'DOB') {
                        $error = true;
                        $error_msg[] = "Row 1 Col E- $value is an invalid header";
                    }
                    break;
                case 'F' : //MobileNumber
                    if ($value != 'City') {
                        $error = true;
                        $error_msg[] = "Row 1 Col F- $value is an invalid header";
                    }
                    break;
                case 'G' : //HomeLandlineNumber
                    if ($value != 'State') {
                        $error = true;
                        $error_msg[] = "Row 1 Col G- $value is an invalid header";
                    }
                    break;
                case 'H' : //PersonalEmailAddress
                    if ($value != 'Country') {
                        $error = true;
                        $error_msg[] = "Row 1 Col H- $value is an invalid header";
                    }
                    break;
                case 'I' : //PAN
                    if ($value != 'UserType') {
                        $error = true;
                        $error_msg[] = "Row 1 Col I- $value is an invalid header";
                    }
                    break;
                case 'J' : //BloodGroup
                    if ($value != 'UserTags') {
                        $error = true;
                        $error_msg[] = "Row 1 Col J- $value is an invalid header";
                    }
                    break;
            }
        }

        return array('Status' => $error, 'ErrorMessages' => $error_msg);
    }

    /**
     * Function to check if users column value Exists
     * @param string $key columnName e.g. EmployeeID
     * @param string $value columnValue e.g. EmployeeID123
     * @return bolean 
     */
    public function check_user_attribute_value_exists($key, $value, $userTable = USERS, $additional_user_condition = array()) {
        $this->db->select("count($key) as Count");
        $this->db->from($userTable);
        $this->db->where($key, $value);
        if (!empty($additional_user_condition)) {
            foreach ($additional_user_condition as $key => $value) {
                if (is_array($value))
                    $this->db->where_not_in($key, $value);
                else
                    $this->db->where($key, $value);
            }
        }
        $result = $this->db->get()->row_array();
        if ($result['Count'])
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Add newsletter subscriber uploaded by Admin from Excel sheet (this will work in background job)   
     * @param [String] $filename [file path]
     * @param [int] $added_by [user id of admin]     
     * @return Boolean [True/False] 
     */
    public function add_subscriber_from_excel_job($filename, $added_by) {
        
        
        $this->load->model(array(
            'admin/media_model',
            'admin/newsletter/newsletter_users_model',
            'admin/newsletter/newsletter_model',            
        ));
        
        
        
        if (file_exists(PATH_IMG_UPLOAD_FOLDER . $filename)) {
            $file_data = $this->get_file_data(PATH_IMG_UPLOAD_FOLDER . $filename);
            $error_in_rows = array();
            $parameters = array();
            $errorReport = [];
            $actual_row_index = 2; //this value will decide if any row is missing
            
            try {
                if (!empty($file_data['values'])) {
                    foreach ($file_data['values'] as $row => $values) {
                                                
                        $row_validation_arr = $this->validate_file_row($row, $values, $actual_row_index, $file_data, $errorReport);
                        
                        if($row_validation_arr['is_error']) {
                            continue;
                        }
                        
                        $is_new_added = $this->add_subscriber_from_excel($row_validation_arr['returingValues'], $added_by);
                    }                    
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                log_message('error', $error);
            }
            //create Reply for error
        } else {
            log_message('error', 'File does not exist: ' . PATH_IMG_UPLOAD_FOLDER . $filename);
        }
    }

    public function isSubscriberExists($params) {
        $subscriber_email = isset($params['Email']) ? trim($params['Email']) : ''; //offical_Email
        if (!$subscriber_email) {
            return false;
        }
        $user_details = $this->db->select("U.*, UD.DOB, UD.CityID, NLS.Email AS NLS_Email, NLS.NewsLetterSubscriberID", FALSE)
                        ->from(NEWSLETTERSUBSCRIBER . ' NLS ')
                        ->join(USERS . " U ", "U.UserID = NLS.UserID ", "left")
                        ->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left')
                        ->where(" ( NLS.Email = '$subscriber_email' OR U.Email = '$subscriber_email'  )", NULL, FALSE)
                        ->get()->row_array();

        if (!empty($user_details['NewsLetterSubscriberID'])) {
            return true;
        }

        return false;
    }

    /**
     * Function to get the requiered value from the required table 
     * @param array $params [insert data]
     * @param array $current_user_id [logged in userid]
     * @return array $result [return resulting array with new insertID]
     */
    public function add_subscriber_from_excel($params, $current_user_id = '', $is_return_obj = false) {

        $new_added = false;

        $subscriber_email = isset($params['Email']) ? trim($params['Email']) : ''; //offical_Email
        if (!$subscriber_email) {
            return;
        }
        $user_details = $this->db->select("U.*, UD.DOB, UD.CityID, NLS.Email NLS_Email, NLS.NewsLetterSubscriberID")
                        ->from(NEWSLETTERSUBSCRIBER . ' NLS ')
                        ->join(USERS . " U ", "U.UserID = NLS.UserID ", "left")
                        ->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left')
                        ->where(" ( NLS.Email = '$subscriber_email' OR U.Email = '$subscriber_email'  )", NULL, FALSE)
                        ->get()->row_array();

        $final_user_details = array(
        );
        // Check exists in newsletter
        if (!empty($user_details['UserID'])) {
            $final_user_details['FirstName'] = !empty($user_details['FirstName']) ? $user_details['FirstName'] : '';
            $final_user_details['LastName'] = !empty($user_details['LastName']) ? $user_details['LastName'] : '';
            $final_user_details['Gender'] = !empty($user_details['Gender']) ? $user_details['Gender'] : '';
            $final_user_details['DOB'] = !empty($user_details['DOB']) ? $user_details['DOB'] : '';
            $final_user_details['CityID'] = !empty($user_details['CityID']) ? $user_details['CityID'] : '';
        }

        // Insert location and get city id
        if (isset($params)) {
            $city = isset($params['City']) ? trim($params['City']) : '';
            $state = isset($params['State']) ? trim($params['State']) : '';
            $country = isset($params['Country']) ? trim($params['Country']) : '';
            $location = update_location(array("City" => $city, "State" => $state, "Country" => $country, "CountryCode" => '', "StateCode" => ''));
        }


        if (!empty($final_user_details['FirstName'])) {
            $subscriber_data['FirstName'] = $final_user_details['FirstName'];
        } else {
            $subscriber_data['FirstName'] = isset($params['FirstName']) ? trim($params['FirstName']) : '';
        }

        if (!empty($final_user_details['LastName'])) {
            $subscriber_data['LastName'] = $final_user_details['LastName'];
        } else {
            $subscriber_data['LastName'] = isset($params['LastName']) ? trim($params['LastName']) : '';
        }

        if (!empty($final_user_details['Gender'])) {
            $subscriber_data['Gender'] = $final_user_details['Gender'];
        } else {
            $subscriber_data['Gender'] = isset($params['Gender']) ? trim($params['Gender']) : '';
        }

        if (!empty($final_user_details['DOB'])) {
            $subscriber_data['DOB'] = $final_user_details['DOB'];
        } else {
            $subscriber_data['DOB'] = isset($params['DOB']) ? trim($params['DOB']) : '';
        }


        if (!empty($final_user_details['CityID'])) {
            $subscriber_data['CityID'] = $final_user_details['CityID'];
        } else {
            $subscriber_data['CityID'] = $location['CityID'];
        }

        $subscriber_data['Email'] = isset($params['Email']) ? trim($params['Email']) : ''; //offical_Email                    
        $subscriber_data['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $subscriber_data['Status'] = 2;

        // Update user details
        if (!empty($user_details['UserID'])) {
            $subscriber_data['UserID'] = $user_details['UserID'];

            $this->db->where('UserID', $user_details['UserID']);
            $this->db->update(USERS, array(
                'FirstName' => $subscriber_data['FirstName'],
                'LastName' => $subscriber_data['LastName'],
                'Gender' => $subscriber_data['Gender'],
            ));

            $this->db->where('UserID', $user_details['UserID']);
            $this->db->update(USERDETAILS, array(
                'DOB' => $subscriber_data['DOB'],
                'CityID' => $subscriber_data['CityID'],
            ));
        }

        // Update subscriber details
        if (!empty($user_details['NewsLetterSubscriberID'])) {
            $this->db->where('NewsLetterSubscriberID', $user_details['NewsLetterSubscriberID']);
            $this->db->update(NEWSLETTERSUBSCRIBER, $subscriber_data);
        } else { // Insert subscriber
            $subscriber_data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $subscriber_data['NewsLetterSubscriberGUID'] = get_guid();
            $this->db->insert(NEWSLETTERSUBSCRIBER, $subscriber_data);
            $user_details['NewsLetterSubscriberID'] = $this->db->insert_id();

            $new_added = true;
        }

        //Update tags
        $entity_type = 'NEWSLETTER_SUBSCRIBER';
        $entity_id = $user_details['NewsLetterSubscriberID'];

        if (!empty($user_details['UserID'])) {
            $entity_type = 'USER';
            $entity_id = $user_details['UserID'];
        }

        $user_types_tags = [];
        $tags = [];
        if (!empty($params['UserType']) && is_array($params['UserType'])) {
            foreach ($params['UserType'] as $user_type_tag) {
                if (!$user_type_tag) {
                    continue;
                }
                $user_types_tags[] = array(
                    'Name' => $user_type_tag
                );
            }
        }

        if (!empty($params['UserTags']) && is_array($params['UserTags'])) {
            foreach ($params['UserTags'] as $user_tag) {
                if (!$user_tag) {
                    continue;
                }
                $tags[] = array(
                    'Name' => $user_tag
                );
            }
        }

        $this->load->model(array('tag/tag_model'));
        if (!empty($user_types_tags)) {
            $tag_type_id = $this->tag_model->get_tag_type_id('USER');
            $user_types_tags = $this->tag_model->save_tag($user_types_tags, $tag_type_id, $current_user_id);
        }

        if (!empty($tags)) {
            $tag_type_id = $this->tag_model->get_tag_type_id('PROFESSION');
            $tags = $this->tag_model->save_tag($tags, $tag_type_id, $current_user_id);
        }

        $tags_data = array_merge($tags, $user_types_tags);

        if (!empty($tags_data) && !empty($entity_id)) {
            $return['Data'] = $this->tag_model->save_entity_tag($tags_data, $entity_type, $entity_id, $current_user_id, true, true, false);
        }

        if($is_return_obj) {
            return $user_details;
        }
        
        return $new_added;
    }
    
}

//End of file users_model.php
