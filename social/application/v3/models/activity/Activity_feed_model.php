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
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"', 'LEFT');
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
        if (!in_array($filter_type, array(1, 11, 12))) {
            $exclude_ids = $this->activity_model->get_newsfeed_announcements($user_id, true, $extra_params);
        }
        if(!empty($tags)) {
            $exclude_ids = '';
        }
        
        $exclude_contest = array();
        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();

        $activity_type_allow = array(1, 8, 25);
        //$activity_type_allow = array(16, 17);
        $modules_allowed = array(3);
        $quiz_list = '';
        $tag_list = '';
        $muted_tag_list = '';
        $friend_followers_list = array();
        $only_friend_followers = array();
        $follow = $this->user_model->get_friend_followers_list();

        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        

        $friend_of_friends = array();//$this->user_model->get_friends_of_friend_list();
        

        if(API_VERSION == "v5" ){
            if(!in_array($filter_type, array(1, 12))) {
                $quiz_list = $this->quiz_model->get_following_quiz_list();       
                $quiz_list[] = 0;
                $quiz_list = implode(',', $quiz_list);
                $muted_tag_list = $this->tag_model->get_muted_tag_list();
                $tag_list = $this->tag_model->get_following_tag_list();    
                
                $tag_list = array_diff($tag_list, $muted_tag_list);

                $tag_list[] = 0;
                $tag_list = implode(',', $tag_list);

                $muted_tag_list[] = 0;
                $muted_tag_list = implode(',', $muted_tag_list);
            }
           

            $modules_allowed[] = 47;
            $activity_type_allow[] = 49;
            $activity_type_allow[] = 50;
            $activity_type_allow[] = 25;

            $follow = $this->user_model->get_followers_list();

            $friends[] = 0;
            $follow[] = 0;
            $friend_of_friends[] = 0;
            $friend_followers_list = array_merge($friends, $follow);
            $friend_followers_list[] = 0;
            if (!in_array($user_id, $friend_followers_list)) {
                $friend_followers_list[] = $user_id;
            }
            $friend_followers_list = array_unique($friend_followers_list);

            $only_friend_followers = $friend_followers_list;
            if (in_array($user_id, $friend_followers_list)) {
                unset($only_friend_followers[$user_id]);
                if (!$only_friend_followers) {
                    $only_friend_followers[] = 0;
                }
            }

            $friend_followers_list = implode(',', $friend_followers_list);
            $friend_of_friends = implode(',', $friend_of_friends);

            if ($tags) { 
                $quiz_list = '';
                $tag_list = '';
                $muted_tag_list = '';
                $friend_followers_list = '';
            }
        } else {
            $friend_followers_list = '';
        }

        $friend_followers_list = implode(",",$follow['Follow']);        
        $privacy_options = $this->privacy_model->get_privacy_options();
        $category_list = array();//$this->forum_model->get_user_category_list();
        

        if(!empty($activity_guid)) {
            $friend_followers_list = '';
            $quiz_list = '';
            $tag_list = '';
            $muted_tag_list = '';
        }
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

        

        $this->show_suggestions = FALSE;
        $show_media = TRUE;

        

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

        if ($filter_type === 1 && !in_array(1, $modules_allowed)) {
            $modules_allowed[] = 1;
        }
        
        
        
        
        $select_array = array();
        $select_array[] = 'A.*, A.Summary as Description';
        $select_array[] = 'ATY.ViewTemplate, ATY.Template,  ATY.CommentsAllowed, ATY.LikeAllowed, ATY.ActivityType,';
        //$select_array[]='A.*, ATY.ActivityTypeID, ';
        $select_array[]= 'ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture, U.IsVIP, U.IsAssociation';
        //$select_array[] = 'IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';
                
        $this->current_db->from(ACTIVITY . ' A');
        $this->current_db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->current_db->join(USERS . ' U', 'U.UserID=A.UserID AND U.StatusID NOT IN (3,4)');
        
        $ward_ids_str = '';
        if(isset($extra_params['WardIds']) && !empty($extra_params['WardIds']) && empty($activity_guid)) {
            $ward_ids = $extra_params['WardIds'];
            if(!in_array(1, $ward_ids)) {
                $ward_ids[] = 1;
            }
            $ward_ids_str = implode(',', $ward_ids);
            $ward_ids_str = trim($ward_ids_str, ',');
            //$this->current_db->join(ACTIVITYWARD . ' AW', "AW.ActivityID=A.ActivityID AND AW.WardID IN(" . $ward_ids_str . ")");
        }
        

        if(isset($extra_params['TagCategoryID']) && !empty($extra_params['TagCategoryID']) && empty($activity_guid)) {
            $tag_category_id = $extra_params['TagCategoryID'];
            $tag_id = isset($extra_params['TagID']) ? $extra_params['TagID'] : 0;
            $this->current_db->join(ENTITYTAGSCATEGORY . ' ET', "ET.EntityID=A.ActivityID AND ET.EntityType='ACTIVITY' AND ET.TagCategoryID=" . $tag_category_id." AND ET.TagID=" . $tag_id);
        }

        // $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID AND UD.LocalityID="' . $this->LocalityID . '"');
        // $this->db->_protect_identifiers = FALSE;
        // $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        // $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        /* $this->db->join(FRIENDS." FR","FR.FriendID=A.ModuleEntityID AND A.ModuleID='3' AND FR.UserID='".$user_id."' AND FR.Status='1'"); */
        $this->current_db->_protect_identifiers = TRUE;

        /* Join Activity Links Starts */
        //$select_array[] = 'IF(URL is NULL,0,1) as IsLinkExists';
        //$select_array[] = 'AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';
        //$select_array_extra[]="'' ReminderGUID,'' ReminderDateTime,'' ReminderCreatedDate,'' ReminderStatus";
        //$select_array_extra[]=" 0 SortByReminder";
        
        //$this->current_db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        /* Join Activity Links Ends */
        
        if(!empty($extra_params['AllowedActivityTypeID'])) {
            $this->current_db->where_in('A.ActivityTypeID', $extra_params['AllowedActivityTypeID']);
        }
        
        $isPromoted = 0;
        if(isset($extra_params['IsPromoted']) && $extra_params['IsPromoted'] == 1 ) {
            $is_admin = $this->user_model->is_super_admin($user_id);
            if($is_admin) $isPromoted = 1;
        } 
                
        // Set privacy condition
        $privacy_condition = $this->privacy_condition(
            $user_id, $friend_followers_list, $activity_ids, $only_friend_followers, $friends, $follow, 
            $group_list, $category_list, $page_list, $event_list, $friend_of_friends, $quiz_list, $tag_list, $muted_tag_list, $ward_ids_str
        );


        // Set feed normal condition
        $select_array = $this->feed_condition(
                $rules, $exclude_ids, $activity_ids, $privacy_condition['condition'], $user_id, $privacy_condition['privacy_condition'], 
                $modules_allowed, $activity_type_allow, $page_list, $filter_type, $activity_guid, $select_array, $extra_params
        );
        
        
        //$this->db->where('A.LocalityID', $this->LocalityID);
                
        // Apply filter condition
        $tag_category_ids = array();
        if(isset($extra_params['TagCategories']) && !empty($extra_params['TagCategories']) && is_array($extra_params['TagCategories'])) {
            $tag_category_ids = $extra_params['TagCategories'];
        } 
        $this->apply_filter(
                $filter_type, $friends, $post_type, $user_id, $activity_guid, $activity_ids, $mentions, $modules_allowed, 
                $activity_type_allow, $tags, $feed_user, $show_media, $is_media_exists, $search_key, $start_date, $end_date, $time_zone, $isPromoted, $ReminderDate, $tag_category_ids
        );

        
        // Sort by condition
        $this->sort_condition($feed_sort_by, $activity_ids, $filter_type, $count_only, $isPromoted, $extra_params);
        
        // Exclude blocked users
        if (!empty($blocked_users) && empty($feed_user)) {
            $this->current_db->where_not_in('A.UserID', $blocked_users);
        }
        
        if($exclude_contest && !$activity_guid)
        {
            $this->current_db->where_not_in('A.ActivityID',$exclude_contest);
        }
        
        // To manage brackets of rules
        if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
            $rules = [];
        }
        $rules_end_bracket = ')';
        if (!empty($rules)) {
            $rules_end_bracket = '';
        }
        $this->current_db->where("ATY.StatusID = 2 $rules_end_bracket", null, false);

        if (!$count_only && !empty($page_size)) {
            $this->current_db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        if ($count_only) {
            $this->current_db->select('COUNT(A.ActivityID) as TotalRow ');            
        } else {
            $this->current_db->select(implode(',', $select_array), false);
        }
        
        if(isset($extra_params['RecentActivityDate']) && !empty($extra_params['RecentActivityDate']) &&  $page_no == 1 && $feed_sort_by == 2) {
            $this->current_db->where('A.PromotedDate > ', $extra_params['RecentActivityDate']);
        }
        
        if(isset($extra_params['ExcludeActivityID']) && !empty($extra_params['ExcludeActivityID']) && $page_no != 1) {
            $this->current_db->where_not_in('A.ActivityID', $extra_params['ExcludeActivityID']);
        }        
        
        if ($filter_type == 12) {
            $this->current_db->group_by('A.ActivityID');
        }
        
        
        $compiled_query = $this->current_db->_compile_select(); // echo $compiled_query; die;

        $this->current_db->reset_query();

        //$result = $this->db->get();
       // echo $compiled_query; echo '===============================================================';die;
        return $compiled_query;
    }
    
    protected function public_feed_conditions() {
        //$this->db->where('A.StatusID', '2');
        $this->db->or_where('(A.Privacy', '1');
        //$this->db->where('ATY.StatusID', '2');
        $this->db->where("IF(A.ActivityTypeID=8,(
                (SELECT UserID FROM ".USERPRIVACY." WHERE PrivacyLabelKey='default_post_privacy' AND Value='everyone' AND UserID=A.ModuleEntityID AND A.ModuleID='3') is not null
            ),true)",NULL,FALSE);
       // $this->db->where("IF(A.ActivityTypeID=23 OR A.ActivityTypeID=24,A.ModuleID=3,true)",NULL,FALSE);
      //  $this->db->where("IF(A.ActivityTypeID=7,(SELECT GroupID FROM ".GROUPS." WHERE IsPublic IN(1, 0) AND GroupID=A.ModuleEntityID AND StatusID='2') is not null,true)",NULL,FALSE);
        
     //   $this->db->where("IF(A.ActivityTypeID=26,(SELECT ForumCategoryID FROM ".FORUMCATEGORY." WHERE Visibility IN(1) AND ForumCategoryID=A.ModuleEntityID AND StatusID='2') is not null,true)",NULL,FALSE);
        
        $this->db->where('A.IsFeatured = 1 ))', NULL, FALSE);
        //$this->db->order_by('A.ModifiedDate','DESC');
    }

    protected function wards_condition($condition, $activity_guid, $extra_params) {
        $ward_ids_str = '';
        if(isset($extra_params['WardIds']) && !empty($extra_params['WardIds']) && empty($activity_guid)) {
            $ward_ids = $extra_params['WardIds'];
            if(!in_array(1, $ward_ids)) {
                $ward_ids[] = 1;
            }
            $ward_ids_str = implode(',', $ward_ids);
            $ward_ids_str = trim($ward_ids_str, ',');
        }
        if (!empty($condition) && !empty($ward_ids_str)) {
           // $wards_conditions = " SELECT AW.ActivityID FROM ".ACTIVITYWARD." AW WHERE AW.WardID IN(" . $ward_ids_str . ")";
          //  $this->current_db->or_where("(A.ModuleID=3 AND  A.ActivityID IN ( $wards_conditions ))  ", NULL, FALSE);
        } else {
            if(!empty($ward_ids_str)) {
                $this->current_db->join(ACTIVITYWARD . ' AW', "AW.ActivityID=A.ActivityID AND AW.WardID IN(" . $ward_ids_str . ")");
            }
        }
    }

    protected function mentions_condition($module_id, $module_entity_id, $extra_params) {
        
        if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
            return;
        }
        
        $module_id = $this->current_db->escape_str($module_id);
        $module_entity_id = $this->current_db->escape_str($module_entity_id);
        
        $mentions_conditions = " SELECT MN.ActivityID FROM ".MENTION." MN WHERE MN.StatusID = 2 AND MN.ModuleID = $module_id AND MN.ModuleEntityID = $module_entity_id";
        //$this->current_db->or_where("( A.ActivityID IN ( $mentions_conditions ))  ", NULL, FALSE); // in case of public_feed_conditions
        $this->current_db->or_where("( A.ActivityID IN ( $mentions_conditions )))  ", NULL, FALSE);
        
        //$this->public_feed_conditions();
        
        
    }

    protected function reminder_condition($user_id, $filter_type, $activity_guid) {
        $select_array_extra = [];
        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            
            $select_array_extra[]="R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array_extra[]="IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";

            $this->current_db->_protect_identifiers = FALSE;
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
                    $this->current_db->where("(R.Status IS NULL OR R.Status='ACTIVE')", NULL, FALSE);
                }
            }
            $this->current_db->join(REMINDER . " R", $joincondition, $jointype);
            $this->current_db->_protect_identifiers = TRUE;
        }
        
        return $select_array_extra;
    }

    protected function privacy_condition(
            $user_id, $friend_followers_list, $activity_ids, $only_friend_followers, $friends, $follow, 
            $group_list, $category_list, $page_list, $event_list, $friend_of_friends, $quiz_list='', $tag_list='', $muted_tag_list='', $ward_ids_str=''
    ) {
       /* return array(
            'condition' => '',
            'privacy_condition' => '',
        );
        */
        //Activity Type 1 for followers, friends and current user
        //Activity Type 2 for followers and friends only
        //Activity Type 3 for follower and friend of UserID
        //Activity Type 8, 9, 10 for Mutual Friends Only
        //Activity Type 4, 7 for Group Members Only
        $condition = '';    
        $privacy_condition = '';    
        $case_array = array();
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        if ($friend_followers_list != '' && empty($activity_ids)) {
            $case_query = '';
            if(!empty($ward_ids_str)) {
                $mute_tag_query = '';
                if(!empty($muted_tag_list)) {
                    $mute_tag_query = " AND AW.ActivityID NOT IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (".$muted_tag_list.") GROUP BY EntityID HAVING COUNT(*) = 1)";
                }

                $wards_conditions = " SELECT AW.ActivityID FROM ".ACTIVITYWARD." AW WHERE AW.WardID IN(" . $ward_ids_str . ")".$mute_tag_query;
                $case_query = " OR  A.ActivityID IN ( ".$wards_conditions." )";
            }

            $tag_query = '';
            if(!empty($tag_list)) {
                $tag_query = " OR  A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" .$tag_list. "))";
            }

            

            $case_array[] = "A.ActivityTypeID IN (1,8,25) AND A.ModuleID=3
                                THEN 
                                A.UserID IN(" . $friend_followers_list . ")
                                OR " . $condition_part_two.$case_query.$tag_query;
        }
        
       /* $condition_part_one = '';
        
        $condition_part_three = '';
        $condition_part_four = '';
        $friends_implode_str = implode(',', $friends);
        if ($friend_followers_list != '' && empty($activity_ids)) {
            $case_array[] = "A.ActivityTypeID=1 AND A.ModuleID=3
                                THEN 
                                A.UserID IN(" . $friend_followers_list . ")
                                OR " . $condition_part_two";

            $case_array[] = "A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
            
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
        if (!empty($category_list)) {
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
        */

        if (!empty($quiz_list)) {
            /* $case_query = " A.ActivityTypeID IN (1,8) THEN A.ModuleID=3 ";
            if(!empty($ward_ids_str)) {
                $wards_conditions = " SELECT AW.ActivityID FROM ".ACTIVITYWARD." AW WHERE AW.WardID IN(" . $ward_ids_str . ")";
                $case_query = $case_query." AND  A.ActivityID IN ( ".$wards_conditions." )";
            }

            $case_array[] = $case_query;
            */
            $case_array[] = " A.ActivityTypeID IN (49,50) 
                                THEN 
                                    A.ModuleID=47 AND A.ModuleEntityID IN(" . $quiz_list . ") ";
        }
        if (!empty($case_array)) {
            $condition = " ( CASE WHEN " . implode(" WHEN ", $case_array) . " ELSE '' END ) ";
        }
        return array(
            'condition' => $condition,
            'privacy_condition' => $privacy_condition,
        );
    }

    protected function apply_filter(
        $filter_type, $friends, $post_type, $user_id, $activity_guid, $activity_ids, $mentions, $modules_allowed, $activity_type_allow, $tags, $feed_user, $show_media, 
        $is_media_exists, $search_key, $start_date, $end_date, $time_zone, $isPromoted, $ReminderDate, $tag_category_ids
    ) {
        if ($this->IsApp == 1)/* For Mobile */ {
            if ($filter_type == 'Connection') /* third param passed to get only folower list */ {
                $this->current_db->where_in('U.UserID', $friends);
            }
        }

        if ($post_type) {
           // $this->db->where_in('A.PostType', $post_type);
        }

        if ($filter_type == 7) {
            $this->current_db->where('A.StatusID', '19');
            $this->current_db->where('A.DeletedBy', $user_id);
        } else if ($filter_type == 10) {
            $this->current_db->where('A.StatusID', '10');
            $this->current_db->where('A.UserID', $user_id);
        } else if ($filter_type == 11) {
            //$this->current_db->where('A.IsFeatured', '1');
            $this->current_db->where('A.StatusID', '2');
        } else {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43)) {
                $this->current_db->_protect_identifiers = FALSE;
                $this->current_db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->current_db->_protect_identifiers = TRUE;
            }
            else if ($filter_type == 1 && empty($activity_ids)) {
              $this->current_db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID AND F.EntityType="ACTIVITY" AND F.StatusID=2 AND F.UserID='.$user_id);
              //$this->current_db->where('F.UserID', $user_id);
              //$this->current_db->where('F.StatusID', '2');
            } else if ($filter_type == 12 && empty($activity_ids)) {
                $this->current_db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID AND F.EntityType="ACTIVITY" AND F.StatusID=2');
                //$this->current_db->where('F.UserID', $user_id);
                //$this->current_db->where('F.StatusID', '2');                
            } else {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43)) {
                    $this->current_db->where("NOT EXISTS (SELECT 1 FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND ActivityID=A.ActivityID AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids) {
                $this->current_db->where_in('A.ActivityID', $activity_ids);
            }

            if ($mentions) {
                $join_condition = "MN.ActivityID=A.ActivityID AND MN.StatusID= 2 AND (";
                foreach ($mentions as $mention) {
                    $join_cond[] = "(MN.ModuleEntityID='" . $mention['ModuleEntityID'] . "' AND MN.ModuleID='" . $mention['ModuleID'] . "')";
                }
                $join_cond = implode(' OR ', $join_cond);
                $join_condition .= $join_cond . ")";

                $this->current_db->_protect_identifiers = FALSE;
                $this->current_db->join(MENTION . " MN", $join_condition, "join");
                $this->current_db->_protect_identifiers = TRUE;
            }

           /* $this->current_db->_protect_identifiers = FALSE;
            $this->current_db->join(MUTESOURCE . ' MS', 'MS.UserID="' . $user_id . '" AND ((MS.ModuleID=A.ModuleID AND MS.ModuleEntityID=A.ModuleEntityID) OR (MS.ModuleID=3 AND MS.ModuleEntityID=A.UserID AND A.ModuleEntityOwner=0))', 'left');
            $this->current_db->where('MS.ModuleEntityID is NULL', null, false);
            $this->current_db->_protect_identifiers = TRUE;
           */
            $this->current_db->where_in('A.ModuleID', $modules_allowed);
            $this->current_db->where_in('A.ActivityTypeID', $activity_type_allow);
            if ($activity_guid) {
                $this->current_db->where('A.ActivityGUID', $activity_guid);
            }

            //$this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->current_db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        }

        if ($tags) {
            if($tag_category_ids) {
                $this->current_db->where("A.ActivityID IN (SELECT ETC.EntityID FROM " . ENTITYTAGSCATEGORY . " ETC 
                    JOIN ".TAGSOFTAGCATEGORY." TTC ON TTC.TagCategoryID = ETC.TagCategoryID AND TTC.TagID IN (" . implode(',', $tags) . ") 
                    WHERE ETC.EntityType='ACTIVITY' AND ETC.TagCategoryID IN (" . implode(',', $tag_category_ids) . "))", null, false);
            } else {
                if(ENVIRONMENT=="production") {
                    if(in_array(870, $tags)) {
                        $this->current_db->where('A.PostType', 2);
                        $this->current_db->group_start();
                        $this->current_db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (20, 872, 873) GROUP BY EntityID HAVING COUNT(*) = 3)", null, false);                    
                        $this->current_db->or_where('A.Solution', 2);
                        $this->current_db->group_end();
                    } else if(in_array(871, $tags)) {
                        $this->current_db->where('A.PostType', 2);
                        $this->current_db->group_start();
                        $this->current_db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' GROUP BY EntityID HAVING COUNT(`TagID` = 20 OR NULL) > 0 AND COUNT(`TagID` = 873 OR NULL) = 0)", null, false);                    
                        $this->current_db->or_where('A.Solution <', 2);
                        $this->current_db->group_end();
                    } else {
                        $this->current_db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" . implode(',', $tags) . "))", null, false);
                    }
                } else {
                    if(in_array(870, $tags)) {
                        $this->current_db->where('A.PostType', 2);
                        $this->current_db->group_start();
                        $this->current_db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (20, 872, 873) GROUP BY EntityID HAVING COUNT(*) = 3)", null, false);                    
                        $this->current_db->or_where('A.Solution', 2);
                        $this->current_db->group_end();
                    } else if(in_array(871, $tags)) {
                        $this->current_db->where('A.PostType', 2);
                        $this->current_db->group_start();
                        $this->current_db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' GROUP BY EntityID HAVING COUNT(`TagID` = 20 OR NULL) > 0 AND COUNT(`TagID` = 873 OR NULL) = 0)", null, false);                    
                        $this->current_db->or_where('A.Solution <', 2);
                        $this->current_db->group_end();
                    } else {
                        $this->current_db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" . implode(',', $tags) . "))", null, false);
                    }
                }
            }           
            
        } else {

            if (!in_array($filter_type, array(1, 11, 12))) {
                $this->current_db->where('A.IsShowOnNewsFeed', 0);
            } else if(empty($activity_guid)) {
                if (in_array($filter_type, array(1, 11, 12))) {
                } else {
                    $this->current_db->where("A.ActivityID NOT IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID=6)", null, false);
                }               
            }
        }

        if ($filter_type == 2) {
            $this->current_db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->current_db->where('F.EntityType', 'Activity');
            $this->current_db->where('F.UserID', $user_id, FALSE);
            $this->current_db->where('F.StatusID', 2);
        }
        if ($feed_user) {
            if (is_array($feed_user)) {
                $this->current_db->where_in('U.UserID', $feed_user);
            } else {
                $this->current_db->where('U.UserID', $feed_user);
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
            $this->current_db->where('A.IsMediaExist', $is_media_exists);
        }

        if (!empty($search_key)) {
            $search_key = $this->current_db->escape_like_str($search_key);
            $this->current_db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        
        if ($filter_type == 3) {
            if ($ReminderDate) {
                $rd_data = array();
                foreach ($ReminderDate as $rd) {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->current_db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }
        
        if ($start_date) {
            $this->current_db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date) {
            $this->current_db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }
        
        if($isPromoted) {
            $this->current_db->where('A.IsPromoted', 1);
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
        if(empty($exclude_ids)) {
            $exclude_ids = array();
        } else {
            $exclude_ids = explode(',', trim($exclude_ids));
        }
        
        
        $exclude_ids[] = 0;

        $hide_activity = $this->activity_hide_model->get_user_hide_activity();
        
        //$exclude_ids = implode(',', $exclude_ids);
        $exclude_ids = array_merge($exclude_ids, $hide_activity);
        $exclude_ids = array_unique($exclude_ids);
        
        if (!empty($rules)) {
            $this->current_db->or_where_not_in('((A.ActivityID', explode(',', $exclude_ids));
        } else if ($exclude_ids) {
            //$this->db->where_not_in('A.ActivityID', explode(',', $exclude_ids));
           // $exclude_ids = trim($exclude_ids, ',');
           // $this->current_db->or_where("(A.ActivityID NOT IN($exclude_ids) ", Null, false );
           $exclude_id_chunks = array_chunk($exclude_ids,50);
           if(!empty($exclude_id_chunks)) {
            $this->current_db->where(" ( 1", NULL, FALSE);
                $this->current_db->group_start();
                foreach ($exclude_id_chunks as $chunk_key => $chunk_arr) {
                    $this->current_db->where_not_in('A.ActivityID', $chunk_arr);
                }
                $this->current_db->group_end();
            }
        }

        if (empty($activity_ids)) {
            if (!empty($condition)) {
                $condition = '('.$condition;
                $this->current_db->where($condition, NULL, FALSE);
            }  else {
               // $this->current_db->where('((A.ModuleID', '3'); //in case of $privacy_condition not empty 
               if(API_VERSION == "v5" ){
                $this->current_db->where_in('(A.ModuleID', array(3,47));
               }else {
                $this->current_db->where('(A.ModuleID', '3');
               }
               
            } 
            
            $this->wards_condition($condition, $activity_guid, $extra_params);

            if ($privacy_condition) {
                $privacy_condition = $privacy_condition.')';
                
                if(isset($extra_params['ReminderSort']) && $extra_params['ReminderSort'] == 1) {
                    $privacy_condition = $privacy_condition.')';
                }
                
                $this->current_db->where($privacy_condition, null, false);
            }
            
            if(empty($activity_guid)) {
                $this->mentions_condition(3, $user_id, $extra_params);
            }
            
        }
        
        // Set reminder condition
        $select_array_extra = $this->reminder_condition($user_id, $filter_type, $activity_guid);
        if(isset($select_array_extra[0])) $select_array[] = $select_array_extra[0];
        if(isset($select_array_extra[1])) $select_array[] = $select_array_extra[1];
        
        if (!empty($rules)) {
            $this->current_db->where(" 1 )) )", NULL, FALSE);
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
                $this->current_db->where('R.ReminderDateTime < "'.get_current_date('%Y-%m-%d %H:%i:%s').'"', NULL, FALSE);
                $this->current_db->where('R.ReminderID IS NOT NULL', NULL, FALSE);
                $this->current_db->order_by("ReminderDateTime DESC");
                
                return;
            } 
            
            if(!isset($extra_params['UnionApply'])) {
                $this->current_db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            }
            
            
            if(!$count_only && isset($extra_params['UnionApply'])) {
                $this->current_db->where('( R.ReminderDateTime IS NULL OR R.ReminderDateTime > "'.get_current_date('%Y-%m-%d %H:%i:%s').'" )', NULL, FALSE);
                //$this->db->where('R.ReminderID IS NULL', NULL, FALSE);
            }
        }
        
        
        
        if ($feed_sort_by == 1 && !$count_only) {
            $this->current_db->order_by('A.ModifiedDate', 'DESC');
        } else if ($feed_sort_by == 2 && !$count_only) {
            //$this->current_db->order_by('A.PromotedDate', 'DESC');   
            $this->current_db->order_by('A.CreatedDate', 'DESC');         
        } else if ($feed_sort_by == 3 && !$count_only) {
            $this->current_db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfCommentReplies+A.NoOfViews)', 'DESC');
        } else if ($feed_sort_by == 4 && !$count_only) {
            $this->current_db->order_by('A.NoOfFavourites', 'DESC');
            $this->current_db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfCommentReplies+A.NoOfViews)', 'DESC');
        } else if ($feed_sort_by == 5 && !$count_only) {
            $this->current_db->order_by('A.CreatedDate', 'DESC');
        } else if(!$count_only){
            $this->current_db->order_by('A.ModifiedDate', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids) && !$count_only) {
            $this->current_db->_protect_identifiers = FALSE;
            $this->current_db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->current_db->_protect_identifiers = TRUE;
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
            
            $where_cnd = '';
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
                                    
            $this->current_db->where("($where_cnd)", NULL, FALSE);                            
        }
        
        if(!empty($extra_params['emailer']['ExcludeActivities'])) {
            $this->current_db->where_not_in('A.ActivityID', $extra_params['emailer']['ExcludeActivities']);
        }
        
        
        if(!empty($extra_params['emailer']['ActivitiesWithinDays'])) {
            $days_diff_date = get_current_date('%Y-%m-%d', $extra_params['emailer']['ActivitiesWithinDays']);            
            $this->current_db->where(" (A.CreatedDate >= '$days_diff_date' )", NULL, FALSE);
        }
        
        
        $this->current_db->where_in('A.ActivityTypeID', $extra_params['emailer']['AllowedActivityTypeID']);        
        $this->current_db->where('A.UserID != ', $extra_params['emailer']['UserID']);
        
        $this->current_db->order_by('A.TotalLikeViewComment', 'DESC');            
        $this->current_db->order_by('A.ModifiedDate', 'DESC');            
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
        $select_array[]= 'ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture, U.IsVIP, U.IsAssociation';
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
        $select_array[]= 'ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture, U.IsVIP, U.IsAssociation';
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
        //$this->benchmark->mark('execution_start');
        if(isset($extra_params['AR']) && $extra_params['AR']==1) {
            $this->load_archive_db_instance(); 
            
        }
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
        
        // echo $final_feed_query; echo '=============================================================='; die;

        $result = $this->current_db->query($final_feed_query);
        //echo $this->db->last_query();die;
        $feed_result = $result->result_array();  //print_r(count($feed_result)); die;
        
        
       // $this->benchmark->mark('execution_ends');
       // log_message('error', "Feed Query Time  => ".$this->benchmark->elapsed_time('execution_start', 'execution_ends'));
      //  $this->benchmark->mark('execution_start');
  
        if ($count_only) {
            $total_count = 0;
            foreach ($feed_result as $total_row){
                //echo $final_feed_query; echo '==============================================================';
                $total_count += (int)!empty($total_row['TotalRow']) ? $total_row['TotalRow'] : 0;
            }
            
            return $total_count;
        }
        
        $return = $this->activity_result_filter_model->filter_result_set($feed_result, $page_no, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id, $this,$is_single_activity);

        return $return;
    }
 
}
