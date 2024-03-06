<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* This Class used as REST API for Interest module
* @category		Controller
* @author		Vinfotech Team
*/
class Interest extends Common_API_Controller {
    // Class Constructor
    function __construct() {
        parent::__construct();
        $this->load->model(array('interest/interest_model'));
    }
	
    /**
     * Used to get Interest list
     */
    public function index_post() {
        $data = $this->post_data; 
        $user_id = $this->UserID;
        
        $parent_interest_id = safe_array_key($data, 'ParentID', 0);
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 50);
        $data = $this->interest_model->get_interests($user_id, $parent_interest_id, $page_no, $page_size);
        
        $this->return['Data']=$data;        
        $this->response($this->return);  // Final Output 
    }
    
    public function update_user_interest_post() {
        $data = $this->post_data; // Get post data        
        $user_id = $this->UserID; 
        $return = $this->return;       
        if (isset($data)) {
            $interest_ids = safe_array_key($data, 'InterestIDS', array());
            $this->interest_model->insert_update_user_interest($interest_ids, $user_id);
        } else {
            $return['ResponseCode'] = self::HTTP_BAD_REQUEST;
            $return['Message'] = lang('input_invalid_format');
        }
        $this->response($return);
    }
    
    /**
     * [get_user_interest Used to get user interest]
     * @return [json] [json object]
     */
    public function get_user_interest_post() {
        $return     = $this->return;
        $user_id    = $this->UserID;
        $data       = $this->post_data;
        
        $user_guid = safe_array_key($data, 'UserGUID', "");
        if (!empty($user_guid)) {
            $user_id = get_detail_by_guid($user_guid, 3);
        }

        $page_no = safe_array_key($data, 'PageNo', PAGE_NO);
        $page_size = safe_array_key($data, 'PageSize', '');
        $return['Data'] = $this->interest_model->get_user_interest($user_id, $page_no, $page_size, false);
        $this->response($return);
    }
    
    /**
     * [get_popular_interest Used to get popular interest]
     * @return [json] [json object]
     */
    public function get_popular_interest_post() {
        $return = $this->return;
        $data = $this->post_data;
        $user_id = $this->UserID;
        $page_no = safe_array_key($data, 'PageNo', 1);
        $page_size = safe_array_key($data, 'PageSize', 20);
        $keyword = safe_array_key($data, 'Keyword', '');
        $exclude = safe_array_key($data, 'Exclude', array());
        $return['Data'] = $this->user_model->get_popular_interest($user_id, $page_no, $page_size, $keyword, $exclude);
        $this->response($return);
    }
}