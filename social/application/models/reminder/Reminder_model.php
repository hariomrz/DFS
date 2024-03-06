<?php
/**
 * This model is used for making any entity as Reminder
 * Model class for to make any entity as Reminder 
 * @package    reminder_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Reminder_model extends Common_Model 
{
    protected $allowed_status = array("ACTIVE", "ARCHIVED", "DELETED");
    function __construct() {
        parent::__construct();        
    }

    /**
     * [add Used to mark an entity as Reminder for current session user]
     * @param [type] $data [input data for Reminder Request]
     */
    function add($data)
    { 
        $return['Message']      = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data']         = array();

        $created_date       = get_current_date('%Y-%m-%d %H:%i:%s');

        $user_id            = $data['UserID'];
        $activity_guid      = $data['ActivityGUID'];
        $date_time          = date('Y-m-d H:i:s',strtotime($data['ReminderDateTime']));
        $status             = (isset($data['Status']) && !empty($data['Status'])) ? strtoupper($data['Status']) : 'ACTIVE' ;        
        if(!in_array($status, $this->allowed_status))
        {
            $status         = "ACTIVE";
        }
        
        if(strtotime($created_date) > strtotime($date_time))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = 'Please check time and set reminder again.';
            return $return;
        }

        /* get EntityId and ModuleEntityID by Entity GuID & Module Entity GUID*/
        $activity_id       = get_detail_by_guid($activity_guid);        
        if(empty($activity_id)){
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "activity GUID");
            return $return;
        }

        $this->load->model('timezone/timezone_model');
        $time_zone = $this->timezone_model->getUserTimeZone($user_id);
        $reminder_date_time    = $this->timezone_model->convert_date_to_time_zone($date_time, $time_zone, 'UTC');

        /* End get ReminderID */
        $this->db->select('ReminderID, Status,ReminderGUID');        
        $this->db->where(
                        array(
                            'UserID'        => $user_id,
                            'ActivityID'    => $activity_id
                        )
                    );    
        
        
        $query = $this->db->get(REMINDER);
        if($query->num_rows() > 0) 
        {            
            $row = $query->row_array();
            $this->db->where('ReminderID',$row['ReminderID'])
                    ->update(REMINDER, array('ReminderDateTime' => $reminder_date_time, 'Status' => $status, 'ModifiedDate' => $created_date));
            $return['Data']['ReminderGUID'] = $row['ReminderGUID'];
            $return['Data']['ReminderDateTime'] = $reminder_date_time;
        } 
        else 
        {
            $reminder_guid = get_guid();
            $input = array(
                            'ReminderGUID'      => $reminder_guid, 
                            'UserID'            => $user_id, 
                            'ActivityID'        => $activity_id, 
                            'ReminderDateTime'  => $reminder_date_time, 
                            'Status'            => $status, 
                            'CreatedDate'       => $created_date, 
                            'ModifiedDate'      => $created_date
                        );            
            $this->db->insert(REMINDER, $input);
            $return['Data']['ReminderGUID'] = $reminder_guid;
            $return['Data']['ReminderDateTime'] = $reminder_date_time;
        }  
        //on reminder add/update UNDONE Mydesk task
        $this->load->model('activity/mydesk_model');
        $this->mydesk_model->toggle_mydesk_task($activity_id,$user_id,'UNDONE');
              
        return $return;
    }

    /**
     * [edit Used to update Reminder]
     * @param [type] $data [input data for Reminder Request]
     */
    function edit($data)
    { 
        $return['Message']      = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data']         = array();

        $created_date       = get_current_date('%Y-%m-%d %H:%i:%s'); 
        $user_id            = $data['UserID'];       
        $status             = (isset($data['Status']) && !empty($data['Status'])) ? strtoupper($data['Status']) : 'ACTIVE' ;        
        if(!in_array($status, $this->allowed_status))
        {
            $status         = "ACTIVE";
        }
        $date_time          = $data['ReminderDateTime'];
        $reminder_guid      = $data['ReminderGUID'];
        $old_status         = "";

        if(strtotime($created_date) > strtotime($date_time))
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = 'Please check time and set reminder again.';
            return $return;
        }

        /* End get ReminderID */
        $this->db->select('ReminderID, Status');
        $this->db->where(
                            array(
                                'ReminderGUID'   => $reminder_guid
                            )
                        );        
        
        $query = $this->db->get(REMINDER);
        if($query->num_rows() > 0) 
        {            
            $this->load->model('timezone/timezone_model');
            $time_zone = $this->timezone_model->getUserTimeZone($user_id);
            $reminder_date_time    = $this->timezone_model->convert_date_to_time_zone($date_time, $time_zone, 'UTC');

            $row = $query->row_array();
            $old_status = $row['Status'];
            $this->db->where('ReminderID',$row['ReminderID'])
                    ->update(REMINDER, array('ReminderDateTime' => $reminder_date_time, 'Status' => $status, 'ModifiedDate' => $created_date));
            $return['Data'] = array("Status" => $old_status);
            $return['Data']['ReminderDateTime'] = $reminder_date_time;
        } 
        else 
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "reminder GUID");
            return $return; 
        }        
        return $return;
    }

    /**
     * [details Used to get reminder details]
     * @param  [String] $reminder_guid [Reminder GUID]
     * @return [array]          [Reminder details]
     */
    function details($reminder_guid)
    {
        $return['Message']      = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data']         = array();
        $this->db->select('ReminderGUID, ActivityID, ReminderDateTime, Status');
        $this->db->where(
                            array(
                                'ReminderGUID'        => $reminder_guid
                            )
                        );
        $query = $this->db->get(REMINDER);
        if($query->num_rows() > 0) 
        {
            $row = $query->row_array();
            $activity_id = $row['ActivityID'];
            $activity_guid = get_detail_by_id($activity_id, 0, 'ActivityGUID');
            $return['Data'] = array(
                            'ReminderGUID'      => $row['ReminderGUID'],
                            'ActivityGUID'      => $activity_guid, 
                            'ReminderDateTime'  => $row['ReminderDateTime'], 
                            'Status'            => $row['Status']
                        ); 
        }
        else 
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "reminder GUID");             
        }
        return $return;          
    }

    /**
     * [get_reminder_count_by_date Used to get the remider count by date wise]
     * @param  [int]    $user_id     [User ID]
     * @return [array]               [Array of reminder count with date]
     */
    function get_reminder_count_by_date($user_id)
    {
                
        // Set privacy condition
        $privacy_condition_arr = $this->privacy_condition(
            $user_id
        );
        $condition = $privacy_condition_arr['condition']; 
        $privacy_condition = $privacy_condition_arr['privacy_condition'];
                
        
        $this->db->select('A.ActivityGUID');
        $this->db->select("COUNT(R.ReminderID) as Count, DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%Y-%m-%d') as ReminderDate",FALSE);

        $this->db->from(ACTIVITY.' A');        
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(REMINDER." R","R.ActivityID=A.ActivityID AND R.UserID='".$user_id."' AND R.Status!='DELETED'",'JOIN');
        $this->db->join(USERDETAILS.' UD','UD.UserID=R.UserID','left');

        if(!empty($condition)) 
        {
            $this->db->where($condition,NULL,FALSE);    
        } 
        else 
        {
            $this->db->where('A.ModuleID','3');
            $this->db->where('A.ModuleEntityID',$user_id);
        }
        if($privacy_condition)
        {
            $this->db->where($privacy_condition,null,false);
        }

        $this->db->where('A.StatusID','2');

        $this->db->group_by('ReminderDate');
        $this->db->_protect_identifiers = TRUE;        
        $query = $this->db->get(); 
        //echo $this->db->last_query(); die;      

        if($query->num_rows())
        {
            return $query->result_array();
        } 
        else 
        {
            return array();
        }
    }
    
    protected function privacy_condition($user_id) {
        $activity_ids = [];
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
        ));
 
        $this->load->model(array('polls/polls_model', 'category/category_model', 'forum/forum_model'));
        $this->load->model('sticky/sticky_model');
        
        
        
        $this->user_model->set_friend_followers_list($user_id);
        $this->group_model->set_user_group_list($user_id);
        $this->forum_model->set_user_category_list($user_id);
        $this->group_model->set_user_categoty_group_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);            
        $this->flag_model->set_user_flagged($user_id); 
        $this->activity_model->set_user_activity_archive($user_id); 
        
        
        $exclude_ids = $this->activity_model->get_newsfeed_announcements($user_id, true);
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

        $activity_type_allow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 23, 24, 25, 26);
        $modules_allowed = array(3, 30, 34);
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
                            $this->show_suggestions = true;
                        }
                    }
                }
            }
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
            $case_array[] = "A.ActivityTypeID IN (1,5,6,25,23,24) AND A.ModuleID=3
                                THEN 
                                A.UserID IN(" . $friend_followers_list . ") 
                                OR A.ModuleEntityID IN(" . $friend_followers_list . ") 
                                OR " . $condition_part_two . " ";
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
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END)  ";
        $condition .= " OR ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null)";
        $condition .= ")";
        
        return array(
            'condition' => $condition,
            'privacy_condition' => $privacy_condition,
        );
    }
    
    function get_reminder_count_by_date_old($user_id)
    {
        $this->load->model(array('users/user_model', 'group/group_model', 'events/event_model','pages/page_model'));

        $friend_followers_list  = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friends                = $friend_followers_list['Friends'];
        $follow                 = $friend_followers_list['Follow'];
        $friends[]              = 0;
        $follow[]               = 0;
        $friend_followers_list    = array_unique(array_merge($friends,$follow));
        $friend_followers_list[]  = 0;
        if(!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $only_friend_followers = $friend_followers_list;
        if(in_array($user_id, $friend_followers_list))
        {
            unset($only_friend_followers[$user_id]);
            if(!$only_friend_followers)
            {
                $only_friend_followers[] = 0;
            }
        }
        $friend_followers_list    = implode(',', $friend_followers_list);
        $group_list              = $this->group_model->get_joined_groups($user_id, false, array(2));
        $event_list              = $this->event_model->get_all_joined_events($user_id);
        $page_list               = $this->page_model->get_liked_pages_list($user_id);

        if(!in_array($user_id,$follow))
        {
            $follow[] = $user_id;
        }

        if(!in_array($user_id,$friends))
        {
            $friends[] = $user_id;
        }

        //Activity Type 1 for followers, friends and current user
        //Activity Type 2 for followers and friends only
        //Activity Type 3 for follower and friend of user_id
        //Activity Type 8, 9, 10 for Mutual Friends Only
        //Activity Type 4, 7 for Group Members Only
        $condition              = "";
        $condition_part_one       = "";
        $condition_part_two       = "A.ModuleEntityID=".$user_id;
        $condition_part_three     = "";
        $condition_part_four      = "";
        $privacy_cond            = ' ( ';
        $privacy_cond1           = '';
        $privacy_cond2           = '';
        if($friend_followers_list!='') 
        {
            $condition = "(
                IF(A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6, (
                    A.UserID IN(".$friend_followers_list.") OR A.ModuleEntityID IN(".$friend_followers_list.") OR ".$condition_part_two."
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(".implode(',', $only_friend_followers).") OR A.ModuleEntityID IN(".implode(',', $only_friend_followers).")) AND (A.UserID!='".$user_id."' OR A.ModuleEntityID!='".$user_id."')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(".implode(',', $only_friend_followers).") AND A.UserID!='".$user_id."'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (
                    (A.UserID IN(".$friend_followers_list.") AND A.ModuleEntityID IN(".$friend_followers_list.")) OR ".$condition_part_two."
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (
                    A.UserID='".$user_id."' OR A.ModuleEntityID='".$user_id."'
                ), '' )";
            
            if($friends)
            {
                $privacy_cond1 = "IF(A.Privacy='2',
                    A.UserID IN (".$friend_followers_list."), true
                )";
            }
            if($follow)
            {
                $privacy_cond2 = "IF(A.Privacy='3',
                    A.UserID IN (".implode(',', $follow)."), true
                )";
            }
        }

        // Check parent activity privacy for shared activity
        $privacy_condition = "
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                IF(A.ActivityTypeID IN(9,10),
                    A.ParentActivityID=(
                        SELECT ActivityID FROM ".ACTIVITY." WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (IF(Privacy=1,true,false) OR
                            IF(Privacy=2,UserID IN (".$friend_followers_list."),false) OR
                            IF(Privacy=3,UserID IN (".implode(',',$friends)."),false) OR
                            IF(Privacy=4,UserID='".$user_id."',false))
                    ),false
                )
                OR
                IF(A.ActivityTypeID IN(14,15),
                    A.ParentActivityID=(
                        SELECT MediaID FROM ".MEDIA." WHERE StatusID=2 AND A.ParentActivityID=MediaID
                    ),false
                )
            ),         
        true)";

        $privacy_cond3 = "IF(A.Privacy='4',
            A.UserID='".$user_id."', true
        )";
        if(!empty($privacy_cond1))
        {
            $privacy_cond .= $privacy_cond1.' OR ';
        }
        if(!empty($privacy_cond2))
        {
            $privacy_cond .= $privacy_cond2.' OR ';
        }
        $privacy_cond .= $privacy_cond3.' )';  

        // /echo $privacy_cond;
        if(!empty($group_list))
        {
            $condition_part_one = $condition_part_one . "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7, (
                        A.ModuleID=1 AND A.ModuleEntityID IN(".$group_list.")
                    ), '' )";  
        }
        if(!empty($page_list))
        {
            $condition_part_three = $condition_part_three . "IF(A.ActivityTypeID=12, (
                        A.ModuleID=18 AND A.ModuleEntityID IN(".$page_list.")
                    ), '' )";  
        }
        if(!empty($event_list))
        {
            $condition_part_four = $condition_part_four . "IF(A.ActivityTypeID=11, (
                        A.ModuleID=14 AND A.ModuleEntityID IN(".$event_list.")
                    ), '' )";  
        }
        if(!empty($condition)) 
        {
            if(!empty($condition_part_one)) 
            {
                $condition = $condition . " OR ".$condition_part_one;
            } 
            if(!empty($condition_part_three)) 
            {
                $condition = $condition . " OR ".$condition_part_three;
            } 
            if(!empty($condition_part_four)) 
            {
                $condition = $condition . " OR ".$condition_part_four;
            } 
            $condition = $condition . ")";
        } 
        else 
        {
            if(!empty($condition_part_one)) 
            {
                $condition = $condition_part_one;
            } 
            if(!empty($condition_part_three)) 
            {
                if(empty($condition)) 
                {
                    $condition = $condition_part_three;
                } 
                else 
                {
                  $condition = $condition . " OR ".$condition_part_three;  
                }                
            }

            if(empty($condition)) 
            {
                $condition =  $condition_part_two;   
            } 
            else 
            {
                //$condition = $condition_part_two. " OR ".$condition_part_one; 
                $condition = "(".$condition.")";    
            }
        }
        $condition .= " AND (CASE WHEN (A.Privacy=2) THEN A.UserID IN (".implode(',', $follow).") ";
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (".$friend_followers_list.")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='".$user_id."' ELSE 1 END) END) END)";

        $this->db->select('A.ActivityGUID');
        $this->db->select("COUNT(R.ReminderID) as Count, DATE_FORMAT(CONVERT_TZ(R.ReminderDateTime,'Etc/UTC',(SELECT StandardTime FROM TimeZones WHERE UD.TimeZoneID=TimeZoneID)),'%Y-%m-%d') as ReminderDate",FALSE);

        $this->db->from(ACTIVITY.' A');        
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(REMINDER." R","R.ActivityID=A.ActivityID AND R.UserID='".$user_id."' AND R.Status!='DELETED'",'JOIN');
        $this->db->join(USERDETAILS.' UD','UD.UserID=R.UserID','left');

        if(!empty($condition)) 
        {
            $this->db->where($condition,NULL,FALSE);    
        } 
        else 
        {
            $this->db->where('A.ModuleID','3');
            $this->db->where('A.ModuleEntityID',$user_id);
        }
        if($privacy_condition)
        {
            $this->db->where($privacy_condition,null,false);
        }

        $this->db->where('A.StatusID','2');

        $this->db->group_by('ReminderDate');
        $this->db->_protect_identifiers = TRUE;        
        $query = $this->db->get(); 
        //echo $this->db->last_query(); die;      

        if($query->num_rows())
        {
            return $query->result_array();
        } 
        else 
        {
            return array();
        }
    }
    
    
    /**
     * [is_reminder To check user set reminder or not for given activity]
     * @param  [type]  $activity_id [Activity ID]
     * @param  [type]  $user_id     [User ID]
     * @return boolean              [TRUE/FALSE]
     */
    function is_reminder($activity_id, $user_id)
    {
        $this->db->select('ReminderID');
        $this->db->where('ActivityID', $activity_id);
        $this->db->where('UserID', $user_id);
        $this->db->where('Status !=','DELETED');
        $query = $this->db->get(REMINDER);
        if($query->num_rows())
        {
            return TRUE;
        } 
        else 
        {
            return FALSE;
        }
    }

    /**
     * [delete Used to delete the reminder]
     * @return [array] [response]
     */
    function delete($reminder_guid, $user_id)
    {
        $return['Message']      = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data']         = array();
        $this->db->select('ReminderID, UserID');
        $this->db->where(
                            array(
                                'ReminderGUID'        => $reminder_guid
                            )
                        );
        $query = $this->db->get(REMINDER);
        if($query->num_rows() > 0) 
        {
            $row = $query->row_array();
            $owner_id = $row['UserID'];
            $reminder_id = $row['ReminderID'];   
            if($user_id == $owner_id)
            {
                $this->db->where('ReminderID', $reminder_id);
                $this->db->delete(REMINDER);
            }
            else 
            {
                $return['ResponseCode'] = 412;
                $return['Message'] = lang('permission_denied');             
            }
        }
        else 
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "reminder GUID");             
        }
        return $return;
    }

        /**
     * [change_status Used to update the reminder status]
     * @return [array] [response]
     */
    function change_status($data)
    {
        $return['Message']      = lang('success');
        $return['ResponseCode'] = 200;
        $return['Data']         = array();

        $created_date       = get_current_date('%Y-%m-%d %H:%i:%s');

        $user_id            = $data['UserID'];
        $reminder_guid      = $data['ReminderGUID'];
        $old_status         = "";
        $status             = (isset($data['Status']) && !empty($data['Status'])) ? strtoupper($data['Status']) : 'ACTIVE' ;        
        if(!in_array($status, $this->allowed_status))
        {
            $status         = "ACTIVE";
        }

        $this->db->select('ReminderID, ActivityID, ReminderDateTime, UserID, Status');
        $this->db->where(
                            array(
                                'ReminderGUID'        => $reminder_guid
                            )
                        );
        $query = $this->db->get(REMINDER);
        if($query->num_rows() > 0) 
        {
            $row = $query->row_array();
            $owner_id = $row['UserID'];
            $reminder_id = $row['ReminderID'];   
            if($user_id == $owner_id)
            {
                $old_status = $row['Status'];
                $this->db->where('ReminderID',$reminder_id)
                    ->update(REMINDER, array('Status' => $status, 'ModifiedDate' => $created_date));
                $return['Data'] = array("Status" => $old_status);
               
                //check if reminder date is arrived
                $this->load->model('timezone/timezone_model');
                $time_zone = $this->timezone_model->getUserTimeZone($user_id);
                // $reminder_date_time = $this->timezone_model->convert_date_to_time_zone($row['ReminderDateTime'], $time_zone, 'UTC');
                $reminder_date_time = $row['ReminderDateTime'];
                $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');

                if(!$this->settings_model->isDisabled(43)) {
                    if($status == 'ARCHIVED' && $current_date_time > $reminder_date_time)
                    {
                        $this->db->insert(ARCHIVEACTIVITY, array('ActivityID' => $row['ActivityID'], 'UserID' => $user_id, 'Status' => 'ARCHIVED', 'CreatedDate' => $current_date_time, 'ModifiedDate' => $current_date_time));
                    }
                    elseif($status == 'ACTIVE')
                    {
                        $this->db->select('ArchiveID');
                        $this->db->where('ActivityID', $row['ActivityID']);
                        $this->db->where('UserID', $user_id);
                        $this->db->where('Status', 'ARCHIVED');
                        $query = $this->db->get(ARCHIVEACTIVITY);
                        if ($query->num_rows())
                        {
                            $archive_details = $query->row_array();
                            /* $this->db->where('ArchiveID',$archive_details['ArchiveID']);
                              $this->db->delete(ARCHIVEACTIVITY); */
                            $this->db->where('ArchiveID', $archive_details['ArchiveID']);
                            $this->db->update(ARCHIVEACTIVITY, array('Status' => "UNARCHIVED", 'ModifiedDate' => $current_date_time));
                        }
                    }
                }
            }
            else 
            {
                $return['ResponseCode'] = 412;
                $return['Message'] = lang('permission_denied');             
            }
        }
        else 
        {
            $return['ResponseCode'] = 412;
            $return['Message'] = sprintf(lang('valid_value'), "reminder GUID");             
        }
        return $return;
    }

    /**
     * [delete_all Used to delete all the reminder for $module_entity_id of $module_id]
     * @param  [int] $user_id          [User ID]
     * @param  [int] $module_id        [Module ID]
     * @param  [int] $module_entity_id [Module Entity ID]
     */
    function delete_all($user_id, $module_id, $module_entity_id)
    {
        $activity_ids = $this->get_activities_id($module_id, $module_entity_id);
        if($activity_ids['ActivityIDs'])
        {
            $arr = array_chunk($activity_ids['ActivityIDs'], 50);
            if($arr)
            {
                foreach($arr as $a)
                {
                    $this->db->where_in('ActivityID',$a);
                    $this->db->where('UserID', $user_id);
                    $this->db->delete(REMINDER);
                }
            }
        }
    }

    /**
     * [get_activities_id Used to get all the activity id's for module_entity_id of module_id]
     * @param  [int] $module_id        [Module ID]
     * @param  [int] $module_entity_id [Module Entity ID]
     * @return [array]                 [Activity ID's]
     */
    function get_activities_id($module_id, $module_entity_id)
    {
        $this->db->select('GROUP_CONCAT(ActivityID) as ActivityIDs',false);
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleID',$module_id);
        $this->db->where('ModuleEntityID',$module_entity_id);
        $this->db->where('StatusID',2);
        $this->db->where_not_in('ActivityTypeID',array(2,3,4,5,6,13));
        $query = $this->db->get();
        $arr = array('ActivityIDs'=>0);
        if($query->num_rows())
        {
            $activity_ids = $query->row()->ActivityIDs;            
            if($activity_ids)
            {
                $arr['ActivityIDs'] = explode(',', $activity_ids);
            }
        }
        return $arr;
    }
}
?>
