<?php defined('BASEPATH') OR exit('No direct script access allowed');

class League extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->post();
		$this->load->model('admin/League_model');
		$allow_livefantasy =  isset($this->app_config['allow_livefantasy'])?$this->app_config['allow_livefantasy']['key_value']:0;
        if($allow_livefantasy == 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Live fantasy not enabled";
            $this->api_response_arry['global_error'] = "Module Disable";
            $this->api_response();
        }
	}

	/**
     * Used for get sports wise league list 
     * @param int $sports_id
     * @return json array
     */   
	public function get_sport_leagues_post()
	{
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$result = $this->League_model->get_sport_league_list($post_data['sports_id']);
		
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	public function league_over_post(){
		$this->form_validation->set_rules('league_id', 'league id','trim|required');
		if ($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
        $league_id = $this->input->post('league_id');
        $over_format = $this->League_model->get_over_format($league_id);
        $over = 20;
        if($over_format['format'] == ODI_FORMAT){
            $over = 50;
        }else if($over_format['format'] == T10_FORMAT){
            $over = 10;
        }
        $data['overs'] = array();
        for($i=1;$i<=$over;$i++){
            $data['overs'][] = $i;
        }
        $this->api_response_arry['data'] = $data;
        $this->api_response();

	}
}