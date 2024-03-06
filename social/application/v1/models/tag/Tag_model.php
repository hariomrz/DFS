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
                $tag = isset($tag['Name']) ? strtolower($tag['Name']) : "";
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
        $entity_type = strtoupper($entity_type);
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
        $question_tag_id = $this->tag_model->is_tag_exist('question', 1);
        foreach ($tags_data as $tag) {
            $tag_id = $tag['TagID'];

            if (!empty($tag_id)) {
                $entity_tag_id = $this->is_entity_tag_exist($entity_id, $entity_type, $tag_id, $forDummyUser);
                if (empty($entity_tag_id)) {
                    $tag_data = array('EntityType' => $entity_type, 'EntityID' => $entity_id, 'TagID' => $tag_id, 'UserID' => $user_id, 'CreatedDate' => $current_date, 'StatusID' => '2', 'AddedBy' => $is_front_end);
                    $this->db->insert($table_name, $tag_data);
                    $entity_tag_id = $this->db->insert_id();
                }
                $return_tag_data[] = array('TagID' => $tag_id, 'Name' => strtolower($tag['Name']), 'EntityTagID' => $entity_tag_id, 'AddedBy' => $is_front_end);
                if($question_tag_id == $tag_id) {
                    $this->mark_activity_as_question($entity_type, $entity_id);
                }

                if($entity_type == 'ACTIVITY' && $tag_id == 6) {
                    $point_data = array('EntityID' => $entity_id, 'EntityType' => 1, 'ActivityTypeID' => 43);        
                    $point_data['ActivityID'] = $entity_id;
                    $point_data['OID'] = 0;
                    $point_data['PT'] = 1;
                    initiate_worker_job('add_point', $point_data,'','point');
                }
                
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

        if (!$forDummyUser && CACHE_ENABLE && $entity_type == 'ACTIVITY') {
            $this->cache->delete('activity_' . $entity_id);
        }
        return $return_tag_data;
    }

    function mark_activity_as_question($entity_type, $entity_id) {
        if ($entity_type == 'ACTIVITY') {
            $this->db->set('PostType', 2);
            $this->db->where('ActivityID', $entity_id);
            $this->db->update(ACTIVITY);

            $user_id = get_detail_by_id($entity_id, 0, 'UserID');
            if($user_id) {
                $this->update_user_question_count($user_id) ;
            }
        }
    }

    function remove_activity_as_question($entity_type, $entity_id) {
        if ($entity_type == 'ACTIVITY') {
            $this->db->set('PostType', 1);
            $this->db->where('ActivityID', $entity_id);
            $this->db->update(ACTIVITY);

            $user_id = get_detail_by_id($entity_id, 0, 'UserID');
            if($user_id) {
                $this->update_user_question_count($user_id, -1) ;
            }

        }
    }

    function update_user_question_count($user_id, $count=1) {
        $set_field = 'QuestionCount';
        $this->db->where('UserID', $user_id);
        $this->db->where('QuestionCount >=', 0);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->update(USERDETAILS);
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
        $this->db->where('LOWER(Name)', $tag);
        $this->db->where('TagType', $tag_type, FALSE);
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
        $this->db->where('TagID', $tag_id, FALSE);
        $this->db->where('EntityID', $entity_id, FALSE);
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
            $question_tag_id = $this->tag_model->is_tag_exist('question', 1);
            if(in_array($question_tag_id, $tag_ids))  {
                $this->remove_activity_as_question($entity_type, $entity_id);
            }
            
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
           // $is_super_admin = $this->user_model->is_super_admin($user_id);
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
        $this->current_db->select('T.TagID,T.Name,T.Name as TooltipTitle, ET.AddedBy', false);
        $this->current_db->from(TAGS . ' T');
        if ($search_keyword) {
            $this->current_db->like('T.Name', $search_keyword);
        }
        
        if($forDummyUser) {
            $this->current_db->join(ENTITYTAGSDUMMY . ' ET', 'T.TagID=ET.TagID', 'left');
        } else {
            $this->current_db->join(ENTITYTAGS . ' ET', 'T.TagID=ET.TagID AND ET.StatusID=2 AND ET.EntityType IN ("'.$entity_type_cond_list.'")', 'left');
        }
         
        
        if(!empty($data['entity_type_set'])) {
            if(!empty($data['entity_type_set_val'])) 
                $this->current_db->where_in('T.TagType', [6]); //,7
            else 
                $this->current_db->where_in('T.TagType', [5]);    //$this->db->where_not_in('T.TagType', [6,7]);
        } else {
            $this->current_db->where('T.TagType', $tag_type_id);
        }
                
        //$this->current_db->where('ET.StatusID', 2);
        if (!$forDummyUser && !$is_super_admin) {
           // $this->current_db->where('ET.AddedBy', 0);
        }
        $exclude_tags = array();
        if(isset($data['ExcludeTag'])) {
            $exclude_tags = $data['ExcludeTag'];
        }
        if(ENVIRONMENT=="production") {
            $exclude_tags[] = '870';
            $exclude_tags[] = '871';            
            //$this->current_db->where_not_in('T.TagID', [870,871]);
        } else {
            $exclude_tags[] = '870';
            $exclude_tags[] = '871';              
            //$this->current_db->where_not_in('T.TagID', [263,264]);
        }
        $this->current_db->where_not_in('T.TagID', $exclude_tags);
        
        if (!empty($entity_id)) {
            $this->current_db->where('ET.EntityID', $entity_id);
        }

        if (isset($page_size) && !empty($page_size) && isset($page_no)) {
            $offset = ($page_no - 1) * $page_size;
            $this->current_db->limit($page_size, $offset);
        }
        $this->current_db->order_by("ET.Weight", "DESC");
        $this->current_db->group_by("T.TagID");
        $query = $this->current_db->get();
        //echo $this->current_db->last_query();die;
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
        $is_admin = $this->user_model->is_super_admin($user_id, 1);
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

        $this->db->where('TagCategoryID', $tag_category_id);
        $this->db->delete(TAGSOFTAGCATEGORY);

        $this->db->where('TagCategoryID', $tag_category_id);
        $this->db->delete(ENTITYTAGSCATEGORY);

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

    function get_categories_by_tag_id($tag_id, $search_keyword='') {
        $tag_categories = array();        

        $this->db->select('DISTINCT T.TagCategoryID, T.Name, TC.TagID', false);
        $this->db->select('IFNULL(T.Icon,"") as Icon', FALSE);
        $this->db->from(TAGSOFTAGCATEGORY.' TC');
        $this->db->join(TAGCATEGORY . ' T', 'T.TagCategoryID = TC.TagCategoryID');
        $this->db->where('TC.TagID', $tag_id);
        if ($search_keyword) {
            $this->db->select('(CASE WHEN Name LIKE "'.$search_keyword.'%" THEN 1 ELSE 2 END) AS sortpreference');
            $this->db->like('T.Name', $search_keyword);
            $this->db->order_by('sortpreference', 'ASC');
        } else {
            if($tag_id == 20) {
                $this->db->where_in('T.TagCategoryID', array(30, 42, 40, 25, 24, 22, 29, 21, 33, 60));
            }
            $this->db->order_by('T.DisplayOrder', 'ASC');
        }
        
        //$this->db->where('TC.NoOfActivity >', 0);
        
        
        $query = $this->db->get();
        if ($query->num_rows()) {
            $tag_categories = $query->result_array();
        }
        return $tag_categories;
    }

    /**
     * [get_tag_categories Used to get tag category]
     */
    function get_tag_categories_suggestion($tag_id, $search_keyword, $page_no=1, $page_size=20) {
        $tag_categories = array();        

        $this->db->select('DISTINCT T.TagCategoryID, T.Name, T.Name as TooltipTitle, 1 AS "AddedBy"', false);
        $this->db->from(TAGSOFTAGCATEGORY.' TC');
        $this->db->join(TAGCATEGORY . ' T', 'T.TagCategoryID = TC.TagCategoryID');
        $this->db->where('TC.TagID', $tag_id);
        $this->db->order_by('T.DisplayOrder', 'ASC');
        if ($search_keyword) {
            $this->db->like('T.Name', $search_keyword);
        }
        if (isset($page_size) && !empty($page_size) && isset($page_no)) {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            $tag_categories = $query->result_array();
        }
        return $tag_categories;
    }

    /**
     * [save_entity_tag_category Used to associated given tag category with given entity]
     * @param [array] $tag_category_list   [tag category data]
     * @param [string] $entity_type [Entity Type]
     * @param [int] $entity_id   [Entity ID]
     * @param [int] $user_id  [User ID]
     */
    public function save_entity_tag_category($tag_category_list, $entity_type, $entity_id, $user_id, $tag_id) { 
        $return_tag_data = array();
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        foreach ($tag_category_list as $tag_category) {
            $tag_category_id = $tag_category['TagCategoryID'];

            if (!empty($tag_category_id)) {
                $entity_tag_category_id = $this->is_entity_tag_category_exist($entity_id, $entity_type, $tag_category_id, $tag_id);
                if (empty($entity_tag_category_id)) {
                    $tag_category_data = array('EntityType' => strtoupper($entity_type), 'EntityID' => $entity_id, 'TagID' => $tag_id, 'TagCategoryID' => $tag_category_id, 'UserID' => $user_id, 'CreatedDate' => $current_date, 'StatusID' => '2', 'AddedBy' => 1);
                    $this->db->insert(ENTITYTAGSCATEGORY, $tag_category_data);
                    $entity_tag_category_id = $this->db->insert_id();
                    $this->update_entity_tag_category_count($tag_id, $tag_category_id);
                }
                $return_tag_data[] = array('TagCategoryID' => $tag_category_id, 'Name' => strtolower($tag_category['Name']), 'EntityTagCategoryID' => $entity_tag_category_id, 'AddedBy' => 1);
            }
        }
        if (CACHE_ENABLE && $entity_type == 'ACTIVITY') {
            $this->cache->delete('activity_' . $entity_id);
        }
        return $return_tag_data;
    }

    public function is_entity_tag_category_exist($entity_id, $entity_type, $tag_category_id, $tag_id) {
        
        $entity_tag_id = 0;

        $this->db->select('EntityTagCategoryID');
        $this->db->from(ENTITYTAGSCATEGORY);
        $this->db->where('TagCategoryID', $tag_category_id);
        $this->db->where('EntityID', $entity_id);
        $this->db->where('TagID', $tag_id);
        $this->db->where('EntityType', $entity_type);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $entity_tag_id = $query->row()->EntityTagCategoryID;
        }
        return $entity_tag_id;
    }

    /**
     * [get_entity_tag_category Used to GET entity tag category]
     * @param [string] $entity_type [Entity Type]
     * @param [int] $entity_id   [Entity ID]
     * @param [int] $tag_id  [Tag ID]
     */
    public function get_entity_tag_category($entity_type, $entity_id, $tag_id, $only_id=false) {
        if($only_id) {
            $this->current_db->select('GROUP_CONCAT(TC.TagCategoryID) as TagCategoryIDs', false);
        } else {
            $this->current_db->select('ET.EntityTagCategoryID, TC.TagCategoryID, TC.Name, TC.Name as TooltipTitle, ET.AddedBy');
        }
        
        $this->current_db->from(ENTITYTAGSCATEGORY.' ET');

        $this->current_db->join(TAGSOFTAGCATEGORY . ' TTC', "TTC.TagCategoryID = ET.TagCategoryID AND TTC.TagID=".$tag_id);
        $this->current_db->join(TAGCATEGORY . ' TC', 'TC.TagCategoryID = TTC.TagCategoryID');
        $this->current_db->where('ET.TagID', $tag_id);
        $this->current_db->where('ET.EntityID', $entity_id);
        $this->current_db->where('ET.EntityType', $entity_type);
        $query = $this->current_db->get();
        $tag_categories =array();
        if ($query->num_rows()) {
            if($only_id) {
                $row = $query->row_array();
                if(!empty($row['TagCategoryIDs'])) {
                    $tag_categories =  explode(',',$row['TagCategoryIDs']);
                }                
            } else {
                $tag_categories = $query->result_array();
            }
            
        }
        return $tag_categories;
    }

    /**
     * [update_entity_tag_category_count]
     * @param  [int]    $tag_id   [tag Id]
     * @param  [string] $tag_category_id [tag category id]
     * @param  int      $count      [Like Count increment/decrement]
     */
    function update_entity_tag_category_count($tag_id, $tag_category_id, $count = 1) {
        $set_field = "NoOfActivity";
        $table_name = TAGSOFTAGCATEGORY;
        $condition = array("TagID" => $tag_id, "TagCategoryID" => $tag_category_id);
        
        $this->db->where($condition);
        $this->db->where($set_field.' >', 0);
        $this->db->set($set_field, "$set_field+($count)", FALSE);        
        $this->db->update($table_name);
    }


    /**
     * [delete_entity_tag_category Used to delete entity tag category]
     * @param  [array]  $$tag_category_ids     [Entity Category Tag IDs]
     * @param  [int] $entity_id   [Entity ID]
     * @param  [string] $entity_type [Entity Type]     
     */
    public function delete_entity_tag_category($tag_category_ids, $entity_id, $entity_type, $tag_id) { 
        foreach ($tag_category_ids as $tag_category_id) {       
            $this->db->where('EntityID', $entity_id);
            $this->db->where('EntityType', $entity_type);
            $this->db->where('TagID', $tag_id);
            $this->db->where('TagCategoryID', $tag_category_id);            
            $this->db->delete(ENTITYTAGSCATEGORY); 
            
            $this->update_entity_tag_category_count($tag_id, $tag_category_id, -1);
        }
        if (CACHE_ENABLE && $entity_type == 'ACTIVITY') {
            $this->cache->delete('activity_' . $entity_id);
        }
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

       /**
     * [To get list of trending tags]
     * @param [array] $data   [posted data]
     * @return [array]      [tags result]
     */
    public function get_trending_tags($data) {         
        $ward_ids[] = $ward_id = $data['WID'];
        $only_tag_id = safe_array_key($data, 'OnlyID', 0);
        if(!in_array(1, $ward_ids)) {
            $ward_ids[] = 1;
        }  
        
        $tags = array();
        if (CACHE_ENABLE && empty($only_tag_id)) {
            $tags = $this->cache->get('ward_tags_'.$ward_id);            
        }
        if(empty($tags)) {
            $exclude_tag_ids = $this->get_top_tags_id();
       
            $this->db->select('T.TagID, T.Name, WT.WardID, WT.WardTrendingTagID as TID', false);
            $this->db->select('IFNULL(T.DisplayName,"") AS DisplayName', FALSE);
            $this->db->select('IFNULL(T.Description,"") AS Description', FALSE);
            $this->db->select('IFNULL(T.Icon,"") AS Icon', FALSE);
            $this->db->select('IFNULL(T.Banner,"") AS Banner', FALSE);

            $this->db->from(WARDTRENDINGTAGS . ' WT');
            $this->db->join(TAGS . ' T', 'T.TagID = WT.TagID');
            $this->db->where_in('WT.WardID', $ward_ids);
            $this->db->where_not_in('T.TagID', $exclude_tag_ids);
            $this->db->order_by('WT.DisplayOrder', 'ASC');       
            
            $query = $this->db->get();
            $tags_result = $query->result_array();

            if($only_tag_id) {
                $tag_ids = [0];
                foreach ($tags_result as $tag){
                    $tag_ids[] = $tag['TagID'];
                }            
                return $tag_ids;
            }
            
            foreach ($tags_result as $tag){
                if($tag['Name'] == 'featured') {
                    $tag['Name'] = 'Top Posts';
                }
                $tag['Name'] = ucfirst(strtolower($tag['Name']));
                $tags[] = $tag;
            }

            if (CACHE_ENABLE) {
                $this->cache->save('ward_tags_'.$ward_id, $tags);
            }
            if(!empty($tags)) {
                initiate_worker_job('upload_api_data_on_bucket', array('FileName' => 'ward_tags_'.$ward_id.'.json', "FileData" => $tags));
            }
        }
        return $tags;
    }

    /**
     * [To get list of non trending tags]
     * @param [array] $data   [posted data]
     * @return [array]      [tags result]
     */
    public function get_non_trending_tags($data) {
        $ward_ids[] = $data['WID'];
        if($data['WID'] == 1) {
            return array();
        }
        $data['OnlyID'] = 1;
        $trending_tag_ids = $this->get_trending_tags($data);
        $exclude_tag_ids = $this->get_top_tags_id();
        $trending_tag_ids = array_merge($trending_tag_ids, $exclude_tag_ids);
        
        if(!in_array(1, $ward_ids)) {
            $ward_ids[] = 1;
        }
        $ward_ids_str = implode(',', $ward_ids);
        $ward_ids_str = trim($ward_ids_str, ',');

        $entity_type = 'ACTIVITY';
        $this->db->select('T.TagID, T.Name', false);
        $this->db->from(ENTITYTAGS . ' ET');
        $this->db->join(TAGS . ' T', 'T.TagID = ET.TagID');
        $this->db->join(ACTIVITYWARD . ' AW', "AW.ActivityID=ET.EntityID AND AW.WardID IN(" . $ward_ids_str . ")");        
        $this->db->where('ET.StatusID', 2);
        $this->db->where('ET.EntityType', $entity_type);
        $this->db->where_not_in('ET.TagID', $trending_tag_ids);
        
        $this->db->group_by('T.TagID');
        $this->db->order_by('T.Name ASC');  
                        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        return $query->result_array();
    }

    function get_top_tags_id() {
        if(ENVIRONMENT=="production") {
            $tag_ids = array(6,20,573,870,871,872,873);
        } else {
            $tag_ids = array(6,20,573,870,871,872,873);
        }
        return $tag_ids;
    }
    function get_top_tags() {
        if(ENVIRONMENT=="production") {
            $tag_data = array(
                array(
                    'Name' => 'Question and Answers',
                    'TagID' => '870',
                    'Image' => 'q_answers.png',
                    'ShowOnNewsfeed' => 1,
                    'Icon' => 'q_answers_ic.png',
                    'Description' => '',
                    'Dicon' => '',
                    'IsBig' => 0,
                    'DisplayName' => '',
                    'Description' => '',
                    'Banner' => '',
                    'TagCategory' => $this->question_top_tags(20)
                ),
                array(
                    'Name' => 'Unanswered Questions',
                    'TagID' => '871',
                    'Image' => 'unaswered_question.png',
                    'ShowOnNewsfeed' => 1,
                    'Icon' => 'unaswered_question_ic.png',
                    'Description' => '',
                    'Dicon' => '',
                    'IsBig' => 0,
                    'DisplayName' => '',
                    'Description' => '',
                    'Banner' => '',
                    'TagCategory' => array()
                ),
                array(
                    'Name' => 'Classified',
                    'TagID' => '6',
                    'Image' => 'classified.png',
                    'ShowOnNewsfeed' => 1,
                    'Icon' => 'classified_ic.png',
                    'Description' => 'Buy, sell, rent, jobs nearby or for entire city',
                    'Dicon' => '',
                    'IsBig' => 1,
                    'DisplayName' => '',
                    'Description' => '',
                    'Banner' => 'classified_b.png',
                    'TagCategory' => $this->get_categories_by_tag_id(6)
                )            
            );
        } else {
            $tag_data = array(
                array(
                    'Name' => 'Question and Answers',
                    'TagID' => '870',
                    'Image' => 'q_answers.png',
                    'ShowOnNewsfeed' => 1,
                    'Icon' => 'q_answers_ic.png',
                    'Description' => '',
                    'Dicon' => '',
                    'IsBig' => 0,
                    'DisplayName' => '',
                    'Description' => '',
                    'Banner' => '',
                    'TagCategory' => $this->question_top_tags(20)
                ),
                array(
                    'Name' => 'Unanswered Questions',
                    'TagID' => '871',
                    'Image' => 'unaswered_question.png',
                    'ShowOnNewsfeed' => 1,
                    'Icon' => 'unaswered_question_ic.png',
                    'Description' => '',
                    'Dicon' => '',
                    'IsBig' => 0,
                    'DisplayName' => '',
                    'Description' => '',
                    'Banner' => '',
                    'TagCategory' => array()
                ),
                array(
                    'Name' => 'Classified',
                    'TagID' => '6',
                    'Image' => 'classified.png',
                    'ShowOnNewsfeed' => 1,
                    'Icon' => 'classified_ic.png',
                    'Description' => 'Buy, sell, rent, jobs nearby or for entire city',
                    'Dicon' => '',
                    'IsBig' => 1,
                    'DisplayName' => '',
                    'Description' => '',
                    'Banner' => 'classified_b.png',
                    'TagCategory' => $this->get_categories_by_tag_id(6)
                )            
            );
        }
        return $tag_data;
    }

    /**
     * [To save ward trending tags]
     * @param [int] $tag_id   [tag id]
     * @param [array] $ward_ids   [ward ids]
     */
    function save_ward_trending_tag($tag_id, $ward_ids, $edit_id=0) {
        
        if(in_array(1, $ward_ids)) {
            $ward_ids = array(1);
        
            $this->db->select('WardID');
            $this->db->from(WARDTRENDINGTAGS);
            $this->db->where('TagID', $tag_id);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $this->db->where('TagID', $tag_id);
                $this->db->delete(WARDTRENDINGTAGS);
                foreach ($query->result_array() as $row) {
                    $edit_ward_id = $row['WardID'];
                    $this->delete_cache_data($edit_ward_id);
                }
            }
        } else if(!empty($edit_id)) {
            $this->db->select('WardID');
            $this->db->from(WARDTRENDINGTAGS);
            $this->db->where('WardTrendingTagID', $edit_id);
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows()) {
                $edit_ward_id = $query->row()->WardID;

                $this->db->where('WardTrendingTagID', $edit_id);
                $this->db->delete(WARDTRENDINGTAGS);

                $this->delete_cache_data($edit_ward_id, TRUE);                
            }
        }

        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');
        if (!empty($ward_ids)) {            
            
            $this->db->where_in('WardID', $ward_ids);
            $this->db->where('TagID', $tag_id);
            $this->db->delete(WARDTRENDINGTAGS);
        
            foreach ($ward_ids as $ward_id) {
                $tag_ward = array();
                $tag_ward['TagID'] = $tag_id;
                $tag_ward['WardID'] = $ward_id;
                $tag_ward['CreatedDate'] = $current_date;
                
                $display_order = 0;
                $get_tag_order = $this->get_single_row('MAX(DisplayOrder) as DisplayOrder', WARDTRENDINGTAGS, array('WardID' => $ward_id));
                if (isset($get_tag_order['DisplayOrder'])) {
                    $display_order = $get_tag_order['DisplayOrder'];
                }
                $tag_ward['DisplayOrder'] = $display_order + 1;
                $this->db->insert(WARDTRENDINGTAGS, $tag_ward);
                $this->delete_cache_data($ward_id);
            }
        }
        
    }

    function delete_cache_data($ward_id, $falg=FALSE) {
        if($ward_id == 1 && $falg) {
            $this->load->model(array('ward/ward_model'));
            $ward_list = $this->ward_model->get_ward_list();
            foreach ($ward_list as $ward) {
                $ward_id = $ward['WID'];
                $this->delete_api_static_file('ward_tags_'.$ward_id);
                if (CACHE_ENABLE) {
                    $this->cache->delete('ward_tags_'.$ward_id);
                }
            }
        } else {
            $this->delete_api_static_file('ward_tags_'.$ward_id);
            if (CACHE_ENABLE) {
                $this->cache->delete('ward_tags_'.$ward_id);
            }
        }        
    }

    function remove_tag_ward_visibility($ward_trending_tag_id) {

        $this->db->select('WardID');
        $this->db->from(WARDTRENDINGTAGS);
        $this->db->where('WardTrendingTagID', $ward_trending_tag_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $ward_id = $query->row()->WardID;

            $this->db->where('WardTrendingTagID', $ward_trending_tag_id);
            $this->db->delete(WARDTRENDINGTAGS);

            $this->delete_api_static_file('ward_tags_'.$ward_id);
            if (CACHE_ENABLE) {
                $this->cache->delete('ward_tags_'.$ward_id);
            }
        }       
    }
    
    /**
     * [change_ward_trending_tag_order Used to change display order for tag]
     * @param [array] $order_data   [tag display order data]
     */
    function change_ward_trending_tag_order($order_data) {
        $orderValue = array();
        foreach ($order_data as $items) {
            $itemArray = array();
            $itemArray['WardTrendingTagID'] = $items['TID'];
            //$itemArray['TagID'] = $items['TagID'];
            //$itemArray['WardID'] = $items['WardID'];
            $itemArray['DisplayOrder'] = $items['DisplayOrder'];
            $orderValue[] = $itemArray;
        }
        if(!empty($orderValue)) {
            $this->db->update_batch(WARDTRENDINGTAGS, $orderValue, 'WardTrendingTagID');
        }
        return true;
    }

    /**
     * [get_activity_tags uses to get entity tags]
     * @param  [int]    	$tag_type_id [tag type in master(default :1)]
     * @param  [string]    	$entity_type [Type of Entity(LINK|ACTIVITY)]
     * @param  [int]    	$entity_id [entity ID]
     * @return [array]      [tags result]
     */
    public function get_activity_tags($tag_type_id = 1, $entity_type = 'ACTIVITY', $entity_id) {
        $tags = array();
        
        if (CACHE_ENABLE && !empty($entity_id) && $tag_type_id == 1 && $entity_type == 'ACTIVITY') {
            $activity_cache = $this->cache->get('activity_' . $entity_id);
            if (!empty($activity_cache)) {
                if (isset($activity_cache['t'])) {
                    return $activity_cache['t'];
                }
            }
        }
        $entity_type_cond = array($entity_type);
       
        $entity_type_cond_list = implode(",", $entity_type_cond);
        $this->current_db->select('DISTINCT T.TagID, T.Name,T.Name as TooltipTitle, ET.AddedBy', false);
        $this->current_db->select('IFNULL(T.DisplayName,"") AS DisplayName', FALSE);
        $this->current_db->select('IFNULL(T.Description,"") AS Description', FALSE);
        $this->current_db->select('IFNULL(T.Icon,"") AS Icon', FALSE);
        $this->current_db->select('IFNULL(T.Banner,"") AS Banner', FALSE);
        $this->current_db->from(TAGS . ' T');        
        $this->current_db->join(ENTITYTAGS . ' ET', 'T.TagID=ET.TagID AND ET.StatusID=2 AND ET.EntityType = "'.$entity_type.'" AND ET.EntityID='.$entity_id);
        $this->current_db->where('T.TagType', $tag_type_id);
       
        $include_tag_category = array(20);
        $exclude_tags = array();        
        if(ENVIRONMENT=="production") {
            $exclude_tags[] = '870';
            $exclude_tags[] = '871';
            $exclude_tags[] = '20';
        } else {
            $exclude_tags[] = '263';
            $exclude_tags[] = '264';
            $exclude_tags[] = '20';
        }
        $this->current_db->where_not_in('T.TagID', $exclude_tags);        
        $this->current_db->order_by("ET.Weight", "DESC");
        $query = $this->current_db->get();
        //echo $this->current_db->last_query();die;
        if ($query->num_rows()) {
            $tags = $query->result_array();
            foreach ($tags as $key => $value) {
                $tags[$key]['Category'] = array();
                /* if(in_array($value['TagID'], $include_tag_category)) {
                    $tags[$key]['Category'] = $this->get_entity_tag_category($entity_type, $entity_id, $value['TagID']);
                }
                */
            }
        }
        return $tags;
    }

    function get_tag_list($tag_type_id = 1, $entity_type = 'ACTIVITY', $page_no=1, $page_size=20) {
        $this->db->select('COUNT(T.TagID) TagCount, T.TagID, T.Name', false);
        $this->db->from(TAGS . ' T');
        $this->db->join(ENTITYTAGS . ' ET', "ET.TagID = T.TagID AND ET.EntityType = '$entity_type' AND ET.StatusID=2");
        $this->db->join(ACTIVITY . ' A', 'A.ActivityID = ET.EntityID AND A.StatusID=2');
        //$this->db->where('ET.EntityType', $entity_type);
        $this->db->where('T.TagType', $tag_type_id);
        $this->db->where('A.StatusID', 2);
        $exclude_tag_ids = array(20, 573, 870, 871, 872, 873, 34, 71, 76, 240, 817, 903, 960, 985, 986, 987, 1116, 1131, 1133, 1144, 1360, 1366, 1374, 1376, 1384, 1462);
        if(!empty($exclude_tag_ids)) {
            $this->db->where_not_in('T.TagID', $exclude_tag_ids);  
        }
        
        
        if (isset($page_size) && !empty($page_size) && isset($page_no)) {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        
             
        
        $this->db->group_by('T.TagID');
        $this->db->order_by('TagCount DESC');
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $tags = $query->result_array();
        return $tags;
    }

    /**
     * Used to get question top tags
     */
    function question_top_tags($tag_id=20) {
        if(API_VERSION != "v4"){
            return $this->get_categories_by_tag_id(20);
        } else {
            $this->db->select('TagID,  Name');
            //$this->db->select('0 as TagCategoryID', false);
            $this->db->select('IFNULL(Icon,"") as Icon', FALSE);
            
            $this->db->select('IFNULL(DisplayName,"") AS DisplayName', FALSE);
            $this->db->select('IFNULL(Description,"") AS Description', FALSE);
            $this->db->select('IFNULL(Icon,"") AS Icon', FALSE);
            $this->db->select('IFNULL(Banner,"") AS Banner', FALSE);
            $this->db->from(TAGS);

            if($tag_id == 20) {
                $this->db->where_in('TagID', array(34, 71, 76, 240, 817, 903, 960, 985, 986, 987, 1116, 1131, 1133, 1144, 1360, 1366, 1374, 1376, 1384, 1462));
            }
            $this->db->order_by('DisplayOrder', 'ASC');
            $query = $this->db->get();
            $tags = array();
            if ($query->num_rows()) {
                $tags = $query->result_array();
            }
            return $tags;
        }        
    }

    /**
     * Used to get top prefered tags
     */
    function top_preferred_tags($tag_id=20) {
        $this->db->select('TagID,  Name');
        //$this->db->select('0 as TagCategoryID', false);
        
        $this->db->select('IFNULL(DisplayName,"") AS DisplayName', FALSE);
        $this->db->select('IFNULL(Description,"") AS Description', FALSE);
        $this->db->select('IFNULL(Icon,"") AS Icon', FALSE);
        $this->db->select('IFNULL(Banner,"") AS Banner', FALSE);
        $this->db->from(TAGS);

        if($tag_id == 20) {
            $this->db->where_in('TagID', array(71, 76, 240, 960, 985, 986, 987, 1116, 1131, 1360));
        }
        $this->db->order_by('DisplayOrder', 'ASC');
        $query = $this->db->get();
        $tags = array();
        if ($query->num_rows()) {
            $tags = $query->result_array();
        }
        return $tags;               
    }

    /**
     * Used to get tag details
     */
    function details($tag_id) {
        $this->db->select('TagID, Name');
        $this->db->select('IFNULL(DisplayName,"") AS DisplayName', FALSE);
        $this->db->select('IFNULL(Description,"") AS Description', FALSE);
        $this->db->select('IFNULL(Icon,"") AS Icon', FALSE);
        $this->db->select('IFNULL(Banner,"") AS Banner', FALSE);
        $this->db->from(TAGS);
        $this->db->where('TagID', $tag_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get();
        $tag = array();
        if ($query->num_rows()) {
            $tag = $query->row_array();
        }
        return $tag;
    }

    
    function sync_tag_category_as_tag($category_tag_id=20) {
        $this->db->select('TC.Name, ETC.EntityType, ETC.EntityID, ETC.TagCategoryID, ETC.CreatedDate');
        $this->db->select('IFNULL(TC.Icon,"") as Icon', FALSE);
        $this->db->from(ENTITYTAGSCATEGORY. ' ETC');
        $this->db->join(TAGCATEGORY . ' TC', "TC.TagCategoryID = ETC.TagCategoryID");
        $this->db->where('ETC.TagID', $category_tag_id, FALSE);
        $this->db->where('ETC.StatusID', 2);
        $this->db->order_by('ETC.EntityID','ASC');
        $query = $this->db->get();
        $tag = array();
        if ($query->num_rows()) {
            $entity_tags_category = $query->result_array();
            foreach ($entity_tags_category as $key => $value) {
                $name = trim($value['Name']);
                $activity_id = $value['EntityID'];
                $entity_type = $value['EntityType'];
                $icon = $value['Icon'];
                $tag_category_id = $value['TagCategoryID'];
                $entity_tag_category_created_date = $value['CreatedDate'];

                //check tag category name exit in tag table or not
                $this->db->select('TagID');
                $this->db->from(TAGS);
                $this->db->where('LOWER(Name)', strtolower($name),NULL,FALSE);
                $this->db->where('TagType', 1);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    $tag_data = $query->row_array();
                    $tag_id = $tag_data['TagID'];
                } else {
                    $tag_data = array('Name' => $name, 'Icon' => $icon, 'TagType' => 1, 'CreatedBy' => 1, 'ModifiedBy' => 1, 'CreatedDate' => $entity_tag_category_created_date, 'ModifiedDate' => $entity_tag_category_created_date);
                    $this->db->insert(TAGS, $tag_data);
                    $tag_id = $this->db->insert_id();
                }

                //check tag_id assign to activity_id or not
                $this->db->select('TagID');
                $this->db->from(ENTITYTAGS);
                $this->db->where('EntityType', $entity_type);
                $this->db->where('EntityID', $activity_id, FALSE);
                $this->db->where('TagID', $tag_id, FALSE);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    $tag_data = $query->row_array();
                    $tag_id = $tag_data['TagID'];
                } else {
                    $tag_data = array('EntityType' => $entity_type, 'EntityID' => $activity_id, 'TagID' => $tag_id, 'UserID' => 1, 'CreatedDate' => $entity_tag_category_created_date, 'StatusID' => '2', 'AddedBy' => 1);
                    $this->db->insert(ENTITYTAGS, $tag_data);
                }

                
                $this->db->where('TagCategoryID', $tag_category_id);
                $this->db->where('TagID', 0);
                $this->db->update(USERTAGCATEGORY, array('TagID' => $tag_id));
        
            }
        }
    }
}
?>
