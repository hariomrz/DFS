<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of User_orientation_model
 *
 * 
 */
class User_orientation_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('activity/activity_model', 'album/album_model'));
        
        // Set exclude modules
        $this->exclude_modules = [0];
        $check_modules = [1, 18, 14, 33, 34];
        
        foreach($check_modules as $check_module) {
            if ($this->settings_model->isDisabled($check_module)) {
                $this->exclude_modules[] = $check_module;
            }
        }
    }

    /**
     * Get log activity listing 
     * @param  [integer]  [$page_no]
     * @param  [integer]  [$page_size]
     * @param  [array]  [$filter]
     * @param  [boolean]  [$count_only]
     */
    public function get_activity_list($page_no = 1, $page_size = 10, $filter = array(), $count_only = false) {
        $offset = ($page_no - 1) * $page_size;
        $filter['CityID'] = 0;
        $this->load->model(array('tag/tag_model'));
        $filter['Tags'][] = $this->tag_model->is_tag_exist('featured', 1);
        

        $top_post_filter = isset($filter['TopPostFilter']) ? $filter['TopPostFilter'] : 1;  

        $select_array = $this->get_select_arr($filter, $page_no);
              
        $this->db->select(implode(',', $select_array), false);
       

        if($top_post_filter == 1) {      
            $this->db->select("IFNULL(UO.OrientationCategoryID, 0) AS OrientationCategoryID");
            $this->db->from(ACTIVITY . ' AATC ');
            $this->db->join(USERORIENTATION . ' UO ', 'UO.ActivityID = AATC.ActivityID', 'left');
        }        

        if($top_post_filter == 2) {
            $this->db->select("0 AS OrientationCategoryID");
            $this->db->from(ACTIVITY . ' AATC ');
            $this->db->where("AATC.ActivityID NOT IN (SELECT ActivityID FROM " . USERORIENTATION.")", null, false);
        }

        if($top_post_filter == 3) {
            $this->db->select("IFNULL(UO.OrientationCategoryID, 0) AS OrientationCategoryID");
            $this->db->from(USERORIENTATION . ' UO ');
            $this->db->join(ACTIVITY . ' AATC ', 'AATC.ActivityID = UO.ActivityID');
        }
        
        
        $this->db->join(USERS . ' AATCU ', 'AATCU.UserID = AATC.UserID', 'left');
        $this->db->join(PROFILEURL . ' AATCU_URL ', 'AATCU.UserID = AATCU_URL.EntityID AND AATCU_URL.EntityType = "User" ', 'left');

        $this->set_entity_join($filter);

        
        $this->db->where_in('AATC.ActivityTypeID',array(1,8,49));
        
        $this->set_entity_conds($filter);

        // Apply filter
        $this->apply_filter($filter);

        //$this->db->group_by('UAL.ID');
        $this->db->group_by('AATC.ActivityID');

        $this->apply_sort_order($filter);

        if (!$count_only) {
            $this->db->limit($page_size, $offset);
        }


        $query = $this->db->get();

      //  echo $this->db->last_query(); die;

        $entities = $query->result_array();
        
        $total_count = 0;
        if($page_no == 1) {
            $this->db->select('FOUND_ROWS() AS Count', false);
            $query = $this->db->get();
            
            $total_count = $query->row_array(); 
            $total_count = isset($total_count['Count']) ? $total_count['Count'] : 0;
        }
        

        $entities = $this->iterate_entities($entities, $filter);
        
        return array(
            'entities' => $entities,
            'total_count' => $total_count,
        );
    }

    /**
     * [get_user_details]  Get user data
     * @param  [type]  [$user_id]
     */
    public function get_user_details($user_id) {

        $today_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $global_settings = $this->config->item("global_settings");
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);

        $select_array[] = "CONCAT(U.FirstName, ' ', U.LastName) AS Name, IFNULL(U.ProfilePicture, '') AS ProfilePicture, U.Verified, IFNULL(U.Gender, '') Gender, DATE_FORMAT(U.CreatedDate, '$mysql_date') AS MemberSince,U.StatusID,U.UserGUID,U.Email";
        $select_array[] = "IFNULL(UD.Occupation, '') AS Occupation";
        $select_array[] = " IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') AS Age, IFNULL(UD.MartialStatus, '') MartialStatus, IFNULL(CT.Name, '') AS City";
        $select_array[] = "IFNULL(COUNT(A.UserID), 0) AS PostCount, IFNULL((Select COUNT(*) FROM PostComments WHERE " . POSTCOMMENTS . ".UserID = $user_id), 0) AS CommnetCount";
        $select_array[] = "UD.BrowsingAverage, UD.ContributionAverage, UD.HighlyActivePercentage, UD.RelationWithID, UD.RelationWithName, UD.ConnectWith, UD.ConnectFrom";
        $select_array[] = "UD.NoOfFriendsFB, UD.NoOfFollowersFB, UD.NoOfFollowersTw, UD.NoOfConnectionsIn, U.UserID, 3 AS ModuleID, UD.LocalityID";
        
        $select_array[] = "PU.Url as UserName";

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(USERS . ' U ');
        $this->db->join(PROFILEURL . ' PU ',"U.UserID=PU.EntityID AND PU.EntityType='User'","left");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left');
        $this->db->join(CITIES . ' CT ', 'CT.CityID = UD.HomeCityID', 'left');
        $this->db->join(ACTIVITY . ' A ', 'A.UserID = U.UserID AND A.ActivityTypeID != 13', 'left');

        $this->db->group_by('U.UserID');
        $this->db->where('U.UserID', $user_id);

        $query = $this->db->get();

        $user_data = $query->row_array();
        
        if($user_data['LocalityID']) {
            $this->load->model(array('locality/locality_model'));
            $user_data['Locality'] = $this->locality_model->get_locality($user_data['LocalityID']);
        }
        unset($user_data['LocalityID']);
                        
       
        $user_data['UserRoleID'] = $this->getUserRoles($user_id);
        return $user_data;
    }

    /**
     * [get_entity_tags]  Get tags for entities ( User, Activity )
     * @param  [type]  [$entity_id]
     * @param  [type]  [$entity_type]
     * @param  [type]  [$page_size]
     * @param  [type]  [$offset]
     */
    public function get_entity_tags($entity_id, $entity_type, $page_size = 0, $offset = 0, $added_by = NULL) {

        $select_array[] = "TG.TagID, TG.Name, TG.TagType, ET.AddedBy,TG.Name AS text";

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(ENTITYTAGS . ' ET');
        $this->db->join(TAGS . ' TG', 'TG.TagID = ET.TagID', 'left');

        $this->db->where('ET.EntityType', $entity_type);
        $this->db->where('ET.StatusID', 2);
        $this->db->where('ET.EntityID', $entity_id);

        $this->db->order_by('TG.TagType');

        if ($page_size) {
            $this->db->limit($page_size, $offset);
        }
        
        if($added_by !== NULL) {
            $this->db->where('ET.AddedBy', $added_by);
        }
        
        $query = $this->db->get();
        $tag_entities = $query->result_array();

        $tag_list_by_type = array(
            'Normal' => [], 'Hash' => [], 'ActivityMood' => [], 'ActivityClassification' => [], 'User_ReaderTag' => [], 'UserProfession' => [], 'Brand' => []
        );
        $tag_types = array(
            1 => 'Normal', 2 => 'Hash', 3 => 'ActivityMood', 4 => 'ActivityClassification', 5 => 'User_ReaderTag', 6 => 'UserProfession', 7 => 'Brand'
        );
        foreach ($tag_entities as $tag_entity) {
            $tag_type = $tag_types[$tag_entity['TagType']];
            $tag_list_by_type[$tag_type][] = $tag_entity;
        }

        return $tag_list_by_type;
    }

    protected function set_entity_join($filter) {


        $this->db->join(USERS . ' UE1 ', 'UE1.UserID  = AATC.ModuleEntityID  AND AATC.ModuleID = 3', 'left');
        $this->db->join(PROFILEURL . ' UE_URL1 ', 'UE1.UserID = UE_URL1.EntityID AND UE_URL1.EntityType = "User" ', 'left');

        $this->db->join(QUIZ . ' QUZ ', 'QUZ.QuizID = AATC.ModuleEntityID  AND AATC.ModuleID = 47 ', 'left');

    }

    protected function set_entity_conds($filter) {
        $lastLogID = isset($filter['LastLogID']) ? $filter['LastLogID'] : 0;
        $is_verified = isset($filter['Verified']) ? $filter['Verified'] : 0;
        $ward_id = isset($filter['WID']) ? $filter['WID'] : '';
        
        $is_city_news = isset($filter['ActivityFilterType']) ? $filter['ActivityFilterType'] : 0;
        
        if(!empty($ward_id) && $ward_id!=1) {
            $ward_ids[] = $ward_id;
            if(!in_array(1, $ward_ids)) {
                $ward_ids[] = 1;
            }
            $ward_ids_str = implode(',', $ward_ids);
            $ward_ids_str = trim($ward_ids_str, ',');
            $this->db->join(ACTIVITYWARD . ' AW', "AW.ActivityID=AATC.ActivityID AND AW.WardID IN(" . $ward_ids_str . ")");
        }

        if($is_city_news == 12) {
            $this->db->join(CITYNEWS . ' C', 'C.ActivityID=AATC.ActivityID');
        
        }
        

        $GetEntityType = isset($filter['GET_ENTITY_TYPE']) ? $filter['GET_ENTITY_TYPE'] : 'ACTIVITY';
        
        if ($GetEntityType == 'ALL') {
            
            
            $not_allowed_activity_types = array(
                2, 3, 4, 13, 18, 22, 28, 29, 30, 31,32 
            );
            $this->db->where('(AATC.StatusID = 2 OR AATC.StatusID IS NULL)', NULL, FALSE);
        } else {
            $not_allowed_activity_types = array(
                2, 3, 4, 13, 18, 19, 20, 21, 22, 27, 28, 29, 30, 31, 32, 33
            );
            $this->db->where('AATC.StatusID', 2);
        }

        //$this->db->where_not_in('UAL.ActivityTypeID', $not_allowed_activity_types);
        
        $entities_deleted_conds = "
            IF(AATC.ActivityID != 0, 
            
                (
                (UE1.StatusID IS NULL OR UE1.StatusID NOT IN (3, 4)) AND

                (QUZ.Status IS NULL OR QUZ.Status != 3)
                )
            
            , 1 )
        ";
        $this->db->where(" $entities_deleted_conds ", NULL, FALSE);
        
        if ($GetEntityType != 'ALL') {
            return;
        }       
    }

    protected function get_select_arr($filter, $page_no) {
        $select_array = array();
        $count_total = '';
        if($page_no == 1) {
            $count_total = "SQL_CALC_FOUND_ROWS";
        }

        $select_array[] = " $count_total AATC.ActivityID";

        //Activity Attached
        $select_array[] = $this->get_activity_details_select('AATC');
        $select_array[] = $this->get_user_details_select('AATCU', 'AATCU_URL');
        $select_array[] = $this->get_entity_details_select(3, 'UE1', 'UE_URL1'); // Activity User Entity

        $select_array[] = $this->get_entity_details_select(47, 'QUZ');  //Activity QUIZ

       

        return $select_array;
    }

    protected function apply_filter($filter) {
        $post_type = isset($filter['PostType']) ? $filter['PostType'] : 0;
        $filter_type = isset($filter['ActivityFilterType']) ? $filter['ActivityFilterType'] : 0;
        $is_media_exists = isset($filter['IsMediaExists']) ? $filter['IsMediaExists'] : NULL;
        //$start_date = isset($filter['StartDate']) ? $filter['StartDate'] : '';
       // $end_date = isset($filter['EndDate']) ? $filter['EndDate'] : '';
        $start_date = (isset($filter['StartDate']) && !empty($filter['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($filter['StartDate'])) : '';
        $end_date = (isset($filter['EndDate']) && !empty($filter['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($filter['EndDate'])) : '';
        
        $user_id = (int) isset($filter['UserID']) ? $filter['UserID'] : 0;
        $search_key = isset($filter['SearchKey']) ? $filter['SearchKey'] : '';
        $tags = isset($filter['Tags']) ? $filter['Tags'] : array();
        $city_id = isset($filter['CityID']) ? $filter['CityID'] : 0;
        $age_group_id = isset($filter['AgeGroupID']) ? $filter['AgeGroupID'] : 0;
        $gender = isset($filter['Gender']) ? $filter['Gender'] : 0;
        $tag_type = isset($filter['TagType']) ? $filter['TagType'] : 0;
        $is_promoted = isset($filter['IsPromoted']) ? $filter['IsPromoted'] : NULL;
        $is_verified = isset($filter['Verified']) ? $filter['Verified'] : 0;

        if($post_type && is_array($post_type) && count($post_type)==1 && $post_type[0]==0)
        {
            $post_type = 0;
        }
                
        
        if($is_promoted !== NULL) {
            //$this->db->where_in('AATC.IsPromoted', $is_promoted);
        }
        
        $time_zone = $this->user_model->get_user_time_zone();

        

        //1- Normal Tag, 2- Hash Tag, 3- Activity Mood, 4- Activity Classification, 5- User/Reader Tag, 6- User Profession, 7- Brand
        if ($tag_type) {
            $this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID = AATC.ActivityID AND ET.EntityType = "ACTIVITY"  AND ET.StatusID = 2', 'left');
            $this->db->join(TAGS . ' TG', 'TG.TagID = ET.TagID', 'left');

            $this->db->where('TG.TagType', $tag_type);
        }

        if ($user_id) {
            if(is_array($user_id)) {
                $this->db->where_in('AATC.UserID', $user_id);
            } else {
                $this->db->where('AATC.UserID', $user_id);
            }
        }



        if (!empty($post_type)) {
            if(is_array($post_type)){
                $filter_post_types = [];
                foreach($post_type as $tempCheck) {
                    if(!$tempCheck) continue;
                    $filter_post_types[] = $tempCheck;
                }
                if(count($filter_post_types)) {
                    $this->db->where_in('AATC.PostType', $filter_post_types);
                }
            } else {
                $this->db->where('AATC.PostType', $post_type);
            }
            
        }

        if ($is_media_exists != 2 && $is_media_exists !== NULL) {
            $this->db->where("AATC.IsMediaExist = '$is_media_exists' ", NULL, FALSE);
        }

        if ($filter_type == 3) {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 23, 24, 25, 26, 30);

            $this->db->where_in('AATC.ModuleID', $modules_allowed);
            $this->db->where_in('AATC.ActivityTypeID', $activity_type_allow);
        }

        if ($filter_type == 7) {
            $this->db->where('AATC.StatusID', '19');
            //$this->db->where('AATC.DeletedBy', $user_id);
        } else if ($filter_type == 10) {
            $this->db->where('AATC.StatusID', '10');
            //$this->db->where('AATC.UserID', $user_id);
        } else if ($filter_type == 11) {
            $this->db->where('AATC.IsFeatured', '1');
        } else if ($filter_type == 13) {
            $this->db->where('AATC.Summary !=', '');
        } else if ($filter_type == 14) {
            $this->db->where('AATC.Summary', '');
        }
        
        if($is_verified != 2) {
           // $this->db->where('AATC.Verified', $is_verified);
        }

        if (!empty($search_key)) {
            $search_where = '(AATCU.FirstName LIKE "%' . $search_key .
                    '%" OR AATCU.LastName LIKE "%' . $search_key .
                    '%" OR CONCAT(AATCU.FirstName," ",AATCU.LastName) LIKE "%' . $search_key .
                    '%" OR AATC.PostContent LIKE "%' . $search_key .
                    '%" OR AATC.PostTitle LIKE "%' . $search_key 
                    . '%"))';
            $this->db->where($search_where, NULL, FALSE);
        }

        if ($start_date) {
            $start_date = date('Y-m-d', strtotime($start_date));    
            $this->db->where("DATE_FORMAT(CONVERT_TZ(AATC.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }

        if ($end_date) {
            $end_date = date('Y-m-d', strtotime($end_date)); 
            $this->db->where("DATE_FORMAT(CONVERT_TZ(AATC.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        if ($tags) {
            $this->db->where("AATC.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" . implode(',', $tags) . "))", null, false);
        }
    }
    
    protected function apply_sort_order($filter) {
        $feed_sort_by = isset($filter['FeedSortBy']) ? $filter['FeedSortBy'] : 2;

       

        if ($feed_sort_by == 2) {
            $this->db->order_by('AATC.ActivityID', 'DESC');
        } else if ($feed_sort_by == 3) {
            $this->db->order_by('(AATC.NoOfComments+AATC.NoOfLikes+AATC.NoOfViews)', 'DESC');
        } else { // $feed_sort_by == 1
            $this->db->order_by('AATC.CreatedDate', 'DESC');
        }

        if ($feed_sort_by == 'popular') {
            $this->db->where_in('AATC.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("AATC.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('AATC.NoOfComments>1', null, false);
            $this->db->order_by('AATC.ActivityTypeID', 'ASC');
            $this->db->order_by('AATC.NoOfComments', 'DESC');
            $this->db->order_by('AATC.NoOfLikes', 'DESC');
        } elseif ($feed_sort_by == 1) {
            $this->db->order_by('AATC.ActivityID', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids)) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(AATC.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } elseif ($feed_sort_by == "General") {
            $this->db->where('AATC.PostType', 0);
        } elseif ($feed_sort_by == "Question") {
            $this->db->where('AATC.PostType', 1);
        } elseif ($feed_sort_by == "UnAnswered") {
            $this->db->where('AATC.PostType', 1);
            $this->db->where('AATC.NoOfComments', 0);
        } else {
            $this->db->order_by('AATC.CreatedDate', 'DESC');
        }
    }

    // Process activity listing
    protected function iterate_entities($entities, $filter) {
        $GetEntityType = isset($filter['GET_ENTITY_TYPE']) ? $filter['GET_ENTITY_TYPE'] : 'ACTIVITY';
        $populated_entities = array();
        $new_entity = array();
        foreach ($entities as $entity) {
            $new_entity = array();

            // Activity Log deatils  
            $new_entity['activity_log_details'] = array(
                'ID' => $entity['AATC_ActivityID'],
                'ModuleID' => $entity['AATC_ModuleID'],
                'ModuleEntityID' => $entity['AATC_ActivityID'],
                'ActivityTypeID' => $entity['AATC_ActivityTypeID'],
                'ActivityDate' => $entity['AATC_CreatedDate'],
                'ActivityData' => '',
            );

            // Subject user who have done action
            $new_entity['subject_user'] = $this->get_select_fields_user_details($entity, 'AATCU', 'AATCU_URL');

            $new_entity['activity'] = $this->get_select_fields_activity_details($entity, 'AATC', TRUE);
            $new_entity['activity_user'] = $this->get_select_fields_user_details($entity, 'AATCU', 'AATCU_URL');
            
            $populated_entities[] = $new_entity;
        }

        return $populated_entities;
    }

    // Field select helpers
    protected function get_user_details_select($pefix_usr_tbl, $prefix_url_tbl) {
        return "CONCAT($pefix_usr_tbl.FirstName, ' ', $pefix_usr_tbl.LastName) AS {$pefix_usr_tbl}_UserName, $pefix_usr_tbl.ProfilePicture {$pefix_usr_tbl}_ProfilePicture, "
                . "$pefix_usr_tbl.UserID {$pefix_usr_tbl}_UserID, $pefix_usr_tbl.UserGUID {$pefix_usr_tbl}_UserGUID, $prefix_url_tbl.Url  {$prefix_url_tbl}_UserProfileURL, {$pefix_usr_tbl}.AccountSuspendTill {$pefix_usr_tbl}_AccountSuspendTill";
    }

    protected function get_activity_details_select($pefix_tbl) {
        return "$pefix_tbl.ActivityID {$pefix_tbl}_ActivityID, $pefix_tbl.PostContent {$pefix_tbl}_PostContent, $pefix_tbl.Summary {$pefix_tbl}_Summary, $pefix_tbl.ActivityTypeID {$pefix_tbl}_ActivityTypeID, "
                . "$pefix_tbl.NoOfLikes {$pefix_tbl}_NoOfLikes, $pefix_tbl.NoOfComments {$pefix_tbl}_NoOfComments, $pefix_tbl.CreatedDate {$pefix_tbl}_CreatedDate, "
                . "$pefix_tbl.IsVisible {$pefix_tbl}_IsVisible, $pefix_tbl.PostType {$pefix_tbl}_PostType, $pefix_tbl.PostTitle {$pefix_tbl}_PostTitle, "
                . "$pefix_tbl.ModuleID {$pefix_tbl}_ModuleID, $pefix_tbl.ModuleEntityID {$pefix_tbl}_ModuleEntityID , $pefix_tbl.UserID {$pefix_tbl}_UserID"
                . ", $pefix_tbl.IsFileExists {$pefix_tbl}_IsFileExists , $pefix_tbl.Params {$pefix_tbl}_Params, $pefix_tbl.ActivityGUID {$pefix_tbl}_ActivityGUID"
                . ", $pefix_tbl.PostAsModuleID {$pefix_tbl}_PostAsModuleID , $pefix_tbl.PostAsModuleEntityID {$pefix_tbl}_PostAsModuleEntityID "
                . ", $pefix_tbl.ParentActivityID {$pefix_tbl}_ParentActivityID, $pefix_tbl.IsPromoted {$pefix_tbl}_IsPromoted "
                . ", $pefix_tbl.IsFeatured {$pefix_tbl}_IsFeatured, $pefix_tbl.IsAdminFeatured {$pefix_tbl}_IsAdminFeatured, $pefix_tbl.Verified {$pefix_tbl}_Verified "
                . ", $pefix_tbl.IsShowOnNewsFeed {$pefix_tbl}_IsShowOnNewsFeed, $pefix_tbl.IsCityNews {$pefix_tbl}_IsCityNews"; 
    }

    protected function get_entity_details_select($module_id, $prefix_tbl1, $prefix_tbl2 = '') {
        
        // User Entity
        if ($module_id == 3) {
            return "{$prefix_tbl1}.UserGUID {$prefix_tbl1}_GUID , CONCAT($prefix_tbl1.FirstName, ' ', $prefix_tbl1.LastName) AS {$prefix_tbl1}_Name,
             $prefix_tbl1.ProfilePicture {$prefix_tbl1}_Image, '' AS {$prefix_tbl1}_CoverImage, $prefix_tbl2.Url AS {$prefix_tbl1}_ProfileURL, 'USER' AS {$prefix_tbl1}_EntityType";
        }

         // Quiz Entity
         if ($module_id == 47) {
            return "{$prefix_tbl1}.QuizGUID {$prefix_tbl1}_GUID, {$prefix_tbl1}.Title {$prefix_tbl1}_Name, '' AS {$prefix_tbl1}_Image, 
            '' AS {$prefix_tbl1}_CoverImage, '' AS {$prefix_tbl1}_ProfileURL, 'QUIZ' AS {$prefix_tbl1}_EntityType";
        }
    }

    // Resulset field get helpers
    protected function get_select_fields_user_details($entity, $pefix_usr_tbl, $prefix_url_tbl) {
        return array(
            'UserName' => !empty($entity[$pefix_usr_tbl . '_UserName']) ? $entity[$pefix_usr_tbl . '_UserName'] : '',
            'ProfilePicture' => !empty($entity[$pefix_usr_tbl . '_ProfilePicture']) ? $entity[$pefix_usr_tbl . '_ProfilePicture'] : '',
            'UserGUID' => !empty($entity[$pefix_usr_tbl . '_UserGUID']) ? $entity[$pefix_usr_tbl . '_UserGUID'] : '',
            'UserID' => !empty($entity[$pefix_usr_tbl . '_UserID']) ? $entity[$pefix_usr_tbl . '_UserID'] : '',
            'UserProfileURL' => !empty($entity[$prefix_url_tbl . '_UserProfileURL']) ? $entity[$prefix_url_tbl . '_UserProfileURL'] : '',
            'SuspendDate' => !empty($entity[$prefix_url_tbl . '_AccountSuspendTill']) ? $entity[$prefix_url_tbl . '_AccountSuspendTill'] : '',
        );
    }

    protected function get_select_fields_activity_details($entity, $pefix_tbl, $get_entity_details = false) {
        $activity_detials = array(
            'ActivityID' => ($entity[$pefix_tbl . '_ActivityID']) ? $entity[$pefix_tbl . '_ActivityID'] : '',
            'ActivityGUID' => ($entity[$pefix_tbl . '_ActivityGUID']) ? $entity[$pefix_tbl . '_ActivityGUID'] : '',
            'PostContent' => ($entity[$pefix_tbl . '_PostContent']) ? $entity[$pefix_tbl . '_PostContent'] : '',
            'ActivityTypeID' => ($entity[$pefix_tbl . '_ActivityTypeID']) ? $entity[$pefix_tbl . '_ActivityTypeID'] : '',
            'NoOfLikes' => ($entity[$pefix_tbl . '_NoOfLikes']) ? $entity[$pefix_tbl . '_NoOfLikes'] : '',
            'NoOfComments' => ($entity[$pefix_tbl . '_NoOfComments']) ? $entity[$pefix_tbl . '_NoOfComments'] : '',
            'CreatedDate' => ($entity[$pefix_tbl . '_CreatedDate']) ? $entity[$pefix_tbl . '_CreatedDate'] : '',
            'IsVisible' => ($entity[$pefix_tbl . '_IsVisible']) ? $entity[$pefix_tbl . '_IsVisible'] : '',
            'PostType' => ($entity[$pefix_tbl . '_PostType']) ? $entity[$pefix_tbl . '_PostType'] : '',
            'PostTitle' => ($entity[$pefix_tbl . '_PostTitle']) ? $entity[$pefix_tbl . '_PostTitle'] : '',
            'ModuleID' => ($entity[$pefix_tbl . '_ModuleID']) ? $entity[$pefix_tbl . '_ModuleID'] : '',
            'ModuleEntityID' => ($entity[$pefix_tbl . '_ModuleEntityID']) ? $entity[$pefix_tbl . '_ModuleEntityID'] : '',
            'UserID' => ($entity[$pefix_tbl . '_UserID']) ? $entity[$pefix_tbl . '_UserID'] : 0,
            'IsFileExists' => ($entity[$pefix_tbl . '_IsFileExists']) ? $entity[$pefix_tbl . '_IsFileExists'] : 0,
            'Params' => ($entity[$pefix_tbl . '_Params']) ? $entity[$pefix_tbl . '_Params'] : '{}',
            'PostAsModuleID' => ($entity[$pefix_tbl . '_PostAsModuleID']) ? $entity[$pefix_tbl . '_PostAsModuleID'] : 0,
            'PostAsModuleEntityID' => ($entity[$pefix_tbl . '_PostAsModuleEntityID']) ? $entity[$pefix_tbl . '_PostAsModuleEntityID'] : 0,
            'ParentActivityID' => ($entity[$pefix_tbl . '_ParentActivityID']) ? $entity[$pefix_tbl . '_ParentActivityID'] : 0,
            'IsPromoted' => ($entity[$pefix_tbl . '_IsPromoted']) ? $entity[$pefix_tbl . '_IsPromoted'] : 0,
            'IsFeatured' => ($entity[$pefix_tbl . '_IsFeatured']) ? $entity[$pefix_tbl . '_IsFeatured'] : 0,
            'IsAdminFeatured' => ($entity[$pefix_tbl . '_IsAdminFeatured']) ? $entity[$pefix_tbl . '_IsAdminFeatured'] : 0,
            'IsShowOnNewsFeed' => ($entity[$pefix_tbl . '_IsShowOnNewsFeed']) ? $entity[$pefix_tbl . '_IsShowOnNewsFeed'] : 0,
            'Verified' => ($entity[$pefix_tbl . '_Verified']) ? $entity[$pefix_tbl . '_Verified'] : 0,
            'IsCityNews' => ($entity[$pefix_tbl . '_IsCityNews']) ? $entity[$pefix_tbl . '_IsCityNews'] : 0,
            'Description' => ($entity[$pefix_tbl . '_Summary']) ? $entity[$pefix_tbl . '_Summary'] : '',
            'OrientationCategoryID' => ($entity['OrientationCategoryID']) ? $entity['OrientationCategoryID'] : 0,
        );

        
        //$activity_detials['Params'] = json_decode($activity_detials['Params']);
        $activity_detials['Album'] = [];
        $activity_detials = $this->get_album_data($activity_detials);
        
        
        $activity_detials['IsPined']        = 0;
        

        $activity_detials['Files'] = array();
        if ($activity_detials['IsFileExists']) {
            $activity_detials['Files'] = $this->activity_model->get_activity_files($activity_detials['ActivityID']);
        }

        
        if ($activity_detials['NoOfComments'] > 0) {
            $activity_detials['NoOfComments'] = $activity_detials['NoOfComments'] + $this->activity_model->get_entity_comment_reply_count($activity_detials['ActivityID'], 'ACTIVITY');
        }

        foreach (array('UE1', 'GE', 'PE', 'EE', 'EFC', 'QUZ', '') as $entity_prefix) {
            $entity_details = $this->get_select_fields_entity_details($entity, $entity_prefix, '', true, $activity_detials['ModuleEntityID']);
            if (!empty($entity_details) || !$entity_prefix) {
                $activity_detials = array_merge($activity_detials, $entity_details);
                break;
            }
        }


        $edit_post_content = $activity_detials['PostContent'];
        $activity_detials['PostContent'] = $this->activity_model->parse_tag($activity_detials['PostContent'], $activity_detials['ActivityID']);
        //$activity_detials['EditPostContent'] = $this->activity_model->parse_tag_edit($edit_post_content, $activity_detials['ActivityID']);
        $activity_detials['PostTitle'] = $this->activity_model->parse_tag($activity_detials['PostTitle'], $activity_detials['ActivityID']);

        $activity_detials['ActivityURL'] = '';
        if (!empty($activity_detials['ActivityID']) && !empty($activity_detials['EntityProfileURL'])) {
            //$activity_detials['ActivityURL'] = get_single_post_url($activity_detials);
        }

        return $activity_detials;
    }
    
    protected function get_select_fields_entity_details($entity, $pefix_tbl, $prefix_tbl2 = '', $check_empty = true, $module_entity_id = 0) {

        if (!$pefix_tbl) {
            return array(
                'EntityName' => '',
                'EntityProfilePicture' => '',
                'EntityType' => '',
                'EntityGUID' => '',
                'EntityProfileURL' => '',
            );
        }

        if ($check_empty && empty($entity[$pefix_tbl . '_GUID'])) {
            return array();
        }

        $entity_details = array(
            'EntityName' => !empty($entity[$pefix_tbl . '_Name']) ? $entity[$pefix_tbl . '_Name'] : '',
            'EntityProfilePicture' => !empty($entity[$pefix_tbl . '_Image']) ? $entity[$pefix_tbl . '_Image'] : '',
            'EntityType' => !empty($entity[$pefix_tbl . '_EntityType']) ? $entity[$pefix_tbl . '_EntityType'] : '',
            'EntityGUID' => !empty($entity[$pefix_tbl . '_GUID']) ? $entity[$pefix_tbl . '_GUID'] : '',
            'EntityProfileURL' => !empty($entity[$pefix_tbl . '_ProfileURL']) ? $entity[$pefix_tbl . '_ProfileURL'] : '',
            'Type' => !empty($entity[$pefix_tbl . '_Type']) ? $entity[$pefix_tbl . '_Type'] : '',
        );
        

        return $entity_details;
    }
              
    protected function get_album_data($activity_detials) {
        
        $params = $activity_detials['Params'] = json_decode($activity_detials['Params']);
        $activity_detials['Album'] = [];
        $album_guid = isset($activity_detials['Params']->AlbumGUID) ? $activity_detials['Params']->AlbumGUID : '';
        $MediaGUID = isset($activity_detials['Params']->MediaGUID) ? $activity_detials['Params']->MediaGUID : '';
        
        
        if (in_array($activity_detials['ActivityTypeID'], array(23,24)))
        {
            //$params = json_decode($res['Params'], true);
            if ($MediaGUID)
            {
                $res_entity_id = get_detail_by_guid($MediaGUID, 21);
                if ($res_entity_id)
                {
                    $res_entity_type = 'Media';
                    //$activity['NoOfComments'] = $this->get_activity_comment_count($res_entity_type, $res_entity_id, $BUsers); //$res['NoOfComments'];
                    //$activity['NoOfLikes'] = $this->get_like_count($res_entity_id, $res_entity_type, $BUsers); //$res['NoOfLikes'];
                   // $activity['NoOfDislikes'] = $this->get_like_count($res_entity_id, $res_entity_type, $BUsers, 3); //$res['NoOfDislikes'];
                    $activity_detials['Album'] = $this->activity_model->get_albums($res_entity_id, $activity_detials['UserID'], '', $res_entity_type, 1); 
                    return $activity_detials;
                }
            }
        } 
        
        
        if (!in_array($activity_detials['ActivityTypeID'], array(5, 6, 9, 10, 14, 15))) {
            //if($album_guid) {
                $media_section_reference_id = $activity_detials['ActivityID'];
                if($activity_detials['ActivityTypeID'] == 23) {
                    $media_section_reference_id = $activity_detials['UserID'];
                }
            
                $activity_detials['Album'] = $this->activity_model->get_albums($media_section_reference_id, $activity_detials['UserID'], $album_guid);
            //}
            
        }
        
        if (in_array($activity_detials['ActivityTypeID'], array(14, 15))) {
            
            $activity_detials['Album'] = $this->activity_model->get_albums($activity_detials['ParentActivityID'], $activity_detials['UserID'], '', 'Media');
            if (!empty($activity_detials['Album']['AlbumType'])) {
                $activity_detials['EntityType'] = ucfirst(strtolower($activity_detials['Album']['AlbumType']));
            } else {
                $activity_detials['EntityType'] = 'Media';
            }
        } 
        
        
        if (in_array($activity_detials['ActivityTypeID'], array(5,6,9,10))) {
            $album_flag = TRUE;
            if ($activity_detials['ActivityTypeID'] == 10 || $activity_detials['ActivityTypeID'] == 9)
            {
                $album_flag = FALSE;
                $activity_detials['ParentActivityID'] = $activity_detials['ParentActivityID'];
                if (!$activity_detials['ParentActivityID'])
                {
                    $activity_detials['ParentActivityID'] = $activity_detials['ActivityID'];
                }
                $parent_activity_detail = get_detail_by_id($activity_detials['ParentActivityID'], '', 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID,ActivityTypeID', 2);
                if (!empty($parent_activity_detail))
                {
                    if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6)))
                    {
                        if (!empty($parent_activity_detail['Params']))
                        {
                            $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                            if (!empty($album_detail['AlbumGUID']))
                            {
                                @$activity_detials['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                $album_flag = TRUE;
                            }
                        }
                    }
                }
            }
            
            if ($album_flag)
            {
                $count = 4;
                if ($activity_detials['ActivityTypeID'] == 6)
                {
                    $count = $activity_detials['Params']->count;
                }
                $album_details = $this->album_model->get_album_by_guid($activity_detials['Params']->AlbumGUID);
                $activity_detials['EntityName'] = $album_details['AlbumName'];
                $activity_detials['Album'] = $this->activity_model->get_albums(
                        $activity_detials['ActivityID'], $activity_detials['UserID'], 
                        $activity_detials['Params']->AlbumGUID, 'Activity', $count
                );
            }

        }        
        return $activity_detials;
        
    }

    function save_user_orientation($data) {
        $user_orientations = safe_array_key($data, 'UserOrientation', array());       
        
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $update_data = array();
        $insert_orientation_data = array();
        $delete_orientation_data = array();
        foreach($user_orientations as $user_orientation) {
            $activity_id = $user_orientation['ActivityID'];
            $description = $user_orientation['Description'];

            $orientation_category_id = $user_orientation['OrientationCategoryID'];
            if(empty($orientation_category_id)) {
                $delete_orientation_data[] = $activity_id;
            } else {
                $insert_orientation_data[] = array(
                    'ActivityID' => $activity_id,
                    'OrientationCategoryID' => $orientation_category_id,
                    'CreatedDate' => $current_date
                );
            }

            $update_data[] = array(
                'ActivityID' => $activity_id,
                'Summary' => $description
            );
        }
        if ($update_data) {            
            $this->db->update_batch(ACTIVITY, $update_data, 'ActivityID');
        }   
        if ($insert_orientation_data) {            
            $this->db->insert_on_duplicate_update_batch(USERORIENTATION, $insert_orientation_data);
        }     
        if ($delete_orientation_data) {   
            $this->db->where_in('ActivityID', $delete_orientation_data);
            $this->db->delete(USERORIENTATION);   
        }
    }
}
