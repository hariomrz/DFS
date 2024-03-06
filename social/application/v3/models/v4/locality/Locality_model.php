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

    public function get_locality_list($data) {  
        $locality_list = array();
       
        
        $page_no    = safe_array_key($data, 'PageNo', 1);
        $page_size  = safe_array_key($data, 'PageSize', 100);
        $search_keyword = safe_array_key($data, 'Keyword', '');
            
        
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID');
        $this->db->select('IFNULL(L.AltEngName,"") as aen', FALSE);
        $this->db->select('IFNULL(L.AltHindiName,"") as ahn', FALSE);
        $this->db->select('IFNULL(W.Name,"") as WName', FALSE);
        $this->db->select('IFNULL(W.WardID,"") as WID', FALSE);
        $this->db->select('IFNULL(W.Number,"") as WNumber', FALSE);
        $this->db->select('IFNULL(W.Description,"") as WDescription', FALSE);
        $this->db->from(LOCALITY . ' L');
        $this->db->join(WARD . ' W', 'W.WardID=L.WardID','LEFT');
        $this->db->where('L.StatusID','2');
        $this->db->order_by('L.Name', 'ASC');
        $this->db->order_by('L.HindiName', 'ASC');
        //$this->db->where_not_in('LocalityID', $ids);
         if (!empty($search_keyword)) {
            $this->db->where("(L.Name like '%" . $this->db->escape_like_str($search_keyword) . "%' or L.HindiName like '%" . $this->db->escape_like_str($search_keyword) . "%' or L.AltEngName like '%" . $this->db->escape_like_str($search_keyword) . "%' or L.AltHindiName like '%" . $this->db->escape_like_str($search_keyword) . "%')");
            //$this->db->where("(L.Name like '%" . $this->db->escape_like_str($search_keyword) . "%')");
        }

        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        
        $query = $this->db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows()) {
            $locality_list = $query->result_array();
        }
        
        return $locality_list;
    }    
}
?>