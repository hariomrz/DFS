<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sticky_model extends Common_Model
{

    protected $blocked_users = array();
    protected $tagged_data = array();
    protected $user_activity_archive = array();

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('users/user_model', 'group/group_model'));
        //$this->load->helper('activity');
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    /**
     * [get_all_sticky Get all sticky posts in sticky widget]
     * @param  [int]    $user_id [user_id]
     * @param  [int]    $role_id [role_id]
     * @param  [int]    $module_entity_id [module_entity_id ]
     * @param  [int]    $module_id [module_id]
     * @param  [int]    $include_group_sticky [include_group_sticky]
     * @param  [int]    $page_no [page_no ID]
     * @param  [int]    $page_size [page_size ID]
     * @param  [int]    $activity_id [activity_id  will be used to get details of a single sticky post for a user]
     */
    public function get_all_sticky1($user_id, $role_id, $module_entity_id='0', $module_id='0', $include_group_sticky='0', $page_no='1', $page_size='20',$activity_id='')
    {
        $result_set = array();
        
        /*   Privacy Conditions Starts */

        $this->load->model('privacy/privacy_model');
        //Privacy of users        
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $Follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();
        $FilterType = 0;
        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $Follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $Follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $OnlyFriendFollowers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($OnlyFriendFollowers[$user_id]);
            if (!$OnlyFriendFollowers)
            {
                $OnlyFriendFollowers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        $GroupList = $this->group_model->get_user_group_list();
        //get All Public Groups
        $this->db->select("group_concat(`GroupID` separator ',') as GroupIDs");
        $this->db->where('StatusID','2');
        $this->db->where('IsPublic','1'); 
        $public_groups = $this->db->get(GROUPS)->row_array();
        if(isset($public_groups['GroupIDs']))
        {
            $public_groups = $public_groups['GroupIDs'];
            $public_groups = explode(',', $public_groups);
            $GroupList = array_merge($GroupList,$public_groups);
            $GroupList = array_unique($GroupList);
        }

        $GroupList[] = 0;
        $GroupList = implode(',', $GroupList);
        $EventList = $this->event_model->get_user_joined_events();
        //$PageList = $this->page_model->get_user_pages_list();
        $PageList = $this->page_model->get_feed_pages_condition();

        if (!in_array($user_id, $Follow))
        {
            $Follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $ActivityTypeAllow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 23, 24, 25);
        $ModulesAllowed = array(3, 30);
        $show_suggestions = FALSE;
        $show_media = TRUE;

        if ($privacy_options)
        {
            foreach ($privacy_options as $key => $val)
            {
                if ($key == 'g' && $val == '0')
                {
                    $ModulesAllowed[] = 1;
                }
                if ($key == 'e' && $val == '0')
                {
                    $ModulesAllowed[] = 14;
                }
                if ($key == 'p' && $val == '0')
                {
                    $ModulesAllowed[] = 18;
                }
                if ($key == 'm')
                {
                    if ($val == '1')
                    {
                        $show_media = FALSE;
                        unset($ActivityTypeAllow[array_search('5', $ActivityTypeAllow)]);
                        unset($ActivityTypeAllow[array_search('6', $ActivityTypeAllow)]);
                    }
                }
                if ($key == 'r' && $val == '0')
                {
                    $ActivityTypeAllow[] = 16;
                    $ActivityTypeAllow[] = 17;
                }
                if ($key == 's' && $val == '0')
                {
                    if ($FilterType == '0' && empty($mentions))
                    {
                        $show_suggestions = true;
                    }
                }
            }
        }

        /*if ($FilterType == 3)
        {
            // 1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18 23, 24, 25 
            $ModulesAllowed = array(1, 3, 14, 18, 23, 30);
            $ActivityTypeAllow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 18, 23, 24, 25, 30);
        }*/

        /* --Filter by activity type id-- */
        /*$activity_ids = array();
        if (!empty($ActivityTypeFilter))
        {
            $ActivityTypeAllow = $ActivityTypeFilter;
            $show_suggestions = false;

            //7 = My Polls, 8= Expired
            if ($FilterType == 7 || $FilterType == 8)
            {
                $is_expired = FALSE;
                if ($FilterType == 8)
                {
                    $is_expired = TRUE;
                }

                $this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_poll_activities($EntityID, $EntityModuleID, $is_expired);
                if (empty($activity_ids))
                {
                    return array();
                }
            }
            //My Voted Polls
            if ($FilterType == 9)
            {
                $this->load->model('polls_model');
                $activity_ids = $this->polls_model->my_voted_poll_activities($EntityID, $EntityModuleID);
                if (empty($activity_ids))
                {
                    return array();
                }
            }

            //print_r($activity_ids);die;
        }*/

        /*if ($FilterType == 'Favourite' && !in_array(1, $ModulesAllowed))
        {

            $ModulesAllowed[] = 1;
        }*/

        //Activity Type 1 for followers, friends and current user
        //Activity Type 2 for followers and friends only
        //Activity Type 3 for follower and friend of UserID
        //Activity Type 8, 9, 10 for Mutual Friends Only
        //Activity Type 4, 7 for Group Members Only
        $condition = "";
        $ConditionPartOne = "";
        $ConditionPartTwo = "A.ModuleEntityID=" . $user_id;
        $ConditionPartThree = "";
        $ConditionPartFour = "";
        $PrivacyCond = ' ( ';
        $PrivacyCond1 = '';
        $PrivacyCond2 = '';
        if ($friend_followers_list != '' )//&& empty($ActivityIDs))
        {
            $condition = "(
                IF(A.ActivityTypeID=25 OR A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6 OR (A.ActivityTypeID=23 AND A.ModuleID=3) OR (A.ActivityTypeID=24 AND A.ModuleID=3), (
                    A.UserID IN(" . $friend_followers_list . ") OR A.ModuleEntityID IN(" . $friend_followers_list . ") OR " . $ConditionPartTwo . "
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(" . implode(',', $OnlyFriendFollowers) . ") OR A.ModuleEntityID IN(" . implode(',', $OnlyFriendFollowers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(" . implode(',', $OnlyFriendFollowers) . ") AND A.UserID!='" . $user_id . "'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (
                    (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $ConditionPartTwo . "
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (
                    A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'
                ), '' )";
            /* if(!empty($ActivityTypeFilter))
              {
              if($ActivityTypeFilter[0]==25)
              {
              $Condition.=" OR IF(A.ActivityTypeID=25, (A.Privacy IN(1,2,3)), '' ) ";
              }
              else
              {
              $Condition.=" OR IF(A.ActivityTypeID=25, (
              A.UserID IN(".implode(',', $OnlyFriendFollowers).") OR A.UserID='".$UserID."'
              ), '' ) ";
              }
              } */
            if ($friends)
            {
                $PrivacyCond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($Follow)
            {
                $PrivacyCond2 = "IF(A.Privacy='3',
                    A.UserID IN (" . implode(',', $Follow) . "), true
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
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $GroupList . "),false))
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

        $PrivacyCond3 = "IF(A.Privacy='4',
            A.UserID='" . $user_id . "', true
        )";
        if (!empty($PrivacyCond1))
        {
            $PrivacyCond .= $PrivacyCond1 . ' OR ';
        }
        if (!empty($PrivacyCond2))
        {
            $PrivacyCond .= $PrivacyCond2 . ' OR ';
        }
        $PrivacyCond .= " OR A.ActivityID=(SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "')";
        $PrivacyCond .= $PrivacyCond3 . ' )';

        // /echo $PrivacyCond;
        if (!empty($GroupList))
        {
            $ConditionPartOne = $ConditionPartOne . "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $GroupList . ")
                    ), '' )";
        }
        if (!empty($PageList))
        {
            /* $ConditionPartThree = $ConditionPartThree . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17, (
              A.ModuleID=18 AND A.ModuleEntityID IN(" . $PageList . ")
              ), '' )"; */


              //marked comment
            /*$ConditionPartThree = $ConditionPartThree . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
                        A.ModuleID=18 AND (" . $PageList . ")
                    ), '' )";*/
        }
        if (!empty($EventList))
        {
            $ConditionPartFour = $ConditionPartFour . "IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
                        A.ModuleID=14 AND A.ModuleEntityID IN(" . $EventList . ")
                    ), '' )";
        }
        if (!empty($condition))
        {
            if (!empty($ConditionPartOne))
            {
                $condition = $condition . " OR " . $ConditionPartOne;
            }
            if (!empty($ConditionPartThree))
            {
                $condition = $condition . " OR " . $ConditionPartThree;
            }
            if (!empty($ConditionPartFour))
            {
                $condition = $condition . " OR " . $ConditionPartFour;
            }
            $condition = $condition . ")";
        } else
        {

            if (!empty($ConditionPartOne))
            {
                $condition = $ConditionPartOne;
            }
            if (!empty($ConditionPartThree))
            {
                if (empty($condition))
                {
                    $condition = $ConditionPartThree;
                } else
                {
                    $condition = $condition . " OR " . $ConditionPartThree;
                }
            }

            if (empty($condition))
            {
                $condition = $ConditionPartTwo;
            } else
            {
                //$Condition = $ConditionPartTwo. " OR ".$ConditionPartOne; 
                $condition = "(" . $condition . ")";
            }
        }
        $condition .= " AND ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        //ELSE A.ActivityID=(SELECT ActivityID FROM ".MENTION." WHERE ModuleID=3 AND ModuleEntityID='".$UserID."')
        $condition .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        $condition .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        $condition .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";

        /*   Privacy Conditions Ends */

        //Query
        $this->db->select('A.ActivityID, A.ActivityGUID, ATY.ActivityType, ATY.Template, A.UserID, A.ParentActivityID, A.ActivityTypeID,ATY.ActivityType, A.Params,A.ModuleID, A.ModuleEntityID, A.PostContent, A.Privacy, A.NoOfComments, A.NoOfLikes, concat(U.FirstName,\' \',U.LastName) as Name, SP.SelfSticky, SP.GroupSticky, SP.EveryoneSticky, A.CreatedDate, P.Url as ProfileURL,U.ProfilePicture');
        $this->db->from(STICKYPOST .' as SP ');
        $this->db->join(ACTIVITY . " AS A", ' A.ActivityID = SP.ActivityID AND A.StatusID=2 ', 'left');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS .' AS U', ' U.UserID=A.UserID ', 'left')
        ->join(PROFILEURL.' as P',"U.UserID=P.EntityID AND P.EntityType='User'",'left');
        $this->db->where('SP.PostAsModuleEntityID', $user_id);
            
        /* Privacy Condtion */    
        // $Condition_final  = " ((CASE WHEN (A.Privacy=2) THEN A.UserID IN (" . $friend_of_friends . ") ";
        // $Condition_final .= " ELSE (CASE WHEN (A.Privacy=3) THEN A.UserID IN (" . implode(',', $friends) . ")";
        // $Condition_final .= " ELSE (CASE WHEN (A.Privacy=4) THEN A.UserID='" . $user_id . "' ELSE 1 END) END) END) OR ";
        // $Condition_final .= " ((SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "' AND ActivityID=A.ActivityID LIMIT 1) is not null))";
        //$this->db->where($Condition_final, NULL, FALSE);

        
        /* Privacy Condtion */    

        //for single sticky details        
        if($activity_id)
        {
            $this->db->where('SP.ActivityID', $activity_id);   
            $sql = $this->db->get();
        }
        else
        {

        /* Privacy Condtion */   
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
        /* Privacy Condtion */   

            if(isset($module_id,$module_entity_id) && $module_id != '3')
            {
                $this->db->where('A.ModuleID', $module_id);
                $this->db->where('A.ModuleEntityID', $module_entity_id);
            }
            
            //select group posts only when include group post is on            
            if(!$include_group_sticky && $module_id != 1)
            {
                //$this->db->where('SP.GroupSticky', '0');
                $this->db->where('A.ModuleID!=', '1');
            }
            $this->db->order_by('SP.EveryoneSticky', 'DESC');    
            $this->db->order_by('SP.GroupSticky', 'DESC');    
            $this->db->order_by('A.CreatedDate', 'DESC');    

            $tempdb = clone $this->db;
            $temp_q = $tempdb->get();
            $total_records = $temp_q->num_rows();

            if (isset($page_size, $page_no))
            {
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }

            $sql = $this->db->get();  
            $result_set=array();
            $result_set['TotalRecords'] = isset($total_records) ? $total_records : 1;
        }
        $count = 0;
        if ($sql->num_rows())
        {   
            foreach ($sql->result_array() as $result)
            {
                //check user's permission on the sticky
                $can_make_sticky = $this->can_make_sticky($user_id, $role_id, $result['ModuleID'],$result['ModuleEntityID']);
                $result['CanMakeSticky'] = $can_make_sticky;
                $result['EntityName'] = $result['Name'];//for now users only
                $result['Album'] = $this->activity_model->get_albums($result['ActivityID'], $result['UserID']);
                $result['Files'] = $this->activity_model->get_activity_files($result['ActivityID']);

                if ($result['ActivityTypeID'] == '14' || $result['ActivityTypeID'] == '15')
                {
                    $result['Album'] = $this->activity_model->get_albums($result['ParentActivityID'], $result['UserID'], '', 'Media');
                }

                if ($result['ActivityTypeID'] == 5 || $result['ActivityTypeID'] == 6 || $result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 9)
                {
                    $album_flag = TRUE;
                    if ($result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($result['ParentActivityID'], '', 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        if (!empty($parent_activity_detail))
                        {
                            if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6)))
                            {
                                if (!empty($parent_activity_detail['Params']))
                                {
                                    $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                    if (!empty($album_detail['AlbumGUID']))
                                    {
                                        @$result['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                        $album_flag = TRUE;
                                    }
                                }
                            }
                        }
                    }
                    if ($album_flag)
                    {
                        $count = 4;
                        if ($result['ActivityTypeID'] == 6)
                        {
                            $count = $result['Params']->count;
                        }
                        if($this->is_json($result['Params']))
                        {
                            $result['Params'] = json_decode($result['Params']);
                            $album_details = $this->album_model->get_album_by_guid($result['Params']->AlbumGUID);
                            $result['AlbumEntityName'] = $result['EntityName'];
                            $result['EntityName'] = $album_details['AlbumName'];
                            $result['Album'] = $this->activity_model->get_albums($result['ActivityID'], $result['UserID'], $result['Params']->AlbumGUID, 'Activity', $count);
                        }                        
                    }
                }

                if ($result['ActivityTypeID'] == 23 || $result['ActivityTypeID'] == 24)
                {
                    $params = json_decode($result['Params']);
                    if ($params->MediaGUID)
                    {
                        $media_id = get_detail_by_guid($params->MediaGUID, 21);
                        if ($media_id)
                        {
                            $result['Album'] = $this->activity_model->get_albums($media_id, $result['UserID'], '', 'Media', 1);
                        }
                    }
                }

                //For shared Posts
                if ($result['ActivityTypeID'] == 9 || $result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 14 || $result['ActivityTypeID'] == 15)
                {
                    /*$original_activity = $this->activity_model->get_activity_details($result['ParentActivityID'], $result['ActivityTypeID']);
                    $result['ActivityOwner'] = $this->user_model->getUserName($original_activity['UserID'], $original_activity['ModuleID'], $original_activity['ModuleEntityID']);
                    $result['ActivityOwnerLink'] = $result['ActivityOwner']['ProfileURL'];
                    $result['ActivityOwner'] = $result['ActivityOwner']['FirstName'] . ' ' . $result['ActivityOwner']['LastName'];
                    $result['Album'] = $original_activity['Album'];
                    $result['Files'] = $original_activity['Files'];
                    $result['SharePostContent'] = $result['PostContent'];
                    $result['PostContent'] = $original_activity['PostContent'];
                    $result['ActivityType'] = $original_activity['ActivityType'];*/
                }

                //parse tag in content
                $result['PostContent'] = $this->activity_model->parse_tag($result['PostContent']);

                //check privacy of post
                /*$is_relation = $this->activity_model->isRelation($result['UserID'],$user_id,TRUE,$result['ActivityGUID']);
                if(!in_array($result['Privacy'], $is_relation))
                {
                    $count++;
                    continue;
                }*/

                $result['SelfSticky'] = isset($result['SelfSticky']) ? (int)$result['SelfSticky'] : 0;
                $result['GroupSticky'] = isset($result['GroupSticky']) ? (int)$result['GroupSticky'] : 0;
                $result['EveryoneSticky'] = isset($result['EveryoneSticky']) ? (int)$result['EveryoneSticky'] : 0;
                $result['CanMakeSticky'] = $this->can_make_sticky($user_id,$role_id,$result['ModuleID'],$result['ModuleEntityID']);
                //if anyone of these sticky options comes true we'll set selfSticky true;
                if(($result['CanMakeSticky'] == 3) && ($result['SelfSticky'] || $result['GroupSticky'] || $result['EveryoneSticky']))
                {
                    $result['SelfSticky'] = 1;
                }
                //unset all unnecessary fields
                unset($result['ActivityID']);
                unset($result['UserID']);
                unset($result['ParentActivityID']);
                unset($result['ActivityTypeID']);
                unset($result['Params']);
                unset($result['ModuleEntityID']);
                $result_set[] = $result;
            }    
            /*if(isset($result_set['TotalRecords'])) 
                $result_set['TotalRecords'] -= $count;*/
        }                
        return $result_set;        
    }

    /**
     * [create_sticky Create sticky post]
     * @param  [int]    $user_id [user ID]
     * @param  [int]    $activity_id [Activity ID]
     * @param  [int]    $module_id [module ID]
     * @param  [int]    $module_entity_id [module_entity_id]
     * @param  [int]    $sticky_type [sticky_type 1->self 2->group 3->everyone]
     * @param  [int]    $group_members [group_members ID]
     */
    public function create_sticky($user_id, $activity_id, $module_id='', $module_entity_id='', $sticky_type='1', $group_members=array(),$role_id='',$group_admins=array())
    {
        $sticky_insert_data =  array();
        switch ($sticky_type) {
            case '1':
                // Self Sticky
                //check if given activity is already a sticky
                if($sticky_post_id=$this->check_sticky_exist($user_id,$activity_id))
                {                
                    $sticky_data = array('CreatedBy' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'SelfSticky' => '1','SelfStickyBeforeUpdate' => '1');        
                    $this->db->where('StickyPostID',$sticky_post_id);
                    $this->db->update(STICKYPOST, $sticky_data);  
                    //update value of sticky in cache
                    $this->cache_user_sticky(array(array('ActivityID' => $activity_id, 'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user_id,'SelfSticky'=>'1')),'1');              
                } 
                else
                {
                    $sticky_data = array('ActivityID' => $activity_id, 'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user_id, 'CreatedBy' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'SelfSticky' => '1','SelfStickyBeforeUpdate' => '1');        
                    $this->db->insert(STICKYPOST, $sticky_data);            

                    //insert in cache                    
                    $this->cache_user_sticky(array($sticky_data),'1');
                }
                break;
            case '2':
                //insert sticky for  all group members
                if(!empty($group_members))
                {   
                    //remove admins
                    $group_members = array_diff($group_members,$group_admins);
                    foreach ($group_members as $member_id) 
                    {
                        if($sticky_post_id=$this->check_sticky_exist($member_id, $activity_id))
                        {                
                            $sticky_update_data = array('GroupSticky' => '1');//,'SelfSticky' => '1'        
                            $this->db->where('StickyPostID',$sticky_post_id);
                            $this->db->update(STICKYPOST, $sticky_update_data);  
                            //update this sticky
                            $this->cache_user_sticky(array(array('ActivityID'=>$activity_id,'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$member_id,'GroupSticky'=>'1')),'1');              
                        }
                        else 
                        {
                            $row = array('ActivityID' => $activity_id, 'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$member_id, 'CreatedBy' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'GroupSticky' => '1','SelfSticky' => '0','SelfStickyBeforeUpdate' => '0');        
                            $sticky_insert_data[] = $row;                            
                        }
                    }
                }  
                if(!empty($group_admins))
                {
                    foreach ($group_admins as $member_id) 
                    {
                        if($sticky_post_id=$this->check_sticky_exist($member_id, $activity_id,'1',TRUE))
                        {                
                            //in case if we need this for logged in admin we have to add this condition ($member_id == $user_id)
                            $self_sticky_before_update = (isset($sticky_post_id['SelfSticky']) && ($sticky_post_id['SelfSticky'] == '1')) ? '1' : '0';
                            $sticky_update_data = array('GroupSticky' => '1','SelfSticky' => '1', 'SelfStickyBeforeUpdate' => $self_sticky_before_update);
                            $this->db->where('StickyPostID',$sticky_post_id['StickyPostID']);
                            $this->db->update(STICKYPOST, $sticky_update_data);
                            //update this sticky
                            $this->cache_user_sticky(array(array('ActivityID'=>$activity_id,'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$member_id,'GroupSticky' => '1','SelfSticky' => '1')),'1');                 
                        }
                        else 
                        {
                            $row = array('ActivityID' => $activity_id, 'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$member_id, 'CreatedBy' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'GroupSticky' => '1','SelfSticky' => '1','SelfStickyBeforeUpdate' => '0');        
                            $sticky_insert_data[] = $row;                            
                        }
                    }
                }
                
                break;
            case '3':
                //Create sticky for everyone                
                //:group_members is all eveyone user with sticky permissions    
                if(!empty($group_members))
                {                    
                    foreach ($group_members as $user) 
                    {                        
                        //check if sticky already set for this user
                        if($sticky_post_id=$this->check_sticky_exist($user, $activity_id, '1',TRUE))
                        {                
                            $self_sticky_before_update = (isset($sticky_post_id['SelfSticky']) && ($sticky_post_id['SelfSticky'] == '1')) ? '1' : '0';
                            if($user_id==$user)   
                            {
                                $sticky_update_data = array('EveryoneSticky' => '1', 'SelfStickyBeforeUpdate' => $self_sticky_before_update, 'SelfSticky' => '1');        //,'SelfSticky' => '1'                                
                                $self_sticky_value_in_cache = '1';
                            }                             
                            else
                            {
                                $sticky_update_data = array('EveryoneSticky' => '1','SelfStickyBeforeUpdate' => $self_sticky_before_update);        //,'SelfSticky' => '1'
                                $self_sticky_value_in_cache = '0';
                            }
                            $this->db->where('StickyPostID',$sticky_post_id['StickyPostID']);
                            $this->db->update(STICKYPOST, $sticky_update_data);                

                            //update this sticky in cache
                            $this->cache_user_sticky(array(array('ActivityID'=>$activity_id,'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user,'SelfSticky'=>$self_sticky_value_in_cache,'EveryoneSticky'=>'1')),'1'); 
                        }
                        else 
                        {            
                            if($user==$user_id)                            
                                $self_sticky_value = '1';                            
                            else
                                $self_sticky_value = '0';

                            $row = array('ActivityID' => $activity_id, 'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user, 'CreatedBy' => $user_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'EveryoneSticky' => '1','SelfSticky' => $self_sticky_value,'SelfStickyBeforeUpdate' => '0');        
                            $sticky_insert_data[] = $row;                            
                        }
                    }          
                }
                break;
            default:
                # code...
                break;
        }   
        if(!empty($sticky_insert_data))
        {
             $this->db->insert_batch(STICKYPOST, $sticky_insert_data);   
        }     

        //insert all sticky cache
        if(isset($sticky_insert_data) && !empty($sticky_insert_data))
            $this->cache_user_sticky($sticky_insert_data,'1');

        //get response of single activity
        $return_data = $this->get_all_sticky($user_id,$role_id,$module_entity_id,$module_id,'','','',$activity_id);  
        return (isset($return_data[0]) && !empty($return_data[0])) ? $return_data[0] : array();
    }

    
    /**
     * [cache_user_sticky call to background job for creating cache of sticky activities for each user]
     * @param  [Array]    $sticky_data [This is field is a 2D array]
     * @param  [int]    $add_sticky_status [this implies whether to add or update activity in cache (0 for remove sticky | 1 for add sticky)]
     */
    public function cache_user_sticky($sticky_data,$add_sticky_status='')
    {
        //call to a background Job
        initiate_worker_job('cache_users_sticky', array('sticky_data'=>$sticky_data,'add_sticky_status'=>$add_sticky_status));  
        //$this->cache_user_sticky_background($sticky_data,$add_sticky_status);
    }


    /**
     * [cache_users_sticky Create cache of sticky activities for each user]
     * @param  [Array]    $sticky_data [This is field is a 2D array]
     * @param  [int]    $add_sticky_status [this implies whether to add or update activity in cache (0 for remove sticky | 1 for add sticky)]
     */
    public function cache_user_sticky_background($sticky_data,$add_sticky_status='')
    {
        if (CACHE_ENABLE) 
        {               
            if($add_sticky_status == '1')
            {
                foreach($sticky_data as $sticky)         
                {
                    if(isset($sticky['PostAsModuleID']) && $sticky['PostAsModuleID'] == '3')
                    {
                        $sticky_activities = $this->cache->get('user_sticky_activities_'.$sticky['PostAsModuleEntityID']);      
                        $sticky_activities = (isset($sticky_activities) && !empty($sticky_activities)) ? $sticky_activities : array();

                        foreach($sticky_activities as $key => $value)
                        {   //if sticky for this activity is already exist
                            if ( $value['ActivityID'] == $sticky['ActivityID'] )
                            {
                                $sticky_activities[$key]['SelfSticky'] = isset($sticky['SelfSticky']) ? $sticky['SelfSticky'] : '0';
                                $sticky_activities[$key]['GroupSticky'] = isset($sticky['GroupSticky']) ? $sticky['GroupSticky'] : '0';
                                $sticky_activities[$key]['EveryoneSticky'] = isset($sticky['EveryoneSticky']) ? $sticky['EveryoneSticky'] : '0';
                            }                            
                        }
                        //Insert sticky data in cache
                        //In case activity does not exist create new
                        $sticky_activities[] = array('ActivityID'=>$sticky['ActivityID'],
                                                 'SelfSticky'=>isset($sticky['SelfSticky']) ? $sticky['SelfSticky'] : '0',
                                                 'GroupSticky'=>isset($sticky['GroupSticky']) ? $sticky['GroupSticky'] : '0',
                                                 'EveryoneSticky'=>isset($sticky['EveryoneSticky']) ? $sticky['EveryoneSticky'] : '0'
                                                 );
                        $this->cache->save('user_sticky_activities_'.$sticky['PostAsModuleEntityID'], $sticky_activities, CACHE_EXPIRATION);                        
                    }
                }
                    
            }    
            else            
            {   
                foreach($sticky_data as $sticky)         
                {                    
                    if(isset($sticky['PostAsModuleID']) && $sticky['PostAsModuleID'] == '3')
                    {
                        $sticky_activities = $this->cache->get('user_sticky_activities_'.$sticky['PostAsModuleEntityID']);       
                        if(!empty($sticky_activities))
                        {
                            /*if (($key = array_search($sticky['ActivityID'], $sticky_activities)) !== false) 
                            {
                                unset($sticky_activities[$key]);
                            }*/
                            foreach($sticky_activities as $key => $value)
                            {
                                if ( $value['ActivityID'] == $sticky['ActivityID'] )
                                {
                                    if($add_sticky_status == '2')
                                    {
                                        //update values instead of delete
                                        $sticky_activities[$key]['SelfSticky'] = isset($sticky['SelfSticky']) ? $sticky['SelfSticky'] : '0';
                                        $sticky_activities[$key]['GroupSticky'] = isset($sticky['GroupSticky']) ? $sticky['GroupSticky'] : '0';
                                        $sticky_activities[$key]['EveryoneSticky'] = isset($sticky['EveryoneSticky']) ? $sticky['EveryoneSticky'] : '0';
                                    }
                                    else
                                    {   
                                        //remove cache value for this activity
                                        unset($sticky_activities[$key]);
                                    }
                                }
                            }
                            //update sticky
                            $this->cache->save('user_sticky_activities_'.$sticky['PostAsModuleEntityID'], $sticky_activities, CACHE_EXPIRATION);
                        }
                    }
                }
            }
        }
        /*if(empty($sticky_activities))
        {
            $this->cache->save('user_sticky_activities_'.$user_id, $userdata,CACHE_EXPIRATION);
        }*/
    }

    /**
     * [remove_sticky Remove sticky post]
     * @param  [int]    $user_id [posted_by user ID]
     * @param  [int]    $activity_id [Activity ID]
     * @param  [int]    $sticky_type [Type of Sticky]
     * @param  [int]    $group_members [Group Members IDs for stickyType 2]
     */
    public function remove_sticky($user_id, $activity_id, $sticky_type='1', $group_members=array(),$is_admin=FALSE,$role_id='',$module_id='',$module_entity_id='')
    {           
        switch ($sticky_type) {
            case '1': //SelfSticky                
                if($is_admin)
                {
                    $this->db->where('ActivityID',$activity_id);                    
                    $this->db->where('PostAsModuleEntityID',$user_id);  
                    $result = $this->db->get(STICKYPOST)->row_array();

                    if($result['GroupSticky'] == '1')
                    {
                        $this->db->where('ActivityID',$activity_id);
                        $this->db->where('PostAsModuleEntityID',$user_id);
                        $this->db->update(STICKYPOST,array('SelfSticky' => '0','SelfStickyBeforeUpdate'=>'0'));
                        //update cache value
                        $this->cache_user_sticky(array(array('ActivityID'=>$activity_id,'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user_id,'SelfSticky'=>'0')),'2');
                    }
                    else
                    {      
                        //if this is superAdmin                
                        if($role_id == 1 && $result['EveryoneSticky'] == '1')     
                        {
                            $this->db->where('ActivityID',$activity_id);
                            $this->db->where('PostAsModuleEntityID',$user_id);
                            $this->db->update(STICKYPOST,array('SelfSticky' => '0','SelfStickyBeforeUpdate'=>'0'));     
                            //update cache value
                            $this->cache_user_sticky(array(array('ActivityID'=>$activity_id,'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user_id,'SelfSticky'=>'0')),'2');  
                        }
                        else
                        {
                            $this->db->where('ActivityID', $activity_id);
                            $this->db->where('PostAsModuleEntityID', $user_id);
                            $this->db->delete(STICKYPOST);      
                            //update in cache (user below inputs as example)
                            $sticky_data = array('ActivityID' => $activity_id, 'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user_id); 
                            $this->cache_user_sticky(array($sticky_data));
                        }
                    }
                }
                else
                {
                    $this->db->where('ActivityID', $activity_id);
                    $this->db->where('PostAsModuleEntityID', $user_id);
                    $this->db->delete(STICKYPOST);
                    //delete cache value
                    $this->cache_user_sticky(array(array('ActivityID'=>$activity_id,'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user_id)));      
                }     
                //update in cache (user below inputs as example)
                /*$sticky_data = array('ActivityID' => $activity_id, 'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$user_id); 
                    $this->cache_user_sticky(array($sticky_data));    */       
                break;
            case '2': //Group Sticky
                if(!empty($group_members))
                {
                    //get all stickyIDs for given members 
                    $this->db->select("group_concat(`StickyPostID` separator ',') as StickyIDs,group_concat(`PostAsModuleEntityID` separator ',') as PostAsModuleEntityIDs")->where('ActivityID', $activity_id)->where_in('PostAsModuleEntityID',$group_members)->where('SelfStickyBeforeUpdate !=','1')->where('EveryoneSticky !=','1');//earlier we were checking for SelfSticky!=1
                    $result = $this->db->get(STICKYPOST)->row_array();
                    if(!empty($result['StickyIDs']))
                    {
                        $this->db->where_in('StickyPostID', explode(',',$result['StickyIDs']));
                        $this->db->delete(STICKYPOST);
                        //delete cache value
                        $group_members_arr = isset($result['PostAsModuleEntityIDs']) ? explode(',',$result['PostAsModuleEntityIDs']) : array();
                        foreach ($group_members as $member_id) 
                        {
                            $this->cache_user_sticky(array(array('ActivityID'=>$activity_id,'PostAsModuleID'=>3, 'PostAsModuleEntityID'=>$member_id)));    
                        }                            
                    }
                //Update for other members   
                    $this->db->where('ActivityID',$activity_id);
                    $this->db->where_in('PostAsModuleEntityID',$group_members);
                    $this->db->where('GroupSticky','1');
                    $this->db->where('(SelfSticky = \'1\' OR EveryoneSticky = \'1\')',NULL,FALSE);                    
                    $this->db->update(STICKYPOST,array('GroupSticky' => '0'));
                    //update group sticky in cache while remove
                    $this->remove_sticky_cache($activity_id,$group_members,2);                    
                }
                break;            
            case '3': //Everyone                
                //Check how many sticky have to be updated
                $this->db->select("group_concat(`StickyPostID` separator ',') as StickyIDs")
                    //->where('(SelfStickyBeforeUpdate=\'1\' OR GroupSticky=\'1\')', NULL, FALSE)
                    ->where('EveryoneSticky','1')->where('ActivityID',$activity_id);
                
                $result = $this->db->get(STICKYPOST)->row_array();  
                
                
                //echo $this->db->last_query(); 
                
                
                if(isset($result['StickyIDs']) && !empty($result['StickyIDs']))
                {
                    // update all data which are already sticky
                    $sticky_update_data = array('EveryoneSticky' => '0');        
                    $this->db->where_in('StickyPostID',explode(',', $result['StickyIDs']));
                    $this->db->update(STICKYPOST, $sticky_update_data); 
                }
                
                $this->db->where('EveryoneSticky', '1');
                $this->db->where('SelfSticky', '0');
                $this->db->where('GroupSticky', '0');
                $this->db->where('ActivityID', $activity_id);
                $this->db->where_not_in('StickyPostID',explode(',', $result['StickyIDs']));
                $this->db->delete(STICKYPOST);                    

                //update group sticky in cache while remove
                $this->remove_sticky_cache($activity_id,array(),3,$result['StickyIDs']);
                break;
            default:            
                # code...
                break;
        } 
        //get response of single activity
        $return_data = $this->get_all_sticky($user_id,$role_id,$module_entity_id,$module_id,'','','',$activity_id);  
        return (isset($return_data[0]) && !empty($return_data[0])) ? $return_data[0] : array();                
    }

    /**
     * [remove_sticky_cache call to background job to Remove sticky from cache in case of group or everyone Sticky]
     * @param   [int]    $activity_id [Activity ID]
     * @param   [Array]  $members     [Users Array]
     * @param   [int]    $sticky_type [Sticky Type (1->SelfSticky, 2->GroupSticky, 3->EveryoneSticky)]
     * @param   [String]  $sticky_ids     [StickyIds in string]
     */
    public function remove_sticky_cache($activity_id='',$group_members=array(),$sticky_type='',$sticky_ids='')
    {
        //call to a background Job
        initiate_worker_job('remove_sticky_cache', array('activity_id'=>$activity_id,'group_members'=>$group_members,'sticky_type'=>$sticky_type,'sticky_ids'=>$sticky_ids));
        //$this->remove_sticky_cache_background($activity_id,$group_members,$sticky_type,$sticky_ids);
    }

    /**
     * [remove_sticky_cache_background Remove sticky from cache in case of group or everyone Sticky]
     * @param   [int]    $activity_id [Activity ID]
     * @param   [Array]  $members     [Users Array]
     * @param   [int]    $sticky_type [Sticky Type (1->SelfSticky, 2->GroupSticky, 3->EveryoneSticky)]
     * @param   [String]  $sticky_ids     [StickyIds in string]
     */
    public function remove_sticky_cache_background($activity_id='',$group_members=array(),$sticky_type='',$sticky_ids='')
    {
        if($activity_id)
        {
            if(!empty($sticky_type) && $sticky_type == '2')
            {
                //Delete sticky for all group usrs
                //Run query to get all group users
                $this->db->select('PostAsModuleEntityID');
                $this->db->where('ActivityID',$activity_id);
                $this->db->where_in('PostAsModuleEntityID',$group_members);
                $this->db->where('GroupSticky','1');
                $this->db->where('(SelfSticky = \'1\' OR EveryoneSticky = \'1\')',NULL,FALSE);
                $query = $this->db->get(STICKYPOST);
                $result = $query->result_array();                
                // $this->remove_sticky_cache2($result,1);
                if(!empty($result))
                {
                    $data = array();
                    foreach($result as $res)
                    {
                        $data[]['PostAsModuleEntityID'] = $res['PostAsModuleEntityID'];
                        $data[]['GroupSticky'] = '0';
                        $data[]['PostAsModuleID'] = '3';
                        $data[]['ActivityID'] = $activity_id;

                    }
                    $this->remove_sticky_cache2($data,1);
                }
            } 
            elseif($sticky_type == '3')
            {
                //Delete cache for all everyone user in sticky
                //Query to get all users for whom stickies are to be deleted from cache
                $this->db->select('PostAsModuleEntityID');
                $this->db->where('EveryoneSticky', '1');
                $this->db->where('SelfSticky', '0');
                $this->db->where('GroupSticky', '0');
                $this->db->where('ActivityID', $activity_id);
                $this->db->where_not_in('StickyPostID',explode(',', $sticky_ids));
                $query = $this->db->get(STICKYPOST);
                $result = $query->result_array();   
                // $this->remove_sticky_cache2($result);
                if(!empty($result))
                {
                    $data = array();
                    foreach($result2 as $res)
                    {
                        $data[]['PostAsModuleEntityID'] = $res['PostAsModuleEntityID'];
                        $data[]['PostAsModuleID'] = '3';
                        $data[]['ActivityID'] = $activity_id;

                    }
                    $this->remove_sticky_cache2($data);
                }

                //QUery for getting users for whom cache needs to be updated only
                $this->db->select('PostAsModuleEntityID');
                $this->db->where_in('StickyPostID',explode(',', $sticky_ids));             
                $query2 = $this->db->get(STICKYPOST);
                $result2= $query2->result_array();                
                if(!empty($result2))
                {
                    $data = array();
                    foreach($result2 as $res)
                    {
                        $data[]['PostAsModuleEntityID'] = $res['PostAsModuleEntityID'];
                        $data[]['EveryoneSticky'] = '0';
                        $data[]['PostAsModuleID'] = '3';
                        $data[]['ActivityID'] = $activity_id;

                    }
                    $this->remove_sticky_cache2($data,1);
                }
            }
        }
    }

    /**
     * [remove_sticky_cache2 This is a part 2 of above function which will update cache value based on given inputs]
     * @param   [Array]    $user_sticky_data [Array of users to update cache for]
     * @param   [int]    $update_cache_value     [this is a flag to update or unset the given values]
     */
    public function remove_sticky_cache2($user_sticky_data=array(),$update_cache_value=0)
    {
        //Loop through all users
        if(!empty($user_sticky_data))
        {            
            foreach($user_sticky_data as $sticky)
            {
                if(isset($sticky['PostAsModuleEntityID']) && !empty($sticky['PostAsModuleID']))
                {
                    $sticky_activities = $this->cache->get('user_sticky_activities_'.$sticky['PostAsModuleEntityID']);       
                    if(!empty($sticky_activities))
                    {
                        /*if (($key = array_search($sticky['ActivityID'], $sticky_activities)) !== false) 
                        {
                            unset($sticky_activities[$key]);
                        }*/
                        foreach($sticky_activities as $key => $value)
                        {
                            if ( $value['ActivityID'] == $sticky['ActivityID'] )
                            {
                                if($update_cache_value)
                                {
                                    //update values instead of delete
                                    $sticky_activities[$key]['SelfSticky'] = isset($sticky['SelfSticky']) ? $sticky['SelfSticky'] : '0';
                                    $sticky_activities[$key]['GroupSticky'] = isset($sticky['GroupSticky']) ? $sticky['GroupSticky'] : '0';
                                    $sticky_activities[$key]['EveryoneSticky'] = isset($sticky['EveryoneSticky']) ? $sticky['EveryoneSticky'] : '0';
                                }
                                else
                                {   
                                    //remove cache value for this activity
                                    unset($sticky_activities[$key]);
                                }
                            }
                        }
                        //update sticky
                        $this->cache->save('user_sticky_activities_'.$sticky['PostAsModuleEntityID'], $sticky_activities, CACHE_EXPIRATION);
                    }
                }
            }
        }
    }
    

    /**
     * [checkk_sticky_exist Check if the given post is already a sticky for logged-in user]
     * @param   [int]    $activity_id [Activity ID]
     * @param   [int]    $user_id     [User ID]
     */
    public function check_sticky_exist($user_id,$activity_id,$details='0',$exception=FALSE)
    {
        if(CACHE_ENABLE && $details && !$exception)
        {
            $sticky_activity_cache=$this->cache->get('user_sticky_activities_'.$user_id);
            if(!empty($sticky_activity_cache))
            {
                foreach($sticky_activity_cache as $key => $product)
                {
                    if ( $product['ActivityID'] == $activity_id )
                        return $sticky_activity_cache[$key];
                }
            }            
        }              
    
        $this->db->select('StickyPostID,SelfSticky,EveryoneSticky,GroupSticky');
        $this->db->where('PostAsModuleEntityID', $user_id);
        $this->db->where('ActivityID', $activity_id);
        $sql = $this->db->get(STICKYPOST);
        if($sql->num_rows())
        {
            $sticky = $sql->row_array();
            if ($details) {
                return $sticky;
            }
            return $sticky['StickyPostID'];
        }
        return false;
    }

    /**
     * [checkk_sticky_exist Check if the given post is already a sticky for logged-in user]
     * @param [int]    $users_id       [UserID]
     * @param [int]    $sticky_type    [Sticky Type]
     * @param [int]    $role_id        [Role_id]
     * @param [int]    $module_id        [ModuleID]
     * @param [int]    $module_entity_id       [ModuleEntityID]
     */
    public function can_make_sticky($user_id, $role_id='', $module_id='',$module_entity_id='', $sticky_type='')
    {
        $is_admin = FALSE;
        //Super Admin always be able to make any post Sticky for everyone
        if(isset($role_id) && $role_id ==1)
        {
            return 1;
        }
        else 
        {
            //Check if user is group's admin or a superAdmin            
            if($module_id==1)            
            {
                $is_admin = $this->group_model->is_admin($user_id,$module_entity_id);            
                return ($is_admin) ? 2 : 3;
            }

        }
        return 3;
    }


    /**
     * [checkk_sticky_exist Check if the given post is already a sticky for logged-in user]
     * @param [int]    $activity_guid       [activity guid]
     * @param [int]    $activity_user_id    [user_id who posted this activity ]
     * @param [int]    $activity_privacy        [privacy condition on that post]
     */
    public function get_everyone_sticky_user($activity_guid,$activity_user_id,$activity_privacy,$activity_type_id='',$module_id='',$module_entity_id='')
    {
        $user_list = $this->db->select('UserID')->where_in('StatusID',array(1,2))->get(USERS)->result_array();
        $user_arr = array();

        if($module_id == 1 )
        {
            $is_public_group = get_detail_by_id($module_entity_id,$module_id,"IsPublic,StatusID",2);
        }

        foreach ($user_list as $user) 
        {
            # code...
            $activity_guid = (isset($activity_type_id) && $activity_type_id == 7) ? $activity_guid : '';            
            
            if(isset($is_public_group['IsPublic'],$is_public_group['StatusID']) && $is_public_group['IsPublic']==1 && $is_public_group['StatusID'] == 2 )
            {
                $user_arr[] = $user['UserID'];       
            }
            else
            {
                $is_relation = $this->activity_model->isRelation($activity_user_id,$user['UserID'],TRUE,$activity_guid);
                
                if(in_array($activity_privacy, $is_relation))
                {
                    $user_arr[] = $user['UserID'];
                }        
            }
        }
        return $user_arr;
    }

    /**
     * [is_json use to Check valid json string (note: add this in helper)]
     * @param [String]    $string       [json string]     
     */
    public function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function get_all_sticky($user_id, $role_id, $module_entity_id='0', $module_id='0', $include_group_sticky='0', $page_no='1', $page_size='20',$activity_id='',$can_make_sticky='',$sticky_by=0)
    {
        $result_set = array();   
        $blocked_users=$this->activity_model->block_user_list($user_id, 3);
        /*   Privacy Conditions Starts */
        $this->load->model('privacy/privacy_model');
        //Privacy of users        
        $friend_followers_list = $this->user_model->get_friend_followers_list();
        $privacy_options = $this->privacy_model->get_privacy_options();
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $Follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();
        $FilterType = 0;
        $friend_of_friends = $this->user_model->get_friends_of_friend_list();
        $friends[] = 0;
        $Follow[] = 0;
        $friend_of_friends[] = 0;
        $friend_followers_list = array_unique(array_merge($friends, $Follow));
        $friend_followers_list[] = 0;
        if (!in_array($user_id, $friend_followers_list))
        {
            $friend_followers_list[] = $user_id;
        }
        $OnlyFriendFollowers = $friend_followers_list;
        if (in_array($user_id, $friend_followers_list))
        {
            unset($OnlyFriendFollowers[$user_id]);
            if (!$OnlyFriendFollowers)
            {
                $OnlyFriendFollowers[] = 0;
            }
        }

        $friend_followers_list = implode(',', $friend_followers_list);
        $friend_of_friends = implode(',', $friend_of_friends);

        $groups_list = $this->group_model->get_user_group_list();
        //get All Public Groups
        $this->db->select("group_concat(`GroupID` separator ',') as GroupIDs");
        $this->db->where('StatusID','2');
        $this->db->where('IsPublic','1'); 
        $public_groups = $this->db->get(GROUPS)->row_array();
        if(isset($public_groups['GroupIDs']))
        {
            $public_groups = $public_groups['GroupIDs'];
            $public_groups = explode(',', $public_groups);
            $groups_list = array_merge($groups_list,$public_groups);
            $groups_list = array_unique($groups_list);
        }

        $groups_list[] = 0;
        $groups_list = implode(',', $groups_list);
        $EventList = $this->event_model->get_user_joined_events();
        //$PageList = $this->page_model->get_user_pages_list();
        $PageList = $this->page_model->get_feed_pages_condition();

        if (!in_array($user_id, $Follow))
        {
            $Follow[] = $user_id;
        }

        if (!in_array($user_id, $friends))
        {
            $friends[] = $user_id;
        }

        $ActivityTypeAllow = array(1, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 23, 24, 25);
        $ModulesAllowed = array(3, 30);
        $show_suggestions = FALSE;
        $show_media = TRUE;

        if ($privacy_options)
        {
            foreach ($privacy_options as $key => $val)
            {
                if ($key == 'g' && $val == '0')
                {
                    $ModulesAllowed[] = 1;
                }
                if ($key == 'e' && $val == '0')
                {
                    $ModulesAllowed[] = 14;
                }
                if ($key == 'p' && $val == '0')
                {
                    $ModulesAllowed[] = 18;
                }
                if ($key == 'm')
                {
                    if ($val == '1')
                    {
                        $show_media = FALSE;
                        unset($ActivityTypeAllow[array_search('5', $ActivityTypeAllow)]);
                        unset($ActivityTypeAllow[array_search('6', $ActivityTypeAllow)]);
                    }
                }
                if ($key == 'r' && $val == '0')
                {
                    $ActivityTypeAllow[] = 16;
                    $ActivityTypeAllow[] = 17;
                }
                if ($key == 's' && $val == '0')
                {
                    if ($FilterType == '0' && empty($mentions))
                    {
                        $show_suggestions = true;
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
        $ConditionPartOne = "";
        $ConditionPartTwo = "A.ModuleEntityID=" . $user_id;
        $ConditionPartThree = "";
        $ConditionPartFour = "";
        $PrivacyCond = ' ( ';
        $PrivacyCond1 = '';
        $PrivacyCond2 = '';
        if ($friend_followers_list != '' )//&& empty($ActivityIDs))
        {
            $condition = "(
                IF(A.ActivityTypeID=25 OR A.ActivityTypeID=1 OR A.ActivityTypeID=5 OR A.ActivityTypeID=6 OR (A.ActivityTypeID=23 AND A.ModuleID=3) OR (A.ActivityTypeID=24 AND A.ModuleID=3), (
                    A.UserID IN(" . $friend_followers_list . ") OR A.ModuleEntityID IN(" . $friend_followers_list . ") OR " . $ConditionPartTwo . "
                ), '' )
                OR
                IF(A.ActivityTypeID=2, (
                    (A.UserID IN(" . implode(',', $OnlyFriendFollowers) . ") OR A.ModuleEntityID IN(" . implode(',', $OnlyFriendFollowers) . ")) AND (A.UserID!='" . $user_id . "' OR A.ModuleEntityID!='" . $user_id . "')
                ), '' )
                OR
                IF(A.ActivityTypeID=3, (
                    A.UserID IN(" . implode(',', $OnlyFriendFollowers) . ") AND A.UserID!='" . $user_id . "'
                ), '' )
                OR            
                IF(A.ActivityTypeID=9 OR A.ActivityTypeID=10 OR A.ActivityTypeID=14 OR A.ActivityTypeID=15, (
                    (A.UserID IN(" . $friend_followers_list . ") AND A.ModuleEntityID IN(" . $friend_followers_list . ")) OR " . $ConditionPartTwo . "
                ), '' )
                OR
                IF(A.ActivityTypeID=8, (
                    A.UserID='" . $user_id . "' OR A.ModuleEntityID='" . $user_id . "'
                ), '' )";
            /* if(!empty($ActivityTypeFilter))
              {
              if($ActivityTypeFilter[0]==25)
              {
              $Condition.=" OR IF(A.ActivityTypeID=25, (A.Privacy IN(1,2,3)), '' ) ";
              }
              else
              {
              $Condition.=" OR IF(A.ActivityTypeID=25, (
              A.UserID IN(".implode(',', $OnlyFriendFollowers).") OR A.UserID='".$UserID."'
              ), '' ) ";
              }
              } */
            if ($friends)
            {
                $PrivacyCond1 = "IF(A.Privacy='2',
                    A.UserID IN (" . $friend_followers_list . "), true
                )";
            }
            if ($Follow)
            {
                $PrivacyCond2 = "IF(A.Privacy='3',
                    A.UserID IN (" . implode(',', $Follow) . "), true
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
                            IF(ActivityTypeID=7,ModuleID=1 AND ModuleEntityID IN(" . $groups_list . "),false))
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

        $PrivacyCond3 = "IF(A.Privacy='4',
            A.UserID='" . $user_id . "', true
        )";
        if (!empty($PrivacyCond1))
        {
            $PrivacyCond .= $PrivacyCond1 . ' OR ';
        }
        if (!empty($PrivacyCond2))
        {
            $PrivacyCond .= $PrivacyCond2 . ' OR ';
        }
        $PrivacyCond .= " OR A.ActivityID=(SELECT ActivityID FROM " . MENTION . " WHERE ModuleID=3 AND ModuleEntityID='" . $user_id . "')";
        $PrivacyCond .= $PrivacyCond3 . ' )';

        // /echo $PrivacyCond;
        if (!empty($groups_list))
        {
            $ConditionPartOne = $ConditionPartOne . "IF(A.ActivityTypeID=4 OR A.ActivityTypeID=7 OR (A.ActivityTypeID=23 AND A.ModuleID=1) OR (A.ActivityTypeID=24 AND A.ModuleID=1), (
                        A.ModuleID=1 AND A.ModuleEntityID IN(" . $groups_list . ")
                    ), '' )";
        }
        if (!empty($PageList))
        {
            /* $ConditionPartThree = $ConditionPartThree . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17, (
              A.ModuleID=18 AND A.ModuleEntityID IN(" . $PageList . ")
              ), '' )"; */


              //marked comment
            /*$ConditionPartThree = $ConditionPartThree . "IF(A.ActivityTypeID=12 OR A.ActivityTypeID=16 OR A.ActivityTypeID=17 OR (A.ActivityTypeID=23 AND A.ModuleID=18) OR (A.ActivityTypeID=24 AND A.ModuleID=18), (
                        A.ModuleID=18 AND (" . $PageList . ")
                    ), '' )";*/
        }
        if (!empty($EventList))
        {
            $ConditionPartFour = $ConditionPartFour . "IF(A.ActivityTypeID=11 OR (A.ActivityTypeID=23 AND A.ModuleID=14) OR (A.ActivityTypeID=24 AND A.ModuleID=14), (
                        A.ModuleID=14 AND A.ModuleEntityID IN(" . $EventList . ")
                    ), '' )";
        }
        
        $condition1 = "(Case  WHEN A.ModuleID=3 
                              Then (A.UserID IN (".$friend_followers_list.")
                                  OR A.ModuleEntityID IN(".$friend_followers_list.")
                                  OR A.ModuleEntityID=".$user_id."
                                  OR A.Privacy=1)
                              WHEN A.ModuleID=1 
                              THEN (IF((SELECT count(G.GroupID) FROM Groups as G where G.GroupID=A.ModuleEntityID and G.IsPublic=1 AND G.StatusID=2), 1, (A.ModuleEntityID IN(".$groups_list.")))) 
                              WHEN A.ModuleID=14
                              THEN (IF((SELECT count(E.EventID) FROM Events as E where E.EventID=A.ModuleEntityID AND E.Privacy='PUBLIC' AND IsDeleted=0 AND IsActive=1 AND Visibility=0), 1, (SELECT count(EU.EventID) FROM EventUsers as EU WHERE EU.UserID=".$user_id." AND EU.IsDeleted=0)))                              
                              ELSE 1
                        END)";

        $condition2 = "((CASE WHEN (A.Privacy=2 AND A.ModuleID=3) THEN A.UserID IN (".$friend_of_friends.")
                            ELSE (CASE
                                    WHEN (A.Privacy=3 AND A.ModuleID=3) THEN A.UserID IN (". implode(',', $friends) .")
                      ELSE (CASE
                                WHEN (A.Privacy=4 AND A.ModuleID=3) THEN A.UserID=".$user_id."
                                ELSE 1
                            END)
                  END)
                END)
            OR (
             (SELECT M.ActivityID
              FROM Mention as M
              WHERE M.ModuleID=3
                AND M.ModuleEntityID=".$user_id."
                AND M.ActivityID=A.ActivityID LIMIT 1) IS NOT NULL
            ))";

        /*   Privacy Conditions Ends */

        //Query
        $this->db->select('A.PostTitle,A.PostType,A.ActivityID, A.ActivityGUID, ATY.ActivityType, ATY.Template, A.UserID, A.ParentActivityID, A.ActivityTypeID,ATY.ActivityType, A.Params,A.ModuleID, A.ModuleEntityID, A.PostContent, A.Privacy, A.NoOfComments, A.NoOfLikes, concat(U.FirstName,\' \',U.LastName) as Name, SP.SelfSticky, SP.GroupSticky, SP.EveryoneSticky, A.CreatedDate, P.Url as ProfileURL,U.ProfilePicture');
        $this->db->from(STICKYPOST .' as SP ');
        $this->db->join(ACTIVITY . " AS A", ' A.ActivityID = SP.ActivityID AND A.StatusID=2 ', 'left');
        $this->db->join(ACTIVITYTYPE . ' ATY', 'A.ActivityTypeID=ATY.ActivityTypeID');
        $this->db->join(USERS .' AS U', ' U.UserID=A.UserID ', 'left')
        ->join(PROFILEURL.' as P',"U.UserID=P.EntityID AND P.EntityType='User'",'left');
//        if($sticky_by != 2 || $module_id==1)
//        {
//            
//        }
        
        $this->db->where('SP.PostAsModuleEntityID', $user_id);
        
         
        if (!empty($blocked_users))
        {
            $this->db->where_not_in('U.UserID', $blocked_users);
        }
        
        //for single sticky details        
        if($activity_id)
        {
            $this->db->where('SP.ActivityID', $activity_id);   
            $sql = $this->db->get();//echo $this->db->last_query();die;
        }
        else
        {

          
        /* Privacy Condtions New */   
            if(isset($condition1,$condition2))
            {
                $this->db->where($condition1 .' AND '.$condition2, NULL,FALSE);
            }
        /* Privacy Condtions New */   

            if(isset($module_id,$module_entity_id) && $module_id != '3')
            {
                $this->db->where('A.ModuleID', $module_id);
                $this->db->where('A.ModuleEntityID', $module_entity_id);
                              
                if($module_id == 1 && ($can_make_sticky == 2 || $can_make_sticky == 1))
                {
                    $this->db->where('SP.SelfSticky','1');
                }
            }
            
            //select group posts only when include group post is on            
            if(!$include_group_sticky && $module_id != 1)
            {
                //$this->db->where('SP.GroupSticky', '0');
                $this->db->where('A.ModuleID!=', '1');
            }
            if($sticky_by == 1)
            {
                $this->db->where('SP.CreatedBy', $user_id);   
            }
            elseif($sticky_by == 2)
            {
                $this->db->where('SP.CreatedBy!=', $user_id);      
            }
            $this->db->order_by('SP.EveryoneSticky', 'DESC');    
            $this->db->order_by('SP.GroupSticky', 'DESC');    
            $this->db->order_by('A.CreatedDate', 'DESC');    

            $tempdb = clone $this->db;
            $temp_q = $tempdb->get();
            $total_records = $temp_q->num_rows();

            if (isset($page_size, $page_no))
            {
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }

            $sql = $this->db->get();       
            //echo $this->db->last_query();die;
            $result_set=array();
            $result_set['TotalRecords'] = isset($total_records) ? $total_records : 1;
        }
        $count = 0;
        if ($sql->num_rows())
        {   
            foreach ($sql->result_array() as $result)
            {
                //check user's permission on the sticky
                $can_make_sticky = $this->can_make_sticky($user_id, $role_id, $result['ModuleID'],$result['ModuleEntityID']);
                $result['CanMakeSticky'] = $can_make_sticky;
                $result['EntityName'] = $result['Name'];//for now users only
                $result['Album'] = $this->activity_model->get_albums($result['ActivityID'], $result['UserID']);
                $result['Files'] = $this->activity_model->get_activity_files($result['ActivityID']);

                if ($result['ActivityTypeID'] == '14' || $result['ActivityTypeID'] == '15')
                {
                    $result['Album'] = $this->activity_model->get_albums($result['ParentActivityID'], $result['UserID'], '', 'Media');
                }

                if ($result['ActivityTypeID'] == 5 || $result['ActivityTypeID'] == 6 || $result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 9)
                {
                    $album_flag = TRUE;
                    if ($result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 9)
                    {
                        $album_flag = FALSE;
                        $parent_activity_detail = get_detail_by_id($result['ParentActivityID'], '', 'ActivityTypeID,PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                        if (!empty($parent_activity_detail))
                        {
                            if (in_array($parent_activity_detail['ActivityTypeID'], array(5, 6)))
                            {
                                if (!empty($parent_activity_detail['Params']))
                                {
                                    $album_detail = json_decode($parent_activity_detail['Params'], TRUE);
                                    if (!empty($album_detail['AlbumGUID']))
                                    {
                                        @$result['Params']->AlbumGUID = $album_detail['AlbumGUID'];
                                        $album_flag = TRUE;
                                    }
                                }
                            }
                        }
                    }
                    if ($album_flag)
                    {
                        $count = 4;
                        if ($result['ActivityTypeID'] == 6)
                        {
                            $count = $result['Params']->count;
                        }
                        if($this->is_json($result['Params']))
                        {
                            $result['Params'] = json_decode($result['Params']);
                            $album_details = $this->album_model->get_album_by_guid($result['Params']->AlbumGUID);
                            $result['AlbumEntityName'] = $result['EntityName'];
                            $result['EntityName'] = $album_details['AlbumName'];
                            $result['Album'] = $this->activity_model->get_albums($result['ActivityID'], $result['UserID'], $result['Params']->AlbumGUID, 'Activity', $count);
                        }                        
                    }
                }

                if ($result['ActivityTypeID'] == 23 || $result['ActivityTypeID'] == 24)
                {
                    $params = json_decode($result['Params']);
                    if ($params->MediaGUID)
                    {
                        $media_id = get_detail_by_guid($params->MediaGUID, 21);
                        if ($media_id)
                        {
                            $result['Album'] = $this->activity_model->get_albums($media_id, $result['UserID'], '', 'Media', 1);
                        }
                    }
                }

                if($result['PostType'] == '7')
                {
                    $d = $this->user_model->getUserName($result['UserID'], $result['ModuleID'], $result['ModuleEntityID']);
                    $result['Name'] = $d['FirstName'].' '.$d['LastName'];
                    $result['ProfilePicture'] = $d['ProfilePicture'];
                }

                //For shared Posts
                if ($result['ActivityTypeID'] == 9 || $result['ActivityTypeID'] == 10 || $result['ActivityTypeID'] == 14 || $result['ActivityTypeID'] == 15)
                {
                    /*$original_activity = $this->activity_model->get_activity_details($result['ParentActivityID'], $result['ActivityTypeID']);
                    $result['ActivityOwner'] = $this->user_model->getUserName($original_activity['UserID'], $original_activity['ModuleID'], $original_activity['ModuleEntityID']);
                    $result['ActivityOwnerLink'] = $result['ActivityOwner']['ProfileURL'];
                    $result['ActivityOwner'] = $result['ActivityOwner']['FirstName'] . ' ' . $result['ActivityOwner']['LastName'];
                    $result['Album'] = $original_activity['Album'];
                    $result['Files'] = $original_activity['Files'];
                    $result['SharePostContent'] = $result['PostContent'];
                    $result['PostContent'] = $original_activity['PostContent'];
                    $result['ActivityType'] = $original_activity['ActivityType'];*/
                }

                //parse tag in content
                $result['PostContent'] = $this->activity_model->parse_tag($result['PostContent']);

                //check privacy of post
                /*$is_relation = $this->activity_model->isRelation($result['UserID'],$user_id,TRUE,$result['ActivityGUID']);
                if(!in_array($result['Privacy'], $is_relation))
                {
                    $count++;
                    continue;
                }*/

                $result['SelfSticky'] = isset($result['SelfSticky']) ? (int)$result['SelfSticky'] : 0;
                $result['GroupSticky'] = isset($result['GroupSticky']) ? (int)$result['GroupSticky'] : 0;
                $result['EveryoneSticky'] = isset($result['EveryoneSticky']) ? (int)$result['EveryoneSticky'] : 0;
                $result['CanMakeSticky'] = $this->can_make_sticky($user_id,$role_id,$result['ModuleID'],$result['ModuleEntityID']);
                //if anyone of these sticky options comes true we'll set selfSticky true;
                if(($result['CanMakeSticky'] == 3) && ($result['SelfSticky'] || $result['GroupSticky'] || $result['EveryoneSticky']))
                {
                    $result['SelfSticky'] = 1;
                }
                
                $result['IsLike'] = $this->activity_model->is_liked($result['ActivityID'], 'ACTIVITY', $user_id, 3, $user_id);
                //unset all unnecessary fields
                unset($result['ActivityID']);
                unset($result['UserID']);
                unset($result['ParentActivityID']);
                unset($result['ActivityTypeID']);
                unset($result['Params']);
                $result['ModuleEntityGUID'] = get_detail_by_id($result['ModuleEntityID'],$result['ModuleID']);
                unset($result['ModuleEntityID']);
                $result_set[] = $result;
            }    
            /*if(isset($result_set['TotalRecords'])) 
                $result_set['TotalRecords'] -= $count;*/
        }                
        return $result_set;            
    }
    
}   