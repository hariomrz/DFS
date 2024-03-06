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
    public function get_users($post_data, $is_return_query = false, $is_real_query = false) {

        $page_no = (int) isset($post_data['PageNo']) ? $post_data['PageNo'] : 1;
        $page_size = (int) isset($post_data['PageSize']) ? $post_data['PageSize'] : 20;
        
        $Locations = isset($post_data['Locations']) ? $post_data['Locations'] : [];
        $citi_ids = [];
        foreach($Locations as $Location) {
            $city_id = $this->util_location_model->get_city_id($Location);
            $citi_ids[] = $city_id;
        }

        if (!$page_no)
            $page_no = 1;
        $offset = ($page_no - 1) * $page_size;

        $this->query_select_users($is_return_query);

        $this->db->from(USERS . "  U ");
        $this->db->join(PROFILEURL . ' PU ', "U.UserID=PU.EntityID AND PU.EntityType='User'", "left");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left');
        $this->db->join(CITIES . ' CT ', 'CT.CityID = UD.HomeCityID', 'left');

        $this->query_filter_users($post_data, $citi_ids);

        //$this->db->where_not_in('U.StatusID', array(3, 4));
        $this->db->group_by('U.UserID');

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $total_users = $temp_q->num_rows();

        $this->query_order_users($post_data);

        /* Start_offset, end_offset */
        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }
        
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query;
        $this->db->reset_query();
        
        if($is_real_query) {
            return $compiled_query;
        }
        
        $query = $this->db->query($compiled_query);
        //$query = $this->db->get();
        $users = $query->result_array();  //echo $this->db->last_query(); echo '======================================';
        
        if($is_return_query) {
            $user_ids = [0];
            foreach ($users as $user){
                $user_ids[] = $user['UserID'];
            }
            
            return implode(',', $user_ids);
        }

        $users = $this->result_set_filter_users($users);
        
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

    public function query_select_users($is_return_query) {
        
        if($is_return_query) {
            $select_array[] = "U.UserID";
            $this->db->select(implode(',', $select_array), FALSE);
            return;
        }
                
        $today_date = get_current_date('%Y-%m-%d %H:%i:%s');

        /* Load Global settings */
        /* Change date_format into mysql date_format */
        $global_settings = $this->config->item("global_settings");
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $time_format = dateformat_php_to_mysql($global_settings['time_format']);

        $select_array[] = " IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') AS Age";
        $select_array[] = "IFNULL(CT.Name, '') AS City";
        $select_array[] = "IFNULL(U.Gender, '') Gender";
        $select_array[] = "IFNULL(U.Email, '') Email";
        $select_array[] = "DATE_FORMAT(U.CreatedDate, '" . $mysql_date . "') AS CreatedDate";
        $select_array[] = "U.UserID";
        $select_array[] = "U.PhoneNumber";
        $select_array[] = "CONCAT(U.FirstName, ' ', U.LastName) AS Name";
        $select_array[] = "U.ProfilePicture AS ProfilePicture";
        $select_array[] = "U.StatusID, U.UserGUID";
        $select_array[] = "UD.AverageScore";
        $select_array[] = "IFNULL(UD.AndroidAppVersion, '') AndroidAppVersion";
        $select_array[] = "IFNULL(UD.IOSAppVersion, '') IOSAppVersion";
        $select_array[] = 'DATE_FORMAT(U.CreatedDate, "' . $mysql_date . '") AS Membersince';

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
        $search_key = isset($filter['SearchKey']) ? $filter['SearchKey'] : '';
        $tag_user_type = isset($filter['TagUserType']) ? $filter['TagUserType'] : [];
        $tag_user_search_type = isset($filter['TagUserSearchType']) ? $filter['TagUserSearchType'] : 0;  //TagUserSearchType (0=> AND 1=> OR)
        $tag_tag_type = isset($filter['TagTagType']) ? $filter['TagTagType'] : [];
        $tag_tag_search_type = isset($filter['TagTagSearchType']) ? $filter['TagTagSearchType'] : 0;  //TagTagSearchType (0=> AND 1=> OR)
        $user_ids = isset($filter['UserIDs']) ? $filter['UserIDs'] : [];
        $user_status = (int) isset($filter['StatusID']) ? $filter['StatusID'] : 0;
        $userExcludeList = isset($filter['userExcludeList']) ? $filter['userExcludeList'] : [];
        
        $start_date = isset($filter['StartDate']) ? $filter['StartDate'] : '';
        $end_date = isset($filter['EndDate']) ? $filter['EndDate'] : '';
        
        $age_start = isset($filter['AgeStart']) ? $filter['AgeStart'] : '';
        $age_end = isset($filter['AgeEnd']) ? $filter['AgeEnd'] : '';

        /* start_date, end_date for filters */
        if (isset($start_date) && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            if(in_array($user_status, array(3,4))){
                $this->db->where('DATE(U.ModifiedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
            } else {
                $this->db->where('DATE(U.CreatedDate) BETWEEN "' . $start_date . '"  AND "' . $end_date . '"', NULL, FALSE);
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
            $this->db->like('CONCAT(U.FirstName," ", U.LastName)', $search_key);
            $this->db->or_like('U.PhoneNumber', $search_key);
            $this->db->or_like('U.Email', $search_key);
        }

        if ($gender) {
            $gender = ($gender == 3) ? 0 : $gender;
            $this->db->where('U.Gender', $gender);
        }

        if (count($citi_ids)) {
            $string_str_ids = implode(',', $citi_ids);
            $this->db->where(" (UD.CityID IN ( $string_str_ids ) ) ", NULL, FALSE);
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

        function tag_select_query($tag_ids, $tag_types, $searchType, $objCtx, $tagSerachType = 1) {
            //1- Normal Tag, 2- Hash Tag, 3- Activity Mood, 4- Activity Classification, 5- User/Reader Tag, 6- User Profession, 7- Brand
            if (!$tag_ids) {
                return;
            }

            $tbl_als_et = ($tagSerachType) ? 'ET' : 'ET1';
            $tbl_als_tg = ($tagSerachType) ? 'TG' : 'TG1';

            $objCtx->db->join(ENTITYTAGS . " $tbl_als_et", "$tbl_als_et.EntityID = U.UserID AND $tbl_als_et.EntityType = 'USER'  AND $tbl_als_et.StatusID = 2", 'left');
            $objCtx->db->join(TAGS . " $tbl_als_tg", "$tbl_als_tg.TagID = $tbl_als_et.TagID", 'left');

            if ($tagSerachType) {
                //$objCtx->db->where_in("$tbl_als_tg.TagType", $tag_types);
            } else {
                //$objCtx->db->where_not_in("$tbl_als_tg.TagType", $tag_types);
            }

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
        
        $tag_user_type_tag_ids = [];
        foreach ($tag_user_type as $tag_user_type_tag ){
            $tag_user_type_tag_ids[] = $tag_user_type_tag['TagID'];
        }
        
        $tag_tag_type_tag_ids = [];
        foreach ($tag_tag_type as $tag_tag_type_tag ){
            $tag_tag_type_tag_ids[] = $tag_tag_type_tag['TagID'];
        }
        
        tag_select_query($tag_user_type_tag_ids, [6, 7], $tag_user_search_type, $this, 0);
        tag_select_query($tag_tag_type_tag_ids, [6, 7], $tag_tag_search_type, $this, 1);
    }

    public function result_set_filter_users($users) {
        $entity_ids = [];
        $newKeyedUsers = [];
        foreach ($users as $user) {
            $user['Name'] = stripslashes($user['Name']);
            $user['userroleid'] = $this->getUserRoles($user['UserID']);
            $profileSection = $this->media_model->getMediaSectionNameById(PROFILE_SECTION_ID);
            $user['ProfilePictureUrl'] = get_image_path($profileSection, $user['ProfilePicture'], ADMIN_THUMB_WIDTH, ADMIN_THUMB_HEIGHT);
            
            $user['Location'] = $this->user_model->get_user_location_admin($user['UserID'], 0, 1);
            if(empty($user['Location']['City'])) {
                $user['Location'] = $this->user_model->get_user_location_admin($user['UserID'], 1);
            }
            

            $user['Tags'] = [];
            //$user['TagsMore'] = [];
            $user['UserTypeTags'] = [];
            //$user['UserTypeTagsMore'] = [];
            

            $entity_ids[] = $user['UserID'];
            $newKeyedUsers[$user['UserID']] = $user;
        }

        $users = $this->set_entity_tags($newKeyedUsers, $entity_ids, 'USER');
        
        $make_browser_compatible_obj = [];
        foreach($users as $user_id => $user) {
            $make_browser_compatible_obj['pre'.$user_id] = $user;
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
            'Email' => 'Email', 
            'Age' => 'Age',
            'Gender' => 'Gender',
            'UserTypeTags' => 'User Types',
            'Tags' => 'Tags',
            'AndroidAppVersion' => 'Android App Version ('.ANDROID_VERSION.')',
            'Location' => 'Location',
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
        $user_ids = !empty($data['useridsString']) ? $data['useridsString'] : 0;
        $notification_text = !empty($data['notification_text']) ? $data['notification_text'] : 0;
        $isSms = !empty($data['isSms']) ? $data['isSms'] : 0;
        if ($isSms == 0) {
            if ($data['allUserSelected'] == 1) {
                $this->db->select("AL.UserID, AL.DeviceToken, AL.DeviceTypeID");
                $this->db->from(ACTIVELOGINS . ' AL');
                $this->db->join(USERS . ' U', 'U.UserID=AL.UserID AND U.StatusID NOT IN (3,4)');            
                $this->db->where('AL.DeviceToken!=', '');
                $this->db->group_by('AL.DeviceToken');
                $this->db->group_by('AL.DeviceTypeID');
            } else {
                $query = $this->db->query("SELECT UserID,DeviceToken, DeviceTypeID FROM `ActiveLogins` WHERE UserID IN (" . $user_ids . ") AND DeviceToken!='' GROUP BY DeviceToken, DeviceTypeID ORDER BY ActiveLoginID DESC ");
            }
            
            if ($query->num_rows() > 0) {
                $push_notification = array("EntityID" => "", "ModuleID" => "", "ModuleEntityGUID" => "", "Refer" => "UPDATE_APP", "EntityGUID" => "");
                foreach ($query->result_array() as $Notifications) {
                    $token = $Notifications['DeviceToken'];
                    $notification_data = array();
                    $notification_data['PushNotification'] = $push_notification;
                    $notification_data['ToUserID'] = $Notifications['UserID'];
                    $notification_data['Summary'] = $notification_text;
                    $message = $notification_text;
                    if ($Notifications['DeviceTypeID'] == 2) {
                        /* Iphone */
                        push_notification_iphone($token, $message, 0, $notification_data);
                    } elseif ($Notifications['DeviceTypeID'] == 3) {
                        /* android */
                        push_notification_android(array($token), $message, 0, $notification_data);
                    }
                }
            }
        } else {
            if ($data['allUserSelected'] == 1) {
                $query = $this->db->query("SELECT UserID,PhoneNumber FROM `Users` WHERE StatusID NOT IN (3,4)");
            } else {
                $query = $this->db->query("SELECT UserID,PhoneNumber FROM `Users` WHERE UserID IN (" . $user_ids . ")");
            }
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $Notifications) {
                    $phone_no = $Notifications['PhoneNumber'];                    
                    //send sms using sms library 	 
                    if(ACTIVE_SMS_GATEWAY == "msg91") {
                        $sms_data = array();
                        $sms_data['mobile'] = $phone_no;
                        $sms_data['phone_code'] = DEFAULT_PHONE_CODE;
                        $sms_data['message'] = $notification_text;
                        send_msg91_sms($sms_data);
                    } else {    
                        $this->load->library('TwoFactorSMS');
                        $TwoF = new TwoFactorSMS(TWO_FACTOR_SMS_API_KEY, TWO_FACTOR_SMS_API_ENDPOINT);
                        $result = $TwoF->SendPromotionalSMS("PROMOS", DEFAULT_PHONE_CODE . $phone_no, $notification_text, "");
                    }                    
                }
            }
        }
    }
    
}

//End of file users_model.php
