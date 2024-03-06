<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class User_activity_log_score_model extends Common_Model {
    
    public $score_fix_date;
    public $entity_module = array();
    public function __construct() {
        parent::__construct();
        
        $this->entity_module['Page'] = 18;
        $this->entity_module['PageLike'] = 18;
        $this->entity_module['User'] = 3;
        $this->entity_module['Group'] = 1;
        $this->entity_module['Event'] = 14;
        $this->entity_module['Comment'] = 20;
        $this->entity_module['CommentLike'] = 20;
        $this->entity_module['Activity'] = 19;
        $this->entity_module['ActivityLike'] = 19;
        $this->entity_module['Media'] = 21;
        $this->entity_module['MediaLike'] = 21;
        $this->entity_module['Album'] = 13;        
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
        $this->db->limit(1);
        
        
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
            '1' => 1                    , //PostSelf
            '8' => 0                    , // Post
            '19' => 2                   , // Like
            '19_po' => 2                   , // Like
            '20' => 2                   , //For comment
            '20_' . $module_id => 2     , //For comment reply
            '20_po' => 3                   , //For comment, point ot post owner

            '43' => 0                   , //Mark as Classified
            '44' => 2                   , //Mark as City news
            '45' => 5                   , //Visible to ALL Wards

            '46' => 5                   , //possible solution within a ward
            '47' => 10                   , //solution within a ward
            '46_c' => 5                   , //possible solution within a city
            '47_c' => 10                   , //solution within a city
            '48' => 8                   , //Upload a photo
            '49' => 10                   , //Quiz Post Added
            '51' => 10                   , //Amazing Comment


            '7' => 10                    , //GroupPostAdded            
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
            '23' => 3                   , //ProfilePicUpdated
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
        $entity_type = ucfirst(strtolower($entity_type));
        if (isset($this->entity_module[$entity_type])) {
            $log_module_id = $this->entity_module[$entity_type];
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
        $post_data = isset($this->post_data) ? $this->post_data : [];
        $login_session_key = isset($post_data[AUTH_KEY]) ? $post_data[AUTH_KEY] : '';
        if(empty($login_session_key)) {
            $login_session_key = isset($data['LoginSessionKey']) ? $data['LoginSessionKey'] : '';
            unset($data['LoginSessionKey']);
        }
        if(!$login_session_key) {
            $user_id = $data['UserID'];
        }
        $data['AnalyticLoginID'] = get_analytics_id($login_session_key, $user_id);

        $pa_id = isset($data['PA_ID']) ? $data['PA_ID'] : 0;
        $oid = isset($data['OID']) ? $data['OID'] : 0;
        unset($data['PA_ID']);
        unset($data['OID']);

        $this->db->insert(USERSACTIVITYLOG, $data);
        
        $last_id = $this->db->insert_id();
        
        if(isset($data['Score']) && $data['Score'] > 0) {
            $LastActivityDate = $this->get_date('%Y-%m-%d %H:%i:%s');
            $this->db->set('LastActivityDate',$LastActivityDate);
            $this->db->where('UserID',$data['UserID']);
            $this->db->update(USERDETAILS);
        }
        
        if(in_array($activity_type_id, array(1, 8, 20, 19))) {
            if(!empty($data['Score'])) {                
                $arrayPoint = array(
                    'UserID'        => $data['UserID'],
                    'EntityID'      => $data['EntityID'],
                    'ActivityID'    => $pa_id,
                    'ActivityTypeID' => $activity_type_id,
                    'Point'         => $data['Score'],                
                    'OID'           => $oid
                );
                if(in_array($activity_type_id, array(1, 8))) {              
                    $arrayPoint['EntityType']    = 1; 
                    $arrayPoint['PT']    = 1; 
                } else if($activity_type_id == 20) {
                    $arrayPoint['EntityType']    = 2; 
                    $arrayPoint['PT']    = 2;
                } else if($activity_type_id == 19) {
                    $arrayPoint = $this->like_object_details($data['EntityID'], $arrayPoint);
                }

                if(!empty($arrayPoint['ActivityID'])) {
                    $this->user_activity_point($arrayPoint);
                }    
            }        
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
        if($this->UserTypeID) {
            return (!empty($this->UserTypeID) && $this->UserTypeID == 4) ? 1 : 0;
        }
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
    
    public function add_update_relationship_score($user_id, $module_id, $module_entity_id, $score) {
        if (!$module_entity_id) {
            return;
        }
        $query = $this->db->limit(1)->get_where(RELATIONSHIPSCORE, array('UserID' => $user_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id));
        if ($query->num_rows()) {
            //Update Query
            if ($module_id == 3 && $module_entity_id == $user_id) {
                $this->db->set('Score', DEFAULT_RELATIONSHIP_SCORE, FALSE);
            } else {
                $this->db->set('Score', "Score+($score)", FALSE);
            }
            $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->where('UserID', $user_id);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->update(RELATIONSHIPSCORE);
        } else {
            if ($module_id == 3 && $module_entity_id == $user_id) {
                $score = DEFAULT_RELATIONSHIP_SCORE;
            }
            //Insert Query
            $this->db->insert(RELATIONSHIPSCORE, array('UserID' => $user_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'Score' => $score, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
        }
    }
    
    /**
    * [save_log]
    * @param entity_type
    * @param entity_guid
    * @param user_id
    */
    function save_log($user_id, $entity_type, $entity_guid, $boolean = false, $device_type_id = 0) {        
        $entity_id = 0;
        if (isset($this->entity_module[$entity_type])) {
            $module_id = $this->entity_module[$entity_type];
        } else {
            return false;                
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

        if (!empty($entity_id)) {
            $this->db->select('EntityViewID');
            $this->db->where('UserID', $user_id);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->limit('1');
            $query = $this->db->get(ENTITYVIEW);
            if ($query->num_rows()) {
                if ($boolean) {
                    return true;
                }
                $entity_view_id = $query->row()->EntityViewID;
                $this->db->set('ViewCount', 'ViewCount+1', FALSE);
                $this->db->set('StatusID', 1);
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->where('EntityViewID', $entity_view_id);
                $this->db->update(ENTITYVIEW);

                /* if($entity_type == 'Activity')
                  {
                  $this->db->set('NoOfViews','NoOfViews+1',FALSE);
                  $this->db->where('ActivityID',$entity_id);
                  $this->db->update(ACTIVITY);
                  } */
                return true;
            } else {
                if ($boolean) {
                    return false;
                }
                $data = array('UserID' => $user_id, 'ViewCount' => '1', 'EntityType' => $entity_type, 'EntityID' => $entity_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->insert(ENTITYVIEW, $data);
                $entity_view_id = $this->db->insert_id();

                if ($entity_type == 'Activity') {
                    $this->db->set('NoOfViews', 'NoOfViews+1', false);
                    $this->db->where('ActivityID', $entity_id);
                    $this->db->update(ACTIVITY);
                }
            }
            $d = array('EntityViewID' => $entity_view_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'BrowserID' => check_browser(), 'DeviceTypeID' => $device_type_id);
            $this->db->insert(ENTITYVIEWLOG, $d);
        }
        return false;
    }
    
    function view_log($user_id, $entity_type, $entity_id) {
        $this->db->select('EntityViewID');
        $this->db->where('UserID', $user_id);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('EntityID', $entity_id);
        $this->db->limit('1');
        $query = $this->db->get(ENTITYVIEW);
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Used to update user activity point
     */
    public function user_activity_point($data) {
        
        $current_date_time  = $this->get_date('%Y-%m-%d %H:%i:%s');
        $current_date       = $this->get_date('%Y-%m-%d');
        $user_id            = $data['UserID'];
        $owner_id           = $data['OID'];
        $data['CreatorID']  = isset($data['CreatorID']) ? $data['CreatorID'] : $user_id;
        
        $album_id           = isset($data['AlbumID']) ? $data['AlbumID'] : 0;
        $entity_type        = isset($data['EntityType']) ? $data['EntityType'] : '';        
        $entity_id          = $data['EntityID'];
        $activity_id        = $data['ActivityID'];
        $activity_type_id   = $data['ActivityTypeID'];
        $point              = $data['Point'];
        
        $entity_type    = $data['EntityType']; 
        $point_type     = $data['PT'];

        if($user_id != $owner_id && $point > 0) {        
            $this->db->select('PointHistoryID');
            $this->db->where('UserID', $user_id);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->where('ActivityTypeID', $activity_type_id);
            $this->db->where('StatusID', 2);
            $this->db->limit(1);
            $query = $this->db->get(USERACTIVITYPOINTHISTORY);
            if ($query->num_rows() == 0) {
                // Check for max point
                if($this->check_max_point($data)) {                
                    $insert_data = array(
                        'ActivityTypeID' => $activity_type_id,
                        'UserID' => $user_id,
                        'EntityType' => $entity_type,
                        'EntityID' => $entity_id,
                        'ActivityID' => $activity_id,
                        'AlbumID' => $album_id,
                        'Point' => $point,
                        'PointType' => $point_type,
                        'StatusID' => 2,
                        'CreatedDate' => $current_date_time,
                        'ModifiedDate' => $current_date_time,
                        'Param' => '',
                        'CreatorID' => $data['CreatorID']
                    );
                    $this->db->insert(USERACTIVITYPOINTHISTORY, $insert_data);

                    $post_point = ($point_type == 1) ? $point : 0;
                    $comment_point = ($point_type == 2) ? $point : 0;
                    $photo_point = ($point_type == 3) ? $point : 0;
                    $this->update_user_daily_point($user_id, $post_point, $comment_point, $photo_point);
                }            
            }
        }
        //if parent owner exit then call function for him
        if(!empty($owner_id) && $user_id != $owner_id) {
            $data['CreatorID'] = $user_id;
            $data['UserID'] = $owner_id;
            //also update PT value based on activity_type_id value

            $data['Point'] = $this->get_activity_type_score($activity_type_id.'_po');

            $data['OID'] = 0;
            if(empty($data['OID'])) {
                if($activity_type_id == 20) { 
                    $data['PT']     = 1; //POST POINT CALCULATED FOR OWNER OF COMMENT ENTITY
                }
            }
            $this->user_activity_point($data);
        }
    }

    /**
     * Used to update user daily point
     */
    public function update_user_daily_point($user_id, $post_point=0, $comment_point=0, $photo_point=0) {        
        
        $today = $this->get_today_ist_date('Y-m-d');
        $current_date_time  = $this->get_date('%Y-%m-%d %H:%i:%s');
        // Prevent accidental multiple save for same day If other thread is working on same
        $select_array = array();
        $select_array[] = "UP.UserActivityPointsID, UP.TotalPoint, UP.PostPoint, UP.CommentPoint, UP.PhotoPoint";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERACTIVITYPOINTS .' UP ');
        $this->db->where('UP.UserID', $user_id);
        $this->db->where("UP.PointDate", $today);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        if(!empty($result['UserActivityPointsID'])) {
            $total_point =  $post_point + $comment_point + $photo_point;
            $this->db->set('PostPoint', "PostPoint+($post_point)", FALSE);
            $this->db->set('CommentPoint', "CommentPoint+($comment_point)", FALSE);
            $this->db->set('PhotoPoint', "PhotoPoint+($photo_point)", FALSE);
            $this->db->set('TotalPoint', "TotalPoint+($total_point)", FALSE);
            $this->db->set('ModifiedDate', $current_date_time);
            $this->db->where('UserActivityPointsID', $result['UserActivityPointsID']);
            $this->db->update(USERACTIVITYPOINTS);
        } else {
            // Get user last total point
            $select_array = array();
            $select_array[] = "UP.UserID, UP.TotalPoint";
            $this->db->select(implode(',', $select_array),false);
            $this->db->from(USERACTIVITYPOINTS .' UP ');
            $this->db->where('UP.UserID', $user_id);
            $this->db->where("UP.PointDate < '$today'", NULL, FALSE);
            $this->db->order_by('UP.PointDate', 'DESC');
            $this->db->limit(1);
            $query = $this->db->get();
            $user_last_score = $query->row_array();            
            
            // get user total point
            $total_point = 0; 
            if(!empty($user_last_score['TotalPoint'])) {
                $total_point = $user_last_score['TotalPoint'];
            }
            
            $total_point =  $total_point + $post_point + $comment_point + $photo_point;
            
            // Store user calculated data
            $point_data = array(
                'UserID'    => $user_id,
                'PostPoint' => $post_point,
                'CommentPoint' => $comment_point,
                'PhotoPoint' => $photo_point,                
                'TotalPoint' => $total_point,                
                'PointDate' => $today,
                'CreatedDate' => $current_date_time,
                'ModifiedDate' => $current_date_time,
            );
            $this->db->insert(USERACTIVITYPOINTS, $point_data);
        }
    }

    /**
     * Used to check max point limit
     */
    public function check_max_point($data) {
        $user_id            = $data['UserID'];
        $point              = $data['Point'];         
        $type               = $data['PT'];
        $activity_id        = $data['ActivityID'];
        $activity_type_id   = $data['ActivityTypeID'];
        $creator_id         = $data['CreatorID'];

        if(empty($type)) {
            return false;
        }

        if($activity_id && in_array($type, array(1,2))) {
            if($activity_type_id == 20) { //check comment point from unique people
                if ($this->check_unique_people_comment($data)) {
                    return false;
                }
            }

            $max_point_limit = array('1' => 50, '2' => 25);

             //check for max point for particular post
            $this->db->select('SUM(Point) as Point');            
            $this->db->where('ActivityID', $activity_id, FALSE);
            $this->db->where('UserID', $user_id, FALSE);
            $this->db->where('PointType', $type);
            $this->db->where('StatusID', 2, FALSE);                
            $query = $this->db->get(USERACTIVITYPOINTHISTORY);
            $result = $query->row_array();
            if(!empty($result)) {
                $total_point = $point;
                if(!empty($result['Point'])) {
                    $total_point = $total_point + $result['Point'];
                }  
                
                if($total_point > $max_point_limit[$type]) {
                    return false;
                }
            }
        }

        $day_max_point_limit = array('1' => 100, '2' => 50, '3' => 25); //1 - Post, 2 - Comment, 3 - Photo
        $point_type = array('1' => 'PostPoint', '2' => 'CommentPoint', '3' => 'PhotoPoint');
        $today = $this->get_today_ist_date('Y-m-d');
        $select_field = $point_type[$type];
        $select_value = 0;
        $this->db->select($select_field,false);
        $this->db->from(USERACTIVITYPOINTS .' UP ');
        $this->db->where('UP.UserID', $user_id, FALSE);
        $this->db->where("UP.PointDate", $today);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        if(!empty($result[$select_field])) {
            $select_value = $result[$select_field];

            $select_value = $select_value + $point;
        }

        if($select_value < $day_max_point_limit[$type]) {
            return true;
        }
        return false;
    }

     /**
     * Used to check unique people comment point
     */
    public function check_unique_people_comment($data) {
        $this->db->select('PointHistoryID');   
        $this->db->where('ActivityTypeID', $data['ActivityTypeID'], FALSE);         
        $this->db->where('ActivityID', $data['ActivityID'], FALSE);
        $this->db->where('UserID', $data['UserID'], FALSE);
        $this->db->where('CreatorID', $data['CreatorID'], FALSE);
        $this->db->where('PointType', $data['PT'], FALSE);
        $this->db->where('StatusID', 2);        
        $this->db->limit(1);
        $query = $this->db->get(USERACTIVITYPOINTHISTORY);
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Used to add point for user
     */
    function add_point($data) {

        $activity_type_id   = $data['ActivityTypeID'];
        $entity_type        = $data['EntityType'];
        $entity_id          = $data['EntityID'];
        $user_id            = isset($data['UserID']) ? $data['UserID'] : 0;
        if(empty($user_id)) {
            if($entity_type == 1) {
                $user_id = get_detail_by_id($entity_id, 0, "UserID");
                $data['UserID'] = $user_id;
            }            
        }

        if(in_array($activity_type_id, array(46,47))) { // This for solution & possible solution
            $activity_id          = $data['ActivityID'];
            $this->load->model('activity/activity_model');
            $ward_ids = $this->activity_model->get_activity_ward_ids($activity_id);
            if(in_array(1, $ward_ids)) {
                $data['Point'] = $this->get_activity_type_score($activity_type_id.'_c');
            } else {
                $data['Point'] = $this->get_activity_type_score($activity_type_id);
            }

            $point_data = array('EntityID' => $entity_id, 'EntityType' => 2, 'ActivityTypeID' => 46, 'ParentID' => -1);
            $this->revert_point($point_data);
        } else {
            $data['Point'] = $this->get_activity_type_score($activity_type_id);
        }
        
        
        if($user_id) {
            $this->user_activity_point($data);
        }        
    }


     /**
     * Used to add media point for user
     */
    function add_media_point($data) {
        $media_guids    = $data['MediaGUIDs'];
        $user_id        = $data['UserID'];
        $album_id        = isset($data['AlbumID']) ? $data['AlbumID'] : 0;
        $point_data = array('EntityType' => 4, 'UserID' => $user_id, 'ActivityTypeID' => 48, 'AlbumID' => $album_id, 'ActivityID' => 0, 'OID' => 0, 'PT' => 3);        
        foreach ($media_guids as $key => $media_guid) {
            $media_id = get_detail_by_guid($media_guid, 21, 'MediaID');
            if(!empty($media_id)) {
                $point_data['EntityID'] = $media_id;
                $this->add_point($point_data);
            }
        }
    }

    /**
     * Used to revert user point
     */
    function revert_point($data) {
        $entity_type        = $data['EntityType'];
        $entity_id          = $data['EntityID'];
        $album_id          = isset($data['AlbumID']) ? $data['AlbumID'] : 0;
        $parent_id          = isset($data['ParentID']) ? $data['ParentID'] : 0;
        $activity_type_id   = isset($data['ActivityTypeID']) ? $data['ActivityTypeID'] : 0;
        
        if(in_array($activity_type_id, array(43,44,45,51))) { // for all ward, city news, classified, amazing comment
            $this->db->select('PointHistoryID, PointType, Point, UserID');
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->where('ActivityTypeID', $activity_type_id);
            $this->db->where('StatusID', 2);
        } else if(in_array($activity_type_id, array(46,47))) { // for solution & possible solution
            $this->db->select('PointHistoryID, PointType, Point, UserID');
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->where_in('ActivityTypeID', array(46,47));
            $this->db->where('StatusID', 2);
        } else if($entity_type == 1) { //if post delete
            $this->db->select('PointHistoryID, PointType, SUM(Point) as Point, UserID');            
            $this->db->where('ActivityID', $entity_id);
            $this->db->where('StatusID', 2);
            $this->db->group_by('UserID, PointType');
        } else if(!empty($album_id)) { //if album delete
            $this->db->select('PointHistoryID, PointType, SUM(Point) as Point, UserID');            
            $this->db->where('AlbumID', $album_id);
            $this->db->where('StatusID', 2);
            $this->db->group_by('UserID, PointType');
        } else { //if comment delete, un like
            $this->db->select('PointHistoryID, PointType, Point, UserID');
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->where('StatusID', 2);
        }  
        $query = $this->db->get(USERACTIVITYPOINTHISTORY);
        if ($query->num_rows() > 0) {
            $current_date_time  = $this->get_date('%Y-%m-%d %H:%i:%s');
            $point_history_ids = array();
            $results = $query->result_array();
            foreach ($results as $result) {  
                $point_history_id   = $result['PointHistoryID'];
                $point_type         = $result['PointType'];
                $point              = $result['Point'];
                $user_id            = $result['UserID'];

                $post_point = ($point_type == 1) ? $point : 0;
                $comment_point = ($point_type == 2) ? $point : 0;
                $photo_point = ($point_type == 3) ? $point : 0;

                $this->update_user_daily_point($user_id, -$post_point, -$comment_point, -$photo_point);

                $point_history_ids[] =  $point_history_id;
            }
            if(!empty($point_history_ids)) {                
                $this->db->set('StatusID', 3);
                $this->db->set('ModifiedDate', $current_date_time);
                if($entity_type == 1 && !in_array($activity_type_id, array(43,44,45,46,47))) {
                    $this->db->where('ActivityID', $entity_id);
                } else if(!empty($album_id)) {
                    $this->db->where('AlbumID', $album_id);
                } else {
                    $this->db->where_in('PointHistoryID', $point_history_ids);
                }                
                $this->db->update(USERACTIVITYPOINTHISTORY);
            }           
        }
        $query->free_result();

        if( $entity_type == 2) {
            if(empty($parent_id)) {
                $this->revert_comment_reply_point($entity_id); // when comment delete then revert points of all replys on that comment  
            }
            $this->revert_comment_like_point($entity_id);
        }
        return true;
    }

    /**
     * Used to get like object details
     */
    function like_object_details($like_id, $point_array) {
        $this->db->select('EntityID, EntityType');
        $this->db->limit(1);
        $this->db->where('PostLikeID', $like_id);
        $this->db->where('StatusID', 2);
        $query = $this->db->get(POSTLIKE);
        $point_array['EntityType'] = 3;
        $point_array['ActivityID'] = 0;
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $entity_type = $row['EntityType'];
            $entity_id = $row['EntityID'];
            switch ($entity_type) {
                case 'ACTIVITY':
                    $entity = get_detail_by_id($entity_id, 0, "ActivityID, UserID", 2);
                    if(!empty($entity)) {                        
                        $point_array['PT'] = 1; 
                        $point_array['ActivityID'] = $entity['ActivityID']; 
                        $point_array['OID'] = $entity['UserID']; 
                    }
                break;
                case 'MEDIA':
                    $this->db->select('M.MediaID, M.UserID, M.MediaSectionID, M.MediaSectionReferenceID');
                    $this->db->from(MEDIA . ' AS M');
                    $this->db->join(MEDIAEXTENSIONS . ' AS ME', 'ME.MediaExtensionID=M.MediaExtensionID');
                    $entity = $this->db->where('M.MediaID', $entity_id)->get()->row_array();
                    if(!empty($entity)) {                                                  
                        if($entity['MediaSectionID'] == 3) {
                            $point_array['PT'] = 1;
                            $point_array['ActivityID'] = $entity['MediaSectionReferenceID'];
                            $point_array['OID'] = $entity['UserID']; 
                        }
                    }
                case 'COMMENT':
                    $entity = get_detail_by_id($entity_id, 20, "PostCommentID, UserID, EntityID, EntityType, IsPointAllowed", 2);
                    if(!empty($entity)) {   
                        if($entity['IsPointAllowed'] == 1) { //don't give point for comment
                            $point_array['ActivityID'] = 0;
                        } else if($entity['EntityType'] == 'ACTIVITY') {
                            $point_array['PT'] = 2; 
                            $point_array['ActivityID'] = $entity['EntityID']; 
                            $point_array['OID'] = $entity['UserID'];
                        } else if($entity['EntityType'] == 'MEDIA') {
                            $this->db->select('M.MediaSectionID, M.MediaSectionReferenceID');
                            $this->db->from(MEDIA . ' AS M');
                            $this->db->join(MEDIAEXTENSIONS . ' AS ME', 'ME.MediaExtensionID=M.MediaExtensionID');
                            $media = $this->db->where('M.MediaID', $entity['EntityID'])->get()->row_array();
                            if(!empty($media)) {                                                  
                                if($media['MediaSectionID'] == 3) {
                                    $point_array['PT'] = 2;
                                    $point_array['ActivityID'] = $media['MediaSectionReferenceID'];
                                    $point_array['OID'] = $entity['UserID']; 
                                }
                            }
                        }                       
                    }
                break;
            }
        }
        $point_array['Point']  = 0;  //Like point 0 for owner of like    
        $query->free_result();    
        return $point_array;
    }

    /**
     * Used to get all replies of an comment and revert point
     */
    function revert_comment_reply_point($comment_id) {
        $this->db->select('PostCommentID');
        $this->db->where('ParentCommentID', $comment_id);
        $query = $this->db->get(POSTCOMMENTS);
        if ($query->num_rows() > 0) {
            $results = $query->result_array();
            foreach ($results as $result) {  
                $point_data = array('EntityID' => $result['PostCommentID'], 'EntityType' => 2, 'ParentID' => $comment_id);
                $this->revert_point($point_data);
                // initiate_worker_job('revert_point', $point_data,'','point');
            }
        }
        $query->free_result();
    }


    /**
     * Used to get all likes of an comment and revert point
     */
    function revert_comment_like_point($comment_id) {

        $this->db->select('PostLikeID, EntityID, EntityType');
        $this->db->where('EntityID', $comment_id);
        $this->db->where('EntityType', 'COMMENT');        
        $this->db->where('StatusID', 2);
        $query = $this->db->get(POSTLIKE);
        if ($query->num_rows() > 0) {
            $results = $query->result_array();
            foreach ($results as $result) {  
                $point_data = array('EntityID' => $result['PostLikeID'], 'EntityType' => 3, 'ParentID' => 0);
                $this->revert_point($point_data);
                // initiate_worker_job('revert_point', $point_data,'','point');
            }
        }
        $query->free_result();
    }

    /**
     * Used to get all replies of an comment and revert point
     */
    function revert_media_point($media_ids) {

        $this->db->select('UserID, MediaID, MediaSectionID, MediaSectionReferenceID, NoOfLikes, NoOfComments, NoOfCommentReplies');
        $this->db->where_in('MediaID', $media_ids);
        $query = $this->db->get(MEDIA);                
        if ($query->num_rows()) {
            $results = $query->result_array();
            $entity_type = 'MEDIA';
            foreach ($results as $result) { 

                if($result['NoOfLikes'] > 0) {
                    $this->db->select('PostLikeID');
                    $this->db->where('EntityID', $result['MediaID']);
                    $this->db->where('EntityType', $entity_type);
                    $this->db->where('StatusID', 2);
                    $like_query = $this->db->get(POSTLIKE);
                    if ($query->num_rows() > 0) {
                        $row = $like_query->row_array();

                        $point_data = array('EntityID' => $row['PostLikeID'], 'EntityType' => 3);
                        initiate_worker_job('revert_point', $point_data,'','point');
                    } 
                    $like_query->free_result();                   
                }

                if(($result['NoOfComments'] + $result['NoOfCommentReplies']) > 0) {
                    $this->db->select('ParentCommentID, PostCommentID');
                    $this->db->where('EntityID', $result['MediaID']);
                    $this->db->where('EntityType', $entity_type);
                    $this->db->where('StatusID', 2);
                    $comment_query = $this->db->get(POSTCOMMENTS);
                    if ($comment_query->num_rows() > 0) {
                        $row = $comment_query->row_array();

                        $point_data = array('EntityID' => $row['PostCommentID'], 'EntityType' => 2, 'ParentID' => $row['ParentCommentID']);
                        initiate_worker_job('revert_point', $point_data,'','point');
                    }   
                    $comment_query->free_result();                 
                }
            }
        }
        $query->free_result();
    }

    public function calculate_users_points($page_no = 1, $page_size = 200) {
        $users_data = $this->get_users_for_point_calculation($page_no, $page_size);
        
        if(!empty($users_data)) {
            $this->db->update_batch(USERDETAILS, $users_data, 'UserID');
            
            //Recursive till all users points is not calculated for today.
            $this->calculate_users_points(++$page_no);            
        }
    }

    public function get_users_for_point_calculation($page_no, $page_size = 200) {
        $select_array = array();
        
        $offset = ($page_no - 1) * $page_size;

        $today = $this->get_today_ist_date('Y-m-d', 1);

        $select_array[] = "UP.TotalPoint, UP.UserID";
        $this->db->select(implode(',', $select_array),false);
        $this->db->from(USERACTIVITYPOINTS .' UP ');
        $this->db->where("UP.PointDate", $today);
        $this->db->order_by('UP.UserActivityPointsID', 'DESC');
        $this->db->limit($page_size, $offset);
        $query = $this->db->get();
        //echo $this->db->last_query(); die; 
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }      
        return array();
    }

    public function get_today_ist_date($format, $diff_date = 0) {
        $current_time_zone = date_default_timezone_get();
        $time_zone = 'Asia/Calcutta';
        date_default_timezone_set($time_zone);
        if($diff_date) {
            $today_date = date($format, strtotime(date($format) .' -'.$diff_date.' day'));
        } else {
            $today_date = date($format);
        }
        
        date_default_timezone_set($current_time_zone);
        return $today_date;
    }

    public function calculate_top_contributor($data) {
        $all = safe_array_key($data, 'all', 0);
       // $this->calculate_tag_top_contributor(array('TagID' => 0));
       if(empty($all)) {
            initiate_worker_job('calculate_tag_top_contributor', array('TagID' => 0), '', 'top_contributor');
       } else {
            $this->db->select('T.TagID, T.Name', false);
            $this->db->from(TAGS . ' T');
            $this->db->order_by('T.TagID', 'ASC');
            $this->db->where('T.TagType', 1);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $tag_list = $query->result_array();   
                foreach ($tag_list as $tag) { 
                    //$this->calculate_tag_top_contributor(array('TagID' => $tag['TagID']));
                    initiate_worker_job('calculate_tag_top_contributor', array('TagID' => $tag['TagID']), '', 'top_contributor');
                }         
            }
       }
    }

    public function calculate_tag_top_contributor($data) {
        $tag_id = safe_array_key($data, 'TagID', 0);

        $this->db->where('TagID', $tag_id);
        $this->db->delete(TOPCONTRIBUTORS);

        $exclude_user_ids = array(0);
        if(empty($tag_id)) {
            if (CACHE_ENABLE)  {
                $this->cache->delete('ttp_0');
            }
            $ninety_days_before_date = $this->get_today_ist_date('Y-m-d', 90);
            $this->db->select('SUM(P.PostPoint+P.CommentPoint+P.PhotoPoint) AS Total_Point', false);
            $this->db->select('P.UserID', false);
            $this->db->from(USERACTIVITYPOINTS . ' P');
            $this->db->join(USERS . ' AS U', 'U.UserID=P.UserID');
            $this->db->where('P.PointDate >= ', $ninety_days_before_date);
            $this->db->where_not_in('P.UserID', $exclude_user_ids);
            $this->db->group_by('P.UserID');    
            $this->db->order_by('Total_Point', 'DESC');
            $this->db->limit(40); 
            $query = $this->db->get();
            
            if ($query->num_rows()) {
                $results = $query->result_array();   
                $point_data = array();
                $top_user_ids = array();
                foreach ($results as $result) { 
                    $point_data[] = array(
                        'TagID' => 0,
                        'UserID' => $result['UserID'],
                        'TotalPoint' => $result['Total_Point'],
                        'CreatedDate' => $this->get_date('%Y-%m-%d %H:%i:%s')
                    );
                    $top_user_ids[] = $result['UserID'];
                }   
                if(!empty($point_data)) {
                    $this->db->insert_batch(TOPCONTRIBUTORS, $point_data);

                    if (CACHE_ENABLE) {
                        $top_user_ids_str = implode(',', $top_user_ids);
                        $this->cache->save('ttp_0', $top_user_ids_str, CACHE_EXPIRATION);
                    }
                }
            } 
        } else {
            if (CACHE_ENABLE)  {
                $this->cache->delete('ttp_'.$tag_id);
            }
            $ninety_days_before_date = get_current_date('%Y-%m-%d %H:%i:%s', 90);
            $time_zone = 'Asia/Calcutta';
            $this->db->select('SUM(P.Point) AS Total_Point', false);
            $this->db->select('P.UserID', false);
            $this->db->from(USERACTIVITYPOINTHISTORY . ' P');
            $this->db->join(USERS . ' AS U', 'U.UserID=P.UserID');
            $this->db->where('P.StatusID', 2);
            $this->db->where_not_in('P.UserID', $exclude_user_ids);
            $this->db->where("DATE_FORMAT(CONVERT_TZ(P.CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d %H:%i:%s') >= '" . $ninety_days_before_date . "'", NULL, FALSE);
            $this->db->where("P.ActivityID IN (SELECT EntityID FROM EntityTags WHERE EntityType='ACTIVITY' AND TagID=".$tag_id.")");
            $this->db->group_by('P.UserID');    
            $this->db->order_by('Total_Point', 'DESC');
            $this->db->limit(6); 
            $query = $this->db->get();
            if ($query->num_rows()) {
                $results = $query->result_array();   
                $point_data = array();
                $top_user_ids = array();
                foreach ($results as $result) { 
                    $point_data[] = array(
                        'TagID' => $tag_id,
                        'UserID' => $result['UserID'],
                        'TotalPoint' => $result['Total_Point'],
                        'CreatedDate' => $this->get_date('%Y-%m-%d %H:%i:%s')
                    );
                    $top_user_ids[] = $result['UserID'];
                }   
                if(!empty($point_data)) {
                    $this->db->insert_batch(TOPCONTRIBUTORS, $point_data);
                    if (CACHE_ENABLE) {
                        $top_user_ids_str = implode(',', $top_user_ids);
                        $this->cache->save('ttp_'.$tag_id, $top_user_ids_str, CACHE_EXPIRATION);
                    }
                }
            } 
        }

    }
}

//End of file users_model.php
