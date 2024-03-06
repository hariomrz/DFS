<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Notification_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_all_device_id($user_ids)
    {
        $this->db->select('device_id,device_type', false);
        $this->db->from(ACTIVE_LOGIN);
        $this->db->where_in('user_id', $user_ids);
        $this->db->where('device_id IS NOT NULL');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }
}