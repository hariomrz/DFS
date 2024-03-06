<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Group_model extends Common_Model {

    protected $user_group_list = array();
    protected $user_category_group_list = '';
    protected $visible_group_list = '';
    protected $recursive_group_list = array();
    protected $recursive_group_list_membership = array();

    function __construct() {
        parent::__construct();
    }

    /**
     * Function Name: privacy validation
     * @param 
     * Description: Check if privacy is valid or not
     */
    function privacy_validation($group_id, $privacy) {
        $this->db->select('IsPublic,CreatedDate');
        $this->db->from(GROUPS);
        $this->db->where('GroupID', $group_id);
        $query = $this->db->get();

        if ($query->num_rows()) {
            $group_details = $query->row_array();
            if ($group_details['IsPublic'] == 0 && $privacy == 1) {
                return false;
            }
            if ($group_details['IsPublic'] == 2 && $privacy != 2) {
                return false;
            }
        }
        return true;
    }

    /**
     * [set_user_group_list Used to set user join/manage Group] 
     * @param type $current_user
     */
    function set_user_group_list($current_user)
    {
        if($this->settings_model->isDisabled(1)) { // Return empty if group module is disabled
            $this->user_group_list = [];
            return;
        }
        $this->user_group_list = $this->get_users_groups($current_user);
    }

    function set_visible_group_list($current_user) {
        $this->visible_group_list = $this->get_visible_groups($current_user);
    }

    /**
     * [set_user_category_group_list Used to user follow category ]
     * @param type $current_user
     */
    function set_user_categoty_group_list($current_user)
    {
        
        if($this->settings_model->isDisabled(1)) { // Return empty if group module is disabled
            $this->user_category_group_list = [];
            return;
        }
        $this->db->select('GROUP_CONCAT(TypeEntityID) as Categoryids');
        $this->db->from(FOLLOW);
        $this->db->where('UserID', $current_user);
        $this->db->where('Type', 'category');
        $this->db->where('StatusID', '2');
        $query = $this->db->get();
        $category_ids = array();
        if ($query->num_rows()) {
            $result = $query->row_array();
            $category_id = $result['Categoryids'];
            if (!empty($category_id)) {
                $this->db->select('GROUP_CONCAT(CategoryID) as SubCategoryids');
                $this->db->from(CATEGORYMASTER);
                $this->db->where_in('ParentID', explode(',', $category_id));
                $this->db->where('StatusID', '2');
                $query_subcat = $this->db->get();
                if ($query_subcat->num_rows()) {
                    $result_subcategory = $query_subcat->row_array();
                    $result_subcategory_id = $result_subcategory['SubCategoryids'];
                    if (!empty($result_subcategory_id)) {
                        $category_id = $category_id . ',' . $result_subcategory_id;
                    }
                }
                $category_ids = array_unique(explode(',', $category_id));

                $this->db->select('GROUP_CONCAT(DISTINCT(ModuleEntityID)) as ModuleEntityIDs');
                $this->db->from(ENTITYCATEGORY . ' EC');
                $this->db->join(GROUPS . ' G', 'G.GroupID=EC.ModuleEntityID');
                $this->db->where_in('EC.CategoryID', $category_ids);
                $this->db->where('EC.ModuleID', '1');
                $this->db->where('G.IsPublic', '1');
                $this->db->where('G.StatusID', '2');
                $query_group = $this->db->get();
                if ($query_group->num_rows()) {
                    $result_group = $query_group->row_array();
                    if (!empty($result_group['ModuleEntityIDs'])) {
                        $this->user_category_group_list = $result_group['ModuleEntityIDs'];
                    }
                }
            }
        }
    }

    /**
     * [create_group Used to Create Group]
     * @param type $user_id
     * @return type
     * 
     */
    function get_visible_groups($user_id) {
        $data = array(0);
        $this->db->select('G.GroupID');
        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' GM', 'GM.GroupID=G.GroupID AND GM.ModuleID="3" AND GM.ModuleEntityID="' . $user_id . '" AND GM.StatusID IN(1,2)', 'left');
        $this->db->where("IF(G.IsPublic='2',GM.GroupID is not null,true)", null, false);
        $this->db->where('G.StatusID', 2);
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $r) {
                $data[] = $r['GroupID'];
            }
        }
        return $data;
    }
    
    function get_user_most_active_groups($user_id, $limit, $returnOnlyIds = false) {
        
        $this->db->select(' COUNT(A.ActivityID) A_Count, G.GroupID');
        $this->db->from(ACTIVITY . ' A');        
        $this->db->join(GROUPMEMBERS . ' GM', "GM.GroupID = A.ModuleEntityID AND A.ModuleID = 1 AND GM.ModuleID = 3 AND GM.ModuleEntityID = $user_id ", 'left');                        
        $this->db->join(GROUPS . ' G', " G.GroupID = GM.GroupID ", 'left');
        
        $this->db->where("G.GroupID IS NOT NULL", NULL, FALSE);    
        
        $this->db->group_by('GM.GroupID');
        $this->db->order_by('A_Count', 'DESC');      
        
        $this->db->limit($limit);
        
        $query = $this->db->get();        
        $rowSet = $query->result_array();   //echo $this->db->last_query(); echo '=============';
        
        if(!$returnOnlyIds) {
            return $rowSet;
        }
        
        $ids = [];
        foreach ($rowSet as $row) {
            $ids[] = $row['GroupID'];
        }
        
        return $ids;
        
    }

    /**
     * 
     * @param type $current_user
     */
    function get_user_group_list()
    {
        if($this->settings_model->isDisabled(1)) { // Return empty if group module is disabled
            return [];
        }
        return $this->user_group_list;
    }

    function get_visible_group_list() {
        return $this->visible_group_list;
    }

    /**
     * @param type $current_user
     */
    function get_user_categoty_group_list()
    {
        if($this->settings_model->isDisabled(1)) { // Return empty if group module is disabled            
            return [];
        }
        return $this->user_category_group_list;
    }

    /**
     * [create_group Used to Create Group]
     * @param  	[Array] $input    		[Array of Group Details]
     * @return 	[int]   $group_id         
     */
    function create_group($input) {
        $group_name = $input['GroupName'];
        $check_exist = TRUE;

        $category_ids = $input['CategoryIds'];
        $allowed_post_type = $input['AllowedPostType'];
        unset($input['CategoryIds']);
        unset($input['AllowedPostType']);

        $params = array('a' => 0, 'ge' => 0, 'p' => 1, 'c' => 1, 'kb' => 0);

        $input['param'] = json_encode($params);

        $this->db->insert(GROUPS, $input);
        $group_id = $this->db->insert_id();
        $module_entity_id = $group_id;
        $module_id = 1;
        $module_role_id = 4;  // 4 for group owner/creater reference from TBL_ModuleRoles
        $status_id = 2; // by default accepted 	
        $user_id = $input['CreatedBy'];

        if (!empty($category_ids)) {
            $this->category_model->insert_update_category($category_ids, $module_id, $module_entity_id);
        }

        $this->update_allowed_content(array('ModuleEntityID' => $group_id, 'AllowedPostType' => $allowed_post_type));

        $this->db->insert(GROUPMEMBERS, array('GroupID' => $group_id, 'ModuleEntityID' => $user_id, 'ModuleID' => 3, 'AddedBy' => $user_id, 'JoinedAt' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModuleRoleID' => $module_role_id, 'StatusID' => $status_id, 'CanCreateKnowledgeBase' => '1'));

        //Create Defualt album
        create_default_album($user_id, $module_id, $module_entity_id, array(
            'is_add_log' => 1,
            'activity_type_id' => ($input['Type'] == 'INFORMAL') ? 29 : 28,
        ));

        $this->load->model('subscribe_model');
        $this->subscribe_model->toggle_subscribe($user_id, 'GROUP', $group_id);
        return $group_id;
    }

    /**
     * [update_group Used to Update Group Details]
     * @param  [String] $GroupName    		[Group Name]
     * @param  [String] $GroupDescription  	[Group Description]
     * @param  [int] 	$CreatedBy       	[Group Creater User ID]
     * @param  [array]  $CategoryIds    	[array of category id]
     * @param  [int]	$StatusID    	   [Status ID]	
     * @param  [int]	$GroupGUID    	   [GroupGUID]	
     * @param  [dateTime]$CreatedDate  	  [Date of creation]
     * @return [int] 	$GroupGUID         	
     */
    function update_group($input) {
        $checkExist = $this->check_existing_group($input['GroupName'], $input['CreatedBy'], $input['CategoryIds'], $input['GroupGUID']);

        if ($checkExist == '') {
            return 509;
        } else {
            $allowed_post_type = $input['AllowedPostType'];

            $updateData = array();
            $updateData['GroupName'] = $input['GroupName'];
            $updateData['GroupDescription'] = $input['GroupDescription'];
            $updateData['StatusID'] = $input['StatusID'];
            $updateData['IsPublic'] = $input['IsPublic'];
            $updateData['Verified'] = 0;  // Change status to unverified on profile update.

            if (!empty($input['Type'])) {
                $updateData['Type'] = $input['Type'];
            }

            if (isset($input['GroupImage']) && $input['GroupImage'] != "") {
                $updateData['GroupImage'] = $input['GroupImage'];
            }                

            if (isset($input['GroupCoverImage']) && $input['GroupCoverImage'] != "") {
                $updateData['GroupCoverImage'] = $input['GroupCoverImage'];
            }

            $this->is_group_formalization($input);

            $this->db->where('GroupGUID', $input['GroupGUID']);
            $this->db->update(GROUPS, $updateData);

            $module_id = 1; // for group module Id is 1 			
            $entity_details = get_detail_by_guid($input['GroupGUID'], 1, 'GroupID, GroupName', 2);

            $module_entity_id = $entity_details['GroupID'];

            $this->update_allowed_content(array('ModuleEntityID' => $module_entity_id, 'AllowedPostType' => $allowed_post_type));

            if (!empty($input['CategoryIds'])) {
                $this->category_model->insert_update_category($input['CategoryIds'], $module_id, $module_entity_id);
            }

            if (CACHE_ENABLE) {
                $this->cache->delete('group_cache_' . $module_entity_id);
            }
            return $input['GroupGUID'];
        }
    }

    function is_group_formalization($input) {
        $this->db->select('GroupName, CreatedBy');
        $this->db->from(GROUPS);
        $this->db->where('GroupGUID', $input['GroupGUID']);
        $query = $this->db->get();
        $row = $query->row_array();

        if (!empty($row['GroupName'])) {
            return;
        }

        if (empty($input['GroupName'])) {
            return;
        }

        $this->load->model(array('log/user_activity_log_score_model'));
        $score = $this->user_activity_log_score_model->get_score_for_activity(34, 1, 0, $input['CreatedBy']);
        $data = array(
            'ModuleID' => 1, 'ModuleEntityID' => 0, 'UserID' => $input['CreatedBy'], 'ActivityTypeID' => 34, 'ActivityID' => 0,
            'ActivityDate' => get_current_date('%Y-%m-%d'), 'PostAsModuleID' => 1, 'PostAsModuleEntityID' => $input['CreatedBy'],
            'EntityID' => 0, 'Score' => $score,
        );
        $this->user_activity_log_score_model->add_activity_log($data);
    }

    /**
     * [add_members dd members to group(depends on their module type)]
     * @param [int]  $group_id          	[Group ID]
     * @param [array]  $members           	[Member Array]
     * @param [boolean] $is_admin          	[Is admin ot not]
     * @param [int]  $status_id         	[Member Status]
     * @param [type]  $params            	[Additional Parameter]
     * @param [int]  $added_as          	[Added AS]
     * @param [int]  $added_by          	[Added by]
     * @param [boolean] $from_edit          [Edit member from edit popup]
     * @param [boolean] $logged_member_add  [description]
     */
    function add_members($group_id, $members, $is_admin = FALSE, $status_id, $params, $added_as, $added_by, $from_edit = FALSE, $logged_member_add = FALSE) {
        $this->load->model('log/user_activity_log_score_model');
        $add_member = array();
        $update_member = array();
        $is_current_user = FALSE;
        $user_id = $this->UserID;
        $group_data = get_detail_by_id($group_id, 1, 'param,Type', 2);

        $group_type = $group_data['Type'];
        $group_params = json_decode($group_data['param'], true);
        if (empty($group_params)) {
            $group_params = array("a" => 0, "ge" => 0, "p" => 1, "c" => 1, "kb" => 0);
        }

        if (!empty($members)) {
            foreach ($members as $key => $member) {
                if ($member['ModuleEntityID'] == $this->UserID && $member['ModuleID'] == 3 && $logged_member_add == FALSE) {
                    $is_current_user = TRUE;
                } else {
                    if (isset($member['IsExpert']))
                        $tmp_data['IsExpert'] = $member['IsExpert'];
                    else
                        $tmp_data['IsExpert'] = isset($group_params['ge']) ? $group_params['ge'] : 0 ;

                    if (isset($member['CanPost']))
                        $tmp_data['CanPostOnWall'] = $member['CanPost'];
                    else
                        $tmp_data['CanPostOnWall'] = isset($group_params['p']) ? $group_params['p'] : 1 ;

                    if (isset($member['CanComment']))
                        $tmp_data['CanComment'] = $member['CanComment'];
                    else
                        $tmp_data['CanComment'] = isset($group_params['c']) ? $group_params['c'] : 1 ;

                    if (isset($member['CanCreateKnowledgeBase']))
                        $tmp_data['CanCreateKnowledgeBase'] = $member['CanCreateKnowledgeBase'];
                    else
                        $tmp_data['CanCreateKnowledgeBase'] = isset($group_params['kb']) ? $group_params['kb'] : 1 ;

                    $exist = $this->db->get_where(GROUPMEMBERS, array('GroupID' => $group_id, 'ModuleEntityID' => $member['ModuleEntityID'], 'ModuleID' => $member['ModuleID']))->row_array();

                    if ($is_admin) {
                        $tmp_data['ModuleRoleID'] = 5;
                    } else {
                        $tmp_data['ModuleRoleID'] = 6;
                    }

                    if (!empty($member['IsAdmin'])) {
                        $member['ModuleRoleID'] = 5;
                        $tmp_data['ModuleRoleID'] = 5;
                    } else {
                        if (!empty($group_params['a'])) {
                            $member['ModuleRoleID'] = 5;
                            $tmp_data['ModuleRoleID'] = 5;
                        }
                    }

                    if (empty($exist)) {
                        $tmp_data['ModuleEntityID'] = $member['ModuleEntityID'];
                        $tmp_data['ModuleID'] = $member['ModuleID'];
                        $tmp_data['GroupID'] = $group_id;
                        $tmp_data['JoinedAt'] = get_current_date('%Y-%m-%d %H:%i:%s');
                        $tmp_data['StatusID'] = $status_id;
                        $tmp_data['params'] = $params;
                        $tmp_data['AddedAs'] = $added_as;
                        $tmp_data['AddedBy'] = $added_by;
                        $add_member[] = $tmp_data;

                        // Give score on group join
                        $user_id = $this->UserID;
                        if ($member['ModuleEntityID'] == $user_id) {
//                            $this->load->model(array('log/User_activity_log_score_model'));
//                            $score = $this->user_activity_log_score_model->get_score_for_activity(31, 1, 0, $user_id);
//                            $data = array(
//                                'ModuleID' => 1, 'ModuleEntityID' => $group_id, 'UserID' => $user_id, 'ActivityTypeID' => 31, 'ActivityDate' => get_current_date('%Y-%m-%d'),
//                                'PostAsModuleID' => 3, 'PostAsModuleEntityID' => $user_id, 'EntityID' => $group_id , 'Score' => $score,
//                            );
//                            $this->user_activity_log_score_model->add_activity_log($data);
                        }
                    } else {
                        $tmp_data['ModuleEntityID'] = $exist['ModuleEntityID'];
                        $tmp_data['ModuleID'] = $exist['ModuleID'];
                        $tmp_data['StatusID'] = 2;
                        $update_member[] = $tmp_data;
                    }
                }
            }

            if (!empty($add_member)) {
                $this->db->insert_batch(GROUPMEMBERS, $add_member);
                $this->load->model(array('activity/activity_wall_model'));
                foreach ($add_member as $key => $member) {
                    if ($member['ModuleRoleID'] == 5) { //Subscribe module entity if added as admin
                        $this->subscribe_model->toggle_subscribe($member['ModuleEntityID'], 'GROUP', $group_id, $member['ModuleID']);

                        $group_activities = $this->activity_model->getActivities($group_id, 1, '', '', $this->UserID, 1, 0, 2, '', '', '', '', 0, 0, false, 'A.ActivityID');
                        if (!empty($group_activities)) {
                            foreach ($group_activities as $key => $g_activity) {
                                $this->subscribe_model->toggle_subscribe($member['ModuleEntityID'], 'ACTIVITY', $g_activity['ActivityID'], $member['ModuleID']);
                            }
                        }
                    }
                    $activity_id = $this->activity_model->addActivity(1, $group_id, 4, $this->UserID, 0, '', '', '', array(), '0', 0, $member['ModuleID'], $member['ModuleEntityID']);
                }
                $notification_type_id = '';
                if ($group_type == 'FORMAL') {
                    $notification_type_id = 4;
                }
                if ($status_id == '1') {
                    $notification_type_id = 22;
                }
                //$this->send_instant_group_create_notification($add_member, $group_id, 'create', 0, $notification_type_id, $added_by);
                initiate_worker_job('send_instant_group_create_notification', array('Members' => $add_member, 'GroupID' => $group_id, 'Type' => 'create', 'ActivityID' => 0, 'NotificationTypeID' => $notification_type_id, 'AddedBy' => $added_by, 'UserID' => $user_id, 'LoggedInName' => $this->LoggedInName));

                if ($status_id == 18) {
                    $admin_list = $this->get_all_group_admins($group_id);
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $add_member[0]['ModuleEntityID'];
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $group_id;
                    $parameters[1]['Type'] = 'Group';
                    $this->notification_model->add_notification(143, $add_member[0]['ModuleEntityID'], $admin_list, $group_id, $parameters);
                }
            }
            if (!empty($update_member)) {
                $member_update = array();

                foreach ($update_member as $key => $value) {
                    $m_r_id = $this->db->select('ModuleRoleID')->from(GROUPMEMBERS)->where(array('GroupID' => $group_id, 'ModuleEntityID' => $value['ModuleEntityID'], 'ModuleID' => $value['ModuleID']))->get()->row()->ModuleRoleID;

                    $member_update['StatusID'] = 2;

                    if (isset($value['IsExpert']))
                        $member_update['IsExpert'] = $value['IsExpert'];

                    if (isset($value['CanPostOnWall']))
                        $member_update['CanPostOnWall'] = $value['CanPostOnWall'];

                    if (isset($value['CanComment']))
                        $member_update['CanComment'] = $value['CanComment'];

                    if (isset($value['CanCreateKnowledgeBase']))
                        $member_update['CanCreateKnowledgeBase'] = $value['CanCreateKnowledgeBase'];

                    if ($m_r_id == 6) {
                        $member_update['ModuleRoleID'] = $value['ModuleRoleID'];
                    }

                    $this->db->where(array('GroupID' => $group_id, 'ModuleEntityID' => $value['ModuleEntityID'], 'ModuleID' => $value['ModuleID']));
                    $this->db->update(GROUPMEMBERS, $member_update);
                }
                $update_member_ids = $this->get_notification_members($update_member);
                foreach ($update_member_ids as $key => $update_id) {
                    $this->activity_model->addActivity(1, $group_id, 4, $update_id);
                    notify_node('liveFeed', array('Type' => 'GJ', 'UserID' => $update_id, 'EntityGUID' => get_detail_by_id($group_id, 1, 'GroupGUID', 1)));
                }
            }
            $total_members = array_merge($add_member, $update_member);
            if (!empty($total_members)) {
                $group_members = $this->get_all_members($group_id);
                $member_ids = array();
                $remove_members = array();
                foreach ($group_members as $key => $value) {
                    foreach ($total_members as $key => $tm) {
                        if ($value['ModuleEntityID'] == $tm['ModuleEntityID'] && $value['ModuleID'] == $tm['ModuleID']) {
                            $member_ids[] = $value['GroupMemeberID'];
                            $remove_members[] = $value;
                        }
                    }
                }
                if (!empty($member_ids)) {
                    if ($from_edit) {
                        $this->db->set('StatusID', 3);
                        $this->db->where_not_in('GroupMemeberID', $member_ids);
                        $this->db->where('GroupID', $group_id);
                        if ($is_admin) {
                            $this->db->where('ModuleRoleID', 5);
                        } else {
                            $this->db->where('ModuleRoleID', 6);
                        }
                        $this->db->update(GROUPMEMBERS);
                    }
                    /* --remove users subscription-- */
                    $remove_subscription_ids = $this->get_notification_members($remove_members);
                    $this->load->model('subscribe_model');
                    foreach ($remove_subscription_ids as $key => $m_id) {
                        $this->subscribe_model->update_subscription($m_id, 'GROUP', $group_id, 3);
                    }
                }
            }
            $this->update_group_member_count($group_id);
            return true;
        } else {
            $this->db->set('StatusID', 3);
            $this->db->where('GroupID', $group_id);
            if ($is_admin) {
                $this->db->where('ModuleRoleID', 5);
            } else {
                $this->db->where('ModuleRoleID', 6);
            }
            $this->db->update(GROUPMEMBERS);
            $this->update_group_member_count($group_id);
        }
    }

    /**
     * [check_existing_formal_group Used to Check If Group Name is already Exists]
     * @param 	[String] $group_name    	[Group Name]
     * @param  	[int]    $user_id       	[User id]
     * @param  	[array]  $category_ids		[array of category id]	
     * @param  	[int]    $group_guid    	[GroupGUID (in case of edit group)]	
     * @return 	[bool]          			[true/false]
     */
    function check_existing_formal_group($group_name, $user_id, $category_ids = '', $group_guid = '') {
        $this->db->select('GroupID, GroupGUID, Type');
        $this->db->where('GroupName', $group_name);
        $this->db->where('CreatedBy', $user_id);
        $this->db->where('StatusID !=', 3);
        $this->db->where('Type', 'FORMAL');

        if ($group_guid) {
            $this->db->where('GroupGUID !=', $group_guid);
        }

        $query = $this->db->get(GROUPS);

        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return $result;
        } else {
            if (!empty($category_ids)) {
                $this->db->select('Groups.GroupID, Groups.GroupGUID, Groups.Type');
                $this->db->where('Groups.GroupName', $group_name);
                $this->db->where('StatusID !=', 3);
                $this->db->where('Type', 'FORMAL');
                $this->db->where('EntityCategory.ModuleID', 1);
                $this->db->where_in('EntityCategory.CategoryID', $category_ids);

                if ($group_guid) {
                    $this->db->where('Groups.GroupGUID !=', $group_guid);
                }

                $this->db->from(GROUPS);
                $this->db->join(ENTITYCATEGORY, 'Groups.GroupID = EntityCategory.ModuleEntityID', 'left');
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    return $result;
                }
            }
        }
        return 0;
    }

    /**
     * [check_existing_informal_group Used to Check if informal group exist beetwen given user.]
     * @param 	[array] $members_list  	[Array of Member list]
     * @return 	[int]          			[Group ID OR 0]
     */
    public function check_existing_informal_group($members_list) {
        $members_count = count($members_list);
        if ($members_count > 0) {
            /* --check if there is any informal group, if found than get only members-- */

            if ($members_count == 1 && $members_list[0]['ModuleID'] == 1) {
                $res = get_detail_by_id($members_list[0]['ModuleEntityID'], 1, 'GroupGUID, GroupName, GroupID, Type', 2);
                return array($res);
            } else {
                $sql = "Select count(G.GroupID) AS totalMembers";

                $sql .= " , count(IF(";
                foreach ($members_list as $key => $members) {
                    $sql .= " (GM.ModuleEntityID = '" . $members['ModuleEntityID'] . "' AND GM.ModuleID='" . $members['ModuleID'] . "') OR ";
                }
                //$sql = trim($sql,'OR ');
                $sql .= " (GM.ModuleEntityID=$this->UserID AND GM.ModuleID=3)";
                $sql .= " AND GM.StatusID=2,1,NULL)) matchingMembers ";

                $sql .= " , G.GroupGUID, G.GroupName, G.GroupID, G.Type 
					FROM " . GROUPS . " G 
					JOIN " . GROUPMEMBERS . " GM ON G.GroupID=GM.GroupID 
					WHERE GM.StatusID=2 AND G.StatusID=2 AND G.Type='INFORMAL'
					Group by G.GroupID 
					Having  matchingMembers=" . ($members_count + 1) . " and totalMembers=matchingMembers";
                $query = $this->db->query($sql);
                if ($query->num_rows()) {
                    $result = $query->result_array();
                    $return_data = array();
                    $this->load->model('activity/activity_model');

                    foreach ($result as $key => $res) {
                        unset($res['totalMembers']);
                        unset($res['matchingMembers']);
                        $return_data[$key] = $res;
                        $return_data[$key]['LastPostContent'] = $this->activity_model->get_group_recent_activity($res['GroupID']);
                        unset($return_data['GroupID']);
                    }
                    return $return_data;
                }
            }
        }
        return 0;
    }

    /**
     * [lists Used to get the group list]
     * @param  [int] $user_id           [User id]
     * @param  [boolean] $count_flag    [count flag]
     * @param  [string] $search_keyword [search keyword]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @param  [string] $filter         [Manage or Join group list]
     * @param  [string] $order_by       [Order by field]
     * @param  [string] $sort_by        [Sort by value ASC/DESC]
     * @return [array]                  [Group list]
     */
    function lists($user_id, $count_flag = FALSE, $search_keyword = '', $page_no = '', $page_size = '', $filter = '', $order_by = "", $sort_by = "DESC", $category_id = '', $privacy_type = -1, $search = 0, $visited_user_id = 0, $owner_guids = array(), $type = '') {
        $con1 = "
            IF(GM.ModuleRoleID='6' AND GM.StatusID='2','Member','Other') ";
        $con2 = "
            IF((GM.ModuleRoleID='4' OR GM.ModuleRoleID='5') AND GM.StatusID='2','Manage'," . $con1 . ") as UserJoinStatus ";

        if ($filter == 'Suggested') {
            $this->load->model('ignore_model');
            $joined_groups = $this->get_users_groups($user_id);
            $ignored_group_list = $this->ignore_model->get_ignored_list($user_id, 'Group');
            $blocked_group_list = $this->get_blocked_group_list($user_id);
            $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        }

        if (empty($search) && $filter != 'Suggested' && $filter != 'AllPublicGroups') {
            $current_user_group_ids = $this->get_users_groups($user_id, $filter);
            if (empty($current_user_group_ids)) {
                if ($user_id) {
                    //return $current_user_group_ids;
                }
            }

            $common_group_ids = $current_user_group_ids;
            $common_group_ids[] = 0;
            $visited_user_group_ids[] = 0;

            if (!empty($visited_user_id) && $user_id != $visited_user_id) {
                $visited_user_group_ids = $this->get_users_groups($visited_user_id, $filter);
                $visited_user_group_ids[] = 0;
                $common_group_ids = array_intersect($current_user_group_ids, $visited_user_group_ids);
            }
        }
        $common_group_ids[] = 0;

        $results = array();
        $friends = array();
        $select_array = array();
        if ($search) {
            $this->load->model('users/user_model');
            $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
            $friends[] = $user_id;

            if ($friends) {
                $select_array[] = '(SELECT COUNT(ModuleEntityID) FROM ' . GROUPMEMBERS . ' WHERE GroupID=G.GroupID AND ModuleID=3 AND ModuleEntityID IN (' . implode(',', $friends) . ')) as FriendsCount';
            }
        }

        $select_array[] = $con2;
        $select_array[] = '(CASE G.Type 
                            WHEN "INFORMAL" THEN (
                            SELECT GROUP_CONCAT(CASE GMM.ModuleID WHEN 3 THEN CONCAT(US.FirstName," ",US.LastName) ELSE GG.GroupName END) 
                            FROM ' . GROUPMEMBERS . ' GMM 
                            LEFT JOIN ' . USERS . ' US ON `US`.`UserID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 3
                            LEFT JOIN ' . GROUPS . ' GG ON `GG`.`GroupID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 1
                            WHERE GMM.GroupID=G.GroupID 
                            GROUP BY GMM.GroupID
                            )   
                            ELSE G.GroupName END) AS GroupName';
        $select_array[] = 'G.Type, G.GroupGUID,CONCAT(U.FirstName," ",U.LastName) AS CreatedBy,U.UserGUID as CreatorGUID, P.Url as CreatedProfileUrl, G.LastActivity, G.GroupID, G.GroupDescription, GM.ModuleRoleID, G.CreatedDate, if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture, G.IsPublic, if(G.IsPublic=0,1,if(G.IsPublic=1,0,2)) as GroupOrder, G.MemberCount, Popularity as ActivityLevel';

        $this->db->from(GROUPS . ' G');
        $this->db->_protect_identifiers = FALSE;
        if ($filter == 'AllPublicGroups') {
            $this->db->join(GROUPMEMBERS . ' GM', 'G.GroupID = GM.GroupID', 'left');
        } else if ($filter != 'Suggested') {
            $this->db->join(GROUPMEMBERS . ' GM', 'G.GroupID = GM.GroupID AND GM.ModuleID="3" AND GM.ModuleEntityID="' . $user_id . '"', 'left');
        }
        $this->db->_protect_identifiers = TRUE;
        if ($user_id) {
            $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'inner');
        } else {
            $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'left');
        }
        $this->db->join(PROFILEURL . ' P', 'P.EntityID = U.UserID', 'left');
        $this->db->_protect_identifiers = FALSE;
        $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID AND EC.ModuleID="1"', 'left');
        $this->db->_protect_identifiers = TRUE;
        $this->db->join(CATEGORYMASTER . ' CM', 'CM.CategoryID=EC.CategoryID', 'left');

        if (!empty($category_id)) {
            $this->db->where('EC.ModuleID', 1);
            if (is_array($category_id)) {
                $this->db->where_in('EC.CategoryID', $category_id);
            } else {
                $this->db->where('EC.CategoryID', $category_id);
            }
        }
        if ($privacy_type != '-1') {
            $this->db->where('G.IsPublic', $privacy_type);
        }
        if (!empty($owner_guids)) {
            $this->db->where_in('U.UserGUID', $owner_guids);
        }

        if($type)
        {
            $this->db->where('G.Type',$type);
        }

        $this->db->where('P.EntityType', 'User');
        if (empty($search)) {
            if ($filter != 'Suggested' && $filter != 'AllPublicGroups') {
                if ($user_id) {
                    
                    // Remove sql breaking strings
                    $visited_user_group_ids = convert_to_numeric_arr($visited_user_group_ids);
                    $common_group_ids = convert_to_numeric_arr($common_group_ids);
                    
                    $this->db->where("((G.GroupID IN (" . implode(',', $common_group_ids) . ") AND G.IsPublic in(0,1,2)) OR (G.GroupID IN (" . implode(',', $visited_user_group_ids) . ") AND G.IsPublic in(0,1))) ", NULL, FALSE);
                } else {
                    $this->db->where("G.IsPublic in(0,1)", NULL, FALSE);
                }
            }
        }

        if (!empty($search_keyword)) {
            $search_keyword = $this->db->escape_like_str($search_keyword); 
            $this->db->where("(GroupName LIKE '%" . $search_keyword . "%' OR G.GroupDescription LIKE '%" . $search_keyword . "%' OR CM.Name LIKE '%" . $search_keyword . "%')", null, false);
            $select_array[] = "IF(GroupName LIKE '%" . $this->db->escape_like_str($search_keyword) . "%',1,2) as OrdBy";
        }

        $filter = ucfirst($filter);
        if ($filter == 'AllPublicGroups') {
            $this->db->where(array(
                "G.StatusID" => 2,
                "G.IsPublic" => 1
            ));
        } else if ($filter == 'Suggested') {
            $this->db->join(GROUPMEMBERS . ' GM', 'G.GroupID = GM.GroupID');
            $this->db->where("(exists (select FriendID as my_friends_userId from " . FRIENDS . " f where f.UserID = " . $user_id . " AND f.Status = 1 And GM.ModuleEntityID = f.FriendID AND GM.ModuleID=3 AND G.IsPublic =1 AND (GM.ModuleEntityID != " . $user_id . " AND GM.ModuleID=3)  AND G.StatusID =2 AND G.CreatedBy <> " . $user_id . ") OR Exists (select GroupID from Groups where CreatedBy != " . $user_id . " AND StatusID = 2 AND (GM.ModuleEntityID != " . $user_id . " AND GM.ModuleID=3))) AND G.GroupID not in (select GroupID from " . GROUPMEMBERS . " where ModuleEntityID = " . $user_id . " AND GM.ModuleID=3) AND G.StatusID=2 AND G.IsPublic = 1 AND (GM.ModuleEntityID != " . $user_id . " AND GM.ModuleID=3)", NULL, FALSE);
            $this->db->where("G.Popularity!='Low'", null, false);
            if (!empty($joined_groups)) {
                $this->db->where_not_in('G.GroupID', $joined_groups);
            }

            if ($ignored_group_list) {
                $this->db->where_not_in('G.GroupID', $ignored_group_list);
            }

            if ($blocked_group_list) {
                $this->db->where_not_in('G.GroupID', $blocked_group_list);
            }
        } else if ($filter == 'Manage') {
            $this->db->where("(G.StatusID = '2' OR G.StatusID = '10')", NULL, FALSE);
        } else if ($filter == 'MyGroupAndJoined') {
            $this->db->where("(G.StatusID = '2' OR G.StatusID = '10')", NULL, FALSE);
        } else if ($filter == 'Join' || $filter == 'Invite') {
            $this->db->where("G.StatusID", 2);
        } else if ($filter == 'All') {
            if ($search) {
                $this->db->where(array(
                    "G.StatusID" => 2,
                    "G.IsPublic in(0,1)" => null
                ));
            } else {
                $this->db->where(array(
                    "G.StatusID" => 2
                        //,"G.IsPublic in(0,1,2)" => null
                ));
            }
        }

        if (empty($count_flag)) { // check if array needed
            if ($search) {
                //$this->db->order_by("OrdBy", 'ASC');
                if (!$order_by) {
                    $this->db->order_by('FIELD(UserJoinStatus,"Manage","Member","Other")');
                    $this->db->order_by('FriendsCount', 'DESC');
                    $this->db->order_by('G.MemberCount', 'DESC');
                }
            }

            // Added Sorting by type and order
            if (!empty($order_by) && !empty($sort_by)) {
                if ($order_by == 'Name') {
                    $this->db->order_by('GroupName', $sort_by);
                } else if ($order_by == 'Members') {
                    $this->db->order_by('G.MemberCount', 'DESC');
                } else if ($order_by == 'ActivityLevel') {
                    $this->db->_protect_identifiers = FALSE;
                    $this->db->order_by('FIELD(Popularity,"HIGH","Moderate","Low")');
                    $this->db->_protect_identifiers = TRUE;
                } else if ($order_by == 'Recent Updated') {
                    $this->db->order_by('G.LastActivity', 'DESC');
                } else if ($order_by == 'IsPublic') {
                    $this->db->_protect_identifiers = FALSE;
                    $this->db->order_by('FIELD(IsPublic,1,0,2)');
                    $this->db->_protect_identifiers = TRUE;
                } else if ($order_by == 'NoOfMember') {
                    $this->db->order_by('G.MemberCount', 'DESC');
                } else {
                    $this->db->order_by($order_by, $sort_by);
                }
            }

            if (!empty($page_size)) { // Check for pagination
                //$offset = ($page_no - 1) * $page_size;
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }
            $this->db->group_by('G.GroupID');
            $this->db->select(implode(',', $select_array), false);
            $query = $this->db->get();
            //echo $this->db->last_query(); die;
            $result = $query->result_array();

            if (empty($friends)) {
                $this->load->model('users/user_model');
                $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
            }

            $this->load->model('activity/activity_model');
            foreach ($result as $key => $val) {
                $result[$key]['Members'] = array();
                $result[$key]['Category'] = array('Name' => '', 'CategoryID' => 0, 'SubCategory' => array());
                $result[$key]['EntityMembers'] = array();
                $result[$key]['TotalPosts'] = $this->activity_model->get_total_module_posts(1, $val['GroupID']);
                

                $permission = check_group_permissions($user_id, $val['GroupID']);
                if (isset($permission['Details'])) {
                    $group_details = $permission['Details'];
                    if (isset($group_details['Category'])) {
                        $result[$key]['Category'] = $group_details['Category'];
                    }
                    if ($val['Type'] == 'INFORMAL' && isset($group_details['Members'])) {
                        $result[$key]['EntityMembers'] = $group_details['Members'];
                    }
                    unset($permission['Details']);
                }

                $result[$key]['Permission'] = $permission;

                $result[$key]['FeaturedPost'] = $this->activity_model->get_featured_post($user_id, 1, $val['GroupID'], 1, 1);

                /*if ($friends && $val['Type'] == 'FORMAL') {
                    $totalLimit = 4;
                    $result[$key]['Members'] = $this->get_group_members_details($val['GroupID'], 1, $totalLimit, $friends);
                }*/
                $result[$key]['Members'] = $this->members($val['GroupID'], $user_id);
                unset($result[$key]['UserJoinStatus']);
                
                //$group_details['GroupName'] = $this->group_model->get_informal_group_name($entity_id, $user_id, 0, false, array(), $entity_details['Members']);
                // Get profile Url
                $group_url_details = $this->get_group_details_by_id($val['GroupID'], '', $result[$key]);
                $result[$key]['ProfileURL'] = $this->get_group_url($val['GroupID'], $group_url_details['GroupNameTitle'], false, 'index');
                
            }
            return $result;
        } else {
            $this->db->select('COUNT( DISTINCT G.GroupID) as TotalRow ');
            $result = $this->db->get();
            //echo $this->db->last_query();
            $count_data = $result->row_array();
            return $count_data['TotalRow'];
        }
    }

    /**
     * [details Used to get group details]
     * @param  [int]  $group_id [Group ID]
     * @param  [int]  $user_id  [Logged in user ID]
     * @param  [boolean] $is_admin [Is logged in user admin of group]
     * @return [array]            [Group details]
     */
    function details($group_id, $user_id, $is_admin = FALSE) {
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);

        $this->db->select('G.Type, G.GroupGUID, G.GroupName, G.GroupDescription, G.LastActivity, G.CreatedDate, G.MemberCount, CONCAT(U.FirstName," ",U.LastName) AS CreatedBy, if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture, G.IsPublic, G.GroupCoverImage, G.StatusID,G.LandingPage,G.GroupID as ModuleEntityID,G.param, G.CreatedDate, G.CreatedBy as CreatorID', FALSE);

        $this->db->from(GROUPS . ' G');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID');
        $this->db->where('GroupID', $group_id);
        $query = $this->db->get();
        $result = $query->row_array();
        if ($result) {
            $result['IsCoverExists'] = 0;

            if ($result['GroupCoverImage']) {
                $result['IsCoverExists'] = 1;
            }
            
                                
            

            $result['GroupCoverImage'] = $result['GroupCoverImage'];
            $result['param'] = json_decode($result['param'], true);

            $result['Category'] = $this->get_group_categories($group_id);

            $result['GroupDescription'] = ucfirst($result['GroupDescription']);

            $result['Members'] = array();
            if ($result['Type'] == 'INFORMAL') {
                
                if (empty($result['GroupName'])) {
                    $result['GroupName'] = $this->get_informal_group_name($group_id, $user_id);
                }    
                
                $result['Members'] = $this->members($group_id, $user_id);
            }

            if (!empty($blocked_users)) {
                $result['MemberCount'] = $this->group_members_count($group_id, $blocked_users);
            }
            $result['AllowedPostType'] = $this->get_post_permission($group_id);            
        }
        return $result;
    }

    /**
     * [media  used to get list of media in groups ] 
     * @param [int]         $group_id       [Group ID]
     * @param [int]         $limit          [Limit]
     */
    function media($group_id, $limit) {
        $query = $this->db->query("SELECT M.MediaGUID,M.ImageName FROM 
        `Groups` G, `Albums` A, `Media` M
        WHERE M.AlbumID=A.`AlbumID` AND A.ModuleID=1 AND A.`ModuleEntityID`=G.`GroupID` AND A.`AlbumName`='Wall Media'
        AND G.GroupID='$group_id' LIMIT $limit");

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    /**
     * [suggestions  used to get list of all suggested groups ]	
     * @param [int] 		$page_no 			[Offset]
     * @param [int] 		$page_size 			[Limit]
     * @param [int] 		$user_id 			[Logged in User ID]
     */
    function suggestions($user_id, $page_no, $page_size = PAGE_SIZE, $num_rows = FALSE, $category_ids = array())
    {
        $this->load->model('ignore_model');
        $joined_groups = $this->get_users_groups($user_id);
        $ignored_group_list = $this->ignore_model->get_ignored_list($user_id, 'Group');
        $blocked_group_list = $this->get_blocked_group_list($user_id);
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);
        $friend_disabled = $this->settings_model->isDisabled(10);

        $this->db->select('G.Popularity, G.GroupGUID, CONCAT(U.FirstName," ",U.LastName) AS CreatedBy, G.LastActivity, G.GroupID, G.GroupName, if(LENGTH(G.GroupDescription) > 70 ,CONCAT(SUBSTR(G.GroupDescription,1,70) ,"","..."),G.GroupDescription) as GroupDescription, G.MemberCount, G.CreatedDate,if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture, G.IsPublic, Gm.ModuleRoleID, (select Count(f.FriendID) from ' . GROUPMEMBERS . ' as Gm,Friends f where Gm.ModuleEntityID  = f.FriendID AND Gm.ModuleID=3 and f.UserID = ' . $user_id . ' AND f.Status = 1 and Gm.GroupID = G.GroupID) as total', FALSE);
        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' Gm', 'G.GroupID = Gm.GroupID');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'inner');

        if (!$friend_disabled) {
            $this->db->where("(exists (select FriendID as my_friends_userId from " . FRIENDS . " f where f.UserID = " . $user_id . " AND f.Status = 1 And Gm.ModuleEntityID = f.FriendID AND Gm.ModuleID=3 AND G.IsPublic =1 AND (Gm.ModuleEntityID != " . $user_id . " AND Gm.ModuleID=3)  AND G.StatusID =2 AND G.CreatedBy <> " . $user_id . ")
             OR Exists (select GroupID from Groups where CreatedBy != " . $user_id . " AND StatusID = 2 AND (Gm.ModuleEntityID != " . $user_id . " AND Gm.ModuleID=3))) AND G.GroupID not in (select GroupID from " . GROUPMEMBERS . " where ModuleEntityID = " . $user_id . " AND Gm.ModuleID=3) AND G.StatusID=2 AND G.IsPublic = 1 AND (Gm.ModuleEntityID != " . $user_id . " AND Gm.ModuleID=3)", NULL, FALSE);
        } else {
            $this->db->where("(exists (select TypeEntityID as my_friends_userId from " . FOLLOW . " f where f.UserID = " . $user_id . " AND f.Type ='user' AND f.StatusID = 2 And Gm.ModuleEntityID = f.TypeEntityID AND Gm.ModuleID=3 AND G.IsPublic =1 AND (Gm.ModuleEntityID != " . $user_id . " AND Gm.ModuleID=3)  AND G.StatusID =2 AND G.CreatedBy <> " . $user_id . ") 
            OR Exists (select GroupID from Groups where CreatedBy != " . $user_id . " AND StatusID = 2 AND (Gm.ModuleEntityID != " . $user_id . " AND Gm.ModuleID=3))) AND G.GroupID not in (select GroupID from " . GROUPMEMBERS . " where ModuleEntityID = " . $user_id . " AND Gm.ModuleID=3) AND G.StatusID=2 AND G.IsPublic = 1 AND (Gm.ModuleEntityID != " . $user_id . " AND Gm.ModuleID=3)", NULL, FALSE);
        }
        $this->db->where("G.Popularity!='Low'", null, false);
        if (!empty($blocked_users)) {
            $this->db->where_not_in('U.UserID', $blocked_users);
        }
        if (!empty($category_ids)) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID', 'left');
            $this->db->where('EC.ModuleID', 1);
            if (is_array($category_ids)) {
                $this->db->where_in('EC.CategoryID', $category_ids);
            } else {
                $this->db->where('EC.CategoryID', $category_ids);
            }
        }

        if (!empty($joined_groups)) {
            $this->db->where_not_in('G.GroupID', $joined_groups);
        }

        if ($ignored_group_list) {
            $this->db->where_not_in('G.GroupID', $ignored_group_list);
        }

        if ($blocked_group_list) {
            $this->db->where_not_in('G.GroupID', $blocked_group_list);
        }
        $this->db->where_not_in("U.StatusID", array(3, 4));
        $this->db->group_by('G.GroupID');
        $this->db->order_by(" total desc,G.MemberCount desc ", NULL, FALSE);

        if (!empty($page_size)) {
            //$offset = ($page_no - 1) * $page_size;
            $offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $offset);
        }

        $res = $this->db->get();
        //echo $this->db->last_query();die;
        if ($num_rows) {
            return $res->num_rows();
        }
        $value = array();
        $val = array();
        foreach ($res->result_array() as $value) {
            $value['Category'] = $this->get_group_categories($value['GroupID']);
            $value['FriendCount'] = $value['total'];
            $value['IsJoined'] = 0;
            $value['Members'] = array();
            if ($value['total'] > 0) {
                $this->load->model('users/user_model');
                $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
                if ($friends) {
                    $value['Members'] = $this->get_group_members_details($value['GroupID'], 1, 4, $friends);
                }
            }
            $members_list = $this->group_recent_post_owner($value['GroupID'],$user_id);
            $value['MembersList'] = $members_list['Data'];
            $value['DiscussionCount'] = $this->get_discussion_count($value['GroupID']);
            $value['MemberCount'] = $this->members($value['GroupID'], $user_id, TRUE, '', '', '', '', 'Name', TRUE,'','');
            
            $group_url_details = $this->group_model->get_group_details_by_id($value['GroupID'], '', $value);
            $value['ProfileURL'] = $this->group_model->get_group_url($value['GroupID'], $group_url_details['GroupNameTitle'], false, 'index');       
            
            $val[] = $value;
        }
        $results['data'] = $val;

        function compareByName($a, $b) {
            return strcmp($a["GroupName"], $b["GroupName"]);
        }

        usort($val, 'compareByName');
        return $val;
    }

    /**
     * [delete - allows user to delete/block a group]
     * @param  [int] 		$GroupID    		[Group Id]
     * @param  [int] 		$user_id  			[User ID]
     * @param  [int] 		$memberGroupRole 	 		
     * @param  [int] 		$ActionType  		
     * @param  [String] 	$Reason  		
     */
    function delete($data, $user_id) {
        if (!empty($data)) {
            if ($data['ActionType'] == 'Delete') {
                $this->db->where('GroupID', $data['GroupID']);
                $this->db->update(GROUPS, array('StatusID' => 3));
                $result['Message'] = lang('group_deleted');
                initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $data['GroupID'], 'user_id' => $user_id, 'Members' => array(), 'Status' => 'Remove'));
            } elseif ($data['ActionType'] == 'admin_multi_delete') {
                $this->db->where_in('GroupGUID', $data['GroupGUIDS']);
                $this->db->update(GROUPS, array('StatusID' => 3));

                if (count($data['GroupGUIDS'] > 1)) {
                    $message = lang('groups_deleted');
                } else {
                    $message = lang('group_deleted');
                }
                if (!empty($data['GroupGUIDS'])) {
                    foreach ($data['GroupGUIDS'] as $g_id) {
                        initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $g_id, 'user_id' => $user_id, 'Members' => array(), 'Status' => 'Remove'));
                    }
                }
                $result['Message'] = $message;
                //initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
            } elseif ($data['ActionType'] == 'Block' && $data['isSiteOwner'] == TRUE) {
                $this->db->where('GroupID', $data['GroupID']);
                $this->db->update(GROUPS, array('StatusID' => 4, 'BlockReason' => $data['Reason']));
                $result['Message'] = lang('group_blocked');
                initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
            } else {
                $result['ResponseCode'] = 501;
                $result['Message'] = lang('permission_denied');
            }
            if (CACHE_ENABLE) {
                $this->cache->delete('group_cache_' . $data['GroupID']);
            }
            return $result;
        }
    }

    /**
     * [members To get users of an group]
     * @param  [int] $group_id      	[event id]
     * @param  [int] $user_id       	[User id]
     * @param  string $count_flag   	[count flag]
     * @param  string $search_keyword 	[search keyword]
     * @param  [string] $page_no    	[page number]
     * @param  [string] $page_size      [page size]
     * @return [type]                 	[description]
     */
    function members($group_id, $user_id, $count_flag = FALSE, $search_keyword = '', $page_no = '', $page_size = '', $filter = '', $order_by = "Name", $is_admin = FALSE, $members_exclude = array(), $sort_by = '') {

        $result = array();

        if ($is_admin) {
            $is_admin = $this->is_admin($user_id, $group_id);
        }
        
            $this->load->model('users/user_model');
        $friend_followers_list = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, FALSE);
        $friends = $friend_followers_list['Friends'];
            $friends[] = 0;
        $followers = $friend_followers_list['Follow'];
        $followers[] = 0;

        $blocked_users = $this->activity_model->block_user_list($user_id, 3);

        $this->db->select('(CASE GM.ModuleID 
							WHEN 3 THEN PU.Url   
							ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE GM.ModuleID 
							WHEN 1 THEN G.GroupGUID 
							WHEN 3 THEN U.UserGUID 
							ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('(CASE GM.ModuleID 
							WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
							WHEN 3 THEN IF(U.ProfilePicture="","",U.ProfilePicture)   
							ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('(CASE GM.ModuleID 
							WHEN 1 THEN G.GroupName 
							WHEN 3 THEN CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,"")) 
							ELSE "" END) AS name', FALSE);

        $this->db->select('(CASE GM.ModuleID 
							WHEN 1 THEN G.GroupName 
							WHEN 3 THEN IFNULL(U.FirstName,"") 
							ELSE "" END) AS FirstName', FALSE);

        $this->db->select('(CASE GM.ModuleID  
							WHEN 3 THEN IFNULL(U.LastName,"") 
							ELSE "" END) AS LastName', FALSE);

        $this->db->select('IFNULL(G.GroupName,"") AS GroupName', FALSE);


        $this->db->select('GM.ModuleID, GM.ModuleEntityID, GM.CanPostOnWall, GM.StatusID, GM.ModuleRoleID,GM.IsExpert,GM.CanPostOnWall,GM.CanComment,GM.CanCreateKnowledgeBase', FALSE);
        $this->db->from(GROUPMEMBERS . " AS GM");
        $this->db->join(GROUPS . " G", "G.GroupID=GM.ModuleEntityID AND GM.ModuleID=1", "LEFT");
        $this->db->join(USERS . " U", "U.UserID=GM.ModuleEntityID AND GM.ModuleID=3 AND U.StatusID NOT IN (3)", "LEFT");

        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");

        $this->db->where('GM.GroupID', $group_id);
        if ($is_admin) {
            if ($filter == 'Pending') {
                $this->db->where_in('GM.StatusID', array(1, 18));
            } else if ($filter == 'AllMembers') {
                $this->db->where("GM.StatusID", '2');
            } else {
                $this->db->where_in("GM.StatusID", array('1', '2'));
            }
        } else {
            if ($filter == 'Pending') {
                $this->db->where_in('GM.StatusID', array(1, 18));
            } else {
                $this->db->where("GM.StatusID", '2');
            }
        }

        if (is_array($members_exclude) && !empty($members_exclude)) {
            $this->db->where("IF(GM.ModuleID=3,GM.ModuleEntityID NOT IN(" . implode(',', $members_exclude) . "),TRUE)", NULL, FALSE);
        }

        if (!empty($search_keyword)) {
            $search_keyword = $this->db->escape_like_str($search_keyword); 
            $this->db->having("name LIKE '%" . $search_keyword . "%'", NULL, FALSE);
        }

        if ($filter) {
            $filter = ucfirst($filter);
            if ($filter == 'Member') {
                $this->db->where('GM.ModuleRoleID', '6');
                if (!empty($friends)) {
                    $friends_list = implode(",", $friends);

                    $this->db->_protect_identifiers = FALSE;
                    $this->db->where_not_in('GM.ModuleEntityID', $friends_list);
                    $this->db->_protect_identifiers = TRUE;
                }
            } else if ($filter == 'Admin') {
                $this->db->where_in('GM.ModuleRoleID', array('4', '5'));
            } else if ($filter == 'Pending') {
                //$this->db->where('GM.AddedAs', 1);
            } else if ($filter == 'CanPost') {
                $this->db->where('GM.CanPostOnWall', 1);
                $this->db->where_in('GM.ModuleRoleID', array(4, 5, 6));
            } else if ($filter == 'CanComment') {
                $this->db->where('GM.CanComment', 1);
                $this->db->where_in('GM.ModuleRoleID', array(4, 5, 6));
            } else if ($filter == 'KnowledgeBase') {
                $this->db->where('GM.CanCreateKnowledgeBase', 1);
                $this->db->where_in('GM.ModuleRoleID', array(4, 5, 6));
            } elseif ($filter == 'Expert') {
                $this->db->where('GM.IsExpert', 1);
            } else if ($filter == 'AllMembers') {
                if (!empty($friends)) {
                    $this->db->order_by("CASE WHEN GM.ModuleID = 3 AND GM.ModuleEntityID IN ('" . implode(',', $friends) . "')  THEN 1 ELSE 0 END DESC");
                }
            } else if ($filter == 'Friends') {
                if (!empty($friends)) {
                    $friends_list = implode(",", $friends);

                    $this->db->_protect_identifiers = FALSE;
                    $this->db->where_in('GM.ModuleEntityID', $friends_list);
                    $this->db->where('GM.ModuleID', 3);
                    $this->db->_protect_identifiers = TRUE;
                }
                $this->db->where('GM.StatusID', 2);
            } elseif ($filter == 'Other') {
                $this->db->where(array('GM.CanPostOnWall' => 0, 'GM.CanComment' => 0, 'GM.CanCreateKnowledgeBase' => 0));
                $this->db->where('GM.ModuleRoleID', 6);
            }

            if ($filter == 'Pending')
            {
                $this->db->where_in('GM.StatusID', array(1, 18));
            }
            else
            {
                $this->db->where('GM.StatusID', 2);
            }
        }

        if ($sort_by == false || $sort_by == '')
            $sort_by = 'ASC';

        if ($sort_by == 'true')
            $sort_by = 'DESC';



        if ($order_by == 'CreatedDate') {
            $this->db->order_by('GM.JoinedAt', 'DESC');
        } elseif ($order_by == 'Name') {
            $this->db->order_by('name', $sort_by);
        } elseif ($order_by == 'Admin') {
            $this->db->order_by('GM.ModuleRoleID', $sort_by);
        } elseif ($order_by == 'Expert') {
            $this->db->order_by('GM.IsExpert', $sort_by);
        } elseif ($order_by == 'CanPost') {
            $this->db->order_by('GM.CanPostOnWall', $sort_by);
        } elseif ($order_by == 'CanComment') {
            $this->db->order_by('GM.CanComment', $sort_by);
        } elseif ($order_by == 'KnowledgeBase') {

            $this->db->order_by('GM.CanCreateKnowledgeBase', $sort_by);
        } else {
            $this->db->order_by('GM.GroupMemeberID', 'ASC');
        }


        if (!empty($blocked_users)) {
            $this->db->where("IF(U.UserID is not null,U.UserID NOT IN (" . implode(',', $blocked_users) . "),true)", null, false);
        }
        $this->db->where_not_in("U.StatusID",array(3,4));
        //echo $this->db->last_query(); die;
        if (empty($count_flag)) { // check if array needed
            if (!empty($page_size)) { // Check for pagination                
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }
            $query = $this->db->get();
            //echo $this->db->last_query(); die;
            $result = $query->result_array();

            foreach ($result as $key => $val) {
                $module_id = $val['ModuleID'];
                $entity_id = $val['ModuleEntityID'];
                if ($module_id == 1) {
                    
                    if ($result[$key]['FirstName'] == '') {
                        $result[$key]['FirstName'] = $this->get_informal_group_name($val['ModuleEntityID'], $user_id);
                    }
                    
                    //$group_details['GroupName'] = $this->group_model->get_informal_group_name($entity_id, $user_id, 0, false, array(), $entity_details['Members']);
                    // Get profile Url
                    $group_url_details = $this->get_group_details_by_id($entity_id, '', $val);
                    $result[$key]['ProfileURL'] = $this->get_group_url($entity_id, $group_url_details['GroupNameTitle'], false, 'index');                    
                }
                $result[$key]['ShowFriendsBtn'] = 0;
                $result[$key]['ShowFollowBtn'] = 0;
                $result[$key]['Location'] = '';
                if ($module_id == 3) {
                    // Privacy check and set / unset keys according to it                  
                    if ($user_id != $entity_id) {
                        $result[$key]['FriendStatus'] = $this->friend_model->checkFriendStatus($user_id, $entity_id); //1 - already friend, 2 - Pending Request, 3 - Accept Friend Request, 4 - Not yet friend or sent request

                        $result[$key]['follow'] = 'Follow';
                        $result[$key]['IsFollow'] =0;
                        
                        if (in_array($entity_id, $followers))
                        {
                            $result[$key]['follow'] = 'Unfollow';
                            $result[$key]['IsFollow'] =1;
                        }
                
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
                        if(!$this->settings_model->isDisabled(10)){
                        $result[$key]['ShowFriendsBtn'] = 1;
                        }else{
                            $result[$key]['ShowFollowBtn']=1;
                        }

                        if(!$this->settings_model->isDisabled(25)){
                            //If message module is not disabled
                            $result[$key]['ShowMessageBtn'] = 1;
                        }else{
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
                                    if ($result[$key]['ShowMessageBtn'] ==1 && $privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation))
                                    {
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
                unset($result[$key]['name']);
            }
            return $result;
        } else {
            $query = $this->db->get();
            return $query->num_rows();
        }
    }

    /**
     * [cancel_invitation User can cancel his request to join particular group]
     * @param [int]  $group_id          	[Group ID]
     * @param [int]  $user_id           	[User ID]
     */
    function cancel_invitation($group_id, $user_id) {
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('ModuleID', 3);
        $this->db->where('GroupID', $group_id);
        $this->db->where('StatusID', '18');
        $this->db->delete(GROUPMEMBERS);
    }

    /**
     * [is_sent_invite Used to check if the user sent request to join this group.]
     * @param  [type]  $group_id [Group ID]
     * @param  [type]  $user_id  [User ID]
     * @return boolean           [TRUE/FALSE]
     */
    function is_sent_invite($group_id, $user_id) {
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('ModuleID', 3);
        $this->db->where('GroupID', $group_id);
        $this->db->where('StatusID', '18');
        $query = $this->db->get(GROUPMEMBERS);
        if ($query->num_rows()) {
            return 1;
        } else {
            return 0;
        }
    }

    function has_access($user_id, $group_id) {
        $members = $this->get_group_members_id_recursive($group_id);
        $has_access = FALSE;
        if (!empty($members)) {
            if (in_array($user_id, $members)) {
                $has_access = TRUE;
            }
        }
        return $has_access;
    }

    /*
      | Function to get users from members
      | Input  : array
      | Output : array
     */
    public function get_notification_members($members) {
        if (!empty($members)) {
            $notification_members = array();
            foreach ($members as $key => $member) {
                if ($member['ModuleID'] == 1) {
                    $group_members = $this->get_group_members_id_recursive($member['ModuleEntityID']);
                    if (!empty($group_members)) {
                        foreach ($group_members as $key => $g_member) {
                            $notification_members[] = $g_member;
                        }
                    }
                } else {
                    $notification_members[] = $member['ModuleEntityID'];
                }
            }
            return $notification_members;
        } else {
            return array();
        }
    }

    public function send_instant_group_create_notification($members = array(), $group_id, $type = 'create', $activity_id = 0, $nti = '', $added_by = '', $user_id = 0, $logged_in_name = '') {
        $this->load->model('notification_model');
        if (!empty($members)) {
            $this->load->model('notification_model');
            $this->UserID = isset($this->UserID) ? $this->UserID : $user_id;
            $this->LoggedInName = isset($this->LoggedInName) ? $this->LoggedInName : $logged_in_name;
            $notification_members = array();
            $notification_members = $this->get_notification_members($members);
            if ($type == 'post') {
                $notification_members[] = $this->UserID;
            }
            $parameters = array();
            $parameters[0]['ReferenceID'] = $group_id;
            $parameters[0]['Type'] = 'Group';
            if (!empty($notification_members)) {
                $group_d = get_detail_by_id($group_id, 1, 'GroupGUID,CreatedBy', 2);
                $group_guid = $group_d['GroupGUID'];
                $created_by = $group_d['CreatedBy'];
                $member_details = $this->get_member_detail($notification_members);
                $member_count = count($notification_members);

                $post_type = '0';
                if ($activity_id) {
                    $this->db->select('PostType');
                    $this->db->from(ACTIVITY);
                    $this->db->where('ActivityID', $activity_id);
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        $post_type = $query->row()->PostType;
                    }
                }

                if ($type == 'create') {
                    $notification_type_id = 86;
                    if ($members[0]['StatusID'] == 1) {
                        $notification_type_id = 22;
                    }
                } else if ($post_type == '7') {
                    $notification_type_id = 125;
                } else {
                    $notification_type_id = 87;
                    $member_count = $member_count - 1;
                }
                $notification_members = array_unique($notification_members);
                $ref_id = $group_id;
                if ($activity_id) {
                    $ref_id = $activity_id;
                }
                if ($nti == 4 || $nti == 22) {
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $added_by;
                    $parameters[0]['Type'] = 'User';
                    $parameters[1]['ReferenceID'] = $group_id;
                    $parameters[1]['Type'] = 'Group';
                    $this->notification_model->add_notification($nti, $added_by, $notification_members, $group_id, $parameters);
                } else {
                    $this->notification_model->add_notification($notification_type_id, $this->UserID, $notification_members, $ref_id, $parameters);
                }
                
                
                $group_url_details = $this->get_group_details_by_id($group_id, '');
                $data_link = site_url($this->get_group_url($group_id, $group_url_details['GroupNameTitle'], false, 'index'));
                                
                if (!empty($activity_id)) {
                    $activity = get_detail_by_id($activity_id, '*');                    
                    $data_link = get_single_post_url($activity);                                        
                }
                
                
                $FromFullName = $this->LoggedInName;
                foreach ($notification_members as $key => $n_member) {
                    if ($n_member != $this->UserID) {
                        if ($type == 'create') {
                            notify_node('liveFeed', array('Type' => 'GA', 'UserID' => $n_member, 'EntityGUID' => $group_guid));
                        }

                        $member_description = "";
                        $Data = get_detail_by_id($n_member, 3, 'FirstName,LastName,Email', 2);

                        $emailDataArr = array();
                        $emailDataArr['IsSave'] = EMAIL_ANALYTICS; //If you want to send email only not save in DB then set 1 otherwise set 0
                        $emailDataArr['IsResend'] = 0;
                        $emailDataArr['Email'] = $Data['Email'];
                        //$emailDataArr['Email'] 			= "piyushj@vinfotech.com";
                        $emailDataArr['EmailTypeID'] = COMMUNICATION_EMAIL_TYPE_ID;
                        $emailDataArr['UserID'] = $this->UserID;
                        $emailDataArr['StatusMessage'] = "InstantGroup";
                        $FullName = $Data['FirstName'] . " " . $Data['LastName'];
                        $emailDataArr['Data'] = array("FirstLastName" => $FullName);
                        $emailDataArr['Data']['From'] = $FromFullName;
                                                
                        //$group_details['GroupName'] = $this->group_model->get_informal_group_name($group_id, $user_id, 0, false, array(), $entity_details['Members']);
                        // Get profile Url
                        
                        $emailDataArr['Data']['Link'] = $data_link;                                                

                        if (!empty($member_details)) {
                            $i = 1;
                            foreach ($member_details as $key => $m_detail) {
                                if ($m_detail['UserID'] != $n_member) {
                                    if (($member_count == 2 || $member_count == 3) && $i == ($member_count - 1)) {
                                        $member_description = rtrim($member_description, ', ');
                                        $member_description .= " and " . $m_detail['FullName'];
                                    } else {
                                        if ($i > 3) {
                                            $member_description = rtrim($member_description, ', ');
                                            $member_description .= " and " . ($member_count - 4) . " Others";
                                            break;
                                        } else {
                                            if ($i == 1 && $type == 'post') {
                                                $member_description .= " " . $m_detail['FullName'] . ", ";
                                            } else if ($i == 1) {
                                                $member_description .= ", " . $m_detail['FullName'] . ", ";
                                            } else {
                                                $member_description .= $m_detail['FullName'] . ", ";
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            }
                        }
                        //$emailDataArr['Data']['member_description'] = rtrim($member_description,', ');

                        if ($type == 'create') {
                            $a = array($n_member, $created_by);
                            $emailDataArr['Data']['member_description'] = $this->get_informal_group_name($group_id, $this->UserID, 0, false, array($n_member, $created_by));
                            $emailDataArr['Subject'] = $FromFullName . " has added you to a conversation";
                            $emailDataArr['TemplateName'] = "emailer/instant_group_creation";
                        } else {

                            $emailDataArr['Data']['member_description'] = $this->get_informal_group_name($group_id, $this->UserID, 0, false, array($n_member));
                            $emailDataArr['Subject'] = "You have a new message from the conversation with " . $emailDataArr['Data']['member_description'];
                            $emailDataArr['TemplateName'] = "emailer/instant_group_new_post";
                        }
                        sendEmailAndSave($emailDataArr, 1);
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function get_member_detail($members) {
        if (!empty($members)) {
            $this->db->select('CONCAT(FirstName,"",LastName) AS FullName,UserID', false);
            $this->db->where_in('UserID', $members);
            return $this->db->get(USERS)->result_array();
        } else {
            return array();
        }
    }

    function get_all_members($GroupID) {
        return $this->db->get_where(GROUPMEMBERS, array('GroupID' => $GroupID))->result_array();
    }

    function get_members_with_module($GroupID) {
        $members = $this->get_all_members($GroupID);
        if (!empty($members)) {
            $module_wise_data = array('Users' => array(), 'Groups' => array());
            foreach ($members as $key => $value) {
                if ($value['ModuleID'] == 3) {
                    $module_wise_data['Users'][] = $value['ModuleEntityID'];
                } else {
                    $module_wise_data['Groups'][] = $value['ModuleEntityID'];
                }
            }
            return $module_wise_data;
        }
        return array();
    }

    /**
     * [leave - allows user to leave a group]
     * @param  [int] 	$GroupID    	[Group Id]
     * @param  [int] 	$user_id  		[User ID]
     */
    function leave($data) {
        if (!empty($data)) {
            $this->cache->delete('my_group_' . $data['ModuleEntityID']);
            $this->db->where('GroupID', $data['GroupID']);
            $this->db->where('ModuleEntityID', $data['ModuleEntityID']);
            $this->db->where('ModuleID', $data['ModuleID']);
            $this->db->where('StatusID', 2);
            $this->db->update(GROUPMEMBERS, array('StatusID' => 14, 'ModuleRoleID' => 6));

            /*$member_check = $this->db->get_where(GROUPS, array('GroupID' => $data['GroupID']));
            $member_count = $member_check->row();
            if ($member_count->MemberCount > 0) {
                $this->db->set('MemberCount', 'MemberCount-1', FALSE);
            } else {
                $this->db->set('MemberCount', '0', FALSE);
            }
            
            #update member count
            $this->db->where('GroupID', $data['GroupID']);
            $this->db->update(GROUPS);
             *              
             */
            
            $set_field = 'MemberCount';
            $count = -1;
            $this->db->set($set_field, "$set_field+($count)", FALSE);                
            $this->db->where("GroupID", $data['GroupID']);
            $this->db->where("MemberCount >" ,0);
            $this->db->update(GROUPS);

            $this->db->where('ActivityTypeID', '4');
            $this->db->where('ModuleID', '1');
            $this->db->where('ModuleEntityID', $data['GroupID']);
            $this->db->where('PostAsModuleEntityID', $data['ModuleEntityID']);
            $this->db->where('PostAsModuleID', $data['ModuleID']);
            $this->db->update(ACTIVITY, array('StatusID' => '3'));

            if ($data['memberGroupRole'] == 4) {
                $this->db->select_max('JoinedAt');
                $this->db->select('ModuleID,ModuleEntityID');
                $this->db->where('GroupID', $data['GroupID']);
                $this->db->where('StatusID', 2);
                $this->db->where('ModuleRoleID !=', 4);
                $this->db->where('ModuleID', 3);
                $this->db->having('ModuleEntityID IS NOT NULL');
                $query = $this->db->get(GROUPMEMBERS);

                // Make admin to second member who joined after creater
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $this->db->where('GroupID', $data['GroupID']);
                    $this->db->where('ModuleEntityID', $result['ModuleEntityID']);
                    $this->db->where('ModuleID', $result['ModuleID']);
                    $this->db->update(GROUPMEMBERS, array('ModuleRoleID' => 4));

                    $this->db->set('CreatedBy', $result['ModuleEntityID']);
                    $this->db->where('GroupID', $data['GroupID']);
                    $this->db->update(GROUPS);
                } else {
                    $this->db->where('GroupID', $data['GroupID']);
                    $this->db->update(GROUPS, array('StatusID' => 3));
                }
            }

            if (!$this->settings_model->isDisabled(28) && $data['ModuleID'] == 3) {
                $this->load->model(array('reminder/reminder_model'));
                $this->reminder_model->delete_all($data['ModuleEntityID'], 1, $data['GroupID']);
            }

            $result['Message'] = lang('group_left');
            //initiate_worker_job('get_groups_of_all_user', array('ENVIRONMENT' => ENVIRONMENT));
            initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $data['GroupID'], 'user_id' => '', 'Members' => array(array('ModuleEntityID' => $data['ModuleEntityID'], 'ModuleID' => $data['ModuleID'])), 'Status' => 'Remove'));
            return $result;
        }
    }

    /**
     * [is_invited Used to Check member is invited to join this group or not]
     * @param  [int] 	$GroupID    	[Group Id]
     * @param  [int] 	$user_id  		[User ID]
     * @return [bool] 	[true/False]       	
     */
    function is_invited($group_id, $member_id) {
        $this->db->select('G.GroupID');
        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' GM', 'G.GroupID=GM.GroupID', 'left');
        $this->db->where('GM.ModuleEntityID', $member_id);
        $this->db->where('GM.ModuleID', 3);
        $this->db->where('GM.StatusID', 1);
        $this->db->where('G.StatusID', 2);
        $this->db->where('GM.GroupID', $group_id);
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [action_request Used to accept/deny group invite request]
     * @param  [int] 	$GroupID    	[Group Id]
     * @param  [int] 	$user_id  		[User ID]
     * @param [int] 	$StatusID       [1=accepted,13=denied]	
     */
    function action_request($data) {
        if (!empty($data)) {
            $this->db->where('GroupID', $data['GroupID']);
            $this->db->where('ModuleEntityID', $data['UserID']);
            $this->db->where('ModuleID', 3);
            $this->db->where('StatusID', 1);
            $this->db->update(GROUPMEMBERS, array('StatusID' => $data['StatusID'], 'JoinedAt' => get_current_date('%Y-%m-%d %H:%i:%s')));

            if ($data['StatusID'] == 13) {
                $result['Message'] = lang('invite_reject');
            } else {
                $this->activity_model->addActivity(1, $data['GroupID'], 4, $data['UserID']);
                $result['Message'] = lang('invite_accept');

                /* Initiate job for group assignment */                
                initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $data['GroupID'], 'user_id' => $data['UserID'], 'Members' => array(array('ModuleEntityID' => $data['UserID'], 'ModuleID' => 3)), 'Status' => 'Join'));
            }
            return $result;
        }
    }

    /**
     * [toggle_user_role ,Owner/Creator/Admin of group can assign/remove user as a admin]
     * @param  [int] 		$data    		[Group details]
     */
    function toggle_user_role($data, $user_id = 0) {
        if (!empty($data)) {
            if ($data['RoleAction'] == 'Add') {
                $update_data = array('ModuleRoleID' => $data['RoleID']);
                if ($data['RoleID'] == 5) {
                    $update_data['CanPostOnWall'] = 1;
                }
                $this->db->where('GroupID', $data['GroupID']);
                $this->db->where('ModuleEntityID', $data['ModuleEntityID']);
                $this->db->where('ModuleID', $data['ModuleID']);
                $this->db->update(GROUPMEMBERS, $update_data);
                $result['Message'] = lang('group_role_changed');
                if ($data['RoleID'] == 5) {
                    $parameters = array();
                    $parameters[0]['ReferenceID'] = $data['GroupID'];
                    $parameters[0]['Type'] = 'Group';
                    if ($data['ModuleID'] == 1) {
                        $members = $this->get_group_members_id_recursive($data['GroupID'], array(), array(), TRUE);
                    } else {
                        $members = array($data['ModuleEntityID']);
                    }
                    $this->notification_model->add_notification(62, $user_id, $members, $data['GroupID'], $parameters);
                }
            } elseif ($data['RoleAction'] == 'Remove') {
                $this->db->where('GroupID', $data['GroupID']);
                $this->db->where('ModuleEntityID', $data['ModuleEntityID']);
                $this->db->where('ModuleID', $data['ModuleID']);
                $this->db->update(GROUPMEMBERS, array('ModuleRoleID' => 6));
                $result['Message'] = lang('group_role_changed');
            }
        }
        return $result;
    }

    /**
     * [get_user_ids_to_send_notification to send notification role wise when new member join a group ]
     * @param  [int] 		$GroupID    		[Group Id]	
     * @param  [int] 		$user_id    			[User Id of the member who is going to be added]	
     * @param  [int] 		$type    			[Group type Public/Private]			
     * @param  [int] 		$current_user    	[Logged In userID]			
     */
    function get_user_ids_to_send_notification($group_id, $user_id, $type = 1, $current_user) {

        $arr['groupOwners'] = $this->get_group_members_id_recursive($group_id, array(), array(), TRUE);
        return $arr;

        $growner_owners = array();
        $group_members = array();
        $friends = array();

        // get group owner/admin ids
        $this->db->select('UserID');
        $this->db->where('GroupID', $group_id);
        $this->db->where('StatusID', 2);
        $this->db->where('(ModuleRoleID = "4" OR ModuleRoleID = "5") ');
        $this->db->where('UserID !=', $user_id);
        $this->db->where('UserID !=', $current_user);
        $query_owners = $this->db->get(GROUPMEMBERS);

        if ($query_owners->num_rows() > 0) {
            $members = $query_owners->result_array();
            foreach ($members as $key => $value) {
                $growner_owners[] = $members[$key]['UserID'];
            }
        }

        // get group members Ids
        $this->db->select('UserID');
        $this->db->where('GroupID', $group_id);
        $this->db->where('StatusID ', 2);
        $this->db->where('(ModuleRoleID != "4" AND ModuleRoleID != "5") ');
        $this->db->where('UserID !=', $user_id);
        $this->db->where('UserID !=', $current_user);

        if (!empty($growner_owners)) {
            $this->db->where_not_in('UserID', $growner_owners);
        }            

        $query_members = $this->db->get(GROUPMEMBERS);

        if ($query_members->num_rows() > 0) {
            $members_group = $query_members->result_array();
            foreach ($members_group as $key => $value) {
                $group_members[] = $members_group[$key]['UserID'];
            }
        }
        // get friends and followler Ids if group is Public / type =1 for public
        $all_group_members = array_merge($growner_owners, $group_members);
        if ($type == 1) {
            $friend_disabled = $this->settings_model->isDisabled(10);
            if(!$friend_disabled) {
                $this->db->select('FriendID');
                $this->db->where('Status ', 1);
                $this->db->where('UserID', $user_id);
                $this->db->where('FriendID !=', $current_user);

                if (!empty($all_group_members)) {
                    $this->db->where_not_in('FriendID', $all_group_members);
                }

                $query_friends = $this->db->get(FRIENDS);
                if ($query_friends->num_rows() > 0) {
                    $all_friends = $query_friends->result_array();
                    foreach ($all_friends as $key => $value) {
                        $friends[] = $all_friends[$key]['FriendID'];
                    }
                }
            }else{
                $this->db->select('TypeEntityID');
                $this->db->where('StatusID ', 2);
                $this->db->where('UserID', $user_id);
                $this->db->where('TypeEntityID !=', $current_user);
                $this->db->where('Type', 'user');

                if (!empty($all_group_members)) {
                    $this->db->where_not_in('TypeEntityID', $all_group_members);
                }

                $query_friends = $this->db->get(FOLLOW);
                if ($query_friends->num_rows() > 0) {
                    $all_friends = $query_friends->result_array();
                    foreach ($all_friends as $key => $value) {
                        $friends[] = $all_friends[$key]['TypeEntityID'];
                    }
                }
            }
        }

        $result_data['friendsFollowlers'] = $friends;
        $result_data['all_group_members'] = $all_group_members;
        $result_data['groupOwners'] = $growner_owners;
        $result_data['GroupMembers'] = $group_members;
        return $result_data;
    }

    /**
     * [get_group_categories  used to count number of groups ]
     * @param [int]         $GroupID            [GroupID]
     * @return [array]      Categories
     */
    function get_group_categories($group_id) {
        $cache_data = array();
        if (CACHE_ENABLE && $group_id) {
            $cache_data = $this->cache->get('group_cache_' . $group_id);
            if (!empty($cache_data)) {
                return $cache_data['Category'];
            }
        }
        if (empty($cache_data)) {
            //initiate_worker_job('group_cache', array('group_id'=>$group_id ));
            $category = array('Name' => '', 'CategoryID' => 0, 'SubCategory' => array());
            $this->db->select('C.Name');
            $this->db->select('E.CategoryID');
            $this->db->select('C.ParentID');
            $this->db->where('E.ModuleID', 1);
            $this->db->where('E.ModuleEntityID', $group_id);
            $this->db->from(ENTITYCATEGORY . ' E');
            $this->db->join(CATEGORYMASTER . ' C', 'C.CategoryID = E.CategoryID');
            $sql = $this->db->get();
            if ($sql->num_rows() > 0) {
                $category_detail = $sql->row_array();
                $parent_id = $category_detail['ParentID'];
                unset($category_detail['ParentID']);
                $category['Name'] = $category_detail['Name'];
                $category['CategoryID'] = $category_detail['CategoryID'];
                if (!empty($parent_id)) {
                    $this->db->select('CM.Name');
                    $this->db->select('CM.CategoryID');
                    $this->db->where('CM.CategoryID', $parent_id);
                    $this->db->from(CATEGORYMASTER . ' CM');
                    $query = $this->db->get();
                    if ($query->num_rows() > 0) {
                        $category['SubCategory'] = $category_detail;

                        $category_detail = $query->row_array();
                        $category['Name'] = $category_detail['Name'];
                        $category['CategoryID'] = $category_detail['CategoryID'];
                    }
                }
            }
            return $category;
        }
    }

    /**
     * [get_friends_for_group  used to get friend list to add in group ]	
     * @param [int] 		$user_id 			
     * @param [int] 		$group_id 			[GroupID]
     * @param [String] 	$search_key 		[SearchKey]
     */
    function get_friends_for_group($search_key, $user_id, $group_id, $friends_only = false) {

        $blockedUsers = $this->activity_model->block_user_list($user_id, 3);
        $blockedGroupUsers = $this->activity_model->block_user_list_group($group_id);
        if (!empty($blockedGroupUsers)) {
            $blockedUsers = array_merge($blockedUsers, $blockedGroupUsers);
        }
        $this->db->select('U.UserID, U.UserGUID, U.FirstName, U.LastName');
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);
        $this->db->from(USERS . ' U');
        $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=U.UserID', 'left');
        $this->db->where('UP.PrivacyLabelKey', 'group_invite');
        $friend_disabled = $this->settings_model->isDisabled(10);
        if(!$friend_disabled) {
            $subSql = " (SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID) ";
        }else{
            $subSql = " (SELECT F2.TypeEntityID FROM Follow F JOIN Follow F2 ON F.TypeEntityID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.StatusID='2' AND F.StatusID='2' AND F2.Type='user' AND F.Type='user' GROUP BY F2.TypeEntityID) ";
        }
        $privacy_condition = "
			IF(UP.Value='everyone',true, 
				IF(UP.Value='network', U.UserID IN
				".$subSql." , 
				IF(UP.Value='friend',U.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1),''))
			)
		";

        if ($friends_only)
        {
            if($friend_disabled){
                $privacy_condition = " U.UserID IN(SELECT TypeEntityID FROM Follow WHERE UserID=" . $user_id . " AND StatusID=2) ";
            }else {
                $privacy_condition = " U.UserID IN(SELECT FriendID FROM Friends WHERE UserID=" . $user_id . " AND Status=1) ";
            }
        }

        if ($blockedUsers) {
            $this->db->where_not_in('U.UserID', $blockedUsers);
        }
        $this->db->where($privacy_condition, NULL, FALSE);
        $this->db->where('U.UserID!=' . $user_id, NULL, FALSE);
        $this->db->where('(U.UserID not in (select ModuleEntityID AS UserID from ' . GROUPMEMBERS . ' gm where gm.GroupID = ' . $group_id . ' AND gm.ModuleID=3 AND gm.StatusID!=14) ) ', NUll, FALSE);
        $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_key) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_key) . "%' or concat(U.FirstName,' ',	U.LastName) like '" . $this->db->escape_like_str($search_key) . "%')", NULL, FALSE);
        $this->db->where_not_in('U.StatusID', array(3, 4));

        $this->db->group_by('U.UserID');

        $query = $this->db->get();
        $result = array();

        if ($query->num_rows() > 0) {
            $result = $query->result_array();

            foreach ($result as $key => $value) {
                $result[$key]['Name'] = stripcslashes($result[$key]['FirstName']) . ' ' . stripcslashes($result[$key]['LastName']);

                $entity_id = $value['UserID'];
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
                        }
                    }
                }

                unset($result[$key]['UserID']);
                unset($result[$key]['FirstName']);
                unset($result[$key]['LastName']);
            }
        }
        return $result;
    }

    function get_friends_for_invite($search_key, $user_id, $group_id, $limit, $offset, $total_records = 0) {
        $friend_disabled = $this->settings_model->isDisabled(10);
        $this->db->select('U.UserID,U.FirstName,U.LastName,U.ProfilePicture,PR.Url,U.UserGUID,CT.CountryName as Country,C.Name as City');
        if(!$friend_disabled) {
            $this->db->from(FRIENDS . ' F');
            $this->db->join(USERS . ' U', 'U.UserID = F.FriendID', 'left');
        }
        else {
            $this->db->from(FOLLOW . ' F');
            $this->db->join(USERS . ' U', "U.UserID = F.TypeEntityID AND F.Type='Type'", 'left');
        }


        $this->db->join(USERDETAILS . ' UD', 'UD.UserID=U.UserID', 'left');
        $this->db->join(COUNTRYMASTER . ' CT', 'CT.CountryID=UD.CountryID', 'left');
        $this->db->join(CITIES . ' C', 'C.CityID=UD.CityID', 'left');
        $this->db->join(PROFILEURL . ' PR', 'PR.EntityID=U.UserID', 'left');
        $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=U.UserID', 'left');
        $this->db->where('UP.PrivacyLabelKey', 'group_invite');

        if(!$friend_disabled) {
            $subSql = " (SELECT F2.FriendID FROM Friends F JOIN Friends F2 ON F.FriendID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.Status='1' AND F.Status='1' GROUP BY F2.FriendID) ";
        }else{
            $subSql = " (SELECT F2.TypeEntityID FROM Follow F JOIN Follow F2 ON F.TypeEntityID = F2.UserID WHERE F.UserID = " . $user_id . " AND F2.StatusID='2' AND F.StatusID='2' AND F2.Type='user' AND F.Type='user' GROUP BY F2.TypeEntityID) ";
        }
        $privacy_condition = "
			IF(UP.Value='everyone',true, 
				IF(UP.Value='network', U.UserID IN ".$subSql.", 
				IF(UP.Value='friend',U.UserID IN(SELECT FriendID FROM Friends AS F1 WHERE F1.UserID=" . $user_id . " AND Status=1),''))
			) ";
        $this->db->where($privacy_condition, NULL, FALSE);
        $this->db->where('F.UserID', $user_id);
        $this->db->where('PR.EntityType', 'User');

        if(!$friend_disabled) {
            $this->db->where(' (F.FriendID not in (select gm.ModuleEntityID from ' . GROUPMEMBERS . ' gm where gm.GroupID = ' . $group_id . ' AND gm.ModuleID=3 AND gm.StatusID!=14) ) ', NUll, FALSE);
        }else{
            $this->db->where(' (F.TypeEntityID not in (select gm.ModuleEntityID from ' . GROUPMEMBERS . ' gm where gm.GroupID = ' . $group_id . ' AND gm.ModuleID=3 AND gm.StatusID!=14) ) ', NUll, FALSE);
        }

        $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_key) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_key) . "%' or concat(U.FirstName,' ',	U.LastName) like '" . $this->db->escape_like_str($search_key) . "%')", NULL, FALSE);
        if(!$friend_disabled) {
            $this->db->where('F.Status', '1');
        }else{
            $this->db->where('F.StatusID', '2');
        }
        if (!$total_records) {
            $this->db->limit($limit, $offset);
        }
        $this->db->group_by('U.UserID');
        $query = $this->db->get();
        $result = array();
        if ($total_records) {
            return $query->num_rows();
        }
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            foreach ($result as $key => $val) {
                $permission = $this->privacy_model->check_privacy($user_id, $val['UserID'], 'view_profile_picture');
                if (!$permission) {
                    $result[$key]['ProfilePicture'] = 'user_default.jpg';
                }
                unset($result[$key]['UserID']);
            }
        }
        return $result;
    }

    /**
     * [get_blocked_group_list]	
     * @param [int] 		$user_id 			
     */
    function get_blocked_group_list($user_id) {
        $arr = array();
        $this->db->select('ModuleEntityID');
        $this->db->where('EntityID', $user_id);
        $this->db->where('ModuleID', '1');
        $query = $this->db->get(BLOCKUSER);
        if ($query->num_rows()) {
            foreach ($query->result() as $result) {
                $arr[] = $result->ModuleEntityID;
            }
        }
        return $arr;
    }

    /**
     * [check_active_status  to check is active or not in group]	
     * @param [int] 		$entity_id 
     * @param [int] 		$entity_module_id 			
     * @param [int] 		$group_id 	[Group ID]
     */
    function check_active_status($entity_id, $entity_module_id, $group_id) {
        $this->db->select('ModuleEntityID');
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleEntityID', $entity_id);
        $this->db->where('ModuleID', $entity_module_id);
        $this->db->where('StatusID', '2');
        $sql = $this->db->get(GROUPMEMBERS);
        if ($sql->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [is_admin to check user is admin/owner of a paticular group]    
     * @param [int]         $user_id            
     * @param [int]         $group_id   [Group ID]
     */
    function is_admin($user_id, $group_id) {
        $group_ids = '';
        $admins = array();
        if (CACHE_ENABLE) {
            $group_cache_data = $this->cache->get('user_groups_' . $user_id);
            if (!empty($group_cache_data)) {
                $group_ids = $group_cache_data['Manage'];
                if (!empty($group_ids)) {
                    $group_ids = explode(',', $group_ids);
                    if (in_array($group_id, $group_ids)) {
                        return TRUE;
                    } else {
                        return False;
                    }
                }
            }
        }
        if (empty($group_ids)) {
            $admins = $this->get_group_members_id_recursive($group_id, array(), array(), TRUE);
        }
        $is_admin = FALSE;
        if (!empty($admins)) {
            if (in_array($user_id, $admins)) {
                $is_admin = TRUE;
            }
        }
        return $is_admin;
    }

    /**
     * [check_group_membership to check given entity is member of a paticular group]	
     * @param [int] 		$entity_id 
     * @param [int] 		$entity_module_id 			
     * @param [int] 		$group_id 	[Group ID]
     */
    function check_group_membership($entity_id, $entity_module_id, $group_id, $filter = "IsOwner", $need_data = false) {
        $this->db->select('ModuleEntityID, CanPostOnWall, StatusID, ModuleRoleID');
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleEntityID', $entity_id);
        $this->db->where('ModuleID', $entity_module_id);
        //$this->db->where('StatusID', '2');
        if ($filter == "IsOwner" || $filter == "Sticky") {
            $this->db->where('(ModuleRoleID = "4" OR ModuleRoleID = "5") ');
        }

        if ($filter == "IsCreator") {
            $this->db->where('(ModuleRoleID = "4") ');
        }

        $query = $this->db->get(GROUPMEMBERS);
        if ($query->num_rows() > 0) {
            if ($need_data) {
                return $query->row_array();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * [check_site_owner to check user is site owner or not]	
     * @param [int] 		$user_id 			
     */
    function check_site_owner($user_id) {
        $this->db->select('UserID');
        $this->db->where('RoleID', 1);
        $this->db->where('UserID', $user_id);
        $sql = $this->db->get(USERROLES);
        if ($sql->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [get_all_group_admins get array of all group admins]
     * @param  [int] $group_id [Group ID]
     * @return [int]          [array]
     */
    function get_all_group_admins($group_id) {
        $admins = $this->get_group_members_id_recursive($group_id, array(), array(), TRUE);
        return $admins;
    }

    /**
     * [getGroupOwner Used to get the Group Owner ID]
     * @param  [int] $GroupID [Group ID]
     * @return [int]          [Group Owner ID OR FALSE]
     */
    function get_group_owner($group_id) {
        $this->db->select('ModuleEntityID');
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleID', 3);
        $this->db->where('ModuleRoleID', 4);
        $this->db->where('StatusID', 2);
        $sql = $this->db->get(GROUPMEMBERS);
        if ($sql->num_rows() > 0) {
            $res = $sql->row();
            return $res->ModuleEntityID;
        } else {
            return false;
        }
    }

    /**
     * [get_joined_groups Used to get the list of group joined by User]
     * @param  [int]  $user_id [UserID]
     * @param  boolean $flag  [true/false used to return array or comma separated string]
     * @return [type]          [return array or string based on $flag value]
     */
    function get_joined_groups($user_id, $flag = false, $status_id = array(1, 2, 13, 14)) {
        $arr = array(0);
        if($this->settings_model->isDisabled(1)){
            return ($flag) ? $arr : '0';
        }
        
        $this->db->select('G.GroupID');
        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' GM', 'GM.GroupID=G.GroupID', 'left');
        $this->db->where('GM.ModuleEntityID', $user_id);
        $this->db->where('GM.ModuleID', '3');
        $this->db->where('G.StatusID', '2');
        $this->db->where_in('GM.StatusID', $status_id);
        $groups = $this->db->get();
        if ($groups->num_rows()) {
            foreach ($groups->result() as $group) {
                $arr[] = $group->GroupID;
            }
        }
        $arr = array_filter($arr);
        if ($flag) {
            return $arr;
        } else {
            return implode(',', $arr);
        }
    }

    function get_group_members_details($group_id, $page_no, $page_size, $only_friends = array()) {
        $this->db->select('U.FirstName,U.LastName,U.ProfilePicture,P.Url as ProfileURL');
        $this->db->from(GROUPMEMBERS . ' GM');
        $this->db->join(USERS . ' U', 'U.UserID=GM.ModuleEntityID AND GM.ModuleID=3', 'join');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID AND P.EntityType="User"', 'join');
        $this->db->where('GM.GroupID', $group_id);
        $this->db->where('GM.StatusID', '2');

        if (!empty($only_friends)) {
            $this->db->where_in('GM.ModuleEntityID', $only_friends);
        }

        if (!empty($page_size)) {
            $Offset = $this->get_pagination_offset($page_no, $page_size);
            $this->db->limit($page_size, $Offset);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    /**
     * [is_informal used to check is given group is informal or not]
     * @param  [int]  $group_id [Group ID]
     * @return boolean           [TRUE/FALSE]
     */
    function is_informal($group_id) {
        $this->db->select('GroupID');
        $this->db->from(GROUPS);
        $this->db->where('GroupID', $group_id);
        $this->db->where('Type', 'INFORMAL');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    // for instant group
    /**
     * [total_member Used to get the total member count of an group]
     * @param  [int] $group_id   	[Group ID]
     * @param  [string] $type       [Managers OR Members]
     * @param  [string] $search_key [Serach keyword]
     * @return [int]             	[Member count]
     */
    function total_member($group_id, $type = "All", $search_key = "") {
        $this->db->select('GM.GroupID');
        $this->db->from(GROUPMEMBERS . " GM");
        $this->db->where("GM.StatusID", '2');
        $this->db->where("GM.GroupID", $group_id);
        $this->db->where('GM.ModuleEntityID!=', '0');
        $this->db->where('GM.ModuleID!=', '0');

        if ($type == 'Managers') {
            $this->db->where(" (GM.ModuleRoleID = '4' OR GM.ModuleRoleID = '5')", NULL, FALSE);
        } else if ($type == 'Members') {
            $this->db->where("GM.ModuleRoleID", '6');
        }

        $res = $this->db->get();
        //echo $this->db->last_query();
        return $res->num_rows();
    }

    /**
     * [get_informal_group_name Used to get informal group name]
     * @param  [int]  $group_id              [Group ID]
     * @param  [int]  $user_id               [Use ID]
     * @param  integer $is_guid               [Is guid flag]
     * @param  boolean $include_self          [Flag to include self or not]
     * @param  array   $member_id_not_include [Exclude given member id's array]
     * @return [type]                         [description]
     */
    function get_informal_group_name($group_id, $user_id, $is_guid = 0, $include_self = false, $member_id_not_include = array(), $members_details = array()) {
        if ($is_guid) {
            $group_id = get_detail_by_guid($group_id, 1, 'GroupID', 1);
        }

        if (empty($members_details)) {
            $members = $this->members($group_id, $user_id, FALSE, '', '', '', '', "Name", FALSE, $member_id_not_include);
            $members_count = $this->members($group_id, $user_id, TRUE, '', '', '', '', "Name", FALSE, $member_id_not_include);
        } else {
            $members = $members_details;
            $members_count = count($members);
        }

        $member_data = array();
        if ($members) {
            foreach ($members as $member) {
                if ($include_self) {
                    $member_data[] = $member;
                } else {
                    $member_guid_not_include = "";
                    if (!empty($member_guid_not_include)) {
                        if ($member_guid_not_include == $member['ModuleEntityGUID'] && $member['ModuleID'] == 3) {
                            $members_count = $members_count - 1;
                        } else {
                            $member_data[] = $member;
                        }
                    } else {
                        $member_data[] = $member;
                    }
                }
            }
        }

        $members = $member_data;
        $group_name = '';
        if ($members) {
            $i = 1;
            foreach ($members as $key => $val) {
                if ($members_count == 1) {
                    $group_name .= $val['FirstName'];
                }
                if ($members_count == 2) {
                    $group_name .= $val['FirstName'];
                    if ($i == 1) {
                        $group_name .= ' and ';
                    }
                }
                if ($members_count == 3) {
                    $group_name .= $val['FirstName'];
                    if ($i == 1) {
                        $group_name .= ', ';
                    }
                    if ($i == 2) {
                        $group_name .= ' and ';
                    }
                }
                if ($members_count > 3) {
                    $group_name .= $val['FirstName'];
                    if ($i < 3) {
                        $group_name .= ', ';
                    } else {
                        $group_name .= ' and ';
                    }
                }
                if ($i == 3) {
                    break;
                }
                $i++;
            }
            if ($members_count == 4) {
                $group_name .= ' 1 other';
            }
            if ($members_count > 4) {
                $group_name .= ($members_count - 3) . ' others';
            }
        }
        //echo $group_name;
        return $group_name;
    }

    /**
     * [get_users_groups Used to get user all group ids]
     * @param  [int] $user_id   [User ID]
     * @param  string $filter   [Filter group]
     * @return [array]          [Array of group id's]
     */
    function get_users_groups($user_id, $filter = '') {
        
        if($this->settings_model->isDisabled(1)) { // Return empty if group module is disabled            
            return [];
        }
        
        $group_ids=array();
        if (CACHE_ENABLE) {
            $group_cache_data = $this->cache->get('user_groups_'.$user_id);
            if(!empty($group_cache_data)) {
               $group_ids= $group_cache_data['All'];
            }
        }
        if (empty($group_ids)) {
            $get_group = get_data('GroupIDs', GROUPALLMEMBERS, array('UserID' => $user_id), '1', '');
            if ($get_group) {
                $group_ids = $get_group->GroupIDs;
            }
        }
        if ($filter && !empty($group_ids)) {
            $filter = ucfirst($filter);

            $this->db->select('GROUP_CONCAT(GM.GroupID) as GroupID', FALSE);
            $this->db->from(GROUPMEMBERS . ' GM');

            if ($filter == 'Manage') {
                $this->db->where("GM.StatusID", 2);
                $this->db->where("(ModuleRoleID = '4' OR ModuleRoleID = '5')", NULL, FALSE);
            } else if ($filter == 'Join') {
                $this->db->where("GM.StatusID", 2);
                $this->db->where("ModuleRoleID", 6);
            } else if ($filter == 'Invite') {
                $this->db->where("GM.StatusID", 1);
            } else if ($filter == 'All' || $filter == 'MyGroupAndJoined') {
                $this->db->where("GM.StatusID", 2);
            }

            $this->db->where("(CASE WHEN (GM.ModuleID=3) THEN GM.ModuleEntityID=" . $user_id . " ELSE GM.ModuleEntityID IN (" . $group_ids . ") END)", NULL, FALSE);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $group_ids = array();
                $group_row = $query->row_array();
                if (!empty($group_row['GroupID'])) {
                    $group_ids = explode(',', $group_row['GroupID']);
                }
            }
        } else if (!empty($group_ids)) {
            $group_ids = explode(",", $group_ids);
        }
        return $group_ids;
    }

    /**
     * [get_invited_groups Used to get user all invited group ids ]
     * @param  [int] $user_id   [User ID]
     * @param  string $filter   [Filter group]
     * @return [array]          [Array of group id's]
     */
    function get_invited_groups($user_id) {
        $group_ids = array();

        $this->db->select('GROUP_CONCAT(GM.GroupID) as GroupID', FALSE);
        $this->db->from(GROUPMEMBERS . ' GM');
        $this->db->where_in("GM.StatusID", array(1, 2));
        $this->db->where("GM.ModuleID=3 AND GM.ModuleEntityID=" . $user_id . "", NULL, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $group_row = $query->row_array();
            if (!empty($group_row['GroupID'])) {
                $group_ids = explode(',', $group_row['GroupID']);
            }
        }
        return $group_ids;
    }

    /**
     * [can_post_on_wall Used to Check If user have rights to post on group's wall]
     * @param  [int] $group_id    	[Group ID]
     * @param  [int] $user_id       [User id]
     * @return [bool]          		[true/false]
     */
    function can_post_on_wall($group_id, $module_entity_id, $module_id = 3) {
        if ($module_id == 1) {
            $this->db->where('GroupID', $group_id);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('StatusID', '2');
            $this->db->where('CanPostOnWall', '1');
            $query = $this->db->get(GROUPMEMBERS);
            if ($query->num_rows()) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            $member_data = $this->check_membership($group_id, $module_entity_id, TRUE);
            if (!empty($member_data) && isset($member_data['StatusID']) && $member_data['StatusID'] == 2 && $member_data['CanPostOnWall'] == 1) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * [check_membership Used to Check member already exist in a group or not]
     * @param  [int] 	$group_id    	[Group Id]
     * @param  [int] 	$user_id  		[User ID]
     * @return [bool] 	[true/False]       	
     */
    function check_membership($group_id, $user_id, $need_data = false) {
        if (!$user_id) {
            return false;
        }

        $this->db->select('GM.ModuleEntityID, GM.ModuleID, GM.CanPostOnWall, GM.CanCreateKnowledgeBase, GM.IsExpert, GM.CanComment, GM.StatusID, GM.ModuleRoleID', FALSE);
        $this->db->from(GROUPMEMBERS . ' GM');
        $this->db->where('GM.ModuleEntityID', $user_id);
        $this->db->where('GM.ModuleID', 3);
        if (!$need_data) {
            $this->db->where('GM.StatusID', 2);
        }
        $this->db->where('GM.GroupID', $group_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            if ($need_data) {
                return $query->row_array();
            }
            return true;
        }

        $this->db->select('GM.ModuleEntityID, GM.ModuleID, GM.CanPostOnWall, GM.CanCreateKnowledgeBase, GM.IsExpert,GM.CanComment, GM.StatusID, GM.ModuleRoleID', FALSE);
        $this->db->from(GROUPMEMBERS . ' GM');
        $this->db->where('GM.ModuleID', 1);
        if (!$need_data) {
            $this->db->where('GM.StatusID', 2);
        }
        $this->db->where('GM.GroupID', $group_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            $this->recursive_group_list_membership[] = $group_id;
            foreach ($result as $key => $value) {
                if (!in_array($value['ModuleEntityID'], $this->recursive_group_list_membership)) {
                    $this->recursive_group_list_membership[] = $value['ModuleEntityID'];
                    $return_flag = $this->check_membership($value['ModuleEntityID'], $user_id, $need_data);
                    if ($return_flag) {
                        return $value;
                        break;
                    }
                }
            }
        }
        return false;
    }

    /**
     * [check_group_creator to check user is createor of a group]	
     * @param [int] 		$user_id 		
     * @param [int] 		$module_entity_id 	[Group ID]
     */
    function check_group_creator($group_id, $user_id, $need_data = false) {
        $this->db->select('GM.ModuleEntityID, GM.CanPostOnWall, GM.StatusID, GM.ModuleRoleID', FALSE);
        $this->db->from(GROUPMEMBERS . ' GM');
        $this->db->where('GM.ModuleEntityID', $user_id);
        $this->db->where('GM.ModuleID', 3);
        $this->db->where('GM.StatusID', 2);
        $this->db->where('GM.GroupID', $group_id);
        $this->db->where('GM.ModuleRoleID', 4);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            if ($need_data) {
                return $query->row_array();
            }
            return true;
        }
        return false;
    }

    /**
     * [get_invited_by_user Used to get who is added this user for given group]
     * @param  [int] $group_id [Group ID]
     * @param  [int] $user_id  [User ID]
     * @return [int]           [User ID]
     */
    function get_invited_by_user($group_id, $user_id) {
        $this->db->select('AddedBy');
        $this->db->from(GROUPMEMBERS);
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->AddedBy;
        } else {
            return false;
        }
    }

    /**
     * [get_group_members_id_recursive Used to get recursively all the members of given group]
     * @param  [int]  $group_id   [Group ID]
     * @param  array   $members    [Member Array]
     * @param  array   $group_ids  [Group ID's as member]
     * @param  boolean $only_admin [Check only for admin]
     * @return [type]              [description]
     */
    function get_group_members_id_recursive($group_id, $members = array(), $group_ids = array(), $only_admin = FALSE, $exclude_deleted = false) {
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(GROUPMEMBERS);
        if ($only_admin) {
            $this->db->where_in('ModuleRoleID', array(4, 5));
        }
        $this->db->where('GroupID', $group_id);
        if ($exclude_deleted) {
            $this->db->where('StatusID', 2);
        }
        $query = $this->db->get();

        if ($query->num_rows()) {
            if ($query->num_rows()) {
                foreach ($query->result_array() as $member) {
                    if ($member['ModuleID'] == 1) {
                        if (in_array($member['ModuleEntityID'], $group_ids)) {
                            continue;
                        } else {
                            $group_ids[] = $member['ModuleEntityID'];
                            $members = $this->get_group_members_id_recursive($member['ModuleEntityID'], $members, $group_ids);
                        }
                    } else {
                        $members[] = $member['ModuleEntityID'];
                    }
                }
            }
        }
        return $members;
    }

    function update_user_group($group_id, $user_id, $Members, $status) {
        $update_user_list = array();
        $associate_group_list = array();
        $member_as_group = array();  // Group id array
        $subscribe_members = array();
        if (!empty($Members)) {
            foreach ($Members as $item) {
                if ($item['ModuleID'] == 3) {
                    $update_user_list[] = $item['ModuleEntityID'];
                    $subscribe_members[] = $item;
                } else if ($item['ModuleID'] == 1) {
                    $associate_group_list[] = $item['ModuleEntityID'];
                }
            }
            // Get all groups add as member of current group
            if ($group_id) {
                $this->get_group_as_member_recursive($member_as_group, array($group_id));
            }
            // Get all recursive members associate to recursive groups
            if (!empty($associate_group_list)) {
                $this->get_group_member_recursive($update_user_list, $associate_group_list);
            }
            // Add & Remove group from all users
            if (!empty($update_user_list)) {
                $update_user_list = array_unique($update_user_list);
                $member_as_group = array_unique($member_as_group);
                foreach ($update_user_list as $item_user_id) {
                    $GroupIDArray = array();
                    $this->db->select('GroupIDs');
                    $this->db->from(GROUPALLMEMBERS);
                    $this->db->where('UserID', $item_user_id);
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        $result = $query->row_array();
                        $GroupIDs = $result['GroupIDs'];

                        if (!empty($GroupIDs)) {
                            $GroupIDArray = explode(',', $GroupIDs);

                            if ($status == 'Join') {
                                if (!in_array($group_id, $GroupIDArray)) {
                                    $GroupIDArray[] = $group_id;
                                }
                                if ($user_id == $item_user_id && !empty($member_as_group)) {
                                    array_merge($GroupIDArray, $member_as_group);
                                }
                            } else if ($status == 'Remove') {
                                $this->get_groups_associate_user($item_user_id);
                            }
                        }
                        if (!empty($GroupIDArray) && $status == 'Join') {
                            $this->db->where('UserID', $item_user_id);
                            $this->db->update(GROUPALLMEMBERS, array('GroupIDs' => implode(',', $GroupIDArray)));
                        }
                    } else {
                        $GroupIDArray[] = $group_id;
                        if ($user_id == $item_user_id && !empty($member_as_group)) {
                            $GroupIDArray = array_merge($GroupIDArray, $member_as_group);
                        }
                        $this->db->insert(GROUPALLMEMBERS, array('UserID' => $item_user_id, 'GroupIDs' => implode(',', $GroupIDArray)));
                    }
                }
            }

            if (!empty($subscribe_members) && $status == 'Join') {
                // Subscribe for all notify group post
                $this->subscribe_notify_post($group_id, $subscribe_members);
            }
        } else if ($status == 'Remove') {
            $associate_group_list[] = $group_id;
            if (!empty($associate_group_list)) {
                $this->get_group_member_recursive($update_user_list, $associate_group_list);
                //get_groups_associate_user($user_id)
                if (!empty($update_user_list)) {
                    foreach ($update_user_list as $item_user_id) {
                        $this->get_groups_associate_user($item_user_id);
                    }
                }
            }
        }
    }

    /**
     * 
     * @param type $group_id
     * @param type $subscribe_members
     */
    function subscribe_notify_post($group_id, $subscribe_members) {
        $this->db->select('ActivityID');
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleID', 1);
        $this->db->where('ModuleEntityID', $group_id);
        $this->db->where('NotifyAll', 1);
        $this->db->where_in('StatusID', array(2, 19));
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $row) {
                $this->subscribe_model->addUpdate($subscribe_members, $row['ActivityID']);
                $entity_id = $row['ActivityID'];
                if (CACHE_ENABLE) {
                    $activity_cache = $this->cache->get('activity_' . $entity_id);
                    if (!empty($activity_cache)) {
                        foreach ($subscribe_members as $subscribe_member) {
                            $activity_cache['s'][] = array('ModuleID' => $subscribe_member['ModuleID'], 'ModuleEntityID' => $subscribe_member['ModuleEntityID']);
                        }
                        $this->cache->delete('activity_' . $entity_id);
                        $this->cache->save('activity_' . $entity_id, $activity_cache, CACHE_EXPIRATION);
                    }
                }
            }
        }
    }

    /**
     * 
     * @param type $update_user_list
     * @param type $associate_group_list
     */
    function get_group_member_recursive(&$update_user_list, $associate_group_list) {
        if (!empty($associate_group_list)) {
            $this->recursive_group_list = array_merge($this->recursive_group_list, $associate_group_list);
            $group_list = array();
            foreach ($associate_group_list as $item) {
                $this->db->select('ModuleID,ModuleEntityID');
                $this->db->from(GROUPMEMBERS);
                $this->db->where_not_in('StatusID', array(3, 14));
                $this->db->where('GroupID', $item);
                $this->db->where('ModuleEntityID IS NOT NULL', NULL, FALSE);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    foreach ($query->result_array() as $item) {
                        if ($item['ModuleID'] == 3) {
                            $update_user_list[] = $item['ModuleEntityID'];
                        } else if ($item['ModuleID'] == 1 && !in_array($item['ModuleEntityID'], $this->recursive_group_list)) {
                            $group_list[] = $item['ModuleEntityID'];
                        }
                    }
                }

                if (!empty($group_list)) {
                    $this->get_group_member_recursive($update_user_list, $group_list);
                }
            }
        }
    }

    /**
     * 
     * @param type $member_as_group
     * @param type $groups
     */
    function get_group_as_member_recursive(&$member_as_group, $groups) {
        $groups_array = array();
        $this->db->select('GM.GroupID');
        $this->db->from(GROUPMEMBERS . " GM");
        $this->db->where('GM.ModuleID', 1);
        $this->db->where_in('GM.ModuleEntityID', $groups);
        $this->db->where('GM.ModuleEntityID IS NOT NULL', NULL, FALSE);
        $this->db->where('GM.StatusID', '2');
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $item) {
                $member_as_group[] = $item['GroupID'];
                $groups_array[] = $item['GroupID'];
            }
        }
        if (!empty($groups_array)) {
            $this->get_group_as_member_recursive($member_as_group, $groups_array);
        }
    }

    /**
     * 
     * @param type $user_id
     */
    function get_groups_associate_user($user_id) {
        if ($user_id) {
            $this->db->select('GM.ModuleEntityID,GROUP_CONCAT(GM.GroupID) AS GroupIDs');
            $this->db->from(GROUPMEMBERS . " GM");
            $this->db->join(GROUPS . " G", "G.GroupID=GM.GroupID");
            $this->db->where('G.StatusID', 2);
            $this->db->where('GM.ModuleID', 3);
            $this->db->where('GM.StatusID', 2);
            $this->db->where('GM.ModuleEntityID', $user_id);
            $this->db->where('GM.ModuleEntityID IS NOT NULL', NULL, FALSE);

            $this->db->group_by('GM.ModuleEntityID');
            $this->db->order_by('GM.ModuleEntityID');
            $query = $this->db->get();

            $user_join_group = array();
            if ($query->num_rows()) {
                $item = $query->row_array();
                if (!empty($item['GroupIDs'])) {
                    $user_join_group[$user_id] = array('UserID' => $user_id, 'GroupIDs' => $item['GroupIDs']);

                    $this->get_all_groups_associate_user_recursive($user_join_group, $item['ModuleEntityID'], $item);
                }
                if (!empty($user_join_group)) {
                    $this->db->select(1);
                    $this->db->from(GROUPALLMEMBERS);
                    $this->db->where('UserID', $user_id);
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        $this->db->where('UserID', $user_id);
                        $this->db->update(GROUPALLMEMBERS, array('GroupIDs' => $user_join_group[$user_id]['GroupIDs']));
                    } else {
                        $this->db->insert(GROUPALLMEMBERS, $user_join_group[$user_id]);
                    }

                    foreach ($user_join_group as $item) {
                        if (CACHE_ENABLE) {
                            $user_id = $item['UserID'];
                            $this->cache->delete('user_groups_' . $user_id);
                            $file_data['All'] = $item['GroupIDs'];
                            $file_data['Manage'] = '';

                            $this->db->select('GROUP_CONCAT(GroupID) as GroupID', FALSE);
                            $this->db->from(GROUPMEMBERS);
                            $this->db->where("StatusID", 2);
                            $this->db->where("(ModuleRoleID = '4' OR ModuleRoleID = '5')", NULL, FALSE);
                            $this->db->where("(CASE WHEN (ModuleID=3) THEN ModuleEntityID=" . $user_id . " ELSE ModuleEntityID IN (" . $file_data['All'] . ") END)", NULL, FALSE);
                            $query = $this->db->get();
                            if ($query->num_rows()) {
                                $group_row = $query->row_array();
                                if (!empty($group_row['GroupID'])) {
                                    $file_data['Manage'] = $group_row['GroupID'];
                                }
                            }
                            $this->cache->save('user_groups_' . $user_id, $file_data, CACHE_EXPIRATION);
                        }
                    }
                } else {
                    $this->db->where('UserID', $user_id);
                    $this->db->delete(GROUPALLMEMBERS);
                }
            } else {
                $this->db->where('UserID', $user_id);
                $this->db->delete(GROUPALLMEMBERS);
            }
        }
    }

    /**
     * [get_all_groups_associate_user Used to get all the associated groups for each user]
     * @return [type] [description]
     */
    function get_all_groups_associate_user() {
        $this->db->select('GM.ModuleEntityID,GROUP_CONCAT(GM.GroupID) AS GroupIDs');
        $this->db->from(GROUPMEMBERS . " GM");
        $this->db->join(GROUPS . " G", "G.GroupID=GM.GroupID");
        $this->db->where('G.StatusID', 2);
        $this->db->where('GM.ModuleID', 3);
        $this->db->where('GM.StatusID', 2);
        $this->db->where('GM.ModuleEntityID IS NOT NULL', NULL, FALSE);
        $this->db->group_by('GM.ModuleEntityID');
        $this->db->order_by('GM.ModuleEntityID');
        $query = $this->db->get();
        $user_join_group = array();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $item) {
                if (!empty($item['GroupIDs'])) {
                    $user_join_group[$item['ModuleEntityID']] = array('UserID' => $item['ModuleEntityID'], 'GroupIDs' => $item['GroupIDs']);

                    $this->get_all_groups_associate_user_recursive($user_join_group, $item['ModuleEntityID'], $item);
                }
            }
            if (!empty($user_join_group)) {
                $this->db->empty_table(GROUPALLMEMBERS);
                $this->db->insert_batch(GROUPALLMEMBERS, $user_join_group);
                foreach ($user_join_group as $item) {
                    if (CACHE_ENABLE) {
                        $user_id = $item['UserID'];
                        $this->cache->delete('user_groups_' . $user_id);
                        $file_data['All'] = $item['GroupIDs'];
                        $file_data['Manage'] = '';
                        $this->db->select('GROUP_CONCAT(GroupID) as GroupID', FALSE);
                        $this->db->from(GROUPMEMBERS);
                        $this->db->where("StatusID", 2);
                        $this->db->where("(ModuleRoleID = '4' OR ModuleRoleID = '5')", NULL, FALSE);
                        $this->db->where("(CASE WHEN (ModuleID=3) THEN ModuleEntityID=" . $user_id . " ELSE ModuleEntityID IN (" . $file_data['All'] . ") END)", NULL, FALSE);
                        $query = $this->db->get();
                        if ($query->num_rows()) {
                            $group_row = $query->row_array();
                            if (!empty($group_row['GroupID'])) {
                                $file_data['Manage'] = $group_row['GroupID'];
                            }
                        }
                        $this->cache->save('user_groups_' . $user_id, $file_data, CACHE_EXPIRATION);
                    }
                }
            }
        }
    }

    /**
     * [get_all_groups_associate_user_recursive Used to recursively get all the associated groups for each user]
     * @param  [type] &$user_join_group [description]
     * @param  [type] $user_id          [description]
     * @param  [type] $item             [description]
     * @return [type]                   [description]
     */
    function get_all_groups_associate_user_recursive(&$user_join_group, $user_id, $item) {
        $this->db->select('GM.ModuleEntityID,GROUP_CONCAT(GM.GroupID) AS GroupIDs');
        $this->db->from(GROUPMEMBERS . " GM");
        $this->db->join(GROUPS . " G", "G.GroupID=GM.GroupID");
        $this->db->where('G.StatusID', 2);
        $this->db->where('GM.ModuleID', 1);
        $this->db->where('GM.StatusID', 2);
        $this->db->where_in('GM.ModuleEntityID', explode(',', $item['GroupIDs']));
        $this->db->where_not_in('GM.GroupID', explode(',', $user_join_group[$user_id]['GroupIDs']));
        $this->db->where('GM.GroupID NOT IN (SELECT ModuleEntityID FROM ' . BLOCKUSER . ' WHERE ModuleID=1 AND EntityID = ' . $user_id . ' )');
        $this->db->group_by('GM.ModuleEntityID');
        $query_group = $this->db->get();
        //echo "<br>".$this->db->last_query();
        if ($query_group->num_rows()) {
            foreach ($query_group->result_array() as $item_group) {
                if (!empty($item_group['GroupIDs'])) {
                    $user_join_group[$user_id] = array('UserID' => $user_id, 'GroupIDs' => $user_join_group[$user_id]['GroupIDs'] . ',' . $item_group['GroupIDs']);
                    $this->get_all_groups_associate_user_recursive($user_join_group, $user_id, $item_group);
                }
            }
        }
    }

    /**
     * [top_group Used to get the group list]
     * @param  [int] $user_id       	[User id]
     * @param  [int] $visited_user_id   [Visited User id]
     * @param  [boolean] $count_flag   	[count flag]
     * @param  [string] $search_keyword [search keyword]
     * @param  [string] $page_no    	[page number]
     * @param  [string] $page_size      [page size]
     * @param  [string] $filter         [Manage or Join group list]
     * @param  [string] $order_by       [Order by field]
     * @param  [string] $sort_by        [Sort by value ASC/DESC]
     * @param  [int] $privacy_type      [privacy type filter]
     * @return [array]                  [Group list]
     */
    function top_group($user_id, $visited_user_id, $count_flag = FALSE, $search_keyword = '', $page_no = '', $page_size = '', $filter = '', $order_by = "LastActivity", $sort_by = "DESC", $category_id = '', $privacy_type = -1, $search = 0) {
        if (CACHE_ENABLE && $user_id == $visited_user_id && $count_flag == FALSE) {
            $cache_data = $this->cache->get('my_group_' . $user_id);
            if (!empty($cache_data)) {
                return $cache_data;
            }
        }
        $current_user_group_ids = $this->get_users_groups($user_id);
        if ($user_id == $visited_user_id) {
            $common_group_ids = $current_user_group_ids;
            $common_group_ids[] = 0;
            $visited_user_group_ids[] = 0;
        } else {
            $visited_user_group_ids = $this->get_users_groups($visited_user_id);
            $visited_user_group_ids[] = 0;
            $common_group_ids = array_intersect($current_user_group_ids, $visited_user_group_ids);
            if (!empty($common_group_ids)) {
                $this->db->select('GROUP_CONCAT(GroupID) as GroupID');
                $this->db->from(GROUPS);
                $this->db->where_in('GroupID', $common_group_ids);
                $this->db->order_by('LastActivity', 'ASC');
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $common_group_ids = array();
                    $result = $query->row_array();
                    $common_group_ids = explode(',', $result['GroupID']);
                }
            }
        }

        $common_group_ids[] = 0;
        $results = array();
        if ($search) {
            $this->load->model('users/user_model');
            $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
            $friends[] = $user_id;
            if ($friends) {
                $this->db->select('(SELECT COUNT(ModuleEntityID) FROM ' . GROUPMEMBERS . ' WHERE GroupID=G.GroupID AND ModuleID=3 AND ModuleEntityID IN (' . implode(',', $friends) . ')) as FriendsCount', false);
            }
        }
        $select_array = array();
        $select_array[] = '(CASE G.Type 
                            WHEN "INFORMAL" THEN (
                            SELECT GROUP_CONCAT(CASE GMM.ModuleID WHEN 3 THEN CONCAT(US.FirstName," ",US.LastName) ELSE GG.GroupName END) 
                            FROM ' . GROUPMEMBERS . ' GMM 
                            LEFT JOIN ' . USERS . ' US ON `US`.`UserID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 3
                            LEFT JOIN ' . GROUPS . ' GG ON `GG`.`GroupID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 1
                            WHERE GMM.GroupID=G.GroupID 
                            GROUP BY GMM.GroupID
                            )   
                            ELSE G.GroupName END) AS GroupName';
        $select_array[] = 'G.Type, G.GroupGUID,CONCAT(U.FirstName," ",U.LastName) AS CreatedBy,U.UserGUID as CreatorGUID, P.Url as CreatedProfileUrl, G.LastActivity, G.GroupID, G.GroupDescription, GM.ModuleRoleID, G.CreatedDate, if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture, G.IsPublic, if(G.IsPublic=0,1,if(G.IsPublic=1,0,2)) as GroupOrder, G.MemberCount';

        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' GM', 'G.GroupID = GM.GroupID AND GM.StatusID NOT IN (14)');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'inner');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID = U.UserID AND P.EntityType="User"', 'left');

        if (!empty($category_id)) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID', 'left');
            $this->db->where('EC.ModuleID', 1);
            $this->db->where('EC.CategoryID', $category_id);
        }
        if ($privacy_type != '-1') {
            $this->db->where('G.IsPublic', $privacy_type);
        }

        if (!empty($search_keyword)) {
            $search_keyword = $this->db->escape_like_str($search_keyword); 
            $this->db->having("GroupName LIKE '%" . $search_keyword . "%' OR G.GroupDescription LIKE '%" . $search_keyword . "%'", NULL, FALSE);
            $select_array[] = "IF(GroupName LIKE '%" . $this->db->escape_like_str($search_keyword) . "%',1,2) as OrdBy";
        }
        $this->db->where("(G.GroupID IN (" . implode(',', $common_group_ids) . ") AND G.IsPublic in(0,1,2)) OR (G.GroupID IN (" . implode(',', $visited_user_group_ids) . ") AND G.IsPublic in(0,1)) ", NULL, FALSE);
        $this->db->where("G.StatusID", 2);
        //$this->db->where_not_in("GM.StatusID", 14);        

        if ($search) {
            $this->db->order_by("OrdBy", 'ASC');
            $this->db->order_by('FriendsCount', 'DESC');
            $this->db->order_by('G.MemberCount', 'DESC');
        }

        // Added Sorting by type and order
        if (!empty($order_by) && !empty($sort_by)) {
            if ($order_by == 'IsPublic') {
                $this->db->_protect_identifiers = FALSE;
                $this->db->order_by('FIELD(IsPublic,1,0,2)');
                $this->db->_protect_identifiers = TRUE;
            } elseif ($order_by == 'Popularity') {
                $this->db->_protect_identifiers = FALSE;
                $this->db->order_by("FIELD(Popularity, 'High','Moderate','Low')");
                $this->db->_protect_identifiers = TRUE;
            } else {
                if ($user_id == $visited_user_id) {
                    $this->db->order_by($order_by, $sort_by);
                } else {
                    $this->db->order_by(" FIELD(G.GroupID, " . implode(',', $common_group_ids) . " ) DESC", NULL);
                    $this->db->order_by($order_by, $sort_by);
                }
            }
        }


        if (empty($count_flag)) { // check if array needed
            if (!empty($page_size)) { // Check for pagination
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }
            $this->db->group_by('G.GroupID');
            $this->db->select(implode(',', $select_array), false);

            $query = $this->db->get();
            $result = $query->result_array();
            foreach ($result as $key => $val) {
                $result[$key]['Category'] = array('Name' => '', 'CategoryID' => 0, 'SubCategory' => array());
                $result[$key]['EntityMembers'] = array();

                $result[$key]['MemberCount'] = $val['MemberCount'];
                $permission = check_group_permissions($user_id, $val['GroupID']);
                if (isset($permission['Details'])) {
                    $group_details = $permission['Details'];
                    if (isset($group_details['Category'])) {
                        $result[$key]['Category'] = $group_details['Category'];
                    }
                    if ($val['Type'] == 'INFORMAL' && isset($group_details['Members'])) {
                        $result[$key]['EntityMembers'] = $group_details['Members'];

                        $result[$key]['GroupName'] = $this->get_informal_group_name($val['GroupID'], $user_id, 0, false, array(), $group_details['Members']);
                    }
                    unset($permission['Details']);
                }
                
                
                // Get profile Url
                $group_url_details = $this->get_group_details_by_id($val['GroupID'], '', $result[$key]);
                $result[$key]['ProfileURL'] = $this->get_group_url($val['GroupID'], $group_url_details['GroupNameTitle'], false, 'index');

                $result[$key]['Permission'] = $permission;

                unset($val['OrdBy']);
            }
            if (CACHE_ENABLE && $user_id == $visited_user_id) {
                $this->cache->save('my_group_' . $user_id, $result, 600);
            }
            return $result;
        } else {
            $this->db->select('COUNT( DISTINCT G.GroupID) as TotalRow ');
            $result = $this->db->get();
            $count_data = $result->row_array();
            return $count_data['TotalRow'];
        }
    }

    /**
     * [category_group Used to get the group list of given category]
     * @param  [int] $user_id           [User id]
     * @param  [boolean] $count_flag    [count flag]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @param  [int] $privacy_type      [privacy type filter]
     * @param  [array/int] $category_id       [Category ID]
     * @return [array]                  [Group list]
     */
    function category_group($user_id, $count_flag = FALSE, $page_no = '', $page_size = '', $category_id = '', $privacy_type = -1, $with_detail=FALSE,$order_by='',$order='',$show_all=false, $is_site_map=FALSE) {
        $current_user_group_ids = $this->get_users_groups($user_id);
        $current_user_group_ids[] = 0;

        $invited_groups = $this->get_invited_groups($user_id);

        if (!empty($invited_groups))
            $current_user_group_ids = array_unique(array_merge($current_user_group_ids, $invited_groups));


        $results = array();

        $blocked_group_list = $this->get_blocked_group_list($user_id);

        $this->load->model('users/user_model');
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $friends[] = $user_id;
        if ($friends) {
            $this->db->select('(SELECT COUNT(ModuleEntityID) FROM ' . GROUPMEMBERS . ' WHERE GroupID=G.GroupID AND ModuleID=3 AND ModuleEntityID IN (' . implode(',', $friends) . ')) as FriendsCount', false);
        }

        $select_array = array();
        $select_array[] = '(CASE G.Type 
                            WHEN "INFORMAL" THEN (
                            SELECT GROUP_CONCAT(CASE GMM.ModuleID WHEN 3 THEN CONCAT(US.FirstName," ",US.LastName) ELSE GG.GroupName END) 
                            FROM ' . GROUPMEMBERS . ' GMM 
                            LEFT JOIN ' . USERS . ' US ON `US`.`UserID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 3
                            LEFT JOIN ' . GROUPS . ' GG ON `GG`.`GroupID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 1 
                            WHERE GMM.GroupID=G.GroupID 
                            GROUP BY GMM.GroupID
                            )   
                            ELSE G.GroupName END) AS GroupName';
        $select_array[] = 'G.Type, G.GroupGUID,CONCAT(U.FirstName," ",U.LastName) AS CreatedBy,U.UserGUID as CreatorGUID,  G.LastActivity, G.GroupID, G.GroupDescription,if(LENGTH(G.GroupDescription) > 70 ,CONCAT(SUBSTR(G.GroupDescription,1,70) ,"","..."),G.GroupDescription) as ShortGroupDescription, G.CreatedDate, if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture, G.IsPublic, if(G.IsPublic=0,1,if(G.IsPublic=1,0,2)) as GroupOrder, G.MemberCount, G.Popularity as ActivityLevel';

        $this->db->from(GROUPS . ' G');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID AND U.StatusID NOT IN (3,4)', 'inner');


        if (!empty($category_id)) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID', 'left');
            $this->db->where('EC.ModuleID', 1);
            if (is_array($category_id)) {
                $this->db->where_in('EC.CategoryID', $category_id);
            } else {
                $this->db->where('EC.CategoryID', $category_id);
            }
        }
        if ($privacy_type != '-1') {
            $this->db->where('G.IsPublic', $privacy_type);
        }

        $this->db->where("IF(G.IsPublic=2,G.GroupID IN(" . implode(',', $current_user_group_ids) . "),TRUE)", null, false);

        if ($blocked_group_list) {
            $this->db->where_not_in('G.GroupID', $blocked_group_list);
        }

        $this->db->where("G.StatusID", 2);
        //$this->db->where_not_in("U.StatusID", array(3,4));

        if($order_by && $order)
        {
            $this->db->order_by($order_by,$order);
        }

        if($order_by != 'FriendsCount')
        {
            $this->db->order_by('FriendsCount', 'DESC');
        }
        $this->db->order_by('G.MemberCount', 'DESC');
        $this->db->order_by(" FIELD(G.GroupID, " . implode(',', $current_user_group_ids) . " ) DESC", NULL);

        if (empty($count_flag)) { // check if array needed
            //$this->db->where('G.Type', 'FORMAL');
            if (!empty($page_size)) { // Check for pagination            
                if(!$show_all) {
                    $offset = $this->get_pagination_offset($page_no, $page_size);
                    $this->db->limit($page_size, $offset);
                }
            }
            $this->db->group_by('G.GroupID');
            $this->db->select(implode(',', $select_array), false);
            $query = $this->db->get();
            // echo $this->db->last_query();die;
            $result = $query->result_array();
            if ($with_detail) {
                foreach ($result as $key => $val) {
                    if(!$is_site_map) {
                        $result[$key]['Category'] = $this->get_group_categories($val['GroupID']);
                        $membership = $this->check_membership($val['GroupID'], $user_id, TRUE);
                        $permissions['IsActiveMember'] = FALSE;
                        $permissions['IsInvited'] = FALSE; //User recieve JOIN request 
                        $permissions['IsInviteSent'] = FALSE; //User sent request to join group 
                        $permissions['DirectGroupMember'] = FALSE;
                        if (!empty($membership)) {
                            if ($membership['StatusID'] == '2') {
                                $permissions['IsActiveMember'] = TRUE;
                            }

                            if ($membership['StatusID'] == 1) { // check if member is invited
                                $permissions['IsInvited'] = $membership['StatusID'];
                            }
                            if ($membership['StatusID'] == 18) { // check if member request to join this group
                                $permissions['IsInviteSent'] = TRUE;
                            }

                            if ($membership['ModuleID'] == 3 && $user_id == $membership['ModuleEntityID']) {
                                $permissions['DirectGroupMember'] = TRUE;
                            }
                        }

                        $result[$key]['Permission'] = $permissions;
                        $result[$key]['FeaturedPost'] = $this->activity_model->get_featured_post($user_id,1,$result[$key]['GroupID'],1,1);
                        $MemberData=$this->group_recent_post_owner($val['GroupID'],$user_id);
                        $result[$key]['MembersList'] = $MemberData['Data'];
                        $result[$key]['TotalMembersList'] = $MemberData['TotalRecords'];
                        $result[$key]['DiscussionCount'] = $this->get_discussion_count($val['GroupID']);
                        $result[$key]['MemberCount'] = $this->members($val['GroupID'], $user_id, TRUE, '', '', '', '', 'Name', TRUE,'','');
                        if ($friends && $val['Type'] == 'FORMAL') {
                            $result[$key]['Members'] = $this->get_group_members_details($val['GroupID'], 1, 4, $friends);
                        }
                        $result[$key]['PopularDiscussion'] = $this->activity_model->get_popuplar_activity_of_module(1, $val['GroupGUID'], 7);

                        unset($result[$key]['FriendsCount']);
                    }                    
                    
                    $group_url_details = $this->get_group_details_by_id($val['GroupID'], '', $val);
                    $result[$key]['ProfileURL'] = $this->get_group_url($val['GroupID'], $group_url_details['GroupNameTitle'], true, 'index');                    
                }
            }
            return $result;
        } else {
            $this->db->select('COUNT( DISTINCT G.GroupID) as TotalRow ');
            $result = $this->db->get();
            $count_data = $result->row_array();
            return $count_data['TotalRow'];
            //return $this->db->get()->num_rows();
        }
    }

    /**
     * [get_category_groupids Used to get list of group ids using category/sub-category id]
     * @param   [int] $category_id       [Category ID]
     * @param   [int]    $user_id           [User id]
     * @return  [array]  $category_ids      [array of group id]
     */
    public function get_category_groups($category_ids, $is_follow = TRUE, $user_id = '') {
        $result = array();
        if (!empty($category_ids)) {
            $this->db->select('Groups.GroupID, Groups.GroupGUID');
            if ($is_follow) {
                // get not deleted and public group ids
                $this->db->where('IsPublic', '1');
            } else {
                // get not deleted join group ids as member
                $this->db->join(GROUPMEMBERS, 'Groups.GroupID = GroupMembers.GroupID', 'left');
                $this->db->where('GroupMembers.ModuleEntityID', $user_id);
                $this->db->where('GroupMembers.ModuleRoleID', 6);
            }
            $this->db->where('Groups.StatusID !=', 3);
            $this->db->where('EntityCategory.ModuleID', 1);
            $this->db->where_in('EntityCategory.CategoryID', $category_ids);
            $this->db->from(GROUPS);
            $this->db->join(ENTITYCATEGORY, 'Groups.GroupID = EntityCategory.ModuleEntityID', 'left');
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $result = $query->result_array();
            }
        }
        return $result;
    }

    /**
     * [check_existing_group Used to Check If Group Name is already Exists]
     * @param   [String] $group_name        [Group Name]
     * @param   [int] $user_id              [User id]
     * @param   [array]$category_ids        [array of category id]  
     * @param   [int]$group_guid            [GroupGUID (in case of edit group)] 
     * @return  [bool]                      [true/false]
     */
    function check_existing_group($group_name, $user_id, $category_ids = '', $group_guid = '') {
        if (empty($group_name)) {
            return TRUE;
        }
        $this->db->select('GroupID');
        $this->db->where('GroupName', $group_name);
        $this->db->where('CreatedBy', $user_id);
        $this->db->where('StatusID !=', 3);

        if ($group_guid)
            $this->db->where('GroupGUID !=', $group_guid);

        $checkquery = $this->db->get(GROUPS);

        if ($checkquery->num_rows() > 0) {
            return false;
        } else {
            if (!empty($category_ids)) {
                $this->db->select('Groups.GroupID');
                $this->db->where('Groups.GroupName', $group_name);
                $this->db->where('EntityCategory.ModuleID', 1);
                $this->db->where_in('EntityCategory.CategoryID', $category_ids);

                if ($group_guid)
                    $this->db->where('Groups.GroupGUID !=', $group_guid);

                $this->db->from(GROUPS);
                $this->db->join(ENTITYCATEGORY, 'Groups.GroupID = EntityCategory.ModuleEntityID', 'left');
                $query = $this->db->get();
                if ($query->num_rows() > 0)
                    return false;
                else
                    return true;
            }
            else {
                return true;
            }
        }
    }

    /**
     * [accept_invite aceept group join invitation]
     * @param  [int] $group_guid  [group guid]
     * @param  [int] $user_guid [user guid]
     * @return [type]          [description]
     */
    function accept_invite($group_guid, $user_guid, $current_user) {
        $user_id = get_detail_by_guid($user_guid, 3);
        $group_id = get_detail_by_guid($group_guid, 1);

        $this->db->set('StatusID', '2');
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('ModuleID', '3');
        $this->db->update(GROUPMEMBERS);

        $this->load->model('activity/activity_model');
        $this->activity_model->addActivity(1, $group_id, 4, $user_id);
        #update member count            
        $this->db->where('GroupID', $group_id);
        $this->db->set('MemberCount', 'MemberCount+1', FALSE);
        $this->db->update(GROUPS);

        $parameters[0]['ReferenceID'] = $current_user;
        $parameters[0]['Type'] = 'User';
        $parameters[1]['ReferenceID'] = $group_id;
        $parameters[1]['Type'] = 'Group';

        $invited_by_user = $this->get_invited_by_user($group_id, $user_id);

        $this->notification_model->add_notification(23, $current_user, array($user_id), $group_id, $parameters);

        /* Initiate job for group assignment */
        initiate_worker_job('update_user_group', array('ENVIRONMENT' => ENVIRONMENT, 'GroupID' => $group_id, 'Members' => array(array('ModuleEntityID' => $user_id, 'ModuleID' => 3)), 'Status' => 'Join'));
    }

    /**
     * [reject_invite deny group join invitation]
     * @param  [int] $group_guid  [group guid]
     * @param  [int] $user_guid [user guid]
     * @return [type]          [description]
     */
    function reject_invite($group_guid, $user_guid) {
        $user_id = get_detail_by_guid($user_guid, 3);
        $group_id = get_detail_by_guid($group_guid, 1);

        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('ModuleID', 3);
        $this->db->delete(GROUPMEMBERS);
    }

    /**
     * [get_user_group_role Get user's role in particular group]
     * @param  [int] $user_id  [user id]
     * @param  [int] $group_id [group id]
     * @return [type]          [description]
     */
    function get_user_group_role($module_entity_id, $module_id, $group_id) {
        $this->db->select('ModuleRoleID');
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('ModuleID', $module_id);
        $sql = $this->db->get(GROUPMEMBERS);
        if ($sql->num_rows() > 0) {
            return $sql->row()->ModuleRoleID;
        } else {
            return false;
        }
    }

    /**
     * [toggle_can_post_on_wall to change user's group wall post permission]
     * @param  [int]    $group_id       [Group Id]
     * @param  [int]    $module_entity_id       [module entity id]
     * @param  [int]    $module_id          [module id]
     * @param [int]     $can_post_on_wall       [0/1]       
     */
    function toggle_can_post_on_wall($group_id, $module_entity_id, $module_id, $can_post_on_wall) {
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('ModuleID', $module_id);
        $this->db->update(GROUPMEMBERS, array('CanPostOnWall' => $can_post_on_wall));
    }

    /**
     * [get_group_members_count Count  number of members in a group]
     * @param  [int]        $GroupID            [Group Id]  
     * @param [sring]       $SearchText         [Search Keyword (member name)]  
     * @return [int]        No of users in a group  
     */
    function get_group_members_count($input, $user_id = 0) {
        $results = array();
        if ($input['GroupID']) {
            $this->db->where("GroupMembers.GroupID", $input['GroupID']);
        }

        if ($input['SearchText']) {
            $this->db->where(" (Users.FirstName like'" . $this->db->escape_like_str($input['SearchText']) . "%' or  Users.LastName like '" . $this->db->escape_like_str($input['SearchText']) . "%' or concat(Users.FirstName,' ',Users.LastName) like '" . $this->db->escape_like_str($input['SearchText']) . "%')", NULL, FALSE);
        }

        if ($input['Type'] == 'Managers') {
            $this->db->where(" (GroupMembers.ModuleRoleID = '4' OR GroupMembers.ModuleRoleID = '5')", NULL, FALSE);
        }

        if ($input['Type'] == 'Members') {
            $this->db->where("GroupMembers.ModuleRoleID", '6');
        }

        $this->db->select('Groups.GroupGUID');
        $this->db->select('p.Url as ProfileLink');
        $this->db->from(GROUPS);
        $this->db->join(GROUPMEMBERS, 'Groups.GroupID = GroupMembers.GroupID', 'left');
        $this->db->join(USERS, 'GroupMembers.UserID = Users.UserID', 'inner');
        $this->db->join(USERDETAILS . ' ud', 'ud.UserID = Users.UserID');
        $this->db->join(PROFILEURL . " as p", "p.EntityID = Users.UserID and p.EntityType = 'User'", "LEFT");
        $res = $this->db->get();
        return $res->num_rows();
    }

    /**
     * [Used to Check member already exist in a group or not]
     * @param  [int]    $group_id       [Group Id]
     * @return [bool]   [true/False]        
     */
    function check_public_group($group_id) {
        $this->db->where('GroupID', $group_id);
        $this->db->where('IsPublic', '1');
        $result = $this->db->get(GROUPS);
        if ($result->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [action_request Used to accept/deny group invite request]
     * @param  [int] 	$GroupID    	[Group Id]
     * @param  [int] 	$user_id  		[User ID]
     * @param [int] 	$StatusID       [1=accepted,13=denied]	
     */
    function is_member($user_id, $group_id) {
        $this->db->select('*');
        $this->db->from(GROUPALLMEMBERS);
        $this->db->where("FIND_IN_SET('$group_id',GroupIDs) !=", 0);
        $this->db->where('UserID', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * [friend_count Used to get frien count in group membes]
     * @param  [int] $group_id [Group ID]
     * @param  [int] $user_id  [User ID]
     * @return [int]           [Friend Count]
     */
    function friend_count($group_id, $user_id) {
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $friends[] = 0;
        $this->db->select('UserID');
        $this->db->from(GROUPALLMEMBERS);
        $this->db->where("FIND_IN_SET('$group_id',GroupIDs) >0 ", null);
        $this->db->where_in('UserID', $friends);
        return $this->db->get()->num_rows();
    }

    /**
     * [calculate_group_popularity Used to calculate group popularity]
     * @param  [int] $group_id [Group ID]
     */
    function calculate_group_popularity($group_id) {
        $popularity = 'Low';

        $this->db->select('COUNT(ID) as ActivityCount', false);
        $this->db->from(USERSACTIVITYLOG);
        $this->db->where('ModuleID', '1');
        $this->db->where('ModuleEntityID', $group_id);
        $this->db->where("ActivityDate BETWEEN '" . get_current_date('%Y-%m-%d', 7) . "' AND '" . get_current_date('%Y-%m-%d') . "'", null, false);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $activity_count = $query->row()->ActivityCount;
            if ($activity_count >= GROUP_POPULARITY_ACTIVITY) {
                $popularity = 'High';
            }
        }

        $this->db->set('Popularity', $popularity);
        $this->db->where('GroupID', $group_id);
        $this->db->update(GROUPS);
    }

    /**
     * [get_group_member_status Used to get group member status]
     * @param  [int]    $group_id       
     * @param  [int]    $user_id  
     * @return [int]    StatusID                
     */
    function get_group_member_status($group_id, $user_id) {
        $this->db->select('StatusID');
        $this->db->where('GroupID', $group_id);
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('ModuleID', 3);
        $sql = $this->db->get(GROUPMEMBERS);
        if ($sql->num_rows() > 0) {
            return $sql->row()->StatusID;
        } else {
            return false;
        }
    }

    /**
     * [update_member_to_group Used to updae member status in group]
     * @param  [int]    $GroupID        [Group Id]
     * @param  [int]    $user_id        [User Id]
     * @param [int]     $StatusID       [pending/accepted]
     * @param [string]  $Params         
     */
    function update_member_to_group($group_id, $user_id, $status_id, $params, $added_as, $added_by) {
        $add_member = array();
        $add_member['JoinedAt'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $add_member['ModuleRoleID'] = 6;
        $add_member['StatusID'] = $status_id;
        $add_member['CanPostOnWall'] = 1;
        $add_member['AddedAs'] = $added_as;
        $add_member['AddedBy'] = $added_by;

        $this->db->where(array('GroupID' => $group_id, 'UserID' => $user_id));
        $this->db->update(GROUPMEMBERS, $add_member);

        if ($status_id == 2) {
            $this->activity_model->addActivity(1, $group_id, 4, $user_id);

            #update member count    
            $this->db->where('GroupID', $group_id);
            $this->db->set('MemberCount', 'MemberCount+1', FALSE);
            $this->db->update(GROUPS);

            notify_node('liveFeed', array('Type' => 'GJ', 'UserID' => $user_id, 'EntityGUID' => get_detail_by_id($group_id, 1, 'GroupGUID', 1)));
        }
    }

    /**
     * [add_member_to_group Used to Add new member in group]
     * @param  [int]    $group_id       [Group Id]
     * @param  [int]    $user_id        [User Id]
     * @param [int]     $status_id      [pending/accepted]
     * @param [string]  $params         
     */
    function add_member_to_group($group_id, $user_id, $status_id, $params, $added_as, $added_by) {
        $add_member = array();
        $add_member['GroupID'] = $group_id;
        $add_member['ModuleID'] = '3';
        $add_member['ModuleEntityID'] = $user_id;
        $add_member['JoinedAt'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $add_member['ModuleRoleID'] = 6;
        $add_member['StatusID'] = $status_id;
        $add_member['Params'] = $params;
        $add_member['AddedAs'] = $added_as;
        $add_member['AddedBy'] = $added_by;

        $query = $this->db->get_where(GROUPMEMBERS, array('GroupID' => $group_id, 'ModuleID' => '3', 'ModuleEntityID' => $user_id));
        if ($query->num_rows()) {
            $this->db->set('StatusID', $status_id);
            $this->db->set('JoinedAt', $add_member['JoinedAt']);
            $this->db->where('GroupID', $group_id);
            $this->db->where('ModuleEntityID', $user_id);
            $this->db->update(GROUPMEMBERS);
        } else {
            $this->db->insert(GROUPMEMBERS, $add_member);
        }

        if ($status_id == 2) {
            $this->load->model('activity/activity_model');
            $this->activity_model->addActivity(1, $group_id, 4, $user_id);
            #update member count            
            $this->db->where('GroupID', $group_id);
            $this->db->set('MemberCount', 'MemberCount+1', FALSE);
            $this->db->update(GROUPS);
        }

        $type = 'GJ';
        if ($added_as == '3') {
            $type = 'GA';
        }
        notify_node('liveFeed', array('Type' => $type, 'UserID' => $user_id, 'EntityGUID' => get_detail_by_id($group_id, 1, 'GroupGUID', 1)));

        $this->subscribe_model->subscribe_email($user_id, $group_id, 'join_group');
    }

    /**
     * [discover_list   Used to get list of popular post, popular group, popular member(priority friend) based on category]
     * @param  [int]    $module_id          [Module ID]
     * @param [int]     $search_keyword     [Search Keyword]  
     * @param [int]     $filter             [Filter]  
     * @param [int]     $order_by           [Order By]  
     * @param [int]     $sort_by            [Sort By]  
     * @param [int]     $page_no            [PageNo]  
     * @param [int]     $page_size          [PageSize]  
     */
    function discover_list($module_id, $search_keyword = '', $filter = '', $order_by = 'CategoryID', $sort_by = 'DESC', $page_no = PAGE_NO, $page_size = PAGE_SIZE, $parent_id = 0, $user_id = 0) {
        $data = array();

        $condition = array('C.ModuleID' => $module_id); // If only root category needed

        $condition['C.ParentID'] = $parent_id; // If specific level of category needed

        $this->db->select('(CASE C.ParentID 
                            WHEN 0 THEN (
                            SELECT GROUP_CONCAT(SC.CategoryID) 
                            FROM ' . CATEGORYMASTER . ' SC 
                            WHERE SC.ParentID=C.CategoryID 
                            )   
                            ELSE 0 END) AS SubCategoryExist');

        $this->db->select('C.CategoryID,C.ParentID, if((select F.FollowID from ' . FOLLOW . ' F where F.TypeEntityID = C.CategoryID AND F.Type="category" AND F.UserID = ' . $user_id . ' limit 1)!="",1,0) as IsFollowing');
        $this->db->select('C.Name', FALSE);
        $this->db->select('C.Description', FALSE);
        $this->db->select('C.Icon', FALSE);
        $this->db->select('IF(MD.ImageName is NULL,"",MD.ImageName) as ImageName', FALSE);
        $this->db->select('MD.MediaGUID AS MediaGUID', FALSE);

        $this->db->from(CATEGORYMASTER . "  C");
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');

        $this->db->where($condition);
        $this->db->where('C.StatusID', 2);
        $this->db->order_by('C.Name', 'ASC');

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $result = $query->result_array();
            foreach ($result as $key => $val) {
                $val['GroupDetail'] = $val['MemberDetails'] = array();
                $sub_category = array();
                if (!empty($val['SubCategoryExist'])) {
                    $sub_category = explode(',', $val['SubCategoryExist']);
                    $val['SubCategoryExist'] = 1;
                } else {
                    $val['SubCategoryExist'] = 0;
                }
                $sub_category[] = $val['CategoryID'];
                $val['GroupCount'] = $this->category_group($user_id, TRUE, '', '', $sub_category);

                if (!empty($val['GroupCount'])) {
                    $val['GroupDetail'] = $this->category_group($user_id,  FALSE, 1, 2, $sub_category,-1, TRUE,'LastActivity','DESC');
                }
                else {   
                    if(empty($val['ParentID']))
                    continue;
                }

                // get overall post count from all category groups 
                $val['DiscussionCount'] = $this->category_activities($val['CategoryID'], $module_id, TRUE, '', '', '', '');
                if (!empty($val['DiscussionCount'])) {
                    //$val['DiscussionDetail'] = $this->category_activities($val['CategoryID'], $module_id, FALSE, 1, 2,'Popular', 'DESC');
                }
                // get overall post count from all category groups 
                $val['MemberCount'] = $this->unique_group_member_detail($val['CategoryID'] , $module_id, TRUE, '', '', '', '',$user_id);
                if (!empty($val['MemberCount']))
                {
                   $val['MemberDetails'] = $this->unique_group_member_detail($val['CategoryID'], $module_id, FALSE, 1, 4,'Popular', 'DESC',$user_id);
                }
                    /*permissions array*/
                foreach ($val['GroupDetail'] as $key => $group) {

                    $membership = $this->check_membership($group['GroupID'], $user_id, TRUE);
                    $permissions['IsActiveMember']  = FALSE;
                    $permissions['IsInvited']       = FALSE; //User recieve JOIN request 
                    $permissions['IsInviteSent']    = FALSE; //User sent request to join group 
                    $permissions['DirectGroupMember'] = FALSE;
                    if (!empty($membership))
                    {
                        if($membership['StatusID']=='2') 
                        {
                            $permissions['IsActiveMember']  = TRUE;
                        }

                        if ($membership['StatusID'] == 1) // check if member is invited
                        {
                            $permissions['IsInvited'] = $membership['StatusID'];
                        }
                        
                        if ($membership['StatusID'] == 18) // check if member request to join this group
                        {
                            $permissions['IsInviteSent'] = TRUE;
                        }
                        
                        if ($membership['ModuleID'] == 3 && $user_id == $membership['ModuleEntityID']) 
                        {
                            $permissions['DirectGroupMember'] = TRUE;
                        }                       
                    }
                    $val['GroupDetail'][$key]['Permission'] = $permissions;
                    /*permissions array*/
                    $val['GroupDetail'][$key]['FeaturedPost'] = $this->activity_model->get_featured_post($user_id,1,$val['GroupDetail'][$key]['GroupID'],1,1);
                    $MemberData = $this->group_recent_post_owner($val['GroupDetail'][$key]['GroupID'],$user_id);
                    $val['GroupDetail'][$key]['MembersList'] = $MemberData['Data'];
                    $val['GroupDetail'][$key]['DiscussionCount'] = $this->get_discussion_count($val['GroupDetail'][$key]['GroupID']);
                    
                    $group_url_details = $this->get_group_details_by_id($val['GroupDetail'][$key]['GroupID'], '', array(
                        'GroupName' => $val['GroupDetail'][$key]['GroupName'],
                        'GroupGUID' => $val['GroupDetail'][$key]['GroupGUID'],
                    ));
                    $val['GroupDetail'][$key]['ProfileURL'] = $this->get_group_url($val['GroupDetail'][$key]['GroupID'], $group_url_details['GroupNameTitle'], true, 'index');  
                    
                    $val['GroupDetail'][$key]['DiscussionCount'] = $this->get_discussion_count($val['GroupDetail'][$key]['GroupID']);
                    
                    //$val['GroupDetail'][$key]['MembersCount'] = $MemberData['TotalRecords'];                    
                }
                 $data[] = $val;
            }
        }
        return $data;
    }

    /**
     * [category_activities   Used to get list of groups]
     * @param [int]       $category_id        [Category ID]
     * @param [int]       $module_id          [Module ID]
     * @param [int]       $count_flag         [Count Flag]  
     * @param [int]       $order_by           [Order By]  
     * @param [int]       $sort_by            [Sort By]  
     * @param [int]       $page_no            [PageNo]  
     * @param [int]       $page_size          [PageSize]  
     */
    function category_activities($category_id = '', $module_id, $count_flag = FALSE, $page_no, $page_size, $order_by = "", $sort_by = "") {
        $results = array();
        $this->db->from(ACTIVITY . ' A');
        $this->db->where("A.StatusID", 2);
        $this->db->where("A.ModuleID", $module_id);
        if ($module_id == 1) {
            $this->db->where("A.ActivityTypeID!='4'", null, false);
        }
        if (!empty($category_id)) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=A.ModuleEntityID');
            $this->db->where('EC.ModuleID', $module_id);
            $this->db->where('EC.CategoryID', $category_id);
        }

        if (empty($count_flag)) { // check if array needed
            if (!empty($page_size)) { // Check for pagination
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }
            // Added Sorting by type and order
            if (!empty($order_by) && !empty($sort_by)) {
                if ($order_by == 'Popular') {
                    $this->db->order_by('TotalCount', $sort_by);
                }
            }

            $this->db->group_by('ActivityID');
            $this->db->select('ActivityGUID, ActivityID, SUM(NoOfLikes+NoOfDislikes+NoOfComments+NoOfViews) as TotalCount', false);
            $query = $this->db->get();
            $result = $query->result_array();
            foreach ($result as $key => $val) {
                $results[] = $this->activity_model->get_activity_details($val['ActivityID'], '');
            }
            return $results;
        } else {
            $this->db->select('COUNT( DISTINCT A.ActivityID) as TotalRow ');
            $result = $this->db->get();
            $count_data = $result->row_array();
            return $count_data['TotalRow'];
        }
    }

    /**
     * [unique_group_member_detail   Used to get unique group member detail]
     * @param [int]       $category_id        [Category ID]
     * @param [int]       $module_id          [Module ID]
     * @param [int]       $count_flag         [Count Flag]  
     * @param [int]       $order_by           [Order By]  
     * @param [int]       $sort_by            [Sort By]  
     * @param [int]       $page_no            [PageNo]  
     * @param [int]       $page_size          [PageSize]  
     * @param [int]       $user_id            [User ID]  
     */
    function unique_group_member_detail($category_id = '', $module_id, $count_flag = FALSE, $page_no, $page_size, $order_by = "", $sort_by = "", $user_id = '') {

        $results = array();
        if (empty($count_flag)) {
            $this->load->model('users/user_model');
            $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        }

        $this->db->from(GROUPMEMBERS . ' GM');
        $this->db->join(GROUPS . ' G', 'G.GroupID = GM.GroupID');
        if (!empty($category_id)) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID');
            $this->db->where('EC.ModuleID', $module_id);
            $this->db->where('EC.CategoryID', $category_id);
        }

        if (empty($count_flag)) { // check if array needed
            $this->db->select('U.UserGUID,U.UserID, U.FirstName,U.LastName,U.ProfilePicture,P.Url as ProfileURL');
            $this->db->join(USERS . ' U', 'U.UserID=GM.ModuleEntityID AND GM.ModuleID=3', 'join');
            $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID AND P.EntityType="User"', 'join');
            $this->db->where('GM.ModuleID', 3);

            if (!empty($friends)) {
                $this->db->where_in('GM.ModuleEntityID', $friends);
            }

            $this->db->group_by('U.UserID');
            if (!empty($page_size)) {
                $Offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $Offset);
            }
            $query = $this->db->get();

            if ($query->num_rows()) {
                return $query->result_array();
            } else {
                return array();
            }
        } else {
            $this->db->select('COUNT( DISTINCT GM.ModuleEntityID) as TotalRow ');
            $result = $this->db->get();
            $count_data = $result->row_array();
            return $count_data['TotalRow'];
        }
    }

    function get_group_userlist_for_answer($group_id, $members = array(), $group_ids = array(), $ModuleType = array(), $StatusID = '') {
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(GROUPMEMBERS);
        if (!empty($ModuleType)) {
            $this->db->where_in('ModuleRoleID', $ModuleType);
        }
        if (!empty($StatusID)) {
            $this->db->where_in('StatusID', $StatusID);
        }
        $this->db->where('GroupID', $group_id);
        $query = $this->db->get();
        //echo $this->db->last_query();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $member) {
                if ($member['ModuleID'] == 1) {
                    if (in_array($member['ModuleEntityID'], $group_ids)) {
                        continue;
                    } else {
                        $group_ids[] = $member['ModuleEntityID'];
                        $members = $this->get_group_members_id_recursive($member['ModuleEntityID'], $members, $group_ids);
                    }
                } else {
                    $members[] = $member['ModuleEntityID'];
                }
            }
        }
        return $members;
    }

    /**
     * [update_group_member_count Used to update member count for a group]
     * @param   [int] $group_id              [Group id]
     */
    function update_group_member_count($group_id) {
        $member_counts = $this->total_member($group_id);
        // update group member count in group table
        $this->db->set('MemberCount', $member_counts);
        $this->db->where('GroupID', $group_id);
        $this->db->update(GROUPS);
    }

    /**
     * [group_cache Used to cache group details]
     * @param  [int]  $group_id [Group ID]
     * @return [array]            [Group details]
     */
    function group_cache($group_id) {
        $this->db->select('G.Type, G.GroupGUID, G.GroupID, G.GroupName, G.GroupDescription, G.CreatedBy, G.CreatedBy as CreatedByID, G.LastActivity, if(G.GroupImage!="",G.GroupImage,"user_default.jpg") as GroupImage, G.IsPublic, G.GroupCoverImage,G.StatusID,G.LandingPage,G.param', FALSE);
        $this->db->from(GROUPS . ' G');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID');
        $this->db->where('GroupID', $group_id);
        $query = $this->db->get();
        $result = $query->row_array();
        if ($result) {
            $result['GroupCoverImage'] = $result['GroupCoverImage'];
            $result['Category'] = $this->get_group_categories($group_id);
            $result['GroupDescription'] = ucfirst($result['GroupDescription']);
        }
        if (CACHE_ENABLE) {
            $this->cache->save('group_cache_' . $group_id, $result, CACHE_EXPIRATION);
        }
        return $result;
    }

    /**
     * Function Name: set_member_permission
     * @param 
     * Description: Set user permission for group
     */
    function set_member_permission($module_id, $module_entity_id, $field, $value, $group_id, $user_id) {
        $this->db->set($field, $value);
        $this->db->where('ModuleID', $module_id);
        $this->db->where('ModuleEntityID', $module_entity_id);
        $this->db->where('GroupID', $group_id);
        $this->db->update(GROUPMEMBERS);

        if ($field == 'ModuleRoleID' && $value == 5) {
            $parameters = array();
            $parameters[0]['ReferenceID'] = $group_id;
            $parameters[0]['Type'] = 'Group';
            if ($module_id == 1) {
                $members = $this->get_group_members_id_recursive($module_entity_id, array(), array(), TRUE);
            } else {
                $members = array($module_entity_id);
            }
            $this->notification_model->add_notification(62, $user_id, $members, $group_id, $parameters);
        }
    }

    /**
     * [get_groups_by_categories Used to get groups of given category]
     * @param  [array] $categories  [Array of category ID's]
     * @param  [int] $user_id       [User ID]
     * @return [array]             [description]
     */
    function get_groups_by_categories($categories, $user_id) {
        $this->db->select('GROUP_CONCAT(G.GroupID) as GroupIDs ');
        $this->db->from(GROUPS . ' G');
        $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID  = G.GroupID', 'inner');
        $this->db->where_in('EC.CategoryID', $categories);
        $this->db->where('EC.ModuleID', 1);
        $this->db->where_in('G.StatusID', array(2, 5));

        $CasePrivacy = "if(G.IsPublic IN (0,2),(
                         Select GM.ModuleEntityID
                         from GroupMembers GM
                         where GM.ModuleEntityID = " . $user_id . " AND GM.GroupID = G.GroupID AND GM.ModuleID=3 AND GM.StatusID='2' Limit 1)!='',TRUE)";

        $this->db->where($CasePrivacy, NULL, FALSE);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $res = $query->row_array();

            return $res['GroupIDs'];
        } else {
            return false;
        }
    }

    /**
     * [group_member_suggestion  used to get list of friends to add in a group] 
     * @param [int]         $offset             [Offset]
     * @param [int]         $page_size          [Limit]
     * @param [int]         $user_id            [Logged in User ID]
     */
    function group_member_suggestion($user_id, $group_id, $page_no, $page_size, $num_rows = FALSE, $group_ids, $categories, $friends_list) {
        $blocked_users = $this->activity_model->block_user_list($user_id, 3);

        $categories = implode(",", $categories);
        if ($group_ids) {
            $this->db->select('U.UserGUID,CONCAT(U.FirstName," ",,U.LastName) as Name ,U.ProfilePicture,
        
        (select count(GM2.GroupID) from  ' . GROUPMEMBERS . '  GM2 where GM2.AddedBy = ' . $user_id . ' AND GM2.ModuleEntityID = U.UserID) as FrequentMembers,
        
        (Select count(GM1.GroupID) from  ' . GROUPMEMBERS . '  GM1 where GM1.ModuleEntityID in (' . $group_ids . ') AND GM1.ModuleEntityID = U.UserID AND GM1.AddedBy = ' . $user_id . ') as CategoryMember,
        
         (Select count(F.FollowID) from  ' . FOLLOW . ' F  where F.TypeEntityID in (' . $categories . ') AND F.UserID = U.UserID AND F.Type = "category" ) as FollowCategory

         ', FALSE);
        } else {
            $this->db->select('U.UserGUID,CONCAT(U.FirstName," ",,U.LastName) as Name ,U.ProfilePicture,
        
        (select count(GM2.GroupID) from  ' . GROUPMEMBERS . '  GM2 where GM2.AddedBy = ' . $user_id . ' AND GM2.ModuleEntityID = U.UserID) as FrequentMembers,
        
        
         (Select count(F.FollowID) from  ' . FOLLOW . ' F  where F.TypeEntityID in (' . $categories . ') AND F.UserID = U.UserID AND F.Type = "category" ) as FollowCategory

         ', FALSE);
        }
        $this->db->from(USERS . ' U');
        $this->db->join(USERPRIVACY . ' UP', 'UP.UserID=U.UserID', 'left');
        $this->db->join(GROUPMEMBERS . ' GM', 'GM.ModuleEntityID = U.UserID AND GM.ModuleID=3', 'left');
        $this->db->where('UP.PrivacyLabelKey', 'add_in_group');

        $privacy_condition = "IF( (UP.Value!='self'),IF(U.UserID IN (" . $friends_list . "),TRUE,FALSE) ,FALSE)";

        $this->db->where($privacy_condition, NULL, FALSE);

        if ($blocked_users) {
            $this->db->where_not_in('U.UserID', $blocked_users);
        }
        $this->db->where('U.UserID!=' . $user_id, NULL, FALSE);

        $this->db->where('(U.UserID not in (select ModuleEntityID AS UserID from ' . GROUPMEMBERS . ' gm where gm.GroupID = ' . $group_id . ' AND gm.ModuleID=3 AND gm.StatusID!=14) ) ', NUll, FALSE);

        $this->db->where_not_in("U.StatusID", array(3, 4));

        $this->db->group_by('U.UserID');

        $this->db->_protect_identifiers = FALSE;
        if ($group_ids) {
            $this->db->order_by('FrequentMembers DESC, CategoryMember DESC, FollowCategory DESC');
        } else {
            $this->db->order_by('FrequentMembers DESC, FollowCategory DESC');
        }

        $this->db->_protect_identifiers = TRUE;

        if (!$num_rows && !empty($page_size)) {
            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        }
        $query = $this->db->get();

        if ($num_rows) {
            return $query->num_rows();
        }

        $result = array();
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
        }
        return $result;
    }

    /**
     * [update group setting]
     * @param   [Array] $input          [Array of Group Details]
     * @return  [BOOL]   TRUE         
     */
    function update_group_setting($input) {

        $this->db->where('GroupID', $input['ModuleEntityID']);
        $this->db->update(GROUPS, array('IsPublic' => $input['IsPublic'], 'LandingPage' => $input['LandingPage']));

        $this->update_allowed_content($input);

        if (CACHE_ENABLE) {
            $this->cache->delete('group_cache_' . $input['ModuleEntityID']);
        }

        return true;
    }

    /**
     * [update group default permisson]
     * @param   [Array] $input          [Array]
     * @return  [BOOL]   TRUE         
     */
    function update_default_permisson($input) {
        $this->db->where('GroupID', $input['group_id']);
        $this->db->update(GROUPS, array('param' => $input['param']));
        if (CACHE_ENABLE) {
            $this->cache->delete('group_cache_' . $input['group_id']);
        }
        return true;
    }

    /**
     * [update_allowed_content Used to update allowed content setting for given input]
     * @param  [array] $input [description]
     */
    function update_allowed_content($input) {
        $labels = array('1'=>'Discussion','2'=>'Q & A','3'=>'Polls','4'=>'Article','5'=>'Tasks & Lists','6'=>'Ideas','7'=>'Announcements','8'=>'Visual Post','9'=>'Contest');

        $allowed_post_type = $input['AllowedPostType'];
        $this->db->where('ModuleID', '1');
        $this->db->where('ModuleEntityID', $input['ModuleEntityID']);
        $this->db->delete(ALLOWEDPOSTTYPE);
        if (!empty($allowed_post_type)) {
            foreach ($allowed_post_type as $Type) {
                $this->db->insert(ALLOWEDPOSTTYPE, array('ModuleID' => 1, 'ModuleEntityID' => $input['ModuleEntityID'], 'PostType' => $Type, 'PostTypeLabel' => $labels[$Type], 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
            }
        }
    }

    /**
     * [get_group_permission Used to get which type of post user can allow at the time of create/edit group]
     * @param  [int] $user_id [User ID]
     * @return [array]        [Permission Array]
     */
    function get_group_permission($user_id) {
        if (CACHE_ENABLE) {
            $data = $this->cache->get('group_allow_' . $user_id);
            if ($data) {
                return $data;
            }
        }
        $data = array(array('Label' => 'Discussion', 'Value' => 1));
        $groups = $this->get_users_groups($user_id);
        $groups[] = 0;

        $case = "
            (CASE
                WHEN ModuleID='0' 
                THEN
                    TRUE
                WHEN ModuleID='3'
                
                THEN
                    ModuleEntityID='" . $user_id . "'
                
                WHEN ModuleID='1'
                THEN
                    ModuleEntityID IN (" . implode(',', $groups) . ")
                ELSE
                '' 
                END) 
        ";

        $this->db->select("PostType as Value,PostTypeLabel as Label", false);
        $this->db->from(ALLOWEDGROUPTYPE);
        $this->db->where($case, null, false);
        $this->db->order_by('PostTypeLabel', 'ASC');
        $query = $this->db->get();
        if ($query->num_rows()) {
            $data = $query->result_array();
            if($this->settings_model->isDisabled(38)){
                $key = array_search('4', array_column($data, 'Value'));
                if ($key !== FALSE) {
                    array_splice($data, $key, 1);
                }
            }
            if($this->settings_model->isDisabled(44)){
                $key = array_search('7', array_column($data, 'Value'));
                if ($key !== FALSE) {
                    array_splice($data, $key, 1);
                }
            }
            if (CACHE_ENABLE) {
                $this->cache->save('group_allow_' . $user_id, $data, 60);
            }
        }
        return $data;
    }

    /**
     * [get_post_permission which type of post allowed for given Group ID]
     * @param  [int] $group_id [Group ID]
     * @return [array]         [Permission Array]
     */
    function get_post_permission($group_id) {
        $data = array();
        $this->db->select("PostType as Value,PostTypeLabel as Label", false);
        $this->db->from(ALLOWEDPOSTTYPE);
        $this->db->where_not_in('PostType', array(3, 5, 6));
        $this->db->where('ModuleID', '1');
        $this->db->where('ModuleEntityID', $group_id);
        $this->db->order_by('PostTypeLabel', 'ASC');
        $query = $this->db->get();
        $data = array(array("Value" => 1, "Label" => 'Discussion'), array("Value" => 2, "Label" => 'Q & A'));
        if ($query->num_rows()) {
            $data = $query->result_array();
        }
        if($this->settings_model->isDisabled(38)){
            $key = array_search('4', array_column($data, 'Value'));
            if ($key !== FALSE) {
                array_splice($data, $key, 1);
            }
        }
        if($this->settings_model->isDisabled(44)){
            $key = array_search('7', array_column($data, 'Value'));
            if ($key !== FALSE) {
                array_splice($data, $key, 1);
            }
        }
        return $data;
    }

    /**
     * [get_subcategories Used to get sub category of given category]
     * @param  [int] $category_id [category id]
     * @return [array]            [Sub category ids]
     */
    function get_subcategories($category_id) {
        $data = array();
        $data[] = $category_id;

        if ($category_id != 0) {
            $this->db->select('CategoryID');
            $this->db->from(CATEGORYMASTER);
            $this->db->where('ParentID', $category_id);
            $this->db->where('ModuleID', '1');
            $this->db->where('StatusID', '2');
            $query = $this->db->get();
            if ($query->num_rows()) {
                foreach ($query->result_array() as $result) {
                    $data[] = $result['CategoryID'];
                }
            }
        }
        return $data;
    }

    /**
     * [check_is_expert]
     * @param   [UserID] $input
     * @param   [GroupID] $input
     * @return  [BOOL]   TRUE         
     */
    function check_is_expert($user_id, $group_id) {
        $this->db->select('IsExpert');
        $this->db->from(GROUPMEMBERS);
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('GroupID', $group_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->IsExpert;
        }
        return 0;
    }

    /**
     * [group_count  used to count number of groups ]
     * @param [sring]       $SearchText         [Search Keyword (group name)]   
     * @param [sring]       $ListingType        [MyGroup | Joined | Invited]    
     * @param [int]         $user_id            [Logged in User ID]
     * @return [int]        No of groups
     */
    function group_count($input) {
        $results = array();
        if (trim($input['SearchText'])) {
            $terms = explode(' ', trim($input['SearchText']));
            $bits = array();
            foreach ($terms as $term) {
                $bits[] = " G.GroupName LIKE '% " . $this->db->escape_like_str($term) . "%'";
                $bits[] = " G.GroupName LIKE '%" . $this->db->escape_like_str($term) . "%'";
            }
            $this->db->where(" (" . implode(' OR ', $bits) . ")", NULL, FALSE);
        }

        switch ($input['ListingType']) {
            case 'MyGroup':
                $this->db->select("'MyGroup' ResponseGroup", false);
                $this->db->where("GM.ModuleEntityID", $input['UserID']);
                $this->db->where("(G.StatusID = '2' OR G.StatusID = '10')", NULL, FALSE);
                $this->db->where("(GM.ModuleRoleID = '4' OR GM.ModuleRoleID = '5')", NULL, FALSE);
                break;
            case 'Joined':
                $this->db->select("'Joined' ResponseGroup", false);
                $this->db->where("GM.ModuleEntityID ", $input['UserID']);
                $this->db->where("G.StatusID", 2);
                $this->db->where("GM.StatusID", 2);
                $this->db->where("GM.ModuleRoleID", 6);
                break;
            case 'Invited':
                $this->db->select("'Invited' ResponseGroup", false);
                $this->db->where("GM.ModuleEntityID", $input['UserID']);
                $this->db->where("GM.StatusID", 1);
                $this->db->where("G.StatusID", 2);
                break;
            default:
                $this->db->select("if( GM.ModuleRoleID = 4 || GM.ModuleRoleID = 5 ,'MyGroup',if(GM.StatusID=2,'Joined','Invited')) ResponseGroup", false)
                        ->where(array(
                            "GM.ModuleEntityID" => $input['UserID'],
                            "G.StatusID" => 2,
                            "GM.StatusID in(1,2)" => null
                        ))
                        ->order_by("ResponseGroup");
        }

        $this->db->select('G.GroupGUID');
        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' GM', 'G.GroupID = GM.GroupID');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'inner');
        $this->db->group_by('G.GroupID');
        $res = $this->db->get();
        return $res->num_rows();
    }

    /**
     * [similar_groups Used to get the similar group list of given category]
     * @param  [int] $user_id           [User id]
     * @param  [boolean] $count_flag    [count flag]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @param  [int] $current_group_id  [current Group ID]
     * @param  [array/int] $category_id [Category ID]
     * @return [array]                  [Group list]
     */
    function similar_groups($user_id, $count_flag = FALSE, $page_no = '', $page_size = '', $category_id = '', $current_group_id = 0, $with_detail = FALSE) {
        $current_user_group_ids = $this->get_users_groups($user_id);
        $current_user_group_ids[] = $current_group_id;

        $results = array();

        $blocked_group_list = $this->get_blocked_group_list($user_id);

        $this->load->model('users/user_model');
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $friends[] = $user_id;
        if ($friends) {
            $this->db->select('(SELECT COUNT(ModuleEntityID) FROM ' . GROUPMEMBERS . ' WHERE GroupID=G.GroupID AND ModuleID=3 AND ModuleEntityID IN (' . implode(',', $friends) . ')) as FriendsCount', false);
        }

        $select_array = array();
        $select_array[] = '(CASE G.Type 
                            WHEN "INFORMAL" THEN (
                            SELECT GROUP_CONCAT(CASE GMM.ModuleID WHEN 3 THEN CONCAT(US.FirstName," ",US.LastName) ELSE GG.GroupName END) 
                            FROM ' . GROUPMEMBERS . ' GMM 
                            LEFT JOIN ' . USERS . ' US ON `US`.`UserID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 3
                            LEFT JOIN ' . GROUPS . ' GG ON `GG`.`GroupID` = `GMM`.`ModuleEntityID` AND `GMM`.`ModuleID` = 1 
                            WHERE GMM.GroupID=G.GroupID 
                            GROUP BY GMM.GroupID
                            )   
                            ELSE G.GroupName END) AS GroupName';
        $select_array[] = 'G.Type, G.GroupGUID,CONCAT(U.FirstName," ",U.LastName) AS CreatedBy,U.UserGUID as CreatorGUID,  G.LastActivity, G.GroupID, G.GroupDescription,if(LENGTH(G.GroupDescription) > 70 ,CONCAT(SUBSTR(G.GroupDescription,1,70) ,"","..."),G.GroupDescription) as ShortGroupDescription, G.CreatedDate, if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture, G.IsPublic, if(G.IsPublic=0,1,if(G.IsPublic=1,0,2)) as GroupOrder, G.MemberCount, G.Popularity as ActivityLevel';

        $this->db->from(GROUPS . ' G');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'inner');

        if (!empty($category_id)) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID', 'left');
            $this->db->where('EC.ModuleID', 1);
            if (is_array($category_id)) {
                $this->db->where_in('EC.CategoryID', $category_id);
            } else {
                $this->db->where('EC.CategoryID', $category_id);
            }
        }

        $this->db->where_not_in('G.GroupID', $current_user_group_ids);
        $this->db->where_in('G.IsPublic', array(0, 1));
        if ($blocked_group_list) {
            $this->db->where_not_in('G.GroupID', $blocked_group_list);
        }

        $this->db->where("G.StatusID", 2);
        $this->db->where_not_in("U.StatusID", array(3, 4));
        $this->db->order_by('FriendsCount', 'DESC');
        $this->db->order_by('G.MemberCount', 'DESC');
        $this->db->order_by(" FIELD(G.GroupID, " . implode(',', $current_user_group_ids) . " ) DESC", NULL);

        if (empty($count_flag)) { // check if array needed
            if (!empty($page_size)) { // Check for pagination
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }
            $this->db->group_by('G.GroupID');
            $this->db->select(implode(',', $select_array), false);
            $query = $this->db->get();
            //echo $this->db->last_query();die;
            $result = $query->result_array();
            if ($with_detail) {
                foreach ($result as $key => $val) {
                    $result[$key]['Category'] = $this->get_group_categories($val['GroupID']);

                    $membership = $this->check_membership($val['GroupID'], $user_id, TRUE);
                    $permissions['IsActiveMember'] = FALSE;
                    $permissions['IsInvited'] = FALSE; //User recieve JOIN request 
                    $permissions['IsInviteSent'] = FALSE; //User sent request to join group 
                    $permissions['DirectGroupMember'] = FALSE;
                    if (!empty($membership)) {
                        if ($membership['StatusID'] == '2') {
                            $permissions['IsActiveMember'] = TRUE;
                        }

                        if ($membership['StatusID'] == 1) { // check if member is invited
                            $permissions['IsInvited'] = $membership['StatusID'];
                        }
                        if ($membership['StatusID'] == 18) { // check if member request to join this group
                            $permissions['IsInviteSent'] = TRUE;
                        }

                        if ($membership['ModuleID'] == 3 && $user_id == $membership['ModuleEntityID']) {
                            $permissions['DirectGroupMember'] = TRUE;
                        }
                    }

                    $result[$key]['Permission'] = $permissions;

                    if ($friends && $val['Type'] == 'FORMAL') {
                        $result[$key]['Members'] = $this->get_group_members_details($val['GroupID'], 1, 4, $friends);
                    }

                    $result[$key]['PopularDiscussion'] = $this->activity_model->get_popuplar_activity_of_module(1, $val['GroupGUID'], 7);
                    
                    
                    $group_url_details = $this->get_group_details_by_id($val['GroupID'], '', $val);
                    $result[$key]['ProfileURL'] = $this->get_group_url($val['GroupID'], $group_url_details['GroupNameTitle'], false, 'index');
                    

                    unset($result[$key]['FriendsCount']);
                }
            }
            return $result;
        } else {
            $this->db->select('COUNT( DISTINCT G.GroupID) as TotalRow ');
            $result = $this->db->get();
            $count_data = $result->row_array();
            return $count_data['TotalRow'];
            //return $this->db->get()->num_rows();
        }
    }

    /**
     * [admin_group_listing  used to get list of groups ]
     * @param [sring]   $SearchText         [Search Keyword (group name)]   
     * @param [sring]   $ListingType        [MyGroup | Joined | Invited]        
     * @param [int]         $Offset             [Offset]
     * @param [int]         $page_size          [Limit]
     * @param [int]         $user_id            [Logged in User ID]
     */
    function admin_group_listing($input, $offset, $page_size = PAGE_SIZE, $search = 0, $count_only = 0, $all = FALSE) {
        $this->load->model('users/user_model');
        $friend_followers_list = $this->user_model->gerFriendsFollowersList($input['UserID'], true, 1);
        $friends = $friend_followers_list['Friends'];
        $friends[] = $input['UserID'];
        $results = array();

        $this->db->select('G.GroupGUID,U.FirstName,,U.LastName,G.LastActivity,G.GroupID,G.GroupName,G.GroupDescription,Gm.ModuleRoleID,G.CreatedDate,G.MemberCount,if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg") as ProfilePicture,G.IsPublic,if(G.IsPublic=0,1,if(G.IsPublic=1,0,2)) as GroupOrder', FALSE);
        $this->db->select('(CASE WHEN (G.IsPublic=1) THEN "Open" ELSE (CASE WHEN (G.IsPublic=0) THEN "Closed" ELSE "Secret" END) END) AS IsPublic', FALSE);
        $this->db->select('G.StatusID,IFNULL(G.GroupCoverImage,"") AS CoverPicture', FALSE);
        $this->db->select('IFNULL(PFU.URL,"") AS ProfileURL,IFNULL(U.ProfilePicture,"")AS UProfilePicture,U.UserGUID,U.UserTypeID', FALSE);

        $this->db->from(GROUPS . ' G');
        $this->db->join(GROUPMEMBERS . ' Gm', 'G.GroupID = Gm.GroupID');
        $this->db->join(USERS . ' U', 'G.CreatedBy = U.UserID', 'inner');
        $this->db->join(PROFILEURL . ' PFU', 'U.UserID = PFU.EntityID AND EntityType="User"', 'left');
        if (isset($input['CategoryID']) && !empty($input['CategoryID'])) {
            $this->db->join(ENTITYCATEGORY . ' EC', 'EC.ModuleEntityID=G.GroupID', 'left');
            $this->db->where('EC.ModuleID', 1);
            $this->db->where('EC.CategoryID', $input['CategoryID']);
        }
        if (!$all) /* condition to get user specific group */ {
            $this->db->where('(CASE WHEN (Gm.ModuleRoleID=4) THEN G.StatusID IN (2,1) ELSE G.StatusID=2 END)', NULL, FALSE);
            $this->db->where('Gm.StatusID', 2);
            $this->db->where('GM.ModuleEntityID', $this->UserID);
        } else {
            $this->db->where('G.StatusID', 2);
        }

        $this->db->group_by('G.GroupID');

        if (!empty($page_size)) {
            if ($offset == 0) {
                $offset = 1;
            }
            $offset = ($offset - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }

        if (isset($input['PrivacyType']) && $input['PrivacyType'] != '-1') {
            $this->db->where('G.IsPublic', $input['PrivacyType']);
        }

        if (trim($input['SearchText'])) {
            $terms = explode(' ', trim($input['SearchText']));
            $bits = array();
            foreach ($terms as $term) {
                $bits[] = " G.GroupName LIKE '%" . $this->db->escape_like_str($term) . "%'";
                //$bits[] = " G.GroupDescription LIKE '%".$this->db->escape_like_str($term)."%'";
            }
            $this->db->select("IF(G.GroupName LIKE '%" . $this->db->escape_like_str($input['SearchText']) . "%',1,2) as OrdBy", FALSE);
            $this->db->where(" (" . implode(' OR ', $bits) . ")", NULL, FALSE);
        }

        // Added Sorting by type and order
        if (isset($input['SortBy']) && isset($input['OrderBy'])) {
            if (empty($input['OrderBy'])) {
                $input['OrderBy'] = "DESC";
            } else {
                $input['OrderBy'] = "ASC";
            }
            $this->db->order_by($input['SortBy'], $input['OrderBy']);
        }

        $res = $this->db->get();
        if ($count_only) {
            return $res->num_rows();
        }
        $result_data = array();
        $results['total_records'] = $res->num_rows();
        $val['TotalRecords'] = $results['total_records'];
        $result = $res->result_array();
        $result_data = array();

        foreach ($result as $key => $value) {
            $value['Category'] = $this->get_group_categories($value['GroupID']);
            unset($value['GroupID']);
            unset($value['OrdBy']);
            $result_data[$key] = $value;

            if (!empty($value['ProfilePicture'])) {
                $result_data[$key]['GroupCover'] = IMAGE_SERVER_PATH . 'upload/profile/' . $value['ProfilePicture'];
            } else {
                $result_data[$key]['GroupCover'] = IMAGE_SERVER_PATH . 'upload/blank-profile.jpg';
            }
            /* set Created by data */
            $result_data[$key]['CreatedBy'] = array(
                'FirstName' => $value['FirstName'],
                'LastName' => $value['LastName'],
                'UserGUID' => $value['UserGUID'],
                'UserTypeID' => $value['UserTypeID'],
                'ProfilePicture' => $value['UProfilePicture'],
                'ProfileURL' => $value['ProfileURL']
            );
            /* set Created by data */

            /* Unset variables not required */
            unset($result_data[$key]['FirstName']);
            unset($result_data[$key]['LastName']);
            unset($result_data[$key]['UserGUID']);
            unset($result_data[$key]['UserTypeID']);
            unset($result_data[$key]['UProfilePicture']);
            unset($result_data[$key]['ProfileURL']);
            /* Unset variables not required */
        }
        $results['data'] = $result_data;
        return $result_data;
    }

    function check_can_create_knowledge_base($user_id, $group_id) {
        $this->db->select('CanCreateKnowledgeBase');
        $this->db->from(GROUPMEMBERS);
        $this->db->where('ModuleID', '3');
        $this->db->where('ModuleEntityID', $user_id);
        $this->db->where('GroupID', $group_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->CanCreateKnowledgeBase;
        }
        return 0;
    }

    /**
     * 
     * @param type $group_id
     * @param type $blocked_users
     */
    function group_members_count($group_id, $blocked_users) {
        $this->db->select('COUNT(*) as MembersCount');
        $this->db->from(GROUPMEMBERS . ' G');
        $this->db->where('GroupID=' . $group_id . ' AND IF(G.ModuleID=3, G.ModuleEntityID NOT IN (' . implode(',', $blocked_users) . '),true) ', null, false);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->MembersCount;
        }
        return 0;
    }

    /**
     * 
     * @param type $group_id
     * @param type $blocked_users
     */
    function group_recent_post_owner($group_id,$user_id)
    {
        $result['Data']=array();
        $result['TotalRecords']=0;
        $friends = $this->user_model->gerFriendsFollowersList($user_id, TRUE, 1, TRUE);
        $friends[]=0;
        $this->db->select('CONCAT(U.FirstName," ",U.LastName) AS Name,U.ProfilePicture,U.UserGUID,PU.Url as ProfileUrl');
        $this->db->from(GROUPS.' G');
        $this->db->join(GROUPMEMBERS.' GM','G.GroupID=GM.GroupID');
        $this->db->join(ACTIVITY.' A','A.ModuleEntityID=G.GroupID AND A.ModuleID=1 AND A.StatusID=2');
        $this->db->join(USERS . ' U', 'A.UserID = U.UserID');
        $this->db->join(PROFILEURL . ' PU', 'U.UserID = PU.EntityID AND PU.EntityType="User"');
        $this->db->where('G.GroupID',$group_id);
        $this->db->group_by('A.UserID');
        $this->db->order_by("CASE WHEN U.UserID IN (". implode(',', $friends).") THEN 1 ELSE 0 END DESC");
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $result['TotalRecords'] = $temp_q->num_rows();
        $this->db->limit(4);
        $query = $this->db->get();
        $result['Data'] =$query->result_array();
        return $result;
    }

    function get_discussion_count($group_id)
    {
        $this->db->select("COUNT(ActivityID) as DiscussionCount");
        $this->db->from(ACTIVITY);
        $this->db->where('ModuleID','1');
        $this->db->where('ModuleEntityID',$group_id);
        $this->db->where('ActivityTypeID','7');
        $this->db->where('StatusID','2');
        $query = $this->db->get();
        if($query->num_rows())
        {
          $row = $query->row_array();
          return $row['DiscussionCount'];
        }
        else
        {
          return 0;
        }
    }
    
    function get_group_url($groupID, $title, $only_title_url = false, $url_type='about') {
        $title_url = str_replace(' ', '-', trim(get_valid_url_str($title)));
        if(!$title_url) { // To handle 404 error
            $title_url =  $groupID;
        }        
        
        if($only_title_url) {
            if($url_type == 'index') {
                return  $title_url .'/'. $groupID . '';
            }
            return  $title_url .'/'.$url_type.'/'. $groupID . '';
        }
        
        if (!empty($groupID)) {
            
            if($url_type == 'index') {
                return 'group/'.$title_url.'/'. $groupID . '';
            }
            
            return 'group/'.$title_url .'/'.$url_type.'/'. $groupID . '';
        } else {
            return false;
        }
    }
    
    function get_group_details_by_id($group_id, $extra_fields = '', $group_details = []) {
        
        $fields = 'GroupID,GroupGUID,IsPublic,LandingPage,CreatedBy, GroupName';        
        $fields = ($extra_fields) ? $fields. ', '. $extra_fields : $fields;
        if(empty($group_details)) {
            $group_details = get_detail_by_id($group_id, 1, $fields, 2);   
        }
        
        if(!empty($group_details['GroupName'])) {
            $group_details['GroupNameTitle'] = isset($group_details['GroupName']) ? $group_details['GroupName'] : '';
        }
        
        
        if(empty($group_details['GroupNameTitle']) ) {
            $group_details['GroupNameTitle'] = isset($group_details['GroupGUID']) ? $group_details['GroupGUID'] : '';
        }
        
        return $group_details;
    }
}
