<?php
/**
 * This model is used to for locality
 * @package    Locality_model
 * @author     Vinfotech Team
 * @version    1.0
 *
 */
class Locality_model extends Common_Model {
    
    function __construct() {
        parent::__construct();
    }

    public function get_locality_list() {
        //$ids = ['5'];
        $this->db->select('Name, HindiName, ShortName, LocalityID');
        $this->db->from(LOCALITY);
        $this->db->where('StatusID','2');
        //$this->db->where_not_in('LocalityID', $ids);
        $this->db->order_by('Name', 'ASC');
        $this->db->where_in('WardID',array(2,3));
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function get_user_locality($user_id) {
        $ids = ['5'];
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->select('IFNULL(W.Name,"") as WName', FALSE);
        $this->db->select('IFNULL(W.WardID,"") as WID', FALSE);
        $this->db->select('IFNULL(W.Number,"") as WNumber', FALSE);        
        $this->db->select('IFNULL(W.Description,"") as WDescription', FALSE);
        $this->db->from(LOCALITY. ' L');
        $this->db->join(USERDETAILS . ' UD', 'UD.LocalityID=L.LocalityID');
        $this->db->join(WARD . ' W', 'W.WardID=L.WardID','LEFT');
        $this->db->where('UD.UserID', $user_id);
        //$this->db->where_not_in('L.LocalityID', $ids);
        $this->db->limit(1);
        $query = $this->db->get();
        $localty = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => '', 'WName'=>'', 'WNumber'=>'', 'WID'=>'', 'WDescription'=>'');
        if ($query->num_rows()) {
            $row = $query->row();            
            $localty["Name"] = $row->Name;
            $localty["HindiName"] = $row->HindiName;
            $localty["LocalityID"] = 0;
            if($row->LocalityID) {
                $localty["LocalityID"] = $row->LocalityID;
            }
            $localty["ShortName"] = $row->ShortName;
            $localty["WName"] = $row->WName;
            $localty["WID"] = $row->WID;
            $localty["WNumber"] = $row->WNumber;
            $localty["WDescription"] = $row->WDescription;
        }
        return $localty;
    }
    
    function get_locality($locality_id) {
        $ids = ['5'];
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID, L.IsPollAllowed');
        $this->db->select('IFNULL(W.Name,"") as WName', FALSE);
        $this->db->select('IFNULL(W.WardID,"") as WID', FALSE);
        $this->db->select('IFNULL(W.Number,"") as WNumber', FALSE);        
        $this->db->select('IFNULL(W.Description,"") as WDescription', FALSE);
        $this->db->from(LOCALITY. ' L');
        $this->db->join(WARD . ' W', 'W.WardID=L.WardID','LEFT');
        $this->db->where('L.LocalityID', $locality_id);
        //$this->db->where_not_in('L.LocalityID', $ids);
        $this->db->limit(1);
        $query = $this->db->get();
        $locality = array('Name' => '', 'HindiName'=>'', 'ShortName'=>'',  'LocalityID' => 0,  'IsPollAllowed' => 0, 'WName'=>'', 'WNumber'=>'', 'WID'=>'', 'WDescription'=>'');        
        if ($query->num_rows()) {
            $row = $query->row();            
            $locality["Name"] = $row->Name;
            $locality["HindiName"] = $row->HindiName;
            $locality["LocalityID"] = 0;
            if($row->LocalityID) {
                $locality["LocalityID"] = $row->LocalityID;
            }
            $locality["ShortName"] = $row->ShortName;
            $locality["IsPollAllowed"] = $row->IsPollAllowed;
            $locality["WName"] = $row->WName;
            $locality["WID"] = $row->WID;
            $locality["WNumber"] = $row->WNumber;
            $locality["WDescription"] = $row->WDescription;
        }
        if(empty($locality['LocalityID'])) {
            $$locality['LocalityID'] = 0;
        }
        return $locality;
    }
   
}
?>