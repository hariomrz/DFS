<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_front_helper_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    public function set_promotion($activity_id = 0, $entity_id = 0, $entity_type = '', $is_promoted = NULL) {

        if ($entity_type == 'ACTIVITY') {
            $activity_id = $entity_id;
        }

        if ($entity_type == 'COMMENT') {

            //Stop on post comment like
            return;

            $this->db->select('EntityID');
            $this->db->where('EntityType', 'ACTIVITY');
            $this->db->where('PostCommentID', $entity_id);
            $this->db->limit(1);
            $query = $this->db->get(POSTCOMMENTS);
            $row = $query->row_array();
            if (!empty($row['EntityID'])) {
                $activity_id = $row['EntityID'];
            }
        }

        if (!$activity_id) {
            return;
        }

        // Set promotion date if activity is promoted
        $activity_update_data = array(
            'PromotedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
        );
        $where_query = "ActivityID = $activity_id";
        if ($is_promoted === NULL) {
            //$this->db->where('IsPromoted', 1);
            $where_query .= " AND IsPromoted = 0";
        } else {
            $activity_update_data = array(
                'PromotedDate' => ($is_promoted == 1) ? 'CreatedDate' : get_current_date('%Y-%m-%d %H:%i:%s'),
                'IsPromoted' => $is_promoted
            );
            //$activity_update_data['IsPromoted'] = $is_promoted;
        }

        //$this->db->where('ActivityID', $activity_id);
        //$this->db->update(ACTIVITY, $activity_update_data);

        $update_data_fields = [];
        foreach ($activity_update_data as $field => $val) {
            if ($val == 'CreatedDate') {
                $update_data_fields[] = " $field = $val ";
            } else {
                $update_data_fields[] = " $field = '$val' ";
            }
        }
        $update_data_fields = implode(',', $update_data_fields);
        $update_entity_query = " Update " . ACTIVITY . " SET $update_data_fields ";
        $update_entity_query .= " Where $where_query";
        $query = $this->db->query($update_entity_query);
    }

    /**
     * [get_entity_bradcrumbs Get entity bradcrumbs]
     * @param  [int]    $module_id    [Module ID]      
     * @param  [int]    $module_entity_id    [Module Entity ID]      
     * @return [array]                [breadcrumb details]
     */
    function get_entity_bradcrumbs($module_id, $module_entity_id) {

        // For Group
        if ($module_id == 1) {
            $this->db->select('G.GroupID, G.GroupGUID, G.GroupName, G.Type');
            $this->db->from(GROUPS . ' G');
            $this->db->where('G.GroupID', $module_entity_id);
            $this->db->where('G.StatusID', '2');
            $this->db->limit(1);
            $query = $this->db->get();
            $row = $query->row_array();
            $url_name = $row['GroupName'];
            if (isset($row['Type']) && $row['Type'] == 'INFORMAL') {
                $this->load->model(array('group/group_model'));
                $group_members = $this->group_model->members($module_entity_id, $user_id);
                $group_member_names = [];
                if (is_array($$group_members)) {
                    foreach ($group_members as $entity_memeber) {
                        $group_member_names[] = $entity_memeber['FirstName'] . ' ' . $entity_memeber['LastName'];
                    }
                }
                $group_names = implode(', ', $group_member_names);
                $row['GroupName'] = $group_names;
                $url_name = $row['GroupGUID'];
            }

            if (empty($row['GroupID'])) {
                return [];
            }
            $this->load->model('group/group_model');
            $url = $this->group_model->get_group_url($row['GroupID'], $url_name, false, 'index');
            $breadcrumb_arr = [array(
            'url' => $url,
            'label' => $row['GroupName']
            )];

            return $breadcrumb_arr;
        }

        // For page
        if ($module_id == 18) {
            $this->db->select('P.PageID, P.Name, P.PageURL');
            $this->db->from(PAGES . ' P');
            $this->db->where('P.PageID', $module_entity_id);
            $this->db->where('P.StatusID', '2');
            $this->db->limit(1);
            $query = $this->db->get();
            $row = $query->row_array();

            if (empty($row['PageID'])) {
                return [];
            }

            $breadcrumb_arr = [array(
            'url' => 'page/' . $row['PageURL'],
            'label' => $row['Name']
            )];

            return $breadcrumb_arr;
        }

        // For Event
        if ($module_id == 14) {
            $this->db->select('E.EventID, E.EventGUID, E.Title');
            $this->db->from(EVENTS . ' E');
            $this->db->where('E.EventID', $module_entity_id);
            $this->db->where('E.IsDeleted', 0);
            $this->db->limit(1);
            $query = $this->db->get();
            $row = $query->row_array();

            if (empty($row['EventID'])) {
                return [];
            }

            $this->load->model('events/event_model');
            $url = $this->event_model->getViewEventUrl($row['EventGUID'], $row['Title'], false);
            $breadcrumb_arr = [array(
                'url' => $url,
                'label' => $row['Title']
            )];

            return $breadcrumb_arr;
        }

        // For User
        if ($module_id == 3) {
            $this->db->select('U.UserID, PU.Url, CONCAT(FirstName, " ", LastName) AS Name');
            $this->db->from(USERS . ' U');
            $this->db->join(PROFILEURL . ' PU', 'PU.EntityID = U.UserID AND PU.EntityType = "User"', 'LEFT');
            $this->db->where('U.UserID', $module_entity_id);
            $this->db->where_not_in('U.StatusID', array(3, 4));
            $this->db->limit(1);
            $query = $this->db->get();
            $row = $query->row_array();

            if (empty($row['UserID'])) {
                return [];
            }

            $breadcrumb_arr = [array(
            'url' => '' . $row['Url'],
            'label' => $row['Name']
            )];

            return $breadcrumb_arr;
        }

        // For Forum
        if ($module_id == 34) {
            $cat_data = $this->get_forum_or_category($module_entity_id, 34);
            if (empty($cat_data['ForumCategoryID'])) {
                return [];
            }
            $forum_data = $this->get_forum_or_category(0, 33, $cat_data['ForumID']);
            if (empty($forum_data['ForumID'])) {
                return [];
            }

            $breadcrumb_arr = [];
            $breadcrumb_arr[] = array(
                'url' => 'community/' . $forum_data['URL'] . '/' . $cat_data['URL'],
                'label' => $cat_data['Name']
            );

            // Get Parent category
            $cat_data = $this->get_forum_or_category($cat_data['ParentCategoryID'], 34);
            if (!empty($cat_data['ForumCategoryID'])) {
                $breadcrumb_arr[] = array(
                    'url' => 'community/' . $forum_data['URL'] . '/' . $cat_data['URL'],
                    'label' => $cat_data['Name']
                );
            }

            // Get Parent category
            $cat_data = $this->get_forum_or_category($cat_data['ParentCategoryID'], 34);
            if (!empty($cat_data['ForumCategoryID'])) {
                $breadcrumb_arr[] = array(
                    'url' => 'community/' . $forum_data['URL'] . '/' . $cat_data['URL'],
                    'label' => $cat_data['Name']
                );
            }

            // Get Parent category
            $cat_data = $this->get_forum_or_category($cat_data['ParentCategoryID'], 34);
            if (!empty($cat_data['ForumCategoryID'])) {
                $breadcrumb_arr[] = array(
                    'url' => 'community/' . $forum_data['URL'] . '/' . $cat_data['URL'],
                    'label' => $cat_data['Name']
                );
            }

            $breadcrumb_arr[] = array(
                'url' => 'community/' . $forum_data['URL'],
                'label' => $forum_data['Name']
            );

            $breadcrumb_arr = array_reverse($breadcrumb_arr);



            return $breadcrumb_arr;
        }
    }
    
    /**
     * [get_search_stripped_content Get stripped content]
     * @param  [string]    $content    [content]      
     * @return [string]                [stripped content]
     */
    function get_search_stripped_content($content) {
        $stripped_content = strip_tags($content);
        
        $stripped_content = strtr($stripped_content, array('{{' => ''));  
        $stripped_content = strtr($stripped_content, array('}}' => ''));        
        $stripped_content = preg_replace('(:\d+)', '', $stripped_content);
        return $stripped_content;
    }
    
    public function set_activities_data_temp() {        
        $this->db->select('PostSearchContent, ActivityID');
        $this->db->from(ACTIVITY);       
        $query = $this->db->get();        
        
        foreach($query->result_array() as $row) {
            $PostSearchContent = $this->get_search_stripped_content($row['PostSearchContent']);            
            $this->db->set('PostSearchContent', $PostSearchContent);
            $this->db->where('ActivityID',$row['ActivityID']);
            $this->db->update(ACTIVITY);
        }
    }

    public function set_search_contents() {
        $this->db->select('PostCommentID, PostComment', FALSE);
        $this->db->from(POSTCOMMENTS);
        $this->db->where('PostSearchComment IS NULL', NULL, FALSE);
        $this->db->limit(200);
        $query = $this->db->get();
        $post_comments = $query->result_array();
        foreach ($post_comments as $post_comment) {
            $postSrchComment = $this->get_search_stripped_content($post_comment['PostComment']);
            $this->db->set('PostSearchComment', $postSrchComment);
            $this->db->where('PostCommentID', $post_comment['PostCommentID']);
            $this->db->update(POSTCOMMENTS);
        }
        $this->db->select('ActivityID, PostContent', FALSE);
        $this->db->from(ACTIVITY);
        $this->db->where('PostSearchContent IS NULL', NULL, FALSE);
        $this->db->limit(200);
        $query = $this->db->get();
        $posts = $query->result_array();
        foreach ($posts as $post) {
            $postSrch = $this->get_search_stripped_content($post['PostContent']);
            $this->db->set('PostSearchContent', $postSrch);
            $this->db->where('ActivityID', $post['ActivityID']);
            $this->db->update(ACTIVITY);
        }
        if (empty($post_comments) && empty($posts)) {
            return;
        }
        $this->set_search_contents();
    }

    function get_forum_or_category($forum_cat_id = 0, $module_id, $forum_id = 0) {
        if ($module_id == 33) {
            $this->db->select('ForumID,ForumGUID,Name, URL', FALSE);
            $this->db->from(FORUM);
            $this->db->where('ForumID', $forum_id);
        } else {
            $this->db->select('ForumCategoryID,ForumCategoryGUID,Name, ForumID, ParentCategoryID, URL', FALSE);
            $this->db->from(FORUMCATEGORY);
            $this->db->where('ForumCategoryID', $forum_cat_id);
        }

        if (!$forum_id) {
            $this->db->where('StatusID', 2);
        }

        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    /* Get the list of members talking on this post */
    public function get_members_talking($activity_id, $limit = 3, $type = 'PC', $exclude_user_ids = []) {
        $result['Data'] = array();
        $result['TotalRecords'] = 0;
        //$friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $this->db->select('U.FirstName, CONCAT(U.FirstName," ",U.LastName) AS Name,U.UserGUID, PU.Url as ProfileUrl, U.UserID, U.ProfilePicture');
        $this->db->from(ACTIVITY . ' A');
        
        if($type == 'PC') {
            $this->db->join(POSTCOMMENTS . ' PC', "PC.EntityType = 'ACTIVITY' AND PC.EntityID = A.ActivityID AND PC.StatusID = 2");
        } else {
            $this->db->join(POSTLIKE . ' PL', "PL.EntityType = 'ACTIVITY' AND PL.EntityID = A.ActivityID AND PL.StatusID = 2");
        }
        
        $this->db->where("$type.UserID IS NOT NULL", NULL, FALSE);
        if($exclude_user_ids) {
            $this->db->where_not_in("$type.UserID", $exclude_user_ids);        
        }
        
        $this->db->join(USERS . ' U', "$type.UserID = U.UserID");
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"', 'LEFT');
        
        $this->db->where("A.ActivityID", $activity_id);        
        $this->db->group_by("$type.UserID");        
        
        $this->db->limit($limit);
        $query = $this->db->get();
        $members = $query->result_array();
        
        $like_members = [];
        if(count($members) < $limit && $type != 'PL') {            
            foreach ($members as $member) {
                $exclude_user_ids[] = $member['UserID'];
            }
            
            $new_limit = $limit - count($members);            
            $like_members = $this->get_members_talking($activity_id, $new_limit, 'PL', $exclude_user_ids);
        }
        
        $members = array_merge($members, $like_members);        
        return $members;
    }
    
    /**
     * [get_sticky_setting Get sticky setting for activity]
     * @param  [int]       $user_id         [User ID]
     * @param  [int]       $activity_id     [Activity ID]
     * @param  [int]       $role_id         [Role ID]
     * @param  [array]     $activity        [Activity details]      
     * @return [array]                      [Activity details with sticky setting]
     */
    function get_sticky_setting($user_id,$activity_id,$role_id,$activity){
        if(!$this->settings_model->isDisabled(42)){
            $this->load->model('sticky/sticky_model');
            //show sticky details and permission on activity
            $sticky_details = $this->sticky_model->check_sticky_exist($user_id, $activity_id, 1);
            $activity['SelfSticky'] = isset($sticky_details['SelfSticky']) ? (int) $sticky_details['SelfSticky'] : 0;
            $activity['GroupSticky'] = isset($sticky_details['GroupSticky']) ? (int) $sticky_details['GroupSticky'] : 0;
            $activity['EveryoneSticky'] = isset($sticky_details['EveryoneSticky']) ? (int) $sticky_details['EveryoneSticky'] : 0;
            $activity['CanMakeSticky'] = $this->sticky_model->can_make_sticky($user_id, $role_id, $activity['ModuleID'], $activity['ModuleEntityID']);
            //if anyone of these sticky options comes true we'll set selfSticky true;
            if (($activity['CanMakeSticky'] == 3) && ($activity['SelfSticky'] || $activity['GroupSticky'] || $activity['EveryoneSticky'])) {
                $activity['SelfSticky'] = 1;
            }
        }else{
            $activity['SelfSticky'] = 0;
            $activity['GroupSticky'] = 0;
            $activity['EveryoneSticky'] = 0;
            $activity['CanMakeSticky'] = 0;
        }
        return $activity;
    }
           
    /**
     * [get_activity_title Get activity title]
     * @param  [int]       $activity_id     [Activity ID]
     * @param  [string]    $post_content    [Post content]      
     * @return [string]                     [Activity title]
     */
    function get_activity_title($activity_id = 0, $post_content = '') {        
        $title = '';
        $post_content = $this->activity_model->parse_tag($post_content, 0, 0);
        if ($post_content != '') {
            $title = substr(strip_tags(html_entity_decode($post_content)), 0, 140);
        } else {
            $activity_data = get_detail_by_id($activity_id, 0, "PostTitle,PostContent", 2);
            if (!empty($activity_data)) {
                if (!empty($activity_data['PostTitle'])) {
                    $title = $activity_data['PostTitle'];
                } else {
                    $title = substr(strip_tags(html_entity_decode($activity_data['PostContent'])), 0, 140);
                }
            }
        }
        return $title;
    }
    
    /**
     * [view_count Get view count for an entity]
     * @param  [string]    $entity_type    [entity type] 
     * @param  [int]       $entity_id       [entity ID]
     * @return [int]                    [view count]
     */
    function view_count($entity_type, $entity_id) {
        $this->current_db->select('U.UserGUID,EV.UserID');
        $this->current_db->from(ENTITYVIEW . ' EV');
        $this->current_db->join(USERS . ' U', 'U.UserID=EV.UserID AND U.StatusID NOT IN(3,4)', 'left');
        $this->current_db->where('EV.EntityType', $entity_type);
        $this->current_db->where('EV.EntityID', $entity_id);      
        $query = $this->current_db->get();
        return $query->num_rows();        
    }

    /**
     * [response_count Get count for response]
     * @param  [string]    $entity_type    [entity type] 
     * @param  [int]       $entity_id       [entity ID]
     * @return [int]                    [view count]
     */
    function response_count($entity_id, $entity_type='ACTIVITY') {
        $this->db->select('PC.PostCommentID');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->where('PC.EntityType', $entity_type);
        $this->db->where('PC.EntityID', $entity_id);  
        $this->db->where('PC.StatusID', 2);    
        $query = $this->db->get();
        return $query->num_rows();        
    }

    /**
     * [solution_count Get solution for response]
     * @param  [string]    $entity_type    [entity type] 
     * @param  [int]       $entity_id       [entity ID]
     * @return [int]                    [view count]
     */
    function solution_count($entity_id, $entity_type='ACTIVITY') {
        $this->db->select('PC.PostCommentID');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->where('PC.EntityType', $entity_type);
        $this->db->where('PC.EntityID', $entity_id);  
        $this->db->where('PC.StatusID', 2);    
        $this->db->where('PC.Solution >', 0);    
        $query = $this->db->get();
        return $query->num_rows();        
    }

    /**
     * [user_response_count Get count for response]
    * @param  [int]       $user_id       [user ID]
     * @return [int]                    [ count]
     */
    function user_response_count($user_id) {
        $this->db->select('PC.PostCommentID');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->where('PC.UserID', $user_id);
        $this->db->where('PC.StatusID', 2);    
        $query = $this->db->get();
        return $query->num_rows();        
    }

    /**
     * [user_question_count Get count for question]
     * @param  [int]       $user_id       [user ID]
     * @return [int]                    [ count]
     */
    function user_question_count($user_id) {
        $activity_type_allow = array(1, 8);
        $this->db->select('A.ActivityID');
        $this->db->from(ACTIVITY . ' A');
        $this->db->where('A.UserID', $user_id);
        $this->db->where('A.StatusID', 2);   
        $this->db->where('A.PostType', 2);  
        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        $query = $this->db->get();
        return $query->num_rows();        
    }

    function get_notification_popup_data($activity_id, $user_id, $is_extra=1) {

        if($is_extra == 1) {
            $activity['RC'] = $this->response_count($activity_id);
            $activity['SOC'] = $this->solution_count($activity_id);

            $activity['URC'] = 0;
            $activity['UQC'] = 0;

            $this->db->select('QuestionCount, CommentCount');
            $this->db->from(USERDETAILS);
            $this->db->where('UserID', $user_id);
            $this->db->limit(1);
            $query = $this->db->get();
            if($query->num_rows() > 0) {
                $row = $query->row_array();
                $activity['URC'] = $row['CommentCount']; 
                $activity['UQC'] = $row['QuestionCount']; 
            }
        }

        $activity['SC'] = 0; 
        $activity['NC'] = 0;
        $activity['IsReady'] = 0;
        $activity['smsText']            = '';
        $activity['notificationText']   = '';
        $activity['notificationTitle']  = '';

        $this->db->select('SmsCount, PushNotificationCount, PushNotificationTitle, PushNotificationText, SmsText, IsReady');
        $this->db->from(ADMINCOMMUNICATION);
        $this->db->where('ActivityID', $activity_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $row = $query->row_array();
            $activity['SC'] = $row['SmsCount']; 
            $activity['NC'] = $row['PushNotificationCount']; 
            $activity['smsText']            = $row['SmsText'];
            $activity['notificationText']   = $row['PushNotificationText'];
            $activity['notificationTitle']  = $row['PushNotificationTitle'];
            $activity['IsReady'] = $row['IsReady']; 
        }   
        return $activity;     
    }    
    
    function get_activity_image_url($activity_guid) {
        $activity_id = get_detail_by_guid($activity_guid);
        if($activity_id) {
            $this->db->select('ImageName');
            $this->db->from(MEDIA);
            $this->db->where('MediaSectionID','3');
            $this->db->where('MediaSectionReferenceID',$activity_id);
            $this->db->where('StatusID','2');
            $this->db->limit(1);
            $query = $this->db->get();
            if($query->num_rows()) {
                $row = $query->row_array();
                return IMAGE_SERVER_PATH.'upload/wall/500x500/'.$row['ImageName'];
            }
        }
        return '';
    }

    function set_activity_title($data) {
        $activity_id = get_detail_by_guid($data['ActivityGUID']);
        if($activity_id) {
            $this->db->set('PostTitle', $data['Title']);
            $this->db->where('ActivityID', $activity_id);
            $this->db->update(ACTIVITY);

            $this->cache->delete('activity_'.$activity_id);
        }
        
    }

    /**
     * [top_contributor Get top top contributor]
     * @param  [int] $user_id   [Logged in user ID]
     * @param  [int] $data  [request data]
     * @return [array]            [user list]
     */
    function top_contributor($user_id, $data) {
      
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 10);
        $tag_id = safe_array_key($data, 'TagID', 0);
        $nf     = safe_array_key($data, 'nf', 0);
        if(empty($tag_id)) {
            $tag_id = 0;
        }

        $followers = array();
        $is_follow_disabled = $this->settings_model->isDisabled(11);
        if(!$is_follow_disabled) {
            $this->user_model->set_friend_followers_list($user_id);
            $followers = $this->user_model->get_followers_list();  
        }
        $followers[] = 0;

        $result = array();
        $this->db->select('TP.TotalPoint');
        $this->db->select('U.ProfilePicture, U.UserGUID, U.UserID, U.FirstName, U.LastName, UD.LocalityID, U.CanCreatePoll, U.IsVIP, U.IsAssociation',FALSE);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->select('IFNULL(UD.UserWallStatus,"") as About', FALSE);
        $this->db->select('IFNULL(UD.Address,"") as Address', FALSE);
        $this->db->from(TOPCONTRIBUTORS.' TP');
        $this->db->join(USERS . ' U', 'U.UserID = TP.UserID and U.StatusID NOT IN (3,4)');      
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');        
        $this->db->where('TP.TagID', $tag_id);
        if($nf == 1) {            
            $this->db->where_not_in('U.UserID', $followers);
        }
        $this->db->order_by('TP.UserID', 'RANDOM');
        if ($page_size) {
           // $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();
        $results = $query->result_array();
        $this->load->model(array('users/user_model', 'locality/locality_model'));

        
        foreach ($results as $key => $result) {
            $result['cmsg'] =  $this->user_model->check_message_button_status($user_id, $result['UserID']);
            $result['sbdg'] =  1;

            if(!$is_follow_disabled) {
                $result['IsFollow'] = 0;
                if ($result['UserID'] == $user_id) {
                    $result['IsFollow'] = 2;
                } else if (in_array($result['UserID'], $followers)) {
                    $result['IsFollow'] = 1;
                } 

                $IsAdmin = $this->user_model->is_super_admin($r['UserID']);
                $this->load->model('activity/activity_model');
                $IsAdminGuid = $this->activity_model->get_user_guid_by_user_ids(array(ADMIN_USER_ID)); // admin set from config page
                if($IsAdmin || $following_id==$IsAdminGuid )
                {
                    $result['IsFollow']=2;
                }
            }
            
            $result['Locality'] = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => 0, 'IsPollAllowed' => 1, 'WName'=>'', 'WNumber'=>'', 'WID'=>'', 'WDescription'=>'');
            if($result['LocalityID']) {
                $result['Locality'] = $this->locality_model->get_locality($result['LocalityID']);
            }
            unset($result['LocalityID']);
            unset($result['UserID']);
            unset($result['TotalPoint']);
            $results[$key] = $result;
        }
        return $results;
    }

    /**
     * [similar_posts ]
     * @param  [Array]       $data      [Request Data]
     * @return [Array]                    [Activity array]
     */
    function similar_posts($data) {

        $search_type = safe_array_key($data, 'SearchType', 0); //(0=> AND 1=> OR)

        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 2);
        $user_id = $data['UserID'];
        $activity_id = $data['ActivityID'];
        $created_date = $data['CreatedDate'];
        $tag_ids = $data['TagIDs'];
        

        $activity_type_allow = array(1, 8, 49);
        
        $this->current_db->select('A.UserID, A.ActivityGUID, A.ActivityID, A.PostTitle, A.CreatedDate, A.NoOfComments, A.NoOfCommentReplies, A.NoOfLikes, A.PostAsModuleID, A.PostAsModuleEntityID');
        
        $this->current_db->from(ACTIVITY . ' A');
        $this->current_db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID AND ATY.StatusID=2');
        $this->current_db->join(USERS . ' U', 'U.UserID=A.UserID and U.StatusID NOT IN (3,4)');
    
        $this->current_db->where('A.ActivityID != ',$activity_id);      
                
        $this->current_db->where_in('A.ActivityTypeID', $activity_type_allow);
        $this->current_db->where('A.CreatedDate <= ', $created_date);
        $this->current_db->where('A.IsCityNews',1);
        $this->current_db->where('A.StatusID', 2); 
        
        $this->current_db->join(ENTITYTAGS . ' ET', 'ET.EntityID=A.ActivityID AND ET.EntityType = "ACTIVITY"');
        $this->current_db->join(TAGS . ' TG', 'TG.TagID=ET.TagID'); 

        if ($search_type) { //Match any
            $this->current_db->where_in("ET.TagID", $tag_ids);
        } else if(count($tag_ids)){
            // Match all
            $in_tag_ids = implode(',', $tag_ids);
            $total_tag_ids = count($tag_ids);
            $and_conditions = "(select COUNT(DISTINCT ET_COUNT.`TagID`) from `EntityTags` ET_COUNT 
            where ET_COUNT.`EntityID` = `A`.`ActivityID` 
            AND `ET_COUNT`.`EntityType` = 'ACTIVITY'  
            AND `ET_COUNT`.`StatusID` = 2
            AND  ET_COUNT.`TagID` IN ($in_tag_ids) ) = $total_tag_ids  ";

            $this->current_db->where($and_conditions, NULL, false);                
        }
      

        $this->current_db->order_by('A.CreatedDate', 'DESC');
        $this->current_db->group_by('A.ActivityID');
        if (!empty($page_size)) {
            $this->current_db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $result = $this->current_db->get(); //if($search_type) { echo $this->db->last_query();die;}

        $return = array();

        if ($result->num_rows()) {
            $this->load->model(array('activity/activity_model'));
            
            foreach ($result->result_array() as $res) {
                $activity = array();
                $res_entity_type            = 'Activity';
                $res_entity_id              = $res['ActivityID'];

                $activity_guid              = $res['ActivityGUID'];
                $activity['CreatedDate']    = $res['CreatedDate'];
                $activity['PostTitle']      = $res['PostTitle'];
                $activity['ActivityGUID']   = $activity_guid;
                $activity['NoOfComments']   = $res['NoOfComments'] + $res['NoOfCommentReplies'];
                $activity['NoOfLikes']      = $res['NoOfLikes'];

                if ($res['UserID'] == $user_id) {
                    $activity['IsLike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
                   
                } else {
                    $activity['IsLike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, 3, $user_id);                    
                }

                $activity['PostTitle'] = trim(str_replace('&nbsp;', ' ', $activity['PostTitle']));
                $return[] = $activity;
            }
        } else {
            if(empty($search_type)) {
                $data['SearchType'] = 1;
                return $this->similar_posts($data);
            }
        }
        return $return;
    }

    /**
     * hide_activity: used to hide/show activity on newsfeed
     * @param [string] $activity
    */
    function update_activity_newsfeed_status($activity) {
        try {       
           if(!empty($activity)) {
                $activity_id = $activity['ActivityID'];
                $is_show_on_news_feed = $activity['IsShowOnNewsFeed'];
                $is_show_on_news_feed = ($is_show_on_news_feed == 1) ? 0 : 1;
            
                $this->db->set('IsShowOnNewsFeed', $is_show_on_news_feed);                                       
                $this->db->where('ActivityID', $activity_id);
                $this->db->update(ACTIVITY);            
            }
        } catch (Exception $e) {
            log_message("error", "Unable to update hide activity: {$e->getMessage()}");
        }    
    }

    

    /**
     * pin_to_top: used to pinned post on top
     * @param [int] $activity_id
    */
    function pin_to_top($activity_id) {
        try {    
            $this->load->model(array('activity/activity_model'));   
            $ward_ids = $this->activity_model->get_activity_ward_ids($activity_id);
            $ward_ids = array_unique($ward_ids);
           
            if(!empty($ward_ids)) {
                $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
                foreach ($ward_ids as $ward_id) { 
                    if($ward_id == 1) {
                        $this->db->truncate(PINTOTOP);
                        //$this->db->delete(PINTOTOP);
                    } else {
                        $this->db->where('WardID', $ward_id);
                        $this->db->delete(PINTOTOP);
                    }
                    
                    $activity_ward = array();
                    $activity_ward['ActivityID'] = $activity_id;
                    $activity_ward['WardID'] = $ward_id;
                    $activity_ward['ModifiedDate'] = $current_date_time;                    
                    $this->db->insert(PINTOTOP, $activity_ward);
                }        
            }
        } catch (Exception $e) {
            log_message("error", "Unable to update pin to top post activity: {$e->getMessage()}");
        }    
    }

     /**
     * remove_pin_to_top: used to removed pinned post from top
     * @param [int] $activity_id
    */
    function remove_pin_to_top($activity_id) {
        try {    
            $this->db->where('ActivityID', $activity_id);
            $this->db->delete(PINTOTOP);
        } catch (Exception $e) {
            log_message("error", "Unable to remove pin to top post: {$e->getMessage()}");
        }    
    }

    /**
     * used to update related to indore flag for an activity
     */
    function related_to_indore($data) {
        $is_related = safe_array_key($data, 'IsRelated', 0);
        $this->db->select('ActivityDetailsID');
        $this->db->from(ACTIVITYDETAILS);
        $this->db->where('ActivityID', $data['ActivityID']);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $this->db->set('IsRelated', $is_related);
            $this->db->where('ActivityDetailsID', $row['ActivityDetailsID']);
            $this->db->update(ACTIVITYDETAILS);
        } else {
            $insert_data = array();
            $insert_data['ActivityID'] = $data['ActivityID'];
            $insert_data['IsRelated'] = $is_related;                    
            $this->db->insert(ACTIVITYDETAILS, $insert_data);
        }
    }

    /**
     * used to update idea for better indore flag for an activity
     */
    function idea_for_better_indore($data) {
        $is_idea = safe_array_key($data, 'IsIdea', 0);
        $this->db->select('ActivityDetailsID');
        $this->db->from(ACTIVITYDETAILS);
        $this->db->where('ActivityID', $data['ActivityID']);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $this->db->set('IsIdea', $is_idea);
            $this->db->where('ActivityDetailsID', $row['ActivityDetailsID']);
            $this->db->update(ACTIVITYDETAILS);
        } else {
            $insert_data = array();
            $insert_data['ActivityID'] = $data['ActivityID'];
            $insert_data['IsIdea'] = $is_idea;                    
            $this->db->insert(ACTIVITYDETAILS, $insert_data);
        }
    }

    /**
     * Used to get activity details
     */
    function get_activity_details($activity_id) {
        $this->current_db->select('IsIdea, IsRelated, IsAnswerRequired');
        $this->current_db->from(ACTIVITYDETAILS);
        $this->current_db->where('ActivityID', $activity_id);
        $this->current_db->limit(1);
        $query = $this->current_db->get();
        $row = array('IsIdea' => 0, 'IsRelated' => 0, 'IsAnswerRequired' => 0); //Idea for a better Indore, Related to Indore
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
        }
        return $row;
    }

    /**
     * bump_up: used to bump up post
     * @param [int] $activity_id
    */
    function bump_up($activity_id) {
        $this->db->select('MAX(ModifiedDate) as ModifiedDate');
        $this->db->from(ACTIVITY);
        $this->db->where('StatusID', 2);
        $this->db->where_in('ActivityTypeID', array(1,8, 49, 50));
        $this->db->limit(1);
        $query = $this->current_db->get();
        $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
        $current_time = strtotime($current_date_time);
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            if($row['ModifiedDate']) {
                $current_date_time = $row['ModifiedDate'];  
                $modified_time = strtotime($current_date_time);  
                if($modified_time > $current_time) {            
                    $current_date_time = date("Y-m-d H:i:s", strtotime($current_date_time . ' +25 seconds'));
                }
            }
        }
        
        $this->db->where('ModifiedDate <= ', $current_date_time);
        $this->db->where('ActivityID', $activity_id);
        $this->db->set('ModifiedDate', $current_date_time);
        $this->db->update(ACTIVITY);

        if (CACHE_ENABLE)  {
            $this->cache->delete('activity_'.$activity_id);
        }
    }

    /**
     * used to not require answer flag for an activity
     */
    function not_require_answer($data) {
        $is_answer_required = safe_array_key($data, 'IsAnswerRequired', 0);
        $this->db->select('ActivityDetailsID');
        $this->db->from(ACTIVITYDETAILS);
        $this->db->where('ActivityID', $data['ActivityID']);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $this->db->set('IsAnswerRequired', $is_answer_required);
            $this->db->where('ActivityDetailsID', $row['ActivityDetailsID']);
            $this->db->update(ACTIVITYDETAILS);
        } else {
            $insert_data = array();
            $insert_data['ActivityID'] = $data['ActivityID'];
            $insert_data['IsAnswerRequired'] = $is_answer_required;                    
            $this->db->insert(ACTIVITYDETAILS, $insert_data);
        }
    }

    function assign_team_member($activity_id, $team_member_id) {
        $this->db->select('ActivityMemberID');
        $this->db->from(ACTIVITYMEMBERS);
        $this->db->where('ActivityID', $activity_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $this->db->set('UserID', $team_member_id);
            $this->db->set('ModifiedDate', $created_date);
            $this->db->where('ActivityMemberID', $row['ActivityMemberID']);
            $this->db->update(ACTIVITYMEMBERS);
        } else {
            $insert_data = array();
            $insert_data['ActivityID'] = $activity_id;
            $insert_data['UserID'] = $team_member_id;  
            $insert_data['CreatedDate']    = $created_date;  
            $insert_data['ModifiedDate']    = $created_date;                
            $this->db->insert(ACTIVITYMEMBERS, $insert_data);
        }
    }

    function get_team_member_data($activity_id) {
        $this->db->select('CONCAT(U.FirstName, " ", U.LastName) AS Name', FALSE);
        $this->db->select('U.UserID as ID');
        $this->db->from(ACTIVITYMEMBERS . ' AM');  
        $this->db->join(USERS . ' U', 'U.UserID=AM.UserID'); 
        $this->db->where('AM.ActivityID', $activity_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $users = array('Name' => '', 'ID' => '');
        if ($query->num_rows()) {
            $users = $query->row_array();
        } 
        return $users;
    }
}
