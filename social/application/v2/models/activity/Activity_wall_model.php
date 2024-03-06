<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_wall_model extends Common_Model {

    protected $blocked_users = array();

    public function __construct() {
        parent::__construct();
        $this->load->model(array('activity/activity_model', 'activity/activity_result_filter_model'));
    }

    /**
     * get activity query
     */
    public function get_activity_query(
            $entity_id, $module_id, $page_no, $page_size, $current_user, $feed_sort_by, $filter_type, $is_media_exists, 
            $activity_guid, $search_key, $start_date, $end_date, $feed_user, $as_owner, $count_only, $field, 
            $activity_type_filter, $m_entity_id, $entity_module_id, $comment_id,$view_entity_tags,$role_id,$post_type,$tags, $extra_params
    ) {

        if($module_id == 1) {
            $exclude_ids = $this->activity_model->get_announcements($module_id, $entity_id,$current_user,true);
            //$exclude_ids = false;
        } else {
            $exclude_ids = false;
        }
        if ($activity_guid) {
            $exclude_ids = false;
        }

        $sticky_ids = array();//$this->activity_model->get_user_sticky_posts($current_user,$module_id,$entity_id);

        $e_module_id = $module_id;
        $e_entity_id = $entity_id;
        $time_zone = $this->user_model->get_user_time_zone();
        
        $group_list = array();//$this->group_model->get_user_group_list();
        $group_list[] = 0;
        $group_list_new = $group_list;
        $group_list = implode(',', $group_list);
        $page_list = [];
        $event_list_current_user = [];
        $event_list = [];
        $forum_list = [];
        $permission = false;
        
        if ($module_id == 3) {
            $page_list = $this->page_model->get_liked_pages_list($entity_id);
            $event_list_current_user = array();//$this->event_model->get_all_joined_events($current_user, true);
            $event_list_current_user[] = 0;
            $event_list = $event_list_current_user;
            $forum_list = array();//$this->forum_model->get_forum_list($entity_id);
            $forum_list[] = 0;

            if ($current_user != $entity_id) {
                
                if($this->settings_model->isDisabled(1)) { // Check if group module is disabled     
                    $group_list_temp[] = 0;
                    $group_list_new[] = 0;
                } else {
                    $group_list_temp = array();//$this->group_model->get_users_groups($entity_id);
                    $group_list_temp[] = 0;
                    $group_list_new = array_intersect($group_list_new, $group_list_temp);

                    /* $this->db->select('GroupID');
                    $this->db->from(GROUPS);
                    $this->db->where('IsPublic', 1);
                    $this->db->where_in('GroupID', $group_list_temp);
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        foreach ($query->result_array() as $key => $value) {
                            $group_list_new[] = $value['GroupID'];
                        }
                    } */
                }
                
                

                $event_list_view_user = array();//$this->event_model->get_all_joined_events($entity_id, true);
                $event_list_view_user[] = 0;
                $event_list = array_intersect($event_list_current_user, $event_list_view_user);
            }
        }
        
        $blocked_users = $this->blocked_users;

        $condition = '';
        if ($module_id == 3) {
            $permission = $this->privacy_model->check_privacy($current_user, $entity_id, 'default_post_privacy');
        }

        if ($module_id == 3) {
            $is_relation = array(1, 2, 3, 4); //$this->activity_model->isRelation($entity_id, $current_user, true); // Visibility
        } else {
            $is_relation = array(1, 2, 3, 4);
        }
        
        $activity_type_allow = $this->activity_model->get_allowed_activity_type('wall',$module_id);

        // To show poll details
        if($activity_guid) {
            $activity_type_allow[] = 25;
        }
        
        $friend_followers_list = array();//$this->user_model->get_friend_followers_list();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        $friends[] = 0;
        $follow[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($current_user, $friend_followers_list)) {
            $friend_followers_list[] = $current_user;
        }
        $friend_followers_list = implode(',', $friend_followers_list);

        if (!in_array($current_user, $follow)) {
            $follow[] = $current_user;
        }

        if (!in_array($current_user, $friends)) {
            $friends[] = $current_user;
        }

        // Check parent activity privacy for shared activity

        $privacy_condition = "
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                CASE
                    WHEN A.ActivityTypeID IN(9,10) 
                        THEN
                            A.ParentActivityID=(
                            SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                            IF(Privacy=2 AND ActivityTypeID!=7,UserID IN (" . $friend_followers_list . "),false) OR
                            IF(Privacy=3 AND ActivityTypeID!=7,UserID IN (" . implode(',', $friends) . "),false) OR
                            IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $current_user . "',false) OR
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $group_list . "),false))
                    )
                    WHEN A.ActivityTypeID IN(14,15)
                        THEN
                           A.ParentActivityID=(
                        SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                        )
                ELSE
                '' 
                END 
                                
            ),         
        true)";

        
        //$result = $this->db->get();
        // echo $this->db->last_query();die;
        
        return $this->build_query(
            $exclude_ids, $condition, $privacy_condition, $module_id, $permission, $entity_id, $current_user, $feed_user, $as_owner, $blocked_users,
            $group_list_new, $page_list, $event_list, $forum_list, $activity_guid, $is_relation, $activity_type_allow, $filter_type, $is_media_exists, 
            $search_key, $start_date, $time_zone, $end_date, $post_type, $tags, $sticky_ids, $feed_sort_by, $count_only, $page_no, $page_size, $field, $extra_params
        );
        
    }
    
    
    /**
     * build activity query
     */
    public function build_query(
            $exclude_ids, $condition, $privacy_condition, $module_id, $permission, $entity_id, $current_user, $feed_user, $as_owner, $blocked_users,
            $group_list_new, $page_list, $event_list, $forum_list, $activity_guid, $is_relation, $activity_type_allow, $filter_type, $is_media_exists, 
            $search_key, $start_date, $time_zone, $end_date, $post_type, $tags, $sticky_ids, $feed_sort_by, $count_only, $page_no, $page_size, $field, $extra_params
    ) {
        
        $isPromoted = 0;
        if(isset($extra_params['IsPromoted']) && $extra_params['IsPromoted'] == 1 ) {
            $is_admin = $this->user_model->is_super_admin($current_user);
            if($is_admin) 
            $isPromoted = 1;
        } 
        
        
        if ($field == 'ALL') {
            $this->db->select(
                'A.*,ATY.ViewTemplate,ATY.Template,ATY.LikeAllowed,ATY.CommentsAllowed,ATY.ActivityType,ATY.ActivityTypeID,'
                . 'ATY.FlagAllowed,ATY.ShareAllowed,ATY.FavouriteAllowed,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture'
            );
        } else {
            $this->db->select($field);
        }

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        
        //echo $filter_type;
        
        $this->activity_conditions(
            $exclude_ids, $condition, $privacy_condition, $module_id, $permission, $entity_id, $current_user, $feed_user, $as_owner, $blocked_users,
            $group_list_new, $page_list, $event_list, $forum_list, $activity_guid, $is_relation, $activity_type_allow, $extra_params
        );
        
        $this->apply_filter(
            $filter_type, $current_user, $activity_guid, $is_media_exists, $search_key,
            $start_date, $time_zone, $end_date, $post_type, $tags, $isPromoted, $extra_params
        );
        
        $this->sort_conditions($filter_type, $sticky_ids, $feed_sort_by, $count_only, $module_id);
        
        
        if (!$count_only && !empty($page_size)) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query;  echo '==============================================';
        $this->db->reset_query();
        return $compiled_query; 
    }

    /**
     * Apply conditions on the activity query
     */
    public function activity_conditions(
            $exclude_ids, $condition, $privacy_condition, $module_id, $permission, $entity_id, $current_user, $feed_user, $as_owner, $blocked_users,
            $group_list_new, $page_list, $event_list, $forum_list, $activity_guid, $is_relation, $activity_type_allow, $extra_params
    ) {

        if ($exclude_ids) {
            $this->db->where_not_in('A.ActivityID', explode(',', $exclude_ids));
        }
        
        if ($condition) {
            $this->db->where($condition, null, false);
        }
        
        if(empty($extra_params['EntityGUID']) && $module_id == 34) {
            $forum_category_exclude_cnds = 
                    "A.ModuleEntityID NOT IN ".
                    
                    "(Select ForumCategoryID From ". 
                    FORUMCATEGORY . " FC INNER JOIN " . FORUM . " F "
                    . " ON F.ForumID = FC.ForumID"
                    . " WHERE F.Visible = 0 )"
                    ;
            
            
            $this->db->where($forum_category_exclude_cnds, null, false);
        }
        
        if ($privacy_condition) {

            if ($module_id == 3) {
                if (!$permission) {
                    $permission_cond = "IF(A.UserID IN(" . $entity_id . "," . $current_user . "),TRUE,FALSE)";
                } else {
                    $permission_cond = "IF(TRUE,TRUE,FALSE)";
                }
                $pc = "(
                        IF(A.UserID=A.ModuleEntityID," . $privacy_condition . "," . $permission_cond . ")
                    )";
                $this->db->where($pc, null, false);
            } else {
                $this->db->where($privacy_condition, null, false);
            }
        }
        
        if ($feed_user)
        {
            if(is_array($feed_user))
            {
                $this->db->where_in('U.UserID', $feed_user);
            }
            else
            {
                $this->db->where('U.UserID', $feed_user);
            }
            $this->db->where('A.ModuleEntityOwner', '0');
        }

        if ($as_owner)
        {
            $this->db->where('A.ModuleEntityOwner', '1');
        }
        
        if (!empty($blocked_users) && empty($feed_user))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }

        //$this->db->where('A.StatusID','2');
        $this->db->where('ATY.StatusID', '2');
        if(empty($activity_guid)) {
           // $this->db->where('A.LocalityID', $this->LocalityID);
        }
        
        $entity_id = $this->db->escape_str($entity_id);
        $module_id = $this->db->escape_str($module_id);
        
        $mention_entity_id = $entity_id;
        
        if($module_id == '34' && !$entity_id)
        {
            $mention_condition = "(A.ModuleID='".$module_id."'";
        }
        else
        {
            if ($group_list_new)
            {
                $mention_condition = "
                    ((A.ModuleID=" . $module_id . " AND A.ModuleEntityID=" . $mention_entity_id . ") OR (A.ActivityID IN(SELECT SM.ActivityID FROM Mention SM LEFT JOIN Activity SA ON SM.ActivityID=SA.ActivityID WHERE SM.StatusID = 2 AND SM.ModuleID=" . $module_id . " AND SM.ModuleEntityID=" . $mention_entity_id . " AND IF(SA.ModuleID=1,SA.ModuleEntityID IN(" . implode(',', $group_list_new) . "),true)))";
            } else
            {
                 $mention_condition = "
                    ((A.ModuleID=" . $module_id . " AND A.ModuleEntityID=" . $mention_entity_id . ") OR (A.ActivityID IN(SELECT SM.ActivityID FROM Mention SM LEFT JOIN Activity SA ON SM.ActivityID=SA.ActivityID WHERE SM.StatusID = 2 AND SM.ModuleID=" . $module_id . " AND SM.ModuleEntityID=" . $mention_entity_id . " ))";
            }
        }

        if ($module_id == 3)
        {
            if (!empty($page_list))
            {
                $mention_condition.= " OR IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
          A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ") AND A.UserID=" . $mention_entity_id . "
          ), '' )";
            }

            if (!empty($group_list_new))
            {                
                $mention_condition .= " OR IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
          A.ModuleID=1 AND A.ModuleEntityID IN(" . implode(',', $group_list_new) . ") AND A.UserID=" . $mention_entity_id . "
          ), '' )";
            }

            if (!empty($event_list))
            {
                $event_list = implode(',', $event_list);
                $mention_condition .= " OR IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
          A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ") AND A.UserID=" . $mention_entity_id . "
          ), '' )";
            }

            if (!empty($forum_list))
            {
                $forum_list = implode(',', $forum_list);
                $mention_condition .= " OR IF(A.ActivityTypeID=26 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=26 AND A.ModuleID=34), (
          A.ModuleID=34 AND A.ModuleEntityID IN(" . $forum_list . ") AND A.UserID=" . $mention_entity_id . "
          ), '' )";
            }

            /*if (!empty($event_list))
            {
                $event_list = implode(',', $event_list);
                $mention_condition .= " OR IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
          A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }*/
        }

        $mention_condition .= ") ";
        if ($activity_guid)
        {
            $this->db->where('ActivityGUID', $activity_guid);
        }
        else
        {
            $this->db->where($mention_condition, null, false);
        }
        
        
        $this->db->where("(A.UserID='" . $current_user . "' OR A.Privacy IN(" . implode(',', $is_relation) . ") OR (SELECT ActivityID FROM " . MENTION . " WHERE ModuleID='3' AND StatusID = 2 AND ModuleEntityID='" . $current_user . "' AND ActivityID=A.ActivityID LIMIT 1) is not null)", NULL, FALSE);
        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        $this->db->where_not_in('U.StatusID', array(3, 4));
        
        $exclude_activity_types = [16,17];
        // To show poll details
        if(!$activity_guid) {
            $exclude_activity_types[] = 25;
        }
        
        
        $this->db->where_not_in('A.ActivityTypeID', $exclude_activity_types);
        
    }

    /**
     * Apply filter conditions on the activity query
     */
    public function apply_filter(
            $filter_type, $current_user, $activity_guid, $is_media_exists, $search_key,
            $start_date, $time_zone, $end_date, $post_type, $tags, $isPromoted, $extra_params
    ) {
        
        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            
            if(empty($extra_params['onlyFeedQuery'])) {
                $this->db->select("R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus", FALSE);
                $this->db->select("IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder", false);
            }
            
            //$this->db->select("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Asia/Calcutta','Etc/UTC'),'%Y-%m-%d') as ReminderDate",FALSE);

            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $current_user . "'";
            if ($filter_type == 3)
            {
                $jointype = 'join';
                $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $current_user . "'";
            } else
            {
                if (!$activity_guid)
                {
                    $this->db->where("(R.Status IS NULL OR R.Status='ACTIVE')");
                }
            }

            $this->db->join(REMINDER . " R", $joincondition, $jointype);
            $this->db->_protect_identifiers = TRUE;
        }
        
        if (($filter_type == 1 || $filter_type === 'Favourite') && $filter_type != '0') {
            $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"', 'JOIN');
            $this->db->where('F.UserID', $current_user);
            $this->db->where('F.StatusID', '2');
        } else if ($filter_type == 2) {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            //$this->db->where('F.UserID',$current_user);
            $this->db->where('F.StatusID', '2');
            $this->db->where('A.Flaggable!=0', null, false);
            $this->db->group_by('F.EntityID');
        }

        if ($filter_type == 7) {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $current_user);
        } else if ($filter_type == 4 && !$this->settings_model->isDisabled(43)) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $current_user . "'", "join");
            $this->db->_protect_identifiers = TRUE;
        } else if ($filter_type == 10) {
            $this->db->where('A.StatusID', '10');
            $this->db->where('A.UserID', $current_user);
        } else if ($filter_type == 11) {
            $this->db->where('A.IsFeatured', '1');
            $this->db->where('A.StatusID', '2');
        } else {
            $this->db->where("IF(A.UserID='" . $current_user . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        }

        if (!$activity_guid && $filter_type != 4 && !$this->settings_model->isDisabled(43)) {
            $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $current_user . "')", NULL, FALSE);
        }

        if ($is_media_exists == 1 || $is_media_exists == 0) {
            $this->db->where('A.IsMediaExist', $is_media_exists);
        }
        
        if ($search_key) {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }

        if ($start_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        if ($post_type) {
            if (is_array($post_type)) {
                $this->db->where_in('A.PostType', $post_type);
            } else {
                $this->db->where('A.PostType', $post_type);
            }
        }

        if ($tags) {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" . implode(',', $tags) . "))", null, false);
        }
        
        if($isPromoted) {
            $this->db->where('A.IsPromoted', 1);
        } 
    }

    /**
     * Apply sort conditions on the activity query
     */
    public function sort_conditions($filter_type, $sticky_ids, $feed_sort_by, $count_only, $module_id) {
        
        if($count_only) {
            return;
        }
        
        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");            
        }
        
        if($module_id == 34) {
            if($sticky_ids) {
                $this->db->_protect_identifiers = false;
                $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $sticky_ids) . ') DESC');
                $this->db->_protect_identifiers = true;
            }


            $this->db->order_by('A.StickyDate', 'DESC');
        }

        if ($feed_sort_by == 2) {
            $this->db->order_by('A.ActivityID', 'DESC');
        } else if ($feed_sort_by == 3) {
            $this->db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfViews)', 'DESC');
        } else if ($feed_sort_by == 4){
            /*featured,popualrthenrecent*/
            $this->db->order_by('A.IsFeatured DESC,(A.NoOfComments+A.NoOfLikes+A.NoOfViews) DESC,A.CreatedDate DESC');
        }else {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }
    }
    
    /**
     * [getActivities Get the activity for wall]
     * @param  [int]       $entity_id      [Module Entity ID]
     * @param  [int]       $module_id      [Module ID]
     * @param  [int]       $page_no        [Page No]
     * @param  [int]       $page_size      [Page Size]
     * @param  [int]       $current_user   [Current User ID]
     * @param  [int]       $feed_sort_by    [Sort By value]
     * @param  [int]       $filter_type    [Post Filter Type ]
     * @param  [int]       $is_media_exists [Is Media Exists]
     * @param  [string]    $activity_guid  [Activity GUID]
     * @param  [string]    $search_key     [Search Keyword]
     * @param  [string]    $start_date     [Start Date]
     * @param  [string]    $end_date       [End Date]
     * @param  [int]       $feed_user      [POST only of this user]
     * @return [Array]                    [Activity array]
     */
    public function get_activities(
            $entity_id, $module_id, $page_no, $page_size, $current_user, $feed_sort_by, $filter_type = 0, $is_media_exists = 2, 
            $activity_guid, $search_key, $start_date, $end_date, $feed_user = 0, $as_owner = 0, $count_only = false, $field = 'ALL', 
            $activity_type_filter = array(), $m_entity_id = '', $entity_module_id = '', $comment_id = '',$view_entity_tags='',
            $role_id = 2, $post_type = 0, $tags = '', $extra_params = []
    ) {
        $is_single_activity = 0;
        if($activity_guid!='' && $activity_guid!='0')
        {
            $is_single_activity = 1;
        }

        if ($count_only) {
            if(!empty($extra_params['onlyFeedQuery'])) {
                $field = 'A.ActivityID';
            } else {
                $field = 'COUNT(A.ActivityID) AS TotalRow';
            }
        }

        $activity_query = $this->get_activity_query(
            $entity_id, $module_id, $page_no, $page_size, $current_user, $feed_sort_by, $filter_type, $is_media_exists, 
            $activity_guid, $search_key, $start_date, $end_date, $feed_user, $as_owner, $count_only, $field, 
            $activity_type_filter, $m_entity_id, $entity_module_id, $comment_id,$view_entity_tags,$role_id,$post_type,$tags, $extra_params
        );
        
        
        if($count_only && !empty($extra_params['onlyFeedQuery'])) {
            return $activity_query;
        }
        
        $result = $this->db->query($activity_query);
        $activity_result = $result->result_array();  //print_r(count($feed_result)); die;


        if ($count_only) {
            $total_count = 0;
            foreach ($activity_result as $total_row) {
                //echo $final_feed_query; echo '==============================================================';
                $total_count += (int)!empty($total_row['TotalRow']) ? $total_row['TotalRow'] : 0;
            }
            return $total_count;
        }

        $this->show_suggestions = FALSE;
        $result = $this->activity_result_filter_model->filter_result_set(
                $activity_result, $page_no, $current_user, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id, $this,$is_single_activity,$module_id
        );

        return $result;
    }

}
