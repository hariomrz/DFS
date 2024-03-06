<?php

class User_updates_model extends Common_Model {
    
    
    

    public function __construct() {
        parent::__construct();
        $this->load->model([
            'activity/activity_model', 'notification_model', 
            'forum/forum_model', 'group/group_model', 
            'page/page_model',
        ]);
        
        $this->load->model(array('activity/activity_feed_model'));
        //$this->email_type = array('daily_digest' => 27, 'inactive_user' => 40, 'incomplete_profile' => 41 );        
    }

    public function send_email_notification_to_incomplete_profile_users($for_days = 15, $offset = 0, $limit = 200) {

        $this->set_query_for_last_sent_users($for_days, $offset, $limit, 'IncompleteProfile');

        //$this->db->limit(1);
        $query = $this->db->get();

        $users = $query->result_array();    if(!empty($_GET['print_users'])) { print_r($users); die; } // For testing

        foreach ($users as $user) {
            $this->send_email_notification(array(
                'UserID' => '0',
                'FromUserDetails' => array(),
                'ToUserID' => $user['ToUserID'],
                'ToUserDetails' => $user,
                'RefrenceID' => '0',
                'NotificationTypeID' => '148'
            ));
        }

        if ($users) {
            $this->send_email_notification_to_incomplete_profile_users($for_days, $offset + $limit);
        }



        return $users;
    }

