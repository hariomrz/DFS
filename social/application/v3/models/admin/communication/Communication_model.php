<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Communication_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Function for add communication 
     * Parameters : Data array
     */
    public function add_communication($data, $count=1) {
        if(!empty($data['ActivityID']) || !empty($data['QuizID'])) {
            $this->db->select('AdminCommunicationID, Type');
            $this->db->from(ADMINCOMMUNICATION);
            //$this->db->where('Type', $data['Type']);
           // $this->db->where('Source', $data['Source']);
           if(!empty($data['ActivityID'])) {
                $this->db->where('ActivityID', $data['ActivityID']);
           } else {
                $this->db->where('QuizID', $data['QuizID']);
           }
            
            $this->db->limit(1);
            $query = $this->db->get();
            if($query->num_rows() > 0) {
                $row = $query->row_array();
                $communication_id = $row['AdminCommunicationID'];
                $set_field = 'PushNotificationCount';
                if($data['Type'] == 2) {
                    $set_field = 'SmsCount';
                }                
                if(isset($data['IsReady'])) {
                    $this->db->set('IsReady', $data['IsReady']);
                }
                if(isset($data['IsActivityDashboard'])) {
                    $this->db->set('IsActivityDashboard', $data['IsActivityDashboard']);
                } 
                    
                $this->db->set($set_field, "$set_field+($count)", FALSE);
                               
                $this->db->set('ModifiedDate', $data['ModifiedDate']);
                $this->db->where('AdminCommunicationID', $communication_id);
                $this->db->update(ADMINCOMMUNICATION);
            } else {
               /* if(isset($data['IsActivityDashboard'])) {
                } else { */
                    if($data['Type'] == 2) {
                        $data['SmsCount'] = $count;
                    }
                    if($data['Type'] == 1) {
                        $data['PushNotificationCount'] = $count;
                    }
               // }                
                $this->db->insert(ADMINCOMMUNICATION, $data);
                $communication_id = $this->db->insert_id(); 
            }
        } else {
            $this->db->insert(ADMINCOMMUNICATION, $data);
            $communication_id = $this->db->insert_id();
        }
        return $communication_id;
    }  
    
    /**
     * Function for add communication history
     * Parameters : Data array
     */
    public function add_histrory($data) {
       /* if(!empty($data['AdminCommunicationID'])) {
            $this->db->select('AdminCommunicationHistoryID');
            $this->db->from(ADMINCOMMUNICATIONHISTORY);
            $this->db->where('CreatedDate', $data['CreatedDate']);
            $this->db->where('UserID', $data['UserID']);
            $this->db->where('DeviceTypeID', $data['DeviceTypeID']);
            $this->db->where('AdminCommunicationID', $data['AdminCommunicationID']);
            $this->db->limit(1);
            $query = $this->db->get();
            if($query->num_rows() == 0) {
                $this->db->insert(ADMINCOMMUNICATIONHISTORY, $data);
                return $this->db->insert_id();
            }
            return 0;
        } else { */
            $this->db->insert(ADMINCOMMUNICATIONHISTORY, $data);
            return $this->db->insert_id();
        //}
    }

    /**
     * Function for get communication history
     * Parameters : Data array
     */
    public function get_history($data, $count_only=false) {
        $page_no        = safe_array_key($data, 'PageNo', PAGE_NO);
        $page_size      = safe_array_key($data, 'PageSize', PAGE_SIZE);
        $user_id        = $data['UserID'];

        $this->db->select('ACH.Content, ACH.Source, ACH.CreatedDate, AC.Type');
        $this->db->from(ADMINCOMMUNICATIONHISTORY . ' ACH');
        $this->db->join(ADMINCOMMUNICATION . ' AC', 'AC.AdminCommunicationID=ACH.AdminCommunicationID');        
        $this->db->where('ACH.UserID', $user_id);
        $this->db->order_by("ACH.CreatedDate DESC");
        if (empty($count_only)) {
            $this->db->limit($page_size, get_pagination_offset($page_no, $page_size));
        }      
       
        $sql = $this->db->get();
        //echo $this->db->last_query();die;
        if ($count_only) {
            return $sql->num_rows();
        }
        return $sql->result_array();
    }

}
