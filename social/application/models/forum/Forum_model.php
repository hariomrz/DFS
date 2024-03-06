<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Forum_model extends Common_Model {

    protected $module_id = '';
    protected $user_page_list = array();
    protected $feed_page_condition = '';
    protected $user_category_list = array();
    protected $visible_category_list = array();

    public function __construct() {
        parent::__construct();
        $this->module_id = 33;
        $this->load->model(array('group/group_model', 'activity/activity_model', 'notification/notification_model', 'users/user_model'));
    }

    /**
     * Function: get_categories
     * Description : return categories as per user visibility
     * @param type $user_id
     * @return array[]
     */
    function get_categories($user_id, $all = false, $pageSize = NULL, $pageNo = NULL,$searchKey='') {
        $this->db->select('FC.*,FC.CreatedBy,CONCAT(U.FirstName," ",U.LastName) AS CreatedByName, IFNULL(M.ImageName,"") as ProfilePicture, M.MediaGUID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(FORUM . ' F', 'FC.ForumID = F.ForumID AND F.StatusID=2 AND F.Visible=1');
        $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID', 'left');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.StatusID', 2);
        if (!$all) {
            $this->db->where('FC.ParentCategoryID', 0);
        } else {
            $this->db->where('FC.IsDiscussionAllowed', 1);
        }
        if(!empty($searchKey)){
            $this->db->where("FC.Name LIKE '%" . $searchKey . "%'", NULL, FALSE);
        }
        
        if ($user_id) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                      WHEN 
                        FC.Visibility=2
                        THEN 
                        ( CASE 
                          WHEN FCV.ModuleID = 3 
                              THEN FCV.ModuleEntityID = " . $user_id . "         
                          WHEN FCM.ModuleID = 3 
                              THEN FCM.ModuleEntityID = " . $user_id . "          
                          ELSE
                          '' 
                          END 
                        )
                    ELSE
                    true 
                    END 
                ";

            $this->db->where($condition, NULL, FALSE);
        } else {
            $this->db->where('FC.Visibility', '1');
        }
        $this->db->group_by('FC.ForumCategoryID');
        $this->db->order_by('FC.DisplayOrder', 'ASC');
        if ($pageSize) {
            $this->db->limit($pageSize, getOffset($pageNo, $pageSize));
        }
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $final_array = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $result) {
                $result['NoOfDiscussions'] = $result['NoOfDiscussions'] + $this->get_discussion_count($result['ForumCategoryID']);
                $result['FullURL'] = $this->get_category_url($result['ForumCategoryID']);
                $result['ImageServerPath'] = IMAGE_SERVER_PATH;
                if ($pageSize)
                    $result['Permissions'] = $this->check_forum_category_permissions($user_id, $result['ForumCategoryID'], FALSE);
                $final_array[] = $result;
            }
        }
        return $final_array;
    }

    /**
     * Function: Create
     * Description : insert data
     * @param type $table
     * @param type $input
     * @return type
     */
    function create($table, $input) {
        $forum_id = $this->insert($table, $input);
        $forum_member = array();
        $forum_member['ForumID'] = $forum_id;
        $forum_member['ModuleID'] = 3;
        $forum_member['ModuleEntityID'] = $input['CreatedBy'];
        $forum_member['ModuleRoleID'] = 10;
        $forum_member['AddedBy'] = $input['CreatedBy'];
        $forum_member['IsDirect'] = 0;
        $forum_member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $this->insert(FORUMMANAGER, $forum_member);
        initiate_worker_job('create_forum_superadmin', array('ForumID' => $forum_id, 'CreatedBy' => $input['CreatedBy']));
        return $forum_id;
    }

    /**
     * Function: add_member
     * Description : insert data in forum manager table
     * @param type $forum_member
     * @return type
     */
    function add_admin($forum_members, $forum_id, $user_id) {
        $add_member_array = array();
        $notification_members = array();
        foreach ($forum_members as $member) {
            $item = array();
            $this->db->select('ForumManagerID');
            $this->db->from(FORUMMANAGER);
            $this->db->where('ForumID', $forum_id);
            $this->db->where('ModuleID', $member['ModuleID']);
            $this->db->where('ModuleEntityID', $member['ModuleEntityID']);
            $query = $this->db->get();
            if (!$query->num_rows()) {
                $item['ForumID'] = $forum_id;
                $item['ModuleID'] = $member['ModuleID'];
                $item['ModuleEntityID'] = $member['ModuleEntityID'];
                $item['ModuleRoleID'] = 12;
                $item['AddedBy'] = $user_id;
                $item['IsDirect'] = 1;
                $item['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $add_member_array[] = $item;

                if ($member['ModuleID'] == 1) {
                    $group_members = array();
                    $group_members = $this->group_model->get_group_members_id_recursive($member['ModuleEntityID']);
                    if (!empty($group_members)) {
                        foreach ($group_members as $key => $g_member) {
                            $notification_members[] = $g_member;
                        }
                    }
                } else {
                    $notification_members[] = $member['ModuleEntityID'];
                }
            }
        }
        initiate_worker_job('create_forum_category_superadmin', array('ForumID' => $forum_id, 'Members' => $forum_members, 'CreatedBy' => $user_id));
        if (!empty($add_member_array)) {
            $this->db->insert_batch(FORUMMANAGER, $add_member_array);
            //print_r($notification_members);die;
            if (!empty($notification_members)) {
                $parameters[0]['ReferenceID'] = $forum_id;
                $parameters[0]['Type'] = 'Forum';
                $this->notification_model->add_notification(130, $user_id, $notification_members, $forum_id, $parameters);
            }
        }
    }

    /**
     * Function: Update
     * Description : Update data
     * @param type $table
     * @param type $where
     * @param type $data
     * @return type
     */
    function update($table, $where, $data) {
        return $this->update_row($table, $where, $data);
    }

    /**
     * Function: Delete
     * Description : Delete Row
     * @param type $table
     * @param type $where
     */
    function delete($table, $where) {
        $this->db->where($where);
        $this->db->delete($table);
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function details($forum_id, $user_id) {
        $this->db->select('F.ForumID,F.Name, F.Description,F.CreatedDate,F.CreatedBy,CONCAT(U.FirstName," ",U.LastName) AS CreatedByName,F.StatusID,F.URL', FALSE);
        $this->db->from(FORUM . ' F');
        $this->db->join(USERS . ' U', 'F.CreatedBy = U.UserID');
        $this->db->where('ForumID', $forum_id);
        $query = $this->db->get();
        $result = array();

        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $permission = $this->check_forum_permissions($user_id, $forum_id, FALSE);
            unset($permission['Details']);
            $result['Permissions'] = $permission;
        }
        return $result;
    }

    /**
     *  Function Name: change_order
     * Description: update forum order
     * @param type $order_data
     * @return boolean
     */
    function change_order($order_data) {
        $orderValue = array();
        foreach ($order_data as $items) {
            $itemArray = array();
            $itemArray['ForumID'] = $items['ForumID'];
            $itemArray['DisplayOrder'] = $items['DisplayOrder'];
            $orderValue[] = $itemArray;
        }

        $this->db->update_batch(FORUM, $orderValue, 'ForumID');
        return true;
    }

    /**
     * [get_forum_list]
     * @param  [int]  $user_id
     */
    function get_forum_list($user_id) {
        $data = array();
        $this->db->select('ForumCategoryID');
        $this->db->from(FORUMCATEGORYMEMBER);
        $this->db->where('ModuleID', 3);
        $this->db->where('ModuleEntityID', $user_id);
        $query = $this->db->get();

        if ($query->num_rows()) {
            foreach ($query->result_array() as $q) {
                $data[] = $q['ForumCategoryID'];
            }
        }
        return $data;
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function lists($user_id, $search, $page_no, $page_size, $order_by, $sort_by, $forum_id = 0) {
        $Return = array();
        $Return['Data'] = array();
        $Return['TotalRecords'] = '';
        $this->db->select('F.ForumID,F.ForumGUID,F.Name, F.Description,F.NoOfDiscussions,IFNULL(F.URL,"") as URL ,F.CreatedDate,F.CreatedBy,CONCAT(U.FirstName," ",U.LastName) AS CreatedByName,F.DisplayOrder', FALSE);
        $this->db->from(FORUM . ' F');
        $this->db->join(USERS . ' U', 'F.CreatedBy = U.UserID');
        $this->db->where('F.StatusID', 2);
        
        if ($forum_id) {
            $this->db->where('F.ForumID', $forum_id);
        }else{
            $this->db->where('F.Visible', 1);
        }

        if ($page_size) {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }
        $this->db->order_by('DisplayOrder', 'ASC');
        if ($search) {
            $search = $this->db->escape_like_str($search);
            $this->db->where("F.Name LIKE '%" . $search . "%' OR F.Description LIKE '%" . $search . "%' ", NULL, FALSE);
        }
        if ($page_no == '' || $page_no == 1) {
            $tempdb = clone $this->db;
            $temp_q = $tempdb->get();
            $Return['TotalRecords'] = $temp_q->num_rows();
        }

        $query = $this->db->get();
        $final_array = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $follow_category = array();
                $unfollow_category = array();
                $category_data = array();
                $permission = $this->check_forum_permissions($user_id, $row['ForumID'], FALSE);
                unset($permission['Details']);
                $row['NoOfMembers'] = $this->forum_members_count($row['ForumID']);
                $MemberData = $this->forum_recent_post_owner($row['ForumID'], $user_id);
                $row['Members'] = $MemberData['Data'];
                $row['MembersCount'] = $MemberData['TotalRecords'];
                $row['Permissions'] = $permission;
                $row['FeatureCategoryData'] = ''; //$this->get_feature_category($row['ForumID'],$user_id,$permission); 

                $all_categories = $this->get_forum_category($row['ForumID'], $user_id, $permission);
                if (!empty($all_categories)) {
                    foreach ($all_categories as $category) {
                        if ($category['Permissions']['IsMember']) {
                            $follow_category[] = $category;
                        } else {
                            $unfollow_category[] = $category;
                        }
                    }
                    if (!empty($follow_category) && count($unfollow_category) < 3) {
                        foreach ($follow_category as $key => $f_category) {
                            $unfollow_category[] = $f_category;
                            unset($follow_category[$key]);
                            if (count($unfollow_category) >= 3) {
                                break;
                            }
                        }
                    }
                } else if (!($permission['IsAdmin']) && !($permission['IsSuperAdmin'])) {
                    continue;
                }
                $row['CategoryData'] = $unfollow_category;
                $row['CategoryFollow'] = array_values($follow_category);
                $row['expandCat'] = 0;
                $final_array[] = $row;
            }
        }
        $Return['Data'] = $final_array;
        return $Return;
    }

    function lists_follow_only($user_id, $pageNo = 1, $pageSize = 20) {
        $this->db->select('FC.*,FC.CreatedBy,CONCAT(U.FirstName," ",U.LastName) AS CreatedByName, IFNULL(M.ImageName,"") as ProfilePicture, M.MediaGUID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(FORUM . ' F', 'FC.ForumID = F.ForumID AND F.StatusID=2');
        $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID', 'left');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.StatusID', 2);

        $this->db->where('FC.ParentCategoryID', 0);
        $this->db->where('FCM.ModuleEntityID', $user_id);
        $this->db->where('FCM.ModuleID', 3);

        if ($user_id) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                      WHEN 
                        FC.Visibility=2
                        THEN 
                        ( CASE 
                          WHEN FCV.ModuleID = 3 
                              THEN FCV.ModuleEntityID = " . $user_id . "         
                          WHEN FCM.ModuleID = 3 
                              THEN FCM.ModuleEntityID = " . $user_id . "          
                          ELSE
                          '' 
                          END 
                        )
                    ELSE
                    true 
                    END 
                ";

            $this->db->where($condition, NULL, FALSE);
        } else {
            $this->db->where('FC.Visibility', '1');
        }
        $this->db->group_by('FC.ForumCategoryID');
        $this->db->order_by('FC.DisplayOrder', 'ASC');
        if ($pageSize) {
            $this->db->limit($pageSize, getOffset($pageNo, $pageSize));
        }
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $final_array = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $result) {
                $result['NoOfDiscussions'] = $result['NoOfDiscussions'] + $this->get_discussion_count($result['ForumCategoryID']);
                $result['FullURL'] = $this->get_category_url($result['ForumCategoryID']);
                $result['Permissions'] = $this->check_forum_category_permissions($user_id, $result['ForumCategoryID'], FALSE);
                $final_array[] = $result;
            }
        }
        return $final_array;
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function manage_feature_category($forum_id, $forum_category_id = 0) {

        $this->db->select('FC.ForumCategoryID,FC.Name,FC.IsFeatured,FC.DisplayOrder', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.ForumID', $forum_id);
        if ($forum_category_id) {
            $this->db->where('FC.ParentCategoryID', $forum_category_id);
        } else {
            $this->db->where('FC.ParentCategoryID', 0);
        }
        $this->db->where('FC.StatusID', 2);
        $this->db->group_by('FC.ForumCategoryID');
        $this->db->order_by('FC.DisplayOrder', 'ASC');
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }

    /**
     * 
     * @param type $user_id
     * @param type $forum_id
     * @param type $with_details
     * @return boolean
     */
    function check_forum_permissions($user_id, $forum_id, $with_details = TRUE) {
        $permissions = array();
        // Set default permissions
        $permissions['IsCreator'] = FALSE;
        $permissions['IsSuperAdmin'] = FALSE;
        $permissions['IsAdmin'] = FALSE;
        $permissions['IsMember'] = FALSE;
        $permissions['Details'] = array();
        $forum_status = get_detail_by_id($forum_id, 33, "StatusID", 1);
        // $forum_detail = $this->forum_model->details($forum_id,$user_id);
        if ($forum_status == 2) {
            /* if($with_details)
              {
              $permissions['Details'] = $forum_detail;
              } */

            $membership = $this->forum_model->check_membership($forum_id, $user_id);
            if (!empty($membership)) {
                if ($membership['ModuleRoleID'] == 10) {
                    $permissions['IsCreator'] = TRUE;
                    $permissions['IsSuperAdmin'] = TRUE;
                    $permissions['IsAdmin'] = TRUE;
                } else if ($membership['ModuleRoleID'] == 11) {
                    $permissions['IsSuperAdmin'] = TRUE;
                    $permissions['IsAdmin'] = TRUE;
                } else if ($membership['ModuleRoleID'] == 12) {
                    $permissions['IsAdmin'] = TRUE;
                }
            }
        }

        return $permissions;
    }

    /**
     * 
     * @param type $user_id
     * @param type $forum_category_id
     * @param type $with_details
     * @return boolean
     */
    function check_forum_category_permissions($user_id, $forum_category_id, $with_details = TRUE) {
        $permissions = array();
        // Set default permissions
        $permissions['IsCreator'] = FALSE;
        $permissions['IsSuperAdmin'] = FALSE;
        $permissions['IsAdmin'] = FALSE;
        $permissions['IsMember'] = FALSE;
        $permissions['Details'] = array();

        $forum_category_status = get_detail_by_id($forum_category_id, 34, "StatusID", 1);
        //$forum_category_detail = $this->forum_model->category_details($forum_category_id,$user_id);
        if ($forum_category_status == 2) {
            /* if($with_details)
              {
              $permissions['Details'] = $forum_category_detail;
              } */

            $membership = $this->forum_model->check_category_membership($forum_category_id, $user_id);
            if (!empty($membership)) {
                if ($membership['ModuleRoleID'] == 14) {
                    $permissions['IsCreator'] = TRUE;
                    $permissions['IsSuperAdmin'] = TRUE;
                    $permissions['IsAdmin'] = TRUE;
                    $permissions['IsMember'] = TRUE;
                } else if ($membership['ModuleRoleID'] == 15) {
                    $permissions['IsSuperAdmin'] = TRUE;
                    $permissions['IsAdmin'] = TRUE;
                    $permissions['IsMember'] = TRUE;
                } else if ($membership['ModuleRoleID'] == 16) {
                    $permissions['IsAdmin'] = TRUE;
                    $permissions['IsMember'] = TRUE;
                } else if ($membership['ModuleRoleID'] == 17) {
                    $permissions['IsMember'] = TRUE;
                }
            }
        }

        return $permissions;
    }

    /**
     * Function: check_membership
     * Description : Check forum permission
     * @param type $forum_id
     * @param type $user_id
     * @return type
     */
    function check_membership($forum_id, $user_id) {
        $this->db->select('ModuleRoleID');
        $this->db->from(FORUMMANAGER);
        $this->db->where('ForumID', $forum_id);
        $this->db->where('ModuleID', 3);
        $this->db->where('ModuleEntityID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            $this->db->select('ModuleID,ModuleEntityID,ModuleRoleID');
            $this->db->from(FORUMMANAGER);
            $this->db->where('ForumID', $forum_id);
            $this->db->where_not_in('ModuleID', array(3));
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    if ($row['ModuleID'] == 1) {
                        $result = $this->group_model->check_membership($row['ModuleEntityID'], $user_id, FALSE);
                        if ($result)
                            return $row;
                        else
                            FALSE;
                    }
                }
            } else
                FALSE;
        }
    }

    /**
     * Function: check_category_membership
     * Description : Check category permission
     * @param type $forum_category_id
     * @param type $user_id
     * @return type
     */
    function check_category_membership($forum_category_id, $user_id) {
        $this->db->select('ModuleRoleID');
        $this->db->from(FORUMCATEGORYMEMBER);
        $this->db->where('ForumCategoryID', $forum_category_id);
        $this->db->where('ModuleID', 3);
        $this->db->where('ModuleEntityID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            $this->db->select('ModuleID,ModuleEntityID,ModuleRoleID');
            $this->db->from(FORUMCATEGORYMEMBER);
            $this->db->where('ForumCategoryID', $forum_category_id);
            $this->db->where_not_in('ModuleID', array(3));
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    if ($row['ModuleID'] == 1) {
                        $result = $this->group_model->check_membership($row['ModuleEntityID'], $user_id, FALSE);
                        if ($result)
                            return $row;
                        else
                            FALSE;
                    }
                }
            } else
                FALSE;
        }
    }

    /**
     * 
     * @param type $forum_category_id
     * @param type $user_id
     * @return type
     */
    function check_category_visibility_membership($forum_category_id, $user_id) {
        $forum_category_visibility = get_detail_by_id($forum_category_id, 34, "Visibility", 1);
        if ($forum_category_visibility == 1) {
            return TRUE;
        }
        $this->db->select('ForumCategoryVisibilityID');
        $this->db->from(FORUMCATEGORYVISIBILITY);
        $this->db->where('ForumCategoryID', $forum_category_id);
        $this->db->where('ModuleID', 3);
        $this->db->where('ModuleEntityID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            $this->db->select('ForumCategoryVisibilityID,ModuleID,ModuleEntityID');
            $this->db->from(FORUMCATEGORYVISIBILITY);
            $this->db->where('ForumCategoryID', $forum_category_id);
            $this->db->where('ModuleID', 1);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $result = $this->group_model->check_membership($row['ModuleEntityID'], $user_id, FALSE);
                    if ($result)
                        return TRUE;
                    else
                        FALSE;
                }
            } else
                FALSE;
        }
    }

    /**
     * Function: create_category
     * Description : insert data in forum category table
     * @param type $input
     * @return type
     */
    function create_category($input, $user_id, $forum_category_id = '') {
        $media_guid = $input['MediaGUID'];
        $input['MediaID'] = NULL;
        $update_media = TRUE;
        if (!empty($media_guid)) {
            $media_id = get_detail_by_guid($media_guid, 21);
            if ($media_id) {
                $input['MediaID'] = $media_id;
            }
        }
        unset($input['MediaGUID']);

        if (empty($forum_category_id)) {
            $params = array('a' => 0, 'ge' => 0, 'p' => 1);
            if ($input['CanAllMemberPost'] == 2) {
                $params = array('a' => 0, 'ge' => 0, 'p' => 0);
            }
            $input['param'] = json_encode($params);
            $forum_category_id = $this->insert(FORUMCATEGORY, $input);

            $forum_category_member = array();
            $forum_category_member['ForumCategoryID'] = $forum_category_id;
            $forum_category_member['ModuleID'] = 3;
            $forum_category_member['ModuleEntityID'] = $input['CreatedBy'];
            $forum_category_member['ModuleRoleID'] = 14;
            $forum_category_member['AddedBy'] = $input['CreatedBy'];
            $forum_category_member['CanPostOnWall'] = 1;
            $forum_category_member['IsExpert'] = 0;
            $forum_category_member['IsDirect'] = 1;
            $forum_category_member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $this->insert(FORUMCATEGORYMEMBER, $forum_category_member);
            //Create Defualt album
            create_default_album($user_id, 34, $forum_category_id);
            if (empty($input['ParentCategoryID'])) {
                //Get all superadmin/admin for forum and set superadmin of forum category
                initiate_worker_job('set_category_superadmin', array('ForumID' => $input['ForumID'], 'CreatedBy' => $user_id, 'ForumCategoryID' => $forum_category_id));
            } else {
                //Get all superadmin/admin for parent category and set superadmin of subcategory
                initiate_worker_job('set_subcategory_superadmin', array('ParentCategoryID' => $input['ParentCategoryID'], 'CreatedBy' => $user_id, 'ForumCategoryID' => $forum_category_id));
            }
        } else {
            $media_id = get_detail_by_id($forum_category_id, 34, 'MediaID');
            if ($media_id == $input['MediaID']) {
                $update_media = FALSE;
            }

            $category_forum_id = get_detail_by_id($forum_category_id, 34, 'ForumID');

            if ($input['ForumID'] && $input['ForumID'] != $category_forum_id) {
                $this->update_category_admins($forum_category_id, $category_forum_id, $input['ForumID'], $user_id);
            }

            $this->update(FORUMCATEGORY, array('ForumCategoryID' => $forum_category_id), $input);
        }

        if ($update_media) {
            if ($input['MediaID']) {
                $media[0]['MediaGUID'] = $media_guid;
                $media[0]['Caption'] = '';
                $this->load->model('media/media_model');

                $album_id = get_album_id($user_id, DEFAULT_PROFILE_ALBUM, 34, $forum_category_id);
                $this->media_model->updateMedia($media, $forum_category_id, $user_id, $album_id);
            }
        }


        // initiate_worker_job('create_forum_superadmin', array('ForumID'=>$forum_id,'CreatedBy'=>$input['CreatedBy']));  
        return $forum_category_id;
    }

    function update_category_admins($forum_category_id, $old_forum_id, $new_forum_id, $user_id) {        

        //Get all superadmin/admin for forum and set superadmin of forum category
         initiate_worker_job('set_category_superadmin', array('ForumID' => $new_forum_id, 'CreatedBy' => $user_id, 'ForumCategoryID' => $forum_category_id));
         
         //$this->set_category_superadmin($new_forum_id, $forum_category_id, $user_id);
         
         // update forum id for child categories
         $this->update(FORUMCATEGORY, array('ParentCategoryID' => $forum_category_id), array('ForumID' => $new_forum_id));
         
         
        // Get all child categories and set their admins
        $query = $this->db->select('ForumCategoryID')
                ->from(FORUMCATEGORY)
                ->where('ParentCategoryID', $forum_category_id)
                ->get();
        foreach ($query->result() as $result) {
            $child_forum_category_id = $result->ForumCategoryID;
            
            //Get all superadmin/admin for parent category and set superadmin of subcategory
            initiate_worker_job('set_subcategory_superadmin', array('ParentCategoryID' => $forum_category_id, 'CreatedBy' => $user_id, 'ForumCategoryID' => $child_forum_category_id));
            
            //$this->set_subcategory_superadmin($forum_category_id, $child_forum_category_id, $user_id);
            
        }
    }

    /**
     *  Function Name: change_order
     * Description: update forum order
     * @param type $order_data
     * @return boolean
     */
    function change_category_order($order_data) {
        $orderValue = array();
        foreach ($order_data as $items) {
            $itemArray = array();
            $itemArray['ForumCategoryID'] = $items['ForumCategoryID'];
            $itemArray['DisplayOrder'] = $items['DisplayOrder'];
            $orderValue[] = $itemArray;
        }

        $this->db->update_batch(FORUMCATEGORY, $orderValue, 'ForumCategoryID');
        return true;
    }

    /**
     *  Function Name: get_all_similar_categories
     * Description: get similar categories
     * @param type $category_id
     * @return array
     */
    function get_all_similar_categories($category_id) {
        $data = array();
        $query = $this->db->select('ForumID')
                ->from(FORUMCATEGORY)
                ->where('ForumCategoryID', $category_id)
                ->get();
        if ($query->num_rows()) {
            $forum_id = $query->row()->ForumID;

            $query = $this->db->select('ForumCategoryID')
                    ->from(FORUMCATEGORY)
                    ->where('ForumID', $forum_id)
                    ->get();
            if ($query->num_rows()) {
                foreach ($query->result() as $result) {
                    $data[] = $result->ForumCategoryID;
                }
            }
        }
        return $data;
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function category_details($forum_category_id, $user_id) {
        $result = array();
        $this->db->select('FC.*,F.URL as F_URL,FC.URL as C_URL,FC1.URL as P_URL,FC1.ForumCategoryID as P_ForumCategoryID,FC1.ForumCategoryGUID as P_ForumCategoryGUID,FC1.Name as P_Name, CONCAT(U.FirstName," ",U.LastName) AS CreatedByName,F.ForumGUID,F.Name as ForumName,F.Visible, IFNULL(M.ImageName,"") as ProfilePicture, M.MediaGUID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORY . ' FC1', 'FC.ParentCategoryID=FC1.ForumCategoryID', 'left');
        //$this->db->join(FORUMCATEGORYMEMBER . ' FCM','FC.ForumCategoryID=FC.ForumCategoryID AND FCM.ModuleID=3 AND FCM.ModuleEntityID='.$user_id,'left');
        $this->db->join(FORUM . ' F', 'FC.ForumID=F.ForumID');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID', 'left');
        $this->db->where('FC.ForumCategoryID', $forum_category_id);
        $this->db->group_by('FC.ForumCategoryID');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            $permission = $this->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
            unset($permission['Details']);
            $result['NoOfMembers'] = $this->forum_category_members_count($result['ForumCategoryID']);
            $MemberData = $this->category_recent_post_owner($result['ForumCategoryID'], $user_id);
            $result['CanPostOnWall'] = $this->can_post_on_wall($forum_category_id, 3, $user_id);
            $result['Members'] = $MemberData['Data'];
            $result['MembersCount'] = $MemberData['TotalRecords'];
            $result['Permissions'] = $permission;
            $sub_cat_details = $this->get_subcategory($forum_category_id, $user_id, $permission, true);
            $result['SubCategory'] = $sub_cat_details['result'];
            $result['SubCategoryFollowed'] = $sub_cat_details['result'];
            $result['SubCategoryFollowed'] = $sub_cat_details['total_followed'];
            $result['SubCategoryUnFollowed'] = $sub_cat_details['total_unfollowed'];


            $result['Param'] = json_decode($result['param'], true);
            $result['FullURL'] = $this->get_category_url($result['ForumCategoryID']);
            $link = $result['F_URL'];
            $breadcrumbs['Forum'] = array('ForumGUID' => $result['ForumGUID'], 'Name' => $result['ForumName'], 'ForumID' => $result['ForumID'], 'Link' => $link);
            if ($result['ParentCategoryID']) {
                $result['ParentCategory'] = $this->get_category_detail($result['ParentCategoryID']);
                $link .= '/' . $result['P_URL'];
                $breadcrumbs['Category'] = array('ForumCategoryGUID' => $result['P_ForumCategoryGUID'], 'Name' => $result['P_Name'], 'ForumCategoryID' => $result['P_ForumCategoryID'], 'Link' => $link);
                $link .= '/' . $result['C_URL'];
                $breadcrumbs['SubCategory'] = array('ForumCategoryGUID' => $result['ForumCategoryGUID'], 'Name' => $result['Name'], 'ForumCategoryID' => $result['ForumCategoryID'], 'Link' => $link);
            } else {
                $link .= '/' . $result['C_URL'];
                $breadcrumbs['Category'] = array('ForumCategoryGUID' => @$result['ForumCategoryGUID'], 'Name' => $result['Name'], 'ForumCategoryID' => $result['ForumCategoryID'], 'Link' => $link);
                $breadcrumbs['SubCategory'] = array('ForumCategoryGUID' => '', 'Name' => '', 'ForumCategoryID' => '', 'Link' => '');
            }
            $result['Breadcrumbs'] = $breadcrumbs;
        }
        return $result;
    }

    /**
     * 
     * @param type $category_id
     * @return url
     */
    function get_category_url($category_id) {
        $url = "community";
        $this->db->select('S.ParentCategoryID as ParentCategoryID,F.URL as ForumURL,P.URL as ParentURL,S.URL as SubURL', false);
        $this->db->from(FORUMCATEGORY . ' S');
        $this->db->join(FORUMCATEGORY . ' P', 'P.ForumCategoryID=S.ParentCategoryID', 'left');
        $this->db->join(FORUM . ' F', 'S.ForumID=F.ForumID', 'left');
        $this->db->where('S.ForumCategoryID', $category_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            if ($row['ParentCategoryID']) {
                $url .= '/' . $row['ForumURL'] . '/' . $row['ParentURL'] . '/' . $row['SubURL'];
            } else {
                $url .= '/' . $row['ForumURL'] . '/' . $row['SubURL'];
            }
        }
        return $url;
    }

    /**
     * 
     * @param type $forum_id
     * @param type $user_id
     * @return type
     */
    function get_forum_category($forum_id, $user_id, $permission_forum, $forum_category_id = 0, $is_site_map=FALSE) {
        $user_groups_array = $this->group_model->get_users_groups($user_id);
        $user_groups = 0;
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('FC.*,FC.CreatedBy,CONCAT(U.FirstName," ",U.LastName) AS CreatedByName, IFNULL(M.ImageName,"") as ProfilePicture, M.MediaGUID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID', 'left');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.ForumID', $forum_id);
        $this->db->where('FC.StatusID', 2);
        $this->db->where('FC.ParentCategoryID', 0);
        $this->db->group_by('FC.ForumCategoryID');
        $this->db->order_by('FC.DisplayOrder', 'ASC');
        if ($forum_category_id) {
            $this->db->where('FC.ForumCategoryID', $forum_category_id);
        }
        if (!$permission_forum['IsCreator'] && !$permission_forum['IsAdmin'] && !$permission_forum['IsSuperAdmin']) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")         
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";

            $this->db->where($condition, NULL, FALSE);
        }
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $final_array = array();
        if ($query->num_rows() > 0) {
            $permission = $permission_forum;
            foreach ($query->result_array() as $row) {
                if(!$is_site_map) {
                    $permission = $this->check_forum_category_permissions($user_id, $row['ForumCategoryID'], FALSE);

                    unset($permission['Details']);
                    $MemberData = $this->category_recent_post_owner($row['ForumCategoryID'], $user_id);
                    $row['Members'] = $MemberData['Data'];
                    $row['MembersCount'] = $MemberData['TotalRecords'];
                    $row['NoOfMembers'] = $this->forum_category_members_count($row['ForumCategoryID']);
                    $row['Permissions'] = $permission;
                    $row['FeaturedPost'] = $this->activity_model->get_featured_post($user_id, 34, $row['ForumCategoryID'], 1, 1);
                }
                
                $row['SubCategory'] = $this->get_forum_subcategory($row['ForumCategoryID'], $user_id, $forum_id, $permission);
                
                $final_array[] = $row;
            }
        }
        return $final_array;
    }

    function get_user_most_active_categories($user_id, $limit, $returnOnlyIds = false) {

        $this->db->select(' FCM.ForumCategoryMemberID, COUNT(A.ActivityID) A_Count, FCM.ForumCategoryID');

        $this->db->from(ACTIVITY . ' A');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', "FCM.ForumCategoryID = A.ModuleEntityID AND A.ModuleID = 34 AND FCM.ModuleID = 3 AND FCM.ModuleEntityID = $user_id ", 'left');
        $this->db->join(FORUMCATEGORY . ' FC', " FC.ForumCategoryID = FCM.ForumCategoryID ", 'left');

        $this->db->where("FCM.ForumCategoryID IS NOT NULL", NULL, FALSE);

        $this->db->group_by('FCM.ForumCategoryID');
        $this->db->order_by('A_Count', 'DESC');

        $this->db->limit($limit);

        $query = $this->db->get();
        $rowSet = $query->result_array();   //echo $this->db->last_query(); echo '============='; die;

        if (!$returnOnlyIds) {
            return $rowSet;
        }

        $ids = [];
        foreach ($rowSet as $row) {
            $ids[] = $row['ForumCategoryID'];
        }

        return $ids;
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function get_feature_category($forum_id, $user_id, $permission_forum) {
        $user_groups_array = $this->group_model->get_users_groups($user_id);
        $user_groups = 0;
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('FC.ForumCategoryGUID,FC.Name,FC.URL,IFNULL(M.ImageName,"") as ProfilePicture, M.MediaGUID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID', 'left');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.ForumID', $forum_id);
        $this->db->where('FC.StatusID', 2);
        $this->db->where('FC.IsFeatured', 1);
        if (!$permission_forum['IsCreator'] && !$permission_forum['IsSuperAdmin']) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")         
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";

            $this->db->where($condition, NULL, FALSE);
        }
        $this->db->group_by('FC.ForumCategoryID');
        $this->db->limit(FEATURE_CATEGORY);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Function: change_feature_category
     * Description : Update data
     * @param type $feature_data
     * @return type
     */
    function set_feature_category($feature_data, $forum_id) {
        $this->update(FORUMCATEGORY, array('ForumID' => $forum_id), array('IsFeatured' => 0));
        $feature_array = array();
        foreach ($feature_data as $row) {
            $itemArray = array();
            $itemArray['ForumCategoryID'] = $row['ForumCategoryID'];
            $itemArray['IsFeatured'] = 1;
            $feature_array[] = $itemArray;
        }
        $this->db->update_batch(FORUMCATEGORY, $feature_array, 'ForumCategoryID');
        return true;
    }

    /**
     * Function: get_forum_subcategory
     * @param  [int]  $category_id 
     * @param  [int]  $user_id  
     * @return [array] [Forum SubCategory data]
     */
    function get_forum_subcategory($category_id, $user_id, $forum_id, $permission_category) {
        $user_groups_array = $this->group_model->get_users_groups($user_id);
        $user_groups = 0;
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('FC.URL,FC.Name,FC.ForumCategoryID,FC.ForumCategoryGUID,FC.NoOfDiscussions', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.StatusID', 2);
        $this->db->where('FC.ParentCategoryID', $category_id);
        if (!$permission_category['IsCreator'] && !$permission_category['IsSuperAdmin']) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")         
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";
            $this->db->where($condition, NULL, FALSE);
        }
        $this->db->group_by('FC.ForumCategoryID');
        $this->db->order_by('FC.DisplayOrder', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Function: get_subcategory
     * @param  [int]  $category_id 
     * @param  [int]  $user_id  
     * @return [array] [SubCategory Detail]
     */
    function get_subcategory($category_id, $user_id, $permission_category, $count_also = false) {
        $user_groups_array = $this->group_model->get_users_groups($user_id);
        $user_groups = 0;
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('FC.*,FC.CreatedBy,CONCAT(U.FirstName," ",U.LastName) AS CreatedByName, IFNULL(M.ImageName,"") as ProfilePicture, M.MediaGUID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->join(USERS . ' U', 'FC.CreatedBy = U.UserID');
        $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID', 'left');
        $this->db->where('FC.StatusID', 2);
        $this->db->where('FC.ParentCategoryID', $category_id);
        if (!$permission_category['IsCreator'] && !$permission_category['IsSuperAdmin']) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")         
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";
            $this->db->where($condition, NULL, FALSE);
        }
        $this->db->group_by('FC.ForumCategoryID');
        $query = $this->db->get();
        $final_array = array();
        $total_followed = 0;
        $total_unfollowed = 0;
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $permission = $this->check_forum_category_permissions($user_id, $row['ForumCategoryID'], FALSE);
                unset($permission['Details']);
                $MemberData = $this->category_recent_post_owner($row['ForumCategoryID'], $user_id);
                $row['Members'] = $MemberData['Data'];
                $row['MembersCount'] = $MemberData['TotalRecords'];
                /* if($row['ParentCategoryID']==0)
                  { */
                $row['NoOfMembers'] = $this->forum_category_members_count($row['ForumCategoryID']);
                //$row['NoOfDiscussions']=$this->category_discussions_count($row['ForumCategoryID']);
                //}
                $row['Permissions'] = $permission;
                if ($permission['IsMember'] == 1) {
                    $total_followed++;
                }
                if ($permission['IsMember'] == 0) {
                    $total_unfollowed++;
                }
                $row['FullURL'] = $this->get_category_url($row['ForumCategoryID']);
                $row['FeaturedPost'] = $this->activity_model->get_featured_post($user_id, 34, $row['ForumCategoryID'], 1, 1);
                $final_array[] = $row;
            }
        }
        if ($count_also) {
            usort($final_array, 'sortByOrder');
            $return_array['result'] = $final_array;
            $return_array['total_followed'] = $total_followed;
            $return_array['total_unfollowed'] = $total_unfollowed;
            return $return_array;
        } else {
            return $final_array;
        }
    }

    /**
     * 
     * @param type $current_user
     */
    function get_user_category_list() {
        return $this->user_category_list;
    }

    function get_visible_category_list() {
        return $this->visible_category_list;
    }

    /**
     * [set_user_category_list Used to set user categories] 
     * @param type $current_user
     */
    function set_user_category_list($current_user) {
        $this->user_category_list = $this->get_users_categories($current_user);
    }

    function set_visible_category_list($current_user) {
        $this->visible_category_list = $this->get_visible_categories($current_user);
    }

    /**
     * [get_users_categories Used to get user all category ids]
     * @param  [int] $user_id   [User ID]
     * @return [array]          [Array of category id's]
     */
    function get_users_categories($user_id) {
        $category_ids = array();
        return $category_ids;
        if (CACHE_ENABLE) {
            //$this->cache->delete('user_categories_'.$user_id);
            $category_ids = $this->cache->get('user_categories_' . $user_id);
            if ($category_ids) {
                return $category_ids;
            }
        }
        if (!$category_ids) {
            $group_list = $this->group_model->get_users_groups($user_id);
            $this->db->select('ForumCategoryID');
            $this->db->from(FORUMCATEGORYMEMBER);
            if ($group_list) {
                $this->db->where("IF(ModuleID=1,ModuleEntityID IN(" . implode(',', $group_list) . "),true)", null, false);
            }
            $this->db->where("IF(ModuleID=3,ModuleEntityID='" . $user_id . "',true)", null, false);
            $query = $this->db->get();
            //echo $this->db->last_query();
            if ($query->num_rows()) {
                $result = $query->result_array();
                foreach ($query->result_array() as $result) {
                    $category_ids[] = $result['ForumCategoryID'];
                }
            }
        }

        if ($category_ids && CACHE_ENABLE) {
            $this->cache->save('user_categories_' . $user_id, $category_ids);
        }
        return $category_ids;
    }

    function get_visible_categories($user_id) {
        $category_ids = array();
        if (CACHE_ENABLE) {
            //$this->cache->delete('user_categories_'.$user_id);
            $category_ids = $this->cache->get('visible_categories_' . $user_id);
            if ($category_ids) {
                return $category_ids;
            }
        }
        if (!$category_ids) {
            $user_groups_array = $this->group_model->get_users_groups($user_id);
            $user_groups = 0;
            if (!empty($user_groups_array)) {
                $user_groups = implode(',', $user_groups_array);
            }
            $this->db->select('FC.ForumCategoryID');
            $this->db->from(FORUMCATEGORY . ' FC');
            $this->db->join(FORUMCATEGORYMEMBER . ' FCM', "FC.ForumCategoryID=FCM.ForumCategoryID AND FCM.ModuleID='3' AND FCM.ModuleEntityID='" . $user_id . "'", 'left');
            // $this->db->where("IF(FC.Visibility=1,true,FCM.ForumCategoryID is not null)",null,false);
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")  
                                            WHEN (FCM.ModuleID is null AND FCV.ModuleID is null)
                                            	THEN false             
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";
            $this->db->where($condition, NULL, FALSE);
            $query = $this->db->get();
            // echo $this->db->last_query();die;
            if ($query->num_rows()) {
                $result = $query->result_array();
                foreach ($query->result_array() as $result) {
                    $category_ids[] = $result['ForumCategoryID'];
                }
            }
        }

        if ($category_ids && CACHE_ENABLE) {
            //$this->cache->save('visible_categories_'.$user_id, $category_ids);
        }
        return $category_ids;
    }

    /**
     * Function: add_category_visibility_post
     * Description : Add member visibility
     * @param type $members
     * @param type $forum_category_id
     * @param type $user_id
     * @return type
     */
    function add_category_visibility($members, $forum_category_id, $user_id) {
        $forum_category_data = get_detail_by_id($forum_category_id, 34, "ParentCategoryID", 2);
        $add_member_array = array();
        foreach ($members as $member) {
            $item = array();
            $this->db->select('ForumCategoryVisibilityID');
            $this->db->from(FORUMCATEGORYVISIBILITY);
            $this->db->where('ForumCategoryID', $forum_category_id);
            $this->db->where('ModuleID', $member['ModuleID']);
            $this->db->where('ModuleEntityID', $member['ModuleEntityID']);
            $query = $this->db->get();
            if (!$query->num_rows()) {
                $item['ForumCategoryID'] = $forum_category_id;
                $item['ModuleID'] = $member['ModuleID'];
                $item['ModuleEntityID'] = $member['ModuleEntityID'];
                $item['AddedBy'] = $user_id;
                $item['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $add_member_array[] = $item;

                if (CACHE_ENABLE && $item['ModuleID'] == 3) {
                    $this->cache->delete('visible_categories_' . $item['ModuleEntityID']);
                }
            }
        }
        if (!empty($add_member_array)) {
            $this->db->insert_batch(FORUMCATEGORYVISIBILITY, $add_member_array);
        }
        if (!$forum_category_data['ParentCategoryID']) {
            initiate_worker_job('set_subcategory_visibility_member', array('ForumCategoryID' => $forum_category_id, 'CreatedBy' => $user_id, 'Members' => $members));
        }
    }

    /**
     * Function: add_category_visibility_post
     * Description : Add member visibility
     * @param type $members
     * @param type $forum_category_id
     * @param type $user_id
     * @return type
     */
    function remove_category_visibility($forum_category_visibility_ids) {
        $this->db->where_in('ForumCategoryVisibilityID', $forum_category_visibility_ids);
        $this->db->delete(FORUMCATEGORYVISIBILITY);
    }

    /**
     * Function: add_members_category
     * Description : Add member visibility
     * @param type $members
     * @param type $forum_category_id
     * @param type $user_id
     * @return type
     */
    function add_category_members($members, $forum_category_id, $user_id) {
        $forum_category_data = get_detail_by_id($forum_category_id, 34, "ParentCategoryID,param,CanAllMemberPost", 2);
        $category_params = json_decode($forum_category_data['param'], true);
        $can_all_member_post = $forum_category_data['CanAllMemberPost'];
        if (empty($category_params)) {
            $category_params = array("a" => 0, "ge" => 0, "p" => 1);
            if ($can_all_member_post == 2) {
                $category_params = array("a" => 0, "ge" => 0, "p" => 0);
            }
        }
        $add_member_array = array();
        $update_member_array = array();
        $notify_admin = array();
        $notify_member = array();
        $group_ids_admin = array();
        $group_ids_member = array();
        foreach ($members as $member) {
            $this->db->select('ForumCategoryMemberID,ModuleRoleID,CanPostOnWall,IsExpert');
            $this->db->from(FORUMCATEGORYMEMBER);
            $this->db->where('ForumCategoryID', $forum_category_id);
            $this->db->where('ModuleID', $member['ModuleID']);
            $this->db->where('ModuleEntityID', $member['ModuleEntityID']);
            $query = $this->db->get();
            $item = array();
            if ($query->num_rows()) {
                $row = $query->row_array();

                $item['ForumCategoryMemberID'] = $row['ForumCategoryMemberID'];
                $item['ForumCategoryID'] = $forum_category_id;
                $item['ModuleID'] = $member['ModuleID'];
                $item['ModuleEntityID'] = $member['ModuleEntityID'];
                $item['ModuleRoleID'] = isset($member['ModuleRoleID']) ? $member['ModuleRoleID'] : $row['ModuleRoleID'];
                $item['CanPostOnWall'] = isset($member['CanPostOnWall']) ? $member['CanPostOnWall'] : $row['CanPostOnWall'];
                $item['IsExpert'] = isset($member['IsExpert']) ? $member['IsExpert'] : $row['IsExpert'];
                $item['AddedBy'] = $user_id;
                $item['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');

                $update_member_array[] = $item;

                if ($item['ModuleID'] == 3) {
                    if ($item['ModuleRoleID'] == 16) {
                        $notify_admin[] = $item['ModuleEntityID'];
                    }
                } else if ($item['ModuleID'] == 1) {
                    if ($item['ModuleRoleID'] == 16) {
                        $group_ids_admin[] = $item['ModuleEntityID'];
                    }
                }
            } else {
                $item['ForumCategoryID'] = $forum_category_id;
                $item['ModuleID'] = $member['ModuleID'];
                $item['ModuleEntityID'] = $member['ModuleEntityID'];
                $item['ModuleRoleID'] = isset($member['ModuleRoleID']) ? $member['ModuleRoleID'] : (($category_params['a'] == 0) ? 17 : 16 );
                $item['CanPostOnWall'] = isset($member['CanPostOnWall']) ? $member['CanPostOnWall'] : $category_params['p'];
                $item['IsExpert'] = isset($member['IsExpert']) ? $member['IsExpert'] : $category_params['ge'];
                $item['AddedBy'] = $user_id;
                $item['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $add_member_array[] = $item;

                if (CACHE_ENABLE && $item['ModuleID'] == 3) {
                    $this->cache->delete('visible_categories_' . $item['ModuleEntityID']);
                }

                if ($item['ModuleID'] == 3) {
                    if ($item['ModuleRoleID'] == 16) {
                        $notify_admin[] = $item['ModuleEntityID'];
                    } else {
                        $notify_member[] = $item['ModuleEntityID'];
                    }
                } else if ($item['ModuleID'] == 1) {
                    if ($item['ModuleRoleID'] == 16) {
                        $group_ids_admin[] = $item['ModuleEntityID'];
                    } else if ($item['ModuleRoleID'] == 17) {
                        $group_ids_member[] = $item['ModuleEntityID'];
                    }
                }
            }
        }
        if (!empty($add_member_array)) {
            $this->db->insert_batch(FORUMCATEGORYMEMBER, $add_member_array);
        }
        if (!empty($update_member_array)) {
            $this->db->update_batch(FORUMCATEGORYMEMBER, $update_member_array, 'ForumCategoryMemberID');
        }
        if (!$forum_category_data['ParentCategoryID']) {
            //Add Admin/Member of all sub category of this category
            initiate_worker_job('set_subcategory_member', array('ForumCategoryID' => $forum_category_id, 'CreatedBy' => $user_id, 'Members' => $members));
        }
        initiate_worker_job('notify_category_member', array('GroupIDAdmin' => $group_ids_admin, 'GroupIDMember' => $group_ids_member, 'NofityAdmin' => $notify_admin, 'NotifyMember' => $notify_member, 'ForumCategoryID' => $forum_category_id, 'CreatedBy' => $user_id));
    }

    /**
     * 
     * @param type $forum_category_id
     * @param type $user_id
     * @param type $group_ids_admin
     * @param type $group_ids_member
     * @param type $notify_admin
     * @param type $notify_member
     */
    function notify_category_member($forum_category_id, $user_id, $group_ids_admin = array(), $group_ids_member = array(), $notify_admin = array(), $notify_member = array()) {
        $notify_group_admin = array(); // When role assign as admin
        $notify_group_member = array(); // When role assign as Member
        if (!empty($group_ids_admin)) {
            foreach ($group_ids_admin as $group_id) {
                $group_members = array();
                $group_members = $this->group_model->get_group_members_id_recursive($group_id);
                if (!empty($group_members)) {
                    foreach ($group_members as $key => $g_member) {
                        $notify_group_admin[$group_id][] = $g_member;
                    }
                }
            }
        }
        if (!empty($group_ids_member)) {
            foreach ($group_ids_member as $group_id) {
                $group_members = array();
                $group_members = $this->group_model->get_group_members_id_recursive($group_id);
                if (!empty($group_members)) {
                    foreach ($group_members as $key => $g_member) {
                        $notify_group_member[$group_id][] = $g_member;
                    }
                }
            }
        }

        if (!empty($notify_admin)) {
            $parameters[0]['ReferenceID'] = $user_id;
            $parameters[0]['Type'] = 'User';
            $parameters[1]['ReferenceID'] = $forum_category_id;
            $parameters[1]['Type'] = 'ForumCategory';
            $this->notification_model->add_notification(133, $user_id, $notify_admin, $forum_category_id, $parameters);
        }
        if (!empty($notify_member)) {
            $parameters[0]['ReferenceID'] = $forum_category_id;
            $parameters[0]['Type'] = 'ForumCategory';
            $this->notification_model->add_notification(134, $user_id, $notify_member, $forum_category_id, $parameters);
        }
        if (!empty($notify_group_admin)) {

            foreach ($notify_group_admin as $key => $member) {
                if (!empty($member)) {
                    $member = array_unique($member);
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $key;
                    $parameters[1]['Type'] = 'Group';
                    $parameters[2]['ReferenceID'] = $forum_category_id;
                    $parameters[2]['Type'] = 'ForumCategory';
                    $this->notification_model->add_notification(135, $user_id, $member, $forum_category_id, $parameters);
                }
            }
        }
        if (!empty($notify_group_member)) {

            foreach ($notify_group_member as $key => $member) {
                if (!empty($member)) {
                    $member = array_unique($member);
                    $parameters[0]['ReferenceID'] = $user_id;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $key;
                    $parameters[1]['Type'] = 'Group';
                    $parameters[2]['ReferenceID'] = $forum_category_id;
                    $parameters[2]['Type'] = 'ForumCategory';
                    $this->notification_model->add_notification(136, $user_id, $member, $forum_category_id, $parameters);
                }
            }
        }
    }

    /**
     * 
     * @param type $forum_id
     * @return type
     */
    function forum_members_count($forum_id) {
        $this->db->select("COUNT('ForumCategoryMemberID') as Members ");
        $this->db->from(FORUM . ' F');
        $this->db->join(FORUMCATEGORY . ' FC', 'F.ForumID=FC.ForumID');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID');
        $this->db->where('F.ForumID', $forum_id);
        $this->db->group_by('FCM.ModuleID,FCM.ModuleEntityID');
        $query = $this->db->get();
        return $query->num_rows();
    }

    /**
     * 
     * @param type $category_id
     * @return type
     */
    function forum_category_members_count($category_id) {
        $this->db->select("COUNT('ForumCategoryMemberID') as Members ");
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID');
        $this->db->join(USERS . " U", "U.UserID=FCM.ModuleEntityID AND FCM.ModuleID=3", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=FCM.ModuleEntityID AND FCM.ModuleID=1", "LEFT");
        $this->db->where('FC.ForumCategoryID', $category_id);
        $this->db->where_not_in('U.StatusID', array(3, 4));
        $this->db->group_by('FCM.ModuleID,FCM.ModuleEntityID');
        $query = $this->db->get();
        return $query->num_rows();
    }

    /**
     * 
     * @param type $forum_id
     * @return type
     */
    function forum_discussions_count($forum_id) {
        $this->db->select("COUNT('ActivityID') as Discussions ");
        $this->db->from(FORUM . ' F');
        $this->db->join(FORUMCATEGORY . ' FC', 'F.ForumID=FC.ForumID');
        $this->db->join(ACTIVITY . ' A', 'A.ModuleEntityID=FC.ForumCategoryID AND A.ModuleID=34 AND A.StatusID=2');
        $this->db->where('F.ForumID', $forum_id);
        //$this->db->group_by('A.ModuleID,A.UserID');
        $query = $this->db->get();
        $result = $query->row_array();
        if ($result['Discussions'])
            return $result['Discussions'];
        else
            return 0;
    }

    /**
     * 
     * @param type $forum_id
     * @return int
     */
    function category_discussions_count($category_id) {
        $this->db->select("COUNT('ActivityID') as Discussions ");
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(ACTIVITY . ' A', 'A.ModuleEntityID=FC.ForumCategoryID AND A.ModuleID=34 AND A.StatusID=2');
        $this->db->where('FC.ForumCategoryID', $category_id);
        //$this->db->group_by('A.ModuleID,A.UserID');
        $query = $this->db->get();
        $result = $query->row_array();
        if ($result['Discussions'])
            return $result['Discussions'];
        else
            return 0;
    }

    /**
     * 
     * @param type $forum_id
     * @return int
     */
    function forum_recent_post_owner($forum_id, $user_id) {
        $result['Data'] = array();
        $result['TotalRecords'] = 0;
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $friends[] = 0;
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS Name,U.UserGUID,PU.Url as ProfileUrl');
        $this->db->from(FORUM . ' F');
        $this->db->join(FORUMCATEGORY . ' FC', 'F.ForumID=FC.ForumID');
        $this->db->join(ACTIVITY . ' A', 'A.ModuleEntityID=FC.ForumCategoryID AND A.ModuleID=34 AND A.StatusID=2');
        $this->db->join(USERS . ' U', 'A.UserID = U.UserID');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('FC.ForumID', $forum_id);
        /* if(!empty($friends))
          {
          $this->db->where_in('U.UserID',$friends);
          } */
        $this->db->group_by('A.UserID');
        //$this->db->order_by('A.LastActionDate','DESC');
        $this->db->order_by("CASE WHEN U.UserID IN (" . implode(',', $friends) . ") THEN 1 ELSE 0 END DESC");
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result['TotalRecords'] = $temp_q->num_rows();
        $this->db->limit(4);
        $query = $this->db->get();
        $result['Data'] = $query->result_array();
        return $result;
    }

    /**
     * 
     * @param type $forum_id
     * @param type $user_id
     * @return type
     */
    function category_recent_post_owner($category_id, $user_id) {
        $result['Data'] = array();
        $result['TotalRecords'] = 0;
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS Name,U.UserGUID,PU.Url as ProfileUrl');
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(ACTIVITY . ' A', 'A.ModuleEntityID=FC.ForumCategoryID AND A.ModuleID=34 AND A.StatusID=2');
        $this->db->join(USERS . ' U', 'A.UserID = U.UserID');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('FC.ForumCategoryID', $category_id);
        if (!empty($friends)) {
            $this->db->where_in('U.UserID', $friends);
        }
        $this->db->group_by('A.UserID');
        $this->db->order_by('A.LastActionDate', 'DESC');
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result['TotalRecords'] = $temp_q->num_rows();
        $this->db->limit(4);
        $query = $this->db->get();
        $result['Data'] = $query->result_array();
        return $result;
    }

    /**
     * 
     * @param type $forum_id
     * @param type $user_id
     * @param type $page_no
     * @param type $page_size
     * @param type $permissions
     * @param type $search_keyword
     * @return array
     */
    function admin_suggestion($forum_id, $user_id, $page_no, $page_size, $permissions, $search_keyword) {
        $result['Data'] = array();
        $result['TotalRecords'] = 0;
        $result = array();
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $group_ids = $this->group_model->get_users_groups($user_id);

        if (!$permissions['IsAdmin'] && empty($friends) && empty($group_ids)) {
            return $result;
        }
        $sql_array = array();
        $where_search_user = '';
        $where_search_group = '';

        if ($search_keyword) {
            $search_keyword = $this->db->escape_like_str($search_keyword);
            $where_search_user = " AND (U.FirstName LIKE '%" . $search_keyword . "%' OR U.LastName LIKE '%" . $search_keyword . "%' )";
            $where_search_group = " AND (G.GroupName LIKE '%" . $search_keyword . "%' )";
        }
        
        $forum_id = $this->db->escape_str($forum_id);
        
        $sql_user = "SELECT CONCAT(FirstName,' ',LastName) as Name, UserID as ModuleEntityID,UserGUID as ModuleEntityGUID,'' as TotalMember, IF(ProfilePicture='','',ProfilePicture) as ProfilePicture, '3' as ModuleID,PU.Url as ProfileUrl,(select count(FM2.ModuleEntityID) from  " . FORUMMANAGER . "  FM2 where FM2.AddedBy = " . $user_id . " AND FM2.ModuleID =3 AND FM2.ModuleEntityID = U.UserID) as FrequentMembers FROM Users as U JOIN " . PROFILEURL . " as PU ON U.UserID=PU.EntityID AND PU.StatusID=2 AND PU.EntityType='User' WHERE  U.StatusID IN (1,2) AND U.UserID not in (SELECT ModuleEntityID FROM " . FORUMMANAGER . " FM WHERE  FM.ModuleID=3 AND FM.ForumID=" . $forum_id . ") " . $where_search_user;
        $sql_group = " SELECT G.GroupName as Name, G.GroupID as ModuleEntityID, G.GroupGUID as ModuleEntityGUID,G.MemberCount as TotalMember, if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') as ProfilePicture, '1' as ModuleID,'' as ProfileUrl,(select count(FM3.ModuleEntityID) FROM  " . FORUMMANAGER . "  FM3 where FM3.AddedBy = " . $user_id . " AND FM3.ModuleID =1 AND FM3.ModuleEntityID = G.GroupID) as FrequentMembers  FROM Groups as G WHERE G.IsPublic IN (0,1) AND G.StatusID=2 AND G.GroupID not in (SELECT ModuleEntityID FROM " . FORUMMANAGER . " FM WHERE  FM.ModuleID=1 AND FM.ForumID=" . $forum_id . ") " . $where_search_group;

        if ($permissions['IsSuperAdmin']) {
            $sql_array[] = $sql_user;
            $sql_array[] = $sql_group;
        } else {
            if (!empty($friends)) {
                $blocked_users = $this->activity_model->block_user_list($user_id, 3);
                if ($blocked_users) {
                    $privacy_condition = " AND U.UserID NOT IN (" . implode(',', $blocked_users) . ") ";
                    $sql_user .= " AND UserID IN (" . implode(',', $friends) . ") " . $privacy_condition . " ";
                }
            }
            if (!empty($group_ids)) {
                $sql_group .= " AND G.GroupID IN (" . implode(',', $group_ids) . ")";
            }
            $sql_array[] = $sql_user;
            $sql_array[] = $sql_group;
        }
        if (!empty($sql_array)) {
            $query = implode('UNION', $sql_array);
            $temp_q = $this->db->query($query);
            $result['TotalRecords'] = $temp_q->num_rows();

            $query .= " ORDER BY FrequentMembers DESC";
            if ($page_no) {
                $query .= " LIMIT $page_size OFFSET " . $this->get_pagination_offset($page_no, $page_size);
            }

            $query = $this->db->query($query);

            $final_result = array();
            foreach ($query->result_array() as $row) {
                $row['Location'] = '';
                if ($row['ModuleID'] == 3) {
                    $LocationArr = $this->user_model->get_user_location($row['ModuleEntityID']);
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
                    $row['Location'] = $Location;
                    $users_relation = get_user_relation($user_id, $row['ModuleEntityID']);
                    $privacy_details = $this->privacy_model->details($row['ModuleEntityID']);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label['Value']) && isset($privacy_label[$privacy])) {

                                if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['Location'] = '';
                                }
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['ProfilePicture'] = '';
                                }
                            }
                        }
                    }
                }
                $final_result[] = $row;
            }
            $result['Data'] = $final_result;
        }
        return $result;
    }

    /**
     * 
     * @param type $forum_manager_id
     * @return boolean
     */
    function check_is_direct($forum_manager_id) {
        $this->db->select('ForumManagerID');
        $this->db->from(FORUMMANAGER);
        $this->db->where('ForumManagerID', $forum_manager_id);
        $this->db->where('IsDirect', 1);
        $query = $this->db->get();
        if ($query->num_rows())
            return TRUE;
        else
            return FALSE;
    }

    /**
     * 
     * @param type $forum_id
     * @param type $user_id
     * @param type $page_no
     * @param type $page_size
     * @param type $permissions
     * @param type $search_keyword
     * @return int
     * 
     */
    function manager($forum_id, $user_id, $page_no, $page_size, $permissions, $search_keyword) {
        $result_data['Data'] = array();
        $result_data['TotalRecords'] = 0;
        $result = array();
        $this->load->model('group/group_model');

        $this->db->select('(CASE FM.ModuleID 
							WHEN 1 THEN 1 
							WHEN 3 THEN 3
							ELSE "" END) AS ModuleID', FALSE);

        $this->db->select('(CASE FM.ModuleID 
							WHEN 1 THEN G.GroupGUID 
							WHEN 3 THEN U.UserGUID 
							ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('(CASE FM.ModuleID 
							WHEN 3 THEN PU.Url   
							ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE FM.ModuleID 
							WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
							WHEN 3 THEN U.ProfilePicture   
							ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('(CASE FM.ModuleID 
							WHEN 1 THEN G.GroupName 
							WHEN 3 THEN CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,"")) 
							ELSE "" END) AS Name', FALSE);

        $this->db->select('(CASE FM.ModuleID 
							WHEN 1 THEN G.GroupName 
							WHEN 3 THEN IFNULL(U.FirstName,"") 
							ELSE "" END) AS FirstName', FALSE);

        $this->db->select('(CASE FM.ModuleID  
							WHEN 3 THEN IFNULL(U.LastName,"") 
							ELSE "" END) AS LastName', FALSE);

        $this->db->select('(CASE FM.ModuleID  
							WHEN 1 THEN IFNULL(G.MemberCount,"")
							ELSE "" END) AS TotalMember', FALSE);

        //$this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,"")) AS Name', FALSE);

        $this->db->select("FM.ForumManagerID,FM.ModuleEntityID,FM.ModuleRoleID");
        $this->db->from(FORUMMANAGER . " AS FM");
        $this->db->join(USERS . " U", "U.UserID=FM.ModuleEntityID AND FM.ModuleID=3", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=FM.ModuleEntityID AND FM.ModuleID=1", "LEFT");
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->where('FM.ForumID', $forum_id);
        if (!empty($search_keyword)) {
            $search_keyword = $this->db->escape_like_str($search_keyword);
            $this->db->having("name LIKE '%" . $search_keyword . "%'", NULL, FALSE);
        }
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result_data['TotalRecords'] = $temp_q->num_rows();
        if ($page_no) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $result = $query->result_array();
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        foreach ($result as $key => $val) {
            $module_id = $val['ModuleID'];
            $entity_id = $val['ModuleEntityID'];
            if ($module_id == 1) {
                $group_url_details = $this->group_model->get_group_details_by_id($entity_id, '', array(
                    'GroupName' => $val['Name'],
                    'GroupGUID' => $val['ModuleEntityGUID'],
                ));
                $result[$key]['ProfileURL'] = $this->group_model->get_group_url($entity_id, $group_url_details['GroupNameTitle'], false, 'index');
            }
            $result[$key]['ShowFriendsBtn'] = 0;
            $result[$key]['Location'] = '';
            if ($module_id == 3) {
                // Privacy check and set / unset keys according to it                  
                if ($user_id != $entity_id) {
                    $result[$key]['FriendStatus'] = $this->friend_model->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request

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
                    $result[$key]['Location'] = $Location;
                    $result[$key]['ShowFriendsBtn'] = 1;
                    if (!$this->settings_model->isDisabled(25)) {
                        //If message module is not disabled
                        $result[$key]['ShowMessageBtn'] = 1;
                    } else {
                        $result[$key]['ShowMessageBtn'] = 0;
                    }

                    $users_relation = get_user_relation($user_id, $entity_id);
                    $privacy_details = $this->privacy_model->details($entity_id);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label['Value']) && isset($privacy_label[$privacy])) {

                                if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $result[$key]['Location'] = '';
                                }
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $result[$key]['ProfilePicture'] = 'user_default.jpg';
                                }

                                if ($privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $result[$key]['ShowFriendsBtn'] = 0;
                                }
                                if ($result[$key]['ShowMessageBtn'] == 1 && $privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $result[$key]['ShowMessageBtn'] = 0;
                                }
                            }
                        }
                    }

                    if (!empty($blocked_users) && in_array($entity_id, $blocked_users)) {
                        $result[$key]['ShowFriendsBtn'] = 0;
                        $result[$key]['ShowMessageBtn'] = 0;
                    }
                }
            }

            //unset($result[$key]['ModuleEntityID']);
        }
        $result_data['Data'] = $result;
        return $result_data;
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function forum_name($user_id) {
        $Return = array();
        $Return['Data'] = array();
        $Return['TotalRecords'] = '';
        $this->db->select('F.ForumID,F.ForumGUID,F.Name', FALSE);
        $this->db->from(FORUM . ' F');
        $this->db->where('F.StatusID', 2);
        $query = $this->db->get();
        $forums = $query->result_array();
        $forum_data = array();
        if (!empty($forums)) {
            foreach ($forums as $forum) {
                $permission = $this->check_forum_permissions($user_id, $forum['ForumID'], FALSE);
                if ($permission['IsAdmin']) {
                    $forum_data[] = $forum;
                }
            }
        }
        return $forum_data;
    }

    /**
     * 
     * @param type $forum_category_id
     * @param type $user_id
     * @param type $page_no
     * @param type $page_size
     * @param type $permissions
     * @param type $search_keyword
     * @return array
     */
    function category_visibility_suggestion($forum_category_id, $user_id, $page_no, $page_size, $permissions, $search_keyword) {
        $result['Data'] = array();
        $result['TotalRecords'] = 0;
        $result = array();
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $group_ids = $this->group_model->get_users_groups($user_id);
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        if (!$permissions['IsAdmin'] && empty($friends) && empty($group_ids)) {
            return $result;
        }
        $sql_array = array();
        $where_search_user = '';
        $where_search_group = '';
        if ($search_keyword) {
            $search_keyword = $this->db->escape_like_str($search_keyword);
            $where_search_user = " AND (U.FirstName LIKE '%" . $search_keyword . "%' OR U.LastName LIKE '%" . $search_keyword . "%' )";
            $where_search_group = " AND (G.GroupName LIKE '%" . $search_keyword . "%' )";
        }
        
        $forum_category_id = $this->db->escape_str($forum_category_id);
        
        $sql_user = "SELECT CONCAT(FirstName,' ',LastName) as Name, UserID as ModuleEntityID,UserGUID as ModuleEntityGUID,'' as TotalMember, IF(ProfilePicture='','',ProfilePicture) as ProfilePicture, '3' as ModuleID,PU.Url as ProfileUrl,(select count(FCV2.ModuleEntityID) from  " . FORUMCATEGORYVISIBILITY . "  FCV2 where FCV2.AddedBy = " . $user_id . " AND FCV2.ModuleID =3 AND FCV2.ModuleEntityID = U.UserID) as FrequentMembers FROM Users as U JOIN " . PROFILEURL . " as PU ON U.UserID=PU.EntityID AND PU.StatusID=2 AND PU.EntityType='User' WHERE  U.StatusID IN (1,2) AND U.UserID not in (SELECT FCV_U.ModuleEntityID FROM " . FORUMCATEGORYVISIBILITY . " FCV_U WHERE  FCV_U.ModuleID=3 AND FCV_U.ForumCategoryID=" . $forum_category_id . ")AND U.UserID not in (SELECT FCM_U.ModuleEntityID FROM " . FORUMCATEGORYMEMBER . " FCM_U WHERE  FCM_U.ModuleID=3 AND FCM_U.ForumCategoryID=" . $forum_category_id . ")" . $where_search_user;
        $sql_group = " SELECT G.GroupName as Name, G.GroupID as ModuleEntityID, G.GroupGUID as ModuleEntityGUID,G.MemberCount as TotalMember, if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') as ProfilePicture, '1' as ModuleID,'' as ProfileUrl,(select count(FCV3.ModuleEntityID) FROM  " . FORUMCATEGORYVISIBILITY . "  FCV3 where FCV3.AddedBy = " . $user_id . " AND FCV3.ModuleID =1 AND FCV3.ModuleEntityID = G.GroupID) as FrequentMembers  FROM Groups as G WHERE G.IsPublic IN (0,1) AND G.StatusID=2 AND G.GroupID not in (SELECT FCV_G.ModuleEntityID FROM " . FORUMCATEGORYVISIBILITY . " FCV_G WHERE  FCV_G.ModuleID=1 AND FCV_G.ForumCategoryID=" . $forum_category_id . ") AND G.GroupID not in (SELECT FCM_G.ModuleEntityID FROM " . FORUMCATEGORYMEMBER . " FCM_G WHERE  FCM_G.ModuleID=1 AND FCM_G.ForumCategoryID=" . $forum_category_id . ") " . $where_search_group;

        if ($permissions['IsSuperAdmin']) {
            $sql_array[] = $sql_user;
            $sql_array[] = $sql_group;
        } else {
            if (!empty($friends)) {
                $blocked_users = $this->activity_model->block_user_list($user_id, 3);
                if ($blocked_users) {
                    $privacy_condition = " AND U.UserID NOT IN (" . implode(',', $blocked_users) . ") ";
                    $sql_user .= " AND UserID IN (" . implode(',', $friends) . ") " . $privacy_condition . " ";
                }
            }
            if (!empty($group_ids)) {
                $sql_group .= " AND G.GroupID IN (" . implode(',', $group_ids) . ")";
            }
            $sql_array[] = $sql_user;
            $sql_array[] = $sql_group;
        }
        if (!empty($sql_array)) {
            $query = implode('UNION', $sql_array);
            $temp_q = $this->db->query($query);
            $result['TotalRecords'] = $temp_q->num_rows();

            $query .= " ORDER BY FrequentMembers DESC";
            if ($page_no) {
                $query .= " LIMIT $page_size OFFSET " . $this->get_pagination_offset($page_no, $page_size);
            }

            $query = $this->db->query($query);

            //echo $this->db->last_query();die;
            //$result['Data']=$query->result_array();
            $final_result = array();
            foreach ($query->result_array() as $row) {
                $row['Location'] = '';
                if ($row['ModuleID'] == 3) {
                    $LocationArr = $this->user_model->get_user_location($row['ModuleEntityID']);
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
                    $row['Location'] = $Location;
                    $users_relation = get_user_relation($user_id, $row['ModuleEntityID']);
                    $privacy_details = $this->privacy_model->details($row['ModuleEntityID']);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label['Value']) && isset($privacy_label[$privacy])) {

                                if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['Location'] = '';
                                }
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['ProfilePicture'] = '';
                                }
                            }
                        }
                    }
                }
                $final_result[] = $row;
            }
            $result['Data'] = $final_result;
        }
        return $result;
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function forum_category_name($forum_id, $user_id, $permissions) {
        $user_groups_array = $this->group_model->get_users_groups($user_id);
        $user_groups = 0;
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('FC.ForumCategoryID,FC.ForumCategoryGUID,FC.Name', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.ForumID', $forum_id);
        $this->db->where('FC.StatusID', 2);
        $this->db->where('FC.ParentCategoryID', 0);
        $this->db->group_by('FC.ForumCategoryID');

        if (!$permissions['IsCreator'] && !$permissions['IsSuperAdmin'] && !$permissions['IsAdmin']) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")         
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";
            $this->db->where($condition, NULL, FALSE);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    function get_forum_category_ids($forum_ids, $user_id) {
        $user_groups_array = $this->group_model->get_users_groups($user_id);
        $user_groups = 0;
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('GROUP_CONCAT(DISTINCT(FC.ForumCategoryID)) as ForumCategoryIDs', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where_in('FC.ForumID', $forum_ids);
        $this->db->where('FC.StatusID', 2);


        $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
        $condition = " CASE 
                            WHEN 
                                FC.Visibility=2
                                THEN 
                                ( CASE 
                                        WHEN FCV.ModuleID = 3 
                                            THEN FCV.ModuleEntityID = " . $user_id . "  
                                        WHEN FCV.ModuleID = 1 
                                            THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                        WHEN FCM.ModuleID = 3 
                                            THEN FCM.ModuleEntityID = " . $user_id . "  
                                        WHEN FCM.ModuleID = 1 
                                            THEN FCM.ModuleEntityID IN (" . $user_groups . ")         
                                    ELSE
                                    '' 
                                    END 
                                )
                    ELSE
                    true 
                    END 
                ";
        $this->db->where($condition, NULL, FALSE);

        $query = $this->db->get();
        $result = $query->row_array();
        if (!empty($result['ForumCategoryIDs'])) {
            return explode(',', $result['ForumCategoryIDs']);
        } else {
            return array();
        }
    }

    /**
     * 
     * @param type $forum_category_id
     * @param type $user_id
     * @param type $page_no
     * @param type $page_size
     * @param type $permissions
     * @param type $search_keyword
     * @return type
     */
    function get_category_visibilty($forum_category_id, $user_id, $page_no, $page_size, $permissions, $search_keyword) {
        $result_data['Data'] = array();
        $result_data['TotalRecords'] = 0;
        $result = array();


        $this->db->select('(CASE FCV.ModuleID 
                                            WHEN 1 THEN 1 
                                            WHEN 3 THEN 3
                                            ELSE "" END) AS ModuleID', FALSE);

        $this->db->select('(CASE FCV.ModuleID 
                                            WHEN 1 THEN G.GroupGUID 
                                            WHEN 3 THEN U.UserGUID 
                                            ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('(CASE FCV.ModuleID 
                                            WHEN 3 THEN PU.Url   
                                            ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE FCV.ModuleID 
                                            WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
                                            WHEN 3 THEN U.ProfilePicture   
                                            ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('(CASE FCV.ModuleID 
                                            WHEN 1 THEN G.GroupName 
                                            WHEN 3 THEN CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,"")) 
                                            ELSE "" END) AS Name', FALSE);

        $this->db->select('(CASE FCV.ModuleID  
                                            WHEN 1 THEN IFNULL(G.MemberCount,"")
                                            ELSE "" END) AS TotalMember', FALSE);

        //$this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,"")) AS Name', FALSE);

        $this->db->select("FCV.ForumCategoryVisibilityID,FCV.ModuleEntityID");
        $this->db->from(FORUMCATEGORYVISIBILITY . " AS FCV");
        $this->db->join(USERS . " U", "U.UserID=FCV.ModuleEntityID AND FCV.ModuleID=3", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=FCV.ModuleEntityID AND FCV.ModuleID=1", "LEFT");
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->where('FCV.ForumCategoryID', $forum_category_id);
        if (!empty($search_keyword)) {
            $search_keyword = $this->db->escape_like_str($search_keyword);
            $this->db->having("name LIKE '%" . $search_keyword . "%'", NULL, FALSE);
        }
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result_data['TotalRecords'] = $temp_q->num_rows();
        if ($page_no) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();
        //$result = $query->result_array();
        $final_result = array();
        foreach ($query->result_array() as $row) {
            $row['Location'] = '';
            if ($row['ModuleID'] == 3) {
                $LocationArr = $this->user_model->get_user_location($row['ModuleEntityID']);
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
                $row['Location'] = $Location;
                $users_relation = get_user_relation($user_id, $row['ModuleEntityID']);
                $privacy_details = $this->privacy_model->details($row['ModuleEntityID']);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label']) {
                    foreach ($privacy_details['Label'] as $privacy_label) {
                        if (isset($privacy_label['Value']) && isset($privacy_label[$privacy])) {

                            if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $row['Location'] = '';
                            }
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                $row['ProfilePicture'] = '';
                            }
                        }
                    }
                }
            }
            $final_result[] = $row;
        }
        $result_data['Data'] = $final_result;
        //$result_data['Data']=$result;
        return $result_data;
    }

    /**
     * 
     * @param type $forum_category_id
     * @param type $user_id
     * @param type $page_no
     * @param type $page_size
     * @param type $permissions
     * @param type $search_keyword
     * @return type
     */
    function category_member_suggestion($forum_category_id, $user_id, $page_no, $page_size, $permissions, $search_keyword) {
        $forum_category_visibility = get_detail_by_id($forum_category_id, 34, "Visibility", 1);
        $result['Data'] = array();
        $result['TotalRecords'] = 0;
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $group_ids = $this->group_model->get_users_groups($user_id);
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        if (!$permissions['IsAdmin'] && empty($friends) && empty($group_ids)) {
            return $result;
        }
        $sql_array = array();
        $where_search_user = '';
        $where_search_group = '';
        if ($search_keyword) {
            $search_keyword = $this->db->escape_like_str($search_keyword);
            $where_search_user = " AND (U.FirstName LIKE '%" . $search_keyword . "%' OR U.LastName LIKE '%" . $search_keyword . "%' )";
            $where_search_group = " AND (G.GroupName LIKE '%" . $search_keyword . "%' )";
        }

        $forum_category_id = $this->db->escape_str($forum_category_id);
        
        $sql_user = "SELECT CONCAT(FirstName,' ',LastName) as Name, UserID as ModuleEntityID,UserGUID as ModuleEntityGUID,'' as TotalMember, IF(ProfilePicture='','',ProfilePicture) as ProfilePicture, '3' as ModuleID,PU.Url as ProfileUrl,(select count(FCM2.ModuleEntityID) from  " . FORUMCATEGORYMEMBER . "  FCM2 where FCM2.AddedBy = " . $user_id . " AND FCM2.ModuleID =3 AND FCM2.ModuleEntityID = U.UserID) as FrequentMembers FROM Users as U JOIN " . PROFILEURL . " as PU ON U.UserID=PU.EntityID AND PU.StatusID=2 AND PU.EntityType='User' WHERE  U.StatusID NOT IN (3,4) AND U.UserID not in (SELECT ModuleEntityID FROM " . FORUMCATEGORYMEMBER . " FCM_U WHERE  FCM_U.ModuleID=3 AND FCM_U.ForumCategoryID=" . $forum_category_id . ") " . $where_search_user;
        $sql_group = " SELECT G.GroupName as Name, G.GroupID as ModuleEntityID, G.GroupGUID as ModuleEntityGUID,G.MemberCount as TotalMember, if(G.GroupImage!='',G.GroupImage,'group-no-img.jpg') as ProfilePicture, '1' as ModuleID,'' as ProfileUrl,(select count(FCM3.ModuleEntityID) FROM  " . FORUMCATEGORYMEMBER . "  FCM3 where FCM3.AddedBy = " . $user_id . " AND FCM3.ModuleID =1 AND FCM3.ModuleEntityID = G.GroupID) as FrequentMembers  FROM Groups as G WHERE G.IsPublic IN (0,1) AND G.StatusID=2 AND G.GroupID not in (SELECT ModuleEntityID FROM " . FORUMCATEGORYMEMBER . " FCM_G WHERE  FCM_G.ModuleID=1 AND FCM_G.ForumCategoryID=" . $forum_category_id . ") " . $where_search_group;

        if ($permissions['IsSuperAdmin']) {
            if ($forum_category_visibility != 2) {
                $sql_array[] = $sql_user;
                $sql_array[] = $sql_group;
            } else {
                $users_list = array(0);
                $this->db->select('GROUP_CONCAT(ModuleEntityID) as GroupIDs');
                $this->db->from(FORUMCATEGORYVISIBILITY . ' FCV');
                $this->db->where('ModuleID', '1');
                $this->db->where('ForumCategoryID', $forum_category_id);
                $query = $this->db->get();
                $group_data = $query->row_array();
                if ($group_data['GroupIDs']) {
                    $group_ids = explode(',', $group_data['GroupIDs']);
                    $this->group_model->get_group_member_recursive($users_list, $group_ids);
                }
                $sql_user .= " AND ( U.UserID IN (SELECT ModuleEntityID FROM " . FORUMCATEGORYVISIBILITY . " WHERE ModuleID =3 AND ForumCategoryID= " . $forum_category_id . ") OR U.UserID IN (" . implode(',', $users_list) . ") )  ";
                $sql_group .= " AND G.GroupID IN ((SELECT ModuleEntityID FROM " . FORUMCATEGORYVISIBILITY . " WHERE ModuleID =1 AND ForumCategoryID= " . $forum_category_id . ") )  ";
                $sql_array[] = $sql_user;
                $sql_array[] = $sql_group;
            }
        } else {
            if (!empty($friends)) {
                $blocked_users = $this->activity_model->block_user_list($user_id, 3);
                if ($forum_category_visibility != 2) {
                    $sql_user .= " AND UserID IN (" . implode(',', $friends) . ")  ";
                } else {
                    $users_list = array(0);
                    $this->db->select('GROUP_CONCAT(ModuleEntityID) as GroupIDs');
                    $this->db->from(FORUMCATEGORYVISIBILITY . ' FCV');
                    $this->db->where('ModuleID', '1');
                    $this->db->where('ForumCategoryID', $forum_category_id);
                    $query = $this->db->get();
                    $group_data = $query->row_array();
                    if ($group_data['GroupIDs']) {
                        $group_ids = explode(',', $group_data['GroupIDs']);
                        $this->group_model->get_group_member_recursive($users_list, $group_ids);
                    }
                    $sql_user .= " AND ( U.UserID IN (SELECT ModuleEntityID FROM " . FORUMCATEGORYVISIBILITY . " WHERE ModuleID =3 AND ForumCategoryID= " . $forum_category_id . ") OR U.UserID IN (" . implode(',', $users_list) . ") )  ";
                }
                if ($blocked_users) {
                    $sql_user .= " AND U.UserID NOT IN (" . implode(',', $blocked_users) . ") ";
                }
                $sql_array[] = $sql_user;
            }
            if (!empty($group_ids)) {
                if ($forum_category_visibility != 2) {
                    $sql_group .= " AND G.GroupID IN (" . implode(',', $group_ids) . ")";
                } else {
                    $sql_group .= " AND G.GroupID IN ((SELECT ModuleEntityID FROM " . FORUMCATEGORYVISIBILITY . " WHERE ModuleID =1 AND ForumCategoryID= " . $forum_category_id . ") )  ";
                }

                $sql_array[] = $sql_group;
            }
        }
        if (!empty($sql_array)) {
            $query = implode('UNION', $sql_array);
            $temp_q = $this->db->query($query);
            $result['TotalRecords'] = $temp_q->num_rows();

            $query .= " ORDER BY FrequentMembers DESC";
            if ($page_no) {
                $query .= " LIMIT $page_size OFFSET " . $this->get_pagination_offset($page_no, $page_size);
            }

            $query = $this->db->query($query);
            //echo $this->db->last_query();die;
            //$result['Data']=$query->result_array();

            $final_result = array();
            foreach ($query->result_array() as $row) {
                $row['Location'] = '';
                if ($row['ModuleID'] == 3) {
                    $LocationArr = $this->user_model->get_user_location($row['ModuleEntityID']);
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
                    $row['Location'] = $Location;
                    $users_relation = get_user_relation($user_id, $row['ModuleEntityID']);
                    $privacy_details = $this->privacy_model->details($row['ModuleEntityID']);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label['Value']) && isset($privacy_label[$privacy])) {

                                if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['Location'] = '';
                                }
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['ProfilePicture'] = '';
                                }
                            }
                        }
                    }
                }
                $final_result[] = $row;
            }
            $result['Data'] = $final_result;
        }
        return $result;
    }

    function can_post_on_wall($category_id, $module_id, $module_entity_id) {
        $can_post_on_wall = 0;
        $this->db->select('CanPostOnWall');
        $this->db->from(FORUMCATEGORYMEMBER);
        $this->db->where('ForumCategoryID', $category_id);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $can_post_on_wall = $query->row()->CanPostOnWall;
        }
        return $can_post_on_wall;
    }

    /**
     * 
     * @param type $forum_category_id
     * @param type $user_id
     * @param type $page_no
     * @param type $page_size
     * @param type $permissions
     * @param type $search_keyword
     * @return type
     */
    function category_members($forum_category_id, $user_id, $page_no, $page_size, $permissions, $search_keyword, $order_by, $sort_by, $expert_only = false) {
        $result_data['Data'] = array();
        $result_data['TotalRecords'] = 0;
        $result = array();


        $this->db->select('(CASE FCM.ModuleID 
                                            WHEN 1 THEN 1 
                                            WHEN 3 THEN 3
                                            ELSE "" END) AS ModuleID', FALSE);

        $this->db->select('(CASE FCM.ModuleID 
                                            WHEN 1 THEN G.GroupGUID 
                                            WHEN 3 THEN U.UserGUID 
                                            ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('(CASE FCM.ModuleID 
                                            WHEN 3 THEN PU.Url   
                                            ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE FCM.ModuleID 
                                            WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
                                            WHEN 3 THEN U.ProfilePicture   
                                            ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('(CASE FCM.ModuleID 
                                            WHEN 1 THEN G.GroupName 
                                            WHEN 3 THEN CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,"")) 
                                            ELSE "" END) AS Name', FALSE);

        $this->db->select('(CASE FCM.ModuleID  
                                            WHEN 1 THEN IFNULL(G.MemberCount,"")
                                            ELSE "" END) AS TotalMember', FALSE);

        //$this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,"")) AS Name', FALSE);

        $this->db->select("FCM.ForumCategoryMemberID,FCM.ModuleEntityID,FCM.ModuleRoleID,FCM.CanPostOnWall,FCM.IsExpert");
        $this->db->select("(SELECT COUNT(F.UserID) FROM " . FOLLOW . " F WHERE F.TypeEntityID=U.UserID AND F.Type='User' AND F.StatusID='2') as TotalFollowers", false);
        $this->db->from(FORUMCATEGORYMEMBER . " AS FCM");
        $this->db->join(USERS . " U", "U.UserID=FCM.ModuleEntityID AND FCM.ModuleID=3 AND U.StatusID NOT IN (3, 4)", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=FCM.ModuleEntityID AND FCM.ModuleID=1 AND G.StatusID=2", "LEFT");
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->where('FCM.ForumCategoryID', $forum_category_id);

        $this->db->where_not_in('U.StatusID', array(3, 4));
        if ($expert_only) {
            $this->db->where('FCM.IsExpert', $expert_only);
        }

        if (!empty($search_keyword)) {
            $search_keyword = $this->db->escape_like_str($search_keyword);
            $this->db->having("name LIKE '%" . $search_keyword . "%'", NULL, FALSE);
        }

        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result_data['TotalRecords'] = $temp_q->num_rows();

        if ($sort_by == 'ASC' || $sort_by == '') {
            $sort_by = 'ASC';
        } else {
            $sort_by = 'DESC';
        }

        if ($order_by == 'Name') {
            $this->db->order_by('Name', $sort_by);
        } elseif ($order_by == 'Admin') {
            $this->db->order_by('FCM.ModuleRoleID', $sort_by);
        } elseif ($order_by == 'Expert') {
            $this->db->order_by('FCM.IsExpert', $sort_by);
        } elseif ($order_by == 'CanPost') {
            $this->db->order_by('FCM.CanPostOnWall', $sort_by);
        } elseif ($order_by == 'Random') {
            $this->db->order_by('RAND()', NULL, FALSE);
        } else {
            $this->db->order_by('FCM.ForumCategoryMemberID', 'ASC');
        }

        if ($page_no) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }

        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        $result = $query->result_array();
        $final_result = array();

        if (!$this->settings_model->isDisabled(10)) {
            $enableFriendModule = 1;
        } else {
            $enableFriendModule = 0;
        }

        if (!$this->settings_model->isDisabled(25)) {
            $enableMessageModule = 1;
        } else {
            $enableMessageModule = 0;
        }

        foreach ($result as $row) {
            $module_id = $row['ModuleID'];
            $entity_id = $row['ModuleEntityID'];
            $row['ShowFriendsBtn'] = 0;
            $row['ShowMessageBtn'] = 0;
            if ($enableFriendModule) {
                $row['ShowFriendsBtn'] = 1;
            }

            if ($enableMessageModule) {
                $row['ShowMessageBtn'] = 1;
            }

            $row['Location'] = '';

            if ($module_id == 3) {
                $row['Discussions'] = $this->user_discussions_count($entity_id, $forum_category_id);
                // Privacy check and set / unset keys according to it                  
                if ($user_id != $entity_id) {
                    if ($enableFriendModule)
                        $row['FriendStatus'] = $this->friend_model->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request

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
                    $row['Location'] = $Location;

                    $users_relation = get_user_relation($user_id, $entity_id);
                    $privacy_details = $this->privacy_model->details($entity_id);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label']) {
                        foreach ($privacy_details['Label'] as $privacy_label) {
                            if (isset($privacy_label['Value']) && isset($privacy_label[$privacy])) {
                                if ($privacy_label['Value'] == 'view_location' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['Location'] = '';
                                }
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['ProfilePicture'] = 'user_default.jpg';
                                }

                                if ($row['ShowFriendsBtn'] == 1 && $privacy_label['Value'] == 'friend_request' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['ShowFriendsBtn'] = 0;
                                }
                                if ($row['ShowMessageBtn'] == 1 && $privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)) {
                                    $row['ShowMessageBtn'] = 0;
                                }
                            }
                        }
                    }

                    if (!empty($blocked_users) && in_array($entity_id, $blocked_users)) {
                        $row['ShowFriendsBtn'] = 0;
                        $row['ShowMessageBtn'] = 0;
                    }

                    if ($row['ShowFriendsBtn'] == 0) {
                        $row['ShowFollowBtn'] = 1;
                        $follow = "select FollowID from Follow where (TypeEntityID = " . $entity_id . " and UserID = " . $user_id . ") and type='User'";
                        $following = $this->db->query($follow)->num_rows();
                        if ($following) {
                            $row['IsFollow'] = '1';
                        } else {
                            $row['IsFollow'] = '0';
                        }
                    }
                }
            }
            $final_result[] = $row;
        }
        $result_data['Data'] = $final_result;
        return $result_data;
    }

    /**
     * Function Name: set_member_permission
     * @param 
     * Description: Set user permission for group
     */
    function set_member_permission($module_id, $module_entity_id, $field, $value, $forum_category_id, $user_id) {
        $this->db->set($field, $value);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('ForumCategoryID', $forum_category_id);
        $this->db->update(FORUMCATEGORYMEMBER);
    }

    /**
     * 
     * @param type $forum_category_id
     * @return type
     */
    function get_category_detail($forum_category_id) {
        $result = array();
        $this->db->select('FC.*,F.URL as F_URL,IFNULL(M.ImageName,"") as ProfilePicture, M.MediaGUID,F.Name as ForumName', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUM . ' F', 'FC.ForumID=F.ForumID');
        $this->db->join(MEDIA . ' M', 'FC.MediaID = M.MediaID', 'left');
        $this->db->where('ForumCategoryID', $forum_category_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
        }
        return $result;
    }

    function check_forum_category_member_permissions($user_id, $forum_category_id, $with_details = TRUE) {
        $permissions = array();
        // Set default permissions
        $permissions['IsCreator'] = FALSE;
        $permissions['IsSuperAdmin'] = FALSE;
        $permissions['IsAdmin'] = FALSE;
        $permissions['IsMember'] = FALSE;
        $permissions['Details'] = array();

        $forum_category_status = get_detail_by_id($forum_category_id, 34, "StatusID", 1);
        //$forum_category_detail = $this->forum_model->category_details($forum_category_id,$user_id);
        if ($forum_category_status == 2) {
            /* if($with_details)
              {
              $permissions['Details'] = $forum_category_detail;
              } */

            $membership = $this->forum_model->check_category_visibility_membership($forum_category_id, $user_id);
            if ($membership) {
                $permissions['IsMember'] = TRUE;
            }
        }

        return $permissions;
    }

    /**
     * 
     * @param type $fourm_id
     * @param type $members
     * @param type $added_by
     */
    function create_forum_category_superadmin($fourm_id, $members, $added_by) {
        if ($fourm_id && !empty($members)) {
            foreach ($members as $member) {
                $this->assign_category_admin($member, $added_by, $fourm_id);
            }
        }
    }

    /**
     * [assign_fourm_admin Insert/Update user assignmet for fourms and its categories]
     * @param  [array] $member_details [Member details]
     * @param  [int] $user_id        [User ID]
     * @param  [int] $role_id        [Assign Role ID]
     * @return [type]                 [description]
     */
    function assign_fourm_admin($member_details, $added_by) {
        $add_manager_array = array();
        $delete_manager_array = array();

        $this->db->select('F.ForumID', FALSE);
        $this->db->from(FORUM . ' F');

        $query = $this->db->get();
        $forums = $query->result_array();
        $add_manager_array = array();
        $update_manager_array = array();
        foreach ($forums as $forum) {
            $member = array();
            $this->db->select('ForumManagerID');
            $this->db->from(FORUMMANAGER);
            $this->db->where('ForumID', $forum['ForumID']);
            $this->db->where('ModuleID', $member_details['ModuleID']);
            $this->db->where('ModuleEntityID', $member_details['ModuleEntityID']);
            $query = $this->db->get();

            if (!$query->num_rows()) {
                $member['ForumID'] = $forum['ForumID'];
                $member['ModuleID'] = $member_details['ModuleID'];
                $member['ModuleEntityID'] = $member_details['ModuleEntityID'];
                $member['ModuleRoleID'] = 11;
                $member['AddedBy'] = $added_by;
                $member['IsDirect'] = 0;
                $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $add_manager_array[] = $member;
            } else {
                $forum_manager = $query->row_array();
                $member['ForumManagerID'] = $forum_manager['ForumManagerID'];
                $member['ModuleRoleID'] = 11;
                $member['AddedBy'] = $added_by;
                $member['IsDirect'] = 0;
                $update_manager_array[] = $member;
            }
        }
        if (!empty($add_manager_array)) { // add manager
            $this->db->insert_batch(FORUMMANAGER, $add_manager_array);
        }
        if (!empty($update_manager_array)) { // update manager role
            $this->db->update_batch(FORUMMANAGER, $update_manager_array, 'ForumManagerID');
        }
        $this->assign_category_admin($member_details, $added_by, 0, 0, 0);
    }

    /**
     * [assign_category_admin description]
     * @param  [array] $member_details [Member details]
     * @param  [int] $user_id        [User ID]
     * @param  [int] $fourm_id       [Forum ID]
     * @return [type]                 [description]
     */
    function assign_category_admin($member_details, $added_by, $fourm_id = 0) {
        $this->db->select('FC.ForumCategoryID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        if ($fourm_id) {
            $this->db->where('FC.ForumID', $fourm_id);
        }
        $query = $this->db->get();
        $forums_category = $query->result_array();
        $add_admin_array = array();
        $update_admin_array = array();
        foreach ($forums_category as $category) {
            $member = array();
            $this->db->select('ForumCategoryMemberID');
            $this->db->from(FORUMCATEGORYMEMBER);
            $this->db->where('ForumCategoryID', $category['ForumCategoryID']);
            $this->db->where('ModuleID', $member_details['ModuleID']);
            $this->db->where('ModuleEntityID', $member_details['ModuleEntityID']);
            $query = $this->db->get();
            if (!$query->num_rows()) {
                $member['ForumCategoryID'] = $category['ForumCategoryID'];
                $member['ModuleID'] = $member_details['ModuleID'];
                $member['ModuleEntityID'] = $member_details['ModuleEntityID'];
                $member['ModuleRoleID'] = 15;
                $member['AddedBy'] = $added_by;
                $member['IsDirect'] = 0;
                $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $add_admin_array[] = $member;
            } else {
                $forum_category_member = $query->row_array();
                $member['ForumCategoryMemberID'] = $forum_category_member['ForumCategoryMemberID'];
                $member['ForumCategoryID'] = $category['ForumCategoryID'];
                $member['ModuleRoleID'] = 15;
                $member['AddedBy'] = $added_by;
                $member['IsDirect'] = 0;
                $update_admin_array[] = $member;
            }
            // $this->assign_subcategory_admin($member_details,$added_by, $category['ForumCategoryID']);
        }
        if (!empty($add_admin_array)) { // add admin
            $this->db->insert_batch(FORUMCATEGORYMEMBER, $add_admin_array);
        }
        if (!empty($update_admin_array)) { // add admin
            $this->db->update_batch(FORUMCATEGORYMEMBER, $update_admin_array, 'ForumCategoryMemberID');
        }
    }

    /**
     * [assign_category_admin description]
     * @param  [array] $member_details [Member details]
     * @param  [int] $user_id        [User ID]
     * @param  [int] $fourm_id       [Forum ID]
     * @return [type]                 [description]
     */
    function assign_subcategory_admin($member_details, $added_by, $fourm_category_id = 0) {
        $this->db->select('FC.ForumCategoryID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        if ($fourm_category_id) {
            $this->db->where('FC.ParentCategoryID', $fourm_category_id);
        }

        $query = $this->db->get();
        $forums_category = $query->result_array();
        $add_admin_array = array();
        $update_admin_array = array();
        foreach ($forums_category as $category) {
            $member = array();
            $this->db->select('ForumCategoryMemberID');
            $this->db->from(FORUMCATEGORYMEMBER);
            $this->db->where('ForumCategoryID', $category['ForumCategoryID']);
            $this->db->where('ModuleID', $member_details['ModuleID']);
            $this->db->where('ModuleEntityID', $member_details['ModuleEntityID']);
            $query = $this->db->get();
            if (!$query->num_rows()) {
                $member['ForumCategoryID'] = $category['ForumCategoryID'];
                $member['ModuleID'] = $member_details['ModuleID'];
                $member['ModuleEntityID'] = $member_details['ModuleEntityID'];
                $member['ModuleRoleID'] = 15;
                $member['AddedBy'] = $added_by;
                $member['IsDirect'] = 0;
                $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $add_admin_array[] = $member;
            } else {
                $forum_category_member = $query->row_array();
                $member['ForumCategoryMemberID'] = $forum_category_member['ForumCategoryMemberID'];
                $member['ForumCategoryID'] = $category['ForumCategoryID'];
                $member['ModuleRoleID'] = 15;
                $member['AddedBy'] = $added_by;
                $member['IsDirect'] = 0;
                $update_admin_array[] = $member;
            }
        }
        if (!empty($add_admin_array)) { // add admin
            $this->db->insert_batch(FORUMCATEGORYMEMBER, $add_admin_array);
        }
        if (!empty($update_admin_array)) { // add admin
            $this->db->update_batch(FORUMCATEGORYMEMBER, $update_admin_array, 'ForumCategoryMemberID');
        }
    }

    /**
     * Function Name: create_forum_superadmin
     * Description:Get all superadmin/admin for forum and set superadmin of forum category
     * @param type $forum_id
     * @param type $added_by
     */
    function create_forum_superadmin($forum_id, $added_by) {
        $this->db->select('UserID');
        $this->db->from(USERROLES);
        $this->db->where('RoleID', '1');
        $this->db->where_not_in('UserID', $added_by);
        $this->db->group_by('UserID');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $add_manager_array = array();
            $update_manager_array = array();
            foreach ($query->result_array() as $user) {
                $member = array();
                $this->db->select('ForumManagerID');
                $this->db->from(FORUMMANAGER);
                $this->db->where('ForumID', $forum_id);
                $this->db->where('ModuleID', 3);
                $this->db->where('ModuleEntityID', $user['UserID']);
                $query = $this->db->get();

                if (!$query->num_rows()) {
                    $member['ForumID'] = $forum_id;
                    $member['ModuleID'] = 3;
                    $member['ModuleEntityID'] = $user['UserID'];
                    $member['ModuleRoleID'] = 11;
                    $member['AddedBy'] = $added_by;
                    $member['IsDirect'] = 0;
                    $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $add_manager_array[] = $member;
                } else if ($user['ModuleEntityID'] != $added_by) {
                    $forum_manager = $query->row_array();
                    $member['ForumManagerID'] = $forum_manager['ForumManagerID'];
                    $member['ModuleRoleID'] = 11;
                    $member['AddedBy'] = $added_by;
                    $member['IsDirect'] = 0;
                    $update_manager_array[] = $member;
                }
            }
            if (!empty($add_manager_array)) { // add manager
                $this->db->insert_batch(FORUMMANAGER, $add_manager_array);
            }
            if (!empty($update_manager_array)) { // update manager role
                $this->db->update_batch(FORUMMANAGER, $update_manager_array, 'ForumManagerID');
            }
        }
    }

    /**
     * [update group default permisson]
     * @param   [Array] $input          [Array]
     * @return  [BOOL]   TRUE         
     */
    function update_default_permisson($input) {
        $this->db->where('ForumCategoryID', $input['forum_category_id']);
        $this->db->update(FORUMCATEGORY, array('param' => $input['param']));
        return true;
    }

    /**
     * [check_url description]
     * @param  [string] $url       [URL]
     * @param  [int] $module_id [MODULE ID]
     * @return [array]            [details of url]
     */
    function check_url($url, $module_id, $forum_id = '') {
        if ($module_id == 33) {
            $this->db->select('ForumID,ForumGUID,Name', FALSE);
            $this->db->from(FORUM);
        } else {
            $this->db->select('ForumCategoryID,ForumCategoryGUID,Name', FALSE);
            $this->db->from(FORUMCATEGORY);
        }
        $this->db->where('URL', strtolower($url));
        if ($forum_id) {
            $this->db->where('ForumID', $forum_id);
        }
        $this->db->where('StatusID', 2);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        return FALSE;
    }

    /**
     * Function Name: set_category_superadmin
     * Description:Get all superadmin/admin for forum and set superadmin of forum category
     * @param type $forum_id
     * @param type $forum_category_id
     * @param type $added_by
     */
    function set_category_superadmin($forum_id, $forum_category_id, $added_by) {
        $notify_member = array();
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(FORUMMANAGER);
        $this->db->where('ForumID', $forum_id);
        $this->db->where_in('ModuleRoleID', array(10, 11, 12));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $add_member_array = array();
            $update_member_array = array();
            foreach ($query->result_array() as $item) {
                $member = array();
                $this->db->select('ForumCategoryMemberID');
                $this->db->from(FORUMCATEGORYMEMBER);
                $this->db->where('ForumCategoryID', $forum_category_id);
                $this->db->where('ModuleID', $item['ModuleID']);
                $this->db->where('ModuleEntityID', $item['ModuleEntityID']);
                $query = $this->db->get();
                if ($item['ModuleEntityID'] != $added_by && $item['ModuleID'] == 3) {
                    $notify_member[] = $item['ModuleEntityID'];
                }
                if (!$query->num_rows()) {
                    $member['ForumCategoryID'] = $forum_category_id;
                    $member['ModuleID'] = $item['ModuleID'];
                    $member['ModuleEntityID'] = $item['ModuleEntityID'];
                    $member['ModuleRoleID'] = 15;
                    $member['AddedBy'] = $added_by;
                    $member['CanPostOnWall'] = 1;
                    $member['IsExpert'] = 0;
                    $member['IsDirect'] = 0;
                    $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $add_member_array[] = $member;
                } else if ($item['ModuleEntityID'] != $added_by && $item['ModuleID'] == 3) {
                    $category_member = $query->row_array();
                    $member['ForumCategoryMemberID'] = $category_member['ForumCategoryMemberID'];
                    $member['ModuleRoleID'] = 15;
                    $member['AddedBy'] = $added_by;
                    $member['IsDirect'] = 0;
                    $update_member_array[] = $member;
                }
            }
            if (!empty($add_member_array)) { // add manager
                $this->db->insert_batch(FORUMCATEGORYMEMBER, $add_member_array);
            }
            if (!empty($update_member_array)) { // update manager role
                $this->db->update_batch(FORUMCATEGORYMEMBER, $update_member_array, 'ForumCategoryMemberID');
            }
            if (!empty($notify_member)) {
                $parameters[0]['ReferenceID'] = $added_by;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $forum_category_id;
                $parameters[1]['Type'] = 'ForumCategory';
                $this->notification_model->add_notification(137, $added_by, $notify_member, $forum_category_id, $parameters);
            }
        }
        //$this->set_subcategory_superadmin($forum_category_id,$added_by);
    }

    /**
     * Function Name: set_subcategory_superadmin
     * Description:Get all superadmin/admin for parent category and set superadmin of subcategory
     * @param type $parent_category_id
     * @param type $sub_category_id
     * @param type $added_by
     */
    function set_subcategory_superadmin($parent_category_id, $sub_category_id, $added_by) {
        $notify_member = array();
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(FORUMCATEGORYMEMBER);
        $this->db->where('ForumCategoryID', $parent_category_id);
        $this->db->where_in('ModuleRoleID', array(14, 15, 16));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $add_member_array = array();
            $update_member_array = array();
            foreach ($query->result_array() as $item) {
                $member = array();
                $this->db->select('ForumCategoryMemberID');
                $this->db->from(FORUMCATEGORYMEMBER);
                $this->db->where('ForumCategoryID', $sub_category_id);
                $this->db->where('ModuleID', $item['ModuleID']);
                $this->db->where('ModuleEntityID', $item['ModuleEntityID']);
                $query = $this->db->get();
                if ($item['ModuleEntityID'] != $added_by && $item['ModuleID'] == 3) {
                    $notify_member[] = $item['ModuleEntityID'];
                }
                if (!$query->num_rows()) {
                    $member['ForumCategoryID'] = $sub_category_id;
                    $member['ModuleID'] = $item['ModuleID'];
                    $member['ModuleEntityID'] = $item['ModuleEntityID'];
                    $member['ModuleRoleID'] = 15;
                    $member['AddedBy'] = $added_by;
                    $member['CanPostOnWall'] = 1;
                    $member['IsExpert'] = 0;
                    $member['IsDirect'] = 0;
                    $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                    $add_member_array[] = $member;
                } else if ($item['ModuleEntityID'] != $added_by && $item['ModuleID'] == 3) {
                    $category_member = $query->row_array();
                    $member['ForumCategoryMemberID'] = $category_member['ForumCategoryMemberID'];
                    $member['ModuleRoleID'] = 15;
                    $member['AddedBy'] = $added_by;
                    $member['IsDirect'] = 0;
                    $update_member_array[] = $member;
                }
            }
            if (!empty($add_member_array)) { // add member
                $this->db->insert_batch(FORUMCATEGORYMEMBER, $add_member_array);
            }
            if (!empty($update_member_array)) { // update member role
                $this->db->update_batch(FORUMCATEGORYMEMBER, $update_member_array, 'ForumCategoryMemberID');
            }
            if (!empty($notify_member)) {
                $parameters[0]['ReferenceID'] = $added_by;
                $parameters[0]['Type'] = 'User';
                $parameters[1]['ReferenceID'] = $sub_category_id;
                $parameters[1]['Type'] = 'ForumCategory';
                $this->notification_model->add_notification(138, $added_by, $notify_member, $sub_category_id, $parameters);
            }
        }
    }

    /**
     * Function Name: set_subcategory_member
     * Description:Get all superadmin/admin for forum and set superadmin of forum category
     * @param type $forum_category_id
     * @param type $added_by
     * @param type $members
     */
    function set_subcategory_member($forum_category_id, $added_by, $members) {
        $this->db->select('ForumCategoryID');
        $this->db->from(FORUMCATEGORY);
        $this->db->where('ParentCategoryID', $forum_category_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $add_member_array = array();
            $update_member_array = array();
            foreach ($query->result_array() as $item) {
                foreach ($members as $item_member) {
                    if ($item_member['ModuleID'] == 3) {
                        $member = array();
                        $this->db->select('ForumCategoryMemberID');
                        $this->db->from(FORUMCATEGORYMEMBER);
                        $this->db->where('ForumCategoryID', $item['ForumCategoryID']);
                        $this->db->where('ModuleID', $item_member['ModuleID']);
                        $this->db->where('ModuleEntityID', $item_member['ModuleEntityID']);
                        $query = $this->db->get();

                        if (!$query->num_rows()) {
                            $member['ForumCategoryID'] = $item['ForumCategoryID'];
                            $member['ModuleID'] = $item_member['ModuleID'];
                            $member['ModuleEntityID'] = $item_member['ModuleEntityID'];
                            $member['ModuleRoleID'] = 17;
                            $member['AddedBy'] = $added_by;
                            $member['CanPostOnWall'] = 1;
                            $member['IsExpert'] = 0;
                            $member['IsDirect'] = 1;
                            $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                            $add_member_array[] = $member;
                        } else {
                            $category_member = $query->row_array();
                            $member['ForumCategoryMemberID'] = $category_member['ForumCategoryMemberID'];
                            $member['ModuleRoleID'] = 17;
                            $member['AddedBy'] = $added_by;
                            $member['IsDirect'] = 1;
                            $update_member_array[] = $member;
                        }
                    }
                }
            }
            if (!empty($add_member_array)) { // add member
                $this->db->insert_batch(FORUMCATEGORYMEMBER, $add_member_array);
            }
            if (!empty($update_member_array)) { // update member role
                $this->db->update_batch(FORUMCATEGORYMEMBER, $update_member_array, 'ForumCategoryMemberID');
            }
        }
    }

    /**
     * Function Name: set_subcategory_visibility
     * Description:Get all superadmin/admin for forum and set superadmin of forum category
     * @param type $forum_category_id
     * @param type $added_by
     * @param type $members
     */
    function set_subcategory_visibility_member($forum_category_id, $added_by, $members) {
        $this->db->select('ForumCategoryID');
        $this->db->from(FORUMCATEGORY);
        $this->db->where('ParentCategoryID', $forum_category_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $add_visibility_array = array();
            foreach ($query->result_array() as $item) {
                foreach ($members as $item_member) {
                    $member = array();
                    $this->db->select('ForumCategoryVisibilityID');
                    $this->db->from(FORUMCATEGORYVISIBILITY);
                    $this->db->where('ForumCategoryID', $item['ForumCategoryID']);
                    $this->db->where('ModuleID', $item_member['ModuleID']);
                    $this->db->where('ModuleEntityID', $item_member['ModuleEntityID']);
                    $query = $this->db->get();

                    if (!$query->num_rows()) {
                        $member['ForumCategoryID'] = $item['ForumCategoryID'];
                        $member['ModuleID'] = $item_member['ModuleID'];
                        $member['ModuleEntityID'] = $item_member['ModuleEntityID'];
                        $member['AddedBy'] = $added_by;
                        $member['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                        $add_visibility_array[] = $member;
                    }
                }
            }
            if (!empty($add_visibility_array)) { // add member
                $this->db->insert_batch(FORUMCATEGORYVISIBILITY, $add_visibility_array);
            }
        }
    }

    /**
     * 
     * @param type $forum_id
     * @param type $forum_category_id
     * @param type $page_no
     * @param type $page_size
     * @return type
     */
    function suggested_articles($user_id, $page_no, $page_size) {
        $result = array();
        //$user_groups_array=$this->group_model->get_users_groups($user_id);
        $user_groups_array = [0];
        $user_groups = 0;
        $follow_category = '';
        $unfollow_category = '';
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('GROUP_CONCAT(DISTINCT FC.ForumCategoryID) as ForumCategoryIDs');
        $this->db->from(FORUM . ' F');
        $this->db->join(FORUMCATEGORY . ' FC', 'F.ForumID=FC.ForumID AND FC.StatusID=2');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FC.ForumCategoryID=FCM.ForumCategoryID');
        $this->db->where('F.StatusID', 2);
        $condition = " CASE 
                            WHEN FCM.ModuleID=3
                                THEN FCM.ModuleEntityID = " . $user_id . "
                            WHEN FCM.ModuleID = 1 
                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")            
                                
                    ELSE
                    true 
                    END 
                ";

        $this->db->where($condition, NULL, FALSE);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            if ($row['ForumCategoryIDs']) {
                $follow_category = $row['ForumCategoryIDs'];
            }
        }

        $this->db->select('GROUP_CONCAT(DISTINCT FC.ForumCategoryID) as UnForumCategoryIDs');
        $this->db->from(FORUM . ' F');
        $this->db->join(FORUMCATEGORY . ' FC', 'F.ForumID=FC.ForumID AND FC.StatusID=2');
        $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FC.ForumCategoryID=FCV.ForumCategoryID', 'left');
        $this->db->where('F.StatusID', 2);
        if (!empty($follow_category)) {
            $this->db->where_not_in('FC.ForumCategoryID', explode(',', $follow_category));
        }
        $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";
        $this->db->where($condition, NULL, FALSE);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            if ($row['UnForumCategoryIDs']) {
                $unfollow_category = $row['UnForumCategoryIDs'];
            }
        }

        if (!empty($unfollow_category)) {
            $this->db->select('A.*,U.FirstName,U.LastName,P.URL as ProfileURL');
            $this->db->from(ACTIVITY . ' as A');
            $this->db->join(USERS . ' U', 'U.UserID=A.UserID', 'left');
            $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID AND P.EntityType="User"', 'left');
            $this->db->where('A.ModuleID', 34);
            $this->db->where_in('A.ModuleEntityID', explode(',', $unfollow_category));
            $this->db->where('A.PostType', 4);
            $this->db->where('A.ActivityID NOT IN (SELECT EntityID FROM ' . SUBSCRIBE . ' as S WHERE S.ModuleID=3 AND S.ModuleEntityID=' . $user_id . ' AND S.EntityType="ACTIVITY" AND S.StatusID=2 )', null, null);

            $this->db->where('A.StatusID', 2);
            $this->db->order_by('A.TotalLikeViewComment', 'DESC');

            if ($page_size) {
                $this->db->limit($page_size, getOffset($page_no, $page_size));
            }
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $this->load->model(array('album/album_model', 'subscribe_model', 'activity/activity_model'));
                $final_array = array();
                foreach ($query->result_array() as $res) {

                    $activity = array();
                    $module_id = $res['ModuleID'];
                    $module_entity_id = $res['ModuleEntityID'];
                    $activity_id = $res['ActivityID'];
                    $res_entity_type = 'Activity';
                    $activity['ActivityGUID'] = $res['ActivityGUID'];
                    $activity['PostContent'] = $res['PostContent'];
                    $activity['CreatedBy'] = $res['FirstName'] . ' ' . $res['LastName'];
                    $activity['ProfileURL'] = site_url() . $res['ProfileURL'];
                    $edit_post_content = $activity['PostContent'];
                    $activity['PostContent'] = $this->activity_model->parse_tag($activity['PostContent']);
                    $activity['EditPostContent'] = $this->activity_model->parse_tag_edit($edit_post_content);
                    $activity['PostTitle'] = $res['PostTitle'];
                    $activity['CreatedDate'] = $res['CreatedDate'];
                    $activity['Album'] = array();
                    $BUsers = $this->activity_model->block_user_list($module_entity_id, $module_id);
                    $entity_details = get_detail_by_id($res['ModuleEntityID'], $res['ModuleID'], 'Name,ForumCategoryGUID,URL', 2);

                    $activity['EntityName'] = '';
                    $activity['EntityGUID'] = '';
                    $activity['EntityURL'] = '';
                    if ($entity_details) {
                        $activity['EntityName'] = $entity_details['Name'];
                        $activity['EntityGUID'] = $entity_details['ForumCategoryGUID'];
                        $activity['EntityURL'] = $entity_details['URL'];
                    }

                    $members_data = $this->activity_model->group_recent_comments_owner($res['ActivityID'], $user_id);
                    $activity['MembersList'] = $members_data['Data'];

                    if ($res['IsMediaExist']) {
                        $activity['Album'] = $this->activity_model->get_albums($activity_id, '0', '', 'Activity', 1);
                    }
                    if ($BUsers) {
                        $activity['NoOfComments'] = $this->activity_model->get_activity_comment_count($res_entity_type, $activity_id, $BUsers); //$res['NoOfComments'];
                        $activity['NoOfLikes'] = $this->activity_model->get_like_count($activity_id, $res_entity_type, $BUsers); //
                    } else {
                        $activity['NoOfComments'] = $res['NoOfComments'];
                        $activity['NoOfLikes'] = $res['NoOfLikes'];
                    }
                    $activity['IsLike'] = $this->activity_model->is_liked($activity_id, $res_entity_type, $user_id, 3, $user_id);
                    $activity['IsSubscribed'] = $this->subscribe_model->is_subscribed($user_id, 'Activity', $activity_id);
                    $activity['ActivityURL'] = get_single_post_url($res);
                    $final_array[] = $activity;
                }
                $result = $final_array;
            }
        }
        return $result;
    }

    /**
     * [most_active_users Get most active users]
     * @param  [int] $user_id   [Logged in user ID]
     * @param  [int] $page_no   [Page Number]
     * @param  [int] $page_size [Page Size]
     * @return [array]            [Active user list]
     */
    function most_active_users($user_id, $page_no, $page_size) {
        $BUsers = $this->activity_model->block_user_list($user_id, 3);
        $friends_data = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, FALSE);
        $followers = $friends_data['Follow'];
        $followers[] = $user_id;
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $old_date = date('Y-m-d H:i:s', strtotime('-30 day', strtotime($current_date)));
        $result = array();
        $this->db->select('COUNT(A.UserID) as Discussions ,A.UserID,U.FirstName,U.LastName,CONCAT(U.FirstName," ",U.LastName) AS Name,U.UserGUID,PU.Url as ProfileUrl,U.ProfilePicture');
        $this->db->from(ACTIVITY . ' as A');
        $this->db->join(USERS . ' U', 'A.UserID = U.UserID AND U.StatusID NOT IN (3,4)');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('A.CreatedDate >=', $old_date);
        $this->db->where_not_in('U.UserID', $followers);
        if (!empty($BUsers)) {
            $this->db->where_not_in('U.UserID ', $BUsers);
        }
        $this->db->where_in('A.ActivityTypeID', array(1, 7, 8, 11, 12, 16, 17, 25, 26));
        $this->db->group_by('A.UserID');
        $this->db->order_by('Discussions', 'DESC');
        if ($page_size) {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows() > 0) {
            $this->load->model(array('users/friend_model'));
            $final_array = array();
            foreach ($query->result_array() as $res) {
                $res['FollowStatus'] = 2;
                $res['MutualFriendCount'] = $this->friend_model->get_mutual_friend($res['UserID'], $user_id, '', 1);
                $final_array[] = $res;
            }
            $result = $final_array;
        }
        return $result;
    }

    /**
     * [assign_fourm_admin Insert/Update user assignmet for fourms and its categories]
     * @param  [array] $member_details [Member details]
     * @param  [int] $user_id        [User ID]
     * @param  [int] $role_id        [Assign Role ID]
     * @return [type]                 [description]
     */
    function change_member_permission($member_details, $added_by) {
        $this->db->where('ModuleID', $member_details['ModuleID']);
        $this->db->where('ModuleEntityID', $member_details['ModuleEntityID']);
        $this->db->delete(FORUMMANAGER);

        $this->db->where('ModuleID', $member_details['ModuleID']);
        $this->db->where('ModuleEntityID', $member_details['ModuleEntityID']);
        $this->db->update(FORUMCATEGORYMEMBER, array('ModuleRoleID' => 17, 'AddedBy' => $added_by));
    }

    /**
     * [forum_count return total forum count]
     * @return [int] [Total forum count]
     */
    function forum_count() {
        $count = 0;
        $forum_cache = array();
        if (CACHE_ENABLE) {
            $forum_cache = $this->cache->get('forum_count');
            if (!empty($forum_cache)) {
                $count = $forum_cache['forum_count'];
            }
        }
        if (empty($forum_cache)) {
            $this->db->select('COUNT(ForumID) as TotalCount');
            $this->db->from(FORUM);
            $this->db->where('StatusID', 2);
            $query = $this->db->get();
            $row = $query->row_array();
            if ($row['TotalCount']) {
                $count = $row['TotalCount'];
            }
            $data['forum_count'] = $count;
            $this->cache->save('forum_count', $data, CACHE_EXPIRATION);
        }
        return $count;
    }

    /**
     * [check_is_expert]
     * @param   [UserID] $input
     * @param   [GroupID] $input
     * @return  [BOOL]   TRUE         
     */
    function check_is_expert($user_id, $forum_category_id) {
        $this->db->select('IsExpert');
        $this->db->from(FORUMCATEGORYMEMBER);
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('ForumCategoryID', $forum_category_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->IsExpert;
        }
        return 0;
    }

    function remove_category_member($category_id, $module_id, $module_entity_id) {
        $this->db->where('ForumCategoryID', $category_id);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->delete(FORUMCATEGORYMEMBER);
    }

    function check_category_visibility($forum_category_id, $user_id) {
        $permissions = $this->check_forum_category_permissions($user_id, $forum_category_id, FALSE);
        $user_groups_array = $this->group_model->get_users_groups($user_id);
        $user_groups = 0;
        if (!empty($user_groups_array)) {
            $user_groups = implode(',', $user_groups_array);
        }
        $this->db->select('FC.ForumCategoryID', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        $this->db->where('FC.ForumCategoryID', $forum_category_id);
        $this->db->where('FC.StatusID', 2);
        $this->db->group_by('FC.ForumCategoryID');
        if (!$permissions['IsCreator'] && !$permissions['IsSuperAdmin'] && !$permissions['IsAdmin']) {
            $this->db->join(FORUMCATEGORYVISIBILITY . ' FCV', 'FCV.ForumCategoryID=FC.ForumCategoryID', 'left');
            $condition = " CASE 
                                WHEN 
                                    FC.Visibility=2
                                    THEN 
                                    ( CASE 
                                            WHEN FCV.ModuleID = 3 
                                                THEN FCV.ModuleEntityID = " . $user_id . "  
                                            WHEN FCV.ModuleID = 1 
                                                THEN FCV.ModuleEntityID IN (" . $user_groups . ")         
                                            WHEN FCM.ModuleID = 3 
                                                THEN FCM.ModuleEntityID = " . $user_id . "  
                                            WHEN FCM.ModuleID = 1 
                                                THEN FCM.ModuleEntityID IN (" . $user_groups . ")         
                                        ELSE
                                        '' 
                                        END 
                                    )
                        ELSE
                        true 
                        END 
                    ";

            $this->db->where($condition, NULL, FALSE);
        }
        $query = $this->db->get();
        $final_array = array();
        if ($query->num_rows() > 0)
            return true;
        else
            return false;
    }

    /**
     * [get_category_userlist_for_answer Get all the follower ids]
     * @param  [int]  $category_id  [Catgory id]
     * @return [array]                [Foller ids]
     */
    function get_category_userlist_for_answer($category_id) {
        $user_list = array();
        $group_list = array();
        $this->db->select('GROUP_CONCAT(ModuleEntityID) AS ModuleEntityIDs');
        $this->db->from(FORUMCATEGORYMEMBER);
        $this->db->where('ModuleID', 3);
        $this->db->where('ForumCategoryID', $category_id);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $item = $query->row_array();
            if (!empty($item['ModuleEntityIDs'])) {
                $user_list = explode(',', $item['ModuleEntityIDs']);
            }
        }

        $this->db->select('GROUP_CONCAT(ModuleEntityID) AS ModuleEntityIDs');
        $this->db->from(FORUMCATEGORYMEMBER);
        $this->db->where('ModuleID', 1);
        $this->db->where('ForumCategoryID', $category_id);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            $item = $query->row_array();
            if (!empty($item['ModuleEntityIDs'])) {
                $group_list = explode(',', $item['ModuleEntityIDs']);
            }
        }

        if (!empty($group_list)) {
            $this->group_model->get_group_member_recursive($user_list, $group_list);
        }
        return $user_list;
    }

    /**
     * [details Used to get forum category list]
     * @param  [int]  $forum_id [Forum ID]
     * @return [array]            [Forum Category list]
     */
    public function all_forum_category_name($forum_id) {

        $this->db->select('FC.ForumCategoryID,FC.ForumCategoryGUID,FC.Name', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        if (!empty($forum_id)) {
            $this->db->where('FC.ForumID', $forum_id);
        }
        $this->db->where('FC.StatusID', 2);
        $this->db->where('FC.ParentCategoryID', 0);
        $this->db->group_by('FC.ForumCategoryID');

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * [top_active_users_of_forum Get top most active user of a perticular forum]
     * @param  [int] $user_id   [Logged in user ID]
     * @param  [int] $page_no   [Page Number]
     * @param  [int] $page_size [Page Size]
     * @param  [int] $forum_id [forum ID]
     * @return [array]            [Active user list]
     */
    function top_active_users_of_forum($user_id, $page_no, $page_size, $forum_category_id = 0) {
        $BUsers = $this->activity_model->block_user_list($user_id, 3);
        //$friends_data = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, FALSE);
        //$followers=$friends_data['Follow'];
        //$followers[]=$user_id;

        $compiled_query = '';
        if (!$forum_category_id) {
            $this->db->select('FC.ForumCategoryID', FALSE);
            $this->db->from(FORUMCATEGORY . ' FC');
            $this->db->where('FC.StatusID', 2);
            //$this->db->where('FC.ParentCategoryID', 0);
            $this->db->group_by('FC.ForumCategoryID');

            $compiled_query = $this->db->_compile_select();  //echo $compiled_query; die;
            $this->db->reset_query();
        }




        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        $old_date = date('Y-m-d H:i:s', strtotime('-30 day', strtotime($current_date)));
        $result = array();
        $this->db->select('(SUM(A.NoOfComments) + COUNT(A.UserID)) TotalDiscussionsComments ,A.UserID,U.FirstName,U.LastName,CONCAT(U.FirstName," ",U.LastName) AS Name,U.UserGUID,PU.Url as ProfileUrl,U.ProfilePicture,UD.TagLine');
        $this->db->select("(SELECT COUNT(F.UserID) FROM " . FOLLOW . " F WHERE F.TypeEntityID=U.UserID AND F.Type='User' AND F.StatusID='2') as TotalFollowers", false);
        $this->db->from(ACTIVITY . ' as A');
        $this->db->join(USERS . ' U', 'A.UserID = U.UserID');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('A.CreatedDate >=', $old_date);
        //$this->db->where_not_in('U.UserID',$followers);
        if (!empty($BUsers)) {
            $this->db->where_not_in('U.UserID ', $BUsers);
        }
        $this->db->where_in('A.ActivityTypeID', array(26));
        $this->db->where_in('A.ModuleID', array(34));

        if ($forum_category_id) {
            if (is_array($forum_category_id)) {
                $this->db->where_in('A.ModuleEntityID', $forum_category_id);
            } else {
                $this->db->where('A.ModuleEntityID', $forum_category_id);
            }
        } else {
            $this->db->where("A.ModuleEntityID IN ($compiled_query) ", NULL, FALSE);
        }


        $this->db->where('A.StatusID', 2);

        $this->db->group_by('A.UserID');
        $this->db->order_by('TotalDiscussionsComments', 'DESC');
        if ($page_size) {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows() > 0) {
            $this->load->model(array('users/friend_model'));
            $final_array = array();
            foreach ($query->result_array() as $res) {
                $res['Discussions'] = $this->user_discussions_count($res['UserID'], $forum_category_id);
                $res['FollowStatus'] = 2;
                $res['MutualFriendCount'] = $this->friend_model->get_mutual_friend($res['UserID'], $user_id, '', 1);
                $final_array[] = $res;
            }
            $result = $final_array;
        }
        return $result;
    }

    function user_discussions_count($user_id, $forum_category_id) {
        $this->db->select('COUNT(A.UserID) as Discussions');
        $this->db->from(ACTIVITY . ' as A');
        $this->db->where_in('A.ActivityTypeID', array(26));
        $this->db->where_in('A.ModuleID', array(34));
        if (is_array($forum_category_id))
            $this->db->where_in('A.ModuleEntityID', $forum_category_id);
        else
            $this->db->where('A.ModuleEntityID', $forum_category_id);

        $this->db->where('A.UserID', $user_id);
        $this->db->where('A.StatusID', 2);

        $query = $this->db->get();
        //echo $this->db->last_query();
        $row = $query->row_array();
        if ($row['Discussions']) {
            return $row['Discussions'];
        } else
            return 0;
    }

    function get_discussion_count($forum_category_id) {
        $this->db->select("COUNT(ActivityID) as DiscussionCount");
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleID', '34');
        $this->db->where('ModuleEntityID', $forum_category_id);
        $this->db->where('StatusID', '2');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            return $row['DiscussionCount'];
        } else {
            return 0;
        }
    }

    /**
     * [details Used to get category guid by name]
     * @param  [int]  $forum_id [Forum ID], [string] $name [Name]
     * @return [array]  first category guid  
     */
    public function get_category_detail_by_name($forum_id = 1, $name = "introduction") {

        $this->db->select('F.ForumID', FALSE);
        $this->db->from(FORUM . ' F');
        $this->db->where('ForumID', $forum_id);
        $query = $this->db->get();
        if ($query->num_rows() < 1) {
            $forum_guid = get_guid();
            $input['Name'] = 'Welcome';
            $input['Description'] = '';
            $input['URL'] = 'welcome';
            $input['ForumGUID'] = $forum_guid;
            $input['CreatedBy'] = $this->UserID;
            $input['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $input['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
            $input['StatusID'] = 2;
            $input['DisplayOrder'] = 1;
            $forum_id = $this->create(FORUM, $input);
        }

        $input = array();

        $this->db->select('FC.ForumCategoryID,FC.ForumCategoryGUID,FC.Name', FALSE);
        $this->db->from(FORUMCATEGORY . ' FC');
        $this->db->join(FORUMCATEGORYMEMBER . ' FCM', 'FCM.ForumCategoryID=FC.ForumCategoryID', 'left');
        if (!empty($forum_id)) {
            $this->db->where('FC.ForumID', $forum_id);
        }
        $this->db->where('FC.Name', $name);
        $this->db->where('FC.StatusID', 2);
        $this->db->where('FC.ParentCategoryID', 0);
        $this->db->group_by('FC.ForumCategoryID');

        $res = $this->db->get()->row_array();
        if ($res) {
            return $res;
        }
        $display_order = 0;
        $get_forum_category_order = get_data('MAX(DisplayOrder) as DisplayOrder', FORUMCATEGORY, array('ForumID' => $forum_id), '1', '');
        if ($get_forum_category_order) {
            $display_order = $get_forum_category_order->DisplayOrder;
        }
        $forum_category_guid = get_guid();

        $input['Name'] = trim($name);
        $input['Description'] = 'This is generated by system';
        $input['URL'] = trim(strtolower($name));
        $input['MediaGUID'] = '';
        $input['Visibility'] = 1;
        $input['ParentCategoryID'] = 0;
        $input['IsDiscussionAllowed'] = 1;
        $input['CanAllMemberPost'] = 1;

        $input['ForumID'] = 1;
        $input['ForumCategoryGUID'] = $forum_category_guid;
        $input['DisplayOrder'] = $display_order + 1;
        $input['CreatedBy'] = 1;
        $input['StatusID'] = 2;
        $input['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $input['ModifiedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $forum_category_id = $this->create_category($input, 1);
        $res_next = $this->db->select('FC.ForumCategoryID,FC.ForumCategoryGUID,FC.Name', FALSE)
                        ->from(FORUMCATEGORY . ' FC')->where('FC.ForumCategoryGUID', $forum_category_guid)->get()->row_array();
        if ($res_next) {
            return $res_next;
        }
        return array();
    }

    public function checkIfIntroductionPosted($user_id, $data) {
        $this->db->select('*', FALSE)
                ->from(ACTIVITY);
        $this->db->where('ActivityTypeID', 26);
        $this->db->where('ModuleID', 34);
        $this->db->where('ModuleEntityID', $data['ForumCategoryID']);
        $this->db->where('UserID', $user_id);
        $query = $this->db->get();
        $row = array();
        return $query->num_rows();
    }

}
