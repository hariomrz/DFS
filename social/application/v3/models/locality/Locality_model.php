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
        if (CACHE_ENABLE) {
            $locality_list = $this->cache->get('locality_list');            
        }
        if(empty($locality_list)) {
            /* $page_no    = safe_array_key($data, 'PageNo', 1);
            $page_size  = safe_array_key($data, 'PageSize', 100);
            $search_keyword = safe_array_key($data, 'Keyword', '');
             * 
             */
         
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
            //$this->db->where_not_in('LocalityID', $ids);
           /* if (!empty($search_keyword)) {
                $this->db->where("(L.Name like '%" . $this->db->escape_like_str($search_keyword) . "%')");
            }

            $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
            * 
            */
            $query = $this->db->get();
            if ($query->num_rows()) {
                $locality_list = $query->result_array();
                if (CACHE_ENABLE) {
                    $this->cache->save('locality_list', $locality_list);
                }
            }
        }
        if(!empty($locality_list)) {
            initiate_worker_job('upload_api_data_on_bucket', array('FileName' => "locality_list.json", "FileData" => $locality_list));
        }
        return $locality_list;
    }
    
    public function get_user_locality($user_id) {
        //$ids = ['5'];
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
        //$ids = ['5'];
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
            $locality['LocalityID'] = 0;
        }
        return $locality;
    }
    
    function is_locality_exist($locality_id) {
        $this->db->select('L.LocalityID');
        $this->db->from(LOCALITY. ' L');
        $this->db->where('L.LocalityID', $locality_id);
        $this->db->where('L.StatusID', 2);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return TRUE;
        }
        return FALSE;
    }
    
    function is_locality_name_exist($name, $ward_id, $locality_id=0) {
        $name = trim(strtolower($name));
        $this->db->select('L.LocalityID');
        $this->db->from(LOCALITY. ' L');
        $this->db->group_start();
        $this->db->where('LOWER(L.Name)', $name,NULL,FALSE);
        $this->db->or_where('LOWER(L.HindiName)', $name);
        $this->db->group_end();
        $this->db->where('WardID', $ward_id);
        $this->db->where_in('L.StatusID', array(2,4));
        if(!empty($locality_id)) {
            $this->db->where('L.LocalityID !=', $locality_id);
        }
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $row = $query->row();
            return $row->LocalityID;
        }
        return FALSE;
    }
    
    function add_locality_name($data) {
        $ward_id = $data['WID'];
        $name = ucwords(trim($data['Name']));
        $hindi_name = safe_array_key($data, 'HindiName', $name);
        $hindi_name = ucwords(trim($hindi_name));

        $status_id = safe_array_key($data, 'StatusID', 2);
        $short_name = safe_array_key($data, 'ShortName', '');
        $aen = safe_array_key($data, 'aen', '');
        $ahn = safe_array_key($data, 'ahn', '');
        $locality_id = safe_array_key($data, 'LocalityID', 0);

        $insert_data = array(
                        'Name' => $name,
                        'HindiName' => $hindi_name,
                        'ShortName' => $short_name,
                        'WardID' => $ward_id,
                        'AltEngName' => $aen, 
                        'AltHindiName' => $ahn, 
                        'StatusID' => $status_id
                        );
        $current_date = get_current_date('%Y-%m-%d %H:%i:%s');                
        if(!empty($locality_id)) {
            $insert_data['ModifiedDate'] = $current_date;
            $this->db->where('LocalityID', $locality_id);
            $this->db->update(LOCALITY, $insert_data);
        } else {
            $insert_data['CreatedDate'] = $current_date;
            $insert_data['CityID'] = 2229;
            $this->db->insert(LOCALITY, $insert_data);
            $locality_id = $this->db->insert_id();
        }

        if($status_id == 2) {
            $this->delete_api_static_file('locality_list');
            if (CACHE_ENABLE) {
                $this->cache->delete('locality_list');
            }
        }
        return $locality_id;
    }

    public function admin_locality_list($data) {  
        $locality_list = array();
        
        $page_no    = safe_array_key($data, 'PageNo', 1);
        $page_size  = safe_array_key($data, 'PageSize', 20);
        $search_keyword = safe_array_key($data, 'Keyword', '');
        $ward_id = safe_array_key($data, 'WID', 0);
        
        $this->db->select('L.Name, L.HindiName, L.ShortName, L.LocalityID, L.StatusID');
        $this->db->select('IFNULL(L.AltEngName,"") as aen', FALSE);
        $this->db->select('IFNULL(L.AltHindiName,"") as ahn', FALSE);
        $this->db->select('IFNULL(W.Name,"") as WName', FALSE);
        $this->db->select('IFNULL(W.WardID,"") as WID', FALSE);
        $this->db->select('IFNULL(W.Number,"") as WNumber', FALSE);
        $this->db->select('IFNULL(W.Description,"") as WDescription', FALSE);
        $this->db->from(LOCALITY . ' L');
        $this->db->join(WARD . ' W', 'W.WardID=L.WardID','LEFT');
        if(!empty($ward_id) && $ward_id!=1) {
            $this->db->where('L.WardID', $ward_id);
        }
        $this->db->where_in('L.StatusID', array(2,4));
        if (!empty($search_keyword)) {
            $search_keyword = strtolower(trim( $search_keyword));

            $this->db->group_start();
            $this->db->where("(LOWER(L.Name) like '%" . $this->db->escape_like_str($search_keyword) . "%')");
            $this->db->or_where("(L.HindiName like '%" . $this->db->escape_like_str($search_keyword) . "%')");
            $this->db->group_end();
        }

        if($data['Count']==1) {
            $query = $this->db->get();
            return $query->num_rows();
        }

        $this->query_order_locality($data);
        $this->db->limit($page_size, $this->get_pagination_offset($page_no, $page_size));
        $query = $this->db->get();
        if ($query->num_rows()) {
            $locality_list = $query->result_array();               
        }
       
        return $locality_list;
    }

    public function query_order_locality($data) {

        $order_by_field    = safe_array_key($data, 'OrderByField', 'L.Name');
        $order_by  = safe_array_key($data, 'OrderBy', 'ASC');

        $allowed_order_by_fields = [
            'L.Name' => 'L.Name',
            'W.Name' => 'W.Name',
            'W.Number' => 'W.Number'
        ];

        if (!in_array($order_by_field, array_keys($allowed_order_by_fields))) {
            $order_by_field_db = $allowed_order_by_fields['L.Name'];
        } else {
            $order_by_field_db = $allowed_order_by_fields[$order_by_field];
        }

        if (!in_array($order_by, ['ASC', 'DESC'])) {
            $order_by = 'DESC';
        }
        $this->db->order_by($order_by_field_db, $order_by);
    }

    public function user_list() {
        $return = array();
        $this->current_db->select('*');
        $this->current_db->from(ACTIVITY);
        $this->current_db->order_by('ActivityID', 'DESC');
        $this->current_db->limit(20);
        $query = $this->current_db->get();
        //echo $this->db->last_query();die;
        if ($query->num_rows() > 0) {
            $return = $query->result_array();
        }
        return $return;
    }
}
?>