<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Comment extends Common_API_Controller {

    function __construct($bypass = false) {    
        parent::__construct($bypass);
        
        $this->load->model(array(
            'users/user_model', 
            'comment/comment_model', 
        ));
    }
       
    public function set_solution_post() {
         /* Define variables - starts */
         $return     = $this->return;
         $data       = $this->post_data;
         if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'CommentGUID',
                    'label' => 'comment guid',
                    'rules' => 'trim|required',
                )
            ) ;
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error  = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
                $this->response($return);
            }
            $user_id    = $this->UserID;
            $comment_guid = $data['CommentGUID'];        
            $comment_details = get_detail_by_guid($comment_guid, 20, "PostCommentID, UserID, EntityID, EntityType, IsPointAllowed", 2);
            if(!empty($comment_details)){
                $comment_id = $comment_details['PostCommentID'];
                $entity_id   = $comment_details['EntityID'];
                $entity_type = $comment_details['EntityType'];
                
                $entity_details = get_detail_by_id($entity_id, 0, 'UserID, PostType', 2);
                $post_type = $entity_details['PostType'];
                $entity_owner_id = $entity_details['UserID'];
                if($post_type == 2) {
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                    if($is_super_admin || $user_id == $entity_owner_id) {
                        $solution = safe_array_key($data, 'Solution', 0);                   
                        if(!in_array($solution, array(0, 1, 2))) {
                            $solution = 0;
                        }
                        
                        $comment_details['Solution'] =  $solution;
                        $comment_details['FromAdmin'] =  0;
                        $this->comment_model->set_solution($comment_details, $user_id);
                        if($entity_type == 'ACTIVITY') {
                            $this->comment_model->mark_question_solution($entity_id, $solution);
                        }       
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "comment guid");
            }            
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);        
    }
    
        /**
     * Function Name: get_responses
     * Description: Get list of all commnets on particular activity
     */
    public function get_responses_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = isset($this->UserID) ? $this->UserID : 0;
        if (isset($data)) {
            $validation_rule = array(
                                    array(
                                        'field' => 'EntityGUID',
                                        'label' => 'entity GUID',
                                        'rules' => 'trim|required'
                                    ),
                                    array(
                                        'field' => 'EntityType',
                                        'label' => 'entity type',
                                        'rules' => 'trim|required|in_list[ACTIVITY,MEDIA]'
                                    )
                                 );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error; //Shows all error messages as a string
            } else {
                $page_no = safe_array_key($data, 'PageNo', PAGE_NO);
                
                $entity_guid = $data['EntityGUID'];
                $entity_type = $data['EntityType'];
                $entity_id = 0;
                $is_owner = 0;

                switch ($entity_type) {
                    case 'MEDIA':
                        // Get details for media
                        $entity = get_detail_by_guid($entity_guid, 21, "MediaID, UserID, ModuleID, ModuleEntityID", 2);
                        if (!empty($entity)) {
                            $entity_id = $entity['MediaID'];                            
                            if ($entity['UserID'] == $user_id) {
                                $is_owner = 1;
                            }
                        }                        
                        break;
                    default:
                        // Get details for activity
                        $entity = get_detail_by_guid($entity_guid, 0, "ActivityID, UserID, ModuleID, ModuleEntityID", 2);
                        if (!empty($entity)) {
                            $entity_id = $entity['ActivityID'];
                            if ($entity['UserID'] == $user_id || ($entity['ModuleID'] == 3 && $entity['ModuleEntityID'] == $user_id)) {
                                $is_owner = 1;
                            }
                        }
                        break;
                }

                if ($entity_id) {
                    $data['ModuleID'] = $entity['ModuleID'];
                    $data['ModuleEntityID'] = $entity['ModuleEntityID'];
                    $data['EntityID'] = $entity_id;
                    $data['IsOwner'] = $is_owner;
                    $return['TotalRecords'] = 0;
                    $return['Data'] = $this->comment_model->get_responses($user_id, $data);
                    if($page_no == 1) {
                        $return['TotalRecords'] = $this->comment_model->get_responses($user_id, $data, 1);
                    }                    
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "entity guid");
                }
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }

    /**
     * Point allow or disallow for comment
     */
    public function point_allowed_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        if (isset($data)) {
           $validation_rule =array(
               array(
                   'field' => 'CommentGUID',
                   'label' => 'comment guid',
                   'rules' => 'trim|required',
               )
           ) ;
           $this->form_validation->set_rules($validation_rule);
           if ($this->form_validation->run() == FALSE) {
               $error  = $this->form_validation->rest_first_error_string();
               $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
               $return['Message'] = $error;
               $this->response($return);
           }
           $user_id    = $this->UserID;
           $comment_guid = $data['CommentGUID'];        
           $comment_details = get_detail_by_guid($comment_guid, 20, "PostCommentID, UserID, ParentCommentID", 2);
           if(!empty($comment_details)){              
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {
                    $is_point_allowed = safe_array_key($data, 'IsPointAllowed', 0);                   
                    if(!in_array($is_point_allowed, array(0, 1))) {
                        $is_point_allowed = 0;
                    }
                    
                    $comment_details['IsPointAllowed'] =  $is_point_allowed;
                    $this->comment_model->point_allowed($comment_details, $user_id);      
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }               
           } else {
               $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
               $return['Message'] = sprintf(lang('valid_value'), "comment guid");
           }            
       } else {
           $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
           $return['Message'] = lang('input_invalid_format');
       }
       $this->response($return);        
    }

    /**
     * Used to get admin tool details for an comment
     */
    public function admin_tool_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'CommentGUID',
                    'label' => 'comment guid',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                if($is_super_admin) {
                    $comment = get_detail_by_guid($data['CommentGUID'],20,'PostCommentID, UserID', 2);
                    if(!empty($comment)) {
                        $comment_id = $comment['PostCommentID'];
                        $user_id = $comment['UserID'];
                        
                        $comment['IsAmazing'] =  $this->comment_model->is_amazing($comment_id);
                        $user_details = $this->user_model->profile($user_id);
                        $comment['UserDetails'] = array(
                            'DOB' => $user_details['DOB'],
                            'IsDOBApprox' => $user_details['IsDOBApprox'],
                            'IncomeLevel' => $user_details['IncomeLevel'],                        
                            'Gender' => $user_details['Gender']
                        );
                        $this->load->model(array('admin/dashboard/dashboard_activity_model'));
                        $comment['UserTags']     = (array)$this->dashboard_activity_model->get_entity_tags($user_id, 'USER');
                        
                        
                        unset($comment['UserID']);
                        unset($comment['PostCommentID']);
                        $return['Data'] = $comment;
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = sprintf(lang('valid_value'), "activity guid");
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
        /* Final Output */
        $this->response($return);
        
    }

    /**
     * Used to set is amazing flag for comment
     */
    public function toggle_amazing_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        $user_id    = $this->UserID;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'CommentGUID',
                    'label' => 'comment guid',
                    'rules' => 'trim|required'
                ),
                array(
                    'field' => 'IsAmazing',
                    'label' => 'amazing',
                    'rules' => 'trim|in_list[0,1]'
                )
            );
            $this->form_validation->set_rules($validation_rule);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {
                $comment_details = get_detail_by_guid($data['CommentGUID'], 20, "PostCommentID, UserID, EntityID, EntityType, IsPointAllowed", 2);
                if(!empty($comment_details)){
                    $comment_id = $comment_details['PostCommentID'];
                    $this->load->model(array(                    
                        'users/user_model'
                    ));
                    $is_super_admin = $this->user_model->is_super_admin($user_id, 1);
                    if($is_super_admin) {
                        $data['CommentID'] = $comment_id;
                        $data['IsPointAllowed'] = $comment_details['IsPointAllowed'];
                        $data['EntityID'] = $comment_details['EntityID'];
                        $data['UserID'] = $comment_details['UserID'];
                        $this->comment_model->toggle_amazing($data);
                    } else {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = lang('permission_denied');
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "comment guid");
                } 
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }      
        $this->response($return);        
    }

    public function amazing_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        //$return['Ar'] = 0;
        if (isset($data)) {  
            $data['PageNo'] = safe_array_key($data, 'PageNo', 1);
            $data['PageSize'] = safe_array_key($data, 'PageSize', PAGE_SIZE);
            
            $data['UserID'] = $user_id;
            $data['CountOnly'] = 0;
            $return['Data'] = $this->comment_model->amazing($data);            

           /* if (count($return['Data']) == 0 || count($return['Data']) < $data['PageSize']) { //check for archival script.
                $this->comment_model->load_archive_db_instance();     
                $data['CountOnly'] = 1;           
                $ar_cnt = $this->comment_model->idea_for_better_indore($data);
                            
                if($ar_cnt > 0) {
                    $return['Ar'] = 1;
                }
                
            }   
            */

        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);        
    }
}