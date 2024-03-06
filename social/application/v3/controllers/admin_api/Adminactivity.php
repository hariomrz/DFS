<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once APPPATH . 'controllers/api/Activity.php';

class Adminactivity extends Activity {

    function __construct() {
        parent::__construct(true);
        
        $this->load->model(array(
            'admin/activity/activity_entities_model',
            'admin/activity/activity_helper_model'
        ));
        $this->activity_helper_model->setUserSessionData();
    }
    

    public function get_user_activity_entities_post() 
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/activity/get_user_activity_entities';
        $return['Data'] = array();
        $data = $this->post_data;

        // Check data posted
        if (!isset($data) || !$data) {
            $return['ResponseCode'] = '519';
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        /* Validation - starts */
        if ($this->form_validation->run('api/admin/activity/get_activity_entities') == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = 511;
            $return['Message'] = $error; //Shows all error messages as a string
            $this->response($return);
        }

        $page_no = (int) isset($data['page_no']) ? $data['page_no'] : 1;
        $page_size = (int) isset($data['page_size']) ? $data['page_size'] : 20;

        $return['Data'] = $this->activity_entities_model->get_user_activity_entities($data['UserID'], $page_no, $page_size);
        $this->response($return);
    }

    public function dummy_activities_post()
    {
        $this->activity_model->getDummyUserActivities($user_id, $page_no, $page_size, $feed_sort_by, $feed_user, $filter_type, $is_media_exists, $search_keyword, $start_date, $end_date, 0, 0, $reminder_date, $activity_guid, $mentions, $entity_id, $module_id, $activity_type_filter,array(),$view_entity_tags,$role_id,$post_type,$tags, $rules);
    }

    public function is_dummy_user_like_post()
    {
        $return['ResponseCode'] = '200';
        $return['Message'] = lang('success');
        $return['ServiceName'] = 'admin_api/activity/get_user_activity_entities';
        $return['Data'] = array();
        $data = $this->post_data;

        $user_id = isset($data['UserID']) ? $data['UserID'] : '' ;
        $activity_id = isset($data['ActivityID']) ? $data['ActivityID'] : '' ;

        if($user_id && $activity_id)
        {
            $return['Data'] = $this->activity_model->is_liked($activity_id, 'ACTIVITY', $user_id, 3, $user_id);
        }

        $this->response($return);
    }

    public function get_questions_feed_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no    = safe_array_key($data, 'PageNo', 1);
        $page_size    = safe_array_key($data, 'PageSize', 1); 

