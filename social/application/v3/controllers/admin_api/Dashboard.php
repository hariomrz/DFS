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
        $user_id = (int)isset($data['UserID']) ? $data['UserID'] : $this->UserID;
        $reason = isset($data['Reason']) ? strip_tags($data['Reason']) : '';
        
        $this->dashboard_model->update_entity($module_id, $module_entity_id, $entity_column_val, $entity_column, $user_id, $reason);
        $return['Message'] = 'Status updated successfully.';
        
        $this->response($return);
    }
    
    public function send_activity_notification_post()
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
        $this->dashboard_model->send_activity_notification($module_id, $module_entity_id);
        $return['Message'] = 'Notification sent successfully.';
        
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
     * Function Name: get_daily_digest_activities
     * @param ModuleID
     * @param ModuleEntityID
     * @param Replyable
     * @param Subject
     * @param Body
     * @param Media
     * Description: Get list of activities for daily digest
     */
    public function get_daily_digest_activities_post() {
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
        $PageSize = (int)isset($data['PageSize']) ? $data['PageSize'] : 500;
        
        $this->load->model(array(
            'admin/dashboard/daily_digest_activity_model',
            'users/user_model',
        ));
        $this->user_model->set_user_time_zone($this->UserID);
        
        $entitiesData = $this->daily_digest_activity_model->get_activity_list($PageNo, $PageSize, $data);
        $return['Data'] = isset($entitiesData['entities']) ? $entitiesData['entities'] : [];
        $return['TotalRecords'] = (int)isset($entitiesData['total_count']) ? $entitiesData['total_count'] : 0;
        
        $this->response($return);
    }

    public function get_daily_digest_list_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        
        $this->load->model(array(
            'admin/dashboard/daily_digest_activity_model',
            'activity/activity_model'
        ));
        
        $page_no    = safe_array_key($data, 'PageNo', 1); 
        if($page_no == 1) {
            $data['Count'] = 1;
            $return['TotalRecord'] = $this->daily_digest_activity_model->get_daily_digest_list($data);
        }
        $data['Count'] = 0;
        $return['snb'] = 0;
        $row = $this->activity_model->is_daily_digest_exist();
        $current_date = get_current_date('%Y-%m-%d', 1);
        if(!empty($row) && isset($row['DailyDigestDate'])) {
            if($row['DailyDigestDate'] == $current_date) {
                $return['snb'] = 1;
            }
        }
        $return['Data'] = $this->daily_digest_activity_model->get_daily_digest_list($data);
                
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
        
        $this->load->model(array('admin/dashboard/dashboard_activity_model','activity/activity_model'));
        $page_no = (int)isset($data['PageNo']) ? $data['PageNo'] : 1;
        $page_size = (int)isset($data['PageSize']) ? $data['PageSize'] : 0;
        $user_id = $data['UserID'];
        $activity_id = $data['ActivityID'];
        $comment_id = safe_array_key($data, 'CommentID', 0);  
        $return['Data']['UserDetails'] = (array)$this->dashboard_activity_model->get_user_details($user_id);
        $return['Data']['UserTags'] = (array)$this->dashboard_activity_model->get_entity_tags($user_id, 'USER');
        $return['Data']['IsCommentView'] = 0; 
        if(empty($comment_id)) {
            $return['Data']['ActivityTags'] = (array)$this->dashboard_activity_model->get_entity_tags($activity_id, 'ACTIVITY');        
            $return['Data']['ActivityVisibility'] = $this->activity_model->visibility($activity_id, 1, 100);
            $return['Data']['StoryVisibility'] = $this->dashboard_activity_model->story_visibility($activity_id);
            $activity = get_detail_by_id($activity_id,0,'IsShowOnNewsFeed, IsCityNews', 2);
            $return['Data'] = array_merge($return['Data'], $activity);

            $details = safe_array_key($data, 'Details', 0); 
            if($details) {
                $this->load->model(array(
                    'activity/activity_front_helper_model', 
                ));
                $activity = $this->activity_front_helper_model->get_activity_details($activity_id);
                $return['Data'] = array_merge($return['Data'], $activity);
            }
            
        } else {
            $this->load->model(array(
                'comment/comment_model', 
            ));

            $return['Data']['IsCommentView'] = 1;
            $return['Data']['CommentDetails'] = get_detail_by_id($comment_id, 20, "PostCommentGUID AS CommentGUID, Solution, IsPointAllowed", 2);
            $return['Data']['CommentDetails']['IsAmazing'] =  $this->comment_model->is_amazing($comment_id);
        }
        
        $this->response($return);
    }
    
    

    /**
     * Function Name: change_activity_ward_visibility
     * Description: Used to change activity ward visibility
     */
    function change_activity_ward_visibility_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data))
        {
            $validation_rule =array(
                array(
                    'field' => 'ActivityID',
                    'label' => 'activity id',
                    'rules' => 'trim|required',
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $ward_ids = isset($data['WardIds']) ? $data['WardIds'] : array();
                $activity_id = $data['ActivityID'];
                //if(!empty($ward_ids)) {
                    // $activity_id = get_detail_by_guid($data['ActivityGUID']);
                    if($activity_id) {      
                        $this->load->model(array('activity/activity_model')); 
                        
                        $activity_details = get_detail_by_id($activity_id, 0, "UserID, ModuleID, ModuleEntityID", 2);
                        $is_modified_date = 1;
                        if ($activity_details['ModuleID'] == 3 && $activity_details['ModuleEntityID'] != $activity_details['UserID']) {
                            $is_modified_date = 0;
                        }

                        $this->activity_model->add_activity_ward($activity_id, $ward_ids, $is_modified_date, $activity_details);
                        if(CACHE_ENABLE) {
                            $this->cache->delete('activity_'.$activity_id);
                        }
                        $return['Message'] = 'Post visibility updated successfully.';        
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                    }   
               /* } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('ward_required');
                }  */
            } 
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);
    }

    /**
     * Function Name: save_story
     * Description: Used to save story with visibility
     */
    function save_story_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data))
        {
            $validation_rule =array(
                array(
                    'field' => 'ActivityID',
                    'label' => 'activity id',
                    'rules' => 'trim|required',
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                if($is_super_admin) {
                    $ward_ids = isset($data['WardIds']) ? $data['WardIds'] : array();
                    $activity_id = $data['ActivityID'];
                    if(!empty($ward_ids)) {
                        $activity_id = get_detail_by_id($activity_id, 0, "ActivityID");
                        if($activity_id) {
                            $this->load->model(array(
                                'admin/dashboard/dashboard_activity_model'
                            ));
                            $this->dashboard_activity_model->add_story($activity_id, $ward_ids);                        
                            $return['Message'] = 'Story added successfully.';        
                        } else {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = sprintf(lang('valid_value'), "activity id");
                        }   
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('ward_required');
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

    public function remove_story_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityID',
                    'label' => 'activity id',
                    'rules' => 'trim|required',
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $this->load->model(array(                    
                    'users/user_model'
                ));
                $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                if($is_super_admin) {
                    $activity_id = $data['ActivityID'];                    
                    $activity_id = get_detail_by_id($activity_id, 0, "ActivityID");
                    if($activity_id) {
                        $this->load->model(array(
                            'admin/dashboard/dashboard_activity_model'
                        ));
                        $this->dashboard_activity_model->remove_story($activity_id);                        
                        $return['Message'] = 'This story removed successfully.';        
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "activity id");
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

    function save_daily_digest_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            
            $this->load->model(array(                    
                'users/user_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
            if($is_super_admin) { 
                $this->load->model(array(
                    'admin/dashboard/daily_digest_activity_model'
                ));
                $this->daily_digest_activity_model->save_daily_digest($data);             
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
            
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return); 
    }

    function delete_daily_digest_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            
            $this->load->model(array(                    
                'users/user_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
            if($is_super_admin) { 
                $this->load->model(array(
                    'admin/dashboard/daily_digest_activity_model'
                ));
                $this->daily_digest_activity_model->delete_daily_digest($data);             
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
            
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return); 
    }

    function send_daily_digest_notification_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'notification_text',
                    'label' => 'message',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {  
                $notification_data['notification_text'] = $data['notification_text'];   
                $return['Message'] = "Notification sent successfully";                 
                initiate_worker_job('send_daily_digest_notification', $notification_data, '', 'daily_digest');               
            }       
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return); 
    }

    /**
     * Function Name: get_top_activities
     * Description: Get list of top activities for user orientation
     */
    public function get_top_activities_post() {
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
        $PageSize = (int)isset($data['PageSize']) ? $data['PageSize'] : 500;
        
        $this->load->model(array(
            'admin/dashboard/user_orientation_model',
            'users/user_model',
        ));
        $this->user_model->set_user_time_zone($this->UserID);
        
        $entitiesData = $this->user_orientation_model->get_activity_list($PageNo, $PageSize, $data);
        $return['Data'] = isset($entitiesData['entities']) ? $entitiesData['entities'] : [];
        $return['TotalRecords'] = (int)isset($entitiesData['total_count']) ? $entitiesData['total_count'] : 0;
        
        $this->response($return);
    }

    function save_user_orientation_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            
            $this->load->model(array(                    
                'users/user_model'
            ));
            $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
            if($is_super_admin) { 
                $this->load->model(array(
                    'admin/dashboard/user_orientation_model'
                ));
                $this->user_orientation_model->save_user_orientation($data);             
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
            
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return); 
    }
        
}//End of file users.php