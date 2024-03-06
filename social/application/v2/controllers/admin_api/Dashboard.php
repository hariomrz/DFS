<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Dashboard extends Admin_API_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('admin/login_model', 'admin/dashboard/dashboard_model'));
        
        $logged_user_data = $this->login_model->activeAdminLoginAuth($this->post_data);
        if ($logged_user_data['ResponseCode'] != 200) {
            $this->response($logged_user_data);
        }
        $this->UserID = $logged_user_data['Data']['UserID'];

    }
        
    public function index_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
                
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $this->load->model(array('mongo/log/user_activity_log_mongo'));
        
        $this->user_activity_log_mongo->getActivityData();
        
        $this->response($return);
    }    
    
    /**
     * Function Name: get_unverified_entities
     
     * @param page_no
     * @param page_size
     * @param entityType ( ALL, GROPS, PAGES, EVENTS )
     * Description: Get list of unverified entities ( Users, Groups, Events, Pages )
     */
    public function get_unverified_entities_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $page_no = (int)isset($data['page_no']) ? $data['page_no'] : 1;
        $page_size = (int)isset($data['page_size']) ? $data['page_size'] : 20;
        $search = isset($data['search']) ? $data['search'] : '';
        $entityType = isset($data['entityType']) ? $data['entityType'] : 'ALL';  
        $mainList = (bool)isset($data['mainList']) ? $data['mainList'] : 0;  
        
        
        $entitiesData = $this->dashboard_model->get_unverified_entities($page_no, $page_size, $search, $entityType);
        $return['TotalRecords'] = $entitiesData['TotalRecords'];
        $return['Data'] = $entitiesData['Data'];
        
        $this->response($return);
    }
    
    
    /**
     * Function Name: get_unverified_entity
     
     * @param ModuleID ( Users, Groups, Events, Pages )
     * @param ModuleEntityID
     * Description: Get unverified entity ( User, Group, Event, Page )
     */
    public function get_unverified_entity_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'ModuleID',
                'label' => 'ModuleID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'ModuleEntityID',
                'label' => 'ModuleEntityID',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
       
        $module_id = (int)isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = (int)isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : 0;
        $entity_types = array( 
            1 => 'GROUPS',
            3 => 'USERS',
            14 => 'EVENTS',
            18 => 'PAGES',
        );
        if(!isset($entity_types[$module_id])) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = 'Invalid module id';
            $this->response($return);
        }
        
        $entityType = $entity_types[$module_id];
        $page_no = 1;
        $page_size = 1;
        $search = '';
        $mainList = false;
        
        $entitiesData = $this->dashboard_model->get_unverified_entities($page_no, $page_size, $search, $entityType, $module_entity_id);
        //$return['TotalRecords'] = $entitiesData['TotalRecords'];
        $return['Data'] = current($entitiesData['Data']);
        $return['Data'] = ($return['Data']) ? $return['Data'] : [];
        $this->response($return);
    }
    
    
    /**
     * Function Name: update_entity
     * @param ModuleID ( Users, Groups, Events, Pages )
     * @param ModuleEntityID
     * Description: Update status of unverified entities ( Users, Groups, Events, Pages )
     */
    public function update_entity_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'ModuleID',
                'label' => 'ModuleID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'ModuleEntityID',
                'label' => 'ModuleEntityID',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
       
        $module_id = (int)isset($data['ModuleID']) ? $data['ModuleID'] : 0;
        $module_entity_id = (int)isset($data['ModuleEntityID']) ? $data['ModuleEntityID'] : 0;
        $entity_column = (int)!empty($data['EntityColumn']) ? $data['EntityColumn'] : 'Verified';
        $entity_column_val = (int)isset($data['EntityColumnVal']) ? $data['EntityColumnVal'] : 1;
        $user_id = (int)isset($data['UserID']) ? $data['UserID'] : 0;
        
        $this->dashboard_model->update_entity($module_id, $module_entity_id, $entity_column_val, $entity_column, $user_id);
        $return['Message'] = 'Status updated successfully.';
        
        $this->response($return);
    }
    
    /**
     * Function Name: save_note
     * @param ModuleID ( Users, Groups, Events, Pages )
     * @param ModuleEntityID
     * @param Description 
     * Description: save notes for unverified entities ( Users, Groups, Events, Pages )
     */
    public function save_note_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'ModuleID',
                'label' => 'ModuleID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'ModuleEntityID',
                'label' => 'ModuleEntityID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'Description',
                'label' => 'Description',
                'rules' => 'trim|required',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        
        
        $status = isset($data['Status']) ? $data['Status'] : NULL;
        $note_id = (int)isset($data['NoteID']) ? $data['NoteID'] : 0;
        
        $this->load->model(array('entity/entitynote_model'));
        $this->entitynote_model->save($data['Description'], $data['ModuleID'], $data['ModuleEntityID'], $status, $note_id);
        $return['Message'] = 'Note saved successfully.';
        
        $this->response($return);
    }
    
    /**
     * Function Name: delete_note
     * @param NoteID 
     * Description: Delete entity note
     */
    public function delete_note_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'NoteID',
                'label' => 'NoteID',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        
        $note_id = (int)isset($data['NoteID']) ? $data['NoteID'] : 0;
        
        $this->load->model(array('entity/entitynote_model'));
        $this->entitynote_model->delete_note($note_id);
        $return['Message'] = 'Note deleted successfully.';
        
        $this->response($return);
    }
    
    /**
     * Function Name: get_note_list
     * @param PageNo ( Users, Groups, Events, Pages )
     * @param PageSize
     * Description: Get list of notes for unverified entities ( Users, Groups, Events, Pages )
     */
    public function get_note_list_post()
    {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $this->load->model(array('entity/entitynote_model'));
        $page_no = (int)isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = (int)isset($data['PageSize']) ? $data['PageSize'] : 10;
        $return['Data'] = (array)$this->entitynote_model->get_list($page_no, $page_size, $data);
        $this->response($return);
    }
    
    /**
     * Function Name: send_message
     * @param ModuleID
     * @param ModuleEntityID
     * @param Replyable
     * @param Subject
     * @param Body
     * @param Media
     * Description: Send Message to ( USER, GROUP, EVENT, PAGE ) 
     */
    public function send_message_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'dashboard/send_message';
        $return['Message'] = lang('msg_sent_success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ModuleID',
            'label' => 'ModuleID',
            'rules' => 'trim|required|integer'
        );
        $validation_rule[] = array(
            'field' => 'ModuleEntityID',
            'label' => 'ModuleEntityID',
            'rules' => 'trim|required|integer'
        );
        $validation_rule[] = array(
            'field' => 'Replyable',
            'label' => 'Replyable',
            'rules' => 'trim|required|less_than[3]'
        );
        if(!(isset($data['Media'])) || empty($data['Media'])) {
            $validation_rule[] = array(
                'field' => 'Body',
                'label' => 'Body',
                'rules' => 'trim|required'
            );
        }

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error;
            $this->response($return);
        } 
        
        $this->load->model('messages/messages_model');
        $data['Recipients'] = $this->dashboard_model->getEntitiesMessageMembers($data['ModuleID'], $data['ModuleEntityID']);
        unset($data['ModuleID']);
        if(empty($data['Recipients'])) {
            $return['ResponseCode'] = 511;
            $return['Message'] = lang('empty_recipients');
        } 
        
        $return['Data'] = $this->messages_model->compose($user_id, $data);  
        $this->response($return);
    }
    
    /**
     * Function Name: get_activities
     * @param ModuleID
     * @param ModuleEntityID
     * @param Replyable
     * @param Subject
     * @param Body
     * @param Media
     * Description: Get list of unverified activities
     */
    public function get_activities_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $PageNo = (int)isset($data['PageNo']) ? $data['PageNo'] : 1;
        $PageSize = (int)isset($data['PageSize']) ? $data['PageSize'] : 20;
        $GetEntityType = isset($data['GET_ENTITY_TYPE']) ? $data['GET_ENTITY_TYPE'] : 'ACTIVITY';
        
        $this->load->model(array(
            'admin/dashboard/dashboard_activity_model',
            'users/user_model',
        ));
        $this->user_model->set_user_time_zone($this->UserID);
        
        $entitiesData = $this->dashboard_activity_model->get_activity_list($PageNo, $PageSize, $data);
        $return['Data'] = isset($entitiesData['entities']) ? $entitiesData['entities'] : [];
        $return['TotalRecords'] = (int)isset($entitiesData['total_count']) ? $entitiesData['total_count'] : 0;
        
        $this->response($return);
    }
    
    /**
     * Function Name: get_user_post_details
     * @param UserID
     * @param ActivityID
     * Description: To get user and post details
     */
    public function get_user_post_details_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'UserID',
                'label' => 'UserID',
                'rules' => 'trim|required|integer',
            ),
            array(
                'field' => 'ActivityID',
                'label' => 'ActivityID',
                'rules' => 'trim|required|integer',
            ),
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
        
        $this->load->model(array('admin/dashboard/dashboard_activity_model',));
        $page_no = (int)isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = (int)isset($data['PageSize']) ? $data['PageSize'] : 0;
        $user_id = $data['UserID'];
        $activity_id = $data['ActivityID'];
       
        $return['Data']['UserDetails'] = (array)$this->dashboard_activity_model->get_user_details($user_id);
        $return['Data']['UserTags'] = array(); //(array)$this->dashboard_activity_model->get_entity_tags($user_id, 'USER');
        $return['Data']['ActivityTags'] = (array)$this->dashboard_activity_model->get_entity_tags($activity_id, 'ACTIVITY');
        $this->response($return);
    }
    
    /**
     * Function Name: hide_activity
     * Description: Update STATUS TO SHOW ACTIVITY ON NEWSFEED OR NOT
     */
    function hide_activity_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {   
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');  
            $this->response($return);
        }
        
        $validation_rule =array(
            array(
                'field' => 'ActivityGUID',
                'label' => 'activity guid',
                'rules' => 'trim|required',
            )
        ) ;
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
            $this->response($return);
        }
       
        $activity_guid = safe_array_key($data, 'ActivityGUID');        
        $this->dashboard_model->hide_activity($activity_guid);
        $return['Message'] = 'Status updated successfully.';        
        $this->response($return);
    }
        
}//End of file users.php