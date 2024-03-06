<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_feed_model extends Common_Model {

    protected $blocked_users = array();
    protected $tagged_data = array();
    protected $user_activity_archive = array();

    public function __construct() {
        parent::__construct();
        $this->load->model(array('activity/activity_model', 'activity/activity_result_filter_model'));
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
    
    public function set_user_entities_data($user_id) {
                
        $this->user_model->set_user_time_zone($user_id);
        $this->user_model->set_user_profile_url($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        $this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_user_group_list($user_id);
        $this->forum_model->set_user_category_list($user_id);
        $this->group_model->set_user_categoty_group_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);            
        $this->flag_model->set_user_flagged($user_id); 
        $this->activity_model->set_user_activity_archive($user_id); 
        
        $this->page_model->set_feed_pages_condition($user_id);
        
        $this->privacy_model->set_privacy_options($user_id);
        $this->event_model->set_user_events($user_id);
        
    }


    protected function feedQuery(
        $user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, 
        $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', 
        $activity_type_filter = array(), $activity_ids = array(), $view_entity_tags = 1, $role_id = 2, $post_type = 0, $tags = '', $rules = array(), $extra_params
    ) {
        $exclude_ids = '';
        if ($filter_type != 11) {
            $exclude_ids = $this->activity_model->get_newsfeed_announcements($user_id, true);
        }
        
        $exclude_contest = array();

        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();

        $friend_followers_list = array();//$this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        $category_list = array();//$this->forum_model->get_user_category_list();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        $friend_of_friends = array();//$this->user_model->get_friends_of_friend_list();
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

        $group_list = array();//$this->group_model->get_user_group_list();
       
        $group_list[] = 0;

        $group_list = implode(',', $group_list);
        
        $event_list = array();//$this->event_model->get_user_joined_events();
        $page_list = '';//$this->page_model->get_feed_pages_condition();

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 25, 26, 36, 37, 38, 39);
        //$activity_type_allow = array(16, 17);
        $modules_allowed = array(1, 3, 30, 34);

        $this->show_suggestions = FALSE;
        $show_media = TRUE;

        if ($privacy_options) {
            foreach ($privacy_options as $key => $val) {
                if ($key == 'g' && $val == '0') {
                    $modules_allowed[] = 1;
                }
                if ($key == 'e' && $val == '0') {
                    $modules_allowed[] = 14;
                }
                if ($key == 'p' && $val == '0') {
                    $modules_allowed[] = 18;
                }
                if ($key == 'm') {
                    if ($val == '1') {
                        $show_media = FALSE;
                        unset($activity_type_allow[array_search('5', $activity_type_allow)]);
                        unset($activity_type_allow[array_search('6', $activity_type_allow)]);
                    }
                }
                if ($key == 'r' && $val == '0') {
                    $activity_type_allow[] = 16;
                    $activity_type_allow[] = 17;
                }
                if ($key == 's' && $val == '0') {
                    if ($filter_type == '0' && empty($mentions)) {
                        if (empty($this->IsApp)) { /* For Web added by gautam */
                            //$this->show_suggestions = true;
                        }
                    }
                }
            }
        }

       /* if ($this->IsApp == 1) { // For Mobile  
            // added by gautam - starts 
            if ($filter_type == 'Group') {
                $modules_allowed = array(
                    1
                );
            } elseif ($filter_type == 'Page') {
                $modules_allowed = array(
                    18
                );
            }
            // added by gautam - ends 
        }
        */

        if ($filter_type == 3) {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = $this->activity_model->get_allowed_activity_type('newsfeed',0,3);
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

                $activity_ids = '';//$this->polls_model->my_poll_activities($entity_id, $entity_module_id, $is_expired);
                if (empty($activity_ids)) {
                    return array();
                }
            }
            //My Voted Polls
            if ($filter_type == 9) {
                $activity_ids = '';//$this->polls_model->my_voted_poll_activities($entity_id, $entity_module_id);
                if (empty($activity_ids)) {
                    return array();
                }
            }
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed)) {

            $modules_allowed[] = 1;
        }
        
        
        
        
        $select_array = array();
        $select_array[] = 'A.*';
        $select_array[] = 'ATY.ViewTemplate, ATY.Template,  ATY.CommentsAllowed, ATY.LikeAllowed, ATY.ActivityType,';
        //$select_array[]='A.*, ATY.ActivityTypeID, ';
        $select_array[]= 'ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';
        //$select_array[] = 'IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';
                
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        
        $ward_ids = array(1, 2, 3);
        $this->db->join(ACTIVITYWARD . ' AW', "AW.ActivityID=A.ActivityID AND AW.WardID IN(" . implode(',', $ward_ids) . ")");
        
        //$this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID AND UD.LocalityID="' . $this->LocalityID . '"');
        //$this->db->_protect_identifiers = FALSE;
       // $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
       // $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        /* $this->db->join(FRIENDS." FR","FR.FriendID=A.ModuleEntityID AND A.ModuleID='3' AND FR.UserID='".$user_id."' AND FR.Status='1'"); */
        $this->db->_protect_identifiers = TRUE;

        /* Join Activity Links Starts */
        $select_array[] = 'IF(URL is NULL,0,1) as IsLinkExists';
        $select_array[] = 'AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';
        $select_array_extra[]="'' ReminderGUID,'' ReminderDateTime,'' ReminderCreatedDate,'' ReminderStatus";
        $select_array_extra[]=" 0 SortByReminder";        

        
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        /* Join Activity Links Ends */
        
        if(!empty($extra_params['AllowedActivityTypeID'])) {
            $this->db->where_in('A.ActivityTypeID', $extra_params['AllowedActivityTypeID']);
        }
        
        $isPromoted = 0;
        if(isset($extra_params['IsPromoted']) && $extra_params['IsPromoted'] == 1 ) {
            $is_admin = $this->user_model->is_super_admin($user_id);
            if($is_admin) $isPromoted = 1;
        } 
        
        
        // Set privacy condition
        $privacy_condition = $this->privacy_condition(
            $user_id, $friend_followers_list, $activity_ids, $only_friend_followers, $friends, $follow, 
            $group_list, $category_list, $page_list, $event_list, $friend_of_friends
        );


        // Set feed normal condition
        $select_array = $this->feed_condition(
                $rules, $exclude_ids, $activity_ids, $privacy_condition['condition'], $user_id, $privacy_condition['privacy_condition'], 
                $modules_allowed, $activity_type_allow, $page_list, $filter_type, $activity_guid, $select_array, $extra_params
        );
        
        
        // Exclude feeds for forum category with visibility 0
        $forum_category_exclude_cnds = 
                "A.ModuleEntityID NOT IN ".

                "(Select ForumCategoryID From ". 
                FORUMCATEGORY . " FC INNER JOIN " . FORUM . " F "
                . " ON F.ForumID = FC.ForumID"
                . " WHERE F.Visible = 0 )"
                ;


        $this->db->where($forum_category_exclude_cnds, null, false);
        
        //$this->db->where('A.LocalityID', $this->LocalityID);
                
        // Apply filter condition
        $this->apply_filter(
                $filter_type, $friends, $post_type, $user_id, $activity_guid, $activity_ids, $mentions, $modules_allowed, 
                $activity_type_allow, $tags, $feed_user, $show_media, $is_media_exists, $search_key, $start_date, $end_date, $time_zone, $isPromoted, $ReminderDate
        );

        // Sort by condition
        $this->sort_condition($feed_sort_by, $activity_ids, $filter_type, $count_only, $isPromoted, $extra_params);
        
        // Exclude blocked users
        if (!empty($blocked_users) && empty($feed_user)) {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        
        if($exclude_contest && !$activity_guid)
        {
            $this->db->where_not_in('A.ActivityID',$exclude_contest);
        }

        $this->db->where_not_in('A.StatusID',array('3'));
        $this->db->where_not_in('U.StatusID',array('3','4'));
        
        // To manage brackets of rules
        if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
            $rules = [];
        }
        $rules_end_bracket = ')';
        if (!empty($rules)) {
            $rules_end_bracket = '';
        }
        $this->db->where("ATY.StatusID = 2 $rules_end_bracket", null, false);
        
        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        if ($count_only) {
            //$this->db->select('COUNT(DISTINCT A.ActivityID) as TotalRow ');
            $this->db->select('COUNT(A.ActivityID) as TotalRow ');
            //$result = $this->db->get();
            //$count_data = $result->row_array();
            //return $count_data['TotalRow'];
        } else {
            $this->db->select(implode(',', $select_array), false);
            //$this->db->group_by('A.ActivityID');
        }
        
        $exclude_activity_types = [25];                 
        $this->db->where_not_in('A.ActivityTypeID', $exclude_activity_types);
        
        
        if(isset($extra_params['RecentActivityDate']) && !empty($extra_params['RecentActivityDate']) &&  $page_no == 1 && $feed_sort_by == 2) {
            $this->db->where('A.PromotedDate > ', $extra_params['RecentActivityDate']);
        }
        
        if(isset($extra_params['ExcludeActivityID']) && !empty($extra_params['ExcludeActivityID']) && $page_no != 1) {
            $this->db->where_not_in('A.ActivityID', $extra_params['ExcludeActivityID']);
        }
        
        
        $compiled_query = $this->db->_compile_select();  //echo $compiled_query; die;

        $this->db->reset_query();

        //$result = $this->db->get();
        //echo $compiled_query; echo '===============================================================';
        return $compiled_query;
    }
    
    protected function public_feed_conditions() {
        //$this->db->where('A.StatusID', '2');
        $this->db->or_where('(A.Privacy', '1');
        //$this->db->where('ATY.StatusID', '2');
        $this->db->where("IF(A.ActivityTypeID=8,(
                (SELECT UserID FROM ".USERPRIVACY." WHERE PrivacyLabelKey='default_post_privacy' AND Value='everyone' AND UserID=A.ModuleEntityID AND A.ModuleID='3') is not null
            ),true)",NULL,FALSE);
        $this->db->where("IF(A.ActivityTypeID=23 OR A.ActivityTypeID=24,A.ModuleID=3,true)",NULL,FALSE);
        $this->db->where("IF(A.ActivityTypeID=7,(SELECT GroupID FROM ".GROUPS." WHERE IsPublic IN(1, 0) AND GroupID=A.ModuleEntityID AND StatusID='2') is not null,true)",NULL,FALSE);
        
        $this->db->where("IF(A.ActivityTypeID=26,(SELECT ForumCategoryID FROM ".FORUMCATEGORY." WHERE Visibility IN(1) AND ForumCategoryID=A.ModuleEntityID AND StatusID='2') is not null,true)",NULL,FALSE);
        
        $this->db->where('A.IsFeatured = 1 ))', NULL, FALSE);
        //$this->db->order_by('A.ModifiedDate','DESC');
    }

    protected function mentions_condition($module_id, $module_entity_id, $extra_params) {
        
        if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
            return;
        }
        
        $module_id = $this->db->escape_str($module_id);
        $module_entity_id = $this->db->escape_str($module_entity_id);
        
        $mentions_conditions = " SELECT MN.ActivityID FROM ".MENTION." MN WHERE MN.StatusID = 2 AND MN.ModuleID = $module_id AND MN.ModuleEntityID = $module_entity_id";
        $this->db->or_where("( A.ActivityID IN ( $mentions_conditions ))  ", NULL, FALSE);
        
        $this->public_feed_conditions();
        
        
    }

    protected function reminder_condition($user_id, $filter_type, $activity_guid) {
        $select_array_extra = [];
        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            
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
                    $this->db->where("(R.Status IS NULL OR R.Status='ACTIVE')", NULL, FALSE);
                }
            }
            $this->db->join(REMINDER . " R", $joincondition, $jointype);
            $this->db->_protect_identifiers = TRUE;
        }
        
        return $select_array_extra;
    }

    protected function privacy_condition(
            $user_id, $friend_followers_list, $activity_ids, $only_friend_followers, $friends, $follow, 
            $group_list, $category_list, $page_list, $event_list, $friend_of_friends
    ) {
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
        $friends_implode_str = implode(',', $friends);
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
                                OR " . $condition_part_two . " OR A.PostType=8";
            $case_array[] = "A.ActivityTypeID=37 THEN A.IsWinnerAnnounced=0";
            $case_array[] = "A.ActivityTypeID=39 THEN A.IsWinnerAnnounced=1";
            $case_array[] = "A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";

            $case_array[] = "A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";

            $case_array[] = "A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";

//            $case_array[] = "A.ActivityTypeID=8 
//                            THEN
//                                (A.UserID IN ( $friend_followers_list) OR A.ModuleEntityID='$user_id') ";
            
//            $case_array[] = "A.ActivityTypeID=8  
//                            THEN
//                                ((A.UserID IN ( $friend_followers_list) AND A.UserID = A.ModuleEntityID ) OR (A.ModuleEntityID IN($friend_followers_list) AND A.ModuleID = 3  )   ) ";
            
            $case_array[] = "A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'  OR (A.ModuleEntityID IN( $friends_implode_str ) AND A.Privacy=1)";

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
        
        //OR A.ModuleEntityID IN($friends_implode_str) )
       
        
        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN (A.UserID IN ($friends_implode_str) OR (A.ModuleEntityID IN($friends_implode_str) AND A.ModuleID = 3 ) )";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN (A.UserID='$user_id' OR (A.ModuleEntityID = $user_id AND A.ModuleID = 3 ) ) ELSE 1 END) END) END)  ";
        $condition .= " OR ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='$user_id' AND ActivityID=A.ActivityID LIMIT 1) is not null)";
        $condition .= ")";

        return array(
            'condition' => '',
            'privacy_condition' => $privacy_condition,
        );
    }

    protected function apply_filter(
        $filter_type, $friends, $post_type, $user_id, $activity_guid, $activity_ids, $mentions, $modules_allowed, $activity_type_allow, $tags, $feed_user, $show_media, 
        $is_media_exists, $search_key, $start_date, $end_date, $time_zone, $isPromoted, $ReminderDate
    ) {
        if ($this->IsApp == 1)/* For Mobile */ {
            /* added by gautam */
            if ($filter_type == 'Connection') /* third param passed to get only folower list */ {
                $this->db->where_in('U.UserID', $friends);
            }
        }

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
            }
            else if (($filter_type == 1 || $filter_type === 'Favourite') && empty($activity_ids))
            {
              $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"');
              $this->db->where('F.UserID', $user_id);
              $this->db->where('F.StatusID', '2');
            } else {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43)) {
                    $this->db->where("NOT EXISTS (SELECT 1 FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND ActivityID=A.ActivityID AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids) {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            if ($mentions) {
                $join_condition = "MN.ActivityID=A.ActivityID AND MN.StatusID= 2 AND (";
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
        } else {
            $this->db->where('A.IsShowOnNewsFeed', 0);
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

       // echo $show_media.' '.$is_media_exists;die;
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

        if (!empty($search_key)) {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        
        if ($filter_type == 3) {
            if ($ReminderDate) {
                $rd_data = array();
                foreach ($ReminderDate as $rd) {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }
        
        if ($start_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date) {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }
        
        if($isPromoted) {
            $this->db->where('A.IsPromoted', 1);
        } 
    }

    protected function feed_condition(
            $rules, $exclude_ids, $activity_ids, $condition, $user_id, $privacy_condition, $modules_allowed, 
            $activity_type_allow, $page_list, $filter_type, $activity_guid, $select_array, $extra_params
    ) {
        
        if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
            $rules = [];
        }
        
        $this->activityrule_model->condition_for_rule_posts($rules, $modules_allowed, $activity_type_allow, $user_id, $page_list, $exclude_ids);
        
        // Fix for query failure
        $exclude_ids = explode(',', $exclude_ids);
        $exclude_ids[] = 0;
        $exclude_ids = implode(',', $exclude_ids);
        
        if (!empty($rules)) {
            $this->db->or_where_not_in('((A.ActivityID', explode(',', $exclude_ids));
        } else if ($exclude_ids) {
            //$this->db->where_not_in('A.ActivityID', explode(',', $exclude_ids));
            $exclude_ids = trim($exclude_ids, ',');
            $this->db->or_where("(A.ActivityID NOT IN($exclude_ids) ", Null, false );
        }

        if (empty($activity_ids)) {
            if (!empty($condition)) {
                $condition = '(('.$condition;
                $this->db->where($condition, NULL, FALSE);
            } else {
                $this->db->where('((A.ModuleID', '3');
               // $this->db->where('A.ModuleEntityID', $user_id);
            }
            
            if ($privacy_condition) {
                $privacy_condition = $privacy_condition.')';
                
                if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
                    $privacy_condition = $privacy_condition.')';
                }
                
                $this->db->where($privacy_condition, null, false);
            }
            
            $this->mentions_condition(3, $user_id, $extra_params);
        }
        
        // Set reminder condition
        $select_array_extra = $this->reminder_condition($user_id, $filter_type, $activity_guid);
        if(isset($select_array_extra[0])) $select_array[] = $select_array_extra[0];
        if(isset($select_array_extra[1])) $select_array[] = $select_array_extra[1];
        
        if (!empty($rules)) {
            $this->db->where(" 1 )) )", NULL, FALSE);
        }
        
        return $select_array;
    }

    protected function sort_condition($feed_sort_by, $activity_ids, $filter_type, $count_only, $isPromoted, $extra_params) {
        
        if(!empty($extra_params['emailer'])) {
            $this->emailer_condition($feed_sort_by, $activity_ids, $filter_type, $count_only, $isPromoted, $extra_params);         
            return;
        }
                
        if (!$this->settings_model->isDisabled(28) && $filter_type != 7 && !$count_only) {
            
            if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1 && !$count_only) {
                $this->db->where('R.ReminderDateTime < "'.get_current_date('%Y-%m-%d %H:%i:%s').'"', NULL, FALSE);
                $this->db->where('R.ReminderID IS NOT NULL', NULL, FALSE);
                $this->db->order_by("ReminderDateTime DESC");
                
                return;
            } 
            
            if(!isset($extra_params['UnionApply'])) {
                $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            }
            
            
            if(!$count_only && isset($extra_params['UnionApply'])) {
                $this->db->where('( R.ReminderDateTime IS NULL OR R.ReminderDateTime > "'.get_current_date('%Y-%m-%d %H:%i:%s').'" )', NULL, FALSE);
                //$this->db->where('R.ReminderID IS NULL', NULL, FALSE);
            }
        }
        
        
        
        
        if ($feed_sort_by == 2 && !$count_only) {
            
            // Disable default order
            //$this->db->order_by('A.ActivityID', 'DESC');
            $this->db->order_by('A.PromotedDate', 'DESC');
            
        } else if ($feed_sort_by == 3 && !$count_only) {
            $this->db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfViews)', 'DESC');
        } else if(!$count_only){
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($feed_sort_by == 'popular') {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12, 26));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            if(!$count_only) {
                $this->db->order_by('A.ActivityTypeID', 'ASC');
                $this->db->order_by('A.NoOfComments', 'DESC');
                $this->db->order_by('A.NoOfLikes', 'DESC');
            }
            
            
        } elseif ($feed_sort_by == 1 && !$count_only) {
            $this->db->order_by('A.ActivityID', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids) && !$count_only) {
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
        } else if(!$count_only){
            // Disable default order
            //$this->db->order_by('A.ModifiedDate', 'DESC');
        }
    }
    
    /*Specific condition for email templates
     */
    protected function emailer_condition($feed_sort_by, $activity_ids, $filter_type, $count_only, $isPromoted, $extra_params) {
        
        $user_id = $extra_params['emailer']['UserID'];
        
        if(!empty($extra_params['emailer']['ActiveCategory']) || !empty($extra_params['emailer']['ActiveGroup']) || !empty($extra_params['emailer']['ActivePage'])) {
            
            $activeCategories = $extra_params['emailer']['ActiveCategory'];
            $activeGroups = $extra_params['emailer']['ActiveGroup'];
            $activePages = $extra_params['emailer']['ActivePage'];
            
            $where_cnd = "";
            if($activeCategories) {
                $activeCategories = implode(',', $activeCategories);
                $where_cnd = "( A.ModuleEntityID IN($activeCategories) AND A.ModuleID = 34 )";
            }
            
            if($activeGroups) {
                $activeGroups = implode(',', $activeGroups);
                $where_cnd .= ($where_cnd) ? ' OR ' : '';
                $where_cnd .= " ( A.ModuleEntityID IN($activeGroups) AND A.ModuleID = 1 ) ";
            }
            
            if($activePages) {
                $activePages = implode(',', $activePages);
                $where_cnd .= ($where_cnd) ? ' OR ' : '';
                $where_cnd .= "( A.ModuleEntityID IN($activePages) AND A.ModuleID = 18 )";
            }
                                    
            $this->db->where("($where_cnd)", NULL, FALSE);                            
        }
        
        if(!empty($extra_params['emailer']['ExcludeActivities'])) {
            $this->db->where_not_in('A.ActivityID', $extra_params['emailer']['ExcludeActivities']);
        }
        
        
        if(!empty($extra_params['emailer']['ActivitiesWithinDays'])) {
            $days_diff_date = get_current_date('%Y-%m-%d', $extra_params['emailer']['ActivitiesWithinDays']);            
            $this->db->where(" (A.CreatedDate >= '$days_diff_date' )", NULL, FALSE);
        }
        
        
        $this->db->where_in('A.ActivityTypeID', $extra_params['emailer']['AllowedActivityTypeID']);        
        $this->db->where('A.UserID != ', $extra_params['emailer']['UserID']);
        
        $this->db->order_by('A.TotalLikeViewComment', 'DESC');            
        $this->db->order_by('A.ModifiedDate', 'DESC');            
    }

    public function getRecentContest($user_id, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $ReminderDate = array(),
         $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), 
        $activity_ids = array(), $view_entity_tags = 1, $role_id = 2, $post_type = 0, $tags = '', $rules = array(), $extra_params = array())
    {
        $return = array();
        $exclude_ids = $this->activity_model->get_newsfeed_announcements($user_id, true);
        
        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();

        $modules_allowed = array(3);

        $friend_followers_list = array('0');
        $privacy_options = $this->privacy_model->get_privacy_options();
        $category_list = array('0');
        $friend_of_friends = array('0');
        $friends = array('0');
        $follow = array('0');
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

        $group_list = array(0);
        $category_group_list = 0;

        $group_list = implode(',', $group_list);
        if (!empty($category_group_list)) {
            $group_list = $group_list . ',' . $category_group_list;
            if ($group_list) {
                $group_list = implode(',', array_unique(explode(',', $group_list)));
            }
        }
        $event_list = 0;
        //$page_list = $this->page_model->get_user_pages_list();
        $page_list = 0;

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $activity_type_allow = $this->activity_model->get_allowed_activity_type('newsfeed',0);

        $select_array = array();
        $select_array[] = 'A.*';
        $select_array[] = 'ATY.ViewTemplate, ATY.Template,  ATY.CommentsAllowed, ATY.LikeAllowed, ATY.ActivityType,';
        $select_array[]= 'ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';
        //$select_array[] = 'IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';
        $select_array[] = 'IF(URL is NULL,0,1) as IsLinkExists';
        $select_array[] = 'AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';
        $select_array_extra[]="'' ReminderGUID,'' ReminderDateTime,'' ReminderCreatedDate,'' ReminderStatus";
        $select_array_extra[]=" 0 SortByReminder";

        // Set privacy condition
        $privacy_condition = $this->privacy_condition(
            $user_id, $friend_followers_list, $activity_ids, $only_friend_followers, $friends, $follow, 
            $group_list, $category_list, $page_list, $event_list, $friend_of_friends
        );

        $select_array = $this->feed_condition(
                $rules, $exclude_ids, $activity_ids, $privacy_condition['condition'], $user_id, $privacy_condition['privacy_condition'], 
                $modules_allowed, $activity_type_allow, $page_list, $filter_type, '', $select_array, $extra_params
        );

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(ACTIVITY.' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->join(PARTICIPANTS.' P',"A.ActivityID=P.ActivityID AND P.ParticipantID='".$user_id."'",'left');
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        $this->db->where("P.ParticipationID is null",null,false);
        $this->db->where('A.ActivityTypeID','37');
        $this->db->where('A.StatusID','2');
        $this->db->where("A.ContestEndDate>'".get_current_date('%Y-%m-%d %H:%i:%s')."'",null,false);

        if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
            $rules = [];
        }
        $rules_end_bracket = ')';
        if (!empty($rules)) {
            $rules_end_bracket = '';
        }
        $this->db->where("ATY.StatusID = 2 $rules_end_bracket", null, false);

        $this->db->order_by('A.ContestEndDate','DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if($query->num_rows())
        {
            $feed_result = $query->result_array();
            $return = $this->activity_result_filter_model->filter_result_set($feed_result, 1, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id, $this);
        }

        return $return;
    }

    public function getUnviewedVisualPost($user_id, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $ReminderDate = array(),
         $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), 
        $activity_ids = array(), $view_entity_tags = 1, $role_id = 2, $post_type = 0, $tags = '', $rules = array(), $extra_params = array())
    {
        $return = array();
        $exclude_ids = $this->activity_model->get_newsfeed_announcements($user_id, true);
        
        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();

        $modules_allowed = array(3);

        $friend_followers_list = array('0');
        $privacy_options = $this->privacy_model->get_privacy_options();
        $category_list = array('0');
        $friend_of_friends = array('0');
        $friends = array('0');
        $follow = array('0');
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

        $group_list = array(0);
        $category_group_list = 0;

        $group_list = implode(',', $group_list);
        if (!empty($category_group_list)) {
            $group_list = $group_list . ',' . $category_group_list;
            if ($group_list) {
                $group_list = implode(',', array_unique(explode(',', $group_list)));
            }
        }
        $event_list = 0;
        //$page_list = $this->page_model->get_user_pages_list();
        $page_list = 0;

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $activity_type_allow = $this->activity_model->get_allowed_activity_type('newsfeed',0);

        $select_array = array();
        $select_array[] = 'A.*';
        $select_array[] = 'ATY.ViewTemplate, ATY.Template,  ATY.CommentsAllowed, ATY.LikeAllowed, ATY.ActivityType,';
        $select_array[]= 'ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';
        //$select_array[] = 'IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';
        $select_array[] = 'IF(URL is NULL,0,1) as IsLinkExists';
        $select_array[] = 'AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';
        $select_array_extra[]="'' ReminderGUID,'' ReminderDateTime,'' ReminderCreatedDate,'' ReminderStatus";
        $select_array_extra[]=" 0 SortByReminder";

        // Set privacy condition
        $privacy_condition = $this->privacy_condition(
            $user_id, $friend_followers_list, $activity_ids, $only_friend_followers, $friends, $follow, 
            $group_list, $category_list, $page_list, $event_list, $friend_of_friends
        );

        $select_array = $this->feed_condition(
                $rules, $exclude_ids, $activity_ids, $privacy_condition['condition'], $user_id, $privacy_condition['privacy_condition'], 
                $modules_allowed, $activity_type_allow, $page_list, $filter_type, '', $select_array, $extra_params
        );

        $this->db->select(implode(',', $select_array), false);
        $this->db->from(ACTIVITY.' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->join(ENTITYVIEW.' E',"A.ActivityID=E.EntityID AND E.EntityType='Activity' AND E.UserID='".$user_id."'",'left');
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        $this->db->where("E.EntityViewID is null",null,false);
        $this->db->where('A.ActivityTypeID','37');
        $this->db->where('A.StatusID','2');
        $this->db->where("A.ContestEndDate>'".get_current_date('%Y-%m-%d %H:%i:%s')."'",null,false);

        if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
            $rules = [];
        }
        $rules_end_bracket = ')';
        if (!empty($rules)) {
            $rules_end_bracket = '';
        }
        $this->db->where("ATY.StatusID = 2 $rules_end_bracket", null, false);

        $this->db->order_by('A.ContestEndDate','DESC');
        $this->db->limit(1);
        $query = $this->db->get();

        if($query->num_rows())
        {
            $feed_result = $query->result_array();
            $return = $this->activity_result_filter_model->filter_result_set($feed_result, 1, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id, $this);
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
    public function getFeed(
        $user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, 
        $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), 
        $activity_ids = array(), $view_entity_tags = 1, $role_id = 2, $post_type = 0, $tags = '', $rules = array(), $extra_params = []
    ) {

        $is_single_activity = 0;
        if(!empty($activity_guid))
        {
            $is_single_activity = 1;
        }
        if($feed_sort_by == 2) {
            $extra_params['UnionApply'] = 1;
        }
        
        $final_feed_query = $this->feedQuery(
            $user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_key, $start_date, $end_date, 
            $show_archive, $count_only, $ReminderDate, $activity_guid, $mentions, $entity_id, $entity_module_id, $activity_type_filter, $activity_ids, 
            $view_entity_tags, $role_id, $post_type, $tags, $rules, $extra_params
        );
        
        
       /* if(!$count_only  && !empty($extra_params['UnionApply'])) {
            
            $extra_params['ReminderSort'] = 1;
            $final_feed_reminder_query = $this->feedQuery(
                $user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_key, $start_date, $end_date, 
                $show_archive, $count_only, $ReminderDate, $activity_guid, $mentions, $entity_id, $entity_module_id, $activity_type_filter, $activity_ids, 
                $view_entity_tags, $role_id, $post_type, $tags, $rules, $extra_params
            );
            $final_feed_query = [
                $final_feed_reminder_query,
                $final_feed_query,
            ];
            
            $final_feed_query = implode(") Union All (", $final_feed_query);
            $final_feed_query = " Select * From (($final_feed_query)) AS alias_feed Group by ActivityID Limit $page_size";
            
        } */ 
        //print_r($final_feed_query);die;
        
//        $extra_params['IsPublicFeed'] = 1;
//        $final_public_feed_query = $this->feedQuery(
//            $user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_key, $start_date, $end_date, 
//            $show_archive, $count_only, $ReminderDate, $activity_guid, $mentions, $entity_id, $entity_module_id, $activity_type_filter, $activity_ids, 
//            $view_entity_tags, $role_id, $post_type, $tags, $rules, $extra_params
//        );
        
        
        
        
         //echo $final_feed_query; echo '=============================================================='; die;

        $result = $this->db->query($final_feed_query);
        //echo $this->db->last_query();die;
        $feed_result = $result->result_array();  //print_r(count($feed_result)); die;
        
        
        if ($count_only) {
            $total_count = 0;
            foreach ($feed_result as $total_row){
                //echo $final_feed_query; echo '==============================================================';
                $total_count += (int)!empty($total_row['TotalRow']) ? $total_row['TotalRow'] : 0;
            }
            
            return $total_count;
        }
        
        $return = $this->activity_result_filter_model->filter_result_set($feed_result, $page_no, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id, $this,$is_single_activity);

        /*$return_data = array();
        if($page_no == 1)
        {
            $i = 1;
            foreach($return as $r)
            {
                if($r==1)
                {
                    $return_data[] = $contest;
                }
                if($r==5)
                {
                    $return_data[] = $visual_post;   
                }
                $return_data[] = $r;
                $i++;
            }
        }*/
        
        return $return;
    }
 
}
