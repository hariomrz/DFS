<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mandrill_model extends Admin_Common_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Function for get mandrill event details by event name
     * Parameters : $Keyname
     * Return : array
     */
    public function getEventByKeyName($Keyname){
        
        $this->db->select('*');
        $this->db->from(MANDRILLEVENTS);
        $this->db->where('Keyname',$Keyname);
        $query = $this->db->get();
        return $query->row_array();
        
    }
    
    /**
     * Function for get mandrill tags details by tag name
     * Parameters : $Tagname
     * Return : array
     */
    public function getTagDetailByName($Tagname){
        
        $this->db->select('*');
        $this->db->from(MANDRILLTAGS);
        $this->db->where('Name',$Tagname);
        $query = $this->db->get();
        return $query->row_array();
        
    }
    
    /**
     * Function for get mandrill message by email key
     * Parameters : $EmailKey
     * Return : array
     */
    public function getMessageByEmailKey($EmailKey){
        
        $this->db->select('*');
        $this->db->from(MANDRILLMESSAGES);
        $this->db->where('EmailKey',$EmailKey);
        $this->db->order_by("MessageID DESC");
        $query = $this->db->get();
        return $query->row_array();        
    }
    
    /**
     * Function for check message tag exist or not
     * @param integer $MessageID
     * @param integer $TagID
     * @return string
     */
    function checkMessageTagExist($MessageID,$TagID){
        
        $this->db->select('*');
        $this->db->from(MESSAGETAGS);
        $this->db->where('MessageID',$MessageID);
        $this->db->where('TagID',$TagID);
                
        $query = $this->db->get();
        if ($query->num_rows()) {
            $return = 'exist';
        } else {
            $return = 'notexist';
        }
        
        return $return;
    }
    
    /**
     * Function for check message event exist or not
     * @param array $whereArr
     * @return string
     */
    function checkMessageEventExist($whereArr){
        
        $query = $this->db->get_where(MESSAGEEVENTS, $whereArr);
        if ($query->num_rows()) {
            $return = 'exist';
        } else {
            $return = 'notexist';
        }
        
        return $return;
    }
    
    /**
     * Function for save mandrill API response
     * Parameters : Data array
     * Return : inteher
     */
    public function saveMandrillResponseData($Data) {
        //echo "<pre>";print_r($Data);die;
        $msgdata = $Data['msg'];
        $create_date = date('Y-m-d H:i:s');
        
        if(!is_array($msgdata))
            return false;
        
        $EmailKey = $msgdata['_id'];
        $open_count = count($msgdata['opens']);
        $click_count = count($msgdata['clicks']);
        $click_last_element = end($msgdata['clicks']);
        
        if($EmailKey == "")
            return false;
        
        $messageData = $this->getMessageByEmailKey($EmailKey);
        if(isset($messageData['MessageID']) && $messageData['MessageID'] != ""){
            $MessageID = $messageData['MessageID'];
            
            $messageArr = array();
            $messageArr['Opens'] = $open_count;
            $messageArr['Clicks'] = $click_count;
            $messageArr['State'] = $msgdata['state'];
            $this->db->where('MessageID', $MessageID);
            $this->db->update(MANDRILLMESSAGES, $messageArr);
        }else{
            $messageArr = array();
            $messageArr['EmailKey'] = $EmailKey;
            $messageArr['TS'] = $msgdata['ts'];
            $messageArr['Sender'] = $msgdata['sender'];
            $messageArr['Template'] = $msgdata['template'];
            $messageArr['Subject'] = $msgdata['subject'];
            $messageArr['EmailTypeID'] = isset($msgdata['metadata']['EmailTypeID']) ? $msgdata['metadata']['EmailTypeID'] : 0 ;
            $messageArr['Email'] = $msgdata['email'];
            $messageArr['Opens'] = $open_count;
            $messageArr['Clicks'] = $click_count;
            $messageArr['State'] = $msgdata['state'];
            $messageArr['CreatedDate'] = $create_date;
            
            $this->db->insert(MANDRILLMESSAGES,$messageArr);
            $MessageID = $this->db->insert_id();
        }
        
        if (is_numeric($MessageID) && $MessageID != "") {
            if(isset($Data['location']['city']))$city = $Data['location']['city'];else $city = '';
            if(isset($Data['location']['region']))$region = $Data['location']['region'];else $region = '';
            if(isset($Data['location']['country']))$country = $Data['location']['country'];else $country = '';
            if(isset($Data['location']['postal_code']))$postal_code = $Data['location']['postal_code'];else $postal_code = '';
            $Location = trim($city.' '.$region.', '.$country.' '.$postal_code);
            
            $EventArr = $this->getEventByKeyName($Data['event']);
            
            if(isset($EventArr['EventID']) && $EventArr['EventID'])
                $EventID = $EventArr['EventID'];
            else 
                $EventID = 9;  
            
            if(isset($msgdata['reject']))
                $RejectReason = $msgdata['reject'];
            else 
                $RejectReason = '';
            
            $smtp_events_type = '';
            $smtp_events_diag = '';
            $smtp_events_source_ip = '';
            $smtp_events_destination_ip = '';
            $ipAddress = '';
            $smtp_events_size = 0;
            $userAgent = '';
            $Url = '';
            
            if(isset($msgdata['smtp_events'][0]['type']))
                $smtp_events_type = $msgdata['smtp_events'][0]['type'];
            
            if(isset($msgdata['smtp_events'][0]['diag']))
                $smtp_events_diag = $msgdata['smtp_events'][0]['diag'];
            
            if(isset($msgdata['smtp_events'][0]['source_ip']))
                $smtp_events_source_ip = $msgdata['smtp_events'][0]['source_ip'];
            
            if(isset($msgdata['smtp_events'][0]['destination_ip']))
                $smtp_events_destination_ip = $msgdata['smtp_events'][0]['destination_ip'];
            
            if(isset($msgdata['smtp_events'][0]['size']))
                $smtp_events_size = $msgdata['smtp_events'][0]['size'];
            
            if(isset($Data['ip']))
                $ipAddress = $Data['ip'];
            
            if(isset($Data['user_agent']))
                $userAgent = $Data['user_agent'];
            
            if(isset($click_last_element['url']))
                $Url = $click_last_element['url'];
            
            //For Message Events
            $messageEventArr = array();
            $messageEventArr['MessageID'] = $MessageID;
            $messageEventArr['EventID'] = $EventID;
            $messageEventArr['TS'] = $Data['ts'];
            $messageEventArr['IP'] = $ipAddress;
            $messageEventArr['Location'] = $Location;
            $messageEventArr['Ua'] = $userAgent;
            $messageEventArr['Url'] = $Url;
            $messageEventArr['Type'] = $smtp_events_type;
            $messageEventArr['Diag'] = $smtp_events_diag;
            $messageEventArr['SourceIP'] = $smtp_events_source_ip;
            $messageEventArr['DestinationIP'] = $smtp_events_destination_ip;
            $messageEventArr['Size'] = $smtp_events_size;
            $messageEventArr['RejectReason'] = $RejectReason;
            $messageEventArr['LastEventAt'] = date("Y-m-d",$Data['ts']);
            $messageEventArr['CreatedDate'] = $create_date;
            
            if ($EventID == 1 && $this->checkMessageEventExist(array('MessageID' => $MessageID, 'TS' => $Data['ts'], 'IP' => $ipAddress)) == 'notexist') {
                $this->db->insert(MESSAGEEVENTS,$messageEventArr);
            }else if ($EventID == 2 && $this->checkMessageEventExist(array('MessageID' => $MessageID, 'TS' => $Data['ts'], 'IP' => $ipAddress, 'Url' => $Url)) == 'notexist') {
                $this->db->insert(MESSAGEEVENTS,$messageEventArr);
            }else if ($EventID == 3 && $this->checkMessageEventExist(array('MessageID' => $MessageID, 'TS' => $Data['ts'], 'SourceIP' => $smtp_events_source_ip, 'DestinationIP' => $smtp_events_destination_ip, 'Diag' => $smtp_events_diag)) == 'notexist') {
                $this->db->insert(MESSAGEEVENTS,$messageEventArr);
            }else if ($EventID == 4 && $this->checkMessageEventExist(array('MessageID' => $MessageID)) == 'notexist') {
                $this->db->insert(MESSAGEEVENTS,$messageEventArr);
            }else if ($EventID == 5 || $EventID == 6 || $EventID == 7 || $EventID == 8 || $EventID == 9) {
                $this->db->insert(MESSAGEEVENTS,$messageEventArr);
            }           
            
            //For Message Tag
            if(is_array($msgdata['tags'])){
                foreach($msgdata['tags'] as $tag){
                    $tagInfo = $this->getTagDetailByName($tag);
                    if ($this->checkMessageTagExist($MessageID,$tagInfo['TagID']) == 'notexist') {
                        $tagArr = array();
                        $tagArr['MessageID'] = $MessageID;
                        $tagArr['TagID'] = $tagInfo['TagID'];
                        $tagArr['CreatedDate'] = $create_date;

                        $this->db->insert(MESSAGETAGS,$tagArr);
                    }
                }
            }
            
            return true;//$MessageID;
            
        }else{
            return false;
        }        
    }
        
        
}//End of file users_model.php
