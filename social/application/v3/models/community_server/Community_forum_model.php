<?php

class Community_forum_model extends CI_Model{
    
    protected $_forum_id = 1;


    public function __construct() {
        $this->load->model(array('community_server/community_server'));
    }
    
    public function create_category($data) {
        $data = array_merge($data, array('ForumID' => $this->_forum_id));
        $response_data = $this->community_server->send_to_server('forum/create_category', $data);
        
        if(!empty($response_data['Data']['ForumCategoryID'])) {
            $forum_category_id = $response_data['Data']['ForumCategoryID'];
        }
    }
    
    
    public function delete_category($data) {
        $data = array_merge($data, array('ForumID' => $this->_forum_id));
        $this->community_server->send_to_server('forum/delete_category', $data);
    }
    
    
    public function change_category_order($data) {
        $data = array_merge($data, array('ForumID' => $this->_forum_id));
        $this->community_server->send_to_server('forum/change_category_order', $data);
    }
    
    
    

}

?>