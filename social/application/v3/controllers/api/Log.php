<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This Class used as REST API for logging module
 * @category Controller
 * @author       Vinfotech Team
 */
class Log extends Common_API_Controller {

    function __construct() {
        parent::__construct();
    }

    /**
     * Function Name: index
     * @param EntityType, EntityGUID
     * Description: Log view counts
     */
    public function index_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/log') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {            
            $entity_type = $data['EntityType'];
            $entity_guid = $data['EntityGUID'];          
            initiate_worker_job('save_log', array('UserID' => $user_id, 'LogType' => $entity_type, 'EntityGUID' => $entity_guid, 'Flag' => FALSE, 'DeviceTypeID' => $this->DeviceTypeID));

            if ($entity_type == 'Activity') {
                $activity_details = get_detail_by_guid($entity_guid, 0, 'ActivityID, UserID, ModuleID, ModuleEntityID, ActivityTypeID', 2);
                if ($activity_details['UserID'] != $user_id) {
                    if ($activity_details['ModuleID'] == 3 && $activity_details['UserID'] == $activity_details['ModuleEntityID']) {
                        initiate_worker_job('add_update_relationship_score', array('UserID' => $user_id, 'ModuleID' => $activity_details['ModuleID'], 'ModuleEntityID' => $activity_details['ModuleEntityID'], 'Score' => 5));                        
                    } else {
                        initiate_worker_job('add_update_relationship_score', array('UserID' => $user_id, 'ModuleID' => 3, 'ModuleEntityID' => $activity_details['UserID'], 'Score' => 5));
                        initiate_worker_job('add_update_relationship_score', array('UserID' => $user_id, 'ModuleID' => $activity_details['ModuleID'], 'ModuleEntityID' => $activity_details['ModuleEntityID'], 'Score' => 5));                        
                    }
                }

                //Update fav modified date for viewed activity
                $condition = array("StatusID" => 2, "UserID" => $user_id, "EntityType" => "ACTIVITY", "EntityID" => $activity_details['ActivityID']);
                $this->db->where($condition);
                $this->db->set("ModifiedDate", get_current_date('%Y-%m-%d %H:%i:%s'));
                $this->db->update(FAVOURITE);
                if (CACHE_ENABLE) {
                    $this->cache->delete('user_favourite_activity' . $user_id);
                }
            } else {
                $module_id = 0;
                switch ($entity_type) {
                    case 'Group':
                        $module_id = 1;
                        break;
                    case 'Event':
                        $module_id = 14;
                        break;
                    case 'Page':
                        $module_id = 18;
                        break;
                    case 'User':
                        $module_id = 3;
                        break;
                }

                $module_entity_id = get_detail_by_guid($entity_guid, $module_id);                
                initiate_worker_job('add_update_relationship_score', array('UserID' => $user_id, 'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'Score' => 5));
                $allowed_entity_types = array('Group', 'User', 'Event', 'Page');

                if (in_array($entity_type, $allowed_entity_types)) {                    
                    $data = array(
                        'ModuleID' => $module_id, 'ModuleEntityID' => $module_entity_id, 'UserID' => $user_id, 'ActivityTypeID' => '21',
                        'PostAsModuleID' => '3', 'PostAsModuleEntityID' => $user_id, 'EntityID' => $module_entity_id, 'ParentCommentID' => 0, 'LoginSessionKey' => $this->post_data[AUTH_KEY], 'LogType' => 'VIEWLOG'
                    );
                    initiate_worker_job('add_activity_log', $data);
                }
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: user_list
     * @param EntityType, EntityGUID, PageSize, PageNo
     * Description: User list
     */
    public function views_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        if ($this->form_validation->run('api/log/user_list') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $entity_type = $data['EntityType'];
            $entity_guid = $data['EntityGUID'];
            $search_key = isset($data['SearchKey']) ? $data['SearchKey'] : '';
            $page_size = isset($data['PageSize']) ? $data['PageSize'] : PAGE_SIZE;
            $page_no = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO;
            $this->load->model('activity/activity_model');
            $return['Data'] = $this->activity_model->get_entity_log_users($entity_type, $entity_guid, $search_key, $page_size, $page_no);
            $return['TotalRecords'] = $this->activity_model->get_entity_log_users($entity_type, $entity_guid, $search_key, $page_size, $page_no, 1);
        }
        $this->response($return);
    }

    public function log_activity_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $this->logLoginActivity($data);

        $validation_rule = array(
            array(
                'field' => 'EntityType',
                'label' => 'EntityType',
                'rules' => 'trim|required',
            )
                );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        $entity_type = $data['EntityType'];
        $module_id = 0;
        switch (strtolower($entity_type)) {
            case 'group':
                $module_id = 1;
                break;
            case 'event':
                $module_id = 14;
                break;
            case 'page':
                $module_id = 18;
                break;
            case 'user':
                $module_id = 3;
                break;
            case 'activity':
                $module_id = 19;
                break;
            case 'forum':
                $module_id = 33;
                break;
        }
        $data = array(
            'ModuleID' => $module_id, 'ModuleEntityID' => 0, 'UserID' => $user_id, 'ActivityID' => 0,
            'ActivityTypeID' => 32, 'PostAsModuleID' => 3,
            'PostAsModuleEntityID' => $user_id, 'EntityID' => 0, 'ParentCommentID' => 0, 'LoginSessionKey' => $this->post_data[AUTH_KEY], 'LogType' => 'VIEWLOG'
        );        
        initiate_worker_job('add_activity_log', $data);
        $this->response($return);
    }

    protected function logLoginActivity($data) {
        $entity_type = $data['EntityType'];

        if ($entity_type == 'LogInEndTime') {
            return;
        }

        if ($entity_type != 'LogIn') {
            return;
        }

        $user_id = !empty($this->UserID) ? $this->UserID : 0;
        if (!$user_id) {
            return;
        }

        // Update login session time
        $this->db->where('UserID', $this->UserID);
        $this->db->update(USERDETAILS, array('LastLoginDate' => get_current_date('%Y-%m-%d')));
        $this->db->limit(1);

        // Add activity score
        $data = array(
            'ModuleID' => 3, 'ModuleEntityID' => $user_id, 'UserID' => $user_id, 'ActivityID' => 0,
            'ActivityTypeID' => 35, 'PostAsModuleID' => 3,
            'PostAsModuleEntityID' => $user_id, 'EntityID' => 0, 'ParentCommentID' => 0, 'LoginSessionKey' => $this->post_data[AUTH_KEY]
        );        
        initiate_worker_job('add_activity_log', $data);
        $this->response($this->return);
    }
}
