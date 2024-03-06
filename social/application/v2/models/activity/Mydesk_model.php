<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Mydesk_model extends Common_Model {

    protected $blocked_users = array();
    protected $tagged_data = array();
    protected $user_activity_archive = array();

    public function __construct() {
        parent::__construct();
        $this->load->model(array('activity/activity_model'));
    }

    public function activity_recent_participants($activity_id, $user_id) {
        $result['Data'] = array();
        $result['TotalRecords'] = 0;
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS Name,U.UserGUID,PU.Url as ProfileUrl');
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(POSTCOMMENTS . ' PC', 'PC.EntityID=A.ActivityID AND A.StatusID=2');
        $this->db->join(USERS . ' U', 'PC.UserID = U.UserID');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('A.ActivityID', $activity_id);
        if (!empty($friends)) {
            $this->db->where_in('U.UserID', $friends);
        }
        $this->db->group_by('PC.UserID');
        $this->db->order_by('PC.CreatedDate', 'DESC');
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result['TotalRecords'] = $temp_q->num_rows();
        $this->db->limit(4);
        $query = $this->db->get();
        $result['Data'] = $query->result_array();
        return $result;
    }

    protected function mydeskQuery($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), $activity_ids = array(), $view_entity_tags = 1, $role_id = 2, $post_type = 0, $tags = '', $rules = array(), $mydesk_filter = '') {
        $this->load->model(array('polls/polls_model', 'category/category_model', 'forum/forum_model'));
        $this->load->model('sticky/sticky_model');
        $exclude_ids = 0;

        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();

        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $category_list = $this->forum_model->get_user_category_list();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list)) {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list)) {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers) {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        $group_list = $this->group_model->get_user_group_list();
        $category_group_list = $this->group_model->get_user_categoty_group_list();
        $group_list[] = 0;

        $group_list = implode(',', $group_list);
        if (!empty($category_group_list)) {
            $group_list = $group_list . ',' . $category_group_list;
            if ($group_list) {
                $group_list = implode(',', array_unique(explode(',', $group_list)));
            }
        }
        $event_list = $this->event_model->get_user_joined_events();
        //$page_list = $this->page_model->get_user_pages_list();
        $page_list = $this->page_model->get_feed_pages_condition();

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 26, 36, 37, 39); //16, 17, 25,23, 24,
        $modules_allowed = array(1, 14, 18, 3, 30, 34);
        $this->show_suggestions = FALSE;
        $show_media = TRUE;
                
        if ($filter_type == 3 || (isset($mydesk_filter['Reminder']))) {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 26, 30, 36, 37, 39); //25,23, 24,
        }

        /* --Filter by activity type id-- */
        //$activity_ids = array();
        if (!empty($activity_type_filter)) {
            $activity_type_allow = $activity_type_filter;
            $this->show_suggestions = false;

            //7 = My Polls, 8= Expired
            if ($filter_type == 7 || $filter_type == 8) {
                $is_expired = FALSE;
                if ($filter_type == 8) {
                    $is_expired = TRUE;
                }

                $activity_ids = $this->polls_model->my_poll_activities($entity_id, $entity_module_id, $is_expired);
                if (empty($activity_ids)) {
                    return array();
                }
            }
            //My Voted Polls
            if ($filter_type == 9) {
                $activity_ids = $this->polls_model->my_voted_poll_activities($entity_id, $entity_module_id);
                if (empty($activity_ids)) {
                    return array();
                }
            }
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed)) {

            $modules_allowed[] = 1;
        }

        //Activity Type 1 for followers, friends and current user
        //Activity Type 2 for followers and friends only
        //Activity Type 3 for follower and friend of UserID
        //Activity Type 8, 9, 10 for Mutual Friends Only
        //Activity Type 4, 7 for Group Members Only
        $condition = "";
        $condition_part_one = "";
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = "";
        $condition_part_four = "";
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array = array();
        if ($friend_followers_list != '' && empty($activity_ids)) {
            /* $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
              OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)
              THEN
              A.UserID IN(" . $friend_followers_list . ")
              OR A.ModuleEntityID IN(" . $friend_followers_list . ")
              OR " . $condition_part_two . " "; */
            $case_array[] = "A.ActivityTypeID IN (1,5,6,25,23,24,36) AND A.ModuleID=3
                                THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " OR A.ActivityTypeID=36 ";
            $case_array[] = "A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";

            $case_array[] = "A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";

            $case_array[] = "A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";

            $case_array[] = "A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";

            if ($friends) {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow) {
                $privacy_cond2 = "IF(A.Privacy='3',
                    A.UserID IN (" . implode(',', $follow) . "), true
                )";
            }
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
                            IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $user_id . "',false) OR
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

        // /echo $privacy_cond;
        if ($group_list) {

            $case_array[] = " A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if ($category_list) {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (" . implode(',', $category_list) . ") 
                            ";
        }
        if (!empty($page_list)) {
            $case_array[] = "A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        if (!empty($event_list)) {
            $case_array[] = "A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")";
        }
        if (!empty($case_array)) {
            $condition = " ( CASE WHEN " . implode(" WHEN ", $case_array) . " ELSE '' END ) ";
        }
        if (empty($condition)) {
            $condition = $condition_part_two;
        }

        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array = array();
        $select_array[] = 'A.*';
        $select_array[] = 'ATY.ViewTemplate, ATY.Template,  ATY.CommentsAllowed, ATY.LikeAllowed, ATY.ActivityType,';
        //$select_array[]='A.*, ATY.ActivityTypeID, ';
        $select_array[]= 'ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';
        $select_array[] = 'IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        /* $this->db->join(FRIENDS." FR","FR.FriendID=A.ModuleEntityID AND A.ModuleID='3' AND FR.UserID='".$user_id."' AND FR.Status='1'"); */
        $this->db->_protect_identifiers = TRUE;

        /* Join Activity Links Starts */
        $select_array[] = 'IF(URL is NULL,0,1) as IsLinkExists';
        $select_array[] = 'AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';
        $select_array_extra[]="'' ReminderGUID,'' ReminderDateTime,'' ReminderCreatedDate,'' ReminderStatus";
        $select_array_extra[]=" 0 SortByReminder";        

        if(isset($mydesk_filter['Reminder']))
        {
            $select_array_extra = [];
            $select_array_extra[] = "R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array_extra[] = "IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";       
        }

        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        
        /* Join Activity Links Ends */
        
        $this->feed_condition($rules, $exclude_ids, $activity_ids, $condition, $user_id, $privacy_condition);
        
        if (!$this->settings_model->isDisabled(28) && $filter_type != 7 && (!isset($mydesk_filter['Reminder'])))
        {
            $select_array_extra = [];
            $select_array_extra[]="R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array_extra[]="IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";

            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3 )
            {
                $jointype = 'join';
                $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            } else
            {
                if (!$activity_guid)
                {
                    $this->db->where("(R.Status IS NULL OR R.Status='ACTIVE')");
                }
            }
            $this->db->join(REMINDER . " R", $joincondition, $jointype);
            if (!$count_only)
            {
            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            }
            $this->db->_protect_identifiers = TRUE;
        }

        $select_array[] = $select_array_extra[0];
        $select_array[] = $select_array_extra[1];

        $bracket_close = ')';

        if ($mydesk_filter) {
            $this->mydesk_condition($mydesk_filter, $user_id, $count_only);
        } 

        if(isset($mydesk_filter['Mention'])) {
            $this->mentions_condition(3, $user_id);
            $this->db->where('1 )', NULL, FALSE);
            $bracket_close = '';
        }

        $this->apply_filter(
                $filter_type, $friends, $post_type, $user_id, $activity_guid, $activity_ids, $mentions, $modules_allowed, $activity_type_allow, $tags, $feed_user, $show_media, $is_media_exists, $search_key, $start_date, $end_date, $time_zone
        );


        $this->sort_condition($feed_sort_by, $activity_ids);

        if (!empty($blocked_users) && empty($feed_user)) {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        $this->db->where("ATY.StatusID = 2 $bracket_close", NULL, FALSE);

        

        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }



        if ($count_only) {
            $this->db->select('COUNT(DISTINCT A.ActivityID) as TotalRow ');
            //$result = $this->db->get();
            //$count_data = $result->row_array();
            //return $count_data['TotalRow'];
        } else {
            $this->db->select(implode(',', $select_array), false);
            $this->db->group_by('A.ActivityID');
        }


        $compiled_query = $this->db->_compile_select();  //echo $compiled_query;die;

        $this->db->reset_query();

        //$result = $this->db->get();
        // echo $this->db->last_query(); die;

        return $compiled_query;
    }
    
    protected function mentions_condition($module_id, $module_entity_id) {
        
        $module_id = $this->db->escape_str($module_id);
        $module_entity_id = $this->db->escape_str($module_entity_id);
        
        $mentions_conditions = " SELECT MN.ActivityID FROM ".MENTION." MN WHERE MN.StatusID = 2 AND MN.ModuleID = $module_id AND MN.ModuleEntityID = $module_entity_id";
        $this->db->or_where("( A.ActivityID IN ( $mentions_conditions ))", NULL, FALSE);
    }
    
    protected function apply_filter(
    $filter_type, $friends, $post_type, $user_id, $activity_guid, $activity_ids, $mentions, $modules_allowed, $activity_type_allow, $tags, $feed_user, $show_media, $is_media_exists, $search_key, $start_date, $end_date, $time_zone
    ) {        
        if ($post_type) {
            $this->db->where_in('A.PostType', $post_type);
        }

        if ($filter_type == 7) {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } else if ($filter_type == 10) {
            $this->db->where('A.StatusID', '10');
            $this->db->where('A.UserID', $user_id);
        } else if ($filter_type == 11) {
            $this->db->where('A.IsFeatured', '1');
            $this->db->where('A.StatusID', '2');
        } else {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43)) {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } else {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43)) {
                    $this->db->where("NOT EXISTS (SELECT 1 FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND ActivityID=A.ActivityID AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids) {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            if ($mentions) {
                $join_condition = "MN.ActivityID=A.ActivityID AND (";
                foreach ($mentions as $mention) {
                    $join_cond[] = "(MN.ModuleEntityID='" . $mention['ModuleEntityID'] . "' AND MN.ModuleID='" . $mention['ModuleID'] . "')";
                }
                $join_cond = implode(' OR ', $join_cond);
                $join_condition .= $join_cond . ")";

                $this->db->_protect_identifiers = FALSE;
                $this->db->join(MENTION . " MN", $join_condition, "join");
                $this->db->_protect_identifiers = TRUE;
            }

            $this->db->_protect_identifiers = FALSE;
            $this->db->join(MUTESOURCE . ' MS', 'MS.UserID="' . $user_id . '" AND ((MS.ModuleID=A.ModuleID AND MS.ModuleEntityID=A.ModuleEntityID) OR (MS.ModuleID=3 AND MS.ModuleEntityID=A.UserID AND A.ModuleEntityOwner=0))', 'left');
            $this->db->where('MS.ModuleEntityID is NULL', null, false);
            $this->db->_protect_identifiers = TRUE;

            $this->db->where_in('A.ModuleID', $modules_allowed);
            $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
            if ($activity_guid) {
                $this->db->where('A.ActivityGUID', $activity_guid);
            }

            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        }

        if ($tags) {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" . implode(',', $tags) . "))", null, false);
        }

        if ($filter_type == 2) {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
        }
        if ($feed_user) {
            if (is_array($feed_user)) {
                $this->db->where_in('U.UserID', $feed_user);
            } else {
                $this->db->where('U.UserID', $feed_user);
            }
        }

       // echo $show_media.' '.$is_media_exists;
        if (!$show_media) {
            if ($is_media_exists == 2) {
                $is_media_exists = '0';
            }
            if ($is_media_exists == 1) {
                $is_media_exists = '3';
            }
        }

        if ($is_media_exists != 2) {
            $this->db->where('A.IsMediaExist', $is_media_exists);
        }
        //echo $show_media.' '.$is_media_exists; die;

        if (!empty($search_key)) {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }

        if ($start_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }
    }

    protected function feed_condition($rules, $exclude_ids, $activity_ids, $condition, $user_id, $privacy_condition) {
        $rules = [];
        
        
        // Fix for query failure
        $exclude_ids = explode(',', $exclude_ids);
        $exclude_ids[] = 0;
        $exclude_ids = implode(',', $exclude_ids);
        
        if (!empty($rules)) {
            $this->db->or_where_not_in('((A.ActivityID', explode(',', $exclude_ids));
        } else if ($exclude_ids) {
            $this->db->where_not_in(' ( A.ActivityID', explode(',', $exclude_ids));
        }

        if (empty($activity_ids)) {
            if (!empty($condition)) {
                $this->db->where($condition, NULL, FALSE);
            } else {
                $this->db->where('A.ModuleID', '3');
                $this->db->where('A.ModuleEntityID', $user_id);
            }
            if ($privacy_condition) {
                $this->db->where($privacy_condition, null, false);
            }
        }

        if (!empty($rules)) {
            $this->db->where(" 1 )) ", NULL, FALSE);
        }
    }

    protected function mydesk_condition($mydesk_filter, $user_id, $count_only) {
        //Mydesk filter
        if (empty($mydesk_filter)) {
            return;
        }

        if (!empty($mydesk_filter['WatchList'])) {
            $this->db->join(WATCHLIST . ' W', "W.ActivityID=A.ActivityID  and W.UserID=$user_id", 'left');
            $mydesk_condition[] = "W.UserID is not NULL";
            // $this->db->where('F.UserID', $user_id);
            // $this->db->where('F.StatusID', '2');
        }
        if (!empty($mydesk_filter['Favourite'])) {
            $this->db->join(FAVOURITE . ' F', "F.EntityID=A.ActivityID  AND F.EntityType='ACTIVITY' and F.UserID=$user_id and F.StatusID=2", 'left');
            $mydesk_condition[] = "F.UserID is not NULL";
            // $this->db->where('F.UserID', $user_id);
            // $this->db->where('F.StatusID', '2');
        }
        if (!empty($mydesk_filter['Mention'])) {
            //get activities mentioned in 
            /* $this->activities_mentioned_in($user_id);
              $join_condition = "MN.ActivityID=A.ActivityID AND (MN.ModuleEntityID='" . $user_id . "' AND MN.ModuleID=3)";
              $this->db->_protect_identifiers = FALSE;
              $this->db->join(MENTION . " MN", $join_condition, "left");
              $this->db->_protect_identifiers = TRUE; */
            $mydesk_condition[] = "(A.ActivityID IN (SELECT Anew.ActivityID FROM Activity as Anew Left Join `Mention` as M on M.ActivityID=Anew.ActivityID Left Join PostComments as P on P.PostCommentID=M.PostCommentID and P.EntityType='ACTIVITY' WHERE M.ModuleID=3 and M.ModuleEntityID=" . $user_id . " and M.StatusID=2))";
        }
        if (!empty($mydesk_filter['Reminder'])) {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26, 30);// 23, 24,


            $select_array[] = "R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array[] = "IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";

            $this->db->_protect_identifiers = FALSE;
            // $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID and R.UserID=$user_id";

            $this->db->join(REMINDER . " R", $joincondition, "left");
            $mydesk_condition[] = "R.UserID is not NULL";

            if (!$count_only) {

                // IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0)  SortByReminder
                //$this->db->order_by("IF(R.SortByReminder=1, R.ReminderDateTime, '') DESC");
            }
            $this->db->_protect_identifiers = TRUE;
        }
        if (!empty($mydesk_filter['NotifyMe'])) {
            $this->db->join(NOTIFICATIONS . ' NT', "NT.RefrenceID=A.ActivityID and NT.ToUserID=$user_id and NT.NotificationTypeID not in (1,65,125)", 'left');
            //$mydesk_condition[] = "A.ActivityID IN (select NT.RefrenceID From Notifications as NT where NT.ToUserID=$user_id)";
            $mydesk_condition[] = "NT.ToUserID is not NULL";
        }

       // if(isset($mydesk_condition))
       // {
           // $mydesk_inline = implode(' OR ', $mydesk_condition);
           // $mydesk_inline = "($mydesk_inline)";
           // $this->db->where($mydesk_inline);
       // }

        if (isset($mydesk_condition) && count($mydesk_condition) == 1) {
            $mydesk_inline = current($mydesk_condition);
            $mydesk_inline = "($mydesk_inline)";
            $this->db->where($mydesk_inline, null, FALSE);
        }

        //Mydesk QUERY for Tasks Status
        $this->db->join(MYTASKSTATUS . " MTS", "A.ActivityID=MTS.ActivityID and MTS.UserID=$user_id", "left");
        $this->db->where("(MTS.MyTaskID IS null OR `MTS`.`Status`='NOTDONE')");

    }

    protected function sort_condition($feed_sort_by, $activity_ids) {
        if ($feed_sort_by == 2) {
            $this->db->order_by('A.ActivityID', 'DESC');
        } else if ($feed_sort_by == 3) {
            $this->db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfViews)', 'DESC');
        } else {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($feed_sort_by == 'popular') {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } elseif ($feed_sort_by == 1) {
            $this->db->order_by('A.ActivityID', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids)) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } elseif ($feed_sort_by == "General") {
            $this->db->where('A.PostType', 0);
        } elseif ($feed_sort_by == "Question") {
            $this->db->where('A.PostType', 1);
        } elseif ($feed_sort_by == "UnAnswered") {
            $this->db->where('A.PostType', 1);
            $this->db->where('A.NoOfComments', 0);
        } else {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }
    }

    protected function filter_result_set($feed_result, $page_no, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id) {
        $return = array();

        $cnt = 1;
        /*         * ** variables defined starts *** */
        $user_favourite = $this->favourite_model->get_user_favourite();
        // $user_subscribed = $this->subscribe_model->get_user_subscribed();
        //$user_tagged = $this->activity_model->get_user_tagged();
        //print_r($user_tagged);die;
        $user_flagged = $this->flag_model->get_user_flagged();
        $user_archive = $this->activity_model->get_user_activity_archive();
        $this->load->model(array('activity/watchlist_model'));
        /*         * ** variables defined ends *** */
        foreach ($feed_result as $res) {
            $activity = array();
            //Suggested Posts
            if ($cnt == 6 && $page_no == 1 && $this->show_suggestions) {
                $activity['Album'] = array();
                $ViewTemplate = '';
                if ($cnt == 6) {
                    $ViewTemplate = 'UpcomingEvents';
                }
                $activity['ViewTemplate'] = $ViewTemplate;
                $activity['PollData'] = array();
                $return[] = $activity;
            }

            $activity_id = $res['ActivityID'];
            $activity_guid = $res['ActivityGUID'];
            $module_id = $res['ModuleID'];
            $activity_type_id = $res['ActivityTypeID'];
            $module_entity_id = $res['ModuleEntityID'];
            $activity['PostAsModuleID'] = $res['PostAsModuleID'];
            $activity['IsWatchList'] = $this->watchlist_model->is_watchlist($res['ActivityID'],$user_id);            
            
            $activity['IsAnyoneTagged'] = $this->activity_model->is_anyone_mentioned($res['ActivityID']);
                
            if($mydesk_task = $this->is_mydesk_task($res['ActivityID'],$user_id,true))
            {
                $activity['IsTaskDone'] =  (isset($mydesk_task['Status']) && $mydesk_task['Status'] == 'NOTDONE') ? 0 : 1;
            }
            else
            {
                $activity['IsTaskDone'] = 0;
            }
            $activity['ActivityID'] = $res['ActivityID'];
            $activity['StatusID'] = $res['StatusID'];
            $activity['IsFlaggedIcon'] = 0;
            $activity['Viewed'] = 0; /* added by gautam */
            $activity['IsDeleted'] = 0;
            $activity['IsEdited'] = $res['IsEdited'];
            $activity['IsEntityOwner'] = 0;
            $activity['IsOwner'] = 0;
            $activity['IsFlagged'] = 0;
            $activity['CanShowSettings'] = 0;
            $activity['CanRemove'] = 0;
            $activity['CanMakeSticky'] = 0;
            $activity['ShowPrivacy'] = 0;
            $activity['PostAsEntityOwner'] = 0;
            $activity['IsMember'] = 1;
            $activity['IsArchive'] = 0;
            $activity['ShowInviteGraph'] = 0;
            $activity['IsPined'] = ($res['IsVisible'] == '3') ? 1 : 0;
            $activity['OriginalActivityGUID'] = '';
            $activity['OriginalActivityType'] = '';
            $activity['OriginalActivityFirstName'] = '';
            $activity['OriginalActivityLastName'] = '';
            $activity['OriginalActivityUserGUID'] = '';
            $activity['OriginalActivityProfileURL'] = '';
            $activity['OriginalPostType'] = 1;

            $activity['PollData'] = array();
            $activity['Reminder'] = array();
            $activity['ActivityGUID'] = $activity_guid;
            $activity['ModuleID'] = $module_id;
            $activity['UserGUID'] = $res['UserGUID'];
            $activity['ActivityType'] = $res['ActivityType'];
            $activity['NoOfFavourites'] = $res['NoOfFavourites'];
            $activity['IsFeatured'] = $res['IsFeatured'];
            $activity['ShareEntityTagged'] = ''; /* added by gautam */

            $activity['LikeAllowed'] = $res['LikeAllowed'];
            $activity['FlagAllowed'] = $res['FlagAllowed'];
            $activity['ShareAllowed'] = $res['ShareAllowed'];
            $activity['FavouriteAllowed'] = $res['FavouriteAllowed'];
            $activity['NoOfShares'] = $res['NoOfShares'];
            $activity['Message'] = $res['Template'];
            $activity['ViewTemplate'] = $res['ViewTemplate'];
            $activity['CreatedDate'] = $res['CreatedDate'];
            $activity['ModifiedDate'] = $res['ModifiedDate'];
            $activity['IsSticky'] = 0;
            $activity['Visibility'] = $res['Privacy'];
            $activity['PostContent'] = $res['PostContent'];
            $activity['PostTitle'] = $res['PostTitle'];
            $activity['PostType'] = $res['PostType'];
            $activity['ModuleEntityID'] = $res['ModuleEntityID'];
            $activity['ParentActivityID'] = $res['ParentActivityID'];
            $activity['SharedActivityModule'] = '';
            $activity['SharedEntityGUID'] = '';
            $activity['Facts'] = $res['Facts'];

            $res_entity_type = 'Activity';
            $res_entity_id = $activity_id;

            $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
            if (in_array($res['UserID'], $BUsers)) {
                continue;
            }

            if ($filter_type == 7) {
                $activity['IsDeleted'] = 1;
            }

            $activity['IsTagged'] = $this->activity_model->is_tagged($res['ActivityID'], $user_id);

            //Link Variable Assignment Starts                
            $activity['Links'] = $this->activity_model->get_activity_links($res['ActivityID']);
            //Link Variable Assignment Ends

            if (isset($res['ReminderGUID'])) {
                $activity['Reminder'] = array('ReminderGUID' => $res['ReminderGUID'], 'ReminderDateTime' => $res['ReminderDateTime'], 'CreatedDate' => $res['ReminderCreatedDate'], 'Status' => $res['ReminderStatus'], 'Meridian' => '');
            }

            if (in_array($activity_type_id, array(23, 24))) {
                $params = json_decode($res['Params'], true);
                if ($params['MediaGUID']) {
                    $res_entity_id = get_detail_by_guid($params['MediaGUID'], 21);
                    if ($res_entity_id) {
                        $res_entity_type = 'Media';
                        $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count($res_entity_type, $res_entity_id, $BUsers); //$res['NoOfComments'];
                        $activity['NoOfLikes'] = $this->activity_model->get_like_count($res_entity_id, $res_entity_type, $BUsers); //$res['NoOfLikes'];
                        // $activity['NoOfDislikes'] = $this->get_like_count($res_entity_id, $res_entity_type, $BUsers, 3); //$res['NoOfDislikes'];
                        $activity['Album'] = $this->activity_model->get_albums($res_entity_id, $res['UserID'], '', $res_entity_type, 1);
                    }
                }
            } else {
                if (!in_array($activity_type_id, array(5, 6, 9, 10, 14, 15))) {
                    $activity['Album'] = $this->activity_model->get_albums($activity_id, $res['UserID']);
                }
                if ($BUsers) {
                    $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count($res_entity_type, $activity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, $res_entity_type, $BUsers); //
                    //$activity['NoOfDislikes'] = $this->get_like_count($activity_id, $res_entity_type, $BUsers, 3); //
                } else {
                    $activity['NoOfComments'] = $res['NoOfComments']; //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $res['NoOfLikes']; //
                    //$activity['NoOfDislikes'] = $res['NoOfDislikes']; //
                }
            }


            if (isset($user_archive[$activity_id])) {
                $activity['IsArchive'] = $user_archive[$activity_id];
            }

            $activity['CommentsAllowed'] = 0;
            if ($res['IsCommentable'] && $res['CommentsAllowed']) {
                $activity['CommentsAllowed'] = 1;
            }


            $activity['Files'] = array();
            if ($res['IsFileExists']) {
                $activity['Files'] = $this->activity_model->get_activity_files($activity_id);
            }

            $activity['Params'] = json_decode($res['Params']);
            $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
            $activity['IsFavourite'] = (in_array($activity_id, $user_favourite)) ? 1 : 0;

            $activity['Flaggable'] = $res['Flaggable'];
            $activity['FlaggedByAny'] = 0;
            $activity['CanBlock'] = 0;

            if ($res['UserID'] == $user_id) {
                $activity['IsOwner'] = 1;
            }

            $activity['IsFlagged'] = (in_array($activity_id, $user_flagged)) ? 1 : 0;

            if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3) {
                $activity['IsOwner'] = 1;
                $activity['CanRemove'] = 1;
            }

            $activity['EntityName'] = '';
            $activity['EntityProfilePicture'] = '';
            $activity['UserName'] = $res['FirstName'] . ' ' . $res['LastName'];
            $activity['UserProfilePicture'] = $res['ProfilePicture'];
            $activity['UserProfileURL'] = get_entity_url($res['UserID'], 'User', 1);
            $activity['EntityType'] = '';
            $activity['IsExpert'] = 0;

            if ($module_id == 1) {
                $group_details = check_group_permissions($user_id, $module_entity_id);

                if (isset($group_details['Details']) && !empty($group_details['Details'])) {
                    $entity = $group_details['Details'];
                    $activity['EntityProfileURL'] = $module_entity_id;
                    $activity['EntityGUID'] = $entity['GroupGUID'];
                    $activity['EntityName'] = $entity['GroupName'];
                    $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                    $activity['IsExpert']           = $group_details['IsExpert'];
                    $activity['GroupType'] = $entity['Type'];
                    $activity['GroupPrivacy'] = $entity['IsPublic'];
                    if (empty($group_details['CanComment'])) {
                        $activity['CommentsAllowed'] = 0;
                    }

                    if ($entity['Type'] == 'INFORMAL') {
                        $activity['EntityMembersCount'] = $this->group_model->members($module_entity_id, $user_id, TRUE);
                        $activity['EntityMembers'] = $this->group_model->members($module_entity_id, $user_id);
                    }

                    if ($group_details['IsAdmin']) {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                    if ($this->group_model->check_group_creator($module_entity_id, $res['UserID'])) {
                        $activity['CanBlock'] = 0;
                    }
                }
            }
            if ($module_id == 3) {
                $activity['EntityName'] = $activity['UserName'];
                $activity['EntityProfilePicture'] = $activity['UserProfilePicture'];
                $activity['EntityGUID'] = $activity['UserGUID'];

                $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                if ($entity) {
                    $entity['EntityName'] = trim($entity['FirstName'] . ' ' . $entity['LastName']);
                    $activity['EntityName'] = $entity['EntityName'];
                    $activity['EntityGUID'] = $entity['UserGUID'];
                }

                $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                if ($user_id == $module_entity_id) {
                    $activity['IsEntityOwner'] = 1;
                    $activity['CanRemove'] = 1;
                    $activity['CanBlock'] = 1;
                }
            }

            $activity['ShowBTNCommentsAllowed'] = 1;
            $activity['MuteAllowed'] = 1;
            $activity['ShowFlagBTN'] = 1;
            if ($res['ActivityTypeID'] == 16 || $res['ActivityTypeID'] == 17) {
                $params = json_decode($res['Params']);
                $activity['RatingData'] = $this->rating_model->get_rating_by_id($params->RatingID, $user_id);
                $activity['FavouriteAllowed'] = 1;
                $activity['ShareAllowed'] = 1;
                $activity['CommentsAllowed'] = 1;
                $activity['ShowBTNCommentsAllowed'] = 0;
                $activity['MuteAllowed'] = 0;
                $activity['ShowFlagBTN'] = 0;
            } else if ($res['ActivityTypeID'] == 25) {
                $params = json_decode($res['Params']);
                $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $entity_id);

                $activity['MuteAllowed'] = 0;
                $activity['ShowFlagBTN'] = 0;

                $user_details_invite = $this->polls_model->get_invite_status('3', $user_id, $params->PollID);
                if ($user_details_invite['TotalInvited'] > 0) {
                    $activity['ShowInviteGraph'] = 1;
                }
            }

            if ($module_id == 14) {
                $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                if ($entity) {
                    $activity['EntityName'] = $entity['Title'];
                    $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                    $activity['EntityGUID'] = $entity['EventGUID'];
                }

                $activity['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');

                if ($this->event_model->isEventOwner($module_entity_id, $user_id)) {
                    $activity['CanRemove'] = 1;
                    $activity['IsEntityOwner'] = 1;
                    $activity['ShowPrivacy'] = 0;
                    $activity['CanBlock'] = 1;
                }
                if ($this->event_model->isEventOwner($module_entity_id, $res['UserID'])) {
                    $activity['CanBlock'] = 0;
                }
            }
            if ($module_id == 18) {
                $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                if ($entity) {
                    $activity['EntityName'] = $entity['Title'];
                    $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                    $activity['EntityProfileURL'] = $entity['PageURL'];
                    $activity['EntityGUID'] = $entity['PageGUID'];
                    $this->load->model('category/category_model');
                    $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                    $category_icon = $category_name['Icon'];
                    if ($entity['ProfilePicture'] == '') {
                        $activity['EntityProfilePicture'] = $category_icon;
                    }

                    $activity['ModuleEntityOwner'] = $res['ModuleEntityOwner'];
                    if ($res['PostAsModuleID'] == 18) {
                        $PostAs = $this->page_model->get_page_detail_cache($res['PostAsModuleEntityID']);
                        $activity['ModuleEntityOwner'] = 1;
                        $activity['UserName'] = $PostAs['Title'];
                        $activity['UserProfilePicture'] = $PostAs['ProfilePicture'];
                        $activity['UserProfileURL'] = $PostAs['PageURL'];
                        $activity['UserGUID'] = $PostAs['PageGUID'];
                    }

                    if ($res['PostAsModuleEntityID'] != $module_entity_id && $res['ActivityTypeID'] == 12) {
                        $activity['Message'] = $activity['Message'] . ' posted in {{Entity}}';
                    }
                }
                $activity['PostAsEntityOwner'] = $res['ModuleEntityOwner'];
                if ($this->page_model->check_page_owner($user_id, $module_entity_id)) {
                    $activity['CanRemove'] = 1;
                    $activity['IsEntityOwner'] = 1;
                    $activity['CanBlock'] = 1;
                }
                if ($this->page_model->check_page_creator($res['UserID'], $module_entity_id)) {
                    $activity['CanBlock'] = 0;
                }
                if ($res['ModuleEntityOwner'] == 1) {
                    $activity['CanBlock'] = 0;
                }
            }

            if ($module_id == 34) {
                $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                if ($entity) {
                    $activity['EntityName'] = $entity['Name'];
                    $activity['EntityProfilePicture'] = $entity['MediaID'];
                    $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                    $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                }
                $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);

                if ($perm['IsAdmin']) {
                    //$activity['IsOwner']        = 1;
                    $activity['CanRemove'] = 1;
                    $activity['CanMakeSticky'] = 1;
                    $activity['IsEntityOwner'] = 1;
                    $activity['ShowPrivacy'] = 0;
                    $activity['CanBlock'] = 1;
                }
                if ($perm['IsMember']) {
                    $activity['IsMember'] = 1;
                }
                if ($res['ActivityTypeID'] == 26) {
                    $activity['Message'] = $activity['Message'] . ' posted in {{Entity}}';
                }
            }

            if ($res['UserID'] == $user_id) {
                $activity['CanBlock'] = 0;
            }

            if (!isset($activity['EntityProfileURL'])) {
                $activity['EntityProfileURL'] = $activity['UserProfileURL'];
            }

            if ($activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 14 || $activity_type_id == 15) {
                $originalActivity = $this->activity_model->get_activity_details($res['ParentActivityID'], $activity_type_id);
                $activity['ActivityOwner'] = $this->user_model->getUserName($originalActivity['UserID'], $originalActivity['ModuleID'], $originalActivity['ModuleEntityID']);
                $activity['ActivityOwnerLink'] = $activity['ActivityOwner']['ProfileURL'];
                $activity['OriginalPostType'] = $originalActivity['PostType'];
                $activity['ActivityOwner'] = $activity['ActivityOwner']['FirstName'] . ' ' . $activity['ActivityOwner']['LastName'];
                $activity['Album'] = $originalActivity['Album'];
                $activity['Files'] = $originalActivity['Files'];
                $activity['SharePostContent'] = $activity['PostContent'];
                $activity['PostContent'] = $originalActivity['PostContent'];
                $activity['SharedActivityModule'] = $originalActivity['SharedActivityModule'];
                $activity['SharedEntityGUID'] = $originalActivity['SharedEntityGUID'];

                if ($activity_type_id == 10 || $activity_type_id == 15) {
                    if ($originalActivity['ModuleID'] == '1' && $originalActivity['PostType'] == '7') {
                        $activity['Message'] = str_replace("{{OBJECT}}", "{{ACTIVITYOWNER}}", $activity['Message']);
                    } else {
                        if ($originalActivity['UserID'] == $res['UserID']) {
                            $activity['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($originalActivity['UserID']), $activity['Message']);
                        } else {
                            if ($originalActivity['ParentActivityTypeID'] == '11' || $originalActivity['ParentActivityTypeID'] == '7' || $originalActivity['ParentActivityTypeID'] == '26') {
                                $u_d = get_detail_by_id($originalActivity['UserID'], 3, 'FirstName,LastName', 2);
                            }
                        }
                    }
                }
                if ($res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15') {
                    $activity['Album'] = $this->activity_model->get_albums($activity['ParentActivityID'], $res['UserID'], '', 'Media');
                    if (!empty($activity['Album']['AlbumType'])) {
                        $activity['EntityType'] = ucfirst(strtolower($activity['Album']['AlbumType']));
                    } else {
                        $activity['EntityType'] = 'Media';
                    }
                } else {
                    $activity['EntityType'] = 'Post';
                    if ($originalActivity['ParentActivityTypeID'] == 5 || $originalActivity['ParentActivityTypeID'] == 6) {
                        $activity['EntityType'] = 'Album';
                    }
                    if (!empty($originalActivity['Album'])) {
                        $activity['EntityType'] = 'Media';
                    }
                    $activity['OriginalActivityGUID'] = $originalActivity['ActivityGUID'];
                    $activity['OriginalActivityType'] = $originalActivity['ActivityType'];
                    $activity['OriginalActivityFirstName'] = $originalActivity['ActivityOwnerFirstName'];
                    $activity['OriginalActivityLastName'] = $originalActivity['ActivityOwnerLastName'];
                    $activity['OriginalActivityUserGUID'] = $originalActivity['ActivityOwnerUserGUID'];
                    $activity['OriginalActivityProfileURL'] = $originalActivity['ActivityOwnerProfileURL'];
                }
                if (isset($originalActivity['ParentActivityTypeID']) && $originalActivity['ParentActivityTypeID'] == 25) {
                    $params = json_decode($originalActivity['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $entity_id);


                    $user_details_invite = $this->polls_model->get_invite_status('3', $user_id, $params->PollID);
                    if ($user_details_invite['TotalInvited'] > 0) {
                        $activity['ShowInviteGraph'] = 1;
                    }
                }
            }

            if ($activity['IsOwner'] == 1) {
                $activity['IsLike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
                $activity['IsDislike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID'], 3);
            } else {
                $activity['IsLike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, 3, $user_id);
                $activity['IsDislike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, 3, $user_id, 3);
            }

            $log_type = 'Activity';
            $log_id = $activity_id;

            if ($activity_type_id == 5 || $activity_type_id == 6 || $activity_type_id == 10 || $activity_type_id == 9) {
                $album_flag = TRUE;
                if ($activity_type_id == 10 || $activity_type_id == 9) {
                    $album_flag = FALSE;
                    $parent_activity_detail = get_detail_by_id($activity['ParentActivityID'], '', 'ActivityTypeID, Params', 2);
                    if (!empty($parent_activity_detail)) {
                        if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6))) {
                            if (!empty($parent_activity_detail['Params'])) {
                                $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                if (!empty($album_detail['AlbumGUID'])) {
                                    @$activity['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                    $album_flag = TRUE;
                                }
                            }
                        }
                    }
                }
                if ($album_flag) {
                    $count = 4;
                    if ($activity_type_id == 6) {
                        $count = $activity['Params']->count;
                    }
                    $album_details = $this->album_model->get_album_by_guid($activity['Params']->AlbumGUID);
                    $activity['AlbumEntityName'] = $activity['EntityName'];
                    $activity['EntityName'] = $album_details['AlbumName'];
                    $activity['Album'] = $this->activity_model->get_albums($activity_id, $res['UserID'], $activity['Params']->AlbumGUID, 'Activity', $count);
                    $log_type = 'Album';
                    $log_id = isset($activity['Album']['AlbumID']) ? $activity['Album']['AlbumID'] : 0;
                }
            }
            



            $edit_post_content = $activity['PostContent'];
            $activity['PostContent'] = $this->activity_model->parse_tag($activity['PostContent'], $activity_id);
            $activity['EditPostContent'] = $this->activity_model->parse_tag_edit($edit_post_content, $activity_id);
            $activity['PostTitle'] = $this->activity_model->parse_tag($activity['PostTitle'], $activity_id);

            if ($res['ActivityTypeID'] == '1' || $res['ActivityTypeID'] == '8' || $res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '7' || $res['ActivityTypeID'] == '11' || $res['ActivityTypeID'] == '12' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15' || $res['ActivityTypeID'] == '5' || $res['ActivityTypeID'] == '6') {
                $activity['CanShowSettings'] = 1;
            }

            if ($res['Privacy'] != 4 && ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12 || $res['ActivityTypeID'] == 14 || $res['ActivityTypeID'] == 15 || $res['ActivityTypeID'] == 5 || $res['ActivityTypeID'] == 6)) {
                $activity['ShareAllowed'] = 1;
            }

            if ($user_id == $res['UserID']) {
                //$activity['ShareAllowed'] = 0; // do not show share likn for self post
                $activity['ShowPrivacy'] = 0;
                if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 23 || $res['ActivityTypeID'] == 24) {
                    $activity['ShowPrivacy'] = 1;
                }
            }
            $activity['Comments'] = array();
            if ($activity['NoOfComments'] > 0) {
                if ($activity['IsOwner']) {
                    $activity['CanRemove'] = 1;
                }
                $activity['Comments'] = $this->activity_model->getActivityComments('Activity', $activity_id, '1', COMMENTPAGESIZE, $user_id, $activity['CanRemove'], 2, TRUE, $BUsers, FALSE, '', $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
            }

            if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12) {
                $activity['PostContent'] = str_replace('­', '', $activity['PostContent']);
                if (empty($activity['PostContent'])) {
                    $pcnt = $this->activity_model->get_photos_count($res['ActivityID']);
                    if (isset($pcnt['Media'])) {
                        $activity['Message'] .= ' added ' . $pcnt['MediaCount'] . ' new ' . $pcnt['Media'];
                    }
                }
            }

            if (isset($activity['RatingData']['CreatedBy']['ModuleID'])) {
                $activity['UserProfileURL'] = $activity['RatingData']['CreatedBy']['ProfileURL'];
                $activity['UserProfilePicture'] = $activity['RatingData']['CreatedBy']['ProfilePicture'];
            }

            $permission = $this->privacy_model->check_privacy($user_id, $res['UserID'], 'view_profile_picture');
            if (!$permission && $module_id == 3) {
                $activity['UserProfilePicture'] = '';
            }
            $activity['PostContent'] = trim(str_replace('&nbsp;', ' ', $activity['PostContent']));
            $activity['PostTitle'] = trim(str_replace('&nbsp;', ' ', $activity['PostTitle']));
            $activity['ShareDetails'] = array('Title' => 'Activity', 'Summary' => $activity['PostContent'], 'Image' => '', 'Link' => get_short_link(site_url() . 'activity/' . $activity['ActivityGUID']));
            if (isset($activity['Album'][0]['Media'][0])) {
                $share_image = IMAGE_SERVER_PATH . 'upload/';
                if ($activity['ActivityType'] == 'ProfilePicUpdated') {
                    $share_image .= 'profile/220x220/';
                } else if ($activity['ActivityType'] == 'ProfileCoverUpdated') {
                    $share_image .= 'profilebanner/1200x300';
                } else if ($activity['Album'][0]['AlbumName'] == 'Wall Media') {
                    $share_image .= 'wall/750x500';
                } else if ($activity['Album'][0]['AlbumName'] != 'Wall Media') {
                    $share_image .= 'album/750x500';
                }
                $share_image .= '/' . $activity['Album'][0]['Media'][0]['ImageName'];
                $activity['ShareDetails']['Image'] = $share_image;
            }
            $cnt++;
            if ($res['ActivityTypeID'] == 16 || $res['ActivityTypeID'] == 17) {
                if (!$activity['RatingData']) {
                    continue;
                }
            }
            $this->load->model(array('activity/activity_front_helper_model')); 
            $activity = $this->activity_front_helper_model->get_sticky_setting($user_id,$activity_id,$role_id,$activity);

            $activity['IsAnonymous'] = $res['IsAnonymous'];

            if ($res['IsAnonymous'] == 1 && $res['UserID'] != $user_id) {
                $activity['UserName'] = "Anonymous User";
                $activity['UserProfileURL'] = "";
                $activity['UserProfilePicture'] = "";
            }

            //Check if  view Tags is allowed
            if ($view_entity_tags) {
                $activity['EntityTags'] = $this->tag_model->get_entity_tags($search_key, 1, 30, 1, 'ACTIVITY', $activity_id, $user_id);
            }
            $activity['ActivityURL'] = get_single_post_url($activity, $res['ActivityID'], $res['ActivityTypeID'], $res['ModuleEntityID']);
            $return[] = $activity;
        }
        
        return $return;
    }

    
    
    /**
     * [getMyDesk Get the activity for dashboard]
     * @param  [int]       $user_id        [Current User ID]
     * @param  [int]       $page_no        [Page No]
     * @param  [int]       $page_size      [Page Size]     
     * @param  [int]       $feed_sort_by    [Sort By value]
     * @param  [int]       $feed_user      [POST only of this user]
     * @param  [int]       $filter_type    [Post Filter Type ] 
     * @param  [int]       $is_media_exists [Is Media Exists]
     * @param  [string]    $search_key     [Search Keyword]
     * @param  [string]    $start_date     [Start Date]
     * @param  [string]    $end_date       [End Date]
     * @return [Array]                    [Activity array]
     */
    public function getMyDesk($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), $activity_ids = array(), $view_entity_tags = 1, $role_id = 2, $post_type = 0, $tags = '', $rules = array(), $mydesk_filter = '') {

        $mydesk_filter_conditions = $mydesk_filter;
        $mydesk_filter = [];
        $my_desk_queries = [];
        $mydesk_filter_applied = 0;      
        
        if(isset($mydesk_filter_conditions['All']))  {
            unset($mydesk_filter_conditions['All']);
        }
        
        $allowed_mydesk_filter_conditions = array(
            'WatchList',
            'Mention',
            'Reminder',
            'NotifyMe',
            'All',
        );
        $sent_filters = [];
        foreach ($mydesk_filter_conditions as $field => $val) {
            if (!$val || !in_array($field, $allowed_mydesk_filter_conditions)) {                
                continue;
            }
            
            $sent_filters[$field] = $val;
        }
        
        if(!count($sent_filters)) {
            $mydesk_filter_conditions = array(
                'WatchList' => 1,
                'Mention' => 1,
                'Reminder' => 1,
                'NotifyMe' => 1,
            );
        }
        
        
        foreach ($mydesk_filter_conditions as $field => $val) {
            if (!$val || !in_array($field, $allowed_mydesk_filter_conditions)) {                
                continue;
            }
            $mydesk_filter_applied=1;
            $mydesk_filter = [];
            $mydesk_filter[$field] = $val;
            $my_desk_queries[] = $this->mydeskQuery(
                    $user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_key, $start_date, $end_date, 
                    $show_archive, $count_only, $ReminderDate, $activity_guid, $mentions, $entity_id, $entity_module_id, $activity_type_filter, $activity_ids, 
                    $view_entity_tags, $role_id, $post_type, $tags, $rules, $mydesk_filter
            );
        }
        //it will be applied on no mydesk input
        if(!$mydesk_filter_applied)
        {   
            $my_desk_queries[] = $this->mydeskQuery(
                    $user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_key, $start_date, $end_date, 
                    $show_archive, $count_only, $ReminderDate, $activity_guid, $mentions, $entity_id, $entity_module_id, $activity_type_filter, $activity_ids, 
                    $view_entity_tags, $role_id, $post_type, $tags, $rules, $mydesk_filter
            );            
        }
        $group_by = ($count_only)? '' : 'group by ActivityID ';
        $where_in = $order_by = '';
        if(!$count_only)
        {
            //FeedSortBy Conditions
            if ($feed_sort_by == 2) {
                $order_by = ' Order By ActivityID desc ';
            } else if ($feed_sort_by == 3) {
                $order_by = ' Order By (NoOfComments+NoOfLikes+NoOfViews) desc ';
            } elseif ($feed_sort_by == 'popular') {
                $where_in .= ' ActivityTypeID IN (1, 7, 11, 12) ';
                $where_in .= " AND CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "' ";
                $where_in .= ' AND ActivityTypeID IN (1, 7, 11, 12) ';
                $where_in .= " AND CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "' ";
                $where_in .= ' AND NoOfComments>1 ';
                $order_by = ' Order By ActivityTypeID ASC, NoOfComments DESC, NoOfLikes DESC ';
            } elseif ($feed_sort_by == 1) {
                $order_by = ' Order By ModifiedDate DESC ';
            } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids)) {            
                $order_by = ' Order By FIELD(ActivityID,' . implode(',', $activity_ids) . ') ';
            } elseif ($feed_sort_by == "General") {
                $where_in .= ' PostType=0 ';
            } elseif ($feed_sort_by == "Question") {
                $where_in .=' PostType=1 ';
            } elseif ($feed_sort_by == "UnAnswered") {
                $where_in .= ' PostType=1 ';
                $where_in .= ' AND NoOfComments=0 ';
            } else {
                $order_by = ' Order By ModifiedDate DESC ';
            }        
            //feedSortBy Condition Ends here
            $where_in = (!empty($where_in)) ? $where_in : '';
            $order_by = (!empty($order_by)) ? $order_by : '';
        }
        //print_r($my_desk_queries); die;
        $final_feed_query = implode(") Union All (", $my_desk_queries);
        $final_feed_query = " Select * From (($final_feed_query)) AS alias_feed $where_in $group_by  $order_by Limit $page_size";

        //echo $final_feed_query; echo '=============================================================================================';

        $result = $this->db->query($final_feed_query);
        //echo $this->db->last_query(); die;
        $feed_result = $result->result_array();  
        // echo $this->db->last_query(); die; 
        
        if ($count_only) {
            $total_count = 0;
            foreach ($feed_result as $total_row){
                $total_count += (int)!empty($total_row['TotalRow']) ? $total_row['TotalRow'] : 0;
            }
            
            return $total_count;
        }
        
        
        $return = $this->filter_result_set($feed_result, $page_no, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id);
        
        return $return;
    }

    /**
     * [Mark Mydesk task Done/Undone]     
     * @param  [int]       $activity_id        [ActivityID]
     * @param  [int]       $user_id        [Current User ID]
     * @return []                    []
     */
    public function toggle_mydesk_task($activity_id,$user_id,$status)
    {
        $return = $this->return;       
        $return['Message'] = lang('success');
        $return['ResponseCode'] = 200;
        //check if mydesk task
        $is_mydesk_task = $this->is_mydesk_task($activity_id,$user_id);
        if(!$is_mydesk_task) 
        {
            if($status=='DONE')
            {
                //insert into MyTaskStatus
                $mydesk_task = array(
                    );            
                $mydesk_task_input = array(                                
                                'UserID'        => $user_id, 
                                'ActivityID'    => $activity_id,
                                'CreatedDate'   => get_current_date('%Y-%m-%d %H:%i:%s'),
                                'ModifiedDate'   => get_current_date('%Y-%m-%d %H:%i:%s'),
                                'Status'        => $status
                            );            
                $this->db->insert(MYTASKSTATUS, $mydesk_task_input); 
                $count = 1;                
            }
        }
        else
        {   
            if(strtolower($status)=='undone')         
                $status='NOTDONE';
            $update_array['Status'] = $status;       
            $update_array['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->db->where('ActivityID',$activity_id);
            $this->db->where('UserID',$user_id);
            $this->db->update(MYTASKSTATUS,$update_array);
        }
        return $return;
    }

    function is_mydesk_task($activity_id, $user_id,$data_required=false){
        $this->db->where('ActivityID',$activity_id);
        $this->db->where('UserID',$user_id);        
        $query = $this->db->get(MYTASKSTATUS);
        if($query->num_rows()){
            if($data_required)
                return $query->row_array();
            else
                return '1';
        } else {
            return '0';
        }
    }

}
