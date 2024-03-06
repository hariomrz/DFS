<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Ipsetting_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * Function for get ip list
     * Parameters : start_offset, end_offset, sort_by, order_by
     * Return : ip array
     */
    public function getIpsList($start_offset = 0, $end_offset = "", $sort_by = "", $order_by = "", $IpFor = 1) {

        $this->db->select('A.AllowedIpID AS allowedipid	', FALSE);
        $this->db->select('A.IP AS ip', FALSE);
        $this->db->select('A.IsForAdmin AS isforadmin', FALSE);
        $this->db->select('A.Description AS description', FALSE);
        $this->db->select('A.StatusID AS statusid', FALSE);
        $this->db->select('(CASE A.StatusID WHEN 1 THEN "Inactive" WHEN 2 THEN "Active" ELSE "Pending" END) AS status', FALSE);
        $this->db->select('A.IsDefault AS isdefault', FALSE);
        
        $this->db->from(ALLOWEDIPS . " as A ");
        $this->db->where('A.StatusID != ',3);
        $this->db->where('A.IsForAdmin',$IpFor);

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        /* Sort_by, Order_by */
        if ($sort_by == '')
            $sort_by = 'IP';

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
     * Function for check ip adress exist or not
     * @param string $IpAdress
     * @param integer $IpFor
     * @param integer $AllowedIpID
     * @return string
     */
    function checkIpAdressExist($IpAdress,$IpFor,$AllowedIpID){
        
        $this->db->select('*');
        $this->db->from(ALLOWEDIPS);
        $this->db->where('IP',$IpAdress);
        $this->db->where('IsForAdmin',$IpFor);
        $this->db->where('StatusID != ',3);
                
        $query = $this->db->get();
        $dataArr = $query->row_array();
        if ($query->num_rows()) {
            if($dataArr['AllowedIpID'] == $AllowedIpID){
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
     * Function for get IP details by IP Address
     * @param integer $AllowedIpID
     * @return array
     */
    function getIpDetailByIpAddress($AllowedIpID){
        
        $this->db->select('*');
        $this->db->from(ALLOWEDIPS);
        $this->db->where('AllowedIpID',$AllowedIpID);
                
        $query = $this->db->get();
        return $query->row_array();
    }
    
    /**
     * Function for get admin active ips count
     * @param void
     * @return integer
     */
    function getAdminActiveIpsCount(){
        
        $this->db->select('*');
        $this->db->from(ALLOWEDIPS);
        $this->db->where('IsForAdmin',1);
        $this->db->where('StatusID',2);
                
        $query = $this->db->get();
        return $query->num_rows();
    }
        
    /**
     * Function for create ip address details
     * @param array $dataArr
     * @return integer
     */
    function addAllowedIpAddress($dataArr){
        
        $this->db->insert(ALLOWEDIPS, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    } 
    
    /**
     * Function for update allowed ip details
     * @param array $dataArr
     * @param integer $AllowedIpID
     * @return integer
     */
    function updateAllowedIpAddress($dataArr,$AllowedIpID){
        
        $this->db->where('AllowedIpID', $AllowedIpID);
        $this->db->update(ALLOWEDIPS, $dataArr);
        return $this->db->affected_rows();        
    }
    
    /**
     * Update ip(s) detail
     * @param type $data
     * @param type $key
     */
    function updateIpInfo($data, $key) {
        $this->db->update_batch(ALLOWEDIPS, $data, $key);
    }
    
}

//End of file ipsetting_model.php
