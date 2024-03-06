<?php

class Booster extends Common_Api_Controller 
{
	function __construct()
	{
        parent::__construct();   
	}

    /**
     * Used for get boosters list
     * @param int $collection_master_id
     * @return array
     */
    public function get_booster_list_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $cache_key = "booster_list_".$sports_id;
        $booster_list = $this->get_cache_data($cache_key);
        if(!$booster_list){
            //get booster list
            $this->load->model("booster/Booster_model");
            $booster_list = $this->Booster_model->get_booster_list($sports_id);
            $this->set_cache_data($cache_key,$booster_list,REDIS_30_DAYS);
        }
        $this->api_response_arry['data'] = $booster_list;
        $this->api_response();
    }

	/**
     * Used for get collection boosters list
     * @param int $collection_master_id
     * @return array
     */
    public function get_collection_booster_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $cm_id = $post_data['collection_master_id'];
        //get booster list
        $cache_key = "collection_booster_".$cm_id;
        $booster_list = $this->get_cache_data($cache_key);
        if(!$booster_list){
            $this->load->model("booster/Booster_model");
            $booster_list = $this->Booster_model->get_collection_booster($cm_id);
            $this->set_cache_data($cache_key,$booster_list,REDIS_24_HOUR);
        }

        //master position set in cache
		$position_cache_key = "position_".$sports_id;
		$master_positions = $this->get_cache_data($position_cache_key);
        if(!$master_positions)
        {
        	$master_positions = $this->Booster_model->get_master_position($sports_id);
			//set master position in cache for 30 days
        	$this->set_cache_data($position_cache_key,$master_positions,REDIS_30_DAYS);
        }
        $result = array();
        $position = array("All");
        $position = array_merge($position,array_column($master_positions,"position"));
        $result['position'] = $position;
        $result['booster'] = $booster_list;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * used for save booster for team
     * @param int $collection_master_id
     * @param int $lineup_master_id
     * @param int $booster_id
     * @return
     */
    public function save_booster_post()
    {   
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('booster_id', "booster id", 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        $lm_id = $post_data['lineup_master_id'];
        $booster_id = $post_data['booster_id'];
        
        //get booster list
        $cache_key = "collection_booster_".$cm_id;
        $booster_list = $this->get_cache_data($cache_key);
        if(!$booster_list){
            $this->load->model("booster/Booster_model");
            $booster_list = $this->Booster_model->get_collection_booster($cm_id);
            $this->set_cache_data($cache_key,$booster_list,REDIS_24_HOUR);
        }

        $booster_ids = array_column($booster_list,"booster_id");
        if(!in_array($booster_id,$booster_ids)){
            $this->api_response_arry['message'] = $this->lang->line('invalid_booster_id');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $this->load->model("booster/Booster_model");
        $team_info = $this->Booster_model->get_single_row("*", LINEUP_MASTER,array("lineup_master_id" => $lm_id));
        if($team_info['is_2nd_inning'] == "1"){
            $this->api_response_arry['message'] = $this->lang->line('booster_only_for_dfs');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        if($team_info['collection_master_id'] != $cm_id){
            $this->api_response_arry['message'] = $this->lang->line('invalid_team_for_match');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $data_arr = array();
        $data_arr['booster_id'] = $booster_id;
        $data_arr['date_modified'] = $current_date;
        $result = $this->Booster_model->update(LINEUP_MASTER,$data_arr,array("collection_master_id"=>$cm_id,"lineup_master_id"=>$lm_id,"user_id"=>$this->user_id));
        if($result){
            //remove user team cache
            $teams_cache_key = "user_teams_".$cm_id."_".$this->user_id;
            $this->delete_cache_data($teams_cache_key);

            if($team_info['booster_id'] > 0){
                $this->api_response_arry['message'] = $this->lang->line('update_booster_success');
            }else{
                $this->api_response_arry['message'] = $this->lang->line('save_booster_success');
            }
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = $this->lang->line('save_booster_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }
}