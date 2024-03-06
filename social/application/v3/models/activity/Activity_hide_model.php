<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_hide_model extends Common_Model {
    protected $user_hide_activities = array();
    public function __construct() {
        parent::__construct();
    }

    /**
     * Used to hide/unhide activity
     */
    function toggle_hide($data) {
        $this->db->select('ActivityHideID, Status');
        $this->db->from(ACTIVITYHIDE);
        $this->db->where('UserID', $data['UserID']);
        $this->db->where('ActivityID', $data['ActivityID']);
        $this->db->limit(1);
        $query = $this->db->get();
        $modified_date = get_current_date('%Y-%m-%d %H:%i:%s');
        if($query->num_rows() > 0) {
            $row = $query->row_array();            
            $activity_hide_id  = $row['ActivityHideID'];
            $status    = $data['Status'];
            $update_data = array();    
            $update_data['ModifiedDate']    = $modified_date;   
            $update_data['Status']    = $status;
           
            $this->db->where('ActivityHideID', $activity_hide_id);
            $this->db->update(ACTIVITYHIDE,$update_data);
        } else {
            $data['ModifiedDate'] = $modified_date;
            $this->db->insert(ACTIVITYHIDE,$data);
        }

        if (CACHE_ENABLE) {
            $this->cache->delete('uhidact_' . $data['UserID']);
        }
    }

    /**
     * Used to check activity hide or not
     */
    function is_hide($activity_id, $user_id) {
        $this->db->select('ActivityHideID');
        $this->db->from(ACTIVITYHIDE);
        $this->db->where('Status', 1);
        $this->db->where('UserID', $user_id);
        $this->db->where('ActivityID', $activity_id);
        $this->db->limit(1);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * used to set user hide activity id
     */
    function set_user_hide_activity($user_id){     
        if(API_VERSION == "v5" ){
            return;
        }   
        $row_ids = '';
        if (CACHE_ENABLE) {
            $row_ids = $this->cache->get('uhidact_' . $user_id);
        }
        if(empty($row_ids)){
            $this->db->select('GROUP_CONCAT(ActivityID) as ActivityIDs ');
            $this->db->where('UserID',$user_id);
            $this->db->where('Status',1);
            $query = $this->db->get(ACTIVITYHIDE);
            $hide_ids = '-1';
            if ($query->num_rows() > 0) {
                $row_ids=$query->row_array();
                $hide_ids = $row_ids['ActivityIDs'];
                if(!empty($hide_ids)) {                    
                    $this->user_hide_activities =  explode(',',$row_ids['ActivityIDs']);
                }
            }
            if (CACHE_ENABLE) {
                $this->cache->save('uhidact_' . $user_id, $hide_ids, CACHE_EXPIRATION);
            }
        } else if($row_ids != '-1') {
            $this->user_hide_activities =  explode(',',$row_ids);
        }
    }
    /**
     * Used to get user hide activity ids
     */
    public function get_user_hide_activity() {
        return $this->user_hide_activities;
    }

    public function get_total_user_used_hide_post($day='All') {
        $this->db->select("UserID");
        if($day == 'today') {
            $current_time_zone = date_default_timezone_get();
            $time_zone = 'Asia/Calcutta';
            date_default_timezone_set($time_zone);
            $today_date = date('Y-m-d');
            date_default_timezone_set($current_time_zone);
            $this->db->like("DATE_FORMAT(CONVERT_TZ(ModifiedDate,'Etc/UTC','" . $time_zone . "'),'%Y-%m-%d')", $today_date, FALSE);        
        }
        $this->db->group_by('UserID');
        $query = $this->db->get(ACTIVITYHIDE);
       // echo $this->db->last_query();die;
        $total = $query->num_rows();
        
        return $total;
    }
    
}