        if ($page_no == '1') {
            $return['TotalRecords'] = $this->activity_model->get_questions_feed($user_id, $page_no, $page_size, 1, $data);                
            $question_type = safe_array_key($data, 'QuestionType', 1);
            
        }
        $return['Data'] = $this->activity_model->get_questions_feed($user_id, $page_no, $page_size, 0, $data);
        $this->response($return);
    }

    public function set_solution_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        if (isset($data)) {
            $solution_arr = safe_array_key($data, 'Solutions', array());
            if (empty($solution_arr)) {
               $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
               $return['Message'] = "Please provide solutions";
               $this->response($return);
           }
           $user_id    = $this->UserID;
           $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
           if($is_super_admin) {
                $this->load->model(array(
                    'comment/comment_model'
                ));
                $entity_id = 0;
                $entity_type = 'ACTIVITY';
                
                foreach($solution_arr as $solution_data) {
                    $comment_guid = safe_array_key($solution_data, 'CommentGUID', ''); 
                    if(!empty($comment_guid)) {
                        $solution = safe_array_key($solution_data, 'Solution', ''); 
                        $comment_details = get_detail_by_guid($comment_guid, 20, "PostCommentID, UserID, EntityID, EntityType, IsPointAllowed", 2);  
                        if(!empty($comment_details)) {
                            $comment_id = $comment_details['PostCommentID'];
                            $entity_id   = $comment_details['EntityID'];
                            $entity_type = $comment_details['EntityType'];
                            if(!in_array($solution, array(0, 1, 2))) {
                                $solution = 0;
                            }
                            $comment_details['Solution'] =  $solution;
                            $comment_details['FromAdmin'] =  1;
                            $this->comment_model->set_solution($comment_details, $user_id);                             
                        } 
                    }                    
                }

                if($entity_id) {
                    $this->comment_model->mark_question_solution($entity_id, 0);
                } 
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

   public function mark_ready_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
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
            $activity_guid = safe_array_key($data, 'ActivityGUID', '');
            $activity_details = get_detail_by_guid($activity_guid, 0, "ActivityID, UserID", 2);  
            if(!empty($activity_details)) {
                $activity_id = $activity_details['ActivityID'];   
                $owner_id   = $activity_details['UserID'];               
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {   
                    $notification_type_id = 161;                 
                    $row = $this->user_model->get_single_row("NotificationID", NOTIFICATIONS, array('NotificationTypeID' => $notification_type_id, 'ToUserID' => $owner_id, 'RefrenceID' => $activity_id));
                    if (empty($row) && $owner_id != $user_id) {          
                        $parameters[0]['ReferenceID'] = $owner_id;
                        $parameters[0]['Type'] = 'User';
                        $parameters[1]['ReferenceID'] = 1;
                        $parameters[1]['Type'] = 'EntityType';
                        initiate_worker_job('add_notification', array('NotificationTypeID' => $notification_type_id, 'SenderID' => $user_id, 'ReceiverIDs' => array($owner_id), 'RefrenceID' => $activity_id, 'Parameters' => $parameters),'','notification');                
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            } else {                
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "activity guid");
            }
        } else {
           $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
           $return['Message'] = lang('input_invalid_format');
        }
       $this->response($return);

    
    }


    public function update_activity_content_post() {
        /* Define variables - starts */
        $return     = $this->return;
        $data       = $this->post_data;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required',
                ),
                array(
                    'field' => 'PostContent',
                    'label' => 'post content',
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
            $activity_guid = $data['ActivityGUID'];
            $activity_details = get_detail_by_guid($activity_guid, 0, "ActivityID, UserID", 2);  
            if(!empty($activity_details)) {
                $keep_original = safe_array_key($data, 'KeepOriginal', 0);
                $post_content = $data['PostContent']; 
                $activity_id = $activity_details['ActivityID']; 
                $owner_id   = $activity_details['UserID'];               
                $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
                if($is_super_admin) {   
                    $post_content = linkify($post_content); 
                    preg_match_all('/{{([0-9.a-zA-Z\s:]+)}}/', $post_content, $matches_content);        
                    $matches = array();                    
                    if(!empty($matches_content[1])) {
                        $matches = $matches_content[1];
                    }        
                    
                    if (!empty($matches)) {
                        $this->load->model(array(
                            'activity/activity_model'
                        ));
                        $post_content_updated = '';
                        foreach ($matches as $match) {
                            $match_details = explode(':', $match);                            
                            $mention_id = $this->activity_model->add_mention($match_details[1], $match_details[2], $activity_id, $match_details[0]);  
                            if(in_array($match, $matches_content[1])) {   
                                $post_content_updated = 1;
                                $post_content = strtr($post_content, array($match => $mention_id));
                            }                                          
                        }                      
                    }

                    $this->db->set('PostContent',$post_content);
                    $this->db->where('ActivityID', $activity_id);        
                    $this->db->update(ACTIVITY);

                    $notification_type_id = 162;                 
                    //$row = $this->user_model->get_single_row("NotificationID", NOTIFICATIONS, array('NotificationTypeID' => $notification_type_id, 'ToUserID' => $owner_id, 'RefrenceID' => $activity_id));
                    if ($owner_id != $user_id && empty($keep_original)) {          
                        $parameters[0]['ReferenceID'] = $owner_id;
                        $parameters[0]['Type'] = 'User';
                        $parameters[1]['ReferenceID'] = 1;
                        $parameters[1]['Type'] = 'EntityType';
                        initiate_worker_job('add_notification', array('NotificationTypeID' => $notification_type_id, 'SenderID' => $user_id, 'ReceiverIDs' => array($owner_id), 'RefrenceID' => $activity_id, 'Parameters' => $parameters),'','notification');                
                    }
                } else {
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = lang('permission_denied');
                }
            } else {                
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = sprintf(lang('valid_value'), "activity guid");
            }
        } else {
           $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
           $return['Message'] = lang('input_invalid_format');
        }
       $this->response($return);

    
    }

    public function assign_team_member_post() {
        $return     = $this->return;
        $data       = $this->post_data;
        if (isset($data)) {
            $validation_rule =array(
                array(
                    'field' => 'ActivityGUID',
                    'label' => 'activity guid',
                    'rules' => 'trim|required',
                ),
                array(
                    'field' => 'UserID',
                    'label' => 'user id',
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
            $is_super_admin = $this->user_model->is_super_admin($user_id, 2);
            if($is_super_admin) {
                $activity_guid = $data['ActivityGUID'];
                $activity_id = get_detail_by_guid($activity_guid); 
                if($activity_id) {
                    $team_member_id = $data['UserID'];
                    $this->load->model(array(
                        'activity/activity_front_helper_model', 
                    ));
                    $this->activity_front_helper_model->assign_team_member($activity_id, $team_member_id);
                } else {                
                    $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                    $return['Message'] = sprintf(lang('valid_value'), "activity guid");
                } 
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


}

//End of file ipsetting.php