<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Group extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->check_module_status(1);
        $this->load->model(array('group/group_model', 'category/category_model', 'users/friend_model', 'activity/activity_model', 'favourite_model', 'subscribe_model', 'notification_model'));
    }

    /**
     * Function Name: create group new
     * @param 
     * Description: To create post on feed with group privacy
     */
    function create_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data)) {
            $group_name = isset($data['GroupName']) ? $data['GroupName'] : '';
            $group_description = isset($data['GroupDescription']) ? $data['GroupDescription'] : '';
            $is_public = isset($data['IsPublic']) ? $data['IsPublic'] : 1;
            $status_id = isset($data['StatusID']) ? $data['StatusID'] : 2;
            $group_logo_image = isset($data['GroupLogoImage']) ? $data['GroupLogoImage'] : '';
            $group_cover_image = isset($data['GroupCoverImage']) ? $data['GroupCoverImage'] : '';
            $category_ids = isset($data['CategoryIds']) ? $data['CategoryIds'] : array();
            $sub_category_ids = isset($data['SubCategoryIds']) ? $data['SubCategoryIds'] : array();
            $members = isset($data['Members']) ? $data['Members'] : array();
            $admins = isset($data['Admins']) ? $data['Admins'] : array();
            $add_force_fully = isset($data['AddForceFully']) ? $data['AddForceFully'] : 1;
            $members_count = count($members);
            $admins_count = count($admins);
            $user_id = $this->UserID;
            $type = 'FORMAL';
            $group_guid = get_guid();
            $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
            $total_member = $members_count + $admins_count + 1;
            $is_error = TRUE;
            $allowed_post_type = isset($data['AllowedPostType']) ? $data['AllowedPostType'] : array(1, 2);

            if (!is_array($allowed_post_type)) {
                $allowed_post_type = json_decode($allowed_post_type, true);
            }

            if (isset($data['GroupType']) && !empty($data['GroupType'])) {
                $type = $data['GroupType'];
            }

            if (!is_array($category_ids)) {
                $category_ids = json_decode($category_ids, true);
            }

            if (!is_array($sub_category_ids)) {
                $sub_category_ids = json_decode($sub_category_ids, true);
            }

            $params = "";
            if ($add_force_fully == 0) {
                $member_status_id = 1;
                $added_as = 1;
            } elseif ($add_force_fully == 1) {
                $member_status_id = 2;
                $added_as = 3;
            }

            /* ---Check and set groups members data--- */
            $member_arr = $this->check_groups_and_users($members);
            $members_list = $member_arr['member_list'];
            $members_status = $member_arr['status'];
            /* ---Check and set groups admin data--- */
            $admin_arr = $this->check_groups_and_users($admins);
            $admin_status = $admin_arr['status'];
            $admin_list = $admin_arr['member_list'];

            /* ---Check user premission to create wiki group--- */
            if ($type == 'WIKI' && $this->check_wiki_group_premission($this->RightIDs) == false) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied_wiki_group');
                $this->response($return); /* Final Output */
            }

            if ($members_status == false) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('invalid_group_member');
                $this->response($return); /* Final Output */
            }

            if ($admin_status == false) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('invalid_group_admin');
                $this->response($return); /* Final Output */
            }


            if (empty($group_name)) {
                $type = 'INFORMAL';
                $is_public = 2;
            } else if ($this->form_validation->run('api/group/create') == FALSE) {
                /* Validation - starts */

                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
                $is_error = FALSE;
            }

            if ($is_error) {
                // assign sub category to category
                if (isset($sub_category_ids) && !empty($sub_category_ids)) {
                    $category_ids = $sub_category_ids;
                }

                $input = array(
                    'GroupGUID' => $group_guid,
                    'GroupName' => $group_name,
                    'GroupDescription' => $group_description,
                    'CreatedBy' => $user_id,
                    'CreatedDate' => $created_date,
                    'StatusID' => $status_id,
                    'IsPublic' => $is_public,
                    'MemberCount' => $total_member,
                    'GroupImage' => $group_logo_image,
                    'GroupCoverImage' => $group_cover_image,
                    'CategoryIds' => $category_ids,
                    'LastActivity' => $created_date,
                    'Type' => $type,
                    'AllowedPostType' => $allowed_post_type
                );

                $group_id = '';
                $group = array();
                if (!empty($group_name)) {
                    $group = $this->group_model->check_existing_formal_group($group_name, $user_id, $category_ids);
                } else {
                    $group = $this->group_model->check_existing_informal_group($members_list);
                }
                $is_existing_group = FALSE;
                if (empty($group)) {
                    /* --Create Group-- */
                    $group_id = $this->group_model->create_group($input);

                    if ($admin_list) {
                        $this->group_model->add_members($group_id, $admin_list, TRUE, $member_status_id, $params, $added_as, $this->UserID, FALSE, TRUE);
                    }

                    if ($members_list) {
                        $this->group_model->add_members($group_id, $members_list, FALSE, $member_status_id, $params, $added_as, $this->UserID);
                    }

                    /* Initiate job for group assignment */
                    //initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
                    $group_member[] = array('ModuleID' => 3, 'ModuleEntityID' => $user_id);
                    if (!empty($members_list)) {
                        $group_member = array_merge($group_member, $members_list);
                    }
                    initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $group_id, 'user_id' => $user_id, 'Members' => $group_member, 'Status' => 'Join'));
                    $return['Message'] = lang('group_created') . ' ' . $group_name;
                } else {
                    $is_existing_group = TRUE;
                    if (count($group) == 1 && $type == 'INFORMAL') {
                        $group_id = $group[0]['GroupID'];
                        $group_guid = $group[0]['GroupGUID'];
                        $type = $group[0]['Type'];
                    } else if (count($group) > 0 && $type == 'FORMAL') {
                        $return['Data'] = $group;
                        $return['Message'] = lang('group_already_exist');
                        $return['ResponseCode'] = 509;
                        $this->response($return);
                    } else {
                        $return['Data'] = $group;
                        $return['Message'] = lang('multiple_group_found');
                        $return['ResponseCode'] = 595;
                        $this->response($return);
                    }
                }



                if ($group_id) {
                    /* --Create Group Post-- */
                    $members_count = count($members_list);
                    if ($type == 'FORMAL' && $members_count == 1 && $members_list[0]['ModuleID'] == 1) {
                        $permission = check_group_permissions($user_id, $group_id, FALSE);
                        $canPost = $permission['CanPostOnWall'];
                        if (!$canPost) {
                            $return['ResponseCode'] = 501;
                            $return['Message'] = lang('post_permission_deny');
                            $this->response($return);
                        }
                        if (isset($group[0]['GroupGUID'])) {
                            $data['ModuleID'] = 1;
                            $data['ModuleEntityGUID'] = $group[0]['GroupGUID'];
                        }
                        $return = $this->createGroupPost($data);

                        $return = $this->create_group_url($return, $group_id);

                        $this->response($return); /* Final Output */
                    } else if ($type == 'INFORMAL') {
                        $data['ModuleEntityGUID'] = $group_guid;
                        $data['ModuleID'] = 1;

                        $permission = check_group_permissions($user_id, $group_id, FALSE);
                        $canPost = $permission['CanPostOnWall'];
                        if (!$canPost) {
                            $return['ResponseCode'] = 501;
                            $return['Message'] = lang('post_permission_deny');
                            $this->response($return);
                        }
                        if (isset($group[0]['GroupGUID'])) {
                            $data['ModuleID'] = 1;
                            $data['ModuleEntityGUID'] = $group[0]['GroupGUID'];
                        }
                        $return = $this->createGroupPost($data);

                        $activity_id = 0;
                        if (isset($return['Data'][0]['ActivityGUID'])) {
                            $activity_id = get_detail_by_guid($return['Data'][0]['ActivityGUID']);
                        }
                        if ($is_existing_group) {
                            $members = array_merge($members_list, $admin_list);
                            // $this->group_model->send_instant_group_create_notification($members, $group_id, 'post', $activity_id);
                            initiate_worker_job('send_instant_group_create_notification', array('Members' => $members, 'GroupID' => $group_id, 'Type' => 'post', 'ActivityID' => $activity_id, 'NotificationTypeID' => '', 'AddedBy' => '', 'UserID' => $user_id, 'LoggedInName' => $this->LoggedInName));
                        }


                        $return = $this->create_group_url($return, $group_id);


                        $this->response($return); /* Final Output */
                    } else {
                        $return['ResponseCode'] = self::HTTP_OK;
                        $response_data = array('GroupGUID' => $group_guid, 'GroupID' => $group_id);

                        $response_data = $this->create_group_url($response_data, $group_id);

                        $return['Data'] = $response_data;
                    }
                } else {
                    $return['ResponseCode'] = 509;
                    $return['Message'] = lang('group_exist');
                }
            }
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    function create_group_url($output, $group_id) {
        // Get entity url
        $group_url_details = $this->group_model->get_group_details_by_id($group_id);
        $output['ProfileURL'] = $this->group_model->get_group_url($group_id, $group_url_details['GroupNameTitle'], true, 'index');

        return $output;
    }

    /**
     * Function Name: update group new
     * Description: Edit a group 
     */
    function update_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data)) {

            $group_name = isset($data['GroupName']) ? $data['GroupName'] : '';
            $group_description = isset($data['GroupDescription']) ? $data['GroupDescription'] : '';

            if (empty($group_name) || empty($group_description)) {
                $return['Message'] = "Group Name & Description is Required.";
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->response($return);
            }

            $is_public = isset($data['IsPublic']) ? $data['IsPublic'] : 1;
            $status_id = isset($data['StatusID']) ? $data['StatusID'] : 2;
            $group_logo_image = isset($data['GroupLogoImage']) ? $data['GroupLogoImage'] : '';
            $group_cover_image = isset($data['GroupCoverImage']) ? $data['GroupCoverImage'] : '';
            $category_ids = isset($data['CategoryIds']) ? $data['CategoryIds'] : array();
            $sub_category_ids = isset($data['SubCategoryIds']) ? $data['SubCategoryIds'] : array();
            $members = isset($data['Members']) ? $data['Members'] : array();
            $admins = isset($data['Admins']) ? $data['Admins'] : array();
            $members_count = count($members);
            $admins_count = count($admins);
            $user_id = $this->UserID;
            $type = empty($data['GroupName']) ? 'INFORMAL' : 'FORMAL';
            $group_guid = $data['GroupGUID'];
            $created_date = get_current_date('%Y-%m-%d %H:%i:%s');
            $total_member = $members_count + $admins_count + 1;
            $add_force_fully = isset($data['AddForceFully']) ? $data['AddForceFully'] : 1;
            $is_error = TRUE;

            $group_id = get_detail_by_guid($group_guid, 1);

            if (!$this->group_model->privacy_validation($group_id, $is_public)) {
                $return['Message'] = 'Privacy is not valid.';
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->response($return);
            }

            $allowed_post_type = isset($data['AllowedPostType']) ? $data['AllowedPostType'] : array();

            if (!is_array($allowed_post_type)) {
                $allowed_post_type = json_decode($allowed_post_type, true);
            }


            /* if(isset($data['GroupType']) && !empty($data['GroupType'])){
              $type = $data['GroupType'];
              } */

            if (!is_array($category_ids)) {
                $category_ids = json_decode($category_ids, true);
            }

            if (!is_array($sub_category_ids)) {
                $sub_category_ids = json_decode($sub_category_ids, true);
            }

            $params = "";
            if ($add_force_fully == 0) {
                $member_status_id = 1;
                $added_as = 1;
            } elseif ($add_force_fully == 1) {
                $member_status_id = 2;
                $added_as = 3;
            }

            /* ---Check and set groups members data--- */
            $member_arr = $this->check_groups_and_users($members);
            $members_list = $member_arr['member_list'];
            $members_status = $member_arr['status'];

            /* ---Check and set groups admin data--- */
            $admin_arr = $this->check_groups_and_users($admins, 1);
            $admin_status = $admin_arr['status'];
            $admin_list = $admin_arr['member_list'];

            if ($members_status == false) {
                $return['ResponseCode'] = 509;
                $return['Message'] = lang('invalid_group_member');
                $this->response($return); /* Final Output */
            }

            if ($admin_status == false) {
                $return['ResponseCode'] = 509;
                $return['Message'] = lang('invalid_group_admin');
                $this->response($return); /* Final Output */
            }

            if (is_array($data['CategoryIds'])) {
                $category_ids = $data['CategoryIds'];
            } else {
                $category_ids = json_decode($data['CategoryIds'], true);
            }

            /* Validation - starts */
            if (empty($group_name)) {
                $is_public = 2;
                $type = 'INFORMAL';
                if ($this->form_validation->run('api/group/update_informal') == FALSE) {
                    $error = $this->form_validation->rest_first_error_string();
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = $error;
                    $is_error = FALSE;
                }
            } elseif ($this->form_validation->run('api/group/update') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
                $is_error = FALSE;
            }

            if ($is_error) {
                // assign sub category to category
                if (isset($sub_category_ids) && !empty($sub_category_ids)) {
                    $category_ids = $sub_category_ids;
                }

                /* Define variables - starts */
                $status_id = 2;
                $group_logo_image = '';
                $group_cover_image = '';
                if (isset($data['StatusID'])) {
                    $status_id = $data['StatusID'];
                }
                if (isset($data['GroupLogoImage'])) {
                    $group_logo_image = $data['GroupLogoImage'];
                }
                if (isset($data['GroupCoverImage'])) {
                    $group_cover_image = $data['GroupCoverImage'];
                }

                $group_old_data = get_detail_by_guid($group_guid, 1, 'GroupID, Type', 2);
                $group_old_type = '';
                if (!empty($group_old_data)) {
                    $group_id = $group_old_data['GroupID'];
                    $group_old_type = $group_old_data['Type'];
                    if ($group_old_type == 'INFORMAL') {
                        $parameters[0]['ReferenceID'] = $user_id;
                        $parameters[0]['Type'] = 'User';
                        $parameters[1]['ReferenceID'] = $group_id;
                        $parameters[1]['Type'] = 'Group';
                        $members = $this->group_model->get_group_members_id_recursive($group_id, array(), array());
                        $this->notification_model->add_notification(124, $user_id, $members, $group_id, $parameters);
                    }
                }
                /* Define variables - Ends */

                $input = array(
                    'GroupName' => $group_name,
                    'GroupDescription' => $group_description,
                    'GroupGUID' => $group_guid,
                    'IsPublic' => $is_public,
                    'UserID' => $user_id,
                    'GroupImage' => $group_logo_image,
                    'GroupCoverImage' => $group_cover_image,
                    'CategoryIds' => $category_ids,
                    'StatusID' => $status_id,
                    'CreatedBy' => $user_id,
                    'Type' => $type,
                    'AllowedPostType' => $allowed_post_type
                );
                $response = $this->group_model->update_group($input);

                if ($members_list) {
                    $this->group_model->add_members($group_id, $members_list, FALSE, $member_status_id, $params, $added_as, $this->UserID, TRUE);
                }
                if ($admin_list) {
                    $this->group_model->add_members($group_id, $admin_list, TRUE, $member_status_id, $params, $added_as, $this->UserID, TRUE, TRUE);
                }

                /* Initiate job for group assignment */
                //initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
                initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $group_id, 'user_id' => $user_id, 'Members' => $members_list, 'Status' => 'Join'));

                if ($response == 509) {
                    $return['ResponseCode'] = 509;
                    $return['Message'] = lang('group_exist');
                } else {
                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = lang('group_updated');
                    $response_data = array('GroupGUID' => $response, 'GroupID' => $group_id);

                    $response_data = $this->create_group_url($response_data, $group_id);

                    $return['Data'] = $response_data;
                }
            }
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: list
     * Description: Provide Group listing.
     */
    function list_post() {
        $this->lists_post();
    }

    /**
     * [get_users_groups_post To get user group]
     * @return [json] [description]
     */
    function lists_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
        $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $filter = isset($data['Filter']) ? $data['Filter'] : 'All';
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'LastActivity';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'DESC';
        $category_ids = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : '';

        $user_guid = isset($data['UserGUID']) ? $data['UserGUID'] : '';
        $owner_guids = isset($data['OwnerGUIDs']) ? $data['OwnerGUIDs'] : array();
        $type = isset($data['Type']) ? $data['Type'] : '';
        $visited_user_id = 0;
        if (!empty($user_guid)) {
            $visited_user_id = get_detail_by_guid($user_guid, 3);
            if ($user_id == $visited_user_id) {
                $visited_user_id = 0;
            }
        }
        $privacy_type = -1;


        $return['TotalRecords'] = $this->group_model->lists($user_id, TRUE, $search_keyword, $page_no, $page_size, $filter, $order_by, $sort_by, $category_ids, $privacy_type, 0, $visited_user_id, $owner_guids, $type);
        if (!empty($return['TotalRecords'])) {
            $return['Data'] = $this->group_model->lists($user_id, FALSE, $search_keyword, $page_no, $page_size, $filter, $order_by, $sort_by, $category_ids, $privacy_type, 0, $visited_user_id, $owner_guids, $type);
        } else {
            $return['Message'] = "No Record Found !";
            $return['TotalRecords'] = 0;
            $return['Data'] = array();
            $return['ResponseCode'] = self::HTTP_OK;
        }
        $this->response($return);
    }

    /**
     * [details_post Get the details of a particular group]
     * @return [type] [description]
     */
    function details_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        $group_guid = isset($data['GroupGUID']) ? $data['GroupGUID'] : '';
        $group_id = isset($data['GroupID']) ? $data['GroupID'] : 0;


        if ($this->form_validation->required($group_guid) == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('group_guid_required');
        } else {
            $group_id = ($group_id) ? $group_id : get_detail_by_guid($group_guid, 1);
            $permission = check_group_permissions($user_id, $group_id);
            if ($permission['IsAccess']) {
                $is_admin = $permission['IsAdmin'];
                $is_creator = $permission['IsCreator'];
                $response = $permission['Details'];
                if (isset($response['AllowedPostType'])) {
                    foreach ($response['AllowedPostType'] as $key => $val) {
                        if ($val['Value'] == '7' && !$is_admin) {
                            unset($response['AllowedPostType'][$key]);
                        }
                    }
                }

                $response['AllowedPostType'] = array_values($response['AllowedPostType']);

                unset($permission['Details']);
                $return['Data'] = $response;
                $return['Data']['IsAdmin'] = $is_admin;
                $return['Data']['IsCreator'] = $is_creator;
                $return['Data']['Permission'] = $permission;
                $return['Data']['IsInviteSent'] = $permission['IsInviteSent'];
                //$return['Data']['TotalMembers'] = $this->group_model->total_member($group_id);
                $return['Data']['TotalMembers'] = $this->group_model->members($group_id, $user_id, TRUE, '', '', '', '', 'Name', TRUE,'','');
                $return['Data']['MemberCount'] = $return['Data']['TotalMembers'];
                $return['Data']['CoverImageState'] = get_cover_image_state($user_id, $group_id, 1);
                $return['Data']['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'GROUP', $group_id);
                $return['Data']['IsUserEmailVerified'] = get_detail_by_id($user_id, 3, 'StatusID');
                $return['Data']['LoggedInUserDefaultPrivacy'] = $this->privacy_model->get_default_privacy($user_id);

                $group_url_details = $this->group_model->get_group_details_by_id($group_id, '', $permission);
                $return['Data']['GroupMemberProfileURL'] = $this->group_model->get_group_url($group_id, $group_url_details['GroupNameTitle'], false, 'members');
                $return['Data']['ProfileURL'] = $this->group_model->get_group_url($group_id, $group_url_details['GroupNameTitle'], false, 'index');
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: suggestions
     * Description: Suggest group to the logged in user on the basis of joined group by friends
     */
    function suggestions_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $current_user = $this->UserID;
        $offset = 0;
        $limit = PAGE_SIZE;
        $category_ids = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : array();
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;


        $return['Data'] = $this->group_model->suggestions($current_user, $page_no, $page_size, FALSE, $category_ids);
        $return['TotalRecords'] = $this->group_model->suggestions($current_user, "", "", TRUE, $category_ids);
        $return['Offset'] = $page_no;
        $return['Limit'] = $page_size;

        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: join
     * Description: Join public Group 
     */
    function join_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'GroupGUID',
                    'label' => 'GroupGUID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $group_guid = $data['GroupGUID'];
                $status_id = 2;

                $user_id = $this->UserID;
                $group = get_detail_by_guid($group_guid, 1, 'GroupID, IsPublic', 2);
                if ($group) {
                    $group_id = $group['GroupID'];
                    $group_type = $group['IsPublic'];
                    if ($group_type == 1) {
                        $check_user = $this->group_model->check_membership($group_id, $user_id);

                        $added_as = 2;
                        $added_by = $user_id;

                        if (!$check_user) {
                            $params = "";
                            $members = array(array('ModuleEntityID' => $user_id, 'ModuleID' => 3));
                            $this->group_model->add_members($group_id, $members, FALSE, $status_id, $params, $added_as, $added_by, FALSE, TRUE);

                            //Send Notification         
                            $parameters[0]['ReferenceID'] = $user_id;
                            $parameters[0]['Type'] = 'User';
                            $parameters[1]['ReferenceID'] = $group_id;
                            $parameters[1]['Type'] = 'Group';
                            // $this->notification_model->add_notification(4,$CurrentUser,array($UserID),$GroupID,$parameters);

                            $notification_users = $this->group_model->get_user_ids_to_send_notification($group_id, $user_id, $group_type, $user_id);

                            # send notification to group owners
                            if (!empty($notification_users['groupOwners']))
                                $this->notification_model->add_notification(24, $user_id, $notification_users['groupOwners'], $group_id, $parameters);

                            /* Initiate job for group assignment */
                            //initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
                            initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $group_id, 'user_id' => $user_id, 'Members' => $members, 'Status' => 'Join'));
                            # send notification to group members
                            //if(!empty($notification_users['GroupMembers']))
                            //$this->notification_model->add_notification(24,$current_user,$notification_users['GroupMembers'],$group_id,$parameters);
                            # send notification to friends and followlers if group is public.
                            //if(!empty($notification_users['friendsFollowlers']) && $group_type==1)
                            //$this->notification_model->add_notification(24,$current_user,$notification_users['friendsFollowlers'],$group_id,$parameters);
                        }
                        $return['Message'] = lang('group_joined');
                    }
                    else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = 504;
                    $return['Message'] = 'Record not found';
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [delete, Group owner can delete a group and site admin can block or delete a group with reason]
     */
    function delete_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $action_type = ucfirst($data['ActionType']);
            if (isset($data['Reason']))
                $reason = $data['Reason'];
            else
                $reason = "";

            $config = array(
                array(
                    'field' => 'GroupGUID',
                    'label' => 'GroupGUID',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ActionType',
                    'label' => 'ActionType',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } elseif ($action_type == 'Block' && $reason == '') {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'Block Reason is Required';
            } else {
                $group_guid = $data['GroupGUID'];
                $current_user_id = $this->UserID;
                $group_id = get_detail_by_guid($data['GroupGUID'], 1);

                $permission = check_group_permissions($current_user_id, $group_id, FALSE);
                if ($permission['IsAccess']) {
                    $is_group_owner = $permission['IsCreator'];
                    $is_site_owner = $this->group_model->check_site_owner($current_user_id);
                    if ($is_group_owner || $is_site_owner) {
                        $data = array('GroupID' => $group_id, 'UserID' => $current_user_id, 'isSiteOwner' => $is_site_owner, 'ActionType' => $action_type, 'Reason' => $reason);

                        $result = $this->group_model->delete($data, $user_id);

                        # send notification to group owner
                        if ($action_type == 'Block') {
                            $parameters[0]['ReferenceID'] = $group_id;
                            $parameters[0]['Type'] = 'Group';

                            $group_owner_id = $this->group_model->get_group_owner($group_id);

                            $this->notification_model->add_notification(25, $current_user_id, array($group_owner_id), $group_id, $parameters);
                        }
                        $return['Message'] = $result['Message'];
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * [members_post To get group users]
     * @return [json] [description]
     */
    function members_post() {
        $return = $this->return;
        $data = $this->post_data; // Get post data
        if (isset($data)) {
            $config[] = array(
                'field' => 'GroupGUID',
                'label' => 'group guid',
                'rules' => 'required|validate_guid[1]'
            );

            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) { // Check for empty request
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $user_id = $this->UserID;
                $group_id = get_detail_by_guid($data['GroupGUID'], 1);
                $permission = check_group_permissions($user_id, $group_id, FALSE);
                if ($permission['IsAccess']) {
                    $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
                    $filter = isset($data['Filter']) ? $data['Filter'] : '';
                    $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'Name';
                    $sort_by = isset($data['SortBy']) ? $data['SortBy'] : '';
                    $search_keyword = (!empty($data['SearchKeyword']) ? $data['SearchKeyword'] : '');
                    $return['TotalRecords'] = 0;
                    $return['TotalFriends'] = 0;
                    $return['Data'] = $this->group_model->members($group_id, $user_id, '', $search_keyword, $page_no, $page_size, $filter, $order_by, TRUE, '', $sort_by);
                    if ($page_no == 1) {
                        $return['TotalRecords'] = $this->group_model->members($group_id, $user_id, TRUE, $search_keyword, '', '', $filter, $order_by, TRUE, '', $sort_by);
                        $return['TotalFriends'] = $this->group_model->members($group_id, $user_id, TRUE, $search_keyword, '', '', 'Friends', $order_by, TRUE, '', $sort_by);
                        if (empty($return['TotalRecords'])) {
                            $return['Message'] = "No Record Found !";
                            $return['Data'] = array();
                            $return['TotalRecords'] = 0;
                            $return['ResponseCode'] = self::HTTP_OK;
                        }
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: get_friends_for_invite
     * Description: Get uninvited friends list
     */
    function get_friends_for_invite_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $data = $this->post_data;

        $user_id = $this->UserID;

        $search_key = isset($data['SearchKey']) ? $data['SearchKey'] : '';
        $group_guid = $data['GroupGUID'];
        $limit = $data['Limit'];
        $offset = $data['Offset'];

        $group_id = get_detail_by_guid($group_guid, 1);
        $return['Data'] = $this->group_model->get_friends_for_invite($search_key, $user_id, $group_id, $limit, $offset);
        $return['TotalRecords'] = $this->group_model->get_friends_for_invite($search_key, $user_id, $group_id, 0, 0, 1);
        $this->response($return);
    }

    /**
     * Function Name: invite_users
     * Description: send group join invitation to member
     */
    function invite_users_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $user_id = $this->UserID;

        $group_guid = $data['GroupGUID'];
        $users_guid = $data['UsersGUID'];
        $add_force_fully = $data['AddForceFully'];

        $added_as = 0;
        $added_by = 0;

        if (is_array($data['UsersGUID'])) {
            $users_guid = $data['UsersGUID'];
        } else {
            $users_guid = json_decode($data['UsersGUID']);
        }

        if ($this->form_validation->run('api/group/addMemberToGroup') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } elseif (!$users_guid) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'UsersGUID is Required.';
        } else {
            $params = "";

            if (isset($data['Type'])) {
                $params = $data['Type'];
            }

            if ($add_force_fully == 0) {
                $status_id = 1;
                $return['Message'] = lang('success_invite');
            } elseif ($add_force_fully == 1) {
                $status_id = 2;
                $return['Message'] = lang('member_added');
            }
            $current_user = $this->UserID;
            $group_id = get_detail_by_guid($data['GroupGUID'], 1);
            $is_owner = $this->group_model->is_admin($user_id, $group_id);
            if ($is_owner) {
                foreach ($users_guid as $user_guid) {
                    $user_id = get_detail_by_guid($user_guid, 3);
                    $check_user = $this->group_model->check_membership($group_id, $user_id);
                    $get_status = 0;
                    if ($check_user) {
                        $get_status = $this->group_model->get_group_member_status($group_id, $user_id);
                    }

                    if ($add_force_fully) {
                        $added_as = 3;
                    } else {
                        $added_as = 2;
                    }
                    $added_by = $current_user;

                    if ($user_id != 0) {
                        if ($get_status) {

                            $this->group_model->update_member_to_group($group_id, $user_id, $status_id, $params, $added_as, $added_by);
                        } else {
                            $this->group_model->add_member_to_group($group_id, $user_id, $status_id, $params, $added_as, $added_by);
                        }
                        //Send Notification
                        if ($current_user != $user_id && $status_id == 2 && $add_force_fully == 0) {
                            $parameters[0]['ReferenceID'] = $current_user;
                            $parameters[0]['Type'] = 'User';
                            $parameters[1]['ReferenceID'] = $group_id;
                            $parameters[1]['Type'] = 'Group';

                            $this->notification_model->add_notification(4, $current_user, array($user_id), $group_id, $parameters);
                            $group_type = get_detail_by_guid($group_guid, 1, 'IsPublic');
                            $notification_users = $this->group_model->get_user_ids_to_send_notification($group_id, $user_id, $group_type, $current_user);

                            # send notification to group owners
                            if (!empty($notification_users['groupOwners']))
                                $this->notification_model->add_notification(24, $user_id, $notification_users['groupOwners'], $group_id, $parameters);
                        }
                        elseif ($current_user != $user_id && $status_id == 2 && $add_force_fully == 1) {
                            $parameters[0]['ReferenceID'] = $current_user;
                            $parameters[0]['Type'] = 'User';
                            $parameters[1]['ReferenceID'] = $group_id;
                            $parameters[1]['Type'] = 'Group';
                            $this->notification_model->add_notification(4, $current_user, array($user_id), $group_id, $parameters);
                            $group_type = get_detail_by_guid($group_guid, 1, 'IsPublic');
                            $notification_users = $this->group_model->get_user_ids_to_send_notification($group_id, $user_id, $group_type, $current_user);

                            # send notification to group owners
                            $parameters[0]['ReferenceID'] = $user_id;
                            $parameters[0]['Type'] = 'User';
                            $parameters[1]['ReferenceID'] = $group_id;
                            $parameters[1]['Type'] = 'Group';
                            $parameters[2]['ReferenceID'] = $current_user;
                            $parameters[2]['Type'] = 'User';

                            if (!empty($notification_users['groupOwners']))
                                $this->notification_model->add_notification(83, $user_id, $notification_users['groupOwners'], $group_id, $parameters);
                        }
                        elseif ($status_id == 1) {
                            $parameters[0]['ReferenceID'] = $current_user;
                            $parameters[0]['Type'] = 'User';
                            $parameters[1]['ReferenceID'] = $group_id;
                            $parameters[1]['Type'] = 'Group';
                            $this->notification_model->add_notification(22, $current_user, array($user_id), $group_id, $parameters);
                        }
                    }
                }
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [request_invite_post User can request to join particular Group]
     * @return [type] [description]
     */
    function request_invite_post() {
        $return = $this->return;
        /* Gather Inputs - starts */

        $data = $this->post_data;

        $group_guid = $data['GroupGUID'];
        $user_guid = $data['UserGUID'];
        $group_id = get_detail_by_guid($group_guid, 1);
        $user_id = get_detail_by_guid($user_guid, 3);

        $added_as = 1;
        $added_by = $this->UserID;

        //$this->group_model->add_member_to_group($group_id, $user_id, 18, "", $added_as, $added_by);
        $members = array(array('ModuleEntityID' => $user_id, 'ModuleID' => 3));
        $this->group_model->add_members($group_id, $members, FALSE, 18, "", $added_as, $added_by, FALSE, TRUE);
        $this->response($return);
    }

    /**
     * [cancel_invite_post User can cancel his request to join particular group]
     * @return [type] [description]
     */
    function cancel_invite_post() {
        $return = $this->return;
        /* Gather Inputs - starts */

        $data = $this->post_data;

        $group_guid = $data['GroupGUID'];
        $user_guid = $data['UserGUID'];
        $group_id = get_detail_by_guid($group_guid, 1);
        $user_id = get_detail_by_guid($user_guid, 3);
        $this->group_model->cancel_invitation($group_id, $user_id);
        $this->response($return);
    }

    /**
     * Function Name: accept_deny_request
     * Description: user can accept/deny group invite request
     */
    function accept_deny_request_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;

        $group_guid = $data['GroupGUID'];
        $user_guid = $data['UserGUID'];
        $status_id = $data['StatusID'];


        if ($this->form_validation->run('api/group/groupAcceptDenyRequest') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $current_user = $this->UserID;
            $group_id = get_detail_by_guid($group_guid, 1);
            $user_id = get_detail_by_guid($user_guid, 3);
            $invited = $this->group_model->is_invited($group_id, $user_id);
            if ($invited) {
                $data = array('GroupID' => $group_id, 'UserID' => $user_id, 'StatusID' => $status_id);
                $result = $this->group_model->action_request($data);
                if ($status_id == 2) {
                    $group_type = get_detail_by_guid($group_guid, 1, 'IsPublic');
                    $notification_users = $this->group_model->get_group_members_id_recursive($group_id, array(), array(), TRUE);
                    $parameters[0]['ReferenceID'] = $current_user;

                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $group_id;
                    $parameters[1]['Type'] = 'Group';

                    $invited_by_user = $this->group_model->get_invited_by_user($group_id, $user_id);

                    if ($invited_by_user) {
                        $notification_users[] = $invited_by_user;
                        $notification_users = array_unique($notification_users);
                    }

                    # send notification to group owners
                    if (!empty($notification_users)) {
                        $this->notification_model->add_notification(23, $current_user, $notification_users, $group_id, $parameters);
                    }

                    # send notification to group members
                    /* if(!empty($notification_users['GroupMembers']))
                      $this->notification_model->add_notification(24,$current_user,$notification_users['GroupMembers'],$group_id,$parameters);

                      # send notification to friends and followlers if group is public.
                      if(!empty($notification_users['friendsFollowlers']) && $group_type==1)
                      $this->notification_model->add_notification(24,$current_user,$notification_users['friendsFollowlers'],$group_id,$parameters); */
                }
                $return['Message'] = $result['Message'];
            } else {
                $return['ResponseCode'] = 504;
                $return['Message'] = 'Record not found';
            }
        }
        $this->response($return);
    }

    /**
     * [leave allows user to leave a group]
     */
    function leave_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $group_guid = $data['GroupGUID'];
        $module_entity_id = $data['ModuleEntityGUID'];
        $module_id = $data['ModuleID'];

        if ($this->form_validation->run('api/group/groupDropOutAction') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $current_user = $this->UserID;
            $group_id = get_detail_by_guid($group_guid, 1);
            $module_entity_id = get_detail_by_guid($module_entity_id, $module_id);
            $removed = isset($data['Removed']) ? $data['Removed'] : 0;

            $member_group_role = $this->group_model->get_user_group_role($module_entity_id, $module_id, $group_id);

            if ($member_group_role) {
                $data = array('GroupID' => $group_id, 'ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id, 'memberGroupRole' => $member_group_role);
                $result = $this->group_model->leave($data);
                $return['Message'] = $result['Message'];

                $g_owner = $this->group_model->get_group_owner($group_id);

                if ($g_owner) {
                    if ($module_id == 1) {
                        $members = $this->group_model->get_group_members_id_recursive($group_id);
                    } else {
                        $members = array($module_entity_id);
                    }

                    if (!empty($members)) {
                        /* ------------------Update subscription--------------------- */
                        $status_id = 3;
                        $this->load->model('subscribe_model');
                        /* ---------------------------------------------------------- */

                        /* ----------------------Notification------------------------ */
                        $parameters[0]['Type'] = 'User';
                        $parameters[1]['ReferenceID'] = $group_id;
                        $parameters[1]['Type'] = 'Group';
                        $notification_no = 43;
                        /* ---------------------------------------------------------- */
                        foreach ($members as $key => $member) {
                            $parameters[0]['ReferenceID'] = $member;
                            if ($removed == '1') {
                                $notification_no = 44;
                            }
                            $this->notification_model->add_notification($notification_no, $member, array($g_owner), $group_id, $parameters);
                            $this->subscribe_model->update_subscription($member, 'GROUP', $group_id, $status_id);
                        }
                    }
                }
            } else {
                $return['ResponseCode'] = 504;
                $return['Message'] = 'Record not found';
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: check_all_num
     * @param [String] group_name
     * Description: At least single letter should be there in group Name
     */
    function check_all_num($group_name) {
        if (preg_match("/^([0-9])+$/i", $group_name)) {
            $this->form_validation->set_message('CheckAllNum', lang('invalid_group'));
            return FALSE;
        } else {

            if (preg_match('/[a-zA-Z]/', $group_name)) {
                return TRUE;
            } else {
                $this->form_validation->set_message('CheckAllNum', lang('invalid_group'));
                return FALSE;
            }
        }
    }

    /**
     * [toggle_user_role, Group owner admin/creator can assign/remove role to group member]
     */
    function toggle_user_role_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $role_action = (!empty($data['RoleAction']) ? ucfirst($data['RoleAction']) : '');

        $role_id = (!empty($data['RoleID']) ? $data['RoleID'] : "");

        if ($this->form_validation->run('api/group/toggle_user_role') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } elseif ($role_action == 'Add' && $role_id == '') {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'RoleID is required.';
        } else {
            $entity_guid = $data['EntityGUID'];
            $entity_module_id = isset($data['EntityModuleID']) ? $data['EntityModuleID'] : 3;
            $module_id = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];

            $current_user_id = $this->UserID;
            $group_id = get_detail_by_guid($module_entity_guid, $module_id);
            $entity_id = get_detail_by_guid($entity_guid, $entity_module_id);
            $is_owner = $this->group_model->is_admin($current_user_id, $group_id);
            if ($is_owner) {
                $user_role_id = $this->group_model->get_user_group_role($entity_id, 3, $group_id);
                if ($user_role_id == $role_id && $role_action == 'Add') {
                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = lang('role_already_assigned');
                } else {
                    $IsMember = $this->group_model->check_group_membership($current_user_id, $module_id, $group_id);

                    if ($IsMember) {
                        $data = array('GroupID' => $group_id, 'ModuleEntityID' => $entity_id, 'ModuleID' => $entity_module_id, 'RoleID' => $role_id, 'RoleAction' => $role_action);
                        $result = $this->group_model->toggle_user_role($data, $current_user_id);
                        $return['Message'] = $result['Message'];

                        $status_id = 2;
                        if ($role_action == 'Remove') {
                            $status_id = 3;
                        }
                        $this->load->model('subscribe_model');
                        $this->subscribe_model->update_subscription($entity_id, 'GROUP', $group_id, $status_id, $entity_module_id);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('user_not_exists');
                    }
                }
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * [accept_invite_post User can accept group join invite]
     * @return [type] [description]
     */
    function accept_invite_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $group_guid = $data['GroupGUID'];
        $user_guid = $data['UserGUID'];

        $this->group_model->accept_invite($group_guid, $user_guid, $user_id);

        $this->response($return);
    }

    /**
     * [reject_invite_post User can deny group join invite]
     * @return [type] [description]
     */
    function reject_invite_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        $group_guid = $data['GroupGUID'];
        $user_guid = $data['UserGUID'];

        $this->group_model->reject_invite($group_guid, $user_guid);

        $this->response($return);
    }

    /**
     * Function Name: get_friends_for_group
     * Description: Get List of friends to make them group member or to Send Invitation
     */
    function get_friends_for_group_get() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        $data = $this->post_data;
        $user_id = $this->UserID;

        $search_key = $data['SearchKey'];
        $group_guid = $data['GroupGUID'];
        $friends_only = isset($data['FriendsOnly']) ? $data['FriendsOnly'] : false;
        $group_id = get_detail_by_guid($group_guid, 1);
        $return['Data'] = $this->group_model->get_friends_for_group($search_key, $user_id, $group_id, $friends_only);
        $this->response($return);
    }

    /**
     * [can_post_on_wall_post Owner/Creator/Admin of any module can remove post on wall permission]
     * @return [JSON] [description]
     */
    function can_post_on_wall_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($this->form_validation->run('api/group/can_post_on_wall') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $entity_guid = $data['EntityGUID'];
            $entity_module_id = isset($data['EntityModuleID']) ? $data['EntityModuleID'] : 3;
            $module_id = $data['ModuleID'];
            $module_entity_guid = $data['ModuleEntityGUID'];
            $can_post_on_wall = $data['CanPostOnWall'];

            $current_user_id = $this->UserID;
            $group_id = get_detail_by_guid($module_entity_guid, $module_id);
            $entity_id = get_detail_by_guid($entity_guid, $entity_module_id);

            $is_owner = $this->group_model->is_admin($current_user_id, $group_id);

            if ($is_owner) {
                if (!$permission['DirectGroupMember']) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('user_not_exists');
                } else {
                    $this->group_model->toggle_can_post_on_wall($group_id, $entity_id, $entity_module_id, $can_post_on_wall);
                    $return['Message'] = lang('status_changed_success');
                }
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: get_group_members_count
     * @param GroupID
     * Description: Get the number of members in a particular group
     */
    function get_group_members_count_post() {
        $return = $this->return;
        $data = $this->post_data;
        $input['GroupID'] = $data['GroupID'];
        $input['SearchKey'] = '';
        $return['Data'] = $this->group_model->get_group_members_count($input);
        $this->response($return);
    }

    /**
     *
     */
    public function post_in_group_post() {
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $members = isset($data['Members']) ? $data['Members'] : array();
        $admins = isset($data['Admins']) ? $data['Admins'] : array();
        $user_id = $this->UserID;

        /* ---Check and set groups members data--- */
        $member_arr = $this->check_groups_and_users($members);
        $members_list = $member_arr['member_list'];

        /* ---Check and set groups admin data--- */
        $admin_arr = $this->check_groups_and_users($admins);
        $admin_list = $admin_arr['member_list'];

        $group_guid = isset($data['GroupGUID']) ? $data['GroupGUID'] : '';
        $group_id = get_detail_by_guid($group_guid, 1);
        $data['ModuleEntityGUID'] = $group_guid;
        $data['ModuleID'] = 1;
        $return = $this->createGroupPost($data);

        $activity_id = 0;
        if (isset($return['Data'][0]['ActivityGUID'])) {
            $activity_id = get_detail_by_guid($return['Data'][0]['ActivityGUID']);
        }
        $members = array_merge($members_list, $admin_list);
        // $this->group_model->send_instant_group_create_notification($members, $group_id, 'post', $activity_id);
        initiate_worker_job('send_instant_group_create_notification', array('Members' => $members, 'GroupID' => $group_id, 'Type' => 'post', 'ActivityID' => $activity_id, 'NotificationTypeID' => '', 'AddedBy' => '', 'UserID' => $user_id, 'LoggedInName' => $this->LoggedInName));
        $this->response($return); /* Final Output */
    }

    /**
     * [check_groups_and_users Function to validate group/user guid and set id accordingly]
     * @param  [array] $members [Member array]
     * @return [array]          [description]
     */
    public function check_groups_and_users($members, $keep_logged_in = 0) {
        $members_list = array();
        $members_status = true;
        if (!empty($members)) {
            foreach ($members as $key => $value) {
                if (!empty($value['Type'])) {
                    if ($value['Type'] == "INFORMAL") {
                        foreach ($value['Members'] as $key_informal => $informal_gp_member) {
                            $ModuleEntityID = get_detail_by_guid($informal_gp_member['ModuleEntityGUID'], $informal_gp_member['ModuleID']);
                            if (!empty($ModuleEntityID)) {
                                $informal_gp_member['ModuleEntityID'] = $ModuleEntityID;
                                $members_list[] = $informal_gp_member;
                            } else {
                                $members_status = false;
                                break;
                            }
                        }
                    } else {
                        if (isset($value['ModuleEntityGUID']) && isset($value['ModuleID'])) {
                            $ModuleEntityID = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
                            if (!empty($ModuleEntityID)) {
                                $value['ModuleEntityID'] = $ModuleEntityID;
                                $members_list[] = $value;
                            } else {
                                $members_status = false;
                                break;
                            }
                        } else {
                            $members_status = false;
                            break;
                        }
                    }
                } else {
                    $ModuleEntityID = get_detail_by_guid($value['ModuleEntityGUID'], $value['ModuleID']);
                    if (!empty($ModuleEntityID)) {
                        $value['ModuleEntityID'] = $ModuleEntityID;
                        $members_list[] = $value;
                    } else {
                        $members_status = false;
                        break;
                    }
                }
            }
            $members_list = $this->unique_group_members($members_list, $keep_logged_in);
        }
        return array('member_list' => $members_list, 'status' => $members_status);
    }

    /**
     * [unique_group_members to make group members unique]
     * @param  [array] $members [Group Member]
     * @return [array]          [Unique Group Member]
     */
    public function unique_group_members($members, $keep_logged_in = 0) {
        //return $unique = array_map('unserialize', array_unique(array_map('serialize', $members)));
        $unique_members = array();
        $iterated_members = array();
        if (!empty($members)) {
            foreach ($members as $key => $mem) {
                if ($keep_logged_in) {
                    if (!in_array($mem['ModuleEntityGUID'], $iterated_members)) {
                        $unique_members[] = $mem;
                    }
                } else {
                    if (!in_array($mem['ModuleEntityGUID'], $iterated_members) && $mem['ModuleEntityGUID'] != $this->session->userdata('UserGUID')) {
                        $unique_members[] = $mem;
                    }
                }
                $iterated_members[] = $mem['ModuleEntityGUID'];
            }
        }
        return $unique_members;
    }

    /**
     * [add_member_forcefully_post Used to add member to a group]
     */
    public function add_member_forcefully_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;

        if ($this->form_validation->run('api/group/add_member_forcefully') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $members = $data['UsersGUID'];
            $group_guid = $data['GroupGUID'];
            $add_force_fully = !empty($data['AddForceFully']) ? $data['AddForceFully'] : 0;
            $group_id = get_detail_by_guid($group_guid, 1);
            /* ---Check and set groups members data--- */
            $member_arr = $this->check_groups_and_users($members);
            $members_list = $member_arr['member_list'];
            $members_status = $member_arr['status'];
            if ($members_status == false) {
                $return['ResponseCode'] = 509;
                $return['Message'] = lang('invalid_group_member');
                $this->response($return); /* Final Output */
            }
            $params = "";
            $member_status_id = 1;
            $added_as = 1;
            if ($add_force_fully == 1) {
                $added_as = 3;
                $member_status_id = 2;
            }
            $this->group_model->add_members($group_id, $members_list, FALSE, $member_status_id, $params, $added_as, $this->UserID, FALSE, TRUE);
            if ($member_status_id == 2) {
                /* Initiate job for group assignment */
                //initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
                initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $group_id, 'user_id' => '', 'Members' => $members_list, 'Status' => 'Join'));
            }
            if (count($members_list) > 1)
                $return['Message'] = lang('members_added_success');
            else
                $return['Message'] = lang('member_added_success');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * [check_wiki_group_premission Function is used to check user wiki group creation premission]
     * @param  [array] $RightIDs [User right ids]
     * @return Boolean TRUE/FALSE
     */
    public function check_wiki_group_premission($RightIDs) {
        $rights_id = getRightsId('group_wiki_creation');
        if (!in_array($rights_id, $RightIDs)) {
            return false;
        }
        return true;
    }

    /*
      |--------------------------------------------------------------------------
      | Use to createGroupPost
      | @Inputs: LoginSessionKey, ModuleID, ModuleEntityGUID, Visibility, Commentable
      |--------------------------------------------------------------------------
     */

    function createGroupPost($Data) {
        $Return = $this->return;
        $UserID = $this->UserID;

        if (isset($Data)) {
            $validation_rule = $this->form_validation->_config_rules['api/activity/createWallPost'];

            if (isset($Data['PostContent']) == '') {
                $validation_rule[] = array(
                    'field' => 'Media[]',
                    'label' => 'wall media',
                    'rules' => 'trim|required'
                );
            }
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $Return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $Return['Message'] = $error;
                $this->response($Return); /* Final Output */
            } else { /* Validation - ends */

                $this->load->model('users/user_model');
                $this->load->model('pages/page_model');
                $this->load->model('media/media_model');

                $Visibility = $Data['Visibility'];
                $Commentable = $Data['Commentable'];
                $ModuleEntityGUID = $Data['ModuleEntityGUID'];

                $PostContent = isset($Data['PostContent']) ? $Data['PostContent'] : '';
                $ModuleID = 1;
                $Media = isset($Data['Media']) ? $Data['Media'] : array();
                $files = isset($Data['Files']) ? $Data['Files'] : array();
                $Links = isset($Data['Links']) ? $Data['Links'] : array(); //added                 
                $entity_tags = isset($Data['EntityTags']) ? $Data['EntityTags'] : array();
                $IsMediaExist = ($Media) ? '1' : '0';
                $AllActivity = (!empty($Data['AllActivity']) ? $Data['AllActivity'] : 0);
                $ModuleEntityOwner = isset($Data['ModuleEntityOwner']) ? $Data['ModuleEntityOwner'] : 0;
                $NotifyAll = isset($Data['NotifyAll']) ? $Data['NotifyAll'] : 0;
                $ModuleEntityID = get_detail_by_guid($ModuleEntityGUID, $ModuleID);
                $PostAsModuleID = 3;
                $PostAsModuleEntityID = $UserID;
                $post_title = isset($Data['PostTitle']) ? $Data['PostTitle'] : '';
                $post_type = isset($Data['PostType']) ? $Data['PostType'] : 1;
                $summary = isset($Data['Summary']) ? $Data['Summary'] : '';
                if ($post_type != '4') {
                    $summary = '';
                }

                if (isset($Data['ActivityGUID']) && !empty($Data['ActivityGUID'])) {
                    $activity_guid = $Data['ActivityGUID'];
                    $activity_id = get_detail_by_guid($activity_guid);
                } else {
                    $activity_id = '';
                }
                $status = isset($Data['Status']) ? $Data['Status'] : 2;
                $is_anonymous = isset($Data['IsAnonymous']) ? $Data['IsAnonymous'] : 0;

                // Send 1 if draft post is going to be published else FALSE
                $publish_post = isset($Data['PublishPost']) ? $Data['PublishPost'] : FALSE;


                $canPost = TRUE;
                /* if($ModuleID == 1)
                  {
                  $canPost = $this->group_model->new_can_post_on_wall($ModuleEntityID, $UserID, 1);
                  } */
                if ($canPost) {
                    $media_cout = $files_count = 0;
                    if (isset($Media)) {
                        $media_cout = $media_cout + count($Media);
                    }
                    if (isset($files)) {
                        $files_count = $files_count + count($files);
                    }
                    //$PostContent = preg_replace('#&lt;script(.*?)&gt;(.*?)­&lt;/script&gt;#is', '', $PostContent);
                    //Insert post and get post id
                    $PostAsModuleID = 3;
                    $PostAsModuleEntityID = $UserID;

                    $analytic_login_id = get_analytics_id($Data[AUTH_KEY]);
                    //print_r($PostContent);
                    $ActivityDetails = $this->activity_model->createPost($PostContent, $ModuleID, $ModuleEntityID, $UserID, $IsMediaExist, $media_cout, $Visibility, $Commentable, $ModuleEntityOwner, $NotifyAll, $Links, $files_count, $entity_tags, $PostAsModuleID, $PostAsModuleEntityID, $post_title, $post_type, $Media, $files, $activity_id, $is_anonymous, $status, $publish_post, $analytic_login_id, '', array(), '', 0, $summary);

                    $activity_id = $ActivityDetails['ActivityID'];
                    $subscribe_action = $ActivityDetails['subscribe_action'];
                    $excluded_mentioned_users = (isset($ActivityDetails['excluded_mentioned_users']) && !empty($ActivityDetails['excluded_mentioned_users'])) ? $ActivityDetails['excluded_mentioned_users'] : array();
                    $StatusID = 2;

                    /* Media will be deleted if draft post updated OR published */

                    if (($status == 10 && !empty($Data['ActivityGUID'])) || $publish_post == 1) {
                        $this->activity_model->delete_row(MEDIA, array('MediaSectionReferenceID' => $activity_id, 'StatusID' => 10));
                    }


                    if (!empty($Media)) {
                        $AlbumName = DEFAULT_WALL_ALBUM;
                        if (count($Media) == 1) {
                            $MediaGUID = $Media[0]['MediaGUID'];
                            $Media_type = get_media_type($MediaGUID);
                            if ($Media_type == 2) {
                                $AlbumName = DEFAULT_WALL_ALBUM;
                            }
                        }

                        $AlbumID = get_album_id($UserID, $AlbumName, $ModuleID, $ModuleEntityID);

                        $this->media_model->updateMedia($Media, $activity_id, $UserID, $AlbumID);
                        if ($this->activity_model->check_media_pending_status($activity_id)) {
                            $StatusID = 1;
                            $this->activity_model->change_activity_status($activity_id, 1);
                        }
                    }

                    if (!empty($files)) {
                        $AlbumName = DEFAULT_FILE_ALBUM;

                        $AlbumID = get_album_id($UserID, $AlbumName, $ModuleID, $ModuleEntityID);

                        $this->media_model->updateMedia($files, $activity_id, $UserID, $AlbumID);
                        if ($this->activity_model->check_media_pending_status($activity_id)) {
                            $StatusID = 1;
                            $this->activity_model->change_activity_status($activity_id, 1);
                        }
                    }

                    if ($StatusID == 2) {
                        $this->subscribe_model->subscribe_email($UserID, $activity_id, $subscribe_action);
                    }

                    // Send notification only if activity status is active
                    if ($StatusID == 2) {
                        if ($ModuleID == 1) {
                            $ActivityTypeID = 7;
                        } elseif ($ModuleID == 3) {
                            if ($UserID == $ModuleEntityID) {
                                $ActivityTypeID = 1;
                            } else {
                                $ActivityTypeID = 8;
                            }
                        } elseif ($ModuleID == 14) {
                            $ActivityTypeID = 11;
                        } elseif ($ModuleID == 18) {
                            $ActivityTypeID = 12;
                        }
                        initiate_worker_job('send_post_notification', 
                                    array(
                                        'UserID' => $UserID, 
                                        'PostContent' => $PostContent, 
                                        'ActivityTypeID' =>$ActivityTypeID, 
                                        'ActivityID' => $activity_id, 
                                        'ModuleID' => $ModuleID, 
                                        'ModuleEntityID' => $ModuleEntityID, 
                                        'AfterProcess' => 0, 
                                        'PostAsModuleID' => 0, 
                                        'PostAsModuleEntityID' => 0, 
                                        'ExcludedUsers' => $excluded_mentioned_users, 
                                        'PostType' => 0, 
                                        'NotifyAll' => $NotifyAll
                                    ));                        
                    }

                    if ($ModuleID != 3) { // Update last activity date
                        $this->load->helper('activity');
                        set_last_activity_date($ModuleEntityID, $ModuleID);
                    }
                    initiate_worker_job('activity_cache', array('ActivityID' => $activity_id));
                    $Return['Data'] = $this->activity_model->getSingleUserActivity($UserID, $activity_id, $AllActivity);

                    // Update current history
                    $UpdateHistory = array();
                    if (!empty($Return['Data'][0]['Album'])) {
                        $Media = json_encode($Return['Data'][0]['Album']);
                        $UpdateHistory['Media'] = $Media;
                    }
                    if (!empty($Return['Data'][0]['Files'])) {
                        $Files = json_encode($Return['Data'][0]['Files']);
                        $UpdateHistory['Files'] = $Files;
                    }

                    if (!empty($UpdateHistory) && $status == 2)
                        $this->activity_model->update_row(ACTIVITYHISTORY, array('StatusID' => 2, 'ActivityID' => $activity_id), $UpdateHistory);

                    return $Return;
                }
                else {
                    $Return['ResponseCode'] = 201;
                    $Return['Message'] = lang('post_permission_deny');
                    $this->response($Return); /* Final Output */
                }
            }
        } else {
            $Return['ResponseCode'] = 500;
            $Return['Message'] = lang('input_invalid_format');
            $this->response($Return); /* Final Output */
        }
    }

    function insert_all_group_member_post() {
        //$this->group_model->insert_all_group_member(10, 1, 2);
        $this->group_model->get_all_groups_associate_user();
    }

    /* ------------------------------------------
      | @Method : To get category list
      | @Params : ModuleID(Int),categoryLevelID(Int)
      | @Output : array
      ------------------------------------------ */

    public function get_group_categories_post() {
        $data = $this->post_data; // Get post data
        $validation_rule = $this->form_validation->_config_rules['api/category/get_categories'];
        $user_id = $this->UserID;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) { // Check for empty request
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 500;
            $return['Message'] = $error;
        } else {
            $parent_category_id = (!empty($data['categoryLevelID']) ? $data['categoryLevelID'] : 0);
            $data = $this->category_model->get_group_categories($data['ModuleID'], $parent_category_id, $user_id);
            $return['ResponseCode'] = self::HTTP_OK;
            $return['Message'] = lang('success');
            $return['ServiceName'] = 'category/get_categories';
            $return['Data'] = $data;
        }
        $this->response($return);  // Final Output 
    }

    /**
     * Function Name: get_discover_list
     * Description: Used to get list of popular post, popular group, popular member(priority friend) based on category 
     */
    function get_discover_list_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        $parent_id = isset($data['ParentID']) ? $data['ParentID'] : 0;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 1;
        $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $filter = isset($data['Filter']) ? $data['Filter'] : '';
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'CategoryID';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'DESC';
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

        $return['Data'] = $this->group_model->discover_list($module_id, $search_keyword, $filter, $order_by, $sort_by, $page_no, $page_size, $parent_id, $user_id);
        $this->response($return); /* Final Output */
    }

    function top_group_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data)) {
            $user_guid = isset($data['UserGUID']) ? $data['UserGUID'] : '';
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : 5;
            $filter = isset($data['Filter']) ? $data['Filter'] : '';
            $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'LastActivity';
            $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'DESC';
            $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';

            $visited_user_id = $user_id;
            if ($this->LoggedInGUID != $user_guid) {
                $visited_user_id = get_detail_by_guid($user_guid, 3);
            }

            $return['TotalRecords'] = $this->group_model->top_group($user_id, $visited_user_id, TRUE, $search_keyword, $page_no, $page_size, $filter, $order_by, $sort_by);
            if (!empty($return['TotalRecords'])) {
                $return['Data'] = $this->group_model->top_group($user_id, $visited_user_id, FALSE, $search_keyword, $page_no, $page_size, $filter, $order_by, $sort_by);
            }
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: set_member_permission
     * @param 
     * Description: Set user permission for group
     */
    function set_member_permission_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule = $this->form_validation->_config_rules['api/group/set_member_permission'];
        if (($this->IsApp == 1)) {
            $validation_rule[] = array(
                'field' => 'Permission[]',
                'label' => 'permission',
                'rules' => 'trim|required'
            );
        } else {
            $validation_rule[] = array(
                'field' => 'Key',
                'label' => 'permission',
                'rules' => 'trim|required'
            );
        }

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $module_id = $data['ModuleID'];
            $module_entity_id = $data['ModuleEntityID'];
            $group_id = $data['GroupID'];
            if (($this->IsApp == 1)) {
                $permissions = isset($data['Permission']) ? $data['Permission'] : array();
                foreach ($permissions as $key => $permission) {
                    $field = $permission['Key'];
                    $value = isset($permission['Value']) ? $permission['Value'] : 0;
                    if ($field == 'ModuleRoleID') {
                        if (empty($value)) {
                            $value = 6;
                        } else {
                            $value = 5;
                        }
                    }
                    $this->group_model->set_member_permission($module_id, $module_entity_id, $field, $value, $group_id, $user_id);
                }
            } else {
                $field = $data['Key'];
                $value = isset($data['Value']) ? $data['Value'] : 0;
                if ($field == 'ModuleRoleID') {
                    if (empty($value)) {
                        $value = 6;
                    } else {
                        $value = 5;
                    }
                }
                $this->group_model->set_member_permission($module_id, $module_entity_id, $field, $value, $group_id, $user_id);
            }
            $return['Message'] = lang('setting_saved');
        }

        $this->response($return);
    }

    /**
     * Function Name: popular_discussion
     * @param ModuleID,ModuleEntityID
     * Description: Get list of popular discussions for particular group
     */
    public function popular_discussion_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 1;
        $module_entity_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : 0;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : 2;

        $return['Data'] = $this->activity_model->get_popular_discussions($user_id, $module_id, $module_entity_id, 7, $page_no, $page_size);
        $this->response($return);
    }

    /**
     * Function Name: similar_discussion
     * @param ModuleID,PageNo,PageSize,EntityID
     * Description: Get list of activity according to input conditions
     */
    public function similar_discussion_post() {
        /* Define variables - starts */
        $return = $this->return;
        $return['TotalRecords'] = 0; /* added by gautam */
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/group/similar_discussion') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $entity_id = $data['EntityID']; // Group ID
            $module_id = $data['ModuleID'];
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : ACTIVITY_PAGE_SIZE;
            $post_type = isset($data['PostType']) ? $data['PostType'] : 1;
            $activity_guid = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : '';
            $this->activity_model->set_block_user_list($user_id, 3);
            $this->activity_model->set_user_activity_archive($user_id);

            $module_entity_guid = $this->LoggedInGUID;
            $login_type = 'user';
            $entity_module_id = 1;
            if ($login_type == 'user') {
                $entity_module_id = 3;
            }

            if (!$page_size) {
                $page_size = ACTIVITY_PAGE_SIZE;
            }

            $module_entity_id = get_detail_by_guid($module_entity_guid, $entity_module_id);

            if ($module_id == '34') {
                $activity = $this->activity_model->similar_forum_discussion($entity_id, $module_id, $page_no, $page_size, $user_id, false, $module_entity_id, $entity_module_id, $post_type, $activity_guid);

                if ($page_no == '1') {
                    $return['TotalRecords'] = $this->activity_model->similar_forum_discussion($entity_id, $module_id, 0, 0, $user_id, true, $module_entity_id, $entity_module_id, $post_type, $activity_guid);
                }
            } else {
                $activity = $this->activity_model->similar_discussion_posts($entity_id, $module_id, $page_no, $page_size, $user_id, false, $module_entity_id, $entity_module_id, $post_type, $activity_guid);

                if ($page_no == '1') {
                    $return['TotalRecords'] = $this->activity_model->similar_discussion_posts($entity_id, $module_id, 0, 0, $user_id, true, $module_entity_id, $entity_module_id, $post_type, $activity_guid);
                }
            }

            /* Define variables - ends */
            if (count($activity) > 0)
                $return['Data'] = $activity;
            $return['PageSize'] = $page_size;
            $return['PageNo'] = $page_no;
        }
        //$this->output->enable_profiler(true);
        $this->response($return);
    }

    /**
     * Function Name: Group Member Sugestion
     * @param ModuleID,PageNo,PageSize,EntityID
     * Description: Get list of friends to add them in a group
     */
    public function group_member_suggestion_post() {
        /* Define variables - starts */
        $return = $this->return;
        $return['TotalRecords'] = 0; /* added by gautam */
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/group/group_member_suggestion') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $entity_id = $data['ModuleEntityID']; // Group ID
            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 1;
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

            $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, true, 1);
            $friends = $friend_followers_list['Friends'];

            if (!empty($friends)) {
                $friends_list = implode(",", $friends);

                $group_categories = $this->group_model->get_group_categories($entity_id);

                $categories = array();

                if (!empty($group_categories)) {
                    $categories[] = $group_categories['CategoryID'];

                    if (!empty($group_categories['SubCategory'])) {
                        $categories[] = $group_categories['SubCategory']['CategoryID'];
                    }
                }

                $group_ids = $this->group_model->get_groups_by_categories($categories, $user_id);

                $members = $this->group_model->group_member_suggestion($user_id, $entity_id, $page_no, $page_size, FALSE, $group_ids, $categories, $friends_list);


                if ($page_no == '1') {
                    $return['TotalRecords'] = $this->group_model->group_member_suggestion($user_id, $entity_id, $page_no, $page_size, TRUE, $group_ids, $categories, $friends_list);
                }

                if (count($members) > 0)
                    $return['Data'] = $members;
                $return['PageSize'] = $page_size;
                $return['PageNo'] = $page_no;
            }
        }

        //$this->output->enable_profiler(true);
        $this->response($return);
    }

    /**
     * Function Name: group_setting
     * Description: To update group setting 
     */
    function save_group_setting_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data)) {


            $is_public = isset($data['IsPublic']) ? $data['IsPublic'] : 1;
            $user_id = $this->UserID;
            $allowed_post_type = isset($data['AllowedPostType']) ? $data['AllowedPostType'] : array();

            if (!is_array($allowed_post_type)) {
                $allowed_post_type = json_decode($allowed_post_type, true);
            }

            $landing_page = isset($data['LandingPage']) ? $data['LandingPage'] : 'Wall';

            $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : "";
            $module_entity_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : "";

            if (!$this->group_model->privacy_validation($module_entity_id, $is_public)) {
                $return['Message'] = 'Privacy is not valid.';
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->response($return);
            }

            if ($this->form_validation->run('api/group/gruop_setting') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $is_group_owner = $this->group_model->is_admin($user_id, $module_entity_id);

                if ($is_group_owner) {
                    $input['IsPublic'] = $is_public;
                    $input['ModuleEntityID'] = $module_entity_id;
                    $input['AllowedPostType'] = $allowed_post_type;
                    $input['LandingPage'] = $landing_page;

                    $this->group_model->update_group_setting($input);

                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = lang('group_setting_saved');
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = 500;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: get post types
     * @param 
     * Description: get Allowed post types for users
     */
    function get_post_content_types_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/group/get_post_content_types') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {

            $module_id = $data['ModuleID'];
            $module_entity_id = $data['ModuleEntityID'];
            $Data = $this->group_model->get_post_permission($module_entity_id);
            $return['Data'] = $Data;
        }
        $this->response($return);
    }

    /**
     * Function Name: get allowed group types
     * @param 
     * Description: Get Post Content Type for Group
     */
    function get_allowed_group_types_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $Data = $this->group_model->get_group_permission($user_id);
        $return['Data'] = $Data;
        $this->response($return);
    }

    /**
     * Function Name: get_group_post_permission
     * @param 
     * Description: Get group post permission
     */
    function get_group_post_permission_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $group_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : 0;
        $group_id = get_detail_by_guid($group_guid, 1);

        $is_admin = $this->group_model->is_admin($user_id, $group_id);
        $d = $this->group_model->get_post_permission($group_id);

        if ($d && !$is_admin) {
            foreach ($d as $key => $val) {
                if ($val['Value'] == 7) {
                    unset($d[$key]);
                }
            }
        }

        $d = array_values($d);
        $return['Data'] = $d;
        $this->response($return);
    }

    function similar_groups_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $category_id = isset($data['CategoryID']) ? $data['CategoryID'] : 0;
        $group_id = isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : 0;
        $categories = $this->group_model->get_subcategories($category_id);

        $return['Data'] = $this->group_model->similar_groups($user_id, FALSE, 1, 2, $categories, $group_id, TRUE);

        $this->response($return);
    }

    /**
     * [get_users_groups_post To get user group]
     * @return [json] [description]
     */
    function category_group_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;

        $category_ids = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : '';

        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : '';
        $order = isset($data['Order']) ? $data['Order'] : '';

        $return['TotalRecords'] = $this->group_model->category_group($user_id, TRUE, $page_no, $page_size, $category_ids, -1, FALSE, $order_by, $order);
        if (!empty($return['TotalRecords'])) {
            $return['Data'] = $this->group_model->category_group($user_id, FALSE, $page_no, $page_size, $category_ids, -1, TRUE, $order_by, $order);
        } else {
            $return['Message'] = "No Record Found !";
            $return['TotalRecords'] = 0;
            $return['Data'] = array();
            $return['ResponseCode'] = self::HTTP_OK;
        }
        $this->response($return);
    }

    /**
     * Function Name: group_setting
     * Description: To update default permission for group members 
     */
    function save_default_permisson_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data)) {
            $user_id = $this->UserID;
            $param = isset($data['Param']) ? $data['Param'] : array();

            if (is_array($param)) {
                $param = json_encode($param, true);
            }

            $group_id = isset($data['GroupID']) ? $data['GroupID'] : "";

            if ($this->form_validation->run('api/group/save_default_permisson') == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $is_group_owner = $this->group_model->is_admin($user_id, $group_id);

                if ($is_group_owner) {
                    $input['param'] = $param;
                    $input['group_id'] = $group_id;

                    $this->group_model->update_default_permisson($input);

                    $return['ResponseCode'] = self::HTTP_OK;
                    $return['Message'] = lang('setting_saved');
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    function featured_activity_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '1';
        $group_id = isset($data['GroupID']) ? $data['GroupID'] : '0';
        $return['Data'] = $this->activity_model->get_featured_post($user_id, 1, $group_id, $page_no, $page_size);
        $this->response($return); /* Final Output */
    }

    /**
     * [get_users_groups_post To get user group]
     * @return [json] [description]
     */
    function group_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        //MyGroupAndJoined
        //Manage
        //Join

        $filter_created = 'Manage';
        $filter_joined = 'Join';
        $page_no = 1;
        $page_size = 2;
        $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $filter = isset($data['Filter']) ? $data['Filter'] : 'All';
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'LastActivity';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'DESC';
        $category_ids = isset($data['CategoryIDs']) ? $data['CategoryIDs'] : '';

        $user_guid = isset($data['UserGUID']) ? $data['UserGUID'] : '';
        $owner_guids = isset($data['OwnerGUIDs']) ? $data['OwnerGUIDs'] : array();
        $type = isset($data['Type']) ? $data['Type'] : '';
        $visited_user_id = 0;
        if (!empty($user_guid)) {
            $visited_user_id = get_detail_by_guid($user_guid, 3);
            if ($user_id == $visited_user_id) {
                $visited_user_id = 0;
            }
        }
        $privacy_type = -1;

        $return['Data']['Created'] = $this->group_model->lists($user_id, FALSE, $search_keyword, $page_no, $page_size, $filter_created, $order_by, $sort_by, $category_ids, $privacy_type, 0, $visited_user_id, $owner_guids, $type);
        $return['Data']['Joined'] = $this->group_model->lists($user_id, FALSE, $search_keyword, $page_no, $page_size, $filter_joined, $order_by, $sort_by, $category_ids, $privacy_type, 0, $visited_user_id, $owner_guids, $type);
        $return['Data']['All'] = $this->group_model->lists($user_id, FALSE, $search_keyword, $page_no, $page_size, $filter, $order_by, $sort_by, $category_ids, $privacy_type, 0, $visited_user_id, $owner_guids, $type);

        if (empty($return['Data']['Created']) && empty($return['Data']['Joined']) && empty($return['Data']['All'])) {
            $return['Message'] = "No Record Found !";
            $return['TotalRecords'] = 0;
            $return['Data'] = array();
            $return['ResponseCode'] = self::HTTP_OK;
        }
        $this->response($return);
    }
}
