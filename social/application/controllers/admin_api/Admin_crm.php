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
      /* Gather Inputs - starts */
      $data = $this->post_data;
      $notification_text =   $data['notification_text'];
      $isSms =   $data['isSms'];
      $subject ='Notification from admin ';
      $notification_data = $data;
      $useridsString = $data['users'];
       
       initiate_worker_job('send_notification', array('useridsString'=>$useridsString, 'notification_text'=>$notification_text,'isSms'=>$isSms), '', 'notification');

       $return['Data']=true;
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
}
//End of file ipsetting.php
