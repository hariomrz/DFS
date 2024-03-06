<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of communication_model
 *
 * @author nitins
 */
class Communication_model extends Admin_Common_Model {

    public function getCommunications($userId, $start_offset = 0, $end_offset = "", $sort_by = "", $order_by = "") {
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date = dateformat_php_to_mysql($global_settings['date_format']);
        $time_format =  dateformat_php_to_mysql($global_settings['time_format']);

        $this->db->select('C.CommunicationID AS communication_id', FALSE);
        $this->db->select('C.UserID AS user_id', FALSE);
        $this->db->select('C.EmailTypeID AS email_type_id', FALSE);
        $this->db->select('C.Subject AS subject', FALSE);
        $this->db->select('C.Body AS body', FALSE);
        $this->db->select('C.CommunicationID AS communication_id', FALSE);
        $this->db->select('C.StatusID as status_id', FALSE);
        $this->db->select('DATE_FORMAT(CreatedDate, "'.$mysql_date.' '.$time_format.'") AS created_date', FALSE);
        $this->db->select('E.name AS email_type', FALSE);
        $this->db->select('S.StatusName AS status_text', FALSE);

        $this->db->join(EMAILTYPES." AS E", ' E.EmailTypeID = C.EmailTypeID','left');
        $this->db->join(STATUS." AS S", ' S.StatusID = C.StatusID','left');
        $this->db->from(COMMUNICATIONS . " AS C");
        $this->db->where('UserID', $userId);
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        $this->db->order_by("CreatedDate", "DESC");
        
        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset,$start_offset);
        
