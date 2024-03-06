<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activityrule_model extends Common_Model
{
    
    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'activity/activity_model',
            'users/user_model',
            'group/group_model',
            'forum/forum_model',
            'favourite_model',
        ));
        
        //$this->load->helper('activity');
    }
    
    /**
     * [getActivityRules Get the rules for showing news feed[activity] of user]
     * @param  [Int]       $userId        [Current User ID]
     * @param  [Int]       $cityId        [Current User's City ID]
     * @return [Array]                    [Rules array]
     */
    public function getActivityRules($userId=0, $cityId = 0, $setOtherData = false, $location = NULL, $FinalProfileIDsCheck = false){//return array();
        
        $cache_data=array();
        $cache_time = 900;
        return $cache_data;
        /*get the rules from cahce if cache enabled & userId set*/
        if(CACHE_ENABLE && $userId && 0) {
            $cache_data=$this->cache->get('rule_user_'.$userId);
            if(!empty($cache_data)) {
                /*return rules array*/
                return $cache_data['Rules'];
            }
        }
                        
        /*find the records in DB if not presents in cache*/
        $data = $this->post_data;
        
        if($setOtherData) {
            $this->setOtherActivityRelatedData($userId);
        }
        
        //If failed to satisfy global rules
        if($this->getActivityRulesCheck($userId)) {
            $cache_data['Rules']= array();
            if(CACHE_ENABLE) {
                $this->cache->save('rule_user_'.$userId, $cache_data, $cache_time);
            }
            return $cache_data['Rules'];
        }
        
        
        //If user not logged in 
        if(!$userId || empty($data[AUTH_KEY])) {
            
            if(!$cityId && $location) {
                $cityName = !empty($location->city) ? $location->city : '';
                $this->db->select('*');
                $this->db->from(CITIES);
                $this->db->where("Name = '$cityName' ",NULL,FALSE);
                $this->db->limit(1);
                $q = $this->db->get();
                $cityData = $q->row_array();
                $cityId = isset($cityData['CityID']) ? $cityData['CityID'] : 0;
            }
            
            //without login case 
            $this->db->select('*');
            $this->db->from(DEFAULTACTIVITYRULE);
            $this->db->where("FIND_IN_SET('".$cityId."',`CityIDs`) > 0 ",NULL,FALSE);
            $this->db->where('StatusID', 2);
            $this->db->where('Gender', 0);
            $this->db->where('AgeGroupID', 0);
            $this->db->where('IntersetIDs IS NULL', NULL, FALSE);
            $this->db->where('TagIDs IS NULL', NULL, FALSE);
            $this->db->where('IsEditable', 1);
            
            if($FinalProfileIDsCheck) {
                $this->db->where('FinalProfileIDs != ""', NULL, FALSE);
            }
            
            $this->db->order_by("DisplayOrder", 'ASC');
            $this->db->limit(1);
            $q = $this->db->get();
            if ($q->num_rows()) {
                foreach($q->result_array() as $result){
                    if($result['AllPublicPost']==1)
                        $cache_data['Rules']['AllPublicPost']= 1;
                    
                    $cache_data['Rules']['PostFromUser']= $result['PostFromUser'];
                    
                    if(!empty($result['FinalPostIDs']))
                    {
                        if(!empty($cache_data['Rules']['FinalPostIDs']))
                        {
                            $uniquePostIds = implode(',', array_keys(array_flip(explode(',', $cache_data['Rules']['FinalPostIDs'].",".$result['FinalPostIDs']))));
                        }else{
                            $uniquePostIds = $result['FinalPostIDs'];
                        }
                        $cache_data['Rules']['FinalPostIDs']= $uniquePostIds;
                    }
                }
            } else {
                //if no rule match set default rule
                $cache_data['Rules']= $this->getDefaultRule();
            }

            //$cache_data['Rules']= $this->getDefaultRule();
            if(CACHE_ENABLE) {
                $this->cache->save('rule_user_'.$userId, $cache_data, $cache_time);
            }
            return $cache_data['Rules'];
        }
        
        $login_session_key = $data[AUTH_KEY];
        $login_user_data = get_analytics_id($login_session_key, 0, 'CityID,AgeGroupID,UserID',2); 
        
        if (empty($login_user_data) || !isset($login_user_data['UserID'])) {
            //if user not found from loginSessionKey then return empty array
            $cache_data['Rules']= $this->getDefaultRule();
            if(CACHE_ENABLE) {
                $this->cache->save('rule_user_'.$userId, $cache_data, $cache_time);
            }
            return $cache_data['Rules'];
        }
        
        /*In case of logged in user find the user's cityId, gender, registration date & agegroup from loginAnalytics*/
        
        $this->db->select('U.CreatedDate AS RegistrationDate,U.Gender, Group_Concat(EC.CategoryID) as interests , Group_Concat(ET.TagID) as tags');
        $this->db->from(USERS . ' U');
        //$this->db->join(ANALYTICLOGINS . ' AL', 'U.UserID=AL.UserID AND AL.LoginSessionKey="'.$login_session_key.'"', 'left');
        $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID = U.UserID AND EC.ModuleID = 3', 'left');
        $this->db->join(ENTITYTAGS . ' ET', 'ET.EntityID = U.UserID AND ET.EntityType = "USER" ', 'left');
        $this->db->where('U.UserID', $login_user_data['UserID']);
        //$this->db->order_by('AL.AnalyticLoginID', 'DESC');
        $this->db->group_by(' U.UserID ');
        $this->db->limit(1);
        
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if (!$query->num_rows()) {
            //if user not found from loginSessionKey then return empty array
            $cache_data['Rules']= $this->getDefaultRule();
            if(CACHE_ENABLE) {
                $this->cache->save('rule_user_'.$userId, $cache_data, $cache_time);
            }
            return $cache_data['Rules'];
        }
        
        /*find the rules based on userId,gender, ageGroup, registartiondate, cityId*/
        $row = $query->row_array();
        $age_gruop_id = empty($login_user_data['AgeGroupID']) ? 0 : $login_user_data['AgeGroupID'];
        $this->db->select('*');
        $this->db->from(DEFAULTACTIVITYRULE);
        
        if($FinalProfileIDsCheck) {
            $this->db->where('FinalProfileIDs != ""', NULL, FALSE);
        }
        
        $this->db->where(" FIND_IN_SET('".$userId."',`UserIDs`) > ",0);
        $find_in_set_conds_tags = $this->createFindInSetConds($row['tags'], 'TagIDs');
        
        if($find_in_set_conds_tags) {
            $this->db->or_where(" ($find_in_set_conds_tags)",NULL,FALSE);
        }        
        
        $this->db->or_where(' ((Gender = 0 OR Gender = "'. $row['Gender'].'")',NULL,FALSE);
        $this->db->where(' ( AgeGroupID = 0 OR  AgeGroupID = "'.$age_gruop_id.'") ',NULL,FALSE);
        //$this->db->where('( RegistrationFromDate="0000-00-00" OR RegistrationFromDate >= "'.date("Y-m-d",strtotime($row['RegistrationDate'])).'") ',NULL,FALSE );
        //$this->db->where('( RegistrationToDate="0000-00-00" OR RegistrationToDate <= "'.date("Y-m-d",strtotime($row['RegistrationDate'])).'") ',NULL,FALSE );
        $this->db->where(" ( `CityIDs` is NULL OR FIND_IN_SET('".$login_user_data['CityID']."',`CityIDs`) > 0 ))",NULL,FALSE);
        
        $find_in_set_conds_interest = $this->createFindInSetConds($row['interests'], 'IntersetIDs');
        if($find_in_set_conds_interest) {
            $this->db->where(" ( `IntersetIDs` is NULL OR  $find_in_set_conds_interest)",NULL,FALSE);
        }   
        
        $this->db->where('StatusID',2);
        $this->db->where('IsEditable', 1);
        $this->db->order_by("DisplayOrder", 'ASC');
        $this->db->limit(1);        
        $q = $this->db->get();
        //echo $this->db->last_query(); die;
        if (!$q->num_rows()) {
            //if no rule match then set default rule
            $cache_data['Rules']= $this->getDefaultRule();
            if(CACHE_ENABLE) {
                $this->cache->save('rule_user_'.$userId, $cache_data, $cache_time);
            }
            return $cache_data['Rules'];
        }
        
        /*if multiple rules found than merge FinalPostIDs into a single string with unique ID's*/
        foreach($q->result_array() as $result) {
            if($result['AllPublicPost']==1 && !empty($result['PublicPost'])) {
                $cache_data['Rules']['AllPublicPost']= 1; 
                $cache_data['Rules']['PublicPost']= $result['PublicPost'];                 
            }
            $cache_data['Rules']['ActivityRuleID']= $result['ActivityRuleID'];
            $cache_data['Rules']['Welcome']= $result['Welcome'];
            $cache_data['Rules']['PostFromUser']= $result['PostFromUserTagUserIDs'];
            $cache_data['Rules']['FinalProfileIDs']= $result['FinalProfileIDs'];
            if(!empty($result['FinalPostIDs'])) {
                if(!empty($cache_data['Rules']['FinalPostIDs'])) {
                    $uniquePostIds = implode(',', array_keys(array_flip(explode(',', $cache_data['Rules']['FinalPostIDs'].",".$result['FinalPostIDs']))));
                } else {
                    $uniquePostIds = $result['FinalPostIDs'];
                }
                $cache_data['Rules']['FinalPostIDs']= $uniquePostIds;
            }
        }
        
        /*store the rules in cache and return */
        if(CACHE_ENABLE) {
            $this->cache->save('rule_user_'.$userId, $cache_data, $cache_time);
        }
        return $cache_data['Rules'];        
    }
    
    protected function getDefaultRule() {
        $cache_data = array();
        $this->db->select('*');
        $this->db->from(DEFAULTACTIVITYRULE);
        $this->db->where('IsEditable', 0);
        $this->db->where('StatusID',2);                    
        $this->db->limit(1);
        $q = $this->db->get();
        $rule= $q->row_array();
        $cache_data = array(
            'ActivityRuleID'=>$rule['ActivityRuleID'],
            'AllPublicPost'=>$rule['AllPublicPost'],
            'FinalPostIDs'=>$rule['FinalPostIDs'],
            'PublicPost' => $rule['PublicPost'],
            'Welcome' => $rule['Welcome'],
            'FinalProfileIDs' => $rule['FinalProfileIDs'],
            'PostFromUser' => $rule['PostFromUser'],
        );
        
        return $cache_data;
    }
    
    
    public function getActivityRulesCheck($userId) {
        
        $FriendFollowersList = $this->user_model->get_friend_followers_list();
        $activity_post_total = (int)isset($this->activity_user_total_records) ? $this->activity_user_total_records : 0; //101
        $user_no_of_friends = isset($FriendFollowersList['Friends']) ? count($FriendFollowersList['Friends']) : 0; //49
        
        /* get global configuration */
        $global_settings = $this->config->item("global_settings");
        $NoOfPost = $global_settings['NoOfPost']; //100
        $NoOfFriends = $global_settings['NoOfFriends']; //50
        //return true;
        
        //log_message('error', "userId : $userId    configuration no of post : $NoOfPost     configuration no of friends : $NoOfFriends   User no of posts : $activity_post_total     user no of friends : $user_no_of_friends");
        
        /*if total posts && no of friends are less than the defined values then fetch the rules to show the activity*/
        if ($NoOfPost <= $activity_post_total && $NoOfFriends <= $user_no_of_friends) {
             return TRUE;
        } 
        
        return FALSE;
    }
    
    public function setUserActivityTotal($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter, $post_type,$tags) {
        // If user not logged in 
        if(!$user_id) {
            return ;
        }
        
        $this->activity_user_total_records = 0;
        return;
            
        $totalRecords = FALSE;
        if(CACHE_ENABLE) {
            $totalRecords = $this->cache->get('user_no_of_post_for_rule' . $user_id);
        }
        
        if($totalRecords !== FALSE) { //Disable caching for testing.
            $this->activity_user_total_records = $totalRecords;
            return;
        }
        
        // Get record and save in cache
        $totalRecords = $this->activity_user_total_records = (int)$this->activity_model->getFeedActivities(
                $user_id, $page_no, $page_size, 2, 0, 0, 2, '', 
                '', '', 0, 1, '', '', [], '', '', '',array(),1,2,'',[]
        );
        if(CACHE_ENABLE) {
            $this->cache->save('user_no_of_post_for_rule' . $user_id, $totalRecords, CACHE_EXPIRATION);
        }
        
    }

    /*
     * [Get activity ids for public posts]
     * @return activityIds
     */
    public function condition_for_rule_posts($rules, $modules_allowed, $activity_type_allow, $user_id, $page_list, $exclude_ids) { //return;
        
        if(empty($rules)) {
            return;
        }
        
        $final_post_ids = !empty($rules['FinalPostIDs']) ? $rules['FinalPostIDs'] : '0';
        $all_public_post = !empty($rules['AllPublicPost']) ? $rules['AllPublicPost'] : 0;
        $public_post = !empty($rules['PublicPost']) ? $rules['PublicPost'] : '';
                
        $condition = '';
        $public_group_query = "SELECT G.`GroupID` FROM ".GROUPS." AS G WHERE G.`IsPublic` = 1 AND G.StatusID = 2";
        $public_events_query = "SELECT E.`EventID` FROM ".EVENTS." AS E WHERE E.`Privacy` = 'PUBLIC' AND E.IsArchive = 0 AND E.IsDeleted = 0 ";
        
         // Check parent activity privacy for shared activity
        $privacy_condition = "  
        IF(A.ActivityTypeID IN(9,10,14,15),
            (
                CASE
                    WHEN A.ActivityTypeID IN(9,10) 
                        THEN
                            A.ParentActivityID=(
                            SELECT ActivityID FROM " . ACTIVITY . " WHERE StatusID=2 AND A.ParentActivityID=ActivityID AND
                            (
                            IF(Privacy=1 AND ActivityTypeID!=7,true,false) OR
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $public_group_query . "),false))
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
                
        
        if ($public_group_query) {
            $case_array[]=" A.ActivityTypeID IN (4,7) 
                                OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=1) 
                                THEN 
                                    A.ModuleID=1 AND A.ModuleEntityID IN(" . $public_group_query . ") ";
        }
        
        
        if (!empty($public_events_query)) {
            $case_array[]="A.ActivityTypeID IN (11,23,14) 
                 OR (A.ActivityTypeID=24 AND A.ModuleID=14)
                 THEN 
                  A.ModuleID=14 AND A.ModuleEntityID IN(" . $public_events_query . ")";
        }
        
        if (!empty($page_list)) {
            $case_array[]="A.ActivityTypeID IN (12,16,17) 
                 OR ((A.ActivityTypeID=23 OR A.ActivityTypeID=24) AND A.ModuleID=18)
                 THEN 
                  A.ModuleID=18 AND (" . $page_list . ")";
        }
        
        if(!empty($case_array))
        {
            $condition= " ( CASE WHEN ".  implode(" WHEN ", $case_array)." ELSE 1 END ) ";
        }
        

        //$condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        //$condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        //$condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) ";
        
        //$condition .= "  OR ";
        //$condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null)";
        

        //$condition .= " )";
        
        
        
        
        
        
//        $final_post_ids = !empty($rules['FinalPostIDs']) ? $rules['FinalPostIDs'] : '0';        
//        $all_public_post = !empty($rules['AllPublicPost']) ? $rules['AllPublicPost'] : 0;
//        $public_post = !empty($rules['PublicPost']) ? $rules['PublicPost'] : '';
//        
//        $public_feed_query = $this->get_rule_query_for_public_feed($all_public_post, $public_post);
//        $extra_or_query = ($public_feed_query) ? " OR  A.ActivityID IN ($public_feed_query) " : '';
//        $where_condition = "   A.ActivityID IN ($final_post_ids) $extra_or_query  ";
//        
//        $this->db->join(ACTIVITY . ' A_RL', 'A_RL.ActivityID = A.ActivityID AND ( ' . $where_condition . ' ) ', 'LEFT', false);
//        $this->db->order_by('A_RL.ModifiedDate','DESC');
//        
//        return;
        
        
        $this->db->or_where_in(' /*  Rules query starts */  (( A.ModuleID', $modules_allowed);
        $this->db->where_in('A.ActivityTypeID', $activity_type_allow);
        $this->db->where('A.ActivityTypeID!="13"', NULL, FALSE);
        $this->db->where('A.Privacy', 1);
        $this->db->where_not_in('A.ActivityID', explode(',', $exclude_ids));
        $this->db->where("IF(A.UserID='" . $user_id . "',A.StatusID IN(1,2,10),A.StatusID=2)", null, false);
        
        $this->db->where($privacy_condition, null, false);
        $this->db->where($condition, NULL, FALSE);
        
        $public_feed_query = $this->get_rule_query_for_public_feed($all_public_post, $public_post);
        $extra_or_query = ($public_feed_query) ? " OR  /* Public feed query */  A.ActivityID IN ($public_feed_query) /* Public feed query */" : '';
        
        $post_from_users = !empty($rules['PostFromUser']) ? $rules['PostFromUser'] : '';
        $extra_specific_user_query = ($post_from_users) ? " OR  /* Specific user query */ A.ActivityID IN ( Select ActivityID From ".ACTIVITY." Where UserID IN( $post_from_users ) ) /* Specific user query */" : '';
        
        $this->db->where(" (  A.ActivityID IN ($final_post_ids)  $extra_specific_user_query  $extra_or_query  )   )  /*  Rules query end */ ", Null, false);
      
    }
    
    public function rule_conds_for_not_logged_in_user($rules) {
        if(empty($rules)) {
            return;
        }
        
        $final_post_ids = !empty($rules['FinalPostIDs']) ? $rules['FinalPostIDs'] : '0';        
        $all_public_post = !empty($rules['AllPublicPost']) ? $rules['AllPublicPost'] : 0;
        $public_post = !empty($rules['PublicPost']) ? $rules['PublicPost'] : '';
        
        $public_feed_query = $this->get_rule_query_for_public_feed($all_public_post, $public_post);
        $extra_or_query = ($public_feed_query) ? " OR  A.ActivityID IN ($public_feed_query) " : '';
        $where_condition = "  A.ActivityID IN ($final_post_ids) $extra_or_query   ";
        
        $this->db->join(ACTIVITY . ' A_RL', 'A_RL.ActivityID = A.ActivityID AND ( ' . $where_condition . ' ) ', 'LEFT', false);
        $this->db->order_by('A_RL.ModifiedDate','DESC');
    }
    
    public function get_rule_query_for_public_feed($all_public_post, $public_post) {   //return '';
        
        if(empty($all_public_post) || empty($public_post)) {
            return '';
        }
        
        $city_ids = array();
        $public_post = json_decode($public_post, true);
        $gender = isset($public_post['Gender']) ? $public_post['Gender'] : 0;
        $age_group_id = isset($public_post['AgeGroupID']) ? $public_post['AgeGroupID'] : 0;
        $locations = isset($public_post['Location']) ? $public_post['Location'] : '';

        if ($locations) {
            foreach ($locations as $key => $location) {
                if (!empty($location['CityID'])) {
                    $city_ids[] = $location['CityID'];
                }
            }
        }

        
        
        $sql_where_in_query = ' Select A.ActivityID From '.ACTIVITY. ' A ';
        
        if (!empty($gender)) {
            $sql_where_in_query .= ' Inner JOIN  '.USERS . ' RLU On RLU.UserID=A.UserID AND RLU.Gender="' . $gender . '"';
        } else {
            $sql_where_in_query .= ' Inner JOIN  '.USERS . ' RLU On RLU.UserID=A.UserID ';
        }

        if (!empty($city_ids) && !empty($age_group_id)) {
            $sql_where_in_query .= ' Inner JOIN  '. ANALYTICLOGINS . ' LA ON  LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id . ' AND LA.CityID IN (' . implode(',', $city_ids) . ') ';
        } else if (!empty($city_ids)) {
            $sql_where_in_query .= ' Inner JOIN  '. ANALYTICLOGINS . ' LA ON  LA.AnalyticLoginID=A.AnalyticLoginID  AND LA.CityID IN (' . implode(',', $city_ids) . ') ';
        } else if (!empty($age_group_id)) {
            $sql_where_in_query .= ' Inner JOIN  '. ANALYTICLOGINS . ' LA ON  LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id;
        }
        
        return $sql_where_in_query;
    }
    
    protected function setOtherActivityRelatedData($user_id) {
        $this->user_model->set_user_time_zone($user_id);
        $this->user_model->set_user_profile_url($user_id);
        $this->user_model->set_friend_followers_list($user_id);
        $this->activity_model->set_block_user_list($user_id, 3);
        $this->group_model->set_user_group_list($user_id);
        $this->forum_model->set_user_category_list($user_id);
        $this->group_model->set_user_categoty_group_list($user_id);
        $this->favourite_model->set_user_favourite($user_id);            
        //$this->flag_model->set_user_flagged($user_id); 
        $this->activity_model->set_user_activity_archive($user_id);
        
        $this->setUserActivityTotal($user_id, 1, 10, 2, array(0), 0, 2, '', '', '', array(), '', array(), $user_id, 3, array(), array(1,2,4,7), array());
        
        
    }
    
    public function createFindInSetConds($values, $column) {
        $values = explode(',', $values);
        $conds = '';
        
        $values = array_unique($values);
        
        foreach($values as $value) {
            
            if(!$value) {
                continue;
            }
            
            if($conds) {
                $conds .= " OR (FIND_IN_SET($value , $column) > 0 ) ";
            } else {
                $conds = "( (FIND_IN_SET($value , $column) > 0 ) ";
            }
        }
        
        if($conds) {
            $conds .= ' )';
        }
        
        return $conds;
    }
    
    public function get_location_by_ip($ip_address) {
        $this->load->library('geoip');
        if(ENVIRONMENT!='production') {
            $ip_address = '103.21.54.66';
        }
        $record = $this->geoip->info($ip_address);

        return $record;
    }
    
    
  }
