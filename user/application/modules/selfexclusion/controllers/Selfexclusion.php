<?php
class Selfexclusion extends Common_Api_Controller {

    function __construct() {
        parent::__construct();      
    }

    public function index_get() {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    public function index_post() {
        $this->response(array(config_item('rest_status_field_name') => FALSE), rest_controller::HTTP_NOT_FOUND);
    }

    public function set_self_exclusion_post(){
		$this->form_validation->set_rules('max_limit', 'max limit', 'trim|required|integer');
		
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}  
        $this->load->model("selfexclusion/Selfexclusion_model");
        $this->Selfexclusion_model->set_self_exclusion();
        $this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response_arry['data']			= array();
		$this->api_response_arry['message']		   = $this->lang->line('self_exclusion_success');
		$this->api_response();
    }

    /**
	 * Used to get self exclusion  for user
	 */
	public function get_user_self_exclusion_post() {

        $allow_self_exclusion = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;
        
        if($allow_self_exclusion) {
            $this->load->model("selfexclusion/Selfexclusion_model");
            $user_self_exclusion = $this->Selfexclusion_model->get_self_exclusion();

            $custom_data = $this->app_config['allow_self_exclusion']['custom_data'];

            if(isset($user_self_exclusion['requested_max_limit']) && !empty($user_self_exclusion['requested_max_limit'])) {
                $custom_data['max_limit'] = $user_self_exclusion['requested_max_limit'];
                unset($user_self_exclusion['requested_max_limit']);
            }

            $this->api_response_arry['data']['user_self_exclusion'] = $user_self_exclusion;
            $this->api_response_arry['data']['default_self_exclusion'] = $custom_data;
        }
		
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;		
		$this->api_response();
	}

    
}