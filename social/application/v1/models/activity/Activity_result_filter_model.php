<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_result_filter_model extends Common_Model {


    public function __construct() {
        parent::__construct();
    }

    public function filter_result_set($feed_result, $page_no, $user_id, $filter_type, $role_id, $view_entity_tags, $search_key,$entity_id, $entity_module_id, $model_obj,$is_single_activity=0,$module_id=3) {      
        $return = array();
        $cnt = 1;
        $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
        $user_favourite = $this->favourite_model->get_user_favourite();
        $user_flagged = $this->flag_model->get_user_flagged();
        $user_archive = $this->activity_model->get_user_activity_archive();            
        foreach ($feed_result as $res) {
            $activity = array();
            //Suggested Posts
            /* if ($cnt == 6 && $page_no == 1 && $model_obj->show_suggestions) {
                $activity['Album'] = array();
                $ViewTemplate = '';
                if ($cnt == 6) {
                    $ViewTemplate = 'UpcomingEvents';
                }
                $activity['ViewTemplate'] = $ViewTemplate;
                $activity['PollData'] = array();
                $return[] = $activity;
            } */
            
            
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
            $activity['CanShowSettings'] = 0;
            $activity['CanRemove']      = 0;
            $activity['CanMakeSticky']  = 0;
            $activity['ShowPrivacy']    = 0;
            $activity['PostAsEntityOwner'] = 0;
            $activity['IsMember']       = 1;
            $activity['IsArchive']      = 0;
            $activity['ShowInviteGraph']= 0;
            $activity['IsPined']        = ($res['IsVisible'] == '3') ? 1 : 0;

            $activity['PollData']       = array();
            $activity['Reminder']       = (object) [];
            $activity['ActivityGUID']   = $activity_guid;
            $activity['ModuleID']       = $module_id;
            $activity['UserGUID']       = $res['UserGUID'];
            $activity['ActivityType']   = $res['ActivityType'];
            $activity['NotifyAll']      = $res['NotifyAll'];
            $activity['NoOfFavourites'] = $res['NoOfFavourites'];
            $activity['IsFeatured']     = $res['IsFeatured'];
            //$activity['ShareEntityTagged'] = ''; /* added by gautam */

            $activity['IsSingleActivity'] = $is_single_activity;
            $activity['LikeAllowed']    = $res['LikeAllowed'];
            $activity['FlagAllowed']    = $res['FlagAllowed'];
            $activity['ShareAllowed']   = $res['ShareAllowed'];
            $activity['FavouriteAllowed'] = $res['FavouriteAllowed'];
            $activity['NoOfShares']     = $res['NoOfShares'];
            $activity['Message']        = $res['Template'];
            $activity['ViewTemplate']   = $res['ViewTemplate'];
            $activity['CreatedDate']    = $res['CreatedDate'];
            $activity['ModifiedDate']   = $res['ModifiedDate'];
            $activity['PromotedDate']   = $res['PromotedDate'];
            $activity['IsSticky']       = 0;
            $activity['Visibility']     = $res['Privacy'];
            $activity['PostContent']    = $res['PostContent'];
            $activity['PostTitle']      = $res['PostTitle'];
            $activity['PostType']       = $res['PostType'];
            $activity['ModuleEntityID'] = $res['ModuleEntityID'];
            $activity['ParentActivityID'] = $res['ParentActivityID'];
            $activity['SharedActivityModule'] = '';
            $activity['SharedEntityGUID'] = '';
            $activity['Summary'] = !empty($res['Summary']) ? $res['Summary'] : '';

            $activity['Facts'] = $res['Facts'];
            $activity['IsPromoted']     = $res['IsPromoted'];
            $activity['Album']          = array();

            $activity['ContestEndDate'] = $res['ContestEndDate'];
            $activity['IsWinnerAnnounced'] = $res['IsWinnerAnnounced'];
            //$activity['Participants'] = $this->contest_model->get_participant_friends($res['ActivityID'],$user_id);
           // $activity['Winners'] = $this->contest_model->get_contest_winners($res['ParentActivityID'],$user_id);
            $activity['IsAdmin'] = 0;
            if($module_id == 3 && $module_entity_id == $this->UserID) {
                $activity['ModuleEntityGUID'] = $this->LoggedInGUID;
            } else {
                $activity['ModuleEntityGUID'] = get_detail_by_id($res['ModuleEntityID'],$res['ModuleID']);
            }
            
            $activity['MembersList'] = array();

            if($is_single_activity=='1' && $res['PostType']=='4')
            {
                $members_data = $this->activity_model->group_recent_comments_owner($res['ActivityID'],$user_id);
                $activity['MembersList'] = $members_data['Data'];
            }

            $activity['IsParticipating'] = 0;

            $res_entity_type            = 'Activity';
            $res_entity_id              = $activity_id;
            
            $BUsers = array();//$this->activity_model->block_user_list($module_entity_id, $module_id);
//            if (in_array($res['UserID'], $BUsers)) {
//                continue;
//            }

            if ($filter_type == 7) {
                $activity['IsDeleted'] = 1;
            }
            
            $activity['IsTaskDone'] = 0;
            /* if($mydesk_task = $this->mydesk_model->is_mydesk_task($activity_id,$user_id,true)) {
                $activity['IsTaskDone'] = (isset($mydesk_task['Status']) && $mydesk_task['Status'] == 'NOTDONE') ? 0 : 1;
            }
            
            $activity['IsAnyoneTagged'] = $this->activity_model->is_anyone_mentioned($activity_id);
            $activity['IsWatchList'] = $this->watchlist_model->is_watchlist($activity_id,$user_id);
            $activity['IsTagged'] = $this->activity_model->is_tagged($activity_id, $user_id);
             * 
            
            //Link Variable Assignment Starts              
            $activity['Links'] = $this->activity_model->get_activity_links($activity_id);
             */
            $edit_post_content              = $activity['PostContent'];
            //$activity['PostContent']        = $this->convert_unicode_html_emoji($activity['PostContent']);
            $activity['PostContent']        = $this->activity_model->parse_tag($activity['PostContent'], $activity_id);
            $activity['EditPostContent']    = $this->activity_model->parse_tag_edit($edit_post_content, $activity_id);
            
            //$activity['PostTitle']          = $this->activity_model->parse_tag($activity['PostTitle'], $activity_id);
            //$activity['PostTitle']          = $this->convert_unicode_html_emoji($activity['PostTitle']);

           /* if (isset($res['ReminderGUID'])) {
                $activity['Reminder'] = array('ReminderGUID' => $res['ReminderGUID'], 'ReminderDateTime' => $res['ReminderDateTime'], 'CreatedDate' => $res['ReminderCreatedDate'], 'Status' => $res['ReminderStatus'], 'Meridian' => '');
            }
            * 
            */

            if (in_array($activity_type_id, array(23, 24))) {
                $params = json_decode($res['Params'], true);
                if ($params['MediaGUID']) {
                    $res_entity_id = get_detail_by_guid($params['MediaGUID'], 21);
                    if ($res_entity_id) {
                        $res_entity_type = 'Media';
                        $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count($res_entity_type, $res_entity_id, $BUsers); //$res['NoOfComments'];
                        $activity['NoOfLikes'] = $this->activity_model->get_like_count($res_entity_id, $res_entity_type, $BUsers); //$res['NoOfLikes'];
                        // $activity['NoOfDislikes'] = $this->get_like_count($res_entity_id, $res_entity_type, $BUsers, 3); //$res['NoOfDislikes'];
                        $activity['Album'] = $this->activity_model->get_albums($res_entity_id, $res['UserID'], '', $res_entity_type, 1);
                    }
                }
            } else {
                if (!in_array($activity_type_id, array(5, 6, 9, 10, 14, 15))) {
                    $activity['Album'] = $this->activity_model->get_albums($res_entity_id, $res['UserID'], '', $res_entity_type, 0);
                }
                if ($BUsers) {
                    $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count($res_entity_type, $activity_id, $BUsers); //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, $res_entity_type, $BUsers); //
                    //$activity['NoOfDislikes'] = $this->get_like_count($activity_id, $res_entity_type, $BUsers, 3); //
                } else {
                    $activity['NoOfComments'] = $res['NoOfComments']; //$res['NoOfComments'];
                    $activity['NoOfLikes'] = $res['NoOfLikes']; //
                    //$activity['NoOfDislikes'] = $res['NoOfDislikes']; //
                }
            }

            if (isset($user_archive[$activity_id])) {
                $activity['IsArchive'] = $user_archive[$activity_id];
            }

            $activity['CommentsAllowed'] = 0;
            if ($res['IsCommentable'] && $res['CommentsAllowed']) {
                $activity['CommentsAllowed'] = 1;
            }


            $activity['Files'] = array();
           /* if ($res['IsFileExists']) {
                $activity['Files'] = $this->activity_model->get_activity_files($activity_id);
            }
            * 
            */

            $activity['Params'] = json_decode($res['Params']);
            //$activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
            $activity['IsFavourite'] = (in_array($activity_id, $user_favourite)) ? 1 : 0;

            $activity['Flaggable'] = $res['Flaggable'];
            $activity['FlaggedByAny'] = 0;
            $activity['CanBlock'] = 0;

            if ($res['UserID'] == $user_id) {
                $activity['IsOwner'] = 1;
            }

            $activity['IsFlagged'] = (in_array($activity_id, $user_flagged)) ? 1 : 0;

            if ($user_id == $res['ModuleEntityID'] && $res['ModuleID'] == 3) {
                $activity['IsOwner'] = 1;
                $activity['CanRemove'] = 1;
            }

            $activity['EntityName'] = '';
            $activity['EntityProfilePicture'] = '';
            $activity['EntityGUID'] = '';
            $activity['EntityID'] = '';
            $activity['UserName'] = $res['FirstName'] . ' ' . $res['LastName'];
            $activity['UserProfilePicture'] = $res['ProfilePicture'];
            $activity['UserProfileURL'] = $res['UserID'];//get_entity_url($res['UserID'], 'User', 1);
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
            
            if($res['PostType'] == '7') {
                $activity['ShowFlagBTN'] = 0;
            }
            
            if($res['ActivityType'] == 'ProfilePicUpdated' || $res['ActivityType'] == 'ProfileCoverUpdated') {
                $activity['ShowFlagBTN'] = 0;
            }
            
            if ($module_id == 1) {
                $group_details = check_group_permissions($user_id, $module_entity_id);

                /* if($this->group_model->is_admin($user_id, $module_entity_id))
                {
                    $activity['IsAdmin'] = 1;
                }
                 * 
                 */

                //$entity = get_detail_by_id($module_entity_id, $module_id, "Type, GroupGUID, GroupName, GroupImage", 2);
                if (isset($group_details['Details']) && !empty($group_details['Details'])) {
                    $entity = $group_details['Details'];
                    
                    // Get entity url
                    $entity = $this->group_model->get_group_details_by_id($module_entity_id, '', $entity);                    
                    $activity['EntityProfileURL']   = $this->group_model->get_group_url($module_entity_id, $entity['GroupNameTitle'], true, 'index');
                    
                    $activity['EntityGUID']         = $entity['GroupGUID'];
                    $activity['EntityID']           = $module_entity_id;
                    $activity['EntityName']         = $entity['GroupName'];
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
                        $activity['IsAdmin'] = 1;
                    }
                    if ($this->group_model->check_group_creator($module_entity_id, $res['UserID'])) {
                        $activity['CanBlock'] = 0;
                    }

                   /* if ($this->IsApp == 1) /// For Mobile  {
                        // Added by gautam 
                        if ($res['ModuleEntityOwner'] == 1) {
                            $activity['UserName'] = $activity['EntityName'];
                            $activity['UserProfilePicture'] = $activity['EntityProfilePicture'];
                            $activity['UserProfileURL'] = $activity['EntityProfileURL'];
                            $activity['UserGUID'] = $activity['EntityGUID'];
                        }
                        
                    } */
                    if($res['UserID']!=$user_id)
                    {
                        $activity['IsExpert'] = $this->group_model->check_is_expert($res['UserID'],$module_entity_id);
                    }
                }
            } else if ($module_id == 3) {
                $activity['EntityName']             = $activity['UserName'];
                $activity['EntityProfilePicture']   = $activity['UserProfilePicture'];
                $activity['EntityGUID']             = $activity['UserGUID'];
                $activity['EntityID']               = $res['UserID'];
                $activity['Occupation']               = '';
                //$entity = get_detail_by_id($module_entity_id, $module_id, 'FirstName,LastName, UserGUID', 2);
                $entity = $this->user_model->get_user_details($module_entity_id);
                if ($entity) {
                    $entity['EntityName'] = trim($entity['FirstName'] . ' ' . $entity['LastName']);
                    $activity['EntityName'] = $entity['EntityName'];
                    $activity['EntityGUID'] = $entity['UserGUID'];
                    $activity['Occupation'] = $entity['Occupation'];
                    $activity['EntityID']   = $module_entity_id;
                }

                $activity['EntityProfileURL'] = '';//get_entity_url($res['ModuleEntityID'], 'User', 1);
                if ($user_id == $module_entity_id) {
                    $activity['IsEntityOwner'] = 1;
                    $activity['CanRemove'] = 1;
                    $activity['CanBlock'] = 1;
                }
                $activity['IsAdmin'] = $this->user_model->is_super_admin($res['UserID'], 1);
            } else if ($module_id == 14) {
                $entity = get_detail_by_id($module_entity_id, $module_id, "EventGUID, Title, ProfileImageID", 2);
                if ($entity) {
                    $activity['EntityName'] = $entity['Title'];
                    $activity['EntityProfilePicture'] = $entity['ProfileImageID'];
                    $activity['EntityGUID'] = $entity['EventGUID'];
                    $activity['EntityID']   = $module_entity_id;
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
            } else if ($module_id == 18) {
                $entity = get_detail_by_id($module_entity_id, $module_id, "PageGUID, Title, ProfilePicture, PageURL, CategoryID", 2);
                if ($entity) {
                    $activity['EntityName'] = $entity['Title'];
                    $activity['EntityProfilePicture'] = $entity['ProfilePicture'];
                    $activity['EntityProfileURL'] = $entity['PageURL'];
                    $activity['EntityGUID'] = $entity['PageGUID'];
                    $activity['EntityID']   = $module_entity_id;
                    $this->load->model('category/category_model');
                    $category_name = $this->category_model->get_category_by_id($entity['CategoryID']);
                    $category_icon = $category_name['Icon'];
                    if ($entity['ProfilePicture'] == '') {
                        $activity['EntityProfilePicture'] = $category_icon;
                    }

                    $activity['ModuleEntityOwner'] = $res['ModuleEntityOwner'];
                    if ($res['PostAsModuleID'] == 18) {
                        $PostAs = $this->page_model->get_page_detail_cache($res['PostAsModuleEntityID']);
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
            } else if ($module_id == 34) {
                $activity['IsExpert'] = $this->forum_model->check_is_expert($res['UserID'],$module_entity_id);
                $entity = get_detail_by_id($module_entity_id, $module_id, "ForumCategoryGUID, Name, MediaID, URL", 2);
                if ($entity) {
                    $activity['EntityName'] = $entity['Name'];
                    $activity['EntityProfilePicture'] = $entity['MediaID'];
                    $activity['EntityGUID'] = $entity['ForumCategoryGUID'];
                    $activity['EntityProfileURL'] = $this->forum_model->get_category_url($module_entity_id);
                    $activity['EntityID']   = $module_entity_id;
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

            /* if ($activity['UserProfilePicture'] == '')
              {
              $activity['UserProfilePicture'] = "user_default.jpg";
              } */
            
            if (!isset($activity['EntityProfileURL'])) {
                $activity['EntityProfileURL'] = $activity['UserProfileURL'];
            }

            if ($activity['IsOwner'] == 1) {
                $activity['IsLike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID']);
               // $activity['IsDislike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, $res['PostAsModuleID'], $res['PostAsModuleEntityID'], 3);
            } else {
                $activity['IsLike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, 3, $user_id);
                //$activity['IsDislike'] = $this->activity_model->is_liked($res_entity_id, $res_entity_type, $user_id, 3, $user_id, 3);
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
                        $count = $activity['Params']->count;
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
                $include_reply = 0;
                if($is_single_activity==1 || $this->IsApp==1)
                {
                    $include_reply = 1;
                }
                $activity['Comments'] = $this->activity_model->getActivityComments('Activity', $activity_id, '1', COMMENTPAGESIZE, $user_id, $activity['CanRemove'], 2, TRUE, $BUsers, FALSE, '', $res['PostAsModuleID'], $res['PostAsModuleEntityID'],'',0,'',$include_reply);
            }

            if ($res['ActivityTypeID'] == 1 || $res['ActivityTypeID'] == 7 || $res['ActivityTypeID'] == 11 || $res['ActivityTypeID'] == 12) {
                $activity['PostContent'] = str_replace('­', '', $activity['PostContent']);
                if (empty($activity['PostContent'])) {
                    $pcnt = $this->activity_model->get_photos_count($res['ActivityID']);
                    if (isset($pcnt['Media'])) {
                        $activity['Message'] .= ' added ' . $pcnt['MediaCount'] . ' new ' . $pcnt['Media'];
                    }
                }
            }

            if (isset($activity['RatingData']['CreatedBy']['ModuleID'])) {
                $activity['UserProfileURL'] = $activity['RatingData']['CreatedBy']['ProfileURL'];
                $activity['UserProfilePicture'] = $activity['RatingData']['CreatedBy']['ProfilePicture'];
            }

//            $permission = $this->privacy_model->check_privacy($user_id, $res['UserID'], 'view_profile_picture');
//            if (!$permission && $module_id == 3) {
//                $activity['UserProfilePicture'] = '';
//            }
            $activity['PostContent'] = trim(str_replace('&nbsp;', ' ', $activity['PostContent']));
            $activity['PostTitle'] = trim(str_replace('&nbsp;', ' ', $activity['PostTitle']));
                        
            if ($res['ActivityTypeID'] == 16 || $res['ActivityTypeID'] == 17) {
                if (!$activity['RatingData']) {
                    continue;
                }
            }
            
            //$this->load->model(array('activity/activity_front_helper_model'));
            //$activity = $this->activity_front_helper_model->get_sticky_setting($user_id,$activity_id,$role_id,$activity);

            $activity['IsAnonymous'] = $res['IsAnonymous'];

            if ($res['IsAnonymous'] == 1 && $res['UserID'] != $user_id) {
                $activity['UserName'] = "Anonymous User";
                $activity['UserProfileURL'] = '';
                $activity['UserProfilePicture'] = '';
            }

            //Check if  view Tags is allowed
            if ($view_entity_tags) {
                $activity['EntityTags'] = $this->tag_model->get_entity_tags($search_key, 1, 30, 1, 'ACTIVITY', $activity_id, $user_id);
            }
            //$activity['ActivityURL'] = get_single_post_url($activity, $res['ActivityID'], $res['ActivityTypeID'], $res['ModuleEntityID']);
            
            /* Share Details */
            $share_data = array();
            if($res['ActivityTypeID'] == '9' || $res['ActivityTypeID'] == '10' || $res['ActivityTypeID'] == '14' || $res['ActivityTypeID'] == '15')
            {
                $share_data = $this->getShareDetails($activity,$activity_type_id,$res['UserID']);
                
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
            //$this->IsApp = 1;
            if ($this->IsApp == 1) { /* For Mobile */
                //print_r($activity['PostContent']);die;
                $activity['TagedUser'] = json_decode($res['TagedUser']);
                $activity['PostContent'] = $this->get_description($activity['PostContent']);
                $activity = $this->getTitleMessage($activity, $entity_id, $entity_module_id,$module_id);
                
            } else {
                $activity['PostContent'] = html_entity_decode($activity['PostContent']);
            }           
            unset($activity['EntityID']);
            unset($activity['UserID']);
            
            $activity['ActivityTypeID'] = $activity_type_id;
            
            $return[] = $activity;
            $cnt++;
        }

        return $return;
    }

    function convert_unicode_html_emoji($content){
              preg_match_all('/((\\\\u)+[0-9A-F]{1,4})+/i', $content, $matches);
            if (!empty($matches[0])) {
              foreach ($matches[0] as $match) {
                  $match1 ="\"$match\"";
                // echo emoji_unified_to_html(json_decode($match));die;
                   $emoji = emoji_unified_to_html(json_decode($match1));
                    $content = str_replace($match, $emoji, $content);
              }}
              return $content;
    }

    /*title message*/
    function getTitleMessage($activity, $entity_id, $entity_module_id,$module_id=3) {
        $entity_type = array(1=>'group',3=>'user',14=>'event',18=>'page', 34=>'Forum Category');

        $msz = $activity['Message'];

        $EntityURL  = base_url();
        $shareType  = 'Post';
        $bold_start = '<b>';
        $bold_close = '</b>';
        $PhotoMediaGUID = '';

        if($activity['ActivityType'] == 'Share' || $activity['ActivityType'] == 'ShareMedia' || $activity['ActivityType'] == 'ShareSelf' || $activity['ActivityType'] == 'ShareMediaSelf') {
            if (isset($activity['ShareDetails']['Album']['length']) > 0) {
                $PhotoMediaGUID = $activity['ShareDetails']['Album']['0']['Media']['0']['MediaGUID'];
                $shareType = 'Photo';
            }
        } elseif(isset($activity['Album']) && count($activity['Album'])>0) {
            $PhotoMediaGUID = $activity['Album']['0']['Media']['0']['MediaGUID'];
            $shareType = 'Photo';
        }

        $ActivityOwnerLink = base_url();

        if(isset($msz)) {
            if($activity['ActivityType'] == 'GroupPostAdded' && $activity['PostType'] ==7) {
                if ($activity['EntityName'] == "") {
                    $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID=' . $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=group></a>', $msz);
                    if(isset($activity['EntityMembers'])) {
                        $str .=  $this->getMembersHTML($activity['EntityMembers'], $activity['EntityID'], $activity['EntityGUID'], $activity['ModuleID'], 1) . $bold_close . '</a>';
                    }
                }
                else {
                    $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=group">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $msz);
                }
            }
            elseif(($activity['ActivityType'] == 'AlbumAdded' || $activity['ActivityType'] == 'AlbumUpdated') && 
                isset($activity['ModuleEntityOwner']) == '1' && $activity['ModuleID'] == '18') {
                $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=group">' . $bold_start . $activity['UserName'] . $bold_close . '</a>', $msz);
            } else {
                if($activity['ActivityType'] == 'ForumPost') {
                    if($activity['IsEntityOwner'] == '1') {
                        $msz = str_replace("{{User}}", '{{User}} <i data-toggle="tooltip" data-original-title="Admin" class="ficon-admin f-green"></i>', $msz);
                    }
                    elseif ($activity['IsExpert'] == '1') {
                        $msz = str_replace("{{User}}", '{{User}} <i data-toggle="tooltip" data-original-title="Expert" class="ficon-expert f-blue"></i>', $msz);
                    }
                }
                $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['UserName'] . $bold_close . '</a>', $msz);
            }
            $str = str_replace("{{SUBJECT}}", '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['UserName'] . $bold_close . '</a>', $str);
            $str = str_replace("{{ACTIVITYOWNER}}", '<a href="' . base_url() . isset($activity['ActivityOwnerLink']) .'">' . isset($activity['ActivityOwnerLink']) . '</a>', $str);//doubt
        }
        else {
            $str = '';
        }
        switch ($activity['ActivityType']) {
            case 'ProfilePicUpdated':
            case 'ProfileCoverUpdated'://checked
                if($activity['ModuleID'] == 1) {
                    $str = str_replace("{{EntityName}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=group">' . $activity['EntityName'] . '</a>\'s', $msz);
                    $str = str_replace("{{Entity}}", '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['UserName'] .$bold_close . '</a>', $str);
                }
                if ($activity['ModuleID'] == 3) {
                    $str = str_replace("{{Entity}}", '<a href="' . $EntityURL . '?GUID=' . $activity['EntityGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['UserName'] . $bold_close . '</a>',$msz);
                    $str = str_replace("{{EntityName}}", 'their',$str);
                }
                if ($activity['ModuleID'] == 14) {
                    $str = str_replace("{{EntityName}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=event">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>\'s' ,$msz);
                    $str = str_replace("{{Entity}}", '<a href="' . $activity['UserProfileURL'] . '?GUID='. $activity['UserGUID'] . '&UserID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['UserName'] . $bold_close . '</a>', $str);
                }
                if ($activity['ModuleID'] == 18) {
                    $str = str_replace("{{EntityName}}", 'their', $msz);
                    $str = str_replace("{{Entity}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=page">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                }
                break;
            case 'RatingAdded':
            case 'RatingUpdated'://checked
                $EntityGUID = $activity['RatingData']['CreatedBy']['EntityGUID'];
                if ($activity['RatingData']['CreatedBy']['ModuleID'] == '18') {
                    $entitytype = 'page';
                    $ID = '&ID=' . $activity['EntityID']; 
                } else {
                    $entitytype = 'user';
                    $ID = '&ID=' . $activity['UserID'];
                }
                $str = str_replace("{{REVIEWER}}", '<a href="' . $EntityURL . '?GUID='. $EntityGUID . '&ID=' . $ID . '&Type='. $entitytype .'\">' . $bold_start .  $activity['RatingData']['CreatedBy']['EntityName'] . $bold_close .'</a>', $str);
                $str = str_replace("{{OBJECT}}", '<span><a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] .  $ID . '&Type=page">' . $bold_start . $activity['EntityName'] . $bold_close . '</a></span>',$str);
                break;
            case 'PollCreated'://checked
                if ($activity['ModuleID'] == 18) {
                    $entitytype = 'page';
                    $ID = '&ID=' . $activity['EntityID']; 
                } else {
                    $entitytype = 'user';
                    $ID = '&ID=' . $activity['UserID'];
                }
                $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . $ID .'&Type='. $entitytype .'">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>',$msz);
                $str = str_replace("{{Entity}}", '<a  href="' . $EntityURL . '?GUID='. $activity['ActivityGUID'] .'&Type=post">' . $bold_start . $activity['ViewTemplate'] . $bold_close . '</a>',$str);
                break;
            case 'AlbumAdded'://checked
                if (isset($activity['Album']['0'])) {                    
                    $str = str_replace("{{Entity}}", '<a  href="' . $EntityURL . '?GUID='. $activity['Album']['0']['AlbumGUID'] . '&Type=album">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>',$str);
                } else {
                    $str = str_replace("{{Entity}}", '',$str);
                }
                if ($activity['ModuleID'] !== '3' && $activity['AlbumEntityName']) {
                    if ($activity['ModuleID'] == '1') {
                        $str .= ' in ' . '<a href="' . site_url() . 'group/' . $activity['EntityProfileURL'] . '">' . $bold_start .
                        $activity['AlbumEntityName'] . $bold_close . '</a>';
                    } else if ($activity['ModuleID'] == '18') {
                        $str .= ' in ' . '<a href="' . site_url() . 'page/' . $activity['EntityProfileURL'] . '">' . $bold_start .
                        $activity['AlbumEntityName'] . $bold_close . '</a>';
                    } else if ($activity['ModuleID'] == '14') {
                        $str .= ' in ' . '<a href="' . site_url() . 'events/' . $activity['EntityGUID'] . '">' . $bold_start .
                        $activity['AlbumEntityName'] . $bold_close . '</a>';
                    }
                }
                break;
            case 'AlbumUpdated':
            $activity['Params'] = json_decode(json_encode($activity['Params']), true);
                    if (isset($activity['Album']['0'])) {
                        $str = str_replace("{{Entity}}", '<a  href="' . $EntityURL . '?GUID='. $activity['Album']['0']['AlbumGUID'] . '&Type=album">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>',$str);                        
                        $str = str_replace("{{AlbumType}}", 'Media',$str);
                        $str = str_replace("{{count}}",$activity['Params']['count'], $str);
                    } else {
                        $tr  = str_replace("{{Entity}}", '', $str);
                        $str = str_replace("{{AlbumType}}", '',$str);
                        $str = str_replace("{{count}}", '',$str);
                    }
                    if ($activity['ModuleID'] !== '3' && $activity['AlbumEntityName']) {
                        if ($activity['ModuleID'] == '1') {
                            $str .= ' in ' . '<a href="' . site_url() . 'group/' . $activity['EntityProfileURL'] . '">' . $bold_start . $activity['AlbumEntityName'] . $bold_close .'</a>';
                        } else if ($activity['ModuleID'] == '18') {
                            $str .= ' in ' . '<a href="' . site_url() . 'page/' . $activity['EntityProfileURL'] . '">' . $bold_start . $activity['AlbumEntityName'] . $bold_close . '</a>';
                        } else if ($activity['ModuleID'] == '14') {
                            $str .= ' in ' . '<a href="' . site_url() . 'events/' . $activity['EntityGUID'] . '">' . $bold_start . $activity['AlbumEntityName'] . $bold_close . '</a>';
                        }

                    }
                    break;
            case 'GroupJoined':
                    $str = str_replace("{{Entity}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] .'&Type=group">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                    break;
            case 'GroupPostAdded'://checked
                        if ($activity['PostType'] !== '7') {
                            if ($activity['EntityName'] !== '') {
                                $str = '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['UserName'] . $bold_close . '</a> ';
                                if($activity['IsEntityOwner'] == '1') {
                                    $str .= '<i data-toggle="tooltip" data-original-title="Admin" class="ficon-admin f-green"></i> ';
                                }
                                elseif($activity['IsExpert'] == '1') {
                                    $str .= '<i data-toggle="tooltip" data-original-title="Expert" class="ficon-expert f-blue"></i> ';
                                }
                                if($module_id != 1)
                                {
                                    $str .= 'posted in <a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=group">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>';
                                }
                            } else {
                                $str = '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['UserName'] . $bold_close .'</a>';
                                if($module_id != 1)
                                {

                                    if(isset($activity['EntityMembers'])) {
                                        $str .= ' posted in '.$this->getMembersHTML($activity['EntityMembers'], $activity['EntityMembersCount'], 
                                                    $activity['EntityID'], $activity['EntityGUID'], 1) . $bold_close . '</a>';
                                    }
                                }
                            }
                        }
                    break;
            case 'ForumPost'://checked
                    $str = str_replace("{{Entity}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&EntityID=' . $activity['EntityID'] . '&Type=forum">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                    break;
            case 'EventWallPost'://checked
                        $str = '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID='.$activity['UserID'].'&Type=user">' . $activity['UserName'] . '</a> posted in <a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=event">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>';
                    break;
            case 'PagePost'://checked
                    if ($msz == "{{User}}") {
                        if ($activity['ModuleEntityOwner'] == 1) {
                            $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=page">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $msz);
                        } else {
                            $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $msz);
                        }
                    } else {
                        if ($activity['ModuleEntityOwner'] == 1) {
                            $str = str_replace("{{User}}", '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['EntityID'] . '&Type=page">' . $bold_start . $activity['UserName'] . $bold_close . '</a>', $msz);
                        }
                        $str = str_replace("{{Entity}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['EntityID'] . '&Type=page">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                    }
                    break;
            case 'Follow':
            case 'FriendAdded':
                    $str = str_replace("{{Entity}}", '<a href="' . $EntityURL . '?GUID='. $activity['UserGUID'] . '&ID=' . $activity['UserID'] . '&Type=user">' .    $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                    break;
            case 'Share':
            case 'ShareMedia'://checked
                    if ($activity['SharedActivityModule'] == 'Users') {
                        $activity['SharedActivityModule'] = 'user';
                    }
                    if ($shareType == 'Photo') {
                        $str = str_replace("{{ENTITYTYPE}}", /*'<a href="javascript:void(0);?GUID=' . $activity['SharedEntityGUID'] .
                            '&ID='. $activity['EntityID'] . '&Type='. $activity['SharedActivityModule'] .'">' .*/ $bold_start . $shareType . $bold_close /*.'</a>'*/, $str);
                    } else {
                        $str = str_replace("{{ENTITYTYPE}}", /*'<a href="' . base_url() . $activity['UserProfileURL'] . '/activity/' . $activity['ActivityGUID'] . '">' .*/ $bold_start . $shareType . $bold_close /*. '</a>'*/, $str);
                    }
                    $str = str_replace("{{OBJECT}}", '<a "href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID='. $activity['ShareDetails']['UserID']  .'&Type='. $activity['SharedActivityModule'] .'">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);

                    if ($activity['ShareDetails']['ActivityModule'] == 'Page' || $activity['ShareDetails']['ActivityModule'] == 'Group' || $activity['ShareDetails']['ActivityModule'] == 'Forum Category' || $activity['ShareDetails']['ActivityModule'] == 'Event' || $activity['ShareDetails']['ActivityModule'] == 'Polls')
                    {
                        $str = str_replace("{{OBJECT}}", '<a "href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID='. $activity['ShareDetails']['UserID']  .'&Type='. $activity['SharedActivityModule'] .'">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                    } else
                    {
                        $str = str_replace("{{OBJECT}}", '<a "href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID='. $activity['ShareDetails']['UserID']  .'&Type='. $activity['SharedActivityModule'] .'">' . $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                    }
                    break;
            case 'ShareSelf':
            case 'ShareMediaSelf':
                    if ($activity['SharedActivityModule'] == 'Users') {
                        $activity['SharedActivityModule'] = 'user';
                    }
                    if ($activity['EntityType'] == 'Photo') {
                        $str = str_replace("{{ENTITYTYPE}}", /*'<a href="javascript:void(0);">' .*/ $bold_start . $activity['ShareDetails']['EntityType'] . $bold_close /*. '</a>'*/,$str);
                    } else {
                        $str = str_replace("{{ENTITYTYPE}}", /*'<a href="' . base_url() . $activity['UserProfileURL'] . '/activity/' . $activity['ActivityGUID'] . '">' .*/ $bold_start . $activity['ShareDetails']['EntityType'] . $bold_close /*. '</a>'*/,$str);
                    }
                    if ($activity['ShareDetails']['ActivityModule'] == 'Page' || $activity['ShareDetails']['ActivityModule'] == 'Group' || $activity['ShareDetails']['ActivityModule'] == 'Forum Category' || $activity['ShareDetails']['ActivityModule'] == 'Event' || $activity['ShareDetails']['ActivityModule'] == 'Polls') {
                        $str = str_replace("{{OBJECT}}", '<a href="' . $ActivityOwnerLink . '?GUID='. $activity['ShareDetails']['UserGUID'] . '&ID='. $activity['ShareDetails']['UserID'] .'&Type='. $entity_type[$activity['ShareDetails']['EntityModuleID']] .'">' . $bold_start . $activity['ShareDetails']['UserName'] . $bold_close . '</a>',$str);
                    } else {
                            $str = str_replace("{{OBJECT}}", '<a href="' . $ActivityOwnerLink . '?GUID='. $activity['ShareDetails']['UserGUID'] . '&ID='. $activity['ShareDetails']['UserID'] . '&Type='. $entity_type[$activity['ShareDetails']['ModuleID']] . '">' . $bold_start . $activity['ShareDetails']['UserName'] . $bold_close . '</a>',$str);

                    }
                    break;
            case 'Post'://checked
                    $str = str_replace("{{OBJECT}}", '<a href="' . $EntityURL . '?GUID='. $activity['EntityGUID'] . '&ID=' . $activity['ModuleEntityID'] . '&Type=user">' .  $bold_start . $activity['EntityName'] . $bold_close . '</a>', $str);
                    break;
            default:
                    break;
        }
        
        $activity['Message'] = $str;

        return $activity;
    
    }

    public function getMembersHTML($members, $count, $module_entity_id, $module_entity_guid, $keep_current_user) {
        $bold_start = '<b>';        
        $html = '<a href="' . site_url() . '?GUID='. $module_entity_guid . '&ID=' . $module_entity_id . '&Type=group">' . $bold_start;
        foreach ($members as $key => $value) {
            if ($key == 3) {
                return;
            }
            if ($value['ModuleID'] == 3 && $keep_current_user !== 1) {
                return false;
            } else {
                $html .= $value['FirstName'];
            }
            if ($count - 2 == $key) {
                $html .= ' and ';
            } elseif($count-1 !== $key) {
                $html .= ', ';
            }
        }

        return $html;
    }

    function get_description($post_content) {
        $post = $post_content;
        //echo $post;die;
        $post = preg_replace('/<img (.*) >/', '<img $1 />', $post);
        $post = preg_replace('/style="\s*?"/', '', $post);
        $post = str_replace('<p>', '<exp><p>', $post);
        $post = str_replace('</p>', '<exp></p>', $post);
        $post = str_replace('<span', '<exp><span', $post);
        $post = str_replace('</span>', '<exp></span>', $post);
        $post = str_replace('<img', '<exp> type="image"', $post);
        $post = str_replace('/>', '<exp>>', $post); //closing tag added for tagging issue
        $post = str_replace('">', '<exp>">', $post); //closing tag added for tagging issue
        $post = str_replace('<iframe', '<exp> type="video"', $post);
        $post = str_replace('</iframe>', '<exp>', $post);
       // echo $post; die;
        $ar = explode('<exp>', $post);
        
        //unset($ar[0]);

        $c = 0 ;
        $is_content = false;
        $final = array();
        foreach ($ar as $key => $value) {
            if($ar[$key] == '<br></exp>') {
                $array = array();
                $array['type'] = "break";
                $array['content'] = $ar[$key];
                $ar[$key] = $array;
            }
            if(!strlen($ar[$key]) && (empty($ar[$key]['type']) && empty($ar[$key]['content']))) {
                $array = array();
                $array['type'] = "space";
                $array['content'] = $ar[$key];
                $ar[$key] = $array;
            }

            $temp[$key] = explode('>', $value);

            foreach ($temp[$key] as $j => $val) {
                preg_match( '/src="([^"]*)"/i', $temp[$key][$j], $array);
                preg_match( '/type="([^"]*)"/i', $temp[$key][$j], $type);

                if($array&&$type) {
                    $index_array = array();
                    $index_array['type'] = $type[1];
                    $index_array['content'] = $array[1];
                    $ar[$key] = $index_array;
                }
            }
            if(empty($ar[$key]['type']) && empty($ar[$key]['content'])) {
                $array = array();
                $array['type'] = "content";
                $array['content'] = $ar[$key];
                $ar[$key] = $array; 
            }

            /*formatting of content array*/
                if($is_content && $ar[$key]['type'] == 'content') {
                    $j = $c-1;
                    $final[$j]['value'] = $final[$j]['value'] . $ar[$key]['content'];
                    continue;
                } elseif($ar[$key]['type'] == 'image') {
                    $is_content = false;
                    $final[$c]['type'] = 'image';
                    $final[$c]['value'] = $ar[$key]['content']; 
                    $c++;
                } elseif($ar[$key]['type'] == 'video') {
                    $is_content = false;
                    $final[$c]['type'] = 'video';
                    $final[$c]['value'] = $ar[$key]['content'];
                    $c++;
                }
                if($ar[$key]['type'] == 'content') {
                    $is_content = true;
                    $final[$c]['type'] = 'content';
                    $final[$c]['value'] = $ar[$key]['content'];
                    $c++;
                }
            /*formatting of content array*/
        }
        if(!$ar) {
            $post_content = array(array('type'=>'content','value'=>$post_content));
        }
        else {
            $post_content = $final;
        }
        //print_r($post_content);die;
        return $post_content;
    }
    

    function original_activity_title($activity) {
        $bold_start = '<b>';
        $bold_close = '</b>';

        if(($activity['ActivityType']=='Share' || $activity['ActivityType']=='ShareSelf') && ($activity['ShareDetails']['ActivityType']=='PagePost' || $activity['ShareDetails']['ActivityType']=='EventWallPost' || $activity['ShareDetails']['ActivityType']=='GroupPostAdded' || $activity['ShareDetails']['ActivityType']=='ForumPost')) {
            if($activity['ShareDetails']['PostType']!=='7') {
                $post_title1 = '<a href="' . base_url() . '?GUID=' . $activity['ShareDetails']['UserGUID'] . '&ID=' . $activity['ShareDetails']['UserID'] .'&Type=user">' . $bold_start . $activity['ShareDetails']['UserName'] . $bold_close . '</a>' ;

            }
            if($activity['ShareDetails']['ActivityType']=='GroupPostAdded') {
                $post_title2 = '<a href="' . base_url() . '?GUID=' . $activity['ShareDetails']['EntityGUID'] . '&ID=' . $activity['ShareDetails']['EntityID'] . '&Type=group">' . $bold_start . $activity['ShareDetails']['EntityName'] . $bold_close . '</a>';
            }
            elseif($activity['ShareDetails']['ActivityType']=='EventWallPost') {
                $post_title2 ='<a href="' . base_url() . '?GUID=' . $activity['ShareDetails']['EntityGUID'] . '&ID=' . $activity['ShareDetails']['EntityID'] . '&Type=event">' . $bold_start . $activity['ShareDetails']['EntityName'] . $bold_close . '</a>';
            }
            elseif($activity['ShareDetails']['ActivityType']=='PagePost') {
                $post_title2 ='<a href="' . base_url() . '?GUID=' . $activity['ShareDetails']['EntityGUID'] . '&ID=' . $activity['ShareDetails']['EntityID'] . '&Type=page">' . $bold_start . $activity['ShareDetails']['EntityName'] . $bold_close . '</a>';
            }
            elseif($activity['ShareDetails']['ActivityType']=='ForumPost') {
                $post_title2 ='<a href="' . base_url() . '?GUID=' . $activity['ShareDetails']['EntityGUID'] . '&ID=' . $activity['ShareDetails']['EntityID'] . '&Type=forum">' . $bold_start . $activity['ShareDetails']['EntityName'] . $bold_close . '</a>';
            }
        }


        if(($activity['ActivityType']=='Share' || $activity['ActivityType']=='ShareSelf') && ($activity['ShareDetails']['ActivityType']!=='PagePost' || $activity['ShareDetails']['ActivityType']!=='EventWallPost' || $activity['ShareDetails']['ActivityType']!=='GroupPostAdded' || $activity['ShareDetails']['ActivityType']!=='ForumPost')) {
            $post_title1 = '<a href="' . base_url() . '?GUID=' . $activity['ShareDetails']['UserGUID'] . '&ID=' . $activity['ShareDetails']['UserID'] .'&Type=user">' . $bold_start . $activity['ShareDetails']['UserName'] . $bold_close . '</a>' ;

        }

        if(isset($post_title1) && isset($post_title2)) {
            return $post_title1 . ' > ' . $post_title2;

        }
        elseif(isset($post_title1)) {
            return $post_title1;
        }
    }

    function getShareDetails($activity,$activity_type_id,$user_id) {
        $share_details = array();

        $original_activity = $this->activity_model->get_activity_details($activity['ParentActivityID'], $activity_type_id);
        $activity_owner = $this->user_model->getUserName($original_activity['UserID'], $original_activity['PostAsModuleID'], $original_activity['PostAsModuleEntityID']);
        $entity_details = $this->user_model->getUserName($user_id, $original_activity['ModuleID'], $original_activity['ModuleEntityID']);
        $share_details['UserProfileURL']        = $activity_owner['ProfileURL'];
        $share_details['UserName']              = $activity_owner['FirstName'].' '.$activity_owner['LastName'];
        $share_details['UserGUID']              = $activity_owner['ModuleEntityGUID'];
        $share_details['UserProfilePicture']    = $activity_owner['ProfilePicture'];
        $share_details['ModuleID']              = $activity_owner['ModuleID'];
        $share_details['UserID']                = $original_activity['UserID'];
        $share_details['EntityProfileURL']      = $entity_details['ProfileURL'];
        $share_details['EntityName']            = $entity_details['FirstName'].' '.$entity_details['LastName'];
        $share_details['EntityGUID']            = $entity_details['ModuleEntityGUID'];
        $share_details['EntityProfilePicture']  = $entity_details['ProfilePicture'];
        $share_details['EntityModuleID']        = $entity_details['ModuleID'];
        $share_details['EntityID']              = $original_activity['ModuleEntityID'];

        $share_details['PostType']              = $original_activity['PostType'];
        $share_details['Album']                 = $original_activity['Album'];
        $share_details['Files']                 = $original_activity['Files'];
        $share_details['PostTitle']             = $original_activity['PostTitle'];
        $share_details['PostContent']           = $original_activity['PostContent'];

        $share_details['PostContent']           = $this->activity_model->parse_tag($share_details['PostContent']);
        $share_details['PostContent']           = $this->convert_unicode_html_emoji($share_details['PostContent']);
        
        $share_details['ActivityModule']        = $original_activity['SharedActivityModule'];
        $share_details['ActivityGUID']            = $original_activity['ActivityGUID'];
        
        $share_details['ActivityType']          = $original_activity['ActivityType'];
        $share_details['ParentActivityTypeID']  = $original_activity['ParentActivityTypeID'];
        $share_details['Privacy']               = $original_activity['Privacy'];
        $share_details['CreatedDate']           = $original_activity['CreatedDate'];

        $share_details['PollData']              = array();
        $activity['Album']                      = array();
        if ($activity_type_id == '14' || $activity_type_id == '15') {
            $activity['Album'] = $this->activity_model->get_albums($activity['ParentActivityID'], $user_id, '', 'Media');
            if (isset($activity['Album']['AlbumType']) && !empty($activity['Album']['AlbumType'])) {
                $share_details['EntityType'] = ucfirst(strtolower($activity['Album']['AlbumType']));
            } else {
                $share_details['EntityType'] = 'Media';
            }
        } else {
            $share_details['EntityType'] = 'Post';
            if ($original_activity['ParentActivityTypeID'] == 5 || $original_activity['ParentActivityTypeID'] == 6) {
                $share_details['EntityType'] = 'Album';
            }
        }
        
        $share_details['ActivityURL'] = get_single_post_url($original_activity, $activity['ParentActivityID'], $original_activity['ParentActivityTypeID'], $original_activity['ModuleEntityID']);
        
        if($this->IsApp == 1) {
            $share_details['TagedUser'] = json_decode($original_activity['TagedUser']);
            $share_details['PostContent'] = $this->get_description($share_details['PostContent']);
            $activity['ShareDetails'] = $share_details;
            $share_details['Message'] = $this->original_activity_title($activity);
        } else {
            $share_details['PostContent'] = html_entity_decode($share_details['PostContent']);
        }
        
        return $share_details;
    }
}
