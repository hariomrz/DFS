<?php 

/**
 * Auth for user authentication
 * @package Auth
 * @category Auth
 */
class Auth extends Common_Api_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index_get() 
    {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    public function index_post() {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    
    public function get_app_master_list_post() 
    {
        $url = NETWORK_FANTASY_URL."/user/auth/get_app_master_list";
        $master_list =  $this->http_post_request($url);
        $this->network_api_response($master_list);
        
    }


}