<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Messages extends Common_API_Controller 
{

    function __construct() 
    {
        parent::__construct();
        $this->check_module_status(25);
        $this->load->model('users/friend_model','messages/messages_model');
    }

    /**
     * Function Name: inbox
     
     * @param ModuleID
     * @param ModuleEntityGUID
     * @param PageNo
     * @param PageSize 
     * Description: User(s) Inbox
     */
    public function inbox_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['TotalRecords'] = 0;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/inbox';
        $return['Message'] = lang('success');

        $user_id    = $this->UserID;
        $data       = $this->post_data;

        $data['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : '' ;

        if($module_id)
        {
            $validation_rule[] = array(
                'field' => 'ModuleEntityGUID',
                'label' => 'ModuleEntityGUID',
                'rules' => 'trim|required'
            );
        }
        
        $show_value = FALSE;
        if(isset($validation_rule) && !empty($validation_rule))
        {
            $this->form_validation->set_rules($validation_rule);            
            $show_value = TRUE;
        }
        if ($this->form_validation->run() == FALSE && $show_value == TRUE) 
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {            
            $this->load->model('messages/messages_model');            
            $return['TotalRecords'] = $this->messages_model->inbox($user_id, $data, TRUE);
            $return['Data'] = $this->messages_model->inbox($user_id, $data);
            $return['UnreadThreadCount'] = 0;
            if($return['Data'])
            {
                foreach($return['Data'] as $key => $thread)
                {
                    if($thread['InboxNewMessageCount']>0)
                    {
                        $return['UnreadThreadCount'] = $return['UnreadThreadCount']+1;
                    }
                    
                    $return['Data'][$key]['Recipients'] = $this->messages_model->get_recipients($thread['ThreadGUID'], $user_id);

                    /*added by gautam*/
                    /* if($this->IsApp == 1){ 
                        / * For page data  - added by gautam - starts * /
                        $thread['PageData'] = array("PageGUID"=>$thread['PageGUID'], "PageTitle"=>$thread['PageTitle'], "Owner"=>0, "ProfilePicture"=>'');
                        $thread['Recipients'] = $this->messages_model->get_recipients($thread['ThreadGUID'], $user_id);
                        foreach ($thread['Recipients'] as $value) {
                            if($value['UserGUID']!=$this->LoggedInGUID){
                                $thread['PageData']['Title'] = trim($value['FirstName'].' '.$value['LastName']);
                                $thread['PageData']['ProfilePicture'] = $value['ProfilePicture'];
                            }
                        }
                        if(!empty($thread['PageOwnerUserID']) && $user_id == $thread['PageOwnerUserID']){
                            $thread['PageData']['Owner'] = 1;
                        }else{ /*if not page owner then show pagepicture* /
                            $thread['PageData']['ProfilePicture'] = $thread['PageProfilePicture'];
                        }
                        unset($thread['Recipients']);
                        unset($thread['ThreadSubject']);
                        unset($thread['ThreadImageName']);
                        unset($thread['Body']);
                        unset($thread['Subject']);
                        //unset($thread['SenderUserGUID']);
                        unset($thread['PageTitle']);
                        unset($thread['PageOwnerUserID']);
                        unset($thread['PageProfilePicture']);
                        $return['Data'][$key] = $thread;
                    }else{
                        $return['Data'][$key]['Recipients'] = $this->messages_model->get_recipients($thread['ThreadGUID'], $user_id);
                    } */

                }
            }
        }       
        $this->response($return);
    }

    /**
     * Function Name: compose
     
     * @param ModuleID
     * @param ModuleEntityGUID
     * @param Replyable
     * @param Subject
     * @param Body
     * @param Media
     * @param Recipients
     * Description: Send Message to User(s)
     */
    public function compose_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/compose';
        $return['Message'] = lang('msg_sent_success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $recipients = isset($data['Recipients']) ? $data['Recipients'] : array() ;
        $recipients_count = count($recipients);

        $r_array = array();
        if($recipients)
        {
            foreach ($recipients as $user) 
            {
                if(is_valid_user($user['UserGUID']))
                {
                    $r_array[] = $user;
                }
            }
        }
        $recipients = $r_array;

        if($recipients_count == 0)
        {
            $validation_rule[] = array(
                'field' => 'ModuleID',
                'label' => 'ModuleID',
                'rules' => 'trim|required'
            );
            $validation_rule[] = array(
                'field' => 'ModuleEntityGUID',
                'label' => 'ModuleEntityGUID',
                'rules' => 'trim|required'
            );
        }

        $validation_rule[] = array(
            'field' => 'Replyable',
            'label' => 'Replyable',
            'rules' => 'trim|required|less_than[3]'
        );

        if(!(isset($data['Media'])) || empty($data['Media']))
        {
            $validation_rule[] = array(
                'field' => 'Body',
                'label' => 'Body',
                'rules' => 'trim|required'
            );
        }

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) 
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $recipients_count = count($recipients);
            if($recipients_count == 0 && (empty($data['ModuleID']) || empty($data['ModuleEntityGUID'])))
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('empty_recipients');
                $this->response($return);
            }

            $this->load->model('messages/messages_model');

            $recipients = array();
            if(isset($data['Recipients']) && !empty($data['Recipients']))
            {
                $can_send_message = 1;
                if(count($data['Recipients'])==1){
                    $recp_buid = $data['Recipients'][0]['UserGUID'];
                    $recpt_id = get_detail_by_guid($recp_buid, 3);
                    
                    if(check_blocked_user($user_id, 3, $recpt_id)){
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = 'Action not allowed';
                        $this->response($return);
                    } else {
                        $users_relation = get_user_relation($user_id, $recpt_id);
                        $privacy_details = $this->privacy_model->details($recpt_id);
                        $privacy = ucfirst($privacy_details['Privacy']);
                        
                        if ($privacy_details['Label']) {
                            foreach ($privacy_details['Label'] as $privacy_label) {
                                if(isset($privacy_label[$privacy])) {
                                    if ($privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)) {
                                        $can_send_message = 0;
                                    }
                                }
                            }
                        }
                        if($can_send_message==0){
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = 'You can not send message to this user';
                            $this->response($return);
                        }
                    }
                }
                foreach ($data['Recipients'] as $Key => $Value) 
                {
                    $recipients[] = array(
                        'UserGUID'=>$Value['UserGUID'], 
                        'UserID' => get_detail_by_guid($Value['UserGUID'], 3),
                    );  
                }                
            } 
            else if(!empty($data['ModuleID']) && !empty($data['ModuleEntityGUID']))
            {
                $recipients = $this->messages_model->get_entity_users($data['ModuleID'],$data['ModuleEntityGUID']);
            }
            $data['Recipients']=$recipients;
            if(!empty($data['Recipients']))
            {
                $return['Data'] = $this->messages_model->compose($user_id, $data);                
            } 
            else 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('empty_recipients');
            }
        }       
        $this->response($return);
    }

    /**
     * Function Name: reply
     
     * @param ThreadGUID
     * @param Body
     * @param Media[]
     * Description: Reply to particular thread
     */
    public function reply_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/reply';
        $return['Message'] = lang('msg_sent_success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );

        /*if(!isset($data['Body']) || empty($data['Body']))
        {
            $validation_rule[] = array(
                'field' => 'Media',
                'label' => 'media',
                'rules' => 'required'
            );
        }*/

        if(!(isset($data['Media'])) || empty($data['Media']))
        {
            $validation_rule[] = array(
                'field' => 'Body',
                'label' => 'Body',
                'rules' => 'trim|required'
            );
        }

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $this->load->model('messages/messages_model');
            
            $check_permission = $this->messages_model->check_thread($user_id, $data['ThreadGUID']);
            if($check_permission)
            {
                $return['Data'] = $this->messages_model->reply($user_id, $data);
            } 
            else 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
            
        }
        $this->response($return);
    }

    /**
     * Function Name: message_details
     
     * @param ThreadGUID
     * Description: Get messages list of particular thread
     */
    public function message_details_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/message_details';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'ThreadGUID',
            'rules' => 'trim|required'
        );
        
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {      
            $data['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : 1 ;
            $data['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : 10 ;
            $thread_guid = $data['ThreadGUID'];
            $this->load->model('messages/messages_model');     

            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if($check_permission)
            {
                $return['Data']['Messages'] = $this->messages_model->thread_message_list($user_id, $data);
            } 
            else 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }       
        $this->response($return);
    }

    /**
     * Function Name: thread_message_list
     
     * @param ThreadGUID
     * @param PageNo
     * @param PageSize 
     * Description: Message list of a thread
     */
    public function thread_details_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/thread_details';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'ThreadGUID',
            'rules' => 'trim|required'
        );
        
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {           
            $thread_guid = $data['ThreadGUID'];
            $data['PageNo'] = 1;
            $data['PageSize'] = 10;
            $this->load->model('messages/messages_model');     

            $check_permission = $this->messages_model->check_thread($user_id,$thread_guid);
            if($check_permission)
            {
                $recipients = $this->messages_model->get_recipients($thread_guid, $user_id);
                $return['Data'] = $this->messages_model->get_thread_details($user_id, $thread_guid, $recipients);
                $return['TotalMessages'] = $this->messages_model->thread_message_list($user_id, $data, TRUE);
                $return['Data']['Messages'] = $this->messages_model->thread_message_list($user_id, $data);
                $return['Data']['Recipients'] = $recipients;
                $this->messages_model->change_thread_status($user_id, $thread_guid, 'READ');
            } 
            else 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }       
        $this->response($return);
    }

    /**
     * Function Name: change_thread_status
     
     * @param ThreadGUID
     * @param Status
     * Description: Change status of particular thread for particular user
     */
    public function change_thread_status_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/change_thread_status';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );
        $validation_rule[] = array(
            'field' => 'Status',
            'label' => 'status',
            'rules' => 'trim|required'
        );

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE)
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $this->load->model('messages/messages_model');
            $thread_guid = $data['ThreadGUID'];
            $Status     = $data['Status'];
            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if($check_permission)
            {
                $this->messages_model->change_thread_status($user_id, $thread_guid, $Status);
            } 
            else 
            {
                $return['Message'] = lang('permission_denied');
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            }            
        }
        $this->response($return);
    }

    /**
     * Function Name: change_unseen_to_seen
     
     * Description: Change unseen messages to seen for particular user
     */
    public function change_unseen_to_seen_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/change_unseen_to_seen';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;

        $this->load->model('messages/messages_model');  
        $this->messages_model->change_unseen_to_seen($user_id);

        $this->response($return);
    }
  
    /**
     * Function Name: delete
     
     * @param MessageGUID
     * Description: Delete particular message
     */
    public function delete_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/delete';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'MessageGUID',
            'label' => 'message guid',
            'rules' => 'trim|required'
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE && $show_value == TRUE) 
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $message_guid = $data['MessageGUID'];
            $this->load->model('messages/messages_model');
            $this->messages_model->delete($user_id, $message_guid);
        }
        $this->response($return);
    }

    /**
     * Function Name: edit_thread
     
     * @param ThreadGUID
     * @param Subject
     * @param Media
     * Description: Edit details (Subject, Media) of thread
     */
    function edit_thread_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/edit_thread';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) 
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $subject = isset($data['Subject']) ? $data['Subject'] : '' ;
            $thread_guid = $data['ThreadGUID'];
            $media = isset($data['Media']) ? $data['Media'] : array() ;
            $this->load->model('messages/messages_model');
            $check_permission = $this->messages_model->check_thread($user_id,$thread_guid);
            if($check_permission)
            {
                $return['Data'] = $this->messages_model->edit_thread($user_id,$thread_guid,$subject,$media);
            } 
            else 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: add_participant
     
     * @param ThreadGUID
     * @param Recipients[]
     * Description: Add participants in particular thread
     */
    function add_participant_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/add_participant';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );

        $validation_rule[] = array(
            'field' => 'Recipients[]',
            'label' => 'recipients',
            'rules' => 'required'
        );

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) 
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $thread_guid = $data['ThreadGUID'];
            $recipients = $data['Recipients'];
            $this->load->model('messages/messages_model');
            $check_permission = $this->messages_model->check_thread($user_id,$thread_guid);
            if($check_permission)
            {
                $return['Data'] = $this->messages_model->add_participant($user_id,$thread_guid,$recipients);
            } 
            else 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: remove_participant
     
     * @param ThreadGUID
     * @param Recipients[]
     * Description: Remove participants from group
     */
    function remove_participant_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/remove_participant';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );

        $validation_rule[] = array(
            'field' => 'Recipients[]',
            'label' => 'recipients',
            'rules' => 'required'
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) 
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $recipients = $data['Recipients'];
            $thread_guid = $data['ThreadGUID'];
            $this->load->model('messages/messages_model');
            $check_permission = $this->messages_model->check_thread($user_id,$thread_guid);
            if($check_permission)
            {
                $this->messages_model->remove_recipient($user_id,$thread_guid,$recipients);
            } 
            else 
            {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: search_user
     
     * @param SearchKeyword
     * Description: Search list of users
     */
    public function search_user_post()
    {
        /* Define variables - starts */
        $return         = $this->return;     
        $data           = $this->post_data;
        $user_id         = $this->UserID;  
        /* Define variables - ends */
        
        $validation_rule[]      =    array(
            'field' => 'SearchKeyword',
            'label' => 'Search Keyword',
            'rules' => 'trim|required'
        );
       
        
        $this->form_validation->set_rules($validation_rule); 
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) 
        {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } 
        else 
        {
            $this->load->model('messages/messages_model');
            $search_keyword     = $data['SearchKeyword'];
            $hide       = isset($data['Hide']) ? $data['Hide'] : array() ;
            $remove_users = array();
            if($hide)
            {
                foreach($hide as $usr)
                {
                    $remove_users[] = $usr['UserGUID'];
                }
            }
            $return['Data']    = $this->messages_model->getUserList($search_keyword, $user_id, $remove_users);
        }       
        $this->response($return);
    }

    /**
     * Function Name: update_last_message
     
     * @param ThreadGUID
     * Description: Update last message details
     */
    public function update_last_message_post()
    {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'messages/update_last_message';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data   = $this->post_data;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'ThreadGUID',
            'rules' => 'required'
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) 
        {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } 
        else 
        {
            $thread_guid = $data['ThreadGUID'];
            $this->load->model('messages/messages_model');
            $thread_id = $this->messages_model->get_thread_id_by_guid($thread_guid);
            $return['Data'] = $this->messages_model->inbox($user_id, array(), FALSE, $thread_id);
        }
        $this->response($return);
    }

    /**
     * Function Name: sendMessage
     
     * @param Subject
     * @param Body
     * @param PreviousMessageGUID
     * @param Receivers
     * Description: Send Message to User(s)
     */
    public function sendMessage_post(){
        $return['ResponseCode'] = 201;
        $return['Data'] = array();
        $return['ServiceName'] = 'api/messages/sendMessage';
        $return['Message'] = lang('msg_sent_success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $subject = 'No Subject';
        $Body = '';
        $PreviousMessageGUID = 0;
        $Receivers = array();
        if(isset($data['Subject']) && !empty($data['Subject'])){
            $subject = $data['Subject'];
        }
        if(isset($data['Body'])){
            $Body = $data['Body'];
        }
        if(isset($data['PreviousMessageGUID'])){
            $PreviousMessageGUID = $data['PreviousMessageGUID'];
        }
        if(isset($data['Receivers'])){
            $Receivers = $data['Receivers'];
        }
        if($this->form_validation->required($Body)==FALSE){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('body_required');

        } elseif($this->form_validation->required($Receivers)==FALSE && $PreviousMessageGUID=='0'){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('receivers_previousmessage_required');

        } else {
                //Add New Message
                $return['Data'] = $this->messages_model->sendMessage($user_id,$subject,$Body,$PreviousMessageGUID,$Receivers);
        }

        $this->response($return);
    }

    /**
     * Function Name: getMessages
     
     * @param Type
     * @param PageNo
     * @param PageSize
     * Description: Get Messages List
     */
    public function getMessages_post(){
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'api/messages/getMessages';
        $return['Message'] = lang('success'); 

        $user_id = $this->UserID;
        $data = $this->post_data;

        $Type = 'Inbox';
        $PageNo = PAGE_NO;
        $PageSize = PAGE_SIZE;
        $Search = '';


        if(isset($data['Type']) && !empty($data['Type'])){
            $Type = $data['Type'];
        }
        if(isset($data['PageNo']) && !empty($data['PageNo'])){
            $PageNo = $data['PageNo'];
        }
        if(isset($data['PageSize']) && !empty($data['PageSize'])){
            $PageSize = $data['PageSize'];
        }
        if(isset($data['SearchKey']) && !empty($data['SearchKey'])){
            $Search = $data['SearchKey'];
        }

        $return['Data'] = $this->messages_model->getMessages($user_id,$Type,$PageNo,$PageSize,$Search);
        $this->response($return);
    }

    /**
     * Function Name: changeMessageSeenStatus
     
     * Description: Change Message Seen Status of Particular User
     */
    public function changeMessageSeenStatus_post() {
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'api/messages/changeMessageSeenStatus';
        $return['Message'] = lang('success');
        
        $user_id = $this->UserID;
        $this->messages_model->updateUnseenStatus($user_id);
        //echo $this->db->last_query();
        $this->response($return);
    }

    /**
     * Function Name: changeMessageStatus
     
     * @param MessageGUID
     * @param MessageReceiverGUID
     * @param Status
     * Description: Change Message Status
     */
    public function changeMessageStatus_post(){
        $return['ResponseCode'] = 203;
        $return['Data'] = array();
        $return['ServiceName'] = 'api/messages/changeMessageStatus';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $message_guid = '';
        $MessageReceiverGUID = '';
        $Status = '';

        if(isset($data['Status'])){
            $Status = $data['Status'];
        }

        if(isset($data['MessageGUID'])){
            $message_guid = $data['MessageGUID'];
        }

        if(isset($data['MessageReceiverGUID'])){
            $MessageReceiverGUID = $data['MessageReceiverGUID'];
        }

        if($this->form_validation->required($Status)==FALSE){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('status_required');
        } elseif($this->form_validation->required($message_guid)==FALSE && $this->form_validation->required($MessageReceiverGUID)==FALSE){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('message_or_receiver_guid_required');
        } else {
            $this->messages_model->changeMessageStatus($user_id,$Status,$message_guid);
        }

        $this->response($return);
    }

    /**
     * Function Name: changeMessageFlagStatus
     
     * @param MessageGUID
     * @param MessageReceiverGUID
     * @param FlagStatus
     * Description: Change Message Flag Status
     */
    public function changeMessageFlagStatus_post(){
        $return['ResponseCode'] = 203;
        $return['Data'] = array();
        $return['ServiceName'] = 'api/messages/changeMessageFlagStatus';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $message_guid = '';
        $MessageReceiverGUID = '';
        $FlagStatus = '';
        
        if(isset($data['FlagStatus'])){
            $FlagStatus = $data['FlagStatus'];
        }
        if(isset($data['MessageGUID'])){
            $message_guid = $data['MessageGUID'];
        }

        if(isset($data['MessageReceiverGUID'])){
            $MessageReceiverGUID = $data['MessageReceiverGUID'];
        }

        if($this->form_validation->required($FlagStatus)==FALSE){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('flag_status_required');
        } elseif($this->form_validation->required($message_guid)==FALSE && $this->form_validation->required($MessageReceiverGUID)==FALSE){
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = lang('message_or_receiver_guid_required');
        } else {
            $return['Data']['UnreadCount'] = $this->messages_model->changeMessageFlagStatus($user_id,$FlagStatus,$message_guid);
        }

        $this->response($return); 
    }

    /**
     * Function Name: messageDetail
     
     * @param MessageGUID
     * Description: Get thread messages
     */
    public function messageDetails_post(){
        $return['ResponseCode'] = self::HTTP_OK;
        $return['Data'] = array();
        $return['ServiceName'] = 'api/messages/messageDetail';
        $return['Message'] = lang('success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $message_guid = '';
        $validation_rule[]       =    array(
                              'field' => 'MessageGUID',
                              'label' => 'MessageGUID',
                              'rules' => 'required|callback_checkMessageGUID'
                              );
        $this->form_validation->set_rules($validation_rule); 
        
        if($this->form_validation->run() == FALSE) // Check for empty request
        {
            $error = $this->form_validation->rest_first_error_string(); 
            $return['ResponseCode'] = 500;
            $return['Message']      = $error; 
        } 
        else 
        {
            $PageNo         = isset($data['PageNo'])                ? $data['PageNo']               : 0 ;
            $PageSize       = isset($data['PageSize'])              ? $data['PageSize']             : PAGE_SIZE ;
            $message_guid    = $data['MessageGUID'];
            $return['Data']         = $this->messages_model->getMessageDetails($user_id,$message_guid,0,$PageNo,$PageSize);
            $return['TotalRecords'] = $this->messages_model->getMessageDetails($user_id,$message_guid,0,0,0,TRUE);
        }        
        $this->response($return);
    }


    /**
     * Function Name: checkMessageGUID
     
     * @param MessageGUID
     * Description: Check status of message guid
     */
    public function checkMessageGUID($message_guid)
    {
        $status = $this->messages_model->getCurrentMessageStatus($this->UserID,$message_guid);
        if(empty($status))
        {
            $this->form_validation->set_message('checkMessageGUID', lang('invalid_messageguid'));
            return FALSE;
        }
        else if ($status==3) 
        {
           $this->form_validation->set_message('checkMessageGUID', lang('message_deleted'));
           return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
