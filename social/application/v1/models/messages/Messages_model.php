<?php

/**
 * This model is used for getting and storing Event related information
 * @package    Message_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Messages_model extends Common_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Function Name: get_recipients
     * @param ThreadGUID
     * Description: Get list of recipients
     */
    function get_recipients($ThreadGUID, $user_id = 0)
    {
        $this->db->select('U.UserGUID,U.FirstName,U.LastName,U.UserID');
        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', false);
        $this->db->select('P.Url as ProfileURL');
        $this->db->from(N_MESSAGE_RECIPIENT . ' MR');
        $this->db->join(N_MESSAGE_THREAD . ' MT', 'MT.ThreadID=MR.ThreadID', 'left');
        $this->db->join(USERS . ' U', 'U.UserID=MR.UserID', 'left');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID', 'left');
        $this->db->where('P.EntityType', 'User');
        $this->db->where_not_in('MR.Status', array('DELETED'));
        $this->db->where('MT.ThreadGUID', $ThreadGUID);
        $query = $this->db->get();
        //echo $this->db->last_query(); die;
        if ($query->num_rows())
        {

            $result = $query->result_array();
            foreach ($result as $key => $val)
            {
                $permission = $this->privacy_model->check_privacy($user_id, $val['UserID'], 'view_profile_picture');
                if (!$permission)
                {
                    $result[$key]['ProfilePicture'] = 'user_default.jpg';
                }
                unset($result[$key]['UserID']);
            }
            return $result;
        } else
        {

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
    function edit_thread($user_id, $ThreadGUID, $Subject, $Media)
    {
        $ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
        $ChangeMedia = 0;
        if ($Media)
        {
            $MediaGUID = $Media['MediaGUID'];
            $MediaID = get_detail_by_guid($MediaGUID, 21);
            $this->db->set('MediaID', $MediaID);
            $ChangeMedia = 1;
        }

        $oldDetails = $this->db->get_where(N_MESSAGE_THREAD, array('ThreadGUID' => $ThreadGUID));
        if ($oldDetails->num_rows())
        {
            $oldRow = $oldDetails->row_array();
        } else
        {
            return false;
        }


        $this->db->set('Subject', $Subject);
        $this->db->where('ThreadGUID', $ThreadGUID);
        $this->db->update(N_MESSAGE_THREAD);

        $Recipients = $this->get_recipients($ThreadGUID, $user_id);

        $data = $this->get_thread_details($user_id, $ThreadGUID, $Recipients);

        if ($oldRow['Subject'] != $Subject)
        {
            if (!$Subject)
            {
                $json = array('Action' => 'CONVERSATION_NAME_REMOVED', 'Value' => '');
            } else
            {
                $json = array('Action' => 'CONVERSATION_NAME', 'Value' => $Subject);
            }
            $this->add_date_message($user_id, $ThreadID);
            $this->add_automatic_message($user_id, $ThreadID, $json);
        }

        if ($ChangeMedia)
        {
            $json = array('Action' => 'CONVERSATION_IMAGE', 'Value' => $MediaID);
            $this->add_automatic_message($user_id, $ThreadID, $json);
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
    function add_automatic_message($user_id, $ThreadID, $msg)
    {
        $msg = json_encode($msg);
        $message = array('MessageGUID' => get_guid(), 'ThreadID' => $ThreadID, 'UserID' => $user_id, 'Subject' => '', 'Body' => $msg, 'AttachmentCount' => '0', 'Type' => 'AUTO', 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
        $this->db->insert(N_MESSAGES, $message);
    }

    /**
     * Function Name: add_participant
     * @param UserID
     * @param ThreadGUID
     * @param Recipients
     * Description: Add participants in particular thread
     */
    function add_participant($user_id, $ThreadGUID, $Recipients)
    {
        $this->load->model('notification_model');
        $participant = array();
        if ($Recipients)
        {
            $ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
            $from_user_detail = get_detail_by_id($user_id, 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
            $from_user_detail['FullName'] = $from_user_detail['FirstName'] . ' ' . $from_user_detail['LastName'];
            foreach ($Recipients as $user)
            {   
                $to_user_detail = get_detail_by_guid($user['UserGUID'], 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
                $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];
                $UID = $to_user_detail['UserID'];

                $query = $this->db->get_where(N_MESSAGE_RECIPIENT, array('UserID' => $UID, 'ThreadID' => $ThreadID));
                if ($query->num_rows())
                {
                    $row = $query->row();
                    if ($row->Status != 'ACTIVE')
                    {
                        $this->db->set('Status', 'ACTIVE');
                        $this->db->where('UserID', $UID);
                        $this->db->where('ThreadID', $ThreadID);
                        $this->db->update(N_MESSAGE_RECIPIENT);
                        $participant[] = array('UserGUID' => $user['UserGUID']);
                    }
                } 
                else
                {
                    $MessageRecipients = array(
                        'UserID' => $UID,
                        'ThreadID' => $ThreadID,
                        'InboxMessageID' => NULL,
                        'InboxUpdated' => NULL,
                        'InboxStatus' => NULL,
                        'OutboxMessageID' => NULL,
                        'OutboxUpdated' => NULL,
                        'OutboxStatus' => NULL
                    );
                    $this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
                    $participant[] = array('UserGUID' => $user['UserGUID']);
                }
                $this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $UID, 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $ThreadID, 'NotificationTypeID' => 78),0,78);
            }
        }

        if ($participant)
        {
            $json = array('Action' => 'ADDED', 'Value' => $participant);
            $this->add_date_message($user_id, $ThreadID);
            $this->add_automatic_message($user_id, $ThreadID, $json);
        }

        return $this->get_recipients($ThreadGUID, $user_id);
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
    function get_thread_id($module_id, $ModuleEntityGUID, $user_id, $Replyable, $Subject, $Recipients, $Datetime)
    {
        $RecipientCount = count($Recipients);
        $module_entity_id = get_detail_by_guid($ModuleEntityGUID, $module_id);
        $ThreadID = 0;
        if ($RecipientCount == 1 && MESSAGE_121_SINGLE_THREAD)
        {
            //One to One Message with Single thread then search for old thread between users
            $ThreadID = $this->get_thread_id_between_sender_receiver($user_id, $Recipients[0]['UserID'],$module_id, $module_entity_id);
        }

        if ($ThreadID == 0)
        {
            $MessageThread = array(
                'ThreadGUID' => get_guid(),
                'ModuleID' => $module_id,
                'ModuleEntityID' => $module_entity_id,
                'Subject' => $Subject,
                'UserID' => $user_id,
                'RecipientCount' => $RecipientCount,
                'Replyable' => $Replyable,
                'CreatedDate' => $Datetime,
                'ModifiedDate' => $Datetime
            );
            $this->db->insert(N_MESSAGE_THREAD, $MessageThread);
            $ThreadID = $this->db->insert_id();

            $json = array('Action' => 'THREAD_CREATED', 'Value' => $Datetime);
            $this->add_automatic_message($user_id, $ThreadID, $json);
        }
        return $ThreadID;
    }

    /**
     * Function Name: get_thread_id_by_guid
     * @param ThreadGUID
     * Description: returns thread id by guid
     */
    function get_thread_id_by_guid($ThreadGUID)
    {
        $this->db->select('ThreadID');
        $this->db->from(N_MESSAGE_THREAD);
        $this->db->where('ThreadGUID', $ThreadGUID);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->row()->ThreadID;
        } else
        {
            0;
        }
    }

    /**
     * Function Name: get_thread_guid_by_id
     * @param ThreadGUID
     * Description: returns thread guid by id
     */
    function get_thread_guid_by_id($ThreadID)
    {
        $this->db->select('ThreadGUID');
        $this->db->from(N_MESSAGE_THREAD);
        $this->db->where('ThreadID', $ThreadID);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->row()->ThreadGUID;
        } else
        {
            0;
        }
    }

    /**
     * Function Name: get_thread_id_between_sender_receiver
     * @param SenderID
     * @param ReceiverID
     * Description: get thread id between sender and receiver
     */
    function get_thread_id_between_sender_receiver($SenderID, $ReceiverID, $ModuleID='', $ModuleEntityID='')
    {
        $ThreadID = 0;
        $this->db->select("MT.ThreadID");
        $this->db->from(N_MESSAGE_THREAD . ' AS MT');
        $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'RIGHT');
        $this->db->where("MT.UserID", $SenderID);
        $this->db->where("MR.UserID", $ReceiverID);
        $this->db->where("MT.RecipientCount", 1);

        /*added by gautam - starts*/
        if($ModuleID!='' && $ModuleEntityID!=''){
            $this->db->where("MT.ModuleID",$ModuleID);
            $this->db->where("MT.ModuleEntityID",$ModuleEntityID);
        }
        /*added by gautam - ends*/
        $this->db->limit(1);        
        $query = $this->db->get();
        if ($query->num_rows() > 0)
        {
            $Thread = $query->row_array();
            $ThreadID = $Thread['ThreadID'];
        } else
        {
            $this->db->select("MT.ThreadID");
            $this->db->from(N_MESSAGE_THREAD . ' AS MT');
            $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'RIGHT');
            $this->db->where("MT.UserID", $ReceiverID);
            $this->db->where("MR.UserID", $SenderID);
            $this->db->where("MT.RecipientCount", 1);
            /*added by gautam - starts*/
                if($ModuleID!='' && $ModuleEntityID!=''){
                    $this->db->where("MT.ModuleID",$ModuleID);
                    $this->db->where("MT.ModuleEntityID",$ModuleEntityID);
                }
            /*added by gautam - ends*/
            $query = $this->db->get();
            if ($query->num_rows() > 0)
            {
                $Thread = $query->row_array();
                $ThreadID = $Thread['ThreadID'];
            }
        }
        return $ThreadID;
    }

    /**
     * Function Name: get_entity_users
     * @param ModuleID
     * @param ModuleEntityGUID
     * Description: get user list of particular entity (Group,Event,Page)
     */
    function get_entity_users($module_id, $ModuleEntityGUID)
    {
        $module_entity_id = get_detail_by_guid($ModuleEntityGUID, $module_id);
        switch ($module_id)
        {
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

        if (isset($table))
        {
            $this->db->select('U.UserID,U.UserGUID');
            $this->db->from(USERS . ' U');
            $this->db->join($table . ' M', 'M.UserID=U.UserID', 'left');
            $this->db->where($where, $module_entity_id);
            $this->db->where_not_in('U.StatusID', array(3,4));
            $this->db->where('M.StatusID', '2');
            $query = $this->db->get();
            if ($query->num_rows())
            {
                return $query->result_array();
            } else
            {
                return array();
            }
        } else
        {
            return array();
        }
    }

    /**
     * Function Name: add_date_message
     * @param UserID
     * @param ThreadID
     * Description: add automatic message for 1st message of a day
     */
    function add_date_message($user_id, $ThreadID)
    {
        /* $this->db->where('ThreadID',$ThreadID);
          $this->db->where("DATE(CreatedDate)='".get_current_date('%Y-%m-%d')."'",NULL,FALSE);
          $query = $this->db->get(N_MESSAGES);
          if(!$query->num_rows())
          {
          $json = array('Action'=>'CONVERSATION_DATE','Value'=>get_current_date('%Y-%m-%d'));
          $this->add_automatic_message($user_id,$ThreadID,$json);
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
    function getUserList($search_keyword, $user_id, $RemoveUsers)
    {
        
        $search_keyword = $this->db->escape_like_str($search_keyword); 
        
        $this->db->select('U.UserID,U.UserGUID,U.FirstName,U.LastName');

        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', false);
        $this->db->select('P.Url as ProfileURL');
        $this->db->from(USERS . ' U');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID', 'LEFT');
        $this->db->where('P.EntityType', 'User');
        $this->db->where('U.UserID!=' . $user_id, NULL, FALSE);
        $this->db->where("(U.FirstName LIKE '%" . $search_keyword . "%' OR U.LastName LIKE '%" . $search_keyword . "%' OR CONCAT(U.FirstName,' ',U.LastName) LIKE '%" . $search_keyword . "%')");

        if(!$this->settings_model->isDisabled(10)) {
            //For Friend Only
            $this->db->join(FRIENDS . " as f", "f.FriendID = U.UserID", "LEFT");
            $this->db->join(MODULES . " as m", "m.ModuleID=f.ModuleID and m.IsActive=1", "LEFT");
            $sqlCondition = array('f.Status' => 1, 'f.UserID' => $user_id);
            $this->db->where($sqlCondition);
        }else{
            //For followers Only
            $this->db->join(FOLLOW . " as f", "f.TypeEntityID = U.UserID", "LEFT");
            $sqlCondition = array('f.StatusID' => 2, 'f.UserID' => $user_id);
            $this->db->where($sqlCondition);
        }

        if ($RemoveUsers)
        {
            $this->db->where_not_in('U.UserGUID', $RemoveUsers);
        }
        $query = $this->db->get();
        if ($query->num_rows())
        {

            $result = $query->result_array();
            foreach ($result as $key => $val)
            {
                $permission = $this->privacy_model->check_privacy($user_id, $val['UserID'], 'view_profile_picture');
                if (!$permission)
                {
                    $result[$key]['ProfilePicture'] = 'user_default.jpg';
                }
                unset($result[$key]['UserID']);
            }
            return $result;
        } else
        {
            return array();
        }
    }

    /**
     * Function Name: compose
     * @param UserID
     * @param Data
     * Description: compose new message / thread
     */
    function compose($user_id, $Data)
    {
        $module_id = isset($Data['ModuleID']) && trim($Data['ModuleID']) != "" ? trim($Data['ModuleID']) : NULL;
        $ModuleEntityGUID = isset($Data['ModuleEntityGUID']) && trim($Data['ModuleEntityGUID']) != "" ? trim($Data['ModuleEntityGUID']) : NULL;
        $Replyable = isset($Data['Replyable']) && trim($Data['Replyable']) != "" ? trim($Data['Replyable']) : 1;
        $Subject = isset($Data['Subject']) && trim($Data['Subject']) != "" ? trim($Data['Subject']) : "";
        $Body = isset($Data['Body']) && trim($Data['Body']) != "" ? trim($Data['Body']) : "";
        $Media = isset($Data['Media']) ? $Data['Media'] : array();
        $Recipients = isset($Data['Recipients']) ? $Data['Recipients'] : array();

        $Datetime = get_current_date('%Y-%m-%d %H:%i:%s');
        $RecipientCount = count($Recipients);
        $AttachmentCount = count($Media);

        if ($RecipientCount >= 1)
        {
            $ThreadID = $this->get_thread_id($module_id, $ModuleEntityGUID, $user_id, $Replyable, $Subject, $Recipients, $Datetime);

            $this->add_date_message($user_id, $ThreadID);

            $Messages = array(
                'MessageGUID' => get_guid(),
                'ThreadID' => $ThreadID,
                'UserID' => $user_id,
                'Subject' => $Subject,
                'Body' => $Body,
                'AttachmentCount' => $AttachmentCount,
                'CreatedDate' => $Datetime,
            );
            $this->db->insert(N_MESSAGES, $Messages);
            $MessageID = $this->db->insert_id();
            //echo $this->db->last_query();die();
            //Sender Entry
            $MessageRecipients = array(
                'UserID' => $user_id,
                'ThreadID' => $ThreadID,
                'InboxMessageID' => NULL,
                'InboxUpdated' => NULL,
                'InboxStatus' => NULL,
                'OutboxMessageID' => $MessageID,
                'OutboxUpdated' => $Datetime,
                'OutboxStatus' => 'READ',
            );

            $this->db->select("MR.ThreadID");
            $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
            $this->db->where("MR.UserID", $user_id);
            $this->db->where("MR.ThreadID", $ThreadID);
            $query = $this->db->get();
            if ($query->num_rows() > 0)
            {
                $this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID' => $user_id, 'ThreadID' => $ThreadID));
            } else
            {
                $this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
            }

            if ($Media)
            {
                $m = array();
                foreach ($Media as $media)
                {
                    $m[] = array('MediaGUID' => $media['MediaGUID'], 'Caption' => $media['Caption'], 'MediaSectionID' => '7', 'MediaSectionReferenceID' => $MessageID);
                }
                $this->db->update_batch(MEDIA, $m, 'MediaGUID');
            }

            $this->load->model('notification_model');

            //receipient entry
                                   
            $from_user_detail = get_detail_by_id($user_id, 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
            $from_user_detail['FullName'] = $from_user_detail['FirstName'] . ' ' . $from_user_detail['LastName'];
                    
            foreach ($Recipients as $RecipientUser)
            {
                $to_user_detail = get_detail_by_id($RecipientUser['UserID'], 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
                $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];
                
                $MessageRecipients = array(
                    'UserID' => $RecipientUser['UserID'],
                    'ThreadID' => $ThreadID,
                    'InboxMessageID' => $MessageID,
                    'InboxNewMessageCount' => 1,
                    'InboxUpdated' => $Datetime,
                    'InboxStatus' => 'UN_SEEN',
                    'OutboxMessageID' => NULL,
                    'OutboxUpdated' => NULL,
                    'OutboxStatus' => NULL,
                );
                $this->db->select("MR.ThreadID");
                $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
                $this->db->where("MR.UserID", $RecipientUser['UserID']);
                $this->db->where("MR.ThreadID", $ThreadID);
                $query = $this->db->get();
                if ($query->num_rows() > 0)
                {
                    unset($MessageRecipients['InboxNewMessageCount']);
                    $this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID' => $RecipientUser['UserID'], 'ThreadID' => $ThreadID));

                    $this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', FALSE);
                    $this->db->where(array('UserID' => $RecipientUser['UserID'], 'ThreadID' => $ThreadID));
                    $this->db->update(N_MESSAGE_RECIPIENT);

                    $this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $MessageRecipients['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $MessageID, 'NotificationTypeID' => 77),0,77);
                } 
                else
                {
                    $this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $MessageRecipients['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $MessageID, 'NotificationTypeID' => 77),0,77);
                    $this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
                }
            }

            //senders entry
            $MessageRecipients = array(
                'UserID' => $user_id,
                'ThreadID' => $ThreadID,
                'InboxMessageID' => NULL,
                'InboxNewMessageCount' => 0,
                'InboxUpdated' => NULL,
                'InboxStatus' => NULL,
                'OutboxMessageID' => $MessageID,
                'OutboxUpdated' => $Datetime,
                'OutboxStatus' => 'READ',
            );
            if (MESSAGE_121_SINGLE_THREAD)
            {
                $MessageRecipients['InboxMessageID'] = $MessageID;
                $MessageRecipients['InboxUpdated'] = $Datetime;
                $MessageRecipients['InboxStatus'] = 'READ';
            }
            $this->db->select("MR.ThreadID");
            $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
            $this->db->where("MR.UserID", $user_id);
            $this->db->where("MR.ThreadID", $ThreadID);
            $query = $this->db->get();
            if ($query->num_rows() > 0)
            {
                $this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID' => $user_id, 'ThreadID' => $ThreadID));
            } else
            {
                $this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
            }
        }
        $return_data = $this->inbox($user_id, array(), FALSE, $ThreadID);
        if(!$return_data && isset($ThreadID))
        {
            $return_data = array('ThreadGUID'=>$this->get_thread_guid_by_id($ThreadID));
        }
        return $return_data;
    }

    /**
     * Function Name: get_total_unseen_count
     * @param UserID
     * Description: get number of total unseen messages of particular user
     */
    function get_total_unseen_count($user_id)
    {
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
    function change_unseen_to_seen($user_id)
    {
        $this->db->set('InboxStatus', 'UN_READ');
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
    function change_thread_status($user_id, $ThreadGUID, $Status)
    {
        $ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
        if ($Status != 'READ' && $Status != 'UN_READ')
        {
            if ($Status == 'UN_ARCHIVE')
            {
                $Status = 'READ';
            }
            $this->db->set('InboxStatus', $Status);
        }

        if ($Status == 'READ')
        {
            $this->db->set('InboxNewMessageCount', '0');
        } else if ($Status == 'UN_READ')
        {
            $this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', false);
        }

        $this->db->where('UserID', $user_id);
        $this->db->where('ThreadID', $ThreadID);
        $this->db->update(N_MESSAGE_RECIPIENT);

        if ($Status == 'DELETED')
        {
            $this->db->select('MessageID');
            $this->db->from(N_MESSAGES);
            $this->db->where('ThreadID', $ThreadID);
            $MsgQry = $this->db->get();
            if ($MsgQry->num_rows())
            {
                $MsgData = $MsgQry->result_array();
                $insert = array();
                foreach ($MsgData as $val)
                {
                    $query = $this->db->get_where(N_MESSAGE_DELETED, array('UserID' => $user_id, 'MessageID' => $val['MessageID']));
                    if ($query->num_rows() == 0)
                    {
                        $insert[] = array('UserID' => $user_id, 'MessageID' => $val['MessageID'], 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s'));
                    }
                }
                if ($insert)
                {
                    $this->db->insert_batch(N_MESSAGE_DELETED, $insert);
                }
            }
        }
    }

    /**
     * Function Name: reply
     * @param UserID
     * @param Data
     * Description: reply to particular thread
     */
    function reply($user_id, $Data)
    {
        $data['ResponseCode'] = 200;
        $data['Message'] = lang('msg_sent_success');

        $ThreadGUID = isset($Data['ThreadGUID']) && trim($Data['ThreadGUID']) != "" ? trim($Data['ThreadGUID']) : NULL;
        $MessageGUID = isset($Data['MessageGUID']) && trim($Data['MessageGUID']) != "" ? trim($Data['MessageGUID']) : NULL;
        $Subject = isset($Data['Subject']) && trim($Data['Subject']) != "" ? trim($Data['Subject']) : "";
        $Body = isset($Data['Body']) && trim($Data['Body']) != "" ? trim($Data['Body']) : "";
        $Media = isset($Data['Media']) ? $Data['Media'] : array();
        $Recipients = isset($Data['Recipients']) ? $Data['Recipients'] : array();

        $Datetime = get_current_date('%Y-%m-%d %H:%i:%s');

        if (empty($Recipients))
        {
            $Recipients = $this->get_recipients($Data['ThreadGUID'], $user_id);
        } 
        else
        {
            $r = array();
            foreach ($Recipients as $receipient)
            {
                $r[] = array(
                    'UserGUID' => $receipient['UserGUID'],
                    'UserID' => get_detail_by_guid($receipient['UserGUID'], 3)
                );
            }
            $Recipients = $r;
        }

        $r_array = array();
        if ($Recipients)
        {
            foreach ($Recipients as $user)
            {
                if (is_valid_user($user['UserGUID']))
                {
                    $r_array[] = $user;
                }
            }
        }
        $Recipients = $r_array;

        $RecipientCount = count($Recipients);
        $AttachmentCount = count($Media);

        if ($RecipientCount >= 1)
        {
            $ThreadID = $this->get_thread_id_by_guid($ThreadGUID);

            $this->add_date_message($user_id, $ThreadID);

            $Messages = array(
                'MessageGUID' => get_guid(),
                'ThreadID' => $ThreadID,
                'UserID' => $user_id,
                'Subject' => $Subject,
                'Body' => $Body,
                'AttachmentCount' => $AttachmentCount,
                'CreatedDate' => $Datetime,
            );
            $this->db->insert(N_MESSAGES, $Messages);
            $MessageID = $this->db->insert_id();
            //echo $this->db->last_query();die();
            //Sender Entry
            $MessageRecipients = array(
                'UserID' => $user_id,
                'ThreadID' => $ThreadID,
                'InboxMessageID' => NULL,
                'InboxUpdated' => NULL,
                'InboxStatus' => NULL,
                'OutboxMessageID' => $MessageID,
                'OutboxUpdated' => $Datetime,
                'OutboxStatus' => 'READ',
            );

            if (MESSAGE_121_SINGLE_THREAD)
            {
                $MessageRecipients['InboxMessageID'] = $MessageID;
                $MessageRecipients['InboxUpdated'] = $Datetime;
                $MessageRecipients['InboxStatus'] = 'READ';
            }

            $this->db->select("MR.ThreadID");
            $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
            $this->db->where("MR.UserID", $user_id);
            $this->db->where("MR.ThreadID", $ThreadID);
            $query = $this->db->get();
            if ($query->num_rows() > 0)
            {
                if (!MESSAGE_121_SINGLE_THREAD)
                {
                    unset($MessageRecipients['InboxMessageID']);
                    unset($MessageRecipients['InboxUpdated']);
                    unset($MessageRecipients['InboxStatus']);
                }
                $this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID' => $user_id, 'ThreadID' => $ThreadID));
            } 
            else
            {
                $this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
            }

            if ($Media)
            {
                $m = array();
                foreach ($Media as $media)
                {
                    $m[] = array('MediaGUID' => $media['MediaGUID'], 'Caption' => $media['Caption'], 'MediaSectionID' => '7', 'MediaSectionReferenceID' => $MessageID);
                }
                $this->db->update_batch(MEDIA, $m, 'MediaGUID');
            }

            $this->load->model('notification_model');
            //receipient entry
            $from_user_detail = array();
            foreach ($Recipients as $RecipientUser)
            {
                if ($RecipientUser['UserID'] == $user_id)
                {
                    continue;
                }
                $MessageRecipients = array(
                    'UserID' => $RecipientUser['UserID'],
                    'ThreadID' => $ThreadID,
                    'InboxMessageID' => $MessageID,
                    'InboxNewMessageCount' => 1,
                    'InboxUpdated' => $Datetime,
                    'InboxStatus' => 'UN_SEEN',
                    'OutboxMessageID' => NULL,
                    'OutboxUpdated' => NULL,
                    'OutboxStatus' => NULL,
                );
                $this->db->select("MR.ThreadID");
                $this->db->from(N_MESSAGE_RECIPIENT . ' AS MR');
                $this->db->where("MR.UserID", $RecipientUser['UserID']);
                $this->db->where("MR.ThreadID", $ThreadID);
                $query = $this->db->get();
                if ($query->num_rows() > 0)
                {
                    unset($MessageRecipients['InboxNewMessageCount']);
                    $this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID' => $RecipientUser['UserID'], 'ThreadID' => $ThreadID));

                    $this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', FALSE);
                    $this->db->where(array('UserID' => $RecipientUser['UserID'], 'ThreadID' => $ThreadID));
                    $this->db->update(N_MESSAGE_RECIPIENT);

                    $to_user_detail = get_detail_by_id($MessageRecipients['UserID'], 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
                    $to_user_detail['FullName'] = $to_user_detail['FirstName'] . ' ' . $to_user_detail['LastName'];

                    if(empty($from_user_detail))
                    {                        
                        $from_user_detail = get_detail_by_id($user_id, 3, 'FirstName,LastName,Email,UserGUID,UserID,ProfilePicture', 2);
                        $from_user_detail['FullName'] = $from_user_detail['FirstName'] . ' ' . $from_user_detail['LastName'];
                    }

                    $this->notification_model->send_email_notification(array('UserID' => $user_id, 'FromUserDetails' => $from_user_detail, 'ToUserID' => $MessageRecipients['UserID'], 'ToUserDetails' => $to_user_detail, 'RefrenceID' => $MessageID, 'NotificationTypeID' => 77),0,77);
                } 
                else
                {
                    $this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
                }
            }
            $this->db->set('ModifiedDate', get_current_date('%Y-%m-%d %H:%i:%s'));
            $this->db->where('ThreadID', $ThreadID);
            $this->db->update(N_MESSAGE_THREAD);
        } else
        {
            $data['Message'] = 412;
            $data['ResponseCode'] = lang('empty_recipients');
        }
        return $this->thread_message_list($user_id, array('ThreadGUID' => $ThreadGUID), FALSE, $MessageID);
    }

    /**
     * Function Name: remove_recipient
     * @param UserID
     * @param ThreadGUID
     * @param Recipients
     * Description: remove recipient from particular thread
     */
    function remove_recipient($user_id, $ThreadGUID, $Recipients)
    {
        $ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
        if ($Recipients)
        {
            foreach ($Recipients as $value)
            {
                $UID = get_detail_by_guid($value['UserGUID'], 3);
                $this->db->set('Status', 'DELETED');
                $this->db->where('UserID', $UID);
                $this->db->where('ThreadID', $ThreadID);
                $this->db->update(N_MESSAGE_RECIPIENT);

                $json = array('Action' => 'REMOVED', 'Value' => array(array('UserGUID' => $value['UserGUID'])));
                $this->add_date_message($user_id, $ThreadID);
                $this->add_automatic_message($user_id, $ThreadID, $json);
            }
        }
    }

    /**
     * Function Name: delete
     * @param UserID
     * @param MessageGUID
     * Description: delete particular message for particular user
     */
    function delete($user_id, $MessageGUID)
    {
        $InboxSet = false;
        $OutboxSet = false;
        $this->db->select('MessageID,ThreadID');
        $this->db->from(N_MESSAGES);
        $this->db->where('MessageGUID', $MessageGUID);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            $row = $query->row_array();
            $MessageID = $row['MessageID'];
            $ThreadID = $row['ThreadID'];

            $check = $this->db->get_where(N_MESSAGE_DELETED, array('UserID' => $user_id, 'MessageID' => $MessageID));
            if (!$check->num_rows())
            {
                $this->db->insert(N_MESSAGE_DELETED, array('UserID' => $user_id, 'MessageID' => $MessageID, 'CreatedDate' => get_current_date('%Y-%m-%d %H:%i:%s')));
            }

            $this->db->select('InboxMessageID,OutboxMessageID');
            $this->db->from(N_MESSAGE_RECIPIENT);
            $this->db->where('UserID', $user_id);
            $this->db->where('ThreadID', $ThreadID);
            $qry = $this->db->get();
            if ($qry->num_rows())
            {
                $row = $qry->row_array();
                $InboxMessageID = $row['InboxMessageID'];
                $OutboxMessageID = $row['OutboxMessageID'];
                if ($InboxMessageID == $MessageID)
                {
                    $this->db->select('M.MessageID');
                    $this->db->from(N_MESSAGES . ' M');
                    $this->db->where('M.ThreadID', $ThreadID);
                    $this->db->where("M.MessageID NOT IN (SELECT MessageID FROM " . N_MESSAGE_DELETED . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
                    $this->db->order_by('M.MessageID', 'DESC');
                    $query = $this->db->get();
                    if ($query->num_rows())
                    {
                        $InboxMessageID = $query->row()->MessageID;
                    } else
                    {
                        $InboxMessageID = NULL;
                    }
                    $InboxSet = true;
                }
                if ($OutboxMessageID == $MessageID)
                {
                    $this->db->select('M.MessageID');
                    $this->db->from(N_MESSAGES . ' M');
                    $this->db->where('M.ThreadID', $ThreadID);
                    $this->db->where("M.MessageID NOT IN (SELECT MessageID FROM " . N_MESSAGE_DELETED . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
                    $this->db->where('M.UserID', $user_id);
                    $this->db->order_by('M.MessageID', 'DESC');
                    $query = $this->db->get();
                    if ($query->num_rows())
                    {
                        $OutboxMessageID = $query->row()->MessageID;
                    } else
                    {
                        $OutboxMessageID = NULL;
                    }
                    $OutboxSet = true;
                }
                if ($InboxSet || $OutboxSet)
                {
                    if ($InboxSet)
                    {
                        $this->db->set('InboxMessageID', $InboxMessageID);
                    }
                    if ($OutboxSet)
                    {
                        $this->db->set('OutboxMessageID', $OutboxMessageID);
                    }
                    $this->db->where('UserID', $user_id);
                    $this->db->where('ThreadID', $ThreadID);
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
    function get_thread_subject($user_id, $ThreadSubject, $EditableThread, $Recipients)
    {
        return $ThreadSubject;
        /* $UserGUID = get_detail_by_id($user_id,3,'UserGUID',1);
          if($ThreadSubject == ''){
          if($Recipients)
          {
          $rcp = $Recipients;
          foreach($rcp as $k=>$v){
          if($v['UserGUID'] == $UserGUID){
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
          $index = ($UserGUID==$Recipients[0]['UserGUID']) ? 1 : 0 ;
          $ThreadSubject = $Recipients[$index]['FirstName'].' '.$Recipients[$index]['LastName'];
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
    function get_thread_image_name($UserGUID, $ThreadImageName, $EditableThread, $Recipients)
    {
        if ($EditableThread == 0)
        {
           // $UserGUID = get_detail_by_id($user_id, 3, 'UserGUID', 1);
            $index = ($UserGUID == $Recipients[0]['UserGUID']) ? 1 : 0;
            $ThreadImageName = isset($Recipients[$index]['ProfilePicture']) ? $Recipients[$index]['ProfilePicture'] : 'user_default.jpg';
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
    function inbox($user_id, $Data, $NumRows = FALSE, $ThreadID = '')
    {
        $UserGUID = get_detail_by_id($user_id, 3, 'UserGUID', 1);
        $module_id = isset($Data['ModuleID']) ? $Data['ModuleID'] : '';
        $ModuleEntityGUID = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : '';
        $search_keyword = isset($Data['SearchKeyword']) ? $Data['SearchKeyword'] : '';
        $Filter = isset($Data['Filter']) ? $Data['Filter'] : '';

        $symb = '*';
        if ($search_keyword == '')
        {
            $symb = '';
        }

        $module_entity_id = 0;
        if ($module_id)
        {
            $module_entity_id = get_detail_by_guid($ModuleEntityGUID, $module_id);
        }

        $this->db->select("MT.ThreadGUID,MT.Subject as ThreadSubject,MT.Replyable", false);
        $this->db->select('U.UserGUID as SenderUserGUID ', False);
        $this->db->select('IF(MT.RecipientCount>1,1,0) as EditableThread', false);
        $this->db->select('IF(MD.ImageName!="",MD.ImageName,"") as ThreadImageName', false);
        $this->db->select("MR.InboxStatus,MR.InboxUpdated,MR.InboxNewMessageCount");
        $this->db->select("M.MessageGUID, M.Body, M.Subject, M.AttachmentCount, M.UserID ");
        //$this->db->select("(SELECT COUNT(InboxMessageID) FROM ".N_MESSAGE_RECIPIENT." WHERE UserID='".$user_id."' AND InboxStatus!='DELETED' AND ThreadID=MT.ThreadID) as MessageCount",false);
       /* if($this->IsApp == 1){  
            // added by gautam
            $this->db->select("P.PageGUID, P.Title AS PageTitle, P.UserID as PageOwnerUserID, P.ProfilePicture as PageProfilePicture");  
        }
        */

        if ($search_keyword)
        {
            $this->db->select("IF(MT.Subject='" . $search_keyword . "','2','1') as InboxOrderBy", false);
        } else
        {
            $this->db->select("'1' as InboxOrderBy", false);
        }
        $this->db->from(N_MESSAGE_THREAD . ' AS MT');
        $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'RIGHT');
        $this->db->join(MEDIA . ' MD', 'MD.MediaID=MT.MediaID', 'LEFT');
        $this->db->join(N_MESSAGES . ' AS M', 'M.MessageID=MR.InboxMessageID', 'LEFT');
        /* if($this->IsApp == 1){  
            // added by gautam
            $this->db->join(PAGES. ' AS P','P.PageID=MT.ModuleEntityID','LEFT');
        }
         */

        $this->db->join(USERS . " AS U", "M.UserID=U.UserID");
        $this->db->where("(SELECT COUNT(InboxMessageID) FROM ".N_MESSAGE_RECIPIENT." WHERE UserID='".$user_id."' AND InboxStatus!='DELETED' AND ThreadID=MT.ThreadID)!=0",null,false);
        $this->db->where("MR.InboxUpdated is not NULL", NULL, FALSE);
        $this->db->where("MR.UserID", $user_id);
        //$this->db->where("(MR.UserID='".$user_id."' OR M.UserID='".$user_id."')",NULL,FALSE);
        $this->db->where('M.Type', 'MANUAL');
        if ($ThreadID)
        {
            $this->db->where('MT.ThreadID', $ThreadID);
        }
        if ($Filter)
        {
            if ($Filter == 'ARCHIVED')
            {
                $this->db->where("MR.InboxStatus", 'ARCHIVED');
            } else if ($Filter == 'UN_READ')
            {
                $this->db->where("MR.InboxNewMessageCount>0", NULL, FALSE);
                //$this->db->where_not_in("MR.InboxStatus",array('ARCHIVED','DELETED'));
                $this->db->where("(MR.InboxStatus NOT IN ('DELETED') OR MR.InboxStatus is NULL)", NULL, FALSE);
                $this->db->where("MR.Status!='DELETED'", NULL, FALSE);
            }
        } else
        {
            //$this->db->where_not_in("MR.InboxStatus",array('ARCHIVED','DELETED'));
            $this->db->where("(MR.InboxStatus NOT IN ('ARCHIVED','DELETED') OR MR.InboxStatus is NULL)", NULL, FALSE);
            $this->db->where("MR.Status!='DELETED'", NULL, FALSE);
        }
        $this->db->order_by('InboxOrderBy', 'DESC');
        $this->db->order_by('MR.InboxUpdated','DESC');
       /* if(empty($this->IsApp)){// added by gautam        
            $this->db->order_by('MT.ThreadID','DESC');
        } 
        */
        
        if ($module_entity_id)
        {
            $this->db->where('MT.ModuleID', $module_id);
            $this->db->where('MT.ModuleEntityID', $module_entity_id);
        }

        if ($search_keyword)
        {
            $search_keyword = $this->db->escape_like_str($search_keyword); 
            $this->db->where("(MT.Subject LIKE '%" . $search_keyword . "%' OR MT.ThreadID IN (SELECT SMT.ThreadID FROM " . N_MESSAGE_RECIPIENT . " SMT JOIN " . USERS . " SU ON SU.UserID=SMT.UserID WHERE SMT.ThreadID=MT.ThreadID AND SMT.Status='ACTIVE' AND (SU.FirstName LIKE '%" . $search_keyword . "%' OR SU.LastName LIKE '%" . $search_keyword . "%' OR CONCAT(SU.FirstName,' ',SU.LastName) LIKE '%" . $search_keyword . "%')))", NULL, FALSE);
            //$this->db->where("MATCH(MT.Subject) AGAINST ('%".$search_keyword.$symb."%' IN BOOLEAN MODE) OR MT.ThreadID IN (SELECT SMT.ThreadID FROM ".N_MESSAGE_RECIPIENT." SMT JOIN ".USERS." SU ON SU.UserID=SMT.UserID WHERE SMT.ThreadID=MT.ThreadID AND SMT.Status='ACTIVE' AND (MATCH(SU.FirstName,SU.LastName) AGAINST ('%".$search_keyword.$symb."%' IN BOOLEAN MODE))))",NULL,FALSE);
            //$this->db->where("(MT.ThreadID IN (SELECT SMT.ThreadID FROM ".N_MESSAGE_RECIPIENT." SMT JOIN ".USERS." SU ON SU.UserID=SMT.UserID WHERE SMT.ThreadID=MT.ThreadID AND (SU.FirstName LIKE '%".$search_keyword."%' OR SU.LastName LIKE '%".$search_keyword."%' OR CONCAT(SU.FirstName,' ',SU.LastName) LIKE '%".$search_keyword."%')))",NULL,FALSE);
        }

        $this->db->group_by('MT.ThreadGUID');

        if ($NumRows)
        {
            $query = $this->db->get();
            return $query->num_rows();
        } else {
            //pagination logic
            $page_no = isset($Data['PageNo']) && is_numeric($Data['PageNo']) ? $Data['PageNo'] : 1;
            $page_size = isset($Data['PageSize']) && is_numeric($Data['PageSize']) ? $Data['PageSize'] : NULL;
            if (!is_null($page_size)) // Check for pagination
            {
                //$offset = ($page_no - 1) * $page_size;
                //$this->db->limit($page_size, $offset);
                $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            }
            $query = $this->db->get();
            //echo $this->db->last_query();
        }
        $array = array();
        if ($query->num_rows())
        {
            foreach ($query->result_array() as $arr)
            {
               // $arr['SenderUserGUID'] = get_detail_by_id($arr['SenderUserGUID'], 3, 'UserGUID', 1); 

                $arr['ThreadSubject'] = $arr['ThreadSubject'];//$this->get_thread_subject($user_id, $arr['ThreadSubject'], $arr['EditableThread'], $this->get_recipients($arr['ThreadGUID'], $user_id));
                $arr['ThreadImageName'] = $this->get_thread_image_name($UserGUID, $arr['ThreadImageName'], $arr['EditableThread'], $this->get_recipients($arr['ThreadGUID'], $user_id));
                unset($arr['InboxOrderBy']);
                $array[] = $arr;
            }
        }
        if ($ThreadID && isset($array[0]))
        {
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
    function check_thread($user_id, $ThreadGUID)
    {
        $this->db->select('MT.ThreadID');
        $this->db->from(N_MESSAGE_THREAD . ' MT');
        $this->db->join(N_MESSAGE_RECIPIENT . ' MR', 'MR.ThreadID=MT.ThreadID');
        $this->db->where('MT.ThreadGUID', $ThreadGUID);
        $this->db->where('MR.UserID', $user_id);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return true;
        } else
        {
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
    function get_thread_details($user_id, $ThreadGUID, $Recipients = array())
    {
        $UserGUID = get_detail_by_id($user_id, 3, 'UserGUID', 1);
        
        $this->db->select('MT.ThreadGUID,MT.UserID as ThreadCreator,MT.Subject as ThreadSubject,MT.Replyable', false);
        $this->db->select('IF(MD.ImageName!="",MD.ImageName,"") as ThreadImageName', false);
        $this->db->select('IF(MT.RecipientCount>1,1,0) as EditableThread', false);
        $this->db->from(N_MESSAGE_THREAD . ' MT');
        $this->db->join(MEDIA . ' MD', 'MD.MediaID=MT.MediaID', 'LEFT');
        $this->db->where('MT.ThreadGUID', $ThreadGUID);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            $data = $query->row_array();
            if (!empty($Recipients))
            {
                $current_userGUID = get_detail_by_id($user_id, 3, 'UserGUID', 1);
                $exists = 0;
                foreach ($Recipients as $r)
                {
                    if ($r['UserGUID'] == $current_userGUID)
                    {
                        $exists = 1;
                    }
                }
                if (!$exists)
                {
                    return array();
                }
                $ThreadUserID = $data['ThreadCreator'];
                unset($data['ThreadCreator']);
                $data['CanRemoveParticipant'] = ($user_id == $ThreadUserID) ? 1 : 0;
                $data['ThreadSubject'] = $data['ThreadSubject'];// $this->get_thread_subject($user_id, $data['ThreadSubject'], $data['EditableThread'], $Recipients);
                $data['ThreadImageName'] = $this->get_thread_image_name($UserGUID, $data['ThreadImageName'], $data['EditableThread'], $Recipients);
            }
            return $data;
        } else
        {
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
    function thread_message_list($user_id, $Data, $NumRows = FALSE, $MessageID = '')
    {

        $this->db->select("MT.Subject");
        $this->db->select("M.MessageID, M.MessageGUID, M.Body, M.CreatedDate,M.MessageID,M.Type, M.UserID as SenderUserGUID");

        $this->db->select("U.UserID,U.UserGUID, U.FirstName, U.LastName");

        $this->db->select('IF(U.ProfilePicture="","user_default.jpg",U.ProfilePicture) as ProfilePicture', FALSE);

        $this->db->select("P.Url as ProfileURL");
        $this->db->select("(CASE WHEN U.Gender=0 THEN '' WHEN U.Gender=1 THEN 'Male' WHEN U.Gender=2 THEN 'Female' ELSE 'Others' END) AS Gender", false);

        // added by gautam
        /* if($this->IsApp == 1){
         $this->db->select("PA.Title AS PageTitle, PA.UserID as PageOwnerUserID, PA.ProfilePicture as PageProfilePicture"); 
        }
         */


        $this->db->from(N_MESSAGES . ' AS M');
        $this->db->join(N_MESSAGE_THREAD . ' AS MT', 'MT.ThreadID=M.ThreadID');
        $this->db->join(N_MESSAGE_RECIPIENT . ' AS MR', 'MR.ThreadID=MT.ThreadID', 'LEFT');
        $this->db->join(USERS . ' AS U', 'U.UserID=M.UserID', 'LEFT');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID', 'LEFT');
        /* if($this->IsApp == 1){       
            $this->db->join(PAGES. ' AS PA','PA.PageID=MT.ModuleEntityID','LEFT');       // added by gautam
        }
         */
        

        $this->db->where('P.EntityType', 'User');
        $this->db->where("M.MessageID NOT IN (SELECT MessageID FROM " . N_MESSAGE_DELETED . " WHERE UserID='" . $user_id . "')", NULL, FALSE);
        $this->db->where("MT.ThreadGUID", $Data['ThreadGUID']);
        $this->db->where("MR.UserID", $user_id);
        //$this->db->where("(MR.UserID='".$user_id."' OR M.UserID='".$user_id."')");
        $this->db->where("MR.Status!='DELETED'", NULL, FALSE);
        $this->db->where_not_in("U.StatusID", array(3, 4));

        if ($MessageID)
        {
            $this->db->where('M.MessageID', $MessageID);
        }

        $this->db->order_by('M.MessageID', 'DESC');


        if ($NumRows)
        {
            $query = $this->db->get();
            return $query->num_rows();
        } 
        else 
        {
            /*edited by gautam - starts*/
           /* if($this->IsApp == 1){          
                //pagination logic
                $page_no = isset($Data['PageNo']) && is_numeric($Data['PageNo']) ? $Data['PageNo'] : 1;
                $page_size = isset($Data['PageSize']) && is_numeric($Data['PageSize']) ? $Data['PageSize'] : NULL;
                if (!is_null($page_size)) // Check for pagination
                {
                    $offset = ($page_no - 1) * $page_size;
                    $this->db->limit($page_size, $offset);
                }
            } else { 
            */
                
                if(!empty($Data['LastRecordID'])){
                    $this->db->where('M.MessageID <',$Data['LastRecordID']);
                }
                $this->db->limit(25);                 
           /* } */

            $query = $this->db->get();
        }
        //echo $this->db->last_query();
        $array = array();
        foreach ($query->result_array() as $arr)
        {
            $arr['SenderUserGUID'] = get_detail_by_id($arr['SenderUserGUID'],3,'UserGUID',1);


            $permission = $this->privacy_model->check_privacy($user_id, $arr['UserID'], 'view_profile_picture');
            if (!$permission)
            {
                $arr['ProfilePicture'] = 'user_default.jpg';
            }



        /* if($this->IsApp == 1){  
            if (!$permission) {
                if($arr['PageData']['Owner']==1){
                    $arr['PageData']['ProfilePicture'] ='user_default.jpg';
                }
            }
             // For page data  - added by gautam
            $arr['PageData'] = array("PageTitle"=>$arr['PageTitle'], "Owner"=>0, "ProfilePicture"=>$arr['PageProfilePicture'], "Title"=>trim($arr['FirstName'].' '.$arr['LastName']));

            if(!empty($arr['PageOwnerUserID']) && $arr['UserID'] == $arr['PageOwnerUserID']){
                $arr['PageData']['Owner'] = 1;
                $arr['PageData']['ProfilePicture'] = $arr['ProfilePicture'];
            }
        }
         */

            unset($arr['UserID']);

            $arr['Media'] = $this->get_message_media($arr['MessageID']);
            //unset($arr['MessageID']);
            $d = $this->get_message_action($user_id, $arr['UserGUID'], $arr['Type'], $arr['Body']);
            $arr['ActionCreator'] = $d['ActionCreator'];
            $arr['ActionValue'] = $d['ActionValue'];
            $arr['ActionName'] = $d['ActionName'];
            $arr['Body'] = nl2br($arr['Body']);


            // For page data  - added by gautam
           /* if($this->IsApp == 1){
                unset($arr['UserID']);
                unset($arr['FirstName']);
                unset($arr['LastName']);
                unset($arr['Subject']);
                unset($arr['Gender']);
                unset($arr['PageTitle']);
                unset($arr['PageOwnerUserID']);
                unset($arr['PageProfilePicture']);
                unset($arr['ProfilePicture']);
                unset($arr['ProfileURL']);
            }
            */
            
            $array[] = $arr;
        }
        if ($MessageID && isset($array[0]))
        {
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
    function get_user_detail($UserGUID, $current_userGUID)
    {
        $this->db->select('U.FirstName,U.LastName,U.UserGUID,P.Url as ProfileURL');
        $this->db->from(USERS . ' U');
        $this->db->join(PROFILEURL . ' P', 'P.EntityID=U.UserID', 'left');
        $this->db->where('UserGUID', $UserGUID);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            $array = $query->row_array();
            if ($UserGUID == $current_userGUID)
            {
                $array['FirstName'] = 'You';
                $array['LastName'] = '';
            }
            return $array;
        } else
        {
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
    function get_message_action($user_id, $UserGUID, $type, $Body)
    {
        $UGUID = get_detail_by_id($user_id, 3, 'UserGUID', 1);
        $data = array('ActionCreator' => '', 'ActionValue' => '', 'ActionName' => '');
        if ($type == 'AUTO')
        {
            $json = json_decode($Body);
            $data['ActionCreator'] = $this->get_user_detail($UserGUID, $UGUID);
            $data['ActionName'] = $json->Action;

            switch ($json->Action)
            {
                case 'ADDED':
                    $data['ActionValue'] = array();
                    if ($json->Value)
                    {
                        foreach ($json->Value as $val)
                        {
                            $data['ActionValue'][] = $this->get_user_detail($val->UserGUID, $UGUID);
                        }
                    }
                    break;

                case 'REMOVED':
                    $data['ActionValue'] = array();
                    if ($json->Value)
                    {
                        foreach ($json->Value as $val)
                        {
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
    function get_message_media($MessageID)
    {
        $this->db->select('M.MediaGUID,M.ConversionStatus,ME.Name as Ext,M.ImageName,M.Caption,IFNULL(M.OriginalName,M.ImageName) as OriginalName', false);
        $this->db->select('MT.Name as MediaType');
        $this->db->from(MEDIA . ' M');
        $this->db->join(MEDIAEXTENSIONS . ' ME', 'M.MediaExtensionID=ME.MediaExtensionID', 'LEFT');
        $this->db->join(MEDIATYPES . ' MT', 'ME.MediaTypeID=MT.MediaTypeID', 'LEFT');
        $this->db->where('M.MediaSectionID', '7');
        $this->db->where('M.MediaSectionReferenceID', $MessageID);
        $query = $this->db->get();
        if ($query->num_rows())
        {
            return $query->result_array();
        } else
        {
            return array();
        }
    }

}
