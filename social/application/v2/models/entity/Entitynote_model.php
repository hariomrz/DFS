<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Entitynote_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }
    
    public function save($desc, $module_id, $module_entity_id, $status = NULL, $note_id = 0) {
        
        $data = array(
            'ModuleID' => $module_id,
            'ModuleEntityID' => $module_entity_id,
            'Description' => $desc,
            'ModifiedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
        );
        
        // Group Page and Event can have only one note
        if(in_array($module_id, array(1, 18, 14))) {
            $this->db->select('NoteID',false);
            $this->db->from(ENTITYNOTE);
            $this->db->where('ModuleID', $module_id);
            $this->db->where('ModuleEntityID', $module_entity_id);
            $query = $this->db->get();
            $this->db->limit(1);
            $entityNote = $query->row_array();
            $note_id = (!empty($entityNote['NoteID'])) ? $entityNote['NoteID'] : 0; 
            $status = 2;
        }
        
        if($status !== NULL) {
            $data['Status'] = $status;
        }
        
        if($note_id) {
            //$this->db->where('ModuleID', $module_id); 
            //$this->db->where('ModuleEntityID', $module_entity_id); 
            $this->db->where('NoteID', $note_id); 
            $this->db->update(ENTITYNOTE, $data); 
            return;
        }
        
        $data['CreatedDate'] = get_current_date('%Y-%m-%d %H:%i:%s');
        $this->db->insert(ENTITYNOTE, $data);
    }
    
    public function get_list($page_no = 1, $page_size = 10, $filter = array()) {
        
        $offset = ($page_no - 1) * $page_size;
        $module_id = (int)isset($filter['ModuleID']) ? $filter['ModuleID'] : 0;
        $module_entity_id = (int)isset($filter['ModuleEntityID']) ? $filter['ModuleEntityID'] : 0;
         
        $this->db->select('ModuleID, ModuleEntityID, Description, NoteID, DATE_FORMAT(CreatedDate,"%d %b %y at %h %i %p") as CreatedDate',false);
        $this->db->from(ENTITYNOTE);
        $this->db->where('Status', 2);
        
        if($module_id) {
            $this->db->where('ModuleID', $module_id);
        }
        
        if($module_entity_id) {
            $this->db->where('ModuleEntityID', $module_entity_id);
        }
        
        $query = $this->db->get();
        $this->db->limit($page_size, $offset);
        return $query->result_array();
        
    }
    
    public function delete_note($note_id) {
        $data = array(
            'Status' => 3
        );
        $this->db->where('NoteID', $note_id);
        $this->db->update(ENTITYNOTE, $data);
        return $this->db->affected_rows();
    }

}

//End of file users_model.php
