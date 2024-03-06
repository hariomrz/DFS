<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Skills_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }


    /**
     * [Used to get skill list]
     * @param  [int] $module_id       [module id]
     * @param  [int] $module_entity_id     [module entity id]
     * @param  [int] $search_keyword     [search keyword]
     * @return [array]
     */
    function get_skills($module_id, $module_entity_id, $search_keyword='') {   
        $this->db->select('C.Name, C.CategoryID');
        $this->db->select('IFNULL(C.Icon,"") as Icon', FALSE);
        $this->db->from(CATEGORYMASTER . ' AS C');
        $this->db->where('C.StatusID', 2);
        $this->db->where('C.ModuleID', 29);
        $this->db->where('C.ParentID', 0);
        $this->db->order_by('C.Name', 'ASC');
        $query = $this->db->get();
        $data = array();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                $skills = $this->get_category_skills($result['CategoryID'], $module_id, $module_entity_id);
                if(count($skills) > 0)  {
                    $result['Skills'] = $skills;
                    $data[] = $result;
                }                
            }            
        }        
        return $data;
    }


    /**
     * [get_category_skills]
     * @param  [int] $category_id    [Category ID]
     * @return [array]               [skills]
     */
    function get_category_skills($category_id, $module_id, $module_entity_id) {
        $this->db->select('S.SkillID, S.Name');
        $this->db->select('IF(S.MediaID="" || S.MediaID IS NULL || S.MediaID=0,"",MD.ImageName) as Icon', FALSE);
        
        $this->db->from(ENTITYCATEGORY . " as SC");
        $this->db->join(SKILLSMASTER . ' S', 'S.SkillID=SC.ModuleEntityID AND S.StatusID=2');
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = S.MediaID', 'LEFT');
        $this->db->where('SC.CategoryID', $category_id, FALSE);
        $this->db->where('SC.ModuleID', 29);
        $this->db->order_by('S.Name', 'ASC');
        $query = $this->db->get();
        // echo $this->db->last_query();die;
        $data = array();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $result) {
                $result['IsAdded'] = $this->is_skill_added($result['SkillID'], $module_id, $module_entity_id);                              
                $data[] = $result;
            }
        }
        return $data;
    }

    /**
     * Used to check given skill added  or not on user profile
     */
    function is_skill_added($skill_id, $module_id, $module_entity_id) {
        $this->db->select("IF(ES.SkillID is not NULL,1,0) as IsAdded",false);
        $this->db->from(ENTITYSKILLS . ' ES');
        $this->db->join(SKILLSMASTER . ' SM', 'SM.SkillID=ES.SkillID and SM.StatusID=2');
        $this->db->where('ES.ModuleID', $module_id);
        $this->db->where('ES.ModuleEntityID', $module_entity_id, FALSE);
        $this->db->where('ES.StatusID', 2);
        $this->db->where('ES.SkillID', $skill_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get(); 
        $data = $query->row_array();
        if(!empty($data)) {
            return $data['IsAdded'];
        } else {
            return 0;
        }
    }

    /**
     * [save Used to insert/update any module entity skills]
     * @param  [array] $skills        [array of skills]
     * @param  [int] $module_id       [module id]
     * @param  [int] $module_entity_id     [module entity id]
     * @return [bool]                     [true/false]
     */
    function save($skills, $module_id, $module_entity_id) {      

        if (count($skills) > 0 && !empty($skills) && is_array($skills)) {
            $skill_order = 0;
            $get_skill_order = get_data('MAX(DisplayOrder) as DisplayOrder', ENTITYSKILLS, array('ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id), '1', '');
            if ($get_skill_order) {
                if ($get_skill_order->DisplayOrder > 0) {
                    $skill_order = $get_skill_order->DisplayOrder;
                }
            }

            $skills_list = array();
            $insert_data = array();
            $update_data = array();

            $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');
            foreach($skills as $key=>$skill_id) {
                $this->db->select("EntitySkillID, StatusID");
                $this->db->from(ENTITYSKILLS);
                $this->db->where(array('SkillID' => $skill_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id));
                $query = $this->db->get();

                $skills_list[] = $skill_id;
 
                if( $query->num_rows() == 0 ) {     
                    $skill_order = $skill_order + 1;
                    $insert_data[$key]['SkillID'] = $skill_id;
                    $insert_data[$key]['EntitySkillGUID'] = get_guid();
                    $insert_data[$key]['ModuleEntityID'] = $module_entity_id;
                    $insert_data[$key]['ModuleID'] = $module_id;
                    $insert_data[$key]['DisplayOrder'] = $skill_order;
                    $insert_data[$key]['CreatedDate'] = $current_date_time;
                    $insert_data[$key]['ModifiedDate'] = $current_date_time;

                    $this->update_skill_profile_count($skill_id);
                } else {
                    $data = $query->row_array();
                    if($data['StatusID'] == 3) {
                        $u = array('SkillID'=>$skill_id,'ModuleEntityID'=>$module_entity_id,'ModuleID'=>$module_id,'StatusID'=>2,'ModifiedDate'=> $current_date_time);
                        $update_data[] = $u; 
                    }
                }        
            } 
               
            if(!empty($insert_data)) {
            	$this->db->insert_batch(ENTITYSKILLS, $insert_data);
            }

            if(!empty($update_data)) {
            	$this->db->insert_on_duplicate_update_batch(ENTITYSKILLS, $update_data);
            }
            
            if(count($skills_list) > 0) {
                $this->db->select("SkillID as ID");
                $this->db->where(array('ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'StatusID' => 2));
                $this->db->where_not_in('SkillID', $skills_list);
                $query = $this->db->get(ENTITYSKILLS);
                if ($query->num_rows()) {
                    $skills = $query->result_array();
                    $this->delete_user_skills($skills, $module_id, $module_entity_id);
                }
            }
        } else  {   // if NO Interest ID SUBMITTED THEN DELETE ALL THE EXISTING Interest FOR GIVEN USER ID
            $this->db->select("SkillID as ID");
            $this->db->where(array('ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'StatusID' => 2));
            $query = $this->db->get(ENTITYSKILLS);
            if ($query->num_rows()) {
                $skills = $query->result_array();
                $this->delete_user_skills($skills, $module_id, $module_entity_id);
            } 
        }
        if($module_id == 3) {
            initiate_worker_job('profile_compete_status', array('UserID' => $module_entity_id),'','profile_compete_status');
        }        
    }


    /**
     * [delete_user_skills Used to delete skill for an entity]
     * @param  [array] $skills         [Array of skills to be deleted]
     * @param  [int] $module_id        [Module ID]
     * @param  [int] $module_entity_id [Module Entity ID]
     * @return [boolean]               [true]
     */
    function delete_user_skills($skills, $module_id, $module_entity_id) {
        if (count($skills) > 0 && !empty($skills) && is_array($skills)) {
            //$entity_skills = array();
            $skills_list = array();
            foreach ($skills as $key => $skill) {
                $skill_id = $skill['ID'];
                $skills_list[] = $skill_id;

                $this->db->select("EntitySkillID");
                $this->db->from(ENTITYSKILLS);
                $this->db->where(array('SkillID' => $skill_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id));
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows()) {
                    $row = $query->row();
                    //$entity_skills[] = $row->EntitySkillID;
                    $this->update_skill_profile_count($skill_id, -1);
                }
            } // foreach Close
            if (count($skills_list) > 0) { //DELETE SKILLS
                $this->db->where(array('ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id));
                $this->db->where_in('SkillID', $skills_list);

                $this->db->set('StatusID', 3);
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));

                $this->db->update(ENTITYSKILLS);
            }
            /* if (count($entity_skills) > 0) { //DELETE ENDORESMENT            
                $this->db->where_in('EntitySkillID', $entity_skills);
                $this->db->delete(ENTITYENDORSEMENT);
            } */
            return true;
        }
    }

    /**
     * [update_skill_profile_count Used to update skill profile count]
     * @param  [int]    $skill_id   [Skill Id]
     * @param  [int]    $count      [increment/decrement]
     * @return [type]               [description]
     */
    public function update_skill_profile_count($skill_id, $count = 1) {
        $set_field = "NoOfProfiles";
        $this->db->where('SkillID', $skill_id, FALSE);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->update(SKILLSMASTER);
    }

    /**
     * [details Used to get module entity skills]
     * @param  [int] $module_id                 [module id]
     * @param  [int] $module_entity_id          [module entity id]
     * @param  [string] $count_flag             [count flag]
     * @param  [int] $page_no                   [page number]
     * @param  [int] $page_size                 [page size]
     * @param  [int] $is_owner                  [logged in user is owner of visited profile or not]
     * @param  [int] $visitor_module_id         [visitor module id]
     * @param  [int] $visitor_module_entity_id  [visitor module entity id]
     * @param  [int] $filter                    [filter flag for skills]
     * @return [array/int]                      [skills list/count]
     */
    function details($module_id, $module_entity_id, $count_flag = FALSE, $page_no = '', $page_size = '', $is_owner = TRUE, $visitor_module_id = '', $visitor_module_entity_id = '', $filter = 0, $ignore_entity_skill_guid = '') {
        $this->db->select('ES.EntitySkillID, ES.TotalEndorsement, S.Name, 0 as IsEndorse');
        $this->db->select('IF(S.MediaID="" || S.MediaID IS NULL || S.MediaID=0,"",MD.ImageName) as Icon', FALSE);
        $this->db->from(ENTITYSKILLS . " ES");
        $this->db->join(SKILLSMASTER . " S", "S.SkillID = ES.SkillID AND S.StatusID=2");
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = S.MediaID', 'LEFT');

        $this->db->where('ES.ModuleID', $module_id, FALSE);
        $this->db->where('ES.ModuleEntityID', $module_entity_id, FALSE);
        $this->db->where('ES.StatusID', 2);

        /* if (!$is_owner) {
            $this->db->where('S.StatusID', 2);
        } else {
            $this->db->where_in('S.StatusID', array(1, 2));
        }

        if ($filter == 2) {
            $this->db->where('ES.StatusID', 1);
        } else {
            $this->db->where('ES.StatusID', 2);
        }
        */

        if ($ignore_entity_skill_guid)
        {
            $this->db->where_not_in('ES.EntitySkillGUID', $ignore_entity_skill_guid);
        }

        $this->db->order_by('ES.DisplayOrder', 'ASC');
        if (empty($count_flag)) { // check if array needed        
            if (!empty($page_size)) { // Check for pagination            
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }
            $query = $this->db->get();

            $response = array();
            if ($query->num_rows()) {
                if($is_owner) {
                    $response = $query->result_array();
                } else {
                    foreach ($query->result_array() as $result) {
                        $entity_skill_id = $result['EntitySkillID'];

                        /* $categories = $this->get_skill_categories($result['SkillID']);
                        $result['CategoryName'] = $categories['CategoryName'];
                        $result['SubCategoryName'] = $categories['SubCategoryName'];
                        $result['CategoryImageName'] = '';
                        if (empty($result['SkillImageName'])) {
                            $result['CategoryImageName'] = $categories['CategoryIcon'];
                        }
                        */
                        
                        if (!empty($visitor_module_id) && !empty($visitor_module_entity_id)) {
                            $result['IsEndorse'] = $this->is_endorse($entity_skill_id, $visitor_module_id, $visitor_module_entity_id);
                        }
                        //$result['Endorsements'] = $this->endorsement_list($entity_skill_id, FALSE, 1, 5, $visitor_module_id, $visitor_module_entity_id);

                        $response[] = $result;
                    }
                }
            }
            return $response;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**
     * [save_endorsement]
     * @param  [int] $user_id
     * @param  [int] $visitor_module_id
     * @param  [int] $visitor_module_entity_id
     * @param  [array] $entity_skill_id
     */
    function save_endorsement($user_id, $visitor_module_id, $visitor_module_entity_id, $entity_skill_id) {
        $entity_skills = $this->get_entity_skill_id($entity_skill_id);
        if(!empty($entity_skills)) {
            if ($this->is_endorse($entity_skill_id, $visitor_module_id, $visitor_module_entity_id)) {
                return true;
            }
            
            $notification_type_id = $entity_skills['NotificationTypeID'];
            $skill_id = $entity_skills['SkillID'];
            $module_id = $entity_skills['ModuleID'];
            $module_entity_id = $entity_skills['ModuleEntityID'];

            $this->db->insert(ENTITYENDORSEMENT, array('EntitySkillID' => $entity_skill_id, 'ModuleEntityID' => $visitor_module_entity_id, 'ModuleID' => $visitor_module_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
            $this->update_endorsement_count($entity_skill_id);

            if ($visitor_module_id == 3) {
                $parameters[0]['Type'] = 'User';
            } else if ($visitor_module_id == 1) {
                $parameters[0]['Type'] = 'Group';
            } else if ($visitor_module_id == 14) {
                $parameters[0]['Type'] = 'Event';
            } else if ($visitor_module_id == 18) {
                $parameters[0]['Type'] = 'Page';
            }
            $parameters[0]['ReferenceID'] = $visitor_module_entity_id;
            $parameters[1]['ReferenceID'] = $skill_id;
            $parameters[1]['Type'] = 'Skills';

            $receiver_ids = $this->get_entity_admins($module_id, $module_entity_id);
                            
            initiate_worker_job('add_notification', array('NotificationTypeID' => $notification_type_id, 'SenderID' => $user_id, 'ReceiverIDs' => $receiver_ids, 'RefrenceID' => $user_id, 'Parameters' => $parameters, 'ExtraParams' => array()),'','notification');

           /* $existing_temp_data = array();
            $existing_temp_skill = array();
            $existing_temp_skill[] = $skill_id;
            $existing_temp_data[] = array('skill_id' => $skill_id, 'notification_type_id' => $notification_type_id);
            $this->endorsement_notification($existing_temp_data, $existing_temp_skill, $user_id, $module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id);
            */
        }
    }    

    /**
     * [is_endorse description]
     * @param  [type]  $entity_skill_id          [entity skill id]
     * @param  [string] $visitor_module_id      [visitor module id]
     * @param  [string] $visitor_module_entity_id      [visitor module entity id]
     * @return boolean                           [description]
     */
    function is_endorse($entity_skill_id, $visitor_module_id, $visitor_module_entity_id)
    {
        $this->db->select("ModuleEntityID");
        $this->db->from(ENTITYENDORSEMENT);
        $this->db->where('EntitySkillID', $entity_skill_id, FALSE);
        $this->db->where('ModuleID', $visitor_module_id, FALSE);
        $this->db->where('ModuleEntityID', $visitor_module_entity_id, FALSE);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return 1;
        }
        return 0;
    }

    /**
     * [get_entity_skill_id]
     * @param  [int] $entity_skill_id
     */
    function get_entity_skill_id($entity_skill_id) {
        $notification_type_id_new = array('3' => 100, '1' => 101, '18' => 102);
        $notification_type_id_existing = array('3' => 97, '1' => 98, '18' => 99);
        $this->db->select('SkillID, ModuleID, ModuleEntityID');
        $this->db->from(ENTITYSKILLS);
        $this->db->where('EntitySkillID', $entity_skill_id, FALSE);
        $this->db->where('StatusID', 2);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return array('SkillID' => $row->SkillID, 'ModuleID' => $row->ModuleID, 'ModuleEntityID' => $row->ModuleEntityID, 'EntitySkillID' => $entity_skill_id, 'NotificationTypeID' => $notification_type_id_existing[$row->ModuleID], 'Type' => 'existing');
        } else {
            return array();
            //$this->db->insert(ENTITYSKILLS, array('EntitySkillGUID' => get_guid(), 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'SkillID' => $skill_id, 'TotalEndorsement' => 0, 'StatusID' => 1, 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
            //return array('EntitySkillID' => $this->db->insert_id(), 'NotificationTypeID' => $notification_type_id_new[$module_id], 'Type' => 'new');
        }
    }    

    function get_entity_admins($module_id, $module_entity_id) {
        if ($module_id == 1) {
            $this->load->model(array('group/group_model'));
            return $this->group_model->get_all_group_admins($module_entity_id);
        } else if ($module_id == 3) {
            return array($module_entity_id);
        } else if ($module_id == 18) {
            $this->load->model(array('pages/page_model'));
            return $this->page_model->get_all_admins($module_entity_id);
        }
    }

    /**
     * [delete_endorsement description]
     * @param  [int] $entity_skill_id              [description]
     * @param  [int] $visitor_module_id        [description]
     * @param  [int] $visitor_module_entity_id [description]     
     * @return [type]                        [description]
     */
    function delete_endorsement($entity_skill_id, $visitor_module_id, $visitor_module_entity_id) {
        $skills_list = array();
        if ($entity_skill_id) {
            $this->db->select("EndorsementID");
            $this->db->from(ENTITYENDORSEMENT);
            $this->db->where('EntitySkillID', $entity_skill_id, FALSE);
            $this->db->where('ModuleID', $visitor_module_id, FALSE);
            $this->db->where('ModuleEntityID', $visitor_module_entity_id, FALSE);                           
            $this->db->limit(1);
            $query = $this->db->get();

            if ($query->num_rows()) {
                $row = $query->row();
                $this->db->where('EndorsementID', $row->EndorsementID, FALSE);
                $this->db->delete(ENTITYENDORSEMENT);

                $this->update_endorsement_count($entity_skill_id, -1);
            }
        }
    }

    /**
     * [update_endorsement_count description]
     * @param  [int]    $entity_skill_id   [Entity Skill Id]
     * @param  [int]      $count      [Like Endorsement increment/decrement]
     * @return [type]               [description]
     */
    public function update_endorsement_count($entity_skill_id, $count = 1) {
        $set_field = "TotalEndorsement";
        $this->db->where('EntitySkillID', $entity_skill_id, FALSE);
        $this->db->set($set_field, "$set_field+($count)", FALSE);
        $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->update(ENTITYSKILLS);
    }
    
    /**
     * [endorsement_list Used to get endorsements for an skills of any entity]     
     * @param  [int] $entity_skill_id   [Entity Skill Id]
     * @param  [string] $count_flag       [count flag]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @return [array/int]              [endorsement list/count]
     */
    function endorsement_list($entity_skill_id, $user_id, $count_flag = FALSE, $page_no = '', $page_size = '',  $keyword = '') {
        $this->db->select('U.UserID, U.UserGUID, U.FirstName, U.LastName, IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);

        $this->db->select('ES.CreatedDate');
        $this->db->from(ENTITYENDORSEMENT . " ES");
        $this->db->join(USERS . " U", "U.UserID=ES.ModuleEntityID AND ES.ModuleID=3");
        
        $this->db->where('ES.EntitySkillID', $entity_skill_id, FALSE);

        if (!empty($keyword)) {
            $keyword = $this->db->escape_like_str($keyword);             
            $this->db->where("(U.FirstName like '%" . $keyword . "%' or U.LastName like '%" . $keyword . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $keyword . "%')");            
        }

        $this->db->_protect_identifiers = FALSE;
        $this->db->order_by("CASE WHEN U.UserID = ".$user_id." THEN 1 ELSE 0 END DESC");
        $this->db->_protect_identifiers = TRUE;
        $this->db->order_by('ES.CreatedDate', 'DESC');
        if (empty($count_flag)) { // check if array needed        
            if (!empty($page_size)) { // Check for pagination            
                $offset = $this->get_pagination_offset($page_no, $page_size);
                $this->db->limit($page_size, $offset);
            }
            $query = $this->db->get();
            $response = array();
            if ($query->num_rows()) {  
                $this->load->model(array('users/user_model'));          
                foreach ($query->result_array() as $result) {
                    $result['Locality']               = (object) [];
                    $result['Occupation'] = '';
                    $entity = $this->user_model->get_user_details($result['UserID']);
                    if ($entity) {                    
                        $result['Occupation'] = isset($entity['Occupation']) ? $entity['Occupation'] : '';
                        $result['Locality']   = $entity['Locality'];
                    }
                    unset($result['UserID']);
                    $response[] = $result;
                }
            }
            return $response;
        } else {
            return $this->db->get()->num_rows();
        }
    }

    /**
     * [get_skill_name]
     * @param  [id] $skill_id        [Skill ID]
     * @return [string]              [Skill Name]
     */
    function get_skill_name($skill_id) {
        $this->db->select('Name');
        $this->db->from(SKILLSMASTER);
        $this->db->where('SkillID', $skill_id, FALSE);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->Name;
        }
        return '';
    }




    function endorsement_notification($data, $all_skill, $user_id, $module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id) {
        $set_email = false;
        for ($i = 0; $i < count($data); $i++) {
            if ($visitor_module_id == '1') {
                $parameters[0]['Type'] = 'Group';
            }
            if ($visitor_module_id == '3') {
                $parameters[0]['Type'] = 'User';
            }
            if ($visitor_module_id == '14') {
                $parameters[0]['Type'] = 'Event';
            }
            if ($visitor_module_id == '18') {
                $parameters[0]['Type'] = 'Page';
            }

            $parameters[0]['ReferenceID'] = $visitor_module_entity_id;
            $parameters[1]['ReferenceID'] = $data[$i]['skill_id'];
            $parameters[1]['Type'] = 'Skills';
            $parameters[1]['SkillData'] = '';
            if ($i <= 0) {
                $set_email = true;
                $parameters[1]['SkillData'] = $all_skill;
            } else {
                $set_email = false;
            }
            $users = $this->get_entity_admins($module_id, $module_entity_id);

            $this->notification_model->add_notification($data[$i]['notification_type_id'], $user_id, $users, $user_id, $parameters, $set_email);
        }
    }

    /**
     * Function Name: delete
     * Description: Delete data in any table
     * @param type $table
     * @param type $field
     * @param type $where
     * @return boolean
     */
    function delete_data($table, $field, $where)
    {
        $this->db->where_in($field, $where);
        $this->db->delete($table);
        return true;
    }

    /**
     * [get_skill_categories]
     * @param  [int] $skill_id       [Skill ID]
     * @return [array]               [Category & Sub Category]
     */
    function get_skill_categories($skill_id)
    {
        $categories = array('CategoryName' => '', 'SubCategoryName' => '', 'CategoryIcon' => '');
        $this->db->select('CategoryID');
        $this->db->from(ENTITYCATEGORY);
        $this->db->where('ModuleID', '29');
        $this->db->where('ModuleEntityID', $skill_id);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            $category_id = $query->row()->CategoryID;

            $this->db->select('C.Name, C.ParentID');
            $this->db->select('IF(MD.ImageName="" || MD.ImageName IS NULL || MD.ImageName =0 ,"",MD.ImageName) as Icon', FALSE);


            $this->db->from(CATEGORYMASTER . ' As C');
            $this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');
            $this->db->where('C.CategoryID', $category_id);

            $query = $this->db->get();
            $row = $query->row();
            if (!empty($row))
            {

                $category_name = $row->Name;
                $category_icon = $row->Icon;

                $categories['SubCategoryName'] = $category_name;
                $categories['CategoryIcon'] = $category_icon;
                if ($row->ParentID)
                {
                    $this->db->select('C.Name, C.ParentID');
                    $this->db->select('IF(MD.ImageName="" || MD.ImageName IS NULL || MD.ImageName =0,"", MD.ImageName) as Icon', FALSE);

                    $this->db->from(CATEGORYMASTER . ' As C');
                    $this->db->join(MEDIA . ' MD', 'MD.MediaID = C.MediaID', 'LEFT');
                    $this->db->where('C.CategoryID', $row->ParentID);
                    $query = $this->db->get();
                    $row = $query->row();

                    $parent_category_name = $row->Name;
                    $category_icon = $row->Icon;
                    $categories['CategoryName'] = $parent_category_name;
                    $categories['CategoryIcon'] = $category_icon;
                }
            }
        }
        return $categories;
    }

    /**
     * [get_skill_id]
     * @param  [string] $skill_name  [skill name]
     * @return [int]                 [skill id]
     */
    function get_skill_id($skill_name)
    {
        $this->db->select('SkillID');
        $this->db->from(SKILLSMASTER);
        $this->db->where('Name', $skill_name);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->row()->SkillID;
        }

        $data = array('Name' => $skill_name, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'StatusID' => 1, 'AddedBy' => $this->UserID);
        $this->db->insert(SKILLSMASTER, $data);
        $skill_id = $this->db->insert_id();
        $insert_data_entity_category['CategoryID'] = 0;
        $insert_data_entity_category['EntityCategoryGUID'] = get_guid();
        $insert_data_entity_category['ModuleID'] = 29;
        $insert_data_entity_category['ModuleEntityID'] = $skill_id;
        $insert_data_entity_category['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $this->db->insert(ENTITYCATEGORY, $insert_data_entity_category);

        return $skill_id;
    }

    

    /**
     * [save Used to insert/update any module entity skills]
     * @param  [array] $skills        [array of skills]
     * @param  [int] $module_id       [module id]
     * @param  [int] $module_entity_id     [module entity id]
     * @return [bool]                     [true/false]
     */
    function manage_save($skills, $module_id, $module_entity_id)
    {
        $skills_list = array();
        if (count($skills) > 0 && !empty($skills) && is_array($skills))
        {
            $skill_order = 1;
            $final_update_array = array();
            $entity_skill_id = array();
            foreach ($skills as $key => $skill)
            {

                if ($skill['StatusID'] == 2)
                {
                    $field['EntitySkillID'] = $skill['EntitySkillID'];
                    $field['DisplayOrder'] = $skill_order;
                    $final_update_array[] = $field;
                    $skill_order = $skill_order + 1;
                }
                else
                {
                    $entity_skill_id[] = $skill['EntitySkillID'];
                }
            }


            if (!empty($final_update_array))
            {
                $this->db->update_batch(ENTITYSKILLS, $final_update_array, 'EntitySkillID');
            }
            if (!empty($entity_skill_id))
            {
                $this->delete_entity_skill($entity_skill_id);
            }
            return true;
        }
    }

    /**
     * [endorse_suggestion description]
     * @param  [int]  $module_id                [Module ID]
     * @param  [int]  $module_entity_id         [Module Entity ID]
     * @param  [int]  $visitor_module_id        [Visitor Module ID]
     * @param  [int]  $visitor_module_entity_id [Visitor Module Entity ID]
     * @param  [int]  $page_no                  [Page Number]
     * @param  [int]  $page_size                [Page Size]
     * @return [array]                          [Skills list]
     */
    function endorse_suggestion($module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id, $page_no = 1, $page_size = 5)
    {
        $this->db->select('ES.SkillID, S.Name');
        $this->db->select('IF(S.MediaID="" || S.MediaID IS NULL || S.MediaID=0,"",MD.ImageName) as SkillImageName', FALSE);
        $this->db->from(ENTITYSKILLS . ' ES');
        $this->db->join(SKILLSMASTER . ' S', 'S.SkillID=ES.SkillID', 'left');
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = S.MediaID', 'LEFT');

        $this->db->where('S.StatusID', '2');
        $this->db->where('ES.ModuleID', $module_id);
        $this->db->where('ES.ModuleEntityID', $module_entity_id);
        $this->db->where('ES.StatusID', 2);

// $this->db->where('ES.TotalEndorsement >= ',1);

        $this->db->where("ES.EntitySkillID NOT IN (SELECT EntitySkillID FROM " . ENTITYENDORSEMENT . " WHERE ModuleID='" . $visitor_module_id . "' AND ModuleEntityID='" . $visitor_module_entity_id . "')");


        if (!empty($page_size)) // Check for pagination
        {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }
        $this->db->order_by('ES.TotalEndorsement', 'DESC');
        $query = $this->db->get();

        $data = array();
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $result)
            {
                $categories = $this->get_skill_categories($result['SkillID']);
                $result['CategoryName'] = $categories['CategoryName'];
                $result['SubCategoryName'] = $categories['SubCategoryName'];
                $result['CategoryImageName'] = $categories['CategoryIcon'];


                $data[] = $result;
            }
        }
        return $data;
    }

    /**
     * [get_endorsed_by]
     * @param  [int] $entity_skill_id      [entity skill id]
     * @return [array]
     */
    function get_endorsed_by($entity_skill_id)
    {
        $this->db->select('ModuleID,ModuleEntityID');
        $this->db->from(ENTITYENDORSEMENT);
        $this->db->where('EntitySkillID', $entity_skill_id);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->result_array();
        }
    }

    /**
     * [skills_list_for_endorsement Used to get skills for auto suggestion of endorsement]
     * @param  [int] $module_id                 [module id]
     * @param  [int] $module_entity_id          [module entity id]
     * @param  [int] $visitor_module_id         [visitor module id]
     * @param  [int] $visitor_module_entity_id  [visitor module entity id]
     * @param  [int] $search                    [search keyword]
     * @return [array]                          [skills list]
     */
    function skills_list_for_endorsement($module_id, $module_entity_id, $visitor_module_id, $visitor_module_entity_id, $search)
    {
        $search = $this->db->escape_like_str($search);
        $this->db->select('S.SkillID, S.Name, C.Name as CategoryName, C.CategoryID, C.ParentID');
        $this->db->select('IF(S.MediaID="" || S.MediaID IS NULL || S.MediaID=0,"",MD.ImageName) as SkillImageName', FALSE);

        $this->db->from(SKILLSMASTER . " as S");
        $this->db->join(ENTITYCATEGORY . ' SC', 'SC.ModuleEntityID=S.SkillID AND SC.ModuleID=29', 'LEFT');
        $this->db->join(CATEGORYMASTER . ' C', 'C.CategoryID=SC.CategoryID', 'LEFT');
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = S.MediaID', 'LEFT');
        $this->db->where('S.StatusID', '2');
        $this->db->where("S.Name LIKE '%" . $search . "%'", NULL, FALSE);

        $visitor_module_id = $this->db->escape_str($visitor_module_id);
        $visitor_module_entity_id = $this->db->escape_str($visitor_module_entity_id);

        $this->db->where("SkillID NOT IN (SELECT ES.SkillID FROM " . ENTITYSKILLS . " AS ES LEFT JOIN " . ENTITYENDORSEMENT . " EE ON ES.EntitySkillID=EE.EntitySkillID WHERE EE.ModuleID=" . $visitor_module_id . " AND EE.ModuleEntityID=" . $visitor_module_entity_id . ")", NULL, FALSE);

        /*   $this->db->where("(EXISTS 
          (
          SELECT 1 FROM " . ENTITYCATEGORY . " AS EC WHERE EC.ModuleEntityID=" . $module_entity_id . " and EC.ModuleID=" . $module_id . " and SC.CategoryID=EC.CategoryID
          ) OR SC.CategoryID IS NULL)", NULL, FALSE); */


        $query = $this->db->get();
//  echo $this->db->last_query();die;
        $data = array();
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $result)
            {
                $parent_category_id = $result['ParentID'];
                $result['SubCategoryName'] = '';

                $categories = $this->get_skill_categories($result['SkillID']);
                $result['CategoryName'] = $categories['CategoryName'];
                $result['SubCategoryName'] = $categories['SubCategoryName'];
                $result['CategoryImageName'] = $categories['CategoryIcon'];


                $data[] = $result;
            }
        }
        return $data;
    }

    /**
     * [Used to get skill list suggestion]
     * @param  [int] $module_id       [module id]
     * @param  [int] $module_entity_id     [module entity id]
     * @param  [int] $search_keyword     [search keyword]
     * @return [array]
     */
    function skills_list($module_id, $module_entity_id, $search_keyword)
    {
        $this->db->select('S.SkillID as SkillID, S.Name, C.Name as CategoryName, C.CategoryID, C.ParentID');

        // $this->db->select('IF(MD.ImageName="","soccer.svg",CONCAT("org_", MD.ImageName)) as ImageName', FALSE);

        $this->db->select('IF(S.MediaID="" || S.MediaID IS NULL || S.MediaID=0,"",MD.ImageName) as SkillImageName', FALSE);

        //$search_keyword = $this->db->escape_like_str($search_keyword); 

        $this->db->from(SKILLSMASTER . " as S");
        $this->db->join(ENTITYCATEGORY . ' SC', 'SC.ModuleEntityID=S.SkillID AND SC.ModuleID=29', 'LEFT');
        $this->db->join(CATEGORYMASTER . ' C', 'C.CategoryID=SC.CategoryID', 'LEFT');
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = S.MediaID', 'LEFT');
        $this->db->where('S.StatusID', '2');
        $this->db->like('S.Name', $search_keyword);
        $this->db->where("S.SkillID NOT IN (SELECT SkillID FROM " . ENTITYSKILLS . " WHERE ModuleID='" . $module_id . "' AND ModuleEntityID='" . $module_entity_id . "')", NULL, FALSE);

        $query = $this->db->get();
        $data = array();
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $result)
            {
                $parent_category_id = $result['ParentID'];
                $result['SubCategoryName'] = '';
                $categories = $this->get_skill_categories($result['SkillID']);
                $result['CategoryName'] = $categories['CategoryName'];
                $result['SubCategoryName'] = $categories['SubCategoryName'];
                $result['CategoryImageName'] = '';
                if (empty($result['SkillImageName']))
                {
                    $result['CategoryImageName'] = $categories['CategoryIcon'];
                }

                $data[] = $result;
            }
        }
        return $data;
    }
   
    /**
     * [get_user_skills Used to get endorsements for an skills of any entity]     
     * @param  [int] $module_id   [Entity Skill Id]
     * @param  [string] $module_entity_id       [count flag]
     * @param  [string] $page_no        [page number]
     * @param  [string] $page_size      [page size]
     * @return [array/int]              [endorsement list/count]
     */
    function get_user_skills($module_id, $module_entity_id, $page_no, $page_size)
    {
        $this->db->select('SM.SkillID,SM.Name');
        $this->db->from(SKILLSMASTER . ' SM');
        $this->db->join(ENTITYSKILLS . ' ES', 'ES.SkillID=SM.SkillID', 'left');
        $this->db->where('ES.ModuleID', $module_id);
        $this->db->where('ES.ModuleEntityID', $module_entity_id);

        if (!empty($page_size)) // Check for pagination
        {
            $offset = ($page_no - 1) * $page_size;
            $this->db->limit($page_size, $offset);
        }

        $query = $this->db->get();
//echo $this->db->last_query();die;
        $data = array();
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $result)
            {
                $categories = $this->get_skill_categories($result['SkillID']);
                $result['CategoryName'] = $categories['CategoryName'];
                $result['CategoryIcon'] = $categories['CategoryIcon'];
                $result['SubCategoryName'] = $categories['SubCategoryName'];
                $data[] = $result;
            }
        }
        return $data;
    }
  
    /**
     * [approve_pending_skills Used to delete skill for an entity]
     * @param  [array] $skills         [Array of skills to be deleted]
     * @param  [int] $module_id        [Module ID]
     * @param  [int] $module_entity_id [Module Entity ID]
     * @return [boolean]               [true]
     */
    function approve_pending_skills($skills, $module_id, $module_entity_id)
    {
        if (count($skills) > 0 && !empty($skills) && is_array($skills))
        {
            $entity_skills = array();
            $skills_list = array();
            foreach ($skills as $key => $skill)
            {
                $skill_id = $skill['SkillID'];
                $skills_list[] = $skill_id;

                $skill_order = 0;
                $get_skill_order = get_data('MAX(DisplayOrder) as DisplayOrder', ENTITYSKILLS, array('ModuleEntityID' => $module_entity_id, 'ModuleID' => $module_id), '1', '');
                if ($get_skill_order)
                {
                    if ($get_skill_order->DisplayOrder > 0)
                        $skill_order = $get_skill_order->DisplayOrder;
                    else
                        $skill_order = 0;
                }

                $this->db->where(array('SkillID' => $skill_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id));

                $this->db->set("StatusID", 2, FALSE);
                $this->db->set("DisplayOrder", $skill_order + 1, FALSE);
                $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->update(ENTITYSKILLS);
            } // foreach Close

            if ($skills_list)
            {
                $this->load->model('activity/activity_model');
                $this->activity_model->addActivity($module_id, $module_entity_id, 19, $this->UserID, 0, '', 1, 1, json_encode($skills_list));
            }
            return true;
        }
    }
    
    /**
     *  Function Name: change_skill
     * Description: update skills order
     * @param type $order_data
     * @return boolean
     */
    function change_skill_order($order_data)
    {

        $order_dataArray = json_decode($order_data);
        $orderValue = array();
        foreach ($order_dataArray as $items)
        {
            $itemArray = array();
            $itemArray['EntitySkillGUID'] = $items->EntitySkillGUID;
            $itemArray['DisplayOrder'] = $items->DisplayOrder;
            $orderValue[] = $itemArray;
        }

        $this->db->update_batch(ENTITYSKILLS, $orderValue, 'EntitySkillGUID');
        return true;
    }

    /**
     * Function Name: delete_pending
     * Description: Delete pending skill
     * @param type $entity_skillGUID_array
     * @return boolean
     */
    function delete_pending_skill($entity_skillGUID_array)
    {
        if (!empty($entity_skillGUID_array))
        {
            $this->db->select("EntitySkillID");
            $this->db->from(ENTITYSKILLS);
            $this->db->where_in('EntitySkillGUID', $entity_skillGUID_array);
            $query = $this->db->get();
            $entity_skill_id = array();
            if ($query->num_rows() > 0)
            {
                foreach ($query->result_array() as $result)
                {
                    $entity_skill_id[] = $result['EntitySkillID'];
                }
                $this->delete_entity_skill($entity_skill_id);
            }
        }
        return true;
    }

    /**
     * Function Name: delete_entity
     * Description: Delete data from entityskill and there endorsment
     * @param type $entity_skill_id
     * @return boolean
     */
    function delete_entity_skill($entity_skill_id)
    {
        $this->skills_model->delete_data(ENTITYSKILLS, 'EntitySkillID', $entity_skill_id);
        $this->skills_model->delete_data(ENTITYENDORSEMENT, 'EntitySkillID', $entity_skill_id);
        return true;
    }

    /**
     * Function Name: get_endorsement
     * Description: endorsement  user data
     * @param type $visitor_module_id
     * @param type $visitor_module_entity_id
     * @param type $module_id
     * @param type $module_entity_id
     * @param type $page_no
     * @param type $page_size
     * @param type $endorsment_entity_id
     * @return type
     */
    function get_endorsement($visitor_module_id, $visitor_module_entity_id, $module_id, $module_entity_id, $page_no, $page_size, $endorsment_entity_id = 0)
    {
        $Return = array();
        $Return['Data'] = array();

        $this->db->select('(CASE EE.ModuleID 
                            WHEN 3 THEN PU.Url
                            WHEN 18 THEN P.PageURL   
                            ELSE "" END) AS ProfileURL', FALSE);

        $this->db->select('(CASE EE.ModuleID 
                            WHEN 1 THEN G.GroupGUID 
                            WHEN 3 THEN U.UserGUID 
                            WHEN 18 THEN P.PageGUID ELSE "" END) AS ModuleEntityGUID', FALSE);

        $this->db->select('(CASE EE.ModuleID 
                            WHEN 3 THEN U.UserTypeID
                            ELSE "" END) AS UserTypeID', FALSE);

        $this->db->select('(CASE EE.ModuleID 
                            WHEN 1 THEN if(G.GroupImage!="",G.GroupImage,"group-no-img.jpg")
                            WHEN 3 THEN IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture)
                            WHEN 18 THEN IF(P.ProfilePicture="",CM.Icon,P.ProfilePicture)   
                            ELSE "" END) AS ProfilePicture', FALSE);

        $this->db->select('CONCAT(IFNULL(U.FirstName,""), " ",IFNULL(U.LastName,""), " ",IFNULL(G.GroupName,""), " ",IFNULL(P.Title,"")) AS Name', FALSE);

        $this->db->select("EE.EndorsementID,EE.ModuleID,EE.ModuleEntityID,EE.CreatedDate");
        $this->db->from(ENTITYENDORSEMENT . ' as EE');
        $this->db->join(ENTITYSKILLS . ' as ES', 'EE.EntitySkillID=ES.EntitySkillID ');
        $this->db->join(SKILLSMASTER . ' as SM', 'ES.SkillID=SM.SkillID AND SM.StatusID=2'); // AND SM.StatusID=2
        $this->db->join(USERS . " U", "U.UserID=EE.ModuleEntityID AND EE.ModuleID=3", "LEFT");
        $this->db->join(GROUPS . " G", "G.GroupID=EE.ModuleEntityID AND EE.ModuleID=1", "LEFT");
        $this->db->join(PAGES . " P", "P.PageID=EE.ModuleEntityID AND EE.ModuleID=18", "LEFT");
        $this->db->join(PROFILEURL . " as PU", "PU.EntityID = U.UserID and PU.EntityType = 'User'", "LEFT");
        $this->db->join(CATEGORYMASTER . " CM", "CM.CategoryID = P.CategoryID", "LEFT");

        // $this->db->group_by('EE.ModuleID,EE.ModuleEntityID');
        $s_where = '';
        if ($endorsment_entity_id)
        {
            $s_where = ' AND S_EE.ModuleEntityID="' . $endorsment_entity_id . '" ';
        }
        
        $module_id = $this->db->escape_str($module_id);
        $module_entity_id = $this->db->escape_str($module_entity_id);
        
        $sql = ' SELECT MAX(S_EE.EndorsementID)  as EndorsementID from ' . ENTITYENDORSEMENT . ' as S_EE JOIN ' . ENTITYSKILLS . ' as S_ES ON S_EE.EntitySkillID=S_ES.EntitySkillID AND S_ES.ModuleID=' . $module_id . ' AND S_ES.ModuleEntityID=' . $module_entity_id . ' JOIN ' . SKILLSMASTER . ' as S_SM ON S_ES.SkillID=S_SM.SkillID AND S_SM.StatusID=2 ' . $s_where . ' GROUP BY S_EE.ModuleID,S_EE.ModuleEntityID ';
        //   die;
        $this->db->where('EE.EndorsementID IN (' . $sql . ')', NULL, FALSE);
        $this->db->order_by('EE.CreatedDate', 'DESC');
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $Return['TotalRecords'] = $temp_q->num_rows();

        if ($page_size)
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }

        $query = $this->db->get();

        //yecho $this->db->last_query(); die;
        $response = array();
        if ($query->num_rows() > 0)
        {
            $result = $query->result_array();
            //  print_r($result);
            //die;

            foreach ($result as $result_item)
            {
                $can_endorse = FALSE;
                if ($result_item['ModuleID'] == 3 && $visitor_module_entity_id != $result_item['ModuleEntityID'])
                {
                    $users_relation = get_user_relation($result_item['ModuleEntityID'], $visitor_module_entity_id);
                    $privacy_details = $this->privacy_model->details($result_item['ModuleEntityID']);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label'])
                    {
                        foreach ($privacy_details['Label'] as $privacy_label)
                        {
                            if ($privacy_label['Value'] == 'endorse_skill' && in_array($privacy_label[$privacy], $users_relation))
                            {
                                $can_endorse = TRUE;
                            }
                        }
                    }
                }

                $result_item['CanEndorse'] = $can_endorse;
                $result_item['Skill'] = $this->get_user_endorse_skill($result_item['ModuleID'], $result_item['ModuleEntityID'], $module_id, $module_entity_id, 1, 10);
                $response[] = $result_item;
            }
        }
        $Return['Data'] = $response;
        return $Return;
    }

    /**
     * Function Name: is_user_endorsement
     * Description: check if any user have endorsement
     * @param type $user_id
     * @return type
     */
    function is_user_endorsement($user_id)
    {
        $this->db->select("SUM(ES.TotalEndorsement) as TotalEndorsement");
        $this->db->from(ENTITYSKILLS . ' as ES');
        $this->db->join(SKILLSMASTER . ' as SM', 'ES.SkillID=SM.SkillID AND SM.StatusID=2'); // AND SM.StatusID=2
        $this->db->where('ES.ModuleID', 3);
        $this->db->where('ES.ModuleEntityID', $user_id);
        $query = $this->db->get();
        $TotalEndorsement = 0;
        $result = $query->row_array();
        if ($result)
        {
            $TotalEndorsement = $result['TotalEndorsement'];
        }
        return $TotalEndorsement;
    }

    /**
     * Function Name: get_user_endorse
     * Description: 
     * @param type $module_id
     * @param type $module_entity_id
     * @param type $to_module_id
     * @param type $to_module_entity_id
     * @param type $page_no
     * @param type $page_size
     * @return type
     */
    function get_user_endorse_skill($module_id, $module_entity_id, $to_module_id, $to_module_entity_id, $page_no, $page_size)
    {
        $Return = array();
        $Return['Data'] = array();
        $Return['TotalRecords'] = '';
        // echo $module_entity_id;die;
        $this->db->select("SM.Name,SM.SkillID,ES.EntitySkillID,ES.TotalEndorsement");
        $this->db->select('IF(SM.MediaID="" || SM.MediaID IS NULL || SM.MediaID=0,"",MD.ImageName) as SkillImageName', FALSE);
        $this->db->from(ENTITYENDORSEMENT . ' as EE');
        $this->db->join(ENTITYSKILLS . ' as ES', 'EE.EntitySkillID=ES.EntitySkillID AND ES.ModuleID="' . $to_module_id . '" AND ES.ModuleEntityID="' . $to_module_entity_id . '" ');
        $this->db->join(SKILLSMASTER . ' as SM', 'ES.SkillID=SM.SkillID'); // AND SM.StatusID=2
        $this->db->join(MEDIA . ' MD', 'MD.MediaID = SM.MediaID', 'LEFT');
        $this->db->where('EE.ModuleID', $module_id);
        $this->db->where('EE.ModuleEntityID', $module_entity_id);
        $this->db->order_by('EE.CreatedDate', 'DESC');

        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $Return['TotalRecords'] = $temp_q->num_rows();

        if ($page_size)
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }

        $query = $this->db->get();

        $response = array();
        if ($query->num_rows() > 0)
        {
            foreach ($query->result_array() as $result)
            {
                $entity_skill_id = $result['EntitySkillID'];

                $categories = $this->get_skill_categories($result['SkillID']);
                $result['CategoryName'] = $categories['CategoryName'];
                $result['SubCategoryName'] = $categories['SubCategoryName'];
                $result['CategoryImageName'] = '';
                $result['CategoryImageName'] = $categories['CategoryIcon'];


                $result['IsEndorse'] = FALSE;
                if (!empty($visitor_module_id) && !empty($visitor_module_entity_id))
                {
                    $result['IsEndorse'] = $this->is_endorse($entity_skill_id, $visitor_module_id, $visitor_module_entity_id);
                }
                $result['TotalEndorsement'] = $result['TotalEndorsement'];
                $result['Endorsements'] = array(); //$this->endorsement_list($entity_skill_id, FALSE, 1, 5, $to_module_id, $to_module_entity_id, '', $module_id, $module_entity_id);
                //print_r($result);die;
                unset($result['TotalEndorsement']);
                $response[] = $result;
            }
        }
        $Return['Data'] = $response;
        return $Return;
    }

    /**
     *  Function Name: endorse_connection
     * Description: get  endorsement  user data
     * @param type $module_id
     * @param type $module_entity_id
     * @param type $endorse_module_id
     * @param type $endorse_module_entity_id
     * @param type $page_no
     * @param type $page_size
     * @return type
     */
    function endorse_connection($module_id, $module_entity_id, $endorse_module_id, $endorse_module_entity_id, $page_no, $page_size)
    {
        $Return = array();
        $Return['Data'] = array();
        $blocked_users = array();
        $result = array();
        if ($module_id == 3)
        {
            if ($page_no == 1)
            {
                $this->db->select('P.Url as ProfileLink, U.FirstName,IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture, U.UserID, U.UserGUID AS ModuleEntityGUID, U.LastName', FALSE);
                $this->db->from(USERS . " U");
                $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
                $this->db->where('U.UserID', $endorse_module_entity_id);
                $query = $this->db->get();
                if ($query->num_rows() > 0)
                {
                    $result_temp = $query->row_array();

                    $users_relation = get_user_relation($result_temp['UserID'], $module_entity_id);
                    $privacy_details = $this->privacy_model->details($endorse_module_entity_id);
                    $privacy = ucfirst($privacy_details['Privacy']);
                    if ($privacy_details['Label'])
                    {
                        foreach ($privacy_details['Label'] as $privacy_label)
                        {
                            if (isset($privacy_label[$privacy]))
                            {
                                if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                                {
                                    $result_temp['ProfilePicture'] = 'user_default.jpg';
                                }
                            }
                        }
                    }

                    $result_temp['ModuleID'] = $module_id;
                    $result_temp['EndorseSuggestion'] = $this->endorse_suggestion($endorse_module_id, $result_temp['UserID'], $module_id, $module_entity_id, 1, 3);
                    $result[] = $result_temp;
                }
            }
            $blocked_users = blocked_users($module_entity_id);
            $this->db->select('P.Url as ProfileLink, U.FirstName, IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture, U.UserID, U.UserGUID, U.LastName', FALSE);
            $this->db->from(USERS . " U");
            $this->db->join(USERPRIVACY . " UP", 'UP.UserID=U.UserID AND UP.PrivacyLabelKey="endorse_skill"  AND UP.Value!= "self" ', 'left');
            $this->db->join(PROFILEURL . " as P", "P.EntityID = U.UserID and P.EntityType = 'User'", "LEFT");
            $this->db->join(FRIENDS . " as F", "F.FriendID = U.UserID AND F.Status=1", "LEFT");
            $this->db->join(MODULES . " as M", "M.ModuleID=F.ModuleID and M.IsActive=1", "LEFT");
            $this->db->where_not_in('U.StatusID', array(3, 4));
            $this->db->where('U.UserID !=', $endorse_module_entity_id);
            $sql_condition = array('U.UserID !=' => $module_entity_id, 'F.UserID' => $module_entity_id);
            $this->db->where($sql_condition);
            if (!empty($blocked_users))
            {
                $this->db->where_not_in('U.UserID', $blocked_users);
            }
        }

        if ($page_size)
        {
            $this->db->limit($page_size, getOffset($page_no, $page_size));
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0)
        {
            $result_array = $query->result_array();
            foreach ($result_array as $result_item)
            {
                $users_relation = get_user_relation($result_item['UserID'], $module_entity_id);
                $privacy_details = $this->privacy_model->details($result_item['UserID']);
                $privacy = ucfirst($privacy_details['Privacy']);
                if ($privacy_details['Label'])
                {
                    foreach ($privacy_details['Label'] as $privacy_label)
                    {
                        if (isset($privacy_label[$privacy]))
                        {
                            if ($privacy_label['Value'] == 'view_profile_picture' && !in_array($privacy_label[$privacy], $users_relation))
                            {
                                $result_item['ProfilePicture'] = 'user_default.jpg';
                            }
                        }
                    }
                }

                $result_item['ModuleID'] = $module_id;
                $result_item['EndorseSuggestion'] = $this->endorse_suggestion($endorse_module_id, $result_item['UserID'], $module_id, $module_entity_id, 1, 3);
                $result[] = $result_item;
            }
        }
        $Return['Data'] = $result;
        return $Return;
    }

    /**
     *  Function Name: get_skills_by_id
     * Description: get skill data by id
     * @param type $skill_id
     * @return type
     */
    function get_skills_by_id($skill_id) {
        $this->db->select(" SM.SkillID as ID, SM.Name ,COUNT(ES.SkillID) as ProfileCount,IFNULL(CM.Name,'') as CategoryName,IFNULL(CM1.Name,'') as ParentCategorName,IFNULL(CM.CategoryID,'') as CategoryID,IFNULL(CM1.CategoryID,'') as ParentCategoryID", false);
        //  $this->db->select("IF((SM.MediaID != NULL && SM.MediaID !=0 ),'M.ImageName','test') as ImageName ",FALSE);
        $this->db->select('IF(SM.MediaID="" || SM.MediaID IS NULL || SM.MediaID=0,"",M.ImageName) as SkillImageName', FALSE);

        $this->db->from(SKILLSMASTER . " AS SM ");
        $this->db->join(ENTITYSKILLS . " AS ES ", "ES.SkillID=SM.SkillID AND ES.StatusID=2", "left");
        $this->db->join(ENTITYCATEGORY . " AS EC ", "SM.SkillID=EC.ModuleEntityID AND EC.ModuleID=29");
        $this->db->join(CATEGORYMASTER . " AS CM ", "CM.CategoryID=EC.CategoryID AND CM.ModuleID=29", "left");
        $this->db->join(CATEGORYMASTER . " AS CM1 ", "CM.ParentID=CM1.CategoryID AND CM1.ModuleID=29", "left");
        $this->db->join(MEDIA . " AS M ", "M.MediaID=SM.MediaID ", "left");
        $this->db->where("SM.SkillID ", $skill_id, FALSE);
        $query = $this->db->get();
        // echo $this->db->last_query();die;
        $results_item = $query->row_array();

        $results_item['CategoryImageName'] = '';
        if (empty($results_item['SkillImageName'])) {
            $category_id = $results_item['ParentCategoryID'];
            if (!$category_id) {
                $category_id = $results_item['CategoryID'];
            }
            $category_data = $this->get_category_icon($category_id);

            if (!empty($category_data)) {
                $results_item['CategoryImageName'] = $category_data['CategoryImageName'];
            }
        }
        return $results_item;
    }

    /**
     *  Function Name: get_category_icon
     * Description: get category icon 
     * @param type $category_id
     * @return type
     */
    function get_category_icon($category_id) {
        $this->db->select('IF((CM.MediaID IS NULL || CM.MediaID = 0 || CM.MediaID = ""),"",M.ImageName) as CategoryImageName', false);
        $this->db->select('IF((M.MediaGUID = NULL),"",M.MediaGUID) as MediaGUID', false);
        $this->db->from(CATEGORYMASTER . " AS CM ");
        $this->db->join(MEDIA . " AS M ", "M.MediaID=CM.MediaID ", "left");
        $this->db->where('CM.CategoryID', $category_id, FALSE);
        $query = $this->db->get();
        return $results = $query->row_array();
    }

}
