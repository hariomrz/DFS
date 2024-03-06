<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Polls extends Common_API_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->check_module_status(30);
        $this->load->model(array('polls/polls_model', 'activity/activity_model', 'users/user_model'));
    }

    /**
     * @Function - function to create poll
     * @Params   - Description(string),Visibility(int),Commentable(boolean),ExpiryDateTime(int),Media(Array),PostAsModuleEntityGUID(int),PostAsModuleID(int),Options(Array)
     * @Output   - JSON
     */
    public function create_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data

        if ($this->form_validation->run('api/polls/add') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $insert = array();
            $insert['Description'] = $data['Description'];
            $visibility = $data['Visibility'];
            $commentable = $data['Commentable'];
            $expire_in_days = !empty($data['ExpiryDateTime']) ? $data['ExpiryDateTime'] : "";
            $poll_cover_media = $data['Media'];

            /* --Converted date from clients timezone to UTC-- */
            $insert['ExpiryDateTime'] = "";
            if (!empty($expire_in_days))
            {
                $insert['ExpiryDateTime'] = get_current_date('%Y-%m-%d %H:%i:%s', $expire_in_days, 1);
            } 
            /* ----------------------------------------------- */

            $entity_tags = isset($data['EntityTags']) ? $data['EntityTags'] : array() ;//Entity Tags for polls
            
            $insert['IsAnonymous'] = isset($data['IsAnonymous']) ? $data['IsAnonymous'] : 0;
            $post_as_module_entity_guid = isset($data['PostAsModuleEntityGUID']) ? $data['PostAsModuleEntityGUID'] : '';
            $post_as_module_id = isset($data['PostAsModuleID']) ? $data['PostAsModuleID'] : 3;
            $poll_for = isset($data['PollFor']) ? $data['PollFor'] : array();
            $module_entity_owner = 0;
            if (!empty($post_as_module_entity_guid) && !empty($post_as_module_id))
            {
                $insert['PostAsModuleID'] = isset($data['PostAsModuleID']) ? $data['PostAsModuleID'] : '';
                $insert['PostAsModuleEntityID'] = '';
                $insert['PostAsModuleEntityID'] = get_detail_by_guid($post_as_module_entity_guid, $post_as_module_id);
                if ($post_as_module_id == '18')
                {
                    $module_entity_owner = 1;
                }
            } else
            {
                $insert['PostAsModuleID'] = 3;
                $insert['PostAsModuleEntityID'] = $this->UserID;
            }

            $options = $data['Options'];
            $return['Data'] = $this->polls_model->create($insert, $options, $visibility, $commentable, $poll_cover_media, $module_entity_owner, $poll_for, $entity_tags);
        }
        $this->response($return);
    }

    /**
     * [get_poll_post get poll list]
     */
    public function get_poll_post()
    {
        $data = $this->post_data; // Get post data
        $return['Data'] = $this->polls_model->get_poll_by_id($data['PollGUID'], 3, $this->UserID);
        $this->response($return);
    }

    /*
      | Function add vote
      | @params : OptionGUID,ModuleEntityGUID,ModuleID
      | @output : JSON
     */

    public function vote_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data

        if ($this->form_validation->run('api/polls/add_vote') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else
        {
            $option_guid = $data['OptionGUID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            $module_id = $data['ModuleID'];

            if(!$module_entity_guid)
            {
                $module_entity_guid = get_detail_by_id($UserID,3);
            }

            if ($this->polls_model->is_poll_expired($option_guid))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'Poll has expired';
            } 
            else
            {
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                $return['Data'] = $this->polls_model->vote($option_guid, $module_entity_id, $module_id);
            }
        }
        $this->response($return);
    }

    /*
      | Function edit poll
      | @params : PollGUID,ExpireDuration
      | @output : JSON
     */

    public function edit_expiry_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data

        if ($this->form_validation->run('api/polls/edit_vote') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else
        {
            $poll_guid = $data['PollGUID'];
            $expire_duration = $data['ExpireDuration'];
            if (!empty($expire_duration))
            {
                $expire_datetime = get_current_date('%Y-%m-%d %H:%i:%s', $expire_duration, 1);
            } else
            {
                $expire_datetime = '';
            }
            $this->polls_model->update_poll_expire_date($poll_guid, $expire_datetime);
            $return['ExpireDatetime'] = $expire_datetime;
        }
        $this->response($return);
    }

    /**
     *
     */
    public function get_future_polls_post()
    {
        $return['MyPolls'] = $this->polls_model->my_poll_activities($this->UserID, '3', TRUE);
        $return['MyVoted'] = $this->polls_model->my_voted_poll_activities($this->UserID, '3', TRUE);
        $this->response($return);
    }

    /*
      | Function get_voters_list
      | @params : PollGUID,ExpireDuration
      | @output : JSON
     */

    public function voters_list_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $UserID = $this->UserID; // Get post data

        $validation_rule[] = array(
            'field' => 'PollGUID',
            'label' => 'PollGUID',
            'rules' => 'required|validate_guid[30]'
        );
        $validation_rule[] = array(
            'field' => 'VisitorModuleID',
            'label' => 'VisitorModuleID',
            'rules' => 'required'
        );
        $validation_rule[] = array(
            'field' => 'VisitorModuleEntityGUID',
            'label' => 'VisitorModuleEntityGUID',
            'rules' => 'required'
        );
        $this->form_validation->set_rules($validation_rule);

        if ($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else
        {
            $poll_guid = $data['PollGUID'];
            $visitor_module_id = $data['VisitorModuleID'];
            $visitor_module_entity_guid = $data['VisitorModuleEntityGUID'];

            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            if (!empty($visitor_module_entity_guid) && !empty($visitor_module_id))
            {
                $visitor_module_entity_id = get_detail_by_guid($visitor_module_entity_guid, $visitor_module_id);
            } else
            {
                $visitor_module_id = 3;
                $visitor_module_entity_id = $this->UserID;
            }
            $return['Data'] = $this->polls_model->voters_list($poll_guid, FALSE, $page_no, $page_size, $visitor_module_id, $visitor_module_entity_id);
            $return['TotalRecords'] = $this->polls_model->voters_list($poll_guid, TRUE, '', '', $visitor_module_id, $visitor_module_entity_id);
        }
        $this->response($return);
    }

    /**
     * Function to get entity list
     * @param : ModuleEntityID,ModuleID
     * @output:JSON
     */
    public function get_entity_list_post()
    {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        $user_id = $this->UserID; // Get post data

        if ($this->form_validation->run('api/polls/get_entity_list') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $module_entity_guid = $data['ModuleEntityGUID'];
            $module_id = $data['ModuleID'];

            $return['Data'] = get_entity_list($user_id, $module_entity_guid, $module_id);
        }
        $this->response($return);
    }

    /**
     * Function Name: index
     * @param ProfileID,PageNomPageSize,ActivityTypeID,EntityID,WallType,ActivityGuID,AllActivity,LoginSessionKey
     * Description: Get list of activity according to input conditions
     */
    public function index_post()
    {
        /* Define variables - starts */
        $Return = $this->return;
        $Data = $this->post_data;
        $UserID = $this->UserID;
        if ($this->form_validation->run('api/activity') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else
        {
            
            $feed_user = isset($Data['FeedUser'][0]) ? $Data['FeedUser'][0] : '';
            
            $EntityGUID = $Data['EntityGUID'];
            $ModuleID = $Data['ModuleID'];
            $EntityID = get_detail_by_guid($EntityGUID, $ModuleID);
            $FeedSortBy = $Data['FeedSortBy'];
            $IsMediaExists = isset($Data['IsMediaExists']) ? $Data['IsMediaExists'] : 2;
            $PageNo = isset($Data['PageNo']) ? $Data['PageNo'] : 1;
            $PageSize = isset($Data['PageSize']) ? $Data['PageSize'] : ACTIVITY_PAGE_SIZE;
            $FilterType = !empty($Data['PollFilterType']) ? $Data['PollFilterType'] : 0;
            $FeedUser = isset($Data['FeedUser']) ? get_detail_by_guid($feed_user, 3) : 0;
            $SearchKey = isset($Data['SearchKey']) ? $Data['SearchKey'] : '';
            $StartDate = (isset($Data['StartDate']) && !empty($Data['StartDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($Data['StartDate'])) : '';
            $EndDate = (isset($Data['EndDate']) && !empty($Data['EndDate'])) ? get_current_date('%Y-%m-%d', 0, 0, strtotime($Data['EndDate'])) : '';
            $ReminderDate = (isset($Data['ReminderFilterDate']) && !empty($Data['ReminderFilterDate'])) ? $Data['ReminderFilterDate'] : array();
            $ActivityGUID = isset($Data['ActivityGUID']) ? $Data['ActivityGUID'] : 0;
            $mentions = isset($Data['Mentions']) ? $Data['Mentions'] : array();
            $expired = isset($Data['expired']) ? $Data['expired'] : '';
            $anonymous = isset($Data['anonymous']) ? $Data['anonymous'] : '';

            $show_archive = isset($Data['ShowArchiveOnly']) ? $Data['ShowArchiveOnly'] : 0;


            if ($mentions)
            {
                foreach ($mentions as $key => $value)
                {
                    $mentions[$key]['ModuleEntityID'] = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
                }
            }
            
            $this->user_model->set_user_time_zone($UserID);
            $this->user_model->set_friend_followers_list($UserID);
            $this->user_model->set_friend_followers_list($UserID);
            $this->user_model->set_user_profile_url($UserID);
            $this->subscribe_model->set_user_subscribed($UserID);            
            $this->favourite_model->set_user_favourite($UserID); 
            $this->flag_model->set_user_flagged($UserID); 
            $this->activity_model->set_user_activity_archive($UserID); 
           $this->activity_model->set_user_tagged($UserID);                 
            
            $FriendFollowersList=$this->user_model->get_friend_followers_list();
                if(!empty($FriendFollowersList)){
                    $this->user_model->set_friends_of_friend_list($UserID,$FriendFollowersList['Friends']);
                }

            $activity = $this->polls_model->getFeedActivities($UserID, $PageNo, $PageSize, $FeedSortBy, $FeedUser, $FilterType, $IsMediaExists, $SearchKey, $StartDate, $EndDate, $show_archive, 0, $ReminderDate, $ActivityGUID, $mentions, $EntityID, $ModuleID, $expired, $anonymous);
            $Return['TotalRecords'] = $this->polls_model->getFeedActivities($UserID, $PageNo, $PageSize, $FeedSortBy, $FeedUser, $FilterType, $IsMediaExists, $SearchKey, $StartDate, $EndDate, $show_archive, 1, $ReminderDate, $ActivityGUID, $mentions, $EntityID, $ModuleID, $expired, $anonymous);
            $Return['TotalFavouriteRecords'] = $this->polls_model->getFeedActivities($UserID, 0, 0, $FeedSortBy, $FeedUser, array(1), $IsMediaExists, $SearchKey, $StartDate, $EndDate, 0, 1, $ReminderDate, $ActivityGUID, $mentions, $EntityID, $ModuleID, $expired, $anonymous);
            $Return['TotalReminderRecords'] = $this->polls_model->getFeedActivities($UserID, 0, 0, $FeedSortBy, $FeedUser, array(3), $IsMediaExists, $SearchKey, $StartDate, $EndDate, 0, 1, $ReminderDate, $ActivityGUID, $mentions, $EntityID, $ModuleID, $expired, $anonymous);

            if (count($activity) > 0)
                $Return['Data'] = $activity;
            $Return['PageSize'] = $PageSize;
            $Return['PageNo'] = $PageNo;
        }
        $Return['LoggedInProfilePicture'] = $this->LoggedInProfilePicture;
        $Return['LoggedInName'] = $this->LoggedInName;
        $this->response($Return);
    }

    /**
     * Function to get polls about to close
     * Param : ModuleEntityGUID(String),ModuleID(Int),PageNo(Int),PageSize(Int)
     * Output: JSON
     */
    public function get_polls_about_to_close_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;
        if ($this->form_validation->run('api/polls/about_to_close') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $Return['Message'] = $error;
        } else
        {
            $entity_guid = $data['ModuleEntityGUID'];
            $module_id = $data['ModuleID'];
            $entity_id = get_detail_by_guid($entity_guid, $module_id);

            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;

            $activity = $this->polls_model->getFeedActivities($user_id, $page_no, $page_size, 1, 0, 15, 2, false, false, false, 0, 0, array(), '', array(), $entity_id, $module_id, array(25));
            $return['Data'] = $activity;
        }
        $this->response($return);
    }

    /**
     * Function to get list of users to invite on poll
     * @internal param $ : PollGUID(String),InviteMembers(Array),InvitedByModuleID(Int),InvitedByModuleEntityID(Int)
     * @return : json
     */
    public function get_users_for_invite_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;

        $keyword = isset($data['Keyword']) ? $data['Keyword'] : '';
        $type = isset($data['Type']) ? $data['Type'] : 'INVITE';

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

        if (!isset($data['PollGUID']))
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->response($return);
        }

        $poll_id = get_detail_by_guid($data['PollGUID'], 30);

        $return['Data'] = $this->polls_model->get_user_for_invite($user_id, $poll_id, $type, $keyword, $page_no, $page_size);
        $return['TotalRecords'] = $this->polls_model->get_user_for_invite($user_id, $poll_id, $type, $keyword, $page_no, $page_size, 1);

        $this->response($return);
    }

    /**
     * Function to get list of group to invite on poll
     * @internal param $ : PollGUID(String),InviteMembers(Array),InvitedByModuleID(Int),InvitedByModuleEntityID(Int)
     * @return : json
     */
    public function get_groups_for_invite_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;

        $keyword = isset($data['Keyword']) ? $data['Keyword'] : '';
        $type = isset($data['Type']) ? $data['Type'] : 'INVITE';

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

        if (!isset($data['PollGUID']))
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->response($return);
        }

        $poll_id = get_detail_by_guid($data['PollGUID'], 30);

        $return['Data'] = $this->polls_model->get_group_for_invite($user_id, $poll_id, $type, $keyword, $page_no, $page_size);
        $return['TotalRecords'] = $this->polls_model->get_group_for_invite($user_id, $poll_id, $type, $keyword, $page_no, $page_size, 1);

        $this->response($return);
    }

    public function get_invite_status_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (!isset($data['PollGUID']))
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->response($return);
        }

        $poll_id = get_detail_by_guid($data['PollGUID'], 30);

        $return['Data'] = $this->polls_model->get_invite_status(3, $user_id, $poll_id);

        $this->response($return);
    }

    public function remind_all_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;

        if (!isset($data['PollGUID']))
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->response($return);
        }

        $poll_id = get_detail_by_guid($data['PollGUID'], 30);

        $this->polls_model->remind(3, $user_id, $poll_id);
    }

    /**
     * Function to invite entity to participate in a poll
     * @internal param $ : PollGUID(String),InviteMembers(Array),InvitedByModuleID(Int),InvitedByModuleEntityID(Int)
     * @return : json
     */
    public function invite_entity_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;

        if (!isset($data['PollGUID']))
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->response($return);
        }

        $invited_by_module_id = isset($data['InvitedByModuleID']) ? $data['InvitedByModuleID'] : 3;
        $invited_by_entity_guid = isset($data['InvitedByEntityGUID']) ? $data['InvitedByEntityGUID'] : '';
        $invited_by_entity_id = $user_id;

        if (!empty($invited_by_entity_guid))
        {
            $invited_by_entity_id = get_detail_by_guid($invited_by_entity_guid, $invited_by_module_id);
        }

        $poll_id = get_detail_by_guid($data['PollGUID'], 30);
        $members = $data['Members'];

        $select_all = isset($data['SelectAll']) ? $data['SelectAll'] : '';

        $this->polls_model->invite_entity($invited_by_module_id, $invited_by_entity_id, $poll_id, $members, $select_all);

        $this->response($return);
    }

    public function remind_invite_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;

        if (!isset($data['PollGUID']))
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $this->response($return);
        }

        $poll_id = get_detail_by_guid($data['PollGUID'], 30);
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '';
        $module_entity_id = isset($data['ModuleEntityGUID']) ? get_detail_by_guid($data['ModuleEntityGUID'], $module_id) : '';

        $this->polls_model->remind_invite($user_id, $poll_id, $module_id, $module_entity_id);

        $this->response($return);
    }

    /**
     * Function to invite friends and groups to participate in a poll
     * @internal param $ : PollGUID(String),InviteMembers(Array),InvitedByModuleID(Int),InvitedByModuleEntityID(Int)
     * @return : json
     */
    public function invite_friends_and_groups_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;
        if ($this->form_validation->run('api/polls/invite_friends_and_groups') == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else
        {
            $invite_members = $data['InviteMembers'];
            $poll_guid = $data['PollGUID'];
            $invited_by_module_id = $data['InvitedByModuleID'];
            $invited_by_module_entity_id = $data['InvitedByModuleEntityID'];
            $is_reminder = !empty($data['IsReminder']) ? $data['IsReminder'] : FALSE;

            $poll_id = get_detail_by_guid($poll_guid, 30);
            if (empty($poll_id))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'Invalid poll guid supplied.';
                $this->response($return);
            }

            $status = $this->poll_model->invite_friends_and_groups($poll_id, $invite_members, $invited_by_module_entity_id, $invited_by_module_id, $is_reminder);
            if ($status)
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'Invalid data for invited members';
                $this->response($return);
            }
        }
        $this->response($return);
    }

    public function get_user_details_post()
    {
        $return = $this->return;
        $data = $this->post_data;

        $user_id = $this->UserID;
        $poll_id = isset($data['PollGUID']) ? get_detail_by_guid($data['PollGUID'], 30) : 0;
        $type = isset($data['Type']) ? $data['Type'] : 'Invited';

        $return['Data'] = $this->polls_model->get_user_details($poll_id, $user_id, $type);

        $this->response($return);
    }

}

/* End of file polls.php */
/* Location: ./application/controllers/polls.php */