        $query = $this->db->get();
        $results['results'] = $query->result_array();
        return $results;
    }


    /**
     * Function for save communication information
     * Parameters : $user_id, $email, $subject, $message
     * Return : True
     */
    public function saveCommunication($user_id, $email, $subject, $message)
    {

        /* First save communication data into table, after this send_email */
        $communication_data = array(
            'UserID' => $user_id,
            'EmailTypeID' => COMMUNICATION_EMAIL_TYPE_ID,
            'EmailTo' => $email,
            'Subject' => $subject,
            'Body' => $message,
            'CreatedDate' => date("Y-m-d H:i:s"),
            'ProcessDate' => date("Y-m-d H:i:s"),
            'StatusID' => '1',
            'StatusMessage' => 'Communication'
         );
         $this->db->insert(COMMUNICATIONS, $communication_data);
         return true;
    }
    
    
    public function getCommunicationEmails($start_offset = 0, $end_offset = "", $email_type = "", $start_date = "", $end_date = "", $sort_by = "", $order_by = "") {
        
        $this->db->select('C.CommunicationID AS communication_id', FALSE);
        $this->db->select('C.UserID AS user_id', FALSE);
        $this->db->select('C.EmailTypeID AS email_type_id', FALSE);
        $this->db->select('C.Subject AS subject', FALSE);
        $this->db->select('C.Body AS body', FALSE);
        $this->db->select('C.CommunicationID AS communication_id', FALSE);
        $this->db->select('C.StatusID as status_id', FALSE);
        //$this->db->select('DATE_FORMAT(C.CreatedDate, "'.$mysql_date.'") AS created_date', FALSE);
        $this->db->select('C.CreatedDate AS created_date', FALSE);
        $this->db->select('E.name AS email_type', FALSE);
        $this->db->select('S.StatusName AS status_text', FALSE);
        
        
        if($email_type == 4){
            $this->db->select('BI.Email AS email', FALSE);
            $this->db->select('BI.Name AS username', FALSE);
            $this->db->select('BI.StatusID AS userstatusid', FALSE);
            $this->db->join(BETAINVITES." AS BI", ' BI.Email = C.EmailTo','inner');
        }else{
            $this->db->select('U.Email AS email', FALSE);
            $this->db->select('CONCAT(U.FirstName, " ", U.LastName) AS username', FALSE);
            $this->db->select('U.ProfilePicture AS profilepicture', FALSE);
            $this->db->select('U.UserGUID AS userguid', FALSE);
            $this->db->select('U.StatusID AS userstatusid', FALSE);
            $this->db->join(USERS." AS U", ' U.UserID = C.UserID','inner');
        }
        
        $this->db->join(EMAILTYPES." AS E", ' E.EmailTypeID = C.EmailTypeID','left');
        $this->db->join(STATUS." AS S", ' S.StatusID = C.StatusID','left');        
        $this->db->from(COMMUNICATIONS . " AS C");
        
        if(isset($email_type) && $email_type != ""){
            $this->db->where('C.EmailTypeID', $email_type);
        }
        
        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(C.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'"', NULL, FALSE);
        }
        
        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        /* Sort_by, Order_by */
        if($sort_by == '' )
           $sort_by='CommunicationID';
            
        if($order_by == false || $order_by == '' )
            $order_by='ASC';

        if($order_by == 'true')
            $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);
        
        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset,$start_offset);
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results['results'] = $query->result_array();
        return $results;
    }
    
    /**
     * Function for get communication details by id
     * Parameters : $communication_id
     * Return : array
     */
    public function getCommunicationDetailById($communication_id)
    {
        $this->db->select("*");
        $this->db->from(COMMUNICATIONS);
        $this->db->where('CommunicationID',$communication_id);
        $query = $this->db->get();
        return $query->row_array();
    }
    
    /**
     * Function for get not sending email communication list
     * Parameters : 
     * Return : array
     */
    public function getNotSendingEmailCommunicationList() {        
        $this->db->select('C.*', FALSE);        
        $this->db->from(COMMUNICATIONS . " AS C");
        $this->db->where("C.StatusID",1);       
        $this->db->limit(30);
        $this->db->order_by("C.CommunicationID ASC");
        $query = $this->db->get();        
        $communications = $query->result_array();

        $all_communication_id = array();
        foreach($communications as $communication) {
            $all_communication_id[] = $communication['CommunicationID'];
        }
        //echo $this->db->last_query();
        //print_r($all_communication_id);
        //die;
        if(!empty($all_communication_id)){
            $this->db->where_in('CommunicationID',$all_communication_id);
            $this->db->update(COMMUNICATIONS, array('StatusID' => 3)); //for processing
        }        
        return $communications;
    }
    
    /**
     * Function for change status of a communication after sending email
     * Parameters : Communication
     * Return : true
     */
    public function updateCommunicationStatus($Communication)
    {
        $this->db->insert_on_duplicate_update_batch(COMMUNICATIONS,$Communication);
        return true;
    }


    public function send_multiple_communication($data) {
        if(isset($data['user_list'])) $users = $data['user_list']; else $users = '';
        if(isset($data['subject'])) $subject = $data['subject']; else $subject = 'Communication Mail';
        if(isset($data['message'])) $message = $data['message']; else $message = '';
        if(isset($data['crm_query'])) $crm_query = $data['crm_query']; else $crm_query = '';
        $this->load->model(array('admin/users_model'));

        if($crm_query) {

            $query = $this->db->query($crm_query);
            //$query = $this->db->get();
            $users = $query->result_array();  //echo $this->db->last_query(); echo 
            foreach ($users as $user) {
               $user_id = $user['UserID'];
               if($user_id){
                    $userData = $this->users_model->getValueById(array('Email','FirstName','LastName'),$user_id);

                    $emailDataArr = array();
                    $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
                    $emailDataArr['IsResend'] = 0;
                    $emailDataArr['Subject'] = $subject;
                    $emailDataArr['TemplateName'] = "emailer/send_communication";
                    $emailDataArr['Email'] = $userData['Email'];
                    $emailDataArr['EmailTypeID'] = COMMUNICATION_EMAIL_TYPE_ID;
                    $emailDataArr['UserID'] = $user_id;
                    $emailDataArr['StatusMessage'] = "Communication";        
                    $emailDataArr['Data'] = array("FirstLastName" => stripslashes($userData['FirstName'].' '.$userData['LastName']),"MainContent" => $message,"VCA_Info_Email" => VCA_INFO_EMAIL);

                    $result = sendEmailAndSave($emailDataArr, 1);
                }
            }
        } else {            
            foreach($users as $user_id){
                if($user_id){
                    $userData = $this->users_model->getValueById(array('Email','FirstName','LastName'),$user_id);

                    $emailDataArr = array();
                    $emailDataArr['IsSave'] = EMAIL_ANALYTICS;//If you want to send email only not save in DB then set 1 otherwise set 0
                    $emailDataArr['IsResend'] = 0;
                    $emailDataArr['Subject'] = $subject;
                    $emailDataArr['TemplateName'] = "emailer/send_communication";
                    $emailDataArr['Email'] = $userData['Email'];
                    $emailDataArr['EmailTypeID'] = COMMUNICATION_EMAIL_TYPE_ID;
                    $emailDataArr['UserID'] = $user_id;
                    $emailDataArr['StatusMessage'] = "Communication";        
                    $emailDataArr['Data'] = array("FirstLastName" => stripslashes($userData['FirstName'].' '.$userData['LastName']),"MainContent" => $message,"VCA_Info_Email" => VCA_INFO_EMAIL);

                    $result = sendEmailAndSave($emailDataArr, 1);
                }
            }
        }

    }  
}