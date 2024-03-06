<?php

class User_activity_log_mongo /*extends Base_mongo*/ {

    public function __construct() {
        $ci = &get_instance();
        $ci->load->library('Mongo_db');
        $this->mongo_db = $ci->mongo_db;
        //parent::__construct();
    }
    
    public function insert_log($data, $type) {
        
        $log_types = array(
            'ACTIVITY' => 1,
            'SEARCH' => 2,
            'VIEWLOG' => 3,
        );
        
        $doc_data = array(
            'ModuleID' => $data['ModuleID'],
            'ModuleEntityID' => $data['ModuleEntityID'],
            'UserID' => $data['UserID'],
            'ActivityTypeID' => isset($data['ActivityTypeID']) ? $data['ActivityTypeID'] : 0,
            'LogData' => $data,
            'LogType' => $log_types[$type]
        );
        
        $this->mongo_db->insert('UsersActivityLog', $doc_data);
    }
    
    
    public function getActivityData() {
        $data = $this->mongo_db->get('test');   print_r($data); die;
    }
}

?>