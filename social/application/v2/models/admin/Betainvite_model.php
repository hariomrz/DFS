<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Betainvite_model extends Admin_Common_Model
{
    
    public function __construct(){
        parent::__construct();
    }
        
    /**
     * Function for get all beta invite users list
     * Parameters : start_offset, end_offset, start_date, end_date, user_status, search_keyword, sort_by, order_by
     * Return : Users array
     */
    public function getBetaInviteUsers($start_offset=0, $end_offset="", $start_date="", $end_date="", $user_status="", $search_keyword="", $sort_by="", $order_by=""){
        /* Load Global settings */
        $global_settings = $this->config->item("global_settings");

        /* Change date_format into mysql date_format */
        $mysql_date =  dateformat_php_to_mysql($global_settings['date_format']);
            
        
        $sub = $this->subquery->start_subquery('select');
        $sub->select('(CASE WHEN BIL.CreatedDate THEN DATE_FORMAT(BIL.CreatedDate, "'.$mysql_date.'") ELSE "N/A" END) AS lastlogindate', FALSE);
        $sub->from(BETAINVITELOGS." AS BIL ");
        $sub->where('BIL.BetaInviteID = BI.BetaInviteID');
        $sub->order_by("BIL.BetaInviteLogID DESC");
        $sub->limit('1');
        $this->subquery->end_subquery('lastlogindate');
        
        $this->db->select('BI.BetaInviteID as betainviteid', FALSE);
        $this->db->select('BI.Name as name', FALSE);
        $this->db->select('BI.Email AS email', FALSE);        
        $this->db->select('BI.Code AS code', FALSE);
        $this->db->select('BI.BetaInviteGUID AS betainviteguid', FALSE);        
        $this->db->select('BI.StatusID AS statusid', FALSE);
        $this->db->select('BI.CreatedDate AS created_date', FALSE);
        $this->db->select('BI.ModifiedDate AS modified_date', FALSE);
        $this->db->select('BI.UserID AS userid', FALSE);
        
        $this->db->from(BETAINVITES."  BI ");
        
        if($user_status == 2 || $user_status == 4){
            $this->db->select('(CASE WHEN U.UserID THEN U.Email ELSE 0 END) AS register_email', FALSE);
            $this->db->join(USERS." AS U", ' U.UserID = BI.UserID','inner');
        }

        /* start_date, end_date for filters */
        if(isset($start_date) && $end_date !='')
        {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));

            $this->db->where('DATE(BI.CreatedDate) BETWEEN "'.$start_date.'"  AND "'.$end_date.'"', NULL, FALSE);
        }

        if(isset($user_status) && $user_status !=''){
            $this->db->where('BI.StatusID',$user_status);
        }
        

        if(isset($search_keyword) && $search_keyword !='')
            $this->db->like('BI.Name',$search_keyword);

        //Here we clone the DB object for get all Count rows
        $tempdb = clone $this->db;
        $temp_q = $tempdb->get();
        $results['total_records'] = $temp_q->num_rows();

        /* Sort_by, Order_by */
        if($sort_by == '' )
           $sort_by='BI.CreatedDate';

        if($order_by == false || $order_by == '' )
           $order_by='ASC';

        if($order_by == 'true')
           $order_by = 'DESC';

        $this->db->order_by($sort_by, $order_by);

        /* Start_offset, end_offset */
        if(isset($start_offset) && $end_offset !='')
             $this->db->limit($end_offset,$start_offset);


        $query = $this->db->get();
        //echo $this->db->last_query();die;
        $results['results'] = $query->result_array();
        return $results;
    }
    
    /**
     * Function for check beta invite email adress exist or not
     * @param string $Email
     * @return string
     */
    function checkBetaInviteEmailExist($Email){
        
        $this->db->select('*');
        $this->db->from(BETAINVITES);
        $this->db->where('Email',$Email);
                
        $query = $this->db->get();
        if ($query->num_rows()) {            
            $return = 'exist';
        } else {
            $return = 'notexist';
        }
        
        return $return;
    } 
    
    /**
     * Function for create beta invite
     * @param array $dataArr
     * @return integer
     */
    function saveBetaInviteDetails($dataArr){
        
        $this->db->insert(BETAINVITES, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
    
    /**
     * Update beta invited users status detail
     * @param type $data
     * @param type $key
     */
    function updateBatchBetaInviteInfo($data, $key) {
        $this->db->update_batch(BETAINVITES, $data, $key);
    }
    
    /**
     * Function for get beta invite user detail by id
     * Parameters : $BetaInviteId
     * Return : array
     */
    public function getBetaInviteById($BetaInviteId){
        
        $this->db->select('*');
        $this->db->from(BETAINVITES);
        $this->db->where('BetaInviteID',$BetaInviteId);
        $query = $this->db->get();
        return $query->row_array();
        
    }
    
    
/************ Front Website Function *********************/
    /**
     * Function for verify beta invitation code
     * Parameters : $BetaInviteCode
     * Return : array
     */
    public function verifyBetaInvitationCode($BetaInviteCode){
        
        $this->db->select('*');
        $this->db->from(BETAINVITES);
        $this->db->where('Code',$BetaInviteCode);
        $this->db->where('(StatusId=1 OR StatusId=2)');
        $query = $this->db->get();
        
        $data = $query->row_array();
        if ($query->num_rows()) {            
            $return = array("result" => "valid", "BetaInviteID" => $data["BetaInviteID"]);
        } else {
            $return = array("result" => "invalid");
        }
        
        return $return;        
    }
    
    /**
     * Function for verify beta invitation guid
     * Parameters : $BetaInviteGUID
     * Return : array
     */
    public function verifyBetaInvitationGuId($BetaInviteGUID){
        
        $this->db->select('*');
        $this->db->from(BETAINVITES);
        $this->db->where('BetaInviteGUID',$BetaInviteGUID);
        $query = $this->db->get();
        $data = $query->row_array();
        
        if ($query->num_rows()) {            
            $return = array("result" => "valid", "BetaInviteID" => $data["BetaInviteID"]);
        } else {
            $return = array("result" => "invalid");
        }
        
        return $return;        
    }
    
    /**
     * Function for verify beta invitation guid
     * Parameters : $BetaInviteGUID
     * Return : array
     */
    public function checkBetaInvitationGuIdAlreadyUsed($BetaInviteGUID){
        
        $this->db->select('*');
        $this->db->from(BETAINVITES);
        $this->db->where('BetaInviteGUID',$BetaInviteGUID);
        $this->db->where('StatusID', '1');
        $this->db->where('UserID IS NULL');
        $query = $this->db->get();
        
        return $query->row_array();
    }
    
    /**
     * Function for update beta invite user detail
     * @param array $dataArr
     * @param integer $BetaInviteID
     * @return integer
     */
    function updateBetaInviteUserDetail($dataArr,$BetaInviteID){
        
        $this->db->where('BetaInviteID', $BetaInviteID);
        $this->db->update(BETAINVITES, $dataArr);
        return $this->db->affected_rows();        
    }
    
    /**
     * Function for save beta invite log
     * @param array $dataArr
     * @return integer
     */
    function saveBetaInviteLogs($dataArr){
        
        $this->db->insert(BETAINVITELOGS, $dataArr);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
        
}
//End of file betainvite_model.php
