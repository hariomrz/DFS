<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Description of Activity_model
 *
 * 
 */
class Dashboard_activity_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('activity/activity_model', 'group/group_model', 'album/album_model', 'util/util_location_model'));
        
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

        $select_array = $this->get_select_arr($filter, $page_no);
        $filter['CityID'] = $this->util_location_model->get_city_id($filter);

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(USERSACTIVITYLOG . ' UAL ');
        $this->db->join(USERS . ' USUB ', 'USUB.UserID = UAL.UserID', 'left');
        $this->db->join(PROFILEURL . ' USUB_URL ', 'USUB.UserID = USUB_URL.EntityID AND USUB_URL.EntityType = "User" ', 'left');



        $this->db->join(ACTIVITY . ' AATC ', 'AATC.ActivityID = UAL.ActivityID', 'left');
        $this->db->join(USERS . ' AATCU ', 'AATCU.UserID = AATC.UserID', 'left');
        $this->db->join(PROFILEURL . ' AATCU_URL ', 'AATCU.UserID = AATCU_URL.EntityID AND AATCU_URL.EntityType = "User" ', 'left');

        $this->set_entity_join($filter);


        $this->db->join(ACTIVITY . ' PA ', 'PA.ActivityID = AATC.ParentActivityID', 'left');
        $this->db->join(USERS . ' PAU ', 'PAU.UserID = PA.UserID', 'left');
        $this->db->join(PROFILEURL . ' PAU_URL ', 'PAU.UserID = PAU_URL.EntityID AND PAU_URL.EntityType = "User" ', 'left');

        $this->db->where_not_in('AATC.ActivityTypeID',array(19, 20, 23, 36,37));
        
        $this->set_entity_conds($filter);

        // Apply filter
        $this->apply_filter($filter);

        $this->db->group_by('UAL.ID');
        //$this->db->group_by('AATC.ActivityID');

        $this->apply_sort_order($filter);

        if (!$count_only) {
            $this->db->limit($page_size, $offset);
        }


        $query = $this->db->get();

        //echo $this->db->last_query(); 

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
     * [user_interest_data]  Get user data
     * @param  [type]  [$user_id]
     */
    public function get_user_details($user_id) {

        $today_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $global_settings = $this->config->item("global_settings");
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);

        $select_array[] = "CONCAT(U.FirstName, ' ', U.LastName) AS Name, IFNULL(U.ProfilePicture, '') AS ProfilePicture, U.Verified, IFNULL(U.Gender, '') Gender, DATE_FORMAT(U.CreatedDate, '$mysql_date') AS MemberSince,U.StatusID,U.UserGUID,U.Email";
        $select_array[] = " IFNULL(DATE_FORMAT(FROM_DAYS(DATEDIFF('$today_date', UD.DOB)), '%Y')+0, '') AS Age, IFNULL(UD.MartialStatus, '') MartialStatus, IFNULL(CT.Name, '') AS City";
        //$select_array[] = " IFNULL((Select Group_Concat(CM.CategoryID) From ".ENTITYCATEGORY." EC LEFT JOIN ".CATEGORYMASTER." CM ON EC.CategoryID = CM.CategoryID WHERE EC.ModuleEntityID = $user_id AND EC.ModuleID = 1), '') AS InterestIDs";
        //$select_array[] = " IFNULL((Select Group_Concat(CM.Name)  From ".ENTITYCATEGORY." EC LEFT JOIN ".CATEGORYMASTER." CM ON EC.CategoryID = CM.CategoryID WHERE EC.ModuleEntityID = $user_id AND EC.ModuleID = 1), '') AS InterestNames";       
        $select_array[] = "IFNULL(COUNT(A.UserID), 0) AS PostCount, IFNULL((Select COUNT(*) FROM PostComments WHERE " . POSTCOMMENTS . ".UserID = $user_id), 0) AS CommnetCount";
        $select_array[] = "UD.BrowsingAverage, UD.ContributionAverage, UD.HighlyActivePercentage, UD.RelationWithID, UD.RelationWithName, UD.ConnectWith, UD.ConnectFrom";
        $select_array[] = "UD.NoOfFriendsFB, UD.NoOfFollowersFB, UD.NoOfFollowersTw, UD.NoOfConnectionsIn, U.UserID, 3 AS ModuleID, UD.LocalityID";

        $select_array[] = "PU.Url as UserName";

        /* $sub = $this->subquery->start_subquery('select');
        $sub->select('GROUP_CONCAT(UR.RoleID)',FALSE)->from(USERROLES.' AS UR');
        $sub->where('UR.UserID = U.UserID');
        $this->subquery->end_subquery('UserRoleID');
         * 
         */

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(USERS . ' U ');
        $this->db->join(PROFILEURL . ' PU ',"U.UserID=PU.EntityID AND PU.EntityType='User'","left");
        $this->db->join(USERDETAILS . ' UD ', 'UD.UserID = U.UserID', 'left');
        $this->db->join(CITIES . ' CT ', 'CT.CityID = UD.HomeCityID', 'left');
        $this->db->join(ACTIVITY . ' A ', 'A.UserID = U.UserID AND A.ActivityTypeID != 13', 'left');


        //$this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID = AATC.ActivityID  AND ET.EntityType = "USER" AND ET.StatusID = 2', 'left');
        //$this->db->join(TAGS . ' TG', 'TG.TagID = ET.TagID', 'left');

        $this->db->group_by('U.UserID');
        $this->db->where('U.UserID', $user_id);

        $query = $this->db->get();
        //echo $this->db->last_query(); die;


        $user_data = $query->row_array();
        
        if($user_data['LocalityID']) {
            $this->load->model(array('locality/locality_model'));
            $user_data['Locality'] = $this->locality_model->get_locality($user_data['LocalityID']);
        }
        unset($user_data['LocalityID']);
                        
        $interest_data = $this->user_interest_data($user_id);
        $user_data['Interests'] = $interest_data['interests'];
        $user_data['NewInterestsData'] = $interest_data['new_interests_data'];
        //$users_relation = get_user_relation($user_id, $user_id);
        //$user_data['relation'] = $users_relation;

        $user_data = $this->set_user_relationship($user_data);

        $this->load->model(array('log/user_activity_log_score_model'));
        $user_data['NowScore'] = $this->user_activity_log_score_model->get_user_score_comparision('BROWSING', 30, 0);
        $user_data['BeforeScore'] = $this->user_activity_log_score_model->get_user_score_comparision('BROWSING', 60, 30);

        $this->load->model(array('users/user_model'));
        $this->user_model->set_friend_followers_list($user_id);
        $FriendFollowersList = $this->user_model->get_friend_followers_list($user_id);    //print_r($FriendFollowersList); die;
        $user_data['NoOfFriendsCSocial'] = count($FriendFollowersList['Friends']);
        $user_data['NoOfFollowCSocial'] = count($FriendFollowersList['Follow']);

        // Contribution score text settings
        $this->load->model(array('log/user_activity_log_score_model'));
        $user_data['ContributionAverageText'] = $this->user_activity_log_score_model->get_score_text($user_data['ContributionAverage'], 'CONTRIBUTION');
        
        // Browsing score text settings
        $user_data['BrowsingAverageText'] = $this->user_activity_log_score_model->get_score_text($user_data['BrowsingAverage'], 'BROWSING');
        $user_data['UserRoleID'] = $this->getUserRoles($user_id);
        return $user_data;
    }

    /**
     * Set user relationship data
     * @param  [array]  [$userdata]
     */
    public function set_user_relationship($userdata) {
        $this->load->model(array('users/user_model'));
        $userdata['RelationWithName'] = $userdata['RelationWithName'];
        $userdata['RelationWithGUID'] = "";
        $userdata['RelationWithURL'] = "";
        $userdata['ConnectWith'] = $this->user_model->get_connect_with($userdata['ConnectWith']);
        $userdata['ConnectFrom'] = $this->user_model->get_connect_from($userdata['ConnectFrom']);
        if (!empty($userdata['RelationWithID'])) {
            $RelationWithDetail = get_detail_by_id($userdata['RelationWithID'], 3, 'FirstName, LastName, UserGUID', 2);
            $userdata['RelationWithName'] = trim($RelationWithDetail['FirstName'] . ' ' . $RelationWithDetail['LastName']);
            $userdata['RelationWithGUID'] = $RelationWithDetail['UserGUID'];

            $userdata['RelationWithURL'] = $this->user_model->get_profile_link($userdata['RelationWithID']);
        }
        unset($userdata['RelationWithID']);

        $userdata['MartialStatusTxt'] = "----";
        if ($userdata['MartialStatus'] == 1) {
            $userdata['MartialStatusTxt'] = 'Single';
        }

        if ($userdata['MartialStatus'] == 2) {
            $userdata['MartialStatusTxt'] = 'In a relationship';
        }

        if ($userdata['MartialStatus'] == 3) {
            $userdata['MartialStatusTxt'] = 'Engaged';
        }

        if ($userdata['MartialStatus'] == 4) {
            $userdata['MartialStatusTxt'] = 'Married';
        }

        if ($userdata['MartialStatus'] == 5) {
            $userdata['MartialStatusTxt'] = 'Its complicated';
        }

        if ($userdata['MartialStatus'] == 6) {
            $userdata['MartialStatusTxt'] = 'Separated';
        }

        if ($userdata['MartialStatus'] == 7) {
            $userdata['MartialStatusTxt'] = 'Divorced';
        }

        return $userdata;
    }

    /**
     * [user_interest_data]  Get user interests with its percentage calculation
     * @param  [type]  [$user_id]
     */
    function user_interest_data($user_id, $user_type = 0) {
        $select_array[] = " CM.CategoryID, CM.Name, EC.ModuleEntityCount, EC.ModuleEntityUserType";
        $this->db->select(implode(',', $select_array), false);
        $this->db->from(ENTITYCATEGORY . ' EC ');
        $this->db->join(CATEGORYMASTER . ' CM ', "EC.CategoryID = CM.CategoryID AND EC.ModuleID = 3 AND EC.ModuleEntityID = $user_id", 'Inner');

        $this->db->where('CM.ModuleID', '31');
        $this->db->where('CM.StatusID', '2');
        if ($user_type) {
            $this->db->where('EC.ModuleEntityUserType', $user_type);
        }
        $this->db->order_by('EC.ModuleEntityCount', 'DESC');
        $query = $this->db->get();
        $interests = $query->result_array();

        $total_interest_count = 0;
        foreach ($interests as $interest) {
            $total_interest_count += (int) $interest['ModuleEntityCount'];
        }

        $total_interests_to_take = 5;
        $other_interest_wight = 0;
        $new_interests_data = [];
        foreach ($interests as $index => $interest) {
            if ($total_interests_to_take > $index) {
                $interests[$index]['Percentage'] = percentage($total_interest_count, $interest['ModuleEntityCount']);
                $new_interests_data[] = $interests[$index];
            } else {
                $other_interest_wight += $interest['ModuleEntityCount'];
            }
            
            $interests[$index]['AddedBy'] = (in_array($interests[$index]['ModuleEntityUserType'], [2, 3])) ? 1 : 0; 
        }

        if ($other_interest_wight) {
            $new_interests_data[] = array(
                'CategoryID' => 0,
                'Name' => 'Others',
                'ModuleEntityCount' => 0,
                'Percentage' => percentage($total_interest_count, $other_interest_wight),
            );
        }


        return array(
            'interests' => $interests,
            'new_interests_data' => $new_interests_data
        );
    }

    /**
     * [user_interest_data]  Get tags for entities ( User, Activity )
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


        $this->db->join(POSTCOMMENTS . ' PCE ', 'PCE.PostCommentID = UAL.EntityID AND UAL.ActivityTypeID IN(19, 20) AND PCE.EntityType IN("Activity", "MEDIA", "RATING") ', 'left');
        $this->db->join(POSTCOMMENTS . ' PCE_P ', 'PCE_P.PostCommentID = PCE.ParentCommentID ', 'left');
        $this->db->join(USERS . ' PCE_P_U ', 'PCE_P_U.UserID = PCE_P.UserID ', 'left');
        $this->db->join(PROFILEURL . ' PCE_P_U_URL ', 'PCE_P_U.UserID = PCE_P_U_URL.EntityID AND PCE_P_U_URL.EntityType = "User" ', 'left');

        $this->db->join(GROUPS . ' GE ', 'GE.GroupID = AATC.ModuleEntityID  AND AATC.ModuleID = 1 ', 'left');
        //$this->db->join(GROUPMEMBERS . ' GM ', 'GM.GroupID = GE.GroupID ', 'left');
        //$this->db->join(USERS . ' GUM ', 'GUM.UserID = GM.ModuleEntityID ', 'left');


        $this->db->join(PAGES . ' PE ', 'PE.PageID = AATC.ModuleEntityID  AND AATC.ModuleID = 18 ', 'left');

        $this->db->join(EVENTS . ' EE ', 'EE.EventID = AATC.ModuleEntityID  AND AATC.ModuleID = 14', 'left');
        $this->db->join(MEDIA . ' EE_M ', 'EE_M.MediaID = EE.ProfileImageID', 'left');

        $this->db->join(FORUMCATEGORY . ' EFC ', 'EFC.ForumCategoryID = AATC.ModuleEntityID  AND AATC.ModuleID = 34', 'left');
        $this->db->join(MEDIA . ' EFC_M ', 'EFC_M.MediaID = EFC.MediaID', 'left');

        $this->db->join(USERS . ' UE1 ', 'UE1.UserID  = AATC.ModuleEntityID  AND AATC.ModuleID = 3', 'left');
        $this->db->join(PROFILEURL . ' UE_URL1 ', 'UE1.UserID = UE_URL1.EntityID AND UE_URL1.EntityType = "User" ', 'left');



        $GetEntityType = isset($filter['GET_ENTITY_TYPE']) ? $filter['GET_ENTITY_TYPE'] : 'ACTIVITY';
        if ($GetEntityType != 'ALL') {
            return;
        }

        $this->db->join(USERS . ' UE ', 'UE.UserID = UAL.ModuleEntityID  AND UAL.ModuleID = 3 AND UAL.ActivityTypeID = 21 ', 'left');
        $this->db->join(PROFILEURL . ' UE_URL ', 'UE.UserID = UE_URL.EntityID AND UE_URL.EntityType = "User" ', 'left');

        $this->db->join(GROUPS . ' GE_PRL ', 'GE_PRL.GroupID = UAL.ModuleEntityID  AND UAL.ModuleID = 1 ', 'left');
        //$this->db->join(GROUPMEMBERS . ' GM_PRL ', 'GM_PRL.GroupID = GE_PRL.GroupID ', 'left');
        //$this->db->join(USERS . ' GUM_PRL ', 'GUM_PRL.UserID = GM_PRL.ModuleEntityID ', 'left');


        $this->db->join(PAGES . ' PE_PRL ', 'PE_PRL.PageID = UAL.ModuleEntityID  AND UAL.ModuleID = 18 ', 'left');

        $this->db->join(EVENTS . ' EE_PRL ', 'EE_PRL.EventID = UAL.ModuleEntityID  AND UAL.ModuleID = 14', 'left');
        $this->db->join(MEDIA . ' EE_M_PRL ', 'EE_M_PRL.MediaID = EE_PRL.ProfileImageID', 'left');

        $this->db->join(FORUMCATEGORY . ' EFC_PRL ', 'EFC_PRL.ForumCategoryID = UAL.ModuleEntityID  AND UAL.ModuleID = 34', 'left');
        $this->db->join(MEDIA . ' EFC_M_PRL ', 'EFC_M_PRL.MediaID = EFC_PRL.MediaID', 'left');
    }

    protected function set_entity_conds($filter) {
        $lastLogID = isset($filter['LastLogID']) ? $filter['LastLogID'] : 0;
        $is_verified = isset($filter['Verified']) ? $filter['Verified'] : 0;

        // Post Comment entity condition
        $this->db->where(" IF(UAL.ActivityTypeID = 20, (PCE.PostCommentID IS NOT NULL), 1) ", NULL, FALSE);
        
        if($is_verified != 2) {
            $this->db->where("(AATC.Verified IS NULL OR AATC.Verified = $is_verified)", NULL, FALSE);
        }
        
        $this->db->where('( (PCE.Verified IS NULL OR PCE.Verified = 0) AND ( PCE.StatusID IS NULL OR  PCE.StatusID = 2) ) ', NULL, FALSE);
        
        if($lastLogID) {
            $this->db->where(" UAL.ID >  $lastLogID", NULL, FALSE);
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

        $this->db->where_not_in('UAL.ActivityTypeID', $not_allowed_activity_types);
        
        $entities_deleted_conds = "
            IF(AATC.ActivityID != 0, 
            
                (
                (UE1.StatusID IS NULL OR UE1.StatusID NOT IN (3, 4)) AND

                (GE.StatusID IS NULL OR GE.StatusID = 2) AND

                (PE.StatusID IS NULL OR PE.StatusID = 2) AND

                (EE.IsDeleted IS NULL OR   EE.IsDeleted = 0) AND

                (EFC.StatusID IS NULL OR EFC.StatusID = 2)
                )
            
            , 1 )
        ";
        $this->db->where(" $entities_deleted_conds ", NULL, FALSE);
        
        if ($GetEntityType != 'ALL') {
            return;
        }

        $profile_view_conditions = "
            UE.UserID IS NOT NULL OR GE_PRL.GroupID IS NOT NULL OR PE_PRL.PageID IS NOT NULL OR EE_PRL.EventID IS NOT NULL OR EFC_PRL.ForumCategoryID IS NOT NULL
        ";
        $this->db->where(" IF(UAL.ActivityTypeID = 21, ($profile_view_conditions), 1) ", NULL, FALSE);
        
        $this->db->where('( (UE.Verified IS NULL OR UE.Verified = 0) AND ( UE.StatusID IS NULL OR  UE.StatusID = 2) ) ', NULL, FALSE);
    }

    protected function get_select_arr($filter, $page_no) {
        $select_array = array();
        $count_total = '';
        if($page_no == 1) {
            $count_total = "SQL_CALC_FOUND_ROWS";
        }
        

        //UserActivityLog
        $select_array[] = " $count_total UAL.ID, UAL.ModuleID UAL_ModuleID, UAL.ModuleEntityID UAL_ModuleEntityID, UAL.ActivityTypeID UAL_ActivityTypeID, UAL.ActivityDate UAL_ActivityDate";
        $select_array[] = "UAL.UserID UAL_UserID, UAL.EntityID UAL_EntityID, UAL.ActivityData UAL_ActivityData";
        $select_array[] = $this->get_user_details_select('USUB', 'USUB_URL');

        //Activity Attached
        $select_array[] = $this->get_activity_details_select('AATC');
        $select_array[] = $this->get_user_details_select('AATCU', 'AATCU_URL');


        // Parent Activity
        $select_array[] = $this->get_activity_details_select('PA');
        $select_array[] = $this->get_user_details_select('PAU', 'PAU_URL');

        // PostCommnets Entity
        $select_array[] = $this->get_comment_details_select('PCE');
        $select_array[] = $this->get_comment_details_select('PCE_P');
        $select_array[] = $this->get_user_details_select('PCE_P_U', 'PCE_P_U_URL');


        
        $select_array[] = $this->get_entity_details_select(1, 'GE');  // Activity Group Entity
        $select_array[] = $this->get_entity_details_select(3, 'UE1', 'UE_URL1'); // Activity User Entity
        $select_array[] = $this->get_entity_details_select(18, 'PE');//Activity Page Entity
        $select_array[] = $this->get_entity_details_select(14, 'EE', 'EE_M'); //Activity Event Entity
        $select_array[] = $this->get_entity_details_select(34, 'EFC', 'EFC_M');  //Activity Forum Category


        $GetEntityType = isset($filter['GET_ENTITY_TYPE']) ? $filter['GET_ENTITY_TYPE'] : 'ACTIVITY';
        if ($GetEntityType != 'ALL') {
            return $select_array;
        }

        $select_array[] = $this->get_user_details_select('UE', 'UE_URL');  //User Entity
        $select_array[] = $this->get_entity_details_select(1, 'GE_PRL');  // Activity Group Entity
        $select_array[] = $this->get_entity_details_select(18, 'PE_PRL', '');  //Activity Page Entity
        $select_array[] = $this->get_entity_details_select(14, 'EE_PRL', 'EE_M_PRL');   //Activity Event Entity
        $select_array[] = $this->get_entity_details_select(34, 'EFC_PRL', 'EFC_M_PRL');  //Activity Forum Category


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
        
        
        
        //Exclude disabled modules        
        $this->db->where_not_in('UAL.ModuleID', $this->exclude_modules);
        
        
        
        if($is_promoted !== NULL) {
            $this->db->where_in('AATC.IsPromoted', $is_promoted);
        }
        
        $time_zone = $this->user_model->get_user_time_zone();

        if ($city_id || $age_group_id) {
            $this->db->join(ANALYTICLOGINS . ' AL ', 'AL.AnalyticLoginID = UAL.AnalyticLoginID', 'left');
            if ($city_id)
                $this->db->where('AL.CityID', $city_id);

            if ($age_group_id)
                $this->db->where('AL.AgeGroupID', $age_group_id);
        }

        //1- Normal Tag, 2- Hash Tag, 3- Activity Mood, 4- Activity Classification, 5- User/Reader Tag, 6- User Profession, 7- Brand
        if ($tag_type) {
            $this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID = AATC.ActivityID AND ET.EntityType = "ACTIVITY"  AND ET.StatusID = 2', 'left');
            $this->db->join(TAGS . ' TG', 'TG.TagID = ET.TagID', 'left');

            $this->db->where('TG.TagType', $tag_type);
        }

        if ($user_id) {
            if(is_array($user_id)) {
                $this->db->where_in('UAL.UserID', $user_id);
            } else {
                $this->db->where('UAL.UserID', $user_id);
            }
        }

        if ($gender) {
            $gender = ($gender == 3) ? 0 : $gender;
            $this->db->where('USUB.Gender', $gender);
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
        }
        
        if($is_verified != 2) {
            $this->db->where('AATC.Verified', $is_verified);
        }

        if (!empty($search_key)) {
            $search_where = '(USUB.FirstName LIKE "%' . $search_key .
                    '%" OR USUB.LastName LIKE "%' . $search_key .
                    '%" OR CONCAT(USUB.FirstName," ",USUB.LastName) LIKE "%' . $search_key .
                    '%" OR AATC.PostContent LIKE "%' . $search_key .
                    '%" OR AATC.PostTitle LIKE "%' . $search_key .
                    '%" OR AATC.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key
                    . '%"))';
            $this->db->where($search_where, NULL, FALSE);
        }

        if ($start_date) {
            $start_date = date('Y-m-d', strtotime($start_date));    
            $this->db->where("DATE_FORMAT(CONVERT_TZ(UAL.ActivityDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }

        if ($end_date) {
            $end_date = date('Y-m-d', strtotime($end_date)); 
            $this->db->where("DATE_FORMAT(CONVERT_TZ(UAL.ActivityDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        if ($tags) {
            $this->db->where("AATC.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" . implode(',', $tags) . "))", null, false);
        }
    }
    
    

    protected function apply_sort_order($filter) {
        $feed_sort_by = isset($filter['FeedSortBy']) ? $filter['FeedSortBy'] : 2;

        if ($filter['FeedSortBy'] == 5) {
            $this->db->order_by('UAL.ID', 'DESC');
            return;
        }

        if ($feed_sort_by == 2) {
            $this->db->order_by('AATC.ActivityID', 'DESC');
        } else if ($feed_sort_by == 3) {
            $this->db->order_by('(AATC.NoOfComments+AATC.NoOfLikes+AATC.NoOfViews)', 'DESC');
        } else { // $feed_sort_by == 1
            $this->db->order_by('AATC.ModifiedDate', 'DESC');
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
            $this->db->order_by('AATC.ModifiedDate', 'DESC');
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
                'ID' => $entity['ID'],
                'ModuleID' => $entity['UAL_ModuleID'],
                'ModuleEntityID' => $entity['UAL_ModuleEntityID'],
                'ActivityTypeID' => $entity['UAL_ActivityTypeID'],
                'ActivityDate' => $entity['UAL_ActivityDate'],
                'ActivityData' => urldecode($entity['UAL_ActivityData']),
            );

            // Subject user who have done action
            $new_entity['subject_user'] = $this->get_select_fields_user_details($entity, 'USUB', 'USUB_URL');

            $new_entity['activity'] = $this->get_select_fields_activity_details($entity, 'AATC', TRUE);
            $new_entity['activity_user'] = $this->get_select_fields_user_details($entity, 'AATCU', 'AATCU_URL');

            $new_entity['parent_activity'] = $this->get_select_fields_activity_details($entity, 'PA');
            $new_entity['parent_activity_user'] = $this->get_select_fields_user_details($entity, 'PAU', 'PAU_URL');

            $new_entity['comment_details'] = $this->get_select_fields_comment_details($entity, 'PCE');
            $new_entity['parent_comment_details'] = $this->get_select_fields_comment_details($entity, 'PCE_P');
            $new_entity['parent_comment_user'] = $this->get_select_fields_user_details($entity, 'PCE_P_U', 'PCE_P_U_URL');




            if ($GetEntityType == 'ALL') {
                $new_entity['user_profile'] = $this->get_select_fields_user_details($entity, 'UE', 'UE_URL');
                $new_entity['group_profile'] = $this->get_select_fields_entity_details($entity, 'GE_PRL', '', FALSE, $entity['UAL_ModuleEntityID']);
                $new_entity['page_profile'] = $this->get_select_fields_entity_details($entity, 'PE_PRL', '', FALSE);
                $new_entity['event_profile'] = $this->get_select_fields_entity_details($entity, 'EE_PRL', 'EE_M_PRL', FALSE);
                $new_entity['forum_cat_profile'] = $this->get_select_fields_entity_details($entity, 'EFC_PRL', 'EFC_M_PRL', FALSE);
            }

            $new_entity['PollData'] = $this->get_poll_data($entity);
            $new_entity['RatingData'] = $this->get_rating_data($entity);
            
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
        return "$pefix_tbl.ActivityID {$pefix_tbl}_ActivityID, $pefix_tbl.PostContent {$pefix_tbl}_PostContent, $pefix_tbl.ActivityTypeID {$pefix_tbl}_ActivityTypeID, "
                . "$pefix_tbl.NoOfLikes {$pefix_tbl}_NoOfLikes, $pefix_tbl.NoOfComments {$pefix_tbl}_NoOfComments, $pefix_tbl.CreatedDate {$pefix_tbl}_CreatedDate, "
                . "$pefix_tbl.IsVisible {$pefix_tbl}_IsVisible, $pefix_tbl.PostType {$pefix_tbl}_PostType, $pefix_tbl.PostTitle {$pefix_tbl}_PostTitle, "
                . "$pefix_tbl.ModuleID {$pefix_tbl}_ModuleID, $pefix_tbl.ModuleEntityID {$pefix_tbl}_ModuleEntityID , $pefix_tbl.UserID {$pefix_tbl}_UserID"
                . ", $pefix_tbl.IsFileExists {$pefix_tbl}_IsFileExists , $pefix_tbl.Params {$pefix_tbl}_Params, $pefix_tbl.ActivityGUID {$pefix_tbl}_ActivityGUID"
                . ", $pefix_tbl.PostAsModuleID {$pefix_tbl}_PostAsModuleID , $pefix_tbl.PostAsModuleEntityID {$pefix_tbl}_PostAsModuleEntityID "
                . ", $pefix_tbl.ParentActivityID {$pefix_tbl}_ParentActivityID, $pefix_tbl.IsPromoted {$pefix_tbl}_IsPromoted "
                . ", $pefix_tbl.IsFeatured {$pefix_tbl}_IsFeatured, $pefix_tbl.IsAdminFeatured {$pefix_tbl}_IsAdminFeatured, $pefix_tbl.Verified {$pefix_tbl}_Verified "
                . ", $pefix_tbl.IsShowOnNewsFeed {$pefix_tbl}_IsShowOnNewsFeed"; 
    }

    protected function get_comment_details_select($pefix_tbl) {
        return "$pefix_tbl.NoOfLikes {$pefix_tbl}_NoOfLikes, $pefix_tbl.NoOfReplies {$pefix_tbl}_NoOfReplies, $pefix_tbl.PostComment {$pefix_tbl}_PostComment, "
                . "$pefix_tbl.PostCommentID {$pefix_tbl}_PostCommentID, $pefix_tbl.CreatedDate {$pefix_tbl}_CreatedDate, $pefix_tbl.IsMediaExists {$pefix_tbl}_IsMediaExists, "
                . "$pefix_tbl.IsFileExists {$pefix_tbl}_IsFileExists";
    }

    protected function get_entity_details_select($module_id, $prefix_tbl1, $prefix_tbl2 = '') {
        // Group Entity
        if ($module_id == 1) {
            return "{$prefix_tbl1}.GroupGUID {$prefix_tbl1}_GUID , {$prefix_tbl1}.Type {$prefix_tbl1}_Type,
             {$prefix_tbl1}.GroupName AS {$prefix_tbl1}_Name, 
             {$prefix_tbl1}.GroupImage {$prefix_tbl1}_Image, 
            {$prefix_tbl1}.GroupCoverImage {$prefix_tbl1}_CoverImage, '' AS {$prefix_tbl1}_ProfileURL, 'GROUP' AS {$prefix_tbl1}_EntityType";
            //$select_array[] = "GE.GroupID GE_GroupID , GE.GroupName GE_Name, GE.MemberCount GE_MemberCount, GE.GroupImage GE_Image, GE.GroupCoverImage GE_CoverImage"; 
            //$select_array[] = "GE.GroupDescription GE_Description, GE.IsPublic GE_IsPublic";
        }

        // User Entity
        if ($module_id == 3) {
            return "{$prefix_tbl1}.UserGUID {$prefix_tbl1}_GUID , CONCAT($prefix_tbl1.FirstName, ' ', $prefix_tbl1.LastName) AS {$prefix_tbl1}_Name,
             $prefix_tbl1.ProfilePicture {$prefix_tbl1}_Image, '' AS {$prefix_tbl1}_CoverImage, $prefix_tbl2.Url AS {$prefix_tbl1}_ProfileURL, 'USER' AS {$prefix_tbl1}_EntityType";
        }

        // Page Entity
        if ($module_id == 18) {
            return "{$prefix_tbl1}.PageGUID {$prefix_tbl1}_GUID, {$prefix_tbl1}.Title {$prefix_tbl1}_Name, {$prefix_tbl1}.ProfilePicture {$prefix_tbl1}_Image, 
            {$prefix_tbl1}.CoverPicture {$prefix_tbl1}_CoverImage, '' AS {$prefix_tbl1}_ProfileURL, 'PAGE' AS {$prefix_tbl1}_EntityType";

            //$select_array[] = "PE.PageID PE_PageID, PE.Name PE_Name, PE.Description PE_Description, PE.ProfilePicture PE_Picture, PE.CoverPicture PE_CoverPicture";
            //$select_array[] = "PE.NoOfFollowers PE_NoOfFollowers, PE.NoOfLikes PE_NoOfLikes, PE.NoOfPosts PE_NoOfPosts";
        }
        // Event Entity
        if ($module_id == 14) {
            return "{$prefix_tbl1}.EventGUID {$prefix_tbl1}_GUID, {$prefix_tbl1}.Title {$prefix_tbl1}_Name, '' AS {$prefix_tbl1}_Image, 
             '' AS {$prefix_tbl1}_CoverImage, '' AS {$prefix_tbl1}_ProfileURL, 'EVENT' AS {$prefix_tbl1}_EntityType";
            //$select_array[] = "EE.EventID EE_EventID, EE.Title EE_Title, EE.IsFullDay EE_IsFullDay, EE.StartDate EE_StartDate, EE.StartTime EE_StartTime, EE.EndDate EE_EndDate";
            //$select_array[] = "EE.EndTime EE_EndTime, EE.Venue EE_Venue, EE.Description EE_Description, EE.Privacy EE_Privacy";
        }

        if ($module_id == 34) {
            return "{$prefix_tbl1}.ForumCategoryGUID {$prefix_tbl1}_GUID, {$prefix_tbl1}.Name {$prefix_tbl1}_Name, '' AS {$prefix_tbl1}_Image, 
        '' AS {$prefix_tbl1}_CoverImage, '' AS {$prefix_tbl1}_ProfileURL, 'FORUMCATEGORY' AS {$prefix_tbl1}_EntityType";
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
        );

        
        //$activity_detials['Params'] = json_decode($activity_detials['Params']);
        $activity_detials['Album'] = [];
        $activity_detials = $this->get_album_data($activity_detials);
        
        
        

        $activity_detials['Files'] = array();
        if ($activity_detials['IsFileExists']) {
            $activity_detials['Files'] = $this->activity_model->get_activity_files($activity_detials['ActivityID']);
        }

        


        foreach (array('UE1', 'GE', 'PE', 'EE', 'EFC', '') as $entity_prefix) {
            $entity_details = $this->get_select_fields_entity_details($entity, $entity_prefix, '', true, $activity_detials['ModuleEntityID']);
            if (!empty($entity_details) || !$entity_prefix) {
                $activity_detials = array_merge($activity_detials, $entity_details);
                break;
            }
        }


        $edit_post_content = $activity_detials['PostContent'];
        $activity_detials['PostContent'] = $this->activity_model->parse_tag($activity_detials['PostContent'], $activity_detials['ActivityID']);
        $activity_detials['EditPostContent'] = $this->activity_model->parse_tag_edit($edit_post_content, $activity_detials['ActivityID']);
        $activity_detials['PostTitle'] = $this->activity_model->parse_tag($activity_detials['PostTitle'], $activity_detials['ActivityID']);

        $activity_detials['ActivityURL'] = '';
        if (!empty($activity_detials['ActivityID']) && !empty($activity_detials['EntityProfileURL'])) {
            $activity_detials['ActivityURL'] = get_single_post_url($activity_detials);
        }

        return $activity_detials;
    }
    
    protected function get_select_fields_comment_details($entity, $pefix_tbl) {
        $comment_details = array(
            'NoOfLikes' => ($entity[$pefix_tbl . '_NoOfLikes']) ? $entity[$pefix_tbl . '_NoOfLikes'] : '',
            'NoOfReplies' => ($entity[$pefix_tbl . '_NoOfReplies']) ? $entity[$pefix_tbl . '_NoOfReplies'] : '',
            'PostComment' => ($entity[$pefix_tbl . '_PostComment']) ? $entity[$pefix_tbl . '_PostComment'] : '',
            'PostCommentID' => ($entity[$pefix_tbl . '_PostCommentID']) ? $entity[$pefix_tbl . '_PostCommentID'] : '',
            'CreatedDate' => ($entity[$pefix_tbl . '_CreatedDate']) ? $entity[$pefix_tbl . '_CreatedDate'] : '',
            'IsMediaExists' => ($entity[$pefix_tbl . '_IsMediaExists']) ? $entity[$pefix_tbl . '_IsMediaExists'] : '',
            'IsFileExists' => ($entity[$pefix_tbl . '_IsFileExists']) ? $entity[$pefix_tbl . '_IsFileExists'] : '',
        );

        $edit_post_comment = $comment_details['PostComment'];
        $comment_details['PostComment'] = nl2br($comment_details['PostComment'], FALSE);
        $comment_details['EditPostComment'] = $this->activity_model->parse_tag_edit($edit_post_comment);
        $comment_details['PostComment'] = $this->activity_model->parse_tag($comment_details['PostComment']);
        $comment_details['PostComment'] = trim(str_replace('&nbsp;', ' ', $comment_details['PostComment']));

        $comment_details['Media'] = array();
        if ($comment_details['IsMediaExists']) {
            $comment_details['Media'] = $this->activity_model->get_comment_media($comment_details['PostCommentID'], FALSE, TRUE); //get all comment media
            //reverse array for media details page issue
            $comment_details['Media'] = (!empty($comment_details['Media'])) ? array_reverse($comment_details['Media']) : $comment_details['Media'];
        }
        $comment_details['Files'] = array();
        if ($comment_details['IsFileExists']) {
            $comment_details['Files'] = $this->activity_model->get_activity_files($comment_details['PostCommentID']);
        }

        return $comment_details;
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
        
        if ($entity_details['EntityType'] == 'GROUP') {
            if ($entity_details['Type'] == 'INFORMAL') {        
                $user_id = $this->UserID;
                $entity_details['EntityMembersCount'] = $this->group_model->members($module_entity_id, $user_id, TRUE);
                $entity_details['EntityMembers'] = $this->group_model->members($module_entity_id, $user_id);

                $group_member_names = [];
                if (is_array($entity_details['EntityMembers'])) {
                    foreach ($entity_details['EntityMembers'] as $entity_memeber) {
                        $group_member_names[] = $entity_memeber['FirstName'] . ' ' . $entity_memeber['LastName'];
                    }
                }

                $entity_details['EntityName'] = implode(', ', $group_member_names);
                
                
            }
        }

        return $entity_details;
    }
    
    
    protected function get_activity_dependent_entities($entities) {
        $select_array = [];
        // Activity Group Entity
        $select_array[] = $this->get_entity_details_select(1, 'GE');
        // Activity User Entity
        $select_array[] = $this->get_entity_details_select(3, 'UE1', 'UE_URL1');
        //Activity Page Entity
        $select_array[] = $this->get_entity_details_select(18, 'PE');
        //Activity Event Entity
        $select_array[] = $this->get_entity_details_select(14, 'EE', 'EE_M');
        //Activity Forum Category
        $select_array[] = $this->get_entity_details_select(34, 'EFC', 'EFC_M');
        
        $activity_ids = [];
        
        foreach($entities as $entity) {
            $activity_ids[] = $entity['AATC_ActivityID'];
            if($entity['PA_ActivityID']) $activity_ids[] = $entity['PA_ActivityID'];
        }
        
        $this->db->select(implode(',', $select_array), false);
        $this->db->from(ACTIVITY . ' AATC ');
        
        $this->db->join(GROUPS . ' GE ', 'GE.GroupID = AATC.ModuleEntityID  AND AATC.ModuleID = 1 ', 'left');
        
        $this->db->join(PAGES . ' PE ', 'PE.PageID = AATC.ModuleEntityID  AND AATC.ModuleID = 18 ', 'left');

        $this->db->join(EVENTS . ' EE ', 'EE.EventID = AATC.ModuleEntityID  AND AATC.ModuleID = 14', 'left');
        $this->db->join(MEDIA . ' EE_M ', 'EE_M.MediaID = EE.ProfileImageID', 'left');

        $this->db->join(FORUMCATEGORY . ' EFC ', 'EFC.ForumCategoryID = AATC.ModuleEntityID  AND AATC.ModuleID = 34', 'left');
        $this->db->join(MEDIA . ' EFC_M ', 'EFC_M.MediaID = EFC.MediaID', 'left');

        $this->db->join(USERS . ' UE1 ', 'UE1.UserID  = AATC.ModuleEntityID  AND AATC.ModuleID = 3', 'left');
        $this->db->join(PROFILEURL . ' UE_URL1 ', 'UE1.UserID = UE_URL1.EntityID AND UE_URL1.EntityType = "User" ', 'left');
        
        $this->db->where_in('AATC.ActivityID', $activity_ids);
        
        $query = $this->db->get();
        //echo $this->db->last_query(); 
        $entities = $query->result_array();
    }

    

    // Get Activities related data.
    protected function get_poll_data($entity) {
        // If UAL_ActivityTypeID is 25 (Poll Created)
        if (!in_array($entity['UAL_ActivityTypeID'], [25, 33])) {
            return [];
        }
        
        $pollData = [];
        
        $this->load->model(array('polls/polls_model'));
        $optionData = [];
        if ($entity['UAL_ActivityTypeID'] == 25) {
            $params = json_decode($entity['AATC_Params']);
            $poll_id = isset($params->PollID) ? $params->PollID : 0;
        } else {
            $row = $this->db->select('*')->get_where(POLLOPTIONVOTES, array('VoteID' => $entity['UAL_EntityID']))->row_array();
            $poll_id = (int) isset($row['PollID']) ? $row['PollID'] : 0;

            $this->db->select('COUNT(POV.VoteID) AS TotalVotes, PO.Value');
            $this->db->from(POLLOPTIONVOTES . ' POV');
            $this->db->join(POLLOPTION . ' PO', 'PO.OptionID = POV.OptionID', 'left');
            $this->db->where('POV.PollID', $poll_id);

            //$this->db->where('POV.ModuleEntityID', $entity['UAL_ModuleEntityID']);
            //$this->db->where('POV.ModuleID', $entity['UAL_ModuleID']);

            $optionData = $this->db->get()->row_array();
        }



        $pollData['PollData'] = $this->polls_model->get_poll_by_id($poll_id, $entity['UAL_ModuleID'], $entity['UAL_ModuleEntityID']);
        $pollData['MuteAllowed'] = 0;
        $pollData['ShowFlagBTN'] = 0;

        $user_details_invite = $this->polls_model->get_invite_status('3', $entity['UAL_UserID'], $poll_id);
        if ($user_details_invite['TotalInvited'] > 0) {
            $pollData['ShowInviteGraph'] = 1;
        }
        
        $pollData['optionData'] = $optionData;
        
        return $pollData;
    }
    
    protected function get_rating_data($entity) {
        
        if (!in_array($entity['UAL_ActivityTypeID'], [16, 17])) {
            return [];
        }
        
        $this->load->model(array('ratings/rating_model'));
        $params = json_decode($entity['AATC_Params']);
        $RatingID = isset($params->RatingID) ? $params->RatingID : 0;
        
        return $this->rating_model->get_rating_by_id($RatingID, $entity['UAL_UserID']);
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



            /*added by gautam*/
//            if($this->DeviceTypeID!='' && $this->DeviceTypeID!=1)
//            {
//                if(in_array($activity_detials['ActivityTypeID'], array(9,10, 14, 15)))
//                { /*For Mobile */
//                    //$activity_detials['PostContent'] = $this->parse_tag($originalActivity['PostContent']);
//                    $activity_detials['EntityTagged'] = $this->get_tagged_entity($activity_detials['ParentActivityID']);
//                    $activity_detials['Links'] = $this->get_activity_links($activity_detials['ParentActivityID']); 
//                    $activity_detials['SharePostContent'] = $this->parse_tag($activity_detials['PostContent']);
//                    $activity_detials['ShareEntityTagged'] = $this->get_tagged_entity($activity_detials['ActivityID']);                      
//                }
//             }   
            /*added by gautam end*/


        }
        
        return $activity_detials;
        
    }
    
    
}
