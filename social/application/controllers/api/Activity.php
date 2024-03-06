<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity extends Common_API_Controller {

    /**
     * Activity constructor.
     * @param bool $bypass
     */
    function __construct($bypass = false) {
        parent::__construct($bypass);
        $this->load->model(array(
            'events/event_model',
            'pages/page_model',
            'users/user_model',
            'group/group_model',
            'media/media_model',
            'users/friend_model',
            'activity/activity_model',
            'favourite_model',
            'subscribe_model',
            'notification_model',
            'forum/forum_model',
            'activity/activityrule_model',
            'polls/polls_model'
        ));
    }

    /**
     *
     */
    public function get_widgets_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $this->load->model('privacy/privacy_model');
        $event_module_disabled = $this->settings_model->isDisabled(14);

        $type = isset($data['Type']) ? $data['Type'] : 'Newsfeed';
        $profile_user = isset($data['UserGUID']) ? get_detail_by_guid($data['UserGUID'], 3) : $user_id;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
        $module_entity_id = isset($data['ModuleEntityGUID']) ? get_detail_by_guid($data['ModuleEntityGUID'], $data['ModuleID']) : $profile_user;

        $result = array();

        if ($type == 'Newsfeed') {
            $result['NewsfeedSettings'] = $this->privacy_model->news_feed_setting_details($user_id);

            if (!$this->settings_model->isDisabled(1)) { // Check if group module is enabled
                $result['TopGroups']['Data'] = $this->group_model->top_group($user_id, $user_id, FALSE, '', 1, 5, '', 'LastActivity', 'DESC');
                $result['SuggestedGroups']['Data'] = $this->group_model->suggestions($user_id, 0, 5, FALSE, array());
            }
            //$result['SuggestedGroups']['TotalRecords'] = $this->group_model->suggestions($user_id, "", "", TRUE, array());

            if (!$this->settings_model->isDisabled(18)) { // Check if page module is enabled
                $resultArr = $this->page_model->suggestions($user_id, 0, 5);
                $tempResults = array();
                if (isset($resultArr['data']) && $resultArr['data']) {
                    foreach ($resultArr['data'] as $temp) {
                        if ($temp['ProfilePicture'] != "" && ($this->DeviceTypeID && $this->DeviceTypeID == 1)) {
                            $temp['PageIcon'] = "upload/profile/220x220/" . $temp['ProfilePicture'];
                            $temp['ProfilePicture'] = "upload/profile/220x220/" . $temp['ProfilePicture'];
                        }

                        $tempResults[] = $temp;
                    }
                }
                $result['SuggestedPages']['Data'] = $tempResults;

                $result['TopPages'] = $this->page_model->get_top_user_pages($user_id, '', 1, 5, $user_id);
            }

            /*            if($this->IsApp == 1){
              $result['SuggestedPages']['TotalRecords'] = $resultArr['total_records'];

              } */
            if (!$event_module_disabled) {
                $result['UpcomingEvents'] = $this->event_model->get_upcoming_events($user_id, $user_id);
            }

            if (!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
                $pymk = $this->friend_model->grow_user_network($user_id, array(), 1, 20, '', array());
                $result['PeopleYouMayKnow']['Data'] = $pymk['data'];
            } else {
                $pymf = $this->friend_model->get_users_to_follow($user_id, array('limit' => 20));
                $result['PeopleYouMayFollow']['Data'] = $pymf;
            }

            //$result['PollsAboutToClose'] = $this->polls_model->getFeedActivities($user_id, 1, 3, 1, 0, 15, 2, false, false, false, 0, 0, array(), '', array(), $user_id, 3, array(25));
            //$result['NewMembers'] = $this->friend_model->get_new_members($user_id, 5,0); 
        } else if ($type == 'Wall') {
            $result['UserInterest'] = $this->user_model->get_user_interest($profile_user, 1, 3, false, 0);
            if (!$event_module_disabled) {
                $result['UpcomingEvents'] = $this->event_model->get_upcoming_events($profile_user, $user_id);
            }

            if (!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
                $result['Connections'] = $this->friend_model->connections($user_id, 'Friends', $profile_user, '', 1, 4);
            }

            if (!$this->settings_model->isDisabled(1)) { // Check if group module is enabled
                $result['TopGroups']['Data'] = $this->group_model->top_group($user_id, $profile_user, FALSE, '', 1, 5, '', 'LastActivity', 'DESC');
            }

            if (!$this->settings_model->isDisabled(18)) { // Check if page module is enabled
                $result['TopPages'] = $this->page_model->get_top_user_pages($profile_user, '', 1, 5, $user_id);
                $result['EntitiesIFollow'] = array();
                if ($profile_user == $user_id) {
                    $result['EntitiesIFollow'] = $this->user_model->entities_i_follow($user_id, 1, 5);
                }
            }

            $result['RecentActivities'] = $this->activity_model->get_recent_activities($profile_user, $user_id);
        } else if ($type == 'GroupWall' && !$this->settings_model->isDisabled(1)) {
            $result['Members'] = $this->group_model->members($module_entity_id, $user_id, '', '', 1, 4, '', 'Name', TRUE, '', '');
            $result['MembersCount'] = $this->group_model->members($module_entity_id, $user_id, TRUE, '', '', '', '', 'Name', TRUE, '', '');

            if (!$this->settings_model->isDisabled(10)) { // Check if friend module is enabled
                $result['FriendCount'] = $this->group_model->members($module_entity_id, $user_id, TRUE, '', '', '', 'Friends', 'Name', TRUE, '', '');
            }

            $result['Discussion'] = $this->activity_model->get_popular_discussions($user_id, $module_id, $module_entity_id, 7, 1, 5);
            $result['SimilarGroup'] = $this->group_model->similar_groups($user_id, FALSE, 1, 2, array(0), $module_entity_id, TRUE);
        }

        $return['Data'] = $result;

        $this->response($return);
    }

    /**
     *
     */
    public function hide_announcement_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $this->form_validation->set_rules('EntityGUID', 'Entity GUID', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $entity_guid = isset($data['EntityGUID']) ? $data['EntityGUID'] : '';
                $remove_for_all = isset($data['RemoveForAll']) ? $data['RemoveForAll'] : false;

                $entity_id = get_detail_by_guid($entity_guid, 0);
                $this->activity_model->hide_announcement($user_id, $entity_id, $remove_for_all);
            }
        }
        $this->response($return);
    }

    /**
     *
     */
    public function get_announcement_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : '';

        $this->activity_model->set_block_user_list($user_id, 3);
        $this->user_model->set_user_time_zone($user_id);
        //$this->user_model->set_user_profile_url($user_id);

        //$this->user_model->set_friend_followers_list($user_id);
        //$this->group_model->set_user_group_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_model->set_user_activity_archive($user_id);
        $this->privacy_model->set_privacy_options($user_id);
       // $this->group_model->set_visible_group_list($user_id);
       // $this->forum_model->set_visible_category_list($user_id);
        if ($module_id == '34' && !$module_entity_id) {
            $return['Data'] = $this->activity_model->get_community_announcements($user_id);
        } else if ($module_id == '1' || $module_id == '34') {
            $return['Data'] = $this->activity_model->get_announcements($module_id, $module_entity_id, $user_id);
        } else {
            //$this->forum_model->set_user_category_list($user_id);
            //$this->group_model->set_user_categoty_group_list($user_id);
            //$this->event_model->set_user_joined_events($user_id);
            //$this->page_model->set_feed_pages_condition($user_id);
//            $FriendFollowersList = $this->user_model->get_friend_followers_list();
//            if (!empty($FriendFollowersList)) {
//                $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
//            }
            $return['Data'] = $this->activity_model->get_newsfeed_announcements($user_id);
        }
        $this->response($return);
    }

    /**
     *
     */
    public function get_newsfeed_announcement_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $this->activity_model->set_block_user_list($user_id, 3);
        $return['Data'] = $this->activity_model->get_newsfeed_announcements($user_id);
        $this->response($return);
    }

    /**
     * Function Name: index
     * @param ProfileID,PageNo,PageSize,ActivityTypeID,EntityID,WallType,ActivityGuID,AllActivity
     * Description: Get list of activity according to input conditions
     */
    public function index_post() {
        /* Define variables - starts */
        $return = $this->return;
        $return['TotalRecords'] = 0; /* added by gautam */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $role_id = isset($this->RoleID) ? $this->RoleID : '';
        $dummy_users_only = isset($data['DummyUsersOnly']) ? $data['DummyUsersOnly'] : 0;
        if (!$dummy_users_only) {
            if ($this->form_validation->run('api/activity') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
                $this->response($return);
            }
        }

        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        if ($activity_guid) {
            $data['ModuleID'] = 19;
        }

        $entity_guid = isset($data['EntityGUID']) ? $data['EntityGUID'] : 0;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;

        if ($module_id == 3 && $this->LoggedInGUID == $entity_guid) {
            $entity_id = $user_id;
        } else {
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
        }

        $post_type = isset($data['PostType']) ? $data['PostType'] : 0;
        $feed_sort_by = isset($data['FeedSortBy']) ? $data['FeedSortBy'] : 1;
        $is_media_exists = isset($data['IsMediaExists']) ? $data['IsMediaExists'] : 2;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
        $filter_type = isset($data['ActivityFilterType']) ? $data['ActivityFilterType'] : 0;
        $feed_user = isset($data['FeedUser']) ? $data['FeedUser'] : 0;
        $user_feed = 0;
        if ($post_type && is_array($post_type) && count($post_type) == 1 && $post_type[0] == 0) {
            $post_type = 0;
        }
        if ($feed_user) {
            if (is_array($feed_user)) {
                $user_feed = array();
                foreach ($feed_user as $uid) {
                    $user_feed[] = get_detail_by_guid($uid, 3);
                }
            } else {
                $user_feed = get_detail_by_guid($feed_user, 3);
            }
        }
        $feed_user = $user_feed;
        $search_keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $start_date = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';

        $end_date = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
        $reminder_date = (isset($data['ReminderFilterDate']) && !empty($data['ReminderFilterDate'])) ? $data['ReminderFilterDate'] : array();

        $all_activity = isset($data['AllActivity']) ? $data['AllActivity'] : 0;
        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;

        $mentions = isset($data['Mentions']) ? $data['Mentions'] : [];
        $as_owner = isset($data['AsOwner']) ? $data['AsOwner'] : 0;
        $activity_type_filter = !empty($data['ActivityFilter']) ? $data['ActivityFilter'] : array();
        $comment_guid = isset($data['CommentGUID']) ? $data['CommentGUID'] : '';
        //View Entity Tags
        $view_entity_tags = isset($data['ViewEntityTags']) ? $data['ViewEntityTags'] : 0;
        $tags = isset($data['Tags']) ? $data['Tags'] : array();

        $comment_id = '';
        if (!empty($comment_guid)) {
            $comment_id = get_detail_by_guid($comment_guid, 20);
        }
        if ($mentions) {
            foreach ($mentions as $key => $value) {
                $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
            }
        }

        $is_sticky = isset($data['IsSticky']) ? $data['IsSticky'] : 0;

        if ($is_sticky == 1) {
            $post_type = 0;
            $feed_sort_by = 1;
            $is_media_exists = 2;
            $filter_type = 0;
            $feed_user = 0;
            $search_keyword = '';
            $start_date = '';
            $end_date = '';
            $reminder_date = array();
            $mentions = [];
            $as_owner = 0;
            $activity_type_filter = array();
            $view_entity_tags = 0;
            $tags = array();
            $comment_id = '';
        }

        $this->user_model->set_user_time_zone($user_id);
        //$this->user_model->set_user_profile_url($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        //$this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_user_group_list($user_id);
        //$this->forum_model->set_user_category_list($user_id);
        //$this->group_model->set_user_categoty_group_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_model->set_user_activity_archive($user_id);

        if ($dummy_users_only) {
            $activity = $this->activity_model->getDummyUserActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags);
            $return['TotalRecords'] = $this->activity_model->getDummyUserActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags);
        } else if ($all_activity == 1) {
            
            if(!empty($tags)) {
                $this->load->model(array('tag/tag_model'));
                $tags = $this->tag_model->get_category_tag_ids($tags);
            }
            
            $this->privacy_model->set_privacy_options($user_id);
            //$this->event_model->set_user_events($user_id);
            //$this->page_model->set_feed_pages_condition($user_id);

            // Set user activity total for rule check
            $this->activityrule_model->setUserActivityTotal($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, $post_type, $tags);

            // Get rules for this user
            $rules = $this->activityrule_model->getActivityRules($user_id);

//            $FriendFollowersList = $this->user_model->get_friend_followers_list();
//            if (!empty($FriendFollowersList)) {
//                $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
//            }
            
            $activity = $this->activity_model->getFeedActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, $rules, $data);
            
            if ($page_no == '1') {
                $return['TotalRecords'] = $this->activity_model->getFeedActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), 1, 2, $post_type, $tags, $rules, $data);                
                $is_admin = $this->user_model->is_super_admin($user_id, 1);
                $return['IsAdmin'] = $is_admin;
            }

            $return['TotalFavouriteRecords'] = 0;
            $return['TotalReminderRecords'] = 0;
            if (count($activity) > 0) {
                $return['TotalReminderRecords'] = 1;
            }
        } else {
            $module_entity_guid = $this->LoggedInGUID;
            $login_type = 'user';
            $return['TotalFlagRecords'] = 0;
            $entity_module_id = 3;
            
            $module_entity_id = get_detail_by_guid($module_entity_guid, $entity_module_id);
            $activity = $this->activity_model->getActivities(
                    $entity_id, $module_id, $page_no, $page_size, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, false, 'ALL', $activity_type_filter, $module_entity_id, $entity_module_id, $comment_id, $view_entity_tags, $role_id, $post_type, $tags, $data);
            
            if ($page_no == '1') {
                $return['TotalRecords'] = $this->activity_model->getActivities(
                        $entity_id, $module_id, 0, 0, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, true, 'ALL', array(), '', '', '', '', 2, $post_type, $tags, $data);
                if ($module_id == 34) {
                    $data['onlyFeedQuery'] = 1;
                    $activityFeedQuery = $this->activity_model->getActivities(
                            $entity_id, $module_id, 0, 0, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, true, 'ALL', array(), '', '', '', '', 2, $post_type, $tags, $data);
                    //$this->load->model(array('tag/tag_model'));
                    $user_id = $this->UserID;
                    //$return['PopularTags'] = $this->tag_model->get_popular_tags(array('FeedQuery' => $activityFeedQuery), $user_id);
                }
            }
            $return['TotalFavouriteRecords'] = 0;
            if ($module_id == 18) {
                $return['TotalFlagRecords'] = $this->activity_model->getActivities(
                        $entity_id, $module_id, 0, 0, $user_id, $feed_sort_by, 2, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, true, 'ALL', array(), '', '', '', '', 2, $post_type, $tags, $data);
            }
        }
        /* Define variables - ends */

        if (count($activity) > 0) {
            $return['Data'] = $activity;
        }

        $return['PageSize'] = $page_size;
        $return['PageNo'] = $page_no;   //print_r($rules); die;
        $return['Welcome'] = !empty($rules['Welcome']) ? $rules['Welcome'] : '';

        $return['LoggedInProfilePicture'] = $this->LoggedInProfilePicture;
        $return['LoggedInName'] = $this->LoggedInName;

        $return = $this->setActivityDetailsData($return, $activity_guid);
        $return['ActivityGUID'] = '13c00d86-cc78-ae27-f6c7-06e6b9d42941';
        
        $this->response($return);
    }

    function setActivityDetailsData($return, $activity_guid) {
        if (!$activity_guid) {
            return $return;
        }

        if (empty($return['Data'][0])) {
            return $return;
        }

        $return['Data'][0]['LoggedInUserDefaultPrivacy'] = $this->privacy_model->get_default_privacy($this->UserID);
        return $return;
    }

    /**
     *
     */
    function public_posts_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;

        $post_type = isset($data['PostType']) ? $data['PostType'] : 0;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = isset($data['ModuleEntityGUID']) ? get_detail_by_guid($data['ModuleEntityGUID'], $data['ModuleID']) : 0;
        $feed_sort_by = isset($data['FeedSortBy'])?$data['FeedSortBy']:'';

        // Get rule for this request
        $location = $this->activityrule_model->get_location_by_ip($_SERVER['REMOTE_ADDR']);
        $rules = $this->activityrule_model->getActivityRules(0, 0, FALSE, $location);

        $return['Data'] = $this->activity_model->get_public_feed('', $module_id, $module_entity_id, $page_no, $page_size, FALSE, $rules, array('feed_sort_by'=>$feed_sort_by), $post_type);
        $return['PageSize'] = $page_size;
        $return['PageNo'] = $page_no;
        $return['TotalRecords'] = 0;
        if ($page_no == '1') {
            $return['TotalRecords'] = $this->activity_model->get_public_feed('', $module_id, $module_entity_id, $page_no, $page_size, true, $rules, array(), $post_type);
            if ($module_id == 34) {
                // Get popular tags
                $public_feed_query = $this->activity_model->get_public_feed('', $module_id, $module_entity_id, $page_no, $page_size, true, $rules, array('onlyFeedQuery' => 1), $post_type);
                $this->load->model(array('tag/tag_model'));
                $user_id = $this->UserID; //echo $activityFeedQuery; die;
                $return['PopularTags'] = $this->tag_model->get_popular_tags(array('FeedQuery' => $public_feed_query), $user_id);
            }
        }
        $this->response($return);
    }

    /**
     *
     */
    function public_feed_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;

        if ($this->form_validation->run('api/public_feed') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $post_type = isset($data['PostType']) ? $data['PostType'] : 0;
            $activity_guid = $data['ActivityGUID'];
            $return['Data'] = $this->activity_model->get_public_feed($activity_guid, 0, 0, 1, 10, false, array(), array(), $post_type);
            $return['PageSize'] = 1;
            $return['PageNo'] = 1;
            $return['TotalRecords'] = 0;
            if ($return['Data']) {
                $return['TotalRecords'] = 1;
            }
        }
        $this->response($return);
    }

    /**
     * [get_popular_tags]
     * @param  [int]       $module_id
     * @param  [int]       $module_entity_id
     * @param  [int]       $page_no
     * @param  [int]       $page_size
     * @return [Array]
     */
    function get_popular_tags_post() {
        $return = $this->return;
        $data = $this->post_data;

        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : '';
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 5;
        $return['Data'] = $this->activity_model->get_popular_tags($module_id, $module_entity_id, $page_no, $page_size);
        $this->response($return);
    }

    /*
      |--------------------------------------------------------------------------
      | Use to createWallPost
      | @Inputs: ModuleID, ModuleEntityGUID, Visibility, Commentable
      |--------------------------------------------------------------------------
     */

    function createWallPost_post() {
        /* Define variables - starts */
        $Return = $this->return;
        /* Define variables - ends */

        $UserID = $this->UserID;
        $role_id = isset($this->RoleID) ? $this->RoleID : '';

        /* Gather Inputs - starts */
        $Data = $this->post_data;
        if (isset($Data)) {
            $validation_rule = $this->form_validation->_config_rules['api/activity/createWallPost'];
            
            $strippedContent = strip_tags($Data['PostContent'], '<img><iframe>');
            if(empty($strippedContent) && empty($Data['Media'])) {                
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = 'Please enter post content or select media.';
                $this->response($Return);
            }
            
            if (isset($Data['PostContent']) == '') {
                $validation_rule[] = array(
                    'field' => 'Media[]',
                    'label' => 'wall media',
                    'rules' => 'trim|required'
                );
            }

            if (isset($Data['PostType']) && $Data['PostType'] == '8') {
                $validation_rule[] = array(
                    'field' => 'Facts',
                    'label' => 'facts',
                    'rules' => 'trim|required'
                );
            }

            if(isset($Data['PostType']) && $Data['PostType'] == '9')
            {
                $validation_rule[] = array(
                    'field' => 'PostTitle',
                    'label' => 'post title',
                    'rules' => 'trim|required|max_length[50]'
                );

                $validation_rule[] = array(
                    'field' => 'PostContent',
                    'label' => 'post content',
                    'rules' => 'trim|required|max_length[100]'
                );

                $validation_rule[] = array(
                    'field' => 'Params[ButtonText]',
                    'label' => 'button text',
                    'rules' => 'trim|required|max_length[20]'
                );
            }

            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = $error;
            } else { /* Validation - ends */

                if (isset($Data['PostType']) && $Data['PostType'] == '8') {
                    if (!$this->user_model->is_super_admin($UserID)) {
                        $error = $this->form_validation->rest_first_error_string();
                        $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $Return['Message'] = lang('super_admin_required');
                        return $Return;
                    }
                }

                $activity_id = '';
                $is_edit = FALSE;
                if (isset($Data['ActivityGUID']) && !empty($Data['ActivityGUID'])) {
                    $activity_guid = $Data['ActivityGUID'];
                    $activity_id = get_detail_by_guid($activity_guid);
                    $is_edit = TRUE;
                }

                // Send 1 if draft post is going to be published else FALSE
                $publish_post = isset($Data['PublishPost']) ? $Data['PublishPost'] : FALSE;

                // Status=10 for draft post 
                $status = isset($Data['Status']) ? $Data['Status'] : 2;

                // IsAnonymous = 1|0 
                $is_anonymous = isset($Data['IsAnonymous']) ? $Data['IsAnonymous'] : 0;

                $Visibility = (!empty($Data['Visibility'])) ? $Data['Visibility'] : 1;
                if ($this->settings_model->isDisabled(10)) {
                    $Visibility = 1;
                }
                $Commentable = $Data['Commentable'];
                $ModuleID = isset($Data['ModuleID']) ? $Data['ModuleID'] : 3;
                $ModuleEntityGUID = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : get_detail_by_id($Data['UserID'], 3);
                $ModuleEntityID = get_detail_by_guid($ModuleEntityGUID, $ModuleID);
                $PostContent = isset($Data['PostContent']) ? $Data['PostContent'] : '';
                $Media = isset($Data['Media']) ? $Data['Media'] : array();
                $files = isset($Data['Files']) ? $Data['Files'] : array();
                $IsMediaExist = ($Media) ? '1' : '0';
                $AllActivity = isset($Data['AllActivity']) ? $Data['AllActivity'] : 0;
                $ModuleEntityOwner = 0;
                $post_as_module_id = isset($Data['PostAsModuleID']) ? $Data['PostAsModuleID'] : 3;
                $PostAsModuleEntityGUID = isset($Data['PostAsModuleEntityGUID']) ? $Data['PostAsModuleEntityGUID'] : 0;
                $canPost = TRUE;
                $Links = isset($Data['Links']) ? $Data['Links'] : array();
                $entity_tags = isset($Data['EntityTags']) ? $Data['EntityTags'] : array();
                $post_title = isset($Data['PostTitle']) ? $Data['PostTitle'] : '';
                $post_type = isset($Data['PostType']) ? $Data['PostType'] : 1;
                $NotifyAll = isset($Data['NotifyAll']) ? $Data['NotifyAll'] : 0;
                $facts = isset($Data['Facts']) ? $Data['Facts'] : '';
                $params = isset($Data['Params']) ? $Data['Params'] : array();
                $contest_date = isset($Data['ContestDate']) ? $Data['ContestDate'] : '';
                $IsIntro = isset($Data['IsIntro']) ? $Data['IsIntro'] : 0;
                $summary = isset($Data['Summary']) ? $Data['Summary'] : '' ;
                $taged_user = isset($Data['TagedUser']) ? json_encode($Data['TagedUser']) : '' ;
                if($post_type != '4')
                {
                    $summary = '';
                }
                $is_featured = 0;
                if ($contest_date) {
                    $c_date1 = explode(' ', $contest_date);
                    $c_date2 = explode('/', $c_date1[0]);
                    if (isset($c_date1) && isset($c_date2)) {
                        $contest_date = $c_date2[2] . '-' . $c_date2[0] . '-' . $c_date2[1] . ' ' . $c_date1[1] . ':00';
                    }
                }

                if ($ModuleID == 3 && $UserID != $ModuleEntityID) {
                    $value = $this->privacy_model->get_value($ModuleEntityID, 'default_post_privacy');
                    if ($Visibility == 0) {
                        $Visibility = 1;
                    }
                    if ($value == 'friend' && $Visibility == 1) {
                        $Visibility = 3;
                    }
                    if ($value == 'self') {
                        $Visibility = 4;
                    }
                }

                $analytic_login_id = NULL;
                if (isset($Data[AUTH_KEY])) {
                    $analytic_login_id = get_analytics_id($Data[AUTH_KEY]);
                }

                if ($ModuleID == 3 && check_blocked_user($UserID, $ModuleID, $ModuleEntityID)) {
                    $canPost = FALSE;
                }
                if ($ModuleID == 18) {
                    $canPost = $this->page_model->get_page_post_permission($UserID, $ModuleEntityGUID);
                }
                if ($ModuleID == 1) {
                    $permission = check_group_permissions($UserID, $ModuleEntityID, FALSE);
                    $canPost = $permission['CanPostOnWall'];
                    if ($permission['CanCreateKnowledgeBase'] == 0 && $post_type == 4) {
                        $canPost = FALSE;
                    }
                }
                if ($ModuleID == 34 && $activity_id == '') { //auto follow
                    $catDetail = $this->forum_model->category_details($ModuleEntityID,$UserID);
                    $forumVisible = get_detail_by_guid($catDetail['ForumGUID'],33,'Visible');
                    if(!$forumVisible){
                        $is_featured = 1;
                    }
                    $permission = $this->forum_model->check_forum_category_permissions($UserID, $ModuleEntityID, FALSE);
                    //follow this category if not following
                    if (!$permission['IsMember']) {
                        $members = array(array('ModuleID' => 3, 'ModuleEntityID' => $UserID));
                        $this->forum_model->add_category_members($members, $ModuleEntityID, $UserID);
                        if (CACHE_ENABLE) {
                            $this->cache->delete('user_categories_' . $UserID);
                        }
                        $this->forum_model->set_user_category_list($UserID);
                    }
                    /* if introduction post */
                    if ($IsIntro) {
                        $this->cache->delete('user_profile_' . $UserID);
                    }
                }
                if ($canPost) {
                    $post_as_module_entity_id = ($PostAsModuleEntityGUID) ? get_detail_by_guid($PostAsModuleEntityGUID, $post_as_module_id) : $UserID;
                    $media_cout = $files_count = 0;
                    if (isset($Media) && !empty($Media)) {
                        $media_cout = $media_cout + count($Media);
                    }
                    if (isset($files) && !empty($files)) {
                        $files_count = $files_count + count($files);
                    }

                    if ($post_type == '8' || $post_type == '9') {
                        if ($post_type == '9') {
                            $is_featured = 1;
                        }
                        $Visibility = 1;
                    }
                    //Insert post and get post id
                    $ActivityDetails = $this->activity_model->createPost($PostContent, $ModuleID, $ModuleEntityID, $UserID, $IsMediaExist, $media_cout, $Visibility, $Commentable, $ModuleEntityOwner, $NotifyAll, $Links, $files_count, $entity_tags, $post_as_module_id, $post_as_module_entity_id, $post_title, $post_type, $Media, $files, $activity_id, $is_anonymous, $status, $publish_post, $analytic_login_id, $facts, $params, $contest_date, $is_featured,$summary,$taged_user);

                    $activity_id = $ActivityDetails['ActivityID'];
                    $subscribe_action = $ActivityDetails['subscribe_action'];
                    $StatusID = $status;

                    /* Media will be deleted if draft post updated OR published */
                    $media_guid_list = array();
                    if (($status == 10 && !empty($Data['ActivityGUID'])) || $publish_post == 1) {
                        if ($Media) {
                            foreach ($Media as $md) {
                                $media_guid_list[] = $md['MediaGUID'];
                            }
                        }
                        $this->db->where('MediaSectionReferenceID', $activity_id);
                        $this->db->where('MediaSectionID', '3');
                        $this->db->where('StatusID', '10');
                        if ($media_guid_list) {
                            $this->db->where_not_in('MediaGUID', $media_guid_list);
                        }
                        $this->db->delete(MEDIA);
                    }

                    /* END Media Delete */
                    $check_media_status = FALSE;
                    if (!empty($Media)) {
                        $check_media_status = TRUE;
                        $AlbumName = DEFAULT_WALL_ALBUM;
                        if (count($Media) == 1) {
                            $MediaGUID = $Media[0]['MediaGUID'];
                            $Media_type = get_media_type($MediaGUID);
                            if ($Media_type == 2) {
                                $AlbumName = DEFAULT_WALL_ALBUM;
                            }
                        }

                        $media_user_id = $UserID;
                        if ($post_as_module_id == '3') {
                            $media_user_id = $post_as_module_entity_id;
                        }

                        $AlbumID = get_album_id($media_user_id, $AlbumName, $ModuleID, $ModuleEntityID);
                        $this->media_model->updateMedia($Media, $activity_id, $media_user_id, $AlbumID, TRUE, 3, $status, $Commentable);                        
                    }

                    //Update FIles for Activity
                    if (!empty($files)) {
                        $check_media_status = TRUE;
                        $AlbumName = DEFAULT_FILE_ALBUM;
                        $AlbumID = get_album_id($UserID, $AlbumName, $ModuleID, $ModuleEntityID);
                        $this->media_model->updateMedia($files, $activity_id, $UserID, $AlbumID, '', '', $status);                        
                    }
                    
                    if ($check_media_status && $this->activity_model->check_media_pending_status($activity_id)) {
                        $StatusID = 1;
                        $this->activity_model->change_activity_status($activity_id, 1);
                    }
                    if ($post_type != 7 && $StatusID == 2) {
                        //$this->subscribe_model->subscribe_email($UserID, $activity_id, $subscribe_action);
                    }

                    //subscribe Post owner too
                    if ($ModuleID == '3' && $UserID != $ModuleEntityID) {
                        //toggle subscribe
                        $subscribed_owner[0]['ModuleID'] = 3;
                        $subscribed_owner[0]['ModuleEntityID'] = $ModuleEntityID;
                        $this->subscribe_model->addUpdate($subscribed_owner, $activity_id, 1, 'ACTIVITY');
                    }

                    // Send notification only if activity status is active
                    if ($StatusID == 2) {

                        if ($ModuleID == 1) {
                            $ActivityTypeID = 7;
                        } elseif ($ModuleID == 3) {
                            if ($post_type == '8') {
                                $ActivityTypeID = 36;
                            } else if ($post_type == '9') {
                                $ActivityTypeID = 37;
                            } else if ($UserID == $ModuleEntityID) {
                                $ActivityTypeID = 1;
                            } else {
                                $ActivityTypeID = 8;
                            }
                        } elseif ($ModuleID == 14) {
                            $ActivityTypeID = 11;
                        } elseif ($ModuleID == 18) {
                            $ActivityTypeID = 12;
                        } elseif ($ModuleID == 34) {
                            $ActivityTypeID = 26;
                        }                       
                        
                        // Send notification only for active posts
                        if ($status == 2) {                            
                            //$this->notification_model->post_notification(array('ActivityID' => $activity_id, 'UserID' => $UserID)); 
                            $this->activity_model->send_post_notifications($UserID, $PostContent, $ActivityTypeID, $activity_id, $ModuleID, $ModuleEntityID, 0, $post_as_module_id, $post_as_module_entity_id, $ActivityDetails['notify_users'], $post_type, $NotifyAll, $is_edit);                            
                        }
                    }

                    if ($ModuleID != 3) { // Update last activity date
                        $this->load->helper('activity');
                        set_last_activity_date($ModuleEntityID, $ModuleID);
                    }
                    initiate_worker_job('activity_cache', array('ActivityID' => $activity_id));

                    $Return['Data'] = $this->activity_model->getSingleUserActivity($UserID, $activity_id, $AllActivity, FALSE, '', '', $role_id);

                    // Update current history
                    $UpdateHistory = array();
                    if (!empty($Return['Data'][0]['Album'])) {
                        $Media = json_encode($Return['Data'][0]['Album']);
                        $UpdateHistory['Media'] = $Media;
                    }
                    if (!empty($Return['Data'][0]['Files'])) {
                        $Files = json_encode($Return['Data'][0]['Files']);
                        $UpdateHistory['Files'] = $Files;
                    }

                    if (!empty($UpdateHistory) && $status == 2) {
                        $this->activity_model->update_row(ACTIVITYHISTORY, array('StatusID' => 2, 'ActivityID' => $activity_id), $UpdateHistory);
                    }
                } else {
                    $Return['ResponseCode'] = 501;
                    $Return['Message'] = lang('post_permission_deny');
                }
            }
        } else {
            $Return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $Return['Message'] = lang('input_invalid_format');
        }
        $this->response($Return); /* Final Output */
    }

    /**
     * Function Name: change_activity_version
     * @param ActivityGuID,HistoryID
     * Description: Get list of history versions on particular activity
     */
    public function change_activity_version_post() {
        $Return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $this->form_validation->set_rules('ActivityGUID', 'ActivityGUID', 'trim|required');
            $this->form_validation->set_rules('HistoryID', 'HistoryID', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = $error;
            } else {
                $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
                $history_id = isset($data['HistoryID']) ? $data['HistoryID'] : '';
                $Result = $this->activity_model->change_activity_version($activity_guid, $history_id, $user_id);
                $Return['Message'] = $Result['Message'];
                $Return['ResponseCode'] = $Result['ResponseCode'];
            }
        } else {
            $Return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $Return['Message'] = lang('input_invalid_format');
        }

        $this->response($Return);
    }

    /**
     * Function Name: getAllComments
     * @param ActivityGuID
     * Description: Get list of all commnets on particular activity
     */
    public function getAllComments_post() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = isset($this->UserID) ? $this->UserID : 0;
        /* Define variables - ends */

        if ($this->form_validation->run('api/activity/getAllComments') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            if (isset($Data['ParentCommentGUID'])) {
                $comment_guid = $Data['ParentCommentGUID'];
                $parent_comment_id = get_detail_by_guid($comment_guid, 20);
            } else {
                $parent_comment_id = 0;
            }

            $EntityGUID = $Data['EntityGUID'];
            $EntityID = '';
            $IsOwner = 0;
            $Flag = FALSE;

            // Check if request for pagination.
            if (!empty($Data['PageSize']) && !empty($Data['PageNo'])) {
                $Flag = TRUE;
            }

            // Check input params and set default values
            $PageSize = !empty($Data['PageSize']) ? $Data['PageSize'] : COMMENTPAGESIZE;
            $PageNo = !empty($Data['PageNo']) ? $Data['PageNo'] : 1;
            $entity_type = !empty($Data['EntityType']) ? $Data['EntityType'] : 'Activity';
            $PostAsModuleID = isset($Data['PostAsModuleID']) ? $Data['PostAsModuleID'] : 3;
            $PostAsModuleEntityGUID = isset($Data['PostAsModuleEntityGUID']) ? $Data['PostAsModuleEntityGUID'] : 0;
            $PostAsModuleEntityID = ($PostAsModuleEntityGUID) ? get_detail_by_guid($PostAsModuleEntityGUID, $PostAsModuleID) : $UserID;
            $filter = !empty($Data['Filter']) ? $Data['Filter'] : "";
            if ($entity_type == 'Album') {
                $entity_type = 'Activity';
                $this->load->model('album/album_model');
                $EntityID = $this->album_model->get_album_activity_id($EntityGUID);
                $EntityGUID = get_detail_by_id($EntityID, 0, 'ActivityGUID', 1);
            }

            if ($entity_type == 'Rating') {
                $entity_type = 'Activity';
                $EntityID = get_detail_by_guid($EntityGUID, 23, "ActivityID", 1);
                $EntityGUID = get_detail_by_id($EntityID, 0, 'ActivityGUID', 1);
            }

            // Set ActivityTypeID according to Entity Type
            switch ($entity_type) {
                case 'Media':
                    $ModuleID = 21;
                    // Get details for media
                    $EntityDetails = get_detail_by_guid($EntityGUID, 21, "MediaID, UserID, ModuleID, ModuleEntityID", 2);
                    if (!empty($EntityDetails)) {
                        $EntityID = $EntityDetails['MediaID'];
                    }
                    if ($EntityDetails['UserID'] == $UserID) {
                        $IsOwner = 1;
                    }
                    break;
                case 'Rating':
                    $EntityID = get_detail_by_guid($EntityGUID, 23);
                    $EntityDetails = false;
                    break;
                case 'Album':
                    $this->load->model('album/album_model');
                    $EntityID = $this->album_model->get_album_activity_id($EntityGUID);
                    $EntityDetails = get_detail_by_id($EntityID, 0, "UserID, ModuleID, ModuleEntityID", 2);
                    if ($EntityDetails['ModuleEntityID'] == $UserID) {
                        $IsOwner = 1;
                    }
                    break;
                default:
                    $ModuleID = 0;
                    // Get details for activity
                    $EntityDetails = get_detail_by_guid($EntityGUID, 0, "ActivityID, UserID, ModuleID, ModuleEntityID", 2);
                    if ($EntityDetails['UserID'] == $UserID) {
                        $IsOwner = 1;
                    }
                    if (!empty($EntityDetails)) {
                        $EntityID = $EntityDetails['ActivityID'];
                    }
                    if ($EntityDetails['ModuleID'] == 3 && $EntityDetails['ModuleEntityID'] == $UserID) {
                        $IsOwner = 1;
                    }
                    if ($EntityDetails['ModuleID'] == 1) {
                        if ($this->group_model->is_admin($UserID, $EntityDetails['ModuleEntityID'])) {
                            $IsOwner = 1;
                        }
                    }
                    if ($EntityDetails['ModuleID'] == 14) {
                        if ($this->event_model->isEventOwner($EntityDetails['ModuleEntityID'], $UserID)) {
                            $IsOwner = 1;
                        }
                    }
                    if ($EntityDetails['ModuleID'] == 18) {
                        if ($this->page_model->check_page_owner($UserID, $EntityDetails['ModuleEntityID'])) {
                            $IsOwner = 1;
                        }
                    }
                    if ($EntityDetails['ModuleID'] == 34) {
                        $permissions = $this->forum_model->check_forum_category_permissions($UserID, $EntityDetails['ModuleEntityID'], FALSE);
                        if ($permissions['IsAdmin']) {
                            $IsOwner = 1;
                        }
                    }
                    break;
            }
            if ($EntityDetails || $entity_type == 'Rating') {
                $ModuleID = $EntityDetails['ModuleID'];
                $ModuleEntityID = $EntityDetails['ModuleEntityID'];
                $include_reply = 0;
                if($this->IsApp==1) {
                    $include_reply = 1;
                }

                $blocked_users = $this->activity_model->block_user_list($ModuleEntityID, $ModuleID);
                $Return['Data'] = $this->activity_model->getActivityComments($entity_type, $EntityID, $PageNo, $PageSize, $UserID, $IsOwner, 2, $Flag, $blocked_users, FALSE, '', $PostAsModuleID, $PostAsModuleEntityID, "", $parent_comment_id, $filter, $include_reply);
                $Return['TotalRecords'] = $this->activity_model->getActivityComments($entity_type, $EntityID, '', '', $UserID, $IsOwner, 2, FALSE, $blocked_users, TRUE, '', $PostAsModuleID, $PostAsModuleEntityID, "", $parent_comment_id, $filter);
            } else {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = sprintf(lang('valid_value'), "activity GUID");
            }
        }
        $this->response($Return);
    }

    /**
     * Function Name: removeActivity
     * @param ActivityID
     * Description: Delete activity
     */
    public function removeActivity_post() {

        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        /* Define variables - ends */

        if ($this->form_validation->run('api/activity/removeActivity') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $activity_id = get_detail_by_guid($Data['EntityGUID']);
            if (empty($activity_id)) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            } else {
                $Result = $this->activity_model->removeActivity($activity_id, $UserID);
                $Return['Message'] = $Result['Message'];
                $Return['ResponseCode'] = $Result['ResponseCode'];
            }
        }
        $this->response($Return);
    }

    public function restoreActivity_post() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        /* Define variables - ends */

        if ($this->form_validation->run('api/activity/restoreActivity') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $activity_id = get_detail_by_guid($Data['EntityGUID']);
            if (empty($activity_id)) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            } else {
                $this->activity_model->restoreActivity($activity_id, $UserID);
            }
        }
        $this->response($Return);
    }

    /**
     * Function Name: addComment
     * @param ActivityGuID,ActivityType,Comment
     * Description: Add new comment on particular activity
     */
    public function addComment_post() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        /* Define variables - ends */

        if ($this->form_validation->run('api/activity/addComment') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            
            $strippedContent = strip_tags($Data['Comment'], '<img><iframe>');
            if(empty($strippedContent) && empty($Data['Media'])) {                
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = 'Please enter comment content or select media.';
                $this->response($Return);
            }
            
            $EntityGUID = $Data['EntityGUID'];
            $Comment = isset($Data['Comment']) ? $Data['Comment'] : '';
            $parent_comment_id = 0;
            $comment_id = 0;
            if (isset($Data['ParentCommentGUID'])) {
                $parent_comment_id = get_detail_by_guid($Data['ParentCommentGUID'], 20);
            }

            $is_anonymous = isset($Data['IsAnonymous']) ? $Data['IsAnonymous'] : 0;
            $comment_guid = isset($Data['CommentGUID']) ? $Data['CommentGUID'] : 0;
            if ($comment_guid) {
                $comment_id = get_detail_by_guid($comment_guid, 20);
            }

            $Media = isset($Data['Media']) ? $Data['Media'] : array();
            $SourceID = isset($Data['SourceID']) ? $Data['SourceID'] : DEFAULT_SOURCE_ID;
            $DeviceID = isset($Data['DeviceID']) ? $Data['DeviceID'] : DEFAULT_SOURCE_ID;
            $taged_user = isset($Data['TagedUser']) ? json_encode($Data['TagedUser']) : '';

            $entity_owner = 0;
            $PostAsModuleID = isset($Data['PostAsModuleID']) ? $Data['PostAsModuleID'] : 3;
            $PostAsModuleEntityGUID = isset($Data['PostAsModuleEntityGUID']) ? $Data['PostAsModuleEntityGUID'] : 0;
            $PostAsModuleEntityID = ($PostAsModuleEntityGUID) ? get_detail_by_guid($PostAsModuleEntityGUID, $PostAsModuleID) : $UserID;
            $entity_type = isset($Data['EntityType']) ? $Data['EntityType'] : 'ACTIVITY';
            //check if media and files are exist
            $is_media_exists = (!empty($Media)) ? '1' : '0';
            $CommentData = $this->activity_model->addComment($EntityGUID, $Comment, $Media, $UserID, $is_media_exists, $SourceID, $DeviceID, $entity_owner, $entity_type, $PostAsModuleID, $PostAsModuleEntityID, $parent_comment_id, $is_anonymous, $comment_id, $taged_user);
        }

        $Return['Data'] = $CommentData;
        $this->response($Return);
    }

    /**
     * Function Name: deleteComment
     * @param ActivityGuID,CommentGuID
     * Description: Delete comment from activity
     */
    public function deleteComment_post() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        /* Define variables - ends */
        if ($this->form_validation->run('api/activity/deleteComment') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $CommentGUID = $Data['CommentGUID'];
            $entity_type = isset($Data['EntityType']) ? $Data['EntityType'] : 'ACTIVITY';
            $Result = $this->activity_model->deleteComment($CommentGUID, $UserID, $entity_type);
            $Return['Message'] = $Result['Message'];
            $Return['ResponseCode'] = $Result['ResponseCode'];
        }
        $this->response($Return);
    }

    /**
     * Function Name: blockUser
     * @param EntityGUID,ModuleID,ModuleEntityGUID
     * Description: block user for specific user
     */
    public function blockUser_post() {
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;

        if ($this->form_validation->run('api/activity/blockUser') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $ModuleID = $Data['ModuleID'];
            $EntityID = get_detail_by_guid($Data['EntityGUID'], 3);
            $ModuleEntityID = get_detail_by_guid($Data['ModuleEntityGUID'], $ModuleID);
            if (empty($EntityID)) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            } else if (empty($ModuleEntityID)) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = sprintf(lang('valid_value'), "module entity GUID");
            } else {
                $this->activity_model->blockUser($UserID, $EntityID, $ModuleID, $ModuleEntityID);
            }
        }
        $this->response($Return);
    }

    /**
     * Function Name: blockedUsers_post
     * @param term
     * Description: Get list of blocked users
     */
    public function blocked_user_list_post() {
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;

        $SearchKey = isset($Data['SearchKeyword']) ? $Data['SearchKeyword'] : '';
        $PageNo = isset($Data['PageNo']) ? $Data['PageNo'] : 0;
        $PageSize = isset($Data['PageSize']) ? $Data['PageSize'] : PAGE_SIZE;

        $Data = $this->activity_model->get_blocked_user_list($UserID, $SearchKey, $PageNo, $PageSize);
        $Return['Data'] = $Data['Data'];
        $Return['total_records'] = $Data['total_records'];
        $this->response($Return);
    }

    /**
     * Function Name: privacyChange
     * @param ActivityGuID,VisibleFor
     * Description: Change privacy settings of activity
     */
    public function privacyChange_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* Define variables - ends */

        if ($this->form_validation->run('api/activity/privacyChange') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $activity_guid = $data['ActivityGUID'];
            $privacy = $data['Visibility'];
            $activity_id = get_detail_by_guid($activity_guid);
            if (empty($activity_id)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "activity GUID");
            } else {
                $this->activity_model->change_privacy($activity_id, $privacy);
            }
        }
        $this->response($return);
    }

    /**
     * [getLikeDetails_post Get list of users who likes activity]
     * @return [json] [list of users who likes activity]
     */
    public function getLikeDetails_post() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        /* Define variables - ends */
        
        if ($this->form_validation->run('api/activity/getLikeDetails') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $PageNo = isset($Data['PageNo']) ? $Data['PageNo'] : 0;
            $PageSize = isset($Data['PageSize']) ? $Data['PageSize'] : PAGE_SIZE;
            $EntityGUID = $Data['EntityGUID'];
            $entity_type = $Data['EntityType'];
            //$this->user_model->set_friend_followers_list($UserID);
            if ($entity_type == 'ALBUM') {
                $this->load->model('album/album_model');
                $EntityID = $this->album_model->get_album_activity_id($EntityGUID);
                $EntityGUID = get_detail_by_id($EntityID, 0, 'ActivityGUID', 1);
                $entity_type = 'ACTIVITY';
            }

            $blocked_users = $this->activity_model->block_user_list($UserID);
            $Return['Data'] = $this->activity_model->getLikeDetails($EntityGUID, $entity_type, $blocked_users, $PageNo, $PageSize, FALSE, $UserID);
            $Return['TotalRecords'] = $this->activity_model->getLikeDetails($EntityGUID, $entity_type, $blocked_users, 0, 0, TRUE, $UserID);
            if (!$Return['Data']) {
                $Return['Data'] = array();
            }
        }
        $this->response($Return);
    }

    /**
     * Function Name: sharePost
     * @param ActivityGuID
     * Description: Share activity of other users
     */
    public function sharePost_post() {
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        if ($this->form_validation->run('api/activity/sharePost') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $ModuleID = $Data['ModuleID'];
            $EntityGUID = $Data['EntityGUID'];
            $ActivityGUID = isset($Data['ActivityGUID']) ? $Data['ActivityGUID'] : '' ;
            $entity_type = $Data['EntityType'];
            $entity_type = strtoupper($entity_type);
            if ($entity_type == 'ALBUM') {
                $this->load->model('album/album_model');
                $EntityID = $this->album_model->get_album_activity_id($EntityGUID);
                $EntityGUID = get_detail_by_id($EntityID, 0, 'ActivityGUID', 1);
                $entity_type = 'ACTIVITY';
            }

            if ($entity_type != 'MEDIA' && $entity_type != 'ACTIVITY') {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = 'EntityType should have valid value.';
            }
            if (isset($Data['Commentable']) && ($Data['Commentable'] != '0' && $Data['Commentable'] != '1')) {
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = 'Commentable should have valid value.';
            }

            $ModuleEntityID = $UserID;
            if (isset($Data['ModuleEntityGUID']) && !empty($Data['ModuleEntityGUID'])) {
                $ModuleEntityID = get_detail_by_guid($Data['ModuleEntityGUID'], $ModuleID);
            }
            if ($entity_type == 'MEDIA') {
                $EntityID = get_detail_by_guid($EntityGUID, 21);
            } else {
                $EntityD = get_detail_by_guid($EntityGUID, 0, 'UserID,ActivityID,Privacy,PostType', 2);
                $EntityID = $EntityD['ActivityID'];
                $PostType = $EntityD['PostType'];
                if ($ModuleID == 3) {
                    $IsRelation = $this->activity_model->isRelation($EntityD['UserID'], $ModuleEntityID, true, $EntityGUID);
                    if (!in_array($EntityD['Privacy'], $IsRelation)) {
                        $Return['ResponseCode'] = 512;
                        $Return['Message'] = lang('cant_share');
                        $this->response($Return);
                    }
                }
            }

            if(!isset($PostType) || empty($PostType))
            {
                $PostType = 1;
            }

            $CurrentModuleEntityID = isset($Data['CurrentModuleEntityID']) ? get_detail_by_guid($Data['CurrentModuleEntityID'], 3) : '';
            $PostContent = '';
            if (isset($Data['PostContent'])) {
                $PostContent = $Data['PostContent'];
            }
            $Commentable = isset($Data['Commentable']) ? $Data['Commentable'] : 0;
            $Visibility = isset($Data['Visibility']) ? $Data['Visibility'] : '';

            $post_as_module_id = isset($Data['PostAsModuleID']) ? $Data['PostAsModuleID'] : '';
            $post_as_module_entity_guid = isset($Data['PostAsModuleEntityGUID']) ? $Data['PostAsModuleEntityGUID'] : '';

            if (!empty($post_as_module_id) && !empty($post_as_module_entity_guid)) {
                $ModuleID = $post_as_module_id;
                $post_as_module_entity_id = get_detail_by_guid($post_as_module_entity_guid, $post_as_module_id);
                $ModuleEntityID = $post_as_module_entity_id;
                if ($ModuleID == '3') {
                    $UserID = $ModuleEntityID;
                }
            }
            $Return['Data'] = $this->activity_model->sharePost($ModuleID, $ModuleEntityID, $UserID, $entity_type, $EntityID, $PostContent, $Commentable, $Visibility,$PostType,$ActivityGUID);
        }
        $this->response($Return);
    }

    /**
     * Function Name: commentStatus
     * @param EntityType,EntityGUID
     * @return success / failure message and response code
     * Description: switch comments on / off 
     */
    public function commentStatus_post() {
        $Return = $this->return;
        $Data = $this->post_data;
        if ($this->form_validation->run('api/activity/commentStatus') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $entity_type = isset($Data['EntityType']) ? $Data['EntityType'] : 'ACTIVITY';
            $EntityGUID = $Data['EntityGUID'];
            $Result = $this->activity_model->commentStatus($entity_type, $EntityGUID);
            $Return['ResponseCode'] = $Result['ResponseCode'];
            $Return['Message'] = $Result['Message'];
        }
        $this->response($Return);
    }

    public function checkLikeStatus_post() {
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        $PostAsModuleID = isset($Data['PostAsModuleID']) ? $Data['PostAsModuleID'] : 3;
        $PostAsModuleEntityGUID = isset($Data['PostAsModuleEntityGUID']) ? $Data['PostAsModuleEntityGUID'] : 0;
        $PostAsModuleEntityID = ($PostAsModuleEntityGUID) ? get_detail_by_guid($PostAsModuleEntityGUID, $PostAsModuleID) : $UserID;
        $ActivityGUID = isset($Data['ActivityGUID']) ? $Data['ActivityGUID'] : '';
        $Return['Data'] = $this->activity_model->checkLikeStatus($ActivityGUID, $UserID, $PostAsModuleID, $PostAsModuleEntityID);
        $this->response($Return);
    }

    /**
     * [toggleLike_post To mark Like or Unlike an entity]
     * @param EntityGUID - Entity GUID of the entity being Like / Un Like
     * @param EntityType - ACTIVITY, COMMENT
     * @return [type] [JSON Object]
     */
    public function toggleLike_post() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        /* Define variables - ends */

        if ($this->form_validation->run('api/toggleLike') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $Data['UserID'] = $this->UserID;
            if (strtoupper($Data['EntityType']) == 'ALBUM') {
                $this->load->model('album/album_model');
                $Data['EntityType'] = 'ACTIVITY';
                $Data['EntityGUID'] = $this->album_model->get_album_activity_guid($Data['EntityGUID']);
            }
            $Result = $this->activity_model->toggleLike($Data);
            if ($Data['EntityType'] == 'ACTIVITY') {
                $Return['Data']['NoOfLikes'] = $this->activity_model->get_like_count(get_detail_by_guid($Data['EntityGUID']), "ACTIVITY", array());
            }

            $Return['ResponseCode'] = $Result['ResponseCode'];
            $Return['Message'] = $Result['Message'];
            $entity_type = strtoupper($Data['EntityType']);
            if ($entity_type == 'ACTIVITY') {
                $Return['LikeName'] = $Result['LikeName'];
            }
        }
        $this->response($Return);
    }

    /**
     * [toggle_sticky_post To mark sticky an entity]
     * @param EntityGUID - Entity GUID of the entity being Pin / Un Pin
     * @return [type] [JSON Object]
     */
    public function toggle_sticky_post() {
        $return = $this->return;
        $data = $this->post_data;
        if ($this->form_validation->run('api/toggle_sticky') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $data['UserID'] = $this->UserID;
            $result = $this->activity_model->toggle_sticky($data);
            $return['ResponseCode'] = $result['ResponseCode'];
            $return['Message'] = $result['Message'];
            if (isset($result['Data'])) {
                $return['Data'] = $result['Data'];
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: approveFlagActivity
     * @param EntityGUID - Entity GUID
     * @return Success / Failure Message and Response Code
     */
    public function approveFlagActivity_post() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        /* Define variables - ends */

        if ($this->form_validation->run('api/activity/approveFlagActivity') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            $EntityID = get_detail_by_guid($Data['EntityGUID']);
            $Result = $this->activity_model->approveFlagActivity($UserID, $EntityID);
            $Return['ResponseCode'] = $Result['ResponseCode'];
            $Return['Message'] = $Result['Message'];
        }
        $this->response($Return);
    }

    /**
     * Function Name: autoSuggestPostOwner
     * @param UserID
     * @param SearchKey
     * @return array | List of users
     */
    public function autoSuggestPostOwner_get() {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        /* Define variables - ends */

        $SearchKey = isset($Data['term']) ? $Data['term'] : '';
        $AllActivity = isset($Data['AllActivity']) ? $Data['AllActivity'] : 0;
        $FilterType = isset($Data['FilterType']) ? $Data['FilterType'] : 0;
        $ModuleID = 0;
        $ModuleEntityID = 0;
        if (!$AllActivity) {
            $ModuleID = isset($Data['ModuleID']) ? $Data['ModuleID'] : 0;
            $ModuleEntityID = isset($Data['ModuleEntityGUID']) ? get_detail_by_guid($Data['ModuleEntityGUID'], $ModuleID) : 0;
        }
        $Result = $this->activity_model->autoSuggestPostOwner($UserID, $SearchKey, $ModuleID, $ModuleEntityID, $AllActivity, $FilterType);
        $Return['Data'] = $Result;
        $this->response($Return);
    }

    /**
     * [get_single_live_feed_post Used to get single live feed details]
     * @return [JSON] [JSON Object]
     */
    public function get_single_live_feed_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $from_user_id = isset($data['FromUserID']) ? $data['FromUserID'] : '';
        $type = isset($data['Type']) ? $data['Type'] : '';
        $entity_guid = isset($data['EntityGUID']) ? $data['EntityGUID'] : '';
        $return['Data'] = $this->activity_model->get_single_live_feed($user_id, $from_user_id, $type, $entity_guid);
        $this->response($return);
    }

    /**
     * [get_recent_activities_post Used to user recent activity]
     * @return [JSON] [JSON Object]
     */
    public function get_recent_activities_post() {
        $return = $this->return;
        $data = $this->post_data;
        $current_user_id = $this->UserID;

        $user_id = isset($data['UserGUID']) ? get_detail_by_guid($data['UserGUID'], 3) : $current_user_id;
        $return['Data'] = $this->activity_model->get_recent_activities($user_id, $current_user_id);
        $this->response($return);
    }

    /**
     * [toggle_archive_post Used to archive activity]
     * @return [JSON] [JSON Object]
     */
    public function toggle_archive_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/activity/toggle_archive') == FALSE) { // for web
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error; //Shows all error messages as a string
        } else {
            //Business Logic
            $activity_id = get_detail_by_guid($data['ActivityGUID']);
            $return['Data'] = $this->activity_model->toggle_archive($user_id, $activity_id);
        }
        $this->response($return);
    }

    /**
     * [live_feed_post Used to get live feed details]
     * @return [JSON] [JSON Object]
     */
    public function live_feed_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $return['Data'] = $this->activity_model->live_feed($user_id, $page_no);
        $return['TotalRecords'] = $this->activity_model->live_feed($user_id, 0, 0, true);
        $this->response($return);
    }

    /**
     * [profile_card_post Used to get prfile card details]
     * @return [JSON] [JSON Object]
     */
    public function profile_card_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $entity_type = isset($data['EntityType']) ? strtoupper($data['EntityType']) : '';
        $entity_guid = isset($data['EntityGUID']) ? $data['EntityGUID'] : '';

        if ($entity_type && $entity_guid) {
            $return['Data'] = $this->activity_model->profile_card($user_id, $entity_guid, $entity_type);
        }
        $this->response($return);
    }

    /**
     * [remove_tags_post Remove tags from activity]
     * @return [JSON] [JSON Object]
     */
    public function remove_tags_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';

        $return['Data'] = $this->activity_model->remove_tags($activity_guid, $user_id);
        $this->response($return);
    }

    public function flag_users_detail_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            /* Validation - starts */
            if ($this->form_validation->run('api/toggle_favourite') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else /* Validation - ends */ {
                $entity_guid = $data['EntityGUID'];
                $entity_type = $data['EntityType'];
                $entity_id = get_detail_by_guid($entity_guid);
                $result = $this->activity_model->flag_users_detail($user_id, $entity_id, $entity_type);
                $return['Data'] = $result;
            }
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * [get_entity_files Used to get files based on selected group|User|Page|Event]
     * @param  [int]       ModuleID         [Module ID]
     * @param  [String]    ModuleEntityGUID [ModuleEntityGUID]
     * @param  [String]    SearchKey        [Search String]
     * @param  [int]       PageNo           [Page Offset]
     * @param  [int]       PageSize         [Total no of records on a page]
     * @param  (0,1)       IsNewsFeed       [1 to Display on Newsfeed]
     * @return [JSON] [JSON Object]
     */
    public function get_entity_files_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $data['UserID'] = $this->UserID;
        $data['ModuleID'] = isset($data['ModuleID']) ? $data['ModuleID'] : '3';
        $data['ModuleEntityGUID'] = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
        $data['ModuleEntityID'] = (isset($data['ModuleEntityGUID']) & !empty($data['ModuleEntityGUID'])) ? get_detail_by_guid($data['ModuleEntityGUID'], $data['ModuleID']) : '';
        $data['SearchKey'] = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $data['PageNo'] = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1';
        $data['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : '20'; //there must be a default limit of the links to show on display
        $data['IsNewsFeed'] = (isset($data['IsNewsFeed']) && $data['IsNewsFeed'] == '1') ? TRUE : FALSE;

        //set value required         
        $this->user_model->set_user_time_zone($data['UserID']);
        $this->activity_model->set_block_user_list($data['UserID'], 3);
        $this->user_model->set_friend_followers_list($data['UserID']);
        $this->group_model->set_user_group_list($data['UserID']);
        $this->privacy_model->set_privacy_options($data['UserID']);
        $this->event_model->set_user_joined_events($data['UserID']);
        $this->page_model->set_user_pages_list($data['UserID']);
        $this->user_model->set_user_profile_url($data['UserID']);
        $this->activity_model->set_user_tagged($data['UserID']);
        $this->subscribe_model->set_user_subscribed($data['UserID']);
        $this->favourite_model->set_user_favourite($data['UserID']);
        $this->flag_model->set_user_flagged($data['UserID']);
        $this->activity_model->set_user_activity_archive($data['UserID']);
        $friend_followers_list = $this->user_model->get_friend_followers_list();

        //check if the Files to be shown on NewsFeed
        if ($data['IsNewsFeed']) {
            if (!empty($friend_followers_list)) {
                $this->user_model->set_friends_of_friend_list($data['UserID'], $friend_followers_list['Friends']);
            }
            //get newsfeed files
            $return['Data'] = $this->activity_model->get_newsfeed_files($data['UserID'], $data['PageNo'], $data['PageSize'], 0, $data['SearchKey'], $data['ModuleEntityID'], $data['ModuleID']);
            $return['TotalRecords'] = (isset($return['Data']['TotalRecords']) && !empty($return['Data']['TotalRecords'])) ? $return['Data']['TotalRecords'] : 0;
            unset($return['Data']['TotalRecords']);
        } else {
            $return['Data'] = $this->activity_model->get_wall_files($data['ModuleEntityID'], $data['ModuleID'], $data['SearchKey'], $data['PageNo'], $data['PageSize'], $data['UserID'], 1);
            $return['TotalRecords'] = (isset($return['Data']['TotalRecords']) && !empty($return['Data']['TotalRecords'])) ? $return['Data']['TotalRecords'] : 0;
            unset($return['Data']['TotalRecords']);
        }
        $this->response($return);
    }

    public function get_popular_feeds_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $limit = (isset($data['Limit']) && $data['Limit'] > 0) ? $data['Limit'] : 20;
        $module_id = $data['ModuleID'] = isset($data['ModuleID']) ? $data['ModuleID'] : '';
        $offset = 0;
        $condition = "
            (
                IF(A.ModuleID=1,A.ModuleEntityID IN(SELECT GroupID FROM " . GROUPS . " WHERE IsPublic='1' AND StatusID='2'),FALSE)
                OR
                IF(A.ModuleID=14,A.ModuleEntityID IN(SELECT EventID FROM " . EVENTS . " WHERE Privacy='PUBLIC' AND IsDeleted='0'),FALSE)
                OR
                IF(A.ModuleID=18,A.ModuleEntityID IN(SELECT PageID FROM " . PAGES . " WHERE StatusID='2'),FALSE)
            )
        ";

        $this->db->select("UAL.ModuleEntityID as ActivityID,COUNT(UAL.ID) as Popularity");
        $this->db->from(USERSACTIVITYLOG . ' UAL');
        $this->db->join(ACTIVITY . ' A', 'A.ActivityID=UAL.ModuleEntityID');
        $this->db->where('UAL.ModuleID', '19');
        $this->db->where('A.Privacy', '1');
        $this->db->where('A.StatusID', '2');

        if (!empty($module_id)) {
            $this->db->where('A.ModuleID', $module_id);
        }

        $this->db->where($condition, null, false);
        $this->db->where_in('A.ActivityTypeID', array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 23, 24, 25));
        $this->db->limit($limit, $offset);
        $this->db->group_by('UAL.ModuleEntityID');
        $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d 00:00:00', 7) . "' AND '" . get_current_date('%Y-%m-%d 23:59:59') . "'", NULL, FALSE);
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(MUTESOURCE . ' MS', 'MS.UserID="' . $user_id . '" AND ((MS.ModuleID=A.ModuleID AND MS.ModuleEntityID=A.ModuleEntityID) OR (MS.ModuleID=3 AND MS.ModuleEntityID=A.UserID AND A.ModuleEntityOwner=0))', 'left');
        $this->db->where('MS.ModuleEntityID is NULL', null, false);
        $this->db->_protect_identifiers = TRUE;
        $this->db->order_by('A.IsFeatured', 'DESC');
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $activity_ids = array();
            $result = $query->result_array();
            foreach ($result as $val) {
                $activity_ids[] = $val['ActivityID'];
            }
            $this->user_model->set_user_time_zone($user_id);
            $this->user_model->set_user_profile_url($user_id);
            $this->activity_model->set_block_user_list($user_id, 3);
            $this->user_model->set_friend_followers_list($user_id);
            $this->group_model->set_user_group_list($user_id);
            $this->group_model->set_user_categoty_group_list($user_id);
            $this->favourite_model->set_user_favourite($user_id);
            $this->flag_model->set_user_flagged($user_id);
            $this->activity_model->set_user_activity_archive($user_id);
            $this->privacy_model->set_privacy_options($user_id);
            $this->event_model->set_user_joined_events($user_id);
            $this->page_model->set_feed_pages_condition($user_id);
            $return['Data'] = $this->activity_model->getFeedActivities($user_id, 1, $limit, 'ActivityIDS', 0, 0, 2, false, false, false, 0, 0, array(), '', array(), '', '', array(), $activity_ids);
            $return['TotalRecords'] = count($return['Data']);
        }
        $return['LoggedInProfilePicture'] = $this->LoggedInProfilePicture;
        $return['LoggedInName'] = $this->LoggedInName;
        $this->response($return);
    }

    /**
     * [get_entity_links Used to get Links based on selected group|User|Page|Event]
     * @param  [int]       ModuleID         [Module ID]
     * @param  [String]    ModuleEntityGUID [ModuleEntityGUID]
     * @param  [String]    SearchKey        [Search String]
     * @param  [int]       PageNo           [Page Offset]
     * @param  [int]       PageSize         [Total no of records on a page]
     * @param  (0,1)       IsNewsFeed       [1 to Display on Newsfeed]
     * @return [JSON] [JSON Object]
     */
    public function get_entity_links_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $data['UserID'] = $this->UserID;
        $data['ModuleID'] = isset($data['ModuleID']) ? $data['ModuleID'] : '3';
        $data['ModuleEntityGUID'] = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
        $data['ModuleEntityID'] = (isset($data['ModuleEntityGUID']) & !empty($data['ModuleEntityGUID'])) ? get_detail_by_guid($data['ModuleEntityGUID'], $data['ModuleID']) : '';
        $data['ModuleEntityID'] = (!empty($data['ModuleEntityID'])) ? $data['ModuleEntityID'] : $data['UserID'];
        $data['SearchKey'] = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $data['PageNo'] = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1';
        $data['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : '20'; //there must be a default limit of the links to show on display
        $data['IsNewsFeed'] = (isset($data['IsNewsFeed']) && $data['IsNewsFeed'] == '1' && $data['ModuleID'] == '3' && $data['ModuleEntityID'] == $data['UserID'] ) ? TRUE : FALSE;

        //set value required         
        $this->user_model->set_user_time_zone($data['UserID']);
        $this->activity_model->set_block_user_list($data['UserID'], 3);
        $this->user_model->set_friend_followers_list($data['UserID']);
        $this->group_model->set_user_group_list($data['UserID']);
        $this->privacy_model->set_privacy_options($data['UserID']);
        $this->event_model->set_user_joined_events($data['UserID']);
        $this->page_model->set_user_pages_list($data['UserID']);
        $this->user_model->set_user_profile_url($data['UserID']);
        $this->activity_model->set_user_tagged($data['UserID']);
        $this->subscribe_model->set_user_subscribed($data['UserID']);
        $this->favourite_model->set_user_favourite($data['UserID']);
        $this->flag_model->set_user_flagged($data['UserID']);
        $this->activity_model->set_user_activity_archive($data['UserID']);
        $friend_followers_list = $this->user_model->get_friend_followers_list();

        //check if the Files to be shown on NewsFeed
        if ($data['IsNewsFeed']) {
            if (!empty($friend_followers_list)) {
                $this->user_model->set_friends_of_friend_list($data['UserID'], $friend_followers_list['Friends']);
            }
            //get newsfeed files
            $return['Data'] = $this->activity_model->get_newsfeed_links($data['UserID'], $data['PageNo'], $data['PageSize'], 0, $data['SearchKey'], $data['ModuleEntityID'], $data['ModuleID']);
            $return['TotalRecords'] = (isset($return['Data']['TotalRecords']) && !empty($return['Data']['TotalRecords'])) ? $return['Data']['TotalRecords'] : 0;
            unset($return['Data']['TotalRecords']);
        } else {
            $return['Data'] = $this->activity_model->get_wall_links($data['ModuleEntityID'], $data['ModuleID'], $data['SearchKey'], $data['PageNo'], $data['PageSize'], $data['UserID'], 1);
            $return['TotalRecords'] = (isset($return['Data']['TotalRecords']) && !empty($return['Data']['TotalRecords'])) ? $return['Data']['TotalRecords'] : 0;
            unset($return['Data']['TotalRecords']);
        }
        $this->response($return);
    }

    public function seen_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $this->form_validation->set_rules('PageNo', 'lang:page_no', 'trim|integer');
            $this->form_validation->set_rules('PageSize', 'lang:page_size', 'trim|integer');
            $this->form_validation->set_rules('EntityType', 'entity type', 'trim|required');
            $this->form_validation->set_rules('EntityGUID', 'entity guid', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $page_no = isset($data['PageNo']) ? $data['PageNo'] : 0;
                $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
                $entity_guid = isset($data['EntityGUID']) ? $data['EntityGUID'] : '';
                $entity_type = isset($data['EntityType']) ? $data['EntityType'] : 'Activity';

                $module_id = 0;
                switch ($entity_type) {
                    case 'Group':
                        $module_id = 1;
                        break;
                    case 'Page':
                        $module_id = 18;
                        break;
                    case 'User':
                        $module_id = 3;
                        break;
                    case 'Event':
                        $module_id = 14;
                        break;
                }

                $module_entity_id = get_detail_by_guid($entity_guid, $module_id);

                if (empty($module_entity_id)) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
                } else {
                    $return['TotalRecords'] = $this->activity_model->get_seen_list($user_id, $entity_type, $module_entity_id, $page_no, $page_size, true);
                    if ($return['TotalRecords']) {
                        $return['Data'] = $this->activity_model->get_seen_list($user_id, $entity_type, $module_entity_id, $page_no, $page_size, false);
                    }
                }
                $this->response($return);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
    }

    /**
     * [request_question_answer_for_activity Used to request a question for an activity]
     * @param  [String]     ActivityGUID     [GUID of Post Activity]
     * @param  [int]        RequestBy        [UserID of who intimate request]
     * @param  [Array]      RequestTo        [RequestTo is an array of user to be requested for the post]
     * @param  [String]     Note             [Description]     
     * @return [JSON] [JSON Object]
     */
    public function request_question_answer_for_activity_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            if ($this->form_validation->run('api/request_question_answer_for_activity') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                if (isset($data['RequestTo']) && (!is_array($data['RequestTo']) || empty($data['RequestTo']))) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('required_requestTo');
                    $this->response($return);
                }

                $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
                $request_to = isset($data['RequestTo']) ? $data['RequestTo'] : array();
                $request_by = isset($data['RequestBy']) ? $data['RequestBy'] : $user_id;
                $note = isset($data['Note']) ? $data['Note'] : '';
                $entity_type = isset($data['EntytyType']) ? $data['EntytyType'] : '';
                $status = isset($data['Status']) ? $data['Status'] : 'PENDING';
                $module_id = 0;
                switch ($entity_type) {
                    case 'Group':
                        $module_id = 1;
                        break;
                    case 'Page':
                        $module_id = 18;
                        break;
                    case 'User':
                        $module_id = 3;
                        break;
                    case 'Event':
                        $module_id = 14;
                        break;
                }
                $return['Data'] = $this->activity_model->request_question_answer($activity_guid, $module_id, $request_by, $request_to, $note, $status);
                if (!$this->settings_model->isDisabled(10))
                    $return['Message'] = 'Your request for answer has been sent to your selected friend(s)';
                else
                    $return['Message'] = 'Your request for answer has been sent to your selected follower(s)';
                $this->response($return);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
    }

    /**
     * [mark_best_answer Used to mark most approiate answer of a question for an activity]
     * @param  [String]     EntityGUID       [GUID of Post Comment]
     * @param  [String]     ActivityGUID     [GUID of Post Activity]
     * @return [JSON] [JSON Object]
     */
    public function mark_best_answer_post() {
        $return = $this->return;
        $data = $this->post_data;
        if ($this->form_validation->run('api/mark_best_answer') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $result = $this->activity_model->most_appropriate_answer($data);
            $return['ResponseCode'] = $result['ResponseCode'];
            $return['Message'] = $result['Message'];
            if (isset($result['Data'])) {
                $return['Data'] = $result['Data'];
            }
        }
        $this->response($return);
    }

    /**
     * [get_requested_answer_users Used to requested an answer user lists for an activity]
     * @param  [String]     ActivityGUID     [GUID of Post Activity]
     * @param  [String]     Status           [Status] (Optional)
     * @return [JSON] [JSON Object]
     */
    public function get_requested_answer_users_post() {
        $return = $this->return;
        $data = $this->post_data;

        if (isset($data)) {
            if ($this->form_validation->run('api/get_requested_answer_users') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
                $status = isset($data['Status']) ? $data['Status'] : '';
                $entity_type = isset($data['EntytyType']) ? $data['EntytyType'] : '';
                $PageNo = isset($Data['PageNo']) ? $Data['PageNo'] : 1;
                $PageSize = isset($Data['PageSize']) ? $Data['PageSize'] : PAGE_SIZE;

                $module_id = 0;
                switch ($entity_type) {
                    case 'Group':
                        $module_id = 1;
                        break;
                    case 'Page':
                        $module_id = 18;
                        break;
                    case 'User':
                        $module_id = 3;
                        break;
                    case 'Event':
                        $module_id = 14;
                        break;
                }
                $return['TotalRecords'] = $this->activity_model->requested_answer_user_lists($activity_guid, $module_id, $status, '', '', TRUE);
                $return['Data'] = $this->activity_model->requested_answer_user_lists($activity_guid, $module_id, $status, $PageNo, $PageSize, FALSE);
                $this->response($return);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
    }

    /**
     * [get_activity_friend  Used to fetch activity freind list]
     * @param  [String]      ActivityGUID     [GUID of Post Activity]
     * @return [JSON]                         [JSON Object]
     */
    public function get_activity_friend_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        if ($this->form_validation->run('api/get_activity_friend_list') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
            $PageNo = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $PageSize = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $search_keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
            $ignore_list = isset($data['IgnoreList']) ? $data['IgnoreList'] : array();

            $return['TotalRecords'] = $this->activity_model->activity_friend_list($activity_guid, '', '', TRUE, $search_keyword);
            $return['Data'] = $this->activity_model->activity_friend_list($activity_guid, $PageNo, $PageSize, FALSE, $search_keyword, $ignore_list);
            $return['ResponseCode'] = self::HTTP_OK;
            $return['Message'] = lang('success');
        }
        $this->response($return);
    }

    /**
     * [get_activity_history Used to fetch all verions/history for an activity]
     * @param  [String]   ActivityGUID     [GUID of Post Activity]
     * @param  [int]      HistoryID     [HistoryID of Post Activity] [optional]
     * @return [JSON]     [JSON Object]
     */
    public function get_activity_history_post() {
        $Return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $this->form_validation->set_rules('ActivityGUID', 'ActivityGUID', 'trim|required');
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = $error;
            } else {
                $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
                $history_id = isset($data['HistoryID']) ? $data['HistoryID'] : '';
                $Result = $this->activity_model->get_activity_versions($activity_guid, $history_id, $user_id);
                $Return['Data'] = $Result;
                $Return['ResponseCode'] = self::HTTP_OK;
                $Return['Message'] = lang('success');
            }
        } else {
            $Return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $Return['Message'] = lang('input_invalid_format');
        }
        $this->response($Return);
    }

    /**
     * [remove_featured_post]
     * @param  $ModuleID
     * @param  $ModuleEntityID
     * @param  $ActivityGUID
     * @return [JSON]
     */
    public function remove_featured_post_post() {
        $this->set_featured_post_post();
    }

    /**
     * [set_featured_post]
     * @param  $ModuleID
     * @param  $ModuleEntityID
     * @param  $ActivityGUID
     * @return [JSON]
     */
    public function set_featured_post_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $this->form_validation->set_rules('ActivityGUID', 'ActivityGUID', 'trim|required');
            $this->form_validation->set_rules('ModuleID', 'ModuleID', 'trim|required');
            $this->form_validation->set_rules('ModuleEntityID', 'ModuleEntityID', 'trim|required');

            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
                $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '';
                $module_entity_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : '';
                $activity_id = 0;
                $post_type = 0;
                $visibility = 0;
                $details = get_detail_by_guid($activity_guid, 0, 'ActivityID,PostType,IsVisible', 2);
                if ($details) {
                    $activity_id = $details['ActivityID'];
                    $post_type = $details['PostType'];
                    $visibility = $details['IsVisible'];
                }
                $return['Data'] = $this->activity_model->set_featured_post($user_id, $module_id, $module_entity_id, $activity_id);
                if($return['Data']['IsFeatured'] == 1) {
                   // initiate_worker_job('post_notification', array('ActivityID' => $activity_id, 'UserID' => $user_id, 'SenderName' => $this->LoggedInName, 'NotificationTypeKey' => 'post_feature','LocalityID' => $this->LocalityID), '', 'notification');
                }
                $return['ResponseCode'] = self::HTTP_OK;
                $return['Message'] = lang('success');
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }

        $this->response($return);
    }

    public function pin_to_top_post() {
        $return = $this->return;
        $data = $this->post_data;
        $entity_guid = isset($data['EntityGUID']) ? $data['EntityGUID'] : '';
        $details = get_detail_by_guid($entity_guid, 0, 'ActivityID,PostType,IsVisible,IsFeatured', 2);
        $activity_id = 0;
        $is_visible = 0;
        $post_type = 0;
        $is_featured = 0;
        if ($details) {
            $activity_id = $details['ActivityID'];
            $is_visible = $details['IsVisible'];
            $is_featured = $details['IsFeatured'];
            $post_type = $details['PostType'];
        }
        $user_id = $this->UserID;
        $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
        if($is_super_admin) {
            $flag = $this->activity_model->pin_to_top($activity_id, $is_visible, $is_featured, $post_type);            
            if(!$flag) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = "Maximum one post can be mark as pin to top";
            } else {
                //initiate_worker_job('post_notification', array('ActivityID' => $activity_id, 'UserID' => $user_id, 'SenderName' => $this->LoggedInName, 'NotificationTypeKey' => 'pin_post','LocalityID' => $this->LocalityID), '', 'notification');
            }
        } else {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('permission_denied');
        }
        $this->response($return);
    }

    public function pin_articles_post() {
        $return = $this->return;
        $data = $this->post_data;
        $articles = isset($data['Articles']) ? $data['Articles'] : array();
        if ($articles) {
            foreach ($articles as $val) {
                $entity_guid = $val;
                $activity_id = get_detail_by_guid($entity_guid, 0);
                $this->activity_model->pin_to_top($activity_id);
            }
        }
        $this->response($return);
    }

    function fav_articles_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $entity_guid = $data['EntityGUID'];
        $module_id = $data['ModuleID'];
        $role_id = isset($this->RoleID) ? $this->RoleID : '';
        if ($module_id == 3 && $this->LoggedInGUID == $entity_guid) {
            $entity_id = $user_id;
        } else {
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
        }
        $post_type = isset($data['PostType']) ? $data['PostType'] : 0;
        $feed_sort_by = isset($data['FeedSortBy']) ? $data['FeedSortBy'] : 1;
        $is_media_exists = isset($data['IsMediaExists']) ? $data['IsMediaExists'] : 2;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
        $filter_type = isset($data['ActivityFilterType']) ? $data['ActivityFilterType'] : 0;
        $feed_user = isset($data['FeedUser']) ? $data['FeedUser'] : 0;
        $user_feed = 0;
        if ($post_type && is_array($post_type) && count($post_type) == 1 && $post_type[0] == 0) {
            $post_type = 0;
        }
        if ($feed_user) {
            if (is_array($feed_user)) {
                $user_feed = array();
                foreach ($feed_user as $uid) {
                    $user_feed[] = get_detail_by_guid($uid, 3);
                }
            } else {
                $user_feed = get_detail_by_guid($feed_user, 3);
            }
        }
        $feed_user = $user_feed;
        $search_keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $start_date = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';
        $end_date = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
        $reminder_date = (isset($data['ReminderFilterDate']) && !empty($data['ReminderFilterDate'])) ? $data['ReminderFilterDate'] : array();
        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        $mentions = isset($data['Mentions']) ? $data['Mentions'] : array();
        $activity_type_filter = !empty($data['ActivityFilter']) ? $data['ActivityFilter'] : array();
        $comment_guid = isset($data['CommentGUID']) ? $data['CommentGUID'] : '';
        //View Entity Tags
        $view_entity_tags = isset($data['ViewEntityTags']) ? $data['ViewEntityTags'] : 0;
        $tags = isset($data['Tags']) ? $data['Tags'] : array();
        $comment_id = '';
        if (!empty($comment_guid)) {
            $comment_id = get_detail_by_guid($comment_guid, 20);
        }
        if ($mentions) {
            foreach ($mentions as $key => $value) {
                $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
            }
        }

        $this->user_model->set_user_time_zone($user_id);
        $this->user_model->set_user_profile_url($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        $this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_visible_group_list($user_id);
        $this->forum_model->set_visible_category_list($user_id);
        // $this->group_model->set_user_categoty_group_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_model->set_user_activity_archive($user_id);
        //$this->privacy_model->set_privacy_options($user_id);
        $this->event_model->set_user_joined_events($user_id);
        $this->page_model->set_feed_pages_condition($user_id);

        $FriendFollowersList = $this->user_model->get_friend_followers_list();
        if (!empty($FriendFollowersList)) {
            $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
        }
        $temp_result = $this->activity_model->fav_articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, false);
        $return['Data'] = $temp_result['Data'];
        $return['FavIDs'] = $temp_result['FavIDs'];
        $return['TotalRecords'] = $this->activity_model->fav_articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, false);
        $this->response($return); /* Final Output */
    }

    function articles_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        $entity_guid = $data['EntityGUID'];
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
        $role_id = isset($this->RoleID) ? $this->RoleID : '';
        if ($module_id == 3 && $this->LoggedInGUID == $entity_guid) {
            $entity_id = $user_id;
        } else {
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
        }
        $post_type = isset($data['PostType']) ? $data['PostType'] : array(4);
        $feed_sort_by = isset($data['FeedSortBy']) ? $data['FeedSortBy'] : 1;
        $is_media_exists = isset($data['IsMediaExists']) ? $data['IsMediaExists'] : 2;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
        $filter_type = isset($data['ActivityFilterType']) ? $data['ActivityFilterType'] : 0;
        $feed_user = isset($data['FeedUser']) ? $data['FeedUser'] : 0;
        $user_feed = 0;
        $article_type = isset($data['ArticleType']) ? $data['ArticleType'] : '';
        $exclude_activity_id = isset($data['ExcludeActivityID']) ? $data['ExcludeActivityID'] : '';
        if ($post_type && is_array($post_type) && count($post_type) == 1 && $post_type[0] == 0) {
            $post_type = 0;
        }

        if ($article_type) {
            $article_type = ucfirst($article_type);
        }
        if ($feed_user) {
            if (is_array($feed_user)) {
                $user_feed = array();
                foreach ($feed_user as $uid) {
                    $user_feed[] = get_detail_by_guid($uid, 3);
                }
            } else {
                $user_feed = get_detail_by_guid($feed_user, 3);
            }
        }
        $feed_user = $user_feed;
        $search_keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $start_date = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';

        $end_date = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
        $reminder_date = (isset($data['ReminderFilterDate']) && !empty($data['ReminderFilterDate'])) ? $data['ReminderFilterDate'] : array();

        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        $mentions = isset($data['Mentions']) ? $data['Mentions'] : array();
        $activity_type_filter = !empty($data['ActivityFilter']) ? $data['ActivityFilter'] : array();
        $show_from = isset($data['ShowFrom']) ? $data['ShowFrom'] : array();
        //View Entity Tags
        $view_entity_tags = isset($data['ViewEntityTags']) ? $data['ViewEntityTags'] : 0;
        $tags = isset($data['Tags']) ? $data['Tags'] : array();

        if ($mentions) {
            foreach ($mentions as $key => $value) {
                $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
            }
        }

        $this->user_model->set_user_time_zone($user_id);
        $this->user_model->set_user_profile_url($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        $this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_visible_group_list($user_id);
        $this->forum_model->set_visible_category_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_model->set_user_activity_archive($user_id);
        //$this->privacy_model->set_privacy_options($user_id);
        //$this->event_model->set_user_joined_events($user_id);
        //$this->page_model->set_feed_pages_condition($user_id);

        $FriendFollowersList = $this->user_model->get_friend_followers_list();
        if (!empty($FriendFollowersList)) {
            $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
        }

        if ($article_type == 'Fav' || $article_type == 'Favourite') {
            $temp_result_fav = $this->activity_model->fav_articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, false);
            $return['Data'] = $temp_result_fav['Data'];
            $return['TotalRecords'] = 0;
        } else if ($article_type == 'Recommended') {
            $temp_result_recommended = $this->activity_model->recommended_article($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags);
            $return['Data'] = $temp_result_recommended['Data'];
            $return['TotalRecords'] = 0;
        } else if ($article_type == 'Suggested') {
            $temp_result_suggested = $this->activity_model->suggested_article($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, false);
            $return['Data'] = $temp_result_suggested['Data'];
            $return['TotalRecords'] = 0;
        } else if($article_type == 'MyCreated') {
            $return['Data'] = $this->activity_model->articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, $show_from, $exclude_activity_id, ['createdBy' => $this->UserID]);
            $return['TotalRecords'] = 0; //$this->activity_model->articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter,array(),$view_entity_tags,$role_id,$post_type,$tags);
        } else {
            $return['Data'] = $this->activity_model->articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, $show_from, $exclude_activity_id);
            $return['TotalRecords'] = 0; //$this->activity_model->articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter,array(),$view_entity_tags,$role_id,$post_type,$tags);
        }
        $this->response($return); /* Final Output */
    }

    function trending_article_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '1';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '4';
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '';
        $module_entity_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : '';
        $return['Data'] = $this->activity_model->trending_article($user_id, $module_id, $module_entity_id, $page_no, $page_size);
        $this->response($return); /* Final Output */
    }

    function trending_widget_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '1';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '4';
        
        $this->user_model->set_user_time_zone($user_id);
        $this->user_model->set_user_profile_url($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        $this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_visible_group_list($user_id);
        $this->forum_model->set_visible_category_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_model->set_user_activity_archive($user_id);
        //$this->privacy_model->set_privacy_options($user_id);
        $this->event_model->set_user_joined_events($user_id);
        $this->page_model->set_feed_pages_condition($user_id);

        $FriendFollowersList = $this->user_model->get_friend_followers_list();
        if (!empty($FriendFollowersList)) {
            $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
        }
        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
        $activity_id = 0;
        if ($activity_guid) {
            $activity_id = get_detail_by_guid($activity_guid);
        }
        $return['Data'] = $this->activity_model->trending_widget($user_id, $page_no, $page_size, $activity_id);
        $this->response($return); /* Final Output */
    }

    function recommended_articles_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $entity_guid = $data['EntityGUID'];
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;
        $role_id = isset($this->RoleID) ? $this->RoleID : '';
        if ($module_id == 3 && $this->LoggedInGUID == $entity_guid) {
            $module_entity_id = $user_id;
        } else {
            $module_entity_id = get_detail_by_guid($entity_guid, $module_id);
        }
        $post_type = isset($data['PostType']) ? $data['PostType'] : 0;
        $feed_sort_by = isset($data['FeedSortBy']) ? $data['FeedSortBy'] : 1;
        $is_media_exists = isset($data['IsMediaExists']) ? $data['IsMediaExists'] : 2;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
        $filter_type = isset($data['ActivityFilterType']) ? $data['ActivityFilterType'] : 0;
        $feed_user = isset($data['FeedUser']) ? $data['FeedUser'] : 0;
        $user_feed = 0;
        $article_type = isset($data['ArticleType']) ? $data['ArticleType'] : '';
        if ($post_type && is_array($post_type) && count($post_type) == 1 && $post_type[0] == 0) {
            $post_type = 0;
        }
        if ($feed_user) {
            if (is_array($feed_user)) {
                $user_feed = array();
                foreach ($feed_user as $uid) {
                    $user_feed[] = get_detail_by_guid($uid, 3);
                }
            } else {
                $user_feed = get_detail_by_guid($feed_user, 3);
            }
        }
        $feed_user = $user_feed;
        $search_keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $start_date = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';

        $end_date = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
        $reminder_date = (isset($data['ReminderFilterDate']) && !empty($data['ReminderFilterDate'])) ? $data['ReminderFilterDate'] : array();

        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        $mentions = isset($data['Mentions']) ? $data['Mentions'] : array();

        $activity_type_filter = !empty($data['ActivityFilter']) ? $data['ActivityFilter'] : array();

        //View Entity Tags
        $view_entity_tags = isset($data['ViewEntityTags']) ? $data['ViewEntityTags'] : 0;
        $tags = isset($data['Tags']) ? $data['Tags'] : array();

        if ($mentions) {
            foreach ($mentions as $key => $value) {
                $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
            }
        }

        $this->user_model->set_user_time_zone($user_id);
        $this->user_model->set_user_profile_url($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        $this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_visible_group_list($user_id);
        $this->forum_model->set_visible_category_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_model->set_user_activity_archive($user_id);
        $this->event_model->set_user_joined_events($user_id);
        $this->page_model->set_feed_pages_condition($user_id);

        $FriendFollowersList = $this->user_model->get_friend_followers_list();
        if (!empty($FriendFollowersList)) {
            $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
        }

        $temp_result = $this->activity_model->recommended_article($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $module_entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags);
        $return['Data'] = $temp_result['Data'];
        $this->response($return); /* Final Output */
    }

    public function remove_articles_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $articles = isset($data['Articles']) ? $data['Articles'] : array();
        if ($articles) {
            $this->activity_model->remove_articles($user_id, $articles);
        }
        $this->response($return);
    }

    public function recommend_articles_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $articles = isset($data['Articles']) ? $data['Articles'] : array();
        if ($articles) {
            $this->activity_model->recommend_articles($user_id, $articles);
        }
        $this->response($return);
    }

    public function remove_recommended_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $articles = isset($data['Articles']) ? $data['Articles'] : array();
        if ($articles) {
            $this->activity_model->remove_recommended($user_id, $articles);
            if (CACHE_ENABLE) {
                $this->cache->delete('article_widgets_' . $user_id);
            }
        }
        $this->response($return);
    }

    function article_widgets_post() {
        $user_id = $this->UserID;
        if (CACHE_ENABLE) {
            $cache_data = $this->cache->get('article_widgets_' . $user_id);
            if (!empty($cache_data)) {
                //$this->response($cache_data);
            }
        }
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $entity_guid = $data['EntityGUID'];
        $module_id = $data['ModuleID'];
        $role_id = isset($this->RoleID) ? $this->RoleID : '';
        if ($module_id == 3 && $this->LoggedInGUID == $entity_guid) {
            $entity_id = $user_id;
        } else {
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
        }
        $post_type = isset($data['PostType']) ? $data['PostType'] : 0;
        $feed_sort_by = isset($data['FeedSortBy']) ? $data['FeedSortBy'] : 2;
        $is_media_exists = isset($data['IsMediaExists']) ? $data['IsMediaExists'] : 2;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
        $filter_type = isset($data['ActivityFilterType']) ? $data['ActivityFilterType'] : 0;
        $feed_user = isset($data['FeedUser']) ? $data['FeedUser'] : 0;
        $user_feed = 0;
        if ($post_type && is_array($post_type) && count($post_type) == 1 && $post_type[0] == 0) {
            $post_type = 0;
        }
        if ($feed_user) {
            if (is_array($feed_user)) {
                $user_feed = array();
                foreach ($feed_user as $uid) {
                    $user_feed[] = get_detail_by_guid($uid, 3);
                }
            } else {
                $user_feed = get_detail_by_guid($feed_user, 3);
            }
        }
        $feed_user = $user_feed;
        $search_keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $start_date = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';

        $end_date = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
        $reminder_date = (isset($data['ReminderFilterDate']) && !empty($data['ReminderFilterDate'])) ? $data['ReminderFilterDate'] : array();

        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        $mentions = isset($data['Mentions']) ? $data['Mentions'] : array();
        $activity_type_filter = !empty($data['ActivityFilter']) ? $data['ActivityFilter'] : array();
        $comment_guid = isset($data['CommentGUID']) ? $data['CommentGUID'] : '';
        //View Entity Tags
        $view_entity_tags = isset($data['ViewEntityTags']) ? $data['ViewEntityTags'] : 0;
        $tags = isset($data['Tags']) ? $data['Tags'] : array();
	$comment_id         = '';
        if (!empty($comment_guid)) {
            $comment_id = get_detail_by_guid($comment_guid, 20);
        }
        if ($mentions) {
            foreach ($mentions as $key => $value) {
                $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
            }
        }

        $this->user_model->set_user_time_zone($user_id);
        $this->user_model->set_user_profile_url($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        $this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_visible_group_list($user_id);
        $this->forum_model->set_visible_category_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_model->set_user_activity_archive($user_id);
        $this->event_model->set_user_joined_events($user_id);
        $this->page_model->set_feed_pages_condition($user_id);

        $FriendFollowersList = $this->user_model->get_friend_followers_list();
        if (!empty($FriendFollowersList)) {
            $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
        }
        $temp_result_fav = $this->activity_model->fav_articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, false);
        $return_fav['Data'] = $temp_result_fav['Data'];
        $return_fav['TotalRecords'] = 0;
        $exclude_ids_fav = $temp_result_fav['FavIDs'];

        $return['Data']['FavArticle'] = $return_fav;
        $temp_result_recommended = $this->activity_model->recommended_article($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, false, $exclude_ids_fav);
        $return['Data']['RecommendedArticle']['Data'] = $temp_result_recommended['Data'];
        $return['Data']['RecommendedArticle']['TotalRecords'] = 0;
        $exclude_ids_recommended = $temp_result_recommended['RecommendedIDs'];
        $temp_result_suggested = $this->activity_model->suggested_article($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, false, $exclude_ids_recommended);
        $return['Data']['SuggestedArticle']['Data'] = $temp_result_suggested['Data'];
        $return['Data']['SuggestedArticle']['TotalRecords'] = 0;
        if (CACHE_ENABLE) {
            $this->cache->save('article_widgets_' . $user_id, $return, 60);
        }
        $this->response($return); /* Final Output */
    }

    function related_activity_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $validation_rule[] = array(
            'field' => 'ActivityID',
            'label' => 'ActivityID',
            'rules' => 'trim|required',
        );
        $validation_rule[] = array(
            'field' => 'RelatedActivity[]',
            'label' => 'RelatedActivity',
            'rules' => 'trim|required',
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $activity_id = isset($data['ActivityID']) ? $data['ActivityID'] : '';
            $related_activity = isset($data['RelatedActivity']) ? $data['RelatedActivity'] : array();
            $related_activity = array_unique($related_activity);

            $activity_details = get_detail_by_id($activity_id, 0, "UserID,ModuleID,ModuleEntityID", 2);

            $is_admin = FALSE;
            //Check permission of logged in user for this tag         
            if ($activity_details['ModuleID'] && $activity_details['ModuleEntityID']) {
                switch ($activity_details['ModuleID']) {
                    case '1': //Group
                        // Check permission for Group Admin + post owner + super admin
                        $permission = $this->group_model->is_admin($user_id, $activity_details['ModuleEntityID']);
                        if ($permission || $activity_details['UserID'] == $user_id)
                            $is_admin = TRUE;
                        break;
                    case '3':
                        //If user is wall owner + post owner + super admin                             
                        if ($user_id == $activity_details['ModuleEntityID'] || $activity_details['UserID'] == $user_id)
                            $is_admin = TRUE;
                        break;
                    case '18':
                        //Tags will be Editable by Page Owner + post owner + super admin
                        $page_details = get_detail_by_id($activity_details['ModuleEntityID'], '18', "UserID", 2);
                        if ($page_details['UserID'] == $user_id || $activity_details['UserID'] == $user_id)
                            $is_admin = TRUE;
                        break;
                    case '14':
                        // Tags will be Editable by Event Creator + post owner + super admin
                        $event_details = get_detail_by_id($activity_details['ModuleEntityID'], '14', "CreatedBy", 2);
                        if ($event_details['CreatedBy'] == $user_id || $activity_details['UserID'] == $user_id)
                            $is_admin = TRUE;
                        break;
                    case '34':
                        // Tags will be Editable by Event Creator + post owner + super admin
                        $category_permissions = $this->forum_model->check_forum_permissions($user_id, $activity_details['ModuleEntityID']);
                        if ($category_permissions['IsAdmin'] || $activity_details['UserID'] == $user_id)
                            $is_admin = TRUE;
                        break;
                    default:
                        # code...
                        break;
                }

                if ($is_admin || $this->user_model->is_super_admin($user_id)) {
                    $this->activity_model->related_activity($activity_id, $related_activity);
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        }
        $this->response($return); /* Final Output */
    }

    function get_related_activity_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $validation_rule[] = array(
            'field' => 'ActivityID',
            'label' => 'ActivityID',
            'rules' => 'trim|required',
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $activity_id = isset($data['ActivityID']) ? $data['ActivityID'] : '';
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 0;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : 0;

            $this->user_model->set_user_time_zone($user_id);
            $this->user_model->set_user_profile_url($user_id);
            $this->activity_model->set_block_user_list($user_id, 3);
            $this->user_model->set_friend_followers_list($user_id);
            $this->group_model->set_visible_group_list($user_id);
            $this->forum_model->set_visible_category_list($user_id);
            $this->favourite_model->set_user_favourite($user_id);
            $this->flag_model->set_user_flagged($user_id);
            $this->activity_model->set_user_activity_archive($user_id);
            $this->event_model->set_user_joined_events($user_id);
            $this->page_model->set_feed_pages_condition($user_id);

            $FriendFollowersList = $this->user_model->get_friend_followers_list();
            if (!empty($FriendFollowersList)) {
                $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
            }

            $return['Data'] = $this->activity_model->get_related_activity($user_id, $activity_id, $page_no, $page_size);
        }
        $this->response($return); /* Final Output */
    }

    function entity_suggestion_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $validation_rule[] = array(
            'field' => 'SearchKeyword',
            'label' => 'SearchKeyword',
            'rules' => 'trim|required',
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
            $this->group_model->set_visible_group_list($user_id);
            $this->forum_model->set_visible_category_list($user_id);
            $return['Data'] = $this->activity_model->entity_suggestion($search_keyword);
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: get_welcome_question_post
     * Description: Get welcome Question on FrontEnd
     */
    public function get_welcome_question_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $user_id = $this->UserID;
        if (!isset($cookie)) {
            //get ActivityRuleID for current user
            $activity_rule_id = $this->activityrule_model->getActivityRules($user_id, 0, true);

            //get welcome question
            $return['Data'] = $this->activity_model->get_welcome_question($user_id, $activity_rule_id['ActivityRuleID']);
        }
        $this->response($return);
    }

    /**
     * Function Name: skip_welcome_question_post
     * Description: skip welcome Question on FrontEnd
     */
    public function skip_welcome_question_post() {
        $return = $this->return;
        $cookie = array(
            'name' => 'welcome_question',
            'value' => 'aa',
            'expire' => '86400'  // 1 day expiration time
        );
        $this->input->set_cookie($cookie);
        $this->response($return);
    }

    /**
     * Function Name: mydesk
     * @param ProfileID,PageNo,PageSize,ActivityTypeID,EntityID,WallType,ActivityGuID,AllActivity
     * Description: Get list of activity according to input conditions
     */
    public function mydesk_post() {
        /* Define variables - starts */
        $return = $this->return;
        $return['TotalRecords'] = 0; /* added by gautam */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $role_id = isset($this->RoleID) ? $this->RoleID : '';
        $entity_guid = isset($data['EntityGUID']) ? $data['EntityGUID'] : 0;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        if ($module_id == 3 && $this->LoggedInGUID == $entity_guid) {
            $entity_id = $user_id;
        } else {
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
        }

        $post_type = isset($data['PostType']) ? $data['PostType'] : 0;
        $feed_sort_by = isset($data['FeedSortBy']) ? $data['FeedSortBy'] : 1;
        $is_media_exists = isset($data['IsMediaExists']) ? $data['IsMediaExists'] : 2;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
        $filter_type = isset($data['ActivityFilterType']) ? $data['ActivityFilterType'] : 0;
        $feed_user = isset($data['FeedUser']) ? $data['FeedUser'] : 0;
        $mydesk_filters = isset($data['MyDesk']) ? $data['MyDesk'] : '';

        $this->load->model('activity/mydesk_model');
        if ($post_type && is_array($post_type) && count($post_type) == 1 && $post_type[0] == 0) {
            $post_type = 0;
        }

        $user_feed = 0;
        if ($feed_user) {
            if (is_array($feed_user)) {
                $user_feed = array();
                foreach ($feed_user as $uid) {
                    $user_feed[] = get_detail_by_guid($uid, 3);
                }
            } else {
                $user_feed = get_detail_by_guid($feed_user, 3);
            }
        }
        $feed_user = $user_feed;

        $search_keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $start_date = (isset($data['StartDate']) && !empty($data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['StartDate'])) : '';

        $end_date = (isset($data['EndDate']) && !empty($data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($data['EndDate'])) : '';
        $reminder_date = (isset($data['ReminderFilterDate']) && !empty($data['ReminderFilterDate'])) ? $data['ReminderFilterDate'] : array();

        $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        $activity_type_filter = !empty($data['ActivityFilter']) ? $data['ActivityFilter'] : array();
        //View Entity Tags
        $view_entity_tags = isset($data['ViewEntityTags']) ? $data['ViewEntityTags'] : 0;
        $tags = isset($data['Tags']) ? $data['Tags'] : array();

        $comment_guid = isset($data['CommentGUID']) ? $data['CommentGUID'] : '';
        $comment_id = '';
        if (!empty($comment_guid)) {
            $comment_id = get_detail_by_guid($comment_guid, 20);
        }
        $mentions = isset($data['Mentions']) ? $data['Mentions'] : array();
        if ($mentions) {
            foreach ($mentions as $key => $value) {
                $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
            }
        }

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

        $this->event_model->set_user_joined_events($user_id);
        $this->page_model->set_feed_pages_condition($user_id);

        $rules = array();

        $FriendFollowersList = $this->user_model->get_friend_followers_list();
        if (!empty($FriendFollowersList)) {
            $this->user_model->set_friends_of_friend_list($user_id, $FriendFollowersList['Friends']);
        }

        $activity = $this->mydesk_model->getMyDesk($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, $rules, $mydesk_filters);

        if ($page_no == '1') {
            $return['TotalRecords'] = $this->mydesk_model->getMyDesk($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), 1, 2, $post_type, $tags, $rules, $mydesk_filters);
        }

        $return['TotalFavouriteRecords'] = 0;
        $return['TotalReminderRecords'] = 0;
        if (count($activity) > 0) {
            $return['TotalReminderRecords'] = 1;
            $return['Data'] = $activity;
        }
        $return['PageSize'] = $page_size;
        $return['PageNo'] = $page_no;
        $return['LoggedInProfilePicture'] = $this->LoggedInProfilePicture;
        $return['LoggedInName'] = $this->LoggedInName;
        $this->response($return);
    }

    public function toggle_mydesk_task_post() {
        /* Define variables - starts */
        $return['Data'] = array();
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) {
            /* Validation - starts */
            if ($this->form_validation->run('api/toggle_mydesk_task') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else /* Validation - ends */ {
                $user_id = $this->UserID;
                $task_status = $data['TaskStatus'];
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                $this->load->model('activity/mydesk_model');
                $result = $this->mydesk_model->toggle_mydesk_task($activity_id, $user_id, $task_status);
                $return['ResponseCode'] = $result['ResponseCode'];
                $return['Message'] = $result['Message'];
                $return['Data'] = $result['Data'];
            }
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    /**
     * Function Name: sharePost
     * @param ActivityGuID,LoginSessionKey
     * Description: Share activity of other users
     */
    public function share_post_by_email_post() {
        $Return = $this->return;
        $UserID = $this->UserID;
        if ($this->form_validation->run('api/share_post_by_email') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $emails = $this->post('emails');
            $link = $this->post('link');
            $message = $this->post('message');

            //SHARE_ACTIVITY_BY_EMAIL
            if (count($emails) > 0) {
                foreach ($emails as $mail) {
                    $emailDataArr = array();
                    $emailDataArr['IsResend'] = 0;
                    $emailDataArr['FromUserID'] = $UserID;
                    $emailDataArr['Subject'] = ucfirst($this->session->userdata('FirstName')) . " share with you";
                    $emailDataArr['TemplateName'] = "emailer/share_activity";
                    $emailDataArr['Email'] = $mail;
                    $emailDataArr['EmailTypeID'] = 13;
                    $emailDataArr['UserID'] = 0;
                    $emailDataArr['StatusMessage'] = "VSocial share with you";
                    $emailDataArr['Data'] = array(
                        "Email" => $mail,
                        "Message" => $message,
                        "Link" => $link,
                        "FirstName" => ucfirst($this->session->userdata('FirstName')),
                        "LastName" => ucfirst($this->session->userdata('LastName')),
                        "ProfilePicture" => $this->session->userdata('ProfilePicture'),
                        "ProfileURL" => get_entity_url($this->session->userdata('UserID')),
                    );
                    sendEmailAndSave($emailDataArr, 1);
                }
            }
            $Return['Message'] = 'Email sent successfully.';
        }
        $this->response($Return);
    }

    /**
     * Function Name: shareEvent
     * @param ActivityGuID,LoginSessionKey
     * Description: Share activity of other users
     */
    public function share_event_by_email_post() {
        $Return = $this->return;
        $UserID = $this->UserID;
        if ($this->form_validation->run('api/share_event_by_email') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else {
            $emails = $this->post('emails');
            $link = $this->post('link');
            $message = $this->post('message');

            //SHARE_ACTIVITY_BY_EMAIL
            if (count($emails) > 0) {
                foreach ($emails as $mail) {
                    $emailDataArr = array();
                    $emailDataArr['IsResend'] = 0;
                    $emailDataArr['FromUserID'] = $UserID;
                    $emailDataArr['Subject'] = ucfirst($this->session->userdata('FirstName')) . " shared an event with you";
                    $emailDataArr['TemplateName'] = "emailer/share_event";
                    $emailDataArr['Email'] = $mail;
                    $emailDataArr['EmailTypeID'] = 38;
                    $emailDataArr['UserID'] = 0;
                    $emailDataArr['StatusMessage'] = "VSocial share with you";
                    $emailDataArr['Data'] = array(
                        "Email" => $mail,
                        "Message" => $message,
                        "Link" => $link,
                        "FirstName" => ucfirst($this->session->userdata('FirstName')),
                        "LastName" => ucfirst($this->session->userdata('LastName')),
                        "ProfilePicture" => $this->session->userdata('ProfilePicture'),
                        "ProfileURL" => get_entity_url($this->session->userdata('UserID')),
                    );
                    sendEmailAndSave($emailDataArr, 1);
                }
            }
            $Return['Message'] = 'Email sent successfully.';
        }
        $this->response($Return);
    }

    public function activity_media_post() {
        /* Define variables - starts */
        $return['Data'] = array();
        /* Define variables - ends */
        $data = $this->post_data;
        if (isset($data)) {
            /* Validation - starts */
            $config = array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $user_id = $this->UserID;
                $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                $page_size = isset($data['PageSize']) ? $data['PageSize'] : 4;
                $activity_id = get_detail_by_guid($data['ActivityGUID']);
                $return['Data'] = $this->activity_model->get_activity_media($activity_id, $user_id, '', 'Activity', $page_no, $page_size);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

}
