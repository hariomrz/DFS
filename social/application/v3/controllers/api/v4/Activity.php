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
            'group/group_model',
            'users/user_model',            
            'media/media_model',
            'users/friend_model',
            'activity/activity_model',
            'favourite_model',
            'subscribe_model',
            'notification_model',
            'activity/activityrule_model',
            'polls/polls_model',
            'tag/tag_model',
        ));
    }

    

    /**
     * Function Name: index
     * @param ProfileID,PageNo,PageSize,ActivityTypeID,EntityID,WallType,ActivityGuID,AllActivity
     * Description: Get list of activity according to input conditions
     */
    public function index_post() {
        $this->load->model(array('privacy/privacy_model', 'activity/activity_hide_model'));
       // $this->benchmark->mark('feed_start');
        $return = $this->return;
        $data = $this->post_data;
        $return['Ar'] = 0;
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

        $tag_id = isset($data['TagID']) ? $data['TagID'] : 0;

        $this->activity_model->set_top_contributors($tag_id);
        
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
        $this->user_model->set_friend_followers_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);
        $this->flag_model->set_user_flagged($user_id);
        $this->activity_hide_model->set_user_hide_activity($user_id);

        $this->tag_model->set_following_tag_list($user_id);
        $this->tag_model->set_muted_tag_list($user_id);

        $this->load->model(array('quiz/quiz_model'));
        $this->quiz_model->set_following_quiz_list($user_id);
        
        if ($dummy_users_only) {
            $return['TotalRecords'] = 0;
            $activity = $this->activity_model->getDummyUserActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, array(), $data);
            if ($page_no == '1') {
                $return['TotalRecords'] = $this->activity_model->getDummyUserActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, array(), $data);
            }
        } else if ($all_activity == 1) {
            
            if(!empty($tags)) {
                $tags = $this->tag_model->get_category_tag_ids($tags);
            }

            if ($filter_type == 11) {
                $tags[] = $this->tag_model->is_tag_exist('featured', 1);
            } else if(!empty($tag_id)) {
                $tags[] = $tag_id;                
            }
            
            
            $this->privacy_model->set_privacy_options($user_id);
            
            // Set user activity total for rule check
            $this->activityrule_model->setUserActivityTotal($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, $post_type, $tags);

            // Get rules for this user
            $rules = $this->activityrule_model->getActivityRules($user_id);
            
            $activity = $this->activity_model->getFeedActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), $view_entity_tags, $role_id, $post_type, $tags, $rules, $data);
            
            if ($page_no == '1' && IS_ARCHIVE_DB == 0) {
               // $return['TotalRecords'] = $this->activity_model->getFeedActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), 1, 2, $post_type, $tags, $rules, $data);                
                $is_admin = $this->user_model->is_super_admin($user_id, 1);
                $return['IsAdmin'] = $is_admin;
                $row = $this->activity_model->is_daily_digest_exist();
                if(!empty($row) && isset($row['formatted_date'])) {
                    $return['DailyDigestDate'] = $row['formatted_date'];
                }
                
                if(!empty($tag_id)) {
                    $return['Tag'] = $this->tag_model->details($tag_id, $user_id, 0);                
                }
            }
            if (count($activity) == 0 || count($activity) < $page_size) { //check for archival script.                 
                $data['AR'] = 1 ;               
                $ar_cnt = $this->activity_model->getFeedActivities($user_id, 1, 5, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 1, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, array(), 1, 2, $post_type, $tags, $rules, $data);                
                if($ar_cnt > 0) {
                    $return['Ar'] = 1;
                }                
            }
        } else {
            $module_entity_guid = $this->LoggedInGUID;
            
            $entity_module_id = 3;
            $this->load->model(array('activity/activity_wall_model'));
            $module_entity_id = get_detail_by_guid($module_entity_guid, $entity_module_id);
            $activity = $this->activity_model->getActivities(
                    $entity_id, $module_id, $page_no, $page_size, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, false, 'ALL', $activity_type_filter, $module_entity_id, $entity_module_id, $comment_id, $view_entity_tags, $role_id, $post_type, $tags, $data);

            if (count($activity) == 0 || count($activity) < $page_size) { //check for archival script.
                $data['AR'] = 1 ;              
                $ar_cnt = $this->activity_model->getActivities(
                    $entity_id, $module_id, 0, 0, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, true, 'ALL', array(), '', '', '', '', 2, $post_type, $tags, $data);
                            
                if($ar_cnt > 0) {
                    $return['Ar'] = 1;
                }
                
            }        
            
        }

        if (count($activity) > 0) {
            $return['Data'] = $activity;
        }

        $this->response($return);
    }   

}