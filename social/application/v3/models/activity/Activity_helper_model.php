<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_helper_model extends Common_Model {

    public function __construct() {
        parent::__construct();
    }

    public function set_promotion($activity_id = 0, $entity_id = 0, $entity_type = '', $is_promoted = NULL) {
        
        if($entity_type == 'ACTIVITY') {
            $activity_id = $entity_id;
        }
        
        if($entity_type == 'COMMENT') {
            
            //Stop on post comment like
            return;
            
            $this->db->select('EntityID');
            $this->db->where('EntityType', 'ACTIVITY');
            $this->db->where('PostCommentID', $entity_id);
            $this->db->limit(1);
            $query = $this->db->get(POSTCOMMENTS);
            $row = $query->row_array();
            if(!empty($row['EntityID'])) {
                $activity_id = $row['EntityID'];
            }
        }

        if(!$activity_id) {
            return;
        }
        
        // Set promotion date if activity is promoted
        $activity_update_data = array(
            'PromotedDate' => get_current_date('%Y-%m-%d %H:%i:%s'),
        );
        $where_query = "ActivityID = $activity_id";
        if($is_promoted === NULL) {
            //$this->db->where('IsPromoted', 1);
            $where_query .= " AND IsPromoted = 1";
        } else {
            $activity_update_data = array(
                'PromotedDate' => ($is_promoted == 1) ? get_current_date('%Y-%m-%d %H:%i:%s') : 'CreatedDate',
                'IsPromoted' => $is_promoted
            );
            //$activity_update_data['IsPromoted'] = $is_promoted;
        }
        
        //$this->db->where('ActivityID', $activity_id);        
        //$this->db->update(ACTIVITY, $activity_update_data);
        
        $update_data_fields = [];
        foreach ($activity_update_data as $field => $val) {
            if($val == 'CreatedDate') {
                $update_data_fields[] = " $field = $val ";
            } else {
                $update_data_fields[] = " $field = '$val' ";
            }
            
        }
        $update_data_fields = implode(',', $update_data_fields);
        $update_entity_query = " Update ".ACTIVITY." SET $update_data_fields ";
        $update_entity_query .= " Where $where_query"; 
        $query = $this->db->query($update_entity_query);
    }
    
    

}
