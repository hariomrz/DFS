<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cronrule_model extends Common_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('users/friend_model');
        $this->load->model('notification_model');
    }

    /**
     * [calculate_default_activity Used to calculate default activity ids for each rule]
     * @return [type] [description]
     */
    function calculate_default_activity($rule_id = 0) {
        $today_date = get_current_date('%Y-%m-%d');
        $this->db->select("
            ActivityRuleID, PostWithTags, PopularPost, 
            PostFromUser, PostFromGroup, CustomizePostIDs, ProfilesWithTags,
            PopularProfiles, CustomizeProfiles, AllPublicPost, PublicPost,
            TrendingTags, PostFromUserTag
        ");
        $this->db->where('StatusID',2);
        if($rule_id) {
            $this->db->where('ActivityRuleID', $rule_id);
        } else {
            $this->db->where('LastCalculationDate < ', $today_date);
        }
        //$this->db->where('ActivityRuleID', $activity_rule_id);
        //$this->db->where('AllPublicPost',0);
        $query = $this->db->get(DEFAULTACTIVITYRULE);
        // echo $this->db->last_query();die;
        if (!$query->num_rows()) {
            return;
        }
        
        $result = $query->row_array();
        $activity_rule_id = $result['ActivityRuleID'];

        $this->db->where('ActivityRuleID', $activity_rule_id);
        $this->db->update(DEFAULTACTIVITYRULE, array('StatusID' => 1)); //for processing
        //Get Post IDS            
        $all_public_post = $result['AllPublicPost'];
        $public_post = $result['PublicPost'];
        $tag_ids = array();
        $city_ids = array();
        $interest_ids = array();
        $public_activity_ids = "";
        $tags_activity_ids = "";
        $popular_activity_ids = "";
        $activity_ids = "";
        if ($all_public_post) {
            if (!empty($public_post)) {
                //$activity_ids = $this->calc_default_activity_publicposts($public_post);  //Calculate it on the front end
            }
        } else {
            $post_with_tags     = $result['PostWithTags'];
            $popular_post       = $result['PopularPost'];
            $customize_post_ids = $result['CustomizePostIDs'];

            //POST IDs CALCULATION
            if (!empty($post_with_tags)) 
            {
                $tags_activity_ids = $this->calc_default_activity_postwithtags($post_with_tags);
            }
            else if (!empty($popular_post)) 
            {
                $popular_activity_ids = $this->calc_default_activity_popularposts($popular_post);
            }

            if (!empty($tags_activity_ids) && !empty($popular_activity_ids)) {
                $activity_ids = $tags_activity_ids . ',' . $popular_activity_ids;
            } else if (!empty($tags_activity_ids)) {
                $activity_ids = $tags_activity_ids;
            } else if (!empty($popular_activity_ids)) {
                $activity_ids = $popular_activity_ids;
            }

            if (!empty($activity_ids)) 
            {
                if(!empty($customize_post_ids))
                {
                    $activity_ids = $activity_ids . "," . $customize_post_ids;
                }
            } else {
                $activity_ids = $customize_post_ids;
            }
        }

        $final_activity_ids = explode(",", $activity_ids);
        $final_activity_ids = array_unique($final_activity_ids);
        rsort($final_activity_ids);
        $activity_ids = implode(",", $final_activity_ids);

        //End POST IDs CALCULATION
        //Get Profile IDS
        $profile_with_tags = $result['ProfilesWithTags'];
        $popular_profile = $result['PopularProfiles'];
        $customize_profile_ids = $result['CustomizeProfiles'];
        $tag_ids = array();
        $city_ids = array();
        $profile_ids = "";
        $tags_profile_ids = "";
        $popular_profile_ids = "";
        if (!empty($profile_with_tags)) {
            $tags_profile_ids = $this->calc_default_activity_profilewithtags($profile_with_tags);
        }

        if (!empty($popular_profile)) {
            $popular_profile_ids = $this->calc_default_activity_popularprofiles($popular_profile);
        }

        if (!empty($tags_profile_ids) && !empty($popular_profile_ids)) {
            $profile_ids = $tags_profile_ids . ',' . $popular_profile_ids;
        } else if (!empty($tags_profile_ids)) {
            $profile_ids = $tags_profile_ids;
        } else if (!empty($popular_profile_ids)) {
            $profile_ids = $popular_profile_ids;
        }

        if (!empty($profile_ids)) 
        {
            if(!empty($customize_profile_ids))
            {
                $profile_ids = $profile_ids . "," . $customize_profile_ids;
            }
        } 
        else 
        {
            $profile_ids = $customize_profile_ids;
        }
        
        // Get activity tranding tags.
        $activity_tranding_tags_ids = '';
        $tranding_tags = isset($result['TrendingTags']) ? $result['TrendingTags'] : '';
        if($tranding_tags) {
            $activity_tranding_tags_ids = $this->get_tranding_tags($tranding_tags, 'ACTIVITY');
        }
        
        
        $final_profile_ids = explode(",", $profile_ids);
        $final_profile_ids = array_unique($final_profile_ids);
        rsort($final_profile_ids);
        $profile_ids = implode(",", $final_profile_ids);
        //End Profile IDS
        $tag_user_ids = $this->get_users_for_usertags($result['PostFromUserTag']);
        
        
        $spefific_user_ids = array_unique(array_merge(explode(',', $result['PostFromUser']),  explode(',', $tag_user_ids) ));  
        $spefific_user_ids = implode(',', $spefific_user_ids);
        $spefific_user_ids = trim($spefific_user_ids, ',');
        
        $this->db->where('ActivityRuleID', $activity_rule_id);
        $this->db->update(DEFAULTACTIVITYRULE, array(
            'StatusID' => 2, 
            'FinalPostIDs' => $activity_ids, 
            'FinalProfileIDs' => $profile_ids, 
            'FinalTrandingTagsIDs' => $activity_tranding_tags_ids,
            'LastCalculationDate' => $today_date,
            'PostFromUserTagUserIDs' => $spefific_user_ids
        )); //processing Done
        
    }
    
    /*
     * [Get activity ids for post tags]
     * @return activityIds
     */
    public function calc_default_activity_postwithtags($post_with_tags) {
        $tags_activity_ids = '';
        
        $tag_ids                = array();
        $city_ids               = array();
        $interest_ids           = array();
        $profile_ids            = "";
        $tags_profile_ids       = "";
        $popular_profile_ids    = "";
        
        $post_with_tags = json_decode($post_with_tags, true);
        $tags           = isset($post_with_tags['Tag']) ? $post_with_tags['Tag'] : '';
        $gender         = isset($post_with_tags['Gender']) ? $post_with_tags['Gender'] : 0;
        $age_group_id   = isset($post_with_tags['AgeGroupID']) ? $post_with_tags['AgeGroupID'] : 0;
        $locations      = isset($post_with_tags['Location']) ? $post_with_tags['Location'] : '';
        $interests      = isset($post_with_tags['Interest']) ? $post_with_tags['Interest'] : '';
        
        if(!($tags || $gender || $age_group_id || $locations || $interests)) {
            return '';
        }
        
        if ($tags) {
            foreach ($tags as $key => $tag) {
                if (!empty($tag['TagID'])) {
                    $tag_ids[] = $tag['TagID'];
                }
            }
        }

        if ($interests) {
            foreach ($interests as $key => $interest) {
                if (!empty($interest['CategoryID'])) {
                    $interest_ids[] = $interest['CategoryID'];
                }
            }
        }

        if ($locations) {
            foreach ($locations as $key => $location) {
                if (!empty($tag['CityID'])) {
                    $city_ids[] = $tag['CityID'];
                }
            }
        }

        $this->db->simple_query('SET SESSION group_concat_max_len=150000');

        $this->db->select('GROUP_CONCAT(DISTINCT(A.ActivityID)) as ActivityIDs', false);
        $this->db->from(ACTIVITY . ' A');
        if (!empty($gender)) {
            $this->db->join(USERS . ' U', 'U.UserID=A.UserID AND U.Gender="' . $gender . '"');
        } else {
            $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        }

        if (!empty($city_ids) && !empty($age_group_id)) {
            $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id . ' AND LA.CityID IN (' . implode(',', $city_ids) . '))');
        } else if (!empty($city_ids)) {
            $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID  AND LA.CityID IN (' . implode(',', $city_ids) . '))');
        } else if (!empty($age_group_id)) {
            $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id);
        }

        if (!empty($tag_ids)) {
            $this->db->where("A.ActivityID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='ACTIVITY' AND TagID IN (" . implode(',', $tag_ids) . "))", null, false);
        }

        if (!empty($interest_ids)) {
            $this->db->where("A.UserID IN (SELECT ModuleEntityID FROM " . ENTITYCATEGORY . " WHERE ModuleID='3' AND CategoryID IN (" . implode(',', $interest_ids) . "))", null, false);
        }
        
        $this->db->where_not_in("A.ActivityTypeID", array(2,3,4,13,18,19,20,21,22));

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $activity_result = $query->row_array();
            $tags_activity_ids = $activity_result['ActivityIDs'];
        }
        
        return $tags_activity_ids;
    }
    
    /*
     * [Get activity ids for popular posts]
     * @return activityIds
     */
    public function calc_default_activity_popularposts($popular_post) 
    {
        $popular_activity_ids = '';
        $city_ids = array();
        $popular_post = json_decode($popular_post, true);
        $locations = isset($popular_post['Location']) ? $popular_post['Location'] : '';
        $gender = isset($popular_post['Gender']) ? $popular_post['Gender'] : 0;
        $age_group_id = isset($popular_post['AgeGroupID']) ? $popular_post['AgeGroupID'] : 0;
        
        if(!($gender || $age_group_id || $locations )) {
            return '';
        }
        
        if ($locations) {
            foreach ($locations as $key => $location) {
                if (!empty($tag['CityID'])) {
                    $city_ids[] = $tag['CityID'];
                }
            }
        }

        $this->db->simple_query('SET SESSION group_concat_max_len=150000');

        $this->db->select('GROUP_CONCAT(DISTINCT(UAL.ModuleEntityID)) as ActivityIDs', false);
        $this->db->from(USERSACTIVITYLOG . ' UAL');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(ACTIVITY . ' A', 'A.ActivityID=UAL.ModuleEntityID AND A.StatusID=2 AND A.ActivityTypeID NOT IN (2,3,4,13,18,19,20,21,22) AND A.CreatedDate BETWEEN "' . get_current_date('%Y-%m-%d 00:00:00', 30) . '" AND "' . get_current_date('%Y-%m-%d 23:59:59') . '"');

        $this->db->_protect_identifiers = TRUE;
        if (!empty($gender)) {
            $this->db->join(USERS . ' U', 'U.UserID=A.UserID AND U.Gender="' . $gender . '"');
        } else {
            $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
        }

        if (!empty($city_ids) && !empty($age_group_id)) {
            $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id . ' AND LA.CityID IN (' . implode(',', $city_ids) . '))');
        } else if (!empty($city_ids)) {
            $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID  AND LA.CityID IN (' . implode(',', $city_ids) . '))');
        } else if (!empty($age_group_id)) {
            $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id);
        }
        

        $this->db->where('UAL.ModuleID', '19');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $activity_result = $query->row_array();
            $popular_activity_ids = $activity_result['ActivityIDs'];
        }
        
        return $popular_activity_ids;
    }
    
    /*
     * [Get profile ids for profile tags]
     * @return userIds
     */
    public function calc_default_activity_profilewithtags($profile_with_tags) {
        
        $tag_ids = array();
        $city_ids = array();
        $profile_ids = "";
        $tags_profile_ids = "";
        $popular_profile_ids = "";
        $interest_ids = array();
        
        $profile_with_tags = json_decode($profile_with_tags, true);
        $tags = isset($profile_with_tags['Tag']) ? $profile_with_tags['Tag'] : '';
        $gender = isset($profile_with_tags['Gender']) ? $profile_with_tags['Gender'] : 0;
        $age_group_id = isset($profile_with_tags['AgeGroupID']) ? $profile_with_tags['AgeGroupID'] : 0;
        $locations = isset($profile_with_tags['Location']) ? $profile_with_tags['Location'] : '';
        $interests = isset($popular_profile['Interest']) ? $popular_profile['Interest'] : '';
        
        if(!($tags || $gender || $age_group_id || $locations || $interests)) {
            return '';
        }
        
        if ($tags) {
            foreach ($tags as $key => $tag) {
                if (!empty($tag['TagID'])) {
                    $tag_ids[] = $tag['TagID'];
                }
            }
        }

        if ($locations) {
            foreach ($locations as $key => $location) {
                if (!empty($location['CityID'])) {
                    $city_ids[] = $location['CityID'];
                }
            }
        }
        
        if ($interests) {
            foreach ($interests as $key => $interest) {
                if (!empty($interest['CategoryID'])) {
                    $interest_ids[] = $interest['CategoryID'];
                }
            }
        }

        $this->db->select('GROUP_CONCAT(DISTINCT(U.UserID)) as UserIDs', false);
        $this->db->from(USERS . ' U');
        if (!empty($gender)) {
            $this->db->where('U.Gender', $gender);
        }

        if (!empty($city_ids) && !empty($age_group_id)) {
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID AND UD.CityID IN (' . implode(',', $city_ids) . ')');
            $this->db->where(" (SELECT AG.AgeGroupID FROM " . AGEGROUPS . " AG WHERE DATE_FORMAT(FROM_DAYS(DATEDIFF('" . get_current_date('%Y-%m-%d') . "',UD.DOB)), '%Y')+0 BETWEEN AG.ValueRangeFrom AND AG.ValueRangeTo LIMIT 1)='" . $age_group_id . "'");
        } else if (!empty($city_ids)) {
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID AND UD.CityID IN (' . implode(',', $city_ids) . ')');
        } else if (!empty($age_group_id)) {
            $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
            $this->db->where(" (SELECT AG.AgeGroupID FROM " . AGEGROUPS . " AG WHERE DATE_FORMAT(FROM_DAYS(DATEDIFF('" . get_current_date('%Y-%m-%d') . "',UD.DOB)), '%Y')+0 BETWEEN AG.ValueRangeFrom AND ValueRangeTo LIMIT 1)='" . $age_group_id . "'");
        }
        
        if (!empty($interest_ids)) {
            $this->db->where("EV.UserID IN (SELECT ModuleEntityID FROM " . ENTITYCATEGORY . " WHERE ModuleID='3' AND CategoryID IN (" . implode(',', $interest_ids) . "))", null, false);
        }
        
        if (!empty($tag_ids)) {
            $this->db->where("U.UserID IN (SELECT EntityID FROM " . ENTITYTAGS . " WHERE EntityType='USER' AND TagID IN (" . implode(',', $tag_ids) . "))", null, false);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $profile_result = $query->row_array();
            $tags_profile_ids = $profile_result['UserIDs'];
        }
        
        return $tags_profile_ids;
    }
    
    /*
     * [Get profile ids for popular profiles]
     * @return userIds
     */
    public function calc_default_activity_popularprofiles($popular_profile) {
        $popular_profile_ids = '';
        
        $interest_ids = array();
        $city_ids = array();
        $popular_profile = json_decode($popular_profile, true);
        $locations = isset($popular_profile['Location']) ? $popular_profile['Location'] : '';
        $gender = isset($popular_profile['Gender']) ? $popular_profile['Gender'] : 0;
        $age_group_id = isset($popular_profile['AgeGroupID']) ? $popular_profile['AgeGroupID'] : 0;
        $interests = isset($popular_profile['Interest']) ? $popular_profile['Interest'] : '';
        
        if(!($gender || $age_group_id || $locations || $interests)) {
            return '';
        }
        
        if ($locations) {
            foreach ($locations as $key => $location) {
                if (!empty($location['CityID'])) {
                    $city_ids[] = $location['CityID'];
                }
            }
        }

        if ($interests && 0) {
            foreach ($interests as $key => $interest) {
                if (!empty($interest['CategoryID'])) {
                    $interest_ids[] = $interest['CategoryID'];
                }
            }
        }

        $this->db->select('GROUP_CONCAT(DISTINCT(EV.UserID)) as UserIDs', false);
        $this->db->from(ENTITYVIEW . ' EV');
        if (!empty($gender)) {
            $this->db->join(USERS . ' U', 'U.UserID=EV.UserID AND U.StatusID NOT IN (3,4) AND U.Gender="' . $gender . '"');
        } else {
            $this->db->join(USERS . ' U', 'U.UserID=EV.UserID AND U.StatusID NOT IN (3,4)');
        }
        if (!empty($city_ids) && !empty($age_group_id)) {
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=EV.UserID AND UD.CityID IN (' . implode(',', $city_ids) . ')');
            $this->db->where("(SELECT AG.AgeGroupID FROM " . AGEGROUPS . " AG WHERE DATE_FORMAT(FROM_DAYS(DATEDIFF('" . get_current_date('%Y-%m-%d') . "',UD.DOB)), '%Y')+0 BETWEEN AG.ValueRangeFrom AND AG.ValueRangeTo LIMIT 1)='" . $age_group_id . "'");
        } else if (!empty($city_ids)) {
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=EV.UserID AND UD.CityID IN (' . implode(',', $city_ids) . ')');
        } else if (!empty($age_group_id)) {
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=EV.UserID', 'left');
            $this->db->where("(SELECT AG.AgeGroupID FROM " . AGEGROUPS . " AG WHERE DATE_FORMAT(FROM_DAYS(DATEDIFF('" . get_current_date('%Y-%m-%d') . "',UD.DOB)), '%Y')+0 BETWEEN AG.ValueRangeFrom AND ValueRangeTo LIMIT 1)='" . $age_group_id . "'");
        }

        if (!empty($interest_ids)) {
            $this->db->where("EV.UserID IN (SELECT ModuleEntityID FROM " . ENTITYCATEGORY . " WHERE ModuleID='3' AND CategoryID IN (" . implode(',', $interest_ids) . "))", null, false);
        }
        $this->db->where('EV.ModifiedDate >=', get_current_date('%Y-%m-%d 00:00:00', 30));
        $this->db->where('EV.ModifiedDate <=', get_current_date('%Y-%m-%d 23:59:59'));
        $query = $this->db->get();
        if ($query->num_rows()) {
            $popular_profile_result = $query->row_array();
            $popular_profile_ids = $popular_profile_result['UserIDs'];
            
            if(!$popular_profile_ids) {
                return $popular_profile_ids;
            }
            
            $popular_follow_friends_sql = "
                Select Count(*) AS Total, ForUserID From 
                (
                    Select CMFRFL.ForUserID, CMFRFL.UserID From 
                    (
                        (
                            Select FL.TypeEntityID AS UserID, FL.UserID AS ForUserID From ".FOLLOW." FL

                            Where FL.Type = 'user' AND FL.UserID IN($popular_profile_ids)

                        )
                        Union All
                        (
                            SELECT FR.FriendID AS UserID, FR.UserID AS ForUserID FROM ".FRIENDS." FR 

                            Where  FR.UserID IN($popular_profile_ids)
                        ) 
                    ) AS CMFRFL
                    Group By CMFRFL.ForUserID, CMFRFL.UserID
                ) AS CMFRFLCount Group By ForUserID Order By Total DESC
                Limit 10
            ";
            
            $query = $this->db->query($popular_follow_friends_sql);
            
            if ($query->num_rows()) {
                $profile_result = $query->result_array();
                $popular_profile_ids = array();
                foreach($profile_result as $profile_row) {
                    $popular_profile_ids[] = $profile_row['ForUserID'];
                }
                $popular_profile_ids = implode(',', $popular_profile_ids);
            }
        }
        
        return $popular_profile_ids;
    }
    
    /*
     * [Get activity ids for public posts]
     * @return activityIds
     */
    public function calc_default_activity_publicposts($public_post) {        
        $city_ids = array();
        $public_activity_ids = "";
        $tags_activity_ids = "";
        $popular_activity_ids = "";
        $activity_ids = "";
        
        $public_post = json_decode($public_post, true);
        $gender = isset($public_post['Gender']) ? $public_post['Gender'] : 0;
        $age_group_id = isset($public_post['AgeGroupID']) ? $public_post['AgeGroupID'] : 0;
        $locations = isset($public_post['Location']) ? $public_post['Location'] : '';
        
        if(!($gender || $age_group_id || $locations )) {
            return '';
        }
        
        if ($locations) {
            foreach ($locations as $key => $location) {
                if (!empty($tag['CityID'])) {
                    $city_ids[] = $tag['CityID'];
                }
            }
        }

        if (!empty($gender) || !empty($age_group_id) || !empty($city_ids)) {
            $this->db->select('GROUP_CONCAT(DISTINCT(A.ActivityID)) as ActivityIDs', false);
            $this->db->from(ACTIVITY . ' A');
            if (!empty($gender)) {
                $this->db->join(USERS . ' U', 'U.UserID=A.UserID AND U.Gender="' . $gender . '"');
            } else {
                $this->db->join(USERS . ' U', 'U.UserID=A.UserID');
            }

            if (!empty($city_ids) && !empty($age_group_id)) {
                $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id . ' AND LA.CityID IN (' . implode(',', $city_ids) . '))');
            } else if (!empty($city_ids)) {
                $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID  AND LA.CityID IN (' . implode(',', $city_ids) . '))');
            } else if (!empty($age_group_id)) {
                $this->db->join(ANALYTICLOGINS . ' LA', 'LA.AnalyticLoginID=A.AnalyticLoginID AND LA.AgeGroupID=' . $age_group_id);
            }

            $this->db->where_not_in("A.ActivityTypeID", array(2,3,4,13,18,19,20,21,22));

            $query = $this->db->get();
            if ($query->num_rows()) {
                $activity_result = $query->row_array();
                $activity_ids = $activity_result['ActivityIDs'];
            }
        }
        
        return $activity_ids;
    }
    
    /*
     * [Get tranding tag ids for activity]
     * @return tranding_tag_ids
     */
    public function get_tranding_tags($tranding_tags, $type = 'ACTIVITY') {
        $tranding_tag_ids = '';
      
        $city_ids = array();
        $tranding_tags = json_decode($tranding_tags, true);
        $gender = isset($tranding_tags['Gender']) ? $tranding_tags['Gender'] : 0;
        $age_group_id = isset($tranding_tags['AgeGroupID']) ? $tranding_tags['AgeGroupID'] : 0;
        $locations = isset($tranding_tags['Location']) ? $tranding_tags['Location'] : '';
        $is_tranding = (int)isset($tranding_tags['IsTranding']) ? $tranding_tags['IsTranding'] : 0;
        
        if(!($gender || $age_group_id || $locations || $is_tranding)) {
            return '';
        }
        
        if(!$is_tranding) {
            return $tranding_tag_ids;
        }
        
        if ($locations) {
            foreach ($locations as $key => $location) {
                if (!empty($location['CityID'])) {
                    $city_ids[] = $location['CityID'];
                }
            }
        }
        
        
        $this->db->select('ET.TagID as TagID, Count(ET.TagID) AS count_tag', false);
        $this->db->from(ENTITYTAGS . ' ET');
        
        $this->db->join(ACTIVITY . ' E', "ET.EntityID = E.ActivityID AND ET.EntityType = 'ACTIVITY'");
        
        
        
        if (!empty($city_ids) && !empty($age_group_id)) {
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=E.UserID AND UD.CityID IN (' . implode(',', $city_ids) . ')');
            $this->db->where(" (SELECT AG.AgeGroupID FROM " . AGEGROUPS . " AG WHERE DATE_FORMAT(FROM_DAYS(DATEDIFF('" . get_current_date('%Y-%m-%d') . "',UD.DOB)), '%Y')+0 BETWEEN AG.ValueRangeFrom AND AG.ValueRangeTo LIMIT 1)='" . $age_group_id . "'");
        } else if (!empty($city_ids)) {
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=E.UserID AND UD.CityID IN (' . implode(',', $city_ids) . ')');
        } else if (!empty($age_group_id)) {
            $this->db->join(USERDETAILS . ' UD', 'E.UserID=UD.UserID', 'left');
            $this->db->where(" (SELECT AG.AgeGroupID FROM " . AGEGROUPS . " AG WHERE DATE_FORMAT(FROM_DAYS(DATEDIFF('" . get_current_date('%Y-%m-%d') . "',UD.DOB)), '%Y')+0 BETWEEN AG.ValueRangeFrom AND ValueRangeTo LIMIT 1)='" . $age_group_id . "'");
        }
        
        
        
        $current_date = get_current_date('%Y-%m-%d 00:00:00');
        $previous_date = get_current_date('%Y-%m-%d 00:00:00', 180); // Before 180 days
        
        $this->db->where(" E.CreatedDate >= '$previous_date' AND E.CreatedDate <= '$current_date' ");
        $this->db->group_by(' ET.TagID ');
        $this->db->order_by('count_tag', 'DESC');
        $this->db->limit(15, 0);
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $result_rows = $query->result_array();
            $tranding_tag_ids = [];
            foreach($result_rows as $result_row) {
                $tranding_tag_ids[] = $result_row['TagID'];
            }
            $tranding_tag_ids = implode(',', $tranding_tag_ids);
        }
        
        return $tranding_tag_ids;
    }
    
    public function get_users_for_usertags($user_tags) {
        
        $user_ids = '';
        if(!$user_tags) {
            return $user_ids;
        }
        $this->db->select('U.UserID', false);
        $this->db->from(ENTITYTAGS . ' ET');
        //$this->db->join(TAGS . ' T', "T.TagID = ET.TagID AND ET.EntityType = 'USER'");
        $this->db->join(USERS . ' U', "ET.EntityID = U.UserID AND ET.EntityType = 'USER'");
        $this->db->where(" ET.TagID IN ( $user_tags ) ");
        $this->db->group_by(' U.UserID ');
        
        
        $query = $this->db->get();
        $result_rows = $query->result_array();
        
        if(!$result_rows) {
            return $user_ids;
        }
        
        $user_ids = [];
        foreach($result_rows as $result_row) {
            $user_ids[] = $result_row['UserID'];
        }
        $user_ids = implode(',', $user_ids);
        
        return $user_ids;
    }

}
