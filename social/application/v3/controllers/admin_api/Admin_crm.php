<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin_crm extends Admin_API_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array(
            'admin/media_model',
            'admin/users/crm_model'
        ));
    }

    public function get_user_list_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(isset($data['UserStatus']))  $user_status=$data['UserStatus']; else $user_status= '2';
        $rights_id='';
        if($user_status==2)//Status 2 for Register users
            $rights_id = getRightsId('registered_user');
        else if($user_status==3)//Status 3 for Deleted users
            $rights_id = getRightsId('deleted_user');
        else if($user_status==4)//Status 4 for Blocked users
            $rights_id = getRightsId('blocked_user');
        else if($user_status==1)//Status 2 for Waiting for Approval users
            $rights_id = getRightsId('waiting_for_approval');
        else if($user_status==23)//Status 2 for Waiting for Approval users
            $rights_id = getRightsId('suspended_user');

        if(!in_array($rights_id, getUserRightsData($this->DeviceType))){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED; //'598';
            $return['Message']= lang('permission_denied');
            $this->response($return);
        }

        $return['Data'] = (array) $this->crm_model->get_users($data);
        
        $download = (int) isset($data['Download']) ? $data['Download'] : 0;
        if($download && !empty($return['Data']['users'])) {
            $return['Data'] = $this->crm_model->download_users($return['Data']['users']);
        }
        
        $this->response($return);
    }

    public function get_user_notification_popup_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        $activity_guid  = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
        $is_follower  = isset($data['IsFollower']) ? $data['IsFollower'] : 0;
        if($is_follower && $activity_guid) {
            $is_follow_disabled = $this->settings_model->isDisabled(11);
            if(!$is_follow_disabled) {
                $post_owner = get_detail_by_guid($activity_guid, 0, 'UserID');
                if($post_owner) {
                    $this->load->model(array('users/user_model'));
                    $followers = array();
                    $this->user_model->set_friend_followers_list($post_owner);
                    $followers = $this->user_model->get_followers_list();                      
                    $data['UserIDs'] = $followers;
                    
                }
            }
        }

        $return['Data'] = (array) $this->crm_model->get_users_notification_popup($data);
        
        
        $this->response($return);
    }

    public function location_auto_suggest_get() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (isset($data)) {  
            $search_keyword = isset($data['SearchKeyword']) ? $data['SearchKeyword'] : '' ;
            $page_no        = (isset($data['PageNo']) && $data['PageNo'] > 0) ? $data['PageNo'] : '1' ;
            $page_size      = isset($data['PageSize']) ? $data['PageSize'] : '20' ;           
            $this->load->model(array('util/util_location_model'));
            $return['Data'] = $this->util_location_model->location_auto_suggest($search_keyword, $page_no, $page_size); 
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);   
    }

    function send_notifications_post(){

        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $isSms = !empty($data['isSms']) ? $data['isSms'] : 0;  
            if(empty($isSms)) {
                $config = array(
                    array(
                        'field' => 'notification_title',
                        'label' => 'title',
                        'rules' => 'trim|required'
                    )
                );
            } else {
                $config = array(
                    array(
                        'field' => 'notification_title',
                        'label' => 'title',
                        'rules' => 'trim'
                    )
                );
            }
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {   
                $data['LocalityID'] = 0;
                $data['PageSize'] = 0;  
                $notification_text = !empty($data['notification_text']) ? $data['notification_text'] : '';   
                $notification_text = trim(strip_tags($notification_text));
                $notification_title = !empty($data['notification_title']) ? $data['notification_title'] : '';
                $title = trim(strip_tags($notification_title));
                $data['notification_title'] = $title;
                $data['notification_text'] = $notification_text;
                
                $source = !empty($data['Source']) ? $data['Source'] : 1;     
                $all_user_selected = isset($data['allUserSelected']) ? $data['allUserSelected'] : 0;
                $activity_guid  = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
                $quiz_guid  = isset($data['QuizGUID']) ? $data['QuizGUID'] : 0;
                $req_data = $data;
                $req_data['OnlyCount'] = 1;

                $data['PushNotification'] = array("EntityID" => "", "ModuleID" => "", "ModuleEntityGUID" => "", "Refer" => "UPDATE_APP", "EntityGUID" => "");
                $current_date_time = get_current_date('%Y-%m-%d %H:%i:%s');  
                $communication_history_data = array('CreatedDate' => $current_date_time);
                $communication_data = array();
                $entity_id = 0;
                if(!empty($activity_guid)) {
                    $is_follower  = isset($data['IsFollower']) ? $data['IsFollower'] : 0;
                    

                    $this->db->select('A.ActivityID, A.UserID');
                    $this->db->select("CONCAT(IFNULL(US.FirstName,''), ' ',IFNULL(US.LastName,'')) as SenderName");
                    $this->db->from(ACTIVITY .' A ');
                    $this->db->join(USERS . ' US', "US.UserID = A.UserID");
                    $this->db->where('A.ActivityGUID', $activity_guid); 
                    $this->db->where('A.StatusID', 2);
                    $this->db->limit(1);
                    $query = $this->db->get();
                    $activity_data = $query->row_array();
                    if (!empty($activity_data)) {
                        $post_owner = $activity_data['UserID']; 
                        $entity_id = $activity_id = $activity_data['ActivityID'];   
                        $sender_name = $activity_data['SenderName'];
                        if($activity_id) {                            
                            $data['ActivityID'] = $activity_id;
                            $communication_data['ActivityID'] =  $activity_id;
                            $data['PushNotification'] = array("EntityID" => $activity_id, "EntityGUID" => $activity_guid, "Refer" => "ACTIVITY");
                                               
                        } 

                        if($is_follower) {
                            $is_follow_disabled = $this->settings_model->isDisabled(11);
                            if(!$is_follow_disabled) {
                                if($post_owner) {
                                    $this->load->model(array('users/user_model'));
                                    $followers = array();
                                    $this->user_model->set_friend_followers_list($post_owner);
                                    $followers = $this->user_model->get_followers_list();  
                                    
                                    $data['UserIDs'] = $followers;
                                    $req_data['UserIDs'] = $followers;
                                }
                            }
                        }
                    }   
                    $query->free_result();        
                } else if(!empty($quiz_guid)) {
                    $quiz = get_detail_by_guid($quiz_guid, 47, 'QuizID', 2);
                    if($quiz) {
                        $entity_id = $quiz['QuizID'];
                        $data['QuizID'] = $entity_id;
                        $communication_data['QuizID'] =  $entity_id;
                        $data['PushNotification'] = array("EntityID" => $entity_id, "EntityGUID" => $quiz_guid, "Refer" => "QUIZ");
                    }
                }

                $total_notification = 0;
                if($all_user_selected == 1) {                      
                    if(in_array($source, array(2, 6, 8))) {
                        $result =  $this->crm_model->get_users_notification_popup($req_data);                        
                    } else {
                        $result =  $this->crm_model->get_users($req_data);
                    }   
                                
                    $total_notification = $result['total'];     
                } else {
                    $user_ids = isset($data['UserIDs']) ? $data['UserIDs'] : array(); 
                    $total_notification = count($user_ids);
                }
                

                if(!empty($entity_id)) {
                    $type = ($isSms == 0) ? 1 : 2;
                    $communication_data['Type'] = $type;
                    $communication_data['CreatedDate'] = $current_date_time;
                    $communication_data['ModifiedDate'] = $current_date_time;
                       
                    if(in_array($source, array(2, 6, 8))) {
                        if ($isSms == 0) {
                            $communication_data['PushNotificationTitle'] =  $title;
                            $communication_data['PushNotificationText'] =  $notification_text;
                        } else {
                            $communication_data['SmsText'] =  $notification_text;
                        }
                    }
                    if($source == 6) {
                        $communication_data['IsActivityDashboard'] =  1;
                    }
                    $this->load->model('admin/communication/communication_model');
                    $communication_id = $this->communication_model->add_communication($communication_data, $total_notification);
                    $communication_history_data['AdminCommunicationID'] = $communication_id; 
                }

                $data['CommunicationHistory'] = $communication_history_data;
                      
                /* $user_ids = isset($data['UserIDs']) ? $data['UserIDs'] : array();  
                $all_user_selected = isset($data['allUserSelected']) ? $data['allUserSelected'] : 0;
                if($all_user_selected == 1) {
                    $user_id_str =  $this->crm_model->get_users($data, true);
                    $user_ids = explode(',', $user_id_str);
                }
                $activity_guid  = isset($data['ActivityGUID']) ? $data['ActivityGUID'] : 0;
                $activity_id = get_detail_by_guid($activity_guid, 0, 'ActivityID');
                $push_notification = array("EntityID" => $activity_id, "EntityGUID" => $activity_guid, "Refer" => "ACTIVITY");
                
                $users =  $this->crm_model->get_users($data, false, false, 'U.PhoneNumber');  
                $return['Data'] = $users;   
                */   
               // $this->crm_model->send_notification($data);die;                        
                initiate_worker_job('send_notification', $data, '', 'custom_notification');               
            }       
        } else {
          $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
          $return['Message'] = lang('input_invalid_format');
        } 

        
 
        //
       //--------------------------------------
       /*$Query = $this->db->query("SELECT UserID,DeviceToken, DeviceTypeID FROM `ActiveLogins` WHERE UserID IN (".$useridsString.") AND DeviceToken!='' GROUP BY DeviceToken, DeviceTypeID ");
        if ($Query->num_rows() > 0) {
            foreach ($Query->result_array() as $Notifications) { 
                $token = $Notifications['DeviceToken'];
                $push_notification = array("EntityID" => "", "ModuleID" => "", "ModuleEntityGUID" => "", "Refer" => "UPDATE_APP", "EntityGUID" => "");
                $message = $notification_text;
                //$return['Data']['googleResponse'] = push_notification_android(array($token), $message, 0, array("PushNotification" => $push_notification));                 
                                
            }
        } */
       //-------------------------------------- 
         $this->response($return);
        
    } 

    public function get_top_following_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(isset($data['UserStatus']))  $user_status=$data['UserStatus']; else $user_status= '2';
        $rights_id='';
        if($user_status==2)//Status 2 for Register users
            $rights_id = getRightsId('registered_user');

        if(!in_array($rights_id, getUserRightsData($this->DeviceType))){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED; //'598';
            $return['Message']= lang('permission_denied');
            $this->response($return);
        }

        $return['Data'] = (array) $this->crm_model->get_top_following($data);
        
        
        
        $this->response($return);
    }

    public function get_top_followed_post() {
        /* Define variables - starts */
        $return = $this->return;
        /* Gather Inputs - starts */
        $data = $this->post_data;
        if (empty($data)) {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
            $this->response($return);
        }

        //Set rights id by action(register,delete,blocked,waiting for approval users)
        if(isset($data['UserStatus']))  $user_status=$data['UserStatus']; else $user_status= '2';
        $rights_id='';
        if($user_status==2)//Status 2 for Register users
            $rights_id = getRightsId('registered_user');

        if(!in_array($rights_id, getUserRightsData($this->DeviceType))){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED; //'598';
            $return['Message']= lang('permission_denied');
            $this->response($return);
        }

        $return['Data'] = (array) $this->crm_model->get_top_followed($data);
        
        
        
        $this->response($return);
    }

    function send_notifications_to_top_contributor_post() {
        $return = $this->return;
        $data = $this->post_data;
        if (isset($data)) {
            $config = array(
                array(
                    'field' => 'Title',
                    'label' => 'title',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = $error;
            } else {   
                $notification_text = safe_array_key($data, 'Description', '');   
                $notification_text = trim(strip_tags($notification_text));
                $notification_title = $data['Title'];                
                $title = trim(strip_tags($notification_title));

                $url = safe_array_key($data, 'Url');
                $tag_id         = safe_array_key($data, 'TagID', 0);
                $activity_guid  = safe_array_key($data, 'ActivityGUID');
                $user_guid      = safe_array_key($data, 'UserGUID');
                $quiz_guid      = safe_array_key($data, 'QuizGUID');
                $custom_url     = safe_array_key($data, 'CustomUrl');

                $data = array();
                $data['NotificationTitle'] = $title;
                $data['NotificationText'] = $notification_text;
                if($url=='UPDATE_APP') {
                    $data['PushNotification'] = array("EntityID" => "",  "EntityGUID" => "", "ANDROID_VERSION" => ANDROID_VERSION, "IOS_VERSION" => IOS_VERSION, "Refer" => "UPDATE_APP");
                } else if($url=='POST') {
                    $data['PushNotification'] = array("EntityID" => '', "EntityGUID" => $activity_guid, "Refer" => "ACTIVITY");
                } else if($url=='PROFILE') {
                    $data['PushNotification'] = array("EntityID" => '', "EntityGUID" => $user_guid, "Refer" => "USER");
                } else if($url=='POST_TAG') {
                    $data['PushNotification'] = array("EntityID" => $tag_id, "EntityGUID" => $tag_id, "Refer" => "POST_TAG");
                } else if($url=='CLASSIFIED_CATEGORY') {
                    $data['PushNotification'] = array("EntityID" => $tag_id, "EntityGUID" => $tag_id, "TagID" => 6, "Refer" => "CLASSIFIED_CATEGORY");
                } else if($url=='QUIZ') {
                    $data['PushNotification'] = array("EntityID" => '', "EntityGUID" => $quiz_guid, "Refer" => "QUIZ");
                } else {
                    $data['PushNotification'] = array("EntityID" => '', "EntityGUID" => '', "Refer" => $url);
                } 
                //$return['Data'] = $data;
                //$this->crm_model->top_contributor_notification($data);                        
                initiate_worker_job('top_contributor_notification', $data, '', 'custom_notification');
                
            }
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        } 
        $this->response($return);

    }
}
//End of file ipsetting.php
