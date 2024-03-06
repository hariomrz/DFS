<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Notifications extends Common_API_Controller
{      
    function __construct()
    {
        parent::__construct();
        $this->check_module_status(9);
        $this->load->model(array('notification_model')); 
    }
    

    function bubble_notifications_post()
    {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $notification_guid  = $data['NotificationGUID'];
        $notification_type  = $data['NotificationTypeID'];
        $current_url        = $data['CurrentURL'];

        $return['Data'] = $this->notification_model->bubble_notifications($user_id,$notification_type,$notification_guid,$current_url);

        $this->response($return);
    }

    /**
     * Function Name: get_notifications_count
     * Description: Get unseen notifications count
     */
    function get_notifications_count_post()
    {
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        
        /* Gather Inputs - starts */
        $data = $this->post_data;   
        $user_id = $this->UserID;         
        /* Gather Inputs - ends */

        $page_no        = isset($data['PageNo'])        ? $data['PageNo']       : PAGE_NO ;
        $page_size      = isset($data['PageSize'])      ? $data['PageSize']     : 30 ;
        $count_only     = isset($data['CountOnly'])    ? $data['CountOnly'] : 0 ;
        $type            = isset($data['Type'])    ? $data['Type'] : '' ;
        

        $return['TotalNotificationRecords'] = 0; 
        if($page_no==1){ /*added by gautam*/
                $return['TotalNotificationRecords'] = $this->notification_model->get_new_notifications($user_id, 0, 0, true);
        }

        if(empty($count_only)) {
            $return['Data'] = $this->notification_model->get_new_notifications($user_id, $page_no, $page_size,false,false,0,$type);     
        }        

        

        $return['IncomingRequestCount'] = 0;
        $return['TotalMessageRecords'] = 0;
        $return['PagesCount'] = 0;
        $return['GroupsCount'] = 0;
        $return['TotalUnread'] = 0;

        if($page_no==1)  {             
            if (!$this->settings_model->isDisabled(25)) {
                $this->load->model('messages/messages_model');
                $return['TotalMessageRecords']  = $this->messages_model->get_total_unseen_count($user_id);
            }
            
            $return['TotalUnread']          = $return['TotalNotificationRecords'];
            $return['TotalRecords']         = $return['TotalNotificationRecords']+$return['TotalMessageRecords'];
        }      
        $this->response($return);
    }

    function get_new_notifications_count_post()
    {
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        
        /* Gather Inputs - starts */
        $data = $this->post_data;   
        $user_id = $this->UserID;         
        /* Gather Inputs - ends */

        $page_no        = isset($data['PageNo'])        ? $data['PageNo']       : PAGE_NO ;
        $page_size      = isset($data['PageSize'])      ? $data['PageSize']     : CONST_PAGE_SIZE ;
        $count_only     = isset($data['CountOnly'])    ? $data['CountOnly'] : 0 ;
        $type            = isset($data['Type'])    ? $data['Type'] : '' ;
        

        $return['TotalNotificationRecords'] = 0;
        if(empty($count_only)) 
        {
            /*if($return['TotalNotificationRecords'] > $page_size) 
            {
                $page_size = $return['TotalNotificationRecords'];
            }*/
            $return['Data'] = $this->notification_model->get_new_notifications($user_id, $page_no, $page_size,false,false,0,$type);
        }        

        $this->load->model('messages/messages_model');
        
        $return['TotalMessageRecords']  = $this->messages_model->get_total_unseen_count($user_id);
        $return['TotalRecords']         = $return['TotalNotificationRecords']+$return['TotalMessageRecords'];
        $return['TotalUnread']          = $return['TotalNotificationRecords'];//$this->notification_model->get_unread_count($user_id);
        
        $this->response($return);
    }

    /**
     * Function Name: list
     * PageNo,PageSize
     * Description: Get list of all notifications
     */
    function list_post()
    {
        /* Define variables - starts */
        $return = $this->return;       
        /* Define variables - ends */
        
        /* Gather Inputs - starts */
        $data = $this->post_data; 
        $user_id = $this->UserID;           
        /* Gather Inputs - ends */
        
        $page_no         = isset($data['PageNo']) ? $data['PageNo'] : PAGE_NO ;
        $page_size       = isset($data['PageSize']) ? $data['PageSize'] : CONST_PAGE_SIZE ;
        $type            = isset($data['Type'])    ? $data['Type'] : '' ;
        
        $return['Data'] = $this->notification_model->get_new_notifications($user_id, $page_no, $page_size, false, false, 0, $type, TRUE);
        if($page_no=='1')
        {
            $return['TotalNotificationRecords'] = $this->notification_model->get_new_notifications($user_id, 0, 0, true, true, 1, $type, TRUE);
        }
        $return['TotalUnread'] = $this->notification_model->get_unread_count($user_id);
        $this->response($return);
    }

    /**
     * Function Name: mark_as_seen
     
     * Description: Mark notifications as seen
     */
    function mark_as_seen_post()
    {
        $return = $this->return;
        $user_id = $this->UserID; 
        $this->notification_model->mark_as_seen($user_id);

        $this->response($return);
    }

    /**
     * Function Name: mark_as_read
     * NotificationGUID
     * Description: Mark notifications as read
     */
    function mark_as_read_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data   = $this->post_data;

        $notification_guid = isset($data['NotificationGUID']) ? $data['NotificationGUID'] : 0 ;

        $this->notification_model->mark_as_read($notification_guid, $user_id);
        $this->response($return);
    }

    /**
     * [mark_all_notifications_as_read_post update status as read of all notification for logged in user]
     * @return [json] [json object]
     */
    function mark_all_notifications_as_read_post()
    {
        $return     = $this->return;
        $user_id    = $this->UserID;
        $data       = $this->post_data;
        $this->notification_model->mark_all_notifications_as_read($user_id);
        $this->response($return);
    }

    /**
     * [get_user_notification_settings_post Used to get user email notification setting]
     * @return [array] [email notification setting]
     */
    function get_user_notification_settings_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data   = $this->post_data;
        $settings = $this->notification_model->get_user_notification_settings($user_id);
        
        unset($settings['AllNotifications']);
        unset($settings['EmailNotifications']);
        unset($settings['MobileNotifications']);
        unset($settings['Modules']);
        $notifications = $settings['Notifications'];
        foreach ($notifications as $notification) {
            if ($notification['NotificationTypeKey'] == 'new_message') {
                unset($notification['Email']);
                unset($notification['DisplayOrder']);
                $settings['Notifications'] = $notification;
                break;
            }
        }            
        $return['Data'] = $settings;
        $this->response($return);
    }

    /**
     * [set_user_notification_settings_post Used to set user notification settings]
     */
    function set_user_notification_settings_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data   = $this->post_data;

        if ($data) {            
            $config = array(
                array(
                    'field' => 'AllNotifications',
                    'label' => 'notification setting',
                    'rules' => 'trim|in_list[0,1]'
                ),
                array(
                    'field' => 'EmailNotifications',
                    'label' => 'email notification setting',
                    'rules' => 'trim|in_list[0,1]'
                ),
                array(
                    'field' => 'MobileNotifications',
                    'label' => 'Mobile notification setting',
                    'rules' => 'trim|in_list[0,1]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {  
                $all_notifications      = $data['AllNotifications'];
                $modules                = isset($data['Modules'])               ? $data['Modules']              : array() ;
                $notifications          = isset($data['Notifications'])         ? $data['Notifications']        : 0;
                $email_notifications    = isset($data['EmailNotifications'])    ? $data['EmailNotifications']   : 0;
                $mobile_notifications   = isset($data['MobileNotifications'])   ? $data['MobileNotifications']  : 0;

                $this->notification_model->set_user_notification_settings($user_id, $all_notifications, $email_notifications, $mobile_notifications, $modules, $notifications);
                $this->return['Message'] = lang('notification_setting_updated');
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }

    /**
     * [update_user_notification_settings Used to set user notification settings]
     */
    function update_user_notification_settings_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data   = $this->post_data;

        if ($data) {            
            $config = array(
                array(
                    'field' => 'Notifications[]',
                    'label' => 'notification setting',
                    'rules' => 'trim|required'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {  
                $notifications          = $data['Notifications'];
                
                $this->notification_model->update_user_notification_settings($user_id, $notifications);
                $this->return['Message'] = lang('notification_setting_updated');
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }
    
    function set_push_notification_settings_post() {
        $return = $this->return;
        $user_id = $this->UserID;
        $data   = $this->post_data; 
        if ($data) {            
            $config = array(
                array(
                    'field' => 'PushNotification',
                    'label' => 'Push notification setting',
                    'rules' => 'trim|required|in_list[0,1]'
                )
            );
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
                $error = $this->form_validation->rest_first_error_string();
                $this->return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $this->return['Message'] = $error;
            } else {            
                $push_notifications   = $data['PushNotification'];
                $this->notification_model->set_push_notification_settings($user_id, $push_notifications);
                $this->return['Message'] = lang('notification_setting_updated');
            }
        } else {
            $this->return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $this->return['Message'] = lang('invalid_format');
        }
        $this->response($this->return);
    }

    /**
     * [archive_users_notifications_post Used to archive notifications of users]
     * @return [array] []
     */
    public function archive_users_notifications_post()
    {
        $return = $this->return;
        $user_id = $this->UserID;
        $data   = $this->post_data;
        $this->notification_model->archive_users_notifications();
        $return['Message'] = 'Archived Successfully';        
        $this->response($return);        
    } 
}
?>
