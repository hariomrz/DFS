<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
class Forum extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->module_id = 33;
        $this->check_module_status($this->module_id);
        $this->load->model(array('forum/forum_model', 'users/user_model', 'activity/activity_model', 'notification_model'));
        $this->reserve_forum_url = array('index', 'manage_admin', 'members_settings', 'media', 'files', 'links', 'members');
    }

    /**
     * Function Name: get_categories
     * Description: returns forum categories as per visibility
     */
    function get_categories_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : NULL;
        $pageNo = isset($data['pageNo']) ? $data['pageNo'] : NULL;
        $return['Data'] = $this->forum_model->get_categories($user_id, false, $pageSize, $pageNo);
        $this->response($return);
    }

    /**
     * Function Name: create
     * Description: Create / Edit a Forum
     */
    function create_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */

        /* Gather Inputs - starts */
        $data = $this->post_data;
        if ($data != NULL && isset($data)) {
            /* Define variables - end */
            $user_id = $this->UserID;
            $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
            $name = isset($data['Name']) ? $data['Name'] : '';
            $url = isset($data['URL']) ? trim(strtolower($data['URL'])) : '';
            $is_unique_name = '|is_unique[' . FORUM . '.Name]';
            $is_unique_url = '|is_unique[' . FORUM . '.URL]';

            if ($forum_id) {
                $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
                if (!$permissions['IsAdmin']) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            } else {
                $is_super_admin = $this->user_model->is_super_admin($user_id);
                if (!$is_super_admin) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
            if ($forum_id) {
                $forum_data = get_detail_by_id($forum_id, 33, "Name,URL", $response_type = 2);
                if ($forum_data) {
                    $is_unique_name = '';
                    $is_unique_url = '';
                    if ($forum_data['Name'] != $name) {
                        $is_unique_name = '|is_unique[' . FORUM . '.Name]';
                    }

                    if ($forum_data['URL'] != $url) {
                        $is_unique_url = '|is_unique[' . FORUM . '.URL]';
                    }
                }
            }
            $validation_rule[] = array(
                'field' => 'Name',
                'label' => 'Name',
                'rules' => 'trim|required|min_length[2]|max_length[100]|alpha_numeric_spaces' . $is_unique_name,
                'errors' => array('is_unique' => lang('forum_name_already_exists')),
            );
            $validation_rule[] = array(
                'field' => 'URL',
                'label' => 'URL',
                'rules' => 'trim|required|max_length[40]|alpha_dash' . $is_unique_url,
                'errors' => array('is_unique' => lang('forum_url_already_exists')),
            );
            $validation_rule[] = array(
                'field' => 'Description',
                'label' => 'Description',
                'rules' => 'trim|required|min_length[2]|max_length[200]',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else if (in_array($url, $this->reserve_forum_url)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('forum_url_already_exists');
            } else {
                $input['Name'] = trim($name);
                $input['Description'] = isset($data['Description']) ? $data['Description'] : '';
                $input['URL'] = $url;
                if ($forum_id) {
                    $response = $this->forum_model->update(FORUM, array('ForumID' => $forum_id), $input);
                    $return['Message'] = lang('forum_updated');
                } else {
                    $display_order = 0;
                    $get_forum_order = get_data('MAX(DisplayOrder) as DisplayOrder', FORUM, array(), '1', '');
                    if ($get_forum_order) {
                        $display_order = $get_forum_order->DisplayOrder;
                    }
                    $forum_guid = get_guid();
                    $input['ForumGUID'] = $forum_guid;
                    $input['CreatedBy'] = $user_id;
                    $input['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $input['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $input['StatusID'] = 2;
                    $input['DisplayOrder'] = $display_order + 1;
                    $forum_id = $this->forum_model->create(FORUM, $input);
                    $return['Message'] = lang('forum_created');
                }
                $result = $this->forum_model->lists($user_id, '', '', '', '', '', $forum_id);
                $return['Data'] = $result['Data'];
                if (CACHE_ENABLE) {
                    $this->cache->delete('forum_count');
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: delete
     * Description: Delete a Forum  
     */
    function delete_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $permissions = $this->forum_model->check_forum_permissions($user_id, $data['ForumID'], FALSE);
                if ($permissions['IsSuperAdmin']) {
                    $forum_id = $data['ForumID'];
                    $this->forum_model->update(FORUM, array('ForumID' => $forum_id), array('StatusID' => 3));
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
     * Function Name: details
     * Description: Get forum detail  
     */
    function details_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_id = $data['ForumID'];
                $return['Data'] = $this->forum_model->details($forum_id, $user_id);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: change_order
     * Description: Change forum order  
     */
    function change_order_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $user_id = $this->UserID;
            $is_super_admin = $this->user_model->is_super_admin($user_id);

            if (!$is_super_admin) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
                $this->response($return);
            }
            $order_data = isset($data['OrderData']) ? $data['OrderData'] : '';

            if (!empty($order_data)) {
                $this->forum_model->change_order($order_data);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: list
     * Description: Get all list of forum  
     */
    function list_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* if (isset($data))
          { */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '';
        $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'DisplayOrder';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'ASC';
        $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';

        $result = $this->forum_model->lists($user_id, $search_keyword, $page_no, $page_size, $order_by, $sort_by, $forum_id);
        $return['Data'] = $result['Data'];
        $return['TotalRecords'] = $result['TotalRecords'];

        $this->response($return);
        /* }
          else
          {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
          } */
        $this->response($return);
    }

    /**
     * Function Name: add_admin
     * Description: add admin type user in forum
     */
    function add_admin_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $permissions = $this->forum_model->check_forum_permissions($user_id, $data['ForumID'], FALSE);
                if ($permissions['IsAdmin']) {
                    $forum_id = $data['ForumID'];
                    $members = isset($data['Members']) ? $data['Members'] : array();
                    if (!empty($members)) {
                        $this->forum_model->add_admin($members, $forum_id, $user_id);
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
     * Function Name: create_category
     * Description: Create / Edit a Forum Category
     */
    function create_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Define variables - ends */
        
        $_POST['URL'] = $this->post_data['URL'] = $this->remove_cat_url_special_chars($this->post_data['URL']);                
        
        //echo $_POST['URL']; die;
        
        /* Gather Inputs - starts */
        $data = $this->post_data;
                
        
        if ($data != NULL && isset($data)) {
            /* Define variables - end */
            $user_id = $this->UserID;

            $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
            $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
            $parent_category_id = isset($data['ParentCategoryID']) ? $data['ParentCategoryID'] : 0;
            if ($parent_category_id) {
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $parent_category_id, FALSE);
                if (!$permissions['IsAdmin']) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            } else if ($forum_category_id) {
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if (!$permissions['IsAdmin']) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            } else if ($forum_id) {
                $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
                if (!$permissions['IsAdmin']) {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }

            $validation_rule[] = array(
                'field' => 'ForumID',
                'label' => 'ForumID',
                'rules' => 'trim|required',
            );
            $validation_rule[] = array(
                'field' => 'Name',
                'label' => 'Name',
                'rules' => 'trim|required|max_length[100]|callback_is_valid_cat_name|callback_is_unique_category',
            );
            $validation_rule[] = array(
                'field' => 'URL',
                'label' => 'URL',
                'rules' => 'trim|required|max_length[40]|alpha_dash|callback_is_unique_category_url',
            );
            $validation_rule[] = array(
                'field' => 'Description',
                'label' => 'Description',
                'rules' => 'trim|required|max_length[200]',
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $name = isset($data['Name']) ? $data['Name'] : '';
                $description = isset($data['Description']) ? $data['Description'] : '';
                $url = isset($data['URL']) ? $data['URL'] : '';
                $media_guid = isset($data['MediaGUID']) ? $data['MediaGUID'] : '';
                $visibility = isset($data['Visibility']) ? $data['Visibility'] : 1;
                $is_discussion_allowed = isset($data['IsDiscussionAllowed']) ? $data['IsDiscussionAllowed'] : 1;
                $can_all_member_post = isset($data['CanAllMemberPost']) ? $data['CanAllMemberPost'] : 1;
                $is_featured = isset($data['IsFeatured']) ? $data['IsFeatured'] : 0;
                $forum_id = isset($data['ForumID']) ? $data['ForumID'] : 0;

                $input['Name'] = trim($name);
                $input['Description'] = $description;
                $input['URL'] = trim(strtolower($url));
                $input['MediaGUID'] = $media_guid;
                $input['Visibility'] = $visibility;
                $input['ParentCategoryID'] = $parent_category_id;
                $input['IsDiscussionAllowed'] = $is_discussion_allowed;
                $input['CanAllMemberPost'] = $can_all_member_post;
                
                if($forum_id) {
                    $input['ForumID'] = $forum_id;
                }
                
                
                //$input['IsFeatured']=$is_featured;
                if ($forum_category_id) {
                    //$response = $this->forum_model->update(FORUMCATEGORY,array('ForumCategoryID'=>$forum_category_id),$input);
                    $this->forum_model->create_category($input, $user_id, $forum_category_id);

                    if ($parent_category_id) {
                        $return['Message'] = lang('forum_sub_category_updated');
                    } else {
                        $return['Message'] = lang('forum_category_updated');
                    }
                } else {
                    $display_order = 0;
                    $get_forum_category_order = get_data('MAX(DisplayOrder) as DisplayOrder', FORUMCATEGORY, array('ForumID' => $forum_id), '1', '');
                    if ($get_forum_category_order) {
                        $display_order = $get_forum_category_order->DisplayOrder;
                    }
                    $forum_category_guid = get_guid();
                    $input['ForumID'] = $forum_id;
                    $input['ForumCategoryGUID'] = $forum_category_guid;
                    $input['DisplayOrder'] = $display_order + 1;
                    $input['CreatedBy'] = $user_id;
                    $input['StatusID'] = 2;
                    $input['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $input['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $forum_category_id = $this->forum_model->create_category($input, $user_id);
                    if ($parent_category_id) {
                        $return['Message'] = lang('forum_sub_category_created');
                    } else {
                        $return['Message'] = lang('forum_category_created');
                    }
                }
                $return['Data'] = $this->forum_model->get_forum_category($forum_id, $user_id, $permissions, $forum_category_id);

                // Community synch forum
                $this->load->model(array(
                    'community_server/community_forum_model',
                    'users/community_model'
                ));
                $email_id = $this->community_model->get_email_by_user_id($this->UserID);
                $data = array_merge($data, array('UserEmailID' => $email_id));
                $this->community_forum_model->create_category($data);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); /* Final Output */
    }
    
    function remove_cat_url_special_chars($str) {
        
        $spc_chars = ['(', ')', ':'];
        
        foreach ($spc_chars as $spc_char) {
            $str = str_replace($spc_char, '', $str);
        }
        
        return $str;
    }

    /**
     * Function Name: category_delete
     * Description: Delete a Forum Category  
     */
    function delete_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsSuperAdmin']) {
                    $this->forum_model->update(FORUMCATEGORY, array('ForumCategoryID' => $forum_category_id), array('StatusID' => 3));

                    // Community synch forum
                    $this->load->model(array('community_server/community_forum_model',));
                    $this->community_forum_model->delete_category($data);
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: change_category_order
     * Description: Change order of forum category  
     */
    function change_category_order_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                if ($forum_category_id) {
                    $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                } else {
                    $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
                }

                if ($permissions['IsAdmin']) {
                    $order_data = isset($data['OrderData']) ? $data['OrderData'] : array();
                    if (!empty($order_data)) {
                        $this->forum_model->change_category_order($order_data);

                        // Community synch forum
                        $this->load->model(array('community_server/community_forum_model',));
                        $this->community_forum_model->change_category_order($data);
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: is_unique_category
     * Description: Check unique category  
     * @param type $str
     * @return boolean
     */
    function is_unique_category($str) {
        $forum_category_id = $this->input->post('ForumCategoryID');
        $forum_id = $this->input->post('ForumID');
        $parent_category_id = $this->input->post('ParentCategoryID');
        $this->db->select('ForumCategoryID');
        $this->db->from(FORUMCATEGORY);
        $this->db->where('ForumID', $forum_id);
        $this->db->where('Name', $str);
        $this->db->where('StatusID', 2);
        if ($forum_category_id) {
            $this->db->where_not_in('ForumCategoryID', $forum_category_id);
        }
        if ($parent_category_id) {
            $this->db->where('ParentCategoryID', $parent_category_id);
        }

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('is_unique_category', lang('forum_category_already_exists'));
            return False;
        } else {
            return TRUE;
            ;
        }
    }
    
    function is_valid_cat_name($str) {
        if (!preg_match('/^[a-z0-9 .\-\(\):]+$/i', $str)) {
            $this->form_validation->set_message('is_valid_cat_name', 'Please enter valid category name');
            return False;
        } else {
            return TRUE;            
        }
    }
    
    function is_valid_cat_url($str) {
        if (!preg_match('/^[a-z0-9 .\-\(\):]+$/i', $str)) {
            $this->form_validation->set_message('is_valid_cat_url', 'Please enter valid category url');
            return False;
        } else {
            return TRUE;            
        }
    }

    /**
     * Function Name: change_category_order
     * Description: Check unique category URL 
     * @param type $str
     * @return boolean
     */
    function is_unique_category_url($str) {
        $forum_category_id = $this->input->post('ForumCategoryID');
        $forum_id = $this->input->post('ForumID');
        $this->db->select('ForumCategoryID');
        $this->db->from(FORUMCATEGORY);
        $this->db->where('ForumID', $forum_id);
        $this->db->where('URL', $str);
        $this->db->where('StatusID', 2);
        if ($forum_category_id) {
            $this->db->where_not_in('ForumCategoryID', $forum_category_id);
        }
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $this->form_validation->set_message('is_unique_category_url', lang('forum_category_url_already_exists'));
            return False;
        } else {
            return TRUE;
            ;
        }
    }

    /**
     * Function Name: details
     * Description: Get Category detail  
     */
    function category_details_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = $data['ForumCategoryID'];
                $permissions = $this->forum_model->check_category_visibility($forum_category_id, $user_id);
                if ($permissions) {
                    $return['Data'] = $this->forum_model->category_details($forum_category_id, $user_id);
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
     * Function Name: details
     * Description: Get Category detail  
     */
    function manage_feature_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
                if ($forum_category_id) {
                    $cat_perm = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                } else {
                    $cat_perm['IsAdmin'] = FALSE;
                }
                if ($permissions['IsAdmin'] || $cat_perm['IsAdmin']) {
                    $return['Data'] = $this->forum_model->manage_feature_category($forum_id, $forum_category_id);
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: details
     * Description: Get Category detail  
     */
    function set_feature_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
                $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $feature_data = $data['FeatureData'];
                    if (count($feature_data) > FEATURE_CATEGORY) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('feature_category_max_error');
                        $this->response($return);
                    } else {
                        $this->forum_model->set_feature_category($feature_data, $forum_id);
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: manage_visibility
     * Description: add member in ForumCategoryVisibility
     */
    function add_category_visibility_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $members = isset($data['Members']) ? $data['Members'] : array();
                    if (!empty($members)) {
                        $this->forum_model->add_category_visibility($members, $forum_category_id, $user_id);
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    function remove_category_visibility_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $forum_category_visibility_ids = isset($data['ForumCategoryVisibilityIDs']) ? $data['ForumCategoryVisibilityIDs'] : array();
                    if (!empty($forum_category_visibility_ids)) {
                        $this->forum_model->remove_category_visibility($forum_category_visibility_ids);
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: add_member_category
     * Description: add member in Forum Category
     */
    function add_category_members_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $members = isset($data['Members']) ? $data['Members'] : array();
                    if (!empty($members)) {
                        $this->forum_model->add_category_members($members, $forum_category_id, $user_id);
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: delete_category_visibilty
     * Description: Delete a Forum Category  
     */
    function delete_category_visibilty_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ForumCategoryVisibilityID',
                    'label' => 'ForumCategoryVisibilityID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $this->forum_model->delete(FORUMCATEGORYVISIBILITY, array('ForumCategoryVisibilityID' => $data['ForumCategoryVisibilityID']));
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: admin_suggestion
     * Description: Get Suggested users and groups  
     */
    function admin_suggestion_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
                $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : 20;
                    $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
                    $result = $this->forum_model->admin_suggestion($forum_id, $user_id, $page_no, $page_size, $permissions, $search_keyword);
                    $return['Data'] = $result['Data'];
                    $return['TotalRecords'] = $result['TotalRecords'];
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: delete
     * Description: Delete a Forum  
     */
    function delete_admin_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $permissions = $this->forum_model->check_forum_permissions($user_id, $data['ForumID'], FALSE);
                if ($permissions['IsAdmin']) {
                    $forum_id = $data['ForumID'];
                    $forum_manager_id = isset($data['ForumManagerID']) ? $data['ForumManagerID'] : array();
                    $is_direct = $this->forum_model->check_is_direct($forum_manager_id);
                    if ($is_direct) {
                        $this->forum_model->delete(FORUMMANAGER, array('ForumManagerID' => $forum_manager_id));
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
     * Function Name: admin_suggestion
     * Description: Get Suggested users and groups  
     */
    function manager_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumID',
                    'label' => 'ForumID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
                $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : 20;
                    $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
                    $result = $this->forum_model->manager($forum_id, $user_id, $page_no, $page_size, $permissions, $search_keyword);
                    $return['Data'] = $result['Data'];
                    $return['TotalRecords'] = $result['TotalRecords'];
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: list
     * Description: Get all list of forum  
     */
    function forum_name_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* if (isset($data))
          { */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '';
        $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
        $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'DisplayOrder';
        $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'ASC';
        $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';

        $return['Data'] = $this->forum_model->forum_name($user_id);

        $this->response($return);
        /* }
          else
          {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
          } */
        $this->response($return);
    }

    /**
     * Function Name: forum_category
     * Description: Get Suggested users and groups  
     */
    function forum_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* if (isset($data))
          { */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
        $permissions = $this->forum_model->check_forum_permissions($user_id, $forum_id, FALSE);
        $return['Data'] = $this->forum_model->forum_category_name($forum_id, $user_id, $permissions);

        $this->response($return);
        /* }
          else
          {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
          } */
        $this->response($return);
    }

    /**
     * Function Name: category_visibility_suggestion
     * Description: Get Suggested users and groups  
     */
    function category_visibility_suggestion_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : 20;
                    $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
                    $result = $this->forum_model->category_visibility_suggestion($forum_category_id, $user_id, $page_no, $page_size, $permissions, $search_keyword);
                    $return['Data'] = $result['Data'];
                    $return['TotalRecords'] = $result['TotalRecords'];
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: get_category_visibilty
     * Description: Get Suggested users and groups  
     */
    function get_category_visibilty_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : '';
                    $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
                    $result = $this->forum_model->get_category_visibilty($forum_category_id, $user_id, $page_no, $page_size, $permissions, $search_keyword);
                    $return['Data'] = $result['Data'];
                    $return['TotalRecords'] = $result['TotalRecords'];
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: category_member_suggestion
     * Description: Get Suggested users and groups  
     */
    function category_member_suggestion_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : 20;
                    $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
                    $result = $this->forum_model->category_member_suggestion($forum_category_id, $user_id, $page_no, $page_size, $permissions, $search_keyword);
                    $return['Data'] = $result['Data'];
                    $return['TotalRecords'] = $result['TotalRecords'];
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: category_member_suggestion
     * Description: Get Suggested users and groups  
     */
    function category_members_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $page_no = isset($data['PageNo']) ? $data['PageNo'] : 1;
                $sort_by = isset($data['SortBy']) ? $data['SortBy'] : 'ASC';
                $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '';
                $ExpertOnly = isset($data['ExpertOnly']) ? $data['ExpertOnly'] : false;
                if ($ExpertOnly) {
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : 5;
                    $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'Random';
                } else {
                    $page_size = isset($data['PageSize']) ? $data['PageSize'] : 20;
                    $order_by = isset($data['OrderBy']) ? $data['OrderBy'] : 'Name';
                }
                $result = $this->forum_model->category_members($forum_category_id, $user_id, $page_no, $page_size, array(), $search_keyword, $order_by, $sort_by, $ExpertOnly);
                $return['Data'] = $result['Data'];
                $return['TotalRecords'] = $result['TotalRecords'];
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: set_member_permission
     * @param 
     * Description: Set user permission for category
     */
    function set_member_permission_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule = array(
            array(
                'field' => 'ForumCategoryID',
                'label' => 'ForumCategoryID',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'Key',
                'label' => 'Key',
                'rules' => 'trim|required'
            ),
            array(
                'field' => 'ModuleID',
                'label' => 'ModuleID',
                'rules' => 'trim|required|numeric'
            ),
            array(
                'field' => 'ModuleEntityID',
                'label' => 'ModuleEntityID',
                'rules' => 'trim|required'
            ),
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error; //Shows all error messages as a string
        } else {
            $field = $data['Key'];
            $value = isset($data['Value']) ? $data['Value'] : 0;
            $module_id = $data['ModuleID'];
            $module_entity_id = $data['ModuleEntityID'];
            $forum_category_id = $data['ForumCategoryID'];
            if ($field == 'ModuleRoleID' && $value == 0) {
                $value = 17;
            }
            $this->forum_model->set_member_permission($module_id, $module_entity_id, $field, $value, $forum_category_id, $user_id);
            $return['Message'] = lang('setting_saved');
        }
        $this->response($return);
    }

    /**
     * Function Name: follow_category_post
     * @param 
     * Description: Set user follow category
     */
    function follow_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_member_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsMember']) {
                    $members = array(array('ModuleID' => 3, 'ModuleEntityID' => $user_id));
                    if (!empty($members)) {
                        $this->forum_model->add_category_members($members, $forum_category_id, $user_id);
                    }
                    $category_name = get_detail_by_id($forum_category_id, 34, 'Name', 1);
                    $return['Message'] = 'You are now following ' . $category_name;
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    function remove_category_member_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ModuleID',
                    'label' => 'ModuleID',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'ModuleEntityGUID',
                    'label' => 'ModuleEntityGUID',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '';
                $module_entity_guid = isset($data['ModuleEntityGUID']) ? $data['ModuleEntityGUID'] : '';
                $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
                $permissions = $this->forum_model->check_forum_category_member_permissions($user_id, $forum_category_id, FALSE);
                $this->forum_model->remove_category_member($forum_category_id, $module_id, $module_entity_id);
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: unfollow_category_post
     * @param 
     * Description: Set user follow category
     */
    function unfollow_category_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '';
                $permissions = $this->forum_model->check_forum_category_member_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsMember']) {
                    $members = array('ForumCategoryID' => $forum_category_id, 'ModuleID' => 3, 'ModuleEntityID' => $user_id, 'IsDirect' => 1);
                    $this->forum_model->delete(FORUMCATEGORYMEMBER, $members);
                    if (CACHE_ENABLE) {
                        $this->cache->delete('visible_categories_' . $user_id);
                    }
                    $category_name = get_detail_by_id($forum_category_id, 34, 'Name', 1);
                    $return['Message'] = 'You are no longer following ' . $category_name;
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                    $this->response($return);
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Function Name: save_default_permisson
     * Description: To update default permission for category members 
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

            $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : "";

            $validation_rule = array(
                array(
                    'field' => 'ForumCategoryID',
                    'label' => 'ForumCategoryID',
                    'rules' => 'trim|required'
                ),
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $permissions = $this->forum_model->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
                if ($permissions['IsAdmin']) {
                    $input['param'] = $param;
                    $input['forum_category_id'] = $forum_category_id;
                    $this->forum_model->update_default_permisson($input);
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

    function suggested_articles_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '20';
        $return['Data'] = $this->forum_model->suggested_articles($user_id, $page_no, $page_size);
        $this->response($return); /* Final Output */
    }

    function most_active_users_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '20';
        $return['Data'] = $this->forum_model->most_active_users($user_id, $page_no, $page_size);
        $this->response($return); /* Final Output */
    }

    function featured_activity_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '1';
        $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '0';
        $return['Data'] = $this->activity_model->get_featured_post($user_id, 34, $forum_category_id, $page_no, $page_size);
        $this->response($return); /* Final Output */
    }

    /**
     * Function Name: all_forum_category
     * Description: Get list of all categories of forum 
     */
    function all_forum_category_post() {
        $return = $this->return;
        $data = $this->post_data;
        $forum_id = isset($data['ForumID']) ? $data['ForumID'] : '';
        $return['Data'] = $this->forum_model->all_forum_category_name($forum_id);
        $this->response($return);
    }

    function top_active_user_of_forum_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;

        if (isset($data)) {
            $forum_category_id = isset($data['ForumCategoryID']) ? $data['ForumCategoryID'] : '0';
            if (!is_array($forum_category_id)) {
                $validation_rule = array(
                    array(
                        'field' => 'ForumCategoryID',
                        'label' => 'ForumCategoryID',
                        'rules' => 'trim|required'
                    ),
                );

                $this->form_validation->set_rules($validation_rule);
                if ($this->form_validation->run() == FALSE) {
                    $error = $this->form_validation->rest_first_error_string();
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = $error; //Shows all error messages as a string
                    $this->response($return); /* Final Output */
                }
            } else if (empty($forum_category_id)) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = 'The ForumCategoryID field is required.'; //Shows all error messages as a string
                $this->response($return); /* Final Output */
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return); /* Final Output */
        }

        $page_no = isset($data['PageNo']) ? $data['PageNo'] : '';
        $page_size = isset($data['PageSize']) ? $data['PageSize'] : '20';
        $return['Data'] = $this->forum_model->top_active_users_of_forum($user_id, $page_no, $page_size, $forum_category_id);

        $this->response($return); /* Final Output */
    }

    /*
      url :  get_category_guid_popup
      Description : function to get category guid with name introduction for forum 1 else create
     */
    public function get_category_detail_by_name_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $forum_id = isset($data['ForumID']) ? $data['ForumID'] : 1;
        $name = isset($data['Name']) ? $data['Name'] : 'introduction';
        $data = $this->forum_model->get_category_detail_by_name($forum_id, $name);
        $return['Data'] = $data;
        $this->response($return);
    }

    /*
      url : get_popup_article
      Description : get latest 3 article in introduction category for welcome popup
     */
    public function get_popup_article_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $module_entity_guid = $this->LoggedInGUID;
        $role_id = isset($this->RoleID) ? $this->RoleID : '';
        $feed_sort_by = 2;
        $entity_id = $data['entity_id'];
        $module_id = 34;
        $page_no = 1;
        $page_size = 3;
        $filter_type = 0;
        $is_media_exists = 2;
        $activity_guid = 0;
        $search_keyword = '';
        $start_date = '';
        $end_date = '';
        $feed_user = [];
        $as_owner = '0';
        $activity_type_filter = [];
        $module_entity_id = $data['entity_id'];
        $entity_module_id = 3;
        $comment_id = '';
        $view_entity_tags = 1;
        $role_id = '';
        $post_type = 0;
        $tags = [];
        $this->load->model(array('activity/activity_wall_model'));
        $activity = $this->activity_wall_model->get_activities(
                $entity_id, $module_id, $page_no, $page_size, $user_id, $feed_sort_by, $filter_type, $is_media_exists, $activity_guid, $search_keyword, $start_date, $end_date, $feed_user, $as_owner, false, 'ALL', $activity_type_filter, $module_entity_id, $entity_module_id, $comment_id, $view_entity_tags, $role_id, $post_type, $tags, $data
        );
        $return['Data'] = $activity;
        $this->response($return);
    }

    public function get_only_follow_category_post() {
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_size = isset($data['pageSize']) ? $data['pageSize'] : NULL;
        $page_no = isset($data['pageNo']) ? $data['pageNo'] : NULL;        
        $result = $this->forum_model->lists_follow_only($user_id, $page_no, $page_size);
        $return['Data'] = $result;
        $this->response($return);
    }

    function get_all_visible_categories_get() {
        $return = $this->return;
        $data = $this->post_data;
        $searchKey = isset($data['SearchKeyword'])?$data['SearchKeyword']:'';
        $pageNo = isset($data['pageNo'])?$data['pageNo']:1;
        $pageSize = isset($data['pageSize'])?$data['pageSize']:10;
        $user_id = $this->UserID;
        $return['Data'] = $this->forum_model->get_categories($user_id, true,$pageSize,$pageNo,$searchKey);
        $this->response($return);
    }
}