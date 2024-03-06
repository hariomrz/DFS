<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Messages extends Common_API_Controller {

    function __construct() {
        parent::__construct();
        $this->check_module_status(25);
        $this->load->model('users/friend_model', 'messages/messages_model');
    }

    /**
     * Function Name: inbox

     * @param ModuleID
     * @param ModuleEntityGUID
     * @param PageNo
     * @param PageSize 
     * Description: User(s) Inbox
     */
    public function inbox_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $data['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : 1;
        $module_id = isset($data['ModuleID']) ? $data['ModuleID'] : 3;

        if ($module_id) {
            $validation_rule[] = array(
                'field' => 'ModuleEntityGUID',
                'label' => 'ModuleEntityGUID',
                'rules' => 'trim'
            );
        }

        $show_value = FALSE;
        if (isset($validation_rule) && !empty($validation_rule)) {
            $this->form_validation->set_rules($validation_rule);
            $show_value = TRUE;
        }
        if ($this->form_validation->run() == FALSE && $show_value == TRUE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $this->load->model('messages/messages_model');
            $return['TotalRecords'] = 0;
            if($data['PageNo'] == 1) {
                $return['TotalRecords'] = $this->messages_model->inbox($user_id, $data, TRUE);
            }
            
            $return['Data'] = $this->messages_model->inbox($user_id, $data);
            
            if ($return['Data']) {
                $this->load->model(array('users/user_model'));
                foreach ($return['Data'] as $key => $thread) {                    
                    $recipients = $this->messages_model->get_recipients($thread['ThreadGUID']);
                    $return['Data'][$key]['IsBlocked'] = 0;
                    foreach ($recipients as $recipient_user) {
                        if($user_id != $recipient_user['UserID']) {
                            $return['Data'][$key]['IsBlocked'] = $this->user_model->check_blocked_status($user_id, $recipient_user['UserID']); 
                        }                         
                    }
                    $return['Data'][$key]['Recipients'] = $recipients;
                    /* added by gautam */
                    /* if($this->IsApp == 1){ 
                      / * For page data  - added by gautam - starts * /
                      $thread['PageData'] = array("PageGUID"=>$thread['PageGUID'], "PageTitle"=>$thread['PageTitle'], "Owner"=>0, "ProfilePicture"=>'');
                      $thread['Recipients'] = $this->messages_model->get_recipients($thread['ThreadGUID']);
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
                      $return['Data'][$key]['Recipients'] = $this->messages_model->get_recipients($thread['ThreadGUID']);
                      } */
                }
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: compose
     * Description: Send Message to User(s)
     */
    public function compose_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $return['Message'] = lang('msg_sent_success');

        $user_id = $this->UserID;
        $data = $this->post_data;

        $recipients = isset($data['Recipients']) ? $data['Recipients'] : array();
        $recipients_count = count($recipients);

        $r_array = array();
        $this->load->model('messages/messages_model');
        if ($recipients) {
            foreach ($recipients as $user) {
                if ($this->messages_model->is_valid_user($user['UserGUID'])) {
                    $r_array[] = $user;
                }
            }
        }
        $recipients = $r_array;

        if ($recipients_count == 0) {
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
            'rules' => 'trim|less_than[3]'
        );

        if (!(isset($data['Media'])) || empty($data['Media'])) {
            $validation_rule[] = array(
                'field' => 'Body',
                'label' => 'Body',
                'rules' => 'trim|required'
            );
        }

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $recipients_count = count($recipients);
            if ($recipients_count == 0 && (empty($data['ModuleID']) || empty($data['ModuleEntityGUID']))) {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('empty_recipients');
                $this->response($return);
            }

            $recipients = array();
            if (isset($data['Recipients']) && !empty($data['Recipients'])) {
                $can_send_message = 1;
                if (count($data['Recipients']) == 1) {
                    $recp_buid = $data['Recipients'][0]['UserGUID'];
                    $recpt_id = get_detail_by_guid($recp_buid, 3);

                    if (check_blocked_user($user_id, 3, $recpt_id)) {
                        $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                        $return['Message'] = 'This user can not accept messages';
                        $this->response($return);
                    }  /*else {
                        $users_relation = get_user_relation($user_id, $recpt_id);
                        $privacy_details = $this->privacy_model->details($recpt_id);
                        $privacy = ucfirst($privacy_details['Privacy']);

                        if ($privacy_details['Label']) {
                            foreach ($privacy_details['Label'] as $privacy_label) {
                                if (isset($privacy_label[$privacy])) {
                                    if ($privacy_label['Value'] == 'message' && !in_array($privacy_label[$privacy], $users_relation)) {
                                        $can_send_message = 0;
                                    }
                                }
                            }
                        }
                        if ($can_send_message == 0) {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = 'You can not send message to this user';
                            $this->response($return);
                        }
                        
                    }*/
                } 
                foreach ($data['Recipients'] as $Key => $Value) {
                    $recipients[] = array(
                        'UserGUID' => $Value['UserGUID'],
                        'UserID' => get_detail_by_guid($Value['UserGUID'], 3),
                    );
                }
            } else if (!empty($data['ModuleID']) && !empty($data['ModuleEntityGUID'])) {
                $recipients = $this->messages_model->get_entity_users($data['ModuleID'], $data['ModuleEntityGUID']);
            }
            $data['Recipients'] = $recipients;
            if (!empty($data['Recipients'])) {
                $return['Data'] = $this->messages_model->compose($user_id, $data);
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('empty_recipients');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: reply
     * Description: Reply to particular thread
     */
    public function reply_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $return['Message'] = lang('msg_sent_success');

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );

        if (!(isset($data['Media'])) || empty($data['Media'])) {
            $validation_rule[] = array(
                'field' => 'Body',
                'label' => 'Body',
                'rules' => 'trim|required'
            );
        }

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $this->load->model('messages/messages_model');

            $check_permission = $this->messages_model->check_thread($user_id, $data['ThreadGUID']);
            if ($check_permission) {
                $recipients = $this->messages_model->get_recipients($data['ThreadGUID'], $user_id);
                if (count($recipients) == 1) {
                    foreach ($recipients as $recipient_user) {                        
                        if (check_blocked_user($user_id, 3, $recipient_user['UserID'])) {
                            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                            $return['Message'] = 'This user can not accept messages';
                            $this->response($return);
                        }                           
                    }
                }
                
                $return['Data'] = $this->messages_model->reply($user_id, $data);
            } else {
                $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
                $return['Message'] = lang('permission_denied');
            }
        }
        $this->response($return);
    }

    /**
     * Function Name: details
     * @param ThreadGUID
     * Description: Get messages list of particular thread
     */
    public function details_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'ThreadGUID',
            'rules' => 'trim|required'
        );

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $data['PageNo'] = isset($data['PageNo']) ? $data['PageNo'] : 1;
            $data['PageSize'] = isset($data['PageSize']) ? $data['PageSize'] : 10;
            $thread_guid = $data['ThreadGUID'];
            $this->load->model('messages/messages_model');

            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if ($check_permission) {
                $return['Data'] = $this->messages_model->thread_message_list($user_id, $data);
                $return['IsBlocked'] = 0; 
                $return['Replyable'] = 1;
                if($data['PageNo'] == 1) {
                    $recipients = $this->messages_model->get_recipients($thread_guid, $user_id);
                    if (count($recipients) == 1) {
                        $this->load->model(array('users/user_model'));
                        foreach ($recipients as $recipient_user) {
                            $return['IsBlocked'] = $this->user_model->check_blocked_status($user_id, $recipient_user['UserID']); 
                            if (check_blocked_user($user_id, 3, $recipient_user['UserID'])) {
                                $return['Replyable'] = 0;
                            }                           
                        }
                    }
                }

                
            } else {
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
    public function thread_details_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'ThreadGUID',
            'rules' => 'trim|required'
        );

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $thread_guid = $data['ThreadGUID'];
            $data['PageNo'] = 1;
            $data['PageSize'] = 10;
            $this->load->model('messages/messages_model');

            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if ($check_permission) {
                $recipients = $this->messages_model->get_recipients($thread_guid);
                $return['Data'] = $this->messages_model->get_thread_details($user_id, $thread_guid, $recipients);
                $return['TotalMessages'] = $this->messages_model->thread_message_list($user_id, $data, TRUE);
                $return['Data']['Messages'] = $this->messages_model->thread_message_list($user_id, $data);
                $return['Data']['Recipients'] = $recipients;
                $this->messages_model->change_thread_status($user_id, $thread_guid, 'READ');
            } else {
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
    public function change_thread_status_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );
        $validation_rule[] = array(
            'field' => 'Status',
            'label' => 'status',
            'rules' => 'trim|required|in_list[READ,UN_READ,DELETED]'
        );

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $this->load->model('messages/messages_model');
            $thread_guid = $data['ThreadGUID'];
            $Status = $data['Status'];
            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if ($check_permission) {
                $this->messages_model->change_thread_status($user_id, $thread_guid, $Status);
            } else {
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
    public function change_unseen_to_seen_post() {
        $return = $this->return;
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
    public function delete_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule[] = array(
            'field' => 'MessageGUID',
            'label' => 'message guid',
            'rules' => 'trim|required'
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE && $show_value == TRUE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
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
    function edit_thread_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'thread guid',
            'rules' => 'trim|required'
        );

        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $subject = isset($data['Subject']) ? $data['Subject'] : '';
            $thread_guid = $data['ThreadGUID'];
            $media = isset($data['Media']) ? $data['Media'] : array();
            $this->load->model('messages/messages_model');
            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if ($check_permission) {
                $return['Data'] = $this->messages_model->edit_thread($user_id, $thread_guid, $subject, $media);
            } else {
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
    function add_participant_post() {
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
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $thread_guid = $data['ThreadGUID'];
            $recipients = $data['Recipients'];
            $this->load->model('messages/messages_model');
            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if ($check_permission) {
                $return['Data'] = $this->messages_model->add_participant($user_id, $thread_guid, $recipients);
            } else {
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
    function remove_participant_post() {
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
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $recipients = $data['Recipients'];
            $thread_guid = $data['ThreadGUID'];
            $this->load->model('messages/messages_model');
            $check_permission = $this->messages_model->check_thread($user_id, $thread_guid);
            if ($check_permission) {
                $this->messages_model->remove_recipient($user_id, $thread_guid, $recipients);
            } else {
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
    public function search_user_post() {
        /* Define variables - starts */
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        /* Define variables - ends */

        $validation_rule[] = array(
            'field' => 'SearchKeyword',
            'label' => 'Search Keyword',
            'rules' => 'trim|required'
        );


        $this->form_validation->set_rules($validation_rule);
        /* Validation - starts */
        if ($this->form_validation->run() == FALSE) {
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $this->form_validation->rest_first_error_string(); //Shows all error messages as a string
        } else {
            $this->load->model('messages/messages_model');
            $search_keyword = $data['SearchKeyword'];
            $hide = isset($data['Hide']) ? $data['Hide'] : array();
            $remove_users = array();
            if ($hide) {
                foreach ($hide as $usr) {
                    $remove_users[] = $usr['UserGUID'];
                }
            }
            $return['Data'] = $this->messages_model->getUserList($search_keyword, $user_id, $remove_users);
        }
        $this->response($return);
    }

    /**
     * Function Name: update_last_message

     * @param ThreadGUID
     * Description: Update last message details
     */
    public function update_last_message_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule[] = array(
            'field' => 'ThreadGUID',
            'label' => 'ThreadGUID',
            'rules' => 'required'
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $thread_guid = $data['ThreadGUID'];
            $this->load->model('messages/messages_model');
            $thread_id = $this->messages_model->get_thread_id_by_guid($thread_guid);
            $return['Data'] = $this->messages_model->inbox($user_id, array(), FALSE, $thread_id);
        }
        $this->response($return);
    }

    public function get_thread_guid_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;

        $validation_rule[] = array(
            'field' => 'UserGUID',
            'label' => 'UserGUID',
            'rules' => 'trim|required'
        );
        $this->form_validation->set_rules($validation_rule);
        if ($this->form_validation->run() == FALSE) {
            $error = $this->form_validation->rest_first_error_string();
            $return['ResponseCode'] = self::HTTP_PRECONDITION_FAILED;
            $return['Message'] = $error;
        } else {
            $user_guid = $data['UserGUID'];
            $reciever_id = get_detail_by_guid($user_guid, 3);
            $return['IsBlocked'] = 0; 
            $return['Replyable'] = 1;
            $this->load->model('messages/messages_model');
            $this->load->model(array('users/user_model'));
            $thread = $this->messages_model->get_thread_id_between_sender_receiver($user_id, $reciever_id, '', '', 2);
            $return['IsBlocked'] = $this->user_model->check_blocked_status($user_id, $reciever_id); 
            if(!empty($thread) && isset($thread['ThreadGUID'])) {
                $return['Data'] = array('ThreadGUID' => $thread['ThreadGUID']);
                
                if (check_blocked_user($user_id, 3, $reciever_id)) {
                    $return['Replyable'] = 0;
                }  
            }

            
        }
        $this->response($return);
    }

}
