<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User_activity_log_score_model extends Common_Model {
    
    public $score_fix_date;
    
    public function __construct() {
        parent::__construct();
        
    }
    
    /**
     * To get score of the activity done by user
     * @return [integer] $activity_type_id
     * @return [integer] $module_id
    */
    public function get_score_for_activity($activity_type_id, $module_id = 0, $parent_entity_id = 0, $user_id = 0) {
        
        if(!$this->is_score_allowed($activity_type_id, $module_id, $parent_entity_id, $user_id)) {
            return 0;
        }
        
        
        $score = $this->get_activity_type_score($activity_type_id, $module_id, $parent_entity_id);
        if(!$score) {
            $score = $this->browsing_activities_score($activity_type_id, $module_id);
        }
        
        if(!$score) {
            //$score = $this->contribution_activities_score($activity_type_id, $module_id);
        }
       
        return $score;
    }
    
    public function get_score_text($score_value, $score_type = 'CONTRIBUTION') {
        
        
        // Contribution score text settings
        if($score_type == 'CONTRIBUTION') {
            if ($score_value >= 30) {
                return 'High';
            }

            if ($score_value >= 20 && $score_value <= 29) {
                return 'Moderate';
            }

            if ($score_value <= 19) {
                return 'Low';
            }
        } else {
            // Browsing score text settings
            if ($score_value >= 10) {
                return 'High';
            }

            if ($score_value >= 5 && $score_value <= 9) {
                return 'Moderate';
            }

            if ($score_value <= 4) {
                return 'Low';
            }
        }
        
        return 'Low';        
    }

    protected function is_score_allowed($activity_type_id, $module_id = 0, $parent_entity_id = 0, $user_id = 0) {
        
        if(in_array($activity_type_id, [3, 31])) { // Use module specific scores only
            $parent_entity_id = 1;
        }
        
        
        $total_times_allowed_activities = array(
            '23' => 1                     , //ProfilePicUpdated
            '3_18' => 10                  , // Follow Entity (Page follow)
            '4' => 10                     , // GroupJoined
            '31_14' => 10                 , // SubscribeEntity ( Event join )
            '35' => 3                     , // LogIn
        );
        
        $score_type_key = '';
        if($activity_type_id) {
            $score_type_key = (string)$activity_type_id;
        }
        
        if($parent_entity_id) {
            $score_type_key = $score_type_key.'_'.$module_id;
        }
        
        if(!isset($total_times_allowed_activities[$score_type_key])) {
            return true;
        }
        
        $this->db->select('COUNT(ID) AS total_activity');
        $this->db->from(USERSACTIVITYLOG);
        
        if(in_array($score_type_key, ['23', '35'])) {
            $this->db->where('ActivityTypeID', $score_type_key);
        } else {
            $where_cnd = "((ActivityTypeID = 3 AND ModuleID = 18) OR (ActivityTypeID = 4) OR (ActivityTypeID = 31 AND ModuleID = 14))";
            $this->db->where($where_cnd, NULL, FALSE);
        }
        $this->db->where('ActivityDate', $this->get_date('%Y-%m-%d'));
        $this->db->where('UserID', $user_id);
        
        
        
        $query = $this->db->get();  //echo $this->db->last_query(); die;
        $row = $query->row_array();
        $total_activity = (int)isset($row['total_activity']) ? $row['total_activity'] : 0;
        
        if($total_activity < $total_times_allowed_activities[$score_type_key]) {
            return true;
        }
        
        return false;
    }

    protected function get_activity_type_score($activity_type_id, $module_id = '', $parent_entity_id = '') {
        
        
        if(in_array($activity_type_id, [3, 31])) { // Use module specific scores only
            $parent_entity_id = 1;
        }
        
        
        $activity_type_score_data = array(
            '' => 0,
            '1' => 10                    , //PostSelf
            '7' => 10                    , //GroupPostAdded
            '8' => 10                    , // Post
            '11' => 10                   , //EventWallPost
            '12' => 10                   , //PagePost
            
            
            
            '14' => 5                   , //ShareMedia
            '15' => 5                   , //ShareMediaSelf
            '9' => 5                    , // Share post
            '10' => 5                   , // ShareSelf
            
            '2' => 5                   , //Add Friend
            
            
            '25' => 0                   , // PollCreated
            
           
            
            
            '3_3' => 5                   , // Follow Entity (User follow)
            '3_18' => 5                  , // Follow Entity (Page follow)
            '4' => 5                     , // GroupJoined
            '31_14' => 5                 , // SubscribeEntity ( Event join )
            
            '19' => 2                   , // Like
            
            
            '23' => 3                   , //ProfilePicUpdated
            
            
            '20' => 4                   , //For comment
            '20_' . $module_id => 4     , //For comment reply
            
            
            '30' => 10                   , // Event Created
            
            '28' => 0                   , // Create Formal Group
            '29' => 0                   , // Informal Group
            '34' => 5                   , // Group Formalization
            
            '35' => 2                   , // LogIn
            
        );
        
        
        $score_type_key = '';
        if($activity_type_id) {
            $score_type_key = (string)$activity_type_id;
        }
        
        if($parent_entity_id) {
            $score_type_key = $score_type_key.'_'.$module_id;
        }
        
        if(isset($activity_type_score_data [$score_type_key])) {
            return $activity_type_score_data [$score_type_key]; 
        }
        
        return 0;
    }

    protected function contribution_activities_score($activity_type_id, $module_id = 0) {
                
        $create_g_e = array(
            28, // Create Formal Group
            30, // Event Created
        );
        
        if(in_array($activity_type_id, $create_g_e)) { // Create group or event
            return 10;
        }
        
        if($activity_type_id == 29) { // Informal Group
            return 8;     
        }
        
        if($activity_type_id == 20) { //For comment
            if($module_id) { // For comment reply
                return 3;     
            }
            return 4;     
        }
        
        
        //subscribe group/page/article + join event 5
        if($activity_type_id == 31) { // subscribe Entity
            return 5;     
        }
        return 0;
    }
    
    protected function browsing_activities_score($activity_type_id, $module_id) {
        if($activity_type_id == 27) { //Search
            return 5;     
        }
        
        
        //Browsing groups/pages 5
        //browsing profiles 5
        //browsing detailed post 5
        //viewing a post 5
        if($activity_type_id == 21 && in_array($module_id, array(1, 3, 18, 19))) {
             return 5;    
        }
        
        
        
        
        
        //News feed 1
        //browsing group list / discover / grow your network 1
        
        if($activity_type_id == 32) { // View Entity Listing
            return 1;  
        }
        
        return 0;
    }
    
    
    public function update_scores_for_dates() {
        $select_array = array();
        $select_array[] = "ActivityDate";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from('UsersActivityLog ' .' UAL ');        
        $this->db->group_by('UAL.ActivityDate');        
        $query = $this->db->get();
        $score_dates = $query->result_array();
        
        foreach ($score_dates as $score_date) {
            $this->score_fix_date = $score_date['ActivityDate'];
            $this->score_fix_date = strtotime($this->score_fix_date);
              //$test_date = $this->get_date('%Y-%m-%d');
            $this->calculate_users_score('BROWSING');
            $this->calculate_users_score('CONTRIBUTION');
            $this->set_highly_active_users();
            
            
        }               
    }
    
    
    /**
     * To calculate user score for his activity done by him
     * @return [string] $calculation_type
    */
    public function calculate_users_score($calculation_type = 'BROWSING') {
        $users_data = $this->get_users_for_calculation(1, 200, $calculation_type);
        $users = $users_data['entities'];
        $total_users = (int)isset($users_data['total_entities']['Count']) ? $users_data['total_entities']['Count'] : 0;
        
        foreach($users as $user) {
            $user_id = $user['UserID'];
            $user_today_score_val = $user['UserScore'];
            $this->calculate_users_score_by_type($user_id, $user_today_score_val, $calculation_type);
        }
        
        //Recursive till all users score is not calculated for today.
        if($total_users > count($users)) {
            $this->calculate_users_score($calculation_type);
        }
    }

    public function calculate_users_score_by_type($user_id, $user_today_score_val, $score_type = 'BROWSING') {        
        
        $today = $this->get_date('%Y-%m-%d');

        // Get user last score
        $select_array = array();
        $select_array[] = "SQL_CALC_FOUND_ROWS US.UserID, US.LastScore";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERACTIVITYSCORES .' US ');
        $this->db->where('US.UserID', $user_id);
        $this->db->where('US.ScoreType', $score_type);
        $this->db->where("US.ScoreDate < '$today'", NULL, FALSE);
        $this->db->order_by('US.ScoreDate', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $user_last_score = $query->row_array();
        
        $this->db->select('FOUND_ROWS() AS Count',false);
        $query = $this->db->get();
        $last_number_of_days = $query->row_array();
        
        
        // Calculate user score data
        $user_last_score_val = 0; $last_number_of_days_val = 1;
        if(!empty($user_last_score['LastScore'])) {
            $user_last_score_val = $user_last_score['LastScore'];
            $last_number_of_days_val += $last_number_of_days['Count'];
        }
        
        $total_score_today = $user_today_score_val + $user_last_score_val;
        //$average_score = (int)($total_score_today/$last_number_of_days_val);
        $average_score = (int)($total_score_today);
        
        
        // Prevent accidental multiple save for same day If other thread is working on same
        $select_array = array();
        $select_array[] = "US.UserID";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERACTIVITYSCORES .' US ');
        $this->db->where('US.UserID', $user_id);
        $this->db->where('US.ScoreType', $score_type);
        $this->db->where("US.ScoreDate", $today);
        $this->db->limit(1);
        $query = $this->db->get();
        $check_user_score = $query->row_array();
        if(!empty($check_user_score['UserID'])) {
            return;
        }
        
        // Store user calculated data
        $score_data = array(
            'ScoreDate' => $today,
            'Score' => $user_today_score_val,
            'UserID' => $user_id,
            'ScoreType' => $score_type,
            'LastScore' => $total_score_today,
            'CreatedDate' => $this->get_date('%Y-%m-%d %H:%i:%s'),
            'ModifiedDate' => $this->get_date('%Y-%m-%d %H:%i:%s'),
        );
        $this->db->insert(USERACTIVITYSCORES, $score_data);
        
        $where_ud = array('UserID' => $user_id,);
        if($score_type == 'BROWSING') {
            $user_details = array(
                'BrowsingAverage' => $average_score,
                'AverageScore' => "$average_score + ContributionAverage"
            );
        } else {
            $user_details = array(
                'ContributionAverage' => $average_score,
                'AverageScore' => "$average_score + BrowsingAverage"
            );
        }
        
        $update_data_fields = [];
        foreach ($user_details as $field => $val) {
            $update_data_fields[] = " $field = $val ";
        }
        $update_data_fields = implode(',', $update_data_fields);
        $update_user_query = " Update ".USERDETAILS." SET $update_data_fields ";
        $update_user_query .= " Where UserID = $user_id"; 
        $query = $this->db->query($update_user_query);
        
        //$this->db->update(USERDETAILS, $user_details, $where_ud);
    }
    
    public function get_users_for_calculation($page_no, $page_size = 200, $calculation_type = 'BROWSING') {
        $select_array = array();
        $select_array[] = "SQL_CALC_FOUND_ROWS UAL.UserID, SUM(UAL.Score) UserScore";
        $offset = ($page_no - 1) * $page_size;
        $allowed_activity_types = array(27, 21, 32);
        
        $today = $this->get_date('%Y-%m-%d');
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERSACTIVITYLOG .' UAL ');
        $this->db->join(USERACTIVITYSCORES . ' US ', "US.UserID = UAL.UserID AND US.ScoreDate = '$today' AND US.ScoreType = '$calculation_type' ", 'left');
        
        if($calculation_type == 'BROWSING') {
            $this->db->where_in('UAL.ActivityTypeID', $allowed_activity_types);
        } else {
            $this->db->where_not_in('UAL.ActivityTypeID', $allowed_activity_types);
        }
        
        
        $this->db->where('US.UserID IS NULL', NULL, FALSE);
        $this->db->where('UAL.ActivityDate', $today);
        $this->db->group_by('UAL.UserID');
        $this->db->having('UserScore > 0', NULL, FALSE);
        $this->db->limit($page_size, $offset);
        
        $query = $this->db->get();
        $entities = $query->result_array();
        
        //echo $this->db->last_query(); die;
        
        $this->db->select('FOUND_ROWS() AS Count',false);
        $query = $this->db->get();
        $total_entities = $query->row_array();
        
        return array(
            'entities' => $entities,
            'total_entities' => $total_entities,
        );
    }
    
    /**
     * To set highly active users by the percentage given
     * @return [integer] $percentage
     * @return [array] $exclude_user_ids
    */
    public function set_highly_active_users($percentage = 1, $exclude_user_ids = array(), $calculate_for_last_days = 0) {
        $today = $this->get_date('%Y-%m-%d');
        // Get user last score
        $select_array = array();
        $select_array[] = "Count(UD.UserID) AS user_count";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERDETAILS .' UD ');
        if($exclude_user_ids) {
            $this->db->where_not_in('UD.UserID', $exclude_user_ids);
        }
        $this->db->where('UD.AverageScore > 0', NULL, FALSE);
        $this->db->order_by('UD.AverageScore', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $users_total = $query->row_array();
        
        
        if($percentage === 1) {
            // Reset old highly active users
            $update_user_query = " Update ".USERDETAILS." SET HighlyActivePercentage = 0 ";
            $query = $this->db->query($update_user_query);
        }
        
        $users_total = isset($users_total['user_count']) ? $users_total['user_count'] : 0;
        $highly_active_users_total = (int) ceil(($percentage * $users_total / 100));
        
        // Get highly active users for percentage
        $select_array = array();
        $select_array[] = "UD.UserID";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERDETAILS .' UD ');
        $this->db->where('UD.AverageScore > 0', NULL, FALSE);
        if($exclude_user_ids) {
            $this->db->where_not_in('UD.UserID', $exclude_user_ids);
        }
        $this->db->order_by('UD.AverageScore', 'DESC');
        $this->db->limit($highly_active_users_total);
        $query = $this->db->get();
        $highly_active_users = $query->result_array();
        
        $highly_active_user_ids = [];
        foreach ($highly_active_users as $highly_active_user) {
            $highly_active_user_ids[] = $highly_active_user['UserID'];
        }
        
        if(empty($highly_active_user_ids)) {
           return; 
        }
        
        $update_user_query = " Update ".USERDETAILS." SET HighlyActivePercentage = $percentage ";
        $update_user_query .= " Where UserID IN (". implode(',', $highly_active_user_ids).")"; 
        $query = $this->db->query($update_user_query);
        
        if($percentage == 10) {
            return;
        }
        
        $percentages = array(1 => 5, 5 => 10,);
        $percentage = $percentages[$percentage];
        
        $exclude_user_ids = array_merge($highly_active_user_ids, $exclude_user_ids);
        $this->set_highly_active_users($percentage, $exclude_user_ids);
    }
    
    /**
     * To get user score between two dates 
     * @param [string] $calculation_type
     * @param [int] $min_days
     * @param [int] $max_days
    */
    public function get_user_score_comparision($calculation_type = 'BROWSING', $min_days = 30, $max_days = 0) {
        
        $max_date_val = $this->get_date('%Y-%m-%d', $max_days);
        $min_date_val = $this->get_date('%Y-%m-%d', $min_days);
        
        $select_array = [];
        $select_array[] = "SUM( US.Score ) total_score";
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERACTIVITYSCORES . ' US ');
        $this->db->where('US.ScoreType', $calculation_type);
        $this->db->where("US.ScoreDate >= '$min_date_val' AND US.ScoreDate <= '$max_date_val'", NULL, FALSE);
        
        $query = $this->db->get();
        $total_score = $query->row_array();
        $total_score = isset($total_score['total_score']) ? $total_score['total_score'] : 0;
        
        return $total_score;
    }
    
    /**
     * To Log search data
     * @param [string] $search_text
     * @param [int] $user_id
    */
    public function log_search_data($search_text, $user_id) {
        if(!$search_text || !$user_id) {
            return;
        }
        
        $module_id = 3;        
        // Check if user already searched for this text
        $select_array[] = " UAL.ID";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERSACTIVITYLOG . ' UAL ');
        $this->db->where('UAL.UserID', $user_id);
        $this->db->where('UAL.ActivityTypeID', 27);
        $this->db->where('UAL.ActivityData', $search_text);
        $this->db->where('UAL.ActivityDate', $this->get_date('%Y-%m-%d'));
        
        $query = $this->db->get();
        $activity_log = $query->row_array();
        
        // If this activity is logged then return
        if(!empty($activity_log['ID'])) {
            return;
        }
        
        $score = 0;
        if(empty($activity_log['ID'])) {
            $score = $this->get_score_for_activity(27, $module_id);
        }
        
        // Save user activity Log 
        $userActivityLog = array(
            'ModuleID' => $module_id, 'ModuleEntityID' => 0, 'UserID' => $user_id, 'ActivityTypeID' => 27, 'ActivityData' => $search_text,
            'ActivityDate' => $this->get_date('%Y-%m-%d'), 'PostAsModuleID'=> 3, 'PostAsModuleEntityID' => $user_id, 'Score' => $score,
        );
        $this->add_activity_log($userActivityLog);
    }
    
    /**
     * To Log subscribe data
     * @param [string] $entity_type
     * @param [int] $entity_id
     * @param [int] $module_entity_id
     * @param [int] $module_id
    */
    public function log_subscribe_data($entity_type, $entity_id, $module_entity_id, $module_id) {
        $log_module_id = 0;
        switch($entity_type) {
            case 'ACTIVITY':
                $log_module_id = 19;
            break;
            case 'GROUP':
                $log_module_id = 1;
            break;

            case 'PAGE':
                $log_module_id = 18;
            break;

            case 'EVENT':
                $log_module_id = 14;
            break;
        }

        $score = $this->get_score_for_activity(31, $module_id, 0, $module_entity_id);
        $data = array(
            'ModuleID' => $log_module_id, 'ModuleEntityID' => $entity_id, 'UserID' => $module_entity_id, 'ActivityTypeID' => 31, 
            'ActivityDate' => $this->get_date('%Y-%m-%d'), 'PostAsModuleID' => $module_id, 'PostAsModuleEntityID' => $module_entity_id, 'EntityID' => $entity_id , 'Score' => $score,
        );
        if($log_module_id) $this->add_activity_log($data);
    }

    /**
     * To add activity log
     * @param [array] $data
     * @param [string] $type
    */
    public function add_activity_log($data, $type = '') {
        $activity_type_id = (int)isset($data['ActivityTypeID']) ? $data['ActivityTypeID'] : 0;
        if(in_array($activity_type_id, array(21, 32))) {
            $this->db->select('ID')
                    ->from(USERSACTIVITYLOG)
                    ->where('UserID', $data['UserID'])
                    ->where('ModuleID', $data['ModuleID'])
                    ->where('ModuleEntityID', $data['ModuleEntityID'])
                    ->where('ActivityDate', $this->get_date('%Y-%m-%d'))
                    ->where('Score > 0', NULL, FALSE)
                    ->where('ActivityTypeID', $activity_type_id);

            $query = $this->db->get();
            $activity_log = $query->row_array();

            // If score given to user for this activity.
            if(!empty($activity_log['ID'])) {
                return 0; // Don't save it many times
                $data['Score'] = 0;
            }
        }
        
        // Set user analytics id
        $user_id = 0;
        $Data = isset($this->post_data) ? $this->post_data : [];
        $login_session_key = isset($Data[AUTH_KEY]) ? $Data[AUTH_KEY] : '';
        if(!$login_session_key) {
            $user_id = $data['UserID'];
        }
        $data['AnalyticLoginID'] = get_analytics_id($login_session_key, $user_id);
        $this->db->insert(USERSACTIVITYLOG, $data);
        
        $last_id = $this->db->insert_id();
        
        if(isset($data['Score']) && $data['Score'] > 0) {
            $LastActivityDate = $this->get_date('%Y-%m-%d %H:%i:%s');
            $this->db->set('LastActivityDate',$LastActivityDate);
            $this->db->where('UserID',$data['UserID']);
            $this->db->update(USERDETAILS);
        }
        
        
        
        return $last_id;
    }
    
    
    public function set_auto_feature_posts() {
        $this->load->model('activity/activity_model');
        $data = $this->get_auto_feature_posts(1, 1000);
        
        $posts = $data['entities'];
        $total_posts = (int)isset($data['total_entities']['Count']) ? $data['total_entities']['Count'] : 0;  
        
        //print_r($posts);  print_r($total_posts);
        
        foreach ($posts as $post) {
            
            $this->activity_model->set_featured_post(0,0,0,$post['ActivityID'], false);
            
            //print_r($post);
        }
    }


    public function get_auto_feature_posts($page_no, $page_size, $no_of_likes = 3, $no_of_comments = 3) {
        $select_array = array();
        
        $no_of_comments_sql = "(SELECT COUNT(DISTINCT PS.UserID) FROM ".POSTCOMMENTS." PS WHERE PS.EntityType = 'Activity' AND PS.EntityID = A.ActivityID AND PS.StatusID = 2)";
        
        $select_array[] = "SQL_CALC_FOUND_ROWS ($no_of_comments_sql + A.NoOfLikes) AS total_count, ActivityID, $no_of_comments_sql AS NoOfComments";
        $offset = ($page_no - 1) * $page_size;
        
        
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(ACTIVITY .' A');     
        $this->db->where('StatusID', 2);
        $this->db->where('IsFeatured', 0);
        $this->db->where("A.NoOfLikes >= $no_of_likes", NULL, FALSE);
        $this->db->where('IsAdminFeatured IS NULL', NULL, FALSE);
        $this->db->where('ActivityTypeID != 13', NULL, FALSE);
        $this->db->where('Privacy', 1);
        $this->db->group_by('A.ActivityID');
        $this->db->having('total_count > 0');
        $this->db->having("NoOfComments >= $no_of_comments", NULL, FALSE);
        
        $this->db->order_by('total_count DESC');
        $this->db->limit($page_size, $offset);
        
        $query = $this->db->get();
        $entities = $query->result_array();
        
        //echo $this->db->last_query(); die;
        
        $this->db->select('FOUND_ROWS() AS Count',false);
        $query = $this->db->get();
        $total_entities = $query->row_array();
        
        return array(
            'entities' => $entities,
            'total_entities' => $total_entities,
        );
    }


    /**
     * To get user activity verify status
     * @param [integer] $user_id
    */
    public function get_user_activity_verify_status($user_id) {
        $this->db->select('U.UserTypeID',false);
        $this->db->from(USERS .' U ');
        $this->db->where('U.UserID', $user_id);
        
        $query = $this->db->get();
        $user = $query->row_array();
        
        return (!empty($user['UserTypeID']) && $user['UserTypeID'] == 4) ? 1 : 0;
    }
    
    
    protected function get_date($format, $diff_date = 0, $plus = 0, $time = 0) {
        if(!empty($this->score_fix_date)) {
            return get_current_date($format, $diff_date, $plus, $this->score_fix_date);
        }
        return get_current_date($format, $diff_date, $plus, $time);
    }
}

//End of file users_model.php
