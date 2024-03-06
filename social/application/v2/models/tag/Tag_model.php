<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tag_model extends Common_Model {
    public $tag_type_data = array();
    public $tag_entity_type_data = array();
    function __construct() {
        parent::__construct();
        $this->tag_type_data = array(
            'ACTIVITY' => 1,
            'MOOD' => 3,
            'CLASSIFICATION' => 4,
            'READER' => 5,
            'USER' => 5,
            'PROFESSION' => 6,
            'BRAND' => 7            
        );
        $this->tag_entity_type_data = array(
            'ACTIVITY' => 'ACTIVITY',
            'MOOD' => 'ACTIVITY',
            'CLASSIFICATION' => 'ACTIVITY',
            'READER' => 'ACTIVITY',
            'USER' => 'USER',
            'PROFESSION' => 'USER',
            'BRAND' => 'USER'            
        );        
    }

    /**
     * [save_tag Used to add tag in master list]
     * @param [array] $tags_list [Tag list]
     * @param [int] $tag_type  [Type of tag]
     * @param [int] $user_id  [User ID]
     */
    public function save_tag($tags_list, $tag_type, $user_id) {
        $tags_data = array();
        if (isset($tags_list) && !empty($tags_list)) {
            $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
            foreach ($tags_list as $tag) {
                $tag = isset($tag['Name']) ? $tag['Name'] : "";
                if (!empty(trim($tag))) {
                    $tag_id = $this->is_tag_exist($tag, $tag_type);
                    if (empty($tag_id)) {
                        $tag_data = array('Name' => $tag, 'TagType' => $tag_type, 'CreatedBy' => $user_id, 'ModifiedBy' => $user_id, 'CreatedDate' => $current_date, 'ModifiedDate' => $current_date);
                        $this->db->insert(TAGS, $tag_data);
                        $tag_id = $this->db->insert_id();
                    }
                    //data to be cached
                    $tags_data[] = array('TagID' => $tag_id, 'Name' => $tag, 'TagType' => $tag_type);
                }
            }
            return $tags_data;
        }
    }

    /**
     * [save_entity_tag Used to associated given tag with given entity]
     * @param [array] $tags_data   [tag data]
     * @param [string] $entity_type [Entity Type]
     * @param [int] $entity_id   [Entity ID]
     * @param [int] $user_id  [User ID]
     */
    public function save_entity_tag($tags_data, $entity_type, $entity_id, $user_id, $is_front_end = 0, $return_ids = false, $forDummyUser = false, $isCheckOldTags = false) {
        $table_name = ($forDummyUser) ? ENTITYTAGSDUMMY : ENTITYTAGS;
        $user_id = ($forDummyUser) ? 0 : $user_id;

        if ($forDummyUser) {
            $table_name = ENTITYTAGSDUMMY;
            $user_id = 0;
            $entity_id = 0;
        } else {
            $table_name = ENTITYTAGS;
        }

        if ($isCheckOldTags) {
            $this->db->select('TagID');
            $this->db->from($table_name);
            $this->db->where('EntityID', $entity_id);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('StatusID', 2);
            $query = $this->db->get();
            $saved_tags = $query->result_array();
            $saved_tags_ids = [];
            foreach ($saved_tags as $saved_tag) {
                $saved_tags_ids[] = $saved_tag['TagID'];
            }

            $sent_tags = $tags_data;
            $sent_tags_ids = [];
            foreach ($sent_tags as $sent_tag) {
                $sent_tags_ids[] = $sent_tag['TagID'];
            }

            $deleting_tag_ids = array_diff($saved_tags_ids, $sent_tags_ids);
            if ($deleting_tag_ids) {
                $this->delete_entity_tag($entity_id, $entity_type, $deleting_tag_ids, [], false);
            }
        }

        $tag_data = array();
        //$entity_tag_ids = array();
        $return_tag_data = array();
        $i = 0;
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        //$is_front_end = ($is_front_end)?0:1;
        foreach ($tags_data as $tag) {
            $tag_id = $tag['TagID'];

            if (!empty($tag_id)) {
                $entity_tag_id = $this->is_entity_tag_exist($entity_id, $entity_type, $tag_id, $forDummyUser);
                if (empty($entity_tag_id)) {
                    $tag_data = array('EntityType' => strtoupper($entity_type), 'EntityID' => $entity_id, 'TagID' => $tag_id, 'UserID' => $user_id, 'CreatedDate' => $current_date, 'StatusID' => '2', 'AddedBy' => $is_front_end);
                    $this->db->insert($table_name, $tag_data);
                    $entity_tag_id = $this->db->insert_id();
                }
                $return_tag_data[] = array('TagID' => $tag_id, 'Name' => $tag['Name'], 'EntityTagID' => $entity_tag_id, 'AddedBy' => $is_front_end);
            }
        }
        /* if ($tag_data) 
          {
          if($return_ids) {
          foreach ($tag_data as $tag_insert_data) {
          $this->db->insert(ENTITYTAGS, $tag_insert_data);
          $entity_tag_id = $this->db->insert_id();
          $entity_tag_ids[] = $entity_tag_id;
          }
          } else {
          $this->db->insert_batch(ENTITYTAGS, $tag_data);
          }

          } */

        if (!$forDummyUser && CACHE_ENABLE && $entity_type == 'ACTIVITY' && empty($is_front_end)) {
            $this->cache->delete('activity_' . $entity_id);
        }
        return $return_tag_data;
    }

    /**
     * [is_tag_exist used to check given tag exist or not]
     * @param  [string]  $tag      [tag name]
     * @param  [int]  $tag_type 	[tag type]
     * @return [int]           	[tag ID]
     */
    public function is_tag_exist($tag, $tag_type) {
        $tag_id = 0;
        $this->db->select('TagID');
        $this->db->from(TAGS);
        $this->db->where('Name', $tag);
        $this->db->where('TagType', $tag_type);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $tag_id = $query->row()->TagID;
        }
        return $tag_id;
    }

    public function is_entity_tag_exist($entity_id, $entity_type, $tag_id, $forDummyUser = false) {
        $table_name = ($forDummyUser) ? ENTITYTAGSDUMMY : ENTITYTAGS;
        $entity_tag_id = 0;

        if ($forDummyUser) {
            $this->db->select('EntityTagID');
            $this->db->from($table_name);
            $this->db->where('TagID', $tag_id);
            $this->db->limit(1);
            //$this->db->where('EntityID', $entity_id);
            //$this->db->where('EntityType', $entity_type);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $entity_tag_id = $query->row()->EntityTagID;
            }
            return $entity_tag_id;
        }

        $this->db->select('EntityTagID');
        $this->db->from($table_name);
        $this->db->where('TagID', $tag_id);
        $this->db->where('EntityID', $entity_id);
        $this->db->where('EntityType', $entity_type);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $entity_tag_id = $query->row()->EntityTagID;
        }
        return $entity_tag_id;
    }

    /**
     * [delete_tag Used to delete tag from master list]
     * @param  [array]  $tag_ids [Tag IDs]
     */
    public function delete_tag(array $tag_ids) {
        if (!empty($tag_ids)) {
            $this->db->where_in('TagID', $tag_ids);
            $this->db->delete(TAGS);
        }
    }

    /**
     * [delete_entity_tag Used to delete entity tags]
     * @param  [int] $entity_id   [Entity ID]
     * @param  [string] $entity_type [Entity Type]
     * @param  [array]  $tag_ids     [Entity Tag IDs]
     */
    public function delete_entity_tag($entity_id, $entity_type, $tag_ids = array(), $entity_tag_ids = array(), $forDummyUser = false) {
        $table_name = ($forDummyUser) ? ENTITYTAGSDUMMY : ENTITYTAGS;

        if (CACHE_ENABLE && $entity_type == 'ACTIVITY') {
            $this->cache->delete('activity_' . $entity_id);
        }

        if ($forDummyUser) {
            $this->db->where_in('TagID', $tag_ids);
            $this->db->delete($table_name);
            return;
        }

        if (!empty($entity_tag_ids)) {
            $this->db->where_in('EntityTagID', $entity_tag_ids);
            $this->db->delete($table_name);
            return;
        }
        if (!empty($entity_id) && !empty($entity_type)) {
            $this->db->where('EntityID', $entity_id);
            $this->db->where('EntityType', $entity_type);
            if (!empty($tag_ids)) {
                $this->db->where_in('TagID', $tag_ids);
            }
            $this->db->delete($table_name);
        }
    }

    /**
     * [get_entity_tags uses to get entity tags]
     * @param  [string]    	$search_keyword [Search Keyword]
     * @param  [int]    	$page_no [PageNo]
     * @param  [int]    	$page_size [PageSize]
     * @param  [int]    	$tag_type_id [tag type in master(default :1)]
     * @param  [string]    	$entity_type [Type of Entity(LINK|ACTIVITY)]
     * @param  [int]    	$entity_id [entity ID]
     * @return [array]      [tags result]
     */
    public function get_entity_tags($search_keyword = '', $page_no = 1, $page_size = 10, $tag_type_id = 1, $entity_type = 'LINK', $entity_id = 0, $user_id = 0, $forDummyUser = false, $data = []) {
        $table_name = ($forDummyUser) ? ENTITYTAGSDUMMY : ENTITYTAGS;

        if ($forDummyUser) {
            $entity_id = 0;
            $user_id = 0;
        }

        $tags = array();
        $is_super_admin = FALSE;
        if (!empty($user_id)) {
            $is_super_admin = $this->user_model->is_super_admin($user_id);
        }
        if (!$forDummyUser && CACHE_ENABLE && !empty($entity_id) && $tag_type_id == 1 && $entity_type == 'ACTIVITY') {
            $activity_cache = $this->cache->get('activity_' . $entity_id);
            if (!empty($activity_cache)) {
                if (isset($activity_cache['t'])) {
                    return $activity_cache['t'];
                }
            }
        }
        $entity_type_cond = array($entity_type);
        if(!empty($data['newsletter'])) {
            $entity_type_cond[] = 'NEWSLETTER_SUBSCRIBER';
        }
        $entity_type_cond_list = implode(",", $entity_type_cond);
        $this->db->select('DISTINCT T.TagID,T.Name,T.Name as TooltipTitle, ET.AddedBy', false);
        $this->db->from(TAGS . ' T');
        if ($search_keyword) {
            $this->db->like('T.Name', $search_keyword);
        }
        
        if($forDummyUser) {
            $this->db->join(ENTITYTAGSDUMMY . ' ET', 'T.TagID=ET.TagID', 'left');
        } else {
            $this->db->join(ENTITYTAGS . ' ET', 'T.TagID=ET.TagID AND ET.StatusID=2 AND ET.EntityType IN ("'.$entity_type_cond_list.'")', 'left');
        }
         
        
        if(!empty($data['entity_type_set'])) {
            if(!empty($data['entity_type_set_val'])) 
                $this->db->where_in('T.TagType', [6,7]);
            else 
                $this->db->where_in('T.TagType', [5]);    //$this->db->where_not_in('T.TagType', [6,7]);
        } else {
            $this->db->where('T.TagType', $tag_type_id);
        }
                
        //$this->db->where('ET.StatusID', 2);
        if (!$forDummyUser && !$is_super_admin) {
            $this->db->where('ET.AddedBy', 0);
        }

        if (!empty($entity_id)) {
            $this->db->where('ET.EntityID', $entity_id);
        }

        if (isset($page_size) && !empty($page_size) && isset($page_no)) {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $this->db->order_by("ET.Weight", "DESC");
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $tags = $query->result_array();
        }
        return $tags;
    }

    /**
     * [get_tag_type_id Used to get tag type id]
     * @param  [string] $tag_type [Tag type]
     * @return [int]           [Tag type ID]
     */
    public function get_tag_type_id($tag_type = 'LINK') {
        $tag_type_id = isset($this->tag_type_data[$tag_type]) ? $this->tag_type_data[$tag_type] : 1;        
        return $tag_type_id;
    }

    /**
     * [get_tag_entity_type Used to get tag entity type]
     * @param  [string] $tag_type [Tag Type]
     * @return [string]           [Tag entity type]
     */
    public function get_tag_entity_type($tag_type = 'LINK') {        
        $entity_type = isset($this->tag_entity_type_data[$tag_type]) ? $this->tag_entity_type_data[$tag_type] : 'LINK';                
        return $entity_type;
    }

    /**
     * [check_permission Used to check current user have permission]
     * @param  [int] $user_id     [User ID]
     * @param  [string] $entity_type [Entity Type]
     * @param  [int] $entity_id   [Entity ID]
     * @return [boolean]              [TRUE/FALE]
     */
    public function check_permission($user_id, $entity_type, $entity_id) {
        $is_admin = $this->user_model->is_super_admin($user_id);
        if (!empty($entity_id) && !$is_admin) {
            if ($entity_type == "USER" && $user_id == $entity_id) {
                $is_admin = TRUE;
            }
            if ($entity_type == "ACTIVITY") {
                $is_admin = FALSE;
                $activity_details = get_detail_by_id($entity_id, 0, 'UserID,ModuleID,ModuleEntityID', 2);
                if (isset($activity_details['ModuleID'], $activity_details['ModuleEntityID'])) {
                    switch ($activity_details['ModuleID']) {
                        case '1': //Group
                            // Check permission for Group Admin + post owner + super admin
                            $this->load->model(array('group/group_model'));
                            $permission = $this->group_model->is_admin($user_id, $activity_details['ModuleEntityID']);
                            if ($permission || $activity_details['UserID'] == $user_id) {
                                $is_admin = TRUE;
                            }
                            break;
                        case '3':
                            //If user is wall owner + post owner + super admin                             
                            if ($user_id == $activity_details['ModuleEntityID'] || $activity_details['UserID'] == $user_id) {
                                $is_admin = TRUE;
                            }
                            break;
                        case '18':
                            //Tags will be Editable by Page Owner + post owner + super admin
                            $page_details = get_detail_by_id($activity_details['ModuleEntityID'], '18', "UserID", 2);
                            if ($page_details['UserID'] == $user_id || $activity_details['UserID'] == $user_id) {
                                $is_admin = TRUE;
                            }
                            break;
                        case '14':
                            // Tags will be Editable by Event Creator + post owner + super admin
                            $event_details = get_detail_by_id($activity_details['ModuleEntityID'], '14', "CreatedBy", 2);
                            if ($event_details['CreatedBy'] == $user_id || $activity_details['UserID'] == $user_id) {
                                $is_admin = TRUE;
                            }
                            break;
                        case '34':
                            // Tags will be Editable by Event Creator + post owner + super admin
                            $this->load->model(array('forum/forum_model'));
                            $category_permissions = $this->forum_model->check_forum_permissions($user_id, $activity_details['ModuleEntityID']);
                            if ($category_permissions['IsAdmin'] || $activity_details['UserID'] == $user_id) {
                                $is_admin = TRUE;
                            }
                            break;
                        default:
                            $is_admin = FALSE;
                            break;
                    }
                }
            }
        }
        return $is_admin;
    }

    /**
     * [add_tags_to_user_from_activity Add tag to user from activity]
     * @param [int]  $user_id     [User ID]
     * @param [int]  $entity_id   [Entity ID]
     * @param [string]  $entity_type [Entity Type]
     * @param [int] $tag_type_id    [Tag Type]
     */
    public function add_tags_to_user_from_activity($user_id, $entity_id, $entity_type = "ACTIVITY", $tag_type_id = 5) {
        $this->db->select('DISTINCT T.TagID', false);
        $this->db->from(TAGS . ' T');
        $this->db->join(ENTITYTAGS . ' ET', 'T.TagID=ET.TagID');
        $this->db->where('ET.EntityID', $entity_id);
        $this->db->where('ET.EntityType', $entity_type);
        $this->db->where('T.TagType', $tag_type_id);
        $this->db->where('ET.StatusID', 2);
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $tags = $query->result_array();
            $this->save_entity_tag($tags, 'USER', $user_id, $user_id);
            $this->update_weightage($user_id, $entity_id, $entity_type, $tags);
        }
    }

    /**
     * [update_weightage Used to update weightage for user tags.]
     * @param  [int] $user_id     [User ID]
     * @param  [int] $entity_id   [Entity ID]
     * @param  [string] $entity_type [Entity Type]
     * @param  [array]  $tags        [Tag array]
     */
    public function update_weightage($user_id, $entity_id, $entity_type = "ACTIVITY", $tags = array()) {
        $tag_ids = array();
        $log_data = array();
        $i = 0;
        foreach ($tags as $tag) {
            $tag_id = $tag['TagID'];

            $this->db->select('TagWeightageLogID');
            $this->db->from(TAGWEIGHTAGELOG . ' T');
            $this->db->where('T.EntityID', $entity_id);
            $this->db->where('T.EntityType', $entity_type);
            $this->db->where('T.UserID', $user_id);
            $this->db->where('T.TagID', $tag_id);
            $query = $this->db->get();
            if ($query->num_rows() <= 0) {
                $tag_ids[] = $tag_id;
                $log_data[$i++] = array('EntityType' => strtoupper($entity_type), 'EntityID' => $entity_id, 'TagID' => $tag_id, 'UserID' => $user_id);
            }
        }

        if (!empty($tag_ids)) {
            $set_field = "Weight";
            $count = 1;
            $this->db->where('EntityID', $user_id);
            $this->db->where('EntityType', "USER");
            $this->db->where_in('TagID', $tag_ids);

            $this->db->set($set_field, "$set_field+($count)", FALSE);
            $this->db->update(ENTITYTAGS);
        }
        if ($log_data) {
            $this->db->insert_batch(TAGWEIGHTAGELOG, $log_data);
        }
    }

    /**
     * [get_entity_tags for dummy users]
     * @return [array]      [tags result]
     */
    public function get_entity_tags_for_dummy_users() {
        $table_name = ENTITYTAGSDUMMY;

        $tags = [];
        $this->db->select('DISTINCT T.TagID,T.Name,T.Name as TooltipTitle', false);
        $this->db->from(TAGS . ' T');
        $this->db->join($table_name . ' ET', 'T.TagID=ET.TagID', 'Inner');
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $tags = $query->result_array();
        }
        return $tags;
    }

    /**
     * [save_entity_tag Used to associed given tag with given entity]
     * @param [array] $tags_data   [tag data]
     * @param [string] $entity_type [Entity Type]
     * @param [int] $entity_id   [Entity ID]
     * @param [int] $user_id  [User ID]
     */
    public function save_entity_tag_dummy_users($tags_data) {
        $table_name = ENTITYTAGSDUMMY;

        $tag_data = array();
        //$entity_tag_ids = array();
        $return_tag_data = array();
        $i = 0;
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        //$is_front_end = ($is_front_end)?0:1;
        foreach ($tags_data as $tag) {
            $tag_id = $tag['TagID'];

            if (!empty($tag_id)) {
                $entity_tag_id = $this->is_entity_tag_exist(0, '', $tag_id, 1);
                if (empty($entity_tag_id)) {
                    $tag_data = array('TagID' => $tag_id, 'CreatedDate' => $current_date);
                    $this->db->insert($table_name, $tag_data);
                    $entity_tag_id = $this->db->insert_id();
                }
                $return_tag_data[] = array('TagID' => $tag_id, 'Name' => $tag['Name'], 'EntityTagID' => $entity_tag_id);
            }
        }
        return $return_tag_data;
    }

    /**
     * [assign or update tags for dummy users]
     * @param [array] $tags   [tag data]
     * @param [array] $users [users array]
     * @return [array]      [tags result]
     */
    public function assign_or_update_tags_to_dummy_users($tags = NULL, $users = NULL) {

        if ($tags === NULL) {
            $tags = $this->get_entity_tags_for_dummy_users();
        }

        if ($users === NULL) {
            $this->load->model(array('admin/users_model'));
            $users = $this->users_model->get_users(0, 0, TRUE, '', TRUE);
        }

        $EntityType = 'USER';
        $AddedBy = 1;

        $user_ids = [];
        $tag_ids = [];
        $db_user_tags = [];

        foreach ($tags as $tag) {
            $tag_ids[] = $tag['TagID'];
        }

        $db_new_insert_arr = [];
        $db_delete_arr = [];

        foreach ($users as $user) {
            $user_id = $user['ModuleEntityID'];

            $this->db->select('EntityTagID, UserID, TagID');
            $this->db->from(ENTITYTAGS);
            //$this->db->where_in('TagID', $tag_ids);
            $this->db->where('UserID', $user_id);
            $this->db->where('EntityType', $EntityType);
            $this->db->where('AddedBy', $AddedBy);
            $query = $this->db->get();
            $existing_tags_users = $query->result_array();

            foreach ($existing_tags_users as $existing_tags_user) {
                $db_user_tags[] = $existing_tags_user['TagID'];
            }

            $new_tags = array_diff($tag_ids, $db_user_tags);
            $deleted_tags = array_diff($db_user_tags, $tag_ids);

            foreach ($new_tags as $new_tag) {
                $db_new_insert_arr[] = array(
                    'TagID' => $new_tag,
                    'UserID' => $user_id,
                    'EntityType' => $EntityType,
                    'AddedBy' => $AddedBy,
                    'EntityID' => $user_id,
                    'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s')
                );
            }

            if (empty($deleted_tags)) {
                continue;
            }

            $this->db->where_in('TagID', $deleted_tags);
            $this->db->where('UserID', $user_id);
            $this->db->where('EntityType', $EntityType);
            $this->db->where('AddedBy', $AddedBy);
            $this->db->delete(ENTITYTAGS);
        }

        if ($db_new_insert_arr) {
            $this->db->insert_batch(ENTITYTAGS, $db_new_insert_arr);
        }
    }

    /**
     * [To get list of popular tags]
     * @param [array] $data   [posted data]
     * @return [array]      [tags result]
     */
    public function get_popular_tags($data, $user_id = 0) { //print_r($data); die;
        $entity_type = 'ACTIVITY';
        $page_size = (int)!empty($data['PageSize']) ? $data['PageSize'] : 10;
        $page_no = (int)!empty($data['PageNo']) ? $data['PageNo'] : 1;
        $module_id = (int)!empty($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = (int)!empty($data['ModuleEntityID']) ? $data['ModuleEntityID'] : 0;
        $feed_query = !empty($data['FeedQuery']) ? $data['FeedQuery'] : '';
        
        $tags = [];
        $this->db->select('COUNT(T.TagID) TagCount, T.TagID,T.Name', false);
        $this->db->from(ACTIVITY . ' A');
        $this->db->join(ENTITYTAGS . ' ET', "A.ActivityID = ET.EntityID AND ET.EntityType = '$entity_type'", 'INNER');
        $this->db->join(TAGS . ' T', 'T.TagID = ET.TagID', 'LEFT');
        //$this->db->where('ET.EntityType', $entity_type);
        $this->db->where('ET.StatusID', 2);
        $this->db->where('A.StatusID', 2);
        
        if($feed_query) {
            $this->db->where("A.ActivityID IN( $feed_query )", NULL, FALSE);
        } else if($module_id && $module_entity_id) {
            $this->db->where('A.ModuleID', $module_id);
            $this->db->where('A.ModuleEntityID', $module_entity_id);
        }
        
        
        
        if(!$user_id && !$feed_query){
            $this->db->where('A.IsFeatured', 1);
        }
        
        $this->db->group_by('T.TagID');
        $this->db->order_by('TagCount DESC');
        
        $offset = ($page_no - 1) * $page_size;
        $this->db->limit($page_size, $offset);
        
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $tags = $query->result_array();
        return $tags;
    }
    
    /**
     * [save_tag_category used to add tag category]
     * @param [string] $name  [Tag category name]
     */
    function save_tag_category($name) {
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');        
        $name = ucwords(strtolower($name));
        $tag_category_id = $this->is_tag_category_exist($name);
        if (empty($tag_category_id)) {
            $display_order = 0;
            $get_tag_category_order = $this->get_single_row('MAX(DisplayOrder) as DisplayOrder', TAGCATEGORY);
            if (isset($get_tag_category_order['DisplayOrder'])) {
                $display_order = $get_tag_category_order['DisplayOrder'];
            }
        
            $tag_category_data = array(
                'Name' => $name, 
                'DisplayOrder' => $display_order + 1,
                'CreatedDate' => $current_date, 
                'ModifiedDate' => $current_date
                    );
            $this->db->insert(TAGCATEGORY, $tag_category_data);
            $tag_category_id = $this->db->insert_id();
        }
        return $tag_category_id;
    }
    
    /**
     * [is_tag_exist used to check given tag category exist or not]
     * @param  [string]  $name      [tag name]
     * @return [int]           	[tag category ID]
     */
    function is_tag_category_exist($name) {
        $tag_category_id = 0;
        $this->db->select('TagCategoryID');
        $this->db->from(TAGCATEGORY);
        $this->db->where('Name', $name);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $tag_category_id = $query->row()->TagCategoryID;
        }
        return $tag_category_id;
    }
    
    /**
     * [save_category_tag Used to associated given tag with given category]
     * @param [array] $tags_data   [tag data]
     * @param [int] $tag_category_id   [tag Category ID]
     */
    function save_category_tag($tags_data, $tag_category_id) {
        $this->db->where('TagCategoryID', $tag_category_id);
        $this->db->delete(TAGSOFTAGCATEGORY);                
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');        
        foreach ($tags_data as $tag) {
            $tag_id = $tag['TagID'];
            $tag_data = array('TagCategoryID' => $tag_category_id, 'TagID' => $tag_id, 'CreatedDate' => $current_date);
            $this->db->insert(TAGSOFTAGCATEGORY, $tag_data);
        }
    }
    
    /**
     * [delete_tag_category Used to delete tag category]
     * @param [int] $tag_category_id   [tag Category ID]
     */
    function delete_tag_category($tag_category_id) {
        $this->db->where('TagCategoryID', $tag_category_id);
        $this->db->delete(TAGCATEGORY);
        $this->delete_api_static_file('tag_categories');
        if (CACHE_ENABLE) {
            $this->cache->delete('tag_categories');
        }
    }
    
    /**
     * [get_tag_categories Used to get tag category]
     */
    function get_tag_categories() {
        $tag_categories = array();
        if (CACHE_ENABLE) {
            $tag_categories = $this->cache->get('tag_categories');            
        }
        if(empty($tag_categories)) {
            $this->db->select('TagCategoryID, Name');
            $this->db->from(TAGCATEGORY);
            $this->db->order_by('DisplayOrder', 'ASC');
            $query = $this->db->get();
            $tag_categories = array();
            if ($query->num_rows()) {
                foreach ($query->result_array() as $row) {
                    $row['Tags'] = $this->get_category_tags($row['TagCategoryID']);
                    $tag_categories[] = $row;
                }
                if (CACHE_ENABLE) {
                    $this->cache->save('tag_categories', $tag_categories);
                }
            }            
        }
        if(!empty($tag_categories)) {
            initiate_worker_job('upload_api_data_on_bucket', array('FileName' => "tag_categories.json", "FileData" => $tag_categories));
        }
        return $tag_categories;
    }
    
    /**
     * [get_category_tags Used to get category tags]
     * @param [int] $tag_category_id   [tag Category ID]
     */
    function get_category_tags($tag_category_id) {
        $this->db->select('T.TagID, T.Name, TC.TagsOFTagCategoryID');
        $this->db->from(TAGSOFTAGCATEGORY.' TC');
        $this->db->join(TAGS . ' T', 'T.TagID = TC.TagID');
        $this->db->where('TC.TagCategoryID', $tag_category_id);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * [change_category_tag_order Used to change display order for tag category]
     * @param [array] $order_data   [tag Category display order data]
     */
    function change_category_tag_order($order_data) {
        $orderValue = array();
        foreach ($order_data as $items) {
            $itemArray = array();
            $itemArray['TagCategoryID'] = $items['TagCategoryID'];
            $itemArray['DisplayOrder'] = $items['DisplayOrder'];
            $orderValue[] = $itemArray;
        }
        if(!empty($orderValue)) {
            $this->db->update_batch(TAGCATEGORY, $orderValue, 'TagCategoryID');
        }
        return true;
    }
    
    /**
     * [get_category_tag_ids Used to get tag ids for given tag categories]
     * @param [array] $tag_category_ids   [tag Category ids]
     */
    function get_category_tag_ids($tag_category_ids) {
        $this->db->select('GROUP_CONCAT(TC.TagID) as TagIDs', false);
        $this->db->from(TAGSOFTAGCATEGORY.' TC');
        $this->db->where_in('TC.TagCategoryID', $tag_category_ids);
        $query = $this->db->get();
        $result = $query->row();
        $tag = array();
        if (!empty($result->TagIDs)) {
            $tag_ids = $result->TagIDs;
            $tag = explode(",", $tag_ids);
        } 
        return $tag;
    }
}
?>
