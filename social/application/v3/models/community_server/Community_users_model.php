<?php

class Community_users_model extends CI_Model{

    public function __construct() {
        $this->load->model(array('community_server/community_server'));
        
    }

    public function sigup_user($data) {
        $data['FirstName'] = 'Test';
        $data['LastName'] = 'Test';
        //$data['DeviceType'] = 'Web';
        
        $this->community_server->send_to_server('community/signup', $data);
    }
    
    public function update_user_profile($data) {
        $this->community_server->send_to_server('community_user/update_profile', $data);
    }
    
    
    public function update_profile_picture($data) {
        $this->community_server->send_to_server('community_user/update_profile_picture', $data);
    }
    
    
    public function change_password($data) {
        $this->community_server->send_to_server('community_user/change_password', $data);
    }

}

?>