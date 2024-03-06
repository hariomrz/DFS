<?php

/**
 * This model is used to subscribe for notification of an activity
 * @package    Subscribe_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Subscribe_model extends Common_Model {

    protected $user_subscribed = array();

    function __construct() {
        parent::__construct();
    }

    /**
     * Function Name: toggle_subscribe
     * @param UserID
     * @param EntityType
     * @param EntityID
     * Description: Subscribe to post
     */
    public function toggle_subscribe($module_entity_id, $entity_type, $entity_id, $module_id = 3) {
        if (empty($module_entity_id) || empty($entity_id)) {
            return false;
        }
        $entity_type = strtoupper($entity_type);
        $activity_cache = array();
        if ($entity_type == 'ALBUM') {
            $entity_type = 'ACTIVITY';
            $this->load->model('album/album_model');
            $entity_id = $this->album_model->get_album_activity_id($entity_id, true);
        }

        if (CACHE_ENABLE && $entity_id && $entity_type == 'ACTIVITY') {
            $activity_cache = $this->cache->get('activity_' . $entity_id);
        }

        $status_id = '2';
        $is_subscribed = '1';
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('EntityID', $entity_id);
        $this->db->limit(1);
        $query = $this->db->get(SUBSCRIBE);
        if ($query->num_rows() == 0) {
            $activity_cache['s'][] = array('ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id);
            $data = array('SubscribeGUID' => get_guid(), 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id, 'EntityType' => $entity_type, 'EntityID' => $entity_id, 'StatusID' => '2', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->insert(SUBSCRIBE, $data);

            // Save score on subscribe
            if ($module_id == 3) { // only applied for user
                $this->load->model(array('log/user_activity_log_score_model'));
                $this->user_activity_log_score_model->log_subscribe_data($entity_type, $entity_id, $module_entity_id, $module_id);
            }
        } else {
            if ($query->row()->StatusID == '2') {
                $status_id = '3';
                $is_subscribed = '0';
            }
            //Add/Remove cache array
            if (!empty($activity_cache) && $query->row()->StatusID == '2') {
                $activity_cache_s = $activity_cache['s'];
                if (!empty($activity_cache_s)) {
                    foreach ($activity_cache_s as $key => $val) {
                        if ($val['ModuleID'] == $module_id && $val['ModuleEntityID'] == $module_entity_id) {
                            unset($activity_cache['s'][$key]);
                        }
                    }
                }
            } else {
                $activity_cache['s'][] = array('ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id);
            }
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->update(SUBSCRIBE, array('StatusID' => $status_id, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
        }

        // Update cache file data
        if (CACHE_ENABLE && $entity_id && $entity_type == 'ACTIVITY') {
            $this->cache->delete('activity_' . $entity_id);
            $this->cache->save('activity_' . $entity_id, $activity_cache, CACHE_EXPIRATION);
        }
        if (CACHE_ENABLE) {
            $this->cache->delete('article_widgets_' . $module_entity_id);
        }
        return $is_subscribed;
    }

    public function subscribe_email($user_id, $entity_id, $type, $send_email = true, $tagged_entity = 0, $comment_id = 0) {
        $this->load->model(array('activity/activity_model', 'group/group_model', 'pages/page_model', 'users/user_model', 'events/event_model', 'timezone/timezone_model', 'Settings_model'));
        if (!$send_email) {
            return false;
        }
        $user_details = $this->user_model->getUserName($user_id, 3, $user_id);
        $user_details['UserID'] = $user_id;
        //print_r($user_details);
        $data = array();
        $EmailTypeID = 37;
        switch ($type) {
            case 'post':
            case 'post_self':
            case 'group_post':
            case 'page_post':
            case 'event_post':
            case 'page_tagged_post':
            case 'page_tagged_comment':

                $activity_details = $this->activity_model->getSingleUserActivity($user_id, $entity_id, 0, true);
                if (isset($activity_details[0])) {
                    $activity_details = $activity_details[0];
                }
                $ent_type = 'USER';
                $ent_id = $activity_details['ModuleEntityID'];
                if ($type == 'post') {
                    $subject = $activity_details['UserName'] . ' posted for ' . $activity_details['EntityName'];
                    $ent_id = $user_id;
                } elseif ($type == 'share') {
                    $subject = $activity_details['UserName'] . ' shared a post on ' . $activity_details['EntityName'] . ' wall';
                    $ent_id = $user_id;
                } elseif ($type == 'post_self') {
                    $subject = $activity_details['UserName'] . ' created a new post';
                } elseif ($type == 'share_self') {
                    $subject = $activity_details['UserName'] . ' shared a new post';
                } elseif ($type == 'group_post' || $type == 'event_post' || $type == 'page_post') {
                    if ($type == 'group_post') {
                        $ent_type = 'GROUP';
                    }
                    if ($type == 'event_post') {
                        $ent_type = 'EVENT';
                    }
                    if ($type == 'page_post') {
                        $ent_type = 'PAGE';
                    }
                    if ($activity_details['ActivityType'] == 'GroupPostAdded' && $activity_details['EntityName'] == '') {
                        $group_entity_name = array();
                        if (!empty($activity_details['EntityMembers'])) {
                            foreach ($activity_details['EntityMembers'] as $item) {
                                $group_entity_name[] = $item['FirstName'];
                            }
                        }
                        if (!empty($group_entity_name)) {
                            $activity_details['EntityName'] = implode(' , ', $group_entity_name);
                        }
                    }
                    $subject = $activity_details['UserName'] . ' posted in ' . $activity_details['EntityName'];
                } elseif ($type == 'page_tagged_post') {
                    $ent_id = $tagged_entity;
                    $ent_type = 'PAGE';
                    $entity_details = get_detail_by_id($tagged_entity, 18, 'Title,PageURL', 2);
                    $subject = $entity_details['Title'] . ' is tagged in a post.';
                } elseif ($type == 'page_tagged_comment') {
                    $ent_id = $tagged_entity;
                    $ent_type = 'PAGE';
                    $entity_details = get_detail_by_id($tagged_entity, 18, 'Title,PageURL', 2);
                    $subject = $entity_details['Title'] . ' is tagged in a comment.';

                    $this->db->select('PC.PostComment');
                    $this->db->from(POSTCOMMENTS . ' PC');
                    //$this->db->join(ACTIVITY.' A','A.ActivityID=PC.EntityID');
                    //this->db->where('PC.EntityType','Activity');
                    $this->db->where('PC.PostCommentID', $comment_id);
                    $this->db->limit(1);
                    $this->db->order_by('PC.CreatedDate', 'DESC');
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        $comment_details = $query->row_array();
                    }
                }
                $data = array('activity_data' => $activity_details, 'Subject' => $subject);
                if (isset($entity_details)) {
                    $data['EntityDetails'] = $entity_details;
                }
                if (isset($comment_details)) {
                    $data['CommentDetails'] = $comment_details;
                }
                $EmailTypeID = 32;
                break;
            case 'join_group':
                $group_data = get_detail_by_id($entity_id, 1, 'GroupID,GroupGUID,GroupName,GroupImage,GroupDescription', 2);
                $data['group_data'] = $group_data;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . ' joined ' . $group_data['GroupName'];
                $data['Subject'] = $subject;
                $ent_type = 'USER';
                $ent_id = $user_id;
                $EmailTypeID = 7;
                break;
            case 'following_page':
                $page_details = $this->page_model->get_page_detail_by_page_id($entity_id);
                $data['page_data'] = $page_details;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . ' is now following ' . $page_details['Title'];
                $data = array('page_data' => $page_details, 'Subject' => $subject);
                $ent_type = 'USER';
                $ent_id = $user_id;
                break;
            case 'following_user':
                $friend_details = $this->user_model->getUserName($entity_id, 3, $entity_id);
                $data['friend_details'] = $friend_details;
                $data['user_details'] = $user_details;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . ' is now following ' . $friend_details['FirstName'] . ' ' . $friend_details['LastName'];
                $ent_type = 'USER';
                $ent_id = $user_id;
                break;
            case 'friend_added':
                $friend_details = $this->user_model->getUserName($entity_id, 3, $entity_id);
                $data['friend_details'] = $friend_details;
                $data['user_details'] = $user_details;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . ' is now friends with ' . $friend_details['FirstName'] . ' ' . $friend_details['LastName'];
                $ent_type = 'USER';
                $ent_id = $user_id;
                $EmailTypeID = 16;
                break;
            case 'event_attending':
                $status = $this->event_model->get_user_presence($user_id, $entity_id);
                $status_text = '';
                if ($status == 'ATTENDING') {
                    $status_text = ' is attending ';
                } else if ($status == 'MAY_BE') {
                    $status_text = ' is maybe attending ';
                }
                $event_data = get_detail_by_id($entity_id, 14, '*', 2);
                $event_data['Location'] = get_location_by_id($event_data['LocationID']);
                $event_data['ProfilePicture'] = $this->event_model->get_event_profile_picture($event_data['ProfileImageID']);
                $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_id, 'Date');
                $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_id, 'Time');
                $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_id, 'Date');
                $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_id, 'Time');
                $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');
                $data['event_data'] = $event_data;
                $data['status_text'] = $status_text;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . $status_text . $data['event_data']['Title'];
                $ent_type = 'USER';
                $ent_id = $user_id;
                $EmailTypeID = 45;
                break;
            case 'create_group':
                $group_data = get_detail_by_id($entity_id, 1, 'GroupID,GroupGUID,GroupName,GroupImage,GroupDescription', 2);
                $data['group_data'] = $group_data;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . ' created a group ' . $data['group_data']['GroupName'];
                $ent_type = 'USER';
                $ent_id = $user_id;
                break;
            case 'create_page':
                $page_details = $this->page_model->get_page_detail_by_page_id($entity_id);
                $data['page_data'] = $page_details;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . ' created a page ' . $page_details['Title'];
                $data = array('page_data' => $page_details, 'Subject' => $subject);
                $ent_type = 'USER';
                $ent_id = $user_id;
                break;
            case 'create_event':
                $event_data = get_detail_by_id($entity_id, 14, '*', 2);
                $event_data['Location'] = get_location_by_id($event_data['LocationID']);
                $event_data['ProfilePicture'] = $this->event_model->get_event_profile_picture($event_data['ProfileImageID']);
                $event_data['StartDate'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_id, 'Date');
                $event_data['StartTime'] = $this->timezone_model->convert_event_date_time($event_data['StartDate'], $event_data['StartTime'], $user_id, 'Time');
                $event_data['EndDate'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_id, 'Date');
                $event_data['EndTime'] = $this->timezone_model->convert_event_date_time($event_data['EndDate'], $event_data['EndTime'], $user_id, 'Time');
                $event_data['ProfileURL'] = $this->event_model->getViewEventUrl($event_data['EventGUID'], $event_data['Title'], false, 'wall');
                $data['event_data'] = $event_data;
                $subject = $user_details['FirstName'] . ' ' . $user_details['LastName'] . ' created an event ' . $data['event_data']['Title'];
                $ent_type = 'USER';
                $ent_id = $user_id;
                break;
            case 'share':

                $activity_data = get_detail_by_id($entity_id, 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                //print_r($activity_data);
                $activity_data['Link'] = get_single_activity_url($entity_id);

                $entity_type = 'post';
                $parent_activity_type_id = get_detail_by_id($activity_data['ParentActivityID'], 0, 'ActivityTypeID', 1);
                if ($parent_activity_type_id == 5 || $parent_activity_type_id == 6 || $parent_activity_type_id == 13) {
                    $entity_type = 'album';
                }

                $parent_activity_data = $this->activity_model->getSingleUserActivity($user_id, $activity_data['ParentActivityID'], 0, true);
                $parent_activity_data = $parent_activity_data[0];

                if (!$parent_activity_data) {
                    return false;
                }

                //$parent_activity_data['ActivityID'] = get_detail_by_guid($parent_activity_data['ActivityGUID']);
                //$parent_activity_data['UserID'] = get_detail_by_guid($parent_activity_data['UserGUID'],3,'UserID',1);
                if ($activity_data['IsMediaExist'] == 1) {
                    $media_data = $this->activity_model->get_albums($parent_activity_data['ActivityID'], $parent_activity_data['UserID']);
                    $media_data = $media_data[0]['Media'];
                } else {
                    $media_data = array();
                }

                $shared_on_user = $activity_data['ModuleEntityID'];
                $share_user_detail = get_detail_by_id($shared_on_user, 3, 'FirstName,LastName', 2);
                if ($user_id == $shared_on_user) {
                    $gender = $this->notification_model->get_gender($user_id);
                } else {
                    $gender = $share_user_detail['FirstName'] . ' ' . $share_user_detail['LastName'] . "'s";
                }

                // $a_data                 = $this->activity_model->getSingleUserActivity($user_details['UserID'],$activity_data['ParentActivityID'],0,true);
                if (isset($parent_activity_data['Album'])) {
                    $activity_data['Album'] = $parent_activity_data['Album'];
                }
                $data = array('activity_data' => $activity_data, 'media_data' => $media_data, 'parent_activity' => $parent_activity_data, 'from_gender' => $gender, 'entity_type' => $entity_type, 'share_user_detail' => $share_user_detail);

                $subject = $user_details['FirstName'] . " " . $user_details['LastName'] . " posted for " . $share_user_detail['FirstName'] . ' ' . $share_user_detail['LastName'];
                $ent_type = 'USER';
                $ent_id = $user_id;
                $EmailTypeID = 32;
                break;
            case 'share_self':

                $activity_data = get_detail_by_id($entity_id, 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                //print_r($activity_data);
                $activity_data['Link'] = get_single_activity_url($entity_id);

                $entity_type = 'post';
                $parent_activity_type_id = get_detail_by_id($activity_data['ParentActivityID'], 0, 'ActivityTypeID', 1);
                if ($parent_activity_type_id == 5 || $parent_activity_type_id == 6 || $parent_activity_type_id == 13) {
                    $entity_type = 'album';
                }

                $parent_activity_data = $this->activity_model->getSingleUserActivity($user_id, $activity_data['ParentActivityID'], 0, true);
                $parent_activity_data = $parent_activity_data[0];

                if (!$parent_activity_data) {
                    return false;
                }

                if ($activity_data['IsMediaExist'] == 1) {
                    $media_data = $this->activity_model->get_albums($parent_activity_data['ActivityID'], $parent_activity_data['UserID']);
                    if (isset($media_data[0]['Media'])) {
                        $media_data = $media_data[0]['Media'];
                    }
                } else {
                    $media_data = array();
                }

                $shared_on_user = $parent_activity_data['ModuleEntityID'];
                if ($user_id == $shared_on_user) {
                    $gender = $this->notification_model->get_gender($user_id);
                } else {
                    if ($parent_activity_data['ModuleID'] == '1') {
                        $share_user_detail = get_detail_by_id($shared_on_user, 1, 'GroupName', 2);
                        $gender = $share_user_detail['GroupName'] . "'s";
                    }
                    if ($parent_activity_data['ModuleID'] == '34') {
                        $share_user_detail = get_detail_by_id($shared_on_user, 34, 'Name', 2);
                        $gender = $share_user_detail['Name'] . "'s";
                    } else if ($parent_activity_data['ModuleID'] == '3') {
                        $share_user_detail = get_detail_by_id($shared_on_user, 3, 'FirstName,LastName', 2);
                        $gender = $share_user_detail['FirstName'] . ' ' . $share_user_detail['LastName'] . "'s";
                    } else if ($parent_activity_data['ModuleID'] == '14') {
                        $share_user_detail = get_detail_by_id($shared_on_user, 14, 'Title', 2);
                        $gender = $share_user_detail['Title'] . "'s";
                    } else if ($parent_activity_data['ModuleID'] == '18') {
                        $share_user_detail = get_detail_by_id($shared_on_user, 18, 'Title', 2);
                        $gender = $share_user_detail['Title'] . "'s";
                    }
                }

                // $a_data                 = $this->activity_model->getSingleUserActivity($user_details['UserID'],$activity_data['ParentActivityID'],0,true);
                if (isset($parent_activity_data['Album'])) {
                    $activity_data['Album'] = $parent_activity_data['Album'];
                }
                $data = array('activity_data' => $activity_data, 'media_data' => $media_data, 'parent_activity' => $parent_activity_data, 'from_gender' => $gender, 'entity_type' => $entity_type);

                $subject = $user_details['FirstName'] . " " . $user_details['LastName'] . " shared a new post";
                $ent_type = 'USER';
                $ent_id = $user_id;
                $EmailTypeID = 32;
                break;
        }

        $entity_type = 'USER';
        if ($type == 'post' || $type == 'post_self' || $type == 'share' || $type == 'share_self' || $type == 'group_post' || $type == 'event_post' || $type == 'page_post' || $type == 'page_tagged_post' || $type == 'page_tagged_comment') {
            
        } else if ($type == 'join_group') {
            $entity_type = 'GROUP';
        } else if ($type == 'event_attending') {
            $entity_type = 'EVENT';
        } else if ($type == 'following_page') {
            $entity_type = 'PAGE';
        }

        $email_data = array();
        $email_data['IsResend'] = 0;
        $email_data['TemplateName'] = "emailer/s_" . $type;
        $email_data['Subject'] = $subject;
        $email_data['EmailTypeID'] = $EmailTypeID;

        if ($type == 'create_page') {
            $entity_type = 'PAGE';
        } elseif ($type == 'create_event') {
            $entity_type = 'EVENT';
        } elseif ($type == 'create_group') {
            $entity_type = 'GROUP';
        } elseif ($type == 'post' || $type == 'post_self' || $type == 'share' || $type == 'share_self' || $type == 'group_post' || $type == 'page_post' || $type == 'event_post' || $type == 'page_tagged_post' || $type == 'page_tagged_comment') {
            $entity_type = 'ACTIVITY';
        }

        $subscriber_list = $this->get_subscriber_list($ent_type, $ent_id);

        if ($type == 'page_post') {
            /* $subscriber_list = $this->get_subscriber_list('USER',$user_id);
              $entity_subscriber_list = $this->get_subscriber_list('PAGE',$activity_details['ModuleEntityID']); */
            if ($activity_details['ModuleID'] == 18) {
                $entity_subscriber_list = $this->get_subscriber_list('PAGE', $activity_details['ModuleEntityID']);
            } else {
                $subscriber_list = $this->get_subscriber_list('USER', $user_id);
            }
        }
        if ($type == 'event_post') {
            $subscriber_list = $this->get_subscriber_list('USER', $user_id);
            $entity_subscriber_list = $this->get_subscriber_list('EVENT', $activity_details['ModuleEntityID']);
        }
        if ($type == 'group_post') {
            $subscriber_list = $this->get_subscriber_list('USER', $user_id);
            $entity_subscriber_list = $this->get_subscriber_list('GROUP', $activity_details['ModuleEntityID']);
        }

        if (isset($entity_subscriber_list) && !empty($entity_subscriber_list)) {
            foreach ($entity_subscriber_list as $key => $val) {
                if (in_array($val, $subscriber_list)) {
                    unset($entity_subscriber_list[$key]);
                } else {
                    if ($user_id == $val) {
                        continue;
                    }
                    $send_email = false;
                    $subscribe_user_details = get_detail_by_id($val, 3, 'FirstName,LastName,Email', 2);
                    $data['user_details'] = $user_details;

                    $data['u_details'] = $this->user_model->getUserName($val, 3, $val);
                    $data['u_details']['Email'] = $subscribe_user_details['Email'];
                    $email_data['Email'] = $subscribe_user_details['Email'];                    
                    $email_data['UserID'] = $val;
                    $email_data['Data'] = $data;

                    //get guid by entityID
                    if (($type == 'post' || $type == 'group_post' || $type == 'page_post' || $type == 'event_post') && !$this->settings_model->isDisabled('32')) {
                        $entityGUID = get_guid_by_id($entity_id);
                        $email_data['EntityGUID'] = $entityGUID; //send entity id with mail in reply_to header
                    }

                    if ($entity_type == 'GROUP') {
                        if ($this->group_model->has_access($val, $entity_id)) {
                            $send_email = true;
                        }
                    } else if ($entity_type == 'EVENT') {
                        if ($this->event_model->has_access($val, $entity_id)) {
                            $send_email = true;
                        }
                    } else if ($entity_type == 'PAGE') {
                        $send_email = true;
                    } else if ($entity_type == 'ACTIVITY') {
                        if ($this->activity_model->has_access($val, $entity_id)) {
                            $send_email = true;
                        }
                    }

                    if ($send_email) {
                        $send_email = $this->send_notification($user_id, $val, $type, $entity_type, $entity_id, $ent_type, $ent_id);
                    }

                    //$send_email = true;
                    if ($send_email) {
                        sendEmailAndSave($email_data, 1);
                    }
                }
            }
        }

        if ($type == 'friend_added') {
            $friends_subscriber_list = $this->get_subscriber_list('USER', $entity_id);
            $subscriber_list = array_unique(array_merge($subscriber_list, $friends_subscriber_list));
        }

        if ($subscriber_list) {
            foreach ($subscriber_list as $subscriber) {
                if ($user_id == $subscriber) {
                    continue;
                }
                $send_email = false;
                $subscribe_user_details = get_detail_by_id($subscriber, 3, 'FirstName,LastName,Email', 2);
                $data['user_details'] = $user_details;

                $data['u_details'] = $this->user_model->getUserName($subscriber, 3, $subscriber);
                $data['u_details']['Email'] = $subscribe_user_details['Email'];
                $email_data['Email'] = $subscribe_user_details['Email'];
                //$email_data['Email']    = 'mohitb@vinfotech.com';
                $email_data['UserID'] = $subscriber;
                $email_data['Data'] = $data;

                //get guid by entityID
                if (($type == 'post' || $type == 'group_post' || $type == 'page_post' || $type == 'event_post') && !$this->settings_model->isDisabled('32')) {
                    $entityGUID = get_guid_by_id($entity_id);
                    $email_data['EntityGUID'] = $entityGUID; //send entity id with mail in reply_to header
                }

                if ($entity_type == 'GROUP') {
                    if ($this->group_model->has_access($subscriber, $entity_id)) {
                        $send_email = true;
                    }
                } else if ($entity_type == 'EVENT') {
                    if ($this->event_model->has_access($subscriber, $entity_id)) {
                        $send_email = true;
                    }
                } else if ($entity_type == 'PAGE') {
                    $send_email = true;
                } else if ($entity_type == 'ACTIVITY') {
                    if ($this->activity_model->has_access($subscriber, $entity_id)) {
                        $send_email = true;
                    }
                }

                if ($send_email) {
                    $send_email = $this->send_notification($user_id, $subscriber, $type, $entity_type, $entity_id, $ent_type, $ent_id);
                }

                //$send_email = true;
                if ($send_email) {
                    sendEmailAndSave($email_data, 1);
                }
            }
        }
    }

    public function send_notification($user_id, $subscriber_id, $type, $entity_type, $entity_id, $ent_type, $ent_id) {
        $this->load->model(array('activity/activity_model', 'group/group_model', 'pages/page_model', 'users/user_model', 'events/event_model', 'timezone/timezone_model'));
        $status = true;

        switch ($type) {
            case 'post':
                $activity_details = get_detail_by_id($entity_id, 0, 'ModuleID,ModuleEntityID', 2);
                if ($activity_details['ModuleID'] == '3' && $subscriber_id == $activity_details['ModuleEntityID']) {
                    $status = false;
                }
                break;
            case 'post_self':
                #done
                break;
            case 'group_post':
                if ($this->group_model->is_admin($subscriber_id, $ent_id)) {
                    $status = false;
                }
                break;
            case 'page_post':
                if ($this->page_model->is_admin($subscriber_id, $ent_id)) {
                    $status = false;
                }
                break;
            case 'event_post':
                if ($this->event_model->is_admin($ent_id, $subscriber_id)) {
                    $status = false;
                }
                break;
            case 'join_group':
                if ($this->group_model->is_admin($subscriber_id, $entity_id)) {
                    $status = false;
                }
                break;
            case 'following_page':
                if ($this->page_model->is_admin($subscriber_id, $entity_id)) {
                    $status = false;
                }
                break;
            case 'following_user':
                #done
                break;
            case 'friend_added':
                #done
                break;
            case 'event_attending':
                if ($this->event_model->is_admin($subscriber_id, $entity_id)) {
                    $status = false;
                }
                break;
            case 'create_group':
                #done
                break;
            case 'create_page':
                #done
                break;
            case 'create_event':
                #done
                break;
            case 'share':
                $shared_activity_details = get_detail_by_id($entity_id, 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                $original_activity = $this->activity_model->getOriginalActivity($entity_id);
                $activity_details = get_detail_by_id($original_activity, 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                if ($activity_details['UserID'] == $subscriber_id) {
                    $status = false;
                }
                if ($shared_activity_details['ModuleID'] == '3' && $shared_activity_details['ModuleEntityID'] == $subscriber_id) {
                    $status = false;
                }
                break;
            case 'share_self':
                $original_activity = $this->activity_model->getOriginalActivity($entity_id);
                $activity_details = get_detail_by_id($original_activity, 0, 'PostContent,NoOfLikes,ModuleID,UserID,IsMediaExist,ActivityID,ModuleEntityID,ParentActivityID,Params,NoOfComments,PostAsModuleID,PostAsModuleEntityID', 2);
                if ($activity_details['UserID'] == $subscriber_id) {
                    $status = false;
                }
                break;
        }
        return $status;
    }

    public function get_subscriber_list($entity_type, $entity_id) {
        $this->load->model('group/group_model');
        $data = array();
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(SUBSCRIBE);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('EntityID', $entity_id);
        $this->db->where('StatusID', '2');
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $subscriber) {
                if ($subscriber['ModuleID'] == 3) {
                    $data[] = $subscriber['ModuleEntityID'];
                } else if ($subscriber['ModuleID'] == 1) {
                    $data = $this->group_model->get_group_members_id_recursive($entity_id, $data);
                }
            }
        }
        $data = array_unique($data);
        return $data;
    }

    /**
     * [update_subscription Update subscription value]
     * @param  [type] $user_id     [User ID]
     * @param  [type] $entity_type [Entity Type]
     * @param  [type] $entity_id   [Entity ID]
     * @param  [type] $status_id   [Status ID]
     * @return [type]              [description]
     */
    public function update_subscription($module_entity_id, $entity_type, $entity_id, $status_id, $module_id = 3) {
        $entity_type = strtoupper($entity_type);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('EntityID', $entity_id);
        $this->db->limit(1);
        $query = $this->db->get(SUBSCRIBE);
        if ($query->num_rows() == 0 && $status_id == 2) {
            $data = array('SubscribeGUID' => get_guid(), 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id, 'EntityType' => $entity_type, 'EntityID' => $entity_id, 'StatusID' => '2', 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->insert(SUBSCRIBE, $data);
        }

        if ($query->num_rows() > 0) {
            $is_subscribed = $query->row()->StatusID;
            if ($is_subscribed != $status_id) {
                $this->db->where('ModuleEntityID', $module_entity_id);
                $this->db->where('ModuleID', $module_id);
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $entity_id);

                $this->db->update(SUBSCRIBE, array('StatusID' => $status_id, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
            }
        }
    }

    /**
     * Function Name: is_subscribed
     * @param user_id
     * @param entity_type
     * @param entity_id
     * Description: get subscribe user list to post
     */
    public function is_subscribed($module_entity_id, $entity_type, $entity_id, $module_id = 3) {
        $activity_cache = array();
        $result = 0;
        if (CACHE_ENABLE && $entity_id && $entity_type == 'Activity') {
            $activity_cache = $this->cache->get('activity_' . $entity_id);
            if (!empty($activity_cache)) {
                $subscribe = isset($activity_cache['s']) ? $activity_cache['s'] : '';
                if (!empty($subscribe)) {
                    foreach ($subscribe as $subscribe_item) {
                        if ($module_id == $subscribe_item['ModuleID'] && $module_entity_id == $subscribe_item['ModuleEntityID']) {
                            $result = 1;
                        }
                    }
                    return $result;
                }
            }
        }
        if (empty($activity_cache)) {
            $entity_type = strtoupper($entity_type);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where("ModuleEntityID > 0", NULL, FALSE);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('EntityID', $entity_id);
            $this->db->where('StatusID', '2');
            $this->db->limit(1);
            $query = $this->db->get(SUBSCRIBE);
            if ($query->num_rows()) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    /**
     * Function Name: set_user_subscribed
     * @param $module_entity_id
     * Description: set subscriber entities for given user
     */
    public function set_user_subscribed($module_entity_id) {
        $entity_type = strtoupper('Activity');
        $this->db->select('GROUP_CONCAT(EntityID) as EntityIDs ');
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('ModuleID', 3);
        $this->db->where('EntityType', $entity_type);
        $this->db->where('StatusID', '2');
        $query = $this->db->get(SUBSCRIBE);
        if ($query->num_rows() > 0) {
            $row_ids = $query->row_array();
            if (!empty($row_ids['EntityIDs'])) {
                $this->user_subscribed = explode(',', $row_ids['EntityIDs']);
            }
            /* foreach ($query->result_array() as $result)
              {
              $this->user_subscribed[] = $result['EntityID'];
              } */
        }
    }

    public function get_user_subscribed() {
        return $this->user_subscribed;
    }

    /**
     * Function Name: addUpdate
     * Description: add / update user in subscribe list
     */
    public function addUpdate($users, $activity_id, $is_activity = 1, $entity_type = 'ACTIVITY') {
        if ($users) {
            $array = array();
            foreach ($users as $key => $val) {
                if ($is_activity == 1) {
                    $arr = array('SubscribeGUID' => get_guid(), 'ModuleID' => $val['ModuleID'], 'ModuleEntityID' => $val['ModuleEntityID'], 'EntityType' => $entity_type, 'EntityID' => $activity_id, 'StatusID' => '2', 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                    $array[] = $arr;
                } else {
                    $this->db->where('ModuleID', $val['ModuleID']);
                    $this->db->where('ModuleEntityID', $val['ModuleEntityID']);
                    $this->db->where('EntityType', $entity_type);
                    $this->db->where('EntityID', $activity_id);
                    $this->db->where('StatusID', '2');
                    $this->db->limit(1);
                    $query = $this->db->get(SUBSCRIBE);
                    if ($query->num_rows()) {
                        $row = $query->row();
                        if ($row->MaxNotifications != '-1') {
                            $this->db->set('MaxNotifications', '5');
                            $this->db->where('EntityType', $entity_type);
                            $this->db->where('EntityID', $activity_id);
                            $this->db->where('ModuleID', $val['ModuleID']);
                            $this->db->where('ModuleEntityID', $val['ModuleEntityID']);
                            $this->db->update(SUBSCRIBE);
                        }
                    } else {
                        $arr = array('SubscribeGUID' => get_guid(), 'ModuleID' => $val['ModuleID'], 'ModuleEntityID' => $val['ModuleEntityID'], 'EntityType' => $entity_type, 'EntityID' => $activity_id, 'StatusID' => '2', 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'MaxNotifications' => '5');
                        $array[] = $arr;
                    }
                }
            }
            if ($array) {
                $this->db->insert_batch(SUBSCRIBE, $array);
            }
        }
    }
}
?>
