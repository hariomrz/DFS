<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Props extends Common_Api_Controller{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('admin/Props_model');
	}

    /**
     * Used for get props filter data
     * @param array $post_data
     * @return json array
     */   
    public function get_filter_data_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports ID','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $filter = array();
        $filter['props'] = $this->get_props_list($sports_id);
        $filter['match'] = $this->Props_model->get_props_match_list($sports_id);
        $this->api_response_arry['data'] = $filter;
        $this->api_response();
    }

	/**
     * Used for get players props list 
     * @param array $post_data
     * @return json array
     */   
	public function get_player_props_list_post()
	{
        $this->form_validation->set_rules('sports_id', 'Sports ID','trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
		$post_data = $this->input->post();
        $result = $this->Props_model->get_player_props_list($post_data);
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

    /**
    * Function used for update player props status
    * @param array $post_data
    * @return array
    */
    public function update_props_status_post()
    {
        $post_data = $this->input->post();
        if(empty($post_data)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please update atleast one props data.";
            $this->api_response();
        }
        //echo "<pre>";print_r($post_data);die;
        $props_arr = array();
        foreach($post_data as $row){
            if(!empty($row['season_prop_id']) && isset($row['status'])){
                $data_arr = array();
                $data_arr['season_prop_id'] = $row['season_prop_id'];
                $data_arr['status'] = $row['status'];
                $props_arr[] = $data_arr;
            }
        }
        if(!empty($props_arr)){
            $this->Props_model->update_player_props_status($props_arr);
        }

        $this->api_response_arry['message'] = $this->lang->line("pl_props_status_success");
        $this->api_response_arry['data'] = array();
        $this->api_response();
    }
}