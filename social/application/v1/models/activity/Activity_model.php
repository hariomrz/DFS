<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_model extends Common_Model {
    protected $blocked_users = array();
    protected $tagged_data = array();
    protected $user_activity_archive = array();

    public function __construct() {
        parent::__construct();
        $this->load->model(array('subscribe_model', 'ratings/rating_model', 'events/event_model', 'pages/page_model', 'users/user_model','users/friend_model', 'group/group_model', 'flag_model', 'favourite_model', 'album/album_model', 'timezone/timezone_model', 'tag/tag_model', 'notification_model', 'activity/activityrule_model','activity/watchlist_model','activity/mydesk_model', 'activity/activity_result_filter_model'));        
        $this->load->helper('activity');
    }
    
    /**
     * [set_block_user_list used to set blocked user ids]
     * @param  [int]    $entity_id [Entity ID]
     * @param [int]     $module_id [Module ID]
     */
    function set_block_user_list($entity_id, $module_id) {
        $this->blocked_users = $this->block_user_list($entity_id, $module_id);
    }

    /**
     * [flagged_by_any Check the activity status as flagged or not]
     * @param  [int]    $activity_id [Activity ID]
     * @return [int]                [description]
     */
    function flagged_by_any($activity_id) {
        $this->db->where('EntityType', 'Activity');
        $this->db->where('EntityID', $activity_id);
        $query = $this->db->get(FLAG);
        if ($query->num_rows()) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * [has_access used to check user has access for given activity]
     * @param  [int]    $user_id [User ID]
     * @param  [int]    $activity_id [Activity ID]
     * @return [boolean]                [true/false]
     */
    function has_access($user_id, $activity_id) {
        $this->load->model(array('group/group_model', 'pages/page_model', 'events/event_model'));
        $this->db->select('UserID,ModuleID,ModuleEntityID,ActivityTypeID,Privacy');
        $this->db->from(ACTIVITY);
        $this->db->where('ActivityID', $activity_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $details = $query->row_array();
            if ($details['ModuleID'] == '1') {
                if ($this->group_model->has_access($user_id, $details['ModuleEntityID'])) {
                    return true;
                }
            }
            if ($details['ModuleID'] == '3') {
                $is_relation = $this->isRelation($details['ModuleEntityID'], $user_id, true);
                if (in_array($details['Privacy'], $is_relation)) {
                    return true;
                }
            }
            if ($details['ModuleID'] == '14') {
                if ($this->event_model->has_access($user_id, $details['ModuleEntityID'])) {
                    return true;
                }
            }
            if ($details['ModuleID'] == '18') {
                return true;
            }
        }
        return false;
    }

    public function get_community_announcements($current_user,$only_id=false) {
        $role_id=2;
        $module_id = 3;
        $entity_id = $current_user;
        $user_id = $current_user;

        $this->load->model(array('polls/polls_model','category/category_model','forum/forum_model'));
        $this->load->model('sticky/sticky_model');
        
        $category_list = array();//$this->forum_model->get_user_category_list();
        
        $condition = '';
        $condition_part_two = '';

        $case_array=array();

        if(!empty($category_list)) {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
        if(!empty($case_array)) {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition)) {
            $condition = $condition_part_two;
        }

        $this->db->where('A.ModuleID', '34');

        if (!empty($condition)) {
            $this->db->where($condition, NULL, FALSE);
        }

        if($only_id) {
            $this->db->select('GROUP_CONCAT(A.ActivityID) as exclude_ids',false);
        } else {
            $this->db->select('A.*,ATY.ViewTemplate,ATY.Template,ATY.LikeAllowed,ATY.CommentsAllowed,ATY.ActivityType,ATY.ActivityTypeID,ATY.FlagAllowed,ATY.ShareAllowed,ATY.FavouriteAllowed,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        }
        $this->db->from(ACTIVITY.' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        $this->db->_protect_identifiers = FALSE;
        $this->db->where('A.ActivityID NOT IN (SELECT ActivityID FROM '.ANNOUNCEMENTVISIBILITYSETTINGS.' WHERE UserID = '.$current_user.' )',null,false);
        $this->db->_protect_identifiers = TRUE;
        $this->db->where('A.StatusID','2');
        //$this->db->where('A.LocalityID', $this->LocalityID);
        $this->db->where("IF(A.IsVisible=1,A.PostType=7,A.IsVisible=3)",NULL,FALSE);
        $this->db->order_by('A.ModifiedDate','DESC');
        $this->db->limit(2);        
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if($only_id) {
            if($query->num_rows()) {
                return $query->row()->exclude_ids;
            } else {
                return '';
            }
        }
        //echo $this->db->last_query();
        $return = array();
        if($query->num_rows()) {
            $feed_result = $query->result_array();
            $return = $this->activity_result_filter_model->filter_result_set($feed_result, 1, $user_id, 0, $role_id, 1, false, $entity_id, $module_id, $this,0);            
        }
        return $return;
    }

    public function get_newsfeed_announcements($current_user,$only_id=false) {        
        $role_id=2;
        $module_id = 3;
        $module_entity_id = $current_user;
        $entity_id = $current_user;
        $user_id = $current_user;

        $this->load->model(array('polls/polls_model','category/category_model','forum/forum_model'));
        $this->load->model('sticky/sticky_model');

        $friend_followers_list  = array();//$this->user_model->get_friend_followers_list();
        $category_list          = array();//$this->forum_model->get_user_category_list();
        $friends                = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow                 = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

//        $friend_of_friends      = $this->user_model->get_friends_of_friend_list();
        $friends[]              = 0;
        $follow[]               = 0;
        $friend_of_friends[]    = 0;
        $friend_followers_list  = array_unique(array_merge($friends, $follow));
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
        //$page_list = $this->page_model->get_user_pages_list();
        $page_list = '';//$this->page_model->get_feed_pages_condition();

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $condition = '';
        $condition_part_one = '';
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = '';
        $condition_part_four = '';
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();
       /* if ($friend_followers_list != '' && empty($activity_ids)) {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25,36) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " OR A.PostType=8";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
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
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if(!empty($category_list)) {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
        if (!empty($page_list)) {
            $case_array[]="A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        if (!empty($event_list)) {
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")";
        }
        if(!empty($case_array)) {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        } 
        if (empty($condition)) {
            $condition = $condition_part_two;
        } 

        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";

        if (!empty($condition))
        {
            $this->db->where($condition, NULL, FALSE);
        } else {
            $this->db->where('A.ModuleID', '3');
            $this->db->where('A.ModuleEntityID', $user_id);
        }
        if ($privacy_condition) {
            $this->db->where($privacy_condition, null, false);
        }*/

        if($only_id) {
            $this->db->select('GROUP_CONCAT(A.ActivityID) as exclude_ids',false);
        } else {
            $this->db->select('A.*,ATY.ViewTemplate,ATY.Template,ATY.LikeAllowed,ATY.CommentsAllowed,ATY.ActivityType,ATY.ActivityTypeID,ATY.FlagAllowed,ATY.ShareAllowed,ATY.FavouriteAllowed,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        }
        $this->db->from(ACTIVITY.' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');        
        $this->db->_protect_identifiers = FALSE;
        $this->db->where('A.ActivityID NOT IN (SELECT ActivityID FROM '.ANNOUNCEMENTVISIBILITYSETTINGS.' WHERE UserID = '.$current_user.' )',null,false);
        $this->db->_protect_identifiers = TRUE;        
        //$this->db->where('A.ModuleID!=','1');
        $this->db->where('A.StatusID','2');
        //$this->db->where('A.LocalityID', $this->LocalityID);
        $this->db->where("IF(A.IsVisible=1,A.PostType=7,A.IsVisible=3)",NULL,FALSE);
        $this->db->order_by('A.ModifiedDate','DESC');
        $this->db->limit(2);        
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if($only_id) {
            if($query->num_rows()) {
                return $query->row()->exclude_ids;
            } else {
                return '';
            }
        }
        
        $return = array();
        if($query->num_rows()) {
            $feed_result = $query->result_array();
            $return = $this->activity_result_filter_model->filter_result_set($feed_result, 1, $user_id, 0, $role_id, 1, false, $entity_id, $module_id, $this,0);
        }
        return $return;
    }
    
    public function get_announcements($module_id, $module_entity_id,$current_user,$only_id=false) {
        $blocked_users = $this->blocked_users;
        $e_module_id = $module_id;
        $e_entity_id = $module_entity_id;
        $role_id=2;
        $comment_id = '';

        if($only_id) {
            $this->db->select('GROUP_CONCAT(A.ActivityID) as exclude_ids',false);
        } else {
            $this->db->select('A.*,ATY.ViewTemplate,ATY.Template,ATY.LikeAllowed,ATY.CommentsAllowed,ATY.ActivityType,ATY.ActivityTypeID,ATY.FlagAllowed,ATY.ShareAllowed,ATY.FavouriteAllowed,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        }
        $this->db->from(ACTIVITY.' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        $this->db->where('A.ModuleID',$module_id);
        $this->db->where('A.ModuleEntityID',$module_entity_id);
        $this->db->where('A.StatusID','2');
        //$this->db->where('A.LocalityID', $this->LocalityID);
        $this->db->where("IF(A.IsVisible=1,A.PostType=7,A.IsVisible=3)",NULL,FALSE);
        $this->db->where('A.ActivityID NOT IN (SELECT ActivityID FROM '.ANNOUNCEMENTVISIBILITYSETTINGS.' WHERE UserID = '.$current_user.' )',null,false);
        $this->db->order_by('A.ModifiedDate','DESC');
        $this->db->limit(2);
        $query = $this->db->get();
        if($only_id) {
            if($query->num_rows()) {
                return $query->row()->exclude_ids;
            } else {
                return '';
            }
        }
        //echo $this->db->last_query();
        $return = array();
        if($query->num_rows()) {
            $feed_result = $query->result_array();
            $return = $this->activity_result_filter_model->filter_result_set($feed_result, 1, $current_user, 0, $role_id, 1, false, $module_entity_id, $module_id, $this,0);            
        }
        return $return;
    }

    /**
     * [get_user_sticky_posts Get activity id of sticky posts]
     * @param  [int]    $user_id          [User ID]
     * @param  [int]    $module_id        [Module ID]
     * @param  [int]    $module_entity_id [Module Entity ID]
     * @return [Array]                    [ActivityIDs array]
     */
    public function get_user_sticky_posts($user_id,$module_id,$module_entity_id) {
        $sticky_id = array();
        $this->db->select("A.ActivityID");
        $this->db->from(STICKYPOST." SP");
        $this->db->_protect_identifiers = false;
        $this->db->join(ACTIVITY." A","A.ActivityID=SP.ActivityID AND SP.PostAsModuleID='3' AND SP.PostAsModuleEntityID='".$user_id."'","left");
        $this->db->_protect_identifiers = true;
        $this->db->where("A.ModuleID",$module_id);
        $this->db->where("A.ModuleEntityID",$module_entity_id);
        $query = $this->db->get();
        if($query->num_rows()) {
            foreach($query->result_array() as $result) {
                $sticky_id[] = $result['ActivityID'];
            }
        }
        return $sticky_id;
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
    public function getActivities(
            $entity_id, $module_id, $page_no, $page_size, $current_user, $feed_sort_by, $filter_type = 0, $is_media_exists = 2, 
            $activity_guid, $search_key, $start_date, $end_date, $feed_user = 0, $as_owner = 0, $count_only = false, $field = 'ALL', 
            $activity_type_filter = array(), $m_entity_id = '', $entity_module_id = '', $comment_id = '',$view_entity_tags='',$role_id=2,$post_type=0,$tags='', $extra_params = []
    ) {       
        $this->load->model(array('activity/activity_wall_model'));
        return $this->activity_wall_model->get_activities(
            $entity_id, $module_id, $page_no, $page_size, $current_user, $feed_sort_by, $filter_type, $is_media_exists, 
            $activity_guid, $search_key, $start_date, $end_date, $feed_user, $as_owner, $count_only, $field, 
            $activity_type_filter, $m_entity_id, $entity_module_id, $comment_id,$view_entity_tags,$role_id,$post_type,$tags, $extra_params
        );
    }
    
    public function get_photos_count($activity_id) {
        $this->db->select('COUNT(M.MediaID) as MediaCount, GROUP_CONCAT(DISTINCT(MT.Name)) as MediaType');
        $this->db->from(MEDIA . ' M');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->where('M.MediaSectionID', 3);
        $this->db->where('M.MediaSectionReferenceID', $activity_id);
        $this->db->where_in('M.StatusID', array(2,10));
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $row = $query->row_array();
            $row['Media'] = 'Media';
            if ($row['MediaType'] == 'Image') {
                if ($row['MediaCount'] == 1) {
                    $row['Media'] = 'Photo';
                } else {
                    $row['Media'] = 'Photos';
                }
            } else if ($row['MediaType'] == 'Video') {
                if ($row['MediaCount'] == 1) {
                    $row['Media'] = 'Video';
                } else {
                    $row['Media'] = 'Videos';
                }
            } else {
                $row['Media'] = 'Media';
            }
            return $row;
        }
    }

    function check_media_pending_status($activity_id) {
        $this->db->select('MediaID');
        $this->db->from(MEDIA);
        $this->db->where('MediaSectionID', '3');
        $this->db->where('MediaSectionReferenceID', $activity_id);
        $this->db->where('ConversionStatus', 'Pending');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return true;
        }
        return false;
    }

    /**
     * [createPost description]
     * @param  [string] $post_content        [Post content]
     * @param  [int] $module_id              [ModuleID (User, Group, Event etc)]
     * @param  [int] $module_entity_id        [EntityID of Module]
     * @param  [int] $user_id                [Current UserID]
     * @param  [string] $is_media_exist       [0 - Not Exists, 1 - Exists]
     * @param  [string] $media_count         [Media count]
     * @param  [int] $visibility            [Meida exist or not]
     * @param  [int] $commentable           [This activity can be commentable or not]
     * @return [int]                        [Activity ID]
     */
    function createPost($post_content, $module_id, $module_entity_id, $user_id, $is_media_exist, $media_count, $visibility, $commentable, $module_entity_owner, $notify_all = 0, $links = array(), $files_count = 0,$entity_tags=array(), $post_as_module_id=3, $post_as_module_entity_id=0,$post_title="",$post_type=1,$files=array(),$media=array(),$activity_id=FALSE,$is_anonymous=0,$status=2,$publish_post=FALSE,$analytic_login_id=NULL,$facts='',$extra_params=array(),$contest_date='',$is_featured=0,$summary='',$taged_user='') {      
        $activity_id_previous = $activity_id;
        $excluded_mentioned_users = array();
        $type = 0;
        if(!$post_as_module_entity_id){
            $post_as_module_entity_id=$user_id;
        }
        $post_content = str_replace('­', '', $post_content);

        $activity_type_id = 1;

        if ($module_id == 1) {
            $activity_type_id = 7;
        } elseif ($module_id == 3) {
            if($post_type == '8') {
                $activity_type_id = 36;
            }
            else if ($post_type == '9') {
                $activity_type_id = 37;
            } else if ($user_id == $module_entity_id) {
                $activity_type_id = 1;
            } else {
                $activity_type_id = 8;
            }
        } elseif ($module_id == 14) {
            $activity_type_id = 11;
        } elseif ($module_id == 18) {
            $activity_type_id = 12;
        } elseif ($module_id == 34) {
            $activity_type_id = 26;
        }
        
        if (trim($post_content)) {
            $type = 1;
        } else if ($is_media_exist == 1) {
            $type = 2;
        }
        
        $params = $extra_params;
        $params['count'] = $media_count;
        $params['file_count'] = $files_count;
        $is_file_exists = ($files_count > 0) ? '1' : '0';
        $ActivityGUID=get_guid();
        
        //$this->load->model('activity/activity_result_filter_model','result_filter');
        //$post_content_mob = $this->parse_tag($post_content);
        //$post_content_mob = $this->result_filter->get_description($post_content_mob); 

        if(!$activity_id) {
            $insertArray = array(
                'ActivityGUID' => $ActivityGUID, 'ActivityTypeID' => $activity_type_id, 'UserID' => $user_id, 'ModuleID' => $module_id, 
                'ModuleEntityID' => $module_entity_id, 'Params' => json_encode($params), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 
                'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'LastActionDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'StatusID' => 2, 
                'Privacy' => $visibility, 'IsCommentable' => $commentable, 'PostContent' => $post_content, 'IsMediaExist' => $is_media_exist, 
                'IsFileExists' => $is_file_exists, 'Type' => $type, 'ModuleEntityOwner' => $module_entity_owner, 'PostAsModuleID' => $post_as_module_id, 
                'PostAsModuleEntityID' => $post_as_module_entity_id,'PostType'=>$post_type,'IsAnonymous'=>$is_anonymous,
                'StatusID'=>$status,'AnalyticLoginID'=>$analytic_login_id,
                'PromotedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),'Facts'=>$facts,'ContestEndDate' => $contest_date, 'IsFeatured' => $is_featured,'Summary' => $summary,
                'LocalityID' => $this->LocalityID
            );

            if (!empty($notify_all)) {
                $insertArray['NotifyAll'] = $notify_all;
            }
            
            $this->load->model(array('log/user_activity_log_score_model'));
            $this->load->model(array('activity/activity_front_helper_model'));
            
            $insertArray['PostSearchContent'] = $this->activity_front_helper_model->get_search_stripped_content($post_content);            
            $insertArray['Verified'] =  (!empty($this->UserTypeID) && $this->UserTypeID == 4) ? 1 : 0; //$this->user_activity_log_score_model->get_user_activity_verify_status($user_id);
            $this->db->insert(ACTIVITY, $insertArray);
            $activity_id = $this->db->insert_id();
            
            //nofify admin dashboard for update.
            notify_node('updateAdminDashboard', array('EntityID' => $activity_id));
            
            if($module_id==34) {
                $this->update_forum_discussions_count($module_entity_id, $module_id, 1);
            }
            $this->update_user_post_count($user_id);
            save_log($user_id, 'Activity', $insertArray['ActivityGUID'], false, $this->DeviceTypeID);
            
            // Save user activity Log 
            $score = $this->user_activity_log_score_model->get_score_for_activity($activity_type_id, $module_id, 0, $user_id);
            $userActivityLog = array(
                'ModuleID' => $module_id, 'ModuleEntityID' => $activity_id, 'UserID' => $user_id, 'ActivityTypeID' => $activity_type_id, 'ActivityID' => $activity_id,
                'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID'=> $post_as_module_id, 'PostAsModuleEntityID' => $post_as_module_entity_id,
                'EntityID' => $activity_id, 'Score' => $score,
            );

            $this->user_activity_log_score_model->add_activity_log($userActivityLog);            

        } else {
            $update_array = array(
                'UserID' => $user_id,'Params' => json_encode($params),'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 
                'LastActionDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'StatusID' => 2,'Privacy' => $visibility, 'IsCommentable' => $commentable, 
                'PostContent' => $post_content, 'IsMediaExist' => $is_media_exist, 'IsFileExists' => $is_file_exists, 'Type' => $type, 
                'ModuleEntityOwner' => $module_entity_owner, 'PostAsModuleID' => $post_as_module_id, 'PostAsModuleEntityID' => $post_as_module_entity_id,
                'IsAnonymous'=>$is_anonymous,'StatusID'=>$status,'Facts'=>$facts,'ContestEndDate'=>$contest_date, 'Summary' => $summary                
            );
            
            $this->load->model(array('activity/activity_front_helper_model'));            
            $update_array['PostSearchContent'] = $this->activity_front_helper_model->get_search_stripped_content($post_content);  

            if (!empty($notify_all)) {
                $update_array['NotifyAll'] = $notify_all;
            }

            $this->db->where('ActivityID',$activity_id);
            $this->db->update(ACTIVITY,$update_array);
        }

        $send_notification = 1;
        $usrs = array(array('ModuleID' => 3, 'ModuleEntityID' => $user_id));
       
        preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $post_content, $matches_content);
        
        $matches = array();
        $mentions = array();
        
        if(!empty($matches_content[1])) {
            $matches = $matches_content[1];
        }        
        
        if (!empty($matches)) {   
            $post_content_updated = '';
            foreach ($matches as $match) {
                $match_details = explode(':', $match);
                if ($match_details[2] == '3' && $match_details[1] != $user_id) {
                    $usrs[] = array('ModuleID' => 3, 'ModuleEntityID' => $match_details[1]);
                    if ($activity_type_id == 8 && $match_details[1] == $module_entity_id) {
                        $send_notification = 0;
                    }
                    add_update_relationship_score($user_id, 3, $match_details[1], 8);
                }
                    
                $proceed_mention = true;
                if($module_id == 1 && !($this->group_model->is_member($match_details[1],$module_entity_id))) {
                    $proceed_mention = false;
                }
                
                if($proceed_mention) {
                    $mentions[]= $mention_id = $this->add_mention($match_details[1], $match_details[2], $activity_id, $match_details[0]);  
                    if(in_array($match, $matches_content[1])) {   
                        $post_content_updated = 1;
                        $post_content = str_replace($match, $mention_id, $post_content);
                    }
                } else {
                    if(in_array($match, $matches_content[1])) {   
                        $post_content_updated = 1;
                        $post_content = str_replace("{{{$match}}}", '@'.$match_details[0], $post_content);  
                    }                         
                }                
            }

            if(!empty($post_content_updated)) {
                $updatedData['PostContent'] = $post_content;
            }

            $this->db->where('ActivityID', $activity_id);
            $this->db->update(ACTIVITY,$updatedData);
        }

        if($module_id == 1 && $post_type == 7) {
            if($activity_id_previous) {
                $announcement_notification_id = 126;
            } else {
                $announcement_notification_id = 125;
            }
            $announcement_members = $this->group_model->get_group_members_id_recursive($module_entity_id,array(),array(),false,true);
            $announcement_param = array();
            $announcement_param[0]['ReferenceID'] = $module_entity_id;
            $announcement_param[0]['Type'] = 'Group';
            $this->notification_model->add_notification($announcement_notification_id, $user_id, $announcement_members, $activity_id, $announcement_param);
            $send_notification = 0;
        }
        
        $media_name = array();
        $regexp = '<img[^>]+src=(?:\"|\')\K(.[^">]+?)(?=\"|\')';
        if(preg_match_all("/$regexp/", $post_content, $matches, PREG_SET_ORDER)) {
            if( !empty($matches) ) {
                for ($i=0; $i <= count($matches)-1; $i++)
                {
                    $img_src = $matches[$i][0];
                    $img_src = explode('/',$img_src);
                    $media_name[] = end($img_src);
                }
            }
        }

        if($media_name)
        {
            if($activity_id_previous)
            {
                $this->db->set('StatusID','1');
                $this->db->where('ActivityID',$activity_id);
                $this->db->update(MEDIA);
            }

            $this->db->set('StatusID','2');
            $this->db->set('ActivityID',$activity_id);
            $this->db->where_in('ImageName',$media_name);
            $this->db->update(MEDIA);
        }

        $this->subscribe_model->addUpdate($usrs, $activity_id);

        add_update_relationship_score($user_id, $module_id, $module_entity_id, 10);
        
        $subscribe_action = 'post_self';
        if ($activity_type_id == 7) {
            $subscribe_action = 'group_post';
        } elseif ($activity_type_id == 8) {
            $subscribe_action = 'post';
        } elseif ($activity_type_id == 11) {
            $subscribe_action = 'event_post';
        } elseif ($activity_type_id == 12) {
            $subscribe_action = 'page_post';
        }

        $links_arr = array();
        if ($links) {
            foreach ($links as $link) {
                $linksArr[] = $this->add_link($user_id, $activity_id, $link);
            }
        }

        $tags_arr = array();
        if(!empty($entity_tags)) {
            $tags_arr = $this->tag_model->save_tag($entity_tags, 1, $user_id);
            if(!empty($tags_arr) && !empty($activity_id)) {                
                $this->tag_model->save_entity_tag($tags_arr, 'ACTIVITY', $activity_id, $user_id);
            }
        }

        /* Save Activity Hostory
           History will not be saved for draft posts
        */

        $previously_tagged = array();

        if($activity_id_previous && !$publish_post) {
            $previously_tagged = $this->update_activity_history($activity_id_previous);
        }

        $notify_users = array();
        if($status==2) {
            $HistoryData = array(
                            'ActivityID'=>$activity_id,
                            'UserID'    => $user_id,
                            'Media'     =>'',
                            'Files'     =>'',
                            'Links'     =>'',
                            'Tags'      => ''
                            );
            $ActivityData = array('PostContent'=>$post_content,'PostType'=>$post_type,'ModuleID'=>$module_id,'ModuleEntityID'=>$module_entity_id);

            $HistoryData['ActivityData'] = json_encode($ActivityData);
            
            if(!empty($linksArr)) {
                $HistoryData['Links'] = json_encode($linksArr);
            }

            if(!empty($tags_arr)) {
               $HistoryData['Tags'] =  json_encode($tags_arr);
            }

            $notify_users = $this->add_activity_history($activity_id_previous,$HistoryData,$previously_tagged,$status,$publish_post);
        }
        /* End Save Activity Hostory*/
        return array('ActivityID' => $activity_id, 'subscribe_action' => $subscribe_action,'notify_users'=>$notify_users,'excluded_mentioned_users'=>$excluded_mentioned_users);
    }
    
    function activity_cache($activity_id) {
        $activity_id= trim($activity_id);
        if(empty($activity_id)) {
            return false;
        }
        if(CACHE_ENABLE) {
            $this->cache->delete('activity_'.$activity_id);
        }
        $cache_data=array();
        $cache_data['mt']=array();
        $cache_data['mtd']=array();
        $cache_data['t']=array();
        $cache_data['m']=array();
        $cache_data['f']=array();
        $cache_data['l']=array();
        $cache_data['s']=array();
      
        $this->db->select('ActivityGUID,ActivityID,PostContent,IsMediaExist,IsFileExists,UserID,ModuleID,ModuleEntityID,PostAsModuleID,PostAsModuleEntityID,ActivityTypeID,Params,Privacy,IsCommentable,PostTitle,NoOfLikes,NoOfComments,ParentActivityID,StatusID,TagedUser');
        $this->db->from(ACTIVITY);
        $this->db->where('StatusID','2');
        $this->db->where('ActivityID',$activity_id); 
        $this->db->limit(1);
        $result = $this->db->get();
        $mentions=array();
        $usrs=array();
        if ($result->num_rows() > 0) {
            $result_activity=$result->row_array();
                  
            preg_match_all('/{{([0-9]+)}}/', $result_activity['PostContent'], $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $mentions[]= $match;
                }

                $cache_data['mt'] = implode(',', $mentions) ;
                $this->db->select('MentionID,ModuleID,ModuleEntityID,Title');
                $this->db->where_in('MentionID',$mentions);
                $result_m = $this->db->get(MENTION);
                if ($result_m->num_rows()) {
                    foreach ($result_m->result_array() as $item) {
                        $cache_data['mtd'][$item['MentionID']]['t'] = $item['Title'];
                        $cache_data['mtd'][$item['MentionID']]['mid']=$item['ModuleID'];
                        $cache_data['mtd'][$item['MentionID']]['meid']=$item['ModuleEntityID'];
                        $cache_data['mtd'][$item['MentionID']]['meguid'] = get_detail_by_id($item['ModuleEntityID'],$item['ModuleID']);
                    }
                }
            }
                    
            // Cache for Tag
            $entity_tags = $this->tag_model->get_entity_tags('', 1, '', 1, 'ACTIVITY', $activity_id);
            
            if(!empty($entity_tags)) {
                $cache_data['t'] = $entity_tags;
            }
            // Cache for Media
            if( $result_activity['IsMediaExist']== 1) {
                $cache_data['m'] = $this->get_albums($activity_id, '0', '', 'Activity');
            }

            // Cache for Media
            if($result_activity['IsFileExists']== 1) {
                $cache_data['f'] = $this->get_activity_files($activity_id);
            }

            // Cache for Subscribe user
            $this->db->select('ModuleID,ModuleEntityID');
            $this->db->from(SUBSCRIBE);
            $this->db->where('StatusID',2);
            $this->db->where('EntityType','ACTIVITY');
            $this->db->where('EntityID',$activity_id);
            $result_s = $this->db->get();
            if ($result_s->num_rows()) {
                foreach ($result_s->result_array() as $item) {
                    $usrs[]=array('ModuleID'=>$item['ModuleID'],'ModuleEntityID'=>$item['ModuleEntityID']);
                } 
                $cache_data['s'] = $usrs;
            }

            // Cache for Link
            $Link=$this->get_activity_links($activity_id);
            if(!empty($Link)) {
                $cache_data['l'] = $Link;
            }

            $cache_data['ActivityGUID']=$result_activity['ActivityGUID'];
            $cache_data['PostTitle']=$result_activity['PostTitle'];
            $cache_data['UserID']=$result_activity['UserID'];
            $cache_data['ModuleID']=$result_activity['ModuleID'];
            $cache_data['ModuleEntityID']=$result_activity['ModuleEntityID'];
            $cache_data['PostAsModuleID']=$result_activity['PostAsModuleID'];
            $cache_data['PostAsModuleEntityID']=$result_activity['PostAsModuleEntityID'];
            $cache_data['ActivityTypeID']=$result_activity['ActivityTypeID'];
            $cache_data['Params']=$result_activity['Params'];
            $cache_data['Privacy']=$result_activity['Privacy'];
            $cache_data['IsCommentable']=$result_activity['IsCommentable'];
            $cache_data['PostContent']=$result_activity['PostContent'];
            $cache_data['NoOfLikes']=$result_activity['NoOfLikes'];
            $cache_data['NoOfComments']=$result_activity['NoOfComments'];
            $cache_data['ParentActivityID']=$result_activity['ParentActivityID'];
            $cache_data['IsMediaExist']=$result_activity['IsMediaExist'];
            $cache_data['IsFileExists']=$result_activity['IsFileExists'];
            $cache_data['TagedUser']=json_decode($result_activity['TagedUser']);
            
            if(CACHE_ENABLE) {
                $this->cache->save('activity_'.$activity_id, $cache_data,CACHE_EXPIRATION);
                $this->cache->delete('user_no_of_post_for_rule'.$result_activity['UserID']);
            }
        }
    }
    public function add_link($user_id, $activity_id, $link) {
        $tags_collection = '';
        if (is_array($link['TagsCollection'])) {
            if ($link['TagsCollection']) {
                $tags_collection = implode(',', $link['TagsCollection']);
            } else {
                $tags_collection = '';
            }
        }
        if ($link['ImageURL']) {
            $this->load->model('upload_file_model');
            $image_data = array('DeviceType' => 'Native', 'ImageData' => file_get_contents(site_url() . $link['ImageURL']), 'ImageURL' => site_url() . $link['ImageURL'], 'ModuleID' => '19', 'ModuleEntityGUID' => '', 'Type' => 'wall');
            $linkURL = $this->upload_file_model->saveFileFromURL($image_data);
            $link['ImageURL'] = 'upload/wall/220x220/' . $linkURL['Data']['ImageName'];
        }
        $data = array('URL' => $link['URL'], 'Title' => $link['Title'], 'MetaDescription' => $link['MetaDescription'], 'ImageURL' => $link['ImageURL'], 'TagsCollection' => $tags_collection, 'ActivityID' => $activity_id, 'UserID' => $user_id, 'IsCrawledURL' => $link['IsCrawledURL'], 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->insert(ACTIVITYLINKS, $data);
        $link_id = $this->db->insert_id();

        if ($tags_collection) {
            $tags_list = explode(',', $tags_collection);
            $tags_arr = $this->tag_model->save_tag($tags_list, 1, $user_id);
            if(!empty($tags_arr) && !empty($link_id)) {                
                $this->tag_model->save_entity_tag($tags_arr, 'LINK', $link_id, $user_id);
            }            
        }
        $IsLinkExists = 0;
        if(!empty($link['URL']))
        $IsLinkExists = 1;

        return array('ActivityLinkID'=>$link_id,
                    'IsLinkExists'=>$IsLinkExists,
                    'LinkDesc'=> $link['MetaDescription'],
                    'LinkImgURL'=>$link['ImageURL'],
                    'LinkTags'=>$tags_collection,
                    'LinkTitle'=>$link['Title'],
                    'LinkURL'=>$link['URL']
                    );
    }
    
    public function activate_activity($activity_id) {
        $this->db->set('StatusID', '2');
        $this->db->where('ActivityID', $activity_id);
        $this->db->update(ACTIVITY);
        $this->db->select('UserID,PostContent,ActivityTypeID,ActivityID,ModuleID,ModuleEntityID');
        $this->db->from(ACTIVITY);
        $this->db->where('ActivityID', $activity_id);
        $query = $this->db->get();
        
        if ($query->num_rows()) {
            $row = $query->row_array();
            $this->send_post_notifications($row['UserID'], $row['PostContent'], $row['ActivityTypeID'], $row['ActivityID'], $row['ModuleID'], $row['ModuleEntityID'], 1);
            return $row;
        }
    }

    public function send_post_notifications($user_id, $post_content, $activity_type_id, $activity_id, $module_id, $module_entity_id, $after_process = 0, $post_as_module_id = 0, $post_as_module_entity_id = 0,$users_excluded=array(),$post_type=0,$NotifyAll=0, $is_edit=FALSE) {
        $send_notification = 1;
        $usrs = array($user_id);

        if ($after_process) {
            preg_match_all('/{{([0-9a-zA-Z\s:]+)}}/', $post_content, $matches);
            $mentions = array();
            $mentions_page = array();
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $mention = $this->get_mention($match);
                    if ($mention['ModuleID'] == 3) {
                        $mentions[] = $mention['ModuleEntityID'];
                        $usrs[] = $mention['ModuleEntityID'];
                        if ($activity_type_id == 8 && $mention['ModuleEntityID'] == $module_entity_id) {
                            $send_notification = 0;
                        }
                    } elseif ($mention['ModuleID'] == 18) {
                        $mentions_page[] = $mention['ModuleEntityID'];
                    }
                }
            }
        } else {
            preg_match_all('/{{([0-9a-zA-Z\s:]+)}}/', $post_content, $matches);
            $mentions = array();
            $mentions_page = array();
            $mentions_group = array();
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $match_details = explode(':', $match);
                    if ($match_details[2] == '3' && $match_details[1] != $user_id) {
                        if(empty($users_excluded)) {
                            $mentions[] = $match_details[1];
                        } elseif(!in_array($match_details[1],$users_excluded)) {
                            $mentions[] = $match_details[1];
                        }
                        
                        $usrs[] = $match_details[1];
                        if ($activity_type_id == 8 && $match_details[1] == $module_entity_id) {
                            $send_notification = 0;
                        }
                    } elseif ($match_details[2] == 18) {
                        $mentions_page[] = $match_details[1];
                    } elseif ($match_details[2] == 1) {
                        $mentions_group[] = $match_details[1];
                    }
                }
            }
        }
        $tagged_users = array();
        if (isset($mentions) && !empty($mentions)) {
            //Send Notifications
            $parameters[0]['ReferenceID'] = $user_id;
            $parameters[0]['Type'] = 'User';
            if ($post_as_module_id == '18' && !empty($post_as_module_entity_id)) {
                $parameters[0]['Type'] = 'Page';
                $parameters[0]['ReferenceID'] = $post_as_module_entity_id;
            }

            if($module_id==1 && !empty($mentions)) {
                foreach ($mentions as $key => $value) {
                    if(!$this->group_model->is_member($value,$module_entity_id)) {
                        unset($mentions[$key]);
                    }
                    $tagged_users[] = $value;
                }                
            }
            $this->notification_model->add_notification(18, $user_id, $mentions, $activity_id, $parameters);
        }
        if(!$is_edit && $this->LoggedInName) {
            //log_message('error', 'post_notification sender name = '.$this->LoggedInName); 
            //initiate_worker_job('post_notification', array('ActivityID' => $activity_id, 'UserID' => $user_id, 'SenderName' => $this->LoggedInName, 'NotificationTypeKey' => 'post_message', 'Mentions' => $mentions, 'LocalityID' => $this->LocalityID), '', 'notification');
        }
        
        if (isset($mentions_group) && !empty($mentions_group)) {
            $this->load->model('group/group_model');
            foreach ($mentions_group as $m_g) {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $m_g;
                $parameters[1]['Type'] = 'Group';
                $group_members = $this->group_model->get_group_members_id_recursive($m_g);
                $this->notification_model->add_notification(90, $user_id, $group_members, $activity_id, $parameters);
                foreach ($group_members as $gm) {
                    $this->subscribe_model->toggle_subscribe($gm, 'ACTIVITY', $activity_id);
                }
            }
        }

        if (isset($mentions_page) && !empty($mentions_page)) {
            $this->load->model('pages/page_model');
            foreach ($mentions_page as $m_p) {
                $admins = $this->page_model->get_all_admins($m_p);
                //Send Notifications
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $m_p;
                $parameters[1]['Type'] = 'Page';
                $parameters[2]['ReferenceID'] = '1';
                $parameters[2]['Type'] = 'EntityType';
                //print_r($admins); die;
                $this->notification_model->add_notification(70, $user_id, $admins, $activity_id, $parameters);
                $this->subscribe_model->subscribe_email($user_id, $activity_id, 'page_tagged_post', true, $m_p);
            }
        }


        if($module_id == 14 && !empty($module_entity_id))
        {
            $this->load->model('events/event_model');
            if($NotifyAll) {
                $event_users = $this->event_model->event_members_id($module_entity_id, $user_id);
                $event_users= array_unique($event_users);
                $subscribe_member=array();
                foreach($event_users as $key=>$event_user) {
                    if(in_array($event_user, $tagged_users)) {
                        unset($event_users[$key]);
                    } if($user_id!=$event_user) {
                        $subscribe_member[]=array('ModuleID'=>3,'ModuleEntityID'=>$event_user);
                    }
                }
                if(!empty($event_users)) {
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $module_entity_id;
                    $parameters[1]['Type'] = 'Event';
                    $this->notification_model->add_notification(152, $user_id, $event_users, $activity_id, $parameters);
                }
                if(!empty($subscribe_member)) {
                    $this->subscribe_model->addUpdate($subscribe_member, $activity_id);
                }
            }
        }

        if ($module_id == 1 && !empty($module_entity_id) && $post_type!=='7') { 
            if($NotifyAll) {
                $group_members = $this->group_model->get_group_members_id_recursive($module_entity_id,array(),array(),FALSE,TRUE);
                $group_members= array_unique($group_members);
                $subscribe_member=array();
                foreach($group_members as $key=>$group_member) {
                    if(in_array($group_member, $tagged_users)) {
                        unset($group_members[$key]);
                    } if($user_id!=$group_member) {
                        $subscribe_member[]=array('ModuleID'=>3,'ModuleEntityID'=>$group_member);
                    }
                }
                if(!empty($group_members)) {
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $module_entity_id;
                    $parameters[1]['Type'] = 'Group';
                    $this->notification_model->add_notification(1, $user_id, $group_members, $activity_id, $parameters);
                }
                if(!empty($subscribe_member)) {
                    $this->subscribe_model->addUpdate($subscribe_member, $activity_id);
                }
            } else {
                $groupOwner = $this->group_model->get_all_group_admins($module_entity_id);
                if ($groupOwner) {
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $module_entity_id;
                    $parameters[1]['Type'] = 'Group';
                    $this->notification_model->add_notification(1, $user_id, $groupOwner, $activity_id, $parameters);
                }
            }
        }


        if ($activity_type_id == 8) {
            if ($send_notification) {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $this->notification_model->add_notification(19, $user_id, array($module_entity_id), $activity_id, $parameters);
            }
        }

        if ($activity_type_id == 12) {
            $page_owner = get_detail_by_id($module_entity_id, 18, "UserID", 1);
            if ($user_id != $page_owner) {
                if($post_as_module_id == 18){
                    $parameters[0]['ReferenceID'] = $post_as_module_entity_id;
                    $parameters[0]['Type'] = 'Page';
                } else {
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                }
                
                $parameters[1]['ReferenceID'] = $module_entity_id;
                $parameters[1]['Type'] = 'Page';
                $this->notification_model->add_notification(46, $user_id, array($page_owner), $activity_id, $parameters);
            }
        }

        if ($activity_type_id == 11) {
            $event_creator = get_detail_by_id($module_entity_id, 14, "CreatedBy", 1);
            if ($user_id != $event_creator) {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $module_entity_id;
                $parameters[1]['Type'] = 'Event';
                $this->notification_model->add_notification(61, $user_id, array($event_creator), $activity_id, $parameters);
            }
        }
    }


    public function getActivitiesForCron($user_id, $last_logins = 3) {
        $last_logins = $last_logins - 1;
        $end_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $this->db->select('CreatedDate');
        $this->db->from(ACTIVELOGINS);
        $this->db->where('UserID', $user_id);
        $this->db->limit(1, $last_logins);
        $this->db->order_by('ActiveLoginID', 'desc');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $start_date = $query->row()->CreatedDate;
        } else {
            $start_date = '';
            $end_date = '';
        }

        $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $privacy_options = $this->privacy_model->news_feed_setting_details($user_id);
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = 0;
        $follow[] = 0;
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
        $group_list = $this->group_model->get_joined_groups($user_id, false, array(2));
        $event_list = $this->event_model->get_all_joined_events($user_id);
        $page_list = $this->page_model->get_liked_pages_list($user_id);

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17);
        $condition = '';
        $condition_part_one = '';
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = '';
        $condition_part_four = '';
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';
        if ($friend_followers_list != '') {
            $condition = "(
                IF(A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6, (
                    A.UserID IN(" . $friend_followers_list . ") OR A.ModuleEntityID IN(" . $friend_followers_list . ") OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (
                    (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (
                    A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'
                ), '' )";

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
                IF(A.ActivityTypeID IN(9,10),
                    A.ParentActivityID=(
                        SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1,true,false) OR
                            IF(Privacy=2,UserID IN (" . $friend_followers_list . "),false) OR
                            IF(Privacy=3,UserID IN (" . implode(',', $friends) . "),false) OR
                            IF(Privacy=4,UserID='" . $user_id . "',false))
                    ),false
                )
                OR
                IF(A.ActivityTypeID IN(14,15),
                    A.ParentActivityID=(
                        SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                    ),false
                )
            ),         
        true)";

        $privacy_cond3 = "IF(A.Privacy='4',
            A.UserID='" . $user_id . "', true
        )";
        if (!empty($privacy_cond1)) {
            $privacy_cond .= $privacy_cond1 . ' OR ';
        }
        if (!empty($privacy_cond2)) {
            $privacy_cond .= $privacy_cond2 . ' OR ';
        }
        $privacy_cond .= $privacy_cond3 . ' )';

        // /echo $privacy_cond;
        if (!empty($group_list)) {
            $condition_part_one = $condition_part_one . "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7, (
                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ")
                    ), '' )";
        }
        if (!empty($page_list)) {
            $condition_part_three = $condition_part_three . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17, (
                        A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ")
                    ), '' )";
        }
        if (!empty($event_list)) {
            $condition_part_four = $condition_part_four . "IF(A.ActivityTypeID=11, (
                        A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")
                    ), '' )";
        }
        if (!empty($condition)) {
            if (!empty($condition_part_one)) {
                $condition = $condition . " OR " . $condition_part_one;
            }
            if (!empty($condition_part_three)) {
                $condition = $condition . " OR " . $condition_part_three;
            }
            if (!empty($condition_part_four)) {
                $condition = $condition . " OR " . $condition_part_four;
            }
            $condition = $condition . ")";
        } else {
            if (!empty($condition_part_one)) {
                $condition = $condition_part_one;
            }
            if (!empty($condition_part_three)) {
                if (empty($condition)) {
                    $condition = $condition_part_three;
                } else {
                    $condition = $condition . " OR " . $condition_part_three;
                }
            }

            if (empty($condition)) {
                $condition = $condition_part_two;
            } else {
                //$condition = $condition_part_two. " OR ".$condition_part_one; 
                $condition = "(" . $condition . ")";
            }
        }
        $this->db->select('A.ActivityID');
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        //$this->db->join(USERACTIVITYRANK.' UAR','UAR.ActivityID=A.ActivityID AND UAR.UserID="'..'"','left');

        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);


        $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

        $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);

        //$this->db->where_in('A.StatusID',array('1','2'));
        $this->db->where('ATY.StatusID', '2');
        if (!empty($condition)) {
            $this->db->where($condition, NULL, FALSE);
        } else {
            $this->db->where('A.ModuleID', '3');
            $this->db->where('A.ModuleEntityID', $user_id);
        }
        if ($privacy_condition) {
            $this->db->where($privacy_condition, null, false);
        }

        if ($start_date) {
            $this->db->where("A.CreatedDate >= '" . $start_date . " 00:00:00'", NULL, FALSE);
        }
        if ($end_date) {
            $this->db->where("A.CreatedDate <= '" . $end_date . " 23:59:59'", NULL, FALSE);
        }

        $this->db->where_not_in('U.StatusID', array(3, 4));

        $result = $this->db->get();
        //echo $this->db->last_query();
        if ($result->num_rows()) {
            return $result->result_array();
        } else {
            return false;
        }
    }

    public function get_activity_links($activity_id) {
        $activity_cache=array();
        $result=array();
        if(CACHE_ENABLE && $activity_id) {
            $activity_cache=$this->cache->get('activity_'.$activity_id);
            if(!empty($activity_cache)) {
                if(isset($activity_cache['l'])) {
                    $result = $activity_cache['l'];
                }
            }
        }
        if(empty($activity_cache)) {
            $this->db->select('IF(URL is NULL,0,1) as IsLinkExists', false);
            $this->db->select('URL as LinkURL,Title as LinkTitle,MetaDescription as LinkDesc,ImageURL as LinkImgURL,TagsCollection as LinkTags');
            $this->db->from(ACTIVITYLINKS);
            $this->db->where('ActivityID', $activity_id);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $result= $query->result_array();
            }
        }
        return $result;
    }

    public function get_activity_share_details($activity_guid) {
        $activity = get_detail_by_guid($activity_guid, 0, 'PostContent,Params,ActivityTypeID,ActivityID,UserID', 2);
        if ($activity['ActivityTypeID'] == '5' || $activity['ActivityTypeID'] == '10') {
            $activity['Params'] = json_decode($activity['Params']);
            $album_details = $this->album_model->get_album_by_guid($activity['Params']->AlbumGUID);
            $activity['Album'] = $this->get_albums($activity['ActivityID'], $activity['UserID'], $activity['Params']->AlbumGUID, 'Activity', 1);
        } else {
            $activity['Album'] = $this->get_albums($activity['ActivityID'], $activity['UserID']);
        }
        $share_image = '';
        if (isset($activity['Album'][0]['Media'][0])) {
            $share_image = IMAGE_SERVER_PATH . 'upload/';
            if ($activity['ActivityTypeID'] == '23') {
                $share_image .= 'profile/220x220/';
            } else if ($activity['ActivityTypeID'] == '24') {
                $share_image .= 'profilebanner/1200x300';
            } else if ($activity['Album'][0]['AlbumName'] == 'Wall Media') {
                $share_image .= 'wall/750x500';
            } else if ($activity['Album'][0]['AlbumName'] != 'Wall Media') {
                $share_image .= 'album/750x500';
            }
            $share_image .= '/' . $activity['Album'][0]['Media'][0]['ImageName'];
        }
        $data = array('OGImage' => $share_image, 'OGDesc' => $activity['PostContent']);
        return $data;
    }

    public function get_public_feed($activity_guid='',$module_id=0,$module_entity_id=0,$page_no=1,$page_size=10,$count_only=false, $rules = array(), $extra_params = array(),$post_type = 0) {
        $this->load->model(array('polls/polls_model','category/category_model','contest/contest_model'));
        $data = array();
        
        if($count_only && !empty($extra_params['onlyFeedQuery'])) {
            $this->db->select('A.ActivityID');
        } else {
            $this->db->select('A.*,ATY.ViewTemplate,ATY.Template,ATY.LikeAllowed,ATY.CommentsAllowed,ATY.ActivityType,ATY.ActivityTypeID,ATY.FlagAllowed,ATY.ShareAllowed,ATY.FavouriteAllowed,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        }
        
        $exclude_activity_type = $this->get_allowed_activity_type('public',$module_id,'',0);
        
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        
        //$this->activityrule_model->rule_conds_for_not_logged_in_user($rules);        
        $this->db->where('A.StatusID', '2');
        $this->db->where('A.Privacy', '1');
        $this->db->where('ATY.StatusID', '2');
        $this->db->where("IF(A.ActivityTypeID=8,(
                (SELECT UserID FROM ".USERPRIVACY." WHERE PrivacyLabelKey='default_post_privacy' AND Value='everyone' AND UserID=A.ModuleEntityID AND A.ModuleID='3') is not null
            ),true)",NULL,FALSE);
        $this->db->where("IF(A.ActivityTypeID=23 OR A.ActivityTypeID=24,A.ModuleID=3,true)",NULL,FALSE);
        $this->db->where("IF(A.ActivityTypeID=7,(SELECT GroupID FROM ".GROUPS." WHERE IsPublic IN(1, 0) AND GroupID=A.ModuleEntityID AND StatusID='2') is not null,true)",NULL,FALSE);
        
        $this->db->where("IF(A.ActivityTypeID=26,(SELECT ForumCategoryID FROM ".FORUMCATEGORY." WHERE Visibility IN(1) AND ForumCategoryID=A.ModuleEntityID AND StatusID='2') is not null,true)",NULL,FALSE);

        $this->db->where_not_in('A.ActivityTypeID',$exclude_activity_type);

        if($post_type) {
            $this->db->where_in('A.PostType',$post_type);
        }

        if($activity_guid) {
            $this->db->where('A.ActivityGUID', $activity_guid);
        } else {
            
            // Exclude feeds for forum category with visibility 0
            $forum_category_exclude_cnds = 
                    "A.ModuleEntityID NOT IN ".

                    "(Select ForumCategoryID From ". 
                    FORUMCATEGORY . " FC INNER JOIN " . FORUM . " F "
                    . " ON F.ForumID = FC.ForumID"
                    . " WHERE F.Visible = 0 )"
                    ;
            $this->db->where($forum_category_exclude_cnds, null, false);
            
            
            if($module_id == '34' && !$module_entity_id) {
                $this->db->where('A.ModuleID',$module_id);
            } else if($module_entity_id) {
                $this->db->where('A.ModuleID',$module_id);
                $this->db->where('A.ModuleEntityID',$module_entity_id);
            }
        }
        if(!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        
        if(!empty($extra['AllowedActivityTypeID'])) {            
            $this->db->where('A.UserID != ', $extra['UserID']);            
            $this->db->order_by('A.TotalLikeViewComment','DESC');
        }else if(!empty($extra_params['feed_sort_by']) && $extra_params['feed_sort_by']==4){
            $this->db->order_by('A.IsFeatured DESC,(A.NoOfComments+A.NoOfLikes+A.NoOfViews) DESC,A.CreatedDate DESC');
        } else {
            if($module_entity_id) {
                $this->db->order_by('A.ModifiedDate','DESC');
            } else {
                if($activity_guid=='') {
                    $this->db->where('A.IsFeatured', '1');
                }
                //$this->db->order_by('A.FeaturedDate','DESC');
                $this->db->order_by('A.ModifiedDate','DESC');
            }
        }
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if($count_only) {
            return $query->num_rows();
        }
        if ($query->num_rows()) {
            $feed_result = $query->result_array();
            $role_id=2;
            $is_single_activity = 0;
            if($activity_guid!='')
            {
                $is_single_activity = 1;
            }
            $data = $this->activity_result_filter_model->filter_result_set($feed_result, 1, 0, 0, $role_id, 1, false, $module_entity_id, $module_id, $this,$is_single_activity);          
        }
        return $data;
    }

    /**
     * [getDummyUserActivities Get the activity for dashboard]
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
    public function getDummyUserActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), $activity_ids = array(),$view_entity_tags=1,$role_id=2,$post_type=0,$tags='', $rules = array())
    {
        $this->load->model(array('polls/polls_model','category/category_model','forum/forum_model'));
        $this->load->model('sticky/sticky_model');

        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        $category_list = $this->forum_model->get_user_category_list();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0; 
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        $group_list = $this->group_model->get_user_group_list();
        $category_group_list = $this->group_model->get_user_categoty_group_list();
        $group_list[] = 0;
        
        $group_list = implode(',', $group_list);
        if(!empty($category_group_list))
        {
           $group_list=$group_list.','.$category_group_list;
           if($group_list)
           {
               $group_list= implode(',',array_unique(explode(',', $group_list)));
           }
        }
        $event_list = $this->event_model->get_user_joined_events();
        //$page_list = $this->page_model->get_user_pages_list();
        $page_list = $this->page_model->get_feed_pages_condition();

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 25, 26);//23, 24,
        $modules_allowed = array(1,3,14,18, 30, 34);
        $show_suggestions = FALSE;
        $show_media = TRUE;
        
        if ($filter_type == 3)
        {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26, 30);//23, 24,
        }

        /* --Filter by activity type id-- */
        //$activity_ids = array();
        if (!empty($activity_type_filter))
        {
            $activity_type_allow = $activity_type_filter;
            $show_suggestions = false;

            //7 = My Polls, 8= Expired
            if ($filter_type == 7 || $filter_type == 8)
            {
                $is_expired = FALSE;
                if ($filter_type == 8)
                {
                    $is_expired = TRUE;
                }

                $activity_ids = $this->polls_model->my_poll_activities($entity_id, $entity_module_id, $is_expired);
                if (empty($activity_ids))
                {
                    return array();
                }
            }
            //My Voted Polls
            if ($filter_type == 9)
            {
                $activity_ids = $this->polls_model->my_voted_poll_activities($entity_id, $entity_module_id);
                if (empty($activity_ids))
                {
                    return array();
                }
            }
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed))
        {

            $modules_allowed[] = 1;
        }

        //Activity Type 1 for followers, friends and current user
        //Activity Type 2 for followers and friends only
        //Activity Type 3 for follower and friend of UserID
        //Activity Type 8, 9, 10 for Mutual Friends Only
        //Activity Type 4, 7 for Group Members Only
        $condition = '';
        $condition_part_one = '';
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = '';
        $condition_part_four = '';
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();

        // /echo $privacy_cond;
        if ($group_list)
        {
            
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if(!empty($category_list))
        {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
        if (!empty($page_list))
        {
            $case_array[]="A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        if (!empty($event_list))
        {
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")";
        }
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition))
        {
            $condition = $condition_part_two;
        } 

        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';
        $select_array[]='IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        /*$this->db->join(FRIENDS." FR","FR.FriendID=A.ModuleEntityID AND A.ModuleID='3' AND FR.UserID='".$user_id."' AND FR.Status='1'");*/
        $this->db->_protect_identifiers = TRUE;

        /* Join Activity Links Starts */
        $select_array[]='IF(URL is NULL,0,1) as IsLinkExists';
        $select_array[]='AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';

        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        /* Join Activity Links Ends */
        
        
        $this->db->where('U.UserTypeID','4');

        if($post_type)
        {
            $this->db->where_in('A.PostType',$post_type);
        }

        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } 
        else if ($filter_type == 10)
        {
            $this->db->where('A.StatusID', '10');
            //$this->db->where('A.UserID', $user_id);
        } 
        else if ($filter_type == 11)
        {
            $this->db->where('A.IsFeatured', '1');
            $this->db->where('A.StatusID', '2');
        } 
        else
        {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED'", "join");
                $this->db->_protect_identifiers = TRUE;
            } 
            else if (($filter_type == 1 || $filter_type === 'Favourite') && empty($activity_ids))
            {
                $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"');
                //$this->db->where('F.UserID', $user_id);
                $this->db->where('F.StatusID', '2');
            } 
            else
            {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43))
                {
                    $this->db->where("NOT EXISTS (SELECT 1 FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND ActivityID=A.ActivityID AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids)
            {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            $this->db->where_in('A.ModuleID', $modules_allowed);
            $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
            if ($activity_guid)
            {
                $this->db->where('A.ActivityGUID', $activity_guid);
            }

            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        }

        if($tags)
        {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM ".ENTITYTAGS." WHERE EntityType='ACTIVITY' AND TagID IN (".implode(',',$tags)."))",null,false);
        }

        if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
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
        }        

        if (!$show_media)
        {
            if ($is_media_exists == 2)
            {
                $is_media_exists = '0';
            }
            if ($is_media_exists == 1)
            {
                $is_media_exists = '3';
            }
        }

        if ($is_media_exists != 2)
        {
            $this->db->where('A.IsMediaExist', $is_media_exists);
        }        

        if (!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        $this->db->where('ATY.StatusID', '2');
        
        
        if ($feed_sort_by == 2)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } else if($feed_sort_by == 3)
        {
            $this->db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfViews)', 'DESC');
        } else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }
        
        if ($feed_sort_by == 'popular')
        {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } elseif ($feed_sort_by == 1)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } 
        elseif($feed_sort_by=="General")
        {
            $this->db->where('A.PostType',0);
        }
        elseif ($feed_sort_by=="Question") {
            $this->db->where('A.PostType',1);
        }
        elseif ($feed_sort_by=="UnAnswered") {
            $this->db->where('A.PostType',1);
            $this->db->where('A.NoOfComments',0);
        }
        else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($filter_type == 3)
        {
            if ($ReminderDate)
            {
                $rd_data = array();
                foreach ($ReminderDate as $rd)
                {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        //$this->db->where_not_in('U.StatusID', array(3, 4));
    
        if (!$count_only)
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        if ($count_only)
        {
            $this->db->select('COUNT(DISTINCT A.ActivityID) as TotalRow ' );
            $result = $this->db->get();
            $count_data=$result->row_array();
            return $count_data['TotalRow'];
        }
        else{
            $this->db->select(implode(',', $select_array),false);
            $this->db->group_by('A.ActivityID');
            $result = $this->db->get();
        }
        //echo $this->db->last_query();
        //if(count($rules)){ print_r($rules);  echo $this->db->last_query(); die; }
        $return = array();
        if ($result->num_rows())
        {
            $cnt = 1;
            /**** variables defined starts ****/
            $user_favourite = $this->favourite_model->get_user_favourite();
           // $user_subscribed = $this->subscribe_model->get_user_subscribed();
            //$user_tagged = $this->activity_model->get_user_tagged();
            //print_r($user_tagged);die;
            $user_flagged = $this->flag_model->get_user_flagged();
            $user_archive = $this->get_user_activity_archive();
            
            $feed_result = $result->result_array();
            
            $return = $this->activity_result_filter_model->filter_result_set($feed_result, $page_no, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id, $this,0);                       
        }
        return $return;
    }
    
    /**
     * [getFeedActivities Get the activity for dashboard]
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
    public function getFeedActivities(
            $user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, 
            $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, 
            $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', 
            $activity_type_filter = array(), $activity_ids = array(),$view_entity_tags=1,$role_id=2,
            $post_type = 0, $tags = '', $rules = array(), $extra_params = []
    ){ 
        $this->load->model(array('activity/activity_feed_model'));
        return $this->activity_feed_model->getFeed(
            $user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, 
            $search_key, $start_date, $end_date, $show_archive, $count_only, 
            $ReminderDate, $activity_guid, $mentions, $entity_id, $entity_module_id, 
            $activity_type_filter, $activity_ids, $view_entity_tags, $role_id, 
            $post_type, $tags, $rules, $extra_params
        );
    }

    /**
     * [change_activity_status change activity status]
     * @param  [type] $activity_id   [ActivityID]
     * @param  [type] $StatusID     [StatusID]
     */
    public function change_activity_status($activity_id, $status_id)
    {
        $this->db->set('StatusID', $status_id);
        $this->db->where('ActivityID', $activity_id);
        $this->db->update(ACTIVITY);
    }

    /**
     * [getSingleUserActivity Used to get particular activity details]
     * @param  [type] $user_id     [User ID]
     * @param  [type] $activity_id [Activity ID]
     * @return [type]             [get Activity details]
     */
    public function getSingleUserActivity($user_id, $activity_id, $AllActivity = 0, $html_parse = false, $entity_id = '', $entity_module_id = '',$role_id=2)
    {
        $exclude_activity_type = $this->activity_model->get_allowed_activity_type('',$entity_module_id,'',0);
        $is_super_admin = $this->user_model->is_super_admin($user_id);
        $this->load->model(array('forum/forum_model','contest/contest_model'));
        $current_user = $user_id;
        $this->load->model(array('polls/polls_model','category/category_model'));
        $this->db->select('A.*,ATY.ViewTemplate,ATY.Template,ATY.LikeAllowed,ATY.CommentsAllowed,ATY.ActivityType,ATY.ActivityTypeID,ATY.FlagAllowed,ATY.ShareAllowed,ATY.FavouriteAllowed,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');

        /* Join Activity Links Starts */
        $this->db->select('IF(URL is NULL,0,1) as IsLinkExists', false);
        $this->db->select('URL as LinkURL,Title as LinkTitle,MetaDescription as LinkDesc,ImageURL as LinkImgURL,TagsCollection as LinkTags');
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        /* Join Activity Links Ends */

        //$this->db->where('A.StatusID','2');
        $this->db->where('A.ActivityID', $activity_id);
        $this->db->where('ATY.StatusID', '2');
        if($exclude_activity_type)
        {
            $this->db->where_not_in('A.ActivityTypeID',$exclude_activity_type);
        }
        $this->db->limit(1);
        $result = $this->db->get();
        $return = array();
        $BUsers = $this->block_user_list($current_user); /*added by gautam*/
        if ($result->num_rows())
        {
            $this->load->model(array('activity/activity_result_filter_model'));            
            foreach ($result->result_array() as $res) {
                $activity_id                = $res['ActivityID'];
                $activity_guid              = $res['ActivityGUID'];
                $module_id                  = $res['ModuleID'];
                $activity_type_id           = $res['ActivityTypeID'];
                $module_entity_id           = $res['ModuleEntityID'];
                
                $activity['PostAsModuleID'] = $res['PostAsModuleID'];
                $activity['ActivityID']     = $res['ActivityID'];
                $activity['StatusID']       = $res['StatusID'];
                $activity['UserID']         = $res['UserID'];
                $activity['Viewed']         = 0; /* added by gautam */
                $activity['IsDeleted']      = 0;
                $activity['IsEdited']       = $res['IsEdited'];
                $activity['IsEntityOwner']  = 0;
                $activity['IsOwner']        = 0;
                $activity['IsFlaggedIcon']  = 0;
                $activity['IsFlagged']      = 0;
                $activity['CanShowSettings'] = 1;
                $activity['CanRemove']      = 0;
                $activity['CanMakeSticky']  = 0;
                $activity['ShowPrivacy']    = 0;
                $activity['PostAsEntityOwner'] = 0;
                $activity['IsMember']       = 1;
                $activity['IsArchive']      = 0;
                $activity['ShowInviteGraph']= 0;
                $activity['IsPined']        = ($res['IsVisible'] == '3') ? 1 : 0;
                
                $activity['PollData']       = array();
                $activity['Reminder']       = array();
                $activity['ActivityGUID']   = $activity_guid;
                $activity['ModuleID']       = $module_id;
                $activity['UserGUID']       = $res['UserGUID'];
                $activity['ActivityType']   = $res['ActivityType'];

                $activity['NotifyAll']      = $res['NotifyAll'];

                $activity['NoOfFavourites'] = $res['NoOfFavourites'];
                $activity['IsFeatured']     = $res['IsFeatured'];
                $activity['IsSingleActivity'] = 1;
                
                //$activity['ShareEntityTagged'] = ''; /* added by gautam */

                $activity['LikeAllowed']    = $res['LikeAllowed'];
                $activity['FlagAllowed']    = $res['FlagAllowed'];
                $activity['ShareAllowed']   = 1;
                $activity['FavouriteAllowed'] = $res['FavouriteAllowed'];
                $activity['NoOfShares']     = $res['NoOfShares'];
                $activity['Message']        = $res['Template'];
                $activity['ViewTemplate']   = $res['ViewTemplate'];
                $activity['CreatedDate']    = $res['CreatedDate'];
                $activity['ModifiedDate']   = $res['ModifiedDate'];
                $activity['PromotedDate']   = $res['PromotedDate'];
                $activity['IsSticky']       = $res['IsSticky'];
                $activity['Visibility']     = $res['Privacy'];
                $activity['PostContent']    = $res['PostContent'];
                $activity['PostTitle']      = $res['PostTitle'];
                $activity['PostType']       = $res['PostType'];
                $activity['ModuleEntityID'] = $res['ModuleEntityID'];
                $activity['ParentActivityID'] = $res['ParentActivityID'];
                $activity['SharedActivityModule'] = '';
                $activity['SharedEntityGUID'] = '';
                $activity['IsPromoted']     = $res['IsPromoted'];

                $activity['ContestEndDate'] = $res['ContestEndDate'];
                $activity['IsWinnerAnnounced'] = 0;
                $activity['IsParticipating'] = 0;
                $activity['Participants'] = $this->contest_model->get_participant_friends($res['ActivityID'],$user_id);
                $activity['Winners'] = $this->contest_model->get_contest_winners($res['ParentActivityID'],$user_id);
                $activity['IsAdmin'] = 0;
                $activity['Facts']          = $res['Facts'];
                
                $res_entity_type            = 'Activity';
                $res_entity_id              = $activity_id;

                
                $activity['ShowBTNCommentsAllowed'] = 1;

                                
                $activity['IsTaskDone'] = 0;
                if($mydesk_task = $this->mydesk_model->is_mydesk_task($activity_id,$user_id,true)) {
                    $activity['IsTaskDone'] =  (isset($mydesk_task['Status']) && $mydesk_task['Status'] == 'NOTDONE') ? 0 : 1;
                }
                $activity['IsAnyoneTagged'] = $this->is_anyone_mentioned($activity_id);                
                $activity['IsWatchList']    = $this->watchlist_model->is_watchlist($activity_id,$user_id);
                //Link Variable Assignment Starts    
                $activity['Links']          = $this->get_activity_links($activity_id);
                
                if (in_array($activity_type_id, array(23, 24))) {
                    $params = json_decode($res['Params'], true);
                    if ($params['MediaGUID']) {
                        $res_entity_id = get_detail_by_guid($params['MediaGUID'], 21);
                        if ($res_entity_id) {
                            $res_entity_type = 'Media';
                            $activity['NoOfComments'] = $this->get_activity_comment_count($res_entity_type, $res_entity_id, $BUsers); //$res['NoOfComments'];
                            $activity['NoOfLikes'] = $this->get_like_count($res_entity_id, $res_entity_type, $BUsers); //$res['NoOfLikes'];
                            // $activity['NoOfDislikes'] = $this->get_like_count($res_entity_id, $res_entity_type, $BUsers, 3); //$res['NoOfDislikes'];
                            $activity['Album'] = $this->get_albums($res_entity_id, $res['UserID'], '', $res_entity_type, 1);
                        }
                    }
                } else {
                    if (!in_array($activity_type_id, array(5, 6, 9, 10, 14, 15))) {
                        $activity['Album'] = $this->get_albums($res_entity_id, $res['UserID'], '', $res_entity_type, 4);
                    }
                    if ($BUsers) {
                        $activity['NoOfComments'] = $this->get_activity_comment_count($res_entity_type, $activity_id, $BUsers); //$res['NoOfComments'];
                        $activity['NoOfLikes'] = $this->get_like_count($activity_id, $res_entity_type, $BUsers); //
                        //$activity['NoOfDislikes'] = $this->get_like_count($activity_id, $res_entity_type, $BUsers, 3); //
                    } else {
                        $activity['NoOfComments'] = $res['NoOfComments']; //$res['NoOfComments'];
                        $activity['NoOfLikes'] = $res['NoOfLikes']; //
                        //$activity['NoOfDislikes'] = $res['NoOfDislikes']; //
                    }
                } 
                
                $activity['CommentsAllowed'] = 0;
                if ($res['IsCommentable'] && $res['CommentsAllowed']) {
                    $activity['CommentsAllowed'] = 1;
                }
                
                $activity['Files'] = array();
                if ($res['IsFileExists']) {
                    $activity['Files'] = $this->get_activity_files($activity_id);
                }
                
                $activity['Params']         = json_decode($res['Params']);
                $activity['IsSubscribed']   = $this->subscribe_model->is_subscribed($current_user, 'Activity', $activity_id);
                $activity['IsFavourite']    = $this->favourite_model->is_favourite($activity_id, $current_user);
                
                $activity['Flaggable'] = $res['Flaggable'];
                $activity['FlaggedByAny'] = 0;
                $activity['CanBlock'] = 0;
                
                if ($res['UserID'] == $user_id) {
                    $activity['IsOwner'] = 1;
                }

                if ($this->flag_model->is_flagged($current_user, $activity_id, 'Activity')) {
                    $activity['IsFlagged'] = 1;
                }
                
                if ($current_user == $res['ModuleEntityID'] && $res['ModuleID'] == 3) {
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                }

                $activity['EntityName'] = '';
                $activity['EntityProfilePicture'] = '';
                $activity['EntityID'] = '';
                $activity['UserName'] = $res['FirstName'] . ' ' . $res['LastName'];
                $activity['UserProfilePicture'] = $res['ProfilePicture'];
                $activity['UserProfileURL'] = get_entity_url($res['UserID'], 'User', 1);
                $activity['EntityType'] = '';
                $activity['IsExpert'] = 0;
                
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
               
                if ($module_id == 1) {
                    $group_details = check_group_permissions($user_id, $module_entity_id);

                    if($this->group_model->is_admin($res['UserID'], $module_entity_id))
                    {
                        $activity['IsAdmin'] = 1;
                    }

                    //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                    if (isset($group_details['Details']) && !empty($group_details['Details'])) {
                        $entity                         = $group_details['Details'];
                        $activity['EntityProfileURL']   = $module_entity_id;
                        $activity['EntityGUID']         = $entity['GroupGUID'];
                        $activity['EntityID']           = $module_entity_id;
                        $activity['EntityName']         = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['IsExpert']           = $group_details['IsExpert'];
                        $activity['GroupType'] = $entity['Type'];
                        $activity['GroupPrivacy'] = $entity['IsPublic'];
                        if(empty($group_details['CanComment'])) {
                            $activity['CommentsAllowed'] = 0;
                        }

                        if ($entity['Type'] == 'INFORMAL') {
                            $activity['EntityMembersCount'] = $this->group_model->members($module_entity_id, $user_id, TRUE);
                            $activity['EntityMembers'] = $this->group_model->members($module_entity_id, $user_id);
                        }

                        if ($group_details['IsAdmin']) {
                            $activity['IsEntityOwner']  = 1;
                            $activity['CanRemove']      = 1;
                            $activity['CanBlock']       = 1;
                            $activity['CanMakeSticky']  = 1;
                        }
                        if ($this->group_model->check_group_creator($module_entity_id, $res['UserID'])) {
                            $activity['CanBlock'] = 0;
                        }
                        if($res['UserID']!=$user_id) {
                            $activity['IsExpert'] = $this->group_model->check_is_expert($res['UserID'],$module_entity_id);
                        }
                    }
                }
                if ($module_id == 3)
                {
                    $activity['EntityName'] = $activity['UserName'];
                    $activity['EntityProfilePicture'] = $activity['UserProfilePicture'];
                    $activity['EntityGUID'] = $res['UserID'];
                    $activity['EntityID']           = $module_entity_id;
                    $activity['Occupation']               = '';
                    //$entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    $entity = $this->user_model->get_user_details($module_entity_id);
                    if ($entity) {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                        $activity['Occupation'] = $entity['Occupation'];
                        $activity['EntityID']           = $module_entity_id;
                    }

                    $activity['EntityProfileURL'] = get_entity_url($module_entity_id, 'User', 1);
                    if ($current_user == $module_entity_id) {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                }
                if ($module_id == 14)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                    if ($entity) {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $activity['EntityGUID'] = $entity['EventGUID'];
                        $activity['EntityID']           = $module_entity_id;
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
                if ($module_id == 18)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                        $activity['EntityID']           = $module_entity_id;
                        $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                        $category_icon = $category_name['Icon'];
                        if ($entity['ProfilePicture'] == '') {
                            $activity['EntityProfilePicture'] =  $category_icon;
                        }
                        
                        $activity['ModuleEntityOwner'] = $res['ModuleEntityOwner'];
                        if ($res['PostAsModuleID'] == 18 ) { 
                            $PostAs=$this->page_model->get_page_detail_cache($res['PostAsModuleEntityID']);
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
                if($module_id == 34)
                {
                    $activity['IsExpert'] = $this->forum_model->check_is_expert($res['UserID'],$module_entity_id);
                    $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                    if ($entity) {
                        $activity['EntityName'] = $entity['Name'];
                        $activity['EntityProfilePicture'] = $entity['MediaID'];
                        $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                        $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                        $activity['EntityID']           = $module_entity_id;
                    }                   
                    $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);
                    $user_perm = $this->forum_model->check_forum_category_permissions($res['UserID'], $module_entity_id);

                    if($user_perm['IsAdmin'])
                    {
                        $activity['IsAdmin'] = 1;
                    }

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
                
                if($activity['IsOwner']== 1) {
                   $activity['IsLike'] = $this->is_liked($res_entity_id, $res_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
                   //$activity['IsDislike'] = $this->is_liked($res_entity_id, $res_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID'], 3);
                } else {
                   $activity['IsLike'] = $this->is_liked($res_entity_id, $res_entity_type, $user_id, 3, $user_id); 
                   //$activity['IsDislike'] = $this->is_liked($res_entity_id, $res_entity_type, $user_id, 3, $user_id, 3);  
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
                            @$count = $activity['Params']->count;
                        }
                        $album_details = $this->album_model->get_album_by_guid($activity['Params']->AlbumGUID);
                        $activity['AlbumEntityName'] = $activity['EntityName'];
                        $activity['EntityName'] = $album_details['AlbumName'];
                        $activity['Album'] = $this->activity_model->get_albums($activity_id, $res['UserID'], $activity['Params']->AlbumGUID, 'Activity', $count);
                        $log_type = 'Album';
                        $log_id = isset($activity['Album']['AlbumID']) ? $activity['Album']['AlbumID'] : 0;
                    }
                }
                
                $activity['ViewCount'] = 0;
                if ($log_id && $is_super_admin) {
                    $activity['ViewCount'] = view_count($log_type, $log_id);
                }
                
                if ($activity_type_id == '1' || $activity_type_id == '8' || $activity_type_id == '9' || $activity_type_id == '10' || $activity_type_id == '7' || $activity_type_id == '11' || $activity_type_id == '12' || $activity_type_id == '14' || $activity_type_id == '15' || $activity_type_id == '5' || $activity_type_id == '6') {
                    $activity['CanShowSettings'] = 1;
                }


                if ($res['Privacy'] != 4 && ($activity_type_id == 1 || $activity_type_id == 8 || $activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 7 || $activity_type_id == 11 || $activity_type_id == 12 || $activity_type_id == 14 || $activity_type_id == 15 || $activity_type_id == 5 || $activity_type_id == 6)) {
                    $activity['ShareAllowed'] = 1;
                }
                if ($user_id == $res['UserID'] || ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)) {
                    //$activity['ShareAllowed'] = 0; // do not show share likn for self post
                    $activity['ShowPrivacy'] = 0;
                    if ($res['ActivityTypeID'] == 1 || $activity_type_id == 8 || $activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 23 || $activity_type_id == 24) {
                        $activity['ShowPrivacy'] = 1;
                        $activity['CanRemove'] = 1;
                    }
                }
                
                $activity['Comments'] = array();
                if ($activity['NoOfComments'] > 0) {
                    if ($activity['IsOwner']) {
                        $activity['CanRemove'] = 1;
                    }
                    $activity['Comments'] = $this->getActivityComments('Activity', $activity_id, '1', COMMENTPAGESIZE, $user_id, $activity['CanRemove'], 2, TRUE, $BUsers, FALSE, '', $res['PostAsModuleID'], $res['PostAsModuleEntityID']);                    
                }
                
                if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12) {
                    $activity['PostContent'] = str_replace('­', '', $activity['PostContent']);
                    if (empty($activity['PostContent'])) {
                        $pcnt = $this->get_photos_count($res['ActivityID']);
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
                
                $this->load->model(array('activity/activity_front_helper_model')); 
                $activity = $this->activity_front_helper_model->get_sticky_setting($user_id,$activity_id,$role_id,$activity);
                
                $activity['IsAnonymous'] = $res['IsAnonymous'];

                if ($res['IsAnonymous'] == 1 && $res['UserID'] != $user_id) {
                    $activity['UserName'] = "Anonymous User";
                    $activity['UserProfileURL'] = '';
                    $activity['UserProfilePicture'] = '';
                }
                
                $activity['EntityTags'] = $this->tag_model->get_entity_tags('', 1, 30, 1, 'ACTIVITY', $activity_id, $current_user);
                $activity['ActivityURL'] = get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
                
                /* Share Details */
                $share_data = array();
                if($res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15')
                {
                    $share_data = $this->activity_result_filter_model->getShareDetails($activity,$activity_type_id,$res['UserID']);

                    if ($activity_type_id == 10 || $activity_type_id == 15) {
                        if ($share_data['ModuleID'] == '1' && $share_data['PostType'] == '7') {
                            $activity['Message'] = str_replace("{{OBJECT}}", "{{ACTIVITYOWNER}}", $activity['Message']);
                        } else {
                            if ($share_data['UserID'] == $res['UserID']) {
                                $activity['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($share_data['UserID']), $activity['Message']);
                            }
                        }
                    }
                    unset($share_data['ParentActivityTypeID']);
                }
                $activity['ShareDetails'] = $share_data;

                
                $edit_post_content = $activity['PostContent'];
                if ($html_parse && $this->IsApp != 1) {
                    $activity['PostContent'] = $this->parse_tag_html($activity['PostContent']);
                    $activity['EditPostContent'] = $this->parse_tag_edit($edit_post_content);                    
                } else {
                    $activity['PostContent'] = $this->parse_tag($activity['PostContent']);
                    $activity['EditPostContent'] = $this->parse_tag_edit($edit_post_content);                    
                }

                if($res['ActivityTypeID'] == 13)
                {
                    $activity['ShareAllowed'] = 0;
                }

                if ($this->IsApp == 1) { /* For Mobile */
                    $activity['TagedUser'] = json_decode($res['TagedUser']);
                    $activity['PostContent'] = $this->activity_result_filter_model->get_description($activity['PostContent']);
                    $activity = $this->activity_result_filter_model->getTitleMessage($activity, $entity_id, $entity_module_id);
                } else {
                    $activity['PostContent'] = html_entity_decode($activity['PostContent']);
                } 
                $return[] = $activity;
            }
        }
        return $return;
    }

    function getSingleComment($comment_id, $user_id) {
        $this->db->select('PC.*,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,U.UserID,EntityType,EntityID,ParentCommentID');
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->join(USERS . ' U', 'PC.UserID=U.UserID', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
        $this->db->where('PC.PostCommentID', $comment_id);

        $sql = $this->db->get();
        $comments = array();
        
        if ($sql->num_rows()) {            
            foreach ($sql->result() as $comment) {
                $IsOwner=0;
                switch ($comment->EntityType) {
                    case 'Media':
                        $ModuleID = 21;
                        // Get details for media
                        $EntityDetails = get_detail_by_id($comment->EntityID, 21, "MediaID, UserID, ModuleID, ModuleEntityID", 2);
                        if (!empty($EntityDetails)) {
                            $EntityID = $EntityDetails['MediaID'];
                        }
                        if ($EntityDetails['UserID'] == $user_id) {
                            $IsOwner = 1;
                        }
                        break;
                    case 'Rating':
                        $EntityID = $comment->EntityID;
                        $EntityDetails = false;
                        break;
                    case 'Album':
                        $this->load->model('album/album_model');
                        $EntityDetails = get_detail_by_id($comment->EntityID, 0, "UserID, ModuleID, ModuleEntityID", 2);
                        if ($EntityDetails['ModuleEntityID'] == $user_id) {
                            $IsOwner = 1;
                        }
                        break;
                    default:
                        $ModuleID = 0;
                        // Get details for activity
                        $EntityDetails = get_detail_by_id($comment->EntityID, 0, "ActivityID, UserID, ModuleID, ModuleEntityID", 2);
                        if (!empty($EntityDetails)) {
                            $EntityID = $EntityDetails['ActivityID'];
                        }
                        if ($EntityDetails['ModuleID'] == 3 && $EntityDetails['ModuleEntityID'] == $user_id) {
                            $IsOwner = 1;
                        }
                        if ($EntityDetails['ModuleID'] == 1) {
                            if ($this->group_model->is_admin($user_id, $EntityDetails['ModuleEntityID'])) {
                                $IsOwner = 1;
                            }
                        }
                        if ($EntityDetails['ModuleID'] == 14) {
                            if ($this->event_model->isEventOwner($EntityDetails['ModuleEntityID'], $user_id)) {
                                $IsOwner = 1;
                            }
                        }
                        if ($EntityDetails['ModuleID'] == 18) {
                            if ($this->page_model->check_page_owner($user_id, $EntityDetails['ModuleEntityID'])) {
                                $IsOwner = 1;
                            }
                        }
                        break;
                }
                
                $cmnt['ProfilePicture'] = $comment->ProfilePicture;
                $cmnt['CommentGUID'] = $comment->PostCommentGUID;
                $cmnt['PostComment'] = nl2br($comment->PostComment);

                $cmnt['PostComment'] = $this->parse_tag($cmnt['PostComment']);
                $cmnt['UserID'] = $comment->UserID;
                $cmnt['Name'] = $comment->FirstName . ' ' . $comment->LastName;
                $cmnt['CreatedDate'] = $comment->CreatedDate;
                $cmnt['UserGUID'] = $comment->UserGUID;
                $cmnt['Occupation'] = $comment->Occupation;
                $cmnt['CanDelete'] = 0;
                $cmnt['ProfileLink'] = get_entity_url($comment->UserID, "User", 1);
                $cmnt['IsLike'] = 0;
                $cmnt['NoOfLikes'] = 0; //$comment->NoOfLikes;
                $cmnt['NoOfDislikes'] = 0; //$comment->NoOfDislikes;
                $cmnt['LikeName'] = $this->getLikeName($comment->PostCommentID, $user_id, 0, array(), 'COMMENT');
                $cmnt['IsMediaExists'] = $comment->IsMediaExists;
                $cmnt['ModuleID'] = 3;
                $cmnt['IsOwner'] = 0;

                $permission = $this->privacy_model->check_privacy($user_id, $comment->UserID, 'view_profile_picture');
                if (!$permission && $comment->EntityOwner == 0) {
                    $cmnt['ProfilePicture'] = '';
                }

                $cmnt['Media'] = array();
                if ($cmnt['IsMediaExists']) {
                    $cmnt['Media'] = $this->get_comment_media($comment->PostCommentID);
                }

                if ($IsOwner ==1 || $user_id == $cmnt['UserID']) {
                    $cmnt['CanDelete'] = 1;
                }
                $parent_comment_id = $comment->ParentCommentID;
                if (!empty($parent_comment_id)) {
                    $parent_comment_owner_id = $this->get_comment_owner($parent_comment_id);
                    if ($IsOwner == 1 || $user_id == $parent_comment_owner_id) {
                        $cmnt['CanDelete'] = 1;
                    }
                }
                if ($user_id == $cmnt['UserID']) {
                    $cmnt['IsOwner'] = 1;
                }
                $cmnt['BestAnswer'] = 0;
                if($comment->StatusID == 22) {
                    $cmnt['BestAnswer'] = 1;
                }
                if ($cmnt['UserID'] == $user_id) {
                    //$cmnt['Name'] = 'You';
                }
                unset($cmnt['UserID']);
                $comments[] = $cmnt;
            }
        }
        return array_reverse($comments);
    }

    /**
     * [getActivityComments Get List of Comments for an Activity]
     * @param  [string] $entity_type  [Entity Type]
     * @param  [int]    $entity_id    [Entity ID]
     * @param  [int]    $page_no      [Page No]
     * @param  [int]    $page_size    [Page Size]
     * @param  [int]    $user_id      [User ID]
     * @param  [int]    $IsOwner     [Is the given user is owner of activity]
     * @param  [int]    $entity_owner [Entity Owner - Used to identify given activity is submitted by as page or as user]
     * @param  [boolean] $Flag       [Flag to return data by pagination otherwise return all data]
     * @param  [array]    $blocked_users    [Array of Blocked Users ID]
     * @return [type]               [List of all Comments]
     */
    public function getActivityComments($entity_type, $entity_id, $page_no, $page_size, $user_id, $IsOwner, $entity_owner = 2, $Flag = TRUE, $blocked_users = array(), $NumRows = FALSE, $comment_id = 0,$postas_module_id=3,$postas_module_entity_id=0,$search_key='',$parent_comment_id=0,$filter="",$include_reply=0) {
        $blocked_users=$this->block_user_list($user_id, 3);
        $this->load->model('group/group_model');
        if(!$postas_module_entity_id) {
            $postas_module_entity_id=$user_id;
        }
        //$comment_id='2117';
        $entity_type = strtoupper($entity_type);
        $module_id = 0;
        $module_entity_id = 0;
        if(!empty($filter) && $filter=='Network') {
            $friends = $this->user_model->gerFriendsFollowersList($user_id,TRUE,0,TRUE);
            $friends[] = 0;
            $friends_list = implode("','", $friends);
            array_unshift($friends, $user_id);
        }

        if ($entity_type == 'ACTIVITY') {
            $this->db->select('ModuleID,ModuleEntityID,Params,ActivityTypeID');
            $this->db->from(ACTIVITY);
            $this->db->where('ActivityID', $entity_id);
            $activity_query = $this->db->get();
            if ($activity_query->num_rows()) {
                $activity_row = $activity_query->row_array();
                if ($activity_row['ActivityTypeID'] == '23' || $activity_row['ActivityTypeID'] == '24') {
                    $params = json_decode($activity_row['Params'], true);
                    $entity_id = get_detail_by_guid($params['MediaGUID'], 21, 'MediaID', 1);
                    $entity_type = 'MEDIA';
                }
                $module_id = $activity_row['ModuleID'];
                $module_entity_id = $activity_row['ModuleEntityID'];
            }
        }

        $this->db->select('PC.*,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,U.UserID,PU.Url as UserProfileUrl,SUM(PC.NoOfLikes+PC.NoOfReplies) AS POPULAR');
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->join(USERS . ' U', 'PC.UserID=U.UserID', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User" ');
        $this->db->where('PC.EntityType', $entity_type);
        $this->db->where_in('PC.StatusID', array(2,22));
        $this->db->where('PC.EntityID', $entity_id);
        $this->db->where('PC.ParentCommentID', $parent_comment_id);
        if($comment_id) {
            $this->db->where('PC.PostCommentID', $comment_id);
        }
        
        if (!empty($blocked_users)) {
            $this->db->where_not_in('PC.UserID', $blocked_users);
        }

        if(!empty($filter)) {
            if($filter=='Recent') {
                $this->db->order_by('PC.ModifiedDate', 'DESC');   
            } elseif($filter=='Popular') {
                $this->db->order_by("POPULAR ASC, PC.StatusID = '22' DESC, PC.PostCommentID DESC");   
            } elseif($filter=='Network') {
                $result = array();
                $this->db->order_by("CASE WHEN PC.UserID IN ('" .$friends_list."') THEN 3 WHEN PC.StatusID=22 THEN 2 ELSE 0 END, PC.PostCommentID DESC");
            }
        } else {   
            if($page_size==1) {
                $this->db->order_by("PC.PostCommentID DESC");   
            } else {
               $this->db->order_by("PC.StatusID = '22' DESC, PC.PostCommentID DESC");    
            }            
        }

        $this->db->group_by('PC.PostCommentID');
        if ($Flag && $comment_id == '') {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        if($search_key != '') {
            $this->db->like('PC.PostComment',$search_key);
        }

        $sql = $this->db->get();
        if ($NumRows) {
            return $sql->num_rows();
        }
        $comments = array();
        if ($sql->num_rows()) {
            //$TimeZone = $this->user_model->get_user_time_zone();
            $is_super_admin = $this->user_model->is_super_admin($user_id);
            foreach ($sql->result() as $comment) {
                $comment_postas_module_id = $comment->PostAsModuleID;
                $comment_postas_module_entity_id = $comment->PostAsModuleEntityID;

                $cmnt['IsExpert'] = 0;
                $cmnt['IsAdmin'] = 0;
                if($module_id==1) {
                    $cmnt['IsExpert'] = $this->group_model->check_is_expert($comment->UserID,$module_entity_id);
                    $cmnt['IsAdmin'] = ($this->group_model->is_admin($comment->UserID,$module_entity_id)) ? 1 : 0 ;
                }
                

                $cmnt['NoOfReplies'] =$comment->NoOfReplies;
                $cmnt['ProfilePicture'] = $comment->ProfilePicture;
                $cmnt['CommentGUID'] = $comment->PostCommentGUID;
                $edit_post_comment = $comment->PostComment;
                $cmnt['PostComment'] = $comment->PostComment;// nl2br($comment->PostComment,FALSE);
                $cmnt['EditPostComment'] = $this->parse_tag_edit($edit_post_comment);
                $cmnt['PostComment'] = $this->parse_tag($cmnt['PostComment']);
                $cmnt['PostComment'] = trim(str_replace('&nbsp;', ' ', $cmnt['PostComment']));
                /*parsed html*/
                if($this->IsApp == 1) {
                    $this->load->model('activity/activity_result_filter_model','result_filter');
                    $cmnt['PostComment'] = $this->result_filter->get_description($cmnt['PostComment']);
                    $cmnt['TagedUser'] = json_decode($comment->TagedUser);
                } else {
                    $cmnt['PostComment'] = html_entity_decode($cmnt['PostComment']);
                }
                /*parsed html*/
                $cmnt['UserID'] = $comment->UserID;
                $cmnt['Name'] = $comment->FirstName . ' ' . $comment->LastName;
                $cmnt['CreatedDate'] = $comment->CreatedDate;
                $cmnt['UserGUID'] = $comment->UserGUID;
                $cmnt['Occupation'] = $comment->Occupation;
                $cmnt['CanDelete'] = 0;
                $cmnt['ProfileLink'] = $comment->UserProfileUrl; //get_entity_url($comment->UserID, "User", 1); 
                
                $cmnt['NoOfLikes'] = $this->get_like_count($comment->PostCommentID, "COMMENT", $blocked_users); //$comment->NoOfLikes;
                //$cmnt['NoOfDislikes'] = $this->get_like_count($comment->PostCommentID, "COMMENT", $blocked_users, 3); //$comment->NoOfLikes;
                $cmnt['LikeName'] = array('ModuleID'=>'','Name'=>'','ProfileURL'=>''); //$this->getLikeName($comment->PostCommentID, $user_id, $entity_owner, $blocked_users, 'COMMENT');
                $cmnt['IsMediaExists'] = $comment->IsMediaExists;
                $cmnt['IsFileExists'] = $comment->IsFileExists;
                $cmnt['ModuleID'] = 3;
                $cmnt['IsHighlight'] = false;
                $cmnt['Replies'] = array();
                if($include_reply==1) {
                    $cmnt['Replies'] = $this->getActivityComments($entity_type, $entity_id, 1, 50, $user_id, $IsOwner, $entity_owner, $Flag, $blocked_users,FALSE,'',$postas_module_id,$postas_module_entity_id,"",$comment->PostCommentID,$filter);
                }
                $cmnt['IsOwner'] = 0;
                if ($comment_id == $comment->PostCommentID) {
                    $cmnt['IsHighlight'] = true;
                }
                if ($comment->PostAsModuleID == 18) {
                    $this->db->select('P.Title,P.ProfilePicture,P.PageGUID,P.PageURL,C.Icon');
                    $this->db->from(PAGES . ' P');
                    $this->db->join(CATEGORYMASTER . ' C', 'C.CategoryID=P.CategoryID', 'left');
                    if ($comment->EntityType == 'RATING') {
                        $this->db->join(RATINGS . ' R', 'R.PostAsModuleEntityID=P.PageID', 'left');
                        $this->db->where('R.RatingID', $comment->EntityID);
                    } else {
                        $this->db->where('P.PageID', $comment->PostAsModuleEntityID);
                    }
                    $pageQry = $this->db->get();
                    if ($pageQry->num_rows()) {
                        $pageRow = $pageQry->row();
                        $cmnt['Name'] = $pageRow->Title;
                        $cmnt['ProfilePicture'] = $pageRow->ProfilePicture;
                        if ($cmnt['ProfilePicture'] == '') {
                            $cmnt['ProfilePicture'] = $pageRow->Icon;
                        }
                        $cmnt['ProfileLink'] = 'page/' . $pageRow->PageURL;
                        $cmnt['ModuleID'] = 18;
                        $cmnt['UserGUID'] = $pageRow->PageGUID;
                    }
                }
                
                if ($comment->PostAsModuleID != 18) {
                    /* $permission = $this->privacy_model->check_privacy($user_id, $comment->UserID, 'view_profile_picture');
                    if (!$permission && $comment->EntityOwner == 0) {
                        $cmnt['ProfilePicture'] = 'user_default.jpg';
                    } */
                }

                $cmnt['Media'] = array();
                if ($cmnt['IsMediaExists']) {
                    $cmnt['Media'] = $this->get_comment_media($comment->PostCommentID, FALSE, TRUE); //get all comment media
                    //reverse array for media details page issue
                    $cmnt['Media'] = (!empty($cmnt['Media'])) ? array_reverse($cmnt['Media']) : $cmnt['Media'];
                }
                $cmnt['Files'] = array();
                if ($cmnt['IsFileExists']) {
                    $cmnt['Files'] = $this->get_activity_files($comment->PostCommentID);
                }

                if ($IsOwner == 1 || $user_id == $cmnt['UserID']) {
                    $cmnt['CanDelete'] = 1;
                }
                if ($user_id == $cmnt['UserID']) {
                    $cmnt['IsOwner'] = 1;
                }
                
                if($cmnt['IsOwner']) {
                    $cmnt['IsLike'] = $this->is_liked($comment->PostCommentID, 'Comment', $user_id, $comment_postas_module_id,$comment_postas_module_entity_id);
                    //$cmnt['IsDislike'] = $this->is_liked($comment->PostCommentID, 'Comment', $user_id, $comment_postas_module_id,$comment_postas_module_entity_id, 3);
                } else if($IsOwner) {
                    $cmnt['IsLike'] = $this->is_liked($comment->PostCommentID, 'Comment', $user_id, $postas_module_id,$postas_module_entity_id);
                    //$cmnt['IsDislike'] = $this->is_liked($comment->PostCommentID, 'Comment', $user_id, $postas_module_id,$postas_module_entity_id, 3);
                } else {
                    $cmnt['IsLike'] = $this->is_liked($comment->PostCommentID, 'Comment', $user_id);
                    //$cmnt['IsDislike'] = $this->is_liked($comment->PostCommentID, 'Comment', $user_id, 3,$user_id, 3);
                }
                
                $parent_comment_id = $comment->ParentCommentID;
                if (!empty($parent_comment_id)) {
                    $parent_comment_owner_id = $this->get_comment_owner($parent_comment_id);
                    if ($IsOwner == 1 || $user_id == $parent_comment_owner_id) {
                        $cmnt['CanDelete'] = 1;
                    }
                }

                $cmnt['BestAnswer'] = 0;
                if($comment->StatusID == 22) {
                    $cmnt['BestAnswer'] = 1;
                }

                $cmnt['IsAnonymous'] = $comment->IsAnonymous;
                if($cmnt['IsAnonymous']==1 && $cmnt['UserID'] != $user_id) {
                    $cmnt['UserName']= "Anonymous User";
                    $cmnt['UserProfileURL'] = "";
                    $cmnt['UserProfilePicture'] = "";
                }

                if($is_super_admin) {
                   $cmnt['CanDelete'] = 1; 
                }

                unset($cmnt['UserID']);
                $comments[] = $cmnt;
            }
        }
        return array_reverse($comments);
    }

    /**
     * [get_comment_owner Used to get comment owner]
     * @param  [type] $comment_id [description]
     * @return [type]             [description]
     */
    function get_comment_owner($comment_id) {
        $this->db->select('PC.UserID');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->where('PC.ParentCommentID', $comment_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->UserID;
        }
        return 0;
    }


    /**
     * [get_comment_reply_count Get total count of replies on a Comment]
     * @param  [string] $parent_comment_id     [Comment ID]
     * @param  [array]  $blocked_users   [Array of Blocked Users ID]
     * @return [int]                    [Total count of all replies]
     */
    public function get_comment_reply_count($parent_comment_id,$blocked_users = array())
    {        
        $this->db->select('COUNT(PC.PostCommentID)as TotalRow');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->join(USERS . ' U', 'PC.UserID=U.UserID AND U.StatusID=2', 'left');
        $this->db->where('PC.ParentCommentID', $parent_comment_id);
        $this->db->where('PC.StatusID', '2');

        if (!empty($blocked_users))
        {
            $this->db->where_not_in('PC.UserID', $blocked_users);
        }

        $sql = $this->db->get();
        $count_data=$sql->row_array();
        return $count_data['TotalRow'];
    }

    /**
     * [get_activity_comment_count Get total count of Comments for an Activity]
     * @param  [string] $entity_type     [Entity Type]
     * @param  [int]    $entity_id       [Entity ID]
     * @param  [array]  $blocked_users   [Array of Blocked Users ID]
     * @return [int]                    [Total count of all Comments]
     */
    public function get_activity_comment_count($entity_type, $entity_id, $blocked_users = array())
    {        
        $this->db->select('COUNT(PC.PostCommentID)as TotalRow');
        $this->db->from(POSTCOMMENTS . ' PC');
        $this->db->join(USERS . ' U', 'PC.UserID=U.UserID AND U.StatusID NOT IN (3,4)', 'left');
        $this->db->where('PC.EntityType', $entity_type);
        $this->db->where_in('PC.StatusID', array(2,22));
        $this->db->where('PC.EntityID', $entity_id);
        $this->db->where('PC.ParentCommentID', 0);

        if (!empty($blocked_users))
        {
            $this->db->where_not_in('PC.UserID', $blocked_users);
        }
        $sql = $this->db->get();
        $count_data=$sql->row_array();
        return $count_data['TotalRow'];
    }

    /**
     * [get_comment_media Get comment media]
     * @param  [int] $comment_id [Comment ID]
     * @return [array]          [Comment media details]
     */
    public function get_comment_media($comment_id, $live_feed = false, $is_all_media = false) {
        $this->db->select('Media.MediaGUID,Media.Resolution,Media.OriginalName,IFNULL(Caption,"") AS Caption,Media.ConversionStatus, IFNULL(ImageName,"") AS ImageName,Media.Size,MediaExtension.Name as MediaExtension,MediaType.Name as MediaType');
        $this->db->select('MS.MediaSectionAlias as MediaFolder');
        $this->db->from(MEDIA . ' AS Media');
        $this->db->join(MEDIAEXTENSIONS . " AS MediaExtension", ' MediaExtension.MediaExtensionID = Media.MediaExtensionID', 'left');
        $this->db->join(MEDIATYPES . " AS MediaType", ' MediaType.MediaTypeID = MediaExtension.MediaTypeID');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=Media.MediaSectionID', 'LEFT');
        $this->db->where('MediaType.MediaTypeID != ', '4'); //documents
        $this->db->where('Media.MediaSectionReferenceID', $comment_id);
        $this->db->where('Media.MediaSectionID', '6');
        $this->db->where('Media.StatusID', '2');

        $query = $this->db->get();
        if ($query->num_rows()) {
            if ($is_all_media) {
                return $query->result_array();
            } else {
                $row = $query->row();
                if ($live_feed) {
                    $media = array(array('ImageName' => $row->ImageName, 'Resolution' => $row->Resolution, 'Caption' => $row->Caption, 'ConversionStatus' => $row->ConversionStatus, 'MediaGUID' => $row->MediaGUID, 'MediaFolder' => $row->MediaFolder));
                    return array(array('AlbumProfileURL' => '', 'AlbumName' => 'Comments Media', 'AlbumGUID' => '', 'Media' => $media, 'AlbumType' => '', 'TotalMedia' => 1, 'ModuleID' => 0, 'ModuleEntityID' => 0));
                } else {
                    return array('ImageName' => $row->ImageName, 'Resolution' => $row->Resolution, 'Caption' => $row->Caption, 'MediaGUID' => $row->MediaGUID, 'MediaFolder' => $row->MediaFolder);
                }
            }
        }
        return array();
    }

    /**
     * [block_user_list Get list of blocked users for wall]
     * @param  [int]  $user_id   [User ID]
     * @param  [int]  $module_id [Module ID]
     * @return [array]          [block user list]
     */
    public function block_user_list($user_id, $module_id = 3) {
        $arr = array();
        return $arr;
        
        if($module_id==3)
        {
            if (CACHE_ENABLE) {
                $arr = $this->cache->get('block_users_'.$user_id);
            }
        }
        else{
            return $arr;
        }
        
        if(empty($arr))
        {
            if ($module_id == 3 && !empty($user_id))
            {
                $this->db->where('(UserID=' . $user_id . ' OR EntityID=' . $user_id . ')', NULL, FALSE);
            } else
            {
                $this->db->where('ModuleEntityID', $user_id);
            }
            $this->db->where('ModuleID', $module_id);
            $this->db->where('StatusID', '2');
            $query = $this->db->get(BLOCKUSER);
            //echo $this->db->last_query();
            if ($query->num_rows())
            {
                foreach ($query->result() as $user)
                {
                    if ($user_id == $user->EntityID && $module_id == 3)
                    {
                        $arr[] = $user->UserID;
                    } else
                    {
                        $arr[] = $user->EntityID;
                    }
                }
            }
            $arr[]=0;
            if (CACHE_ENABLE && $module_id==3) {
                $this->cache->save('block_users_'.$user_id, $arr,CACHE_EXPIRATION);
            }
        }
        return $arr;
    }
    /**
     * [block_user_list Get list of blocked users for wall]
     * @param  [int]  $user_id   [User ID]
     * @param  [int]  $module_id [Module ID]
     * @return [array]          [block user list]
     */
    public function block_user_list_group($group_id)
    {
        $arr = array();
        
            $this->db->select('GROUP_CONCAT(EntityID) as EntityIDs');
            $this->db->where('ModuleEntityID', $group_id);
            $this->db->where('ModuleID', 1);
            $this->db->where('StatusID', '2');
            $this->db->limit('1');
            $query = $this->db->get(BLOCKUSER);
            //echo $this->db->last_query();
            if ($query->num_rows())
            {
                $result=$query->row_array();
                if(!empty($result['EntityIDs']))
                {
                   $arr= explode(',', $result['EntityIDs']);
                }
                
            }
        return $arr;
    }    

    public function get_blocked_user_list($user_id, $search_key, $PageNo, $PageSize) {
        /* Select Interest - starts */
        $Interest = $this->db->query("SELECT GROUP_CONCAT(CM.CategoryID) AS CategoryIDs FROM `EntityCategory` EC, `CategoryMaster` CM
         WHERE (EC.CategoryID=CM.CategoryID AND EC.ModuleID='3' AND EC.ModuleEntityID='".$user_id."')")->row_array();
        $Interest = $Interest['CategoryIDs'];
        if(empty($Interest))
        {
            $Interest =0;
        }
        $this->db->select('U.UserID, U.ProfilePicture, U.UserGUID,CONCAT(U.FirstName," ",U.LastName) as Name',FALSE);
        $this->db->from(BLOCKUSER.' BU');
        $this->db->join(USERS.' U','BU.EntityID=U.UserID AND BU.ModuleID=3','left');
        $this->db->where_not_in('U.StatusID',array(3,4));
        if(!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%'.$search_key.'%" OR U.LastName LIKE "%'.$search_key.'%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%'.$search_key.'%")',NULL,FALSE);
        }
        $this->db->where('BU.UserID',$user_id);

        $q=" (SELECT GROUP_CONCAT(CM.Name) FROM EntityCategory EC, CategoryMaster CM WHERE EC.ModuleEntityID=U.UserID AND EC.ModuleID='3' AND EC.CategoryID=CM.CategoryID
        AND CM.CategoryID IN ( $Interest ) ) AS Interests";

        $this->db->select($q, false);   

        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();

        $returnArr['Data'] = array();
        $returnArr['total_records'] = $temp_q->num_rows();

        $this->db->limit($PageSize, $this->get_pagination_offset($PageNo, $PageSize));
        $query = $this->db->get();

        //echo $this->db->last_query();
        if($query->num_rows()>0) {
            foreach ($query->result_array() as $key => $UserList) {
                $this->load->model('users/friend_model');
                $UserList['MutualFriends'] = $this->friend_model->getMutualFriend(get_detail_by_guid($UserList['UserGUID'],3),$user_id,1);
                $UserList['Location']      = $this->user_model->get_user_location($UserList['UserID']);

                /*privacy check - starts added by gautam*/
                if ($user_id != $UserList['UserID']) {
                    $users_relation = get_user_relation($user_id, $UserList['UserID']);
                    $privacy_details = $this->privacy_model->details($UserList['UserID']);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if(isset($privacy_label[$privacy])) {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $UserList['ProfilePicture'] = '';
                                }
                            }
                        }
                    }
                }
                /*privacy check - ends added by gautam*/
                unset($UserList['UserID']);
                $returnArr['Data'][] = $UserList;
            }            
        }
        return $returnArr;
    }

    /**
     * [is_activity_user Check if activity should have to show on user wall or not]
     * @param  [int]  $user_id     [User ID]
     * @param  [int]  $activity_id [Activity ID]
     * @return boolean            [description]
     */
    public function is_activity_user($user_id, $activity_id)
    {
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('StatusID', '2');
        $row = $this->db->get(ACTIVITY);
        if ($row->num_rows())
        {
            return 1;
        } else
        {
            return 0;
        }
    }

    /**
     * [get_albums( returns associative array of albums and its media attached with activity]
     * @param  [type]  $entity_id   [Entity ID]
     * @param  [type]  $user_id     [User ID]
     * @param  string  $album_guid  [Album GUID]
     * @param  string  $entity_type [Entity Type]
     * @param  integer $limit       [Limit]
     * @return [array]               [associative array of albums]
     */
    public function get_albums($entity_id, $user_id, $album_guid = '', $entity_type = 'Activity', $page_size = 0, $search_key='') {
        $album_data=array();
        if ($this->IsApp == 1) {
            $album_data = $this->get_activity_media($entity_id, $user_id, $album_guid, $entity_type, $page_no = 1, $page_size);
            return $album_data;
        }
        
        if(CACHE_ENABLE && $entity_id && $entity_type== 'Activity' ) {
            $activity_cache=$this->cache->get('activity_'.$entity_id);
            if(!empty($activity_cache)) {   
                if(!empty($activity_cache['m']))
                $album_data= $activity_cache['m'];
            }
        }
        
        $this->load->model('group/group_model');
        
        if(empty($album_data)) {
            $this->db->select('M.MediaID, M.Resolution, A.AlbumID,M.MediaGUID, M.NoOfComments, M.NoOfLikes, M.NoOfShares, M.ImageName, M.Caption, M.AlbumID, A.AlbumName, A.AlbumGUID, A.AlbumType, A.ModuleID, A.ModuleEntityID, MT.Name as MediaType');
            $this->db->select('IFNULL(M.ConversionStatus,"") AS ConversionStatus,IFNULL(M.VideoLength,"") AS VideoLength', FALSE);
            $this->db->select('P.Url as AlbumProfileURL', false);
            $this->db->select('MS.MediaSectionAlias as MediaFolder'); /*added by gautam*/
            $this->db->select('G.GroupName, G.GroupGUID');
            $this->db->from(MEDIA . ' M');
            $this->db->join(ALBUMS . ' A', 'M.AlbumID=A.AlbumID AND A.AlbumType != "DOCUMENT" ');
            $this->db->join(PROFILEURL . ' P', 'P.EntityID=A.UserID AND P.EntityType="User" ');
            $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
            $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
            $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');/*added by gautam*/
            $this->db->join(GROUPS . ' G', 'A.ModuleID = 1 AND A.ModuleEntityID = G.GroupID', 'LEFT');
            
            if (!$album_guid) {
                if ($entity_type == 'Activity') {
                    $this->db->join(ACTIVITY . ' ACT', 'M.MediaSectionReferenceID=ACT.ActivityID AND M.MediaSectionID=3', 'left');
                    $this->db->where('ACT.IsMediaExist', '1');
                    $this->db->where('ACT.ActivityID', $entity_id);
                    //$this->db->where('A.UserID',$user_id);
                } else if ($entity_type == 'Ratings') {
                    $this->db->join(RATINGS . ' RT', 'M.MediaSectionReferenceID=RT.RatingID AND M.MediaSectionID=8', 'left');
                    $this->db->where('RT.UserID', $user_id);
                    $this->db->where('RT.RatingID', $entity_id);
                } else {
                    $this->db->where('M.MediaID', $entity_id);
                }
            } else {
                $this->db->where('A.AlbumGUID', $album_guid);
            }
            $this->db->where_not_in('M.StatusID', array(3,21));
            $this->db->order_by('M.CreatedDate', 'DESC');

            $tempdb = clone $this->db;
            //$temp_q = $tempdb->get();
            $total_media = $tempdb->count_all_results();

            if($page_size)
            {
                $this->db->limit($page_size);
            }
            
            $query = $this->db->get();
            if ($query->num_rows()) {
                $album_name = '';
                $album_guid = '';
                $album_type = '';
                $has_video = 0;
                $media = array();
                foreach ($query->result() as $row) {
                    $album_name = $row->AlbumName;
                    $album_guid = $row->AlbumGUID;
                    $album_type = $row->AlbumType;
                    $module_id = $row->ModuleID;
                    $album_id = $row->AlbumID;
                    $module_entity_id = $row->ModuleEntityID;
                    $album_profile_url = $row->AlbumProfileURL . '/media';

                    if($row->MediaType == 'Video')
                    {
                        $has_video = 1;
                    }

                    /*if($this->IsApp == 1){*/ /*For Mobile */
                    /*added by gautam*/
                    //$LikeList = $this->getLikeDetails($row->MediaGUID, 'MEDIA', array(),0, 1,FALSE,$user_id);
                    $media[] = array(
                        'MediaFolder'=>$row->MediaFolder,/*added by gautam*/
                        'MediaGUID' => $row->MediaGUID,
                        'ImageName' => $row->ImageName,
                        'Caption' => $row->Caption,
                        'MediaType' => $row->MediaType,
                        'VideoLength' => $row->VideoLength,
                        'ConversionStatus'=>$row->ConversionStatus,
                        'Resolution'=>$row->Resolution,
                        /* // added by gautam
                            'NoOfComments'=>$row->NoOfComments,
                            'NoOfLikes'=>$row->NoOfLikes,
                            'NoOfShares'=>$row->NoOfShares,
                            'LikeList'=>$LikeList,      
                            'IsLike'=>$this->is_liked($row->MediaID, 'MEDIA', $this->UserID)
                        */
                        );

                    switch ($module_id) {
                        case '1':
                            $group_url_details = $this->group_model->get_group_details_by_id($module_entity_id, '', array(
                                'GroupName' => $row->GroupName,
                                'GroupGUID' => $row->GroupGUID,
                            ));
                            $album_profile_url = $this->group_model->get_group_url($module_entity_id, $group_url_details['GroupNameTitle'], false, 'media');             
                            
                            
                            break;
                        case '14':
                            $entity_details = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title", 2);
                            
                            $module_entity_guid = $entity_details['EventGUID'];
                            $title = $entity_details['Title'];                            
                            $album_profile_url = $this->event_model->getViewEventUrl($module_entity_guid, $title, false, 'media');                            
                            break;
                        case '18':
                            $page_url = get_detail_by_id($module_entity_id, $module_id, "PageURL");
                            $album_profile_url = "page/" . $page_url . "/media";
                            break;
                        default:
                            break;
                    }
                }
                $album_data[] = array('AlbumID'=>$album_id,'AlbumProfileURL' => $album_profile_url, 'AlbumName' => $album_name, 'AlbumGUID' => $album_guid, 'Media' => $media, 'AlbumType' => $album_type, 'TotalMedia' => $total_media, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id,'HasVideo'=>$has_video);
            }
        }
        //print_r($album_data);die;
        return $album_data;
    }

    public function get_activity_media($entity_id, $user_id, $album_guid = '', $entity_type = 'Activity', $page_no = 1, $page_size = 4) {
        $album_data=array();
       
        $this->load->model('group/group_model');
        
        $this->db->select('M.MediaID, M.Resolution, A.AlbumID,M.MediaGUID, M.NoOfComments, M.NoOfLikes, M.NoOfShares, M.ImageName, M.Caption, M.AlbumID, A.AlbumName, A.AlbumGUID, A.AlbumType, A.ModuleID, A.ModuleEntityID, MT.Name as MediaType');
        $this->db->select('IFNULL(M.ConversionStatus,"") AS ConversionStatus,IFNULL(M.VideoLength,"") AS VideoLength', FALSE);
        $this->db->select('P.Url as AlbumProfileURL', false);
        $this->db->select('MS.MediaSectionAlias as MediaFolder'); /*added by gautam*/
        $this->db->select('G.GroupName, G.GroupGUID');
        $this->db->from(MEDIA . ' M');
        $this->db->join(ALBUMS . ' A', 'M.AlbumID=A.AlbumID AND A.AlbumType != "DOCUMENT" ');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=A.UserID AND P.EntityType="User" ');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'ME.MediaExtensionID=M.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'MT.MediaTypeID=ME.MediaTypeID', 'LEFT');
        $this->db->join(MEDIASECTIONS . ' MS', 'MS.MediaSectionID=M.MediaSectionID', 'LEFT');/*added by gautam*/
        $this->db->join(GROUPS . ' G', 'A.ModuleID = 1 AND A.ModuleEntityID = G.GroupID', 'LEFT');
        if (!$album_guid) {
            if ($entity_type == 'Activity') {
                $this->db->join(ACTIVITY . ' ACT', 'M.MediaSectionReferenceID=ACT.ActivityID AND M.MediaSectionID=3', 'left');
                $this->db->where('ACT.IsMediaExist', '1');
                $this->db->where('ACT.ActivityID', $entity_id);
            } else if ($entity_type == 'Ratings') {
                $this->db->join(RATINGS . ' RT', 'M.MediaSectionReferenceID=RT.RatingID AND M.MediaSectionID=8', 'left');
                $this->db->where('RT.UserID', $user_id);
                $this->db->where('RT.RatingID', $entity_id);
            } else {
                $this->db->where('M.MediaID', $entity_id);
            }
        } else {
            $this->db->where('A.AlbumGUID', $album_guid);
        }
        $this->db->where_not_in('M.StatusID', array(3,21));
        $this->db->order_by('M.CreatedDate', 'DESC');

        $total_media = 0;
        if($page_no <= 1) {
            $tempdb = clone $this->db;
            $total_media = $tempdb->count_all_results();
        }
        
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        
        $query = $this->db->get();        
        if ($query->num_rows()) {
            $album_name = '';
            $album_guid = '';
            $album_type = '';
            $media = array();
            foreach ($query->result() as $row) {
                $album_name = $row->AlbumName;
                $album_guid = $row->AlbumGUID;
                $album_type = $row->AlbumType;
                $module_id = $row->ModuleID;
                $album_id = $row->AlbumID;
                $module_entity_id = $row->ModuleEntityID;
                $album_profile_url = $row->AlbumProfileURL . '/media';
                $is_like = $this->is_liked($row->MediaID, 'MEDIA', $this->UserID);

                $media[] = array(
                    'MediaFolder'=>$row->MediaFolder,/*added by gautam*/
                    'MediaGUID' => $row->MediaGUID,
                    'ImageName' => $row->ImageName,
                    'Caption' => $row->Caption,
                    'MediaType' => $row->MediaType,
                    'VideoLength' => $row->VideoLength,
                    'ConversionStatus'=>$row->ConversionStatus,
                    'Resolution'=>$row->Resolution,
                    'NoOfComments'=>$row->NoOfComments,
                    'NoOfLikes'=>$row->NoOfLikes,
                    'IsLike'=>$is_like
                    );

                switch ($module_id) {
                    case '1':                        
                        $group_url_details = $this->group_model->get_group_details_by_id($module_entity_id, '', array(
                            'GroupName' => $row->GroupName,
                            'GroupGUID' => $row->GroupGUID,
                        ));
                        $album_profile_url = $this->group_model->get_group_url($module_entity_id, $group_url_details['GroupNameTitle'], false, 'media');    
                        
                        break;
                    case '14':
                        $entity_details = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title", 2);
                        $module_entity_guid = $entity_details['EventGUID'];
                        $title = $entity_details['Title'];                            
                        $album_profile_url = $this->event_model->getViewEventUrl($module_entity_guid, $title, false, 'media');                        
                        break;
                    case '18':
                        $page_url = get_detail_by_id($module_entity_id, $module_id, "PageURL");
                        $album_profile_url = "page/" . $page_url . "/media";
                        break;
                    default:
                        break;
                }
            }
            $album_data[] = array('AlbumID'=>$album_id,'AlbumProfileURL' => $album_profile_url, 'AlbumName' => $album_name, 'AlbumGUID' => $album_guid, 'Media' => $media, 'AlbumType' => $album_type, 'TotalMedia' => $total_media, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id);
        }
        return $album_data;
    }

    /**
     * Function Name: removeChildActivity
     * @param ActivityID, ActivityTypeIDs
     * Description: Remove Child Activity
     */
    public function removeChildActivity($activity_id, $activity_type_ids)
    {
        $this->db->where('ParentActivityID', $activity_id);
        $this->db->where_in('ActivityTypeID', $activity_type_ids);
        $this->db->update(ACTIVITY, array('StatusID' => '3'));


        if (!$this->settings_model->isDisabled(28))
        {
            $this->db->select('ActivityID');
            $this->db->where('ParentActivityID', $activity_id);
            $query = $this->db->get(ACTIVITY);
            if ($query->num_rows())
            {
                $update = array();
                foreach ($query->result() as $result)
                {
                    $this->db->where('ActivityID', $result->ActivityID);
                    $this->db->delete(REMINDER);
                }
            }
        }
    }

    public function restoreActivity($activity_id, $user_id)
    {
        $result = $this->db->get_where(ACTIVITY, array('ActivityID' => $activity_id));
        if ($result->num_rows())
        {
            $row = $result->row();
            if (checkPermission($user_id, $row->ModuleID, $row->ModuleEntityID, 'Sticky', 3, $user_id) || checkPermission($user_id, 3, $row->UserID))
            {
                $this->db->where('ActivityID', $activity_id);
                $this->db->update(ACTIVITY, array('StatusID' => '2'));

                $users = array();

                $this->db->select('ModuleID,ModuleEntityID');
                $this->db->from(MENTION);
                $this->db->where('ActivityID', $activity_id);
                $query = $this->db->get();
                $mentions = array();
                if ($query->num_rows())
                {
                    foreach ($query->result_array() as $r)
                    {
                        if ($r['ModuleID'] == 3)
                        {
                            $mentions[] = $r['ModuleEntityID'];
                        } elseif ($r['ModuleID'] == 18)
                        {
                            $this->load->model('pages/page_model');
                            $mention_admins = $this->page_model->get_all_admins($row->ModuleEntityID);
                            if ($mention_admins)
                            {
                                foreach ($mention_admins as $ma)
                                {
                                    $mentions[] = $ma;
                                }
                            }
                        }
                    }
                    $mentions = array_unique($mentions);
                }

                $users = array();

                if ($row->ActivityTypeID == '7')
                {
                    $type = 'Group';
                    $this->load->model('group/group_model');
                    $users = $this->group_model->get_all_group_admins($row->ModuleEntityID);
                }
                if ($row->ActivityTypeID == '8')
                {
                    $type = 'User';
                    $users[] = $row->ModuleEntityID;
                }
                if ($row->ActivityTypeID == '11')
                {
                    $type = 'Event';
                    $this->load->model('events/event_model');
                    $users = $this->event_model->getEventAdmins($row->ModuleEntityID);
                    $users[] = $this->event_model->getEventOwner($row->ModuleEntityID);
                }
                if ($row->ActivityTypeID == '12')
                {
                    $type = 'Page';
                    $this->load->model('pages/page_model');
                    $users = $this->page_model->get_all_admins($row->ModuleEntityID);
                }

                if ($users)
                {
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $row->ModuleEntityID;
                    $parameters[1]['Type'] = $type;

                    $this->notification_model->add_notification(85, $user_id, $users, $activity_id, $parameters);
                }

                if ($mentions)
                {
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $this->notification_model->add_notification(84, $user_id, $mentions, $activity_id, $parameters);
                }
            }
        }
    }

    /**
     * Function Name: removeActivity
     * @param ActivityID
     * Description: Remove Activity
     */
    public function removeActivity($activity_id, $user_id, $explicit_status_id = 0)
    {
        $result = $this->db->get_where(ACTIVITY, array('ActivityID' => $activity_id));
        if ($result->num_rows())
        {
            $row = $result->row();

            $this->load->model('users/user_model');
            $is_super_admin=$this->user_model->is_super_admin($user_id, 1);

            if (checkPermission($user_id, $row->ModuleID, $row->ModuleEntityID, 'Sticky', 3, $user_id) || checkPermission($user_id, 3, $row->UserID) || $is_super_admin)
            {

                if ($row->ActivityTypeID == '9' || $row->ActivityTypeID == '10' || $row->ActivityTypeID == '14' || $row->ActivityTypeID == '15')
                {
                    if ($row->ActivityTypeID == '9' || $row->ActivityTypeID == '10')
                    {
                        $this->updateShareCount('ACTIVITY', $row->ParentActivityID, 0);
                    } else
                    {
                        $this->updateShareCount('MEDIA', $row->ParentActivityID, 0);
                    }
                }

                $this->removeChildActivity($activity_id, array('9', '10'));
                
                
                
                if ($row->StatusID == '2')
                {
                    $status_id = '19';
                    if (CACHE_ENABLE) 
                    {
                        $this->cache->delete('activity_'.$activity_id);
                    }
                    if($row->ActivityTypeID == '26')
                    {
                        $discussion_count = $this->forum_model->get_discussion_count($row->ModuleEntityID);
                        $this->db->set('NoOfDiscussions',$discussion_count,false);
                        $this->db->where('ForumCategoryID',$row->ModuleEntityID);
                        $this->db->update(FORUMCATEGORY);   
                    }
                } else
                {
                    $status_id = '3';
                }
                
                if($explicit_status_id) {
                    $status_id = $explicit_status_id;
                }
                
                $this->db->where('ActivityID', $activity_id);
                $this->db->update(ACTIVITY, array('StatusID' => $status_id));
                if($row->ModuleID==34)
                {
                    $this->update_forum_discussions_count($row->ModuleEntityID, $row->ModuleID, '-1');
                }

                if (!$this->settings_model->isDisabled(28))
                {
                    if ($status_id == '3')
                    {
                        $this->db->where('ActivityID', $activity_id);
                        $this->db->delete(REMINDER);
                    }
                }

                if($status_id == '3')
                {
                    $this->remove_activity_album_media($activity_id);

                    $query = $this->db->get_where(POSTCOMMENTS, array('EntityType' => 'Activity', 'EntityID' => $activity_id));
                    if ($query->num_rows())
                    {
                        $update = array();
                        foreach ($query->result() as $result)
                        {
                            $this->db->where('MediaSectionReferenceID', $result->PostCommentID);
                            $this->db->where('MediaSectionID', '6');
                            $this->db->update(MEDIA, array('StatusID' => '3'));
                            $this->db->where('EntityID', $result->PostCommentID);
                            $this->db->where('EntityType', 'Activity');
                            $this->db->update(POSTCOMMENTS, array('StatusID' => '3'));
                        }
                    }
                } else
                {
                    $this->db->set('DeletedBy', $user_id);
                    $this->db->where('ActivityID', $activity_id);
                    $this->db->update(ACTIVITY);
                }

                $this->db->set('StatusID','3');
                $this->db->where('MediaSectionID','3');
                $this->db->where('MediaSectionReferenceID',$activity_id);
                $this->db->update(MEDIA);
            } else
            {
                $Return['ResponseCode'] = 412;
                $Return['Message'] = lang('permission_denied');
                return $Return;
            }
            if(CACHE_ENABLE)
            {
                $this->cache->delete('article_widgets_'.$user_id);
            }
            $Return['ResponseCode'] = 200;
            $Return['Message'] = lang('success');
            return $Return;
        }
    }

    /* Function to remove, deleted media's count from album table */
    function remove_activity_album_media($activity_id = '')
    {
        if (!empty($activity_id))
        {
            $this->db->select('AlbumID,COUNT(MediaID) AS MediaCount');
            $this->db->where('MediaSectionReferenceID', $activity_id);
            $this->db->where('MediaSectionID', '3');
            $this->db->group_by('AlbumID');
            $res = $this->db->get(MEDIA)->row_array();
            if (!empty($res))
            {
                $media_count = $res['MediaCount'];
                $album_id = $res['AlbumID'];
                $media_id = 0;
                $set_field = "MediaCount";

                //Update status is deleted for al media of given activity ID
                $this->db->where('MediaSectionReferenceID', $activity_id);
                $this->db->where('MediaSectionID', '3');
                //$this->db->or_where('MediaSectionID','4');
                $this->db->update(MEDIA, array('StatusID' => '3'));


                //Cover Media - Get recent media ID for the $album_id of given activity ID
                $this->db->select('MediaID');
                $this->db->from(MEDIA);
                $this->db->where('AlbumID', $album_id);
                $this->db->where('StatusID', 2);
                $this->db->order_by("MediaID", "DESC");
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows())
                {
                    $row = $query->row_array();
                    $media_id = isset($row['MediaID']) ? $row['MediaID'] : 0;
                }

                //Update media count and cover media for $album_id of given activity ID
                $this->db->set("MediaID", $media_id, FALSE);
                $this->db->set($set_field, "$set_field-$media_count", FALSE);
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->where('AlbumID', $album_id);
                $this->db->update(ALBUMS);
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * [checkLikeStatus Used to check like status of an activity]
     * @param  [string] $activity_guid [Activity GUID]
     * @param  [string] $type         [Type may be User or Page]
     * @param  [int] $user_id          [User ID]
     * @return [array]                [Return like details]
     */
    public function checkLikeStatus($activity_guid, $user_id, $postas_module_id, $postas_module_entity_id)
    {
        $activity_row = get_detail_by_guid($activity_guid, '0', 'Params,ActivityTypeID,ActivityID', 2);
        if (!empty($activity_row))
        {
            $activity_id        = $activity_row['ActivityID'];
            $activity_type_id   = $activity_row['ActivityTypeID'];

            //check is liked or not
            $like_entity_type = 'ACTIVITY';
            $like_entity_id = $activity_id;
            if (in_array($activity_type_id, array(23,24)))
            {
                $params = json_decode($res['Params']);
                if ($params->MediaGUID)
                {
                    $media_id = get_detail_by_guid($params->MediaGUID, 21);
                    if ($media_id)
                    {
                        $like_entity_type = 'MEDIA';
                        $like_entity_id = $media_id;
                    }
                }
            }

            $return['IsLike'] = $this->is_liked($like_entity_id,$like_entity_type,$user_id,$postas_module_id,$postas_module_entity_id); 
            $return['IsDislike'] = $this->is_liked($like_entity_id,$like_entity_type,$user_id,$postas_module_id,$postas_module_entity_id, 3); 
            $blocked_users = $this->block_user_list($user_id);
            $return['Comments'] = $this->getActivityComments('Activity', $activity_id, '1', COMMENTPAGESIZE, $user_id, $user_id, 0, TRUE, $blocked_users,FALSE,'',$postas_module_id,$postas_module_entity_id);           
        }
        return $return;
    }

    /**
     * Function Name: checkLike
     * @param ActivityGuID
     * @param ActivityType
     * @param UserID
     * Description: Check If User Like Activity or Not
     */
    function checkLike($entity_guid, $entity_type, $user_id, $entity_owner = 2)
    {
        $entity_type = strtoupper($entity_type);
        $entity_id = '';
        if ($entity_type == 'ACTIVITY')
        {

            $activity_row = get_detail_by_guid($entity_guid, '0', 'Params,ActivityTypeID,ActivityID', 2);
            if (!empty($activity_row))
            {
                if ($activity_row['ActivityTypeID'] == '23' || $activity_row['ActivityTypeID'] == '24')
                {
                    $params = json_decode($activity_row['Params'], true);
                    $entity_guid = $params['MediaGUID'];
                    $entity_type = 'MEDIA';
                }
                $entity_id = $activity_row['ActivityID'];
            }            
        }

        if ($entity_type == 'ACTIVITY')
        {
            //$entity_id = get_detail_by_guid($entity_guid);
        } 
        else if ($entity_type == 'COMMENT')
        {
            $entity_id = get_detail_by_guid($entity_guid, 20, "PostCommentID", 1);
        } 
        else if ($entity_type == 'MEDIA')
        {
            $entity_id = get_detail_by_guid($entity_guid, 21, "MediaID", 1);
        } 
        else if ($entity_type == 'BLOG')
        {
            $entity_id = get_detail_by_guid($entity_guid, 24, "BlogID", 1);
        }
        $this->db->select('EntityID');
        $this->db->where('EntityID', $entity_id);
        if ($entity_owner != 1)
        {
            $this->db->where('UserID', $user_id);
        } else
        {
            $this->db->where('EntityOwner', $entity_owner);
        }
        $this->db->where('EntityType', $entity_type);
        
        $this->db->where('StatusID', '2');
        $this->db->limit(1);
        $ActivityLike = $this->db->get(POSTLIKE);
        return $ActivityLike->num_rows();
    }
    /**
     * Function Name: is_liked
     * @param ActivityGuID
     * @param ActivityType
     * @param UserID
     * Description: Check If User Like Activity or Not
     */ 

    function is_liked($entity_id, $entity_type, $user_id, $postas_module_id=3, $postas_module_entity_id=0,$status = 2)
    {
        if(!$postas_module_entity_id){
            $postas_module_entity_id=$user_id;
        }

        $entity_type = strtoupper($entity_type);                
        $this->db->select('EntityID');
        $this->db->where('EntityID', $entity_id);
        
        if ($entity_type == 'ACTIVITY' || $entity_type == 'COMMENT')
        {
             $this->db->where('UserID', $user_id);
             $this->db->where('PostAsModuleID', $postas_module_id);
             $this->db->where('PostAsModuleEntityID', $postas_module_entity_id);
         }
        else
        {
             $this->db->where('UserID', $user_id);
        }
        $this->db->where('EntityType', $entity_type);
        $this->db->where('StatusID', $status);
        $this->db->limit(1);
        $ActivityLike = $this->db->get(POSTLIKE);
        //echo $this->db->last_query(); die;
        return $ActivityLike->num_rows();
    }

    /**
     * Function Name: addActivity
     * @param Data
     * @param ActivityType
     * @param Users
     * @param EntityType
     * @param WallType
     * @param EntityID
     * @param VisibleFor
     * Description: Add new activity
     */
    public function addActivity($module_id, $module_entity_id, $activity_type_id, $user_id, $activity_id = 0, $post_content = '', $CommentSettings = '', $VisibleFor = '', $Params = array(), $is_media_exist = '0', $module_entity_owner = 0, $post_as_module_id = 3, $post_as_module_entity_id = 0, $is_file_exists = 0,$post_title='',$post_type=1,$activity_guid='')
    {
        $this->load->model('subscribe_model');
        
        if(!$post_as_module_entity_id){
            $post_as_module_id=3;
            $post_as_module_entity_id=$user_id;
        }
        //var_dump($is_media_exist);
        if (empty($is_media_exist))
        {
            $is_media_exist = '0';
        }
        if (empty($is_file_exists))
        {
            $is_file_exists = '0';
        }

        if($activity_type_id == 38 || $activity_type_id == 39)
        {
            $Data['PostType'] = 9;
            if($activity_type_id == 39)
            {
                $Data['IsWinnerAnnounced'] = 1;
            }
        }else{
            $Data['PostType']=($post_type) ? $post_type : 1 ;
        }

        $Data['ActivityGuID'] = ($activity_guid) ? $activity_guid : get_guid() ;
        $Data['ActivityTypeID'] = $activity_type_id;
        $Data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $Data['LastActionDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $Data['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $Data['PromotedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $Data['ModuleID'] = $module_id;
        $Data['ModuleEntityID'] = $module_entity_id;
        $Data['UserID'] = $user_id;
        $Data['ParentActivityID'] = $activity_id;
        $Data['PostContent'] = $post_content;
        $Data['IsMediaExist'] = $is_media_exist;
        $Data['IsFileExists'] = $is_file_exists;
        $Data['ModuleEntityOwner'] = $module_entity_owner;
        $Data['PostAsModuleEntityID'] = $post_as_module_entity_id;
        $Data['PostAsModuleID'] = $post_as_module_id;
        $Data['PostTitle'] = $post_title;
        
        if ($CommentSettings != '')
        {
            $Data['IsCommentable'] = $CommentSettings;
        } else
        {
            $Data['IsCommentable'] = 0;
        }
        if ($VisibleFor)
        {
            $Data['Privacy'] = $VisibleFor;
        }

        if ($Params)
        {
            $Data['Params'] = json_encode($Params);
        }
        
        $this->load->model(array('log/user_activity_log_score_model'));
        $Data['Verified'] = $this->user_activity_log_score_model->get_user_activity_verify_status($user_id);

        if($activity_guid == '')
        {
            $this->db->insert(ACTIVITY, $Data);
            $activity_id = $this->db->insert_id();
        }
        else
        {
            $this->db->where('ActivityGUID',$activity_guid);
            $this->db->update(ACTIVITY, $Data);
            $activity_id = get_detail_by_guid($activity_guid,19);
        }
        if($activity_guid == '')
        {
            //echo $this->db->last_query();
            $this->subscribe_model->addUpdate(array(array('ModuleID' => 3, 'ModuleEntityID' => $user_id)), $activity_id);
            
            // Save log on share/auto generated activities.
            $log_activity_types = array(
                9, 10, 14, 15, //Share activities
                2, 3, 4, 5, 6, 7, // Auto Generated Activities
            );
            
            
            if(in_array($activity_type_id, $log_activity_types)) {
            }
            
            $score = $this->user_activity_log_score_model->get_score_for_activity($activity_type_id, $module_id, 0, $user_id);
            $data = array(
                'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'UserID' => $user_id, 'ActivityTypeID' => $activity_type_id, 'ActivityID' => $activity_id,
                'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID' => $post_as_module_id, 'PostAsModuleEntityID' => $post_as_module_entity_id,
                'EntityID' => $activity_id, 'Score' => $score,
            );
            $this->user_activity_log_score_model->add_activity_log($data);
            
            
            // To give score to both the users on friendship
            if($activity_type_id == 2) {
                $data = array(
                    'ModuleID' => $module_id, 'ModuleEntityID' => $user_id, 'UserID' => $module_entity_id, 'ActivityTypeID' => $activity_type_id, 'ActivityID' => $activity_id,
                    'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID' => $post_as_module_id, 'PostAsModuleEntityID' => $user_id,
                    'EntityID' => $activity_id, 'Score' => $score,
                );
                $this->user_activity_log_score_model->add_activity_log($data);
            }
        }
        
        
        return $activity_id;
    }

    /**
     * [change_privacy Change privacy settings of activity]
     * @param  [int] $activity_id [Activity ID]
     * @param  [int] $privacy     [Activity Privacy]
     */
    public function change_privacy($activity_id, $privacy)
    {
        $this->db->where('ActivityID', $activity_id);
        $this->db->update(ACTIVITY, array('Privacy' => $privacy));
        initiate_worker_job('activity_cache', array('ActivityID'=>$activity_id ));
    }

    /**
     * [getActivityOwnerdetails Used to get the activity owner details]
     * @param  [int] $activity_id [Activity ID]
     * @return [array]           [Activity owner details]
     */
    public function getActivityOwnerdetails($activity_id)
    {
        $Name = '';
        $user_id = '';
        $UserGUID = '';
        $this->db->select('U.FirstName,U.LastName,U.UserID,U.UserGUID');
        $this->db->from(USERS . ' U');
        $this->db->join(ACTIVITY . ' A', 'A.UserID=U.UserID', 'left');
        $this->db->where('A.ActivityID', $activity_id);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            $row = $query->row();
            $user_id = $row->UserID;
            $UserGUID = $row->UserGUID;
            $Name = $row->FirstName . ' ' . $row->LastName;
        }
        return array('Name' => $Name, 'UserID' => $user_id, 'UserGUID' => $UserGUID);
    }

    public function get_tagged_user($entity_type, $entity_id)
    {
        if ($entity_type == 'ACTIVITY')
        {
            $select = 'PostContent';
            $table = ACTIVITY;
            $entity_column = 'ActivityID';
        }

        $this->db->select($select);
        $this->db->from($table);
        $this->db->where($entity_column, $entity_id);
        $query = $this->db->get();
        $content = $query->row()->$select;

        $tagged_user = array();
        if(!empty($content))
        {            

            preg_match_all('/{{([0-9a-zA-Z\s:]+)}}/', $content, $matches);
            $mentions = array();
            if (!empty($matches[1]))
            {
                foreach ($matches[1] as $match)
                {
                    $mention = $this->get_mention($match);
                    if ($mention['ModuleID'] == 3)
                    {
                        $tagged_user[] = $mention['ModuleEntityID'];
                    }
                }
            }
        }
        return $tagged_user;
    }   

    /**
     * Function Name: addComment
     * @param EntityID
     * @param UserID
     * @param Activity
     * Description: Add comment on activity 
     */
    public function addComment($entity_guid, $Comment, $Media, $user_id, $is_media_exists = 0, $SourceID = 1, $DeviceID = 1, $entity_owner = 0, $entity_type = 'ACTIVITY', $postas_module_id = 3, $postas_module_entity_id=0,$parent_comment_id=0,$is_anonymous=0, $comment_id=0, $taged_user='')
    {
        
        if(!$postas_module_entity_id)
        {
            $postas_module_entity_id=$user_id;
        }
       // $comment_id=3341;
        $comment_id_previous = $comment_id;

        $entity_type = strtoupper($entity_type);
        $this->load->model(array('media/media_model'));
        $Count = 1;
        $type = '';
        $notify_node = false;
        $module_entity_owner = 0;
        $activity_id = 0;
        if ($entity_type == 'ACTIVITY')
        {
            $entity = get_detail_by_guid($entity_guid, 0, "ModuleEntityOwner,ActivityID, ActivityTypeID, Params, ModuleID, ModuleEntityID, Privacy, UserID", 2);
            if(!empty($entity)){
                if ($entity['ActivityTypeID'] == '23' || $entity['ActivityTypeID'] == '24')
                {
                    $params = json_decode($entity['Params'], true);
                    $entity_guid = $params['MediaGUID'];
                    $entity_type = 'MEDIA';
                }
                $activity_id = $entity_id = $entity['ActivityID'];
                $module_id = $entity['ModuleID'];
                $Params = $entity['Params'];
                $entity_typeID = $entity['ActivityTypeID'];
                $module_entity_id = $entity['ModuleEntityID'];
                $module_entity_owner = $entity['ModuleEntityOwner'];
            }
        }

        /* get EntityId by Entity GuID */
        if (!empty($entity_type))
        {
            switch ($entity_type)
            {
                case 'ACTIVITY':
                    $type = 'PC';
                    $notify_node = true;
                    break;
                case 'ALBUM':
                    $this->load->model('album/album_model');
                    $entity_id = $this->album_model->get_album_activity_id($entity_guid);
                    $entity = get_detail_by_id($entity_id, 0, "ActivityTypeID, Params, ModuleID, ModuleEntityID, Privacy, UserID", 2);
                    $module_id = $entity['ModuleID'];
                    $Params = $entity['Params'];
                    $entity_typeID = $entity['ActivityTypeID'];
                    $module_entity_id = $entity['ModuleEntityID'];
                    break;
                case 'MEDIA':
                    $this->db->select('M.MediaID, ME.MediaTypeID');
                    $this->db->from(MEDIA . ' AS M');
                    $this->db->join(MEDIAEXTENSIONS . ' AS ME', 'ME.MediaExtensionID=M.MediaExtensionID');
                    $res = $this->db->where('M.MediaGUID', $entity_guid)->get()->row_array();

                    $entity_id = $res['MediaID'];
                    $MediaTypeID = $res['MediaTypeID'];
                    $module_id = 21;
                    $module_entity_id = $entity_id;
                    $type = 'MC';
                    $notify_node = true;
                    break;
                case 'RATING':
                    $entity = array();
                    $entity_id = get_detail_by_guid($entity_guid, 23, "ActivityID", 1);
                    $entity = get_detail_by_id($entity_id, 0, "ActivityTypeID, Params, ModuleID, ModuleEntityID, Privacy, UserID", 2);

                    $module_id = $entity['ModuleID'];
                    $Params = $entity['Params'];
                    $entity_typeID = $entity['ActivityTypeID'];
                    $module_entity_id = $entity['ModuleEntityID'];
                    break;
                case 'BLOG':
                    $entity_id = get_detail_by_guid($entity_guid, 24);
                    $module_id = 24;
                    $module_entity_id = $entity_id;
                    break;
                default:
                    $Return['ResponseCode'] = 412;
                    $Return['Message'] = sprintf(lang('valid_value'), "Entity Type");
                    return $Return;
                    break;
            }
        }

        if(empty($entity_id))
        {
            $Return['ResponseCode'] = 412;
            $Return['Message'] = sprintf(lang('valid_value'), "Entity GUID");
            return $Return;
        }


        add_update_relationship_score($user_id, $module_id, $module_entity_id, 4);

        if ($entity_type == 'ALBUM' || $entity_type == 'RATING')
        {
            $entity_type = 'ACTIVITY';
        }
        //Media should be contained MediaType in order to change update its status in PostComment Table
        $is_file_exists = $is_media_exists = '0';
        if (isset($Media) && !empty($Media))
        {
            foreach ($Media as $media_row)
            {
                # Check if Media is a file then set IsFileExists Flag in Comments (also updating IsMediaExists Flag)
                if (isset($media_row['MediaType']) && !empty($media_row['MediaType']))
                {
                    $is_file_exists = (strtolower($media_row['MediaType']) == 'documents' || strtolower($media_row['MediaType']) == 'document' || strtolower($media_row['MediaType']) == 'file' || strtolower($media_row['MediaType']) == 'files' || $is_file_exists ) ? '1' : '0';
                    $is_media_exists = (strtolower($media_row['MediaType']) == 'Photos' || strtolower($media_row['MediaType']) == 'photo' || strtolower($media_row['MediaType']) == 'image' || strtolower($media_row['MediaType']) == 'images' || strtolower($media_row['MediaType']) == 'video' || strtolower($media_row['MediaType']) == 'videos' || $is_media_exists) ? '1' : '0';
                }
            }
        }
        $Isedit=false;
        $this->load->model(array('activity/activity_front_helper_model')); 
        if(empty($comment_id))
        {
            $data = array(
                        'EntityType'        => $entity_type,
                        'PostCommentGUID'   => get_guid(),
                        'PostComment'       => $Comment,
                        'EntityID'          => $entity_id,
                        'StatusID'          => 2,
                        'UserID'            => $user_id,
                        'CreatedDate'       => get_current_date('%Y-%m-%d %H:%i:%s'),
                        'IsMediaExists'     => $is_media_exists,
                        'IsFileExists'      => $is_file_exists,
                        'DeviceTypeID'      => isset($this->DeviceTypeID) ? $this->DeviceTypeID : $DeviceID,
                        'EntityOwner'       => $entity_owner,
                        'PostAsModuleID'    => $postas_module_id,
                        'PostAsModuleEntityID' => $postas_module_entity_id,
                        'ParentCommentID'   => $parent_comment_id,
                        'IsAnonymous'       => $is_anonymous,
                        'TagedUser'         => $taged_user
                    );
            
            // Set verify status according to user type
            $this->load->model(array('log/user_activity_log_score_model'));
            $data['Verified'] = $this->user_activity_log_score_model->get_user_activity_verify_status($user_id);
                       
            $postSrchComment = $this->activity_front_helper_model->get_search_stripped_content($Comment);               
            $data['PostSearchComment'] = $postSrchComment;
            
            $comment_id = $this->insert(POSTCOMMENTS, $data); 
            
            // Set promotion date if activity is promoted
            $this->activity_front_helper_model->set_promotion($activity_id);
                        
            // Log this user activity
            if($entity_type == 'MEDIA') {
                
            }
            
            $score = $this->user_activity_log_score_model->get_score_for_activity(20, 0, $parent_comment_id, $user_id);
            $user_log_data = array(
                'ModuleID' => 3, 'ModuleEntityID' => $user_id, 'UserID' => $user_id, 'ActivityID' => $activity_id,
                'ActivityTypeID' => 20, 'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID' => 3,
                'PostAsModuleEntityID' => $user_id, 'EntityID' => $comment_id, 'Score' => $score,
            );
            $this->user_activity_log_score_model->add_activity_log($user_log_data);
            
            //nofify admin dashboard for update.
            notify_node('updateAdminDashboard', array('EntityID' => $comment_id));
            
            $this->updateCommentCount($entity_id, $entity_type, $Count, $parent_comment_id);
            $this->update_user_comment_count($user_id);
            
            $LogEntityGUID = $data['PostCommentGUID'];
            $LogType = 'Comment';
            $DeviceID = isset($DeviceID) ? $DeviceID : $this->DeviceTypeID;
            save_log($user_id, $LogType, $LogEntityGUID, false, $DeviceID);
        }
        else
        {   $Isedit=true;
            $data = array(
                        'EntityType'        => $entity_type,
                        'PostComment'       => $Comment,
                        'EntityID'          => $entity_id,
                        'UserID'            => $user_id,
                        'ModifiedDate'      => get_current_date('%Y-%m-%d %H:%i:%s'),
                        'IsMediaExists'     => $is_media_exists,
                        'IsFileExists'      => $is_file_exists,
                        'DeviceTypeID'      => isset($this->DeviceTypeID) ? $this->DeviceTypeID : $DeviceID,
                        'EntityOwner'       => $entity_owner,
                        'PostAsModuleID'    => $postas_module_id,
                        'PostAsModuleEntityID' => $postas_module_entity_id,
                        'ParentCommentID'   => $parent_comment_id,
                        'IsAnonymous'       => $is_anonymous,
                        'TagedUser'         => $taged_user
                    );
            
                    
            $postSrchComment = $this->activity_front_helper_model->get_search_stripped_content($Comment);               
            $data['PostSearchComment'] = $postSrchComment;

            $this->db->where('PostCommentID',$comment_id);
            $this->db->update(POSTCOMMENTS,$data);
            if ($entity_type == 'ACTIVITY')
            {
                //$entity['UserID']
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                /*$parameters[1]['ReferenceID'] = $data['EntityID'];
                $parameters[1]['Type'] = 'EditPostComment';*/
                $this->notification_model->add_notification(127, $user_id, array($entity['UserID']), $data['EntityID'], $parameters, true, 0, array('Comment' => $comment_id));
            }
        }
        //check if media exists
        $usrs = array();
        $tagged = array();
        $usrs[] = array('ModuleID' => 3, 'ModuleEntityID' => $user_id);

        preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $Comment, $matches);
        $mentions = array();
        $mentions_page = array();
        if (!empty($matches[1]))
        {
            foreach ($matches[1] as $match)
            {
                $match_details = explode(':', $match);
                if ($match_details[2] == '3' && $match_details[1] != $user_id)
                {
                    $mentions[] = $match_details[1];
                    $usrs[] = array('ModuleID' => 3, 'ModuleEntityID' => $match_details[1]);
                    $tagged[] = $match_details[1];
                } elseif ($match_details[2] == '18')
                {
                    $mentions_page[] = $match_details[1];
                }
                $mention_id = $this->add_mention($match_details[1], $match_details[2], 0, $match_details[0], $comment_id);
                $Comment = str_replace($match, $mention_id, $Comment);
            }
                  
            $postSrchComment = $this->activity_front_helper_model->get_search_stripped_content($Comment);   
            
            $this->db->set('PostSearchComment', $postSrchComment);
            $this->db->set('PostComment', $Comment);
            $this->db->where('PostCommentID', $comment_id);
            $this->db->update(POSTCOMMENTS);
        }

    if(!empty($comment_id))
        {
            // Save Comment Hostory        

            $previously_tagged = array();

            if($comment_id_previous)
            {
                $previously_tagged = $this->update_comment_history($comment_id_previous); 
            }
            $HistoryData = array(
                            'CommentID'=>$comment_id,
                            'UserID'    => $user_id,
                            'Media'     => json_encode($Media)
                            );
            $CommentData = array('PostComments'=>$Comment); 

            $HistoryData['CommentData'] = json_encode($CommentData);

            $this->add_comment_history($comment_id_previous,$HistoryData,$previously_tagged,2);  
            $this->add_notification_reply($comment_id,$user_id,$entity_id); 
        }       

        if (!empty($data['IsMediaExists']) || !empty($data['IsFileExists']))
        {
            $AlbumID = 0;
            $this->media_model->updateMedia($Media, $comment_id, $user_id, $AlbumID);
        }

        $this->subscribe_model->addUpdate($usrs, $entity_id, 0, $entity_type);

        if (isset($mentions_page) && !empty($mentions_page))
        {
            $this->load->model('pages/page_model');
            foreach ($mentions_page as $m_p)
            {
                $admins = $this->page_model->get_all_admins($m_p);
                //Send Notifications
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $m_p;
                $parameters[1]['Type'] = 'Page';
                $parameters[2]['ReferenceID'] = '5';
                $parameters[2]['Type'] = 'EntityType';
                $this->notification_model->add_notification(70, $user_id, $admins, $data['EntityID'], $parameters, true, 0, array('Comment' => $comment_id));
                $this->subscribe_model->subscribe_email($user_id, $data['EntityID'], 'page_tagged_comment', true, $m_p, $comment_id);
            }
        }

        $stopNotification1 = $this->block_user_list($module_entity_id, $module_id);
        $stopNotification2 = $this->block_user_list($user_id, 3);
        $stopNotification = array_unique(array_merge($stopNotification1, $stopNotification2));

        //$NotificationUserList = $this->notification_model->get_notification_user_list($data['EntityID'],$user_id);

        $NotificationUserList = '';
        if ($entity_type == 'ACTIVITY')
        {
            $NotificationUserList = $this->notification_model->get_notification_user_list($entity_id, $user_id);
            $post_tagged_users = $this->get_tagged_user('ACTIVITY', $entity_id);

            if ($NotificationUserList)
            {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';

                $parameters[1]['ReferenceID'] = 1;
                $parameters[1]['Type'] = 'EntityType';
                if ($entity_typeID == 5 || $entity_typeID == 6 || $entity_typeID == 13)
                {
                    $Params = json_decode($Params, true);
                    $AlbumID = get_detail_by_guid($Params['AlbumGUID'], 13);
                    $parameters[1]['ReferenceID'] = 2;
                    $parameters[2]['ReferenceID'] = $AlbumID;
                    $parameters[2]['Type'] = 'Album';
                }

                $NotificationTypeID = 2;
            }
        } 
        else if ($entity_type == 'MEDIA')
        {
            $NotificationUserList = $this->getMediaOwner($entity_id);
            if ($NotificationUserList)
            {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['Type'] = 'EntityType';
                $parameters[1]['ReferenceID'] = 2;
                $parameters[2]['ReferenceID'] = $entity_id;
                if ($MediaTypeID == 1)
                {
                    $parameters[1]['ReferenceID'] = 3;
                    $parameters[2]['Type'] = 'Media';
                } elseif ($MediaTypeID == 2 || $MediaTypeID == 3)
                {
                    $parameters[1]['ReferenceID'] = 4;
                    $parameters[2]['Type'] = 'Media';
                }
                $NotificationTypeID = 51;
            }
        } 
        else if ($entity_type == 'RATING')
        {
            $this->load->model('ratings/rating_model');
            $NotificationUserList[] = $this->rating_model->get_rating_owner($entity_id);
            if ($NotificationUserList)
            {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $NotificationTypeID = 63;
            }
        }

        $comment_owner_id = '';

        if($parent_comment_id!=0)
        {
           $comment_owner_id =  $this->get_comment_owner_id($parent_comment_id);
        }

        $tagged_notify = array();
        if ($NotificationUserList)
        {
            //$parameters[0]['ReferenceID'] = $user_id;
            //$parameters[0]['Type'] = 'User';
            foreach ($NotificationUserList as $key => $val)
            {
              
                if( !empty($comment_owner_id) && ($comment_owner_id==$val))
                {
                    unset($NotificationUserList[$key]);
                }

                if (in_array($val, $tagged))
                {
                    unset($NotificationUserList[$key]);
                }
                if (in_array($val, $stopNotification))
                {
                    unset($NotificationUserList[$key]);
                }

                if (isset($entity['UserID']) && isset($entity['Privacy']))
                {
                    if (!in_array($entity['Privacy'], $this->isRelation($entity['UserID'], $val, true)))
                    {
                        unset($NotificationUserList[$key]);
                    }
                }
                if (isset($post_tagged_users) && !empty($post_tagged_users))
                {
                    if (in_array($val, $post_tagged_users) && !in_array($val, $tagged))
                    {   
                            if(empty($comment_owner_id))
                            {
                                 $tagged_notify[] = $val;
                            }
                            elseif (!in_array($comment_owner_id, $post_tagged_users)) {
                                $tagged_notify[] = $val;
                            }

                        unset($NotificationUserList[$key]);
                    }
                }
            }
            if (isset($NotificationTypeID) && $Isedit==false )
            {
                if ($postas_module_id == 18  && isset($module_entity_id))
                {
                    $parameters[0]['ReferenceID'] = $postas_module_entity_id;
                    $parameters[0]['Type'] = 'Page';
                }
                $this->notification_model->add_notification($NotificationTypeID, $user_id, $NotificationUserList, $data['EntityID'], $parameters, true, 0, array('Comment' => $comment_id));
            }
            if ($tagged_notify)
            {
                $this->notification_model->add_notification(55, $user_id, $tagged_notify, $data['EntityID'], array($parameters[0]), true, 0, array('Comment' => $comment_id));
            }
        }
        if ($tagged)
        {
            foreach ($tagged as $k => $v)
            {
                if( !empty($comment_owner_id) && ($comment_owner_id==$v) )
                {
                    unset($tagged[$k]);
                }
                if (in_array($v, $NotificationUserList) || in_array($v, $tagged_notify))
                {
                    unset($tagged[$k]);
                }
                if (isset($entity['UserID']) && isset($entity['Privacy']))
                {
                    if (!in_array($entity['Privacy'], $this->isRelation($entity['UserID'], $v, true)))
                    {
                        unset($NotificationUserList[$k]);
                    }
                }
            }
            if ($tagged)
            {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                if(isset($entity))
                {
                    if ($entity['ActivityTypeID'] == '23' || $entity['ActivityTypeID'] == '24')
                    {
                        $this->notification_model->add_notification(132, $user_id, $tagged, $entity['ActivityID'], $parameters, true, 0, array('Comment' => $comment_id));
                    }
                    else
                    {
                        $this->notification_model->add_notification(21, $user_id, $tagged, $data['EntityID'], $parameters, true, 0, array('Comment' => $comment_id));
                    }
                }
            }
        }

        if ($comment_owner_id)
        {
            $parameters[0]['ReferenceID'] = $user_id;
            $parameters[0]['Type'] = 'User';
            $this->notification_model->add_notification(113, $user_id, array($comment_owner_id), $data['EntityID'], $parameters, true, 0, array('Comment' => $comment_id));
        }

        if ($entity_type == 'ACTIVITY')
        {
            set_last_activity_date($data['EntityID']);
            if ($module_id != 3)
            {
                set_last_activity_date($module_entity_id, $module_id);
            }
        }

        if ($notify_node)
        {
            notify_node('liveFeed', array('Type' => $type, 'UserID' => $user_id, 'EntityGUID' => $entity_guid));
        }

        return $this->getActivityComments($data['EntityType'], $data['EntityID'], 1, 1, $data['UserID'], 1,2,TRUE,array(),FALSE,$comment_id,$postas_module_id,$postas_module_entity_id,'',$parent_comment_id);
    }
    
    function add_notification_reply($comment_id,$user_id,$entity_id)
    {
       $notification_user_lisr = array();
       $this->db->select('GROUP_CONCAT(UserID) UserIDs');
       $this->db->from(POSTCOMMENTS);
       $this->db->where('ParentCommentID',$comment_id);
       $this->db->where_not_in('UserID',array($user_id));
       $this->db->where('StatusID',2);
       $Query = $this->db->get();
       if($Query->num_rows()>0)
        {
            $result = $Query->row_array();
            $notification_user_list = $result['UserIDs'];
            
            if (!empty($notification_user_list))
            {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $this->notification_model->add_notification(121, $user_id, explode(',',$notification_user_list), $comment_id, $parameters);
            }
            
        }
        
    }

    function get_comment_owner_id($post_comment_id)
    {
        $this->db->select('UserID');
        $this->db->from(POSTCOMMENTS);
        $this->db->where('PostCommentID',$post_comment_id);
        $Query = $this->db->get();

        if($Query->num_rows()>0)
        {
            $row = $Query->row_array();
            
            return $row['UserID'];
        }
        else
        {
            return FALSE;
        }      

    }

    /**
     * [get_entity_log_users description]
     * @param  [string]     $entity_type
     * @param  [guid]       $entity_guid
     * @param  [string]     $page_size
     * @param  [guid]       $page_no
     * @return [Array]      [Users]
     */
    public function get_entity_log_users($entity_type, $entity_guid, $search_key, $page_size, $page_no, $CountOnly = 0)
    {
        $Offset = $this->get_pagination_offset($page_no, $page_size);

        switch ($entity_type) {
            case 'Page' :
            case 'PageLike' :
                $module_id = 18;
                break;
            case 'User' :
                $module_id = 3;
                break;
            case 'Group' :
                $module_id = 1;
                break;
            case 'Event' :
                $module_id = 14;
                break;
            case 'Comment' :
            case 'CommentLike' :
                $module_id = 20;
                break;
            case 'Activity' :
            case 'ActivityLike' :
                $module_id = 19;
                break;
            case 'Media' :
                $module_id = 21;
                break;
            case 'Album' :
                $module_id = 13;
                break;
            default:
                return false;
                break;
        }
        if ($entity_type == 'Activity') {
            $entity_data = get_detail_by_guid($entity_guid, $module_id, 'ActivityID,ActivityTypeID', 2);
            if ($entity_data['ActivityTypeID'] == '5' || $entity_data['ActivityTypeID'] == '6' || $entity_data['ActivityTypeID'] == '13') {
                $this->db->select('AlbumID');
                $this->db->where('ActivityID', $entity_data['ActivityID']);
                $this->db->limit(1);
                $qry = $this->db->get(ALBUMS);
                if ($qry->num_rows()) {
                    $entity_id = $qry->row()->AlbumID;
                    $entity_type = 'Album';
                }
            } else {
                $entity_id = $entity_data['ActivityID'];
            }
        } else {
            $entity_id = get_detail_by_guid($entity_guid, $module_id);
        }

        $this->db->select('U.FirstName,U.LastName,U.UserGUID,EV.ViewCount');
        $this->db->from(ENTITYVIEW . ' EV');
        $this->db->join(USERS . ' U', 'U.UserID=EV.UserID', 'left');
        $this->db->where('EV.EntityType', $entity_type);
        $this->db->where('EV.EntityID', $entity_id);
        if ($search_key) {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where("(U.FirstName LIKE '%" . $search_key . "%' OR U.LastName LIKE '%" . $search_key . "%')", NULL, FALSE);
        }
        if (!$CountOnly) {
            $this->db->limit($page_size, $Offset);
        }
        $query = $this->db->get();
        if ($CountOnly) {
            return $query->num_rows();
        }
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    /**
     * [updateShareCount description]
     * @param  [string]    $entity_type   [Entity Id]
     * @param  [guid] $entity_guid [ACTIVITY, COMMENT]
     * @param  int      $Count      [increment/decrement]
     * @return [type]               [description]
     */
    public function updateShareCount($entity_type, $entity_id, $Count = 1)
    {
        $set_field = "NoOfShares";
        $table_name = ACTIVITY;
        $condition = array("ActivityID" => $entity_id);
        switch ($entity_type)
        {
            case 'ACTIVITY':
                $table_name = ACTIVITY;
                $condition = array("ActivityID" => $entity_id);
                break;
            case 'MEDIA':
                $table_name = MEDIA;
                $condition = array("MediaID" => $entity_id);
                break;
            default:
                return false;
                break;
        }
        $query = $this->db->get_where($table_name, $condition);
        if ($query->num_rows())
        {
            $row = $query->row();
            $this->db->where($condition);
            if ($Count)
            {
                $this->db->set($set_field, "$set_field+1", FALSE);
            } else
            {
                if ($row->NoOfShares > 0)
                {
                    $this->db->set($set_field, "$set_field-1", FALSE);
                } else
                {
                    $this->db->set($set_field, "0", FALSE);
                }
            }
            if ($entity_type == "ACTIVITY" && $Count == 1)
            {
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            }
            $this->db->update($table_name);
        }
    }

    /**
     * [updateCommentCount description]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [string] $entity_type [ACTIVITY, COMMENT]
     * @param  int      $Count      [Like Count increment/decrement]
     * @return [type]               [description]
     */
    public function updateCommentCount($entity_id, $entity_type, $Count = 1,$parent_comment_id=0)
    {
        $set_field = "NoOfComments";
        $table_name = ACTIVITY;
        $condition = array("ActivityID" => $entity_id);
        switch ($entity_type)
        {
            case 'ACTIVITY':
                $table_name = ACTIVITY;
                $condition = array("ActivityID" => $entity_id);
                break;
            case 'MEDIA':
                $table_name = MEDIA;
                $condition = array("MediaID" => $entity_id);
                break;
            case 'RATING':
                $table_name = RATINGS;
                $condition = array("RatingID" => $entity_id);
                break;
            case 'BLOG':
                $table_name = BLOG;
                $condition = array("BlogID" => $entity_id);
                break;
            default:
                return false;
                break;
        }

        if($parent_comment_id==0)
        {
            $this->db->where($condition);
            $this->db->set($set_field, "$set_field+($Count)", FALSE);
            if ($entity_type == "ACTIVITY" && $Count == 1)
            {
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            }
            $this->db->update($table_name);
        }
        else{
            
            $this->db->where(array('PostCommentID'=>$parent_comment_id));
            $this->db->set('NoOfReplies', "NoOfReplies+($Count)", FALSE);
            $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->update(POSTCOMMENTS);
        }

    }

    /**
     * Function Name: deleteComment
     * @param CommentGuID
     * @param ActivityGuID
     * Description: Remove comments from activity 
     */
    function deleteComment($CommentGUID, $user_id, $entity_type = 'Activity')
    {
        $this->db->select('PC.UserID as CommenterID,ParentCommentID, PostCommentID');
        $this->db->from(POSTCOMMENTS . ' PC');
        switch (strtoupper($entity_type))
        {
            case 'ACTIVITY':
                $this->db->select('A.ActivityID,A.UserID,A.ModuleID,A.ModuleEntityID');
                $this->db->join(ACTIVITY . ' A', 'PC.EntityID=A.ActivityID', 'left');
                break;
            case 'MEDIA':
                $this->db->select('M.UserID,M.MediaID,M.ModuleID,M.MediaID AS ModuleEntityID');
                $this->db->join(MEDIA . ' M', 'M.MediaID=PC.EntityID', 'left');
                break;
            case 'RATING':
                $this->db->select('R.UserID,R.RatingID,R.ModuleID,R.ModuleEntityID');
                $this->db->join(RATINGS . ' R', 'R.RatingID=PC.EntityID', 'left');
                break;
            case 'BLOG':
                $this->db->select('B.BlogID, B.BlogID AS ModuleEntityID');
                $this->db->join(BLOG . ' B', 'B.BlogID=PC.EntityID', 'left');
                //23
                break;
            default:
                $this->db->select('A.ActivityID,A.UserID,A.ModuleID,A.ModuleEntityID');
                $this->db->join(ACTIVITY . ' A', 'PC.EntityID=A.ActivityID', 'left');
                break;
        }

        //$this->db->where('PC.EntityType',$entity_type); 
        $this->db->where('PC.PostCommentGUID', $CommentGUID);
        $this->db->where('PC.StatusID', '2');
        $query = $this->db->get();
        if ($query->num_rows())
        {
            $row = $query->row();
            $comment_owner_id = '';
            if($row->ParentCommentID)
            {
               $comment_owner_id = $this->get_comment_owner_id($row->ParentCommentID);   
            }
                        
            if (($comment_owner_id == $user_id) || checkPermission($user_id, $row->ModuleID, $row->ModuleEntityID, 'Sticky', 3, $user_id) || checkPermission($user_id, 3, $row->UserID) || checkPermission($user_id, 3, $row->CommenterID))
            {
                $this->db->where('PostCommentGUID', $CommentGUID);
                $this->db->update(POSTCOMMENTS, array('StatusID' => '3'));
                $Comments = $this->getCommentDetails($CommentGUID);
                
                
                if(!empty($row->ParentCommentID) && $row->ParentCommentID!=0 )
                {
                    $this->db->set('NoOfReplies', 'NoOfReplies-1', FALSE);
                    $this->db->where('PostCommentID', $row->ParentCommentID);
                    $this->db->update(POSTCOMMENTS);
                } else {
                    if (strtoupper($Comments->EntityType) == 'ACTIVITY')
                    {
                        $this->db->set('NoOfComments', 'NoOfComments-1', FALSE);
                        $this->db->where('ActivityID', $Comments->EntityID);
                        $this->db->update(ACTIVITY);
                    } 
                    else if (strtoupper($Comments->EntityType) == 'MEDIA')
                    {
                        $this->db->set('NoOfComments', 'NoOfComments-1', FALSE);
                        $this->db->where('MediaID', $Comments->EntityID);
                        $this->db->update(MEDIA);
                    } 
                    else if (strtoupper($Comments->EntityType) == 'BLOG')
                    {
                        $this->db->set('NoOfComments', 'NoOfComments-1', FALSE);
                        $this->db->where('BlogID', $Comments->EntityID);
                        $this->db->update(BLOG);
                    } 
                    else if (strtoupper($Comments->EntityType) == 'RATING')
                    {
                        $this->db->set('NoOfComments', 'NoOfComments-1', FALSE);
                        $this->db->where('RatingID', $Comments->EntityID);
                        $this->db->update(RATINGS);
                    }
                }
                
                
                
                
                
                if ($Comments->IsMediaExists == 1)
                {
                    $this->db->set('StatusID', '3');
                    $this->db->where('MediaSectionID', '6');
                    $this->db->where('MediaSectionReferenceID', $Comments->PostCommentID);
                    $this->db->update(MEDIA);
                }


                


                $Return['Message'] = lang('success');
                $Return['ResponseCode'] = 200;
                return $Return;
            } else
            {
                $Return['Message'] = lang('permission_denied');
                $Return['ResponseCode'] = 412;
                return $Return;
            }
        }
        $Return['Message'] = sprintf(lang('valid_value'), "comment GUID");
        $Return['ResponseCode'] = 412;
        return $Return;
    }

    /**
     * Function Name: isRelation
     * @param UserID
     * @param CurrentUser
     * Description: Get relation between users to determine if we have to show activity or not
     */
    public function isRelation($user_id, $current_user, $arr = false, $activity_guid = 0)
    {
        if ($activity_guid)
        {
            $activity_details = get_detail_by_guid($activity_guid, 0, 'ActivityTypeID,ModuleID,ModuleEntityID', 2);
            if ($activity_details)
            {
                if ($activity_details['ActivityTypeID'] == 7)
                {
                    $this->load->model('group/group_model');
                    $status = $this->group_model->can_post_on_wall($activity_details['ModuleEntityID'], $current_user);
                    if (!$status)
                    {
                        return array(0);
                    }
                }
            }
        }

        $relation[] = 1;
        if ($user_id == $current_user)
        {
            $relation[] = 2;
            $relation[] = 3;
            $relation[] = 4;
        }
        else
        {
            $this->load->model('users/friend_model');
            if ($this->friend_model->checkFriendStatus($current_user, $user_id) == 1)
            {
                $relation[] = 2;
                $relation[] = 3;
            } 
            else
            {
                $check_status = $this->friend_model->get_friends_of_friends($user_id,$current_user,true);
                if($check_status)
                {
                        $relation[] = 2;
                }
            }
        }
        
        if ($arr)
        {
            return $relation;
        } else
        {
            return implode(",", $relation);
        }
    }

    /**
     * [getLikeDetails Get list of all users who likes particular activity]
     * @param  [string] $entity_guid [Entity GUID]
     * @param  [string] $entity_type [Entity Type]
     * @param  [array]  $blocked_users [Array of Blocked User IDs]
     * @param  [int]  $page_no [Page number]
     * @param  [int]  $page_size [Total records per page]
     * @param  [int]  $NumRows [flag to return only count of total records]
     * @return [array]              [list of all users who likes activity]
     * 
     */
    public function getLikeDetails($entity_guid, $entity_type, $blocked_users = array(), $page_no = 0, $page_size = 0, $NumRows = FALSE, $user_id = 0)
    {
        $result = array();
       // $friend_followers_list = $this->user_model->get_friend_followers_list();
       // $friends = $friend_followers_list['Friends'];
        $friends[] = 0;
        //$followers = $friend_followers_list['Follow'];
        $followers[] = 0;
        $friends_list = implode("','", $friends);

        array_unshift($friends, $user_id);
        $entity_id = '';
        if ($entity_type == 'ACTIVITY')
        {
            $activity_row = get_detail_by_guid($entity_guid, '0', 'Params,ActivityTypeID,ActivityID', 2);
            if (!empty($activity_row))
            {
                if ($activity_row['ActivityTypeID'] == '23' || $activity_row['ActivityTypeID'] == '24')
                {
                    $params = json_decode($activity_row['Params'], true);
                    $entity_guid = $params['MediaGUID'];
                    $entity_type = 'MEDIA';
                }
                $entity_id = $activity_row['ActivityID'];
            }
        }

        if ($entity_type == 'ALBUM')
        {
            $this->load->model('album/album_model');
            $entity_id = $this->album_model->get_album_activity_id($entity_guid);
            $entity_type = 'ACTIVITY';
        } 
        else if ($entity_type == 'ACTIVITY')
        {
            //$entity_id = get_detail_by_guid($entity_guid);
        } 
        else if ($entity_type == 'COMMENT')
        {
            $entity_id = get_detail_by_guid($entity_guid, 20, "PostCommentID", 1);
        } 
        else if ($entity_type == 'PAGE')
        {
            $entity_id = get_detail_by_guid($entity_guid, 18, "PageID", 1);
        } 
        else if ($entity_type == 'MEDIA')
        { // Added condition to fetch like detail of activity type media
            $entity_id = get_detail_by_guid($entity_guid, 21, "MediaID", 1);
        } 
        else if ($entity_type == 'BLOG')
        { // Added condition to fetch like detail of activity type media
            $entity_id = get_detail_by_guid($entity_guid, 24, "BlogID", 1);
        }

        $this->db->select('U.UserGUID,U.FirstName,U.LastName,IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture, U.UserID,PL.EntityOwner,PL.EntityType,PL.EntityID,PU.Url AS ProfileURL,PL.PostAsModuleID,PL.PostAsModuleEntityID', false);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CN.CountryName,"") as CountryName', FALSE);
        //$this->db->select("IF(U.UserID='" . $user_id . "',2,IF(U.UserID IN('" . $friends_list . "'),1,0)) as OrderByVar", false);
        $this->db->from(POSTLIKE. ' PL');
        $this->db->join(USERS . ' U', 'U.UserID=PL.UserID', 'left');
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CN', 'CN.CountryID=UD.CountryID', 'left');
        $this->db->where('PL.EntityType', $entity_type);
        $this->db->where('PL.EntityID', $entity_id);
        $this->db->where('PL.StatusID', '2');
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('PL.UserID', $blocked_users);
        }

        if (!empty($page_size))
        {
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        $this->db->_protect_identifiers = FALSE;
        //$this->db->order_by('OrderByVar', 'DESC');
        $this->db->order_by("CASE WHEN U.UserID = ".$user_id." THEN 2 WHEN U.UserID IN ('" . $friends_list . "') THEN 1 ELSE 0 END DESC");
        $this->db->_protect_identifiers = TRUE;
        $this->db->order_by('PL.ModifiedDate', 'DESC');

        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($NumRows)
        {
            return $query->num_rows();
        }
        if ($query->num_rows())
        {
            $is_liked = 0;
            $i = 0;
            $user_follow_page = array();
            $this->db->select('TypeEntityID');
            $this->db->from(FOLLOW);
            $this->db->where('UserID', $user_id);
            $this->db->where('type', 'page');
            $follow_page_query = $this->db->get();

            if ($follow_page_query->num_rows())
            {
                foreach ($follow_page_query->result_array() as $item)
                {
                    $user_follow_page[] = $item['TypeEntityID'];
                }
            }

            $this->load->model('users/friend_model');
            $connected_user = $this->friend_model->get_connect_user($user_id);
            foreach ($query->result_array() as $r)
            {
                //$result[$i] = $r;
                $result[$i]['UserGUID'] = $r['UserGUID'];
                $result[$i]['FirstName'] = $r['FirstName'];
                $result[$i]['LastName'] = $r['LastName'];
                $result[$i]['ProfileURL'] = $r['ProfileURL'];  //get_entity_url($r['UserID'], "User", 1);
                $result[$i]['ProfilePicture'] = $r['ProfilePicture'];
                $result[$i]['CityName'] = $r['CityName'];
                $result[$i]['CountryName'] = $r['CountryName'];
                $result[$i]['UserGUID'] = $r['UserGUID'];
                $result[$i]['ModuleID'] = 3;

                $result[$i]['ShowFriendsBtn'] = 0;
                $result[$i]['FriendStatus'] = 0;
                $result[$i]['ShowFollowBtn'] = 0;

                $result[$i]['follow'] = 'Follow';
                if (in_array($r['UserID'], $followers))
                {
                    $result[$i]['follow'] = 'Unfollow';
                }

                /* check if logged user already liked */
                if ($r['UserID'] == $user_id)
                {
                    $is_liked = 1;
                }
                $result[$i]['IsLiked'] = $is_liked;

                if ($r['PostAsModuleID'] == 18)
                {
                    $this->db->select('P.PageID,P.Title,P.ProfilePicture,P.PageGUID,P.PageURL,P.CategoryID');
                    $this->db->from(PAGES . ' P');
                    $this->db->where('P.PageID', $r['PostAsModuleEntityID']);
                    
                    $pageQry = $this->db->get();
                    if ($pageQry->num_rows())
                    {
                        $pageRow = $pageQry->row();
                        $result[$i]['FirstName'] = $pageRow->Title;
                        $result[$i]['LastName'] = '';
                        $result[$i]['ProfilePicture'] = $pageRow->ProfilePicture;
                        $result[$i]['ProfileURL'] = $pageRow->PageURL;
                        $result[$i]['ModuleID'] = 18;
                        $result[$i]['UserGUID'] = $pageRow->PageGUID;

                        $result[$i]['follow'] = 'Follow';
                        if (in_array($pageRow->PageID, $user_follow_page))
                        {
                            $result[$i]['follow'] = 'Unfollow';
                        }

                        if ($result[$i]['ProfilePicture'] == '')
                        {
                            $this->load->model('category/category_model');
                            $category_name=$this->category_model->get_category_by_id($pageRow->CategoryID);
                            $category_icon = $category_name['Icon'];
                            $result[$i]['ProfilePicture'] = $category_icon;
                            //}
                        }
                    }
                } else
                {
                    $entity_id = $r['UserID'];
                    if ($user_id != $entity_id)
                    {
                        $this->load->model(array('users/friend_model'));
                        $result[$i]['ShowFriendsBtn'] = 1;
                        $result[$i]['ShowFollowBtn'] = 1;
                        $result[$i]['FriendStatus'] = 4;
                        if (isset($connected_user[$entity_id]))
                        {
                            if ($connected_user[$entity_id])
                            {
                                if ($connected_user[$entity_id]['Status'] == '1')
                                {
                                    $result[$i]['FriendStatus'] = 1; // Already Friend
                                } elseif ($connected_user[$entity_id]['RequestedBy'] == $user_id)
                                {
                                    $result[$i]['FriendStatus'] = 2; // Pending Request
                                } else
                                {
                                    $result[$i]['FriendStatus'] = 3; // Accept Friend Request
                                }
                            }
                        }
                        //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request
                        $users_relation = get_user_relation($user_id, $entity_id);
                        $privacy_details = $this->privacy_model->details($entity_id);
                        $privacy = ucfirst($privacy_details['Privacy']);
                        if ($privacy_details['Label'])
                        {
                            foreach ($privacy_details['Label'] as $privacy_label)
                            {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                                {
                                    $result[$i]['ProfilePicture'] = 'user_default.jpg';
                                }
                                if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation))
                                {
                                    $result[$i]['ShowFriendsBtn'] = 0;
                                }
                                if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation))
                                {
                                    $result[$i]['CityName']    = '';
                                    $result[$i]['CountryName'] = '';
                                }
                            }
                        }
                    }
                }
                $i++;
            }
            return $result;
        } else
        {
            return $result;
        }
    }

    /**
     * [get_like_count Get total count of likes for particular activity]
     * @param  [string] $entity_id   [Entity ID]
     * @param  [string] $entity_type [Entity Type]
     * @param  [array]  $blocked_users [Array of Blocked User IDs]
     * @return [int]              [total count of likes]
     */
    public function get_like_count($entity_id, $entity_type = "ACTIVITY", $blocked_users = array(), $status = 2)
    {
        $this->db->select('COUNT(PL.PostLikeID) as TotalRow');
        $this->db->from(POSTLIKE .' PL');
        $this->db->join(USERS . ' U', 'U.UserID=PL.UserID AND U.StatusID=2', 'left');
        $this->db->where('PL.EntityType', strtoupper($entity_type));
        $this->db->where('PL.EntityID', $entity_id);
        $this->db->where('PL.StatusID', $status);
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('PL.UserID', $blocked_users);
        }
        $result = $this->db->get();
        $count_data=$result->row_array();
        return $count_data['TotalRow'];
    }

    /**
     * Function Name: sharePost
     * @param ActivityID,EntityType,EntityID,UserID
     * @return Activity Details
     * Description: Share particular activity and returns shared activity details 
     */
    public function sharePost($module_id, $module_entity_id, $user_id, $entity_type, $entity_id, $post_content, $CommentSettings = '', $VisibleFor = '',$post_type=1,$activity_guid='')
    {
        $entity_typeID = 1;
        $is_media_exist = 0;
        $IsFileExist = 0;
        if ($user_id == $module_entity_id)
        {
            $activity_type_id = 10;
            $subscribe_action = 'share_self';
        } else
        {
            $activity_type_id = 9;
            $subscribe_action = 'share';
        }

        if ($entity_type == 'ACTIVITY')
        {
            $parent_activity_id = get_detail_by_id($entity_id, 0, 'ActivityTypeID', 1);
            if ($parent_activity_id == 5 || $parent_activity_id == 6 || $parent_activity_id == 13)
            {
                $entity_typeID = 2;
            }
        }

        if($activity_guid == '')
        {
            if ($entity_type == 'MEDIA')
            {
                if ($activity_type_id == 10)
                {
                    $activity_type_id = 15;
                    $is_media_exist = 1;
                } else if ($activity_type_id == 9)
                {
                    $activity_type_id = 14;
                    $is_media_exist = 1;
                }
                $entity_typeID = 3;
                $this->updateShareCount('MEDIA', $entity_id, 1);
            } else
            {
                $entity_id = $this->getOriginalActivity($entity_id);
                $this->updateShareCount('ACTIVITY', $entity_id, 1);
                $activity_info = get_detail_by_id($entity_id, 0, 'IsMediaExist,IsFileExists', 2);
                $IsFileExist = $activity_info['IsFileExists'];
                $is_media_exist = $activity_info['IsMediaExist'];
            }
        }

        $activity_id = $this->addActivity($module_id, $module_entity_id, $activity_type_id, $user_id, $entity_id, $post_content, $CommentSettings, $VisibleFor, array(), $is_media_exist, 0, 0, 0, $IsFileExist,'',$post_type,$activity_guid);

        //$this->notification_model->add_notification($NotificationTypeID,$user_id,$NotificationUserList,$entity_id,$parameters);
        if($activity_guid == '')
        {
            if ($activity_type_id == 9 || $activity_type_id == 14)
            {
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $entity_typeID;
                $parameters[1]['Type'] = 'EntityType';
                $this->notification_model->add_notification(47, $user_id, array($module_entity_id), $activity_id, $parameters);
            }
            if ($entity_type == 'MEDIA')
            {
                $this->load->model('media/media_model');
                $OriginalActivityOwnerDetail = $this->media_model->get_media_owner_details($entity_id);
            } else
            {
                $OriginalActivityOwnerDetail = $this->getActivityOwnerdetails($entity_id);
            }
            $parameters[0]['ReferenceID'] = $user_id;
            $parameters[0]['Type'] = 'User';
            $parameters[1]['ReferenceID'] = $module_entity_id;
            $parameters[1]['Type'] = 'User';
            $parameters[2]['ReferenceID'] = $entity_typeID;
            $parameters[2]['Type'] = 'EntityType';
            if ($OriginalActivityOwnerDetail['UserID'] != $user_id && $OriginalActivityOwnerDetail['UserID'] != $module_entity_id)
            {
                $this->notification_model->add_notification(48, $user_id, array($OriginalActivityOwnerDetail['UserID']), $activity_id, $parameters);
            }
            if (isset($subscribe_action))
            {
                $this->subscribe_model->subscribe_email($user_id, $activity_id, $subscribe_action);
            }
        }
        return $this->getSingleUserActivity($user_id, $activity_id);
    }

    /**
     * Function Name: getOriginalActivity
     * @param ActivityID
     * @return ActivityID
     * Description: Get ID of parent Activity (In case of share) 
     */
    public function getOriginalActivity($activity_id)
    {
        $this->db->select('ParentActivityID');
        $this->db->where('ActivityID', $activity_id);
        $query = $this->db->get(ACTIVITY);
        if ($query->num_rows())
        {
            $row = $query->row();
            if ($row->ParentActivityID == 0)
            {
                return $activity_id;
            } else
            {
                return $row->ParentActivityID;
            }
        } else
        {
            return $activity_id;
        }
    }

    /**
     * Function Name: getSharedActivityDetail
     * @param ActivityID
     * @return ActivityID
     * Description: Get ID of parent Activity details
     */
    public function getSharedActivityDetail($ParentActivityID)
    {
        if ($ParentActivityID != 0)
        {
            $this->db->select('M.ModuleName,A.ModuleEntityID,A.ModuleID');
            $this->db->from(ACTIVITY . ' AS A');
            $this->db->join(MODULES . ' AS M', 'M.ModuleID=A.ModuleID');
            $this->db->where('A.ActivityID', $ParentActivityID);
            $query = $this->db->get();
            if ($query->num_rows())
            {
                $row = $query->row();
                $ModuleEntityGUID = get_detail_by_id($row->ModuleEntityID, $row->ModuleID);
                return array('SharedActivityModule' => $row->ModuleName, 'SharedEntityGUID' => $ModuleEntityGUID);
            } else
            {
                return array('SharedActivityModule' => '', 'SharedEntityGUID' => '');
            }
        } else
        {
            return array('SharedActivityModule' => '', 'SharedEntityGUID' => '');
        }
    }

    /**
     * [getCommentDetails Get comment details]
     * @param  [type] $CommentGUID [Comment GUID]
     * @return [array]              [comment details]
     */
    public function getCommentDetails($CommentGUID)
    {
        $this->db->select('EntityID, IsMediaExists, PostCommentID, EntityType');
        $this->db->where('PostCommentGUID', $CommentGUID);
        $query = $this->db->get(POSTCOMMENTS);
        //echo $this->db->last_query();       
        if ($query->num_rows())
        {
            $row = $query->row();
            return $row;
        }
    }

    /**
     * [commentStatus switch comments on / off ]
     * @param  [type] $entity_type [Entity Type]
     * @param  [type] $entity_guid [Entity GUID]
     * @return [array]            [Response array]
     */
    public function commentStatus($entity_type, $entity_guid)
    {
        
        $entity_type = strtoupper($entity_type);
        $Return['ResponseCode'] = 412;
        $Return['Message'] = sprintf(lang('valid_value'), "Entity Type");
        if ($entity_type == 'ACTIVITY')
        {
            $activity_id=get_detail_by_guid($entity_guid);
            $this->db->set('IsCommentable', 'IF(IsCommentable=1,0,1)', FALSE);
            $this->db->where('ActivityGuID', $entity_guid);
            $this->db->update(ACTIVITY);

            $this->db->set('IsCommentable', 'IF(IsCommentable=1,0,1)', FALSE);
            $this->db->where('MediaSectionID', 3);
            $this->db->where('MediaSectionReferenceID', $activity_id);
            $this->db->update(MEDIA);

            $Return['Message'] = lang('success');
            $Return['ResponseCode'] = 200;
            initiate_worker_job('activity_cache', array('ActivityID'=>$activity_id ));
            }
             
        return $Return;
    }

    /**
     * [toggleLike To mark Like or Unlike an entity]
     * @param  [array] $Data [input data for toggleLike Request]
     * @return [array]       [array of response code and message]
     */
    function toggleLike($Data)
    {
        $Return['Message'] = lang('success');
        $Return['ResponseCode'] = 200;
        $user_id = $Data['UserID'];
        $entity_type = strtoupper($Data['EntityType']);
        $entity_guid = $Data['EntityGUID'];
        $entity_owner = '0';
        $post_as_module_id = isset($Data['PostAsModuleID']) ? $Data['PostAsModuleID'] : 3;
        $PostAsModuleEntityGUID = isset($Data['PostAsModuleEntityGUID']) ? $Data['PostAsModuleEntityGUID'] : '0';
        $post_as_module_entity_id=($PostAsModuleEntityGUID) ? get_detail_by_guid($PostAsModuleEntityGUID, $post_as_module_id) : $user_id;
        $StatusID = 2;
        $Count = 1;
        $module_id = 3;
        $module_entity_id = '';
        $ParentEntityID = '';
        $NotificationUserList = array();
        $type = '';
        $notify_node = false;
        $notify_params = array();

        // add parameter to check actiontype -- 
        $like_count = 1;
        $dislike_count = 1;
        $action_type = isset($Data['ActionType']) ? strtoupper($Data['ActionType']): 'LIKE';
        $dis_like = isset($Data['Dislike']) ? $Data['Dislike']: 0;
        
        $action_post_status = 3;
        if($action_type == 'LIKE' && empty($dis_like)){
            $action_post_status = 2;
        }
        
        $activity_id = 0;
        if ($entity_type == 'ACTIVITY')
        {
            $entity = get_detail_by_guid($entity_guid, 0, "ActivityID, ActivityTypeID, Params, ModuleID, ModuleEntityID", 2);
            if(!empty($entity)){
                 if ($entity['ActivityTypeID'] == '23' || $entity['ActivityTypeID'] == '24')
                {
                    $params = json_decode($entity['Params'], true);
                    $entity_guid = $params['MediaGUID'];
                    $entity_type = 'MEDIA';
                }
                $entity_id = $entity['ActivityID'];
                $module_id = $entity['ModuleID'];
                $Params = $entity['Params'];
                $entity_typeID = $entity['ActivityTypeID'];
                $module_entity_id = $entity['ModuleEntityID'];
                $activity_id = $entity['ActivityID'];
            }
        }

        /* get EntityId by Entity GuID */
        switch ($entity_type)
        {
            case 'ACTIVITY':
                $LogType = 'ActivityDislike';
                if($action_type == 'LIKE'){
                    $notify_node = true;
                    $LogType = 'ActivityLike';
                }
                $type = 'PL';
                break;
            case 'ALBUM':
                $this->load->model('album/album_model');
                $entity_id = $this->album_model->get_album_activity_id($entity_guid);
                $entity = get_detail_by_id($entity_id, 0, "ActivityTypeID, Params, ModuleID, ModuleEntityID", 2);
                $module_id = $entity['ModuleID'];
                $Params = $entity['Params'];
                $entity_typeID = $entity['ActivityTypeID'];
                $module_entity_id = $entity['ModuleEntityID'];
                $LogType = 'ActivityDislike';
                if($action_type == 'LIKE'){
                    $LogType = 'ActivityLike';
                }
                break;
            case 'COMMENT':
                //$entity_id = get_detail_by_guid($entity_guid, 20);
                $entity = get_detail_by_guid($entity_guid, 20, "PostCommentID, EntityID, EntityType", 2);
                $entity_id = $entity['PostCommentID'];
                $CommentEntityType = $entity['EntityType'];
                $activity_id = $ParentEntityID = $entity['EntityID'];
                if (strtolower($CommentEntityType) == 'activity')
                {
                    $module_details = get_detail_by_id($ParentEntityID, 0, 'ModuleID,ModuleEntityID', 2);
                    if ($module_details['ModuleID'] == 18)
                    {
                        $module_entity_id = $module_details['ModuleEntityID'];
                    }
                }
                $LogType = 'CommentDislike';
                if($action_type == 'LIKE'){
                    $notify_node = true;
                    $LogType = 'CommentLike';
                }
                $type = 'CL';
                $e_guid = $entity_guid;
                break;
            case 'PAGE':
                $entity_id = get_detail_by_guid($entity_guid, 18);
                $module_id = 18;
                $module_entity_id = $entity_id;
                $LogType = 'PageLike';
                break;
            case 'MEDIA':
                $this->db->select('MediaID,MediaTypeID');
                $this->db->from(MEDIA . ' AS M');
                $this->db->join(MEDIAEXTENSIONS . ' AS ME', 'ME.MediaExtensionID=M.MediaExtensionID');
                $res = $this->db->where('M.MediaGUID', $entity_guid)->get()->row_array();

                $entity_id = $res['MediaID'];
                $MediaTypeID = $res['MediaTypeID'];
                $module_id = 21;
                $module_entity_id = $entity_id;
                $LogType = 'MediaDislike';
                if($action_type == 'LIKE'){
                    $notify_node = true;
                    $LogType = 'MediaLike';
                }
                $type = 'ML';
                break;
            case 'BLOG':
                $entity_id = get_detail_by_guid($entity_guid, 24);
                $module_id = 24;
                $module_entity_id = $entity_id;
                break;
            default:
                $Return['ResponseCode'] = 412;
                $Return['Message'] = sprintf(lang('valid_value'), "Entity Type");
                return $Return;
                break;
        }
        if ($entity_type == 'ALBUM')
        {
            $entity_type = 'ACTIVITY';
        }

        if (empty($entity_id))
        {
            $Return['ResponseCode'] = 412;
            $Return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            return $Return;
        }
        /* End get EntityId */
        $CreatedDate = get_current_date('%Y-%m-%d %H:%i:%s');

        $this->db->select('PostLikeID, StatusID');
        $this->db->where('EntityID', $entity_id);
        $this->db->where('EntityType', $entity_type);
        
        if($entity_type=='ACTIVITY' || $entity_type=='COMMENT')
        {
            $this->db->where('UserID', $user_id);
            $this->db->where('PostAsModuleID', $post_as_module_id);
            $this->db->where('PostAsModuleEntityID', $post_as_module_entity_id);
        }
        else
        {
           $this->db->where('UserID', $user_id); 
        }
        
        $this->db->limit(1);
        $query = $this->db->get(POSTLIKE);
        $this->load->model('follow/follow_model');
        if ($query->num_rows() > 0)
        {
            $row = $query->row_array();
            $notify_node = false;
            // case 1 = like already liked a entity --
            if($row['StatusID'] == 2 && $action_post_status == 2){
                $like_count = -1;
                $this->db->where('PostLikeID', $row['PostLikeID'])
                    ->delete(POSTLIKE);
            }else if($row['StatusID'] == 3 && $action_post_status == 3){ // case 2 = dislike already disliked a entity
                $dislike_count = -1;
                $this->db->where('PostLikeID', $row['PostLikeID'])
                    ->delete(POSTLIKE);
            }else if($row['StatusID'] == 3 && $action_post_status == 2){ // case 3 = dislike a liked a entity
                $dislike_count = -1;
                $like_count = 1;
                $StatusID = 2;
                $this->db->where('PostLikeID', $row['PostLikeID'])
                    ->update(POSTLIKE, array('StatusID' => $StatusID, 'ModifiedDate' => $CreatedDate, 'DeviceTypeID' => $this->DeviceTypeID));
                $this->update_dislike_count($entity_id, $entity_type, $dislike_count);
            }else if($row['StatusID'] == 2 && $action_post_status == 3){ // case 4 = liked a disliked a entity
                $dislike_count = 1;
                $like_count = -1;
                $StatusID = 3;
                $this->db->where('PostLikeID', $row['PostLikeID'])
                    ->update(POSTLIKE, array('StatusID' => $StatusID, 'ModifiedDate' => $CreatedDate, 'DeviceTypeID' => $this->DeviceTypeID));
                $this->update_like_count($entity_id, $entity_type, $like_count);
            }

            if($row['StatusID'] != 2){
                if ($entity_type == 'PAGE')
                {
                    // check user already follow that page or not
                    $this->db->select('FollowID');
                    $this->db->where('TypeEntityID', $entity_id);
                    $this->db->where('UserID', $user_id);
                    $this->db->where('Type', 'page');
                    $this->db->limit(1);
                    $query_new = $this->db->get(FOLLOW);
                    if ($query_new->num_rows() == 0)
                    {
                        // follow page when user liked page
                        $data = array('TypeEntityID' => $entity_id, 'UserID' => $user_id, 'Type' => 'page');

                        $this->follow_model->follow($data);
                    }
                }
            }
        } else
        {
            // follow page when user liked page first time
            if ($entity_type == 'PAGE')
            {
                $this->db->select('FollowID');
                $this->db->where('TypeEntityID', $entity_id);
                $this->db->where('UserID', $user_id);
                $this->db->where('Type', 'page');
                $this->db->limit(1);
                $query_new = $this->db->get(FOLLOW);
                if ($query_new->num_rows() == 0)
                {
                    // follow page when user liked page
                    $data = array('TypeEntityID' => $entity_id, 'UserID' => $user_id, 'Type' => 'page');
                    $this->follow_model->follow($data);
                }
            }
            // add status id parameter -- 
            $Input = array('UserID' => $user_id, 'EntityID' => $entity_id, 'EntityType' => $entity_type, 'CreatedDate' => $CreatedDate, 'ModifiedDate' => $CreatedDate, 'DeviceTypeID' => $this->DeviceTypeID, 'EntityOwner' => $entity_owner,'PostAsModuleID'=>$post_as_module_id,'PostAsModuleEntityID'=>$post_as_module_entity_id,'StatusID'=>$action_post_status);
            $this->db->insert(POSTLIKE, $Input);
            $PostLikeID = $this->db->insert_id();
            
            if(!$activity_id) {
                $activity_id = $entity_id;
            }
            
            // Set promotion date if activity is promoted
            $this->load->model(array('activity/activity_front_helper_model'));
            $this->activity_front_helper_model->set_promotion(0, $entity_id, $entity_type);
            
            // Insert activity log and its score
            $this->load->model(array('log/user_activity_log_score_model'));
            $score = $this->user_activity_log_score_model->get_score_for_activity(19, 0, 0, $user_id);
            $user_log_data = array(
            'ModuleID' => 3, 'ModuleEntityID' => $user_id, 'UserID' => $user_id, 'ActivityID' => $activity_id,
            'ActivityTypeID' => 19, 'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID' => 3,
            'PostAsModuleEntityID' => $user_id, 'EntityID' => $PostLikeID, 'Score' => $score,
            );
            $this->user_activity_log_score_model->add_activity_log($user_log_data);
            
            
        }

        // call insert_page_member function to insert follow page userid 
        if ($entity_type == 'PAGE')
        {
            $this->page_model->insert_page_member($entity_id, $user_id);
        }

        // remove old code --
        //$this->update_like_count($entity_id, $entity_type, $Count);
        if($action_post_status == 2){
            $this->update_like_count($entity_id, $entity_type, $like_count);
            $this->update_user_like_recieved_count($user_id, $like_count);
        }else{
            $this->update_dislike_count($entity_id, $entity_type, $dislike_count);
        }

        if ($StatusID == 2)
        {
            save_log($user_id, $LogType, $entity_guid, false, $this->DeviceTypeID);

            if ($entity_type == 'ACTIVITY')
            {
                $NotificationUserList = $this->notification_model->get_notification_user_list($entity_id, $user_id, false);
                $post_tagged_users = $this->get_tagged_user('ACTIVITY', $entity_id);
                if ($NotificationUserList)
                {
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    if ($post_as_module_id == 18 )
                    {
                        $parameters[0]['ReferenceID'] = $post_as_module_entity_id;
                        $parameters[0]['Type'] = 'Page';
                    }
                    $parameters[1]['ReferenceID'] = 1;
                    $parameters[1]['Type'] = 'EntityType';
                    if ($entity_typeID == 5 || $entity_typeID == 6 || $entity_typeID == 13)
                    {
                        $Params = json_decode($Params, true);
                        $AlbumID = get_detail_by_guid($Params['AlbumGUID'], 13);
                        $parameters[1]['ReferenceID'] = 2;
                        $parameters[2]['ReferenceID'] = $AlbumID;
                        $parameters[2]['Type'] = 'Album';
                    }
                    $NotificationTypeID = 3;
                }
                $this->load->helper('activity');
                set_last_activity_date($entity_id, 0, FALSE); // Update the modified date for this ACTIVITY
            } else if ($entity_type == 'MEDIA')
            {
                $NotificationUserList = $this->getMediaOwner($entity_id);
                if ($NotificationUserList)
                {
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['Type'] = 'EntityType';
                    $parameters[1]['ReferenceID'] = 2;
                    $parameters[2]['ReferenceID'] = $entity_id;
                    if ($MediaTypeID == 1)
                    {
                        $parameters[1]['ReferenceID'] = 3;
                        $parameters[2]['Type'] = 'Media';
                    } elseif ($MediaTypeID == 2 || $MediaTypeID == 3)
                    {
                        $parameters[1]['ReferenceID'] = 4;
                        $parameters[2]['Type'] = 'Media';
                    }
                    $NotificationTypeID = 49;
                }
            } elseif ($entity_type == 'COMMENT')
            {
                // $NotificationUserList = $this->getCommentOwnerAndTaggedUsers($entity_id, $user_id);
                $NotificationUserList = $this->get_comment_owner_and_tagged_users($entity_id, $user_id);
                $owner = isset($NotificationUserList['owner'][0]) ? $NotificationUserList['owner'][0] : array();
                $comment_tagged_users = $NotificationUserList = $NotificationUserList['tagged'];
                if(!empty($owner))
                    $NotificationUserList[] = $owner;
                
                if ($NotificationUserList)
                {
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    /*if ($entity_owner == 1 && isset($module_entity_id))
                    {
                        $parameters[0]['ReferenceID'] = $module_entity_id;
                        $parameters[0]['Type'] = 'Page';
                    }*/
                    if ($post_as_module_id == 18 )
                    {
                        $parameters[0]['ReferenceID'] = $post_as_module_entity_id;
                        $parameters[0]['Type'] = 'Page';
                    }
                    $NotificationTypeID = 20;
                    $notify_params = array('Comment' => $entity_id);
                }
                if ($CommentEntityType == 'MEDIA')
                {
                    $NotificationTypeID = 50;
                }
                // get the comment ParentEntity details
                if (!empty($ParentEntityID))
                {
                    $entity = get_detail_by_id($ParentEntityID, 0, "ActivityID, ModuleID, ModuleEntityID", 2);
                    if (!empty($entity))
                    {
                        $entity_id = $entity['ActivityID'];
                        $module_id = $entity['ModuleID'];
                        $module_entity_id = $entity['ModuleEntityID'];
                    }
                }
            }
            $tagged_notify = $comment_tagged_notify = array();
            if ($NotificationUserList)
            {
                $stopNotification = array();
                if (!empty($module_entity_id))
                {
                    $stopNotification = $this->block_user_list($module_entity_id, $module_id);
                }
                //$stopNotification2 = $this->block_user_list($user_id, 3); 
                //$stopNotification = array_unique(array_merge($stopNotification1,$stopNotification2));
                foreach ($NotificationUserList as $key => $value)
                {
                    if (in_array($value, $stopNotification))
                    {
                        unset($NotificationUserList[$key]);
                    }

                    if (isset($post_tagged_users) && !empty($post_tagged_users))
                    {
                        if (in_array($NotificationUserList[$key], $post_tagged_users))
                        {
                            $tagged_notify[] = $NotificationUserList[$key];
                            unset($NotificationUserList[$key]);
                        }
                    }
                    //unset comment tagged users
                    if (isset($comment_tagged_users) && !empty($comment_tagged_users))
                    {
                        if (in_array($NotificationUserList[$key], $comment_tagged_users))
                        {
                            $comment_tagged_notify[] = $NotificationUserList[$key];
                            unset($NotificationUserList[$key]);
                        }
                    }
                }
                if ($NotificationUserList)
                {
                    if(!($NotificationTypeID=='3' && isset($row['StatusID']) && $row['StatusID'] == '2'))
                    {
                        $this->notification_model->add_notification($NotificationTypeID, $user_id, $NotificationUserList, $entity_id, $parameters, true, 0, $notify_params);
                    }
                }
                if ($tagged_notify)
                {
                    $this->notification_model->add_notification(54, $user_id, $tagged_notify, $entity_id, array($parameters[0]));
                }
                if ($comment_tagged_notify)
                {
                    $this->notification_model->add_notification(140, $user_id, $comment_tagged_notify, $entity_id, array($parameters[0]));
                }
            }

            if ($module_id != 3 & !empty($module_entity_id))
            { // Update the last activity date for this ModuleEntityID of ModuleID
                //$this->load->helper('activity');
                //set_last_activity_date($module_entity_id, $module_id); 
            }
        }

        if ($entity_type == 'ACTIVITY' || $entity_type == 'MEDIA' || $entity_type == 'BLOG')
        {
            $like_name = $this->getLikeName($entity_id, $user_id, $entity_owner, array(), $entity_type);
            $Return['LikeName'] = $like_name;
        }

        if ($notify_node)
        {
            $entity_guid = '';
            if ($type == 'CL' || $type == 'PL')
            {
                $entity_guid = get_detail_by_id($entity_id, 0, 'ActivityGUID', 1);
                if ($type == 'CL')
                {
                    $entity_guid = $e_guid;
                }
            }
            notify_node('liveFeed', array('Type' => $type, 'UserID' => $user_id, 'EntityGUID' => $entity_guid));
        }
        if(CACHE_ENABLE)
        {
            $this->cache->delete('article_widgets_'.$user_id);
        }
        return $Return;
    }

    public function get_comment_owner_and_tagged_users($entity_id, $user_id)
    {
        $arr = array('tagged'=>array(),'owner'=>array());
        $this->db->where('PostCommentID', $entity_id);
        $query = $this->db->get(POSTCOMMENTS);
        if ($query->num_rows())
        {
            $row = $query->row();
            preg_match_all('/{{([0-9]+)}}/', $row->PostComment, $matches);
            if (!empty($matches[1]))
            {
                foreach ($matches[1] as $match)
                {
                    $mention = $this->get_mention($match);
                    if ($mention['ModuleID'] == '3')
                    {
                        $arr['tagged'][] = $mention['ModuleEntityID'];
                    }
                    /* if($match!=$user_id){
                      $arr[] = $match;
                      } */
                }
            }
            if (!in_array($row->UserID, $arr) && $user_id != $row->UserID)
            {
                $arr['owner'][] = $row->UserID;
            }
        }
        return $arr;
    }

    public function getCommentOwnerAndTaggedUsers($entity_id, $user_id)
    {
        $arr = array();
        $this->db->where('PostCommentID', $entity_id);
        $query = $this->db->get(POSTCOMMENTS);
        if ($query->num_rows())
        {
            $row = $query->row();
            preg_match_all('/{{([0-9]+)}}/', $row->PostComment, $matches);
            if (!empty($matches[1]))
            {
                foreach ($matches[1] as $match)
                {
                    $mention = $this->get_mention($match);
                    if ($mention['ModuleID'] == '3')
                    {
                        $arr[] = $mention['ModuleEntityID'];
                    }
                    /* if($match!=$user_id){
                      $arr[] = $match;
                      } */
                }
            }
            if (!in_array($row->UserID, $arr) && $user_id != $row->UserID)
            {
                $arr[] = $row->UserID;
            }
        }
        return $arr;
    }

    /**
     * [getMediaOwner Get the media owner details]
     * @param  [type] $MediaID [Media ID]
     */
    public function getMediaOwner($MediaID)
    {
        $arr = array();
        $this->db->select('UserID');
        $this->db->where('MediaID', $MediaID);
        $res = $this->db->get(MEDIA)->row_array();
        if (!empty($res))
        {
            return array($res['UserID']);
        }
        return array();
    }

    /**
     * [update_like_count description]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [string] $entity_type [ACTIVITY, COMMENT]
     * @param  int      $count      [Like Count increment/decrement]
     * @return [type]               [description]
     */
    public function update_like_count($entity_id, $entity_type, $count = 1)
    {
        $set_field = "NoOfLikes";
        $table_name = ACTIVITY;
        $condition = array("ActivityID" => $entity_id);
        switch ($entity_type)
        {
            case 'ACTIVITY':
                $table_name = ACTIVITY;
                $condition = array("ActivityID" => $entity_id);
                break;
            case 'COMMENT':
                $table_name = POSTCOMMENTS;
                $condition = array("PostCommentID" => $entity_id);
                break;
            case 'PAGE':
                $table_name = PAGES;
                $condition = array("PageID" => $entity_id);
                break;
            case 'MEDIA':
                $table_name = MEDIA;
                $condition = array("MediaID" => $entity_id);
                break;
            case 'BLOG':
                $table_name = BLOG;
                $condition = array("BlogID" => $entity_id);
                break;
            default:
                return false;
                break;
        }
        $this->db->where($condition);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        if ($entity_type == "ACTIVITY" && $count == 1)
        {
            //$this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        }
        $this->db->update($table_name);
    }

    /**
     * [update_dislike_count description]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [string] $entity_type [ACTIVITY, COMMENT]
     * @param  int      $count      [Like Count increment/decrement]
     * @return [type]               [description]
     */
    public function update_dislike_count($entity_id, $entity_type, $count = 1)
    {
        $set_field = "NoOfDislikes";
        $table_name = ACTIVITY;
        $condition = array("ActivityID" => $entity_id);
        switch ($entity_type)
        {
            case 'ACTIVITY':
                $table_name = ACTIVITY;
                $condition = array("ActivityID" => $entity_id);
                break;
            case 'COMMENT':
                $table_name = POSTCOMMENTS;
                $condition = array("PostCommentID" => $entity_id);
                break;
            case 'PAGE':
                $table_name = PAGES;
                $condition = array("PageID" => $entity_id);
                break;
            case 'MEDIA':
                $table_name = MEDIA;
                $condition = array("MediaID" => $entity_id);
                break;
            case 'BLOG':
                $table_name = BLOG;
                $condition = array("BlogID" => $entity_id);
                break;
            default:
                return false;
                break;
        }
        $this->db->where($condition);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        if ($entity_type == "ACTIVITY" && $count == 1)
        {
            //$this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        }
        $this->db->update($table_name);
    }

    /**
     * [toggle_sticky To mark Pin or Un-Pin an entity]
     * @param  [array] $data [input data for toggle sticky Request]
     * @return [array]       [array of response code and message]
     */
    function toggle_sticky($data)
    {
        $user_id = $data['UserID'];
        $entity_guid = $data['EntityGUID'];
        $return['Message'] = lang('success');
        $return['ResponseCode'] = 200;
        /* get Entity details by Entity GuID */
        $select_field = "ActivityID, ModuleID, ModuleEntityID";
        $entity = get_detail_by_guid($entity_guid, 0, $select_field, 2);
        if (empty($entity))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "entity GUID");
            return $return;
        }
        $activity_id = $entity['ActivityID'];
        $module_id = $entity['ModuleID'];
        $module_entity_id = $entity['ModuleEntityID'];
        /* End get EntityId */

        /* Check logged in user have permission to mark this entiy as Pin or not */
        $Permission = checkPermission($user_id, $module_id, $module_entity_id, 'Sticky', 3, $user_id);
        if (empty($Permission))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = lang('permission_denied');
            return $return;
        }
        /* End check permission */

        /* Check this entity already marked as Pin or not */
        $sticky_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $this->db->select('ActivityID');
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('IsSticky', 1);
        $this->db->limit(1);
        $query = $this->db->get(ACTIVITY);
        if ($query->num_rows() > 0)
        {  // if yes then mark it as Un Pin
            $sticky_date = NULL;
            $this->db->set('IsSticky', '0');
            $this->db->set('StickyBy', $user_id);
            $this->db->set('StickyDate', $sticky_date);
            $this->db->where('ActivityID', $activity_id);
            $this->db->update(ACTIVITY);
        } else
        { // if not
            /* Check total number of Pin post for this enity Module and ModuleEntityID */
            $this->db->select('ActivityID');
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('IsSticky', 1);
            $this->db->where('StatusID', 2);
            $this->db->order_by('StickyDate', 'ASC');
            $query = $this->db->get(ACTIVITY);
            if ($query->num_rows() >= MAXSTICKYPOST)
            { // if it is greater or equal to MAXSTICKYPOST then Un Pin the first sticky post
                $return['ResponseCode'] = 412;
                $return['Message'] = lang('already_pin_max_no');
                return $return;
            }
            /* End total number of Pin post */

            /* Mark this entity as Pin */
            $this->db->set('IsSticky', 1);
            $this->db->set('StickyDate', $sticky_date);
            $this->db->set('StickyBy', $user_id);
            $this->db->where('ActivityID', $activity_id);
            $this->db->update(ACTIVITY);
            /* End Mark this entity as Pin */
        }
        $return['Data']['StickyDate'] = $sticky_date;
        /* End this entity already marked as Pin or not */
        return $return;
    }

    /**
     * [get_activity_details get activity details]
     * @param  [int] $entity_id       [Entity ID]
     * @param  [int] $activity_type_id [Activity Type ID]
     * @return [array]                 [Activity Details]
     */
    public function get_activity_details($entity_id, $activity_type_id)
    {
        $data = array();
        if ($activity_type_id == 14 || $activity_type_id == 15)
        {
            $table = MEDIA;
            $where = 'MediaID';
            $this->db->where($where, $entity_id);
            $query = $this->db->get($table);
        } 
        else
        {
            $where = 'A.ActivityID';
            $activity['OriginalActivityProfileURL'] = '';
            $this->db->select('M.ModuleName,A.*,ATY.ActivityType,U.FirstName,U.LastName,U.UserGUID,P.Url');
            $this->db->from(ACTIVITY . ' A');
            $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
            $this->db->join(USERS . ' U', 'A.UserID=U.UserID', 'left');
            $this->db->join(PROFILEURL . ' P', 'A.UserID=P.EntityID', 'left');
            $this->db->join(MODULES . ' M', 'M.ModuleID=A.ModuleID', 'left');
            $this->db->where('P.EntityType', 'User');
            $this->db->where($where, $entity_id);
            $this->db->limit(1);
            $query = $this->db->get();
        }

        if ($query->num_rows())
        {
            $row = $query->row();
            $data['ActivityOwner'] = $this->user_model->getUserName($row->UserID);
            $data['ActivityOwner'] = $data['ActivityOwner']['FirstName'] . ' ' . $data['ActivityOwner']['LastName'];
            $data['ActivityOwnerLink'] = get_entity_url($row->UserID, "User", 1);
            $data['UserID'] = $row->UserID;
            $data['ModuleID'] = 3;
            $data['ModuleEntityID'] = 0;
            $data['PostAsModuleID'] = 3;
            $data['PostAsModuleEntityID'] = 0;
            $data['Params'] = '';
            $data['ActivityType'] = '';
            $data['ActivityOwnerFirstName'] = '';
            $data['ActivityOwnerLastName'] = '';
            $data['ActivityOwnerUserGUID'] = '';
            $data['ActivityOwnerProfileURL'] = '';
            $data['SharedEntityGUID'] = '';
            $data['SharedActivityModule'] = '';            
            $data['NoOfLikes'] = $row->NoOfLikes;
            $data['NoOfComments'] = $row->NoOfComments;
            $data['PostType'] = isset($row->PostType) ? $row->PostType : 1 ;
            $data['PostTitle'] = isset($row->PostTitle) ? $row->PostTitle : '' ;
            $data['Privacy'] = isset($row->Privacy) ? $row->Privacy : 1 ;
            $data['CreatedDate'] = $row->CreatedDate;
            $data['ActivityGUID'] = isset($row->ActivityGUID) ? $row->ActivityGUID : $row->MediaGUID ;
            $data['TagedUser'] = isset($row->TagedUser) ? json_decode($row->TagedUser) : '' ;

            if(isset($row->ModuleName))
            {
                $data['SharedActivityModule'] = $row->ModuleName;
            }

            if(isset($row->ModuleID) && isset($row->ModuleEntityID))
            {
                $data['SharedEntityGUID'] = get_detail_by_id($row->ModuleEntityID, $row->ModuleID);
            }

            if (isset($row->ActivityType))
            {
                $data['ActivityType'] = $row->ActivityType;
                $data['ActivityOwnerFirstName'] = $row->FirstName;
                $data['ActivityOwnerLastName'] = $row->LastName;
                $data['ActivityOwnerUserGUID'] = $row->UserGUID;
                $data['ActivityOwnerProfileURL'] = $row->Url;
            }

            if (isset($row->Params))
            {
                $data['Params'] = $row->Params;
            }
            if ($activity_type_id == 14 || $activity_type_id == 15)
            {
                $data['ModifiedDate'] = $row->CreatedDate;
                $data['PostContent'] = '';
                $data['PostTitle'] = '';
                $data['ParentActivityTypeID'] = 1;
                if(isset($row->ActivityID))
                {
                    $data['Files'] = $this->get_activity_files($row->ActivityID);
                }
                else
                {
                    $data['Files'] = array();
                }
                $data['Files'] = $this->get_activity_files($row->MediaSectionReferenceID);
                $data['Album'] = $this->get_albums($row->MediaID, $row->UserID, '', 'Media');
                if (isset($data['Album'][0]))
                {
                    $data['ModuleID'] = $data['Album'][0]['ModuleID'];
                    $data['ModuleEntityID'] = $data['Album'][0]['ModuleEntityID'];
                    $data['PostAsModuleID'] = $data['Album'][0]['ModuleID'];
                    $data['PostAsModuleEntityID'] = $data['Album'][0]['ModuleEntityID'];
                }
            } 
            else
            {
                $data['ModifiedDate'] = $row->ModifiedDate;
                $data['ModuleID'] = $row->ModuleID;
                $data['ModuleEntityID'] = $row->ModuleEntityID;
                $data['PostAsModuleID'] = $row->PostAsModuleID;
                $data['PostAsModuleEntityID'] = $row->PostAsModuleEntityID;
                
                //get Files
                if(isset($row->ActivityID))
                {
                    if($row->ActivityTypeID == '5' || $row->ActivityTypeID == '6')
                    {
                        $params = json_decode($row->Params);
                        $count = 4;
                        if ($row->ActivityTypeID == 6) {
                            $count = $params->count;
                        }
                        $data['Album'] = $this->activity_model->get_albums($row->ActivityID, $row->UserID, $params->AlbumGUID, 'Activity', $count);
                    }
                    else
                    {
                        $data['Album'] = $this->get_albums($row->ActivityID, $row->UserID);
                    }
                    $data['Files'] = $this->get_activity_files($row->ActivityID);
                }
                else
                {
                    $data['Album'] = array();
                    $data['Files'] = array();
                }
                $data['PostTitle'] = $row->PostTitle;
                $data['PostContent'] = $row->PostContent;
                
                $data['PostContent'] = $this->parse_tag($data['PostContent']);

                $data['ParentActivityTypeID'] = $row->ActivityTypeID;
                $data['ActivityGUID'] = $row->ActivityGUID;
            }
        }
        return $data;
    }

    /**
     * Function Name: check_valid_activity_guid
     * @param activity_guid
     * @return boolean
     */
    function check_valid_activity_guid($activity_guid)
    {
        $this->db->select('ActivityGUID');
        $this->db->from(ACTIVITY);
        $this->db->where('ActivityGUID',$activity_guid);
        $query = $this->db->get();
        if($query->num_rows())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Function Name: blockUser
     * @param UserID,EntityID,ModuleID,ModuleEntityID
     * Description: block user for specific user
     */
    public function blockUser($user_id, $entity_id, $module_id, $module_entity_id)
    {
        $this->db->where('EntityID', $entity_id);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $result = $this->db->get(BLOCKUSER);
        if ($result->num_rows())
        {
            $row = $result->row();
/*            $this->db->set('StatusID', '2');
            $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));*/
            $this->db->where('ModuleID', $module_id);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where('UserID', $user_id);
            $this->db->where('EntityID', $entity_id);  /*added by gautam*/
            $this->db->delete(BLOCKUSER);
            $Message ="Member is successfully unblocked."; /*added by gautam*/
        } else
        {
            $data = array('BlockUserGUID' => get_guid(), 'UserID' => $user_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'EntityID' => $entity_id, 'StatusID' => '2', 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->insert(BLOCKUSER, $data);
            $Message ="Member is successfully blocked.";  /*added by gautam*/
        }
        if ($module_id == 1)
        {

            // Delete group notifications
            $this->db->where_in('NotificationTypeID', array(4, 23, 24));
            $this->db->where('(UserID=' . $entity_id . ' OR ToUserID=' . $entity_id . ')');
            $this->db->where('RefrenceID', $module_entity_id);
            $this->db->delete(NOTIFICATIONS);

            // Delete activity
            $this->db->where('ActivityTypeID', 4);
            $this->db->where('ModuleID', '1');
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where('UserID', $entity_id);
            $this->db->delete(ACTIVITY);

            // Remove group relationship
            $this->db->where('GroupID', $module_entity_id);

            $this->db->where('ModuleEntityID', $entity_id);
            $this->db->where('ModuleID', 3);

            $this->db->delete(GROUPMEMBERS);
            
            //initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
        }
        if ($module_id == 3)
        {
            if (CACHE_ENABLE) {
               $this->cache->delete('block_users_'.$user_id);
               $this->cache->delete('block_users_'.$module_entity_id);
            }
            $this->friend_model->deleteFriend($user_id, $entity_id);

            //delete follow relationship                
            $this->db->where('((UserID="' . $user_id . '" AND TypeEntityID="' . $entity_id . '" AND Type="user") OR (TypeEntityID="' . $user_id . '" AND UserID="' . $entity_id . '" AND Type="user"))', NULL, FALSE);
            $this->db->delete(FOLLOW);

            //Delete follow & friends activity
            $this->db->where_in('ActivityTypeID', array(2, 3));
            $this->db->where('ModuleID', '3');
            $this->db->where('((UserID="' . $user_id . '" AND ModuleEntityID="' . $entity_id . '") OR (ModuleEntityID="' . $user_id . '" AND UserID="' . $entity_id . '"))', NULL, FALSE);
            $this->db->delete(ACTIVITY);

            //Delete follow & friends notification
            $this->db->where_in('NotificationTypeID', array(5, 16, 17));
            $this->db->where('((UserID="' . $user_id . '" AND ToUserID="' . $entity_id . '") OR (ToUserID="' . $user_id . '" AND UserID="' . $entity_id . '"))', NULL, FALSE);
            $this->db->delete(NOTIFICATIONS);
        }
        if ($module_id == 18)
        {

            //delete follow relationship                
            $this->db->where('(UserID="' . $entity_id . '" AND TypeEntityID="' . $module_entity_id . '" AND Type="page")', NULL, FALSE);
            $this->db->delete(FOLLOW);
            //echo $this->db->last_query();
            //Hide follow activity
            $this->db->where('ActivityTypeID', '3');
            $this->db->where('ModuleID', '18');
            $this->db->where('(UserID="' . $entity_id . '" AND ModuleEntityID="' . $module_entity_id . '")', NULL, FALSE);
            $this->db->delete(ACTIVITY);

            // Update Follower count in page table by 1
            $this->db->where('PageID', $module_entity_id);
            $this->db->set('NoOfFollowers', 'NoOfFollowers-1', FALSE);
            $this->db->update(PAGES);

            $this->db->where('UserID', $entity_id);
            $this->db->where('PageID', $module_entity_id);
            $this->db->update(PAGEMEMBERS, array('StatusID' => 3, 'ModuleRoleID' => 9, 'JoinedAt' => get_current_date('%Y-%m-%d %H:%i:%s')));
        }
        if ($module_id == 14)
        {

            //delete attend relationship
            $this->db->where('EventID', $module_entity_id);
            $this->db->where('UserID', $entity_id);
            $this->db->delete(EVENTUSERS);
        }
        return $Message;    /*added by gautam*/
    }

    /**
     * Function Name: approveFlagActivity
     * @param EntityGUID - Entity GUID
     * @return Success / Failure Message and Response Code
     */
    public function approveFlagActivity($user_id, $activity_id)
    {
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(ACTIVITY);
        $this->db->where('ActivityID', $activity_id);
        $query = $this->db->get();

        if ($query->num_rows())
        {
            $row = $query->row();
            if (checkPermission($user_id, $row->ModuleID, $row->ModuleEntityID, 'IsOwner', 3, $user_id))
            {
                $this->db->set('Flaggable', '0');
                $this->db->where('ActivityID', $activity_id);
                $this->db->update(ACTIVITY);
                $data['Message'] = lang('success');
                $data['ResponseCode'] = 200;
                return $data;
            }
        }
        $data['Message'] = lang('permission_denied');
        $data['ResponseCode'] = 412;
        return $data;
    }

    /**
     * Function Name: autoSuggestPostOwner
     * @param UserID
     * @param SearchKey
     * @return array | List of users
     */
    public function autoSuggestPostOwner($user_id, $search_key, $module_id = 0, $module_entity_id = 0, $AllActivity = 0, $filter_type = 0)
    {
        if (!$module_entity_id)
        {
            $module_entity_id = $user_id;
        }
        //$blocked_users = $this->block_user_list($user_id);
        // Get all joined/admin/self group
        $this->group_model->set_user_group_list($user_id);
        $group_list = $this->group_model->get_user_group_list();
        $group_list[] = 0;
        $group_list = implode(',', $group_list);
        // Get all follow/self pages
        $this->page_model->set_user_pages_list($user_id);
        $page_list = $this->page_model->get_user_pages_list();

        $this->event_model->set_user_joined_events($user_id);
        $event_list = $this->event_model->get_user_joined_events();

        $blocked_users = $this->blocked_users;

        $this->user_model->set_friend_followers_list($user_id);
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }
        if ($module_id == 1)
        {
            $activity_type_allow = array(7, 5, 6, 25);//23, 24,
        } 
        else if ($module_id == 14)
        {
            $activity_type_allow = array(11, 5, 6);//23, 24
        } 
        else if ($module_id == 18)
        {
            $activity_type_allow = array(1, 8, 12, 5, 6, 25);//23, 24,
        } 
        else
        {
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 25);//23, 24,
        }

        $modules_allowed = array(3, 30);

        $condition_array = array();
        $condition = '';
        $wall_user_condition = '';

        if ($privacy_options)
        {
            foreach ($privacy_options as $key => $val)
            {
                if ($key == 'g' && $val == '0')
                {
                    $modules_allowed[] = 1;
                }
                if ($key == 'e' && $val == '0')
                {
                    $modules_allowed[] = 14;
                }
                if ($key == 'p' && $val == '0')
                {
                    $modules_allowed[] = 18;
                }
                if ($key == 'm')
                {
                    if ($val == '1')
                    {
                        $show_media = FALSE;
                        unset($activity_type_allow[array_search('5', $activity_type_allow)]);
                        unset($activity_type_allow[array_search('6', $activity_type_allow)]);
                    }
                }
                if ($key == 'r' && $val == '0')
                {
                    $activity_type_allow[] = 16;
                    $activity_type_allow[] = 17;
                }
                if ($key == 's' && $val == '0')
                {
                    if ($filter_type == '0' && empty($mentions))
                    {
                       // $show_suggestions = true;
                    }
                }
            }
        }

        if ($friend_followers_list != '' && $AllActivity == 1)
        {
            $condition_array[] = "
                IF(A.ActivityTypeID=25 OR A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6 OR (A.ActivityTypeID=23 AND A.ModuleID=3) OR (A.ActivityTypeID=24 AND A.ModuleID=3), (
                    A.UserID IN(" . $friend_followers_list . ") OR A.ModuleEntityID IN(" . $friend_followers_list . ") OR A.ModuleEntityID=" . $user_id . " 
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (

                    (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR A.ModuleEntityID=" . $user_id . "
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (A.UserID=" . $user_id . " OR A.ModuleEntityID=" . $user_id . "
                ), '' )";
        } 
        else
        {
            $condition_array[] = "(A.ModuleID='" . $module_id . "' AND A.ModuleEntityID='" . $module_entity_id . "')";
        }

        // Check parent activity privacy for shared activity
        $privacy_condition = "
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                IF(A.ActivityTypeID IN(9,10),
                    A.ParentActivityID=(
                        SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                            IF(Privacy=2 AND ActivityTypeID!=7,UserID IN (" . $friend_followers_list . "),false) OR
                            IF(Privacy=3 AND ActivityTypeID!=7,UserID IN (" . implode(',', $friends) . "),false) OR
                            IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $user_id . "',false) OR
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $group_list . "),false))
                    ),false
                )
                OR
                IF(A.ActivityTypeID IN(14,15),
                    A.ParentActivityID=(
                        SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                    ),false
                )
            ),         
        true)";

        if ($privacy_condition)
        {

            if ($module_id == 3)
            {
                $wall_user_condition = " AND A.UserID=" . $user_id;
                $permission_cond = "IF(TRUE,TRUE,FALSE)";
                $pc = "(
                        IF(A.UserID=A.ModuleEntityID," . $privacy_condition . "," . $permission_cond . ")
                    )";
                $this->db->where($pc, null, false);
            } 
            else
            {
                $this->db->where($privacy_condition, null, false);
            }
        }

        if ($module_id == 3)
        {

            if (!empty($group_list))
            {
                $condition_array[] = "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ")" . $wall_user_condition . "
                    ), '' )";
            }
            if (!empty($page_list))
            {
                $condition_array[] = "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17, (
                        A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ")" . $wall_user_condition . "
                    ), '' )";
            }
            if (!empty($event_list))
            {
                $condition_array[] = "IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
                        A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")" . $wall_user_condition . "
                    ), '' )";
            }
        }
        $this->db->select('U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture', FALSE);
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');

        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);

        //$this->db->where('A.StatusID', '2');
        if ($filter_type == 7)
        {
            $condition_array[]=" A.StatusID =19 ";
            $this->db->where('A.DeletedBy', $user_id);
        } 
        elseif ($filter_type == 4 && !$this->settings_model->isDisabled(43))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
            $this->db->_protect_identifiers = TRUE;
        } 
        else
        {
            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,19),A.StatusID=2)", null, false);
            $this->db->where("(A.UserID='" . $user_id . "' OR A.Privacy IN(1,2,3,4) )", NULL, FALSE);
        }

        if (!empty($condition_array))
        {
            $condition = "(" . implode(' OR ', $condition_array) . ")";
        }
        
        if (!empty($condition))
        {
            $this->db->where($condition, NULL, FALSE);
        }
        if (!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%")', NULL, FALSE);
        }

        if (!empty($blocked_users))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }

        $this->db->group_by('U.UserGUID');
        $result = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($result->num_rows())
        {
            return $result->result_array();
        }
        return array();
    }

    /**
     * [getLikeName Used to get user name who likes Activity]
     * @param  [int]  $activity_id  [Activity ID]
     * @param  [int]  $user_id      [User ID]
     * @param  [int] $entity_owner  [EntityOwner]
     * @return [array]             [description]
     */
    public function getLikeName($activity_id, $user_id, $entity_owner = 2, $blocked_users = array(), $entity_type = 'ACTIVITY')
    {
        $pagerow = array();
        //if($entity_owner==1){
        $this->db->select('P.Title,P.PageURL');
        $this->db->from(PAGES . ' P');
        $this->db->join(ACTIVITY . ' A', 'A.ModuleEntityID=P.PageID', 'left');
        $this->db->where('A.ActivityID', $activity_id);
        $pagequery = $this->db->get();
        if ($pagequery->num_rows())
        {
            $pagerow = $pagequery->row();
        }
        // }
        $this->db->select('count( `PL`.`PostLikeID` ) AS totalLike, group_concat( `U`.`UserID`) AS UserID');
        $this->db->from(POSTLIKE . ' PL');
        $this->db->join(USERS . ' U', 'U.UserID=PL.UserID AND U.StatusID NOT IN(3,4)');
        $this->db->where('PL.EntityType', $entity_type);
        $this->db->where('PL.EntityID', $activity_id);
        $this->db->where('PL.StatusID', '2');
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('PL.UserID', $blocked_users);
        }
        $this->db->group_by('PL.EntityID');
        $query = $this->db->get();

        $like_name = 'You';
        //$like_link = get_entity_url($user_id, "User", 1);
        $like_link = $this->user_model->get_user_profile_url($user_id);
        $is_like = 0;
        $like_module_id = 3;

        if ($query->num_rows() > 0)
        {
            $row = $query->row();
            $explode = explode(',', $row->UserID);
            if (($user_id && in_array($user_id, $explode)) || $entity_owner == 1)
            {
                $like_name = 'You';
                // $like_link = get_entity_url($user_id, "User", 1);
                $is_like = 1;
                $like_module_id = 3;


                $this->db->select('U.FirstName, U.LastName, PL.EntityOwner');
                $this->db->from(USERS . ' U');
                $this->db->join(POSTLIKE . ' PL', 'PL.UserID=U.UserID AND PL.StatusID=2');
                $this->db->where('PL.EntityType', $entity_type);
                $this->db->where('PL.EntityID', $activity_id);
                $this->db->where('PL.UserID', $user_id);
                //$this->db->where('PL.EntityOwner','1');
                $this->db->where('U.StatusID', '2');
                if (!empty($blocked_users))
                {
                    $this->db->where_not_in('PL.UserID', $blocked_users);
                }
                //$this->db->limit(1);
                $query = $this->db->get();
                $totalRecords = $query->num_rows();
                if ($totalRecords && $totalRecords < 2)
                {
                    foreach ($query->result_array() as $row)
                    {
                        $is_like = 1;
                        if ($row['EntityOwner'] == 1 && $pagerow)
                        {
                            $like_name = $pagerow->Title;
                            $like_link = $pagerow->PageURL;
                            $like_module_id = 18;
                        }
                        if ($row['EntityOwner'] != $entity_owner && $entity_owner == 1)
                        {
                            $is_like = 0;
                        }
                        if ($row['EntityOwner'] != $entity_owner && $entity_owner == 0)
                        {
                            $is_like = 0;
                        }
                        if ($row['EntityOwner'] == $entity_owner)
                        {
                            break;
                        }
                    }
                }
            } 
            else
            {
                $this->db->select('U.FirstName,U.LastName, U.UserID, PL.EntityOwner,PU.Url as ProfileURL');
                $this->db->from(USERS . ' U');
                $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
                $this->db->join(POSTLIKE . ' PL', 'PL.UserID=U.UserID AND PL.StatusID=2');
                $this->db->where('PL.EntityType', $entity_type);
                $this->db->where('PL.EntityID', $activity_id);
                $this->db->where('U.StatusID', '2');
                if (!empty($blocked_users))
                {
                    $this->db->where_not_in('PL.UserID', $blocked_users);
                }
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows())
                {
                    $row = $query->row();
                    $like_name = $row->FirstName . ' ' . $row->LastName;
                    $like_link = $row->ProfileURL; //get_entity_url($row->UserID, "User", 1);
                    $like_module_id = 3;
                    if ($row->EntityOwner == 1 && $pagerow)
                    {
                        $like_name = $pagerow->Title;
                        $like_link = $pagerow->PageURL;
                        $like_module_id = 18;
                    }
                }
            }
        }
        return array('Name' => $like_name, 'ProfileURL' => $like_link, 'IsLike' => $is_like, 'ModuleID' => $like_module_id);
    }

    /**
     * [get_last_comment Get last comment of an activity]
     * @param  [int] $activity_id [Activity ID]
     * @param  [int] $user_id     [User ID]
     * @return [array]            [Comment details]
     */
    public function get_last_comment($activity_id, $user_id,$entity_type='ACTIVITY')
    {
        $this->db->select('*');
        $this->db->from(POSTCOMMENTS);
        $this->db->where('EntityType',$entity_type);
        $this->db->where('EntityID', $activity_id);
        $this->db->where('UserID', $user_id);
        $this->db->order_by('PostCommentID', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->row_array();
        }
    }

    /**
     * [get_recent_activities Used to get the user recent activity]
     * @param  [int] $user_id         [User ID]
     * @param  [int] $current_user_id [Logged in User ID]
     * @return [array]                [Activity Details]
     */
    public function get_recent_activities($user_id, $current_user_id)
    {
        $inner_sql = array();

        $data = array();

        $this->load->model('users/user_model');
        $friend_followers_list = $this->user_model->gerFriendsFollowersList($current_user_id, true, 1);
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = 0;
        $follow[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($current_user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $current_user_id;
        }

        $activity_type_allow = array(1, 2, 3, 4, 7, 9, 10, 11, 12, 14, 15);
        $case_array=array();
        $case_array[]="tbl.ModuleID=1 
                        THEN 
                            tbl.ModuleEntityID IN 
                            (SELECT G.GroupID FROM Groups G LEFT JOIN GroupMembers GM ON G.GroupID=GM.GroupID WHERE GM.ModuleID='3' AND GM.ModuleEntityID='18' AND GM.StatusID='2')
                            OR 
                            tbl.ModuleEntityID IN 
                            ( SELECT GroupID FROM Groups WHERE IsPublic='1' )  
                        ";
        /*$conditions[] = "
            IF(tbl.ModuleID=1,
                tbl.ModuleEntityID IN (SELECT G.GroupID FROM " . GROUPS . " G LEFT JOIN " . GROUPMEMBERS . " GM ON G.GroupID=GM.GroupID WHERE GM.ModuleID='3' AND GM.ModuleEntityID='" . $current_user_id . "' AND GM.StatusID='2') OR
                tbl.ModuleEntityID IN (SELECT GroupID FROM " . GROUPS . " WHERE IsPublic='1')
            ,'')
        ";*/
        $case_array[]="tbl.ModuleID=3
                        THEN 
                            (CASE 
                                    WHEN tbl.Privacy=1
                                    THEN
                                        true
                                    WHEN tbl.Privacy=2
                                    THEN
                                        tbl.ModuleEntityID IN ('" . implode("','", $friend_followers_list) . "')
                                    WHEN tbl.Privacy=3
                                    THEN
                                        tbl.ModuleEntityID IN ('" . implode("','", $friends) . "')
                                    WHEN tbl.Privacy=4
                                    THEN
                                        tbl.ModuleEntityID='" . $current_user_id . "'
                                ELSE
                                ''
                            END
                            )";
        /*$conditions[] = "
            IF(tbl.ModuleID=3,
                IF(tbl.Privacy=1,true,'') OR
                IF(tbl.Privacy=2,tbl.ModuleEntityID IN ('" . implode("','", $friend_followers_list) . "'),'') OR
                IF(tbl.Privacy=3,tbl.ModuleEntityID IN ('" . implode("','", $friends) . "'),'') OR
                IF(tbl.Privacy=4,tbl.ModuleEntityID='" . $current_user_id . "','')
            ,'')
        ";*/
        $case_array[]="tbl.ModuleID=14
                        THEN
                            tbl.ModuleEntityID IN (SELECT E.EventID FROM Events E LEFT JOIN EventUsers EU ON E.EventID=EU.EventID WHERE EU.UserID='18' AND EU.Presence='ATTENDING')
                       ";
        /*$conditions[] = "
            IF(tbl.ModuleID=14,
                tbl.ModuleEntityID IN (SELECT E.EventID FROM " . EVENTS . " E LEFT JOIN " . EVENTUSERS . " EU ON E.EventID=EU.EventID WHERE EU.UserID='" . $current_user_id . "' AND EU.Presence='ATTENDING')
            ,'')
        ";*/
        $case_array[]="tbl.ModuleID=18
                        THEN
                            true
                        ";
       /* $conditions[] = "
            IF(tbl.ModuleID=18,
                true
            ,'')
        ";*/

        //$conditions = implode(' or ', $conditions);
        if(!empty($case_array))
        {
            $conditions= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        //get like activities of user
        $inner_sql[] = "SELECT A.Privacy,'Comment' as Type,PC.UserID,PC.CreatedDate,A.ModuleID,A.ModuleEntityID,A.ActivityGUID, A.ActivityID, A.ActivityTypeID, A.StatusID FROM " . ACTIVITY . " A LEFT JOIN " . POSTCOMMENTS . " PC ON A.ActivityID=PC.EntityID AND PC.EntityType='Activity'";

        $inner_sql[] = "SELECT A.Privacy,'Like' as Type,PL.UserID,PL.CreatedDate,A.ModuleID,A.ModuleEntityID,A.ActivityGUID,A.ActivityID,A.ActivityTypeID, A.StatusID FROM " . ACTIVITY . " A  JOIN " . POSTLIKE . " PL ON A.ActivityID=PL.EntityID AND PL.EntityType='ACTIVITY'";

        $inner_sql[] = "SELECT Privacy,'Activity' as Type,UserID,CreatedDate,ModuleID,ModuleEntityID,ActivityGUID,ActivityID,ActivityTypeID, StatusID FROM " . ACTIVITY;

        $inner_sql = implode(" UNION ", $inner_sql);


        /*$var = "
            SELECT A.Privacy,'' as Type,UserID,ActivityDate,ModuleID,ModuleEntityID,A.ActivityGUID,A.ActivityID,A.ActivityTypeID,A.StatusID
            FROM 
        ";*/

        $sql = "SELECT * FROM (
            " . $inner_sql . "
            ) tbl
            WHERE tbl.UserID='" . $user_id . "' AND StatusID=2 AND
            tbl.ActivityTypeID IN('" . implode("','", $activity_type_allow) . "')
            AND (" . $conditions . ")
            ORDER BY tbl.CreatedDate DESC LIMIT 0,5
        ";

        $query = $this->db->query($sql);
        // echo $this->db->last_query();die;
        if ($query->num_rows())
        {
            $this->load->model('notification_model');
            $name = $this->user_model->getUserName($user_id);
            $ProfilePicture = $name['ProfilePicture'];
            $name = $name['FirstName'] . ' ' . $name['LastName'];
            $isAre = '';

            if ($user_id == $current_user_id)
            {
                $name = lang('you');
                $isAre = '';
            }
            $this->load->model('group/group_model');
            foreach ($query->result_array() as $result)
            {
                $arr = array();
                $arr['Message'] = '';
                $message_part = '';
                $post_type = 'post';
                $module_id = $result['ModuleID'];
                $module_entity_id = $result['ModuleEntityID'];
                if ($module_id == 1)
                {
                    $entity = $this->group_model->get_group_details_by_id($module_entity_id);                    
                    
                    if ($entity)
                    {
                        $entity_name = $entity['GroupName'];
                        $entity_guid = $entity['GroupGUID'];
                        $entity_type = 'group';
                        $profile_url = site_url($this->group_model->get_group_url($module_entity_id, $entity['GroupNameTitle'], false, 'index'));
                        $message_part = ' ' . lang('for') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '</a>';
                    } else
                    {
                        continue;
                    }
                }
                if ($module_id == 14)
                {
                    $entity = get_detail_by_id($module_entity_id, 14, " Title, EventGUID", 2);
                    if ($entity)
                    {
                        $entity_name = $entity['Title'];
                        $entity_guid = $entity['EventGUID'];
                        $entity_type = 'event';
                        
                        $profile_url = $this->event_model->getViewEventUrl($entity_guid, $entity_name, false, 'wall');
                        $profile_url = site_url($profile_url);
                        $message_part = ' ' . lang('for') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '</a>';
                    } else
                    {
                        continue;
                    }
                }
                if ($module_id == 18)
                {
                    $entity = get_detail_by_id($module_entity_id, 18, " Title, PageGUID, PageURL", 2);
                    if ($entity)
                    {
                        $entity_name = $entity['Title'];
                        $entity_guid = $entity['PageGUID'];
                        $entity_type = 'page';
                        $profile_url = site_url() . 'page/' . $entity['PageURL'];
                        $message_part = ' ' . lang('for') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '</a>';
                    } else
                    {
                        continue;
                    }
                }
                if ($module_id == 3)
                {
                    $entity = $this->user_model->getUserName($module_entity_id);

                    $entity_name = $entity['FirstName'] . ' ' . $entity['LastName'];
                    $entity_guid = $entity['ModuleEntityGUID'];
                    $entity_type = 'user';
                    $profile_url = $entity['ProfileURL'];
                }

                $activityOwnerdetails = $this->getActivityOwnerdetails($result['ActivityID']);
                $activityOwnerName = $activityOwnerdetails['Name'];
                $activityOwner = $activityOwnerdetails['UserID'];
                $activity_owner_guid = $activityOwnerdetails['UserGUID'];

                $activityOwnerName = '<a class="loadbusinesscard" entityguid="' . $activity_owner_guid . '" entitytype="user" href="' . get_entity_url($activityOwner) . '">' . $activityOwnerName . '\'s</a>';
                if ($activityOwner == $user_id && $user_id!=$current_user_id)
                {
                    $activityOwnerName = $this->notification_model->get_gender($user_id);
                } else if ($activityOwner == $current_user_id)
                {
                    $activityOwnerName = lang('your');
                }

                switch ($result['Type'])
                {
                    case 'Like':
                        $arr['Message'] = $name . ' ' . $isAre . ' ' . lang('liked') . ' ' . $activityOwnerName . ' <a href="' . get_single_activity_url($result['ActivityID']) . '">' . $post_type . '</a>' . $message_part;
                        break;
                    case 'Comment':
                        $arr['Message'] = $name . ' ' . $isAre . ' ' . lang('commented') . ' ' . lang('on') . ' ' . $activityOwnerName . ' <a href="' . get_single_activity_url($result['ActivityID']) . '">' . $post_type . '</a>' . $message_part;
                        break;
                    case 'Activity':
                        switch ($result['ActivityTypeID'])
                        {
                            case '1':
                            case '7':
                            case '8':
                            case '11':
                            case '12':
                                if ($module_id == 3)
                                {
                                    $message_part = ' ' . lang('on') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '\'s</a> ' . strtolower(lang('wall'));
                                }
                                $profile_url = get_single_activity_url($result['ActivityID']);
                                $arr['Message'] = $name . ' ' . lang('posted') . ' ' . lang('new') . ' <a href="' . $profile_url . '">' . lang('status') . '</a>';
                                if ($module_id == 1 || $module_id == 18 || $result['ActivityTypeID'] == 8)
                                {
                                    $arr['Message'] = $name . ' <a href="' . $profile_url . '">' . lang('posted') . '</a>' . $message_part;
                                }
                                break;
                            case '2':
                                $arr['Message'] = $name . ' ' . $isAre . ' ' . lang('now_friend') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '</a>';
                                break;
                            case '3':
                                $arr['Message'] = $name . ' ' . $isAre . ' ' . lang('now_following') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '</a>';
                                break;
                            case '4':
                                $arr['Message'] = $name . ' ' . lang('joined') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '</a>';
                                break;
                            case '10':
                            case '15':
                                $profile_url = get_single_activity_url($result['ActivityID']);
                                $arr['Message'] = $name . ' shared ' . $activityOwnerName . ' <a href="' . $profile_url . '">' . $post_type . '</a>';
                                break;
                            case '9':
                            case '14':
                                $profile_url = get_single_activity_url($result['ActivityID']);
                                if ($module_id == 3)
                                {
                                    $message_part = ' ' . lang('on') . ' <a class="loadbusinesscard" entityguid="' . $entity_guid . '" entitytype="' . $entity_type . '" href="' . $profile_url . '">' . $entity_name . '\'s</a> ' . strtolower(lang('wall'));
                                }
                                $arr['Message'] = $name . ' shared ' . $activityOwnerName . ' <a href="' . $profile_url . '">' . $post_type . '</a>' . $message_part;
                                break;
                            default:
                                # code...
                                break;
                        }
                        break;
                    default:
                        # code...
                        break;
                }
                $arr['ActivityGUID'] = $result['ActivityGUID'];
                if ($arr['Message'])
                {
                    $data[] = $arr;
                }
            }
        }
        return $data;
    }

    /**
     * [is_archive Used to check activity archived by user or not]
     * @param  [int]  $user_id     [User ID]
     * @param  [int]  $activity_id [Activity ID]
     * @return [int]               [description]
     */
    function is_archive($user_id, $activity_id) {
        if($this->settings_model->isDisabled(43)) {
            return 0;
        }
        $this->db->select('ArchiveID, Status');
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get(ARCHIVEACTIVITY);
        if ($query->num_rows())
        {
            $row = $query->row()->Status;
            if ($row == 'ARCHIVED')
            {
                return 1;
            } else
            {
                return 2;
            }
        } 
        else
        {
            $this->db->select('ReminderID,Status,');
            $this->db->from(REMINDER);
            $this->db->where('ActivityID', $activity_id);
            $this->db->where('UserID', $user_id);
            $query = $this->db->get();
            if ($query->num_rows())
            {
                $row = $query->row()->Status;
                if ($row == 'ARCHIVED')
                {
                    return 1;
                }
            }
            return 0;
        }
        return 0;
    }

    /**
     * [set_user_activity_archive Used to set all user archive activity ]
     * @param  [int]  $user_id     [User ID]
     * @param  [int]  $activity_id [Activity ID]
     * @return [int]               [description]
     */
    function set_user_activity_archive($user_id) {
        if($this->settings_model->isDisabled(43)) {
            return $this->user_activity_archive;
        }
        $this->db->select('ArchiveID, Status,ActivityID');
        $this->db->where('UserID', $user_id);
        $this->db->where('Status','ARCHIVED');
        $query = $this->db->get(ARCHIVEACTIVITY);
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $result)
            {
                if ($result['Status'] == 'ARCHIVED')
                {
                    $this->user_activity_archive[$result['ActivityID']] = 1;
                } 
                else
                {
                    $this->user_activity_archive[$result['ActivityID']] = 2;
                }
            }
        }

        $this->db->select('ReminderID,Status,ActivityID');
        $this->db->from(REMINDER);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $result)
            {
                $row = $query->row()->Status;
                if ($result['Status'] == 'ARCHIVED')
                {
                    $this->user_activity_archive[$result['ActivityID']] = 1;
                }
            }
        }
        return $this->user_activity_archive;
    }

    /**
     * 
     * @return type
     */
    function get_user_activity_archive()
    {
        return $this->user_activity_archive;
    }

    /**
     * [toggle_archive Used to archive / un-archive an entity]
     * @param  [int] $user_id     [User ID]
     * @param  [int] $activity_id [Activity ID]
     * @return [type]              [description]
     */
    function toggle_archive($user_id, $activity_id) {
        //Insert record in ArchiveActivity
        $data = 0;
        if($this->settings_model->isDisabled(43)) {
            return $data;
        }
        $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $this->db->select('ArchiveID');
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('Status', 'ARCHIVED');
        $query = $this->db->get(ARCHIVEACTIVITY);
        if ($query->num_rows())
        {
            $archive_details = $query->row_array();
            /* $this->db->where('ArchiveID',$archive_details['ArchiveID']);
              $this->db->delete(ARCHIVEACTIVITY); */

            $this->db->where('ArchiveID', $archive_details['ArchiveID']);
            $this->db->update(ARCHIVEACTIVITY, array('Status' => "UNARCHIVED", 'ModifiedDate' => $created_date));

            if (!$this->settings_model->isDisabled(28))
            {
                $this->db->where('UserID', $user_id);
                $this->db->where('ActivityID', $activity_id);
                $this->db->delete(REMINDER);
            }
        } 
        else
        {
            $this->db->insert(ARCHIVEACTIVITY, array('ActivityID' => $activity_id, 'UserID' => $user_id, 'Status' => 'ARCHIVED', 'CreatedDate' => $created_date, 'ModifiedDate' => $created_date));
            $data = 1;
        }
        return $data;
    }

    /**
     * [update_album_activity Used to update album activity]
     * @param  [int] $album_id         [Album ID]
     * @param  array $param            [Additional Information]
     * @param  [int] $commentable      [Commentable or not]
     * @param  [int] $visibility       [Visibility]
     * @param  [int] $activity_type_id [Activity Type ID]
     */
    function update_album_activity($album_id, $param = array(), $commentable = '3', $visibility = '', $activity_type_id = 6)
    {
        $album = get_detail_by_id($album_id, 13, 'ActivityID', 2);
        $activity_id = $album['ActivityID'];
        $this->db->where('ActivityID', $activity_id);
        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->set('LastActionDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->set('ActivityTypeID', $activity_type_id);
        if ($commentable != '3')
        {
            $this->db->set('IsCommentable', $commentable);
        }
        if (!empty($visibility))
        {
            $this->db->set('Privacy', $visibility);
        }
        $this->db->set('Params', json_encode($param));
        $this->db->update(ACTIVITY);
    }

    /**
     * [add_mention Used to add mention]
     * @param  [int] $module_entity_id  [Module Entity ID]
     * @param  [int] $module_id         [Module ID]
     * @param  [int] $comment_id        [Comment ID]
     * @param  [int] $activity_id       [Activity ID]
     * @param  [string] $tagged_title   [Tagged Title]
     */
    function add_mention($module_entity_id, $module_id, $activity_id, $tagged_title, $comment_id = 0, $type = 1)
    {
        if (!$activity_id)
        {
            $this->db->select('EntityType,EntityID');
            $this->db->from(POSTCOMMENTS);
            $this->db->where('PostCommentID', $comment_id);
            $query = $this->db->get();
            $row = $query->row_array();
            $activity_id = 0;
            switch ($row['EntityType'])
            {
                case 'ACTIVITY':
                    $activity_id = $row['EntityID'];
                    break;
                case 'MEDIA':
                    $this->db->select('A.ActivityID');
                    $this->db->from(MEDIA . ' M');
                    $this->db->join(ALBUMS . ' A', 'M.AlbumID=A.AlbumID', 'left');
                    $this->db->where('M.MediaID', $row['EntityID']);
                    $comment_query = $this->db->get();
                    if ($comment_query->num_rows())
                    {
                        $activity_id = $comment_query->row()->ActivityID;
                    }
                    break;
            }
        }

        $mention_id = $this->check_mention_exists($module_entity_id, $module_id, $activity_id,$type,$comment_id);

        if(empty($mention_id))
        {
            $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
            $insert = array('ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id, 'ActivityID' => $activity_id, 'PostCommentID' => $comment_id, 'Title' => $tagged_title, 'CreatedDate' => $created_date, 'Type' => $type);
            //print_r($insert);die;
            $this->db->insert(MENTION, $insert);
            $mention_id = $this->db->insert_id();

            $user_id = $this->UserID;
            if ($module_id == 3)
            {
                if (!$this->settings_model->isDisabled(28))
                {
                    $this->db->where('UserID', $module_entity_id);
                    $this->db->where('ActivityID', $activity_id);
                    $this->db->update(REMINDER, array('Status' => "ACTIVE", 'ModifiedDate' => $created_date));
                }
                if (!$this->settings_model->isDisabled(43))
                {
                    $this->db->where('UserID', $module_entity_id);
                    $this->db->where('ActivityID', $activity_id);
                    $this->db->update(ARCHIVEACTIVITY, array('Status' => "UNARCHIVED", 'ModifiedDate' => $created_date));
                    //$this->db->delete(ARCHIVEACTIVITY);
                }
            }
        }
        return $mention_id;
    }

    function check_mention_exists($module_entity_id, $module_id, $activity_id,$type,$comment_id=0)
    {
        $this->db->select('MentionID');
        $this->db->from(MENTION);
        $this->db->where('ModuleID',$module_id);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->where('ActivityID',$activity_id);
        $this->db->where('Type',$type);
        if($comment_id)
        {
           $this->db->where('PostCommentID',$comment_id); 
        }
        $Query = $this->db->get();
        
        if($Query->num_rows()>0)
        {
            $res = $Query->row_array();
            
            return $res['MentionID'];
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * [get_mention Used to get mention details]
     * @param  [int] $mention_id [Mention ID]
     * @return [array]           [Mention details]
     */
    function get_mention($mention_id)
    {
        $this->db->select('ModuleID, ModuleEntityID, ActivityID, PostCommentID, Title');
        $this->db->where('MentionID', $mention_id);
        $query = $this->db->get(MENTION);
        $result = array();
        if ($query->num_rows())
        {
            $result = $query->row_array();
            $module_entity_id = $result['ModuleEntityID'];
            $module_id = $result['ModuleID'];
            if ($module_id == 18)
            {
                $result['ProfileURL'] = 'page/' . get_guid_by_id($module_entity_id, $module_id, "PageURL", 1);
            }
            if ($module_id == 3)
            {
                $result['ProfileURL'] = get_entity_url($module_entity_id, "User", 1);
            }
        }
        return $result;
    }


    /**
     * [get tagged entity list]
     * @param  [string] $ActivityID [ActivityID] or [string] $PostCommentID [PostCommentID]
     * @return [Array]               
     */
    function get_tagged_entity($ActivityID='', $PostCommentID=''){
        $ID = $ActivityID;
        if($ActivityID==''){$ID = $PostCommentID;}
        if($ID==''){return array();}

        $query="SELECT
        M.ModuleID,
        M.Title,
        IF(M.ModuleID=3, (SELECT U.UserGUID FROM `Users` U WHERE U.UserID=M.ModuleEntityID),'') AS EntityGUID
        FROM `Mention` M
        WHERE M.ActivityID=$ID
        ORDER BY M.MentionID ASC
        ";
            $result = $this->db->query($query);
            if($result->num_rows())
            {
                return $result->result_array();
            }
            else
            {
                return array();
            }
    }



    /**
     * [parse_tag Used to parse tagged value from existing post content]
     * @param  [string] $post_content [Post Content]
     * @return [string]               [Parsed Post Content]
     */
    function parse_tag($post_content,$activity_id=0,$is_tag_link=1)
    {
        $url = '';
        $entity = '';
        $entity_guid = '';
        $user_id = isset($this->UserID) ? $this->UserID : 0;
        $activity_cache=array();
        $block_users=$this->block_user_list($user_id, 3);
        if(CACHE_ENABLE && $activity_id) {
            $activity_cache=$this->cache->get('activity_'.$activity_id);
            if(isset($activity_cache['mtd'])) {
                foreach($activity_cache['mtd'] as $key=>$val) {
                   $title= $val['t'];
                   $module_entity_id = $val['meid'];
                    if ($val['mid'] == 3) {
                       $block_users=$this->block_user_list($val['meid'], 3);
                        $entity_guid = $val['meguid'];
                        $url = get_entity_url($val['meid'], 'User', 1);
                        $entity = 'user';
                    } else if ($val['mid'] == 18) {
                        $entity_guid = $val['meguid'];
                        $url = 'page/'.get_detail_by_guid($entity_guid, 18, 'PageURL', 1);
                        $entity = 'page';
                    } else if ($val['mid'] == 1) {
                        $entity_guid = $val['meguid'];                        
                        $this->load->model('group/group_model');
                        $group_url_details = $this->group_model->get_group_details_by_id($val['meid']);
                        $url = $this->group_model->get_group_url($val['meid'], $group_url_details['GroupNameTitle'], false, 'index');  
                    
                        $entity = 'group';
                    }else if ($val['mid'] == 34) {
                        $this->load->model('forum/forum_model');
                        $entity_guid = $val['meguid'];
                        $url = $this->forum_model->get_category_url($module_entity_id);
                        $entity= 'forumcategory';
                    }

                    /*added by gautam */
                    if(($this->IsApp == 1) || $is_tag_link==0){ /*For Mobile */
                        $post_content = str_replace('{{'.$key.'}}', '<a href="' . base_url() . '?GUID='. $entity_guid .'&ID='.$module_entity_id.'&Type=' . $entity . '">' .  $title . '</a>' , $post_content);
                         // $post_content = str_replace('{{'.$key.'}}', $title , $post_content);
                    }else{
                        if ($val['mid'] == 34)
                            $post_content = str_replace('{{' . $key . '}}', '<a href="javascript:void(0);" onclick="redirectUserName(\'' . $url . '\')" entityguid="' . $entity_guid . '" class="tagged-person tagged-person-click">' . $title . '</a>', $post_content);
                        else
                            $post_content = str_replace('{{' . $key . '}}', '<a href="javascript:void(0);" onclick="redirectUserName(\'' . $url . '\')" entitytype="' . $entity . '" entityguid="' . $entity_guid . '" class="tagged-person tagged-person-click loadbusinesscard">' . $title . '</a>', $post_content);
                    }


                }
            }
        }
        
        if(!isset($activity_cache['mtd'])) {
            preg_match_all('/{{([0-9]+)}}/', $post_content, $matches);
            if (!empty($matches[1])) { 
                foreach ($matches[1] as $match) {
                    $this->db->select('ModuleID,ModuleEntityID,Title');
                    $this->db->where('MentionID', $match);
                    $result = $this->db->get(MENTION);
                    if ($result->num_rows()) {
                        $details = $result->row_array();
                        $title = $details['Title'];
                        $module_entity_id = $details['ModuleEntityID'];
                        if ($details['ModuleID'] == 3) {
                            $url = get_entity_url($details['ModuleEntityID'], 'User', 1);
                            $entity_guid = get_detail_by_id($details['ModuleEntityID'], 3);
                            $entity = 'user';
                        } else if ($details['ModuleID'] == 18) {
                            $url = 'page/'.get_detail_by_id($details['ModuleEntityID'], 18, 'PageURL', 1);
                            $entity_guid = get_detail_by_id($details['ModuleEntityID'], 18);
                            $entity = 'page';
                        } else if ($details['ModuleID'] == 1) {
                            $this->load->model('group/group_model');
                            $group_url_details = $this->group_model->get_group_details_by_id($details['ModuleEntityID']);
                            $url = $this->group_model->get_group_url($details['ModuleEntityID'], $group_url_details['GroupNameTitle'], false, 'index');  
                            $entity_guid = $group_url_details['GroupGUID'];
                            $entity = 'group';
                        }else if ($details['ModuleID'] == 34) {
                            $this->load->model('forum/forum_model');
                            $url = $this->forum_model->get_category_url($module_entity_id);
                            $entity_guid = get_detail_by_id($details['ModuleEntityID'], 34);
                            $entity = 'forumcategory';
                        }
                    /*added by gautam */                 
                        if($this->IsApp == 1 || $is_tag_link==0 ){ /*For Mobile */
                            $post_content = str_replace('{{'.$match.'}}', '<a href="' . base_url() . '?GUID='. $entity_guid .'&ID='.$module_entity_id.'&Type=' . 
                                $entity . '">' .  $title . '</a>' , $post_content);
                        } else {
                            if($details['ModuleID'] == 3 && in_array($details['ModuleEntityID'], $block_users)) {
                                $post_content = str_replace('{{'.$match.'}}', $title , $post_content);
                            } else {
                                if($details['ModuleID']==34)
                                    $post_content = str_replace('{{' . $match . '}}', '<a href="javascript:void(0);" onclick="redirectUserName(\'' . $url . '\')" entityguid="' . $entity_guid . '" class="tagged-person tagged-person-click" >' . $title . '</a>', $post_content);
                                else
                                    $post_content = str_replace('{{' . $match . '}}', '<a href="javascript:void(0);" onclick="redirectUserName(\'' . $url . '\')" entitytype="' . $entity . '" entityguid="' . $entity_guid . '" class="tagged-person tagged-person-click loadbusinesscard" >' . $title . '</a>', $post_content);
                            }
                        }                       

                    }
                }
                $post_content = html_entity_decode($post_content);
            }
        }
        $post_content = preg_replace('/(^)?(<br\s*\/?>\s*)+$/', '', $post_content);
        return $post_content;
    }

    /**
     * [parse_tag_edit Used to parse tagged value from existing post content]
     * @param  [string] $post_content [Post Content]
     * @return [string]               [Parsed Post Content]
     */
    function parse_tag_edit($post_content,$activity_id=0,$is_tag_link=1) {   
        preg_match_all('/{{([0-9]+)}}/', $post_content, $matches);
        if (!empty($matches[1])) {
            $activity_cache=array();
            if(CACHE_ENABLE && $activity_id) {
                $activity_cache=$this->cache->get('activity_'.$activity_id);                
            }


            foreach ($matches[1] as $match) {
                if($activity_cache && isset($activity_cache['mtd'])) {
                    foreach($activity_cache['mtd'] as $key=>$val) {                        
                        $title= $val['t'];
                        if($match == $key) {
                            $post_content = strtr($post_content, array('{{' . $match . '}}' => $title));
                            break 1;
                        }
                    }
                } else {
                    $this->db->select('ModuleID,ModuleEntityID,Title');
                    $this->db->where('MentionID', $match);
                    $result = $this->db->get(MENTION);
                    if ($result->num_rows()) {
                        $row = $result->row_array();   
                        $post_content = strtr($post_content, array('{{' . $match . '}}' => $row['Title']));
                    }
                }            
            }
            $post_content = html_entity_decode($post_content);
        }
        $post_content = preg_replace('/(^)?(<br\s*\/?>\s*)+$/', '', $post_content);
        return $post_content;
    }

    /**
     * [parse_tag Used to parse tagged value from existing post content]
     * @param  [string] $post_content [Post Content]
     * @return [string]               [Parsed Post Content]
     */
    public function parse_tag_html($post_content)
    {
        preg_match_all('/{{([0-9]+)}}/', $post_content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $this->db->select('ModuleID,ModuleEntityID,Title');
                $this->db->where('MentionID', $match);
                $result = $this->db->get(MENTION);
                if ($result->num_rows()) {
                    $details = $result->row_array();
                    $title = $details['Title'];
                    $module_entity_id = $details['ModuleEntityID'];
                    $url = site_url();
                    if ($details['ModuleID'] == 3) {
                        $url .= get_entity_url($details['ModuleEntityID'], 'User', 1);
                        $entity_guid = get_detail_by_id($details['ModuleEntityID'], 3);
                        $entity = 'user';
                    } else if ($details['ModuleID'] == 18) {
                        $url .= 'page/';
                        $url .= get_detail_by_id($details['ModuleEntityID'], 18, 'PageURL', 1);
                        $entity_guid = get_detail_by_id($details['ModuleEntityID'], 18);
                        $entity = 'page';
                    } else if ($details['ModuleID'] == 1) {
                        $this->load->model('group/group_model');
                        $group_url_details = $this->group_model->get_group_details_by_id($details['ModuleEntityID']);
                        $url = $this->group_model->get_group_url($details['ModuleEntityID'], $group_url_details['GroupNameTitle'], true, 'index');  
                        $entity_guid = $group_url_details['GroupGUID'];
                        $entity = 'group';
                    }else if($details['ModuleID'] == 34){
                        $this->load->model('forum/forum_model');
                        $url = $this->forum_model->get_category_url($details['ModuleEntityID']);
                        $entity_guid = get_detail_by_id($details['ModuleEntityID'], 34);
                        $entity = 'forumcategory';
                    }
                    
                    if($this->IsApp == 1){ /*For Mobile */
                            $post_content = str_replace('{{'.$match.'}}', '<a href="' . base_url() . '?GUID='. $entity_guid .'&ID='.$module_entity_id.'&Type=' . 
                            $entity . '">' .  $title . '</a>' , $post_content);
                    } else {
                        if($details['ModuleID'] == 34)
                            $post_content = str_replace('{{' . $match . '}}', '<a entityguid="' . $entity_guid . '" href="' . $url . '" class="tagged-person tagged-person-click">' . $title . '</a>', $post_content);
                        else
                            $post_content = str_replace('{{' . $match . '}}', '<a entitytype="' . $entity . '" entityguid="' . $entity_guid . '" href="' . $url . '" class="tagged-person tagged-person-click loadbusinesscard">' . $title . '</a>', $post_content);
                    }
                }
            }
            //$post_content = html_entity_decode($post_content);
        }
        $post_content = preg_replace('/(^)?(<br\s*\/?>\s*)+$/', '', $post_content);
        return $post_content;
    }

    /**
     * [live_feed Used to get the live feeds]
     * @param  [int]  $user_id    [User ID]
     * @param  [int]  $page_no    [Page Number]
     * @param  [int]  $page_size  [Page Size]
     * @param  boolean $count_only [Count only flag]
     * @return [array]              [Live Feed Details]
     */
    public function live_feed($user_id, $page_no = PAGE_NO, $page_size = 20, $count_only = false)
    {
        $time_zone = get_user_time_zone($user_id);
        $current_utc_date = get_current_date('%Y-%m-%d');
        $friend_follower = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friends = $friend_follower['Friends'];
        $follow = $friend_follower['Follow'];
        $friends[] = 0;
        $follow[] = 0;
        $friend_follower = array_unique(array_merge($friends, $follow));
        $friend_follower[] = 0;
        $friend_follower = implode(',', $friend_follower);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17);
        $frnd = implode(',', $friends);
        if(!$this->settings_model->isDisabled(10)) {
            $sql[] = "
                SELECT 'FA' as Type, UserID, GROUP_CONCAT(DISTINCT(FriendID) ORDER BY AcceptedDate DESC) as EntityID,MAX(AcceptedDate) as CreatedDate,'3' as ModuleID, UserID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . FRIENDS . " 
                WHERE UserID IN(" . $frnd . ")
                AND FriendID!='" . $user_id . "'
                AND Status='1'
                GROUP BY UserID,DATE(AcceptedDate)
            ";
        }

        $sql[] = "
                SELECT 'FU' as Type, UserID, GROUP_CONCAT(DISTINCT(TypeEntityID) ORDER BY CreatedDate DESC) as EntityID, MAX(CreatedDate) as CreatedDate,'3' as ModuleID, UserID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . FOLLOW . " WHERE Type='user'
                AND UserID IN(" . $frnd . ")
                AND TypeEntityID!='" . $user_id . "'
                GROUP BY UserID,DATE(CreatedDate)
            ";
        if(!$this->settings_model->isDisabled(18)) {
            $sql[] = "
                SELECT 'FP' as Type, UserID, GROUP_CONCAT(DISTINCT(TypeEntityID) ORDER BY CreatedDate DESC) as EntityID, MAX(CreatedDate) as CreatedDate,'18' as ModuleID, UserID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . FOLLOW . " WHERE Type='page'
                AND UserID IN(" . $frnd . ")
                GROUP BY UserID,DATE(CreatedDate)
            ";
        }
        $cond = "
            LEFT JOIN " . ACTIVITY . " A ON PC.EntityID=A.ActivityID AND PC.EntityType='ACTIVITY'
                WHERE A.ActivityTypeID IN(1,5,6,7,8,11,12) AND A.StatusID='2' AND
                (IF(A.Privacy=1,true,false) OR
                IF(A.Privacy=2,A.UserID IN (" . $friend_follower . "),false) OR
                IF(A.Privacy=3,A.UserID IN (" . $frnd . "),false) OR
                IF(A.Privacy=4,A.UserID='" . $user_id . "',false))
                AND PC.UserID IN(" . $frnd . ")
        ";

        $sql[] = "
                SELECT 'PC' as Type, GROUP_CONCAT(DISTINCT(PC.UserID) ORDER BY PC.CreatedDate DESC) as UserID, A.ActivityID as EntityID,MAX(PC.CreatedDate) as CreatedDate,'0' as ModuleID, A.ActivityGUID as EntityGUID, PC.PostComment as PostContent,A.UserID as ActivityOwner,A.ModuleID as ActivityModuleID,A.ModuleEntityID as ActivityModuleEntityID,A.ActivityTypeID as ActivityTypeID,A.Params as Params,PC.PostCommentID,A.ModuleEntityOwner,A.UserID as EntityUserID
                FROM " . POSTCOMMENTS . " PC 
                LEFT JOIN (SELECT MAX(PC.CreatedDate) as CreatedDate FROM " . POSTCOMMENTS . " PC " . $cond . ") PLJ
                        ON PLJ.CreatedDate=PC.CreatedDate
                " . $cond . "
                GROUP BY EntityID,DATE(PC.CreatedDate)
            ";

        $sql[] = "
                SELECT 'MC' as Type, GROUP_CONCAT(DISTINCT(PC.UserID) ORDER BY PC.CreatedDate DESC) as UserID, M.MediaID as EntityID,MAX(PC.CreatedDate) as CreatedDate,'0' as ModuleID, M.MediaGUID as EntityGUID, '' as PostContent,M.UserID as ActivityOwner,'3' as ActivityModuleID,M.UserID as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,M.UserID as EntityUserID
                FROM " . POSTCOMMENTS . " PC 
                LEFT JOIN " . MEDIA . " M ON PC.EntityID=M.MediaID AND PC.EntityType='MEDIA'
                WHERE M.StatusID='2'
                AND PC.UserID IN(" . $frnd . ")
                GROUP BY EntityID,DATE(PC.CreatedDate)
            ";

        $sql[] = "
                SELECT 'PL' as Type, GROUP_CONCAT(DISTINCT(PL.UserID) ORDER BY PL.ModifiedDate DESC) as UserID, A.ActivityID as EntityID,MAX(PL.ModifiedDate) as CreatedDate,'0' as ModuleID, A.ActivityGUID as EntityGUID, A.PostContent,A.UserID as ActivityOwner,A.ModuleID as ActivityModuleID,A.ModuleEntityID as ActivityModuleEntityID,A.ActivityTypeID as ActivityTypeID,A.Params as Params,'0' as PostCommentID,A.ModuleEntityOwner,A.UserID as EntityUserID
                FROM " . POSTLIKE . " PL 
                LEFT JOIN " . ACTIVITY . " A ON PL.EntityID=A.ActivityID AND PL.EntityType='Activity'
                WHERE A.ActivityTypeID IN(1,5,6,7,8,11,12) AND A.StatusID='2' AND
                (IF(A.Privacy=1,true,false) OR
                IF(A.Privacy=2,A.UserID IN (" . $friend_follower . "),false) OR
                IF(A.Privacy=3,A.UserID IN (" . $frnd . "),false) OR
                IF(A.Privacy=4,A.UserID='" . $user_id . "',false))
                AND PL.UserID IN(" . $frnd . ")
                GROUP BY EntityID,DATE(PL.ModifiedDate)
            ";

        $sql[] = "
                SELECT 'ML' as Type, GROUP_CONCAT(DISTINCT(PL.UserID) ORDER BY PL.ModifiedDate DESC) as UserID, M.MediaID as EntityID,MAX(PL.ModifiedDate) as CreatedDate,'0' as ModuleID, M.MediaGUID as EntityGUID, '' as PostContent,M.UserID as ActivityOwner,'3' as ActivityModuleID,M.UserID as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,M.UserID as EntityUserID
                FROM " . POSTLIKE . " PL 
                LEFT JOIN " . MEDIA . " M ON PL.EntityID=M.MediaID AND PL.EntityType='MEDIA'
                WHERE M.StatusID='2'
                AND PL.UserID IN(" . $frnd . ")
                GROUP BY EntityID,DATE(PL.ModifiedDate)
            ";

        $cond = "LEFT JOIN " . POSTCOMMENTS . " PC ON PL.EntityID=PC.PostCommentID AND PL.EntityType='COMMENT'
                        LEFT JOIN " . ACTIVITY . " A ON PC.EntityID=A.ActivityID AND PC.EntityType='Activity'
                        LEFT JOIN " . MEDIA . " M ON PC.EntityID=M.MediaID AND PC.EntityType<>'Activity'
                        WHERE PC.StatusID='2' AND 
                        IF(PC.EntityType='Activity',
                        A.ActivityTypeID IN(1,5,6,13,7,8,11,12) AND A.StatusID='2' AND
                        (IF(A.Privacy=1,true,false) OR
                        IF(A.Privacy=2,A.UserID IN (" . $friend_follower . "),false) OR
                        IF(A.Privacy=3,A.UserID IN (" . $frnd . "),false) OR
                        IF(A.Privacy=4,A.UserID='" . $user_id . "',false))
                        ,
                        M.StatusID='2'
                        )
                        AND PL.UserID IN(" . $frnd . ")";
        $sql[] = "
                        SELECT 'CL' as Type, GROUP_CONCAT(DISTINCT(PL.UserID) ORDER BY PL.ModifiedDate DESC) as UserID, PC.EntityID as EntityID,MAX(PL.ModifiedDate) as CreatedDate,'0' as ModuleID, PC.PostCommentGUID as EntityGUID, PC.PostComment as PostContent,PC.UserID as ActivityOwner,IF(PC.EntityType='Activity',A.ModuleID,M.ModuleID) as ActivityModuleID,IF(PC.EntityType='Activity',A.ModuleEntityID,M.ModuleEntityID) as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,PC.PostCommentID,PC.EntityOwner as ModuleEntityOwner,PC.UserID as EntityUserID
                        FROM " . POSTLIKE . " PL 
                        LEFT JOIN (SELECT MAX(PL.ModifiedDate) as ModifiedDate FROM " . POSTLIKE . " PL " . $cond . ") PLJ
                        ON PLJ.ModifiedDate=PL.ModifiedDate
                        " . $cond . "
                        GROUP BY EntityID,DATE(PL.ModifiedDate)
                    ";

        if(!$this->settings_model->isDisabled(1)) {
            $cond = "
                LEFT JOIN " . GROUPS . " G ON G.GroupID=GM.GroupID
                WHERE GM.StatusID='2' AND G.StatusID='2' AND
                GM.ModuleID='3' AND
                GM.AddedAs='2' AND
                GM.ModuleID='3' AND GM.ModuleEntityID IN(" . $frnd . ") AND 
                (G.GroupID IN (SELECT G2.GroupID FROM " . GROUPS . " G2 LEFT JOIN " . GROUPMEMBERS . " GM2 ON G2.GroupID=GM2.GroupID WHERE GM2.ModuleID='3' AND GM2.ModuleEntityID='" . $user_id . "' AND GM2.StatusID='2') OR
                                G.GroupID IN (SELECT GroupID FROM " . GROUPS . " WHERE IsPublic='1'))
            ";

//INNER JOIN (SELECT MAX(GM.JoinedAt) as JoinedAt FROM ".GROUPMEMBERS." GM ".$cond.") PLJ ON PLJ.JoinedAt=GM.JoinedAt
            $sql[] = "
                    SELECT 'GJ' as Type, GROUP_CONCAT(DISTINCT(GM.ModuleEntityID) ORDER BY JoinedAt DESC) as UserID,
                GM.GroupID as EntityID, MAX(GM.JoinedAt) as CreatedDate,'1' as ModuleID, G.GroupGUID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . GROUPMEMBERS . " GM
                
                " . $cond . "
                GROUP BY DATE(GM.JoinedAt),GM.GroupID
                    ";

            $cond = "
                LEFT JOIN " . GROUPS . " G ON G.GroupID=GM.GroupID
                WHERE GM.StatusID='2' AND G.StatusID='2' AND
                GM.ModuleID='3' AND
                GM.AddedAs='3' AND
                GM.ModuleID='3' AND GM.ModuleEntityID IN(" . $frnd . ") AND 
                (G.GroupID IN (SELECT G2.GroupID FROM " . GROUPS . " G2 LEFT JOIN " . GROUPMEMBERS . " GM2 ON G2.GroupID=GM2.GroupID WHERE GM2.ModuleID='3' AND GM2.ModuleEntityID='" . $user_id . "' AND GM2.StatusID='2') OR
                                G.GroupID IN (SELECT GroupID FROM " . GROUPS . " WHERE IsPublic='1'))
            ";

            $sql[] = "
                    SELECT 'GA' as Type, GROUP_CONCAT(DISTINCT(GM.ModuleEntityID) ORDER BY JoinedAt DESC) as UserID,
                GM.GroupID as EntityID, MAX(GM.JoinedAt) as CreatedDate,'1' as ModuleID, G.GroupGUID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,GM.AddedBy as EntityUserID
                FROM " . GROUPMEMBERS . " GM
                
                " . $cond . "
                GROUP BY DATE(GM.JoinedAt),GM.GroupID
                    ";
        }

        if(!$this->settings_model->isDisabled(14)) {
            $sql[] = "
                SELECT 'EJ' as Type, GROUP_CONCAT(DISTINCT(EU.UserID)) as UserID, EU.EventID as EntityID,MAX(EU.ModifiedDate) as CreatedDate,'14' as ModuleID, E.EventGUID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . EVENTUSERS . " EU
                LEFT JOIN " . EVENTS . " E ON EU.EventID=E.EventID
                WHERE E.IsDeleted='0' AND EU.Presence='ATTENDING'
                AND EU.UserID IN(" . $frnd . ")
                GROUP BY DATE(EU.ModifiedDate)
                ";
        }
        $sql = implode(' UNION ', $sql);
        $sql = "
            SELECT * FROM 
            (" . $sql . ") tbl
            GROUP BY DATE(tbl.CreatedDate),tbl.Type,tbl.EntityID
            ORDER BY tbl.CreatedDate DESC
        ";

        if (!$count_only)
        {
            $sql .= " LIMIT " . $this->get_pagination_offset($page_no, $page_size) . ',' . $page_size;
        }

        $query = $this->db->query($sql);
        //echo $this->db->last_query();
        $num_rows = $query->num_rows();
        if ($count_only)
        {
            return $num_rows;
        }
        $data = array();
        if ($num_rows)
        {
            foreach ($query->result_array() as $result)
            {
                $arr = array('Type' => $result['Type'], 'Users' => array(), 'Entities' => array(), 'Message' => '', 'CreatedDate' => $result['CreatedDate'], 'EntityGUID' => $result['EntityGUID'], 'PostContent' => $this->parse_tag_html($result['PostContent']), 'ExtMsg' => '', 'ShowExtMsg' => 0, 'ActivityTypeID' => $result['ActivityTypeID']);

                if ($result['UserID'])
                {
                    $result['UserID'] = explode(',', $result['UserID']);
                    foreach ($result['UserID'] as $user_ids)
                    {
                        $arr['Users'][] = $this->user_model->getUserName(0, 3, $user_ids);
                    }
                }

                if ($result['EntityID'] && $result['ModuleID'] != '0')
                {
                    $result['EntityID'] = explode(',', $result['EntityID']);
                    foreach ($result['EntityID'] as $entity_ids)
                    {
                        $arr['Entities'][] = $this->user_model->getUserName(0, $result['ModuleID'], $entity_ids);
                    }
                    if (count($result['EntityID']) > 2)
                    {
                        $arr['ShowExtMsg'] = 1;
                    }
                }

                $entity_module_id = '';
                $entity_module_guid = '';
                $entity_name = '';
                $entity_link = '';
                $e_module_id = '';
                $e_module_guid = '';
                $e_name = '';
                $e_link = '';
                $entity_module_guid = get_detail_by_id($result['UserID'][0], 3);
                $entity_module_id = 3;

                if ($result['Type'] == 'GA')
                {
                    $e_details = $this->user_model->getUserName(0, 3, $result['EntityUserID']);
                    if ($e_details)
                    {
                        $e_link = $e_details['ProfileURL'];
                        $e_name = $e_details['FirstName'] . ' ' . $e_details['LastName'];
                        $e_module_id = $e_details['ModuleID'];
                        $e_module_guid = $e_details['ModuleEntityGUID'];
                    }
                }

                if ($result['ActivityModuleID'] && $result['ActivityModuleEntityID'] && $result['ActivityOwner'])
                {
                    if ($result['ActivityModuleID'] != 3)
                    {
                        $e_details = $this->user_model->getUserName(0, $result['ActivityModuleID'], $result['ActivityModuleEntityID']);
                        if ($e_details)
                        {
                            $e_link = $e_details['ProfileURL'];
                            $e_name = $e_details['FirstName'] . ' ' . $e_details['LastName'];
                            $e_module_id = $e_details['ModuleID'];
                            $e_module_guid = $e_details['ModuleEntityGUID'];
                        }
                    }
                    if ($result['UserID'][0] == $result['ActivityOwner'] && $result['ActivityModuleID'] == '3' && count($result['UserID']) == '1')
                    {
                        $entity_name = 'their';
                    } else if ($user_id == $result['ActivityOwner'] && $result['ActivityModuleID'] == '3')
                    {
                        $entity_name = 'your';
                    } else
                    {
                        if ($result['ActivityModuleID'] == '1')
                        {
                            $this->load->model('group/group_model');
                            if (!$this->group_model->has_access($user_id, $result['ActivityModuleEntityID']))
                            {
                                continue;
                            }
                        } else if ($result['ActivityModuleID'] == '14')
                        {
                            $this->load->model('events/event_model');
                            if (!$this->event_model->has_access($user_id, $result['ActivityModuleEntityID']))
                            {
                                continue;
                            }
                        }
                        if ($result['ModuleEntityOwner'] == '0' && $result['EntityUserID'] != '0')
                        {
                            $entity_name = $this->user_model->getUserName(0, 3, $result['EntityUserID']);
                        } else
                        {
                            $entity_name = $this->user_model->getUserName(0, $result['ActivityModuleID'], $result['ActivityModuleEntityID']);
                        }
                        $uid = get_detail_by_guid($entity_name['ModuleEntityGUID'], 3, 'UserID', 1);
                        if ($uid == $user_id)
                        {
                            $entity_module_id = $entity_name['ModuleID'];
                            $entity_module_guid = $entity_name['ModuleEntityGUID'];
                            $entity_name = 'your';
                        } else if ($result['UserID'][0] == $uid && count($result['UserID']) == '1')
                        {
                            $entity_module_id = $entity_name['ModuleID'];
                            $entity_module_guid = $entity_name['ModuleEntityGUID'];
                            $entity_name = 'their';
                        } else
                        {
                            $entity_module_id = $entity_name['ModuleID'];
                            $entity_module_guid = $entity_name['ModuleEntityGUID'];
                            $entity_link = $entity_name['ProfileURL'];
                            $entity_name = $entity_name['FirstName'] . ' ' . $entity_name['LastName'] . '\'s';
                        }
                    }
                }
                $arr['EntityModuleID'] = $entity_module_id;
                $arr['ModuleEntityGUID'] = $entity_module_guid;
                $arr['EntityName'] = $entity_name;
                $arr['EntityLink'] = $entity_link;
                if ($e_link != $entity_link)
                {
                    if (!empty($e_module_guid))
                    {
                        $arr['EModuleID'] = $e_module_id;
                        $arr['MEntityGUID'] = $e_module_guid;
                    }
                    $arr['EName'] = $e_name;
                    $arr['ELink'] = $e_link;
                }
                $continue = false;
                switch ($result['Type'])
                {
                    case 'FA':
                        $arr['Message'] = 'is now friends with';
                        $arr['ExtMsg'] = 'people';
                        if (isset($prev['UserID']) && isset($prev['EntityID']) && $prev['UserID'] == $result['EntityID'] && $prev['EntityID'] == $result['UserID'])
                        {
                            $continue = true;
                        } else
                        {
                            $prev['UserID'] = $result['UserID'];
                            $prev['EntityID'] = $result['EntityID'];
                        }
                        break;

                    case 'FU':
                        $arr['Message'] = 'is now following';
                        $arr['ExtMsg'] = 'people';
                        break;
                    case 'FP':
                        $arr['Message'] = 'is now following';
                        $arr['ExtMsg'] = 'pages';
                        break;

                    case 'PC':
                    case 'MC':
                        $arr['Message'] = 'commented on ';
                        break;

                    case 'PL':
                    case 'ML':
                    case 'CL':
                        $arr['Message'] = 'likes ';
                        break;

                    case 'GJ':
                        $arr['Message'] = 'joined';
                        $arr['ExtMsg'] = 'groups';
                        break;
                    case 'GA':
                        $arr['Message'] = 'has been added to';
                        $arr['ExtMsg'] = 'groups';
                        break;

                    case 'EJ':
                        $arr['Message'] = 'joined';
                        $arr['ExtMsg'] = 'events';
                        break;
                }

                if ($continue)
                {
                    continue;
                }

                $arr['ActivityLink'] = '';
                $arr['Album'] = array();
                if ($result['Type'] == 'PL' || $result['Type'] == 'CL' || $result['Type'] == 'PC')
                {
                    $arr['ActivityLink'] = get_single_activity_url($result['EntityID']);
                    if ($result['Type'] == 'PL')
                    {
                        $album_guid = '';
                        if ($result['ActivityTypeID'] == '5' || $result['ActivityTypeID'] == '6')
                        {
                            $params = json_decode($result['Params'], true);
                            $album_guid = $params['AlbumGUID'];
                        }
                        $arr['Album'] = $this->get_albums($result['EntityID'], $user_id, $album_guid);
                    } else
                    {
                        $arr['Album'] = $this->get_comment_media($result['PostCommentID'], true);
                    }
                }

                if ($result['Type'] == 'ML' || $result['Type'] == 'MC')
                {
                    $arr['Album'] = $this->get_albums($result['EntityID'], $user_id, '', 'Media');
                }

                $data[] = $arr;
            }
        }
        return $data;
    }

    /**
     * [get_single_live_feed Get the activity details to show in live feed]
     * @param  [int] $user_id      [User ID]
     * @param  [int] $from_user_id [From User ID]
     * @param  [string] $type         [TYPE OF ACTIVITY]
     * @param  [string] $entity_guid  [Entity GUID]
     * @return [array]               [Live feed details]
     */
    public function get_single_live_feed($user_id, $from_user_id, $type, $entity_guid)
    {
        $time_zone = get_user_time_zone($user_id);
        $current_utc_date = get_current_date('%Y-%m-%d');
        $friend_follower = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friends = $friend_follower['Friends'];
        $follow = $friend_follower['Follow'];

        $friends[] = 0;
        $follow[] = 0;
        $friend_follower = array_unique(array_merge($friends, $follow));
        $friend_follower[] = 0;
        $friend_follower = implode(',', $friend_follower);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17);

        $frnd = implode(',', $friends);

        if ($type == 'FA')
        {
            $sql = "
                    SELECT 'FA' as Type, UserID, GROUP_CONCAT(DISTINCT(FriendID) ORDER BY AcceptedDate DESC) as EntityID,MAX(AcceptedDate) as CreatedDate,'3' as ModuleID, UserID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . FRIENDS . " 
                WHERE UserID IN(" . $frnd . ")
                AND FriendID!='" . $user_id . "'
                AND Status='1'
                GROUP BY UserID,DATE(AcceptedDate)
                ";
        }

        if ($type == 'FU')
        {
            $sql = "
                     SELECT 'FU' as Type, UserID, GROUP_CONCAT(DISTINCT(TypeEntityID) ORDER BY CreatedDate DESC) as EntityID, MAX(CreatedDate) as CreatedDate,'3' as ModuleID, UserID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . FOLLOW . " WHERE Type='user'
                AND UserID IN(" . $frnd . ")
                AND TypeEntityID!='" . $user_id . "'
                GROUP BY UserID,DATE(CreatedDate)
                ";
        }

        if ($type == 'FP')
        {
            $sql = "
                    SELECT 'FP' as Type, UserID, GROUP_CONCAT(DISTINCT(TypeEntityID) ORDER BY CreatedDate DESC) as EntityID, MAX(CreatedDate) as CreatedDate,'18' as ModuleID, UserID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . FOLLOW . " WHERE Type='page'
                AND UserID IN(" . $frnd . ")
                GROUP BY UserID,DATE(CreatedDate)
                ";
        }

        if ($type == 'PC')
        {
            $cond = "
            LEFT JOIN " . ACTIVITY . " A ON PC.EntityID=A.ActivityID AND PC.EntityType='ACTIVITY'
                WHERE A.ActivityTypeID IN(1,5,6,7,8,11,12) AND A.StatusID='2' AND
                (IF(A.Privacy=1,true,false) OR
                IF(A.Privacy=2,A.UserID IN (" . $friend_follower . "),false) OR
                IF(A.Privacy=3,A.UserID IN (" . $frnd . "),false) OR
                IF(A.Privacy=4,A.UserID='" . $user_id . "',false))
                AND PC.UserID IN(" . $frnd . ")
        ";

            $sql = "
                SELECT 'PC' as Type, GROUP_CONCAT(DISTINCT(PC.UserID) ORDER BY PC.CreatedDate DESC) as UserID, A.ActivityID as EntityID,MAX(PC.CreatedDate) as CreatedDate,'0' as ModuleID, A.ActivityGUID as EntityGUID, PC.PostComment as PostContent,A.UserID as ActivityOwner,A.ModuleID as ActivityModuleID,A.ModuleEntityID as ActivityModuleEntityID,A.ActivityTypeID as ActivityTypeID,A.Params as Params,PC.PostCommentID,A.ModuleEntityOwner,A.UserID as EntityUserID
                FROM " . POSTCOMMENTS . " PC 
                LEFT JOIN (SELECT MAX(PC.CreatedDate) as CreatedDate FROM " . POSTCOMMENTS . " PC " . $cond . ") PLJ
                        ON PLJ.CreatedDate=PC.CreatedDate
                " . $cond . "
                GROUP BY EntityID,DATE(PC.CreatedDate)
            ";
        }

        if ($type == 'MC')
        {
            $sql = "
                    SELECT 'MC' as Type, GROUP_CONCAT(DISTINCT(PC.UserID) ORDER BY PC.CreatedDate DESC) as UserID, M.MediaID as EntityID,MAX(PC.CreatedDate) as CreatedDate,'0' as ModuleID, M.MediaGUID as EntityGUID, '' as PostContent,M.UserID as ActivityOwner,'3' as ActivityModuleID,M.UserID as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . POSTCOMMENTS . " PC 
                LEFT JOIN " . MEDIA . " M ON PC.EntityID=M.MediaID AND PC.EntityType='MEDIA'
                WHERE M.StatusID='2'
                AND PC.UserID IN(" . $frnd . ")
                GROUP BY EntityID,DATE(PC.CreatedDate)
                ";
        }

        if ($type == 'PL')
        {
            $sql = "
                    SELECT 'PL' as Type, GROUP_CONCAT(DISTINCT(PL.UserID) ORDER BY PL.ModifiedDate DESC) as UserID, A.ActivityID as EntityID,MAX(PL.ModifiedDate) as CreatedDate,'0' as ModuleID, A.ActivityGUID as EntityGUID, A.PostContent,A.UserID as ActivityOwner,A.ModuleID as ActivityModuleID,A.ModuleEntityID as ActivityModuleEntityID,A.ActivityTypeID as ActivityTypeID,A.Params as Params,'0' as PostCommentID,A.ModuleEntityOwner,A.UserID as EntityUserID
                FROM " . POSTLIKE . " PL 
                LEFT JOIN " . ACTIVITY . " A ON PL.EntityID=A.ActivityID AND PL.EntityType='Activity'
                WHERE A.ActivityTypeID IN(1,5,6,7,8,11,12) AND A.StatusID='2' AND
                (IF(A.Privacy=1,true,false) OR
                IF(A.Privacy=2,A.UserID IN (" . $friend_follower . "),false) OR
                IF(A.Privacy=3,A.UserID IN (" . $frnd . "),false) OR
                IF(A.Privacy=4,A.UserID='" . $user_id . "',false))
                AND PL.UserID IN(" . $frnd . ")
                GROUP BY EntityID,DATE(PL.ModifiedDate)
                ";
        }

        if ($type == 'ML')
        {
            $sql = "
                    SELECT 'ML' as Type, GROUP_CONCAT(DISTINCT(PL.UserID) ORDER BY PL.ModifiedDate DESC) as UserID, M.MediaID as EntityID,MAX(PL.ModifiedDate) as CreatedDate,'0' as ModuleID, M.MediaGUID as EntityGUID, '' as PostContent,M.UserID as ActivityOwner,'3' as ActivityModuleID,M.UserID as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . POSTLIKE . " PL 
                LEFT JOIN " . MEDIA . " M ON PL.EntityID=M.MediaID AND PL.EntityType='MEDIA'
                WHERE M.StatusID='2'
                AND PL.UserID IN(" . $frnd . ")
                GROUP BY EntityID,DATE(PL.ModifiedDate)
                ";
        }

        if ($type == 'CL')
        {
            $cond = "LEFT JOIN " . POSTCOMMENTS . " PC ON PL.EntityID=PC.PostCommentID AND PL.EntityType='COMMENT'
                        LEFT JOIN " . ACTIVITY . " A ON PC.EntityID=A.ActivityID AND PC.EntityType='Activity'
                        LEFT JOIN " . MEDIA . " M ON PC.EntityID=M.MediaID AND PC.EntityType<>'Activity'
                        WHERE PC.StatusID='2' AND 
                        IF(PC.EntityType='Activity',
                        A.ActivityTypeID IN(1,5,6,13,7,8,11,12) AND A.StatusID='2' AND
                        (IF(A.Privacy=1,true,false) OR
                        IF(A.Privacy=2,A.UserID IN (" . $friend_follower . "),false) OR
                        IF(A.Privacy=3,A.UserID IN (" . $frnd . "),false) OR
                        IF(A.Privacy=4,A.UserID='" . $user_id . "',false))
                        ,
                        M.StatusID='2'
                        )
                        AND PL.UserID IN(" . $frnd . ")";
            $sql = "
                        SELECT 'CL' as Type, GROUP_CONCAT(DISTINCT(PL.UserID) ORDER BY PL.ModifiedDate DESC) as UserID, PC.EntityID as EntityID,MAX(PL.ModifiedDate) as CreatedDate,'0' as ModuleID, PC.PostCommentGUID as EntityGUID, PC.PostComment as PostContent,PC.UserID as ActivityOwner,IF(PC.EntityType='Activity',A.ModuleID,M.ModuleID) as ActivityModuleID,IF(PC.EntityType='Activity',A.ModuleEntityID,M.ModuleEntityID) as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,PC.PostCommentID,PC.EntityOwner as ModuleEntityOwner,PC.UserID as EntityUserID
                        FROM " . POSTLIKE . " PL 
                        LEFT JOIN (SELECT MAX(PL.ModifiedDate) as ModifiedDate FROM " . POSTLIKE . " PL " . $cond . ") PLJ
                        ON PLJ.ModifiedDate=PL.ModifiedDate
                        " . $cond . "
                        GROUP BY EntityID,DATE(PL.ModifiedDate)
                    ";
        }

        if ($type == 'GJ')
        {
            $cond = "
                LEFT JOIN " . GROUPS . " G ON G.GroupID=GM.GroupID
                WHERE GM.StatusID='2' AND G.StatusID='2' AND
                GM.AddedAs='2' AND
                GM.ModuleID='3' AND GM.ModuleEntityID IN(" . $frnd . ") AND 
                (G.GroupID IN (SELECT G2.GroupID FROM " . GROUPS . " G2 LEFT JOIN " . GROUPMEMBERS . " GM2 ON G2.GroupID=GM2.GroupID WHERE GM2.ModuleID='3' AND GM2.ModuleEntityID='" . $user_id . "' AND GM2.StatusID='2') OR
                                G.GroupID IN (SELECT GroupID FROM " . GROUPS . " WHERE IsPublic='1'))
            ";
//INNER JOIN (SELECT MAX(GM.JoinedAt) as JoinedAt FROM ".GROUPMEMBERS." GM ".$cond.") PLJ ON PLJ.JoinedAt=GM.JoinedAt
            $sql = "
                    SELECT 'GJ' as Type, GROUP_CONCAT(DISTINCT(GM.ModuleEntityID) ORDER BY JoinedAt DESC) as UserID,
                GM.GroupID as EntityID, MAX(GM.JoinedAt) as CreatedDate,'1' as ModuleID, G.GroupGUID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . GROUPMEMBERS . " GM
                
                " . $cond . "
                GROUP BY DATE(GM.JoinedAt),GM.GroupID
                    ";
        }

        if ($type == 'GA')
        {
            $cond = "
                LEFT JOIN " . GROUPS . " G ON G.GroupID=GM.GroupID
                WHERE GM.StatusID='2' AND G.StatusID='2' AND
                GM.AddedAs='3' AND
                GM.ModuleID='3' AND GM.ModuleEntityID IN(" . $frnd . ") AND 
                (G.GroupID IN (SELECT G2.GroupID FROM " . GROUPS . " G2 LEFT JOIN " . GROUPMEMBERS . " GM2 ON G2.GroupID=GM2.GroupID WHERE GM2.ModuleID='3' AND GM2.ModuleEntityID='" . $user_id . "' AND GM2.StatusID='2') OR
                                G.GroupID IN (SELECT GroupID FROM " . GROUPS . " WHERE IsPublic='1'))
            ";

            $sql = "
                    SELECT 'GA' as Type, GROUP_CONCAT(DISTINCT(GM.ModuleEntityID) ORDER BY JoinedAt DESC) as UserID,
                GM.GroupID as EntityID, MAX(GM.JoinedAt) as CreatedDate,'1' as ModuleID, G.GroupGUID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,GM.AddedBy as EntityUserID
                FROM " . GROUPMEMBERS . " GM
                
                " . $cond . "
                GROUP BY DATE(GM.JoinedAt),GM.GroupID
                    ";
        }

        if ($type == 'EJ')
        {
            $sql = "
                    SELECT 'EJ' as Type, GROUP_CONCAT(DISTINCT(EU.UserID)) as UserID, EU.EventID as EntityID,MAX(EU.ModifiedDate) as CreatedDate,'14' as ModuleID, E.EventGUID as EntityGUID, '' as PostContent,'0' as ActivityOwner,'0' as ActivityModuleID,'0' as ActivityModuleEntityID,'0' as ActivityTypeID,'' as Params,'0' as PostCommentID,'0' as ModuleEntityOwner,'0' as EntityUserID
                FROM " . EVENTUSERS . " EU
                LEFT JOIN " . EVENTS . " E ON EU.EventID=E.EventID
                WHERE E.IsDeleted='0' AND EU.Presence='ATTENDING'
                AND EU.UserID IN(" . $frnd . ")
                GROUP BY DATE(EU.ModifiedDate)
                    ";
        }

        $sql = "
                SELECT * FROM 
                (" . $sql . ") tbl
                ORDER BY tbl.CreatedDate DESC
                LIMIT 0,1
            ";

        $query = $this->db->query($sql);
        //echo $this->db->last_query();
        $num_rows = $query->num_rows();
        if ($num_rows)
        {
            $data = array();
            foreach ($query->result_array() as $result)
            {
                $arr = array('Type' => $result['Type'], 'Users' => array(), 'Entities' => array(), 'Message' => '', 'CreatedDate' => $result['CreatedDate'], 'EntityGUID' => $result['EntityGUID'], 'PostContent' => $this->parse_tag_html($result['PostContent']), 'ExtMsg' => '', 'ShowExtMsg' => 0, 'ActivityTypeID' => $result['ActivityTypeID']);

                if ($result['UserID'])
                {
                    $result['UserID'] = explode(',', $result['UserID']);
                    foreach ($result['UserID'] as $user_ids)
                    {
                        $arr['Users'][] = $this->user_model->getUserName(0, 3, $user_ids);
                    }
                }

                if ($result['EntityID'] && $result['ModuleID'] != '0')
                {
                    $result['EntityID'] = explode(',', $result['EntityID']);
                    foreach ($result['EntityID'] as $entity_ids)
                    {
                        $arr['Entities'][] = $this->user_model->getUserName(0, $result['ModuleID'], $entity_ids);
                    }
                    if (count($result['EntityID']) > 2)
                    {
                        $arr['ShowExtMsg'] = 1;
                    }
                }

                $entity_module_id = '';
                $entity_module_guid = '';
                $entity_name = '';
                $entity_link = '';
                $e_module_id = '';
                $e_module_guid = '';
                $e_name = '';
                $e_link = '';

                if ($result['Type'] == 'GA')
                {
                    $e_details = $this->user_model->getUserName(0, 3, $result['EntityUserID']);
                    if ($e_details)
                    {
                        $e_link = $e_details['ProfileURL'];
                        $e_name = $e_details['FirstName'] . ' ' . $e_details['LastName'];
                        $e_module_id = $e_details['ModuleID'];
                        $e_module_guid = $e_details['ModuleEntityGUID'];
                    }
                }

                if ($result['ActivityModuleID'] && $result['ActivityModuleEntityID'] && $result['ActivityOwner'])
                {
                    if ($result['ActivityModuleID'] != 3)
                    {
                        $e_details = $this->user_model->getUserName(0, $result['ActivityModuleID'], $result['ActivityModuleEntityID']);
                        if ($e_details)
                        {
                            $e_link = $e_details['ProfileURL'];
                            $e_name = $e_details['FirstName'] . ' ' . $e_details['LastName'];
                            $e_module_id = $e_details['ModuleID'];
                            $e_module_guid = $e_details['ModuleEntityGUID'];
                        }
                    }
                    if ($result['UserID'][0] == $result['ActivityOwner'] && $result['ActivityModuleID'] == '3' && count($result['UserID']) == '1')
                    {
                        $entity_name = 'their';
                    } else if ($user_id == $result['ActivityOwner'] && $result['ActivityModuleID'] == '3')
                    {
                        $entity_name = 'your';
                    } else
                    {
                        if ($result['ActivityModuleID'] == '1')
                        {
                            $this->load->model('group/group_model');
                            if (!$this->group_model->has_access($user_id, $result['ActivityModuleEntityID']))
                            {
                                continue;
                            }
                        } else if ($result['ActivityModuleID'] == '14')
                        {
                            $this->load->model('events/event_model');
                            if (!$this->event_model->has_access($user_id, $result['ActivityModuleEntityID']))
                            {
                                continue;
                            }
                        }
                        if ($result['ModuleEntityOwner'] == '0' && $result['EntityUserID'] != '0')
                        {
                            $entity_name = $this->user_model->getUserName(0, 3, $result['EntityUserID']);
                        } else
                        {
                            $entity_name = $this->user_model->getUserName(0, $result['ActivityModuleID'], $result['ActivityModuleEntityID']);
                        }
                        $uid = get_detail_by_guid($entity_name['ModuleEntityGUID'], 3, 'UserID', 1);
                        if ($uid == $user_id)
                        {
                            $entity_module_id = $entity_name['ModuleID'];
                            $entity_module_guid = $entity_name['ModuleEntityGUID'];
                            $entity_name = 'your';
                        } else if ($result['UserID'][0] == $uid && count($result['UserID']) == '1')
                        {
                            $entity_module_id = $entity_name['ModuleID'];
                            $entity_module_guid = $entity_name['ModuleEntityGUID'];
                            $entity_name = 'their';
                        } else
                        {
                            $entity_module_id = $entity_name['ModuleID'];
                            $entity_module_guid = $entity_name['ModuleEntityGUID'];
                            $entity_link = $entity_name['ProfileURL'];
                            $entity_name = $entity_name['FirstName'] . ' ' . $entity_name['LastName'] . '\'s';
                        }
                    }
                }

                $arr['EntityModuleID'] = $entity_module_id;
                $arr['ModuleEntityGUID'] = $entity_module_guid;
                $arr['EntityName'] = $entity_name;
                $arr['EntityLink'] = $entity_link;
                if ($e_link != $entity_link)
                {
                    if (!empty($e_module_guid))
                    {
                        $arr['EModuleID'] = $e_module_id;
                        $arr['MEntityGUID'] = $e_module_guid;
                    }
                    $arr['EName'] = $e_name;
                    $arr['ELink'] = $e_link;
                }

                switch ($result['Type'])
                {
                    case 'FA':
                        $arr['Message'] = 'is now friends with';
                        $arr['ExtMsg'] = 'people';
                        break;

                    case 'FU':
                        $arr['Message'] = 'is now following';
                        $arr['ExtMsg'] = 'people';
                        break;
                    case 'FP':
                        $arr['Message'] = 'is now following';
                        $arr['ExtMsg'] = 'pages';
                        break;

                    case 'PC':
                    case 'MC':
                        $arr['Message'] = 'commented on ';
                        break;

                    case 'PL':
                    case 'ML':
                    case 'CL':
                        $arr['Message'] = 'likes ';
                        break;

                    case 'GJ':
                        $arr['Message'] = 'joined';
                        $arr['ExtMsg'] = 'groups';
                        break;
                    case 'GA':
                        $arr['Message'] = 'has been added to';
                        $arr['ExtMsg'] = 'groups';
                        break;

                    case 'EJ':
                        $arr['Message'] = 'joined';
                        $arr['ExtMsg'] = 'events';
                        break;
                }

                $arr['ActivityLink'] = '';
                $arr['Album'] = array();
                if ($result['Type'] == 'PL' || $result['Type'] == 'CL' || $result['Type'] == 'PC')
                {
                    $arr['ActivityLink'] = get_single_activity_url($result['EntityID']);
                    if ($result['Type'] == 'PL')
                    {
                        $album_guid = '';
                        if ($result['ActivityTypeID'] == '5' || $result['ActivityTypeID'] == '6')
                        {
                            $params = json_decode($result['Params'], true);
                            $album_guid = $params['AlbumGUID'];
                        }
                        $arr['Album'] = $this->get_albums($result['EntityID'], $user_id, $album_guid);
                    } else
                    {
                        $arr['Album'] = $this->get_comment_media($result['PostCommentID'], true);
                    }
                }

                if ($result['Type'] == 'ML' || $result['Type'] == 'MC')
                {
                    $arr['Album'] = $this->get_albums($result['EntityID'], $user_id, '', 'Media');
                }

                $data[] = $arr;
            }
            return $data;
        }
    }

    function add_rating_activity()
    {
        $this->db->select('RatingID,ActivityID,ModuleID,ModuleEntityID,UserID');
        $this->db->where('ActivityID', null);
        $res = $this->db->get(RATINGS);
        $ratingID = '';

        if ($res->num_rows() > 0)
        {
            foreach ($res->result_array() as $result)
            {
                $activity_id = $this->activity_model->addActivity(
                        $result['ModuleID'], $result['ModuleEntityID'], 16, $result['UserID'], 0, '', '', '', array('RatingID' => $result['RatingID'])
                );
                //Update activityid in rating table
                $this->db->set('ActivityID', $activity_id);
                $this->db->where('RatingID', $result['RatingID']);
                $this->db->update(RATINGS);
                $ratingID .= $result['RatingID'] . '<br>';
            }
            echo '(' . $res->num_rows() . ') Records added for rating id =' . $ratingID;
        }
    }

    /**
     * [profile_card Used to get the profile card details]
     * @param  [type] $user_id     [Logged in user id]
     * @param  [type] $entity_guid [Entity guid whose profile being viewed]
     * @param  [type] $entity_type [Entity Type]
     * @return [array]             [Profile card details]
     */
    function profile_card($user_id, $entity_guid, $entity_type)
    {
        $entity_details = array();
        if ($entity_type == "USER")
        {
            $entity_id = get_detail_by_guid($entity_guid, 3);
            // get user data from chache if exist 
            if (CACHE_ENABLE) {
               $user_cache = $this->cache->get('user_profile_'.$entity_id);
               if(!is_array($user_cache)){ 
                    $user_cache = '';
                }
               if(!empty($user_cache)){
                  $entity_details['Name']=$user_cache['EntityName'];
                  $entity_details['About']=$user_cache['UserWallStatus'];
                  $entity_details['EntityGUID']=$entity_guid;
                  $entity_details['Location']=$user_cache['Location'];
                  $entity_details['ProfileCover']=$user_cache['ProfileCover'];
                  $entity_details['ProfilePicture']=$user_cache['ProfilePicture'];
                  $entity_details['ProfileURL']=$user_cache['ProfileURL'];
                  $entity_details['ShowFriendsBtn'] = 0;
                  $entity_details['ShowMessageBtn'] = 0;
               }
            }
            if(empty($entity_details))
            {
                $this->db->select('PU.Url AS ProfileURL, U.UserID');
                $this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,"")) AS Name', FALSE);
                $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);
                $this->db->select('IFNULL(U.ProfileCover,"") as ProfileCover', FALSE);
                $this->db->select("UD.UserWallStatus AS About");
                //$this->db->select("(SELECT COUNT(FR.FriendID) FROM " . FRIENDS . " FR WHERE FR.UserID=U.UserID AND FR.Status='1') as TotalFriends", false);
                $this->db->from(USERS . ' U');
                $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
                $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
                
                if($this->settings_model->isDisabled(10)) { // If friend module is disabled
                    $this->db->select("1 AS ShowFollowBtn, IF(F.StatusID IS NULL, 'Follow', 'Unfollow')follow");
                    $this->db->join(FOLLOW . '  F', " F.TypeEntityID=U.UserID AND F.Type='user' AND F.StatusID='2' AND F.UserID = $user_id", 'left');
                }
                $this->db->where('U.UserGUID', $entity_guid);
                $this->db->limit(1);
                //$this->db->where('U.UserID != ',$user_id);
                $query = $this->db->get();
                $entity_details = $query->row_array();
                if (!empty($entity_details))
                {
                    $entity_details['EntityGUID'] = $entity_guid;
                    $this->load->model(array('users/user_model', 'users/friend_model'));
                    $entity_details['Location'] = $this->user_model->get_user_location($entity_id);
                    $entity_details['ShowFriendsBtn'] = 0;
                    $entity_details['ShowMessageBtn'] = 0;
                    unset($entity_details['UserID']);
                }
            }
            
            if(!$this->settings_model->isDisabled(10)) { // If friend module is not disabled
                $this->db->select("(SELECT COUNT(FR.FriendID) FROM " . FRIENDS . " FR WHERE FR.UserID=" . $entity_id . " AND FR.Status='1') as TotalFriends", false);
                $query = $this->db->get();
                $friend_result = $query->row_array();
                $entity_details['TotalFriends'] = $friend_result['TotalFriends'];            
            }

            // Privacy check and set / unset keys according to it
            if ($user_id != $entity_id)
            {
                $entity_details['ShowFriendsBtn'] = 0;
                if(!$this->settings_model->isDisabled(10)) { // If friend module is not disabled                                    
                    $entity_details['MutualFriend'] = array();
                    $entity_details['MutualFriendCount'] = $this->friend_model->get_mutual_friend($entity_id, $user_id, '', 1);
                    $mutual_friend = $this->friend_model->get_mutual_friend($entity_id, $user_id, '', 0, 1, 3);
                    if (isset($mutual_friend['Friends']))
                    {
                        $entity_details['MutualFriend'] = $mutual_friend['Friends'];
                    }
                    $entity_details['FriendStatus'] = $this->friend_model->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request
                    $entity_details['ShowFriendsBtn'] = 1;
                }
                if(!$this->settings_model->isDisabled(25)){
                    //If message module is not disabled
                    $entity_details['ShowMessageBtn'] = 1;
                }

                $users_relation = get_user_relation($user_id, $entity_id);
                
                $privacy_details = $this->privacy_model->details($entity_id);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if(isset($privacy_label[$privacy]))
                        {
                            if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation))
                            {
                                unset($entity_details['Location']);
                            }
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                            {
                                $entity_details['ProfilePicture'] = 'user_default.jpg';
                            }
                            if ($privacy_label['Value'] == 'view_friends' && !in_array($privacy_label[$privacy], $users_relation))
                            {
                                unset($entity_details['TotalFriends']);
                            }
                            if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation))
                            {
                                $entity_details['ShowFriendsBtn'] = 0;
                            }
                            // If message module is not disabled and privacy settings not allowed
                            if ($entity_details['ShowMessageBtn'] == 1 && ($privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)))
                            {
                                $entity_details['ShowMessageBtn'] = 0;
                            }
                        }
                    }
                }
            }
            return $entity_details;
        }        
        if ($entity_type == "GROUP")
        {
            $entity_id = get_detail_by_guid($entity_guid, 1, 'GroupID', 1);
            $permission = check_group_permissions($user_id, $entity_id);
            $entity_details = array();
            if (isset($permission['Details'])) 
            {                    
                $group_details = $permission['Details'];
                unset($permission['Details']);

                $entity_details = $permission;

                $entity_details['EntityGUID'] = $entity_guid;
                $entity_details['Name'] = $group_details['GroupName'];
                $entity_details['Category'] = $group_details['Category']['Name'];
                $entity_details['Type'] = $group_details['Type'];
                $entity_details['About'] = $group_details['GroupDescription'];
                $entity_details['Privacy'] = $group_details['IsPublic'];
                $entity_details['ProfileCover'] = $group_details['GroupCoverImage'];
                $entity_details['ProfilePicture'] = $group_details['ProfilePicture'];
                $entity_details['TotalMembers'] = $group_details['MemberCount'];
                
                
                $this->load->model(array('group/group_model'));
                $group_data = $this->group_model->get_group_details_by_id($entity_id, '', $group_details);
                $entity_details['ProfileURL'] = $this->group_model->get_group_url($entity_id, $group_data['GroupNameTitle'], true, 'index');                                                         

                $entity_details['EntityMembers'] = $group_details['Members'] ;

                $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
                
                $entity_details['Members'] = array();
                if ($friends)
                {
                    $entity_details['Members'] = $this->group_model->get_group_members_details($entity_id, 1, 3, $friends);
                }
                
                //$group_details['GroupName'] = $this->group_model->get_informal_group_name($entity_id, $user_id, 0, false, array(), $entity_details['Members']);
                // Get profile Url
                $group_url_details = $this->group_model->get_group_details_by_id($entity_id, '', $group_details);
                $entity_details['ProfileURL'] = $this->group_model->get_group_url($entity_id, $group_url_details['GroupNameTitle'], false, 'index');
                
            }
            return $entity_details;
        }
        if ($entity_type == "EVENT")
        {
            $this->db->select('E.EventID, E.Privacy, E.LocationID, E.StartDate, E.StartTime, E.EndDate, E.EndTime');
            $this->db->select('IFNULL(E.Title,"") AS Name', FALSE);
            $this->db->select('CONCAT(E.StartDate, " ", DATE_FORMAT(E.StartTime,"%l:%i %p")) AS DateTime', FALSE);
            $this->db->select('IFNULL(E.ProfileImageID,"") as ProfilePicture', FALSE);
            $this->db->select('IFNULL(E.ProfileBannerID,"") as ProfileCover', FALSE);
            $this->db->from(EVENTS . ' E');
            $this->db->where('E.EventGUID', $entity_guid);
            $query = $this->db->get();
            $entity_details = $query->row_array();
            if ($entity_details)
            {
                $entity_id = $entity_details['EventID'];
                $entity_details['EntityGUID'] = $entity_guid;

                $this->load->model(array('event/event_model', 'users/friend_model'));

                $this->load->helper('location');
                $entity_details['Location'] = get_location_by_id($entity_details['LocationID']);
                $entity_details['StartDate'] = $entity_details['StartDate'];
                $entity_details['EndDate'] = $entity_details['EndDate'];
                $entity_details['StartTime'] = $entity_details['StartTime'];
                $entity_details['EndTime'] = $entity_details['EndTime'];
                $entity_details['Presence'] = $this->event_model->get_user_presence($user_id, $entity_id);
                $entity_details['Presence'] = getPresenceFromConfig($entity_details['Presence']);
                $permissions = checkPermission($user_id, 14, $entity_id, 'IsAccess');
                $entity_details = array_merge($entity_details, $permissions);

                $friends_only = $this->friend_model->getFriendIDS($user_id);
                $entity_details['TotalMembers'] = $this->event_model->get_event_members_details($entity_id, '', '', TRUE);
                $entity_details['Members'] = array();
                if ($friends_only)
                {
                    $entity_details['Members'] = $this->event_model->get_event_members_details($entity_id, 1, 3, FALSE, $friends_only);
                }
                if (!empty($entity_details['ProfilePicture']))
                {
                    $this->db->select('ImageName');
                    $ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $entity_details['ProfilePicture']))->row_array();
                    $entity_details['ProfilePicture'] = $ImageArr['ImageName'];
                } else
                {
                    $entity_details['ProfilePicture'] = 'event-placeholder.png';
                }
                if (!empty($entity_details['ProfileCover']))
                {
                    $this->db->select('ImageName');
                    $ImageArr = $this->db->get_where(MEDIA, array('MediaID' => $entity_details['ProfileCover']))->row_array();
                    $entity_details['ProfileCover'] = $ImageArr['ImageName'];
                } else {
                    $entity_details['ProfileCover'] = '';
                }
                $entity_details['ProfileURL'] = $this->event_model->getViewEventUrl($entity_guid, $entity_details['Name']);                
                unset($entity_details['EventID']);
                unset($entity_details['LocationID']);
            }
            return $entity_details;
        }
        if ($entity_type == "PAGE")
        {
            $this->db->select('P.PageID, P.PageURL AS ProfileURL, CM.Name AS Category');
            $this->db->select('IFNULL(P.Title,"") AS Name', FALSE);
            $this->db->select('IF(P.ProfilePicture="",CM.Icon,P.ProfilePicture) as ProfilePicture', FALSE);
            $this->db->select('IFNULL(P.CoverPicture,"") as ProfileCover', FALSE);
            $this->db->select("P.NoOfFollowers as TotalMembers", false);
            $this->db->from(PAGES . ' P');
            //$this->db->join(ENTITYCATEGORY." EC", "EC.ModuleEntityID=P.PageID AND EC.ModuleID=18", "LEFT");
            $this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = P.CategoryID", "LEFT");
            $this->db->where('P.PageGUID', $entity_guid);
            $query = $this->db->get();
            $entity_details = $query->row_array();
            if ($entity_details)
            {
                $entity_id = $entity_details['PageID'];
                $entity_details['EntityGUID'] = $entity_guid;

                $this->load->model(array('page/page_model', 'users/friend_model'));

                $entity_details['IsFollowed'] = $this->page_model->check_user_follow_page_status($entity_guid, $user_id);

                $entity_details['IsAdmin'] = $this->page_model->get_user_page_permission($entity_guid, $user_id);


                $friends_only = $this->friend_model->getFriendIDS($user_id);
                $entity_details['TotalMembers'] = $this->page_model->get_page_members_details($entity_id, '', '', TRUE);
                $entity_details['Members'] = array();
                if ($friends_only)
                {
                    $entity_details['Members'] = $this->page_model->get_page_members_details($entity_id, 1, 3, FALSE, $friends_only);
                }
                $entity_details['ProfileURL'] = 'page/' . $entity_details['ProfileURL'];
                unset($entity_details['PageID']);
            }
            return $entity_details;
        }
        if ($entity_type == "ACTIVITY")
        {
            $this->db->select('A.*, U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
            $this->db->from(ACTIVITY . ' A');
            $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
            $this->db->where('A.ActivityGUID', $entity_guid);
            $this->db->limit(1);
            $result = $this->db->get();
            $activity = array();            
            if ($result->num_rows())
            {
                foreach ($result->result_array() as $res)
                {
                    $activity_id                = $res['ActivityID'];
                    $activity_guid              = $res['ActivityGUID'];
                    $module_id                  = $res['ModuleID'];
                    $activity_type_id           = $res['ActivityTypeID'];
                    $module_entity_id           = $res['ModuleEntityID'];
                                       

                    $activity['ModuleEntityID'] = $module_entity_id; 
                    $activity['ActivityGUID']   = $activity_guid;
                    $activity['ModuleID']       = $module_id;
                    $activity['UserGUID']       = $res['UserGUID'];
                    $activity['NoOfComments']   = $res['NoOfComments'];
                    $activity['NoOfLikes']      = $res['NoOfLikes'];
                    $activity['CreatedDate']    = $res['CreatedDate'];
                    $activity['ModifiedDate']   = $res['LastActionDate'];
                    $activity['PostContent']    = $res['PostContent']; 
                    $activity['PostTitle']      = $res['PostTitle'];
                  

                    $activity['EntityName']           = '';
                    $activity['EntityProfilePicture'] = '';
                    $activity['EntityProfileURL']   = '';

                    $activity['UserName']           = $res['FirstName'] . ' ' . $res['LastName'];
                    $activity['UserProfilePicture'] = $res['ProfilePicture'];
                    $activity['UserProfileURL']     = get_entity_url($res['UserID'], 'User', 1);
                    
                    if ($module_id == 1)
                    {
                        $group_details = check_group_permissions($user_id, $module_entity_id);
                        if (isset($group_details['Details']) && !empty($group_details['Details']))
                        {
                            $entity                         = $group_details['Details'];
                            $activity['EntityProfileURL']   = $module_entity_id;
                            $activity['EntityGUID']         = $entity['GroupGUID'];
                            $activity['EntityName']         = $entity['GroupName'];
                            $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                            
                            if ($res['ModuleEntityOwner'] == 1)
                            {
                                $activity['UserName']           = $activity['EntityName'];
                                $activity['UserProfilePicture'] = $activity['EntityProfilePicture'];
                                $activity['UserProfileURL']     = $activity['EntityProfileURL'];
                                $activity['UserGUID']           = $activity['EntityGUID'];
                            }                       
                        }
                    }
                    if ($module_id == 3)
                    {
                        $activity['EntityName'] = $activity['UserName'];
                        $activity['EntityProfilePicture'] = $activity['UserProfilePicture'];
                        $activity['EntityGUID'] = $activity['UserGUID'];

                        if($module_entity_id != $user_id)
                        {
                            $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                            if ($entity)
                            {
                                $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                                $activity['EntityName'] = $entity['EntityName'];
                                $activity['EntityGUID'] = $entity['UserGUID'];
                            }

                            $activity['EntityProfileURL'] = get_entity_url($module_entity_id, 'User', 1);
                        }
                    }
                    if($module_id == 34)
                    {
                        $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                        if ($entity)
                        {
                            $this->load->model('forum/forum_model');
                            $activity['EntityName'] = $entity['Name'];
                            $activity['EntityProfilePicture'] = $entity['MediaID'];
                            $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                            $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                        }
                    }

                    if (!isset($activity['EntityProfileURL']))
                    {
                        $activity['EntityProfileURL'] = $activity['UserProfileURL'];
                    }
                    
                    $activity['PostContent'] = $this->parse_tag($activity['PostContent']);
                    $activity['PostTitle'] = $this->parse_tag($activity['PostTitle']);
                    
                    $activity['PostContent'] = trim(strtr($activity['PostContent'], array('&nbsp;' => ' ')));
                    $activity['PostTitle'] = trim(strtr($activity['PostTitle'], array('&nbsp;' => ' ')));
                    
                    $activity['ActivityURL'] = get_single_post_url($activity, $activity_id, $activity_type_id, $module_entity_id);

                    //check is liked or not
                    $like_entity_type = 'ACTIVITY';
                    $like_entity_id = $activity_id;               

                    $activity['IsLike'] = $this->is_liked($like_entity_id, $like_entity_type, $user_id, 3, $user_id); 

                    $activity['IsSubscribed']   = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);                    
                }
            }
            return $activity;
        }
    }

    /**
     * @param $group_id
     * @return string
     */
    public function get_group_recent_activity($group_id)
    {
        $this->db->select('PostContent');
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleEntityID', $group_id);
        $this->db->where('ModuleID', 1);
        $this->db->where('PostContent !=', '');
        $this->db->where('StatusID', 2);
        $this->db->order_by('CreatedDate', 'DESC');
        $this->db->limit(1);
        $res = $this->db->get()->row_array();
        if (!empty($res))
        {
            $res['PostContent'] = $this->parse_tag($res['PostContent']);
            return $res['PostContent'];
        } else
        {
            return '';
        }
    }

    /**
     * @param $activity_id
     * @param $user_id
     * @return boolean
     */
    public function is_tagged($activity_id, $user_id)
    {
        $activity_cache=array();
        if(CACHE_ENABLE && $activity_id)
            {
                $activity_cache=$this->cache->get('activity_'.$activity_id); 
                if(!empty($activity_cache))
                {
                    if(!empty($activity_cache['mtd']))
                    {
                        foreach($activity_cache['mtd'] as $item)
                        {
                          if($item['mid']==3 && $item['meid']==$user_id)  
                          {
                              return 1;
                          }
                        }
                        return 0;
                    }
                }
            }
        if(empty($activity_cache))
        {
            initiate_worker_job('activity_cache', array('ActivityID'=>$activity_id ));
            $this->db->select('MentionID');
            $this->db->from(MENTION);
            $this->db->where('Type', '1');
            $this->db->where('ActivityID', $activity_id);
            $this->db->where('ModuleID', '3');
            $this->db->where('ModuleEntityID', $user_id);
            $this->db->where('StatusID', 2);
            $query = $this->db->get();
            if ($query->num_rows())
            {
                return 1;
            } else
            {
                return 0;
            }
        }
    }

    /**
     * @param $activity_id
     * @param $user_id
     * @return boolean
     */
    public function set_user_tagged($user_id)
    {
        $this->db->select('GROUP_CONCAT(ActivityID) as ActivityIDs');
        $this->db->from(MENTION);
        $this->db->where('Type', '1');
        $this->db->where('StatusID', 2);
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0)
        {
            $row_ids=$query->row_array();
            if(!empty($row_ids['ActivityIDs']))
            {
                $this->tagged_data=  explode(',',$row_ids['ActivityIDs']);
            }
            /*foreach ($query->result_array() as $result)
            {
                $this->tagged_data[] = $result['ActivityID'];
            }*/
        }
    }

    /**
     * 
     * @param type $user_id
     * @return type
     */
    public function get_user_tagged()
    {
        return $this->tagged_data;
    }

    /**
     * @param $activity_id
     * @param $user_id
     * @return return single activity
     */
    public function remove_tags($activity_guid, $user_id)
    {
        $activity_id = get_detail_by_guid($activity_guid);

        $this->db->select('Title,MentionID');
        $this->db->from(MENTION);
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('Type', '1');
        $this->db->where('ActivityID', $activity_id);
        $query = $this->db->get();

        $this->db->set('StatusID', '3');
        $this->db->where('EntityType', 'ACTIVITY');
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('EntityID', $activity_id);
        $this->db->update(SUBSCRIBE);

        if ($query->num_rows())
        {
            $row = $query->row();
            $title = $row->Title;
            $mention_id = $row->MentionID;
            
            $this->db->set('StatusID',3);
            $this->db->where('MentionID', $mention_id);
            $this->db->update(MENTION);
            
            $this->db->set("PostContent", "REPLACE(PostContent, '{{" . $mention_id . "}}', '" . $title . "')", false);
            $this->db->where('ActivityID', $activity_id);
            $this->db->update(ACTIVITY);

            //$activity_details = $this->getFeedActivities($user_id, 1, 1, 1, 0, 0, 2, false, false, false, 0, 0, array(), $activity_guid, array());
            $activity_details = $this->getSingleUserActivity($user_id, $activity_id, $AllActivity = 0, '', '', '','');
            if(CACHE_ENABLE && $activity_id)
            {
                $activity_cache=$this->cache->get('activity_'.$activity_id);
                if(!empty($activity_cache))
                {
                    if(!empty($activity_cache['mtd']))
                    {
                        unset($activity_cache['mtd'][$mention_id]);
                        $mt_id=array_keys($activity_cache['mtd']);
                        $activity_cache['mt']='';
                        if(!empty($mt_id))
                        {
                            $activity_cache['mt']=  implode(',', $mt_id);
                        }
                        $this->cache->delete('activity_'.$activity_id);
                        $this->cache->save('activity_'.$activity_id, $activity_cache,CACHE_EXPIRATION);
                    }
                }
            }
            
            if (isset($activity_details[0]))
            {
                return $activity_details[0];
            }
        }
    }

    public function flag_users_detail($user_id, $entity_id, $entity_type)
    {
        $this->db->select('FL.FlagReason,U.UserID, U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL');
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(FLAG . ' FL');
        $this->db->join(USERS . ' U', 'U.UserID=FL.UserID');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
        $this->db->where('FL.EntityID', $entity_id);
        $this->db->where('FL.EntityType', $entity_type);
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        $final_user_list = array();
        if ($query->num_rows())
        {
            $users = $query->result_array();
            foreach ($users as $value)
            {
                $users_relation = get_user_relation($user_id, $value['UserID']);
                $privacy_details = $this->privacy_model->details($value['UserID']);
                $privacy = ucfirst($privacy_details['Privacy']);
                $value['FlagReason'] = rtrim($value['FlagReason'], ",");
                $value['FlagReason'] = str_replace(',', ', ', $value['FlagReason']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                        {
                            $value['ProfilePicture'] = 'user_default.jpg';
                        }
                    }
                }
                unset($value['UserID']);
                $final_user_list[] = $value;
            }
        }
        return $final_user_list;
    }

    /**
     * [get_entity_files Used to get all uploaded files from selected entity's album]
     * @param  [strng] $module_id (data filters for result set)    
     * @param  [strng] $modle_entity_id (data filters for result set)    
     * @param  [number] $limit (data filters for result set)    
     * @param  [number] $page (data filters for result set)    
     * @return [array] resulting array (Filtered Files Information)
     */
    function get_entity_files($user_id, $module_id = '', $module_entity_id = '', $search_key = '', $page_size = 20, $page_no = 1, $count_only = FALSE, $activity_ids = array(), $media_ids = array(), $allowed_media = FALSE)
    {
        $resultSet = array();
        $is_relation = array(1);
        if (isset($module_id, $module_entity_id) && $module_id == 3)
            $is_relation = $this->isRelation($module_entity_id, $user_id, true);

        if ($count_only)
        {
            $this->db->select('count(Media.MediaGUID) as totalFiles');
        } else
        {
            $this->db->select('Media.MediaGUID, MediaSection.MediaSectionAlias as MediaFolder, Media.OriginalName, Media.ImageName, Media.Size, Media.Caption, Media.CreatedDate, CONCAT(User.FirstName," ",User.LastName) as Name, MediaExtension.Name as MediaExtension,Media.ConversionStatus, User.UserID, User.UserGUID, ProfileUrl.Url as ProfileURL, MediaType.Name EntityType'); /* Activity.Privacy,Activity.ActivityGUID, Activity.ModuleID,Activity.ModuleEntityID,Activity.ModuleEntityOwner, */
        }

        $this->db->from(MEDIA . " AS Media");
        $this->db->join(MEDIAEXTENSIONS . " AS MediaExtension", ' MediaExtension.MediaExtensionID = Media.MediaExtensionID', 'left');
        $this->db->join(MEDIATYPES . " AS MediaType", ' MediaType.MediaTypeID = MediaExtension.MediaTypeID', 'left');
        $this->db->join(MEDIASECTIONS . " AS MediaSection", ' MediaSection.MediaSectionID = Media.MediaSectionID', 'left');
        $this->db->join(ALBUMS . ' AS Album', 'Media.AlbumID = Album.AlbumID', 'left');
        $this->db->join(USERS . " AS User", ' User.UserID = Media.UserID', 'left');
        $this->db->join(PROFILEURL . " AS ProfileUrl", " User.UserID = ProfileUrl.EntityID AND ProfileUrl.EntityType='User' ", 'left');
        /* $this->db->join(ACTIVITY . " AS Activity", ' Activity.ActivityID = Media.MediaSectionReferenceID AND Activity.StatusID=2 AND Activity.Privacy IN ('.implode(',', $is_relation).')', 'left'); */

        // $this->db->where_in('Activity.Privacy', $is_relation);
        $this->db->where('MediaExtension.MediaExtensionID !=', '9'); //excluding youtube media
        //$this->db->where('Media.MediaSectionID','3');//in case of wall media and files
        //check for given activities only
        $media_cond = $activity_cond = 0;
        if (!empty($activity_ids))
            $activity_cond = 'Media.MediaSectionReferenceID IN (' . implode(',', $activity_ids) . ')'; //'Activity.ActivityID IN (' . implode(',', $activity_ids) . ')';
        if (!empty($media_ids))
            $media_cond = 'Media.MediaID IN (' . implode(',', $media_ids) . ')';
        if ($media_cond || $activity_cond)
            $this->db->where('(' . $activity_cond . ' OR ' . $media_cond . ')', NULL, FALSE);

        if (isset($module_entity_id) && !empty($module_id) && !empty($module_entity_id) && empty($activity_ids) && empty($media_ids))
        {
            $this->db->where('Album.ModuleID', $module_id);
            $this->db->where('Album.ModuleEntityID', $module_entity_id);
        }

        //Filters result by search
        if (isset($search_key) && !empty($search_key))
        {
            $this->db->like('Media.OriginalName', $search_key);
        }

        if ($allowed_media)
        {
            switch ($allowed_media)
            {
                case 1:
                    $this->db->where(array('AlbumName !=' => DEFAULT_FILE_ALBUM));
                    break;
                case 2:
                    $this->db->where('AlbumName', DEFAULT_FILE_ALBUM);
                    break;
                default:
                    break;
            }
        }
        //$this->db->where('AlbumName','Files');        
        $this->db->order_by('Media.CreatedDate', 'DESC');

        $tempdb = clone $this->db;
       // $temp_q = $tempdb->get();
        $total_records = $tempdb->count_all_results();

        if (isset($page_size, $page_no))
        {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }

        $sql = $this->db->get();        //echo $this->db->last_query();
        $result_set = array();
        $result_set['TotalRecords'] = isset($total_records) ? $total_records : 1;
        foreach ($sql->result_array() as $result)
        {
            if (empty($result['ProfileURL']))
            {
                $result['ProfileURL'] = get_entity_url($result['UserID']);
            }
            unset($result['UserID']);
            unset($result['Privacy']);
            $result_set[] = $result;
        }

        return $result_set;
    }

    /**
     * [getFiles Used to get all uploaded files on wall only]
     * @param  [array] $activity_id [activity_id is mandatory to filter out result set by acvity]    
     * @return [array] resulting array [Filtered Files Information]
     */
    function get_activity_files($activity_id)
    {
        if(CACHE_ENABLE && $activity_id )
        {
            $activity_cache=$this->cache->get('activity_'.$activity_id);
            if(!empty($activity_cache))
            {   
                if(!empty($activity_cache['f']))
                return $activity_cache['f'];
            }
        }
        
        $this->db->select('Media.MediaGUID,Media.OriginalName,Media.ImageName,Media.Size,MediaExtension.Name as MediaExtension,MediaType.Name as MediaType');
        $this->db->from(MEDIA . ' AS Media');
        $this->db->join(MEDIAEXTENSIONS . " AS MediaExtension", ' MediaExtension.MediaExtensionID = Media.MediaExtensionID', 'left');
        $this->db->join(MEDIATYPES . " AS MediaType", ' MediaType.MediaTypeID = MediaExtension.MediaTypeID');
        $this->db->where('MediaType.MediaTypeID', '4'); //documents
        $this->db->where('Media.MediaSectionReferenceID', $activity_id);
        $this->db->where_in('Media.StatusID', array(2,10));
        $sql = $this->db->get();
        return $sql->result_array();
    }

    public function update_activity_file_status()
    {
        try
        {
            $this->db->select('A.ActivityID');
            $this->db->from(ACTIVITY . ' AS A');
            $this->db->join(MEDIA . " AS M", ' M.MediaSectionReferenceID = A.ActivityID');
            $this->db->join(MEDIAEXTENSIONS . " AS ME", ' ME.MediaExtensionID = M.MediaExtensionID and ME.MediaTypeID=4 ');
            $sql = $this->db->get();
            $res = $sql->result_array($sql);

            $activities = array();
            foreach ($res as $act)
            {
                $activities [] = $act['ActivityID'] . ',';
            }
            $query = "UPDATE  `Activity` SET  `IsFileExists` =  '1' WHERE  `Activity`.`ActivityID` IN ( " . implode(',', $activities) . ") ";
            //run this query
            $this->db->query($query);
        } catch (Exception $e)
        {
            return false;
        }
        //var_dump($this->db->affected_rows());
        return true;
    }

    /**
     * [get_newsfeed_files Get files in filetab on the basis of newsfeed]
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
    public function get_newsfeed_files($user_id, $page_no = 1, $page_size = 20, $count_only = 0, $search_key = false, $entity_id = '', $entity_module_id = '', $show_archive = 0, $feed_sort_by = 1, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $is_file_exists = 1, $start_date = false, $end_date = false, $reminder_date = array(), $activity_guid = '', $mentions = array(), $activity_type_filter = array())
    {
        # code...
        $this->load->model('polls/polls_model');

        //$time_zone = get_user_time_zone($user_id);
        $time_zone = $this->user_model->get_user_time_zone();
        //$friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        //$privacy_options = $this->privacy_model->news_feed_setting_details($user_id);
        $privacy_options = $this->privacy_model->get_privacy_options();
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];

        //$friend_of_friends = $this->user_model->get_friends_of_friend($user_id, $friends);
        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        //$group_list              = $this->group_model->get_joined_groups($user_id, false, array(2));
        //$group_list = $this->group_model->get_users_groups($user_id);
        $group_list = $this->group_model->get_user_group_list();
        //print_r($group_list);
        $group_list[] = 0;
        $group_list = implode(',', $group_list);
        //$event_list = $this->event_model->get_all_joined_events($user_id);
        $event_list = $this->event_model->get_user_joined_events();
        // $page_list = $this->page_model->get_liked_pages_list($user_id);
        $page_list = $this->page_model->get_user_pages_list();

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 25);//23, 24,
        $modules_allowed = array(3, 30);
        $show_suggestions = FALSE;
        $show_media = TRUE;

        if ($privacy_options)
        {
            foreach ($privacy_options as $key => $val)
            {
                if ($key == 'g' && $val == '0')
                {
                    $modules_allowed[] = 1;
                }
                if ($key == 'e' && $val == '0')
                {
                    $modules_allowed[] = 14;
                }
                if ($key == 'p' && $val == '0')
                {
                    $modules_allowed[] = 18;
                }
                if ($key == 'm')
                {
                    if ($val == '1')
                    {
                        $show_media = FALSE;
                        unset($activity_type_allow[array_search('5', $activity_type_allow)]);
                        unset($activity_type_allow[array_search('6', $activity_type_allow)]);
                    }
                }
                if ($key == 'r' && $val == '0')
                {
                    $activity_type_allow[] = 16;
                    $activity_type_allow[] = 17;
                }
                if ($key == 's' && $val == '0')
                {
                    if ($filter_type == '0' && empty($mentions))
                    {
                        //$show_suggestions = true;
                    }
                }
            }
        }

        if ($filter_type == 3)
        {
            /* 1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18 23, 24, 25 */
            $modules_allowed = array(1, 3, 14, 18, 23, 30);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 30);//23, 24,
        }

        /* --Filter by activity type id-- */
        $activity_ids = array();
        if (!empty($activity_type_filter))
        {
            $activity_type_allow = $activity_type_filter;
            $show_suggestions = false;

            //7 = My Polls, 8= Expired
            if ($filter_type == 7 || $filter_type == 8)
            {
                $is_expired = FALSE;
                if ($filter_type == 8)
                {
                    $is_expired = TRUE;
                }

                $this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_poll_activities($entity_id, $entity_module_id, $is_expired);
                if (empty($activity_ids))
                {
                    return array();
                }
            }
            //My Voted Polls
            if ($filter_type == 9)
            {
                $this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_voted_poll_activities($entity_id, $entity_module_id);
                if (empty($activity_ids))
                {
                    return array();
                }
            }

            //print_r($activity_ids);die;
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed))
        {

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
        if ($friend_followers_list != '')
        {
            $condition = "(
                IF(A.ActivityTypeID=25 OR A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6 OR (A.ActivityTypeID=23 AND A.ModuleID=3) OR (A.ActivityTypeID=24 AND A.ModuleID=3), (
                    A.UserID IN(" . $friend_followers_list . ") OR A.ModuleEntityID IN(" . $friend_followers_list . ") OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (
                    (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (
                    A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'
                ), '' )";

            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
                $privacy_cond2 = "IF(A.Privacy='3',
                    A.UserID IN (" . implode(',', $follow) . "), true
                )";
            }
        }

        // Check parent activity privacy for shared activity
        $privacy_condition = "
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                IF(A.ActivityTypeID IN(9,10),
                    A.ParentActivityID=(
                        SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                            IF(Privacy=2 AND ActivityTypeID!=7,UserID IN (" . $friend_followers_list . "),false) OR
                            IF(Privacy=3 AND ActivityTypeID!=7,UserID IN (" . implode(',', $friends) . "),false) OR
                            IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $user_id . "',false) OR
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $group_list . "),false))
                    ),false
                )
                OR
                IF(A.ActivityTypeID IN(14,15),
                    A.ParentActivityID=(
                        SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                    ),false
                )
            ),         
        true)";

        $privacy_cond3 = "IF(A.Privacy='4',
            A.UserID='" . $user_id . "', true
        )";
        if (!empty($privacy_cond1))
        {
            $privacy_cond .= $privacy_cond1 . ' OR ';
        }
        if (!empty($privacy_cond2))
        {
            $privacy_cond .= $privacy_cond2 . ' OR ';
        }
        $privacy_cond .= " OR A.ActivityID=(SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "')";
        $privacy_cond .= $privacy_cond3 . ' )';

        // /echo $privacy_cond;
        if (!empty($group_list))
        {
            $condition_part_one = $condition_part_one . "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ")
                    ), '' )";
        }
        if (!empty($page_list))
        {
            $condition_part_three = $condition_part_three . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
                        A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ")
                    ), '' )";
        }
        if (!empty($event_list))
        {
            $condition_part_four = $condition_part_four . "IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
                        A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")
                    ), '' )";
        }
        if (!empty($condition))
        {
            if (!empty($condition_part_one))
            {
                $condition = $condition . " OR " . $condition_part_one;
            }
            if (!empty($condition_part_three))
            {
                $condition = $condition . " OR " . $condition_part_three;
            }
            if (!empty($condition_part_four))
            {
                $condition = $condition . " OR " . $condition_part_four;
            }
            $condition = $condition . ")";
        } else
        {

            if (!empty($condition_part_one))
            {
                $condition = $condition_part_one;
            }
            if (!empty($condition_part_three))
            {
                if (empty($condition))
                {
                    $condition = $condition_part_three;
                } else
                {
                    $condition = $condition . " OR " . $condition_part_three;
                }
            }

            if (empty($condition))
            {
                $condition = $condition_part_two;
            } 
            else
            {
                //$condition = $condition_part_two. " OR ".$condition_part_one; 
                $condition = "(" . $condition . ")";
            }
        }
        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        //ELSE A.ActivityID=(SELECT ActivityID FROM ".MENTION." WHERE ModuleID=3 AND ModuleEntityID='".$user_id."')
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";

        $this->db->select('A.ActivityID,PC.PostCommentID,A.ActivityTypeID,A.ParentActivityID,A.Params'); //MD.MediaID
        // $this->db->select('(CASE A.ActivityType WHEN 1 THEN G.GroupName WHEN 3 THEN U.FirstName END)');
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');

        //Getting Comments and its media in the result set
        $this->db->join(POSTCOMMENTS . ' PC', 'PC.EntityID=A.ActivityID AND PC.StatusID=2', 'left');

        /* $this->db->join(MEDIA . ' MD', '(MD.MediaSectionReferenceID=A.ActivityID OR MD.MediaSectionReferenceID=PC.PostCommentID) AND MD.StatusID=2', 'left'); */

        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        $this->db->_protect_identifiers = TRUE;


        /* Join Activity Links Starts */
        //$this->db->select('IF(URL is NULL,0,1) as IsLinkExists', false);
        //$this->db->select('URL as LinkURL,AL.Title as LinkTitle,MetaDescription as LinkDesc,ImageURL as LinkImgURL,TagsCollection as LinkTags');
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        /* Join Activity Links Ends */
        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } else {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } else if ($filter_type === 'Favourite')
            {
                $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"');
                $this->db->where('F.UserID', $user_id);
                $this->db->where('F.StatusID', '2');
            } else
            {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43))
                {
                    $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids)
            {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            if ($mentions)
            {
                $join_condition = "MN.ActivityID=A.ActivityID AND (";
                foreach ($mentions as $mention)
                {
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
            if ($activity_guid)
            {
                $this->db->where('A.ActivityGUID', $activity_guid);
            }

            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        }


        if ($filter_type == 1)
        {
            $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"', 'right');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
        } else if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
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
        }

        $this->db->where('(A.IsMediaExist=\'1\' OR A.IsFileExists=\'1\' OR PC.IsMediaExists=\'1\' OR PC.IsFileExists=\'1\')', NULL, FALSE);

        if (!empty($blocked_users) && empty($feed_user))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        //$this->db->where_in('A.StatusID',array('1','2'));
        $this->db->where('ATY.StatusID', '2');
        if (empty($activity_ids))
        {
            if (!empty($condition))
            {
                $this->db->where($condition, NULL, FALSE);
            } else
            {
                $this->db->where('A.ModuleID', '3');
                $this->db->where('A.ModuleEntityID', $user_id);
            }
            if ($privacy_condition)
            {
                $this->db->where($privacy_condition, null, false);
            }
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            //$this->db->select("R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus", FALSE);
            $this->db->select("IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder", false);

            //$this->db->select("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Asia/Calcutta','Etc/UTC'),'%Y-%m-%d') as ReminderDate",FALSE);

            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3)
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



            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            $this->db->_protect_identifiers = TRUE;
        }

        if ($feed_sort_by != 2)
        {
            //$this->db->order_by('UARRANK','ASC');    
        }

        if ($feed_sort_by == 'popular')
        {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } elseif ($feed_sort_by == 1)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($filter_type == 3)
        {
            if ($reminder_date)
            {
                $rd_data = array();
                foreach ($reminder_date as $rd)
                {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        $result = $this->db->get();

        if ($count_only)
        {
            return $result->num_rows();
        }
        $activity_ids = $media_ids = $comment_ids = $files = array();
        if ($result->num_rows())
        {
            foreach ($result->result_array() as $res)
            {
                $activity_ids[] = $res['ActivityID'];
                if (isset($res['PostCommentID']) && !empty($res['PostCommentID']) && !in_array($res['PostCommentID'], $comment_ids))
                {
                    $comment_ids[] = $res['PostCommentID'];
                }

                if ($res['ActivityTypeID'] == 5 || $res['ActivityTypeID'] == 6 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 9)
                {
                    $album_flag = TRUE;
                    //check if this entity shared someone else post so here we can get original postID
                    if ($res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($res['ParentActivityID'], '', 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_ids[] = $parent_activity_detail['ActivityID'];
                    }
                }

                //When a friend has updated his profile or some pictures from album 
                if ($res['ActivityTypeID'] == 23 || $res['ActivityTypeID'] == 24)
                {
                    $params = json_decode($res['Params']);
                    if ($params->MediaGUID)
                    {
                        $media_ids[] = get_detail_by_guid($params->MediaGUID, 21);
                    }
                }
            }
            //filter out duplicate records 
            $activity_ids = array_unique($activity_ids);
            $comment_ids = array_unique($comment_ids);
            $activity_ids = array_merge($activity_ids, $comment_ids);
            $media_ids = array_unique($media_ids);
            //var_dump($activity_ids);var_dump($media_ids);die;
            //find all media on the basis of given parameters
            $files = $this->get_entity_files($user_id, $entity_module_id, $entity_id, $search_key, $page_size, $page_no, false, $activity_ids, $media_ids);
        }
        return $files;
    }

    /**
     * [get_wall_files Get the activity for wall]
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
    public function get_wall_files($entity_id, $module_id, $search_key, $page_no, $page_size, $current_user, $feed_sort_by = 1, $filter_type = 0, $is_media_exists = 2, $activity_guid = '', $start_date = '', $end_date = '', $feed_user = 0, $as_owner = 0, $count_only = false, $field = 'ALL', $activity_type_filter = array(), $m_entity_id = '', $entity_module_id = '', $comment_id = '')
    {
        $time_zone = $this->user_model->get_user_time_zone();
        $this->load->model('polls/polls_model');
        $group_list = $this->group_model->get_user_group_list();
        $group_list[] = 0;
        $group_list_new = $group_list;
        $group_list = implode(',', $group_list);
        if ($module_id == 3)
        {
            $page_list = $this->page_model->get_liked_pages_list($entity_id);
            $event_list_current_user = $this->event_model->get_all_joined_events($current_user, true);
            $event_list_current_user[] = 0;
            $event_list = $event_list_current_user;

            if ($current_user != $entity_id)
            {
                $group_list_temp = $this->group_model->get_users_groups($entity_id);
                $group_list_temp[] = 0;
                $group_list_new = array_intersect($group_list_new, $group_list_temp);

                $this->db->select('GroupID');
                $this->db->from(GROUPS);
                $this->db->where('IsPublic', 1);
                $this->db->where_in('GroupID', $group_list_temp);
                $query = $this->db->get();
                if ($query->num_rows())
                {
                    foreach ($query->result_array() as $key => $value)
                    {
                        $group_list_new[] = $value['GroupID'];
                    }
                }

                $event_list_view_user = $this->event_model->get_all_joined_events($entity_id, true);
                $event_list_view_user[] = 0;
                $event_list = array_intersect($event_list_current_user, $event_list_view_user);
            }
        }
        // $blocked_users = $this->block_user_list($entity_id, $module_id);
        $blocked_users = $this->blocked_users;
        $condition = '';
        if ($module_id == 3)
        {
            $permission = $this->privacy_model->check_privacy($current_user, $entity_id, 'default_post_privacy');
        }

        if ($module_id == 3)
        {
            $is_relation = $this->isRelation($entity_id, $current_user, true); // Visibility
        } else
        {
            $is_relation = array(1, 2, 3, 4);
        }
        if ($module_id == 1)
        {
            $activity_type_allow = array(7, 5, 6, 25);//23, 24,
        } else if ($module_id == 3)
        {
            $activity_type_allow = array(1, 8, 9, 10, 5, 6, 14, 15, 25, 7, 11, 12);//23, 24,
        } else if ($module_id == 14)
        {
            $activity_type_allow = array(11, 5, 6);//, 23, 24
        } else if ($module_id == 18)
        {
            $activity_type_allow = array(1, 8, 12, 5, 6, 25);//23, 24,
        } else if ($module_id == 34)
        {
            $activity_type_allow = array(26, 5, 6);//, 23, 24
        }
        else
        {
            $activity_type_allow = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 25, 26);//23, 24, 
        }
        // $friend_followers_list = $this->user_model->gerFriendsFollowersList($current_user, true, 1);
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = 0;
        $follow[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($current_user, $friend_followers_list))
        {
            $friend_followers_list[] = $current_user;
        }
        $friend_followers_list = implode(',', $friend_followers_list);

        if (!in_array($current_user, $follow))
        {
            $follow[] = $current_user;
        }

        if (!in_array($current_user, $friends))
        {
            $friends[] = $current_user;
        }

        // Check parent activity privacy for shared activity
        $privacy_condition = "
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                IF(A.ActivityTypeID IN(9,10),
                    A.ParentActivityID=(
                        SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                            IF(Privacy=2 AND ActivityTypeID!=7,UserID IN (" . $friend_followers_list . "),false) OR
                            IF(Privacy=3 AND ActivityTypeID!=7,UserID IN (" . implode(',', $friends) . "),false) OR
                            IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $current_user . "',false) OR
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $group_list . "),false))
                    ),false
                )
                OR
                IF(A.ActivityTypeID IN(14,15),
                    A.ParentActivityID=(
                        SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                    ),false
                )
            ),         
        true)";

        if ($field == 'ALL')
        {
            $this->db->select('A.ActivityID,A.ActivityTypeID,PC.PostCommentID,A.Params,A.ParentActivityID');
        }
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(POSTCOMMENTS . ' PC', "PC.EntityID=A.ActivityID AND (PC.IsMediaExists='1' OR PC.IsFileExists='1')", 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        /* $this->db->join(MEDIA . ' MD', '(MD.MediaSectionReferenceID=A.ActivityID OR MD.MediaSectionReferenceID=PC.PostCommentID) AND MD.StatusID=2', 'left'); */
        if ($filter_type == 1)
        {
            $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"', 'right');
            $this->db->where('F.UserID', $current_user);
            $this->db->where('F.StatusID', '2');
        } else if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            //$this->db->where('F.UserID',$current_user);
            $this->db->where('F.StatusID', '2');
            $this->db->where('A.Flaggable!=0', null, false);
            $this->db->group_by('F.EntityID');
        }

        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $current_user);
        } else if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $current_user . "'", "join");
            $this->db->_protect_identifiers = TRUE;
        } else
        {
            $this->db->where("IF(A.UserID='" . $current_user . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        }

        if (!$activity_guid && $filter_type != 4 && !$this->settings_model->isDisabled(43))
        {
            $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $current_user . "')", NULL, FALSE);
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            $this->db->select("R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus", FALSE);
            $this->db->select("IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder", false);

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



            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            $this->db->_protect_identifiers = TRUE;
        }

        if ($condition)
        {
            $this->db->where($condition, null, false);
        }
        if ($privacy_condition)
        {

            if ($module_id == 3)
            {
                if (!$permission)
                {
                    $permission_cond = "IF(A.UserID IN(" . $entity_id . "," . $current_user . "),TRUE,FALSE)";
                } else
                {
                    $permission_cond = "IF(TRUE,TRUE,FALSE)";
                }
                $pc = "(
                        IF(A.UserID=A.ModuleEntityID," . $privacy_condition . "," . $permission_cond . ")
                    )";
                $this->db->where($pc, null, false);
            } else
            {
                $this->db->where($privacy_condition, null, false);
            }
        }
        $this->db->where('(A.IsMediaExist=\'1\' OR A.IsFileExists=\'1\' OR PC.IsMediaExists=\'1\' OR PC.IsFileExists=\'1\')', NULL, FALSE);

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
        
        $module_id = $this->db->escape_str($module_id);
        $entity_id = $this->db->escape_str($entity_id);

        $mention_condition = "
            ((A.ModuleID='" . $module_id . "' AND A.ModuleEntityID='" . $entity_id . "') OR (A.ActivityID IN(SELECT ActivityID FROM " . MENTION . " WHERE ModuleID='" . $module_id . "' AND ModuleEntityID='" . $entity_id . "'))";

        if ($module_id == 3)
        {
            if (!empty($page_list))
            {
                $mention_condition.= " OR IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
          A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }

            if (!empty($group_list_new))
            {
                $group_list_new = implode(',', $group_list_new);
                $mention_condition .= " OR IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
          A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list_new . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }

            if (!empty($event_list))
            {
                $event_list = implode(',', $event_list);
                $mention_condition .= " OR IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
          A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }
        }

        $mention_condition .= ") ";

        //$this->db->where('A.ModuleI D',$module_id);
        if ($activity_guid)
        {
            $this->db->where('ActivityGUID', $activity_guid);
        } else
        {
            $this->db->where($mention_condition, null, false);
            //$this->db->where('A.ModuleEntityID',$entity_id);
        }

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        $this->db->where("(A.UserID='" . $current_user . "' OR A.Privacy IN(" . implode(',', $is_relation) . ") OR (SELECT ActivityID FROM " . MENTION . " WHERE ModuleID='3' AND ModuleEntityID='" . $current_user . "' AND ActivityID=A.ActivityID LIMIT 1) is not null)", NULL, FALSE);

        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        //$this->db->where_in('A.Privacy',$is_relation);
        $this->db->where_not_in('U.StatusID', array(3, 4));
        $this->db->order_by('A.StickyDate', 'DESC');
        if ($feed_sort_by == 1)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }
        $result = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($count_only)
        {
            return $result->num_rows();
        }
        if ($field != 'ALL')
        {
            return $result->result_array();
        }
        //  echo $this->db->last_query();die;
        $return = $media_ids = $comment_ids = $activity_ids = array();
        if ($result->num_rows())
        {
            foreach ($result->result_array() as $res)
            {
                $activity_ids[] = $res['ActivityID'];
                //check Media and insert into media array
                /* if(isset($res['MediaID']) && !empty($res['MediaID']) && !in_array($res['MediaID'], $media_ids))
                  {
                  $media_ids[] = $res['MediaID'];
                  } */
                //comments
                if (isset($res['PostCommentID']) && !empty($res['PostCommentID']) && !in_array($res['PostCommentID'], $comment_ids))
                {
                    $comment_ids[] = $res['PostCommentID'];
                }
                if ($res['ActivityTypeID'] == 5 || $res['ActivityTypeID'] == 6 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 9)
                {
                    $album_flag = TRUE;
                    //check if this entity shared someone else post so here we can get original postID
                    if ($res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($res['ParentActivityID'], '', 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        $activity_ids[] = $parent_activity_detail['ActivityID'];
                    }
                }

                //When a friend has updated his profile or some pictures from album 
                if ($res['ActivityTypeID'] == 23 || $res['ActivityTypeID'] == 24)
                {
                    $params = json_decode($res['Params']);
                    if ($params->MediaGUID)
                    {
                        $media_ids[] = get_detail_by_guid($params->MediaGUID, 21);
                    }
                }
            }
            //filter out duplicate records 
            $activity_ids = array_unique($activity_ids);
            $comment_ids = array_unique($comment_ids);
            $activity_ids = array_merge($activity_ids, $comment_ids);
            $media_ids = array_unique($media_ids);

            //find all media on the basis of given parameters
            $return = $this->get_entity_files($current_user, $module_id, $entity_id, $search_key, $page_size, $page_no, false, $activity_ids, $media_ids);
        }
        return $return;
    }



    /**
     * [get_wall_links Get the activity for wall]
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
    public function get_wall_links($entity_id, $module_id, $search_key, $page_no, $page_size, $current_user, $feed_sort_by = 1, $filter_type = 0, $is_media_exists = 2, $activity_guid = '', $start_date = '', $end_date = '', $feed_user = 0, $as_owner = 0, $count_only = false, $field = 'ALL', $activity_type_filter = array(), $m_entity_id = '', $entity_module_id = '', $comment_id = '')
    {
        $time_zone = $this->user_model->get_user_time_zone();
        $this->load->model(array('polls/polls_model','category/category_model'));
        $group_list = $this->group_model->get_user_group_list();
        $group_list[] = 0;
        $group_list_new = $group_list;
        $group_list = implode(',', $group_list);
        if ($module_id == 3)
        {
            $page_list = $this->page_model->get_liked_pages_list($entity_id);
            $event_list_current_user = $this->event_model->get_all_joined_events($current_user, true);
            $event_list_current_user[] = 0;
            $event_list = $event_list_current_user;

            if ($current_user != $entity_id)
            {
                $group_list_temp = $this->group_model->get_users_groups($entity_id);
                $group_list_temp[] = 0;
                $group_list_new = array_intersect($group_list_new, $group_list_temp);

                $this->db->select('GroupID');
                $this->db->from(GROUPS);
                $this->db->where('IsPublic', 1);
                $this->db->where_in('GroupID', $group_list_temp);
                $query = $this->db->get();
                if ($query->num_rows())
                {
                    foreach ($query->result_array() as $key => $value)
                    {
                        $group_list_new[] = $value['GroupID'];
                    }
                }

                $event_list_view_user = $this->event_model->get_all_joined_events($entity_id, true);
                $event_list_view_user[] = 0;
                $event_list = array_intersect($event_list_current_user, $event_list_view_user);
            }
        }
        // $blocked_users = $this->block_user_list($entity_id, $module_id);
        $blocked_users = $this->blocked_users;
        $condition = '';
        if ($module_id == 3)
        {
            $permission = $this->privacy_model->check_privacy($current_user, $entity_id, 'default_post_privacy');
        }

        if ($module_id == 3)
        {
            $is_relation = $this->isRelation($entity_id, $current_user, true); // Visibility
        } else
        {
            $is_relation = array(1, 2, 3, 4);
        }
        if ($module_id == 1)
        {
            $activity_type_allow = array(7, 5, 6, 25);//23, 24,
        } else if ($module_id == 3)
        {
            $activity_type_allow = array(1, 8, 9, 10, 5, 6, 14, 15, 25, 7, 11, 12);//23, 24,
        } else if ($module_id == 14)
        {
            $activity_type_allow = array(11, 5, 6);//23, 24
        } else if ($module_id == 18)
        {
            $activity_type_allow = array(1, 8, 12, 5, 6, 25);// 23, 24,
        }else if ($module_id == 34)
        {
            $activity_type_allow = array(26, 5, 6);//23, 24
        } else
        {
            $activity_type_allow = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 25, 26);// 23, 24,
        }
        // $friend_followers_list = $this->user_model->gerFriendsFollowersList($current_user, true, 1);
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = 0;
        $follow[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($current_user, $friend_followers_list))
        {
            $friend_followers_list[] = $current_user;
        }
        $friend_followers_list = implode(',', $friend_followers_list);

        if (!in_array($current_user, $follow))
        {
            $follow[] = $current_user;
        }

        if (!in_array($current_user, $friends))
        {
            $friends[] = $current_user;
        }

        // Check parent activity privacy for shared activity
        $privacy_condition = "
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                IF(A.ActivityTypeID IN(9,10),
                    A.ParentActivityID=(
                        SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                            IF(Privacy=2 AND ActivityTypeID!=7,UserID IN (" . $friend_followers_list . "),false) OR
                            IF(Privacy=3 AND ActivityTypeID!=7,UserID IN (" . implode(',', $friends) . "),false) OR
                            IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $current_user . "',false) OR
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $group_list . "),false))
                    ),false
                )
                OR
                IF(A.ActivityTypeID IN(14,15),
                    A.ParentActivityID=(
                        SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                    ),false
                )
            ),         
        true)";

        if ($field == 'ALL')
        {
            //$this->db->select('A.ActivityID,A.ActivityTypeID,PC.PostCommentID,A.Params,A.ParentActivityID');
            $this->db->select('A.ActivityGUID,A.ActivityID, ATY.ActivityTypeID, A.PostTitle, A.PostType, A.PostContent,A.ModuleEntityID,A.ModuleID, A.PostAsModuleID,A.PostAsModuleEntityID,AL.URL, AL.Title, AL.MetaDescription,AL.ImageURL,AL.UserID,U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture,concat(U.FirstName,\' \',U.LastName) as Name, AL.CreatedDate');
        }
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID','inner');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(POSTCOMMENTS . ' PC', "PC.EntityID=A.ActivityID AND (PC.IsMediaExists='1' OR PC.IsFileExists='1')", 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        /* $this->db->join(MEDIA . ' MD', '(MD.MediaSectionReferenceID=A.ActivityID OR MD.MediaSectionReferenceID=PC.PostCommentID) AND MD.StatusID=2', 'left'); */

        if ($filter_type == 1)
        {
            $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"', 'right');
            $this->db->where('F.UserID', $current_user);
            $this->db->where('F.StatusID', '2');
        } else if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            //$this->db->where('F.UserID',$current_user);
            $this->db->where('F.StatusID', '2');
            $this->db->where('A.Flaggable!=0', null, false);
            $this->db->group_by('F.EntityID');
        }

        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $current_user);
        } else if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $current_user . "'", "join");
            $this->db->_protect_identifiers = TRUE;
        } else
        {
            $this->db->where("IF(A.UserID='" . $current_user . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        }

        if (!$activity_guid && $filter_type != 4 && !$this->settings_model->isDisabled(43))
        {
            $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $current_user . "')", NULL, FALSE);
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            $this->db->select("R.ReminderDateTime", FALSE);
            $this->db->select("IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder", false);

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



            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            $this->db->_protect_identifiers = TRUE;
        }

        if ($condition)
        {
            $this->db->where($condition, null, false);
        }
        if ($privacy_condition)
        {

            if ($module_id == 3)
            {
                if (!$permission)
                {
                    $permission_cond = "IF(A.UserID IN(" . $entity_id . "," . $current_user . "),TRUE,FALSE)";
                } else
                {
                    $permission_cond = "IF(TRUE,TRUE,FALSE)";
                }
                $pc = "(
                        IF(A.UserID=A.ModuleEntityID," . $privacy_condition . "," . $permission_cond . ")
                    )";
                $this->db->where($pc, null, false);
            } else
            {
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
        
        $module_id = $this->db->escape_str($module_id);
        $entity_id = $this->db->escape_str($entity_id);
        
        
        $mention_condition = "
            ((A.ModuleID='" . $module_id . "' AND A.ModuleEntityID='" . $entity_id . "') OR (A.ActivityID IN(SELECT ActivityID FROM " . MENTION . " WHERE ModuleID='" . $module_id . "' AND ModuleEntityID='" . $entity_id . "'))";

        if ($module_id == 3)
        {
            if (!empty($page_list))
            {
                $mention_condition.= " OR IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
          A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }

            if (!empty($group_list_new))
            {
                $group_list_new = implode(',', $group_list_new);
                $mention_condition .= " OR IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
          A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list_new . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }

            if (!empty($event_list))
            {
                $event_list = implode(',', $event_list);
                $mention_condition .= " OR IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
          A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }
        }

        $mention_condition .= ") ";

        //$this->db->where('A.ModuleI D',$module_id);
        if ($activity_guid)
        {
            $this->db->where('ActivityGUID', $activity_guid);
        } else
        {
            $this->db->where($mention_condition, null, false);
            //$this->db->where('A.ModuleEntityID',$entity_id);
        }

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        $this->db->where("(A.UserID='" . $current_user . "' OR A.Privacy IN(" . implode(',', $is_relation) . ") OR (SELECT ActivityID FROM " . MENTION . " WHERE ModuleID='3' AND ModuleEntityID='" . $current_user . "' AND ActivityID=A.ActivityID LIMIT 1) is not null)", NULL, FALSE);

        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        //$this->db->where_in('A.Privacy',$is_relation);
        $this->db->where_not_in('U.StatusID', array(3, 4));
        $this->db->order_by('A.StickyDate', 'DESC');
        //search
        if (!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(AL.MetaDescription LIKE "%' . $search_key . '%" OR AL.Title LIKE "%' . $search_key . '%")', NULL, FALSE);
        }

        $this->db->order_by('AL.CreatedDate', 'DESC');
        //clone to find total records
        $tempdb = clone $this->db;
       // $temp_q = $tempdb->get();
        $total_records = $tempdb->count_all_results();
        if (isset($page_size, $page_no))
        {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }        
        
        $result = $this->db->get();
        // echo $this->db->last_query(); die;
        $result_set = array();
        $result_set['TotalRecords'] = isset($total_records) ? $total_records : 1;
        if ($result->num_rows())
        {
            foreach ($result->result_array() as $res)
            {
                $res['UserProfileURL'] = get_entity_url($res['UserID'], 'User', 1);
                switch ($res['ModuleID']) {
                    case '1':
                        # code...
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], "Type, GroupGUID, GroupName, GroupImage", 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['GroupName'];
                            $res['EntityProfilePicture'] = $entity['GroupImage'];
                            $res['EntityGUID'] = $entity['GroupGUID'];                              
                            $res['EntityProfileURL']   = $this->group_model->get_group_url($res['ModuleEntityID'], $entity['GroupName'], true, 'index');
                        }
                        break;
                    case '3':
                        # code...
                        $res['EntityName'] = $res['Name'];
                        $res['EntityProfilePicture'] = $res['ProfilePicture'];
                        $res['EntityGUID'] = $res['UserGUID'];

                        //$entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], 'CONCAT(FirstName," ",LastName) as EntityName, UserGUID', 2);
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], 'FirstName,LastName, UserGUID', 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['FirstName'].' '.$entity['LastName'];
                            $res['EntityGUID'] = $entity['UserGUID'];
                        }

                        $res['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                        break;
                    case '14':
                        # code...
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], "EventGUID, Title, ProfileImageID", 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['Title'];
                            $res['EntityProfilePicture'] = $entity['ProfileImageID'];
                            $res['EntityGUID'] = $entity['EventGUID'];
                        }
                        $res['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');                        
                        break;
                    case '18':
                        # code...
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['Title'];
                            $res['EntityProfilePicture'] = $entity['ProfilePicture'];
                            $res['EntityProfileURL'] = $entity['PageURL'];
                            $res['EntityGUID'] = $entity['PageGUID'];
                            $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                            $category_icon = $category_name['Icon'];
                            if ($entity['ProfilePicture'] == '')
                            {
                                $res['EntityProfilePicture'] = $category_icon;
                            }
                            
                            //$res['ModuleEntityOwner'] = $res['ModuleEntityOwner'];
                            if (isset($res['PostAsModuleID']) && $res['PostAsModuleID'] == 18 )
                            {
                                $PostAs=$this->page_model->get_page_detail_cache($res['PostAsModuleEntityID']);
                                $res['ModuleEntityOwner'] = 1;
                                $res['UserName'] = $PostAs['Title'];
                                $res['UserProfilePicture'] = $PostAs['ProfilePicture'];
                                $res['UserProfileURL'] = $PostAs['PageURL'];
                                $res['UserGUID'] = $PostAs['PageGUID'];
                            }                        
                        }                    
                        break;
                    default:
                        # code...
                        break;
                }
                if (!isset($res['EntityProfileURL'])) {
                    $res['EntityProfileURL'] = $res['UserProfileURL'];
                }
                $res['PostContent'] = trim(str_replace('&nbsp;', ' ', $res['PostContent']));
                $res['PostTitle'] = trim(str_replace('&nbsp;', ' ', $res['PostTitle']));
                $res['ActivityURL'] = get_single_post_url($res, $res['ActivityID'], $res['ActivityTypeID'], $res['ModuleEntityID']);
                
                unset($res['SortByReminder']);
                unset($res['ReminderDateTime']);  
                unset($res['PostContent']);
                unset($res['PostTitle']);                  
                unset($res['ActivityID']); 
                unset($res['ActivityTypeID']); 
                unset($res['PostType']);                 
                $result_set[] = $res;
            }            
        }
        return $result_set  ;        
    }

    /**
    * [get_entity_links used to get all links posted using activityIDs]
    */
    public function get_entity_links($activity_ids, $search_key='', $page_size=20, $page_no=1)
    {
        if(isset($activity_ids) && !empty($activity_ids))
        {
            $this->db->select('AL.URL, AL.Title, AL.MetaDescription,AL.ImageURL,AL.UserID,concat(U.FirstName,\' \',U.LastName) as Name, AL.CreatedDate');
            $this->db->from(ACTIVITYLINKS . ' AL');
            $this->db->join(USERS .' U', 'U.UserID=AL.UserID','left');
            if($search_key)
            {
                $this->db->like('AL.Title',$search_key);
            }
            if (isset($page_size, $page_no))
            {
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }
            $this->db->order_by('AL.CreatedDate', 'DESC');

            $tempdb = clone $this->db;
           // $temp_q = $tempdb->get();
            $total_records = $tempdb->count_all_results();

            if (isset($page_size, $page_no))
            {
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }

            $sql = $this->db->get();        //echo $this->db->last_query();
            $result_set = $sql->result_array();
            $result_set['TotalRecords'] = isset($total_records) ? $total_records : 1;
            $result_set = $sql->result_array();
            return $result_set;
        }
        return array();
    }

    /**
     * [get_newsfeed_links Get the links for link tab on the basis of newsfeed]
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
    public function get_newsfeed_links($user_id, $page_no = 1, $page_size = 20, $count_only = 0, $search_key = false, $entity_id = '', $entity_module_id = '', $show_archive = 0, $feed_sort_by = 1, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $is_file_exists = 1, $start_date = false, $end_date = false, $reminder_date = array(), $activity_guid = '', $mentions = array(), $activity_type_filter = array())
    {
        # code...
        $this->load->model(array('polls/polls_model','category/category_model'));
        //$time_zone = get_user_time_zone($user_id);
        $time_zone = $this->user_model->get_user_time_zone();
        //$friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        //$privacy_options = $this->privacy_model->news_feed_setting_details($user_id);
        $privacy_options = $this->privacy_model->get_privacy_options();
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];

        //$friend_of_friends = $this->user_model->get_friends_of_friend($user_id, $friends);
        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        //$group_list              = $this->group_model->get_joined_groups($user_id, false, array(2));
        //$group_list = $this->group_model->get_users_groups($user_id);
        $group_list = $this->group_model->get_user_group_list();
        //print_r($group_list);
        $group_list[] = 0;
        $group_list = implode(',', $group_list);
        //$event_list = $this->event_model->get_all_joined_events($user_id);
        $event_list = $this->event_model->get_user_joined_events();
        // $page_list = $this->page_model->get_liked_pages_list($user_id);
        $page_list = $this->page_model->get_user_pages_list();

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 25);//23, 24,
        $modules_allowed = array(3, 30);
        $show_suggestions = FALSE;
        $show_media = TRUE;

        if ($privacy_options)
        {
            foreach ($privacy_options as $key => $val)
            {
                if ($key == 'g' && $val == '0')
                {
                    $modules_allowed[] = 1;
                }
                if ($key == 'e' && $val == '0')
                {
                    $modules_allowed[] = 14;
                }
                if ($key == 'p' && $val == '0')
                {
                    $modules_allowed[] = 18;
                }
                if ($key == 'm')
                {
                    if ($val == '1')
                    {
                        $show_media = FALSE;
                        unset($activity_type_allow[array_search('5', $activity_type_allow)]);
                        unset($activity_type_allow[array_search('6', $activity_type_allow)]);
                    }
                }
                if ($key == 'r' && $val == '0')
                {
                    $activity_type_allow[] = 16;
                    $activity_type_allow[] = 17;
                }
                if ($key == 's' && $val == '0')
                {
                    if ($filter_type == '0' && empty($mentions))
                    {
                        //$show_suggestions = true;
                    }
                }
            }
        }

        if ($filter_type == 3)
        {
            /* 1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18 23, 24, 25 */
            $modules_allowed = array(1, 3, 14, 18, 23, 30);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 30);//23, 24,
        }

        /* --Filter by activity type id-- */
        $activity_ids = array();
        if (!empty($activity_type_filter))
        {
            $activity_type_allow = $activity_type_filter;
            $show_suggestions = false;

            //7 = My Polls, 8= Expired
            if ($filter_type == 7 || $filter_type == 8)
            {
                $is_expired = FALSE;
                if ($filter_type == 8)
                {
                    $is_expired = TRUE;
                }

                $this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_poll_activities($entity_id, $entity_module_id, $is_expired);
                if (empty($activity_ids))
                {
                    return array();
                }
            }
            //My Voted Polls
            if ($filter_type == 9)
            {
                $this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_voted_poll_activities($entity_id, $entity_module_id);
                if (empty($activity_ids))
                {
                    return array();
                }
            }

            //print_r($activity_ids);die;
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed))
        {

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
        if ($friend_followers_list != '')
        {
            $condition = "(
                IF(A.ActivityTypeID=25 OR A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6 OR (A.ActivityTypeID=23 AND A.ModuleID=3) OR (A.ActivityTypeID=24 AND A.ModuleID=3), (
                    A.UserID IN(" . $friend_followers_list . ") OR A.ModuleEntityID IN(" . $friend_followers_list . ") OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (
                    (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (
                    A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'
                ), '' )";

            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
                $privacy_cond2 = "IF(A.Privacy='3',
                    A.UserID IN (" . implode(',', $follow) . "), true
                )";
            }
        }

        // Check parent activity privacy for shared activity
        $privacy_condition = "
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                IF(A.ActivityTypeID IN(9,10),
                    A.ParentActivityID=(
                        SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                            IF(Privacy=2 AND ActivityTypeID!=7,UserID IN (" . $friend_followers_list . "),false) OR
                            IF(Privacy=3 AND ActivityTypeID!=7,UserID IN (" . implode(',', $friends) . "),false) OR
                            IF(Privacy=4 AND ActivityTypeID!=7,UserID='" . $user_id . "',false) OR
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $group_list . "),false))
                    ),false
                )
                OR
                IF(A.ActivityTypeID IN(14,15),
                    A.ParentActivityID=(
                        SELECT MediaID FROM " . MEDIA . " WHERE StatusID=2 AND A.ParentActivityID=MediaID
                    ),false
                )
            ),         
        true)";

        $privacy_cond3 = "IF(A.Privacy='4',
            A.UserID='" . $user_id . "', true
        )";
        if (!empty($privacy_cond1))
        {
            $privacy_cond .= $privacy_cond1 . ' OR ';
        }
        if (!empty($privacy_cond2))
        {
            $privacy_cond .= $privacy_cond2 . ' OR ';
        }
        $privacy_cond .= " OR A.ActivityID=(SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "')";
        $privacy_cond .= $privacy_cond3 . ' )';

        // /echo $privacy_cond;
        if (!empty($group_list))
        {
            $condition_part_one = $condition_part_one . "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ")
                    ), '' )";
        }
        if (!empty($page_list))
        {
            $condition_part_three = $condition_part_three . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
                        A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ")
                    ), '' )";
        }
        if (!empty($event_list))
        {
            $condition_part_four = $condition_part_four . "IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
                        A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")
                    ), '' )";
        }
        if (!empty($condition))
        {
            if (!empty($condition_part_one))
            {
                $condition = $condition . " OR " . $condition_part_one;
            }
            if (!empty($condition_part_three))
            {
                $condition = $condition . " OR " . $condition_part_three;
            }
            if (!empty($condition_part_four))
            {
                $condition = $condition . " OR " . $condition_part_four;
            }
            $condition = $condition . ")";
        } else
        {

            if (!empty($condition_part_one))
            {
                $condition = $condition_part_one;
            }
            if (!empty($condition_part_three))
            {
                if (empty($condition))
                {
                    $condition = $condition_part_three;
                } else
                {
                    $condition = $condition . " OR " . $condition_part_three;
                }
            }

            if (empty($condition))
            {
                $condition = $condition_part_two;
            } else
            {
                //$condition = $condition_part_two. " OR ".$condition_part_one; 
                $condition = "(" . $condition . ")";
            }
        }
        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        //ELSE A.ActivityID=(SELECT ActivityID FROM ".MENTION." WHERE ModuleID=3 AND ModuleEntityID='".$user_id."')
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";

        $this->db->select("A.ActivityGUID,A.ActivityID, ATY.ActivityTypeID, A.PostTitle, A.PostType, A.PostContent, A.ModuleEntityID,A.ModuleID,A.PostAsModuleID,A.PostAsModuleEntityID, AL.URL, AL.Title, AL.MetaDescription,AL.ImageURL,AL.UserID,U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture,concat(U.FirstName,' ',U.LastName) as Name, AL.CreatedDate");
        
        $this->db->from(ACTIVITY . ' A');        
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'right');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');

        //Getting Comments and its media in the result set
        //$this->db->join(POSTCOMMENTS . ' PC', 'PC.EntityID=A.ActivityID AND PC.StatusID=2', 'left');

        /* $this->db->join(MEDIA . ' MD', '(MD.MediaSectionReferenceID=A.ActivityID OR MD.MediaSectionReferenceID=PC.PostCommentID) AND MD.StatusID=2', 'left'); */

        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        $this->db->_protect_identifiers = TRUE;


        /* Join Activity Links Ends */
        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } else
        {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } else if ($filter_type === 'Favourite')
            {
                $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"');
                $this->db->where('F.UserID', $user_id);
                $this->db->where('F.StatusID', '2');
            } else
            {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43))
                {
                    $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids)
            {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            if ($mentions)
            {
                $join_condition = "MN.ActivityID=A.ActivityID AND (";
                foreach ($mentions as $mention)
                {
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
            if ($activity_guid)
            {
                $this->db->where('A.ActivityGUID', $activity_guid);
            }

            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        }


        if ($filter_type == 1)
        {
            $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"', 'right');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
        } else if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
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
        }
        

        if (!empty($blocked_users) && empty($feed_user))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        //$this->db->where_in('A.StatusID',array('1','2'));
        $this->db->where('ATY.StatusID', '2');
        if (empty($activity_ids))
        {
            if (!empty($condition))
            {
                $this->db->where($condition, NULL, FALSE);
            } else
            {
                $this->db->where('A.ModuleID', '3');
                $this->db->where('A.ModuleEntityID', $user_id);
            }
            if ($privacy_condition)
            {
                $this->db->where($privacy_condition, null, false);
            }
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            $this->db->select("R.ReminderDateTime", FALSE);
            $this->db->select("IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder", false);


            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3)
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



            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            $this->db->_protect_identifiers = TRUE;
        }

        if ($feed_sort_by != 2)
        {
            //$this->db->order_by('UARRANK','ASC');    
        }

        if ($feed_sort_by == 'popular')
        {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } elseif ($feed_sort_by == 1)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($filter_type == 3)
        {
            if ($reminder_date)
            {
                $rd_data = array();
                foreach ($reminder_date as $rd)
                {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        //search
        if (!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(AL.MetaDescription LIKE "%' . $search_key . '%" OR AL.Title  LIKE "%' . $search_key . '%")', NULL, FALSE);
        }       
        $this->db->order_by('AL.CreatedDate', 'DESC');
        //clone to find total records
        $tempdb = clone $this->db;
        //$temp_q = $tempdb->get();
        $total_records = $tempdb->count_all_results();
        if (isset($page_size, $page_no))
        {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }        
        
        $result = $this->db->get();
        // echo $this->db->last_query(); die;
        $result_set = array();
        $result_set['TotalRecords'] = isset($total_records) ? $total_records : 1;
        if ($result->num_rows())
        {
            foreach ($result->result_array() as $res)
            {
                $res['UserProfileURL'] = get_entity_url($res['UserID'], 'User', 1);
                switch ($res['ModuleID']) {
                    case '1':
                        # code...
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], "Type, GroupGUID, GroupName, GroupImage", 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['GroupName'];
                            $res['EntityProfilePicture'] = $entity['GroupImage'];
                            $res['EntityGUID'] = $entity['GroupGUID'];
                            $res['EntityProfileURL']   = $this->group_model->get_group_url($res['ModuleEntityID'], $entity['GroupName'], true, 'index');
                        }
                        break;
                    case '3':
                        # code...
                        $res['EntityName'] = $res['Name'];
                        $res['EntityProfilePicture'] = $res['ProfilePicture'];
                        $res['EntityGUID'] = $res['UserGUID'];

                        //$entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], 'CONCAT(FirstName," ",LastName) as EntityName, UserGUID', 2);
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], 'FirstName,LastName, UserGUID', 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['FirstName'].' '.$entity['LastName'];
                            $res['EntityGUID'] = $entity['UserGUID'];
                        }

                        $res['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                        break;
                    case '14':
                        # code...
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], "EventGUID, Title, ProfileImageID", 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['Title'];
                            $res['EntityProfilePicture'] = $entity['ProfileImageID'];
                            $res['EntityGUID'] = $entity['EventGUID'];
                        }
                        $res['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');                        
                        break;
                    case '18':
                        # code...
                        $entity = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                        if ($entity)
                        {
                            $res['EntityName'] = $entity['Title'];
                            $res['EntityProfilePicture'] = $entity['ProfilePicture'];
                            $res['EntityProfileURL'] = $entity['PageURL'];
                            $res['EntityGUID'] = $entity['PageGUID'];
                            $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                            $category_icon = $category_name['Icon'];
                            if ($entity['ProfilePicture'] == '')
                            {
                                $res['EntityProfilePicture'] = $category_icon;
                            }
                            
                            //$res['ModuleEntityOwner'] = $res['ModuleEntityOwner'];
                            if ($res['PostAsModuleID'] == 18 )
                            { 
                                $PostAs=$this->page_model->get_page_detail_cache($res['PostAsModuleEntityID']);
                                $res['ModuleEntityOwner'] = 1;
                                $res['UserName'] = $PostAs['Title'];
                                $res['UserProfilePicture'] = $PostAs['ProfilePicture'];
                                $res['UserProfileURL'] = $PostAs['PageURL'];
                                $res['UserGUID'] = $PostAs['PageGUID'];
                            }                        
                        }                    
                        break;
                    default:
                        # code...
                        break;
                }
                if (!isset($res['EntityProfileURL'])) {
                    $res['EntityProfileURL'] = $res['UserProfileURL'];
                }

                $res['PostContent'] = trim(str_replace('&nbsp;', ' ', $res['PostContent']));
                $res['PostTitle'] = trim(str_replace('&nbsp;', ' ', $res['PostTitle']));
                $res['ActivityURL'] = get_single_post_url($res, $res['ActivityID'], $res['ActivityTypeID'], $res['ModuleEntityID']);
                
                unset($res['SortByReminder']);
                unset($res['ReminderDateTime']);  
                unset($res['PostContent']);
                unset($res['PostTitle']);                  
                unset($res['ActivityID']); 
                unset($res['ActivityTypeID']); 
                unset($res['PostType']); 
                $result_set[] = $res;
            }            
        }
        return $result_set  ;
    }


    public function is_new_user($user_id)
    {
        $data = $this->getFeedActivities($user_id,1,10,1);
        if(empty($data))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * [search_feed_activities used to search content ]
     * @param  [Int]        user_id     [User Id of logged-in user]
     * @param  [int]        page_no   [page offset for pagination]     
     * @param  [int]        page_size [no of item in sigle page]     
     * @param  [int]        feed_sort_by   [used to sort resultset]     
     * @param  [int]        feed_user    [id of user whose feed is to be searched]     
     * @param  [int]        filter_type    []
     * @param  [int]        is_media_exists    [0->no media, 1->only media, 2->can't say]     
     * @param  [string]        search_key    [seach keyword]
     * @param  [string]        start_date    [seach content created after this date]
     * @param  [string]        end_date    [seach content created before this date]
     * @param  [array]        include_entity    [associative array values: $include_entity['Attachment']=(0|1), $include_entity['UserAndGroup']=(0|1)]
     * @param  [int]        count_only    [count_only is used for total count]
     * @param  [string]        ReminderDate    []
     * @param  [string]        activity_guid    [guid of activity]
     * @param  [array]        mentions    [array of tagged users]
     * @param  [int]        entity_id    [ModuleENtityID]
     * @param  [int]        entity_module_id    [module id]
     * @param  [array]        activity_type_filter    []
     * @param  [string]        acivity_ids    [comma seperated ids of activities]
     * @param  [int]        view_entity_tags    [Flag to view entity tags; values are (0|1)]     
     * @param  [int]        role_id    [role_id of user]
     * @param  [int]        posted_by    [filter to search; 0->any,1->you,2->frieds,3->friends+groups]
     * @param  [string]        modified_start_date    [search content updated after this date]
     * @param  [string]        modified_end_date    [seach content updated before this date]
     * @param  [string]        selected_group_ids    [array of selected group ids]
     * @param  [array]        search_only_for    [search filter; value can be 1->post,2->comment,3->wiki,4->meetings(ex. $search_only_for['1','2'])]
     * @return [JSON] [JSON Object]
     */ 
    public function search_feed_activities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $include_entity = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), $activity_ids = array(),$view_entity_tags=1,$role_id=2,$posted_by='0', $modified_start_date='', $modified_end_date='',$selected_group_ids=array(),$search_only_for=0)
    {    
        $this->load->model(array('polls/polls_model','category/category_model'));
        $time_zone = $this->user_model->get_user_time_zone();
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        $group_list = $this->group_model->get_visible_groups($user_id);
        $my_groups = $group_list;
        $category_list = $this->forum_model->get_user_category_list();
        $event_list = $this->event_model->get_visible_events($user_id);
        $page_list = $this->page_model->get_user_pages_list();

        $group_list = implode(',', $group_list);

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 25, 26);
        $modules_allowed = array(3, 30, 34);
        $show_suggestions = FALSE;
        $show_media = TRUE;

        if ($privacy_options)
        {
            foreach ($privacy_options as $key => $val)
            {
                if ($key == 'g' && $val == '0')
                {
                    $modules_allowed[] = 1;
                }
                if ($key == 'e' && $val == '0')
                {
                    $modules_allowed[] = 14;
                }
                if ($key == 'p' && $val == '0')
                {
                    $modules_allowed[] = 18;
                }
                if ($key == 'm')
                {
                    if ($val == '1')
                    {
                        $show_media = FALSE;
                        unset($activity_type_allow[array_search('5', $activity_type_allow)]);
                        unset($activity_type_allow[array_search('6', $activity_type_allow)]);
                    }
                }
                if ($key == 'r' && $val == '0')
                {
                    $activity_type_allow[] = 16;
                    $activity_type_allow[] = 17;
                }
                if ($key == 's' && $val == '0')
                {
                    if ($filter_type == '0' && empty($mentions))
                    {
                        //$show_suggestions = true;
                    }
                }
            }
        }

        if ($filter_type == 3)
        {
            /* 1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18 23, 24, 25 */
            $modules_allowed = array(1, 3, 14, 18, 23, 30);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 30);
        }

        /* --Filter by activity type id-- */
        $activity_ids = array();
        if (!empty($activity_type_filter))
        {
            $activity_type_allow = $activity_type_filter;
            $show_suggestions = false;

            //7 = My Polls, 8= Expired
            if ($filter_type == 7 || $filter_type == 8)
            {
                $is_expired = FALSE;
                if ($filter_type == 8)
                {
                    $is_expired = TRUE;
                }

                //$this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_poll_activities($entity_id, $entity_module_id, $is_expired);
                if (empty($activity_ids))
                {
                    return array();
                }
            }
            //My Voted Polls
            if ($filter_type == 9)
            {
                //$this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_voted_poll_activities($entity_id, $entity_module_id);
                if (empty($activity_ids))
                {
                    return array();
                }
            }

            //print_r($activity_ids);die;
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed))
        {

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

        $case_array=array();

        if ($friend_followers_list != '' && empty($activity_ids))
        {
            //optimized from vsocial
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3) 
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN 
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
                        
            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
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
        //Group Condition
        if (!empty($group_list))
        {            
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }

        $case_array[] = " A.ModuleID=18 THEN TRUE ";
                
        if ($category_list) {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (" . implode(',', $category_list) . ") 
                            ";
        }
        
        //event condition
        if (!empty($event_list))
        {            
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . implode(',',$event_list) . ")";
        }
        //combining all conditions
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END OR (A.ModuleID=3 AND A.Privacy=1 )) ";
        }
        if (empty($condition))
        {
            $condition = $condition_part_two;
        } 
        //Privacy conditions
        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        //Selected Fields
        $select_array = array();
        $select_array[] = 'A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserID, U.UserGUID, U.ProfilePicture';
        //get count of occurrence of search term 
        $select_array[] = '(LENGTH(`A`.`PostContent`) - LENGTH(REPLACE(LOWER(`A`.`PostContent`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') as PostContentCount';        
        $select_array[] = 'IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';        
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID=A.ActivityID', 'left');
        $this->db->join(TAGS . ' TG', 'TG.TagID=ET.TagID', 'left');
        $this->db->join(RATINGS . ' RT', 'RT.ModuleEntityID=A.ModuleEntityID AND RT.ActivityID=A.ActivityID AND A.ActivityTypeID=16 AND A.ModuleID=18', 'left');
        $this->db->join(REVIEWS . ' RE', 'RE.RatingID=RT.RatingID  ', 'left');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        $this->db->_protect_identifiers = TRUE;
        
        $this->db->where_not_in('A.StatusID',array('3'));
        $this->db->where_not_in('U.StatusID',array('3','4'));
        
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('U.UserID', $blocked_users);
        }
        /* Search parameters for content*/
        //if include parameters are set        
        if (!empty($search_key))
        {
            //if attachemnt is included
            $where_attachment = $where_user_and_group = '';
            if($include_entity['Attachment'])
            {
                $where_attachment = ' OR A.ActivityID IN(SELECT MediaSectionReferenceID FROM Media where MediaSectionReferenceID=A.ActivityID AND OriginalName like "%'.addslashes($search_key).'%")';   
            }
            //if user and group filter is included
            if($include_entity['UserAndGroup'])
            {
                //$where_user_and_group = ' OR U.FirstName LIKE "%' . addslashes($search_key) . '%" OR U.LastName LIKE "%' . addslashes($search_key) . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . addslashes($search_key) . '%" OR IF(A.ModuleID="1",A.ModuleEntityID in (Select GroupID From Groups G where G.StatusID="2" and GroupName LIKE "%' . addslashes($search_key) . '%"),0) OR IF(A.ModuleID="14",A.ModuleEntityID in (Select EventID From Events E where E.IsDeleted="0" and Title LIKE "%' . addslashes($search_key) . '%"),0) OR IF(A.ModuleID="18",A.ModuleEntityID in (Select PageID From Pages P where P.StatusID="2" and Title LIKE "%' . addslashes($search_key) . '%"),0)';
            }
            //Condition if search_only_for filter is on
            //below is the common condition which will be include in all search only conditions
            $search_only_for_condition = '(A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID = 0 and Title LIKE "%' . addslashes($search_key) . '%" ) '.$where_user_and_group . $where_attachment .' OR TG.Name like "%'.addslashes($search_key).'%" ';
            //if search only for is an array and multivalued selection from front end
            if(is_array($search_only_for) && !empty($search_only_for))
            {
                foreach($search_only_for as $search_value)
                {
                    switch($search_value)   
                    {
                        case '1': #Post
                            $search_only_for_condition.= 'OR A.PostSearchContent LIKE "%' . addslashes($search_key) . '%" OR A.PostTitle LIKE "%' . addslashes($search_key) . '%" OR RE.Title LIKE "%' . addslashes($search_key) . '%" OR RE.Description LIKE "%' . addslashes($search_key) . '%" ';
                            break;
                        case '2': #comment
                            $search_only_for_condition.=' OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostSearchComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostSearchComment`) - LENGTH(REPLACE(LOWER(`PostSearchComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc )';
                            break;
                        case '3': #meetings
                            break;
                        case '4': #wiki
                            break;
                        default: #none
                    }
                }    
                //end of multivalued search
                $search_only_for_condition.=')';                 
                $this->db->where($search_only_for_condition,NULL,FALSE);              
            }
            else
            {   //if not multivalued
                switch ($search_only_for) 
                {
                    case '1':
                        # Post
                        $this->db->where('(A.PostSearchContent LIKE "%' . addslashes($search_key) . '%" OR A.PostTitle LIKE "%' . addslashes($search_key) . '%" OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID = 0 and Title LIKE "%' . addslashes($search_key) . '%" ) '.$where_user_and_group . $where_attachment .' OR TG.Name like "%'.addslashes($search_key).'%")', NULL, FALSE);                                    
                        break;
                    case '2':
                        # Comment                
                        $this->db->where('( A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostSearchComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostSearchComment`) - LENGTH(REPLACE(LOWER(`PostSearchComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc ) OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID > 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR TG.Name like "%'.addslashes($search_key).'%" ' . $where_attachment . $where_user_and_group . ')', NULL, FALSE);                
                        break;
                    case '3':
                        # Meetings (dev Pending)                
                        break;
                    case '4':
                        # Wiki Post (dev Pending)                
                        break; 
                    case '5':
                        # Post And Comment both
                        $this->db->where('(A.PostSearchContent LIKE "%' . addslashes($search_key) . '%" OR A.PostTitle LIKE "%' . addslashes($search_key) . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostSearchComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostSearchComment`) - LENGTH(REPLACE(LOWER(`PostSearchComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc ) OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID > 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR TG.Name like "%'.addslashes($search_key).'%" ' . $where_attachment . $where_user_and_group . ')', NULL, FALSE);
                        break;            
                    default:
                        # code...                    
                        $this->db->where('( A.PostSearchContent LIKE "%' . addslashes($search_key) . '%" OR A.PostTitle LIKE "%' . addslashes($search_key) . '%" OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID = 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostSearchComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostSearchComment`) - LENGTH(REPLACE(LOWER(`PostSearchComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc ) OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID > 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR TG.Name like "%'.addslashes($search_key).'%" ' . $where_attachment . $where_user_and_group . ')', NULL, FALSE);
                        
                        break;        
                }    
            }                     
        }
                        
        if(!($entity_module_id == '3' && $entity_id == $user_id))//if(!empty($selected_group_ids))
        {
            $this->db->where('A.ModuleID', $entity_module_id);
            $this->db->where('A.ModuleEntityID', $entity_id);
        }
        //get data by posted by filter        
        if(isset($posted_by) && !empty($posted_by))
        {
            switch ($posted_by) 
            {
                case '1': //Posted by you only
                    $this->db->where('A.UserID',$user_id);
                    break;
                case '2'://Posted by your Friends
                    $friends_ids = $this->friend_model->getFriendIDS($user_id);
                    if(!empty($friends_ids))
                    {
                        $this->db->where_in('U.UserID',$friends_ids);                
                    }
                    else
                    {
                        $this->db->where('U.UserID',0);                   
                    }
                    break;
                case '3'://Posted by your Friends + Groups
                    $friends_ids = $this->friend_model->getFriendIDS($user_id);
                    if(!empty($friends_ids))
                    {
                        $this->db->where('U.UserID IN ('.implode(',',$friends_ids)  .') OR (A.ModuleID=1 and A.ModuleEntityID IN ('.implode(',',$my_groups).'))',NULL,FALSE);
                    }
                    break;
                default:
                    # Look for more posetd by selected users
                    if(is_array($posted_by))
                    {
                        $this->db->where_in('U.UserID',$posted_by);
                    }
                    break;
            }    
        }        
        /* Search parameters */

        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } 
        else
        {   //if archive filter is enabled
            if ($include_entity['Archive'] && !$this->settings_model->isDisabled(43))// Earliar it was:($filter_type == 4)
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "left");
                $this->db->_protect_identifiers = TRUE;
            } else if (isset($include_entity['Favourite']))
            {
                $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"');
                $this->db->where('F.UserID', $user_id);
                $this->db->where('F.StatusID', '2');
            } else
            {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43))
                {
                    $this->db->where("NOT EXISTS (SELECT 1 FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND ActivityID=A.ActivityID AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }
            //If activity ids are given
            if ($activity_ids)
            {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }
            //check who is tagged
            if ($mentions)
            {
                $join_condition = "MN.ActivityID=A.ActivityID AND (";
                foreach ($mentions as $mention)
                {
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
            if ($activity_guid)
            {
                $this->db->where('A.ActivityGUID', $activity_guid);
            }
            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);
            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        }

        if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
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
        }

        if (!$show_media)
        {
            if ($is_media_exists == 2)
            {
                $is_media_exists = '0';
            }
            if ($is_media_exists == 1)
            {
                $is_media_exists = '3';
            }
        }
        //conditions for attachment
        if ($is_media_exists != 2)
        {            
            if ($is_media_exists)
            {
                $this->db->where('(A.IsMediaExist=\'1\' OR A.IsFileExists=\'1\')', NULL,FALSE);
            }
            else
            {
                $this->db->where('A.IsMediaExist', '0');
                $this->db->where('A.IsFileExists', '0');
            }
        }
        //remove blocked user's content
        if (!empty($blocked_users) && empty($feed_user))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }        
        $this->db->where('ATY.StatusID', '2');
        if (empty($activity_ids))
        {
            if (!empty($condition))
            {
                $this->db->where($condition, NULL, FALSE);
            } else
            {
                $this->db->where('A.ModuleID', '3');
                $this->db->where('A.ModuleEntityID', $user_id);
            }
            if ($privacy_condition)
            {
                $this->db->where($privacy_condition, null, false);
            }
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {            
            $select_array[]="R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array[]="IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";
            
            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3)
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

        if ($feed_sort_by == 'popular')
        {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } elseif ($feed_sort_by == 1)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($filter_type == 3)
        {
            if ($ReminderDate)
            {
                $rd_data = array();
                foreach ($ReminderDate as $rd)
                {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }
        //Search content created after given date
        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {   //Search content created before given date
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }
        if ($modified_start_date)
        {   //Search content updated after this date
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.ModifiedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $modified_start_date . "'", NULL, FALSE);
        }
        if ($modified_end_date)
        {   //Search content updated before this date
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.ModifiedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $modified_end_date . "'", NULL, FALSE);
        }
        if (!$count_only)
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }        
        if ($count_only)
        {
            $this->db->select('COUNT(DISTINCT A.ActivityID) as TotalRow ' );
            $this->db->distinct();
            $result = $this->db->get();
            $count_data=$result->row_array();
            return $count_data['TotalRow'];            
        }
        else{
            $this->db->select(implode(',', $select_array),false);            
            $this->db->distinct();
            $this->db->group_by('A.ActivityID');
            //sort by no of times search occur in post
            $this->db->order_by('PostContentCount','DESC');
            $result = $this->db->get();
            //echo $this->db->last_query();die;
            
        }
        //print_r($result->result_array());
        $return = array();
        if ($result->num_rows())
        {
            $cnt = 1;
            /**** variables defined starts ****/    
            $user_favourite = $this->favourite_model->get_user_favourite();                     
            $user_flagged = $this->flag_model->get_user_flagged();          
            $user_archive = $this->get_user_activity_archive();         
            /**** variables defined ends ****/
            foreach ($result->result_array() as $res)
            {
                $activity = array();
                $activity['PostType'] = $res['PostType'];
                //Suggested Posts
                if ($cnt == 6 && $page_no == 1 && $show_suggestions)
                {
                    $activity['Album'] = array();
                    $ViewTemplate = '';
                    if ($cnt == 6)
                    {
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
                
                $BUsers = $this->block_user_list($module_entity_id, $module_id);
                if (in_array($res['UserID'], $BUsers))
                {
                    continue;
                }

                $activity['IsDeleted'] = 0;
                if ($filter_type == 7)
                {
                    $activity['IsDeleted'] = 1;
                }
                $activity['IsTagged'] = $this->is_tagged($res['ActivityID'], $user_id);
                $activity['ImageServerPath'] = IMAGE_SERVER_PATH;
                $activity['RatingData'] = array();
                $activity['PollData'] = array();
                $activity['IsEntityOwner'] = 0;
                $activity['IsOwner'] = 0;
                $activity['IsFlagged'] = 0;
                $activity['CanShowSettings'] = 0;
                $activity['CanRemove'] = 0;
                $activity['CanMakeSticky'] = 0;
                $activity['ShowPrivacy'] = 0;
                $activity['IsMember'] = 1;
                $activity['Reminder'] = array();
                $activity['ShowInviteGraph'] = 0;
                $activity['PostAsModuleID'] = $res['PostAsModuleID'];                 
                $activity['Links'] = $this->get_activity_links($res['ActivityID']);
                //Link Variable Assignment Ends

                if (isset($res['ReminderGUID']))
                {
                    $activity['Reminder'] = array('ReminderGUID' => $res['ReminderGUID'], 'ReminderDateTime' => $res['ReminderDateTime'], 'CreatedDate' => $res['ReminderCreatedDate'], 'Status' => $res['ReminderStatus'], 'Meridian' => '');
                }                
                $activity['ModuleEntityID'] = $res['ModuleEntityID'];
                $activity['PostAsEntityOwner'] = 0;
                $activity['OriginalActivityGUID'] = '';
                $activity['OriginalActivityType'] = '';
                $activity['OriginalActivityFirstName'] = '';
                $activity['OriginalActivityLastName'] = '';
                $activity['OriginalActivityUserGUID'] = '';
                $activity['OriginalActivityProfileURL'] = '';

                $activity['ActivityGUID'] = $activity_guid;
                $activity['ModuleID'] = $module_id;
                $activity['UserGUID'] = $res['UserGUID'];
                $activity['ActivityType'] = $res['ActivityType'];
                $activity['NoOfFavourites'] = $res['NoOfFavourites'];


                if (in_array($res['ActivityTypeID'], array(23,24)))
                {
                    $params = json_decode($res['Params'], true);
                    $entity_id = get_detail_by_guid($params['MediaGUID'], 21, 'MediaID', 1);
                    $activity['NoOfComments'] = $this->get_activity_comment_count('Media', $entity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->get_like_count($entity_id, "MEDIA", $BUsers); //$res['NoOfLikes'];
                    //below line is copied from getFeedActivity function
                    $activity['Album'] = $this->get_albums($entity_id, $res['UserID'], '', 'Media', 1);
                } else
                {
                    if(!in_array($activity_type_id,array(5,6,9,10,14,15)))  
                    {           
                        $activity['Album'] = $this->get_albums($activity_id, $res['UserID']);           
                    }
                    if($BUsers)
                    {                        
                        $activity['NoOfComments'] = $this->get_activity_comment_count('Activity', $activity_id, $BUsers); 
                        $activity['NoOfLikes'] = $this->get_like_count($activity_id, 'Activity', $BUsers);                     
                    }
                    else
                    {
                        $activity['NoOfComments'] = $res['NoOfComments']; 
                        $activity['NoOfLikes'] = $res['NoOfLikes'];
                    }
                }

                $activity['NoOfShares'] = $res['NoOfShares'];
                $activity['Message'] = $res['Template'];
                $activity['ViewTemplate'] = $res['ViewTemplate'];                
                $activity['IsArchive'] = 0;

                /* to be popped out */
                $user_archive = $this->get_user_activity_archive();

                if (isset($user_archive[$activity_id]))
                {
                    $activity['IsArchive'] = $user_archive[$activity_id];
                }
                $activity['CommentsAllowed'] = 0;
                if ($res['IsCommentable'] && $res['CommentsAllowed'])
                {
                    $activity['CommentsAllowed'] = 1;
                }

                $activity['LikeAllowed'] = $res['LikeAllowed'];
                $activity['FlagAllowed'] = $res['FlagAllowed'];
                $activity['ShareAllowed'] = $res['ShareAllowed'];
                $activity['FavouriteAllowed'] = $res['FavouriteAllowed'];
                $activity['CreatedDate'] = $res['CreatedDate'];
                $activity['ModifiedDate'] = $res['ModifiedDate'];
                $activity['IsSticky'] = 0;
                $activity['Visibility'] = $res['Privacy'];
                $activity['PostContent'] = $res['PostContent'];
                $activity['PostTitle']          = $res['PostTitle'];
                $activity['PostType']           = $res['PostType'];
                $activity['Files'] = $this->get_activity_files($activity_id);
                $activity['Params'] = json_decode($res['Params']);
                $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);                
                $activity['IsFavourite'] = (in_array($activity_id, $user_favourite)) ? 1 : 0;
                $activity['Flaggable'] = $res['Flaggable'];
                $activity['FlaggedByAny'] = 0;
                $activity['ParentActivityID'] = $res['ParentActivityID'];
                
                //below lines have been commented after comparing of getFeedActivity
                $activity['CanBlock'] = 0;
                if ($res['UserID'] == $user_id)
                {
                    $activity['IsOwner'] = 1;
                }                
                $activity['IsFlagged'] = (in_array($activity_id, $user_flagged)) ? 1 : 0;
                if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                { 
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

                    //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
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
                        
                        if($res['UserID']!=$user_id)
                        {
                            $activity['IsExpert'] = $this->group_model->check_is_expert($res['UserID'],$module_entity_id);
                        }
                    }
                }
                if ($module_id == 3)
                {
                    $activity['EntityName'] = $activity['UserName'];
                    $activity['EntityProfilePicture'] = $activity['UserProfilePicture'];
                    $activity['EntityGUID'] = $activity['UserGUID'];

                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity)
                    {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                    if ($user_id == $module_entity_id)
                    {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                }

                $activity['ShowBTNCommentsAllowed'] = 1;
                $activity['MuteAllowed'] = 1;
                $activity['ShowFlagBTN'] = 1;
                if ($res['ActivityTypeID'] == 16 || $res['ActivityTypeID'] == 17)
                {
                    $params = json_decode($res['Params']);
                    $activity['RatingData'] = $this->rating_model->get_rating_by_id($params->RatingID, $user_id);
                    $activity['FavouriteAllowed'] = 1;
                    $activity['ShareAllowed'] = 1;
                    $activity['CommentsAllowed'] = 1;
                    $activity['ShowBTNCommentsAllowed'] = 0;
                    $activity['MuteAllowed'] = 0;
                    $activity['ShowFlagBTN'] = 0;
                } 
                else if ($res['ActivityTypeID'] == 25)
                {
                    $params = json_decode($res['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $entity_id);
                    $activity['MuteAllowed'] = 0;
                    $activity['ShowFlagBTN'] = 0;

                    $user_details_invite = $this->polls_model->get_invite_status('3', $user_id, $params->PollID);
                    if ($user_details_invite['TotalInvited'] > 0)
                    {
                        $activity['ShowInviteGraph'] = 1;
                    }
                }

                if ($module_id == 14)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $activity['EntityGUID'] = $entity['EventGUID'];
                    }
                    
                    $activity['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');

                    if ($this->event_model->isEventOwner($module_entity_id, $user_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                        $activity['ShowPrivacy'] = 0;
                        $activity['CanBlock'] = 1;
                    }
                    if ($this->event_model->isEventOwner($module_entity_id, $res['UserID']))
                    {
                        $activity['CanBlock'] = 0;
                    }
                }
                if ($module_id == 18)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                        $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                        $category_icon = $category_name['Icon'];
                        if ($entity['ProfilePicture'] == '')
                        {
                            $activity['EntityProfilePicture'] = $category_icon;
                        }                        
                        if ($res['ModuleEntityOwner'] == 1 || !empty($activity['RatingData']))
                        {
                            $activity['UserName'] = $activity['EntityName'];
                            $activity['UserProfilePicture'] = $activity['EntityProfilePicture'];
                            $activity['UserProfileURL'] = $activity['EntityProfileURL'];
                            $activity['UserGUID'] = $activity['EntityGUID'];
                        }
                        if ($res['ModuleEntityOwner'] == 0 && $res['ActivityTypeID'] == 12)
                        {
                            $activity['Message'] = $activity['Message'];// . ' posted in {{Entity}}';
                        }
                        $activity['ModuleEntityOwner'] = $res['ModuleEntityOwner'];

                        //optimized from vsocial
                        if ($res['PostAsModuleID'] == 18 )
                        { 
                            $PostAs=$this->page_model->get_page_detail_cache($res['PostAsModuleEntityID']);
                            $activity['ModuleEntityOwner'] = 1;
                            $activity['UserName'] = $PostAs['Title'];
                            $activity['UserProfilePicture'] = $PostAs['ProfilePicture'];
                            $activity['UserProfileURL'] = $PostAs['PageURL'];
                            $activity['UserGUID'] = $PostAs['PageGUID'];
                        }
                        if ($res['PostAsModuleEntityID']!=$module_entity_id && $res['ActivityTypeID'] == 12)
                        {
                            $activity['Message'] = $activity['Message'] . ' posted in {{Entity}}';
                        }                        

                        if ( ($res['PostAsModuleID'] == 18 && $res['PostAsModuleEntityID']==$module_entity_id) || !empty($activity['RatingData']) )
                        {
                            $activity['Message'] = $res['Template'];
                        }
                        //vsocial
                    }
                    $activity['PostAsEntityOwner'] = $res['ModuleEntityOwner'];
                    if ($this->page_model->check_page_owner($user_id, $module_entity_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                    if ($this->page_model->check_page_creator($res['UserID'], $module_entity_id))
                    {
                        $activity['CanBlock'] = 0;
                    }
                    if ($res['ModuleEntityOwner'] == 1)
                    {
                        $activity['CanBlock'] = 0;
                    }
                }
                if ($module_id == 34) {
                    $activity['IsExpert'] = $this->forum_model->check_is_expert($res['UserID'],$module_entity_id);
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

                if ($res['UserID'] == $user_id)
                {
                    $activity['CanBlock'] = 0;
                }

                /*if ($activity['UserProfilePicture'] == '')
                {
                    $activity['UserProfilePicture'] = "user_default.jpg";
                }*/
                
                if (!isset($activity['EntityProfileURL']))
                {
                    $activity['EntityProfileURL'] = $activity['UserProfileURL'];
                }

                $activity['SharedActivityModule'] = '';         
                $activity['SharedEntityGUID'] = '';

                if ($activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 14 || $activity_type_id == 15)
                {
                    $originalActivity = $this->get_activity_details($res['ParentActivityID'], $activity_type_id);
                    $activity['ActivityOwner'] = $this->user_model->getUserName($originalActivity['UserID'], $originalActivity['ModuleID'], $originalActivity['ModuleEntityID']);
                    $activity['ActivityOwnerLink'] = $activity['ActivityOwner']['ProfileURL'];
                    $activity['ActivityOwner'] = $activity['ActivityOwner']['FirstName'] . ' ' . $activity['ActivityOwner']['LastName'];
                    $activity['Album'] = $originalActivity['Album'];
                    $activity['Files'] = $originalActivity['Files'];
                    $activity['SharePostContent'] = $activity['PostContent'];
                    $activity['PostContent'] = $originalActivity['PostContent'];

                    //copied from getFeedActivity
                    $activity['SharedActivityModule'] = $originalActivity['SharedActivityModule'];  
                    $activity['SharedEntityGUID'] = $originalActivity['SharedEntityGUID'];

                    if ($activity_type_id == 10 || $activity_type_id == 15)
                    {
                        if ($originalActivity['UserID'] == $res['UserID'])
                        {
                            $activity['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($originalActivity['UserID']), $activity['Message']);
                        } else
                        {
                            if ($originalActivity['ParentActivityTypeID'] == '11' || $originalActivity['ParentActivityTypeID'] == '7')
                            {
                                $u_d = get_detail_by_id($originalActivity['UserID'], 3, 'FirstName,LastName', 2);
                                if ($u_d)
                                {
                                    $activity['Message'] = str_replace("{{OBJECT}}", $u_d['FirstName'] . ' ' . $u_d['LastName'], $activity['Message']);
                                }
                            }                            
                        }
                    }
                    if ($res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15')
                    {
                        $activity['Album'] = $this->get_albums($activity['ParentActivityID'], $res['UserID'], '', 'Media');
                        if (!empty($activity['Album']['AlbumType']))
                        {
                            $activity['EntityType'] = ucfirst(strtolower($activity['Album']['AlbumType']));
                        } else
                        {
                            $activity['EntityType'] = 'Media';
                        }
                    } else
                    {
                        $activity['EntityType'] = 'Post';
                        if ($originalActivity['ParentActivityTypeID'] == 5 || $originalActivity['ParentActivityTypeID'] == 6)
                        {
                            $activity['EntityType'] = 'Album';
                        }
                        if (!empty($originalActivity['Album']))
                        {
                            $activity['EntityType'] = 'Media';
                        }
                        $activity['OriginalActivityGUID'] = $originalActivity['ActivityGUID'];
                        $activity['OriginalActivityType'] = $originalActivity['ActivityType'];
                        $activity['OriginalActivityFirstName'] = $originalActivity['ActivityOwnerFirstName'];
                        $activity['OriginalActivityLastName'] = $originalActivity['ActivityOwnerLastName'];
                        $activity['OriginalActivityUserGUID'] = $originalActivity['ActivityOwnerUserGUID'];
                        $activity['OriginalActivityProfileURL'] = $originalActivity['ActivityOwnerProfileURL'];
                    }
                    if (isset($originalActivity['ParentActivityTypeID']) && $originalActivity['ParentActivityTypeID'] == 25)
                    {
                        $params = json_decode($originalActivity['Params']);
                        $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $entity_id);


                        $user_details_invite = $this->polls_model->get_invite_status('3', $user_id, $params->PollID);
                        if ($user_details_invite['TotalInvited'] > 0)
                        {
                            $activity['ShowInviteGraph'] = 1;
                        }
                    }
                }

                /** copied from getFeedActivity **/
                if($activity['IsOwner']== 1)    
                {           
                   $activity['IsLike'] = $this->is_liked($entity_id, 'Activity', $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);         
                }           
                else            
                {           
                   $activity['IsLike'] = $this->is_liked($entity_id, 'Activity', $user_id, 3, $user_id);              
                }           
                /** copied from getFeedActivity **/
            
                $log_type = 'Activity';         
                $log_id = $activity_id;

                if ($activity_type_id == 5 || $activity_type_id == 6 || $activity_type_id == 10 || $activity_type_id == 9)
                {
                    $album_flag = TRUE;
                    if ($activity_type_id == 10 || $activity_type_id == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($activity['ParentActivityID'], '', 'ActivityTypeID,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        if (!empty($parent_activity_detail))
                        {
                            if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6)))
                            {
                                if (!empty($parent_activity_detail['Params']))
                                {
                                    $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                    if (!empty($album_detail['AlbumGUID']))
                                    {
                                        @$activity['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                        $album_flag = TRUE;
                                    }
                                }
                            }
                        }
                    }
                    if ($album_flag)
                    {
                        $count = 4;
                        if ($activity_type_id == 6)
                        {
                            $count = $activity['Params']->count;
                        }
                        $album_details = $this->album_model->get_album_by_guid($activity['Params']->AlbumGUID);
                        $activity['AlbumEntityName'] = $activity['EntityName'];
                        $activity['EntityName'] = $album_details['AlbumName'];
                        $activity['Album'] = $this->get_albums($activity_id, $res['UserID'], $activity['Params']->AlbumGUID, 'Activity', $count);

                        $log_type = 'Album';    
                        $log_id = isset($activity['Album']['AlbumID']) ? $activity['Album']['AlbumID'] : 0 ;
                    }
                }
                
                
                //$activity['PostContent'] = $this->parse_tag($activity['PostContent']);
                $activity['PostContent'] = $this->parse_tag($activity['PostContent'],$activity_id);
                $activity['PostTitle'] = $this->parse_tag($activity['PostTitle'],$activity_id); 

                /** earlier it was commented **/
                if ($activity['IsEntityOwner'] == 1)
                {
                    $activity['LikeName'] = $this->getLikeName($activity_id, $user_id, $res['ModuleEntityOwner'], $BUsers);
                } else
                {
                    $activity['LikeName'] = $this->getLikeName($activity_id, $user_id, 0, $BUsers);
                }

                //check is liked or not
                $like_entity_type = 'ACTIVITY';
                $like_entity_id = $activity_id;
                if (in_array($activity_type_id, array(23,24)))
                {                    
                    $params = json_decode($res['Params']);
                    if ($params->MediaGUID)
                    {
                        $media_id = get_detail_by_guid($params->MediaGUID, 21, "MediaID", 1);
                        if ($media_id)
                        {
                            $like_entity_type = 'MEDIA';
                            $like_entity_id = $media_id;
                            $activity['Album'] = $this->get_albums($media_id, $res['UserID'], '', 'Media', 1);
                        }
                    }
                }

                if($activity['IsOwner']== 1)
                {
                   $activity['IsLike'] = $this->is_liked($like_entity_id, $like_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
                }
                else
                {
                   $activity['IsLike'] = $this->is_liked($like_entity_id, $like_entity_type, $user_id, 3, $user_id);  
                }

                unset($activity['LikeName']['IsLike']);
                /** earlier it was commented **/

                if ($res['ActivityTypeID'] == '1' || $res['ActivityTypeID'] == '8' || $res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '7' || $res['ActivityTypeID'] == '11' || $res['ActivityTypeID'] == '12' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15' || $res['ActivityTypeID'] == '5' || $res['ActivityTypeID'] == '6')
                {
                    $activity['CanShowSettings'] = 1;
                }

                if ($res['Privacy'] != 4 && ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12 || $res['ActivityTypeID'] == 14 || $res['ActivityTypeID'] == 15 || $res['ActivityTypeID'] == 5 || $res['ActivityTypeID'] == 6))
                {
                    $activity['ShareAllowed'] = 1;
                }

                if ($user_id == $res['UserID'])
                {                    
                    $activity['ShowPrivacy'] = 0;
                    if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 23 || $res['ActivityTypeID'] == 24)
                    {
                        $activity['ShowPrivacy'] = 1;
                    }
                }
                $activity['Comments'] = array();    
                if($activity['NoOfComments'] > 0)           
                {
                    $activity['Comments'] = $this->getActivityComments('Activity', $activity_id, '1', COMMENTPAGESIZE, $user_id, $activity['CanRemove'], 2, TRUE, $BUsers,FALSE,'',$res['PostAsModuleID'],$res['PostAsModuleEntityID'],$search_key);
                }

                if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12)
                {
                    $activity['PostContent'] = str_replace('­', '', $activity['PostContent']);
                    if (empty($activity['PostContent']))
                    {
                        $pcnt = $this->get_photos_count($res['ActivityID']);
                        if (isset($pcnt['Media']))
                        {
                            $activity['Message'] .= ' added ' . $pcnt['MediaCount'] . ' new ' . $pcnt['Media'];
                        }
                    }
                }
                $activity['LikeList'] = $this->getLikeDetails($activity['ActivityGUID'], 'ACTIVITY', array(), 0, 12, FALSE, $user_id);

                if (isset($activity['RatingData']['CreatedBy']['ModuleID']))
                {
                    $activity['UserProfileURL'] = $activity['RatingData']['CreatedBy']['ProfileURL'];
                    $activity['UserProfilePicture'] = $activity['RatingData']['CreatedBy']['ProfilePicture'];
                }

                $permission = $this->privacy_model->check_privacy($user_id, $res['UserID'], 'view_profile_picture');
                if (!$permission && $module_id == 3)
                {
                    $activity['UserProfilePicture'] = '';
                }
                $activity['PostContent'] = trim(str_replace('&nbsp;', ' ', $activity['PostContent']));
                $activity['PostTitle'] = trim(str_replace('&nbsp;', ' ', $activity['PostTitle']));
                $activity['ShareDetails'] = array('Title' => 'Activity', 'Summary' => $activity['PostContent'], 'Image' => '', 'Link' => get_short_link(site_url() . 'activity/' . $activity['ActivityGUID']));
                if (isset($activity['Album'][0]['Media'][0]))
                {
                    $share_image = IMAGE_SERVER_PATH . 'upload/';
                    if ($activity['ActivityType'] == 'ProfilePicUpdated')
                    {
                        $share_image .= 'profile/220x220/';
                    } else if ($activity['ActivityType'] == 'ProfileCoverUpdated')
                    {
                        $share_image .= 'profilebanner/1200x300';
                    } else if ($activity['Album'][0]['AlbumName'] == 'Wall Media')
                    {
                        $share_image .= 'wall/750x500';
                    } else if ($activity['Album'][0]['AlbumName'] != 'Wall Media')
                    {
                        $share_image .= 'album/750x500';
                    }
                    $share_image .= '/' . $activity['Album'][0]['Media'][0]['ImageName'];
                    $activity['ShareDetails']['Image'] = $share_image;
                }
                $cnt++;
                if ($res['ActivityTypeID'] == 16 || $res['ActivityTypeID'] == 17)
                {
                    if (!$activity['RatingData'])
                    {
                        continue;
                    }
                }
                
                $this->load->model(array('activity/activity_front_helper_model'));
                $activity = $this->activity_front_helper_model->get_sticky_setting($user_id,$activity_id,$role_id,$activity);

                //Check if  view Tags is allowed
                if($view_entity_tags)
                {
                    $activity['EntityTags'] = $this->tag_model->get_entity_tags('', 1, 30, 1, 'ACTIVITY', $activity_id, $user_id);
                }
                
                $activity['ActivityURL'] = get_single_post_url($activity, $res['ActivityID'], $res['ActivityTypeID'], $res['ModuleEntityID']);


                $share_data = array();
                if($res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15')
                {
                    $share_data = $this->activity_result_filter_model->getShareDetails($activity,$activity_type_id,$res['UserID']);
                    
                    if ($activity_type_id == 10 || $activity_type_id == 15) {
                        if ($share_data['ModuleID'] == '1' && $share_data['PostType'] == '7') {
                            $activity['Message'] = str_replace("{{OBJECT}}", "{{ACTIVITYOWNER}}", $activity['Message']);
                        } else {
                            if ($share_data['UserID'] == $res['UserID']) {
                                $activity['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($share_data['UserID']), $activity['Message']);
                            }
                        }
                    }
                    unset($share_data['ParentActivityTypeID']);
                }
                $activity['ShareDetails'] = $share_data;

                $return[] = $activity;
            }
        }
        return $return;
    }

    /**
     * [get_seen_list description]
     * @param  [int]    $user_id     [Current User ID]
     * @param  [string] $entity_type [Entity Type]
     * @param  [int]    $entity_id   [Entity ID]
     * @param  [int]    $page_no     [Page Number]
     * @param  [int]    $page_size   [Page Size]
     * @param  boolean  $count_only  [Count only flag]
     * @return [type]                [int/array based on value of count flag]
     */
    function get_seen_list($user_id, $entity_type, $entity_id, $page_no, $page_size, $count_only=false)
    {
        $data = array();

        $activity_details = get_detail_by_id($entity_id,0,'ActivityTypeID',2);
        if($activity_details['ActivityTypeID'] == '5' || $activity_details['ActivityTypeID'] == '6' || $activity_details['ActivityTypeID'] == '13')
        {
            $entity_type = 'Album';
            
            $this->db->select('AlbumID');
            $this->db->from(ALBUMS);
            $this->db->where('ActivityID',$entity_id);
            $query = $this->db->get();
            if($query->num_rows())
            {
                $entity_id = $query->row()->AlbumID;
            }
        }

        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $friends = $friend_followers_list['Friends'];
        $friends[] = 0;
        $followers = $friend_followers_list['Follow'];
        $followers[] = 0;
        $friends_list = implode("','", $friends);

        array_unshift($friends, $user_id);

        $this->db->select('U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture, U.UserID,PU.Url AS ProfileURL, "3" as ModuleID', false);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CN.CountryName,"") as CountryName', FALSE);
        $this->db->select("IF(U.UserID='" . $user_id . "',2,IF(U.UserID IN('" . $friends_list . "'),1,0)) as OrderByVar", false);
        $this->db->from(USERS.' U');
        $this->db->join(ENTITYVIEW.' EV','U.UserID=EV.UserID','left');
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CN', 'CN.CountryID=UD.CountryID', 'left');
        $this->db->where('EV.EntityType',$entity_type);
        $this->db->where('EV.EntityID',$entity_id);
        if(!$count_only)
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();
        if($query->num_rows())
        {
            if($count_only)
            {
                return $query->num_rows();
            }
            foreach($query->result_array() as $key=>$val)
            {
                $users_relation = get_user_relation($user_id, $val['UserID']);
                $privacy_details = $this->privacy_model->details($val['UserID']);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                        {
                            $val['ProfilePicture'] = 'user_default.jpg';
                        }
                        if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation))
                        {
                            $val['ShowFriendsBtn'] = 0;
                        }
                        if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation))
                        {
                            $val['CityName']    = '';
                            $val['CountryName'] = '';
                        }
                    }
                }
                $data[] = $val;
            }
        }
        return $data;
    }

    public function remove_unused_tags()
    {
        $this->db->where('EntityType','ACTIVITY');
        $this->db->where('EntityID in (select ActivityID from '.ACTIVITY.' where StatusID=3)');
        $this->db->delete(ENTITYTAGS);
        $this->db->where("TagID NOT IN (SELECT DISTINCT TagID FROM EntityTags)",NULL,FALSE);
        $this->db->delete(TAGS);
    }

    /**
     * [search_entity_activities Get the activity for wall]
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
    public function search_entity_activities($entity_id, $module_id, $page_no, $page_size, $current_user, $feed_sort_by, $filter_type = 0, $is_media_exists = 2, $activity_guid, $search_key, $start_date, $end_date, $feed_user = 0, $as_owner = 0, $count_only = false, $field = 'ALL', $activity_type_filter = array(), $m_entity_id = '', $entity_module_id = '', $comment_id = '',$view_entity_tags='',$role_id=2,$include_entity=0,$posted_by='0', $modified_start_date='', $modified_end_date='',$selected_group_ids=array(),$search_only_for=0)
    {

        $time_zone = $this->user_model->get_user_time_zone();
        
        $this->load->model(array('polls/polls_model','category/category_model'));
        
        $group_list = $this->group_model->get_user_group_list();
        $group_list[] = 0;
        $group_list_new = $group_list;
        $group_list = implode(',', $group_list);
        if ($module_id == 3)
        {
            $event_list_current_user = $this->event_model->get_all_joined_events($current_user, true);
            $event_list_current_user[] = 0;
            $event_list = $event_list_current_user;

            if ($current_user != $entity_id)
            {
                $group_list_temp = $this->group_model->get_users_groups($entity_id);
                $group_list_temp[] = 0;
                $group_list_new = array_intersect($group_list_new, $group_list_temp);

                $this->db->select('GroupID');
                $this->db->from(GROUPS);
                $this->db->where('IsPublic', 1);
                $this->db->where_in('GroupID', $group_list_temp);
                $query = $this->db->get();
                if ($query->num_rows())
                {
                    foreach ($query->result_array() as $key => $value)
                    {
                        $group_list_new[] = $value['GroupID'];
                    }
                }

                $event_list_view_user = $this->event_model->get_all_joined_events($entity_id, true);
                $event_list_view_user[] = 0;
                $event_list = array_intersect($event_list_current_user, $event_list_view_user);
            }
        }

        $blocked_users = $this->blocked_users;

        $condition = '';
        if ($module_id == 3)
        {
            $permission = $this->privacy_model->check_privacy($current_user, $entity_id, 'default_post_privacy');
        }

        if ($module_id == 3)
        {
            $is_relation = $this->isRelation($entity_id, $current_user, true); // Visibility
        } 
        else
        {
            $is_relation = array(1, 2, 3, 4);
        }
        if ($module_id == 1)
        {
            $activity_type_allow = array(7, 5, 6, 25);
        } 
        else if ($module_id == 3)
        {
            $activity_type_allow = array(1, 8, 9, 10, 5, 6, 14, 15, 25, 7, 11, 12);
        } 
        else if ($module_id == 14)
        {
            $activity_type_allow = array(11, 5, 6);
        } 
        else if ($module_id == 18)
        {
            $activity_type_allow = array(1, 8, 12, 5, 6, 25);
        } 
        else
        {
            $activity_type_allow = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 25);
        }

        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $friends = $friend_followers_list['Friends'];
        $follow = $friend_followers_list['Follow'];
        $friends[] = 0;
        $follow[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($current_user, $friend_followers_list))
        {
            $friend_followers_list[] = $current_user;
        }
        $friend_followers_list = implode(',', $friend_followers_list);

        if (!in_array($current_user, $follow))
        {
            $follow[] = $current_user;
        }

        if (!in_array($current_user, $friends))
        {
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

        if ($field == 'ALL')
        {
            $this->db->select('A.*,ATY.ViewTemplate,ATY.Template,ATY.LikeAllowed,ATY.CommentsAllowed,ATY.ActivityType,ATY.ActivityTypeID,ATY.FlagAllowed,ATY.ShareAllowed,ATY.FavouriteAllowed,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        } else
        {
            $this->db->select($field);
        }
        $this->db->distinct();
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID=A.ActivityID', 'left');
        $this->db->join(TAGS . ' TG', 'TG.TagID=ET.TagID', 'left');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');        
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        //echo $filter_type;
        if (($filter_type == 1 || $filter_type === 'Favourite') && $filter_type != '0')
        {
            $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"', 'JOIN');
            $this->db->where('F.UserID', $current_user);
            $this->db->where('F.StatusID', '2');
        } else if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            //$this->db->where('F.UserID',$current_user);
            $this->db->where('F.StatusID', '2');
            $this->db->where('A.Flaggable!=0', null, false);
            $this->db->group_by('F.EntityID');
        }

        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $current_user);
        } else if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $current_user . "'", "join");
            $this->db->_protect_identifiers = TRUE;
        } else
        {
            $this->db->where("IF(A.UserID='" . $current_user . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        }

        if (!$include_entity['Archive'] && !$this->settings_model->isDisabled(43))// Earliar it was:($filter_type == 4)
        {
            $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $current_user . "')", NULL, FALSE);
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            $this->db->select("R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus", FALSE);
            $this->db->select("IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder", false);

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

            $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            $this->db->_protect_identifiers = TRUE;
        }

        if ($condition)
        {
            $this->db->where($condition, null, false);
        }

        /* Search parameters */
        //if include parameters are set
        
        if (!empty($search_key))
        {
            $where_attachment = $where_user_and_group = '';
            if($include_entity['Attachment'])
            {
                $where_attachment = ' OR A.ActivityID IN(SELECT MediaSectionReferenceID FROM Media where MediaSectionReferenceID=A.ActivityID AND OriginalName like "%'.addslashes($search_key).'%")';   
            }
            if($include_entity['UserAndGroup'])
            {
                $where_user_and_group = ' OR U.FirstName LIKE "%' . addslashes($search_key) . '%" OR U.LastName LIKE "%' . addslashes($search_key) . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . addslashes($search_key) . '%" OR IF(A.ModuleID="1",A.ModuleEntityID in (Select GroupID From Groups where StatusID="2" and GroupName LIKE "%' . addslashes($search_key) . '%"),0) OR IF(A.ModuleID="14",A.ModuleEntityID in (Select EventID From Events where StatusID="2" and Title LIKE "%' . addslashes($search_key) . '%"),0) OR IF(A.ModuleID="18",A.ModuleEntityID in (Select PageID From Pages where StatusID="2" and Title LIKE "%' . addslashes($search_key) . '%"),0)';
            }            

            //New Multivalued condition for search_only_for param
            //if search only for is an array and multivalued selection from front end
            if(is_array($search_only_for) && !empty($search_only_for))
            {
                foreach($search_only_for as $search_value)
                {
                    switch($search_value)   
                    {
                        case '1': #Post
                            $search_only_for_condition.= 'OR A.PostContent LIKE "%' . addslashes($search_key) . '%"';
                            break;
                        case '2': #comment
                            $search_only_for_condition.=' OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostComment`) - LENGTH(REPLACE(LOWER(`PostComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc )';
                            break;
                        case '3': #meetings
                            break;
                        case '4': #wiki
                            break;
                        default: #none
                    }
                }    
                //end of multivalued search
                $search_only_for_condition.=')';                 
                $this->db->where($search_only_for_condition,NULL,FALSE);              
            }
            else
            {   //if not multivalued
                switch ($search_only_for) 
                {
                    case '1':
                        # Post
                        $this->db->where('(A.PostContent LIKE "%' . addslashes($search_key) . '%" OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID = 0 and Title LIKE "%' . addslashes($search_key) . '%" ) '.$where_user_and_group . $where_attachment .' OR TG.Name like "%'.addslashes($search_key).'%")', NULL, FALSE);                                    
                        break;
                    case '2':
                        # Comment                
                        $this->db->where('( A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostComment`) - LENGTH(REPLACE(LOWER(`PostComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc ) OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID > 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR TG.Name like "%'.addslashes($search_key).'%" ' . $where_attachment . $where_user_and_group . ')', NULL, FALSE);                
                        break;
                    case '3':
                        # Meetings (dev Pending)                
                        break;
                    case '4':
                        # Wiki Post (dev Pending)                
                        break; 
                    case '5':
                        # Post And Comment both
                        $this->db->where('(A.PostContent LIKE "%' . addslashes($search_key) . '%"  OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostComment`) - LENGTH(REPLACE(LOWER(`PostComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc ) OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID > 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR TG.Name like "%'.addslashes($search_key).'%" ' . $where_attachment . $where_user_and_group . ')', NULL, FALSE);
                        break;            
                    default:
                        # code...                    
                        $this->db->where('( A.PostContent LIKE "%' . addslashes($search_key) . '%" OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID = 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostComment LIKE "%' . addslashes($search_key) . '%" Order By (LENGTH(`PostComment`) - LENGTH(REPLACE(LOWER(`PostComment`), \''.addslashes($search_key).'\', \'\'))) / LENGTH(\''.addslashes($search_key).'\') desc ) OR A.ActivityID in (select ActivityID from Mention where ActivityID=A.ActivityID and PostCommentID > 0 and Title LIKE "%' . addslashes($search_key) . '%" ) OR TG.Name like "%'.addslashes($search_key).'%" ' . $where_attachment . $where_user_and_group . ')', NULL, FALSE);
                        
                        break;        
                }    
            }  
        }
       
        //get data by posted by        
        if(isset($posted_by) && !empty($posted_by))
        {
            switch ($posted_by) 
            {
                case '1':
                    $this->db->where('A.UserID',$current_user);
                    break;
                case '2':
                    //Friends
                    $friends_ids = $this->friend_model->getFriendIDS($current_user);
                    if(!empty($friends_ids))
                    {
                        $this->db->where_in('U.UserID',$friends_ids);                
                    }
                    else
                    {
                        $this->db->where('U.UserID',0);                   
                    }
                    break;
                case '3':
                    //Friends + Groups
                    $friends_ids = $this->friend_model->getFriendIDS($current_user);
                    if(!empty($friends_ids))
                    {
                        $this->db->where('U.UserID IN ('.implode(',',$friends_ids)  .') OR A.ModuleID=1',NULL,FALSE);
                    }
                    break;
                default:
                    # selected users
                    if(is_array($posted_by))
                    {
                        $this->db->where_in('U.UserID',$posted_by);
                    }
                    break;
            }    
        }
        
        //search on Modified date
        if ($modified_start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.ModifiedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $modified_start_date . "'", NULL, FALSE);
        }
        if ($modified_end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.ModifiedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $modified_end_date . "'", NULL, FALSE);
        }

        /* Search parameters */        

        if ($privacy_condition)
        {

            if ($module_id == 3)
            {
                if (!$permission)
                {
                    $permission_cond = "IF(A.UserID IN(" . $entity_id . "," . $current_user . "),TRUE,FALSE)";
                } else
                {
                    $permission_cond = "IF(TRUE,TRUE,FALSE)";
                }
                $pc = "(
                        IF(A.UserID=A.ModuleEntityID," . $privacy_condition . "," . $permission_cond . ")
                    )";
                $this->db->where($pc, null, false);
            } else
            {
                $this->db->where($privacy_condition, null, false);
            }
        }
        if ($is_media_exists == 1 || $is_media_exists == 0)
        {
            $this->db->where('A.IsMediaExist', $is_media_exists);
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
        
        $module_id = $this->db->escape_str($module_id);
        $entity_id = $this->db->escape_str($entity_id);
        
        if ($group_list_new)
        {
            $mention_condition = "
                ((A.ModuleID=" . $module_id . " AND A.ModuleEntityID=" . $entity_id . ") OR (A.ActivityID IN(SELECT SM.ActivityID FROM Mention SM LEFT JOIN Activity SA ON SM.ActivityID=SA.ActivityID WHERE SM.ModuleID=" . $module_id . " AND SM.ModuleEntityID=" . $entity_id . " AND IF(SA.ModuleID=1,SA.ModuleEntityID IN(" . implode(',', $group_list_new) . "),'')))";
        } else
        {
            $mention_condition = "
                ((A.ModuleID=" . $module_id . " AND A.ModuleEntityID=" . $entity_id . ") OR (A.ActivityID IN(SELECT SM.ActivityID FROM Mention SM LEFT JOIN Activity SA ON SM.ActivityID=SA.ActivityID WHERE SM.ModuleID=" . $module_id . " AND SM.ModuleEntityID=" . $entity_id . " AND IF(SA.ModuleID=1,false,'')))";
        }

        if ($module_id == 3)
        {
            /* if (!empty($page_list))
            {
                $mention_condition.= " OR IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
          A.ModuleID=18 AND A.ModuleEntityID IN(" . $page_list . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            } */

            if (!empty($group_list_new))
            {
                $mention_condition .= " OR IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
          A.ModuleID=1 AND A.ModuleEntityID IN(" . implode(',', $group_list_new) . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }

            if (!empty($event_list))
            {
                $event_list = implode(',', $event_list);
                $mention_condition .= " OR IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
          A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ") AND A.UserID=" . $entity_id . "
          ), '' )";
            }
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
        /*if ($search_key)
        {
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND StatusID=2 AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }*/

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }


        $this->db->where("(A.UserID='" . $current_user . "' OR A.Privacy IN(" . implode(',', $is_relation) . ") OR (SELECT ActivityID FROM " . MENTION . " WHERE ModuleID='3' AND ModuleEntityID='" . $current_user . "' AND ActivityID=A.ActivityID LIMIT 1) is not null)", NULL, FALSE);

        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        $this->db->where_not_in('U.StatusID', array(3, 4));
        $this->db->order_by('A.StickyDate', 'DESC');
        if ($feed_sort_by == 1)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }
        if (!$count_only && !empty($page_size))
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $result = $this->db->get();
        // echo $this->db->last_query();die;
        if ($count_only)
        {
            return $result->num_rows();
        }
        if ($field != 'ALL')
        {
            return $result->result_array();
        }
        $return = array();
        if ($result->num_rows())
        {
            foreach ($result->result_array() as $res)
            {
                $activity = array();
                $activity_id = $res['ActivityID'];
                $activity_guid = $res['ActivityGUID'];
                $module_id = $res['ModuleID'];
                $activity_type_id = $res['ActivityTypeID'];
                $module_entity_id = $res['ModuleEntityID'];
                $activity['IsFlaggedIcon'] = 0;
                $activity['PostAsModuleID'] = $res['PostAsModuleID'];

                $activity['IsDeleted'] = 0;
                if ($filter_type == '7')
                {
                    $activity['IsDeleted'] = 1;
                }
                $activity['IsMember'] = 0;                

                $activity['ShowBTNCommentsAllowed'] = 0;
                $activity['MuteAllowed'] = 1;
                $activity['ShowFlagBTN'] = 1;

                if ($res['UserID'] == $current_user)
                {
                    if ($res['ModuleID'] == 3 && $current_user == $res['ModuleEntityID'])
                    {
                        $activity['ShowBTNCommentsAllowed'] = 1;
                    }
                    $activity['MuteAllowed'] = 1;
                    $activity['ShowFlagBTN'] = 1;
                }

               // $activity['IsTagged'] = $activity['IsTagged'] = (in_array($res['ActivityID'], $this->activity_model->get_user_tagged())) ? 1 : 0; // $this->is_tagged($res['ActivityID'], $current_user);
                $activity['IsTagged'] = $this->is_tagged($res['ActivityID'], $current_user);

                $activity['IsEntityOwner'] = 0;
                $activity['IsOwner'] = 0;
                $activity['IsFlagged'] = 0;
                $activity['CanShowSettings'] = 0;
                $activity['CanRemove'] = 0;
                $activity['CanMakeSticky'] = 0;
                $activity['ShowPrivacy'] = 0;
                $activity['PostAsEntityOwner'] = 0;
                $activity['OriginalActivityGUID'] = '';
                $activity['OriginalActivityType'] = '';
                $activity['OriginalActivityFirstName'] = '';
                $activity['OriginalActivityLastName'] = '';
                $activity['OriginalActivityUserGUID'] = '';
                $activity['OriginalActivityProfileURL'] = '';
                $activity['IsArchive'] = 0;
                $user_archive = $this->get_user_activity_archive();
                if (isset($user_archive[$activity_id]))
                {
                    $activity['IsArchive'] = $user_archive[$activity_id];
                }
                $activity['ActivityGUID'] = $activity_guid;
                $activity['ModuleID'] = $module_id;
                $activity['UserGUID'] = get_guid_by_id($res['UserID'], 3);
                $activity['ActivityType'] = $res['ActivityType'];
                $activity['NoOfFavourites'] = $res['NoOfFavourites'];

                $activity['PollData'] = array();
                $activity['Links'] = $this->get_activity_links($res['ActivityID']);
                $activity['Reminder'] = array();
                if (isset($res['ReminderGUID']))
                {
                    $activity['Reminder'] = array('ReminderGUID' => $res['ReminderGUID'], 'ReminderDateTime' => $res['ReminderDateTime'], 'CreatedDate' => $res['ReminderCreatedDate'], 'Status' => $res['ReminderStatus'], 'Meridian' => '');
                }

                if ($res['ActivityTypeID'] == '23' || $res['ActivityTypeID'] == '24')
                {
                    $params = json_decode($res['Params'], true);
                    $entity_id = get_detail_by_guid($params['MediaGUID'], 21, 'MediaID', 1);
                    $activity['NoOfComments'] = $this->get_activity_comment_count('Media', $entity_id, $blocked_users); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->get_like_count($entity_id, "MEDIA", $blocked_users); //$res['NoOfLikes'];
                    //$activity['NoOfDislikes'] = $this->get_like_count($entity_id, "MEDIA", $blocked_users, 3); //
                } 
                else
                {
                    $activity['NoOfComments'] = $this->get_activity_comment_count('Activity', $activity_id, $blocked_users); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->get_like_count($activity_id, "ACTIVITY", $blocked_users);
                    //$activity['NoOfDislikes'] = $this->get_like_count($activity_id, "ACTIVITY", $blocked_users, 3);
                }
                //$activity['NoOfViews'] = $res['NoOfViews'];
                $activity['NoOfShares'] = $res['NoOfShares'];

                $activity['Message'] = $res['Template'];
                $activity['ViewTemplate'] = $res['ViewTemplate'];

                $activity['CommentsAllowed'] = 0;
                if ($res['IsCommentable'] && $res['CommentsAllowed'])
                {
                    $activity['CommentsAllowed'] = 1;
                }

                $activity['LikeAllowed'] = $res['LikeAllowed'];
                $activity['FlagAllowed'] = $res['FlagAllowed'];
                $activity['ShareAllowed'] = $res['ShareAllowed'];
                $activity['FavouriteAllowed'] = $res['FavouriteAllowed'];

                $activity['CreatedDate'] = $res['CreatedDate'];
                $activity['ModifiedDate'] = $res['LastActionDate'];

                $activity['IsSticky'] = $res['IsSticky'];

                $activity['Visibility'] = $res['Privacy'];
                $activity['PostContent'] = $res['PostContent'];
                $activity['ParentActivityID'] = $res['ParentActivityID'];

                $SharedActivityDetail = $this->getSharedActivityDetail($res['ParentActivityID']);
                $activity['SharedActivityModule'] = $SharedActivityDetail['SharedActivityModule'];
                $activity['SharedEntityGUID'] = $SharedActivityDetail['SharedEntityGUID'];

                $activity['Album'] = $this->get_albums($activity_id, $res['UserID']);
                $activity['Files'] = $this->get_activity_files($activity_id);
                $activity['Params'] = json_decode($res['Params']);
                //$activity['IsSubscribed'] = (in_array($activity_id, $this->subscribe_model->get_user_subscribed())) ? 1 : 0;
                $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($current_user, 'Activity', $activity_id);
                $activity['IsFavourite'] = (in_array($activity_id, $this->favourite_model->get_user_favourite())) ? 1 : 0;

                $activity['Flaggable'] = $res['Flaggable'];
                $activity['FlaggedByAny'] = $this->flagged_by_any($activity_id);
                $activity['CanBlock'] = 0;

                $activity['IsFlagged'] = (in_array($activity_id, $this->flag_model->get_user_flagged())) ? 1 : 0;
                
                if ($res['UserID'] == $current_user)
                {
                    $activity['IsOwner'] = 1;
                    //$activity['Flaggable'] = 0;
                } else
                {
                    // $activity['CanBlock'] = 1;
                }

                if ($current_user == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                {
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                    $activity['ShowPrivacy'] = 1;
                }

                $activity['EntityName'] = '';
                $activity['EntityProfilePicture'] = '';

                $activity['UserName'] = $res['FirstName'] . ' ' . $res['LastName'];
                $activity['UserProfilePicture'] = $res['ProfilePicture'];
                $activity['UserProfileURL'] = get_entity_url($res['UserID'], 'User', 1);
                $activity['EntityType'] = '';
                $activity['IsExpert']   = 0;

                if ($module_id == 1)
                {
                    $group_details = check_group_permissions($current_user, $module_entity_id);

                    //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                    if (isset($group_details['Details']) && !empty($group_details['Details']))
                    {
                        $entity                         = $group_details['Details'];
                        $activity['EntityProfileURL']   = $module_entity_id;
                        $activity['EntityGUID']         = $entity['GroupGUID'];
                        $activity['EntityName']         = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        if(empty($group_details['CanComment']))
                        {
                            $activity['CommentsAllowed'] = 0;
                        }

                        if ($group_details['IsAdmin'])
                        {
                            $activity['IsEntityOwner']  = 1;
                            $activity['CanRemove']      = 1;
                            $activity['CanBlock']       = 1;
                            $activity['CanMakeSticky']  = 1;
                        }
                        if ($this->group_model->check_group_creator($module_entity_id, $res['UserID']))
                        {
                            $activity['CanBlock'] = 0;
                        }

                        if ($res['ModuleEntityOwner'] == 1)
                        {
                            $activity['UserName']           = $activity['EntityName'];
                            $activity['UserProfilePicture'] = $activity['EntityProfilePicture'];
                            $activity['UserProfileURL']     = $activity['EntityProfileURL'];
                            $activity['UserGUID']           = $activity['EntityGUID'];
                        }
                    }

                    if (!empty($group_list_new) && in_array($module_entity_id, $group_list_new))
                    {
                        $activity['IsMember'] = 1;
                    }
                }
                if ($module_id == 3)
                {
                    $activity['EntityName'] = $activity['UserName'];
                    $activity['EntityProfilePicture'] = $activity['UserProfilePicture'];
                    $activity['EntityGUID'] = $activity['UserGUID'];

                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity)
                    {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($module_entity_id, 'User', 1);
                    if ($current_user == $module_entity_id)
                    {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanMakeSticky'] = 1;
                        $activity['CanBlock'] = 1;
                    }
                    if(in_array($entity_id, $friends) || $current_user == $entity_id)
                    {
                        $activity['IsMember'] = 1;
                    }
                }
                if ($module_id == 14)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $activity['EntityGUID'] = $entity['EventGUID'];
                    }
                    $activity['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');                    

                    if ($this->event_model->is_admin($module_entity_id, $current_user))
                    {
                        //$activity['IsOwner']        = 1;
                        $activity['CanRemove'] = 1;
                        $activity['CanMakeSticky'] = 1;
                        $activity['IsEntityOwner'] = 1;
                        $activity['ShowPrivacy'] = 0;
                        $activity['CanBlock'] = 1;
                    }
                    if ($this->event_model->isEventOwner($module_entity_id, $res['UserID']))
                    {
                        $activity['CanBlock'] = 0;
                    }

                    if ($this->event_model->check_member($entity_id, $current_user, false, true))
                    {
                        $activity['IsMember'] = 1;
                    }
                }
                if ($module_id == 18)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                        $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                        $category_icon = $category_name-['Icon'];
                        if ($entity['ProfilePicture'] == '')
                        {
                            $activity['EntityProfilePicture'] = $category_icon;
                        }

                        $activity['ModuleEntityOwner'] = $res['ModuleEntityOwner'];
                        if ($res['PostAsModuleID'] == 18 )
                        { 
                            $PostAs=$this->page_model->get_page_detail_cache($res['PostAsModuleEntityID']);
                            $activity['ModuleEntityOwner'] = 1;
                            $activity['UserName'] = $PostAs['Title'];
                            $activity['UserProfilePicture'] = $PostAs['ProfilePicture'];
                            $activity['UserProfileURL'] = $PostAs['PageURL'];
                            $activity['UserGUID'] = $PostAs['PageGUID'];
                        }
                       
                        if ($res['PostAsModuleEntityID']!=$module_entity_id && $res['ActivityTypeID'] == 12)
                        {
                            $activity['Message'] = $activity['Message'] . ' posted in {{Entity}}';
                        }
                        
                    }

                    $activity['PostAsEntityOwner'] = $res['ModuleEntityOwner'];
                    if ($this->page_model->check_page_owner($current_user, $module_entity_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['CanMakeSticky'] = 1;
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanBlock'] = 1;
                    }

                    if ($this->page_model->check_page_creator($res['UserID'], $module_entity_id))
                    {
                        $activity['CanBlock'] = 0;
                    }
                    if ($res['ModuleEntityOwner'] == 1)
                    {
                        $activity['CanBlock'] = 0;
                    }
                    if ($this->page_model->check_member($entity_id, $current_user))
                    {
                        $activity['IsMember'] = 1;
                    }
                }

                if ($res['UserID'] == $current_user)
                {
                    $activity['CanBlock'] = 0;
                }

                if (!isset($activity['EntityProfileURL']))
                {
                    $activity['EntityProfileURL'] = $activity['UserProfileURL'];
                }

                if ($activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 14 || $activity_type_id == 15)
                {
                    $originalActivity = $this->get_activity_details($res['ParentActivityID'], $activity_type_id);
                    $activity['ActivityOwner'] = $this->user_model->getUserName($originalActivity['UserID'], $originalActivity['ModuleID'], $originalActivity['ModuleEntityID']);
                    $activity['ActivityOwnerLink'] = $activity['ActivityOwner']['ProfileURL'];
                    $activity['ActivityOwner'] = $activity['ActivityOwner']['FirstName'] . ' ' . $activity['ActivityOwner']['LastName'];
                    $activity['Album'] = $originalActivity['Album'];
                    $activity['Files'] = $originalActivity['Files'];
                    $activity['SharePostContent'] = $activity['PostContent'];
                    $activity['PostContent'] = $originalActivity['PostContent'];
                    if ($activity_type_id == 10 || $activity_type_id == 15)
                    {
                        if ($originalActivity['UserID'] == $res['UserID'])
                        {
                            $activity['Message'] = str_replace("{{OBJECT}}'s", $this->notification_model->get_gender($originalActivity['UserID']), $activity['Message']);
                        } else
                        {
                            if ($originalActivity['ParentActivityTypeID'] == '11' || $originalActivity['ParentActivityTypeID'] == '7')
                            {
                                $u_d = get_detail_by_id($originalActivity['UserID'], 3, 'FirstName,LastName', 2);
                                if ($u_d)
                                {
                                    $activity['Message'] = str_replace("{{OBJECT}}", $u_d['FirstName'] . ' ' . $u_d['LastName'], $activity['Message']);
                                }
                            }
                        }
                    }
                    if ($res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15')
                    {
                        $activity['Album'] = $this->get_albums($activity['ParentActivityID'], $res['UserID'], '', 'Media');
                        if (!empty($activity['Album']['AlbumType']))
                        {
                            $activity['EntityType'] = ucfirst(strtolower($activity['Album']['AlbumType']));
                        } else
                        {
                            $activity['EntityType'] = 'Media';
                        }
                    } else
                    {
                        $activity['EntityType'] = 'Post';
                        if ($originalActivity['ParentActivityTypeID'] == 5 || $originalActivity['ParentActivityTypeID'] == 6)
                        {
                            $activity['EntityType'] = 'Album';
                        }
                        if (!empty($originalActivity['Album']))
                        {
                            $activity['EntityType'] = 'Media';
                        }
                        $activity['OriginalActivityGUID'] = $originalActivity['ActivityGUID'];
                        $activity['OriginalActivityType'] = $originalActivity['ActivityType'];
                        $activity['OriginalActivityFirstName'] = $originalActivity['ActivityOwnerFirstName'];
                        $activity['OriginalActivityLastName'] = $originalActivity['ActivityOwnerLastName'];
                        $activity['OriginalActivityUserGUID'] = $originalActivity['ActivityOwnerUserGUID'];
                        $activity['OriginalActivityProfileURL'] = $originalActivity['ActivityOwnerProfileURL'];
                    }
                }

                if ($activity_type_id == 5 || $activity_type_id == 6 || $activity_type_id == 10 || $activity_type_id == 9)
                {
                    $album_flag = TRUE;
                    if ($activity_type_id == 10 || $activity_type_id == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($activity['ParentActivityID'], '', 'ActivityTypeID,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        if (!empty($parent_activity_detail))
                        {
                            if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6)))
                            {
                                if (!empty($parent_activity_detail['Params']))
                                {
                                    $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                    if (!empty($album_detail['AlbumGUID']))
                                    {
                                        @$activity['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                        $album_flag = TRUE;
                                    }
                                }
                            }
                        }
                    }
                    if ($album_flag)
                    {
                        $count = 4;
                        if ($activity_type_id == 6)
                        {
                            if (isset($activity['Params']->count))
                            {
                                $count = $activity['Params']->count;
                            }
                        }
                        $album_details = $this->album_model->get_album_by_guid($activity['Params']->AlbumGUID);
                        if ($album_details)
                        {
                            $activity['EntityName'] = $album_details['AlbumName'];
                        } else
                        {
                            $activity['EntityName'] = '';
                        }
                        $activity['Album'] = $this->get_albums($activity_id, $res['UserID'], $activity['Params']->AlbumGUID, 'Activity', $count);
                    }
                }
                
                if ($res['ActivityTypeID'] == 25)
                {
                    $params = json_decode($res['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $m_entity_id);
                    //$activity['FavouriteAllowed']   = 1;
                    //$activity['ShareAllowed']       = 1;
                    //$activity['CommentsAllowed']    = 1;
                    //$activity['ShowBTNCommentsAllowed'] = 0;
                    $activity['MuteAllowed'] = 0;
                    $activity['ShowFlagBTN'] = 0;
                }

                if (isset($originalActivity['ParentActivityTypeID']) && $originalActivity['ParentActivityTypeID'] == 25)
                {
                    $params = json_decode($originalActivity['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $m_entity_id);
                }
                
                $activity['PostContent'] = $this->parse_tag($activity['PostContent']);

                if ($activity['IsEntityOwner'] == 1)
                {
                    $activity['LikeName'] = $this->getLikeName($activity_id, $current_user, $res['ModuleEntityOwner'], $blocked_users);
                } else
                {
                    $activity['LikeName'] = $this->getLikeName($activity_id, $current_user, 0, $blocked_users);
                }

                //check is liked or not                
                $like_entity_type = 'ACTIVITY';
                $like_entity_id = $activity_id;
                if (in_array($activity_type_id, array(23,24)))
                {                    
                    $params = json_decode($res['Params']);
                    if ($params->MediaGUID)
                    {
                        $media_id = get_detail_by_guid($params->MediaGUID, 21, "MediaID", 1);
                        if ($media_id)
                        {
                            $like_entity_type = 'MEDIA';
                            $like_entity_id = $media_id;
                            $activity['Album'] = $this->get_albums($media_id, $res['UserID'], '', 'Media', 1);
                        }
                    }
                }

                if($activity['IsOwner']== 1)
                {
                   $activity['IsLike'] = $this->is_liked($like_entity_id, $like_entity_type, $current_user, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
                }
                else
                {
                   $activity['IsLike'] = $this->is_liked($like_entity_id, $like_entity_type, $current_user, 3, $current_user);  
                }
                
                unset($activity['LikeName']['IsLike']);

                if ($res['ActivityTypeID'] == '1' || $res['ActivityTypeID'] == '8' || $res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '7' || $res['ActivityTypeID'] == '11' || $res['ActivityTypeID'] == '12' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15' || $res['ActivityTypeID'] == '5' || $res['ActivityTypeID'] == '6')
                {
                    $activity['CanShowSettings'] = 1;
                }

                if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12 || $res['ActivityTypeID'] == 14 || $res['ActivityTypeID'] == 15 || $res['ActivityTypeID'] == 5 || $res['ActivityTypeID'] == 6)
                {
                    $activity['ShareAllowed'] = 1;
                } else
                {
                    $activity['ShareAllowed'] = 0;
                }
                if ($current_user == $res['UserID'])
                {
                    //$activity['ShareAllowed'] = 0; // do not show share likn for self post
                    if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 8 || $res['ActivityTypeID'] == 9 || $res['ActivityTypeID'] == 10)
                    {
                        $activity['ShowPrivacy'] = 1;
                    }
                }

                if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12)
                {
                    $activity['PostContent'] = str_replace('­', '', $activity['PostContent']);
                    if (empty($activity['PostContent']))
                    {
                        $pcnt = $this->get_photos_count($res['ActivityID']);
                        if (isset($pcnt['Media']))
                        {
                            $activity['Message'] .= ' added ' . $pcnt['MediaCount'] . ' new ' . $pcnt['Media'];
                        }
                    }
                }

                $activity['Comments'] = $this->getActivityComments('Activity', $activity_id, '1', COMMENTPAGESIZE, $current_user, $activity['CanRemove'], 2, TRUE, $blocked_users, FALSE, $comment_id,$res['PostAsModuleID'],$res['PostAsModuleEntityID']);

                $activity['LikeList'] = $this->getLikeDetails($activity['ActivityGUID'], 'ACTIVITY', array(), 0, 12, FALSE, $current_user);
                $permission = $this->privacy_model->check_privacy($current_user, $res['UserID'], 'view_profile_picture');
                if (!$permission && $module_id == 3)
                {
                    $activity['UserProfilePicture'] = '';
                }
                $activity['PostContent'] = trim(str_replace('&nbsp;', ' ', $activity['PostContent']));
                $activity['ShareDetails'] = array('Title' => 'Activity', 'Summary' => $activity['PostContent'], 'Image' => '', 'Link' => get_short_link(site_url() . 'activity/' . $activity['ActivityGUID']));
                if (isset($activity['Album'][0]['Media'][0]))
                {
                    $share_image = IMAGE_SERVER_PATH . 'upload/';
                    if ($activity['ActivityType'] == 'ProfilePicUpdated')
                    {
                        $share_image .= 'profile/220x220/';
                    } else if ($activity['ActivityType'] == 'ProfileCoverUpdated')
                    {
                        $share_image .= 'profilebanner/1200x300';
                    } else if ($activity['Album'][0]['AlbumName'] == 'Wall Media')
                    {
                        $share_image .= 'wall/750x500';
                    } else if ($activity['Album'][0]['AlbumName'] != 'Wall Media')
                    {
                        $share_image .= 'album/750x500';
                    }
                    $share_image .= '/' . $activity['Album'][0]['Media'][0]['ImageName'];
                    $activity['ShareDetails']['Image'] = $share_image;
                }
                if ($activity['IsEntityOwner'] == 1 && $res['ModuleID'] == 18)
                {
                    $activity['IsFlaggedIcon'] = $this->flag_model->check_flagged($activity_id, 'Activity');
                }
                
                $this->load->model(array('activity/activity_front_helper_model'));
                $activity = $this->activity_front_helper_model->get_sticky_setting($user_id,$activity_id,$role_id,$activity);

                //Check if  view Tags is allowed
                if($view_entity_tags)
                {
                    $activity['EntityTags'] = $this->tag_model->get_entity_tags($search_key, 1, 30, 1, 'ACTIVITY', $activity_id, $current_user);
                }                
                $return[] = $activity;
            }
        }
        //echo strlen(json_encode($return));
        return $return;
    
    }
    
    public function set_activity_job($page_no=1, $page_size=100)
    {
       initiate_worker_job('cache_all_activity', array('page_no'=>$page_no,'page_size'=>$page_size));
    }
    public function cache_all_activity($page_no, $page_size)
    {
        $this->db->select('ActivityID');
        $this->db->from(ACTIVITY);
        $this->db->where('StatusID',2);
        $this->db->order_by('ActivityID','DESC');
        if(!empty($page_no) && !empty($page_size) && empty($activity_id))
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $result = $this->db->get();
        $q = $this->db->last_query();
                    
        if ($result->num_rows()){
            foreach ($result->result_array() as $res)
            {
                $activity_id=$res['ActivityID'];
                $activity_cache=array();
                if(CACHE_ENABLE)
                {
                    $activity_cache=$this->cache->get('activity_'.$activity_id);
                }
                if(empty($activity_cache))
                {                    
                    initiate_worker_job('activity_cache', array('ActivityID'=>$activity_id ));
                } 
            }
        }
    }

    public function get_popular_discussions($user_id,$module_id, $module_entity_id, $activity_type_id,$page_no,$page_size)
    {
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $activity_ids = array();
        $this->db->select('ActivityGUID,ActivityID, TotalLikeViewComment as TotalCount', false);
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->where('ModuleID',$module_id);
        if(isset($activity_type_id) && !empty($activity_type_id)){
            $this->db->where_in('ActivityTypeID',$activity_type_id);
        }
        if(isset($time_duration) && !empty($time_duration)){
            $this->db->where("CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', $time_duration) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        }
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('UserID', $blocked_users);
        }
        $this->db->where("TotalLikeViewComment>10",null,false);
        $this->db->where('PostTitle!=','');
        $this->db->group_by('ActivityID');
        $this->db->order_by('TotalCount', 'DESC');
        $this->db->order_by('ActivityID', 'DESC');
        $offset = $this->get_pagination_offset($page_no, $page_size);
        $this->db->limit($page_size, $offset);
        $result = $this->db->get();
        if ($result->num_rows()){
            foreach($result->result_array() as $res)
            {
                $activity_ids[] = $res['ActivityID'];
            }
        }
        if($activity_ids)
        {
            $data = $this->activity_model->getFeedActivities($user_id, $page_no, $page_size, 'ActivityIDS',0,0,2,false,false,false,0,0,array(),'',array(),'','',array(),$activity_ids);
            return $data;
        }
        else
        {
            return array();
        }
    }

    /**
     * [get_popuplar_activity Used to get most popular activity detail using ModuleEntityID and ModuleID]
     * @param  [int]    $module_id              [Module ID]
     * @param  [int]    $module_entity_guid     [Module EntityID]
     * @param  [array]  $activity_type_id       [Activity TypeID]
     * @param  [int]    $time_duration          [Time Duration (in days)]
     * @return [array]                          [array of activity details}
     */
    public function get_popuplar_activity_of_module($module_id, $module_entity_guid, $activity_type_id, $time_duration = 30){
        $activity_detail = array();
        $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
        $this->db->select('ActivityGUID,ActivityID, SUM(NoOfLikes+NoOfDislikes+NoOfComments+NoOfViews) as TotalCount', false);
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->where('ModuleID',$module_id);
        if(isset($activity_type_id) && !empty($activity_type_id)){
            $this->db->where_in('ActivityTypeID',$activity_type_id);
        }
        if(isset($time_duration) && !empty($time_duration)){
            $this->db->where("CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', $time_duration) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
        }
        $this->db->group_by('ActivityID');
        $this->db->order_by('TotalCount', 'DESC');
        $this->db->order_by('ActivityID', 'DESC');
        $this->db->limit(1);
        $result = $this->db->get();
        if ($result->num_rows()){
            $res =   $result->row_array();
            $activity_detail = $this->get_activity_details($res['ActivityID'], $activity_type_id);
        }
        return $activity_detail;
    }

    /**
     * [request_question_answer Used to request a question for a activity]
     * @param  [String]     ActivityGUID      [GUID of Post Activity]
     * @param  [int]        request_by        [UserID of who intimate request]
     * @param  [Array]      request_to        [RequestTo is an array of user to be requested for the post]
     * @param  [String]     note              [Description]     
     * @return [JSON] [JSON Object]
     */
    public function request_question_answer($activity_guid, $module_id, $request_by, $request_to, $note, $status = 'PENDING'){

        $add_request = array();
        $update_request = array();
        $requested_user = array();
        $exist = '';

        $activity_detail = get_detail_by_guid($activity_guid, $module_id,'ActivityID', 2);
        $activity_id = $activity_detail['ActivityID'];

        if (!empty($request_to))
        {
            foreach ($request_to as $key => $to_user)
            {
                $to_user                    =  get_detail_by_guid($to_user, 3,'UserID', 2);
                $to_user                    =  $to_user['UserID'];
               
                $tmp_data['ActivityID']     =  $activity_id;
                $tmp_data['Status']         =  $status;
                $tmp_data['ModifiedDate']   =  get_current_date('%Y-%m-%d %H:%i:%s');
                $tmp_data['RequestBy']      =  $request_by;
                $tmp_data['RequestTo']      =  $to_user;

                // check user entry is exist or not
                if($status == 'PENDING'){
                    $exist = $this->db->get_where(REQUESTFORANSWER, array('ActivityID' => $activity_id, 'RequestBy' => $request_by, 'RequestTo' => $to_user, 'Status' => $status))->row_array();

                    if(empty($exist)){
                        $requested_user[]           = $to_user;
                        // prepare array to insert record into database
                        $tmp_data['Note']           = $note;
                        $tmp_data['CreatedDate']    = get_current_date('%Y-%m-%d %H:%i:%s');
                        $add_request[]              = $tmp_data;
                    }
                }else{
                    $exist = $this->db->get_where(REQUESTFORANSWER, array('ActivityID' => $activity_id, 'RequestBy' => $to_user, 'RequestTo' => $request_by, 'Status' => 'PENDING'))->row_array();

                    $requested_user[]               = $to_user;
                    // prepare array to update record into database
                    if(!empty($exist)){
                        $update_request[]           = $tmp_data;
                    }else{
                        $tmp_data['Note']           = $note;
                        $tmp_data['CreatedDate']    = get_current_date('%Y-%m-%d %H:%i:%s');
                        $add_request[]              = $tmp_data;
                    }
                }
            }

            if (!empty($add_request))
            {
                $this->db->insert_batch(REQUESTFORANSWER, $add_request);              
            }

            if (!empty($update_request))
            {
                foreach ($update_request as $key => $value)
                {
                    $this->db->set('Status', $value['Status']);
                    $this->db->set('ModifiedDate', $value['ModifiedDate']);
                    $this->db->where(array('RequestBy' => $value['RequestTo'], 'RequestTo' => $value['RequestBy'], 'ActivityID' => $value['ActivityID']));
                    $this->db->update(REQUESTFORANSWER);
                }
            }

            // send notificaton
            if(!empty($requested_user)){
                $notification_type_id = 115;
                if ($status == 'ANSWERED' && $exist == '')
                {
                    $notification_type_id = 116;
                }else if ($status == 'ANSWERED' && $exist != '')
                {
                    $notification_type_id = 118;
                }

                $parameters = array();
                $parameters[0]['ReferenceID'] = $request_by;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $activity_id;
                $parameters[1]['Type'] = 'Question';
                $parameters[2]['ReferenceID'] = $activity_id;
                $parameters[2]['Type'] = 'RequestNote';

                $this->notification_model->add_notification($notification_type_id, $request_by, $requested_user, $activity_id, $parameters);
            }
            return true;
        }
    }

    /**
     * [most_appropriate_answer To mark an entity as appropriate answer]
     * @param  [array] $data [input data for appropriate answer Request]
     * @return [array]       [array of response code and message]
     */
    function most_appropriate_answer($data)
    {
        $comment_guid   = $data['CommentGUID'];
        $activity_guid  = $data['ActivityGUID'];

        $return['Message'] = lang('success');
        $return['ResponseCode'] = 200;
        /* get comment details by comment GuID */
        $select_field = "PostCommentID, EntityID, EntityType";
        $comment = get_detail_by_guid($comment_guid, 20, $select_field, 2);
        $activity_id = get_detail_by_guid($activity_guid);

        if (empty($comment))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "comment GUID");
            return $return;
        }else if($activity_id != $comment['EntityID']){
            $return['ResponseCode'] = 412;
            $return['Message'] = lang('mis_match_comment_guid');
            return $return;
        }
        /* End get EntityId */

        /* Check logged in user have permission to mark approiate answer */
        $Permission = $this->check_activity_owner($activity_id);
        if (empty($Permission))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = lang('permission_denied');
            return $return;
        }
        
        $this->db->set('StatusID',2);
        $this->db->where('StatusID',22);
        $this->db->where('EntityType','ACTIVITY');
        $this->db->where('EntityID',$activity_id);
        $this->db->update(POSTCOMMENTS);


        /* update status of comment to best answer*/
        $this->db->set('StatusID', 22);
        $this->db->where('PostCommentID', $comment['PostCommentID']);
        $this->db->update(POSTCOMMENTS);
        /* End update status code */

        $Return['ResponseCode'] = 200;
        $Return['Message'] = lang('success');
        return $Return;
    }

    /**
     * [check_activity_owner Used to Check activity validation]
     * @param [int]      $activity_id  [ActivityId]
     */
    function check_activity_owner($activity_id)
    {
        $user_id = $this->UserID;
        $this->db->select('*');
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('UserID', $user_id);
        $result = $this->db->get(ACTIVITY);
        $result = $result->row();
        if (!empty($result))
        {
            return true;
        } 
        return false;
    }

    /**
     * [check_is_appropriate_already Used to check activity is already marked as approiate or not]
     * @param [int]      $activity_id  [ActivityId]
     */
    function check_is_appropriate_already($activity_id)
    {
        $this->db->select('PostCommentID');
        $this->db->where('EntityId', $activity_id);
        $this->db->where('StatusID', 22);
        $result = $this->db->get(POSTCOMMENTS);
        $result = $result->row();
        if (!empty($result))
        {
            return true;
        } 
        return false;
    }

    /**
     * [requested_answer_user_lists          fetch requested an answer user lists for an activity ]
     * @param  [String]     activity_guid    [GUID of Post Activity]
     * @param  [int]        module_id        [ModuleId]
     * @param  [int]        page_no         [Page number]
     * @param  [int]        page_size       [Total records per page]
     * @param  [String]     Status           [Status] (Optional)
     * @return [JSON] [JSON Object]
     */
    function requested_answer_user_lists($activity_guid, $module_id, $status, $page_no = 0, $page_size = 0, $NumRows = FALSE){
        $activity_detail = get_detail_by_guid($activity_guid, $module_id,'ActivityID', 2);
        $activity_id = $activity_detail['ActivityID'];
        $user_id    = $this->UserID;

        $this->db->select('U.UserGUID,U.FirstName,U.LastName,IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture, U.UserID, PU.Url AS ProfileURL,RFA.Status,RFA.CreatedDate', false);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CN.CountryName,"") as CountryName', FALSE);
        //$this->db->select("IF(U.UserID='" . $user_id . "',2,IF(U.UserID IN('" . $friends_list . "'),1,0)) as OrderByVar", false);
        $this->db->from(REQUESTFORANSWER. ' RFA');
        $this->db->join(USERS . ' U', 'U.UserID=RFA.RequestTo', 'left');
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CN', 'CN.CountryID=UD.CountryID', 'left');
        $this->db->where('RFA.ActivityID', $activity_id);
        $this->db->where('RFA.RequestBy', $user_id);
        if (!empty($status))
        {
            $this->db->where('RFA.Status', $status);
        }
        $this->db->order_by('RFA.ModifiedDate', 'DESC');
        if (!empty($page_size))
        {
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        $query = $this->db->get();
        if ($NumRows)
        {
            return $query->num_rows();
        }
        if ($query->num_rows())
        {
            return $query->result_array();
        } else
        {
            return array();
        }
    }

    /**
     * [activity_friend_list  fetch requested an answer user lists for an activity ]
     * @param  [String]       activity_guid    [GUID of Post Activity]
     * @param  [int]          module_id        [ModuleId]
     * @param  [int]          page_no         [Page number]
     * @param  [int]          page_size       [Total records per page]
     * @param  [String]       Status           [Status] (Optional)
     * @return [JSON] [JSON Object]
     */
    function activity_friend_list($activity_guid , $page_no = 0, $page_size = 0, $NumRows = FALSE, $search_keyword = '',$ignore_list=array()){

        $exist_users        = array();
        $member_ids         = array();
        $activity_detail    = get_detail_by_guid($activity_guid , '','ActivityID, ModuleID, ModuleEntityID', 2);
        $activity_id        = $activity_detail['ActivityID'];
        $module_id          = $activity_detail['ModuleID'];
        $module_entity_id   = $activity_detail['ModuleEntityID'];
        $user_id            = $this->UserID;
        $blocked_users      = $this->block_user_list($user_id);
            
        // get requested activity users
        $exist_users[] = $user_id;
        $this->db->select('GROUP_CONCAT(RequestTo) as RequestedUsers', false);
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('RequestBy', $user_id);
        $result = $this->db->get(REQUESTFORANSWER);
        $result = $result->row();
        if (!empty($result->RequestedUsers))
        {
            $exist_users = $result->RequestedUsers;
            $exist_users = explode(",", $exist_users);
        } 
        
        $this->db->select('RequestTo,COUNT(RequestTo) as RequestCount', false);
        $this->db->where('RequestBy', $user_id);
        $this->db->group_by('RequestTo');
        $this->db->order_by('RequestCount','DESC');
        $result_requst = $this->db->get(REQUESTFORANSWER);
        //echo $this->db->last_query();die;
        $request_users=array();
        if ($result_requst->num_rows())
        {
            foreach($result_requst->result_array() as $item)
            {
                $request_users[]=$item['RequestTo'];
            }
        } 
        // get module activity users
        $user_list = array();
        switch ($module_id) 
        {
            case '1':
                $user_list = $this->group_model->get_group_userlist_for_answer($module_entity_id, array(), array(), array(), 2);
                break;
            case '34':
                $user_list = $this->forum_model->get_category_userlist_for_answer($module_entity_id);
                break;
            case '18':
                $user_list = $this->page_model->get_page_userlist_for_answer($module_entity_id, array(), 2);
                if (!empty($user_list))
                {
                    $user_list = explode(",", $user_list);
                } 
                break;
        }

        // get friend ids
        $friends_only = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 0, TRUE);

        $combine_users = array_merge($user_list, $friends_only);
        unset($user_list);
        unset($friends_only);

        // fetch remaining users ids other then requested user for an activity
        if(!empty($exist_users)){
            $member_ids = array_diff($combine_users, $exist_users); 
        }else{
            $member_ids = $combine_users;
            unset($combine_users);
        }
        $member_ids[] = 0;   

        $this->db->select('U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture as ProfilePicture, U.UserID, PU.Url AS ProfileURL', false);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CN.CountryName,"") as CountryName', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CN', 'CN.CountryID=UD.CountryID', 'left');
        $this->db->where_in('U.UserID', $member_ids);
        $this->db->where_not_in('U.StatusID', array(3,4));
        if(!empty($ignore_list))
        {
          $this->db->where_not_in('U.UserID', $ignore_list);  
        }
        if($search_keyword){
            $this->db->like("CONCAT(U.FirstName,' ',U.LastName)", $search_keyword, FALSE);
        }
        if(!empty($request_users))
        {
          $this->db->order_by("CASE WHEN U.UserID IN ('".implode(',',$request_users)."') THEN 1 ELSE 0 END DESC");  
        }else{
           $this->db->order_by("CONCAT(U.FirstName,' ',U.LastName)"); 
        }
        
        //$this->db->order_by("CONCAT(U.FirstName,' ',U.LastName)");
        if (!empty($page_size))
        {
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        $query = $this->db->get();
        $result=array();
        if ($NumRows)
        {
            return $query->num_rows();
        }
        if ($query->num_rows())
        {
            foreach($query->result_array() as $item)
            {
                $item['AnswerCount']=$this->get_answer_count($item['UserID']);
                $result[]=$item;
            }
        }
        return $result;
    }
    /**
     * [Get Answer Count]
     * @param  [string] $user_id      [user id]
     */

    function get_answer_count($user_id)
    {   
        $this->db->select('COUNT(RequestID) as AnswerCount', false);
        $this->db->where('RequestTo', $user_id);
        $this->db->order_by('Status','ANSWERED');
        $result_requst = $this->db->get(REQUESTFORANSWER);
        $request_users=array();
        if ($result_requst->num_rows())
        {
            return $result_requst->row_array()['AnswerCount'];
        } 
    }
    /**
     * [Save Activity History]
     * @param  [string] $activity_id      [Post content]
     * @param  [array] $data              [ActivityID,Media,Files,Links,Tags,UserID,ActivityData]
     */

    function add_activity_history($activity_id_previous,$data,$previously_tagged,$status,$publish_post)
    {   

        $activity_id =  $data['ActivityID'];
        // Add History Data
        $HistoryData = array(
                        'StatusID'      =>  2,
                        'CreatedDate'   =>  get_current_date('%Y-%m-%d %H:%i:%s'),
                        'ModifiedDate'  =>  get_current_date('%Y-%m-%d %H:%i:%s'),
                        'ActivityID'    =>  $data['ActivityID'],
                        'Media'         =>  $data['Media'],
                        'Files'         =>  $data['Files'],
                        'Links'         =>  $data['Links'],
                        'Tags'          =>  $data['Tags'],
                        'UserID'        =>  $data['UserID'],
                        'ActivityData'  =>  $data['ActivityData']
                    );

        $this->db->insert(ACTIVITYHISTORY,$HistoryData);
        $HistoryID = $this->db->insert_id();

        $user_id = $data['UserID'];

        $activity_data = json_decode($data['ActivityData'],true);
        $PostContent = $activity_data['PostContent'];

        /* Send Notification to previously tagged users*/
        
     
        $mentions = array();
        $tagged_in_post = array();
        if($status==2)
        {
            preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $PostContent, $matches);
            
            $mentions_page = array();
            if (!empty($matches[1]))
            {
                foreach ($matches[1] as $match)
                {
                    $this->update_row(MENTION,array('MentionID'=>$match),array('StatusID'=>2));
                    $mention = $this->get_mention($match);
                    if ($mention['ModuleID'] == 3)
                    {   
                        if(!empty($previously_tagged) && in_array($mention['ModuleEntityID'], $previously_tagged)) 
                        $mentions[] = $mention['ModuleEntityID'];
                        else
                        $tagged_in_post[] = $mention['ModuleEntityID'];
                    }
                }
            }

            if (isset($mentions) && !empty($mentions))
            {
                //Send Notifications
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';

                $this->notification_model->add_notification(114, $user_id, $mentions,$activity_id, $parameters);
            }
                
            /*Update Tag Status*/
            if($data['Tags'])
            {
                $tags_data = json_decode($data['Tags'],TRUE);
                
                if(!empty($tags_data))
                {
                    foreach ($tags_data as $tag) {
                        $this->db->where(array('EntityType'=>'ACTIVITY','EntityID'=>$data['ActivityID'],'TagID'=>$tag['TagID'])); 
                        $this->db->update(ENTITYTAGS,array('StatusID'=>2));
                    }
                }
            }

            /*Update Links Status*/
            if($data['Links'])
            {
                $link_data = json_decode($data['Links'],TRUE);
                
                if(!empty($link_data))
                {
                    foreach ($link_data as $link) {
                        
                        $this->update_row(ACTIVITYLINKS,array('ActivityLinkID'=>$link['ActivityLinkID']),array('StatusID'=>2));
                    }
                }
            }

            if($activity_id_previous && !$publish_post)
            {
                /* Send Notification to subscribers*/
                $NotificationUserList = $this->notification_model->get_notification_user_list($activity_id, $data['UserID']);
                $owner_ids=array();
                if($activity_data['ModuleID']==1)
                {
                    $owner_ids = $this->group_model->get_all_group_admins($activity_data['ModuleEntityID']);
                }
                $NotificationUserList=array_diff($NotificationUserList,$owner_ids);
                if(!empty($NotificationUserList))
                {      
                    foreach ($NotificationUserList as $key=>$notify_user) {
                        
                        if (!empty($mentions) && in_array($notify_user, $mentions))
                        {
                            unset($NotificationUserList[$key]);
                        }

                        if (!empty($tagged_in_post) && in_array($notify_user, $tagged_in_post))
                        {
                            unset($NotificationUserList[$key]);
                        }
                    }

                    /* if (isset($NotificationUserList) && !empty($NotificationUserList))
                    {
                        //Send Notifications
                        $parameters[0]['ReferenceID'] = $user_id;
                        $parameters[0]['Type'] = 'User';

                        $this->notification_model->add_notification(119, $user_id, $NotificationUserList,$activity_id, $parameters);
                    }
                     * 
                     */

                    if(!empty($mentions))
                    $mentions = array_unique(array_merge($mentions, $NotificationUserList));
                }
            }
        }

        return $mentions;

    }

    /* To set current activited activity version status 21 */   

    function update_activity_history($activity_id)
    {
        $history_detail = $this->active_activity_history($activity_id);

        if($history_detail)
        {
            /* Update Media Status*/
            if($history_detail['Media'])
            {
                $media_data = json_decode($history_detail['Media'],TRUE);
                if(!empty($media_data))
                {
                    $media_data=$media_data[0];
                }
                
                if(!empty($media_data['Media']))
                {
                    foreach ($media_data['Media'] as $media) {
                        $this->db->where('MediaGUID',$media['MediaGUID']); 
                        $this->db->update(MEDIA,array('StatusID'=>21));
                    }
                }
            }

            /* Update Files Status*/
            if($history_detail['Files'])
            {
                $files_data = json_decode($history_detail['Files'],TRUE);


                if(!empty($files_data))
                {
                    foreach ($files_data as $media) {
                        $this->db->where('MediaGUID',$media['MediaGUID']); 
                        $this->db->update(MEDIA,array('StatusID'=>21));
                    }
                }
            }

            /*Update Tag Status*/
            if($history_detail['Tags'])
            {
                $tags_data = json_decode($history_detail['Tags'],TRUE);
                
                if(!empty($tags_data))
                {
                    foreach ($tags_data as $tag) {
                        $this->db->where(array('EntityType'=>'ACTIVITY','EntityID'=>$activity_id,'TagID'=>$tag['TagID'])); 
                        $this->db->update(ENTITYTAGS,array('StatusID'=>21));
                    }
                }
            }

            /*Update Links Status*/
            if($history_detail['Links'])
            {
                $link_data = json_decode($history_detail['Links'],TRUE);
                
                if(!empty($link_data))
                {
                    foreach ($link_data as $link) {
                        
                        $this->update_row(ACTIVITYLINKS,array('ActivityLinkID'=>$link['ActivityLinkID']),array('StatusID'=>21));
                    }
                }
            }

            // Update Activity history status
            $this->update_row(ACTIVITYHISTORY,array('HistoryID'=>$history_detail['HistoryID'],'ActivityID'=>$activity_id),array('StatusID'=>21));

            /* Update Mnetion status to 21 if exists*/

            $activity_data = json_decode($history_detail['ActivityData'],true);
            $PostContent = $activity_data['PostContent'];
            $tagged_users = array();

            preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $PostContent, $matches);
            $mentions = array();
            $mentions_page = array();
            if (!empty($matches[1]))
            {
                foreach ($matches[1] as $match)
                {
                    $this->update_row(MENTION,array('MentionID'=>$match),array('StatusID'=>21));
                }

                $mention = $this->get_mention($match);
                if ($mention['ModuleID'] == 3)
                {
                    $tagged_users[] = $mention['ModuleEntityID'];
                }
            }

            $this->db->set('IsEdited','1');
            $this->db->where('ActivityID',$activity_id);
            $this->db->update(ACTIVITY);

            return $tagged_users;
        }
    }

     /* Get active Activity History data by Activity ID*/
    function active_activity_history($activity_id)
    {
        $this->db->select('*');
        $this->db->from(ACTIVITYHISTORY);
        $this->db->where('StatusID',2);
        $this->db->where('ActivityID',$activity_id);
        $Query = $this->db->get();

        if($Query->num_rows()>0)
        {
            return $Query->row_array();
        }
        else
        {
            return FALSE;
        }
    }

      /**
     * [to set_activity_history_active by history id]
     * @param  [int] $activity_id    
     * @param  [int] $history_id             
     */

    function change_activity_version($activity_guid,$history_id,$user_id)
    {   
        $result = $this->db->get_where(ACTIVITY, array('ActivityGUID' => $activity_guid));
        
        if ($result->num_rows())
        {
            $row = $result->row();

            $activity_id = $row->ActivityID;

            if (checkPermission($user_id, $row->ModuleID, $row->ModuleEntityID, 'IsOwner', 3, $user_id) || checkPermission($user_id, 3, $row->UserID))
            {
                $this->db->trans_start();

                // set current history status to 21
                $this->update_activity_history($activity_id);

                $history_detail = $this->get_activity_by_history_id($history_id);

                if($history_detail)
                {
                    /* Update Media Status*/
                    if($history_detail['Media'])
                    {
                        $media_data = json_decode($history_detail['Media'],TRUE);
                        if(!empty($media_data))
                        {
                            $media_data=$media_data[0];
                        }
                        if(!empty($media_data['Media']))
                        {
                            foreach ($media_data['Media'] as $media) {
                                $this->db->where('MediaGUID',$media['MediaGUID']); 
                                $this->db->update(MEDIA,array('StatusID'=>2));
                            }
                        }
                    }

                    /* Update Files Status*/
                    if($history_detail['Files'])
                    {
                        $files_data = json_decode($history_detail['Files'],TRUE);
                        
                        if(!empty($files_data))
                        {
                            foreach ($files_data as $media) {
                                $this->db->where('MediaGUID',$media['MediaGUID']); 
                                $this->db->update(MEDIA,array('StatusID'=>2));
                            }
                        }
                    }

                    /*Update Tag Status*/
                    if($history_detail['Tags'])
                    {
                        $tags_data = json_decode($history_detail['Tags'],TRUE);
                        
                        if(!empty($tags_data))
                        {
                            foreach ($tags_data as $tag) {
                                $this->db->where(array('EntityType'=>'ACTIVITY','EntityID'=>$activity_id,'TagID'=>$tag['TagID'])); 
                                $this->db->update(ENTITYTAGS,array('StatusID'=>2));
                            }
                        }
                    }


                    /*Update Links Status*/
                    if($history_detail['Links'])
                    {
                        $link_data = json_decode($history_detail['Links'],TRUE);
                        
                        if(!empty($link_data))
                        {
                            foreach ($link_data as $link) {
                                
                                $this->update_row(ACTIVITYLINKS,array('ActivityLinkID'=>$link['ActivityLinkID']),array('StatusID'=>2));
                            }
                        }
                    }

                    

                    // Update Activity history status to 2
                    $this->update_row(ACTIVITYHISTORY,array('HistoryID'=>$history_detail['HistoryID'],'ActivityID'=>$activity_id),array('StatusID'=>2));

                    $activity_data = json_decode($history_detail['ActivityData'],true);
                    $PostContent = $activity_data['PostContent'];

                    $updated_post_data = array('PostTitle' =>$activity_data['PostTitle'],'PostContent'=>$PostContent,'PostType'=>$activity_data['PostType']);

                    $this->update_row(ACTIVITY,array('ActivityID'=>$activity_id),$updated_post_data);

                    $this->db->trans_complete();

                    /* Send Notification to previously tagged users*/
                    
                    preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $PostContent, $matches);
                    $mentions = array();
                    $mentions_page = array();
                    if (!empty($matches[1]))
                    {

                        foreach ($matches[1] as $match)
                        {
                            $this->update_row(MENTION,array('MentionID'=>$match),array('StatusID'=>2));

                            $mention = $this->get_mention($match);
                            if ($mention['ModuleID'] == 3)
                            {
                                $mentions[] = $mention['ModuleEntityID'];
                            }
                        }
                    }

                    if (isset($mentions) && !empty($mentions))
                    {
                        //Send Notifications
                        $parameters[0]['ReferenceID'] = $user_id;
                        $parameters[0]['Type'] = 'User';

                        $this->notification_model->add_notification(114, $user_id, $mentions,$activity_id, $parameters);
                    }

                    /* Send Notification to subscribers*/
                    $NotificationUserList = $this->notification_model->get_notification_user_list($activity_id, $user_id);

                    if(!empty($NotificationUserList))
                    {
                        foreach ($NotificationUserList as $key=>$notify_user) {
                            
                            if (in_array($notify_user, $mentions))
                            {
                                unset($NotificationUserList[$key]);
                            }
                        }

                        /* if (isset($NotificationUserList) && !empty($NotificationUserList))
                        {
                            //Send Notifications
                            $parameters[0]['ReferenceID'] = $user_id;
                            $parameters[0]['Type'] = 'User';

                            $this->notification_model->add_notification(119, $user_id, $NotificationUserList,$activity_id, $parameters);
                        }
                         * 
                         */
                    }
                    /* END Notification*/

                    $Return['ResponseCode'] = 200;
                    $Return['Message'] = lang('success');
                    return $Return;
                
                }
                else
                {
                    $Return['ResponseCode'] = 412;
                    $Return['Message'] = lang('no_record');
                    return $Return;
                }
               
            }
            else
            {
                $Return['ResponseCode'] = 412;
                $Return['Message'] = lang('permission_denied');
                return $Return;
            }

        }
        else
        {

            $Return['ResponseCode'] = 412;
            $Return['Message'] = lang('no_record');
            return $Return;
        }
    }
    

    /* Get Activity History data by history ID*/
    function get_activity_by_history_id($history_id)
    {
        $this->db->select('*');
        $this->db->from(ACTIVITYHISTORY);
        $this->db->where('HistoryID',$history_id);
        $Query = $this->db->get();

        if($Query->num_rows()>0)
        {
            return $Query->row_array();
        }
        else
        {
            return FALSE;
        }
    }
        
    /**
     * [update_comment_history To set current activited comment version status 21]
     * @param  [int] $comment_id [Comment ID]
     * @return [type]              [description]
     */
    function update_comment_history($comment_id)
    {
        $history_detail = $this->active_comment_history($comment_id);

        if($history_detail)
        {
            /* Update Media Status*/
            if($history_detail['Media'])
            {
                $media_data = json_decode($history_detail['Media'],TRUE);
                if(!empty($media_data))
                {
                    foreach ($media_data as $media) {
                        $this->db->where('MediaGUID',$media['MediaGUID']); 
                        $this->db->update(MEDIA,array('StatusID'=>21));
                    }
                }
            }
            

            // Update Comment history status
            $this->update_row(COMMENTHISTORY,array('HistoryID'=>$history_detail['HistoryID'],'CommentID'=>$comment_id),array('StatusID'=>21));

            /* Update Mnetion status to 21 if exists*/

            $comment_data = json_decode($history_detail['CommentData'],true);
            $PostContent = $comment_data['PostComments'];


            $tagged_users = array();

            preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $PostContent, $matches);
            $mentions = array();
            $mentions_page = array();
            if (!empty($matches[1]))
            {
                foreach ($matches[1] as $match)
                {
                    $this->update_row(MENTION,array('MentionID'=>$match),array('StatusID'=>21));
                
                    $mention = $this->get_mention($match);
                    if ($mention['ModuleID'] == 3)
                    {
                        $tagged_users[] = $mention['ModuleEntityID'];
                    }
                }
            }
            return $tagged_users;
        }
    }

    /**
     * [active_comment_history Get active Comment History data]
     * @param  [int] $comment_id [Comment ID]
     * @return [type]              [description]
     */
    function active_comment_history($comment_id)
    {
        $this->db->select('*');
        $this->db->from(COMMENTHISTORY);
        $this->db->where('StatusID',2);
        $this->db->where('CommentID',$comment_id);
        $Query = $this->db->get();

        if($Query->num_rows()>0)
        {
            return $Query->row_array();
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * [add_comment_history description]
     * @param [int] $comment_id_previous [Comment ID]
     * @param [array] $data                 [CommentID,Media,Files,UserID,CommentData]
     * @param [array] $previously_tagged    [description]
     * @param [int] $status               [description]
     */
    function add_comment_history($comment_id_previous,$data,$previously_tagged,$status)
    {   
        $comment_id =  $data['CommentID'];
        // Add History Data
        $HistoryData = array(
                        'StatusID'      =>  2,
                        'CreatedDate'   =>  get_current_date('%Y-%m-%d %H:%i:%s'),
                        'ModifiedDate'  =>  get_current_date('%Y-%m-%d %H:%i:%s'),
                        'CommentID'     =>  $data['CommentID'],
                        'Media'         =>  $data['Media'],
                        'UserID'        =>  $data['UserID'],
                        'CommentData'   =>  $data['CommentData']
                    );

        $this->db->insert(COMMENTHISTORY,$HistoryData);
        $HistoryID = $this->db->insert_id();

        $user_id = $data['UserID'];

        $comment_data = json_decode($data['CommentData'],true);
        $PostContent  = $comment_data['PostComments'];

        /* Send Notification to previously tagged users*/
        $mentions = array();
        $tagged_in_post = array();
        if($status==2)
        {
            preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $PostContent, $matches);
            
            $mentions_page = array();
            if (!empty($matches[1]))
            {
                foreach ($matches[1] as $match)
                {
                    $this->update_row(MENTION,array('MentionID'=>$match),array('StatusID'=>2));
                    $mention = $this->get_mention($match);
                    if ($mention['ModuleID'] == 3)
                    {   
                        if(!in_array($mention['ModuleEntityID'], $previously_tagged)) 
                            $tagged_in_post[] = $mention['ModuleEntityID']; // New tagged user ids
                    }
                }
            }
            if (!empty($previously_tagged))
            {
                //Send Notifications
                $parameters[0]['ReferenceID'] = $user_id;
                $parameters[0]['Type'] = 'User';

                $this->notification_model->add_notification(120, $user_id, $previously_tagged,$comment_id, $parameters);
            }
        }
        return $tagged_in_post; // New tagged user ids

    }
    
    /**
     * [get_request_detail Get last request detail of an activity]
     * @param  [int] $activity_id [Activity ID]
     * @param  [int] $user_id     [User ID]
     * @return [array]            [Comment details]
     */
    public function get_request_detail($activity_id, $user_id)
    {
        $this->db->select('*');
        $this->db->from(REQUESTFORANSWER);
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('RequestBy', $user_id);
        $this->db->order_by('RequestID', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->row_array();
        }
    }


    function get_activity_versions($activity_guid,$history_id,$user_id)
    {   
        $activity_id = get_detail_by_guid($activity_guid);

        $this->db->select('AH.*,U.FirstName,U.LastName');
        $this->db->from(ACTIVITYHISTORY.' AH');
        $this->db->join(USERS .' U','U.UserID = AH.UserID');
        $this->db->where('AH.ActivityID',$activity_id);
        $this->db->order_by('AH.HistoryID','DESC');
        $this->db->where('AH.StatusID',21);
        
        if(!empty($history_id))
        $this->db->where('HistoryID',$history_id);

        $Query = $this->db->get();

        $history = array();
        if($Query->num_rows()>0)
        {
            $history_data = $Query->result_array();
            
            foreach ($history_data as $key => $value) {
                
                $data['HistoryID']   = $value['HistoryID'];
                $post_data = json_decode($value['ActivityData'],true); 

                $post_content = $post_data['PostContent'];
                $data['UpdatedBy']       = $value['FirstName'].' '.$value['LastName'];
                $data['PostContent']    = $this->parse_tag($post_data['PostContent'],$activity_id);
                $data['PostTitle']      = $this->parse_tag($post_data['PostTitle'],$activity_id); 
                $data['PostType']       = $post_data['PostType'];
                
                $data['Links']          = ($value['Links']!="")? json_decode($value['Links'],TRUE):"";
                $data['EntityTags']     = ($value['Tags']!="")?json_decode($value['Tags'],TRUE):"";
                $data['Album']          = ($value['Media']!="")?json_decode($value['Media'],TRUE):"";
                $data['Files']          = ($value['Files']!="")?json_decode($value['Files'],TRUE):"";

                $data['StatusID']       = $value['StatusID'];
                $data['CreatedDate']    = $value['CreatedDate'];
                $data['ModifiedDate']   = $value['ModifiedDate'];


                $history[] = $data;
            }

        }

        return $history;
    }


    function get_entity_viewd($ActivityID, $ActivityTypeID)
    {
        if($ActivityTypeID == '5' || $ActivityTypeID == '6' || $ActivityTypeID 
            == '13')
        {
             $this->db->select('AlbumID');
             $this->db->where('ActivityID',$ActivityID);
             $this->db->limit(1);
             $qry = $this->db->get(ALBUMS);

             if($qry->num_rows()){
                 $EntityID = $qry->row()->AlbumID;
                 $EntityType = 'Album';
             }
        }
        else
        {
            $EntityID = $ActivityID;
            $EntityType = 'Activity';
        }

        $this->db->select('SUM(1) AS EntityView');
        $this->db->from("EntityView");
        $this->db->where('EntityID',$EntityID);
        $this->db->where('EntityType',$EntityType);
        $query = $this->db->get();
        //echo $this->db->last_query();;
        return $query->row()->EntityView;
    }   

    /**
     * [similar_forum_discussion ]
     * @param  [int]       $entity_id      [Module Entity ID]
     * @param  [int]       $module_id      1 Always
     * @param  [int]       $page_no        [Page No]
     * @param  [int]       $page_size      [Page Size]
     * @param  [int]       $current_user   [Current User ID]
     * @param  [int]       $count_only     [TRUE | FALSE]
     * @return [Array]                    [Activity array]
     */
    public function similar_forum_discussion($entity_id, $module_id, $page_no, $page_size, $current_user,$count_only = false, $m_entity_id = '', $entity_module_id = '',$post_type,$activity_guid)
    {
        $e_module_id = $module_id;
        $e_entity_id = $entity_id;
        $this->load->model('forum/forum_model');
        $categories = $this->forum_model->get_all_similar_categories($entity_id);
        $blocked_users=$this->block_user_list($current_user, 3);
        
        $activity_data = get_detail_by_guid($activity_guid,0,'ActivityID,PostType',2);
        $post_type='';
        $tag_ids=array();
        if(!empty($activity_data))
        {
            $activity_id    = $activity_data['ActivityID'];  
            $post_type      = $activity_data['PostType'];  
            $entity_tags    = $this->tag_model->get_entity_tags('', 1, '', 1, 'ACTIVITY', $activity_id, $current_user);
            foreach($entity_tags as $entity_tag)
            {
                $tag_ids[]=$entity_tag['TagID'];
            }
        }
            
            
        $time_zone = $this->user_model->get_user_time_zone();
        $this->load->model(array('polls/polls_model','category/category_model'));

        $activity_type_allow = array(26);
        
        $this->db->select('A.*,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
    
        //$this->db->where("IF(A.UserID='" . $current_user . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        
        $this->db->where('A.StatusID',2);     
        $this->db->where('A.ActivityGUID != ',$activity_guid);
        if(!$this->settings_model->isDisabled(43)) {
            $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $current_user . "')", NULL, FALSE);
        }
        
        
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }

        $this->db->where('ATY.StatusID', '2');
        
        if(!empty($categories))
        {
            $mention_condition = " (A.ModuleID='" . $module_id . "' AND A.ModuleEntityID in (".implode(',',$categories).") )";

            $this->db->where($mention_condition, NULL, FALSE);
        }
        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        $this->db->where_not_in('U.StatusID', array(3, 4));
        if(!empty($tag_ids))
        {
            $this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID=A.ActivityID', 'left');
            $this->db->join(TAGS . ' TG', 'TG.TagID=ET.TagID', 'left');
            $this->db->where_in('TG.TagID',$tag_ids);
        }
        if(!empty($post_type))
        {
            $this->db->where('A.PostType',$post_type);
        }
        $this->db->order_by(" CASE WHEN (A.ActivityID IN (SELECT M.ActivityID from ".MENTION." M where M.Type =1 AND M.StatusID = 2 AND M.ActivityID = A.ActivityID)) THEN 1 ELSE 0 END DESC,A.ModifiedDate DESC");

        //$this->db->order_by('A.ModifiedDate', 'DESC');
        $this->db->group_by('A.ActivityGUID');
        if (!$count_only && !empty($page_size))
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $result = $this->db->get();//echo $this->db->last_query();die;
        if ($count_only)
        {
            return $result->num_rows();
        }
        
        $return = array();

        if ($result->num_rows())
        {
            foreach ($result->result_array() as $res)
            {
                $activity = array();
                $activity_id = $res['ActivityID'];
                $activity_guid = $res['ActivityGUID'];
                $module_id = $res['ModuleID'];
                $activity_type_id = $res['ActivityTypeID'];
                $module_entity_id = $res['ModuleEntityID'];
     
                $activity['PostTitle'] = $res['PostTitle'];
                $activity['ActivityTypeID'] = $res['ActivityTypeID'];
                $activity['PostAsModuleID'] = $res['PostAsModuleID'];
                $activity['PostType'] = $res['PostType'];

                $activity['ActivityGUID'] = $activity_guid;
                $activity['ModuleID'] = $module_id;
               
                $activity['PollData'] = array();
                
                $activity['NoOfComments']   = $this->get_activity_comment_count('Activity', $activity_id, $blocked_users);
                //$activity['NoOfLikes']      = $this->get_like_count($activity_id, "ACTIVITY", $blocked_users);
                //$activity['NoOfDislikes']   = $this->get_like_count($activity_id, "ACTIVITY", $blocked_users, 3);
                
                $activity['CreatedDate'] = $res['CreatedDate'];
                $activity['ModifiedDate'] = $res['LastActionDate'];

             
                $activity['PostContent'] = $res['PostContent'];
                
                $activity['UserName'] = $res['FirstName'] . ' ' . $res['LastName'];
                
                if ($res['ActivityTypeID'] == 25)
                {
                    $params = json_decode($res['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $m_entity_id);
                    $activity['MuteAllowed'] = 0;
                    $activity['ShowFlagBTN'] = 0;
                }

                if (isset($originalActivity['ParentActivityTypeID']) && $originalActivity['ParentActivityTypeID'] == 25)
                {
                    $params = json_decode($originalActivity['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $m_entity_id);
                }
                $temp_post_content=$activity['PostContent'];
                $activity['PostContent'] = $this->parse_tag($activity['PostContent']);
                $activity['PostTitle'] = $activity['PostTitle'];
                
                $activity['PostContent'] = trim(str_replace('&nbsp;', ' ', $activity['PostContent']));
                $activity['PostTitle'] = trim(str_replace('&nbsp;', ' ', $activity['PostTitle']));
                if($activity['PostTitle']=='')
                {
                    $activity['PostTitle']= get_activity_title('',$temp_post_content);
                }
                //$activity['ActivityLink'] = get_short_link(site_url() . 'activity/' . $activity['ActivityGUID']);
                $activity['ActivityLink'] = get_single_activity_url($activity_id);
               /* Get Album */
                $activity['Album'] = array();

                if (in_array($activity_type_id, array(23,24)))
                {
                    $params = json_decode($res['Params'], true);
                    if ($params['MediaGUID'])
                    {
                        $res_entity_id = get_detail_by_guid($params['MediaGUID'], 21);
                        if ($res_entity_id)
                        {
                            $res_entity_type = 'Media';
                            $activity['Album'] = $this->get_albums($res_entity_id, $res['UserID'], '', $res_entity_type, 1); 
                        }
                    }
                } 
                else
                {
                    if(!in_array($activity_type_id,array(5,6,9,10,14,15)))
                    {
                        $activity['Album'] = $this->get_albums($activity_id, $res['UserID']);
                    }

                }

                if ($activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 14 || $activity_type_id == 15)
                {
                    $originalActivity = $this->get_activity_details($res['ParentActivityID'], $activity_type_id);
                    $activity['Album'] = $originalActivity['Album'];
                }

                if ($res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15')
                {
                    $activity['Album'] = $this->get_albums($activity['ParentActivityID'], $res['UserID'], '', 'Media');
                }

                if ($activity_type_id == 5 || $activity_type_id == 6 || $activity_type_id == 10 || $activity_type_id == 9)
                {
                    $album_flag = TRUE;
                    if ($activity_type_id == 10 || $activity_type_id == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($activity['ParentActivityID'], '', 'ActivityTypeID, Params', 2);
                        if (!empty($parent_activity_detail))
                        {
                            if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6)))
                            {
                                if (!empty($parent_activity_detail['Params']))
                                {
                                    $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                    if (!empty($album_detail['AlbumGUID']))
                                    {
                                        @$activity['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                        $album_flag = TRUE;
                                    }
                                }
                            }
                        }
                    }
                    if ($album_flag)
                    {
                        $count = 4;
                        if ($activity_type_id == 6)
                        {
                            $count = $activity['Params']->count;
                        }
                        $activity['Album'] = $this->get_albums($activity_id, $res['UserID'], $activity['Params']->AlbumGUID, 'Activity', $count);
                    }
                }
                if($res['IsFileExists'])
                {
                    $activity['Files'] = $this->get_activity_files($activity_id);
                }
                $return[] = $activity;
            }
        }
        //echo strlen(json_encode($return));
        return $return; 
    }

    /**
     * [similar_discussion_posts ]
     * @param  [int]       $entity_id      [Module Entity ID]
     * @param  [int]       $module_id      1 Always
     * @param  [int]       $page_no        [Page No]
     * @param  [int]       $page_size      [Page Size]
     * @param  [int]       $current_user   [Current User ID]
     * @param  [int]       $count_only     [TRUE | FALSE]
     * @return [Array]                    [Activity array]
     */
    public function similar_discussion_posts($entity_id, $module_id, $page_no, $page_size, $current_user,$count_only = false, $m_entity_id = '', $entity_module_id = '',$post_type,$activity_guid)
    {
        $e_module_id = $module_id;
        $e_entity_id = $entity_id;
        
        $group_categories = $this->group_model->get_group_categories($entity_id);
        $blocked_users=$this->block_user_list($current_user, 3);
        $categories = [];

        if(!empty($group_categories))
        {
          if(!empty($group_categories['SubCategory'])) 
          {
              $categories[]=$group_categories['SubCategory']['CategoryID'];
          }
          else
          {
              $categories = $this->group_model->get_subcategories($group_categories['CategoryID']);
          }
        }
        
        $activity_data = get_detail_by_guid($activity_guid,0,'ActivityID,PostType',2);
        $post_type='';
        $tag_ids=array();
        if(!empty($activity_data))
        {
            $activity_id    = $activity_data['ActivityID'];  
            $post_type      = $activity_data['PostType'];  
            $entity_tags    = $this->tag_model->get_entity_tags('', 1, '', 1, 'ACTIVITY', $activity_id,$current_user);
            foreach($entity_tags as $entity_tag)
            {
                $tag_ids[]=$entity_tag['TagID'];
            }
        }
            
        $group_ids = $this->group_model->get_groups_by_categories($categories,$current_user);
            
        $time_zone = $this->user_model->get_user_time_zone();
        $this->load->model(array('polls/polls_model','category/category_model'));

        $activity_type_allow = array(7,25);
        
        $this->db->select('A.*,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture');
        
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
    
        $this->db->where("IF(A.UserID='" . $current_user . "',A.StatusID IN(1,2),A.StatusID=2)", null, false);
        
        $this->db->where('A.PostType',$post_type);
        $this->db->where('A.ActivityGUID != ',$activity_guid);
        if(!$this->settings_model->isDisabled(43)) {
            $this->db->where("A.ActivityID NOT IN (SELECT ActivityID FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND UserID='" . $current_user . "')", NULL, FALSE);
        }
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }

        $this->db->where('ATY.StatusID', '2');
        
        if(!empty($group_ids))
        {
            $mention_condition = " (A.ModuleID='" . $module_id . "' AND A.ModuleEntityID in (".$group_ids.") )";

            $this->db->where($mention_condition, NULL, FALSE);
        }
        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        $this->db->where_not_in('U.StatusID', array(3, 4));
        if(!empty($tag_ids))
        {
            $this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID=A.ActivityID', 'left');
            $this->db->join(TAGS . ' TG', 'TG.TagID=ET.TagID', 'left');
            $this->db->where_in('TG.TagID',$tag_ids);
        }
        if(!empty($post_type))
        {
            $this->db->where('A.PostType',$post_type);
        }
        $this->db->order_by(" CASE WHEN (A.ActivityID IN (SELECT M.ActivityID from ".MENTION." M where M.Type =1 AND M.StatusID = 2 AND M.ActivityID = A.ActivityID)) THEN 1 ELSE 0 END DESC,A.ModifiedDate DESC");

        //$this->db->order_by('A.ModifiedDate', 'DESC');
        $this->db->group_by('A.ActivityGUID');
        if (!$count_only && !empty($page_size))
        {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $result = $this->db->get();//echo $this->db->last_query();die;
        if ($count_only)
        {
            return $result->num_rows();
        }
        
        $return = array();

        if ($result->num_rows())
        {
            foreach ($result->result_array() as $res)
            {
                $activity = array();
                $activity_id = $res['ActivityID'];
                $activity_guid = $res['ActivityGUID'];
                $module_id = $res['ModuleID'];
                $activity_type_id = $res['ActivityTypeID'];
                $module_entity_id = $res['ModuleEntityID'];
     
                $activity['PostTitle'] = $res['PostTitle'];
                $activity['ActivityTypeID'] = $res['ActivityTypeID'];
                $activity['PostAsModuleID'] = $res['PostAsModuleID'];
                $activity['PostType'] = $res['PostType'];

                $activity['ActivityGUID'] = $activity_guid;
                $activity['ModuleID'] = $module_id;
               
                $activity['PollData'] = array();
                
                $activity['NoOfComments']   = $this->get_activity_comment_count('Activity', $activity_id, $blocked_users);
                //$activity['NoOfLikes']      = $this->get_like_count($activity_id, "ACTIVITY", $blocked_users);
                //$activity['NoOfDislikes']   = $this->get_like_count($activity_id, "ACTIVITY", $blocked_users, 3);
                
                $activity['CreatedDate'] = $res['CreatedDate'];
                $activity['ModifiedDate'] = $res['LastActionDate'];

             
                $activity['PostContent'] = $res['PostContent'];
                
                $activity['UserName'] = $res['FirstName'] . ' ' . $res['LastName'];
                
                if ($res['ActivityTypeID'] == 25)
                {
                    $params = json_decode($res['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $m_entity_id);
                    $activity['MuteAllowed'] = 0;
                    $activity['ShowFlagBTN'] = 0;
                }

                if (isset($originalActivity['ParentActivityTypeID']) && $originalActivity['ParentActivityTypeID'] == 25)
                {
                    $params = json_decode($originalActivity['Params']);
                    $activity['PollData'] = $this->polls_model->get_poll_by_id($params->PollID, $entity_module_id, $m_entity_id);
                }
                $temp_post_content=$activity['PostContent'];
                $activity['PostContent'] = $this->parse_tag($activity['PostContent']);
                $activity['PostTitle'] = $activity['PostTitle'];
                
                $activity['PostContent'] = trim(str_replace('&nbsp;', ' ', $activity['PostContent']));
                $activity['PostTitle'] = trim(str_replace('&nbsp;', ' ', $activity['PostTitle']));
                if($activity['PostTitle']=='')
                {
                    $activity['PostTitle']= get_activity_title('',$temp_post_content);
                }
                //$activity['ActivityLink'] = get_short_link(site_url() . 'activity/' . $activity['ActivityGUID']);
                $activity['ActivityLink'] = get_single_activity_url($activity_id);
               /* Get Album */
                $activity['Album'] = array();

                if (in_array($activity_type_id, array(23,24)))
                {
                    $params = json_decode($res['Params'], true);
                    if ($params['MediaGUID'])
                    {
                        $res_entity_id = get_detail_by_guid($params['MediaGUID'], 21);
                        if ($res_entity_id)
                        {
                            $res_entity_type = 'Media';
                            $activity['Album'] = $this->get_albums($res_entity_id, $res['UserID'], '', $res_entity_type, 1); 
                        }
                    }
                } 
                else
                {
                    if(!in_array($activity_type_id,array(5,6,9,10,14,15)))
                    {
                        $activity['Album'] = $this->get_albums($activity_id, $res['UserID']);
                    }

                }

                if ($activity_type_id == 9 || $activity_type_id == 10 || $activity_type_id == 14 || $activity_type_id == 15)
                {
                    $originalActivity = $this->get_activity_details($res['ParentActivityID'], $activity_type_id);
                    $activity['Album'] = $originalActivity['Album'];
                }

                if ($res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15')
                {
                    $activity['Album'] = $this->get_albums($activity['ParentActivityID'], $res['UserID'], '', 'Media');
                }

                if ($activity_type_id == 5 || $activity_type_id == 6 || $activity_type_id == 10 || $activity_type_id == 9)
                {
                    $album_flag = TRUE;
                    if ($activity_type_id == 10 || $activity_type_id == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($activity['ParentActivityID'], '', 'ActivityTypeID, Params', 2);
                        if (!empty($parent_activity_detail))
                        {
                            if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6)))
                            {
                                if (!empty($parent_activity_detail['Params']))
                                {
                                    $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                    if (!empty($album_detail['AlbumGUID']))
                                    {
                                        @$activity['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                        $album_flag = TRUE;
                                    }
                                }
                            }
                        }
                    }
                    if ($album_flag)
                    {
                        $count = 4;
                        if ($activity_type_id == 6)
                        {
                            $count = $activity['Params']->count;
                        }
                        $activity['Album'] = $this->get_albums($activity_id, $res['UserID'], $activity['Params']->AlbumGUID, 'Activity', $count);
                    }
                }
                if($res['IsFileExists'])
                {
                    $activity['Files'] = $this->get_activity_files($activity_id);
                }
                $return[] = $activity;
            }
        }
        //echo strlen(json_encode($return));
        return $return;
    }
    
    public function hide_announcement($user_id,$entity_id,$remove_for_all)
    {
        if($remove_for_all)
        {
            $this->db->set('IsVisible','2');
            $this->db->where('ActivityID',$entity_id);
            $this->db->update(ACTIVITY);
        }
        else
        {
            $this->db->select('AnnouncementVisibilitySettingID');
            $this->db->from(ANNOUNCEMENTVISIBILITYSETTINGS);
            $this->db->where('UserID',$user_id);
            $this->db->where('ActivityID',$entity_id);
            $query = $this->db->get();

            if($query->num_rows()<=0)
            {
               $this->db->insert(ANNOUNCEMENTVISIBILITYSETTINGS,array('UserID'=>$user_id,'ActivityID'=>$entity_id,'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s')));
            }
        }
    }

    /**
     * [get_popular_tags]
     * @param  [int]       $module_id
     * @param  [int]       $module_entity_id
     * @param  [int]       $page_no
     * @param  [int]       $page_size
     * @return [Array]
     */
    public function get_popular_tags($module_id,$module_entity_id,$page_no,$page_size)
    {
        $this->db->select('T.Name,ET.TagID');
        $this->db->select('COUNT(ET.TagID) as TagCount',false);
        $this->db->from(ACTIVITY.' A');
        $this->db->join(ENTITYTAGS." ET","ET.EntityType='ACTIVITY' AND ET.EntityID=A.ActivityID","join");
        $this->db->join(TAGS." T","T.TagID=ET.TagID","left");
        $this->db->where('ModuleID',$module_id);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->order_by('TagCount','DESC');
        $this->db->group_by('ET.TagID');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
        if($query->num_rows())
        {
            return $query->result_array();
        }
    }

    /**
     * [get_featured_post]
     * @param  $user_id
     * @param  $module_id
     * @param  $module_entity_id
     * @param  $page_no
     * @param  $page_size
     * @return [boolean]
     */
    function get_featured_post($user_id,$module_id,$module_entity_id,$page_no=1,$page_size=3)
    {
        $data = array();
        $this->db->select('A.ActivityID,A.PostTitle,A.PostContent,A.NoOfLikes,A.NoOfComments,A.ActivityGUID');
        $this->db->select('U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture,P.Url as ProfileURL',false);
        $this->db->from(ACTIVITY.' A');
        $this->db->join(USERS.' U','A.UserID=U.UserID','left');
        $this->db->join(PROFILEURL.' P','P.EntityType="User" AND P.EntityID=U.UserID','left');
        $this->db->where('A.ModuleID',$module_id);
        $this->db->where('A.ModuleEntityID',$module_entity_id);
        $this->db->where('A.IsFeatured','1');
        $this->db->where('A.StatusID','2');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
        if($query->num_rows())
        {
            foreach($query->result_array() as $result)
            {
                $result['ActivityURL'] = get_single_activity_url($result['ActivityID']);
                if(!empty($result['PostTitle']))
                {
                    $result['PostContent'] = $result['PostTitle'];    
                }
                else
                {
                    $result['PostContent'] = $this->parse_tag($result['PostContent']);
                }
                $result['IsLike'] = $this->is_liked($result['ActivityID'], 'ACTIVITY', $user_id, 3, $user_id);
                unset($result['ActivityID']);
                $data[] = $result;
            }
        }
        else
        {
            $this->db->select("UAL.ModuleEntityID as ActivityID,COUNT(UAL.ID) as Popularity,A.PostTitle,A.PostContent,A.NoOfLikes,A.NoOfComments,A.ActivityGUID",false);
            $this->db->select('U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture,P.Url as ProfileURL',false);
            $this->db->from(USERSACTIVITYLOG.' UAL');
            $this->db->join(ACTIVITY.' A','A.ActivityID=UAL.ModuleEntityID');
            $this->db->join(USERS.' U','A.UserID=U.UserID','left');
            $this->db->join(PROFILEURL.' P','P.EntityType="User" AND P.EntityID=U.UserID','left');
            $this->db->where('UAL.ModuleID','19');
            $this->db->where('A.StatusID','2');
            $this->db->where('A.ModuleID',$module_id);
            $this->db->where('A.ModuleEntityID',$module_entity_id);
            $this->db->where_in('A.ActivityTypeID',array(26,7,1));
            $this->db->where("UAL.ActivityDate BETWEEN '".get_current_date('%Y-%m-%d 00:00:00', 7)."' AND '".get_current_date('%Y-%m-%d 23:59:59')."'",NULL,FALSE);
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            $this->db->group_by('UAL.ModuleEntityID');
            $query = $this->db->get();
            if($query->num_rows())
            {
                foreach($query->result_array() as $result)
                {
                    $result['ActivityURL'] = get_single_activity_url($result['ActivityID']);
                    if(!empty($result['PostTitle']))
                    {
                        $result['PostContent'] = $result['PostTitle'];    
                    }
                    else
                    {
                        $result['PostContent'] = $this->parse_tag($result['PostContent']);
                    }
                    $result['IsLike'] = $this->is_liked($result['ActivityID'], 'ACTIVITY', $user_id, 3, $user_id);
                    unset($result['ActivityID']);
                    $data[] = $result;
                }
            }
        }
        return $data;
    }

    /**
     * [remove_featured_post]
     * @param  $user_id
     * @param  $module_id
     * @param  $module_entity_id
     * @param  $activity_id
     * @return [boolean]
     */
    function remove_featured_post($user_id,$module_id,$module_entity_id,$activity_id)
    {
        return $this->set_featured_post($user_id,$module_id,$module_entity_id,$activity_id);
    }

    /**
     * [set_featured_post]
     * @param  $user_id
     * @param  $module_id
     * @param  $module_entity_id
     * @param  $activity_id
     * @return [boolean]
     */
    function set_featured_post($user_id,$module_id,$module_entity_id,$activity_id, $need_check = true)
    {
        if($need_check) {
            $this->load->model('users/user_model');
            $check = false;
            switch ($module_id) {
                case '1':
                    $check = $this->group_model->is_admin($user_id,$module_entity_id);
                    break;
                case '3':
                    if($user_id == $module_entity_id)
                    {
                        $check = true;
                    }
                    break;
                case '14':
                    $check = $this->event_model->is_admin($module_entity_id,$user_id);
                    break;
                case '18':
                    $check = $this->page_model->check_page_owner($user_id,$module_entity_id);
                    break;
                case '34':
                    $perm = $this->forum_model->check_forum_category_permissions($user_id,$module_entity_id);
                    $check = $perm['IsAdmin'];
                    break;
            }

            if($this->user_model->is_super_admin($user_id, 1))
            {
                $check = true;
            }
        } else {
            $check = true;
        }
        
        if($check)
        {
            $this->db->select("IsFeatured");
            $this->db->from(ACTIVITY);
            $this->db->where('ActivityID',$activity_id);
            //$this->db->where('ModuleEntityID',$module_entity_id);
            $query = $this->db->get();
            
            $row = $query->row();
            $isFeatured = 1;    
            if(isset($row->IsFeatured) && $row->IsFeatured == 1 ) {
                $isFeatured = 0;
            }
            
            $feature_date = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->db->set('FeaturedDate',$feature_date);
            $this->db->set('IsFeatured',$isFeatured);
            
            if($need_check) {
                $this->db->set('IsAdminFeatured', $isFeatured);
            }
            
            
            $this->db->where('ActivityID',$activity_id);
            $this->db->update(ACTIVITY);
            return array(
                'IsFeatured' => $isFeatured
            );
        }
        
        return array(
            'IsFeatured' => null
        );
    }
    
    /**
     * [update_discussions_count description]
     * @param  [int]    $entity_id   [Entity Id]
     * @param  [string] $module_id [ACTIVITY, COMMENT]
     * @param  int      $count      [Like Count increment/decrement]
     * @return [type]               [description]
     */
    public function update_forum_discussions_count($module_entity_id, $module_id, $count = 1)
    {
        $set_field = "NoOfDiscussions";
        switch ($module_id)
        {
            case '34':
                $table_name = FORUMCATEGORY;
                $condition = array("ForumCategoryID" => $module_entity_id);
                break;
            default:
                return false;
                break;
        }
        if($module_id=='34')
        {
            $forum_id=get_detail_by_id($module_entity_id, 34, "ForumID", 1);
            $this->db->where("ForumID" ,$forum_id);
            $this->db->set($set_field, "$set_field+($count)", FALSE);
            $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->update(FORUM);
        }
        $this->db->where($condition);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->update($table_name);
    }
    
    public function pin_to_top($activity_id,$is_visible=1,$is_featured=0,$post_type=1)
    {
        $this->db->select("ActivityID",false);
        $this->db->from(ACTIVITY);
        //$this->db->where('LocalityID', $this->LocalityID);
        $this->db->where('IsVisible',3);
        $this->db->where('StatusID',2);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            return FALSE;
        }

        $this->db->where('ActivityID',$activity_id);
        $this->db->set('IsVisible',3);
        $this->db->update(ACTIVITY);
        return TRUE;
    }
    

    /**
     * 
     * @param type $forum_id
     * @param type $forum_category_id
     * @param type $page_no
     * @param type $page_size
     * @return type
     */
    function articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), $activity_ids = array(),$view_entity_tags=1,$role_id=2,$post_type=0,$tags='',$show_from=array(),$exclude_article_id='', $extraData = []) 
    {
        $result=array();
       
        $this->load->model(array('polls/polls_model','category/category_model','forum/forum_model'));
        $this->load->model('sticky/sticky_model');
        
        $exclude_ids = array();
        $show_from_group=array();
        $show_from_forum=array();
        $show_from_category=array();
        
        if(!empty($show_from))
        {
            foreach($show_from as $show_from_item) {
               if($show_from_item['ModuleID']==1) {
                  $show_from_group[]= $show_from_item['ModuleEntityID'];
               } else if($show_from_item['ModuleID']==33) {
                  $show_from_forum[]= $show_from_item['ModuleEntityID'];
               } else if($show_from_item['ModuleID']==34) {
                  $show_from_category[]= $show_from_item['ModuleEntityID'];
               }
            }
        }
        //print_r($show_from_category);die;
        $trending_article       = array();
        $suggested_article      = array();
        $recommended_article    = array();

        $blocked_users          = $this->blocked_users;
        $time_zone              = $this->user_model->get_user_time_zone();
        $friend_followers_list  = $this->user_model->get_friend_followers_list();
        
        $friends                = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow                 = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        //$friend_of_friends      = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        //$friend_of_friends[] = 0;
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
        //$friend_of_friends = implode(',', $friend_of_friends);
        $event_list=array();
        $page_list=array();
        $group_list='0';
        $category_list=array(0);
        if($entity_module_id==1) {
           $group_list=$entity_id; 
        } else if($entity_module_id==34) {
           $category_list[]=$entity_id; 
        } else if($entity_module_id==3 && empty($show_from)) {            
            $group_list = $this->group_model->get_visible_group_list();            
            $group_list[] = 0;
            $group_list = implode(',', $group_list);            
            $category_list = $this->forum_model->get_visible_category_list();
            $event_list = $this->event_model->get_user_joined_events();
            $page_list = $this->page_model->get_feed_pages_condition();
        }
        if(!empty($show_from_group)) {
            $group_list= implode(',', $show_from_group);
        }
        if(!empty($show_from_category)) {
            $category_list= $show_from_category;
        }
        if(!empty($show_from_forum)) {
            $forum_category_list=$this->forum_model->get_forum_category_ids($show_from_forum,$user_id);
            $forum_category_list[]=0;
            if(!empty($category_list)) {
                $category_list= array_merge($category_list,$forum_category_list);
            } else {
                $category_list=$forum_category_list;
            }
        }
                
        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $show_suggestions = FALSE;
        $show_media = TRUE;       
        
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        if($entity_module_id==1) {
            $modules_allowed = array(1);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        }
        else if($entity_module_id==34) {
            $modules_allowed = array(34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
            $this->db->where_in('A.ModuleEntityID',$category_list);
        }
        
        if(!empty($show_from)) {
            $modules_allowed=[];
            if(!empty($show_from_group)) {
               $modules_allowed[] =1; 
            } if(!empty($category_list)) {
               $modules_allowed[] =34; 
            }
            $show_from_module_entity_id=$category_list;
            if($group_list) {
               $show_from_module_entity_id= array_merge($category_list, explode(',', $group_list)) ;
            }
            $this->db->where_in('A.ModuleEntityID',$show_from_module_entity_id);
        }       

        if ($filter_type == 3) {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26, 30);
        }

        /* --Filter by activity type id-- */
        //$activity_ids = array();
        if (!empty($activity_type_filter)) {
            $activity_type_allow = $activity_type_filter;
            $show_suggestions = false;            
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed)) {
            $modules_allowed[] = 1;
        }

        //Activity Type 1 for followers, friends and current user
        //Activity Type 2 for followers and friends only
        //Activity Type 3 for follower and friend of UserID
        //Activity Type 8, 9, 10 for Mutual Friends Only
        //Activity Type 4, 7 for Group Members Only
        $condition = '';
        $condition_part_one = '';
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = '';
        $condition_part_four = '';
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids)) {
            /* $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
             
             */
            
            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
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
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if(!empty($category_list)) {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
        /* if (!empty($page_list)) {
            $case_array[]="A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        if (!empty($event_list)) {
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")";
        } */
        if(!empty($case_array)) {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition)) {
            $condition = $condition_part_two;
        } 

        if($entity_module_id != '3') {
            $this->db->where('A.ModuleID',$entity_module_id);
            $this->db->where('A.ModuleEntityID',$entity_id);
        }

        if($exclude_article_id)
        {
            $this->db->where('A.ActivityID!=',$exclude_article_id);
        }

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';
        $select_array[]='IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');        
        $this->db->_protect_identifiers = TRUE;
        
        
        if(/*$feed_sort_by == 'Name' || */ $search_key) {
            $this->db->join(GROUPS . ' G', 'G.GroupID=A.ModuleEntityID AND A.ModuleID = 1', 'left');            
            $this->db->join(FORUMCATEGORY . ' FC', 'FC.ForumCategoryID=A.ModuleEntityID AND A.ModuleID = 34', 'left');
        }
        

        /* Join Activity Links Starts */
        $select_array[]='IF(AL.URL is NULL,0,1) as IsLinkExists';
        $select_array[]='AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';

        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        //$this->db->select("(SELECT COUNT(ID) FROM ".USERSACTIVITYLOG." WHERE ModuleID='19' AND ModuleEntityID=A.ActivityID AND UserID IN(".$friend_followers_list.") AND ActivityTypeID IN(19,20,22) AND ActivityDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 30) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s')."') as Popularity",false);
        /* Join Activity Links Ends */
        
       /* if($exclude_ids)
        {
            $this->db->where_not_in('A.ActivityID',$exclude_ids);
        }*/
        if($post_type) {
            $this->db->where_in('A.PostType',$post_type);
        }
        
        if(!empty($extraData['createdBy'])) {
            $this->db->where('A.UserID', $extraData['createdBy']);
        }

        if ($filter_type == 7) {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } else if ($filter_type == 10) {
            $this->db->where('A.StatusID', '10');
            $this->db->where('A.UserID', $user_id);
        } else if ($filter_type == 11) {
            $this->db->where('A.IsFeatured', '1');
        } else {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43)) {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } else if (($filter_type == 1 || $filter_type === 'Favourite') && empty($activity_ids)) {
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

        if($tags) {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM ".ENTITYTAGS." WHERE EntityType='ACTIVITY' AND TagID IN (".implode(',',$tags)."))",null,false);
        }

        if ($filter_type == 2) {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
        }
        if ($feed_user) {
            if(is_array($feed_user)) {
                $this->db->where_in('U.UserID', $feed_user);
            } else {
                $this->db->where('U.UserID', $feed_user);
            }
        }

        if (!$show_media) {
            if ($is_media_exists == 2) {
                $is_media_exists = '0';
            } if ($is_media_exists == 1) {
                $is_media_exists = '3';
            }
        }

        if ($is_media_exists != 2) {
            $this->db->where('A.IsMediaExist', $is_media_exists);
        }        

        if (!empty($search_key)) {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('( FC.Name LIKE "%' . $search_key . '%" OR G.GroupName LIKE "%' . $search_key . '%" OR  U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        if (!empty($blocked_users) && empty($feed_user)) {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        //$this->db->where('A.IsRecommended','0');
        $this->db->where('ATY.StatusID', '2');
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

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7) {
            $select_array[]="R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array[]="IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";

            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3) {
                $jointype = 'join';
                $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            } else {
                if (!$activity_guid) {
                    $this->db->where("(R.Status IS NULL OR R.Status='ACTIVE')");
                }
            }
            $this->db->join(REMINDER . " R", $joincondition, $jointype);
            $this->db->_protect_identifiers = TRUE;
        }

        if ($feed_sort_by == 2) {
            $this->db->order_by('A.CreatedDate', 'DESC');
        } 
        else if($feed_sort_by == 'Name') {
            //$this->db->order_by('IF(G.GroupName IS NOT NULL AND G.GroupName != '', G.GroupName, IF(FC.Name IS NOT NULL, FC.Name, A.PostTitle) )', 'ASC');
            $this->db->order_by('A.PostTitle', 'ASC');
        } 
        else if($feed_sort_by == 3) {
            $this->db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfViews)', 'DESC');
        }         
        else if ($feed_sort_by == 'popular') {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            //$this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } elseif ($feed_sort_by == 1) {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        } elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids)) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } elseif($feed_sort_by=="General") {
            $this->db->where('A.PostType',0);
        } elseif ($feed_sort_by=="Question") {
            $this->db->where('A.PostType',1);
        } elseif ($feed_sort_by=="UnAnswered") {
            $this->db->where('A.PostType',1);
            $this->db->where('A.NoOfComments',0);
        } else if($feed_sort_by == 0) {
            $this->db->order_by('Popularity', 'DESC');
        } else {
            $this->db->order_by('A.CreatedDate', 'DESC');
        }
        
        $this->db->where('A.PostType', 4);
        
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

        if (!$count_only) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        if ($count_only) {
            $this->db->select('COUNT(DISTINCT A.ActivityID) as TotalRow ' );
            $query = $this->db->get();
            $count_data=$query->row_array();
            return $count_data['TotalRow'];
        } else {
            $this->db->select(implode(',', $select_array),false);
            $this->db->group_by('A.ActivityID');
            $query = $this->db->get();
        }
        //echo $this->db->last_query(); die;
        $final_array=array();
        
        if($query->num_rows() >0 ) {  
            $this->load->model(array('album/album_model','subscribe_model', 'activity/activity_front_helper_model')); 
           foreach($query->result_array() as $res) {                
                $activity=array();
                $module_id                      = $res['ModuleID'];
                $module_entity_id               = $res['ModuleEntityID'];
                $activity_id                    = $res['ActivityID'];
                $activity['ModuleID']           = $res['ModuleID'];
                $activity['IsTrending']         = $res['IsTrending'];
                $activity['IsRecommended']      = $res['IsRecommended'];
                $activity['ModuleEntityID']     = $res['ModuleEntityID'];
                $activity['Params']             = $res['Params'];
                $activity['ActivityGUID']       = $res['ActivityGUID'];
                $activity['PostContent']        = $res['PostContent'];
                $edit_post_content              = $activity['PostContent'];
                $activity['IsFavourite']        = (in_array($activity_id, $this->favourite_model->get_user_favourite())) ? 1 : 0;
                $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                $activity['PostContent']        = $this->activity_model->parse_tag($activity['PostContent']);
                
                $activity['IsLike'] = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                $activity['PostTitle']=$res['PostTitle'];
                $activity['CreatedDate']=$res['CreatedDate'];
                $activity['Album']=array();
                $activity['CanRemove'] = 0;
                $activity['IsOwner'] = 0;
                $activity['LikeName'] = array();
                $activity['IsEntityOwner']  = 0;
                $activity['PostType']           = $res['PostType'];
                $activity['IsChecked']  = 0;
                $activity['UserName']  = $res['FirstName'].' '.$res['LastName'];
                $activity['UserGUID']  = $res['UserGUID'];
                $activity['ProfilePicture']  = $res['ProfilePicture'];
                $activity['UserProfileURL']  = get_entity_url($res['UserID'], 'User', 1);
                $activity['ActivityID']       = $res['ActivityID'];
                $activity['ArticleSummary']           = !empty($res['Summary']) ? $res['Summary'] : $res['PostSearchContent'];                
                $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
                
                if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                { 
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                }
                
                if($res['IsMediaExist'])
                {
                    $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                } 
                if($BUsers)
                {
                    $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                }
                else
                {
                    $activity['NoOfComments'] = $res['NoOfComments']; 
                    $activity['NoOfLikes'] = $res['NoOfLikes']; 
                }
                if ($module_id == 1)
                {
                    $group_details = check_group_permissions($user_id, $module_entity_id);

                    //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                    if (isset($group_details['Details']) && !empty($group_details['Details']))
                    {
                        $entity                         = $group_details['Details'];
                        $activity['EntityProfileURL']   = $module_entity_id;
                        $activity['EntityGUID']         = $entity['GroupGUID'];
                        $activity['EntityName']         = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        
                        if ($group_details['IsAdmin'])
                        {
                            $activity['IsEntityOwner']  = 1;
                            $activity['CanRemove']      = 1;
                        }
                    
                    }                    
                }
                if ($module_id == 3)
                {

                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity)
                    {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                    if ($user_id == $module_entity_id)
                    {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                    }
                }
                if ($module_id == 14)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $activity['EntityGUID'] = $entity['EventGUID'];
                    }
                    $activity['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');
                    if ($this->event_model->isEventOwner($module_entity_id, $user_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }
                    
                }
                if ($module_id == 18)
                    {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                   if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                    }
                    if ($this->page_model->check_page_owner($user_id, $module_entity_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }
                    
                }

                if($module_id == 34)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Name'];
                        $activity['EntityProfilePicture'] = $entity['MediaID'];
                        $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                        $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                    } 
                    $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);
                    if ($perm['IsAdmin'])
                    {
                        //$activity['IsOwner']        = 1;
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }

                }
                if($activity['NoOfComments'] > 0)
                {
                    if($activity['IsOwner'])
                    {
                        $activity['CanRemove'] = 1;
                    }
                }
                $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
                
                $activity['MembersTalking'] = $this->activity_front_helper_model->get_members_talking($activity_id);
                
                $final_array[]=$activity;
           }
        }
        $result=$final_array;
        return $result;
    }

    function fav_articles($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $entity_id = '', $entity_module_id = '', $activity_type_filter = array(), $activity_ids = array(),$view_entity_tags=1,$role_id=2,$post_type=0,$tags='',$is_exclude_ids=false) 
    {
        $filter_type = 'Favourite';
        $result=array('Data'=>array(),'FavIDs'=>array());
       
        $this->load->model(array('polls/polls_model', 'forum/forum_model'));
        
        $blocked_users          = $this->blocked_users;
        $time_zone              = $this->user_model->get_user_time_zone();
        $friend_followers_list  = $this->user_model->get_friend_followers_list();
                
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        //$friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        //$friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
       // $friend_of_friends = implode(',', $friend_of_friends);
        $event_list=array();
        $page_list=array();
        $group_list='0';
        $category_list=array(0);
        if($entity_module_id==1)
        {
           $group_list=$entity_id; 
        }
        else if($entity_module_id==34)
        {
           $category_list[]=$entity_id; 
        }
        else if($entity_module_id==3)
        {            
            $group_list = $this->group_model->get_visible_group_list();            
            $group_list[] = 0;
            $group_list = implode(',', $group_list);
            $category_list = $this->forum_model->get_visible_category_list();
            $event_list = $this->event_model->get_user_joined_events();            
            $page_list = $this->page_model->get_feed_pages_condition();
        }
        

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $show_suggestions = FALSE;
        $show_media = TRUE;

                
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        $post_type=array(4);
        if($entity_module_id==1)
        {
            $modules_allowed = array(1);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        }
        else if($entity_module_id==34)
        {
            $modules_allowed = array(34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
            $this->db->where_in('A.ModuleEntityID',$category_list);
        }
        
        
        if ($filter_type == 3)
        {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26, 30);
        }

        /* --Filter by activity type id-- */
        //$activity_ids = array();
        if (!empty($activity_type_filter))
        {
            $activity_type_allow = $activity_type_filter;
            $show_suggestions = false;
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed))
        {
            $modules_allowed[] = 1;
        }

        //Activity Type 1 for followers, friends and current user
        //Activity Type 2 for followers and friends only
        //Activity Type 3 for follower and friend of UserID
        //Activity Type 8, 9, 10 for Mutual Friends Only
        //Activity Type 4, 7 for Group Members Only
        $condition = '';
        $condition_part_one = '';
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = '';
        $condition_part_four = '';
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids))
        {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
            
            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
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
        if ($group_list)
        {
            
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if(!empty($category_list))
        {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
        if (!empty($page_list))
        {
            $case_array[]="A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        if (!empty($event_list))
        {
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")";
        }
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition))
        {
            $condition = $condition_part_two;
        } 

        if($entity_module_id != '3')
        {
            $this->db->where('A.ModuleID',$entity_module_id);
            $this->db->where('A.ModuleEntityID',$entity_id);
        }

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';
        //$select_array[]='IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
       /* $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        /*$this->db->join(FRIENDS." FR","FR.FriendID=A.ModuleEntityID AND A.ModuleID='3' AND FR.UserID='".$user_id."' AND FR.Status='1'");*/
        //$this->db->_protect_identifiers = TRUE;

        /* Join Activity Links Starts */
        /*$select_array[]='IF(AL.URL is NULL,0,1) as IsLinkExists';
        $select_array[]='AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';

        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
         */
        /* Join Activity Links Ends */
        if($activity_guid)
        {
           $this->db->where('A.ActivityGUID != ',$activity_guid); 
        }
        
        if($post_type)
        {
            $this->db->where_in('A.PostType',$post_type);
        }

        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } 
        else if ($filter_type == 10)
        {
            $this->db->where('A.StatusID', '10');
            $this->db->where('A.UserID', $user_id);
        } 
        else if ($filter_type == 11)
        {
            $this->db->where('A.IsFeatured', '1');
        } 
        else
        {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } 
            else if (($filter_type == 1 || $filter_type === 'Favourite') && empty($activity_ids))
            {
                $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"');
                $this->db->where('F.UserID', $user_id);
                $this->db->where('F.StatusID', '2');
            } 
            else
            {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43))
                {
                    $this->db->where("NOT EXISTS (SELECT 1 FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND ActivityID=A.ActivityID AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids)
            {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            if ($mentions)
            {
                $join_condition = "MN.ActivityID=A.ActivityID AND (";
                foreach ($mentions as $mention)
                {
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

            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        }

        if($tags)
        {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM ".ENTITYTAGS." WHERE EntityType='ACTIVITY' AND TagID IN (".implode(',',$tags)."))",null,false);
        }

        if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
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
        }

        if (!$show_media)
        {
            if ($is_media_exists == 2)
            {
                $is_media_exists = '0';
            }
            if ($is_media_exists == 1)
            {
                $is_media_exists = '3';
            }
        }

        if ($is_media_exists != 2)
        {
            $this->db->where('A.IsMediaExist', $is_media_exists);
        }        
        
        if (!empty($search_key))
        {
            
            $this->db->join(GROUPS . ' G', 'G.GroupID=A.ModuleEntityID AND A.ModuleID = 1', 'left');            
            $this->db->join(FORUMCATEGORY . ' FC', 'FC.ForumCategoryID=A.ModuleEntityID AND A.ModuleID = 34', 'left');
            
            $search_key = $this->db->escape_like_str($search_key);
            
            $this->db->where('( FC.Name LIKE "%' . $search_key . '%" OR G.GroupName LIKE "%' . $search_key . '%" OR U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        if (!empty($blocked_users) && empty($feed_user))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        $this->db->where('ATY.StatusID', '2');
        if (empty($activity_ids))
        {
            if (!empty($condition))
            {
                $this->db->where($condition, NULL, FALSE);
            } else
            {
                $this->db->where('A.ModuleID', '3');
                $this->db->where('A.ModuleEntityID', $user_id);
            }
            if ($privacy_condition)
            {
                $this->db->where($privacy_condition, null, false);
            }
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            $select_array[]="R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array[]="IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";

            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3)
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


            /*if (!$count_only)
            {
                $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            }*/
            $this->db->_protect_identifiers = TRUE;
        }

        if ($feed_sort_by == 2)
        {
            $this->db->order_by('A.ActivityID', 'DESC');
        } 
        else if($feed_sort_by == 3)
        {
            $this->db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfViews)', 'DESC');
        } 
        else if ($feed_sort_by == 'popular')
        {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            //$this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } 
        elseif ($feed_sort_by == 1)
        {
            $this->db->order_by('F.ModifiedDate', 'DESC');
        } 
        elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } 
        elseif($feed_sort_by=="General")
        {
            $this->db->where('A.PostType',0);
        }
        elseif($feed_sort_by=="Name")
        {
            $this->db->order_by('A.PostTitle', 'ASC');
        }
        elseif ($feed_sort_by=="Question") {
            $this->db->where('A.PostType',1);
        }
        elseif ($feed_sort_by=="UnAnswered") {
            $this->db->where('A.PostType',1);
            $this->db->where('A.NoOfComments',0);
        }
        else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($filter_type == 3)
        {
            if ($ReminderDate)
            {
                $rd_data = array();
                foreach ($ReminderDate as $rd)
                {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }

        //$this->db->where_not_in('U.StatusID', array(3, 4));

    
        if (!$count_only)
        {
            if($page_size > 0)
            {
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
        }
        if ($count_only)
        {
            $this->db->select('COUNT(DISTINCT A.ActivityID) as TotalRow ' );
            $query = $this->db->get();
            $count_data=$query->row_array();
            return $count_data['TotalRow'];
        }
        else
        {
            $this->db->select(implode(',', $select_array),false);
            $this->db->group_by('A.ActivityID');
            $query = $this->db->get();
        }
        //echo $this->db->last_query(); die;
        if($query->num_rows() >0 )
        {  
            $exclude_ids=array();
            if($is_exclude_ids)
            {
               foreach($query->result_array() as $res)
               {
                   $exclude_ids[]=$res['ActivityID'];
               }
               return $exclude_ids;
            }
            
            $this->load->model(array('album/album_model','subscribe_model', 'activity/activity_front_helper_model')); 
            $final_array=array();
           foreach($query->result_array() as $res)
           {                
                $activity=array();
                $module_id                      = $res['ModuleID'];
                $module_entity_id               = $res['ModuleEntityID'];
                $activity_id=$res['ActivityID'];
                $activity['ModuleID']           = $res['ModuleID'];
                $activity['IsTrending']         = $res['IsTrending'];
                $activity['IsRecommended']      = $res['IsRecommended'];
                $activity['IsFavourite']        = 1;
                $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                $activity['ModuleEntityID']     = $res['ModuleEntityID'];
                $activity['Params']             = $res['Params'];
                $activity['ActivityGUID']       = $res['ActivityGUID'];
                $activity['PostContent']        = $res['PostContent'];
                $edit_post_content              = $activity['PostContent'];
                $activity['PostContent']        = $this->activity_model->parse_tag($activity['PostContent']);
                $activity['PostTitle']          = $res['PostTitle'];
                $activity['CreatedDate']        = $res['CreatedDate'];
                $activity['IsLike']             = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                $activity['Album']              = array();
                $activity['CanRemove']          = 0;
                $activity['IsOwner']            = 0;
                $activity['LikeName']           = array();
                $activity['IsEntityOwner']      = 0;
                $activity['PostType']           = $res['PostType'];
                
                $activity['ArticleSummary']           = !empty($res['Summary']) ? $res['Summary'] : $res['PostSearchContent'];                
                
                $activity['NoOfFollowers']      = $this->get_follower_count($activity_id);
                $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
                
                if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                { 
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                }
                
                if($res['IsMediaExist'])
                {
                    $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                } 
                if($BUsers)
                {
                    $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                }
                else
                {
                    $activity['NoOfComments'] = $res['NoOfComments']; 
                    $activity['NoOfLikes'] = $res['NoOfLikes']; 
                }
                if ($module_id == 1)
                {
                    $group_details = check_group_permissions($user_id, $module_entity_id);
                    if (isset($group_details['Details']) && !empty($group_details['Details']))
                    {
                        $entity                         = $group_details['Details'];
                        $activity['EntityProfileURL']   = $module_entity_id;
                        $activity['EntityGUID']         = $entity['GroupGUID'];
                        $activity['EntityName']         = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        
                        if ($group_details['IsAdmin'])
                        {
                            $activity['IsEntityOwner']  = 1;
                            $activity['CanRemove']      = 1;
                        }
                    
                    }                    
                }
                if ($module_id == 3)
                {

                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity)
                    {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                    if ($user_id == $module_entity_id)
                    {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                    }
                }
                if ($module_id == 14)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $activity['EntityGUID'] = $entity['EventGUID'];
                    }
                    $activity['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');
                    if ($this->event_model->isEventOwner($module_entity_id, $user_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }
                    
                }
                if ($module_id == 18)
                    {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                   if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                    }
                    if ($this->page_model->check_page_owner($user_id, $module_entity_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }
                    
                }

                if($module_id == 34)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Name'];
                        $activity['EntityProfilePicture'] = $entity['MediaID'];
                        $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                        $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                    } 
                    $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);
                    if ($perm['IsAdmin'])
                    {
                        //$activity['IsOwner']        = 1;
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }

                }
                if($activity['NoOfComments'] > 0)
                {
                    if($activity['IsOwner'])
                    {
                        $activity['CanRemove'] = 1;
                    }
                }
                $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
                                                
                $activity['MembersTalking'] = $this->activity_front_helper_model->get_members_talking($activity_id);
                
               $final_array[]=$activity;
               $exclude_ids[]=$activity_id;
           }
           $result['Data']=$final_array;
           $result['FavIDs']=$exclude_ids;
        }
        if($is_exclude_ids)
            return array();
        else
            return $result;
    }
    
    function suggested_article($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $module_entity_id = '', $module_id = '', $activity_type_filter = array(), $activity_ids = array(),$view_entity_tags=1,$role_id=2,$post_type=0,$tags='',$is_exclude_ids=false,$exclude_ids=array())
    {
        $result=array('Data'=>array(),'SuggestedIDs'=>array());
        $entity_id = $module_entity_id;
        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        
        /*$exclude_ids = $this->fav_articles($user_id, 1, 4, 1, 0, '', 2, '', '', '', 0, 0, array(), 0,array(), $module_entity_id, $module_id, array(), array(),0,'',0,array(),true);
        if(!$exclude_ids || !is_array($exclude_ids))
        {
            $exclude_ids = array();
        }*/

        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

       // $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
       // $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
       // $friend_of_friends = implode(',', $friend_of_friends);
        if(!$friend_followers_list)
        {
            return array();
        }
        $group_list='0';
        $category_list=array(0);
        if($module_id==1)
        {
           $group_list=$entity_id; 
        }
        else if($module_id==34)
        {
           $category_list[]=$entity_id; 
        }
        else if($module_id==3)
        {
            $group_list = $this->group_model->get_visible_group_list();
            $group_list[] = 0;

            $group_list = implode(',', $group_list);
            $category_list = $this->forum_model->get_visible_category_list();
        }
        

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $show_suggestions = FALSE;
        $show_media = TRUE;

     
        
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        $post_type=array(4);
        if($module_id==1)
        {
            $modules_allowed = array(1);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        }
        else if($module_id==34)
        {
            $modules_allowed = array(34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
            $this->db->where_in('A.ModuleEntityID',$category_list);
        }
     

        $condition = '';
        $condition_part_one = '';
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = '';
        $condition_part_four = '';
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids))
        {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
            
            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
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
        if ($group_list)
        {
            
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if(!empty($category_list))
        {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
    
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition))
        {
            $condition = $condition_part_two;
        } 

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture , TotalLikeViewComment as TotalCount';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->join(USERSACTIVITYLOG.' UAL','UAL.UserID=U.UserID AND U.UserID IN('.$friend_followers_list.' ) AND A.ActivityTypeID IN(1,7,8,19,20,26)','join');
        $this->db->select(implode(',', $select_array),false);
        $this->db->where('A.ActivityID NOT IN (SELECT EntityID FROM '.IGNORE.' WHERE EntityType="SuggestedArticle" AND UserID ='.$user_id.' ) ', null,false);
        if($post_type)
        {
            $this->db->where_in('A.PostType',$post_type);
        }
        
        if ($filter_type == 7)
        {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } 
        else if ($filter_type == 10)
        {
            $this->db->where('A.StatusID', '10');
            $this->db->where('A.UserID', $user_id);
        } 
        else if ($filter_type == 11)
        {
            $this->db->where('A.IsFeatured', '1');
        } 
        else
        {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43))
            {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } 
            else if (($filter_type == 1 || $filter_type === 'Favourite') && empty($activity_ids))
            {
                $this->db->join(FAVOURITE . ' F', 'F.EntityID=A.ActivityID  AND F.EntityType="ACTIVITY"');
                $this->db->where('F.UserID', $user_id);
                $this->db->where('F.StatusID', '2');
            } 
            else
            {
                if (!$activity_guid && empty($activity_ids) && !$this->settings_model->isDisabled(43))
                {
                    $this->db->where("NOT EXISTS (SELECT 1 FROM " . ARCHIVEACTIVITY . " WHERE Status='ARCHIVED' AND ActivityID=A.ActivityID AND UserID='" . $user_id . "')", NULL, FALSE);
                }
            }

            if ($activity_ids)
            {
                $this->db->where_in('A.ActivityID', $activity_ids);
            }

            if ($mentions)
            {
                $join_condition = "MN.ActivityID=A.ActivityID AND (";
                foreach ($mentions as $mention)
                {
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

            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        }

        if($tags)
        {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM ".ENTITYTAGS." WHERE EntityType='ACTIVITY' AND TagID IN (".implode(',',$tags)."))",null,false);
        }

        if ($filter_type == 2)
        {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
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
        }

        if (!$show_media)
        {
            if ($is_media_exists == 2)
            {
                $is_media_exists = '0';
            }
            if ($is_media_exists == 1)
            {
                $is_media_exists = '3';
            }
        }

        if ($is_media_exists != 2)
        {
            $this->db->where('A.IsMediaExist', $is_media_exists);
        }        
        
        
        if($module_id != '3')
        {
            $this->db->where('A.ModuleID',$module_id);
            $this->db->where('A.ModuleEntityID',$module_entity_id);
        }

        if (!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        if (!empty($blocked_users) )
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
       // $this->db->where('A.IsRecommended','0');
        $this->db->where('ATY.StatusID', '2');
        if (empty($activity_ids))
        {
            if (!empty($condition))
            {
                $this->db->where($condition, NULL, FALSE);
            } else
            {
                $this->db->where('A.ModuleID', '3');
                $this->db->where('A.ModuleEntityID', $user_id);
            }
            if ($privacy_condition)
            {
                $this->db->where($privacy_condition, null, false);
            }
        }

        if (!$this->settings_model->isDisabled(28) && $filter_type != 7)
        {
            $select_array[]="R.ReminderGUID,R.ReminderDateTime,R.CreatedDate as ReminderCreatedDate,R.Status as ReminderStatus";
            $select_array[]="IF(R.ReminderDateTime<'" . get_current_date('%Y-%m-%d %H:%i:%s') . "',1,0) as SortByReminder";

            $this->db->_protect_identifiers = FALSE;
            $jointype = 'left';
            $joincondition = "R.ActivityID=A.ActivityID AND R.UserID='" . $user_id . "'";
            if ($filter_type == 3)
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


            /*if (!$count_only)
            {
                $this->db->order_by("IF(SortByReminder=1,ReminderDateTime,'') DESC");
            }*/
            $this->db->_protect_identifiers = TRUE;
        }

        if ($feed_sort_by == 2)
        {
            //$this->db->order_by('A.ActivityID', 'DESC');
        } 
        else if($feed_sort_by == 3)
        {
            $this->db->order_by('(A.NoOfComments+A.NoOfLikes+A.NoOfViews)', 'DESC');
        } 
        else if ($feed_sort_by == 'popular')
        {
            $this->db->where_in('A.ActivityTypeID', array(1, 7, 11, 12));
            $this->db->where("A.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s') . "'");
            $this->db->where('A.NoOfComments>1', null, false);
            //$this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.ActivityTypeID', 'ASC');
            $this->db->order_by('A.NoOfComments', 'DESC');
            $this->db->order_by('A.NoOfLikes', 'DESC');
        } 
        elseif ($feed_sort_by == 1)
        {
            $this->db->order_by('F.ModifiedDate', 'DESC');
        } 
        elseif ($feed_sort_by == 'ActivityIDS' && !empty($activity_ids))
        {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by('FIELD(A.ActivityID,' . implode(',', $activity_ids) . ')');
            $this->db->_protect_identifiers = TRUE;
        } 
        elseif($feed_sort_by=="General")
        {
            $this->db->where('A.PostType',0);
        }
        elseif ($feed_sort_by=="Question") {
            $this->db->where('A.PostType',1);
        }
        elseif ($feed_sort_by=="UnAnswered") {
            $this->db->where('A.PostType',1);
            $this->db->where('A.NoOfComments',0);
        }
        else
        {
            $this->db->order_by('A.ModifiedDate', 'DESC');
        }

        if ($filter_type == 3)
        {
            if ($ReminderDate)
            {
                $rd_data = array();
                foreach ($ReminderDate as $rd)
                {
                    $rd_data[] = "'" . $rd . "'";
                }
                $this->db->where_in("DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $rd_data, FALSE);
            }
        }

        if ($start_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') >= '" . $start_date . "'", NULL, FALSE);
        }
        if ($end_date)
        {
            $this->db->where("DATE_FORMAT(CONVERT_TZ(A.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d') <= '" . $end_date . "'", NULL, FALSE);
        }
        if($exclude_ids)
        {
            $this->db->where_not_in('A.ActivityID',$exclude_ids);
        }
        $this->db->where('ATY.StatusID', '2');
        //$this->db->where('A.StatusID', '2');
        $this->db->order_by('TotalLikeViewComment','DESC');
        $this->db->group_by('A.ActivityID');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
       
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if($query->num_rows() >0 )
        {  
            $exclude_ids=array();
            if($is_exclude_ids)
            {
               foreach($query->result_array() as $res)
               {
                   $exclude_ids[]=$res['ActivityID'];
               }
               return $exclude_ids;
            }
           $this->load->model(array('album/album_model','subscribe_model')); 
           $final_array=array();
           foreach($query->result_array() as $res)
           {
                
                $activity=array();
                $module_id                      = $res['ModuleID'];
                $module_entity_id               = $res['ModuleEntityID'];
                $activity_id                    = $res['ActivityID'];
                $activity['ModuleID']           = $res['ModuleID'];
                $activity['IsTrending']         = $res['IsTrending'];
                $activity['IsRecommended']      = $res['IsRecommended'];
                $activity['ModuleEntityID']     = $res['ModuleEntityID'];
                $activity['Params']             = $res['Params'];
                $activity['ActivityGUID']       = $res['ActivityGUID'];
                $activity['PostContent']        = $res['PostContent'];
                $edit_post_content              = $activity['PostContent'];
                $activity['IsFavourite']        = (in_array($activity_id, $this->favourite_model->get_user_favourite())) ? 1 : 0;
                $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                $activity['PostContent']        = $this->activity_model->parse_tag($activity['PostContent']);
                $activity['PostTitle']          = $res['PostTitle'];
                $activity['CreatedDate']        = $res['CreatedDate'];
                $activity['LikeName']           = $this->get_friend_likes($user_id,$res['ActivityID'],$friend_followers_list);
                $activity['Album']=array();
                $activity['IsLike']             = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                $activity['CanRemove']          = 0;
                $activity['IsOwner']            = 0;
                $activity['IsEntityOwner']      = 0;
                $activity['PostType']           = $res['PostType'];
                $activity['TotalCount']         = $res['TotalCount'];
                $activity['ArticleSummary']           = !empty($res['Summary']) ? $res['Summary'] : $res['PostSearchContent'];                
                $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
                
                if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                { 
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                }
                
                if($res['IsMediaExist'])
                {
                    $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                } 
                if($BUsers)
                {
                    $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                }
                else
                {
                    $activity['NoOfComments'] = $res['NoOfComments']; 
                    $activity['NoOfLikes'] = $res['NoOfLikes']; 
                }
                if ($module_id == 1)
                {
                    $group_details = check_group_permissions($user_id, $module_entity_id);

                    //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                    if (isset($group_details['Details']) && !empty($group_details['Details']))
                    {
                        $entity                         = $group_details['Details'];
                        $activity['EntityProfileURL']   = $module_entity_id;
                        $activity['EntityGUID']         = $entity['GroupGUID'];
                        $activity['EntityName']         = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        
                        if ($group_details['IsAdmin'])
                        {
                            $activity['IsEntityOwner']  = 1;
                            $activity['CanRemove']      = 1;
                        }
                    
                    }                    
                }
                if ($module_id == 3)
                {

                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity)
                    {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                    if ($user_id == $module_entity_id)
                    {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                    }
                }
                if($module_id == 34)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Name'];
                        $activity['EntityProfilePicture'] = $entity['MediaID'];
                        $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                        $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                    } 
                    $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);
                    if ($perm['IsAdmin'])
                    {
                        //$activity['IsOwner']        = 1;
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }

                }
                if($activity['NoOfComments'] > 0)
                {
                    if($activity['IsOwner'])
                    {
                        $activity['CanRemove'] = 1;
                    }
                }
                
                $activity['PostType'] = $res['PostType']; 
                $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
               $final_array[]=$activity;
               $exclude_ids[]=$activity_id;
           }
           $result['Data']=$final_array;
           $result['SuggestedIDs']=$exclude_ids;
        }
        if($is_exclude_ids)
            return array();
        else
            return $result;

    }

    public function trending_article($user_id, $module_id, $module_entity_id, $page_no, $page_size, $is_exclude_ids=false)
    {
        $result                 = array();
        $entity_id              = $module_entity_id;
        $blocked_users          = $this->blocked_users;
        $time_zone              = $this->user_model->get_user_time_zone();
        $friend_followers_list  = $this->user_model->get_friend_followers_list();
                
        $exclude_ids = array();
        if($module_id!= 1 && $module_id != 34)
        {
            $exclude_ids1 = $this->suggested_article($user_id, 1, 4, 1, 0, '', 2, '', '', '', 0, 0, array(), 0,array(), $module_entity_id, $module_id, array(), array(),0,'',0,array(),true);
            $exclude_ids2 = $this->fav_articles($user_id, 1, 4, 1, 0, '', 2, '', '', '', 0, 0, array(), 0,array(), $module_entity_id, $module_id, array(), array(),0,'',0,array(),true);
            if(!$exclude_ids1 || !is_array($exclude_ids1))
            {
                $exclude_ids1 = array();
            }
            if(!$exclude_ids2 || !is_array($exclude_ids2))
            {
                $exclude_ids2 = array();
            }
            $exclude_ids = array_merge($exclude_ids1,$exclude_ids2);
        }
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        //$friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        //$friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        //$friend_of_friends = implode(',', $friend_of_friends);
        $group_list='0';
        $category_list=array(0);
        if($module_id==1)
        {
           $group_list=$entity_id; 
        }
        else if($module_id==34)
        {
           $category_list[]=$entity_id; 
        }
        else if($module_id==3)
        {
            $group_list = $this->group_model->get_visible_group_list();
            $group_list[] = 0;

            $group_list = implode(',', $group_list);
            
            $category_list = $this->forum_model->get_visible_category_list();
        }
        

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $show_suggestions = FALSE;
        $show_media = TRUE;

     
        
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18,25, 26);
        $post_type=array(4);
        if($module_id==1)
        {
            $modules_allowed = array(1);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        }
        else if($module_id==34)
        {
            $modules_allowed = array(34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
            $this->db->where_in('A.ModuleEntityID',$category_list);
        }
     

        $condition = '';
        $condition_part_one = '';
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = '';
        $condition_part_four = '';
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids))
        {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
            
            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
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
        if ($group_list)
        {
            
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if(!empty($category_list))
        {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
    
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition))
        {
            $condition = $condition_part_two;
        } 

        if($module_id != '3')
        {
            $this->db->where('A.ModuleID',$module_id);
            $this->db->where('A.ModuleEntityID',$module_entity_id);
        }

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture , SUM(NoOfLikes+NoOfComments+NoOfViews) as TotalCount';
        $select_array[]='IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(PRIORITIZESOURCE . ' PS', 'PS.ModuleID=A.ModuleID AND PS.ModuleEntityID=A.ModuleEntityID AND PS.UserID="' . $user_id . '"', 'left');
        $this->db->join(USERACTIVITYRANK . ' UAR', 'UAR.UserID="' . $user_id . '" AND UAR.ActivityID=A.ActivityID', 'left');
        /*$this->db->join(FRIENDS." FR","FR.FriendID=A.ModuleEntityID AND A.ModuleID='3' AND FR.UserID='".$user_id."' AND FR.Status='1'");*/
        $this->db->_protect_identifiers = TRUE;

        /* Join Activity Links Starts */
        $select_array[]='IF(URL is NULL,0,1) as IsLinkExists';
        $select_array[]='AL.URL as LinkURL,AL.Title as LinkTitle,AL.MetaDescription as LinkDesc,AL.ImageURL as LinkImgURL,AL.TagsCollection as LinkTags';
        $this->db->select(implode(',', $select_array),false);
        $this->db->join(ACTIVITYLINKS . ' AL', 'AL.ActivityID=A.ActivityID', 'left');
        /* Join Activity Links Ends */

        
        if($post_type)
        {
            $this->db->where_in('A.PostType',$post_type);
        }


        if (!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        if (!empty($blocked_users) )
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }

        $this->db->where('A.IsRecommended','0');

        if($exclude_ids)
        {
            $this->db->where_not_in('A.ActivityID',$exclude_ids);
        }

        $this->db->select("(SELECT COUNT(ID) FROM ".USERSACTIVITYLOG." WHERE ModuleID='19' AND ModuleEntityID=A.ActivityID AND ActivityTypeID IN(19,20,22) AND ActivityDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s')."') as Popularity",null,false);
        $this->db->where('ATY.StatusID', '2');
        $this->db->order_by('Popularity','DESC');
        $this->db->order_by('TotalCount','DESC');
        $this->db->group_by('A.ActivityID');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
       // echo $this->db->last_query(); die;
        if($query->num_rows() >0 )
        {  
           $this->load->model(array('album/album_model','subscribe_model')); 
           $final_array=array();
           if($is_exclude_ids)
           {
               $exclude_ids=array();
               foreach($query->result_array() as $res)
               {
                   $exclude_ids[]=$res['ActivityID'];
               }
               return $exclude_ids;
           }
           else
            {
                foreach($query->result_array() as $res)
                {

                     $activity=array();
                     $module_id                     = $res['ModuleID'];
                     $module_entity_id              = $res['ModuleEntityID'];
                     $activity_id                   = $res['ActivityID'];
                     $activity['ModuleID']          = $res['ModuleID'];
                     $activity['IsTrending']        = $res['IsTrending'];
                     $activity['IsRecommended']     = $res['IsRecommended'];
                     $activity['ModuleEntityID']    = $res['ModuleEntityID'];
                     $activity['Params']            = $res['Params'];
                     $activity['ActivityGUID']      = $res['ActivityGUID'];
                     $activity['PostContent']       = $res['PostContent'];
                     $edit_post_content             = $activity['PostContent'];
                     $activity['IsFavourite']       = (in_array($activity_id, $this->favourite_model->get_user_favourite())) ? 1 : 0;
                     $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                     $activity['PostContent']       = $this->activity_model->parse_tag($activity['PostContent']);
                     $activity['PostTitle']         = $res['PostTitle'];
                     $activity['CreatedDate']       = $res['CreatedDate'];
                     $activity['Album']             = array();
                     $activity['CanRemove']         = 0;
                     $activity['IsOwner']           = 0;
                     $activity['IsEntityOwner']     = 0;
                     $activity['PostType']          = $res['PostType'];
                     $activity['IsLike']            = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                     $activity['LikeName']          = array();
                     $activity['TotalCount']        = $res['TotalCount'];
                     $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);

                     if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                     { 
                         $activity['IsOwner'] = 1;
                         $activity['CanRemove'] = 1;
                     }

                     if($res['IsMediaExist'])
                     {
                         $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                     } 
                     if($BUsers)
                     {
                         $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                         $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                     }
                     else
                     {
                         $activity['NoOfComments'] = $res['NoOfComments']; 
                         $activity['NoOfLikes'] = $res['NoOfLikes']; 
                     }
                     if ($module_id == 1)
                     {
                         $group_details = check_group_permissions($user_id, $module_entity_id);

                         //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                         if (isset($group_details['Details']) && !empty($group_details['Details']))
                         {
                             $entity                         = $group_details['Details'];
                             $activity['EntityProfileURL']   = $module_entity_id;
                             $activity['EntityGUID']         = $entity['GroupGUID'];
                             $activity['EntityName']         = $entity['GroupName'];
                             $activity['EntityProfilePicture'] = $entity['ProfilePicture'];

                             if ($group_details['IsAdmin'])
                             {
                                 $activity['IsEntityOwner']  = 1;
                                 $activity['CanRemove']      = 1;
                             }

                         }                    
                     }
                     if ($module_id == 3)
                     {

                         $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                         if ($entity)
                         {
                             $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                             $activity['EntityName'] = $entity['EntityName'];
                             $activity['EntityGUID'] = $entity['UserGUID'];
                         }

                         $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                         if ($user_id == $module_entity_id)
                         {
                             $activity['IsEntityOwner'] = 1;
                             $activity['CanRemove'] = 1;
                         }
                     }
                     if($module_id == 34)
                     {
                         $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                         if ($entity)
                         {
                             $activity['EntityName'] = $entity['Name'];
                             $activity['EntityProfilePicture'] = $entity['MediaID'];
                             $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                             $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                         } 
                         $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);
                         if ($perm['IsAdmin'])
                         {
                             //$activity['IsOwner']        = 1;
                             $activity['CanRemove'] = 1;
                             $activity['IsEntityOwner'] = 1;
                         }

                     }
                     if($activity['NoOfComments'] > 0)
                     {
                         if($activity['IsOwner'])
                         {
                             $activity['CanRemove'] = 1;
                         }
                     }
                     $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                     $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
                    $final_array[]=$activity;
                }
                $result=$final_array;
            }
        }
        return $result;

    } 
    
    public function get_secondary_activity($user_id,$activity_id,$page_no,$page_size,$exclude_ids)
    {
        $exclude_all = $exclude_ids;
        $exclude_all[] = $activity_id;
        $this->load->model(array('polls/polls_model', 'forum/forum_model'));
        
        $blocked_users          = $this->blocked_users;
        $time_zone              = $this->user_model->get_user_time_zone();
        $friend_followers_list  = $this->user_model->get_friend_followers_list();
                
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        //$friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        //$friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        //$friend_of_friends = implode(',', $friend_of_friends);
        $event_list=array();
        $page_list=array();
        $group_list='0';
        $category_list=array(0);
        
        $group_list = $this->group_model->get_visible_group_list();            
        $group_list[] = 0;
        $group_list = implode(',', $group_list);
        $category_list = $this->forum_model->get_visible_category_list();
        $event_list = $this->event_model->get_user_joined_events();            
        $page_list = $this->page_model->get_feed_pages_condition();
        

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

                
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        $post_type=array(4);
        /* --Filter by activity type id-- */

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

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids))
        {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
            
            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
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
        if ($group_list)
        {
            
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if($category_list)
        {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
        if (!empty($page_list))
        {
            $case_array[]="A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        if (!empty($event_list))
        {
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")";
        }
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition))
        {
            $condition = $condition_part_two;
        } 

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
       
        /* Join Activity Links Ends */
     
        
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        $this->db->where('ATY.StatusID', '2');
        $this->db->where('A.ActivityID != ', $activity_id);
        $this->db->where('((A.ActivityID IN (SELECT RelatedActivity FROM '.RELATEDACTIVITY.' WHERE ActivityID IN('.implode(',',$exclude_ids).') )) OR A.ActivityID IN (SELECT ActivityID FROM '.RELATEDACTIVITY.' WHERE RelatedActivity IN('.implode(',',$exclude_ids).') ) )',null,false);

        $this->db->where_not_in('A.ActivityID',$exclude_all);

        if (!empty($condition))
        {
            $this->db->where($condition, NULL, FALSE);
        } 
        if ($privacy_condition)
        {
            $this->db->where($privacy_condition, null, false);
        }

        $this->db->order_by('rand()');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $this->db->select(implode(',', $select_array),false);
        $this->db->group_by('A.ActivityID');
        $query = $this->db->get();

        $final_array=array();
        if($query->num_rows() >0 )
        {  
            $this->load->model(array('album/album_model','subscribe_model')); 
            
           foreach($query->result_array() as $res)
           {                
                $activity=array();
                $module_id                      = $res['ModuleID'];
                $module_entity_id               = $res['ModuleEntityID'];
                $activity_id=$res['ActivityID'];
                $activity['ModuleID']           = $res['ModuleID'];
                $activity['IsTrending']         = $res['IsTrending'];
                $activity['IsRecommended']      = 1;
                $activity['IsFavourite']        = 1;
                $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                $activity['ModuleEntityID']     = $res['ModuleEntityID'];
                $activity['Params']             = $res['Params'];
                $activity['ActivityGUID']       = $res['ActivityGUID'];
                $activity['PostContent']        = $res['PostContent'];
                $edit_post_content              = $activity['PostContent'];
                $activity['PostContent']        = $this->activity_model->parse_tag($activity['PostContent']);
                $activity['PostTitle']          = $res['PostTitle'];
                $activity['CreatedDate']        = $res['CreatedDate'];
                $activity['IsLike']             = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                $activity['Album']              = array();
                $activity['CanRemove']          = 0;
                $activity['IsOwner']            = 0;
                $activity['LikeName']           = array();
                $activity['IsEntityOwner']      = 0;
                $activity['NoOfFollowers']      = $this->get_follower_count($activity_id);
                $activity['IsChecked']  = 0;
                $activity['UserName']  = $res['FirstName'].' '.$res['LastName'];
                $activity['UserGUID']  = $res['UserGUID'];
                $activity['ProfilePicture']  = $res['ProfilePicture'];
                $activity['UserProfileURL']  = get_entity_url($res['UserID'], 'User', 1);
                $activity['ActivityID']       = $res['ActivityID'];
                $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
                
                if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                { 
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                }
                
                if($res['IsMediaExist'])
                {
                    $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                } 
                if($BUsers)
                {
                    $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                }
                else
                {
                    $activity['NoOfComments'] = $res['NoOfComments']; 
                    $activity['NoOfLikes'] = $res['NoOfLikes']; 
                }
                if ($module_id == 1)
                {
                    $group_details = check_group_permissions($user_id, $module_entity_id);
                    if (isset($group_details['Details']) && !empty($group_details['Details']))
                    {
                        $entity                         = $group_details['Details'];
                        $activity['EntityProfileURL']   = $module_entity_id;
                        $activity['EntityGUID']         = $entity['GroupGUID'];
                        $activity['EntityName']         = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        
                        if ($group_details['IsAdmin'])
                        {
                            $activity['IsEntityOwner']  = 1;
                            $activity['CanRemove']      = 1;
                        }
                    
                    }                    
                }
                if ($module_id == 3)
                {

                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity)
                    {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                    if ($user_id == $module_entity_id)
                    {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
                    }
                }
                if ($module_id == 14)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                        $activity['EntityGUID'] = $entity['EventGUID'];
                    }

                    $activity['EntityProfileURL'] = $this->event_model->getViewEventUrl($entity['EventGUID'], $entity['Title'], false, 'wall');                    
                    if ($this->event_model->isEventOwner($module_entity_id, $user_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }
                    
                }
                if ($module_id == 18)
                    {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                   if ($entity)
                    {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                    }
                    if ($this->page_model->check_page_owner($user_id, $module_entity_id))
                    {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }
                    
                }

                if($module_id == 34)
                {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                    if ($entity)
                    {
                        $activity['EntityName'] = $entity['Name'];
                        $activity['EntityProfilePicture'] = $entity['MediaID'];
                        $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                        $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                    } 
                    $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);
                    if ($perm['IsAdmin'])
                    {
                        //$activity['IsOwner']        = 1;
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }

                }
                if($activity['NoOfComments'] > 0)
                {
                    if($activity['IsOwner'])
                    {
                        $activity['CanRemove'] = 1;
                    }
                }
                $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
               $final_array[]=$activity;
           }
        }
        return $final_array;
    }

    public function trending_widget($user_id, $page_no, $page_size,$activity_id=0)
    {
        $exclude_ids = array();
        $result = array();
        $result = $this->get_related_activity($user_id,$activity_id,$page_no, $page_size);
        if($result)
        {
            foreach($result as $r)
            {
                $exclude_ids[] = $r['ActivityID'];
            }
        }

        $length = count($result);
        if($length>=$page_size)
        {
            return $result;
        }

        if($exclude_ids)
        {
            $secondary_result = $this->get_secondary_activity($user_id,$activity_id,$page_no,$page_size-$length,$exclude_ids);
            if($secondary_result)
            {
                foreach($secondary_result as $sr)
                {
                    $exclude_ids = $sr['ActivityID'];
                    $result[] = $sr;
                }
            }
        }
        


        $length = count($result);
        if($length<$page_size)
        {
            $page_size = $page_size-$length;
        }
        else
        {
            return $result;
        }
        $blocked_users          = $this->blocked_users;
        $time_zone              = $this->user_model->get_user_time_zone();
        $friend_followers_list  = $this->user_model->get_friend_followers_list();
                
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        //$friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        //$friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if (!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        //$friend_of_friends = implode(',', $friend_of_friends);
        $group_list='0';
        $category_list=array(0);
        
        $group_list = $this->group_model->get_visible_group_list();
        $group_list[] = 0;

        $group_list = implode(',', $group_list);

        $category_list = $this->forum_model->get_visible_category_list();

        if (!in_array($user_id, $follow))
        {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $show_suggestions = FALSE;
        $show_media = TRUE;

     
        
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        $post_type=array(4);

        $condition = "";
        $condition_part_one = "";
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = "";
        $condition_part_four = "";
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids))
        {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
                            THEN
                                A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'";
            
            if ($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($follow)
            {
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
        if ($group_list)
        {
            
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if($category_list)
        {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
    
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition))
        {
            $condition = $condition_part_two;
        } 

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture , SUM(NoOfLikes+NoOfComments+NoOfViews) as TotalCount';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        
        /* Join Activity Links Starts */
        $this->db->select(implode(',', $select_array),false);
        /* Join Activity Links Ends */

        
        if($post_type)
        {
            $this->db->where_in('A.PostType',$post_type);
        }


        if (!empty($search_key))
        {
            $search_key = $this->db->escape_like_str($search_key);
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        if (!empty($blocked_users) )
        {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }

        $this->db->select("(SELECT COUNT(ID) FROM ".USERSACTIVITYLOG." WHERE ModuleID='19' AND ModuleEntityID=A.ActivityID AND ActivityTypeID IN(19,20,22) AND ActivityDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s')."') as Popularity",null,false);
        $this->db->where('ATY.StatusID', '2');
        $this->db->where('A.StatusID', 2);
        if($exclude_ids)
        {
            $this->db->where_not_in('A.ActivityID',$exclude_ids);
        }
        $this->db->order_by('Popularity','DESC');
        $this->db->order_by('TotalCount','DESC');
        $this->db->group_by('A.ActivityID');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
       // echo $this->db->last_query(); die;
        if($query->num_rows() >0 )
        {  
           $this->load->model(array('album/album_model','subscribe_model')); 
           $final_array=array();
           
                foreach($query->result_array() as $res)
                {

                     $activity=array();
                     $module_id                     = $res['ModuleID'];
                     $module_entity_id              = $res['ModuleEntityID'];
                     $activity_id                   = $res['ActivityID'];
                     $activity['ModuleID']          = $res['ModuleID'];
                     $activity['IsTrending']        = $res['IsTrending'];
                     $activity['IsRecommended']     = $res['IsRecommended'];
                     $activity['ModuleEntityID']    = $res['ModuleEntityID'];
                     $activity['Params']            = $res['Params'];
                     $activity['ActivityGUID']      = $res['ActivityGUID'];
                     $activity['PostContent']       = $res['PostContent'];
                     $edit_post_content             = $activity['PostContent'];
                     $activity['IsFavourite']       = (in_array($activity_id, $this->favourite_model->get_user_favourite())) ? 1 : 0;
                     $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                     $activity['PostContent']       = $this->activity_model->parse_tag($activity['PostContent']);
                     $activity['NoOfFollowers']     = $this->get_follower_count($activity_id);
                     $activity['PostTitle']         = $res['PostTitle'];
                     $activity['CreatedDate']       = $res['CreatedDate'];
                     $activity['Album']             = array();
                     $activity['CanRemove']         = 0;
                     $activity['IsOwner']           = 0;
                     $activity['IsEntityOwner']     = 0;
                     $activity['PostType']          = $res['PostType'];
                     $activity['IsLike']            = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                     $activity['LikeName']          = array();
                     $activity['TotalCount']        = $res['TotalCount'];
                     $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);

                     if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3)
                     { 
                         $activity['IsOwner'] = 1;
                         $activity['CanRemove'] = 1;
                     }

                     if($res['IsMediaExist'])
                     {
                         $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                     } 
                     if($BUsers)
                     {
                         $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                         $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                     }
                     else
                     {
                         $activity['NoOfComments'] = $res['NoOfComments']; 
                         $activity['NoOfLikes'] = $res['NoOfLikes']; 
                     }
                     if ($module_id == 1)
                     {
                         $group_details = check_group_permissions($user_id, $module_entity_id);

                         //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                         if (isset($group_details['Details']) && !empty($group_details['Details']))
                         {
                             $entity                         = $group_details['Details'];
                             $activity['EntityProfileURL']   = $module_entity_id;
                             $activity['EntityGUID']         = $entity['GroupGUID'];
                             $activity['EntityName']         = $entity['GroupName'];
                             $activity['EntityProfilePicture'] = $entity['ProfilePicture'];

                             if ($group_details['IsAdmin'])
                             {
                                 $activity['IsEntityOwner']  = 1;
                                 $activity['CanRemove']      = 1;
                             }

                         }                    
                     }
                     if ($module_id == 3)
                     {

                         $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                         if ($entity)
                         {
                             $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                             $activity['EntityName'] = $entity['EntityName'];
                             $activity['EntityGUID'] = $entity['UserGUID'];
                         }

                         $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                         if ($user_id == $module_entity_id)
                         {
                             $activity['IsEntityOwner'] = 1;
                             $activity['CanRemove'] = 1;
                         }
                     }
                     if($module_id == 34)
                     {
                         $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                         if ($entity)
                         {
                             $activity['EntityName'] = $entity['Name'];
                             $activity['EntityProfilePicture'] = $entity['MediaID'];
                             $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                             $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                         } 
                         $perm = $this->forum_model->check_forum_category_permissions($user_id, $module_entity_id);
                         if ($perm['IsAdmin'])
                         {
                             //$activity['IsOwner']        = 1;
                             $activity['CanRemove'] = 1;
                             $activity['IsEntityOwner'] = 1;
                         }

                     }
                     if($activity['NoOfComments'] > 0)
                     {
                         if($activity['IsOwner'])
                         {
                             $activity['CanRemove'] = 1;
                         }
                     }
                     $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                     $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
                    $result[]=$activity;
                }
            
        }
        return $result;

    } 

    public function recommended_article($user_id, $page_no, $page_size, $feed_sort_by, $feed_user = 0, $filter_type = 0, $is_media_exists = 2, $search_key = false, $start_date = false, $end_date = false, $show_archive = 0, $count_only = 0, $ReminderDate = array(), $activity_guid = '', $mentions = array(), $module_entity_id = '', $module_id = '', $activity_type_filter = array(), $activity_ids = array(),$view_entity_tags=1,$role_id=2,$post_type=0,$tags='',$is_exclude_ids=false,$exclude_ids=array()) {
        $result=array('Data'=>array(),'RecommendedIDs'=>array());
        $entity_id = $module_entity_id;
        $blocked_users = $this->blocked_users;
        $time_zone = $this->user_model->get_user_time_zone();
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        
        
        /*$exclude_ids = $this->fav_articles($user_id, 1, 4, 1, 0, '', 2, '', '', '', 0, 0, array(), 0,array(), $module_entity_id, $module_id, array(), array(),0,'',0,array(),true);
        if(!$exclude_ids || !is_array($exclude_ids))
        {
            $exclude_ids = array();
        }*/

        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        //$friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        //$friend_of_friends[] = 0;
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
        //$friend_of_friends = implode(',', $friend_of_friends);
        $group_list='0';
        $category_list=array(0);
        if($module_id==1) {
           $group_list=$entity_id; 
        } else if($module_id==34) {
           $category_list[]=$entity_id; 
        } else if($module_id==3) {
            $group_list = $this->group_model->get_visible_group_list();            
            $group_list[] = 0;
            $group_list = implode(',', $group_list);            
            $category_list = $this->forum_model->get_visible_category_list();
        }        

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

        $show_suggestions = FALSE;
        $show_media = TRUE;
       
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        $post_type=array(4);
        if($module_id==1) {
            $modules_allowed = array(1);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        } else if($module_id==34) {
            $modules_allowed = array(34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
            $this->db->where_in('A.ModuleEntityID',$category_list);
        }
     
        if ($filter_type == 3) {
            $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
            $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26, 30);
        }
        
         /* --Filter by activity type id-- */
        //$activity_ids = array();
        if (!empty($activity_type_filter)) {
            $activity_type_allow = $activity_type_filter;
            $show_suggestions = false;
        }

        if ($filter_type === 'Favourite' && !in_array(1, $modules_allowed)) {
            $modules_allowed[] = 1;
        }

        $condition = "";
        $condition_part_one = "";
        $condition_part_two = "A.ModuleEntityID=" . $user_id;
        $condition_part_three = "";
        $condition_part_four = "";
        $privacy_cond = ' ( ';
        $privacy_cond1 = '';
        $privacy_cond2 = '';

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids)) {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
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
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if($category_list) {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
    
        if(!empty($case_array)) {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition)) {
            $condition = $condition_part_two;
        } 

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture , SUM(NoOfLikes+NoOfComments+NoOfViews) as TotalCount';
        //$select_array[]='IF(PS.ModuleID is not null,0,IFNULL(UAR.Rank,100000)) as UARRANK';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
        $this->db->select(implode(',', $select_array),false);
        
        if($activity_guid) {
           $this->db->where('A.ActivityGUID != ',$activity_guid); 
        }
        if($module_id != '3') {
            $this->db->where('A.ModuleID',$module_id);
            $this->db->where('A.ModuleEntityID',$module_entity_id);
        }

        if($post_type) {
            $this->db->where_in('A.PostType',$post_type);
        }
        
        if ($filter_type == 7) {
            $this->db->where('A.StatusID', '19');
            $this->db->where('A.DeletedBy', $user_id);
        } else if ($filter_type == 10) {
            $this->db->where('A.StatusID', '10');
            $this->db->where('A.UserID', $user_id);
        }  else if ($filter_type == 11) {
            $this->db->where('A.IsFeatured', '1');
        } else {
            if ($filter_type == 4 && !$this->settings_model->isDisabled(43)) {
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ARCHIVEACTIVITY . " AA", "AA.ActivityID=A.ActivityID AND AA.Status='ARCHIVED' AND AA.UserID='" . $user_id . "'", "join");
                $this->db->_protect_identifiers = TRUE;
            } else if (($filter_type == 1 || $filter_type === 'Favourite') && empty($activity_ids)) {
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

            $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);

            $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        }

        if($tags) {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM ".ENTITYTAGS." WHERE EntityType='ACTIVITY' AND TagID IN (".implode(',',$tags)."))",null,false);
        }

        if ($filter_type == 2) {
            $this->db->join(FLAG . ' F', 'F.EntityID=A.ActivityID');
            $this->db->where('F.EntityType', 'Activity');
            $this->db->where('F.UserID', $user_id);
            $this->db->where('F.StatusID', '2');
        }
        if ($feed_user) {
            if(is_array($feed_user)) {
                $this->db->where_in('U.UserID', $feed_user);
            } else {
                $this->db->where('U.UserID', $feed_user);
            }
        }

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
            $this->db->join(GROUPS . ' G', 'G.GroupID=A.ModuleEntityID AND A.ModuleID = 1', 'left');            
            $this->db->join(FORUMCATEGORY . ' FC', 'FC.ForumCategoryID=A.ModuleEntityID AND A.ModuleID = 34', 'left');
            $this->db->where('( FC.Name LIKE "%' . $search_key . '%" OR G.GroupName LIKE "%' . $search_key . '%" OR  U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%" OR A.PostContent LIKE "%' . $search_key . '%" OR A.PostTitle LIKE "%' . $search_key . '%" OR A.ActivityID IN(SELECT EntityID FROM PostComments WHERE EntityType="Activity" AND PostComment LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }
        if (!empty($blocked_users)) {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }

        if($exclude_ids) {
            $this->db->where_not_in('A.ActivityID',$exclude_ids);
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
        
        $this->db->select("(SELECT COUNT(ID) FROM ".USERSACTIVITYLOG." WHERE ModuleID='19' AND ModuleEntityID=A.ActivityID AND ActivityTypeID IN(19,20,22) AND ActivityDate BETWEEN '" . get_current_date('%Y-%m-%d %H:%i:%s', 7) . "' AND '" . get_current_date('%Y-%m-%d %H:%i:%s')."') as Popularity",null,false);
        $this->db->where('ATY.StatusID', '2');
        $this->db->where_not_in('A.StatusID', array(10));
        $this->db->having('A.IsRecommended > 0 OR Popularity > 0',null,false);
        $this->db->order_by('A.IsRecommended','DESC');
        $this->db->order_by('Popularity','DESC');
       // $this->db->order_by('TotalCount','DESC');
        $this->db->group_by('A.ActivityID');
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if($query->num_rows() >0 ) {             
           $this->load->model(array('album/album_model','subscribe_model', 'activity/activity_front_helper_model')); 
           $final_array=array();
           if($is_exclude_ids) {
               $exclude_ids=array();
               foreach($query->result_array() as $res) {
                   $exclude_ids[]=$res['ActivityID'];
               }
               return $exclude_ids;
           } else {
                foreach($query->result_array() as $res) {
                     $activity=array();
                     $module_id                     = $res['ModuleID'];
                     $module_entity_id              = $res['ModuleEntityID'];
                     $activity_id                   = $res['ActivityID'];
                     $activity['ModuleID']          = $res['ModuleID'];
                     $activity['IsTrending']        = $res['IsTrending'];
                     $activity['IsRecommended']     = $res['IsRecommended'];
                     $activity['ModuleEntityID']    = $res['ModuleEntityID'];
                     $activity['Params']            = $res['Params'];
                     $activity['ActivityGUID']      = $res['ActivityGUID'];
                     $activity['PostContent']       = $res['PostContent'];
                     $edit_post_content             = $activity['PostContent'];
                     $activity['IsFavourite']       = (in_array($activity_id, $this->favourite_model->get_user_favourite())) ? 1 : 0;
                     $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                     $activity['PostContent']       = $this->activity_model->parse_tag($activity['PostContent']);
                     $activity['PostTitle']         = $res['PostTitle'];
                     $activity['CreatedDate']       = $res['CreatedDate'];
                     $activity['Album']=array();
                     $activity['CanRemove'] = 0;
                     $activity['IsOwner'] = 0;
                     $activity['IsEntityOwner']  = 0;
                     $activity['PostType']           = $res['PostType'];
                     $activity['ArticleSummary']           = !empty($res['Summary']) ? $res['Summary'] : $res['PostSearchContent'];                
                     $activity['LikeName'] = array();
                     $activity['IsLike'] = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                     $activity['TotalCount']  = $res['TotalCount'];
                     $activity['NoOfFollowers']  = $this->get_follower_count($activity_id);
                     $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);

                     if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3) { 
                         $activity['IsOwner'] = 1;
                         $activity['CanRemove'] = 1;
                     }

                     if($res['IsMediaExist']) {
                         $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                     } 
                     if($BUsers) {
                         $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                         $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                     } else {
                         $activity['NoOfComments'] = $res['NoOfComments']; 
                         $activity['NoOfLikes'] = $res['NoOfLikes']; 
                     }
                     if ($module_id == 1) {
                         $group_details = check_group_permissions($user_id, $module_entity_id);
                         if (isset($group_details['Details']) && !empty($group_details['Details'])) {
                             $entity                         = $group_details['Details'];
                             $activity['EntityProfileURL']   = $module_entity_id;
                             $activity['EntityGUID']         = $entity['GroupGUID'];
                             $activity['EntityName']         = $entity['GroupName'];
                             $activity['EntityProfilePicture'] = $entity['ProfilePicture'];

                             if ($group_details['IsAdmin']) {
                                 $activity['IsEntityOwner']  = 1;
                                 $activity['CanRemove']      = 1;
                             }
                         }                    
                     }
                     if ($module_id == 3) {
                         $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                         if ($entity) {
                             $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                             $activity['EntityName'] = $entity['EntityName'];
                             $activity['EntityGUID'] = $entity['UserGUID'];
                         }

                         $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                         if ($user_id == $module_entity_id) {
                             $activity['IsEntityOwner'] = 1;
                             $activity['CanRemove'] = 1;
                         }
                     }
                     if($module_id == 34) {
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
                             $activity['IsEntityOwner'] = 1;
                         }
                     }
                     if($activity['NoOfComments'] > 0) {
                         if($activity['IsOwner']) {
                             $activity['CanRemove'] = 1;
                         }
                     }
                     $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                     $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);                     
                     $activity['MembersTalking'] = $this->activity_front_helper_model->get_members_talking($activity_id);
                     
                    $final_array[]=$activity;
                    $exclude_ids[]=$activity_id;
                }                
                $result['Data']=$final_array;
                $result['RecommendedIDs']=$exclude_ids;
            }
        }
        return $result;
    }    
    
    public function get_friend_likes($user_id,$activity_id,$friend_followers_list) {
        $data = array();
        $this->db->select("COUNT(U.UserID) as TotalFriends");
        $this->db->from(USERS.' U');
        $this->db->join(USERSACTIVITYLOG.' UAL','UAL.UserID=U.UserID','join');
        $this->db->where('UAL.ModuleEntityID',$activity_id);
        $this->db->where('UAL.ModuleID',19);
        $this->db->where('UAL.ActivityTypeID',19);
        $this->db->where("UAL.UserID IN(".$friend_followers_list.")");
        $this->db->group_by('UAL.UserID');
        $query = $this->db->get();
        $total_records = $query->num_rows();

        $this->db->select("U.FirstName,U.LastName,U.UserGUID");
        $this->db->from(USERS.' U');
        $this->db->join(USERSACTIVITYLOG.' UAL','UAL.UserID=U.UserID','join');
        $this->db->where('UAL.ModuleEntityID',$activity_id);
        $this->db->where('UAL.ModuleID',19);
        $this->db->where('UAL.ActivityTypeID',19);
        $this->db->where("UAL.UserID IN(".$friend_followers_list.")");
        $this->db->limit(3);
        $this->db->group_by('UAL.UserID');
        $query = $this->db->get();
        if($query->num_rows()) {
            foreach($query->result_array() as $r) {
                $r['TotalFriends'] = $total_records;
                $data[] = $r;
            }
            if($total_records == 0) {
                $data = array();
            }
        }
        return $data;
    }

    public function remove_articles($user_id,$articles) {
        $this->db->set('StatusID','3');
        $this->db->where_in('ActivityGUID',$articles);
        $this->db->update(ACTIVITY);
    }

    public function recommend_articles($user_id,$articles) {
        $this->db->set('IsRecommended','1');
        $this->db->where_in('ActivityGUID',$articles);
        $this->db->update(ACTIVITY);
    }
    
    function get_follower_count($activity_id) {
        $this->db->select('COUNT(SubscribeID) as TotalCount');
        $this->db->from(SUBSCRIBE);
        $this->db->where('EntityID',$activity_id);
        $this->db->where('EntityType','ACTIVITY');
        $sql = $this->db->get();
        $count_data=$sql->row_array();
        return $count_data['TotalCount'];
    }

    function remove_recommended($user_id,$articles) {
        $this->db->set('IsRecommended','0');
        $this->db->where_in('ActivityGUID',$articles);
        $this->db->update(ACTIVITY);
    }
    
    function related_activity($activity_id,$related_activity) {
        $this->db->where('ActivityID',$activity_id);
        $this->db->delete(RELATEDACTIVITY);

        $this->db->where('RelatedActivity',$activity_id);
        $this->db->delete(RELATEDACTIVITY);
        if(!empty($related_activity)) {
            $insert_data=array();
            foreach($related_activity as $activity) {
                $insert_data[] = array('ActivityID'=>$activity_id,'RelatedActivity'=>$activity, 'CreatedDate'=> get_current_date('%Y-%m-%d %H:%i:%s'));
            }
            $this->db->insert_batch(RELATEDACTIVITY,$insert_data);
        }
    }
    
    function get_related_activity($user_id,$activity_id,$page_no, $page_size,$count_only=false) {
        $this->load->model(array('polls/polls_model', 'forum/forum_model'));
        
        $blocked_users          = $this->blocked_users;
        $time_zone              = $this->user_model->get_user_time_zone();
        $friend_followers_list  = $this->user_model->get_friend_followers_list();
                
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();

        //$friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $follow[] = 0;
        //$friend_of_friends[] = 0;
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
        //$friend_of_friends = implode(',', $friend_of_friends);
        $event_list=array();
        $page_list=array();
        $group_list='0';
        $category_list=array(0);
        
        $group_list = $this->group_model->get_visible_group_list();            
        $group_list[] = 0;
        $group_list = implode(',', $group_list);
        $category_list = $this->forum_model->get_visible_category_list();
        $event_list = $this->event_model->get_user_joined_events();            
        $page_list = $this->page_model->get_feed_pages_condition();
        

        if (!in_array($user_id, $follow)) {
            $follow[] = $user_id;
        }

        if (!in_array($user_id, $friends)) {
            $friends[] = $user_id;
        }

                
        $modules_allowed = array(1, 3, 14, 18, 23, 27, 30, 34);
        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26);
        $post_type=array(4);
        /* --Filter by activity type id-- */

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

        $case_array=array();
        if ($friend_followers_list != '' && empty($activity_ids)) {
            $case_array[]="A.ActivityTypeID IN (1,5,6,25) 
                            OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=3)  
                            THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
            $case_array[]="A.ActivityTypeID=2
                            THEN    
                                (A.UserID IN(" . implode(',', $only_friend_followers) . ") OR A.ModuleEntityID IN(" . implode(',', $only_friend_followers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')";
            
            $case_array[]="A.ActivityTypeID=3
                            THEN
                                A.UserID IN(" . implode(',', $only_friend_followers) . ") AND A.UserID!='" . $user_id . "'";
            
            $case_array[]="A.ActivityTypeID IN (9,10,14,15) 
                            THEN
                                (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $condition_part_two . "";
            
            $case_array[]="A.ActivityTypeID=8
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
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $group_list . ") ";
        }
        if($category_list) {
            $case_array[] = " A.ActivityTypeID=26 
                                THEN 
                                A.ModuleID=34 AND A.ModuleEntityID IN (".implode(',',$category_list).") 
                            ";
        }
        if (!empty($page_list)) {
            $case_array[]="A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        if (!empty($event_list)) {
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $event_list . ")";
        }
        if(!empty($case_array)) {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE '' END ) ";
        }
        if (empty($condition)) {
            $condition = $condition_part_two;
        } 

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        $condition .= " AND ((CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        $select_array=array();
        $select_array[]='A.*,ATY.ViewTemplate, ATY.Template, ATY.LikeAllowed, ATY.CommentsAllowed, ATY.ActivityType, ATY.ActivityTypeID, ATY.FlagAllowed, ATY.ShareAllowed, ATY.FavouriteAllowed, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture';

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
       
        /* Join Activity Links Ends */
        if (!empty($blocked_users)) {
            $this->db->where_not_in('A.UserID', $blocked_users);
        }
        $this->db->where('ATY.StatusID', '2');
        $this->db->where('A.ActivityID != ', $activity_id);
        $this->db->where('((A.ActivityID IN (SELECT RelatedActivity FROM '.RELATEDACTIVITY.' WHERE ActivityID='.$activity_id.' )) OR A.ActivityID IN (SELECT ActivityID FROM '.RELATEDACTIVITY.' WHERE RelatedActivity='.$activity_id.' ) )',null,false);

        if (!empty($condition)) {
            $this->db->where($condition, NULL, FALSE);
        } 
        if ($privacy_condition) {
            $this->db->where($privacy_condition, null, false);
        }

        $this->db->order_by('rand()');
        if (!$count_only) {
            if($page_size > 0) {
                //$this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
        }
        if ($count_only) {
            $this->db->select('COUNT(DISTINCT A.ActivityID) as TotalRow ' );
            $query = $this->db->get();
            $count_data=$query->row_array();
            return $count_data['TotalRow'];
        } else {
            $this->db->select(implode(',', $select_array),false);
            $this->db->group_by('A.ActivityID');
            $query = $this->db->get();
        }
        $final_array=array();
        if($query->num_rows() >0 ) {  
            $this->load->model(array('album/album_model','subscribe_model'));             
           foreach($query->result_array() as $res) {                
                $activity=array();
                $module_id                      = $res['ModuleID'];
                $module_entity_id               = $res['ModuleEntityID'];
                $activity_id=$res['ActivityID'];
                $activity['ModuleID']           = $res['ModuleID'];
                $activity['IsTrending']         = $res['IsTrending'];
                $activity['IsRecommended']      = 1;
                $activity['IsFavourite']        = 1;
                $activity['IsPined']            = ($res['IsVisible']=='3') ? 1 : 0 ;
                $activity['ModuleEntityID']     = $res['ModuleEntityID'];
                $activity['Params']             = $res['Params'];
                $activity['ActivityGUID']       = $res['ActivityGUID'];
                $activity['PostContent']        = $res['PostContent'];
                $edit_post_content              = $activity['PostContent'];
                $activity['PostContent']        = $this->activity_model->parse_tag($activity['PostContent']);
                $activity['PostTitle']          = $res['PostTitle'];
                $activity['CreatedDate']        = $res['CreatedDate'];
                $activity['IsLike']             = $this->is_liked($activity_id, 'Activity', $user_id, 3, $user_id);
                $activity['Album']              = array();
                $activity['CanRemove']          = 0;
                $activity['IsOwner']            = 0;
                $activity['LikeName']           = array();
                $activity['IsEntityOwner']      = 0;
                $activity['NoOfFollowers']      = $this->get_follower_count($activity_id);
                $activity['IsChecked']  = 0;
                $activity['UserName']  = $res['FirstName'].' '.$res['LastName'];
                $activity['UserGUID']  = $res['UserGUID'];
                $activity['ProfilePicture']  = $res['ProfilePicture'];
                $activity['UserProfileURL']  = get_entity_url($res['UserID'], 'User', 1);
                $activity['ActivityID']       = $res['ActivityID'];
                $activity['PostType']           = $res['PostType'];
                $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
                
                if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3) { 
                    $activity['IsOwner'] = 1;
                    $activity['CanRemove'] = 1;
                }
                
                if($res['IsMediaExist']) {
                    $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity',1);
                } 
                if($BUsers) {
                    $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count('Activity', $activity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, 'ACTIVITY', $BUsers); //
                } else {
                    $activity['NoOfComments'] = $res['NoOfComments']; 
                    $activity['NoOfLikes'] = $res['NoOfLikes']; 
                }
                if ($module_id == 1) {
                    $group_details = check_group_permissions($user_id, $module_entity_id);
                    if (isset($group_details['Details']) && !empty($group_details['Details'])) {
                        $entity                         = $group_details['Details'];
                        $activity['EntityProfileURL']   = $module_entity_id;
                        $activity['EntityGUID']         = $entity['GroupGUID'];
                        $activity['EntityName']         = $entity['GroupName'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        
                        if ($group_details['IsAdmin']) {
                            $activity['IsEntityOwner']  = 1;
                            $activity['CanRemove']      = 1;
                        }
                    
                    }                    
                }
                if ($module_id == 3) {
                    $entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                    if ($entity) {
                        $entity['EntityName']=  trim($entity['FirstName'].' '.$entity['LastName']);
                        $activity['EntityName'] = $entity['EntityName'];
                        $activity['EntityGUID'] = $entity['UserGUID'];
                    }

                    $activity['EntityProfileURL'] = get_entity_url($res['ModuleEntityID'], 'User', 1);
                    if ($user_id == $module_entity_id) {
                        $activity['IsEntityOwner'] = 1;
                        $activity['CanRemove'] = 1;
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
                    }                    
                }
                if ($module_id == 18) {
                    $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                    if ($entity) {
                        $activity['EntityName'] = $entity['Title'];
                        $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                        $activity['EntityProfileURL'] = $entity['PageURL'];
                        $activity['EntityGUID'] = $entity['PageGUID'];
                    }
                    if ($this->page_model->check_page_owner($user_id, $module_entity_id)) {
                        $activity['CanRemove'] = 1;
                        $activity['IsEntityOwner'] = 1;
                    }                    
                }

                if($module_id == 34) {
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
                        $activity['IsEntityOwner'] = 1;
                    }
                }
                if($activity['NoOfComments'] > 0) {
                    if($activity['IsOwner']) {
                        $activity['CanRemove'] = 1;
                    }
                }
                $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                $activity['ActivityURL']=get_single_post_url($activity,$res['ActivityID'],$res['ActivityTypeID'],$res['ModuleEntityID']);
               $final_array[]=$activity;
           }
        }
        return $final_array;
    }
    
    function entity_suggestion($search_keyword) {
        $group_list = $this->group_model->get_visible_group_list();
        $category_group_list = $this->group_model->get_user_categoty_group_list();
        
        $search_keyword = $this->db->escape_like_str($search_keyword);
        
        $sql = ' SELECT F.ForumGUID AS ModuleEntityGUID, F.ForumID AS ModuleEntityID,F.Name as Name ,33 AS ModuleID FROM '.FORUM.' as F WHERE F.StatusID=2  ';
        $sql .= " AND F.Name LIKE '%" . $search_keyword . "%' ";
        $sql .= ' UNION ALL';
        $sql .= ' SELECT G.GroupGUID AS ModuleEntityGUID, G.GroupID AS ModuleEntityID,G.GroupName as Name,1 AS ModuleID FROM '.GROUPS.' as G WHERE G.Type="FORMAL" AND G.StatusID=2 ';
        $sql .= " AND G.GroupName LIKE '%" . $search_keyword . "%' ";
        if (!empty($group_list)) {
                $sql .= " AND G.GroupID IN(" . implode(',', $group_list) . ")";
        }
        $sql .= " UNION ALL";
        $sql .= ' SELECT FC.ForumCategoryGUID AS ModuleEntityGUID, FC.ForumCategoryID AS ModuleEntityID,FC.Name as Name ,34 AS ModuleID FROM '.FORUMCATEGORY.' as FC WHERE FC.StatusID=2  ';
        if (!empty($category_group_list)) {
                $sql .= " AND F.ForumCategoryID IN(" . implode(',', $category_group_list) . ")";        
        }
        $sql .= " AND FC.Name LIKE '%" . $search_keyword . "%' ";
        $sql .= " ORDER BY Name ASC";
        $query = $this->db->query($sql);
        //echo $this->db->last_query(); die;
        return $query->result_array();
    }

    public function get_total_module_posts($module_id,$module_entity_id) {
        $this->db->select("COUNT(ActivityID) as TotalPosts",false);
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleID',$module_id);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->where('StatusID','2');
        if($module_id == 1) {
            $this->db->where('ActivityTypeID','7');
        }
        $query = $this->db->get();
        if($query->num_rows()) {
            return $query->row()->TotalPosts;
        }
    }


    /**
     * Function Name: get_welcome_question
     * Description: get welcome Question
     * @param int $user_id 
     * @param int $activity_rule_id 
     * @return Array
     */
    public function get_welcome_question($user_id,$activity_rule_id) {
        $activity = array();
        $this->db->select('ActivityID');
        $this->db->where('ActivityRuleID',$activity_rule_id);
        $this->db->order_by('DisplayOrder ASC');
        $query = $this->db->get(RULEQUESIONS);
        if($query->num_rows()) {
            foreach ($query->result_array() as $question) {
                if(!$this->check_user_commented_on_activity($question['ActivityID'],$user_id)) {
                    //get Activity details
                    $activity_details = get_detail_by_id($question['ActivityID'],0,'ActivityGUID,ModuleID,ModuleEntityID',2);                    
                    $activity = $this->getActivities($activity_details['ModuleEntityID'], $activity_details['ModuleID'], 1, 1, $user_id, 2,0,2,$activity_details['ActivityGUID'],false, false, false,0, 0, false, 'ALL', array(), '', '', '','',2,0,''); 
                    if(!empty($activity)) {
                        break;    
                    }                    
                }
            }
        }
        return $activity;
    }

    /**
     *  Function Name: get_welcome_questions
     * Description: get details of welcome Questions
     * @param int $activity_rule_id 
     * @return Array
     */
    public function check_user_commented_on_activity($activity_id,$user_id) {
        $this->db->select('PostCommentID');
        $this->db->where('EntityType','ACTIVITY')    ;
        $this->db->where('EntityID',$activity_id);
        $this->db->where('UserID',$user_id);
        $query = $this->db->get(POSTCOMMENTS);
        return $query->num_rows();
    }

    public function activity_recent_participants($activity_id,$user_id) {
        $result['Data']=array();
        $result['TotalRecords']=0;
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS Name,U.UserGUID,PU.Url as ProfileUrl');
        $this->db->from(ACTIVITY.' A');
        $this->db->join(POSTCOMMENTS.' PC','PC.EntityID=A.ActivityID AND A.StatusID=2');
        $this->db->join(USERS . ' U', 'PC.UserID = U.UserID');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('A.ActivityID',$activity_id);
        if(!empty($friends)) {
            $this->db->where_in('U.UserID',$friends);
        }
        $this->db->group_by('PC.UserID');
        $this->db->order_by('PC.CreatedDate','DESC');
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result['TotalRecords'] = $temp_q->num_rows();
        $this->db->limit(4);
        $query = $this->db->get();
        $result['Data'] =$query->result_array();
        return $result;
    }

    /**
     * [is_anyone_mentioned Used to check if anyone is mentioned in post content]
     * @param  [string] $activity_id [activity id]
     * @return [string]               [Parsed Post Content]
     */
    function is_anyone_mentioned($activity_id,$post_content=false) { 
        $tagged_count = 0;
        $this->db->select('MentionID');
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('StatusID', 2);
        $query = $this->db->get(MENTION);
        if($query->num_rows()) {
            $tagged_count = $query->num_rows();
        }
        return $tagged_count;
    }

    function get_allowed_activity_type($type,$module_id,$filter='',$enable=1) {
        $visual_post_disbaled = $this->settings_model->isDisabled(37);
        $contest_disbaled = $this->settings_model->isDisabled(36);
        $event_disbaled = $this->settings_model->isDisabled(14);
        $activity_type_allow = array();
        if($type == 'wall') {
            if($module_id == '1') {
                $activity_type_allow = array(7, 5, 6, 25); //23, 24,
            } else if($module_id == '3' || $module_id == '19') {
                $activity_type_allow = array(1, 8, 9, 10, 5, 6, 14, 15, 25, 7, 11, 12, 26, 36, 37, 38, 39); //23, 24,
            } else if($module_id == '14') {
                $activity_type_allow = array(11, 5, 6);// 23, 24
            } else if($module_id == '18') {
                $activity_type_allow = array(1, 8, 12, 5, 6, 25);//23, 24,
            } else if($module_id == '34') {
                $activity_type_allow = array(26);
            } else {
                $activity_type_allow = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 25, 36, 37, 38, 39);//23, 24,
            }
        } else if($type == 'newsfeed') {
            if($filter == '3') {
                $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26, 30, 36, 37, 38, 39);//23, 24,
            } else {
                $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 25, 26, 36, 37, 38, 39);//, 23, 24,
            }
        } else if($type == 'public') {
            if($module_id == '3') {
                $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 25, 26, 30, 36, 37, 38, 39);//23, 24,
            } else {
                $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 25, 26, 36, 37, 38, 39);//23, 24,
            }

            if($module_id == 1) {
                $activity_type_allow = array(2,3,4,13,19,20,14,16,17);
            } else {
                $activity_type_allow = array(2,3,4,13,19,20,14,16,17);
            }
        }
        
        if($visual_post_disbaled) {
            if($enable) {
                $activity_type_allow = array_diff($activity_type_allow,array(36));
            } else {
                $activity_type_allow[] = 36;   
            }
        }

        if($contest_disbaled) {
            if($enable) {
                $activity_type_allow = array_diff($activity_type_allow,array(37,39));
            } else {
                $activity_type_allow[] = 37;   
                $activity_type_allow[] = 39;   
            }
        }

        if($event_disbaled) {
            if($enable) {
                $activity_type_allow = array_diff($activity_type_allow,array(11,30));
            } else {
                $activity_type_allow[] = 11;   
                $activity_type_allow[] = 30;   
            }
        }
        
        // In case of mobile app exclude contest and visual posts
        if ($this->IsApp == 1) {
            //$exclude_activity_types = [25, 36, 37, 38, 39];            
            $exclude_activity_types = [25];            
            $activity_type_allow = array_diff($activity_type_allow, $exclude_activity_types);
        }        

        return $activity_type_allow;
    }

    public function is_valid_activity_type($activity_type_id) {
        $r = true;
        if($activity_type_id == '36') {
            if($this->settings_model->isDisabled(37)) {
                $r = false;
            }
        } else if($activity_type_id == '11' || $activity_type_id == '30') {
            if($this->settings_model->isDisabled(14)) {
                $r = false;
            }
        } else if($activity_type_id == '37' || $activity_type_id == '39') {
            if($this->settings_model->isDisabled(36)) {
                $r = false;
            }
        }
        return $r;
    }

    /**
     * 
     * @param type $activity_id
     * @param type $user_id
     */
    function group_recent_comments_owner($activity_id,$user_id)
    {
        $result['Data']=array();
        $result['TotalRecords']=0;
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $friends[]=0;
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS Name,U.ProfilePicture,U.UserGUID,PU.Url as ProfileUrl');
        $this->db->from(POSTCOMMENTS.' PC');
        $this->db->join(USERS . ' U', 'PC.UserID = U.UserID');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('PC.EntityType','Activity');
        $this->db->where('PC.EntityID',$activity_id);
        $this->db->group_by('PC.UserID');
        $this->db->order_by("CASE WHEN U.UserID IN (". implode(',', $friends).") THEN 1 ELSE 0 END DESC");
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result['TotalRecords'] = $temp_q->num_rows();
        $this->db->limit(4);
        $query = $this->db->get();
        $result['Data'] =$query->result_array();
        return $result;
    }

    /**
     * 
     * @param type $activity_guid
     */
    function get_basic_post_details($activity_guid)
    {
        $data = array();
        $this->db->select('A.ActivityTypeID,A.PostType,A.PostTitle,A.PostSearchContent,A.ActivityID');
        $this->db->from(ACTIVITY.' A');
        $this->db->where('A.ActivityGUID',$activity_guid);
        $query = $this->db->get();
        if($query->num_rows())
        {
            $this->load->model('tag_model');
            $data = $query->row_array();
            $data['Tags'] = array();
            $tags = $this->tag_model->get_entity_tags('', 1, '', 1, 'ACTIVITY', $data['ActivityID']);
            if($tags)
            {
                foreach($tags as $tag)
                {
                    $data['Tags'][] = $tag['Name'];
                }
                $data['Tags'] = implode(',',$data['Tags']);
            }
            else
            {
                $data['Tags'] = '';
            }
        }
        return  $data;
    }

    function update_user_post_count($user_id, $count=1) {
        $set_field = 'PostCount';
        $this->db->where('UserID', $user_id);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update(USERDETAILS);
    }

    function update_user_comment_count($user_id, $count=1) {
        $set_field = 'CommentCount';
        $this->db->where('UserID', $user_id);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update(USERDETAILS);
    }

    function update_user_like_recieved_count($user_id, $count=1) {
        $set_field = 'LikeRecievedCount';
        $this->db->where('UserID', $user_id);
        $this->db->where('LikeRecievedCount >=', 0);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update(USERDETAILS);
    }
}