    private function set_query_for_last_sent_users($for_days = 15, $offset = 0, $limit = 200, $type = 'IncompleteProfile') {
        $days_diff_date = get_current_date('%Y-%m-%d', $for_days);
        $current_date = get_current_date('%Y-%m-%d');

        $this->db->select('U.UserID ToUserID, U.UserID, U.Email, U.FirstName, U.LastName');
        $this->db->from(USERS . ' U');

        $email_type_id = $this->db->escape_str($email_type_id);

        if ($type == 'IncompleteProfile') {
            $this->db->where('U.IsProfileSetup', 0);
            $email_type_id = 41;
        }

        if ($type == 'LastLogin') {
            $this->db->where(" (UD.LastLoginDate <= '$days_diff_date' )", NULL, FALSE);
            $this->db->where(" UD.UserID IS NOT NULL", NULL, FALSE);
            $email_type_id = 40;
        }

        if ($type == 'DailyDigest') {
            $this->db->where('U.IsProfileSetup', 1);
            $this->db->where('U.StatusID', 2);
            $email_type_id = 27;
        }


        $this->db->join(COMMUNICATIONS . ' CM', "CM.UserID = U.UserID AND `CM`.`EmailTypeID` = $email_type_id", 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');

        if(!empty($_GET['users_in'])) $this->db->where_in('UD.UserID', $_GET['users_in']); // For testing



        $this->db->where(" (  (`CM`.`CommunicationID` IS NULL )", NULL, FALSE);

        
        
        $this->db->or_where("( `CM`.`CommunicationID` = (    
            SELECT `CommunicationID` 
            FROM `Communications` `CMI` 
            WHERE   `CMI`.`UserID` = `U`.`UserID` 
            AND `CMI`.`EmailTypeID` = $email_type_id 
            ORDER BY `CMI`.`CommunicationID` DESC LIMIT 1
        ) 
         ", NULL, FALSE);

        $this->db->where(" MOD(datediff('$current_date', CM.CreatedDate), $for_days) = 0 ", NULL, FALSE);
        $this->db->where(" datediff('$current_date', CM.CreatedDate) > 0 "
                . ")"
                . ")", NULL, FALSE);
        
        $this->db->where(" (U.CreatedDate <= '$days_diff_date' )", NULL, FALSE);

        $this->db->group_by('U.UserID');

        $this->db->limit($limit, $offset);
    }

    public function send_email_notification_to_inactive_users($for_days = 15, $offset = 0, $limit = 200) {

        $this->set_query_for_last_sent_users($for_days, $offset, $limit, 'LastLogin');

        $query = $this->db->get();      //echo $this->db->last_query(); die;  

        $users = $query->result_array();    if(!empty($_GET['print_users'])) { print_r($users); die; } // For testing

        foreach ($users as $user) {
            $this->send_email_notification(array(
                'UserID' => 0,
                'FromUserDetails' => array(),
                'ToUserID' => $user['ToUserID'],
                'ToUserDetails' => $user,
                'RefrenceID' => '0',
                'NotificationTypeID' => '147'
            ));
        }

        if ($users) {
            $this->send_email_notification_to_inactive_users($for_days, $offset + $limit);
        }

        return $users;
    }

    public function send_notification_daily_digest($for_days = 7, $offset = 0, $limit = 200) {
        $this->set_query_for_last_sent_users($for_days, $offset, $limit, 'DailyDigest');

        $query = $this->db->get();      //echo $this->db->last_query(); die;  

        $users = $query->result_array();    if(!empty($_GET['print_users'])) { print_r($users); die; } // For testing

        foreach ($users as $user) {
            $this->send_email_notification(array(
                'UserID' => 0,
                'FromUserDetails' => array(),
                'ToUserID' => $user['ToUserID'],
                'ToUserDetails' => $user,
                'RefrenceID' => '0',
                'NotificationTypeID' => '150'
            ));
        }

        if ($users) {
            $this->send_notification_daily_digest($for_days, $offset + $limit);
        }

        return $users;
    }

    public function get_email_data($type, $user, $email_data_sent) {
        $email_data = [];
        if ($type == 'incomplete_profile') {

            //$email_data['EmailTypeID'] = $this->email_type[$type];
            $email_data['StatusMessage'] = "";
            $email_data['Data'] = array('user' => $user);
            $email_data['Subject'] = "Seems like you forgot something!";
            $email_data['UserID'] = $user['UserID'];

            $email_data = array_merge($email_data, $email_data_sent);

            return $email_data;
        }


        if ($type == 'inactive_user') {

            $activities = $this->activity_model->get_public_feed('', 3, 0, 1, 3, false, [], array(
                'AllowedActivityTypeID' => [1, 7, 11, 12, 26],
                'UserID' => $user['UserID'],
            ));
            $email_data['StatusMessage'] = "";
            $email_data['Data'] = array('user' => $user, 'activities' => $activities);
            $user_name = !empty($user['FirstName']) ? $user['FirstName'] : 'Hey there';
            $email_data['Subject'] = "$user_name, we miss you here!";
            $email_data['UserID'] = $user['UserID'];

            $email_data = array_merge($email_data, $email_data_sent);

            return $email_data;
        }

        if ($type == 'weekly_digest_feed') {
            //$activities = $this->activity_model->get_public_feed('', 3, 0, 1, 3, false, [], array('AllowedActivityTypeID' => [1, 7, 11, 12, 26]));  
            
            $exclude_activities = [];
            
            
            $this->activity_feed_model->set_user_entities_data($user['UserID']);
            
            // Most active forum categories, groups, pages activites
            $forum_categories = $this->forum_model->get_user_most_active_categories($user['UserID'], 1, true);
            $group_categories = $this->group_model->get_user_most_active_groups($user['UserID'], 1, true);
            $page_categories = $this->page_model->get_user_most_active_pages($user['UserID'], 1, true);
            
            $entity_activities = $this->activity_model->getFeedActivities($user['UserID'], 1, 2, 'popular', 0, 0, 2, false, false, false, 0, 0, [], '', [], '', '', [], array(), 1, 2, 0, '', [], [                
                'emailer' => [
                    'AllowedActivityTypeID' => [1, 7, 11, 12, 26],
                    'UserID' => $user['UserID'],
                    'ActiveCategory' => $forum_categories,
                    'ActiveGroup' => $group_categories,
                    'ActivePage' => $page_categories,
                    'ExcludeActivities' => $exclude_activities,
                    'ActivitiesWithinDays' => 7
                ]
            ]);
            
                        
            $exclude_activities = [];
            foreach ($entity_activities as $activity) {
                $exclude_activities[] = $activity['ActivityID'];
            }
            
            $limit = 1 + (2 - count($exclude_activities));
            
            
            
            //Followers activities
            $follower_activities = $this->activity_model->getFeedActivities($user['UserID'], 1, $limit, 'popular', 0, 0, 2, false, false, false, 0, 0, [], '', [], '', '', [], array(), 1, 2, 0, '', [], [                
                'emailer' => [
                    'AllowedActivityTypeID' => [1, 7, 11, 12, 26],
                    'UserID' => $user['UserID'],
                    'ExcludeActivities' => $exclude_activities,
                    'ActivitiesWithinDays' => 7
                ]
            ]);
            
            //$follower_activities = [];
            $activities = array_merge($entity_activities, $follower_activities);    //print_r($activities); die;
            
            
            //echo $this->db->last_query(); die;

            $email_data['StatusMessage'] = "";
            $email_data['Data'] = array('user' => $user, 'activities' => $activities);
            $user_name = !empty($user['FirstName']) ? $user['FirstName'] : 'Hey there';
            $email_data['Subject'] = "$user_name, Here are some amazing picks just for you!";
            $email_data['UserID'] = $user['UserID'];

            $email_data = array_merge($email_data, $email_data_sent);

            return $email_data;
        }

        return $email_data;
    }

    public function send_email_notification($notification_data) {        
        $notification_type_data = $this->notification_model->get_notification_type_data($notification_data['NotificationTypeID']);
        $template_key = isset($notification_type_data['NotificationTypeKey']) ? $notification_type_data['NotificationTypeKey'] : '';

        $language = strtolower($this->config->item("language"));
        $user_detail = array();
        $from_user_detail = array();
        if ($notification_data['ToUserID']) {
            $user_detail = $notification_data['ToUserDetails'];
        }

        if ($notification_data['UserID']) {
            $from_user_detail = $notification_data['FromUserDetails'];
        }

        $email_data = array();
        $email_data['IsResend'] = 0;
        $email_data['TemplateName'] = "emailer/n_" . $template_key;
        $email_data['Email'] = $user_detail['Email'];
        $email_data['UserID'] = $notification_data['ToUserID'];        

        $email_data = $this->get_email_data($template_key, $notification_data['ToUserDetails'], $email_data);
        $email_data['EmailTypeID'] = $this->notification_model->email_type[$template_key];
        
        
        // Check if feed exists for this user else return it
        if(in_array($notification_data['NotificationTypeID'], [150, 147]) && empty($email_data['Data']['activities']) ) {
            return;
        }

        if (isset($user_detail['UserID'])) {
            $user_detail['ProfileURL'] = get_entity_url($user_detail['UserID'], "User", 1);
        }
        if (isset($from_user_detail['UserID'])) {
            $from_user_detail['ProfileURL'] = get_entity_url($from_user_detail['UserID'], "User", 1);
        }

        $email_data['Data']['To'] = $user_detail;
        $email_data['Data']['From'] = $from_user_detail;
        $this->load->model('Settings_model');


        sendEmailAndSave($email_data, 1); // Common function to send email
        
        //SendPushMsg($notification_data['ToUserID'], $email_data['Subject']);
    }

}

?>
