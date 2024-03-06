<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Configuration_model extends Admin_Common_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Function for get configuration settings list
     * Parameters : start_offset, end_offset, sort_by, order_by
     * Return : configuration setting array
     */
    public function getConfigurationSettings($start_offset = 0, $end_offset = "", $sort_by = "", $order_by = "") {

        $this->db->select('BU.BusinessUnitID AS BusinessUnitID', FALSE);
        $this->db->select('BUC.BUCID AS BUConfigID', FALSE);
        $this->db->select('BUC.Value AS ConfigValue', FALSE);
        $this->db->select('C.ConfigID AS ConfigID', FALSE);
        $this->db->select('C.Name AS ConfigurationName', FALSE);
        $this->db->select('C.Description AS Description', FALSE);
        $this->db->select('DT.DataTypeID AS DataTypeID', FALSE);
        $this->db->select('DT.Name AS DataTypeName', FALSE);

        
        $this->db->from(BUSINESSUNITS . "  BU ");
        $this->db->join(BUSINESSUNITCONFIGS." AS BUC", ' BUC.BusinessUnitID = BU.BusinessUnitID','inner');
        $this->db->join(CONFIGS." AS C", ' C.ConfigID = BUC.ConfigID','inner');
        $this->db->join(DATATYPES." AS DT", ' DT.DataTypeID = C.DataTypeID','inner');
        
        $this->db->where('C.IsActive',1);
        $this->db->where('BU.BusinessStatusId',2);

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $results['total_records'] = $tempdb->count_all_results();

        /* Sort_by, Order_by */
        if ($sort_by == 'config_name' || $sort_by == '')
            $sort_by = 'ConfigurationName';

        if ($order_by == false || $order_by == '')
            $order_by = 'ASC';

        if ($order_by == 'true')
            $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if (isset($start_offset) && $end_offset != '')
            $this->db->limit($end_offset, $start_offset);


        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results['results'] = $query->result_array();
        return $results;
    }
    
    /**
     * Function for update configuration setting details
     * @param array $dataArr
     * @param integer $BUConfigID
     * @return integer
     */
    function updateConfigurationSetting($dataArr,$BUConfigID){
        
        $this->db->where('BUCID', $BUConfigID);
        $this->db->update(BUSINESSUNITCONFIGS, $dataArr);
        return $this->db->affected_rows();        
    }
    
    /*
     * Function for get configuration setting details by id
     * Parameters : $ColumnName,$ColumnVal
     * Return : array
     */
    function getConfigurationSettingByKeyAndValue($ColumnName,$ColumnVal,$select_column='*'){        
        
        $this->db->select("BC.".$select_column);
        $this->db->select("C.Name");
        $this->db->from(BUSINESSUNITCONFIGS."  BC ");
        $this->db->join(CONFIGS." AS C", ' C.ConfigID = BC.ConfigID','inner');
                
        $this->db->where('BC.'.$ColumnName,$ColumnVal);
        $query = $this->db->get();
        
        return $query->row_array();
    }

    
    /**
     * Function for get configuration settings list
     * Parameters : 
     * Return : configuration setting array
     */
    public function getAdminConfigurationSettings() {

        $this->db->select('BU.BusinessUnitID AS BusinessUnitID', FALSE);
        $this->db->select('BUC.BUCID AS BUConfigID', FALSE);
        $this->db->select('BUC.Value AS ConfigValue', FALSE);
        $this->db->select('C.ConfigID AS ConfigID', FALSE);
        $this->db->select('C.Name AS ConfigurationName', FALSE);
        $this->db->select('C.Description AS Description', FALSE);
        $this->db->select('C.IsActive AS IsActive', FALSE);
        $this->db->select('DT.DataTypeID AS DataTypeID', FALSE);
        $this->db->select('DT.Name AS DataTypeName', FALSE);

        
        $this->db->from(BUSINESSUNITS . "  BU ");
        $this->db->join(BUSINESSUNITCONFIGS." AS BUC", ' BUC.BusinessUnitID = BU.BusinessUnitID','inner');
        $this->db->join(CONFIGS." AS C", ' C.ConfigID = BUC.ConfigID','inner');
        $this->db->join(DATATYPES." AS DT", ' DT.DataTypeID = C.DataTypeID','inner');
        
        $this->db->where('BU.BusinessStatusId',2);

        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results = $query->result_array();
        return $results;
    }

    public function set_group_permission($qa,$wiki,$tasks,$ideas,$polls,$discussion,$announcements)
    {
        $this->db->empty_table(ALLOWEDGROUPTYPE);
        if($qa)
        {
            foreach($qa as $key=>$val)
            {
                $data = array();
                $data['ModuleID'] = $val['ModuleID'];
                $data['ModuleEntityID'] = get_detail_by_guid($val['ModuleEntityGUID'],$val['ModuleID']);
                $data['PostType'] = 2;
                $data['PostTypeLabel'] = 'Q & A';
                $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->insert(ALLOWEDGROUPTYPE,$data);
            }
        }

        if($wiki)
        {
            foreach($wiki as $key=>$val)
            {
                $data = array();
                $data['ModuleID'] = $val['ModuleID'];
                $data['ModuleEntityID'] = get_detail_by_guid($val['ModuleEntityGUID'],$val['ModuleID']);
                $data['PostType'] = 4;
                $data['PostTypeLabel'] = 'Article';
                $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->insert(ALLOWEDGROUPTYPE,$data);
            }
        }

        /*if($tasks)
        {
            foreach($tasks as $key=>$val)
            {
                $data = array();
                $data['ModuleID'] = $val['ModuleID'];
                $data['ModuleEntityID'] = get_detail_by_guid($val['ModuleEntityGUID'],$val['ModuleID']);
                $data['PostType'] = 5;
                $data['PostTypeLabel'] = 'Tasks & Lists';
                $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->insert(ALLOWEDGROUPTYPE,$data);
            }
        }

        if($ideas)
        {
            foreach($ideas as $key=>$val)
            {
                $data = array();
                $data['ModuleID'] = $val['ModuleID'];
                $data['ModuleEntityID'] = get_detail_by_guid($val['ModuleEntityGUID'],$val['ModuleID']);
                $data['PostType'] = 6;
                $data['PostTypeLabel'] = 'Ideas';
                $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->insert(ALLOWEDGROUPTYPE,$data);
            }
        }

        if($polls)
        {
            foreach($polls as $key=>$val)
            {
                $data = array();
                $data['ModuleID'] = $val['ModuleID'];
                $data['ModuleEntityID'] = get_detail_by_guid($val['ModuleEntityGUID'],$val['ModuleID']);
                $data['PostType'] = 3;
                $data['PostTypeLabel'] = 'Polls';
                $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->insert(ALLOWEDGROUPTYPE,$data);
            }
        }*/

        if($discussion)
        {
            foreach($discussion as $key=>$val)
            {
                $data = array();
                $data['ModuleID'] = $val['ModuleID'];
                $data['ModuleEntityID'] = get_detail_by_guid($val['ModuleEntityGUID'],$val['ModuleID']);
                $data['PostType'] = 1;
                $data['PostTypeLabel'] = 'Discussion';
                $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->insert(ALLOWEDGROUPTYPE,$data);
            }
        }

        if($announcements)
        {
            foreach($announcements as $key=>$val)
            {
                $data = array();
                $data['ModuleID'] = $val['ModuleID'];
                $data['ModuleEntityID'] = get_detail_by_guid($val['ModuleEntityGUID'],$val['ModuleID']);
                $data['PostType'] = 7;
                $data['PostTypeLabel'] = 'Announcements';
                $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
                $this->db->insert(ALLOWEDGROUPTYPE,$data);
            }
        }
    }

    // Get Group permission
    public function get_group_permission()
    {
        $this->db->where_in('PostTypeLabel',array('Article','Discussion','Q & A','Announcements'));
        $res = $this->db->get(ALLOWEDGROUPTYPE)->result_array();
        $return = array();
        if(!empty($res))
        {
            foreach ($res as $key => $r) 
            {
                if($r['ModuleID']==0)
                {
                    $return[$key] = $r;
                    $return[$key]['Name'] = 'Everyone';
                    $return[$key]['ModuleEntityGUID'] = 0;
                }
                else
                {
                    if($r['ModuleID']==1)
                    {
                        $return[$key] = $r;
                        $group_details          = get_detail_by_id($r['ModuleEntityID'],1,'',2);    
                        $return[$key]['Name']   = $group_details['GroupName'];
                        $return[$key]['ModuleEntityGUID'] = $group_details['GroupGUID'];
                    }
                    else if($r['ModuleID']==3)
                    {
                        $return[$key] = $r;
                        $user_details = get_detail_by_id($r['ModuleEntityID'],3,'',2);
                        $return[$key]['Name'] = $user_details['FirstName'].' '.$user_details['LastName'];
                        $return[$key]['ModuleEntityGUID'] = $user_details['UserGUID'];
                    }
                }
            }
        }
        return $return;
    }
    
}
//End of file configuration_model.php
