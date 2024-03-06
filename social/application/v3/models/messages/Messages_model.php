<?php

/**
 * This model is used for getting and storing Event related information
 * @package    Message_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Messages_model extends Common_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * Function Name: get_recipients
     * @param ThreadGUID
     * Description: Get list of recipients
     */
    function get_recipients($thread_guid, $user_id = 0) {
        $this->db->select('U.UserID,U.UserGUID,U.FirstName,U.LastName');
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', false);
        $this->db->select('IFNULL(UD.Occupation,"") as Occupation', FALSE);
        $this->db->from(N_MESSAGE_RECIPIENT . ' MR');
        $this->db->join(N_MESSAGE_THREAD . ' MT', 'MT.ThreadID=MR.ThreadID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=MR.UserID', 'left');
        $this->db->join(USERDETAILS . ' UD', 'UD.UserID = U.UserID', 'left');
        $this->db->where_not_in('MR.Status', array('DELETED'));
        $this->db->where('MT.ThreadGUID', $thread_guid);
        if(!empty($user_id)) {
            $this->db->where('MR.UserID!=', $user_id);
        }
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($query->num_rows()) {
            $result = $query->result_array();
            return $result;
        } else {

            return array();
        }
    }

    /**
     * Function Name: edit_thread
     * @param UserID
     * @param ThreadGUID
     * @param Subject
     * @param Media
     * Description: Edit thread details
     */
    function edit_thread($user_id, $thread_guid, $subject, $media) {
        $thread_id = $this->get_thread_id_by_guid($thread_guid);
        $ChangeMedia = 0;
        if ($media) {
            $mediaGUID = $media['MediaGUID'];
            $mediaID = get_detail_by_guid($mediaGUID, 21);
            $this->db->set('MediaID', $mediaID);
            $ChangeMedia = 1;
        }

        $oldDetails = $this->db->limit(1)->get_where(N_MESSAGE_THREAD, array('ThreadGUID' => $thread_guid));
        if ($oldDetails->num_rows()) {
            $oldRow = $oldDetails->row_array();
        } else {
            return false;
        }


        $this->db->set('Subject', $subject);
        $this->db->where('ThreadGUID', $thread_guid);
        $this->db->update(N_MESSAGE_THREAD);

        $recipients = $this->get_recipients($thread_guid);

        $data = $this->get_thread_details($user_id, $thread_guid, $recipients);

        if ($oldRow['Subject'] != $subject) {
            if (!$subject) {
                $json = array('Action' => 'CONVERSATION_NAME_REMOVED', 'Value' => '');
            } else {
                $json = array('Action' => 'CONVERSATION_NAME', 'Value' => $subject);
            }
            $this->add_date_message($user_id, $thread_id);
            $this->add_automatic_message($user_id, $thread_id, $json);
        }

        if ($ChangeMedia) {
            $json = array('Action' => 'CONVERSATION_IMAGE', 'Value' => $mediaID);
            $this->add_automatic_message($user_id, $thread_id, $json);
        }

        return $data;
    }

    /**
     * Function Name: add_automatic_message
     * @param UserID
     * @param ThreadGUID
     * @param msg
     * Description: Add automatic application generated messages
     */
    function add_automatic_message($user_id, $thread_id, $msg) {
        $msg = json_encode($msg);
        $message = array('MessageGUID' => get_guid(), 'ThreadID' => $thread_id, 'UserID' => $user_id, 'Subject' => '', 'Body' => $msg, 'AttachmentCount' => '0', 'Type' => 'AUTO', 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->insert(N_MESSAGES, $message);
    }

    /**
     * Function Name: add_participant
     * @param UserID
     * @param ThreadGUID
     * @param Recipients
     * Description: Add participants in particular thread
     */
    function add_participant($user_id, $thread_guid, $recipients) {
        $this->load->model('notification_model');
        $participant = array();
        if ($recipients) {
            $thread_id = $this->get_thread_id_by_guid($thread_guid);
            $from_user_detail = get_detail_by_id($user_id, 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
            $from_user_detail['FullName'] = $from_user_detail['FirstName'] . ' ' . $from_user_detail['LastName'];
            foreach ($recipients as $user) {
                $to_user_detail = get_detail_by_guid($user['UserGUID'], 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
                $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];
                $UID = $to_user_detail['UserID'];

                $query = $this->db->limit(1)->get_where(N_MESSAGE_RECIPIENT, array('UserID' => $UID, 'ThreadID' => $thread_id));
                if ($query->num_rows()) {
                    $row = $query->row();
                    if ($row->Status != 'ACTIVE') {
                        $this->db->set('Status', 'ACTIVE');
                        $this->db->where('UserID', $UID);
                        $this->db->where('ThreadID', $thread_id);
                        $this->db->update(N_MESSAGE_RECIPIENT);
                        $participant[] = array('UserGUID' => $user['UserGUID']);
                    }
                } else {
                    $message_recipients = array(
                        'UserID' => $UID,
                        'ThreadID' => $thread_id,
                        'InboxMessageID' => NULL,
                        'InboxUpdated' => NULL,
                        'InboxStatus' => NULL,
                        'OutboxMessageID' => NULL,
                        'OutboxUpdated' => NULL,
                        'OutboxStatus' => NULL
                    );
                    $this->db->insert(N_MESSAGE_RECIPIENT, $message_recipients);
                    $participant[] = array('UserGUID' => $user['UserGUID']);
                }
                $this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $UID, 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $thread_id, 'NotificationTypeID' => 78), 0, 78);
            }
        }

        if ($participant) {
            $json = array('Action' => 'ADDED', 'Value' => $participant);
            $this->add_date_message($user_id, $thread_id);
            $this->add_automatic_message($user_id, $thread_id, $json);
        }

        return $this->get_recipients($thread_guid);
    }

    /**
     * Function Name: get_thread_id
     * @param ModuleID
     * @param ModuleEntityGUID
     * @param UserID
     * @param Replyable
     * @param Subject
     * @param Recipients
     * @param Datetime
     * Description: get thread id
     */
    function get_thread_id($module_id, $module_entity_guid, $user_id, $replyable, $subject, $recipients, $date_time, $return_type=1) {
        $recipient_count = count($recipients);
        $module_entity_id = 0;
        if(!empty($module_entity_guid)) {
            $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
        }
        
        $thread_id = 0;
        $thread = array();
        if ($recipient_count == 1 && MESSAGE_121_SINGLE_THREAD) {
            //One to One Message with Single thread then search for old thread between users
            $thread = $this->get_thread_id_between_sender_receiver($user_id, $recipients[0]['UserID'], '', '', 2);
            //log_message('error', "Threadc => ".json_encode($thread));
            if(!empty($thread) && isset($thread['ThreadID']) && isset($thread['ThreadGUID'])) {
                $thread_id = $thread['ThreadID'];
                $thread_guid = $thread['ThreadGUID'];
            }
        }

        if ($thread_id == 0) {
            $thread = array(
                'ThreadGUID' => get_guid(),
                'ModuleID' => $module_id,
                'ModuleEntityID' => $module_entity_id,
                'Subject' => $subject,
                'UserID' => $user_id,
                'RecipientCount' => $recipient_count,
                'Replyable' => $replyable,
                'CreatedDate' => $date_time,
                'ModifiedDate' => $date_time
            );
            $this->db->insert(N_MESSAGE_THREAD, $thread);
            $thread_id = $this->db->insert_id();
            $thread['ThreadID'] = $thread_id;
            $json = array('Action' => 'THREAD_CREATED', 'Value' => $date_time);
            $this->add_automatic_message($user_id, $thread_id, $json);
        }
        if($return_type == 1) {
            return $thread_id;
        } else {
            return $thread;
        }
        
    }

    /**
     * Function Name: get_thread_id_by_guid
     * @param ThreadGUID
     * Description: returns thread id by guid
     */
    function get_thread_id_by_guid($thread_guid) {
        $this->db->select('ThreadID');
        $this->db->from(N_MESSAGE_THREAD);
        $this->db->where('ThreadGUID', $thread_guid);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->ThreadID;
        } else {
            0;
        }
    }

    /**
     * Function Name: get_thread_guid_by_id
     * @param ThreadGUID
     * Description: returns thread guid by id
     */
    function get_thread_guid_by_id($thread_id) {
        $this->db->select('ThreadGUID');
        $this->db->from(N_MESSAGE_THREAD);
        $this->db->where('ThreadID', $thread_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row()->ThreadGUID;
        } else {
            0;
        }
    }

    /**
     * Function Name: get_thread_id_between_sender_receiver
     * @param SenderID
     * @param ReceiverID
     * Description: get thread id between sender and receiver
     */
    function get_thread_id_between_sender_receiver($SenderID, $ReceiverID, $ModuleID = '', $ModuleEntityID = '', $return_type=1) {
        $thread_id = 0;
        $thread = array();
        $this->db->select("MT.ThreadID, MT.ThreadGUID");
        $this->db->from(N_MESSAGE_THREAD . ' AS MT');
        $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'RIGHT');
        $this->db->where("MT.UserID", $SenderID);
        $this->db->where("MR.UserID", $ReceiverID);
       // $this->db->where("MR.InboxStatus!=", 'DELETED');
        $this->db->where("MT.RecipientCount", 1);

        /* added by gautam - starts */
        if ($ModuleID != '' && $ModuleEntityID != '') {
            $this->db->where("MT.ModuleID", $ModuleID);
            $this->db->where("MT.ModuleEntityID", $ModuleEntityID);
        }
        /* added by gautam - ends */
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $thread = $query->row_array();
            $thread_id = $thread['ThreadID'];
        } else {
            $this->db->select("MT.ThreadID, MT.ThreadGUID");
            $this->db->from(N_MESSAGE_THREAD . ' AS MT');
            $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'RIGHT');
            $this->db->where("MT.UserID", $ReceiverID);
            $this->db->where("MR.UserID", $SenderID);
            $this->db->where("MT.RecipientCount", 1);
            if ($ModuleID != '' && $ModuleEntityID != '') {
                $this->db->where("MT.ModuleID", $ModuleID);
                $this->db->where("MT.ModuleEntityID", $ModuleEntityID);
            }
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $thread = $query->row_array();
                $thread_id = $thread['ThreadID'];
            }
        }
        if($return_type == 1) {
            return $thread_id;
        } else {
            return $thread;
        }
        
    }

    /**
     * Function Name: get_entity_users
     * @param ModuleID
     * @param ModuleEntityGUID
     * Description: get user list of particular entity (Group,Event,Page)
     */
    function get_entity_users($module_id, $module_entity_guid) {
        $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
        switch ($module_id) {
            case '1':
                $table = GROUPMEMBERS;
                $where = 'M.GroupID';
                break;
            case '14':
                $table = PAGEMEMBERS;
                $where = 'M.PageID';
                break;
            case '18':
                $table = EVENTUSERS;
                $where = 'M.EventID';
                break;
            default:
                return array();
                break;
        }

        if (isset($table)) {
            $this->db->select('U.UserID,U.UserGUID');
            $this->db->from(USERS . ' U');
            $this->db->join($table . ' M', 'M.UserID=U.UserID', 'left');
            $this->db->where($where, $module_entity_id);
            $this->db->where_not_in('U.StatusID', array(3, 4));
            $this->db->where('M.StatusID', '2');
            $query = $this->db->get();
            if ($query->num_rows()) {
                return $query->result_array();
            } else {
                return array();
            }
        } else {
            return array();
        }
    }

    /**
     * Function Name: add_date_message
     * @param UserID
     * @param ThreadID
     * Description: add automatic message for 1st message of a day
     */
    function add_date_message($user_id, $thread_id) {
        /* $this->db->where('ThreadID',$thread_id);
          $this->db->where("DATE(CreatedDate)='".get_current_date('%Y-%m-%d')."'",NULL,FALSE);
          $query = $this->db->get(N_MESSAGES);
          if(!$query->num_rows())
          {
          $json = array('Action'=>'CONVERSATION_DATE','Value'=>get_current_date('%Y-%m-%d'));
          $this->add_automatic_message($user_id,$thread_id,$json);
          } */
        return true;
    }

    /**
     * Function Name: getUserList
     * @param SearchKeyword
     * @param UserID
     * @param RemoveUsers
     * Description: get list of users with search filter
     */
    function getUserList($search_keyword, $user_id, $RemoveUsers) {

        $search_keyword = $this->db->escape_like_str($search_keyword);

        $this->db->select('U.UserGUID,U.FirstName,U.LastName'); //DISTINCT(U.UserID),

        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', false);
        //$this->db->select('P.Url as ProfileURL');
        $this->db->from(USERS . ' U');
       // $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID', 'LEFT');
       // $this->db->where('P.EntityType', 'User');
        $this->db->where('U.UserID!=' . $user_id, NULL, FALSE);        
        if (!empty($search_keyword)) {
            $this->db->where("(U.FirstName like '%" . $this->db->escape_like_str($search_keyword) . "%' or U.LastName like '%" . $this->db->escape_like_str($search_keyword) . "%' or concat(U.FirstName,' ',U.LastName) like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }
     /*   if (!$this->settings_model->isDisabled(10)) {
            //For Friend Only
            $this->db->join(FRIENDS . " as f", "f.FriendID = U.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            if ($user_id != 1) {
                $sqlCondition = array('f.Status' => 1, 'f.UserID' => $user_id);
                $this->db->where($sqlCondition);
            }
        } else {
            //For followers Only
            $this->db->join(FOLLOW . " as f", "f.TypeEntityID = U.UserID", "LEFT");
            if ($user_id != 1) {
                $sqlCondition = array('f.StatusID' => 2, 'f.UserID' => $user_id);
                $this->db->where($sqlCondition);
            }
        }
        */

        if ($RemoveUsers) {
            $this->db->where_not_in('U.UserGUID', $RemoveUsers);
        }
        $this->db->limit(100);
        $query = $this->db->get(); //echo $this->db->last_query();die;
        if ($query->num_rows()) {

            $result = $query->result_array();
           /* foreach ($result as $key => $val) {
                $permission = $this->privacy_model->check_privacy($user_id, $val['UserID'], 'view_profile_picture');
                if (!$permission) {
                    $result[$key]['ProfilePicture'] = 'user_default.jpg';
                }
                unset($result[$key]['UserID']);
            }
            */
            return $result;
        } else {
            return array();
        }
    }

    /**
     * Function Name: compose
     * @param UserID
     * @param Data
     * Description: compose new message / thread
     */
    function compose($user_id, $data) {
        $module_id = isset($data['ModuleID']) && trim($data['ModuleID']) != "" ? trim($data['ModuleID']) : NULL;
        $module_entity_guid = isset($data['ModuleEntityGUID']) && trim($data['ModuleEntityGUID']) != "" ? trim($data['ModuleEntityGUID']) : NULL;
        $replyable = isset($data['Replyable']) && trim($data['Replyable']) != "" ? trim($data['Replyable']) : 1;
        $subject = isset($data['Subject']) && trim($data['Subject']) != "" ? trim($data['Subject']) : "";
        $body = isset($data['Body']) && trim($data['Body']) != "" ? trim($data['Body']) : "";
        $body = linkify($body);
        $media = isset($data['Media']) ? $data['Media'] : array();
        $recipients = isset($data['Recipients']) ? $data['Recipients'] : array();
        $links = isset($data['Links']) ? $data['Links'] : array();
        $date_time = get_current_date('%Y-%m-%d %H:%i:%s');
        $recipient_count = count($recipients);
        $attachment_count = count($media);

        if ($recipient_count >= 1) {
            $thread = $this->get_thread_id($module_id, $module_entity_guid, $user_id, $replyable, $subject, $recipients, $date_time, 2);
            $thread_id = $thread['ThreadID'];
            $thread_guid = $thread['ThreadGUID'];
            $this->add_date_message($user_id, $thread_id);

            $messages = array(
                'MessageGUID' => get_guid(),
                'ThreadID' => $thread_id,
                'UserID' => $user_id,
                'Subject' => $subject,
                'Body' => $body,
                'AttachmentCount' => $attachment_count,
                'CreatedDate' => $date_time,
            );
            $this->db->insert(N_MESSAGES, $messages);
            $message_id = $this->db->insert_id();

            
            if ($links) {
                foreach ($links as $link) {
                    $this->add_message_link($user_id, $message_id, $link);
                }
            }
            //echo $this->db->last_query();die();
            //Sender Entry
            $message_recipients = array(
                'UserID' => $user_id,
                'ThreadID' => $thread_id,
                'InboxMessageID' => NULL,
                'InboxUpdated' => NULL,
                'InboxStatus' => NULL,
                'OutboxMessageID' => $message_id,
                'OutboxUpdated' => $date_time,
                'OutboxStatus' => 'READ',
            );

            $this->db->select("MR.ThreadID");
            $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
            $this->db->where("MR.UserID", $user_id);
            $this->db->where("MR.ThreadID", $thread_id);
            $query = $this->db->get();
            //$this->db->last_query();
            $recipient_rows = $query->num_rows();
            if ($recipient_rows > 0) {
                $this->db->update(N_MESSAGE_RECIPIENT, $message_recipients, array('UserID' => $user_id, 'ThreadID' => $thread_id));
            } else {
                $this->db->insert(N_MESSAGE_RECIPIENT, $message_recipients);
            }

            if ($media) {
                $m = array();
                foreach ($media as $media) {
                    $m[] = array('MediaGUID' => $media['MediaGUID'], 'Caption' => $media['Caption'], 'MediaSectionID' => '7', 'MediaSectionReferenceID' => $message_id, 'StatusID' => 2);
                }
                $this->db->update_batch(MEDIA, $m, 'MediaGUID');
            }

            $this->load->model('notification_model');

            //receipient entry

           // $from_user_detail = get_detail_by_id($user_id, 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
          //  $from_user_detail['FullName'] = $from_user_detail['FirstName'] . ' ' . $from_user_detail['LastName'];
                    
            foreach ($recipients as $recipient_user)
            {
               // $to_user_detail = get_detail_by_id($recipient_user['UserID'], 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
               // $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];
                $message_recipients = array(
                    'UserID' => $recipient_user['UserID'],
                    'ThreadID' => $thread_id,
                    'InboxMessageID' => $message_id,
                    'InboxNewMessageCount' => 1,
                    'InboxUpdated' => $date_time,
                    'InboxStatus' => 'UN_SEEN',
                    'OutboxMessageID' => NULL,
                    'OutboxUpdated' => NULL,
                    'OutboxStatus' => NULL,
                );
                $this->db->select("MR.ThreadID");
                $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
                $this->db->where("MR.UserID", $recipient_user['UserID']);
                $this->db->where("MR.ThreadID", $thread_id);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    unset($message_recipients['InboxNewMessageCount']);
                    $this->db->update(N_MESSAGE_RECIPIENT, $message_recipients, array('UserID' => $recipient_user['UserID'], 'ThreadID' => $thread_id));

                    $this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', FALSE);
                    $this->db->where(array('UserID' => $recipient_user['UserID'], 'ThreadID' => $thread_id));
                    $this->db->update(N_MESSAGE_RECIPIENT);

                    //$this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $message_recipients['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $message_id, 'NotificationTypeID' => 77), 0, 77);
                } else {
                    //$this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $message_recipients['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $message_id, 'NotificationTypeID' => 77), 0, 77);
                    $this->db->insert(N_MESSAGE_RECIPIENT, $message_recipients);
                }
                $IsMessageRequest = 0;
                
                if($user_id != $recipient_user['UserID']) {
                    $push_title = lang('push_message_sent');
                    if ($attachment_count > 0) {//change push message to 'sent an attachment'
                        if ($attachment_count == 1) {
                            $push_title = lang('push_image_sent');
                        } else {
                            $push_title = lang('push_images_sent');
                        }
                    }
                    //$this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $message_recipients['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $message_id, 'ThreadGUID' => $thread_guid, 'Subject' => $this->LoggedInName . " " . $push_title, 'Message' => $body, 'NotificationTypeID' => 77), 0, 77);
                    //initiate_worker_job('SendMessagePushMsg', array('UserID' => $recipient_user['UserID'], 'Title' => $this->LoggedInName . " " . $push_title, 'Message' => $body, 'Data' => array('ThreadGUID' => $thread_guid)));
                    $notification_data = array('UserID' => $user_id, 'UserGUID' => $this->LoggedInGUID, 'Name' => $this->LoggedInName, 'ToUserID' => $recipient_user['UserID'], 'RefrenceID' => $thread_id, 'EntityGUID' => $thread_guid, 'Message' => $this->LoggedInName . " " . $push_title, 'Summary' => $body, 'NotificationTypeID' => 77);
                // $this->send_msg_push_notification($notification_data);
                    initiate_worker_job('pushmsg', $notification_data, '', 'message_notification');
                }
            }

            //senders entry
            $message_recipients = array(
                'UserID' => $user_id,
                'ThreadID' => $thread_id,
                'InboxMessageID' => NULL,
                'InboxNewMessageCount' => 0,
                'InboxUpdated' => NULL,
                'InboxStatus' => NULL,
                'OutboxMessageID' => $message_id,
                'OutboxUpdated' => $date_time,
                'OutboxStatus' => 'READ',
            );
            if (MESSAGE_121_SINGLE_THREAD) {
                $message_recipients['InboxMessageID'] = $message_id;
                $message_recipients['InboxUpdated'] = $date_time;
                $message_recipients['InboxStatus'] = 'READ';
            }
            $this->db->select("MR.ThreadID");
            $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
            $this->db->where("MR.UserID", $user_id);
            $this->db->where("MR.ThreadID", $thread_id);
            $this->db->limit(1);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $this->db->update(N_MESSAGE_RECIPIENT, $message_recipients, array('UserID' => $user_id, 'ThreadID' => $thread_id));
            } else {
                $this->db->insert(N_MESSAGE_RECIPIENT, $message_recipients);
            }
        }
        $return_data = $this->inbox($user_id, array(), FALSE, $thread_id);
        if (!$return_data && isset($thread_id)) {
            $return_data = array('ThreadGUID' => $this->get_thread_guid_by_id($thread_id));
        }
        return $return_data;
    }

    /**
     * Function Name: get_total_unseen_count
     * @param UserID
     * Description: get number of total unseen messages of particular user
     */
    function get_total_unseen_count($user_id) {
        $this->db->select('ThreadID');
        $this->db->from(N_MESSAGE_RECIPIENT);
        $this->db->where('UserID', $user_id);
        $this->db->where('InboxStatus', 'UN_SEEN');
        $this->db->where('Status', 'ACTIVE');
        $query = $this->db->get();
        return $query->num_rows();
    }

    /**
     * Function Name: change_unseen_to_seen
     * @param UserID
     * Description: change status of unseen messages to seen for particular user
     */
    function change_unseen_to_seen($user_id) {
        $this->db->set('InboxStatus', 'READ'); //UN_READ
        $this->db->where('UserID', $user_id);
        $this->db->where('InboxStatus', 'UN_SEEN');
        $this->db->where('Status', 'ACTIVE');
        $this->db->update(N_MESSAGE_RECIPIENT);
    }

    /**
     * Function Name: change_thread_status
     * @param UserID
     * @param ThreadGUID
     * @param Status
     * Description: change thread status
     */
    function change_thread_status($user_id, $thread_guid, $status) {
        $thread_id = $this->get_thread_id_by_guid($thread_guid);
        if ($status != 'READ' && $status != 'UN_READ') {
            if ($status == 'UN_ARCHIVE')
            {
                $status = 'READ';
            }
            $this->db->set('InboxStatus', $status);
        }

        if ($status == 'READ') {
            $this->db->set('InboxNewMessageCount', '0');
        } else if ($status == 'UN_READ') {
            $this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', false);
        }

        $this->db->where('UserID', $user_id);
        $this->db->where('ThreadID', $thread_id);
        $this->db->update(N_MESSAGE_RECIPIENT);

        if ($status == 'DELETED') {
            $this->db->select('MessageID');
            $this->db->from(N_MESSAGES);
            $this->db->where('ThreadID', $thread_id);
            $MsgQry = $this->db->get();
            if ($MsgQry->num_rows()) {
                $MsgData = $MsgQry->result_array();
                $insert = array();
                foreach ($MsgData as $val) {
                    $query = $this->db->get_where(N_MESSAGE_DELETED, array('UserID' => $user_id, 'MessageID' => $val['MessageID']));
                    if ($query->num_rows() == 0) {
                        $insert[] = array('UserID' => $user_id, 'MessageID' => $val['MessageID'], 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                    }
                }
                if ($insert) {
                    $this->db->insert_batch(N_MESSAGE_DELETED, $insert);
                }
            }
        }     
    }

    /**
     * Function Name: delete_thread
     * @param UserID
     * @param $thread_guid
     * Description: Delete thread when the message request is denied
     */
    function delete_thread($thread_guid) {
        $thread_id = $this->get_thread_id_by_guid($thread_guid);
        //delete from MessageThread, MessagesRecipients, MessagesNew
        $this->db->where(array('ThreadID' => $thread_id));
        $this->db->delete(N_MESSAGE_THREAD);

        $this->db->where(array('ThreadID' => $thread_id));
        $this->db->delete(N_MESSAGE_RECIPIENT);

        $this->db->where(array('ThreadID' => $thread_id));
        $this->db->delete(N_MESSAGES);
    }

    /**
     * Function Name: reply
     * @param UserID
     * @param Data
     * Description: reply to particular thread
     */
    function reply($user_id, $data) {

        $thread_guid = isset($data['ThreadGUID']) && trim($data['ThreadGUID']) != "" ? trim($data['ThreadGUID']) : NULL;
        $message_guid = isset($data['MessageGUID']) && trim($data['MessageGUID']) != "" ? trim($data['MessageGUID']) : NULL;
        $subject = isset($data['Subject']) && trim($data['Subject']) != "" ? trim($data['Subject']) : "";
        $body = isset($data['Body']) && trim($data['Body']) != "" ? trim($data['Body']) : "";
        $body = linkify($body);
        $media = isset($data['Media']) ? $data['Media'] : array();
        $recipients = isset($data['Recipients']) ? $data['Recipients'] : array();
        $links = isset($data['Links']) ? $data['Links'] : array();
        $date_time = get_current_date('%Y-%m-%d %H:%i:%s');

        if (empty($recipients)) {
            $recipients = $this->get_recipients($data['ThreadGUID']);
        } else {
            $r = array();
            foreach ($recipients as $receipient) {
                if ($this->is_valid_user($receipient['UserGUID'])) {
                    $r[] = array(
                        'UserGUID' => $receipient['UserGUID'],
                        'UserID' => get_detail_by_guid($receipient['UserGUID'], 3)
                    );
                }
            }
            $recipients = $r;
        }

        $recipient_count = count($recipients);
        $attachment_count = count($media);

        if ($recipient_count >= 1) {
            $thread_id = $this->get_thread_id_by_guid($thread_guid);

            $this->add_date_message($user_id, $thread_id);

            $messages = array(
                'MessageGUID' => get_guid(),
                'ThreadID' => $thread_id,
                'UserID' => $user_id,
                'Subject' => $subject,
                'Body' => $body,
                'AttachmentCount' => $attachment_count,
                'CreatedDate' => $date_time,
            );
            $this->db->insert(N_MESSAGES, $messages);
            $message_id = $this->db->insert_id();

            if ($links) {
                foreach ($links as $link) {
                    $this->add_message_link($user_id, $message_id, $link);
                }
            }
            //echo $this->db->last_query();die();
            //Sender Entry
            $message_recipients = array(
                'UserID' => $user_id,
                'ThreadID' => $thread_id,
                'InboxMessageID' => NULL,
                'InboxUpdated' => NULL,
                'InboxStatus' => NULL,
                'OutboxMessageID' => $message_id,
                'OutboxUpdated' => $date_time,
                'OutboxStatus' => 'READ',
            );

            if (MESSAGE_121_SINGLE_THREAD) {
                $message_recipients['InboxMessageID'] = $message_id;
                $message_recipients['InboxUpdated'] = $date_time;
                $message_recipients['InboxStatus'] = 'READ';
            }

            $this->db->select("MR.ThreadID");
            $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
            $this->db->where("MR.UserID", $user_id);
            $this->db->where("MR.ThreadID", $thread_id);
            $this->db->limit(1);
            $query = $this->db->get();
            $recipient_rows = $query->num_rows();
            if ($recipient_rows > 0) {
                if (!MESSAGE_121_SINGLE_THREAD) {
                    unset($message_recipients['InboxMessageID']);
                    unset($message_recipients['InboxUpdated']);
                    unset($message_recipients['InboxStatus']);
                }
                $this->db->update(N_MESSAGE_RECIPIENT, $message_recipients, array('UserID' => $user_id, 'ThreadID' => $thread_id));
            } else {
                $this->db->insert(N_MESSAGE_RECIPIENT, $message_recipients);
            }

            if ($media) {
                $m = array();
                foreach ($media as $media) {
                    $m[] = array('MediaGUID' => $media['MediaGUID'], 'Caption' => $media['Caption'], 'MediaSectionID' => '7', 'MediaSectionReferenceID' => $message_id, 'StatusID' => 2);
                }
                $this->db->update_batch(MEDIA, $m, 'MediaGUID');
            }

            $this->load->model('notification_model');
            //receipient entry
            $from_user_detail = array();
            foreach ($recipients as $recipient_user)
            {
                if ($recipient_user['UserID'] == $user_id)
                {
                    continue;
                }
                $message_recipients = array(
                    'UserID' => $recipient_user['UserID'],
                    'ThreadID' => $thread_id,
                    'InboxMessageID' => $message_id,
                    'InboxNewMessageCount' => 1,
                    'InboxUpdated' => $date_time,
                    'InboxStatus' => 'UN_SEEN',
                    'OutboxMessageID' => NULL,
                    'OutboxUpdated' => NULL,
                    'OutboxStatus' => NULL,
                );
                $this->db->select("MR.ThreadID");
                $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
                $this->db->where("MR.UserID", $recipient_user['UserID']);
                $this->db->where("MR.ThreadID", $thread_id);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    unset($message_recipients['InboxNewMessageCount']);
                    $this->db->update(N_MESSAGE_RECIPIENT, $message_recipients, array('UserID' => $recipient_user['UserID'], 'ThreadID' => $thread_id));

                    $this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', FALSE);
                    $this->db->where(array('UserID' => $recipient_user['UserID'], 'ThreadID' => $thread_id));
                    $this->db->update(N_MESSAGE_RECIPIENT);
                } else {
                    $this->db->insert(N_MESSAGE_RECIPIENT, $message_recipients);
                }

                if($user_id != $message_recipients['UserID']) {
                    $push_title = lang('push_message_sent');
                    if ($attachment_count > 0) {//change push message to 'sent an attachment'
                        if ($attachment_count == 1) {
                            $push_title = lang('push_image_sent');
                        } else {
                            $push_title = lang('push_images_sent');
                        }
                    }

                    $notification_data = array('UserID' => $user_id, 'UserGUID' => $this->LoggedInGUID, 'Name' => $this->LoggedInName, 'ToUserID' => $message_recipients['UserID'], 'RefrenceID' => $thread_id, 'EntityGUID' => $thread_guid, 'Message' => $this->LoggedInName . " " . $push_title, 'Summary' => $body, 'NotificationTypeID' => 77);
                // $this->send_msg_push_notification($notification_data);
                    initiate_worker_job('pushmsg', $notification_data, '', 'message_notification');
                }
            }
            $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->where('ThreadID', $thread_id);
            $this->db->update(N_MESSAGE_THREAD);
        }
        return $this->thread_message_list($user_id, array('ThreadGUID' => $thread_guid), FALSE, $message_id);
    }

    /**
     * Function Name: remove_recipient
     * @param UserID
     * @param ThreadGUID
     * @param Recipients
     * Description: remove recipient from particular thread
     */
    function remove_recipient($user_id, $thread_guid, $recipients) {
        $thread_id = $this->get_thread_id_by_guid($thread_guid);
        if ($recipients) {
            foreach ($recipients as $value) {
                $UID = get_detail_by_guid($value['UserGUID'], 3);
                $this->db->set('Status', 'DELETED');
                $this->db->where('UserID', $UID);
                $this->db->where('ThreadID', $thread_id);
                $this->db->update(N_MESSAGE_RECIPIENT);

                $json = array('Action' => 'REMOVED', 'Value' => array(array('UserGUID' => $value['UserGUID'])));
                $this->add_date_message($user_id, $thread_id);
                $this->add_automatic_message($user_id, $thread_id, $json);
            }
        }
    }

    /**
     * Function Name: delete
     * @param UserID
     * @param MessageGUID
     * Description: delete particular message for particular user
     */
    function delete($user_id, $message_guid) {
        $InboxSet = false;
        $OutboxSet = false;
        $this->db->select('MessageID,ThreadID');
        $this->db->from(N_MESSAGES);
        $this->db->where('MessageGUID', $message_guid);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row_array();
            $message_id = $row['MessageID'];
            $thread_id = $row['ThreadID'];

            $check = $this->db->limit(1)->get_where(N_MESSAGE_DELETED, array('UserID' => $user_id, 'MessageID' => $message_id));
            if (!$check->num_rows()) {
                $this->db->insert(N_MESSAGE_DELETED, array('UserID' => $user_id, 'MessageID' => $message_id, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
            }

            $this->db->select('InboxMessageID,OutboxMessageID');
            $this->db->from(N_MESSAGE_RECIPIENT);
            $this->db->where('UserID', $user_id);
            $this->db->where('ThreadID', $thread_id);
            $this->db->limit(1);
            $qry = $this->db->get();
            if ($qry->num_rows()) {
                $row = $qry->row_array();
                $InboxMessageID = $row['InboxMessageID'];
                $OutboxMessageID = $row['OutboxMessageID'];
                if ($InboxMessageID == $message_id) {
                    $this->db->select('M.MessageID');
                    $this->db->from(N_MESSAGES . ' M');
                    $this->db->where('M.ThreadID', $thread_id);
                    $this->db->where("M.MessageID NOT IN (SELECT MessageID FROM " . N_MESSAGE_DELETED . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
                    $this->db->limit(1);
                    $this->db->order_by('M.MessageID', 'DESC');
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        $InboxMessageID = $query->row()->MessageID;
                    } else {
                        $InboxMessageID = NULL;
                    }
                    $InboxSet = true;
                }
                if ($OutboxMessageID == $message_id) {
                    $this->db->select('M.MessageID');
                    $this->db->from(N_MESSAGES . ' M');
                    $this->db->where('M.ThreadID', $thread_id);
                    $this->db->where("M.MessageID NOT IN (SELECT MessageID FROM " . N_MESSAGE_DELETED . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
                    $this->db->where('M.UserID', $user_id);
                    $this->db->limit(1);
                    $this->db->order_by('M.MessageID', 'DESC');
                    $query = $this->db->get();
                    if ($query->num_rows()) {
                        $OutboxMessageID = $query->row()->MessageID;
                    } else {
                        $OutboxMessageID = NULL;
                    }
                    $OutboxSet = true;
                }
                if ($InboxSet || $OutboxSet) {
                    if ($InboxSet) {
                        $this->db->set('InboxMessageID', $InboxMessageID);
                    }
                    if ($OutboxSet) {
                        $this->db->set('OutboxMessageID', $OutboxMessageID);
                    }
                    $this->db->where('UserID', $user_id);
                    $this->db->where('ThreadID', $thread_id);
                    $this->db->update(N_MESSAGE_RECIPIENT);
                }
            }
        }
    }

    /**
     * Function Name: get_thread_subject
     * @param MessageGUID
     * @param ThreadSubject
     * @param EditableThread
     * @param Recipients
     * Description: get thread subject
     */
    function get_thread_subject($user_id, $ThreadSubject, $EditableThread, $recipients) {
        return $ThreadSubject;
        /* $user_guid = get_detail_by_id($user_id,3,'UserGUID',1);
          if($ThreadSubject == ''){
          if($recipients)
          {
          $rcp = $recipients;
          foreach($rcp as $k=>$v){
          if($v['UserGUID'] == $user_guid){
          unset($rcp[$k]);
          }
          }
          $rcp = array_values($rcp);
          if($rcp){
          $rc = count($rcp);
          if($rc==1){
          $ThreadSubject = $rcp[0]['FirstName'].' '.$rcp[0]['LastName'];
          } else if($rc==2){
          $ThreadSubject = $rcp[0]['FirstName'].' and '.$rcp[1]['FirstName'];
          } else if($rc>2){
          if($rc==3){
          $other = 'other';
          } else {
          $other = 'others';
          }
          $ThreadSubject = $rcp[0]['FirstName'].', '.$rcp[1]['FirstName'].' and '.($rc-2).' '.$other;
          }
          }
          }
          }
          if($EditableThread == 0){
          $index = ($user_guid==$recipients[0]['UserGUID']) ? 1 : 0 ;
          $ThreadSubject = $recipients[$index]['FirstName'].' '.$recipients[$index]['LastName'];
          }
          return $ThreadSubject; */
    }

    /**
     * Function Name: get_thread_image_name
     * @param UserID
     * @param ThreadImageName
     * @param EditableThread
     * @param Recipients
     * Description: returns thread image name
     */
    function get_thread_image_name($user_guid, $ThreadImageName, $EditableThread, $recipients) {
        if ($EditableThread == 0) {
            // $user_guid = get_detail_by_id($user_id, 3, 'UserGUID', 1);
            $index = ($user_guid == $recipients[0]['UserGUID']) ? 1 : 0;
            $ThreadImageName = isset($recipients[$index]['ProfilePicture']) ? $recipients[$index]['ProfilePicture'] : 'user_default.jpg';
        }
        return $ThreadImageName;
    }

    /**
     * Function Name: inbox
     * @param UserID
     * @param Data
     * @param NumRows
     * @param ThreadID
     * Description: returns list of threads
     */
    function inbox($user_id, $data, $num_rows = FALSE, $thread_id = '') {        
        $module_id = safe_array_key($data, 'ModuleID', '');
        $module_entity_guid = safe_array_key($data, 'ModuleEntityGUID', '');
        $search_keyword = safe_array_key($data, 'SearchKeyword', '');
        $Filter = safe_array_key($data, 'Filter', '');

        $module_entity_id = 0;
        if ($module_id && $module_entity_guid) {
            $module_entity_id = get_detail_by_guid($module_entity_guid, $module_id);
        }

        $this->db->select("MT.ThreadGUID,MT.Subject as ThreadSubject,MT.Replyable", false);
        $this->db->select('U.UserGUID as SenderUserGUID ', False);
        //$this->db->select('IF(MT.RecipientCount>1,1,0) as EditableThread', false);
        //$this->db->select('IF(MD.ImageName!="",MD.ImageName,"") as ThreadImageName', false);
        $this->db->select("MR.InboxStatus,MR.InboxUpdated,MR.InboxNewMessageCount");
        $this->db->select("M.MessageGUID, M.MessageID, M.Body, M.Subject, M.AttachmentCount, M.UserID ");
        //$this->db->select("(SELECT COUNT(InboxMessageID) FROM ".N_MESSAGE_RECIPIENT." WHERE UserID='".$user_id."' AND InboxStatus!='DELETED' AND ThreadID=MT.ThreadID) as MessageCount",false);
        /* if($this->IsApp == 1){  
          // added by gautam
          $this->db->select("P.PageGUID, P.Title AS PageTitle, P.UserID as PageOwnerUserID, P.ProfilePicture as PageProfilePicture");
          }
         */

        if ($search_keyword) {
            $this->db->select("IF(MT.Subject='" . $search_keyword . "','2','1') as InboxOrderBy", false);
        } else {
            $this->db->select("'1' as InboxOrderBy", false);
        }
        $this->db->from(N_MESSAGE_THREAD . ' AS MT');
        $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'RIGHT');
        //$this->db->join(MEDIA . ' MD', 'MD.MediaID=MT.MediaID', 'LEFT');
        $this->db->join(N_MESSAGES . ' AS M', 'M.MessageID=MR.InboxMessageID', 'LEFT');
        
        $this->db->join(USERS . " AS U", "M.UserID=U.UserID");
       // $this->db->where("(SELECT COUNT(InboxMessageID) FROM " . N_MESSAGE_RECIPIENT . " WHERE UserID='" . $user_id . "' AND InboxStatus!='DELETED' AND ThreadID=MT.ThreadID)!=0", null, false);
        $this->db->where("MR.InboxUpdated is not NULL", NULL, FALSE);
        $this->db->where("MR.UserID", $user_id);
        //$this->db->where("(MR.UserID='".$user_id."' OR M.UserID='".$user_id."')",NULL,FALSE);
        $this->db->where('M.Type', 'MANUAL');
        if ($thread_id) {
            $this->db->where('MT.ThreadID', $thread_id);
        }
        if ($Filter) {
            if ($Filter == 'REQUEST') {
                $this->db->where("MR.InboxStatus", 'REQUEST');
            } else if ($Filter == 'ARCHIVED') {
                $this->db->where("MR.InboxStatus", 'ARCHIVED');
            } else if ($Filter == 'UN_READ') {
                $this->db->where("MR.InboxNewMessageCount>0", NULL, FALSE);
                $this->db->where("(MR.InboxStatus NOT IN ('DELETED','REQUEST') OR MR.InboxStatus is NULL)", NULL, FALSE);
                $this->db->where("MR.Status!='DELETED'", NULL, FALSE);
            }
        } else {
            $this->db->where("(MR.InboxStatus NOT IN ('ARCHIVED','DELETED','REQUEST') OR MR.InboxStatus is NULL)", NULL, FALSE);
            $this->db->where("MR.Status!='DELETED'", NULL, FALSE);
        }
        $this->db->order_by('InboxOrderBy', 'DESC');
        $this->db->order_by('MR.InboxUpdated', 'DESC');

        if ($module_entity_id) {
            $this->db->where('MT.ModuleID', $module_id);
            $this->db->where('MT.ModuleEntityID', $module_entity_id);
        }

        if ($search_keyword) {
            $search_keyword = $this->db->escape_like_str($search_keyword);
            $this->db->where("(MT.Subject LIKE '%" . $search_keyword . "%' OR MT.ThreadID IN (SELECT SMT.ThreadID FROM " . N_MESSAGE_RECIPIENT . " SMT JOIN " . USERS . " SU ON SU.UserID=SMT.UserID WHERE SMT.ThreadID=MT.ThreadID AND SMT.Status='ACTIVE' AND (SU.FirstName LIKE '%" . $search_keyword . "%' OR SU.LastName LIKE '%" . $search_keyword . "%' OR CONCAT(SU.FirstName,' ',SU.LastName) LIKE '%" . $search_keyword . "%')))", NULL, FALSE);            
        }

        $this->db->group_by('MT.ThreadGUID');

        if ($num_rows) {
            $query = $this->db->get();
            return $query->num_rows();
        } else {
            //pagination logic
            $page_no        = safe_array_key($data, 'PageNo', PAGE_NO);
            $page_size      = safe_array_key($data, 'PageSize', '');
            if ($page_size) { // Check for pagination
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
            $query = $this->db->get();
            //echo $this->db->last_query();
        }
        $array = array();
        if ($query->num_rows()) {
           // $user_guid = get_detail_by_id($user_id, 3, 'UserGUID', 1);
            
            foreach ($query->result_array() as $arr) {
                if (check_blocked_user($user_id, 3, $arr['UserID'])) {
                    $arr['Replyable'] = 0; //cannot reply as this user is blocked by the other
                }  
                $arr['Media'] = array();
                if($arr['AttachmentCount'] > 0) {
                    $arr['Media'] = $this->get_message_media($arr['MessageID']);
                }              
                //$arr['ThreadImageName'] = $this->get_thread_image_name($user_guid, $arr['ThreadImageName'], $arr['EditableThread'], $this->get_recipients($arr['ThreadGUID'], $user_id));
                unset($arr['MessageID']);
                unset($arr['InboxOrderBy']);
                unset($arr['ThreadSubject']);
                unset($arr['EditableThread']);
                unset($arr['UserID']);
                $array[] = $arr;
            }
        }
        if ($thread_id && isset($array[0])) {
            $array[0]['IsBlocked'] = 0;
            return $array[0];
        }
        return $array;
    }

    /**
     * Function Name: check_thread
     * @param UserID
     * @param ThreadGUID
     * Description: check if user is in the thread or not
     */
    function check_thread($user_id, $thread_guid) {
        $this->db->select('MT.ThreadID');
        $this->db->from(N_MESSAGE_THREAD . ' MT');
        $this->db->join(N_MESSAGE_RECIPIENT . ' MR', 'MR.ThreadID=MT.ThreadID');
        $this->db->where('MT.ThreadGUID', $thread_guid);
        $this->db->where('MR.UserID', $user_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function Name: get_thread_details
     * @param UserID
     * @param ThreadGUID
     * @param Recipients
     * Description: get details of particular thread
     */
    function get_thread_details($user_id, $thread_guid, $recipients = array()) {       

        $this->db->select('MT.ThreadGUID,MT.UserID as ThreadCreator,MT.Subject as ThreadSubject,MT.Replyable', false);
        //$this->db->select('IF(MD.ImageName!="",MD.ImageName,"") as ThreadImageName', false);
        $this->db->select('IF(MT.RecipientCount>1,1,0) as EditableThread', false);
        $this->db->from(N_MESSAGE_THREAD . ' MT');
       // $this->db->join(MEDIA . ' MD', 'MD.MediaID=MT.MediaID', 'LEFT');
        $this->db->where('MT.ThreadGUID', $thread_guid);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $data = $query->row_array();
            if (!empty($recipients)) {
                $user_guid = get_detail_by_id($user_id, 3, 'UserGUID', 1);
                $exists = 0;
                foreach ($recipients as $r) {
                    if ($r['UserGUID'] == $user_guid) {
                        $exists = 1;
                    }
                }
                if (!$exists) {
                    return array();
                }
                $thread_user_id = $data['ThreadCreator'];
                unset($data['ThreadCreator']);
                $data['CanRemoveParticipant'] = ($user_id == $thread_user_id) ? 1 : 0;
                $data['ThreadSubject'] = $data['ThreadSubject']; // $this->get_thread_subject($user_id, $data['ThreadSubject'], $data['EditableThread'], $recipients);
                //$data['ThreadImageName'] = $this->get_thread_image_name($user_guid, $data['ThreadImageName'], $data['EditableThread'], $recipients);
            }
            return $data;
        } else {
            return array();
        }
    }

    /**
     * Function Name: thread_message_list
     * @param UserID
     * @param Data
     * @param NumRows
     * @param MessageID
     * Description: list of messages of particular thread
     */
    function thread_message_list($user_id, $data, $num_rows = FALSE, $message_id = '') {
        
        $this->db->select("M.MessageID, M.MessageGUID, M.Body, M.CreatedDate, M.Type,  M.AttachmentCount");
        $this->db->select("U.UserID, U.UserGUID, U.FirstName, U.LastName");
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);

        $this->db->from(N_MESSAGES . ' AS M');
        $this->db->join(N_MESSAGE_THREAD . ' AS MT', 'MT.ThreadID=M.ThreadID');
        $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'LEFT');
        $this->db->join(USERS . ' AS U', 'U.UserID=M.UserID');
        
        $this->db->where("M.MessageID NOT IN (SELECT MessageID FROM " . N_MESSAGE_DELETED . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
        $this->db->where("MT.ThreadGUID", $data['ThreadGUID']);
        $this->db->where("MR.UserID", $user_id);
        $this->db->where("MR.Status!='DELETED'", NULL, FALSE);
        $this->db->where_not_in("U.StatusID", array(3, 4));

        if ($message_id) {
            $this->db->where('M.MessageID', $message_id);
        }

        $this->db->order_by('M.MessageID', 'DESC');


        if ($num_rows) {
            $query = $this->db->get();
            return $query->num_rows();
        } else {
            
            if (!empty($data['LastRecordID'])) {
                $this->db->where('M.MessageID <', $data['LastRecordID']);
            }
            //$this->db->limit(25);
            $page_no = isset($data['PageNo']) && is_numeric($data['PageNo']) ? $data['PageNo'] : 1;
            $page_size = isset($data['PageSize']) && is_numeric($data['PageSize']) ? $data['PageSize'] : NULL;
            if (!is_null($page_size)) { // Check for pagination
                $offset = ($page_no - 1) * $page_size;
                $this->db->limit($page_size, $offset);
            }
           

            $query = $this->db->get();
        }
        //echo $this->db->last_query();
        $array = array();
        foreach ($query->result_array() as $arr) {
            //$arr['SenderUserGUID'] = get_detail_by_id($arr['SenderUserID'], 3, 'UserGUID', 1);
           
            $arr['Media'] = array();
            if($arr['AttachmentCount'] > 0) {
                $arr['Media'] = $this->get_message_media($arr['MessageID']);
            }
            
            //unset($arr['MessageID']);
            $d = $this->get_message_action($user_id, $arr['UserGUID'], $arr['Type'], $arr['Body']);
            $arr['ActionCreator'] = $d['ActionCreator'];
            $arr['ActionValue'] = $d['ActionValue'];
            $arr['ActionName'] = $d['ActionName'];
            //$arr['Body'] = nl2br($arr['Body']);

            $arr['Links'] = $this->get_message_links($arr['MessageID']);

            unset($arr['UserID']);
            unset($arr['SenderUserID']);
            unset($arr['MessageID']);
            unset($arr['AttachmentCount']);
            $array[] = $arr;
        }
        if ($message_id && isset($array[0])) {
            return $array[0];
        }
        return array_reverse($array);
    }

    /**
     * Function Name: get_user_detail
     * @param UserGUID
     * @param CurrentUserGUID
     * Description: get user details (FirstName,LastName,UserGUID,ProfileURL)
     */
    function get_user_detail($user_guid, $current_user_guid) {
        $this->db->select('U.FirstName,U.LastName,U.UserGUID');
        $this->db->from(USERS . ' U');
        //$this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID', 'left');
        $this->db->where('UserGUID', $user_guid);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $array = $query->row_array();
            if ($user_guid == $current_user_guid) {
                $array['FirstName'] = 'You';
                $array['LastName'] = '';
            }
            return $array;
        } else {
            return array();
        }
    }

    /**
     * Function Name: get_message_action
     * @param UserID
     * @param UserGUID
     * @param Type
     * @param Body
     * Description: get action of automatic messages
     */
    function get_message_action($user_id, $user_guid, $type, $body) {
        $UGUID = get_detail_by_id($user_id, 3, 'UserGUID', 1);
        $data = array('ActionCreator' => '', 'ActionValue' => '', 'ActionName' => '');
        if ($type == 'AUTO') {
            $json = json_decode($body);
            $data['ActionCreator'] = $this->get_user_detail($user_guid, $UGUID);
            $data['ActionName'] = $json->Action;

            switch ($json->Action) {
                case 'ADDED':
                    $data['ActionValue'] = array();
                    if ($json->Value) {
                        foreach ($json->Value as $val) {
                            $data['ActionValue'][] = $this->get_user_detail($val->UserGUID, $UGUID);
                        }
                    }
                    break;

                case 'REMOVED':
                    $data['ActionValue'] = array();
                    if ($json->Value) {
                        foreach ($json->Value as $val) {
                            $data['ActionValue'][] = $this->get_user_detail($val->UserGUID, $UGUID);
                        }
                    }
                    break;

                case 'CONVERSATION_NAME':
                    $data['ActionValue'] = $json->Value;
                    break;

                case 'CONVERSATION_IMAGE':
                    $data['ActionValue'] = get_detail_by_id($json->Value, 21, 'ImageName', 1);
                    break;
                case 'CONVERSATION_DATE':
                    $data['ActionValue'] = date('d M Y', strtotime($json->Value));
                    break;

                case 'THREAD_CREATED':
                    $data['ActionValue'] = date('d M Y', strtotime($json->Value));
                    break;
            }
        }
        return $data;
    }

    /**
     * Function Name: get_message_media
     * @param MessageID
     * Description: get list of media of particular message
     */
    function get_message_media($message_id) {
        $this->db->select('M.MediaGUID, M.Resolution, M.ConversionStatus,ME.Name as Ext,M.ImageName,M.Caption,IFNULL(M.OriginalName,M.ImageName) as OriginalName', false);
        $this->db->select('MT.Name as MediaType');
        $this->db->from(MEDIA . ' M');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'M.MediaExtensionID=ME.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'ME.MediaTypeID=MT.MediaTypeID', 'LEFT');
        $this->db->where('M.MediaSectionID', '7');
        $this->db->where('M.MediaSectionReferenceID', $message_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }


    /**
     * [add_link used to add message links details]
     * @param  [int] $user_id   [UserID]
     * @param  [int] $message_id   [Message ID]
     * @param  [array] $link   [Link details]
     */
    function add_message_link($user_id, $message_id, $link) {
        
        if ($link['ImageURL']) {
            $this->load->model('upload_file_model');
            $image_data = array('DeviceType' => 'Native', 'ImageData' => file_get_contents($link['ImageURL']), 'ImageURL' => $link['ImageURL'], 'ModuleID' => '25', 'ModuleEntityGUID' => '', 'Type' => 'messages');
            $linkURL = $this->upload_file_model->saveFileFromURL($image_data);
            $link['ImageURL'] =  $linkURL['Data']['ImageName'];
        }
        $data = array('URL' => $link['URL'], 'Title' => $link['Title'], 'MetaDescription' => $link['MetaDescription'], 'ImageURL' => $link['ImageURL'], 'MessageID' => $message_id, 'UserID' => $user_id, 'IsCrawledURL' => $link['IsCrawledURL'], 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'), 'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->insert(N_MESSAGES_LINKS, $data);
        $link_id = $this->db->insert_id();

        
        $IsLinkExists = 0;
        if(!empty($link['URL']))
        $IsLinkExists = 1;

        return array('MessageLinkID'=>$link_id,
                    'IsLinkExists'=>$IsLinkExists,
                    'LinkDesc'=> $link['MetaDescription'],
                    'LinkImgURL'=>$link['ImageURL'],
                    'LinkTitle'=>$link['Title'],
                    'LinkURL'=>$link['URL']
                    );
    }
    
    /**
     * [get_message_links used to get message links details]
     * @param  [int] $message_id   [Message ID]
     */
    function get_message_links($message_id) {
        $result=array();        
        $this->db->select('IF(URL is NULL,0,1) as IsLinkExists', false);
        $this->db->select('URL as LinkURL,Title as LinkTitle,MetaDescription as LinkDesc,ImageURL as LinkImgURL');
        $this->db->from(N_MESSAGES_LINKS);
        $this->db->where('MessageID', $message_id);
        $this->db->where('StatusID', 2);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $result= $query->result_array();
        }        
        return $result;
    }

    function send_msg_push_notification($notification_data) {
        $this->load->model('notification_model');
        $to_user_id = $notification_data['ToUserID'];
        $notification_type_data = $this->notification_model->get_notification_type_data($notification_data['NotificationTypeID']);
        $template_key = isset($notification_type_data['NotificationTypeKey']) ? trim($notification_type_data['NotificationTypeKey']) : '';
        $notification_settings = $this->notification_model->check_user_notification_settings($to_user_id, $template_key, $notification_type_data['ModuleID']);        
        //log_message("error", "user_id => ".$to_user_id."  template_key => ".$template_key."  ModuleID => ".$notification_type_data['ModuleID']);
        //log_message("error", "user_notification_settings => ".json_encode($notification_settings));
        if ($notification_settings['send_mobile_notification']) {
            $query = $this->db->query("SELECT DeviceToken, DeviceTypeID FROM `ActiveLogins` WHERE UserID='$to_user_id' AND DeviceToken!='' GROUP BY DeviceToken, DeviceTypeID ORDER BY ActiveLoginID DESC ");
            if ($query->num_rows() > 0) {
                if(empty($notification_data['Name'])) {
                    $notification_data['Name'] = 'Bhopu';
                    $notification_data['UserGUID'] = '94915094-46e8-3d2c-cc58-84876f9c99e1';
                }
                $notification_data['PushNotification'] = array("ModuleID" => 25, "ModuleEntityGUID" => $notification_data['EntityGUID'], "EntityID" => $notification_data['RefrenceID'], "EntityGUID" => $notification_data['EntityGUID'], "Name" => $notification_data['Name'], "UserGUID" => $notification_data['UserGUID'], "Refer" => "MESSAGE");

                if (isset($notification_data['Summary']) && !empty($notification_data['Summary']))  {
                    $notification_data['Summary'] = html_entity_decode($notification_data['Summary']);
                    $notification_data['Summary'] = strip_tags($notification_data['Summary']);
                    $notification_data['Summary'] = mb_substr($notification_data['Summary'], 0, 250,'UTF-8');
                }
                $message = $notification_data['Message'];
                unset($notification_data['Message']);
                unset($notification_data['UserGUID']);
                unset($notification_data['Name']);
                //log_message("error", "notification_data => ".json_encode($notification_data));
                foreach ($query->result_array() as $notifications) {

                    $notification_data['DeviceTypeID'] = $notifications['DeviceTypeID'];
                    send_push_notification($notifications['DeviceToken'], $message, 1, $notification_data);

                    /* if ($notifications['DeviceTypeID'] == 2) {
                        push_notification_iphone($notifications['DeviceToken'], $message, 0, $notification_data);
                    } elseif ($notifications['DeviceTypeID'] == 3) {
                        push_notification_android(array($notifications['DeviceToken']), $message, 0, $notification_data);
                    }
                    */
                }
            }
        }
    }


    function send_message_to_all_user() {
        return true;
        $this->db->select("AL.UserID, U.UserGUID,  AL.DeviceToken, AL.DeviceTypeID");
        $this->db->from(ACTIVELOGINS . ' AL');
        $this->db->join(USERS . ' U', 'U.UserID=AL.UserID AND U.StatusID NOT IN (3,4)');  
        
        $this->db->where('AL.DeviceTypeID', 3);
        $this->db->group_by('AL.UserID');
        $this->db->order_by('AL.ActiveLoginID', 'DESC');
        $query = $this->db->get();
            
        if ($query->num_rows() > 0) { 
            $data = array();
            $sender_id= ADMIN_USER_ID;
            $data['Body'] = 'अरे वाह! अब कीजिये इंदौर में किसी को भी भोपू में पर्सनल मैसेज';
            foreach ($query->result_array() as $value) {
                if($value['UserID'] == $sender_id) {
                    continue;
                }

                $recipients = array();
                $recipients[] = array(
                    'UserGUID' => $value['UserGUID'],
                    'UserID' => $value['UserID'],
                );

                $data['Recipients'] = $recipients;

                $this->compose($sender_id, $data);
            }
        }
    }

   /**
     * Used to get Total number of messages exchanged so far or today
     */
    public function get_total_message($day='all') {
        $this->db->select("MessageID");
        if($day == 'today') {
            $current_time_zone = date_default_timezone_get();
            $time_zone = 'Asia/Calcutta';
            date_default_timezone_set($time_zone);
            $today_date = date('Y-m-d');
            date_default_timezone_set($current_time_zone);
            $this->db->like("DATE_FORMAT(CONVERT_TZ(CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $today_date, FALSE);        
        }
        $this->db->where('Type', 'MANUAL');
        $query = $this->db->get(N_MESSAGES);
       // echo $this->db->last_query();die;
        $total = $query->num_rows();
        
        return $total;
    }

    
     /**
     * Used to get Total no of users who've sent or received messages so far or today
     */
    public function get_total_message_user($day='all') {
        $this->db->select("UserID");
        if($day == 'today') {
            $current_time_zone = date_default_timezone_get();
            $time_zone = 'Asia/Calcutta';
            date_default_timezone_set($time_zone);
            $today_date = date('Y-m-d');
            date_default_timezone_set($current_time_zone);
            $this->db->like("DATE_FORMAT(CONVERT_TZ(CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $today_date, FALSE);        
        }
        $this->db->where('Type', 'MANUAL');
        $this->db->group_by('UserID');
        $query = $this->db->get(N_MESSAGES);
       // echo $this->db->last_query();die;
        $total = $query->num_rows();
        
        return $total;
    }

    /**
     * Used to get Unique people who sent message today
     */
    public function get_total_unique_user_sent_message($day='all') {
        $this->db->select("ThreadID, UserID");
        if($day == 'today') {
            $current_time_zone = date_default_timezone_get();
            $time_zone = 'Asia/Calcutta';
            date_default_timezone_set($time_zone);
            $today_date = date('Y-m-d');
            date_default_timezone_set($current_time_zone);
            $this->db->like("DATE_FORMAT(CONVERT_TZ(CreatedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $today_date, FALSE);        
        }
        $this->db->where('Type', 'MANUAL');
        $this->db->group_by('ThreadID');
        $query = $this->db->get(N_MESSAGES);
       // echo $this->db->last_query();die;
        $total = 0;
        if ($query->num_rows() > 0) { 
            $result = $query->result_array();
            $user_ids = array_unique(array_column($result, 'UserID'));
            $total = count($user_ids);
        }
        return $total;
    }

}
