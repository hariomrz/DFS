<?php
/**
 * This model is used for getting and storing Event related information
 * @package    Message_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Messages_model extends CI_Model {

    function __construct() {
        parent::__construct();
        
    }
    
    function get_recipients($ThreadGUID)
    {
    	$this->db->select('U.UserGUID,U.FirstName,U.LastName');
    	$this->db->select('IF(u.ProfilePicture="","user_default.jpg",u.ProfilePicture) as ProfilePicture',false);
    	$this->db->select('P.Url as ProfileURL');
    	$this->db->from(N_MESSAGE_RECIPIENT.' MR');
    	$this->db->join(N_MESSAGE_THREAD.' MT','MT.ThreadID=MR.ThreadID','left');
    	$this->db->join(USERS.' U','U.UserID=MR.UserID','left');
    	$this->db->join(PROFILEURL.' P','P.EntityID=U.UserID','left');
    	$this->db->where('P.EntityType','User');
    	$this->db->where_not_in('MR.Status',array('DELETED'));
    	$this->db->where('MT.ThreadGUID',$ThreadGUID);
    	$query = $this->db->get();
    	if($query->num_rows()){
    		return $query->result_array();
    	} else {
    		return array();
    	}
    }
    
    function edit_thread($user_id,$ThreadGUID,$Subject,$Media){
    	$ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
    	$ChangeMedia = 0;
    	if($Media){
    		$MediaGUID = $Media['MediaGUID'];
    		$MediaID = get_detail_by_guid($MediaGUID,21);
    		$this->db->set('MediaID',$MediaID);
    		$ChangeMedia = 1;
    	}

    	$this->db->set('Subject',$Subject);
    	$this->db->where('ThreadGUID',$ThreadGUID);
    	$this->db->update(N_MESSAGE_THREAD);
    	$Recipients = $this->get_recipients($ThreadGUID);
    	$data = $this->get_thread_details($ThreadGUID,$Recipients);
    	
    	$json = array('Action'=>'CONVERSATION_NAME','Value'=>$Subject);
    	$this->add_automatic_message($user_id,$ThreadID,$json);

    	if($ChangeMedia){
    		$json = array('Action'=>'CONVERSATION_IMAGE','Value'=>$MediaID);
    		$this->add_automatic_message($user_id,$ThreadID,$json);
    	}

    	return $data;
    }

    function add_automatic_message($user_id,$ThreadID,$msg){
    	$msg = json_encode($msg);
    	$message = array('MessageGUID'=>get_guid(),'ThreadID'=>$ThreadID,'UserID'=>$user_id,'Subject'=>'','Body'=>$msg,'AttachmentCount'=>'0','Type'=>'AUTO','CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));
    	$this->db->insert(N_MESSAGES,$message);
    }

    function add_participant($user_id,$ThreadGUID,$Recipients){
    	$participant = array();
    	if($Recipients){
    		$ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
    		foreach ($Recipients as $user) {
    			$UID = get_detail_by_guid($user['UserGUID'],3);
    			$query = $this->db->get_where(N_MESSAGE_RECIPIENT,array('UserID'=>$UID,'ThreadID'=>$ThreadID));
    			if($query->num_rows()){
    				$row = $query->row();
    				if($row->Status!='ACTIVE'){
    					$this->db->set('Status','ACTIVE');
    					$this->db->where('UserID',$UID);
    					$this->db->where('ThreadID',$ThreadID);
    					$this->db->update(N_MESSAGE_RECIPIENT);
    					$participant[] = array('UserGUID'=>$user['UserGUID']);
    				}
    			} else {
    				$MessageRecipients = array(
		                'UserID'=>$UID,
		                'ThreadID'=>$ThreadID,
		                'InboxMessageID'=>NULL,
		                'InboxUpdated'=>NULL,
		                'InboxStatus'=>NULL,
		                'OutboxMessageID'=>NULL,
		                'OutboxUpdated'=>NULL,
		                'OutboxStatus'=>NULL
		            );
    				$this->db->insert(N_MESSAGE_RECIPIENT,$MessageRecipients);
    				$participant[] = array('UserGUID'=>$user['UserGUID']);
    			}
    		}
    	}

    	if($participant){
    		$json = array('Action'=>'ADDED','Value'=>$participant);
    		$this->add_automatic_message($user_id,$ThreadID,$json);
    	}
    }
    
	function get_thread_id($module_id, $ModuleEntityGUID, $user_id, $Replyable, $Subject, $Recipients, $Datetime)
	{
		$RecipientCount =count($Recipients);
		$module_entity_id = get_detail_by_guid($ModuleEntityGUID, $module_id);
		$ThreadID = 0;
		if($RecipientCount==1 && MESSAGE_121_SINGLE_THREAD){
			//One to One Message with Single thread then search for old thread between users
			$ThreadID = $this->get_thread_id_between_sender_receiver($user_id, $Recipients[0]['UserID']);
		}	

		if($ThreadID==0){
			$MessageThread = array(
				'ThreadGUID'=>get_guid(),
	            'ModuleID'=>$module_id,
	            'ModuleEntityID'=>$module_entity_id,
	            'Subject'=>$Subject,
	            'UserID'=>$user_id,
	            'RecipientCount'=>$RecipientCount,
	            'Replyable'=>$Replyable,
	            'CreatedDate'=>$Datetime,	            
	        );
	        $this->db->insert(N_MESSAGE_THREAD, $MessageThread);
	        $ThreadID = $this->db->insert_id();

	        $json = array('Action'=>'THREAD_CREATED','Value'=>$Datetime);
    		$this->add_automatic_message($user_id,$ThreadID,$json);
		}
		return $ThreadID;
	}


	function get_thread_id_by_guid($ThreadGUID){
		$this->db->select('ThreadID');
		$this->db->from(N_MESSAGE_THREAD);
		$this->db->where('ThreadGUID',$ThreadGUID);
		$query = $this->db->get();
		if($query->num_rows()){
			return $query->row()->ThreadID;
		} else {
			0;
		}
	}

	function get_thread_id_between_sender_receiver($SenderID, $ReceiverID){
		$ThreadID=0;
		$this->db->select("MT.ThreadID");
		$this->db->from(N_MESSAGE_THREAD.' AS MT');
		$this->db->join(N_MESSAGE_RECIPIENT. ' AS MR','MR.ThreadID=MT.ThreadID','RIGHT');
		$this->db->where("MT.UserID",$SenderID);
		$this->db->where("MR.UserID",$ReceiverID);
		$this->db->where("MT.RecipientCount",1);
		$query = $this->db->get();
		if($query->num_rows() > 0){
			$Thread = $query->row_array();
			$ThreadID=$Thread['ThreadID'];
		}else{
			$this->db->select("MT.ThreadID");
			$this->db->from(N_MESSAGE_THREAD.' AS MT');
			$this->db->join(N_MESSAGE_RECIPIENT. ' AS MR','MR.ThreadID=MT.ThreadID','RIGHT');
			$this->db->where("MT.UserID",$ReceiverID);
			$this->db->where("MR.UserID",$SenderID);
			$this->db->where("MT.RecipientCount",1);
			$query = $this->db->get();
			if($query->num_rows() > 0){
				$Thread = $query->row_array();
				$ThreadID=$Thread['ThreadID'];
			}
		}
		return $ThreadID;
	}



	function get_entity_users($module_id,$ModuleEntityGUID)
	{
		$module_entity_id = get_detail_by_guid($ModuleEntityGUID,$module_id);
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

		if(isset($table))
		{
			$this->db->select('U.UserID,U.UserGUID');
			$this->db->from(USERS.' U');
			$this->db->join($table.' M','M.UserID=U.UserID','left');
			$this->db->where($where,$module_entity_id);
			$this->db->where('U.StatusID','2');
			$this->db->where('M.StatusID','2');
			$query = $this->db->get();
			if($query->num_rows())
			{
				return $query->result_array();
			} else {
				return array();
			}
		} else {
			return array();
		}
	}


	function compose($user_id, $Data){
		
		$module_id = isset($Data['ModuleID']) && trim($Data['ModuleID'])!=""?trim($Data['ModuleID']):NULL;
		$ModuleEntityGUID = isset($Data['ModuleEntityGUID']) && trim($Data['ModuleEntityGUID'])!=""?trim($Data['ModuleEntityGUID']):NULL;
		$Replyable = isset($Data['Replyable']) && trim($Data['Replyable'])!=""?trim($Data['Replyable']):1;
		$Subject = isset($Data['Subject']) && trim($Data['Subject'])!=""?trim($Data['Subject']):"";
		$Body = isset($Data['Body']) && trim($Data['Body'])!=""?trim($Data['Body']):"";
		$Media = isset($Data['Media']) ? $Data['Media']:array();		
		$Recipients = isset($Data['Recipients']) ? $Data['Recipients']:array();
		
		$Datetime = gmdate("Y-m-d H:i:s");
		$RecipientCount =count($Recipients);
		$AttachmentCount =count($Media);

        if($RecipientCount >=1)
        {
        	$ThreadID = $this->get_thread_id($module_id, $ModuleEntityGUID, $user_id, $Replyable, $Subject, $Recipients, $Datetime);
            
            $Messages = array(
                'MessageGUID'=>get_guid(),
                'ThreadID'=>$ThreadID,
                'UserID'=>$user_id,  
                'Subject'=>$Subject,              
                'Body'=>$Body,
                'AttachmentCount'=>$AttachmentCount,
                'CreatedDate'=>$Datetime,
            );
            $this->db->insert(N_MESSAGES, $Messages);
            $MessageID = $this->db->insert_id();
            //echo $this->db->last_query();die();

            //Sender Entry
            $MessageRecipients = array(
                'UserID'=>$user_id,
                'ThreadID'=>$ThreadID,
                'InboxMessageID'=>NULL,
                'InboxUpdated'=>NULL,
                'InboxStatus'=>NULL,
                'OutboxMessageID'=>$MessageID,
                'OutboxUpdated'=>$Datetime,
                'OutboxStatus'=>'READ',
            );

            $this->db->select("MR.ThreadID");
			$this->db->from(N_MESSAGE_RECIPIENT.' AS MR');
			$this->db->where("MR.UserID",$user_id);
			$this->db->where("MR.ThreadID",$ThreadID);
			$query = $this->db->get();
			if($query->num_rows() > 0){
				$this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID'=>$user_id, 'ThreadID'=>$ThreadID));
			}else{
				$this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
			}
            
			if($Media)
			{
				$m = array();
				foreach ($Media as $media)
				{
					$m[] = array('MediaGUID'=>$media['MediaGUID'],'Caption'=>$media['Caption'],'MediaSectionID'=>'7','MediaSectionReferenceID'=>$MessageID);
					
				}
				$this->db->update_batch(MEDIA, $m, 'MediaGUID'); 
			}

            //receipient entry
            foreach($Recipients as $RecipientUser)
            {
            	$MessageRecipients = array(
	                'UserID'=>$RecipientUser['UserID'],
	                'ThreadID'=>$ThreadID,
	                'InboxMessageID'=>$MessageID,
	                'InboxNewMessageCount'=>1,
	                'InboxUpdated'=>$Datetime,
	                'InboxStatus'=>'UN_SEEN',
	                'OutboxMessageID'=>NULL,
	                'OutboxUpdated'=>NULL,
	                'OutboxStatus'=>NULL,
	            );
	            $this->db->select("MR.ThreadID");
				$this->db->from(N_MESSAGE_RECIPIENT.' AS MR');
				$this->db->where("MR.UserID",$RecipientUser['UserID']);
				$this->db->where("MR.ThreadID",$ThreadID);
				$query = $this->db->get();
				if($query->num_rows() > 0){

					unset($MessageRecipients['InboxNewMessageCount']);
					$this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID'=>$RecipientUser['UserID'], 'ThreadID'=>$ThreadID));
					
					$this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', FALSE);
					$this->db->where(array('UserID'=>$RecipientUser['UserID'], 'ThreadID'=>$ThreadID));
					$this->db->update(N_MESSAGE_RECIPIENT);

				}else{
					$this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
				}	            
            }

            //senders entry
        	$MessageRecipients = array(
                'UserID'=>$user_id,
                'ThreadID'=>$ThreadID,
                'InboxMessageID'=>NULL,
                'InboxNewMessageCount'=>0,
                'InboxUpdated'=>NULL,
                'InboxStatus'=>NULL,
                'OutboxMessageID'=>$MessageID,
                'OutboxUpdated'=>$Datetime,
                'OutboxStatus'=>'READ',
            );
            if(MESSAGE_121_SINGLE_THREAD)
            {
            	$MessageRecipients['InboxMessageID'] = $MessageID;
            	$MessageRecipients['InboxUpdated'] = $Datetime;
            	$MessageRecipients['InboxStatus'] = 'READ';
            }
            $this->db->select("MR.ThreadID");
			$this->db->from(N_MESSAGE_RECIPIENT.' AS MR');
			$this->db->where("MR.UserID",$user_id);
			$this->db->where("MR.ThreadID",$ThreadID);
			$query = $this->db->get();
			if($query->num_rows() > 0){
				$this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID'=>$user_id, 'ThreadID'=>$ThreadID));
			}else{
				$this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
			}	            

        }
        return $this->inbox($user_id,array(),FALSE,$ThreadID);
	}

	function change_thread_status($user_id,$ThreadGUID,$Status){
		$ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
		$this->db->set('InboxStatus',$Status);
		if($Status == 'READ')
		{
			$this->db->set('InboxNewMessageCount','0');
		} else if($Status == 'UN_READ')
		{
			$this->db->set('InboxNewMessageCount','InboxNewMessageCount+1',false);
		}
		$this->db->where('UserID',$user_id);
		$this->db->where('ThreadID',$ThreadID);
		$this->db->update(N_MESSAGE_RECIPIENT);

		if($Status == 'DELETED')
		{
			$this->db->select('MessageID');
			$this->db->from(N_MESSAGES);
			$this->db->where('ThreadID',$ThreadID);
			$MsgQry = $this->db->get();
			if($MsgQry->num_rows())
			{
				$MsgData = $MsgQry->result_array();
				$insert = array();
				foreach($MsgData as $val){
					$query = $this->db->get_where(N_MESSAGE_DELETED,array('UserID'=>$user_id,'MessageID'=>$val['MessageID']));
					if($query->num_rows()==0){
						$insert[] = array('UserID'=>$user_id,'MessageID'=>$val['MessageID'],'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s'));						
					}
				}
				if($insert){
					$this->db->insert_batch(N_MESSAGE_DELETED,$insert);					
				}
			}
		}
	}

	function reply($user_id, $Data){
		
		$data['ResponseCode'] = 200;
		$data['Message'] = lang('msg_sent_success');

		$ThreadGUID = isset($Data['ThreadGUID']) && trim($Data['ThreadGUID'])!=""?trim($Data['ThreadGUID']):NULL;
		$MessageGUID = isset($Data['MessageGUID']) && trim($Data['MessageGUID'])!=""?trim($Data['MessageGUID']):NULL;
		$Subject = isset($Data['Subject']) && trim($Data['Subject'])!=""?trim($Data['Subject']):"";
		$Body = isset($Data['Body']) && trim($Data['Body'])!=""?trim($Data['Body']):"";
		$Media = isset($Data['Media']) ? $Data['Media']:array();		
		$Recipients = isset($Data['Recipients']) ? $Data['Recipients']:array();		
		
		$Datetime = gmdate("Y-m-d H:i:s");
		
		if(empty($Recipients)){
			$Recipients = $this->get_recipients($Data['ThreadGUID']);
		} else {
			$r = array();
			foreach($Recipients as $receipient){
				$r[] = array(
					'UserGUID'=>$receipient['UserGUID'],
					'UserID'=>get_detail_by_guid($receipient['UserGUID'], 3)
				);
			}
			$Recipients = $r;
		}

		$r_array = array();
        if($Recipients){
            foreach ($Recipients as $user) {
                if(is_valid_user($user['UserGUID'])){
                    $r_array[] = $user;
                }
            }
        }
        $Recipients = $r_array;

		$RecipientCount =count($Recipients);
		$AttachmentCount =count($Media);
        


        if($RecipientCount >=1)
        {
        	$ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
            
            $Messages = array(
                'MessageGUID'=>get_guid(),
                'ThreadID'=>$ThreadID,
                'UserID'=>$user_id,  
                'Subject'=>$Subject,              
                'Body'=>$Body,
                'AttachmentCount'=>$AttachmentCount,
                'CreatedDate'=>$Datetime,
            );
            $this->db->insert(N_MESSAGES, $Messages);
            $MessageID = $this->db->insert_id();
            //echo $this->db->last_query();die();

            //Sender Entry
            $MessageRecipients = array(
                'UserID'=>$user_id,
                'ThreadID'=>$ThreadID,
                'InboxMessageID'=>NULL,
                'InboxUpdated'=>NULL,
                'InboxStatus'=>NULL,
                'OutboxMessageID'=>$MessageID,
                'OutboxUpdated'=>$Datetime,
                'OutboxStatus'=>'READ',
            );

            $this->db->select("MR.ThreadID");
			$this->db->from(N_MESSAGE_RECIPIENT.' AS MR');
			$this->db->where("MR.UserID",$user_id);
			$this->db->where("MR.ThreadID",$ThreadID);
			$query = $this->db->get();
			if($query->num_rows() > 0){
				$this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID'=>$user_id, 'ThreadID'=>$ThreadID));
			}else{
				$this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
			}
	
			if($Media)
			{
				$m = array();
				foreach ($Media as $media)
				{
					$m[] = array('MediaGUID'=>$media['MediaGUID'],'Caption'=>$media['Caption'],'MediaSectionID'=>'7','MediaSectionReferenceID'=>$MessageID);
					
				}
				$this->db->update_batch(MEDIA, $m, 'MediaGUID'); 
			}            

            //receipient entry
            foreach($Recipients as $RecipientUser)
            {
            	$MessageRecipients = array(
	                'UserID'=>$RecipientUser['UserID'],
	                'ThreadID'=>$ThreadID,
	                'InboxMessageID'=>$MessageID,
	                'InboxNewMessageCount'=>1,
	                'InboxUpdated'=>$Datetime,
	                'InboxStatus'=>'UN_SEEN',
	                'OutboxMessageID'=>NULL,
	                'OutboxUpdated'=>NULL,
	                'OutboxStatus'=>NULL,
	            );
	            $this->db->select("MR.ThreadID");
				$this->db->from(N_MESSAGE_RECIPIENT.' AS MR');
				$this->db->where("MR.UserID",$RecipientUser['UserID']);
				$this->db->where("MR.ThreadID",$ThreadID);
				$query = $this->db->get();
				if($query->num_rows() > 0){

					unset($MessageRecipients['InboxNewMessageCount']);
					$this->db->update(N_MESSAGE_RECIPIENT, $MessageRecipients, array('UserID'=>$RecipientUser['UserID'], 'ThreadID'=>$ThreadID));
					
					$this->db->set('InboxNewMessageCount', 'InboxNewMessageCount+1', FALSE);
					$this->db->where(array('UserID'=>$RecipientUser['UserID'], 'ThreadID'=>$ThreadID));
					$this->db->update(N_MESSAGE_RECIPIENT);

				}else{
					$this->db->insert(N_MESSAGE_RECIPIENT, $MessageRecipients);
				}	            
            }            
        } else {
        	$data['Message'] = 511;
        	$data['ResponseCode'] = lang('empty_recipients');
        }
        return $this->thread_message_list($user_id, array('ThreadGUID'=>$ThreadGUID), FALSE, $MessageID);
	}

	function remove_recipient($user_id,$ThreadGUID,$Recipients){
		$ThreadID = $this->get_thread_id_by_guid($ThreadGUID);
		if($Recipients){
			foreach ($Recipients as $value) {
				$UID = get_detail_by_guid($value['UserGUID'],3);
				$this->db->set('Status','DELETED');
				$this->db->where('UserID',$UID);
				$this->db->where('ThreadID',$ThreadID);
				$this->db->update(N_MESSAGE_RECIPIENT);
				
				$json = array('Action'=>'REMOVED','Value'=>array(array('UserGUID'=>$value['UserGUID'])));
    			$this->add_automatic_message($user_id,$ThreadID,$json);
			}
		}
	}

	function delete($user_id,$MessageGUID){
		$InboxSet = false;
		$OutboxSet = false;
		$this->db->select('MessageID,ThreadID');
		$this->db->from(N_MESSAGES);
		$this->db->where('MessageGUID',$MessageGUID);
		$query = $this->db->get();
		if($query->num_rows()){
			$row = $query->row_array();
			$MessageID = $row['MessageID'];
			$ThreadID = $row['ThreadID'];

			$check = $this->db->get_where(N_MESSAGE_DELETED,array('UserID'=>$user_id,'MessageID'=>$MessageID));
			if(!$check->num_rows()){
				$this->db->insert(N_MESSAGE_DELETED,array('UserID'=>$user_id,'MessageID'=>$MessageID,'CreatedDate'=>get_current_date('%Y-%m-%d %H:%i:%s')));				
			}

			$this->db->select('InboxMessageID,OutboxMessageID');
			$this->db->from(N_MESSAGE_RECIPIENT);
			$this->db->where('UserID',$user_id);
			$this->db->where('ThreadID',$ThreadID);
			$qry = $this->db->get();
			if($qry->num_rows()){
				$row = $qry->row_array();
				$InboxMessageID = $row['InboxMessageID'];
				$OutboxMessageID = $row['OutboxMessageID'];
				if($InboxMessageID == $MessageID){
					$this->db->select('M.MessageID');
					$this->db->from(N_MESSAGES.' M');
					$this->db->where('M.ThreadID',$ThreadID);
					$this->db->where("M.MessageID NOT IN (SELECT MessageID FROM ".N_MESSAGE_DELETED." WHERE UserID='".$user_id."')",NULL,FALSE);
					$this->db->order_by('M.MessageID','DESC');
					$query = $this->db->get();
					if($query->num_rows()){
						$InboxMessageID = $query->row()->MessageID;
					} else {
						$InboxMessageID = NULL;
					}
					$InboxSet = true;
				}
				if($OutboxMessageID == $MessageID){
					$this->db->select('M.MessageID');
					$this->db->from(N_MESSAGES.' M');
					$this->db->where('M.ThreadID',$ThreadID);
					$this->db->where("M.MessageID NOT IN (SELECT MessageID FROM ".N_MESSAGE_DELETED." WHERE UserID='".$user_id."')",NULL,FALSE);
					$this->db->where('M.UserID',$user_id);
					$this->db->order_by('M.MessageID','DESC');
					$query = $this->db->get();
					if($query->num_rows()){
						$OutboxMessageID = $query->row()->MessageID;
					} else {
						$OutboxMessageID = NULL;
					}
					$OutboxSet = true;
				}
				if($InboxSet || $OutboxSet){
					if($InboxSet){
						$this->db->set('InboxMessageID',$InboxMessageID);
					}
					if($OutboxSet){
						$this->db->set('OutboxMessageID',$OutboxMessageID);
					}
					$this->db->where('UserID',$user_id);
					$this->db->where('ThreadID',$ThreadID);
					$this->db->update(N_MESSAGE_RECIPIENT);
				}
			}
		}
	}

	function get_thread_subject($ThreadSubject,$Recipients){
		if($ThreadSubject == ''){
			if($Recipients)
			{
				foreach ($Recipients as $recipient)
				{
					$ThreadSubject .= $recipient['FirstName'].' '.$recipient['LastName'].', ';
				}
				if($ThreadSubject)
				{
					$ThreadSubject = substr($ThreadSubject, 0, -2);
				}
			}
		}
		return $ThreadSubject;
	}


	function inbox($user_id, $Data, $NumRows=FALSE, $ThreadID='')
	{

		$module_id = isset($Data['ModuleID']) ? $Data['ModuleID'] : '' ;
		$ModuleEntityGUID = isset($Data['ModuleEntityGUID']) ? $Data['ModuleEntityGUID'] : '' ;
        $search_keyword = isset($Data['SearchKeyword']) ? $Data['SearchKeyword'] : '' ;
        $Filter = isset($Data['Filter']) ? $Data['Filter'] : '' ;
        $module_entity_id = 0;
        if($module_id)
        {
        	$module_entity_id = get_detail_by_guid($ModuleEntityGUID,$module_id);        	
        }

		$this->db->select("MT.ThreadGUID,MT.Subject as ThreadSubject,MT.Replyable",false);
		$this->db->select('IF(MD.ImageName!="",MD.ImageName,"") as ThreadImageName',false);
		$this->db->select("MR.InboxStatus,MR.InboxUpdated,MR.InboxNewMessageCount");
		$this->db->select("M.MessageGUID, M.Body, M.Subject, M.AttachmentCount");
		$this->db->from(N_MESSAGE_THREAD.' AS MT');
		$this->db->join(N_MESSAGE_RECIPIENT. ' AS MR','MR.ThreadID=MT.ThreadID','RIGHT');
		$this->db->join(MEDIA.' MD','MD.MediaID=MT.MediaID','LEFT');
		$this->db->join(N_MESSAGES. ' AS M','M.MessageID=MR.InboxMessageID','LEFT');
		$this->db->where("MR.UserID",$user_id);
		if($ThreadID){
			$this->db->where('MT.ThreadID',$ThreadID);
		}
		if($Filter){
			if($Filter == 'ARCHIVED'){
				$this->db->where("MR.InboxStatus",'ARCHIVED');
			} else if($Filter == 'UN_READ'){
				$this->db->where("MR.InboxNewMessageCount>0",NULL,FALSE);
				$this->db->where_not_in("MR.InboxStatus",array('ARCHIVED','DELETED'));

			}
		} else {
			$this->db->where_not_in("MR.InboxStatus",array('ARCHIVED','DELETED'));
		}
		$this->db->order_by('MT.ThreadID','DESC');
        if($module_entity_id)
        {
    		$this->db->where('MT.ModuleID',$module_id);
    		$this->db->where('MT.ModuleEntityID',$module_entity_id);
        }

        if($search_keyword)
        {
        	$this->db->where("(MT.ThreadID IN (SELECT SMT.ThreadID FROM ".N_MESSAGE_RECIPIENT." SMT JOIN ".USERS." SU ON SU.UserID=SMT.UserID WHERE SMT.ThreadID=MT.ThreadID AND (SU.FirstName LIKE '%".$search_keyword."%' OR SU.LastName LIKE '%".$search_keyword."%' OR CONCAT(SU.FirstName,' ',SU.LastName) LIKE '%".$search_keyword."%')))",NULL,FALSE);
        }

		if($NumRows)
		{
			$query = $this->db->get();
			return $query->num_rows();	
		} else {
			//pagination logic
			$page_no=isset($Data['PageNo']) && is_numeric($Data['PageNo'])? $Data['PageNo'] : 1;
			$page_size=isset($Data['PageSize']) && is_numeric($Data['PageSize']) ? $Data['PageSize'] : NULL;
			if(!is_null($page_size)) // Check for pagination
			{
				$offset = ($page_no-1)*$page_size;	
				$this->db->limit($page_size,$offset);		
			}
			$query = $this->db->get();
		}
		$array = array();
		if($query->num_rows()){
			foreach($query->result_array() as $arr){
				$arr['ThreadSubject'] = $this->get_thread_subject($arr['ThreadSubject'],$this->get_recipients($arr['ThreadGUID']));
				$array[] = $arr;
			}
		}
		if($ThreadID && isset($array[0])){
			return $array[0];
		}
		return $array;

	}

	function check_thread($user_id,$ThreadGUID)
	{
		$this->db->select('MT.ThreadID');
		$this->db->from(N_MESSAGE_THREAD.' MT');
		$this->db->join(N_MESSAGE_RECIPIENT.' MR','MR.ThreadID=MT.ThreadID');
		$this->db->where('MR.UserID',$user_id);
		$query = $this->db->get();
		if($query->num_rows())
		{
			return true;
		} else {
			return false;
		}
	}

	function get_thread_details($ThreadGUID,$Recipients=array())
	{
		$this->db->select('MT.ThreadGUID,MT.Subject as ThreadSubject,MT.Replyable',false);
		$this->db->select('IF(MD.ImageName!="",MD.ImageName,"") as ThreadImageName',false);
		$this->db->from(N_MESSAGE_THREAD.' MT');
		$this->db->join(MEDIA.' MD','MD.MediaID=MT.MediaID','LEFT');
		$this->db->where('MT.ThreadGUID',$ThreadGUID);
		$query = $this->db->get();
		if($query->num_rows())
		{
			$data = $query->row_array();
			if(!empty($Recipients)){
				$data['ThreadSubject'] = $this->get_thread_subject($data['ThreadSubject'],$Recipients);
			}
			return $data;
		} else {
			return array();
		}
	}

	function thread_message_list($user_id, $Data, $NumRows=FALSE, $MessageID='')
	{
		
		$this->db->select("MT.Subject");
		$this->db->select("M.MessageGUID, M.Body, M.CreatedDate,M.MessageID,M.Type");
		$this->db->select("U.UserGUID, U.FirstName, U.LastName, U.ProfilePicture");
		$this->db->select("P.Url as ProfileURL");
		$this->db->select("(CASE WHEN U.Gender=0 THEN '' WHEN U.Gender=1 THEN 'Male' WHEN U.Gender=2 THEN 'Female' ELSE 'Others' END) AS Gender",false);
		$this->db->from(N_MESSAGES. ' AS M');
		$this->db->join(N_MESSAGE_THREAD.' AS MT','MT.ThreadID=M.ThreadID');
		$this->db->join(N_MESSAGE_RECIPIENT. ' AS MR','MR.ThreadID=MT.ThreadID','LEFT');
		$this->db->join(USERS. ' AS U','U.UserID=M.UserID','LEFT');
		$this->db->join(PROFILEURL.' P','P.EntityID=U.UserID','LEFT');
		$this->db->where('P.EntityType','User');
		$this->db->where("M.MessageID NOT IN (SELECT MessageID FROM ".N_MESSAGE_DELETED." WHERE UserID='".$user_id."')",NULL,FALSE);
		$this->db->where("MT.ThreadGUID",$Data['ThreadGUID']);
		$this->db->where("MR.UserID",$user_id);
		$this->db->where("U.StatusID",2);
		
		if($MessageID){
			$this->db->where('M.MessageID',$MessageID);
		}
		
		$this->db->order_by('M.MessageID','DESC');	


		if($NumRows)
		{
			$query = $this->db->get();
			return $query->num_rows();	
		}else{
			//pagination logic
			$page_no=isset($Data['PageNo']) && is_numeric($Data['PageNo'])? $Data['PageNo'] : 1;
			$page_size=isset($Data['PageSize']) && is_numeric($Data['PageSize']) ? $Data['PageSize'] : NULL;
			if(!is_null($page_size)) // Check for pagination
			{
				$offset = ($page_no-1)*$page_size;	
				$this->db->limit($page_size,$offset);		
			}
			$query = $this->db->get();
		}
		$array = array();
		foreach($query->result_array() as $arr){
			$arr['Media'] = $this->get_message_media($arr['MessageID']);
			unset($arr['MessageID']);
			$d = $this->get_message_action($arr['UserGUID'],$arr['Type'],$arr['Body']);
			$arr['ActionCreator'] = $d['ActionCreator'];
			$arr['ActionValue'] = $d['ActionValue'];
			$arr['ActionName'] = $d['ActionName'];
			$array[] = $arr;
		}
		if($MessageID && isset($array[0])){
			return $array[0];
		}
		return array_reverse($array);

	}

	function get_user_detail($UserGUID){
		$this->db->select('U.FirstName,U.LastName,U.UserGUID,P.Url as ProfileURL');
		$this->db->from(USERS.' U');
		$this->db->join(PROFILEURL.' P','P.EntityID=U.UserID','left');
		$this->db->where('UserGUID',$UserGUID);
		$query = $this->db->get();
		if($query->num_rows()){
			return $query->row_array();
		} else {
			return array();
		}
	}

	function get_message_action($UserGUID,$type,$Body){
		$data = array('ActionCreator'=>'','ActionValue'=>'','ActionName'=>'');
		if($type == 'AUTO'){
			$json = json_decode($Body);
			$data['ActionCreator'] = $this->get_user_detail($UserGUID);
			$data['ActionName'] = $json->Action;
			
			switch ($json->Action) {
				case 'ADDED':
					$data['ActionValue'] = array();
					if($json->Value){
						foreach ($json->Value as $val) {
							$data['ActionValue'][] = $this->get_user_detail($val->UserGUID);
						}
					}
				break;

				case 'REMOVED':
					$data['ActionValue'] = array();
					if($json->Value){
						foreach ($json->Value as $val) {
							$data['ActionValue'][] = $this->get_user_detail($val->UserGUID);
						}
					}
				break;

				case 'CONVERSATION_NAME':
					$data['ActionValue'] = $json->Value;
				break;

				case 'CONVERSATION_IMAGE':
					$data['ActionValue'] = get_detail_by_id($json->Value,21,'ImageName',1);
				break;

				case 'THREAD_CREATED':
					$data['ActionValue'] = date('d M Y',strtotime($json->Date));
				break;
			}

		}
		return $data;
	}

	function get_message_media($MessageID){
		$this->db->select('M.MediaGUID,M.ImageName,M.Caption');
		$this->db->select('MT.Name as MediaType');
		$this->db->from(MEDIA.' M');
		$this->db->join(MEDIAEXTENSIONS.' ME','M.MediaExtensionID=ME.MediaExtensionID','LEFT');
		$this->db->join(MEDIATYPES.' MT','ME.MediaTypeID=MT.MediaTypeID','LEFT');
		$this->db->where('M.MediaSectionID','7');
		$this->db->where('M.MediaSectionReferenceID',$MessageID);
		$query = $this->db->get();
		if($query->num_rows()){
			return $query->result_array();
		} else {
			return array();
		}
	}
}
