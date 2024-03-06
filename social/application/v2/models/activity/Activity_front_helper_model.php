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
            $where_query .= " AND IsPromoted = 1";
        } else {
            $activity_update_data = array(
                'PromotedDate' => ($is_promoted == 1) ? get_current_date('%Y-%m-%d %H:%i:%s') : 'CreatedDate',
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
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        
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
        $this->db->select('U.UserGUID,EV.UserID');
        $this->db->from(ENTITYVIEW . ' EV');
        $this->db->join(USERS . ' U', 'U.UserID=EV.UserID AND U.StatusID NOT IN(3,4)', 'left');
        $this->db->where('EV.EntityType', $entity_type);
        $this->db->where('EV.EntityID', $entity_id);      
        $query = $this->db->get();
        return $query->num_rows();        
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
}
