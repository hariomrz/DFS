<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Friends extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->is_method_allowed();
        $this->load->model(array('users/friend_model', 'subscribe_model', 'notification_model', 'activity/activity_model'));
    }

    /**
     * Function Name: addFriend
     * @param FriendID
     * Description: Send a friend request to another user
     */
    public function addFriend_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/friends/addFriend') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $friend_id = get_detail_by_guid($data['FriendGUID'], 3);
            
            if(check_blocked_user($user_id, 3, $friend_id)){
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'Action not allowed';
                $this->response($return);
            }
            
            if ($user_id == $friend_id) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('invalid_friend_request');
            } else {
                $return['Data']['Status'] = $status = $this->friend_model->sendFriendRequest($user_id, $friend_id);
                $return['Message'] = lang('add_friend_success');
                if ($status == 4) {
                    $return['Message'] = lang('request_sent_success');
                }
                if ($status == 2) {
                    $return['Message'] = lang('request_sent_success');
                }
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: deleteFriend
     * @param FriendID
     * Description: Remove a friend
     */
    public function deleteFriend_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/friends/deleteFriend') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $friend_id = get_detail_by_guid($data['FriendGUID'], 3);
            $this->friend_model->deleteFriend($user_id, $friend_id);
            $return['Message'] = lang('request_delete_success');
        }
        $this->response($return);
    }

    /**
     * Function Name: rejectFriend
     * @param FriendID
     * @return success / failure message and response code
     * Description: Reject friend request
     */
    public function rejectFriend_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/friends/rejectFriend') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $friend_id = get_detail_by_guid($data['FriendGUID'], 3);
            $this->friend_model->deleteFriend($user_id, $friend_id);
            $return['Message'] = lang('request_reject_success');
        }
        $this->response($return);
    }

    /**
     * Function Name: denyFriend
     * @param FriendID
     * @return success / failure message and response code
     * Description: Deny friend request
     */
    public function denyFriend_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/friends/denyFriend') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $friend_id = get_detail_by_guid($data['FriendGUID'], 3);
            $this->friend_model->denyFriend($user_id, $friend_id);
            $return['Message'] = lang('request_denied_success');
        }
        $this->response($return);
    }

    /**
     * Function Name: acceptFriend
     * @param FriendID
     * @return success / failure message and response code
     * Description: Accept friend request
     */
    public function acceptFriend_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/friends/acceptFriend') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $friend_id = get_detail_by_guid($data['FriendGUID'], 3);
            $status = $this->friend_model->sendFriendRequest($user_id, $friend_id);
            $this->subscribe_model->subscribe_email($user_id, $friend_id, 'friend_added');
            $return['Message'] = lang('request_accept_success');
            if ($status == 1) {
                $return['ResponseCode'] = 509;
                $return['Message'] = lang('already_friend');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: getPeopleYouMayKnow
     * @return list of users 
     * Description: return list of user person may know
     */
    public function getPeopleYouMayKnow_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $fields = array('U.FirstName', 'U.LastName', 'U.Email', 'U.UserID', 'U.UserGUID');
        $sort = array('ASC', 'DESC');
        $limit = isset($data['Limit']) ? $data['Limit'] : 5;
        $offset = isset($data['Offset']) ? $data['Offset'] : 0;
        $user_guid = isset($data['UserGUID']) ? get_detail_by_guid($data['UserGUID'], 3) : $user_id;
        $order_by = isset($data['OrdBy']) ? $data['OrdBy'] : $fields[array_rand($fields, 1)];
        $order = isset($data['Order']) ? $data['Order'] : $sort[array_rand($sort, 1)];
        //$return['Data'] = $this->friend_model->getPeopleYouMayKnow($user_id, $user_guid, $limit, $offset, $order_by, $order);
        $return['Data'] = $this->friend_model->getPeopleYouMayKnow($user_id, $user_guid, $limit, $offset, $order_by, $order);
        $return['OrderBy'] = $order_by;
        $return['Order'] = $order;
        $this->response($return);
    }

    /**
     * [get_mutual_friend_post Get list of Mutual Friend]
     * @return [json] [list of Mutual Friend]
     */
    public function getMutualFriend_post() {
        $this->get_mutual_friend_post();
    }

    public function get_mutual_friend_post() {

        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'UserGUID',
                    'label' => 'user guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $current_user_id = $this->UserID;

                $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
                $keyword = isset($data['SearchKey']) ? $data['SearchKey'] : '';
                $count_only = isset($data['Count']) ? $data['Count'] : 0;
                $viewingUserID = isset($data['ViewingUserID']) ? $data['ViewingUserID'] : 0;

                $user_details = get_detail_by_guid($data['UserGUID'], 3, 'UserID, UserID as FriendID, FirstName, LastName, ProfilePicture', 2);

                if (empty($user_details['UserID'])) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "user GUID");
                } else {

                    /* edited by gautam - starts */
                    if ($this->IsApp == 1) {
                        $data['FriendID'] = $user_details['FriendID'];
                        unset($user_details['FriendID']);
                        /* Get Final Records */
                        $Records = $this->friend_model->getMutualFriendNew($this->UserID, $data);
                        if (isset($data['Count']) && $data['Count'] == 1) { /* only returns metual friends count */
                            $return['Data'] = $Records;
                        } else { /* return metual friends array with count */
                            $return['Data']['TotalRecords'] = $Records['TotalRecords'];
                            $return['Data']['Friends'] = $Records['Data'];
                        }
                    } else {
                        $user_id = $user_details['UserID'];
                        //$return['Data'] = $this->friend_model->getMutualFriend($user_id, $current_user_id, $count);
                        $return['Data'] = $this->friend_model->get_mutual_friend($user_id, $current_user_id, $keyword, $count_only, $page_no, $page_size, $viewingUserID);
                        $return['TotalRecords'] = $this->friend_model->get_mutual_friend($user_id, $current_user_id, $keyword, 1, $page_no, $page_size, $viewingUserID);
                        unset($user_details['UserID']);
                        $return['Data']['User'] = $user_details;
                    }
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        /* Final Output */
        $this->response($return);
    }

    public function grow_user_network_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $categories = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : array();
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $user_guid = isset($data['UserGUID']) ? $data['UserGUID'] : '';
        $interest = isset($data['Interest']) ? $data['Interest'] : array();

        $records = $this->friend_model->grow_user_network($user_id, $categories, $page_no, $page_size, $user_guid, $interest);
        $return['Data'] = $records['data'];

        $this->response($return);
    }

    public function grow_group_network_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        /* if($this->friend_model->check_low_connection($user_id))
          { */
        $categories = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : array();
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $records = $this->friend_model->grow_group_network($user_id, $categories, $page_no, $page_size);
        $return['Data'] = $records['data'];
        $return['TotalRecords'] = $records['total_records'];
        /* }
          else
          {
          $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
          $return['Message'] = 'You already have many connections.';
          } */

        $this->response($return);
    }

    /**
     * Function Name: get_new_members
     * @return list of users 
     * Description: return list of new users that signup in last 7 days, added introduction based on the same location and interest
     */
    public function get_new_members_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $limit = isset($data['Limit']) ? $data['Limit'] : 20;
        $offset = isset($data['Offset']) ? $data['Offset'] : 0;
        $return['Data'] = $this->friend_model->get_new_members($user_id, $limit, $offset);
        $this->response($return);
    }

    /**
     * Function Name: get_users_to_follow
     * @return list of users to be followed
     * Description: return list of  users that are not followed yet
     */
    public function get_users_to_follow_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;

        $return['Data'] = $this->friend_model->get_users_to_follow($user_id, $data);
        $this->response($return);
    }

    public function get_users_with_similar_interest_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $return['Data'] = $this->friend_model->get_users_with_similar_inerest($user_id, $data);
        $this->response($return);
    }

    /**
     * Function Name: get_popular_profile
     * @return list of users 
     * Description: return list of popular user based on activity
     */
    public function get_popular_profile_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data = $this->post_data;
        $fields = array('U.FirstName', 'U.LastName', 'U.Email', 'U.UserID', 'U.UserGUID');
        $sort = array('ASC', 'DESC');
        $limit = isset($data['PageSize']) ? $data['PageSize'] : 5;
        $offset = isset($data['PageNo']) ? $data['PageNo'] : 0;
        $user_guid = isset($data['UserGUID']) ? get_detail_by_guid($data['UserGUID'], 3) : $user_id;
        $order_by = isset($data['OrdBy']) ? $data['OrdBy'] : $fields[array_rand($fields, 1)];
        $order = isset($data['Order']) ? $data['Order'] : $sort[array_rand($sort, 1)];
        $return['Data'] = $this->friend_model->get_popular_profile($user_id, $limit, $offset, $order_by, $order);
        $return['OrderBy'] = $order_by;
        $return['Order'] = $order;
        $this->response($return);
    }

    public function is_method_allowed() {
        return;
        if (!$this->settings_model->isDisabled(10)) { // If friend module is enabled then return
            return;
        }

        $method = $this->router->fetch_method();
        $allowed_methods = array(
            'get_users_to_follow',
            'get_popular_profile'
        );

        if (in_array($method, $allowed_methods)) {
            return;
        }

        $this->return['Message'] = 'The resource that is being accessed is blocked';
        $this->return['ResponseCode'] = 508;
        $this->response($this->return);
    }
}

/* End of file friends.php */
/* Location: ./application/controllers/api/friends.php */