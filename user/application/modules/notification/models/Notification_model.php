<?php
class Notification_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }
    
    public function sync_notification_description() {
        $this->db->select("*",FALSE);
        $this->db->from(NOTIFICATION_DESCRIPTION . ' ND');
        $query = $this->db->get();
        $resultList = $query->result_array();
        if($resultList) {
            $this->load->model('notification/Notify_nosql_model');
            $this->Notify_nosql_model->delete_collection(NOTIFICATION_DESCRIPTION);
            foreach ($resultList as $result) {
                $this->Notify_nosql_model->insert_nosql(NOTIFICATION_DESCRIPTION,$result);
            }
        }
    }
}