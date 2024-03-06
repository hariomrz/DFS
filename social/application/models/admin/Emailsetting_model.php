<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Emailsetting_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Function for get smtp settings list
     * Parameters : start_offset, end_offset, start_date, end_date, sort_by, order_by
     * Return : smtp setting array
     */
    public function getSmtpSettings($start_offset = 0, $end_offset = "", $sort_by = "", $order_by = "") {

        $this->db->select('E.EmailSettingID AS emailsettingid', FALSE);
        $this->db->select('E.Name AS name', FALSE);
        $this->db->select('(CASE E.IsUseLocalSMTP WHEN 1 THEN "True" ELSE "False" END) AS islocalsmtp', FALSE);
        $this->db->select('E.FromEmail AS fromemail', FALSE);
        $this->db->select('E.FromName AS fromname', FALSE);
        $this->db->select('E.ServerName AS servername', FALSE);
        $this->db->select('E.SPortNo AS sportno', FALSE);
        $this->db->select('E.UserName AS username', FALSE);
        $this->db->select('E.Password AS password', FALSE);
        $this->db->select('E.IsSSLRequire AS issslrequire', FALSE);
        $this->db->select('E.ReplyTo AS replyto', FALSE);
        $this->db->select('E.StatusID AS statusid', FALSE);
        $this->db->select('(CASE E.StatusID WHEN 1 THEN "Inactive" WHEN 2 THEN "Active" ELSE "Pending" END) AS status', FALSE);
        $this->db->select('E.IsDefault AS isdefault', FALSE);

        $this->db->from(EMAILSETTINGS . "  E ");
        $this->db->where('StatusID != ',3);

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        /* Sort_by, Order_by */
        if ($sort_by == 'name' || $sort_by == '')
            $sort_by = 'Name';

        if ($order_by == false || $order_by == '')
            $order_by = 'ASC';

        if ($order_by == 'true')
            $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);


        $query = $this->db->get();
        //echo $this->db->last_query();
        $results['results'] = $query->result_array();
        return $results;
    }
    
    /**
     * Function for create smtp setting
     * @param array $dataArr
     * @return integer
     */
    function createSmtpSetting($dataArr){
        
        $this->db->insert(EMAILSETTINGS, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    } 
    
    /**
     * Function for update smtp setting details
     * @param array $dataArr
     * @param integer $emailSettingId
     * @return integer
     */
    function updateSmtpSetting($dataArr,$emailSettingId){
        
        $this->db->where('EmailSettingID', $emailSettingId);
        $this->db->update(EMAILSETTINGS, $dataArr);
        return $this->db->affected_rows();        
    }
    
    /**
     * Function for check smtp email existi or not
     * @param string $fromEmail
     * @param integer $emailSettingId
     * @return integer
     */
    function checkSmtpEmailExist($fromEmail,$emailSettingId){
        
        $this->db->select('*');
        $this->db->from(EMAILSETTINGS);
        $this->db->where('FromEmail',$fromEmail);
                
        $query = $this->db->get();
        $dataArr = $query->row_array();
        if ($query->num_rows()) {
            if($dataArr['EmailSettingID'] == $emailSettingId){
                $return = 'notexist';
            }else{
                $return = 'exist';
            }
        } else {
            $return = 'notexist';
        }
        
        return $return;
    } 
    
    /**
     * Function for get email setting details by id
     * Parameters : $EmailSettingId
     * Return : array
     */
    public function getEmailSettingById($EmailSettingId)
    {
        $this->db->select('*');
        $this->db->from(EMAILSETTINGS);
        $this->db->where('EmailSettingID',$EmailSettingId);
        $query = $this->db->get();
        return $query->row_array();
    }
    
    /**
     * Update multiple settings detail
     * @param type $data
     * @param type $key
     */
    function updateMultipleSmtpSettingInfo($data, $key) {
        $this->db->update_batch(EMAILSETTINGS, $data, $key);
    }
    
    
    /**
     * Function for get smtp email type list
     * Parameters : start_offset, end_offset, start_date, end_date, sort_by, order_by
     * Return : smtp setting array
     */
    public function getSmtpEmailsType($start_offset = 0, $end_offset = "", $sort_by = "", $order_by = "") {

        $this->db->select('ET.EmailTypeID AS emailtypeid', FALSE);
        $this->db->select('ET.EmailSettingID AS emailsettingid', FALSE);
        $this->db->select('ET.Name AS name', FALSE);
        $this->db->select('(CASE ET.Subject WHEN ET.Subject THEN ET.Subject ELSE "N/A" END) AS subject', FALSE);
        $this->db->select('ES.FromEmail AS fromemail', FALSE);
        $this->db->select('ET.StatusID AS statusid', FALSE);
        $this->db->select('(CASE ET.StatusID WHEN 1 THEN "Inactive" WHEN 2 THEN "Active" ELSE "Pending" END) AS status', FALSE);

        $this->db->join(EMAILSETTINGS . " AS ES", ' ES.EmailSettingID = ET.EmailSettingID', 'inner');
        $this->db->from(EMAILTYPES . "  ET ");
        $this->db->where('ET.StatusID != ',3);

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        /* Sort_by, Order_by */
        if ($sort_by == 'name' || $sort_by == '')
            $sort_by = 'Name';

        if ($order_by == false || $order_by == '')
            $order_by = 'ASC';

        if ($order_by == 'true')
            $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);


        $query = $this->db->get();
        //echo $this->db->last_query();
        $results['results'] = $query->result_array();
        return $results;
    }
    
    /**
     * Update multiple settings detail
     * @param type $data
     * @param type $key
     */
    function updateSmtpEmailTypeInfo($data, $key) {
        $this->db->update_batch(EMAILTYPES, $data, $key);
    }
    
    /**
     * Function for update smtp email type details
     * @param array $dataArr
     * @param integer $EmailTypeID
     * @return integer
     */
    function updateSmtpEmailTypaDetail($dataArr,$EmailTypeID){
        
        $this->db->where('EmailTypeID', $EmailTypeID);
        $this->db->update(EMAILTYPES, $dataArr);
        return $this->db->affected_rows();        
    }
    
    /**
     * Function to get default email settings data
     * Parameters : void
     * Return : array
     */
    function getEmailSettingParamData() {
        /* get email setting section */
        $this->db->select('*');
        $this->db->from(EMAILSETTINGS);
        $this->db->where('StatusID',2);
        $query = $this->db->get();
        
        $param['email_setting'] = $query->result_array();        
        
        return $param;
    }

}

//End of file emailsetting_model.php
