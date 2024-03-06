<?php

/**
 * This model is used for getting and storing Friend related information
 * @package    Friend_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Friend_model extends Common_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model(array('notification_model', 'activity/activityrule_model'));
    }

    /**
     * @Summary: Request to friend
     * @create_date: Monday, July 14, 2014
     * @last_update_date:
     * @access: public
     * @param:	friend_id, status
     * @return:
     */
    function sendFriendRequest($user_id, $FriendID, $status = 0) {
        if (!$this->settings_model->isDisabled(10)) {
            if ($status == '1') { //adding friend directly, gets called if the user comes from an invite link or signups via an invite link
                if ($this->checkFriendStatus($user_id, $FriendID) != '1' && $user_id != $FriendID) {

                    $this->db->where("(UserID='" . $user_id . "' AND FriendID='" . $FriendID . "') OR (UserID='" . $FriendID . "' AND FriendID='" . $user_id . "')", NULL, FALSE);
                    $this->db->delete(FRIENDS);

                    $f1['UserID'] = $user_id;
                    $f1['FriendID'] = $FriendID;
                    $f1['RequestedBy'] = $user_id;
                    $f1['Status'] = 1;
                    $f1['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $f1['AcceptedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                    $f2['UserID'] = $FriendID;
                    $f2['FriendID'] = $user_id;
                    $f2['RequestedBy'] = $user_id;
                    $f2['Status'] = 1;
                    $f2['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $f2['AcceptedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                    $this->db->insert(FRIENDS, $f1);
                    $this->db->insert(FRIENDS, $f2);

                    //Send Notification
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $this->notification_model->add_notification(17, $user_id, array($FriendID), $FriendID, $parameters);

                    $activity['UserID'] = $user_id;
                    $activity['EntityID'] = $FriendID;
                    $activity['EntityType'] = 'User';

                    $this->activity_model->addActivity(3, $FriendID, 2, $user_id);

                    if (CACHE_ENABLE) {
                        $this->cache->delete('user_friends_' . $user_id);
                        $this->cache->delete('user_friends_' . $FriendID);
                    }
                }
            } else {
                //Accept Friend 
                $sql = $this->db->get_where(FRIENDS, array('UserID' => $user_id, 'FriendID' => $FriendID));
                if ($sql->num_rows()) {
                    $result = $sql->row();
                    if ($result->Status == '1') {
                        return 1;
                    }
                    if ($result->RequestedBy == $user_id) {
                        return 2;
                    } else {
                        $this->db->where('UserID', $user_id)
                                ->where('FriendID', $FriendID)
                                ->update(FRIENDS, array('Status' => '1', 'AcceptedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));

                        $this->db->where('UserID', $FriendID)
                                ->where('FriendID', $user_id)
                                ->update(FRIENDS, array('Status' => '1', 'AcceptedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));

                        $activity['UserID'] = $user_id;
                        $activity['EntityID'] = $FriendID;
                        $activity['EntityType'] = 'User';

                        $this->activity_model->addActivity(3, $FriendID, 2, $user_id);

                        //Send Notification
                        $parameters[0]['ReferenceID'] = $user_id;
                        $parameters[0]['Type'] = 'User';
                        $this->notification_model->add_notification(17, $user_id, array($FriendID), $FriendID, $parameters);


                        $this->db->set('StatusID', '17');
                        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));

                        $this->db->where('RefrenceID', $FriendID);
                        $this->db->where('ToUserID', $user_id);
                        $this->db->where('NotificationTypeID', 16);
                        $this->db->where_in('StatusID', array(15, 16));
                        $this->db->update(NOTIFICATIONS);

                        /* Auto follow */
                        $this->load->model('follow/follow_model');
                        $data = array('TypeEntityID' => $FriendID, 'UserID' => $user_id, 'Type' => 'user');
                        $this->follow_model->auto_follow($data);

                        $data = array('UserID' => $FriendID, 'TypeEntityID' => $user_id, 'Type' => 'user');
                        $this->follow_model->auto_follow($data);
                        /* End Auto follow */

                        if (CACHE_ENABLE) {
                            $this->cache->delete('user_friends_' . $user_id);
                            $this->cache->delete('user_followers_' . $user_id);

                            $this->cache->delete('user_friends_' . $FriendID);
                            $this->cache->delete('user_followers_' . $FriendID);
                        }

                        return 3;
                    }
                } else {
                    //Once denied now adding as friend				
                    $newSql = $this->db->get_where(FRIENDS, array('FriendID' => $user_id, 'UserID' => $FriendID));
                    if ($newSql->num_rows() > 0) {
                        $this->db->where('FriendID', $user_id)
                                ->where('UserID', $FriendID)
                                ->update(FRIENDS, array('Status' => '1', 'AcceptedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));

                        $f1['UserID'] = $user_id;
                        $f1['FriendID'] = $FriendID;
                        $f1['RequestedBy'] = $user_id;
                        $f1['Status'] = 1;
                        $f1['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                        $f1['AcceptedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                        $this->db->insert(FRIENDS, $f1);

                        $activity['UserID'] = $user_id;
                        $activity['EntityID'] = $FriendID;
                        $activity['EntityType'] = 'User';

                        $this->activity_model->addActivity(3, $FriendID, 2, $user_id);

                        //Send Notification
                        $parameters[0]['ReferenceID'] = $user_id;
                        $parameters[0]['Type'] = 'User';
                        $this->notification_model->add_notification(17, $user_id, array($FriendID), $FriendID, $parameters);


                        $this->db->set('StatusID', '17');
                        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));

                        $this->db->where('RefrenceID', $FriendID);
                        $this->db->where('ToUserID', $user_id);
                        $this->db->where('NotificationTypeID', 16);
                        $this->db->where_in('StatusID', array(15, 16));
                        $this->db->update(NOTIFICATIONS);

                        /* Auto follow */
                        $this->load->model('follow/follow_model');
                        $data = array('TypeEntityID' => $FriendID, 'UserID' => $user_id, 'Type' => 'user');
                        $this->follow_model->auto_follow($data);

                        $data = array('UserID' => $FriendID, 'TypeEntityID' => $user_id, 'Type' => 'user');
                        $this->follow_model->auto_follow($data);
                        /* End Auto follow */

                        if (CACHE_ENABLE) {
                            $this->cache->delete('user_friends_' . $user_id);
                            $this->cache->delete('user_followers_' . $user_id);

                            $this->cache->delete('user_friends_' . $FriendID);
                            $this->cache->delete('user_followers_' . $FriendID);
                        }

                        return 5;
                    } else {
                        //Send Friend, 
                        $f1['UserID'] = $user_id;
                        $f1['FriendID'] = $FriendID;
                        $f1['RequestedBy'] = $user_id;
                        $f1['Status'] = 0;
                        $f1['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                        $f1['AcceptedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                        $f2['UserID'] = $FriendID;
                        $f2['FriendID'] = $user_id;
                        $f2['RequestedBy'] = $user_id;
                        $f2['Status'] = 0;
                        $f2['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                        $f2['AcceptedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                        $this->db->insert(FRIENDS, $f1);
                        $this->db->insert(FRIENDS, $f2);

                        //Send Notification
                        $parameters[0]['ReferenceID'] = $user_id;
                        $parameters[0]['Type'] = 'User';
                        $this->notification_model->add_notification(16, $user_id, array($FriendID), $user_id, $parameters);
                        return 4;
                    }
                }
            }
        }
    }

    /**
     * [addFriendByToken This used to add friend by request TOKEN]
     * @param [sting] $token  [Friend request token]
     * @param [int] $user_id [user id]
     */
    function addFriendByToken($token, $user_id = '') {
        if (!$user_id) {
            $user_id = $this->session->userdata('UserID');
        }
        $tqry = $this->db->get_where(INVITATION, array('Token' => $token));
        if ($tqry->num_rows()) {
            $FriendUserID = $tqry->row()->UserID;
            $this->sendFriendRequest($user_id, $FriendUserID, 1);
        }
    }

    /**
     * [addFriendByLSK This used to add friend by Login Session Key]
     * @param [sting] $LoginSessionKey [Login Session Key]
     * @param [int] $FriendID        [Frined Id]
     */
    function addFriendByLSK($LoginSessionKey, $FriendID) {
        $UserData = $this->login_model->active_login_auth(array('LoginSessionKey' => $LoginSessionKey));
        if ($UserData['Data']['UserID'] != $FriendID) {
            $this->sendFriendRequest($UserData['Data']['UserID'], $FriendID);
        }
    }

    /**
     * [checkFriendStatus This is used to check the status of Friend Request]
     * @param  [int] $user_id   [User Id]
     * @param  [int] $FriendID [Friend Id]
     * @return [int]           [return Friend Request Status]
     */
    function checkFriendStatus($user_id, $FriendID) {
        $sql = $this->db->get_where(FRIENDS, array('UserID' => $user_id, 'FriendID' => $FriendID));
        if ($sql->num_rows()) {
            $data = $sql->row();
            if ($data->Status == '1') {
                return 1; // Already Friend
            } elseif ($data->RequestedBy == $user_id) {
                return 2; // Pending Request
            } else {
                return 3; // Accept Friend Request
            }
        } else {
            return 4; // Not yet friend or sent request
        }
    }

    /**
     * [get_connect_user This is used to get all connected friends data like(friend request,pending request)]
     * @param  [int] $user_id   [User Id]
     */
    function get_connect_user($user_id) {
        $this->db->select('FriendID,RequestedBy,Status');
        $sql = $this->db->get_where(FRIENDS, array('UserID' => $user_id));
        $friend_array = array();
        if ($sql->num_rows()) {
            foreach ($sql->result_array() as $item) {
                $friend_array[$item['FriendID']] = $item;
            }
        }
        return $friend_array;
    }

    function get_total_users_count($search_key, $user_id, $type, $UID, $module_id = 0, $module_entity_id = 0) {
        $this->load->model('activity/activity_model');
        if ($module_id && $module_entity_id) {
            $blockedUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
        } else {
            $blockedUsers = array();
        }
        $row = array();
        if ($search_key) {
            $searck = " and (u.FirstName like '" . $this->db->escape_like_str($search_key) . "%' or u.LastName like '" . $this->db->escape_like_str($search_key) . "%' or concat(u.FirstName,' ',u.LastName) like '" . $this->db->escape_like_str($search_key) . "%') GROUP BY u.UserID ";
        } else {
            $searck = ' GROUP BY u.UserID ';
        }
        $UsrID = $user_id;

        if ($type == 'Friends') {
            $frnd1 = " left join " . FRIENDS . " on " . FRIENDS . ".FriendID=ud.UserID ";
            $frnd2 = " and " . FRIENDS . ".Status=1 and " . FRIENDS . ".UserID=" . $UID . " and m.IsActive='1' and m.ModuleID='10' ";
            $UsrID = $UID;
        } elseif ($type == 'Request') {
            $frnd1 = " left join " . FRIENDS . " on " . FRIENDS . ".FriendID=ud.UserID ";
            $frnd2 = " and " . FRIENDS . ".Status=0 and " . FRIENDS . ".UserID=" . $user_id . " and " . FRIENDS . ".RequestedBy!='" . $user_id . "' and m.IsActive='1' and m.ModuleID='10' ";
        } elseif ($type == 'Followers') {
            $frnd1 = " left join " . FOLLOW . " on " . FOLLOW . ".UserID=ud.UserID ";
            $frnd2 = " and " . FOLLOW . ".Type='User' and " . FOLLOW . ".TypeEntityID=" . $UID . " and m.IsActive='1' and m.ModuleID='11' ";
            $UsrID = $UID;
        } elseif ($type == 'Following') {
            $frnd1 = " left join " . FOLLOW . " on " . FOLLOW . ".TypeEntityID=ud.UserID ";
            $frnd2 = " and " . FOLLOW . ".Type='User' and " . FOLLOW . ".UserID=" . $UID . " and m.IsActive='1' and m.ModuleID='11' ";
            $UsrID = $UID;
        } else {
            $frnd1 = '';
            $frnd2 = '';
        }
        $cond = '';
        if ($blockedUsers) {
            $cond .= ' u.UserID NOT IN(' . implode(',', $blockedUsers) . ') AND ';
        }

        $sql = $this->db->query("SELECT m.IsActive,u.FirstName,u.ProfilePicture,u.UserID,u.LastName 
		from Modules m, " . USERS . " u left join " . USERDETAILS . " ud on ud.UserID = u.UserID " . $frnd1 . " where " . $cond . " u.StatusID = 2 AND u.UserID != '" . $UsrID . "' AND ud.LocalityID = '" . $this->LocalityID . "' AND ud.UserID!='" . $UsrID . "' " . $frnd2 . " " . $searck);
        return $sql->num_rows();
    }

    /**
     * [deleteFriend Used to delete Friend]
     * @param  [type] $user_id   [User Id]
     * @param  [type] $friend_id [Friend Id]
     */
    function deleteFriend($user_id, $friend_id) {

        $this->db->where("(UserID='" . $user_id . "' AND FriendID='" . $friend_id . "') OR (FriendID='" . $user_id . "' AND UserID='" . $friend_id . "')", NULL, FALSE);
        $this->db->delete(FRIENDS);

        $this->db->set('StatusID', '3');
        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->where('ActivityTypeID', '2');
        $this->db->where("((UserID='" . $user_id . "' AND ModuleEntityID='" . $friend_id . "' AND ModuleID='3') OR (UserID='" . $friend_id . "' AND ModuleEntityID='" . $user_id . "' AND ModuleID='3'))", NULL, FALSE);
        $this->db->update(ACTIVITY);

        // Remove Notification if User Cancel Friendship
        //$this->db->where('NotificationTypeID','16');
        $this->db->where_in('NotificationTypeID', array(16, 17));
        $this->db->where('((UserID="' . $user_id . '" AND ToUserID="' . $friend_id . '") OR (ToUserID="' . $user_id . '" AND UserID="' . $friend_id . '"))', NULL, FALSE);
        /* $this->db->where('ToUserID',$FriendID);
          $this->db->where('UserID',$user_id); */
        $this->db->delete(NOTIFICATIONS);

        if (!$this->settings_model->isDisabled(28)) {
            $this->load->model(array('reminder/reminder_model'));
            $this->reminder_model->delete_all($user_id, 3, $friend_id);
            $this->reminder_model->delete_all($friend_id, 3, $user_id);
        }

        /* Auto un follow */
        $this->load->model('follow/follow_model');
        $data = array('TypeEntityID' => $friend_id, 'UserID' => $user_id, 'Type' => 'user');
        $this->follow_model->auto_unfollow($data);

        $data = array('UserID' => $friend_id, 'TypeEntityID' => $user_id, 'Type' => 'user');
        $this->follow_model->auto_unfollow($data);
        /* End Auto un  follow */

        if (CACHE_ENABLE) {
            $this->cache->delete('user_friends_' . $user_id);
            $this->cache->delete('user_followers_' . $user_id);

            $this->cache->delete('user_friends_' . $friend_id);
            $this->cache->delete('user_followers_' . $friend_id);
        }
    }

    /**
     * [denyFriend Used to deny Friend Request]
     * @param  [type] $user_id   [User Id]
     * @param  [type] $FriendID [Friend Id]
     */
    function denyFriend($user_id, $FriendID) {
        $this->db->where('UserID', $user_id);
        $this->db->where('FriendID', $FriendID);
        $this->db->delete(FRIENDS);

        $this->db->set('StatusID', '3');
        $this->db->where('ActivityTypeID', '2');
        $this->db->where("(UserID='" . $user_id . "' AND ModuleEntityID='" . $FriendID . "' AND ModuleID='3') OR (UserID='" . $FriendID . "' AND ModuleEntityID='" . $user_id . "' AND ModuleID='3')", NULL, FALSE);
        $this->db->update(ACTIVITY);

        // Remove Notification if User Cancel Friend Request
        $this->db->where('NotificationTypeID', '16');
        $this->db->where('ToUserID', $FriendID);
        $this->db->where('UserID', $user_id);
        $this->db->delete(NOTIFICATIONS);
    }

    /**
     * [get_user_list Used to get USER list based on requested parameter]
     * @param  [string] $search_key     [Search String]
     * @param  [int]  	$user_id        [User Id]
     * @param  [int] 	$showFriend    [Flag to Show only friend or all user]
     * @param  [array]  $selectedUsers [description]
     * @return [type]                 [Array of User details]
     */
    public function get_user_list($search_key, $user_id, $showFriend = 0, $selectedUsers = array(), $Limit = 100, $Offset = 1, $AdvanceSearch = array(), $CountOnly = 0, $sort_by = '', $search_type = "") {
        if (!empty($search_type)) {
            $type = $search_type;
        } else {
            $type = 'User';
            if ($showFriend == 1) {
                $type = 'Friends';
            }
        }

        $Data = $this->get_all_user($search_key, $user_id, $type, $Offset, $Limit, '', $user_id, 0, 0, $AdvanceSearch, $CountOnly, $sort_by);
        if ($CountOnly) {
            return $Data;
        }
        $UserData = array();
        if (isset($Data['Members']) && !empty($Data['Members'])) {
            $i = 1;
            foreach ($Data['Members'] as $key => $user) {
                $alreadyExists = 0;
                if ($selectedUsers) {
                    if (in_array($user['UserGUID'], $selectedUsers)) {
                        $alreadyExists = 1;
                    }
                }
                if ($alreadyExists == 0) {
                    //unset($user['UserID']);
                    unset($user['MySelf']);
                    /* $user['label'] = $user['FirstName'].'pp';
                      $user['value'] = $user['FirstName']; */
                    $UserData[] = $user;
                    $i++;
                }
            }
        }
        if (!isset($UserData) || empty($UserData)) {
            //$UserData[0] = array('label' => 'No result found.', 'value' => $search_key);
        }
        return $UserData;
    }

    function incoming_request_count($user_id, $uid) {
        $blocked_users = blocked_users($user_id);
        
        $this->db->select('count(f.FriendID) AS total_requests');
        $this->db->from(USERS . " u");
        $this->db->join(FRIENDS . " as f", "f.FriendID = u.UserID", "LEFT");
        $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
        $sql_condition = array('u.UserID !=' => $uid, 'f.Status' => 0, 'f.UserID' => $uid, 'f.RequestedBy !=' => $uid);
        
        $this->db->where($sql_condition);
        $this->db->where_not_in('u.StatusID', array(3, 4));
        if ($blocked_users) {
            $this->db->where_not_in('f.FriendID', $blocked_users);
        }
        $res = $this->db->get()->row_array();
        return $res['total_requests'];
    }

    function incoming_request_count_update($user_id) {
        /* update last incoming request date to get record from this after - changes by gautam */
        $this->db->set('DateOfLastIncomingRequest', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->where('UserID', $user_id);
        $this->db->limit(1);
        $this->db->update(USERS);
        /* -/- */
    }

    /**
     * Function Name: connections
     * @param Type[user_id[int],Type[string eg.Friends,IncomingRequest etc],SearchKey[String],PageNo[Int],PageSize[Int]
     * @return JSON[response of user list based on type and given userguid]
     * Description: To get friend connections/ requests / followers / following
     */
    function connections($user_id, $type, $uid, $search_key, $page_no, $page_size, $viewingUserID = 0) {
        $frnd_cnt = 0;
        $permission = 0;
        if ($type == 'Friends') {
            $permission = $this->privacy_model->check_privacy($user_id, $uid, 'view_friends');
        } elseif ($type == 'Following') {
            $permission = $this->privacy_model->check_privacy($user_id, $uid, 'view_followings');
        }

        $blocked_users = blocked_users($user_id);
        $usr_id = $user_id;
        $sql_condition = array();
        $this->db->select('p.Url as ProfileLink, u.FirstName, u.ProfilePicture, u.UserID, u.UserGUID, u.LastName');
        $this->db->from(USERS . " u");

        $this->db->join(PROFILEURL . " as p", "p.EntityID = u.UserID and p.EntityType = 'User'", "LEFT");

        if ($type == 'Friends') {
            $module_id = 10;
            $this->db->select('f.Status');
            $this->db->join(FRIENDS . " as f", "f.FriendID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $this->db->select("IF((SELECT Status FROM " . FRIENDS . " WHERE UserID='" . $user_id . "' AND Status='1' AND FriendID=u.UserID LIMIT 1) is not null,1,0) as IsFriend", false);
            $this->db->order_by('IsFriend', 'DESC');
            $sql_condition = array('u.UserID !=' => $uid, 'f.UserID' => $uid);
            $sql_condition['f.Status'] = 1;
            if ($user_id != $uid) {
                if (!$permission) {
                    $subquery = "IF((SELECT UserID FROM " . FRIENDS . " WHERE UserID=f.FriendID AND FriendID='" . $user_id . "' AND Status='1') is not null,TRUE,FALSE)";
                    $this->db->where($subquery, NULL, FALSE);
                }
            }
        } elseif ($type == 'IncomingRequest') {
            $this->db->join(FRIENDS . " as f", "f.FriendID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $sql_condition = array('u.UserID !=' => $uid, 'f.Status' => 0, 'f.UserID' => $uid, 'f.RequestedBy !=' => $uid);
        } elseif ($type == 'OutgoingRequest') {
            $this->db->join(FRIENDS . " as f", "f.FriendID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $sql_condition = array('u.UserID !=' => $user_id, 'f.Status' => 0, 'f.UserID' => $user_id, 'f.RequestedBy =' => $usr_id);
        } elseif ($type == 'RequestSentAndRecieved') {
            $this->db->join(FRIENDS . " as f", "f.FriendID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $sql_condition = array('u.UserID !=' => $usr_id, 'f.Status' => 0, 'f.UserID' => $usr_id);
        } elseif ($type == 'Followers') {
            $this->db->join(FOLLOW . " as f", "f.UserID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $sql_condition = array('u.UserID !=' => $uid, 'f.Type' => 'User', 'f.TypeEntityID' => $uid, 'f.StatusID' => '2');
        } elseif ($type == 'Following') {

            if ($user_id != $uid) {
                if (!$permission) {
                    $subquery = "IF(FALSE,TRUE,FALSE)";
                    $this->db->where($subquery, NULL, FALSE);
                }
            }

            $this->db->join(FOLLOW . " as f", "f.TypeEntityID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $sql_condition = array('u.UserID !=' => $uid, 'f.Type' => 'User', 'f.UserID' => $uid, 'f.StatusID' => '2');
        } else {
            $sql_condition = array('u.UserID !=' => $usr_id);
        }
        $this->db->where_not_in('u.StatusID', array(3, 4));
        $this->db->where($sql_condition);
        if ($blocked_users) {
            $this->db->where_not_in('u.UserID', $blocked_users);
        }
        if (!empty($search_key)) {
            $this->db->where("(u.FirstName like '%" . $this->db->escape_like_str($search_key) . "%' or u.LastName like '%" . $this->db->escape_like_str($search_key) . "%' or concat(u.FirstName,' ',u.LastName) like '%" . $this->db->escape_like_str($search_key) . "%')");
        }
        $this->db->group_by('u.UserID');
        /* ----------- Cloning database object before adding pagination to get total count from query-------------- */
        $tempdb = clone $this->db;
        $num_results = $tempdb->count_all_results();
        /* -------------------------------------- */



        /* --------Added pagination-------- */
        $offset = ($page_no - 1) * $page_size;
        $this->db->limit($page_size, $offset);
        /* -------------------------------------- */

        $Query = $this->db->get();
        $users = $Query->result_array();
        $module_settings = $this->settings_model->getModuleSettings(true);
        $row = array();
        $i = 0;

        $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friends = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();
        $friends[] = 0;
        $follow[] = 0;
        $connected_user = $this->friend_model->get_connect_user($user_id);
        foreach ($users as $value) {
            $value['FriendStatus'] = 4;
            if (isset($connected_user[$value['UserID']])) {
                if ($connected_user[$value['UserID']]) {
                    if ($connected_user[$value['UserID']]['Status'] == '1') {
                        $value['FriendStatus'] = 1; // Already Friend
                        $frnd_cnt++;
                    } elseif ($connected_user[$value['UserID']]['RequestedBy'] == $user_id) {
                        $value['FriendStatus'] = 2; // Pending Request
                    } else {
                        $value['FriendStatus'] = 3; // Accept Friend Request
                    }
                }
            }
            $value['Location'] = $this->user_model->get_user_location($value['UserID']);
            $row[$i] = $value;

            if (isset($module_settings['m11']) && !empty($module_settings['m11'])) {
                // Follow Module
                $row[$i]['ShowFollowBtn'] = 1;
//                if ($uid != $usr_id)
//                {
//                    $row[$i]['ShowFollowBtn'] = 0;
//                }

                $row[$i]['FollowStatus'] = 'Follow';
                if (in_array($value['UserID'], $follow)) {
                    $row[$i]['FollowStatus'] = 'Unfollow';
                }

                $row[$i]['MutualFriendCount'] = $this->friend_model->get_mutual_friend($value['UserID'], $user_id, '', 1, 1, PAGE_SIZE, $viewingUserID);
            }

            if (isset($module_settings['m11']) && !empty($module_settings['m10'])) {
                $row[$i]['ShowFriendsBtn'] = 1;
            }
//            if ($uid != $usr_id)
//            {
//                $row[$i]['ShowFriendsBtn'] = 0;
//            }

            $row[$i]['ProfileLink'] = $value['ProfileLink'];

            if ($user_id != $row[$i]['UserID']) {
                $users_relation = get_user_relation($user_id, $row[$i]['UserID']);
                $privacy_details = $this->privacy_model->details($row[$i]['UserID']);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label']) {
                    foreach ($privacy_details['Label'] as $privacy_label) {
                        $privacy_label['Value'] = isset($privacy_label['Value']) ? $privacy_label['Value'] : '';
                        $privacy_label[$privacy] = isset($privacy_label[$privacy]) ? $privacy_label[$privacy] : '';
                        if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $row[$i]['ProfilePicture'] = 'user_default.jpg';
                        }

                        if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $row[$i]['ShowFriendsBtn'] = 0;
                        }
                    }
                }
            }

            $row[$i]['MySelf'] = 0;
            if ($row[$i]['UserID'] == $user_id) {
                $row[$i]['MySelf'] = 1;
            }

            $i++;
        }
        unset($row[$i]['UserID']);
        unset($row[$i]['Status']);

        /* $r['MutualFriendCount'] = 0;
        if ($type == 'Friends') {
            $r['MutualFriendCount'] = $this->friend_model->get_mutual_friend($uid, $user_id, '', 1);
        }
         */

        $r['Members'] = $row;
        $r['TotalRecords'] = $num_results;
        $r['Permission'] = $permission;
        //$r['FriendsCount'] = $frnd_cnt;

        $r['IsDifferentUserProfile'] = 0;
        if ($uid != $usr_id) {
            $r['IsDifferentUserProfile'] = 1;
        }

        return $r;
    }

    function get_newsfeed_tagging_records($search_key, $user_id, $type, $page_no = PAGE_NO, $page_size = PAGE_SIZE, $Status = '', $UID = '', $module_id = 0, $module_entity_id = 0, $AdvanceSearch = array(), $CountOnly = 0, $exclude_ids = array()) {
        $this->load->model(array('activity/activity_model', 'users/user_model', 'group/group_model','forum/forum_model'));
        $groups = $this->group_model->get_users_groups($user_id);
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $search_key = trim($search_key);
        $UsrID = $user_id;
        $offset = $this->get_pagination_offset($page_no, $page_size);
        $limit = " LIMIT " . $offset . ',' . $page_size;
        $privacy_condition = "
			IF(UP.Value='everyone',true, 
				IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
				IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
			)
		";

        if ($blocked_users) {
            $privacy_condition .= " AND u.UserID NOT IN (" . implode(',', $blocked_users) . ") ";
        }

        $exclude_ids[] = $UsrID;
        $innerSql = ' 
				SELECT p.Url as ProfileUrl, u.FirstName, IF(u.ProfilePicture="","user_default.jpg",u.ProfilePicture) as ProfilePicture, u.UserID, u.UserGUID, u.LastName, "3" as ModuleID
				FROM ' . USERS . ' u 
				left join ' . USERDETAILS . ' ud ON ud.UserID=u.UserID
				left join ' . PROFILEURL . ' p ON p.EntityID = u.UserID and p.EntityType="User"
				left join ' . USERPRIVACY . ' UP ON UP.UserID=u.UserID
				WHERE UP.PrivacyLabelKey="tagged" AND ud.LocalityID = '.$this->LocalityID.'				AND ' . $privacy_condition . ' AND u.StatusID NOT IN(3,4) AND u.UserID NOT IN (' . implode(',', $exclude_ids) . ')
				UNION
				SELECT page.PageURL as ProfileUrl, page.Title as FirstName, IF(page.ProfilePicture="","user_default.jpg",page.ProfilePicture) as ProfilePicture, page.PageID as UserID, page.PageGUID as UserGUID, "" as LastName, "18" as ModuleID from ' . PAGES . ' page
					JOIN ' . PAGEMEMBERS . ' PM ON PM.PageID=page.PageID AND PM.UserID="' . $UsrID . '" AND PM.StatusID="2" 
			';
        $group_where = '';
        if(!$this->settings_model->isDisabled(1)) { // if group module is not disabled
            if ($groups) {
                $group_where = " WHERE grp.GroupID IN (" . implode(',', $groups) . ") ";
            }
            $innerSql.= ' UNION
				SELECT grp.GroupID as ProfileUrl, grp.GroupName as FirstName, IF(grp.GroupImage="","user_default.jpg",grp.GroupImage) as ProfilePicture, grp.GroupID as UserID, grp.GroupGUID as UserGUID, "" as LastName, "1" as ModuleID from ' . GROUPS . ' grp
					JOIN ' . GROUPMEMBERS . ' GM ON GM.GroupID=grp.GroupID AND GM.ModuleID="3" AND GM.ModuleEntityID="' . $UsrID . '" AND GM.StatusID="2"
                    ' . $group_where . '';
        }
        
        if(!$this->settings_model->isDisabled(34) && $module_id==34 && !empty($module_entity_id)) {
            $innerSql.= ' UNION
				SELECT FC.ForumCategoryID as ProfileUrl, FC.Name as FirstName, IFNULL(M.ImageName,"") as ProfilePicture, FC.ForumCategoryID as UserID, FC.ForumCategoryGUID as UserGUID, "" as LastName, "34" as ModuleID from ' . FORUMCATEGORY . ' FC
                                JOIN '.FORUM.' F ON F.ForumID = FC.ForumID AND F.StatusID=2 AND F.Visible=1
                                LEFT JOIN ' . MEDIA . ' M ON M.MediaID=FC.MediaID WHERE FC.IsDiscussionAllowed=1 AND FC.Visibility=1 AND FC.ForumCategoryID!='.$module_entity_id;
        }
        
        $search_key = $this->db->escape_like_str($search_key); 

        $sql = 'SELECT * FROM ( ' .$innerSql.' ) tbl1 WHERE (FirstName like "' . $search_key . '%" or LastName like "' . $search_key . '%" or concat(FirstName," ",LastName) like "' . $search_key . '%") ';

        if ($CountOnly) {
            $query = $this->db->query($sql);
            return $query->num_rows();
        }
        //$sql .= $limit;
        $query = $this->db->query($sql);
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $data = array();
            foreach ($query->result_array() as $result) {
                $result['AllowedPostType'] = $this->user_model->get_post_permission_for_newsfeed($user_id);
                if ($result['ModuleID'] == 1) {
                    $result['AllowedPostType'] = $this->group_model->get_post_permission($result['UserID']);
                }
                if($result['ModuleID'] == 34)
                {
                    $result['ProfileUrl'] = $this->forum_model->get_category_url($result['UserID']);
                }
                $data[] = $result;
            }
            return array('Members' => $data);
        }
    }

    /**
     * [get_all_user get user details based on request parameter]
     * @param  [type] $search_key [Search keyword]
     * @param  [type] $user_id    [Logged in user ID]
     * @param  [type] $type      [Friends, Request, Followers, Following]
     * @param  [type] $page_no    [Page number offset]
     * @param  [type] $page_size  [Limit per page records]
     * @param  string $Status    [description]
     * @param  string $UID       [description]
     * @return [type]            [description]
     */
    function get_all_user($search_key, $user_id, $type, $page_no = PAGE_NO, $page_size = PAGE_SIZE, $Status = '', $UID = '', $module_id = 0, $module_entity_id = 0, $AdvanceSearch = array(), $CountOnly = 0, $sort_by = '', $exclude_ids = array()) {
        $this->load->model(array('activity/activity_model', 'users/user_model'));
        $sort_friends = array();
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $sort_network = array();
        if ($sort_by == 'Network') {
            $sort_friends = $this->user_model->gerFriendsFollowersList($user_id, true, 1, true);
            if ($sort_friends) {
                $sort_network = $this->user_model->get_friends_of_friend($user_id, $sort_friends);
            }
        }
        $search_key = trim($search_key);
        if (!$module_id) {
            $module_id = 3;
            $module_entity_id = $user_id;
        }
        if ($module_id && $module_entity_id) {
            $blockedUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
        } else {
            $blockedUsers = array();
        }
        if (isset($AdvanceSearch['AgeGroup']) && $AdvanceSearch['AgeGroup']) {
            $AgeRange = get_age_range($AdvanceSearch['AgeGroup']);
        }
        if ($AdvanceSearch) {
            if ($AdvanceSearch['Location']) {
                $this->load->helper('location');
                $LocationData = update_location($AdvanceSearch['Location']);
            }
        }

        $group_user_list = array(0);
        if ($type == 'MembersTagging' && $module_id == '1') {
            $this->load->model(array('group/group_model'));
            $this->group_model->get_group_member_recursive($group_user_list, array($module_entity_id));
            $group_user_list = array_unique($group_user_list);
        }
        $UsrID = $user_id;
        $sqlCondition = array();
        $this->db->select('p.Url as ProfileLink, u.FirstName, IF(u.ProfilePicture="","user_default.jpg",u.ProfilePicture) as ProfilePicture, u.UserID, u.UserGUID, u.LastName, "3" as ModuleID,ud.UserWallStatus', false);
        $this->db->from(USERS . " u");
        $this->db->join(PROFILEURL . " as p", "p.EntityID = u.UserID and p.EntityType = 'User'", "LEFT");
        $this->db->join(USERDETAILS . " as ud", "ud.UserID = u.UserID", "LEFT");

        if ($exclude_ids) {
            $this->db->where_not_in('u.UserID', $exclude_ids);
        }

        if (!empty($blocked_users)) {
            $this->db->where_not_in('u.UserID', $blocked_users);
        }
        
        $this->db->where('ud.LocalityID', $this->LocalityID);
        
        $friend_disabled = $this->settings_model->isDisabled(10);
        if($friend_disabled && $type == 'Friends') {
            $type = 'Followers';
        }
        if ($type == 'Friends') {
            $moduleId = 10;
            $this->db->join(FRIENDS . " as f", "f.FriendID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $sqlCondition = array('u.UserID !=' => $UID, 'f.Status' => 1, 'f.UserID' => $UID);
        } elseif ($type == 'Request') {
            $this->db->join(FRIENDS . " as f", "f.FriendID = ud.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $sqlCondition = array('u.UserID !=' => $UsrID, 'f.Status' => 0, 'f.UserID' => $UsrID, 'f.RequestedBy !=' => $user_id);
        } elseif ($type == 'Followers') {
            $this->db->join(FOLLOW . " as f", "f.UserID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $sqlCondition = array('u.UserID !=' => $UID, 'f.Type' => 'User', 'f.TypeEntityID' => $UID);
        }
        /* elseif ($type == 'Members' && $module_id == '1')
          {
          $this->db->join(GROUPMEMBERS . ' as gm', "gm.ModuleEntityID = u.UserID AND gm.ModuleID=3", "LEFT");
          $this->db->where('gm.StatusID', '2');
          $this->db->where('gm.GroupID', $module_entity_id);
          $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
          $this->db->where_not_in('u.StatusID', array(3, 4));
          $this->db->where('UP.PrivacyLabelKey', 'tagged');
          $privacy_condition = "
          IF(UP.Value='everyone',true,
          IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID),
          IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
          )
          ";
          $this->db->where($privacy_condition, NULL, FALSE);
          } */ elseif (($type == 'MembersTagging' || $type == 'Members') && $module_id == '1') {
            $this->db->join(GROUPMEMBERS . ' as gm', "gm.ModuleEntityID = u.UserID AND gm.ModuleID=3", "LEFT");
            $this->db->where('gm.StatusID', '2');
            $this->db->where('gm.GroupID', $module_entity_id);


            $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
            $this->db->where_in('u.UserID', $group_user_list);
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $this->db->where('u.UserID!=', $user_id);
            $this->db->where('UP.PrivacyLabelKey', 'tagged');
            $privacy_condition = "
				IF(UP.Value='everyone',true, 
					IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
					IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
				)
			";
            $this->db->where($privacy_condition, NULL, FALSE);
        } elseif ($type == 'MembersTagging' && $module_id == '18') {
            $this->db->join(PAGEMEMBERS . ' as pm', "pm.UserID = u.UserID", "LEFT");
            $this->db->where('pm.PageID', $module_entity_id);
            $this->db->where('pm.StatusID', '2');
            $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $this->db->where('UP.PrivacyLabelKey', 'tagged');
            $privacy_condition = "
				IF(UP.Value='everyone',true, 
					IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
					IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
				)
			";
            $this->db->where($privacy_condition, NULL, FALSE);
        } elseif ($type == 'Members' && $module_id == '14') {
            $this->db->join(EVENTUSERS . ' as eu', "eu.UserID = u.UserID", "LEFT");
            $this->db->where('eu.IsDeleted', '0');
            $this->db->where('eu.EventID', $module_entity_id);
            $this->db->where_in('eu.Presence', array('ARRIVED', 'MAY_BE', 'ATTENDING'));
            $this->db->where_not_in('u.StatusID', array(3, 4));
        } elseif ($type == 'MembersTagging' && $module_id == '14') {
            $this->db->join(EVENTUSERS . ' as eu', "eu.UserID = u.UserID", "LEFT");
            $this->db->where('eu.IsDeleted', '0');
            $this->db->where('eu.EventID', $module_entity_id);
            $this->db->where_in('eu.Presence', array('ARRIVED', 'MAY_BE', 'ATTENDING'));
            $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $this->db->where('UP.PrivacyLabelKey', 'tagged');
            $privacy_condition = "
				IF(UP.Value='everyone',true, 
					IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
					IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
				)
			";
            $this->db->where($privacy_condition, NULL, FALSE);
        } elseif ($type == 'Following') {
            $this->db->join(FOLLOW . " as f", "f.TypeEntityID = u.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $sqlCondition = array('u.UserID !=' => $UID, 'f.Type' => 'User', 'f.UserID' => $UID);
        } elseif ($type == 'Tagging' || $type == 'MembersTagging') {
            $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $this->db->where('UP.PrivacyLabelKey', 'tagged');
            $privacy_condition = "
				IF(UP.Value='everyone',true, 
					IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
					IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
				)
			";
            $this->db->where($privacy_condition, NULL, FALSE);
            $sqlCondition = array('u.UserID !=' => $UsrID);
        } elseif ($type == 'NewsFeedTagging') {
            $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $this->db->where('UP.PrivacyLabelKey', 'tagged');
            $privacy_condition = "
				IF(UP.Value='everyone',true, 
					IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
					IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
				)
			";
            $this->db->where($privacy_condition, NULL, FALSE);
            $sqlCondition = array('u.UserID !=' => $UsrID);
        } elseif ($type == 'MyGroupOwners') {
            $this->db->join(GROUPS . " as G", "G.CreatedBy = ud.UserID AND G.StatusID=2");
            $this->db->join(GROUPMEMBERS . " as GM", "G.GroupID = GM.GroupID AND GM.ModuleEntityID=$user_id AND GM.ModuleID=3 AND GM.StatusID=2 AND GM.ModuleRoleID IN (5,6)");
        } elseif ($type == 'All') {
            $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
            $this->db->where('UP.PrivacyLabelKey', 'search');
            $privacy_condition = "
                IF(UP.Value='everyone',true, 
                    IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
                    IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
                )
            ";
            $this->db->where($privacy_condition, NULL, FALSE);
            $this->db->where_not_in('u.StatusID', array(3, 4));
        } else {
            $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=u.UserID', 'left');
            $this->db->where('UP.PrivacyLabelKey', 'search');
            $privacy_condition = "
				IF(UP.Value='everyone',true, 
					IF(UP.Value='network', u.UserID IN(SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID), 
					IF(UP.Value='friend',u.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
				)
			";
            $this->db->where($privacy_condition, NULL, FALSE);
            $this->db->where_not_in('u.StatusID', array(3, 4));
            $sqlCondition = array('u.UserID !=' => $UsrID);
        }

        if($type == 'MembersTagging')
        {
            $this->db->where('u.UserID!=',$UsrID);
        }

        if ($sort_by == 'NameAsc') {
            $this->db->order_by('u.FirstName', 'ASC');
        }
        if ($sort_by == 'NameDesc') {
            $this->db->order_by('u.FirstName', 'DESC');
        }
        if ($sort_by == 'Followers') {
            $this->db->select("(SELECT COUNT(FollowID) FROM " . FOLLOW . " WHERE TypeEntityID = u.UserID and Type='User') as FollowersCount", false);
            $this->db->order_by('FollowersCount', 'DESC');
        }
        if ($sort_by == 'ActivityLevel') {
            $this->db->select("(SELECT COUNT(ID) FROM " . USERSACTIVITYLOG . " WHERE ModuleID='3' AND ModuleEntityID=u.UserID) as ActivityLevel", false);
            $this->db->order_by('ActivityLevel', 'DESC');
        }
        if ($sort_by == 'Network') {
            $this->db->_protect_identifiers = FALSE;
            if ($sort_friends) {
                $this->db->order_by("FIELD(u.UserID," . implode(',', $sort_friends) . ") DESC");
                if ($sort_network) {
                    $this->db->order_by("FIELD(u.UserID," . implode(',', $sort_network) . ") DESC");
                }
            }
            $this->db->_protect_identifiers = TRUE;
        }

        if ($AdvanceSearch) {
            if ($AdvanceSearch['Location']) {
                if ($LocationData['CityID']) {
                    $this->db->where('ud.CityID', $LocationData['CityID']);
                }
            }
            if (isset($AdvanceSearch['Cities']) && $AdvanceSearch['Cities']) {
                $this->db->where_in('ud.CityID', $AdvanceSearch['Cities']);
            }
            if ($AdvanceSearch['Gender']) {
                if ($AdvanceSearch['Gender'] == 'Male') {
                    $AdvanceSearch['Gender'] = 1;
                } elseif ($AdvanceSearch['Gender'] == 'Female') {
                    $AdvanceSearch['Gender'] = 2;
                } elseif ($AdvanceSearch['Gender'] == 'Other') {
                    $AdvanceSearch['Gender'] = 3;
                }
                $this->db->where('u.Gender', $AdvanceSearch['Gender']);
            }
            if ($AdvanceSearch['AgeGroup']) {
                $this->db->where('YEAR(ud.DOB) BETWEEN ' . $AgeRange['ValueRangeFrom'] . ' AND ' . $AgeRange['ValueRangeTo'], NULL, FALSE);
            }

            if (isset($AdvanceSearch['Skills']) && !empty($AdvanceSearch['Skills'])) {
                $skills_list = implode(',', $AdvanceSearch['Skills']);
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ENTITYSKILLS . ' ES', 'ES.ModuleEntityID=u.UserID AND ES.ModuleID=3 AND ES.StatusID=2 AND ES.SkillID IN (' . $skills_list . ')', 'join');
                $this->db->_protect_identifiers = TRUE;
                //$this->db->where("u.UserID IN (SELECT ModuleEntityID FROM ".ENTITYSKILLS." WHERE ModuleID='3' AND StatusID='2' AND SkillID IN(".$skills_list."))");
            }

            if (isset($AdvanceSearch['Interest']) && !empty($AdvanceSearch['Interest'])) {
                $interest_list = implode(',', $AdvanceSearch['Interest']);
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=u.UserID AND EC.ModuleID=3 AND EC.CategoryID IN (' . $interest_list . ')', 'join');
                $this->db->_protect_identifiers = TRUE;
                //$this->db->where("u.UserID IN (SELECT ModuleEntityID FROM ".ENTITYCATEGORY." WHERE ModuleID='3' AND CategoryID IN(".$interest_list."))");
            }

            if (isset($AdvanceSearch['WorkExp']) && !empty($AdvanceSearch['WorkExp'])) {
                $work_exp_list = "'" . implode("','", $AdvanceSearch['WorkExp']) . "'";
                $this->db->_protect_identifiers = FALSE;
                $this->db->join(WORKEXPERIENCE . ' WE', 'WE.UserID=u.UserID AND WE.OrganizationName IN (' . $work_exp_list . ')', 'join');
                $this->db->_protect_identifiers = TRUE;
                //$this->db->where("u.UserID IN (SELECT UserID FROM ".WORKEXPERIENCE." WHERE OrganizationName IN(".$work_exp_list."))");
            }
            if (isset($AdvanceSearch['Education']) && !empty($AdvanceSearch['Education'])) {
                $education_list = "'" . implode("','", $AdvanceSearch['Education']) . "'";
                $this->db->join(EDUCATION . ' E', 'E.UserID=u.UserID AND E.University IN (' . $education_list . ')', 'join');
                //$this->db->where("u.UserID IN (SELECT UserID FROM ".EDUCATION." WHERE University IN(".$education_list."))");
            }
        }

        if ($blockedUsers) {
            $this->db->where_not_in('u.UserID', $blockedUsers);
        }
        if (!empty($sqlCondition)) {
            $this->db->where($sqlCondition);
        }

        //$search_key = '';
        if (!empty($search_key)) {
            $this->db->where("(u.FirstName like '%" . $this->db->escape_like_str($search_key) . "%' or u.LastName like '%" . $this->db->escape_like_str($search_key) . "%' or concat(u.FirstName,' ',u.LastName) like '%" . $this->db->escape_like_str($search_key) . "%')");
        }

        if (!empty($page_size)) {
            $Offset = $this->get_pagination_offset($page_no, $page_size);
            if (!$CountOnly) {
                $this->db->limit($page_size, $Offset);
            }
        }
        $this->db->group_by('u.UserID');
        $Query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($CountOnly) {
            return $Query->num_rows();
        }
        //echo $this->db->last_query();die;
        $Users = $Query->result_array();
        $ModuleSettings = $this->settings_model->getModuleSettings(true);
        $i = 0;
        $key = 0;
        $row['Members'] = array();
        foreach ($Users as $value) {
            unset($value['IsActive']);

            $entity_id = $value['UserID'];
            $value['Location'] = '';
            $value['FollowStatus'] = 'Follow';
            $value['ShowFriendsBtn'] = 0;
            $value['ShowFollowBtn'] = 0;
            if (isset($ModuleSettings['m11']) && !empty($ModuleSettings['m11'])) {
                // Follow Module
                $value['ShowFollowBtn'] = 1;
            }
            if ($user_id != $entity_id) {
                $value['FriendStatus'] = $this->friend_model->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request

                $LocationArr = $this->user_model->get_user_location($entity_id);
                $Location = '';
                if (!empty($LocationArr['City'])) {
                    $Location .= $LocationArr['City'];
                    if (!empty($LocationArr['StateCode'])) {
                        $Location .= ', ' . $LocationArr['StateCode'];
                    }
                    if (!empty($LocationArr['Country'])) {
                        $Location .= ', ' . $LocationArr['Country'];
                    }
                }
                $value['Location'] = $Location;

                if (isset($ModuleSettings['m11']) && !empty($ModuleSettings['m10'])) {
                    $value['ShowFriendsBtn'] = 1;
                }

                if(!$this->settings_model->isDisabled(25)){
                    //If message module is not disabled
                    $value['ShowMessageBtn'] = 1;
                }else
                    $value['ShowMessageBtn'] = 0;

                $users_relation = get_user_relation($user_id, $entity_id);
                $privacy_details = $this->privacy_model->details($entity_id);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label']) {
                    foreach ($privacy_details['Label'] as $privacy_label) {
                        if ($privacy) {
                            if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $value['Location'] = '';
                            }
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $value['ProfilePicture'] = 'user_default.jpg';
                            }

                            if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $value['ShowFriendsBtn'] = 0;
                            }

                            if ($value['ShowMessageBtn'] == 1 && $privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $value['ShowMessageBtn'] = 0;
                            }
                        }
                    }
                }

                $follow = "select FollowID from Follow where (TypeEntityID = " . $entity_id . " and UserID = " . $user_id . ") and type='user'";
                $following = $this->db->query($follow)->num_rows();
                if ($following) {
                    $value['FollowStatus'] = 'Unfollow';
                }
            }

            $value['AboutMe'] = $value['UserWallStatus'];
            $value['Interests'] = $this->user_model->get_user_interest($value['UserID']);
            $value['InterestsCount'] = $this->user_model->get_user_interest($value['UserID'], 0, 0, true);

            $value['Location'] = isset($Location) ? $Location : array();
            $value['MutualFriend'] = $this->friend_model->get_mutual_friend($value['UserID'], $user_id, '', 1);

            $value['ProfileLink'] = $value['ProfileLink'];
            /* $row['Members'][$value['UserID']]['label'] = $value['FirstName'].' '.$value['LastName'];
              $row['Members'][$value['UserID']]['value'] = $value['FirstName'].' '.$value['LastName']; */
            $value['MySelf'] = 0;
            if ($value['UserID'] == $user_id) {
                $value['MySelf'] = 1;
            }
            $row['Members'][] = $value;
            $key++;
            $i++;
        }
        $row['showFriendBox'] = 0;
        if (isset($ModuleSettings['m11']) && !empty($ModuleSettings['m10'])) {
            $row['showFriendBox'] = 1;
        }

        if (isset($row['Members']) && !empty($row['Members']) && $sort_by == '') {
            aasort($row['Members'], 'MutualFriend');
        }
        return $row;
    }

    public function get_friend_request_date($current_user_id, $user_id) {
        $this->db->select('CreatedDate');
        $this->db->from(FRIENDS);
        $this->db->where('UserID', $current_user_id);
        $this->db->where('FriendID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $create_date = $query->row()->CreatedDate;
            return get_current_date('%d %M at %H:%i %A', 0, 0, strtotime($create_date));
        } else {
            return get_current_date('%d %M at %H:%i %A');
        }
    }

    /**
     * [action_button_status Get action button information ]
     * @param  [type]  [$current_user_id]
     * @param  [type] $user_id    [Logged in user ID]
     */
    public function action_button_status($current_user_id, $user_id) {
        $row['ShowFollowBtn'] = 0;
        $row['ShowFriendsBtn'] = 0;
        if(!$this->settings_model->isDisabled(25)){
            //If message module is not disabled
            $row['ShowMessageBtn'] = 1;
        }else{
            $row['ShowMessageBtn'] = 0;
        }

        $row['RequestDate'] = $this->get_friend_request_date($current_user_id, $user_id);
        if (!$this->settings_model->isDisabled(11)) {
            $row['ShowFollowBtn'] = 1;
        }
        if (!$this->settings_model->isDisabled(10)) {
            $row['ShowFriendsBtn'] = 1;
        }

        if ($current_user_id != $user_id) {
            $users_relation = get_user_relation($current_user_id, $user_id);
            $privacy_details = $this->privacy_model->details($user_id);
            $privacy = ucfirst($privacy_details['Privacy']);
            if ($privacy_details['Label']) {
                foreach ($privacy_details['Label'] as $privacy_label) {
                    if(isset($privacy_label[$privacy])) {
                        if (isset($privacy_label['Value']) && $privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $row['ShowFriendsBtn'] = 0;
                        }
                        if ($row['ShowMessageBtn'] ==1 && isset($privacy_label['Value']) && $privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)) {
                            $row['ShowMessageBtn'] = 0;
                        }
                    }                    
                }
            }
        }

        $row['IsSubscribed'] = $this->subscribe_model->is_subscribed($current_user_id, 'USER', $user_id);
        $row['FriendStatus'] = $this->checkFriendStatus($current_user_id, $user_id);
        $follow = "select FollowID from Follow where (TypeEntityID = " . $user_id . " and UserID = " . $current_user_id . ") and type='user'";
        $following = $this->db->query($follow)->num_rows();
        $row['follow'] = 'Follow';
        if ($following) {
            $row['follow'] = 'Unfollow';
        }
        $row['CanReport'] = 1;
        $this->load->model('flag_model');
        if ($this->flag_model->is_flagged($current_user_id, $user_id, 'User')) {
            $row['CanReport'] = '0';
        }
        $row['UserID'] = $user_id;
        $row['UserGUID'] = get_detail_by_id($user_id, 3, 'UserGUID');
        return $row;
    }

    /**
     * [getSuggestedMutualFriend]
     * @param  [type] $user_id
     * @param  [type] $UID
     * @param  [type] $Users
     * @param  [type] $blockedUsers
     */
    public function getSuggestedMutualFriend($user_id, $UID, $Users, $blockedUsers) {
        $UserGUIDs = array();
        $this->db->select('U.UserGUID');
        $this->db->from(USERS . ' U');
        $this->db->join(FRIENDS . ' FR', 'FR.UserID=U.UserID');
        $this->db->where('U.UserID!=', $user_id, FALSE);
        $this->db->where('FR.UserID!=', $user_id, FALSE);
        $this->db->where('U.UserID!=', $UID, FALSE);
        //$this->db->where('U.StatusID','2');
        $this->db->where_not_in('U.StatusID', array(3, 4));
        if (!empty($Users)) {
            $this->db->where_in('FR.FriendID', $Users);
            $this->db->where_not_in('FR.UserID', $Users);
        }
        if ($blockedUsers) {
            $this->db->where_not_in('U.UserID', $blockedUsers);
        }
        $this->db->where('FR.Status', '1');
        $query = $this->db->get(USERS);
        if ($query->num_rows()) {
            foreach ($query->result() as $User) {
                $UserGUIDs = $User->UserGUID;
            }
        }
        return $UserGUIDs;
    }

    public function get_user_interest($user_id) {
        $this->db->select('WhyYouHere');
        $this->db->from(USERDETAILS);
        $this->db->where('UserID', $user_id);
        $this->db->where('IsAllInterest', '0');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->WhyYouHere;
        }
        return false;
    }

    public function get_user_selected_interest($user_id){
        $this->db->select('CM.CategoryID');
        $this->db->from(ENTITYCATEGORY." EC");
        $this->db->join(CATEGORYMASTER." CM",'CM.CategoryID=EC.CategoryID');
        $this->db->where('EC.ModuleEntityID', $user_id);
        $this->db->where('EC.ModuleID', 3);
        $this->db->where('EC.ModuleEntityUserType',1);
        $query = $this->db->get();
        $selectedInt = array();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $row){
                $selectedInt[] = $row['CategoryID'];
            }
        }
        return $selectedInt;
    }

    public function get_people_you_may_know($user_id, $UID, $limit = 5, $offset = 0, $OrdBy, $Order) {
        $this->load->model('ignore_model');

        $connected_user = $this->friend_model->get_connect_user($user_id);
        $blockedUsers = blocked_users($user_id);
        $IgnoreUsers = $this->ignore_model->get_ignored_list($user_id, 'User');
        $UserList = array();
        $Users = array();
        $this->db->_protect_identifiers = false;
        $this->db->select('FS.UserID');
        $this->db->select('U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL');
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(FRIENDS . ' F');
        $this->db->join(FRIENDS . ' FS', 'F.FriendID=FS.FriendID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=FS.UserID', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
        $this->db->where('FS.UserID!=' . $user_id, null, false);
        $this->db->where('F.UserID', $user_id);
        $this->db->where('FS.Status', '1');
        $this->db->where('F.Status', '1');
        $this->db->where('FS.FriendID', 'F.FriendID');
        if ($IgnoreUsers) {
            $this->db->where_not_in('FS.UserID', $IgnoreUsers);
        }
        if ($blockedUsers) {
            $this->db->where_not_in('FS.UserID', $blockedUsers);
        }
        $this->db->group_by('FS.UserID');
        $this->db->order_by('FS.UserID', 'ASC');
        $this->db->where('FS.Status', '1');
        $this->db->where('U.StatusID!=', 3);
        $this->db->order_by($OrdBy, $Order);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        $this->db->_protect_identifiers = true;
        $limit = $limit - $query->num_rows();
        if ($query->num_rows()) {
            $UserList = $query->result_array();
        } else {
            $this->db->select('F.UserID');
            $this->db->from(FRIENDS . ' F');
            $this->db->where('F.FriendID', $user_id);
            $this->db->where('F.Status', '1');
            if ($IgnoreUsers) {
                $this->db->where_not_in('F.UserID', $IgnoreUsers);
            }
            if ($blockedUsers) {
                $this->db->where_not_in('F.UserID', $blockedUsers);
            }
            $this->db->group_by('F.UserID');
            $this->db->where('F.Status', '1');
            $users_query = $this->db->get();
            if ($users_query->num_rows()) {
                foreach ($users_query->result_array() as $r) {
                    $Users[] = $r['UserID'];
                }
            }
        }

        if ($limit > 0) {
            $UserGUIDs = $this->getSuggestedMutualFriend($user_id, $UID, $Users, $blockedUsers);
            $this->db->select('U.UserID, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture, PU.Url as ProfileURL');
            $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
            $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
            $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
            $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
            $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
            $this->db->where('U.UserID!=', $user_id, FALSE);
            $this->db->where('U.UserID!=', $UID, FALSE);
            $this->db->where_not_in('U.StatusID', array(3, 4));
            //$this->db->where('U.StatusID','2');
            if (!empty($Users)) {
                $this->db->where_not_in('U.UserID', $Users);
            }
            if (!empty($UserGUIDs)) {
                $this->db->where_not_in('U.UserGUID', $UserGUIDs);
            }
            if ($blockedUsers) {
                $this->db->where_not_in('U.UserID', $blockedUsers);
            }
            if ($IgnoreUsers) {
                $this->db->where_not_in('U.UserID', $IgnoreUsers);
            }
            $this->db->order_by($OrdBy, $Order);
            $this->db->limit($limit, $offset);
            $this->db->group_by("U.UserID");
            $query = $this->db->get(USERS);
            //echo $this->db->last_query();die;
            if ($query->num_rows) {
                $result = $query->result_array();
                $UserList = array_merge_recursive($UserList, $result);
                //return $query->result_array();
            }
        }

        $user_guids = array();
        $final_user_list = array();
        foreach ($UserList as $key => $val) {
            if (!in_array($val['UserGUID'], $user_guids)) {
                $entity_id = $val['UserID'];
                $UserList[$key]['MutualFriends'] = $this->get_mutual_friend(get_detail_by_guid($val['UserGUID'], 3), $user_id, '', 1);

                $UserList[$key]['ShowFriendsBtn'] = 0;
                $UserList[$key]['FriendStatus'] = 0;

                if ($user_id != $entity_id) {
                    $UserList[$key]['ShowFriendsBtn'] = 1;
                    //$UserList[$key]['FriendStatus'] = $this->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request
                    $UserList[$key]['FriendStatus'] = 4;
                    if (isset($connected_user[$entity_id])) {
                        if ($connected_user[$entity_id]) {
                            if ($connected_user[$entity_id]['Status'] == '1') {
                                $UserList[$key]['FriendStatus'] = 1; // Already Friend
                            } elseif ($connected_user[$entity_id]['RequestedBy'] == $user_id) {
                                $UserList[$key]['FriendStatus'] = 2; // Pending Request
                            } else {
                                $UserList[$key]['FriendStatus'] = 3; // Accept Friend Request
                            }
                        }
                    }
                    $users_relation = get_user_relation($user_id, $entity_id);
                    $privacy_details = $this->privacy_model->details($entity_id);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label[$privacy])) {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $UserList[$key]['ProfilePicture'] = 'user_default.jpg';
                                }
                                if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $UserList[$key]['ShowFriendsBtn'] = 0;
                                }
                            }
                        }
                    }
                }

                $UserList[$key]['Interest'] = $this->user_model->get_interest($entity_id, 1, 5);

                unset($UserList[$key]['UserID']);
                $user_guids[] = $val['UserGUID'];

                $final_user_list[] = $UserList[$key];
            }
        }
        return $final_user_list;
    }

    /**
     * [getPeopleYouMayKnow]
     * @param  [type]  [$user_id]
     * @param  [type] $limit
     * @param  [type] $offset
     */
    public function getPeopleYouMayKnow($user_id, $UID, $limit = 5, $offset = 0, $OrdBy, $Order) {
        $this->load->model('ignore_model');

        $connected_user = $this->friend_model->get_connect_user($user_id);

        $blockedUsers = blocked_users($user_id);
        $IgnoreUsers = $this->ignore_model->get_ignored_list($user_id, 'User');
        $this->db->select('FriendID');
        $this->db->from(FRIENDS);
        $this->db->where('UserID', $user_id);
        //$this->db->where('Status','1');
        $query = $this->db->get();
        $Users = array();
        $UserGUIDs = array();
        $UserList = array();
        if ($query->num_rows()) {
            foreach ($query->result() as $usr) {
                $Users[] = $usr->FriendID;
            }
            $this->db->select('U.UserID, U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL');
            $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
            $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
            $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
            $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
            $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
            $this->db->join(FRIENDS . ' FR', 'FR.UserID=U.UserID');
            if ($IgnoreUsers) {
                $this->db->where_not_in('U.UserID', $IgnoreUsers);
            }
            $this->db->where('U.UserID!=', $user_id, FALSE);
            //$this->db->where('FR.UserID!=', $user_id, FALSE);
            if ($user_id != $UID) {
                $this->db->where('U.UserID!=', $UID, FALSE);
            }

            $this->db->where_not_in('U.StatusID', array(3, 4));

            //$this->db->where('U.StatusID','2');
            if (!empty($Users)) {
                $this->db->where_in('FR.FriendID', $Users);
                $this->db->where_not_in('FR.UserID', $Users);
            }
            if ($blockedUsers) {
                $this->db->where_not_in('U.UserID', $blockedUsers);
            }
            $this->db->where('FR.Status', '1');
            $this->db->order_by($OrdBy, $Order);
            $this->db->limit($limit, $offset);
            $this->db->group_by("U.UserID");
            $query = $this->db->get(USERS);
            //echo $this->db->last_query();die;
            $limit = $limit - $query->num_rows();
            if ($query->num_rows()) {
                $UserList = $query->result_array();
            }
        }
        if ($limit > 0) {
            $UserGUIDs = $this->getSuggestedMutualFriend($user_id, $UID, $Users, $blockedUsers);
            $this->db->select('U.UserID, U.FirstName, U.LastName, U.UserGUID, U.ProfilePicture, PU.Url as ProfileURL');
            $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
            $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
            $this->db->from(USERS . ' U');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
            $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
            $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
            $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
            $this->db->where('U.UserID!=', $user_id, FALSE);
            $this->db->where('U.UserID!=', $UID, FALSE);
            $this->db->where_not_in('U.StatusID', array(3, 4));
            //$this->db->where('U.StatusID','2');
            if (!empty($Users)) {
                $this->db->where_not_in('U.UserID', $Users);
            }
            if (!empty($UserGUIDs)) {
                $this->db->where_not_in('U.UserGUID', $UserGUIDs);
            }
            if ($blockedUsers) {
                $this->db->where_not_in('U.UserID', $blockedUsers);
            }
            if ($IgnoreUsers) {
                $this->db->where_not_in('U.UserID', $IgnoreUsers);
            }
            $this->db->order_by($OrdBy, $Order);
            $this->db->limit($limit, $offset);
            $this->db->group_by("U.UserID");
            $query = $this->db->get(USERS);
            //echo $this->db->last_query();die;
            if ($query->num_rows) {
                $result = $query->result_array();
                $UserList = array_merge_recursive($UserList, $result);
                //return $query->result_array();
            }
        }

        $user_guids = array();
        $final_user_list = array();
        foreach ($UserList as $key => $val) {
            if (!in_array($val['UserGUID'], $user_guids)) {
                $entity_id = $val['UserID'];
                $UserList[$key]['MutualFriends'] = $this->get_mutual_friend(get_detail_by_guid($val['UserGUID'], 3), $user_id, '', 1);

                $UserList[$key]['ShowFriendsBtn'] = 0;
                $UserList[$key]['FriendStatus'] = 0;

                if ($user_id != $entity_id) {
                    $UserList[$key]['ShowFriendsBtn'] = 1;
                    //$UserList[$key]['FriendStatus'] = $this->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request
                    $UserList[$key]['FriendStatus'] = 4;
                    if (isset($connected_user[$entity_id])) {
                        if ($connected_user[$entity_id]) {
                            if ($connected_user[$entity_id]['Status'] == '1') {
                                $UserList[$key]['FriendStatus'] = 1; // Already Friend
                            } elseif ($connected_user[$entity_id]['RequestedBy'] == $user_id) {
                                $UserList[$key]['FriendStatus'] = 2; // Pending Request
                            } else {
                                $UserList[$key]['FriendStatus'] = 3; // Accept Friend Request
                            }
                        }
                    }
                    $users_relation = get_user_relation($user_id, $entity_id);
                    $privacy_details = $this->privacy_model->details($entity_id);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label[$privacy])) {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $UserList[$key]['ProfilePicture'] = 'user_default.jpg';
                                }
                                if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $UserList[$key]['ShowFriendsBtn'] = 0;
                                }
                            }
                        }
                    }
                }

                $UserList[$key]['Interest'] = $this->user_model->get_interest($entity_id, 1, 5);

                unset($UserList[$key]['UserID']);
                $user_guids[] = $val['UserGUID'];

                $final_user_list[] = $UserList[$key];
            }
        }
        return $final_user_list;
    }

    public function getFriendAndRequestIDS($user_id) {
        $row = array();
        $frnd1 = " join " . FRIENDS . " on " . FRIENDS . ".FriendID=u.UserID ";
        $frnd2 = " and " . FRIENDS . ".UserID=" . $user_id . " and m.IsActive='1' and m.ModuleID='10' ";

        $sql = $this->db->query("Select " . FRIENDS . ".FriendID
		from Modules m, " . USERS . " u " . $frnd1 . " where u.StatusID IN (1,2) AND u.UserID != '" . $user_id . "' " . $frnd2 . " ");
        //echo $this->db->last_query();die;
        $FriendArr = $sql->result_array();
        $FriendIDS = array();
        if (!empty($FriendArr)) {
            foreach ($FriendArr as $key => $Friend) {
                $FriendIDS[] = $Friend['FriendID'];
            }
        }
        return $FriendIDS;
    }

    /**
     * [getFriendIDS]
     * @param  [type]  [$user_id]
     */
    public function getFriendIDS($user_id) {
        $row = array();
        $frnd1 = " join " . FRIENDS . " on " . FRIENDS . ".FriendID=u.UserID ";
        $frnd2 = " and " . FRIENDS . ".Status=1 and " . FRIENDS . ".UserID=" . $user_id . " and m.IsActive='1' and m.ModuleID='10' ";

        $sql = $this->db->query("Select " . FRIENDS . ".FriendID
		from Modules m, " . USERS . " u " . $frnd1 . " where u.StatusID IN (1,2) AND u.UserID != '" . $user_id . "' " . $frnd2 . " ");
        //echo $this->db->last_query();die;
        $FriendArr = $sql->result_array();
        $FriendIDS = array();
        if (!empty($FriendArr)) {
            foreach ($FriendArr as $key => $Friend) {
                $FriendIDS[] = $Friend['FriendID'];
            }
        }
        return $FriendIDS;
    }

    /**
     * [mutual_friend_list Used to get mutual friend list]
     * @param  [int] $user_id       [User ID for whom checking mutual friend]     
     * @param  [int] $current_user_id  [Logged in User ID]
     * @param  [string] $keyword    [Search Keyword]
     * @param  [int] $count_only    [Count only flag] 
     * @param  [int] $page_no       [Page Number]
     * @param  [int] $page_size     [Page Size]
     * @return [array]              [mutual friend list]   
     */
    function get_mutual_friend($user_id, $current_user_id, $keyword = '', $count_only = 0, $page_no = 1, $page_size = PAGE_SIZE, $viewingUserID = 0) {
        $row = array();
        $cnt = 0;
        
        if($viewingUserID && $count_only && $user_id == $current_user_id) {
            return $cnt;
        }
        
        $friends = array();
//        $not_in_subquery = implode(',', [$user_id, $viewingUserID]);
//        $sub_query = "FR.UserID IN (SELECT FriendID FROM " . FRIENDS . " WHERE UserID = " . $current_user_id . " AND FriendID NOT IN ($not_in_subquery) AND Status=1)";
        
        
        $sub_query = "( $current_user_id =  (SELECT UserID FROM " . FRIENDS . " WHERE UserID = " . $current_user_id . " AND FriendID = $user_id AND Status=1))";

        $this->db->select('U.FirstName,U.LastName,U.UserID,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL');
        $this->db->from(USERS . ' U');
        $this->db->join(FRIENDS . ' FR', "FR.UserID=U.UserID AND FR.FriendID = $user_id AND FR.Status = 1 ");
        $this->db->join(FRIENDS . ' FR_VW', "FR_VW.UserID = U.UserID AND  FR_VW.FriendID = $current_user_id AND FR_VW.Status = 1 ");
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        
        
        $this->db->where(" `FR`.`UserID`=`FR_VW`.`UserID` ", NULL, FALSE);
        //$this->db->where('FR.UserID', $friends);
        
        
        //$this->db->where_in('FR.UserID', $friends);
        //$this->db->where($sub_query);

        if (!empty($search_key)) {
            $search_key = $this->db->escape_like_str($search_key); 
            $this->db->where('(U.FirstName LIKE "%' . $search_key . '%" OR U.LastName LIKE "%' . $search_key . '%" OR CONCAT(U.FirstName," ",U.LastName) LIKE "%' . $search_key . '%"))', NULL, FALSE);
        }

        if ($count_only) {
            $query = $this->db->get();  //echo $this->db->last_query(); die;
            $cnt = $query->num_rows();
        } else {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            $query = $this->db->get();
            //echo $this->db->last_query();
            if ($query->num_rows()) {
                $users = $query->result_array();
                foreach ($users as $value) {
                    $value['Location'] = $this->user_model->get_user_location($value['UserID']);
                    $users_relation = get_user_relation($current_user_id, $value['UserID']);
                    $privacy_details = $this->privacy_model->details($value['UserID']);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                unset($value['Location']);
                            }
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $value['ProfilePicture'] = 'user_default.jpg';
                            }
                        }
                    }
                    unset($value['UserID']);
                    $friends['Friends'][] = $value;
                }
            }
        }

        if ($count_only == 1) {
            return $cnt;
        } else {
            return $friends;
        }
    }

    /**
     * [use to get MutualFriend records]
     * @param  [int]    $Input [loggedin user User ID]
     * @param  [Array]  $Input [post data]
     */
    function getMutualFriendNew($UserID, $data = array()) {
        /* Define variables - starts */
        $Return = array();
        $CaseWhere = '';
        $Input['FriendID'] = isset($data['FriendID']) ? $data['FriendID'] : '';
        $Input['SearchKey'] = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $Input['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $Input['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $Input['Count'] = isset($data['Count']) ? $data['Count'] : '';
        /* Define variables - ends */

        /* Create where case Filter - keyword */
        if (!empty($Input['SearchKey'])) {
            $Input['SearchKey'] = $this->db->escape_like_str($Input['SearchKey']); 
            $CaseWhere .= "
            AND CONCAT_WS(' ',U.FirstName,U.LastName) LIKE '%" . $Input['SearchKey'] . "%' ";
        }

        $sql = "SELECT U.UserID,U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture,PU.Url AS ProfileURL, UD.Address
            FROM Friends AS F1
            JOIN Users AS U ON U.UserID = F1.UserID
            LEFT JOIN `ProfileUrl` PU ON PU.EntityID=U.UserID AND PU.EntityType='User'
            LEFT JOIN `UserDetails` UD ON UD.UserID=U.UserID
            WHERE F1.FriendID = '" . $UserID . "' AND F1.Status='1' AND
            F1.UserID IN (SELECT F2.UserID
            FROM Friends AS F2
            WHERE F2.FriendID = '" . $Input['FriendID'] . "' AND F2.Status='1')
            $CaseWhere ";
        $query = $this->db->query($sql);
        /* Count Total Records - starts */
        $Return['TotalRecords'] = $query->num_rows();
        /* Count Total Records - ends */

        /* Stop code execution and return only totalcount - starts */
        if (isset($Input['Count']) && $Input['Count'] == 1) { /* only returns metual friends count */
            return $data['TotalRecords'];
        }
        /* Stop code execution and return only totalcount - ends */

        /* Add Limit - starts */
        $Offset = $this->get_pagination_offset($Input['PageNo'], $Input['PageSize']);
        $sql .= "
        LIMIT " . $Offset . ", " . $Input['PageSize'] . " ";
        /* Add Limit - ends */

        /* Get Data - starts */
        $users = $this->db->query($sql)->result_array();
        /* Get Data - ends */

        $module_settings = $this->settings_model->getModuleSettings(true);
        $i = 0;
        $output = array();
        foreach ($users as $value) {
            $value['Location'] = $this->user_model->get_user_location($value['UserID']);
            $value['FriendStatus'] = $this->checkFriendStatus($UserID, $value['UserID']);
            $output[$i] = $value;

            if (isset($module_settings['m11']) && !empty($module_settings['m11'])) {
                // Follow Module
                $output[$i]['ShowFollowBtn'] = 1;
                if ($Input['FriendID'] != $UserID) {
                    $output[$i]['ShowFollowBtn'] = 0;
                }
                /* Follow-or-unfollow-status - starts */
                $output[$i]['FollowStatus'] = 'Follow';
                $following = $this->db->query("select FollowID from Follow where (TypeEntityID = " . $value['UserID'] . " and UserID = " . $Input['FriendID'] . ") and type='user'")->num_rows();
                if ($following) {
                    $output[$i]['FollowStatus'] = 'Unfollow';
                }
                /* Follow-or-unfollow-status - ends */
            }
            if (isset($module_settings['m11']) && !empty($module_settings['m10'])) {
                $output[$i]['ShowFriendsBtn'] = 1;
            }
            if ($Input['FriendID'] != $UserID) {
                $output[$i]['ShowFriendsBtn'] = 0;
            }
            unset($output[$i]['UserID']);
            $i++;
        }
        $Return['Data'] = $output;
        return $Return;
    }

    /**
     * [getMutualFriend Get list of Mutual Friend]
     * @param  [int]  	$user_id      [User ID]
     * @param  [int]  	$current_user [Logged in User ID]
     * @param  [int] 	$count       [FLAG use to return total count or details]
     * @return [json]                [list of Mutual Friend]
     */
    public function getMutualFriend($user_id, $current_user, $count = 0, $limit = 0) {
        $friends = array();
        $row = array();
        $cnt = 0;
        $this->db->select('FriendID');
        $this->db->from(FRIENDS);
        $this->db->where('UserID', $current_user);
        $this->db->where('FriendID!=', $user_id, FALSE);
        $this->db->where('Status', '1');
        $qry = $this->db->get();
        if ($qry->num_rows()) {
            foreach ($qry->result() as $frnd) {
                $friends[] = $frnd->FriendID;
            }
        }

        if ($friends) {
            $this->db->select('U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL');
            $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
            $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
            $this->db->from(USERS . ' U');
            $this->db->join(FRIENDS . ' FR', 'FR.UserID=U.UserID');
            $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
            $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
            $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
            $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
            $this->db->where('FR.FriendID', $user_id);
            $this->db->where('FR.Status', '1');
            $this->db->where_in('FR.UserID', $friends);
            if ($limit) {
                $this->db->limit($limit);
            }
            $query = $this->db->get();
            if ($query->num_rows()) {
                if ($count == 1) {
                    $cnt = $query->num_rows();
                } else {
                    $row['Friends'] = $query->result_array();
                }
            }
        }
        if ($count == 1) {
            return $cnt;
        } else {
            return $row;
        }
    }

    function check_low_connection($user_id) {
        
        if ($this->settings_model->isDisabled(10)) {
            $this->db->select('FollowID');
            $this->db->from(FOLLOW);
            $this->db->where('UserID', $user_id);
            $this->db->where('Type', 'User');
            $this->db->where('StatusID', '2');
            $connection_count = $this->db->get()->num_rows();
        } else {
            $this->db->select('FriendID');
            $this->db->from(FRIENDS);
            $this->db->where('UserID', $user_id);
            $this->db->where('Status', '1');
            $connection_count = $this->db->get()->num_rows(); 
        }
        
        if ($this->settings_model->isDisabled(1)) {
            
            if ($connection_count <= MIN_CONNECTION) {
                return true;
            }  
            
        } else {
            $this->db->select('G.GroupID');
            $this->db->from(GROUPS . ' G');
            $this->db->join(GROUPMEMBERS . ' GM', 'GM.GroupID=G.GroupID', 'left');
            $this->db->where('G.StatusID', '2');
            $this->db->where('GM.StatusID', '2');
            $this->db->where('GM.ModuleEntityID', $user_id);
            $this->db->where('GM.ModuleID', '3');
            $group_count = $this->db->get()->num_rows();
            if ($connection_count <= MIN_CONNECTION && $group_count <= MIN_GROUP) {
                return true;
            }            
        }
        
        

        return false;
    }

    function get_interest($user_id) {
        $this->load->model('users/user_model');
        $interest = $this->user_model->get_interest($user_id, false, false);
        $categories = array();
        if ($interest) {
            foreach ($interest as $i) {
                if ($i['IsInterested'] == '1') {
                    $categories[] = $i['CategoryID'];
                }
            }
        }
        return $categories;
    }

    function getSimilarUsers($user_id) {
        $user_ids = array();
        $this->db->select('UserID');
        $this->db->from(WORKEXPERIENCE);
        $this->db->where('UserID!=' . $user_id, NULL, FALSE);
        $this->db->where("OrganizationName IN (SELECT OrganizationName FROM " . WORKEXPERIENCE . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
        $query1 = $this->db->get();

        $this->db->select('UserID');
        $this->db->from(EDUCATION);
        $this->db->where('UserID!=' . $user_id, NULL, FALSE);
        $this->db->where("University IN (SELECT University FROM " . WORKEXPERIENCE . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
        $query2 = $this->db->get();

        if ($query1->num_rows()) {
            foreach ($query1->result() as $result) {
                if (!in_array($result->UserID, $user_ids)) {
                    $user_ids[] = $result->UserID;
                }
            }
        }

        if ($query2->num_rows()) {
            foreach ($query2->result() as $result) {
                if (!in_array($result->UserID, $user_ids)) {
                    $user_ids[] = $result->UserID;
                }
            }
        }
        return $user_ids;
    }

    function grow_user_network($user_id, $categories, $page_no, $page_size, $user_guid, $interest = array()) {
        $this->load->model(array('users/user_model', 'activity/activity_model'));
        //$this->output->enable_profiler(TRUE);
        // Get activity rule
        $rules = $this->activityrule_model->getActivityRules($user_id, NULL, true, NULL);

        $blocked_users = $this->activity_model->block_user_list($user_id, 3);

        $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
        $friends_only = isset($friend_followers_list['Friends']) ? $friend_followers_list['Friends'] : array();
        $follow = isset($friend_followers_list['Follow']) ? $friend_followers_list['Follow'] : array();
        $follow[] = 0;

        $connected_user = $this->friend_model->get_connect_user($user_id);
        foreach ($connected_user as $item) {
            $friends[] = $item['FriendID'];
        }
       
        $friends[] = $user_id;
        $similar_users = $this->getSimilarUsers($user_id);
        $user_interests = $this->get_user_interest($user_id);

        $user_location_details = $this->user_model->get_user_location($user_id);

        if (!$categories) {
            $categories = $this->get_interest($user_id);
        }

        $this->db->select('U.UserID,U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,U.ProfileCover,P.Url as ProfileURL,CT.Name as CityName,CM.CountryName,UD.UserWallStatus as About');
        if ($friends_only) {
            $this->db->select("(SELECT COUNT(FR.FriendID) FROM " . FRIENDS . " FR WHERE FR.UserID=U.UserID AND FR.Status='1' AND FR.FriendID IN(" . implode(',', $friends_only) . ")) as MutualFriends", false);
        } else {
            $this->db->select("'0' as MutualFriends", false);
        }
        $this->db->select("(SELECT COUNT(FR.FriendID) FROM " . FRIENDS . " FR WHERE FR.UserID=U.UserID AND FR.Status='1') as TotalFriends", false);
        $this->db->select("(SELECT COUNT(F.UserID) FROM " . FOLLOW . " F WHERE F.TypeEntityID=U.UserID AND F.Type='User' AND F.StatusID='2') as TotalFollowers", false);
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'U.UserID=UD.UserID', 'left');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID AND P.EntityType="User"', 'left');
        $this->db->join(CITIES . ' CT', 'CT.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');

        // Check if user belongs to any rule
        if (!empty($rules) && !empty($rules['FinalProfileIDs'])) {
            $profile_ids = $rules['FinalProfileIDs'];
            $this->db->join(USERDETAILS . ' UDCF', "U.UserID = UDCF.UserID AND UDCF.UserID IN($profile_ids)", 'left');
            $this->db->order_by('UDCF.UserID', 'DESC');
        }
        $select_remove_users = "Select EntityID From " . IGNORE . " Where UserID = $user_id AND EntityType = 'User'";
        $this->db->where("U.UserID NOT IN ($select_remove_users)", NULL, FALSE);

        $this->db->where('U.FirstName!=""',null,false);
        $this->db->where('U.LastName!=""',null,false);

        if ($interest) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=U.UserID', 'join');
            $this->db->where('EC.ModuleID', '3');
            $this->db->where_in('EC.CategoryID', $interest);
            $this->db->group_by('U.UserID');
        } else if ($user_interests) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=U.UserID', 'left');
            $this->db->where('EC.ModuleID', '3');
            $this->db->where_in('EC.CategoryID', explode(',', $user_interests));
            $this->db->group_by('U.UserID');
        }
       
        if ($user_guid) {
            $this->db->where('U.UserGUID', $user_guid);
        } else {
            if ($blocked_users) {
                $this->db->where_not_in('U.UserID', $blocked_users);
            }
            $this->db->order_by('MutualFriends', 'DESC');
            if ($user_location_details['City']) {
                $this->db->_protect_identifiers = FALSE;
                $this->db->order_by("CT.Name='" . $user_location_details['City'] . "'", 'DESC');
                $this->db->_protect_identifiers = TRUE;
            }
            $this->db->order_by('TotalFriends', 'DESC');

            if ($similar_users) {
                $this->db->_protect_identifiers = FALSE;
                $this->db->order_by("FIELD(U.UserID,'" . implode(',', $similar_users) . "')", 'DESC');
                $this->db->_protect_identifiers = TRUE;
            }
        }


        $this->db->where_not_in('U.UserID', $friends);
        $this->db->where_not_in('U.StatusID', array(3, 4, 6, 7));

        if (!empty($page_size)) {
            $Offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $Offset);
        }

        $query = $this->db->get();

        //echo $this->db->last_query(); die;
        $result = array();

        if ($query->num_rows()) {
            foreach ($query->result_array() as $r) {
                $uid = $r['UserID'];
                unset($r['UserID']);
               
                //$album_guid = get_album_guid($uid, DEFAULT_WALL_ALBUM);
                //$r['Album'] = $this->activity_model->get_albums(0, $uid, $album_guid, 'Activity', 7);

               // $r['Album'] = [];

               // $r['Followers'] = $this->connections($uid, 'Followers', $uid, '', 1, 3);
               // $r['Followers'] = $r['Followers']['Members'];
                $r['SentRequest'] = 0;
                $r['IsFollowing'] = 0;

                if (isset($connected_user[$uid])) {
                    if ($connected_user[$uid]) {
                        if ($connected_user[$uid]['Status'] == 2) {
                            $r['SentRequest'] = 1;
                        }
                    }
                }

                if (in_array($uid, $follow)) {
                    $r['IsFollowing'] = 1;
                }
                
                if ($user_id != $uid) {
                    $users_relation = get_user_relation($user_id, $uid);
                    $privacy_details = $this->privacy_model->details($uid);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label[$privacy])) {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $r['ProfilePicture'] = 'user_default.jpg';
                                }
                            }
                        }
                    }
                }
                $result[] = $r;
            }
        }
        $returnArr['data'] = $result;  //$this->output->enable_profiler(FALSE);
        return $returnArr;
    }

    function grow_group_network($user_id, $categories, $page_no, $page_size) {

        $people_suggestion = $this->grow_user_network($user_id, $categories, 1, 1000, '');
        $people_ids = array();
        if ($people_suggestion) {
            foreach ($people_suggestion as $ps) {
                $people_ids[] = get_detail_by_guid($ps['UserGUID'], 3, 'UserID', 1);
            }
        }

        $this->load->model(array('group/group_model'));

        $friends = $this->getFriendIDS($user_id);
        $start_data = get_current_date('%Y-%m-%d', 7);
        $end_date = get_current_date('%Y-%m-%d');

        if (!$categories) {
            $categories = $this->get_interest($user_id);
        }

        if ($categories) {
            $categories = implode(',', $categories);
        }

        $joined_groups = $this->group_model->get_joined_groups($user_id);

        $this->db->select('G.MemberCount,G.GroupID,G.GroupGUID,G.LastActivity,G.GroupName,G.GroupDescription,G.CreatedDate,G.IsPublic');
        $this->db->select("if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') as ProfilePicture", false);
        $this->db->select("CONCAT_WS(' ',U.FirstName,U.LastName) as CreatedBy,P.Url as CreatedProfileUrl", false);
        $this->db->select("(SELECT COUNT(ActivityID) FROM " . ACTIVITY . " WHERE ModuleID='1' AND ModuleEntityID=G.GroupID AND ActivityTypeID='7' AND CreatedDate BETWEEN '" . $start_data . "' AND '" . $end_date . "') as ActivityCount", false);
        if ($friends) {
            $this->db->select("(SELECT COUNT(GroupMemeberID) FROM " . GROUPMEMBERS . " WHERE GroupID=G.GroupID AND StatusID='2' AND UserID IN(" . implode(',', $friends) . ")) as FriendsCount", false);
        } else {
            $this->db->select("'0' as FriendsCount", false);
        }

        if ($people_ids) {
            $this->db->select("(SELECT COUNT(GroupMemeberID) FROM " . GROUPMEMBERS . " WHERE GroupID=G.GroupID AND StatusID='2' AND UserID IN(" . implode(',', $people_ids) . ")) as SuggestedMembersCount", false);
        } else {
            $this->db->select("'0' as SuggestedMembersCount", false);
        }

        $this->db->from(GROUPS . ' G');
        $this->db->join(USERS . ' U', 'U.UserID=G.CreatedBy', 'left');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID AND P.EntityType="User"', 'left');
        $this->db->where('G.StatusID', '2');
        $this->db->where('G.IsPublic', '1');
        $this->db->where_not_in('G.GroupID', explode(',', $joined_groups));

        if ($categories) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID AND EC.ModuleID=1', 'left');
            $this->db->where_in('EC.CategoryID', $categories);
        }

        $this->db->order_by('SuggestedMembersCount', 'DESC');
        $this->db->order_by('ActivityCount', 'DESC');
        
        if (!empty($page_size)) {
            $Offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $Offset);
        }

        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        $result = array();

        if ($query->num_rows()) {
            foreach ($query->result_array() as $r) {
                $group_id = $r['GroupID'];
                unset($r['GroupID']);
                unset($r['ActivityCount']);
                unset($r['FriendsCount']);
                unset($r['SuggestedMembersCount']);
                $r['Members'] = $this->group_model->get_group_members_details($group_id, 1, 3);
                $result[] = $r;
            }
        }
        $returnArr['data'] = $result;
        return $returnArr;
    }

    /**
     * [get_new_members]
     * @param  [type]  [$user_id]
     * @param  [type] $limit
     */
    public function get_new_members($user_id, $limit = 5, $offset) {
        $this->load->model('ignore_model');
        $blocked_users = blocked_users($user_id);
        $interest = $this->get_interest($user_id);
        $user_list = array();
        $final_user_list = array();
        $this->db->select('U.UserID, U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL,UD.Introduction');
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
        $this->db->join(ENTITYCATEGORY . ' EC', " EC.ModuleEntityID=U.UserID", 'left');

        $this->db->where("U.CreatedDate BETWEEN '" . get_current_date('%Y-%m-%d', 7) . "' AND '" . get_current_date('%Y-%m-%d') . "'");

        //not logged in user
        $this->db->where('U.UserID!=', $user_id, FALSE);

        //same interest
        //$this->db->where('EC.ModuleID', 3, FALSE);  
        if (!empty($interest)) {
            //$this->db->where_in('EC.CategoryID', $interest);
        }
        //introduction should not be blank
        $this->db->where('UD.Introduction!=', "''", FALSE);
        $this->db->where('U.ProfilePicture!=', "''", FALSE);
        //get signedup user from last 7 days 
        //$this->db->where('U.CreatedDate>=', 'DATE_SUB(DATE(NOW()), INTERVAL 7 DAY)', FALSE);  
        $this->db->where_not_in('U.StatusID', array(3, 4));
        //execlude blocked users
        if ($blocked_users) {
            $this->db->where_not_in('U.UserID', $blocked_users);
        }

        $this->db->order_by('RAND()');
        $this->db->limit($limit, $offset);
        $this->db->group_by("U.UserID");
        $query = $this->db->get(USERS);

        if ($query->num_rows()) {
            $user_list = $query->result_array();
            foreach ($user_list as $key => $val) {
                $entity_id = $val['UserID'];
                $user_list[$key]['ShowFriendsBtn'] = 0;
                $user_list[$key]['FriendStatus'] = 0;
                $user_list[$key]['ShowFollowBtn'] = 0;
                $user_list[$key]['FollowStatus'] = 0;
                //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request
                $user_list[$key]['FriendStatus'] = $this->checkFriendStatus($user_id, $entity_id);
                if ($user_list[$key]['FriendStatus'] != 4) {
                    $user_list[$key]['ShowFriendsBtn'] = 1;
                }

                $user_list[$key]['ShowFollowBtn'] = 1;
                if ($user_id == $entity_id) {
                    $user_list[$key]['ShowFollowBtn'] = 0;
                }
                $follow = "select FollowID from Follow where (TypeEntityID = " . $entity_id . " and UserID = " . $user_id . ") and type='user'";
                $following = $this->db->query($follow)->num_rows();
                if ($following) {
                    $user_list[$key]['FollowStatus'] = 'Unfollow';
                } else {
                    $user_list[$key]['FollowStatus'] = 'Follow';
                }

                $users_relation = get_user_relation($user_id, $entity_id);
                $privacy_details = $this->privacy_model->details($entity_id);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label']) {
                    foreach ($privacy_details['Label'] as $privacy_label) {
                        if (isset($privacy_label[$privacy])) {
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $user_list[$key]['ProfilePicture'] = 'user_default.jpg';
                            }
                            if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $user_list[$key]['ShowFriendsBtn'] = 0;
                            }
                        }
                    }
                }
                $final_user_list[] = $user_list[$key];
            }
        }

        return $final_user_list;
    }

    public function   get_popular_profile($user_id, $limit = 5, $offset = 0) {
        $data = array();
        $this->load->model('ignore_model');
        $blockedUsers = $this->activity_model->block_user_list($user_id, 3);
        $IgnoreUsers = $this->ignore_model->get_ignored_list($user_id, 'User');
        $user_interests = $this->get_user_interest($user_id);

        $join = "";
        $condition = "";
        if ($user_interests) {
            $join = "
                LEFT JOIN " . ENTITYCATEGORY . " EC ON EC.ModuleEntityID=tbl.ModuleEntityID AND EC.ModuleID=tbl.ModuleEntityID AND EC.CategoryID IN(" . $user_interests . ")
            ";
            $condition = "
                AND IF(tbl.ModuleID=3,(EC.CategoryID is NOT NULL),true)
            ";
        }


        $query = "
            SELECT COUNT(Popularity) as Popularity,tbl.ModuleID,tbl.ModuleEntityID,ActivityDate FROM (
                SELECT UAL.ID as Popularity,
                    IF(UAL.ModuleID=19,
                        (SELECT PostAsModuleID FROM " . ACTIVITY . " WHERE ActivityID=UAL.ModuleEntityID)
                    ,UAL.ModuleID) as ModuleID,
                    IF(UAL.ModuleID=19,
                        (SELECT PostAsModuleEntityID FROM " . ACTIVITY . " WHERE ActivityID=UAL.ModuleEntityID)
                    ,UAL.ModuleEntityID) as ModuleEntityID,
                    ActivityDate 
                FROM " . USERSACTIVITYLOG . " UAL
            ) tbl
            LEFT JOIN " . FOLLOW . " F ON F.TypeEntityID=tbl.ModuleEntityID AND F.Type=IF(tbl.ModuleID='3','user','page') AND F.StatusID='2' AND F.UserID='" . $user_id . "'
            " . $join . "
            WHERE tbl.ModuleID IN(3,18)
            " . $condition . "
            AND F.FollowID is NULL
            AND DATE(ActivityDate)>='" . get_current_date('%Y-%m-%d', 7) . "'
            GROUP BY tbl.ModuleID,tbl.ModuleEntityID
            ORDER BY Popularity DESC
            LIMIT " . $offset . "," . $limit . ";
        ";
        $result = $this->db->query($query);
        //echo $this->db->last_query();
        $d = array();
        if ($result->num_rows()) {
            $popular_profiles = $result->result_array();
            $pages = array();
            $users = array();
            foreach ($popular_profiles as $pp) {
                if ($pp['ModuleID'] == '3') {
                    $users[] = $pp['ModuleEntityID'];
                }
                if ($pp['ModuleID'] == '18') {
                    $pages[] = $pp['ModuleEntityID'];
                }
            }

            $user_where = " WHERE U.UserID!='" . $user_id . "' AND U.StatusID NOT IN (3,4,6,7) ";
            if ($IgnoreUsers) {
                $user_where .= " AND U.UserID NOT IN(" . implode(',', $IgnoreUsers) . ") ";
            }
            if ($blockedUsers) {
                $user_where .= " AND U.UserID NOT IN(" . implode(',', $blockedUsers) . ") ";
            }
            if ($users) {
                $user_where .= " AND U.UserID IN(" . implode(',', $users) . ") ";
            }

            $user_sql = "
                SELECT U.FirstName,U.LastName,IFNULL(C.Name,'') as CityName,IFNULL(CM.CountryName,'') as CountryName,'3' as ModuleID,U.UserID as ModuleEntityID,U.UserGUID as ModuleEntityGUID,U.ProfilePicture,PU.Url as ProfileURL, 'user' as EntityType
                FROM " . USERS . " U 
                LEFT JOIN " . USERDETAILS . " UD ON UD.UserID=U.UserID
                LEFT JOIN " . CITIES . " C ON C.CityID=UD.CityID
                LEFT JOIN " . COUNTRYMASTER . " CM ON CM.CountryID=UD.CountryID
                LEFT JOIN " . PROFILEURL . " PU ON PU.EntityID=U.UserID AND PU.EntityType='User'
                " . $user_where . "
            ";

            $page_where = " WHERE EC.ModuleID='18' ";
            if ($pages) {
                $page_where .= " AND PageID IN(" . implode(',', $pages) . ") ";
            }
            $page_sql = "
                SELECT P.Title as FirstName, '' as LastName, '' as CityName,'' as CountryName, '18' as ModuleID,P.PageID as ModuleEntityID,P.PageGUID as ModuleEntityGUID,IF(P.ProfilePicture='',CM.Icon,P.ProfilePicture) as ProfilePicture,CONCAT('page/',P.PageURL) as ProfileURL,'page' as EntityType
                FROM " . PAGES . " P
                LEFT JOIN " . ENTITYCATEGORY . " EC ON EC.ModuleEntityID=P.PageID
                LEFT JOIN " . CATEGORYMASTER . " CMP ON CMP.CategoryID=EC.CategoryID
                LEFT JOIN " . CATEGORYMASTER . " CM ON IF(CMP.ParentID=0,CMP.CategoryID,CMP.ParentID)=CM.CategoryID
                    " . $page_where . "
                GROUP BY P.PageID HAVING ProfilePicture!=''
            ";
            $union_sql = "
                SELECT FirstName,LastName,CityName,CountryName,ModuleID,ModuleEntityID,ModuleEntityGUID,ProfilePicture,ProfileURL,EntityType FROM (
                    " . $user_sql . " UNION ALL " . $page_sql . "
                ) tbl
            ";

            $sql = "";
            if ($pages && $users) {
                $sql = $union_sql;
            } else if ($pages) {
                $sql = $page_sql;
            } else if ($users) {
                $sql = $user_sql;
            }
            if ($sql) {
                $query = $this->db->query($sql);
                //echo $this->db->last_query();
                if ($query->num_rows()) {
                    $d = array();
                    $data = $query->result_array();
                    foreach ($popular_profiles as $key => $val) {
                        foreach ($data as $k => $v) {
                            if ($val['ModuleID'] == $v['ModuleID'] && $val['ModuleEntityID'] == $v['ModuleEntityID']) {
                                $follow = "select FollowID from Follow where (TypeEntityID = " . $v['ModuleEntityID'] . " and UserID = " . $user_id . ") and type='" . $v['EntityType'] . "'";
                                $following = $this->db->query($follow)->num_rows();
                                $followcount = "select COUNT(FollowID) as FollowersCount from Follow where TypeEntityID = " . $v['ModuleEntityID'] . " and type='" . $v['EntityType'] . "'";
                                $followerscount = $this->db->query($followcount)->row()->FollowersCount;
                                if ($following) {
                                    $v['IsFollowing'] = '1';
                                } else {
                                    $v['IsFollowing'] = '0';
                                }
                                if ($followerscount) {
                                    $v['Followers'] = $followerscount;
                                } else {
                                    $v['Followers'] = '0';
                                }

                                if ($user_id != $v['ModuleEntityID']) {
                                    $users_relation = get_user_relation($user_id, $v['ModuleEntityID']);
                                    $privacy_details = $this->privacy_model->details($v['ModuleEntityID']);
                                    $privacy = ucfirst($privacy_details['Privacy']);
                                    if ($privacy_details['Label']) {
                                        foreach ($privacy_details['Label'] as $privacy_label) {
                                            if (isset($privacy_label[$privacy]) && $privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                                $v['ProfilePicture'] = 'user_default.jpg';
                                            }
                                        }
                                    }
                                }

                                $d[] = $v;
                            }
                        }
                    }
                }
            }
        }
        return $d;
    }

    public function get_users_to_follow($user_id, $data = []) {
        
        $order_by_field = (!empty($data['order_by_field'])) ? $data['order_by_field'] : 'U.UserID';
        $order_by = (!empty($data['order_by'])) ? $data['order_by'] : 'DESC';
        $limit = (!empty($data['limit'])) ? $data['limit'] : 2;
        $offset = (!empty($data['offset'])) ? $data['offset'] : 0;
        $interest = isset($Data['Interest']) ? $Data['Interest'] : array() ;
        
        
        $this->load->model(array('users/user_model', 'activity/activity_model'));               
        $rules = $this->activityrule_model->getActivityRules($user_id, NULL, true, NULL);        
        $user_location_details = $this->user_model->get_user_location($user_id);
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $user_interests = $this->get_user_interest($user_id);
        
        
        if(!in_array($order_by, [])) {
            $order_by = 'DESC';
        }
        $allowed_order_fields = ['U.UserID'];
        if(!in_array($order_by_field, $allowed_order_fields)) {
            $order_by_field = 'U.UserID';
        }
        
        
        $this->db->select('U.UserID, U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL');
        $this->db->select('"3" as ModuleID,U.UserGUID as ModuleEntityGUID', FALSE);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(USERS . ' U');
        
        $this->db->join(FOLLOW . '  F', " F.TypeEntityID=U.UserID AND F.Type='user' AND F.StatusID='2' AND F.UserID= $user_id", 'left');
        
        
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');        
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
        
                
        $this->db->where('U.FirstName!=""', null, FALSE);
        $this->db->where('U.LastName!=""', null, FALSE);
        $this->db->where('U.UserID!=', $user_id, FALSE);
        $this->db->where('F.FollowID is NULL', NULL, FALSE);
        //$this->db->where('U.UserID!=', $UID, FALSE);

        $this->db->where_not_in('U.StatusID', array(3, 4));
        
        // Exclude blocked users        
        if ($blocked_users) {
            $this->db->where_not_in('U.UserID', $blocked_users);
        }                
        
        // Check if user belongs to any rule
        if (!empty($rules) && !empty($rules['FinalProfileIDs'])) {
            $profile_ids = $rules['FinalProfileIDs'];
            $this->db->join(USERDETAILS . ' UDCF', "U.UserID = UDCF.UserID AND UDCF.UserID IN($profile_ids)", 'left');
            $this->db->order_by('UDCF.UserID', 'DESC');
        }
        
        // Exclude ignored users
        $select_remove_users = "Select EntityID From " . IGNORE . " Where UserID = $user_id AND EntityType = 'User'";
        $this->db->where("U.UserID NOT IN ($select_remove_users)", NULL, FALSE);        
        
        // Apply join for interest
        if ($interest) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=U.UserID', 'join');
            $this->db->where('EC.ModuleID', '3');
            $this->db->where_in('EC.CategoryID', $interest);
            $this->db->group_by('U.UserID');
        } else if ($user_interests) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=U.UserID', 'left');
            $this->db->where('EC.ModuleID', '3');
            $this->db->where_in('EC.CategoryID', explode(',', $user_interests));
            $this->db->group_by('U.UserID');
        }
        
        // Change order by city        
        if ($user_location_details['City']) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by("C.Name='" . $user_location_details['City'] . "'", 'DESC');
            $this->db->_protect_identifiers = TRUE;
        }
        
        $this->db->order_by($order_by_field, $order_by);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        
        return $query->result_array();
    }
    public function get_users_with_similar_inerest($user_id, $data = []) {

        $order_by_field = (!empty($data['order_by_field'])) ? $data['order_by_field'] : 'U.UserID';
        $order_by = (!empty($data['order_by'])) ? $data['order_by'] : 'DESC';
        $limit = (!empty($data['limit'])) ? $data['limit'] : 2;
        $offset = (!empty($data['offset'])) ? $data['offset'] : 0;


        $this->load->model(array('users/user_model', 'activity/activity_model','category/category_model'));
        //$rules = $this->activityrule_model->getActivityRules($user_id, NULL, true, NULL);
        //$user_location_details = $this->user_model->get_user_location($user_id);
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $user_interests = $this->get_user_interest($user_id);
        $interest = $this->get_user_selected_interest($user_id);

        if(!in_array($order_by, [])) {
            $order_by = 'DESC';
        }
        $allowed_order_fields = ['U.UserID'];
        if(!in_array($order_by_field, $allowed_order_fields)) {
            $order_by_field = 'U.UserID';
        }


        $this->db->select('U.UserID, U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL,U.Gender,UD.TagLine');
        $this->db->select('"3" as ModuleID,U.UserGUID as ModuleEntityGUID', FALSE);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(USERS . ' U');

        $this->db->join(FOLLOW . '  F', " F.TypeEntityID=U.UserID AND F.Type='user' AND F.StatusID='2' AND F.UserID= $user_id", 'left');

        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');


        $this->db->where('U.FirstName!=""', null, FALSE);
        $this->db->where('U.LastName!=""', null, FALSE);
        $this->db->where('U.UserID!=', $user_id, FALSE);
        $this->db->where('F.FollowID is NULL', NULL, FALSE);
        //$this->db->where('U.UserID!=', $UID, FALSE);

        $this->db->where_not_in('U.StatusID', array(3, 4));

        // Exclude blocked users
        if ($blocked_users) {
            $this->db->where_not_in('U.UserID', $blocked_users);
        }

        // Check if user belongs to any rule
        /*if (!empty($rules) && !empty($rules['FinalProfileIDs'])) {
            $profile_ids = $rules['FinalProfileIDs'];
            $this->db->join(USERDETAILS . ' UDCF', "U.UserID = UDCF.UserID AND UDCF.UserID IN($profile_ids)", 'left');
            $this->db->order_by('UDCF.UserID', 'DESC');
        }*/

        // Exclude ignored users
        $select_remove_users = "Select EntityID From " . IGNORE . " Where UserID = $user_id AND EntityType = 'User'";
        $this->db->where("U.UserID NOT IN ($select_remove_users)", NULL, FALSE);

        // Apply join for interest
        if ($interest) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=U.UserID', 'join');
            $this->db->where('EC.ModuleID', '3');
            $this->db->where_in('EC.CategoryID', $interest);
            $this->db->group_by('U.UserID');
            //$this->db->select('EC', FALSE);
        } else if ($user_interests) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=U.UserID', 'left');
            $this->db->where('EC.ModuleID', '3');
            $this->db->where_in('EC.CategoryID', explode(',', $user_interests));
            $this->db->group_by('U.UserID');
        }

        // Change order by city
        /*if ($user_location_details['City']) {
            $this->db->_protect_identifiers = FALSE;
            $this->db->order_by("C.Name='" . $user_location_details['City'] . "'", 'DESC');
            $this->db->_protect_identifiers = TRUE;
        }*/

        $this->db->order_by($order_by_field, $order_by);
        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        //return $query->result_array();
        $final_array = array();
        foreach($query->result_array() as $res) {
            $follow = "select FollowID from Follow where (TypeEntityID = " . $res['UserID'] . " and UserID = " . $user_id . ") and type='User'";
            $following = $this->db->query($follow)->num_rows();
            if ($following) {
                $res['isFollow'] = '1';
            } else {
                $res['isFollow'] = '0';
            }
            $final_array[]=$res;
        }
        return $final_array;
    }

    /**
     * [get_popular_profile]
     * @param  [type]  [$user_id]
     * @param  [type] $limit
     * @param  [type] $offset
     */
    public function get_popular_profile_2($user_id, $UID, $limit = 5, $offset = 0, $OrdBy, $Order) {
        $this->load->model('ignore_model');
        $blockedUsers = blocked_users($user_id);
        $IgnoreUsers = $this->ignore_model->get_ignored_list($user_id, 'User');

        $Users = array();
        $UserGUIDs = array();
        $UserList = array();


        $this->db->select('U.UserID, U.FirstName,U.LastName,U.UserGUID,U.ProfilePicture,PU.Url as ProfileURL');
        $this->db->select('"3" as ModuleID,U.UserGUID as ModuleEntityGUID', FALSE);
        $this->db->select('IFNULL(C.Name,"") as CityName', FALSE);
        $this->db->select('IFNULL(CM.CountryName,"") as CountryName', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(COUNTRYMASTER . ' CM', 'CM.CountryID=UD.CountryID', 'left');
        $this->db->join(PROFILEURL . ' PU', 'PU.EntityID=U.UserID AND PU.EntityType="User"', 'left');
        $this->db->join(FRIENDS . ' FR', 'FR.UserID=U.UserID');
        if ($IgnoreUsers) {
            $this->db->where_not_in('U.UserID', $IgnoreUsers);
        }
        $this->db->where('U.UserID!=', $user_id, FALSE);
        $this->db->where('FR.UserID!=', $user_id, FALSE);
        $this->db->where('U.UserID!=', $UID, FALSE);

        $this->db->where_not_in('U.StatusID', array(3, 4));


        if ($blockedUsers) {
            $this->db->where_not_in('U.UserID', $blockedUsers);
        }
        $this->db->where('FR.Status', '1');
        $this->db->order_by($OrdBy, $Order);
        $this->db->limit($limit, $offset);
        $this->db->group_by("U.UserID");
        $query = $this->db->get(USERS);
        // echo $this->db->last_query();die;
        $limit = $limit - $query->num_rows();
        $UserList = array();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $ul) {
                $follow = "select FollowID from Follow where (TypeEntityID = " . $ul['UserID'] . " and UserID = " . $user_id . ") and type='user'";
                $following = $this->db->query($follow)->num_rows();
                if ($following) {
                    $ul['IsFollowing'] = '1';
                } else {
                    $ul['IsFollowing'] = '0';
                }
                $UserList[] = $ul;
            }
        }


        $user_guids = array();
        $final_user_list = array();
        foreach ($UserList as $key => $val) {
            if (!in_array($val['UserGUID'], $user_guids)) {
                $entity_id = $val['UserID'];
                $UserList[$key]['MutualFriends'] = $this->get_mutual_friend(get_detail_by_guid($val['UserGUID'], 3), $user_id, '', 1);

                $UserList[$key]['ShowFriendsBtn'] = 0;
                $UserList[$key]['FriendStatus'] = 0;

                if ($user_id != $entity_id) {
                    $UserList[$key]['ShowFriendsBtn'] = 1;
                    $UserList[$key]['FriendStatus'] = $this->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request

                    $users_relation = get_user_relation($user_id, $entity_id);
                    $privacy_details = $this->privacy_model->details($entity_id);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label[$privacy])) {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $UserList[$key]['ProfilePicture'] = 'user_default.jpg';
                                }
                                if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $UserList[$key]['ShowFriendsBtn'] = 0;
                                }
                            }
                        }
                    }
                }

                unset($UserList[$key]['UserID']);
                $user_guids[] = $val['UserGUID'];

                $final_user_list[] = $UserList[$key];
            }
        }
        return $final_user_list;
    }

    /**
     * [use to check users following or now]
     * @param  [int]    $Input [loggedin user User ID]
     * @param  [Array]  $Input [friend id]
     */
    /* New function created by gautam - starts */
    function checkFollowStatus($UserID, $FriendID) {
        $following = $this->db->query("select FollowID from Follow where (TypeEntityID = " . $FriendID . " and UserID = " . $UserID . ") and type='user' ")->num_rows();
        if ($following) {
            return 1; // Follow
        } else {
            return 2; // not Follow
        }
    }

    /**
     * [use to get Group MutualFriend records]
     * @param  [int]    $Input [loggedin user User ID]
     * @param  [Array]  $Input [post data]
     */
    function get_metual_friends_group_members($UserID, $data = array()) {
        /* Define variables - starts */
        $Return = array();
        $CaseWhere = '';
        $Input['GroupID'] = isset($data['GroupID']) ? $data['GroupID'] : '';
        $Input['SearchKey'] = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $Input['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $Input['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $Input['Count'] = isset($data['Count']) ? $data['Count'] : '';
        /* Define variables - ends */

        /* Create where case Filter - keyword */
        if (!empty($Input['SearchKey'])) {
            $Input['SearchKey'] = $this->db->escape_like_str($Input['SearchKey']); 
            $CaseWhere .= "
            AND CONCAT_WS(' ',U.FirstName,U.LastName) LIKE '%" . $Input['SearchKey'] . "%' ";
        }
        
        $Input['GroupID'] = $this->db->escape_str($Input['GroupID']);
        
        $sql = "SELECT U.UserID,U.UserGUID,U.FirstName,U.LastName,U.ProfilePicture,PU.Url AS ProfileURL, UD.Address
            FROM Friends AS F1
            JOIN Users AS U ON U.UserID = F1.UserID
            LEFT JOIN `ProfileUrl` PU ON PU.EntityID=U.UserID AND PU.EntityType='User'
            LEFT JOIN `UserDetails` UD ON UD.UserID=U.UserID
            WHERE F1.FriendID = '" . $UserID . "' AND
            F1.UserID IN (SELECT F2.UserID
            FROM GroupMembers AS F2
            WHERE F2.GroupID = " . $Input['GroupID'] . " )
            $CaseWhere ";
        $query = $this->db->query($sql);
        /* Count Total Records - starts */
        $Return['TotalRecords'] = $query->num_rows();
        /* Count Total Records - ends */

        /* Stop code execution and return only totalcount - starts */
        if (isset($Input['Count']) && $Input['Count'] == 1) { /* only returns metual friends count */
            return $data['TotalRecords'];
        }
        /* Stop code execution and return only totalcount - ends */

        /* Add Limit - starts */
        $Offset = $this->get_pagination_offset($Input['PageNo'], $Input['PageSize']);
        $sql .= "
        LIMIT " . $Offset . ", " . $Input['PageSize'] . " ";
        /* Add Limit - ends */

        /* Get Data - starts */
        $users = $this->db->query($sql)->result_array();
        /* Get Data - ends */

        $module_settings = $this->settings_model->getModuleSettings(true);
        $i = 0;
        $output = array();
        foreach ($users as $value) {
            $value['Location'] = $this->user_model->get_user_location($value['UserID']);
            $value['FriendStatus'] = $this->checkFriendStatus($UserID, $value['UserID']);
            $output[$i] = $value;

            if (isset($module_settings['m11']) && !empty($module_settings['m11'])) {
                // Follow Module
                $output[$i]['ShowFollowBtn'] = 1;
                if ($value['UserID'] != $UserID) {
                    $output[$i]['ShowFollowBtn'] = 0;
                }
                /* Follow-or-unfollow-status - starts */
                $output[$i]['FollowStatus'] = 'Follow';
                $following = $this->db->query("select FollowID from Follow where (TypeEntityID = " . $value['UserID'] . " and UserID = " . $value['UserID'] . ") and type='user'")->num_rows();
                if ($following) {
                    $output[$i]['FollowStatus'] = 'Unfollow';
                }
                /* Follow-or-unfollow-status - ends */
            }
            if (isset($module_settings['m11']) && !empty($module_settings['m10'])) {
                $output[$i]['ShowFriendsBtn'] = 1;
            }
            if ($value['UserID'] != $UserID) {
                $output[$i]['ShowFriendsBtn'] = 0;
            }
            unset($output[$i]['UserID']);
            $i++;
        }
        $Return['Data'] = $output;
        return $Return;
    }

    /* New function created by gautam - ends */

    /**
     * [get_friends_of_friends function returns list of friends of friends or boolean if check_status is true]
     * @param  [int]  [$user_id]
     * @param  [int]  [$current_user]
     * @param  [boolean]  [$check_status]
     */
    function get_friends_of_friends($user_id, $current_user, $check_status = false) {
        $this->db->_protect_identifiers = false;
        $this->db->select('FS.UserID');
        $this->db->from(FRIENDS . ' F');
        $this->db->join(FRIENDS . ' FS', 'F.FriendID=FS.FriendID', 'left');
        $this->db->where('FS.UserID!=', $user_id);
        $this->db->where('F.UserID', $user_id);
        $this->db->where('FS.Status', '1');
        $this->db->where('F.Status', '1');
        $this->db->where('FS.FriendID', 'F.FriendID');
        $this->db->group_by('FS.UserID');
        if ($check_status) {
            $this->db->where('FS.UserID', $current_user);
            $this->db->limit(1);
        }
        $this->db->order_by('FS.UserID', 'ASC');
        $query = $this->db->get();
        //echo $this->db->last_query();
        $this->db->_protect_identifiers = true;
        if ($query->num_rows()) {
            if ($check_status) {
                return true;
            } else {
                return $query->result_array();
            }
        } else {
            if ($check_status) {
                return false;
            } else {
                return array();
            }
        }
    }

}
