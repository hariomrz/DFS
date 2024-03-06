<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Multigame extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();

		$allow_multigame =  isset($this->app_config['allow_multigame'])?$this->app_config['allow_multigame']['key_value']:0;
		if($allow_multigame == 0)
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message'] = "Multigame not enabled";
			$this->api_response();
		}	
		$this->admin_roles_manage($this->admin_id,'multigame');
		$this->load->model('Multigame_model');
	}

	/**
    * Function used for get publish collection list
    * @param array
    * @return array
    */   
    public function get_fixture_list_post()
    {    
        $this->form_validation->set_rules('sports_id','Sports ID','trim|required');
        $this->form_validation->set_rules('status','Status','trim|required');
        if(!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

		$post_data = $this->input->post();
        $fixture = $this->Multigame_model->get_fixture_list($post_data);
        //echo "<pre>";print_r($fixture);
        $fixture['match_list'] = array();
       	if(!empty($fixture['result'])){
       		$this->load->model('season/Season_model');
       		$season_ids = array_unique(explode(",",implode(",",array_column($fixture['result'],'season_ids'))));
       		$match_list = $this->Season_model->get_fixture_season_detail($season_ids,"status,status_overview");
       		$fixture['match_list'] = $match_list;
       	}
        $this->api_response_arry['data'] = $fixture;
        $this->api_response();
    }

    /**
	* Used for get sports wise league list
	* @param int $sports_id
	* @return array
	*/    
	public function get_leagues_list_post()
	{	
		$this->form_validation->set_rules('sports_id', 'sports id','trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$result = $this->Multigame_model->get_leagues_list($post_data['sports_id']);
		
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}

	/**
	* Used for get league published fixture list
	* @param int $league_id
	* @return array
	*/
	public function get_published_fixtures_post()
	{
		$this->form_validation->set_rules('league_id', 'League id', 'trim|required|is_natural_no_zero');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$fixtures = $this->Multigame_model->get_published_fixtures($post_data['league_id']);
		$this->api_response_arry['data'] = $fixtures;
		$this->api_response();
	}

	/**
	* Function used for create multigame collection
	* @param array
	* @return array
	*/
	public function create_collection_post()
	{
		$this->form_validation->set_rules('league_id', 'League id', 'trim|required|is_natural_no_zero');
		$this->form_validation->set_rules('collection_name', 'Multigame name', 'trim|required|alpha_numeric_spaces');
		$this->form_validation->set_message('alpha_numeric_spaces', 'Please type characher or number only in multigame name');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		
		$this->load->model('Multigame_model');
		$post_data = $this->input->post();
		$collection_name = trim($post_data['collection_name']);
		$league_id = $post_data['league_id'];
		$season_ids = $post_data['fixture_ids'];
		$cdata = $this->Multigame_model->check_collection($collection_name);
		if(!empty($cdata)){
			$this->api_response_arry['message'] = 'Collection name already exist.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
		}

		if(sizeof($season_ids) < 2) { 

             $allow_intversion = $this->app_config['int_version']['key_value'];
			 if ($allow_intversion == 1 || $allow_intversion == "1") {
				$msg = 'Please select at least 2 Games.';
			 }else{
				$msg = 'Please select at least 2 fixtures.';
			 }

			$this->api_response_arry['message'] = $msg;
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response();
		}

		$match_list = $this->Multigame_model->get_season_detail_by_ids($season_ids);
        if(count($match_list) != count($season_ids)){
            $this->api_response_arry['message'] = "Invalid selected match ids.";
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $result = $this->Multigame_model->check_collection_exist($season_ids);
        if(!empty($result)) {
            $this->api_response_arry['message'] = 'Multigame already exist with selected matches.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else{
        	$match_list = array_column($match_list,"season_scheduled_date","season_id");
            $post_data['season_ids'] = $match_list;
            $collection_master_id = $this->Multigame_model->save_collection_data($post_data);
            if(!$collection_master_id){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line("multigame_save_error");
                $this->api_response();
            }

            $this->api_response_arry['message'] = $this->lang->line("multigame_save_success");
            $this->api_response_arry['data'] = array("collection_master_id"=>$collection_master_id);
            $this->api_response();
        }
	}

    public function get_fixture_league_list_post(){
        $this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $league_list = $this->Multigame_model->get_fixture_league_list($sports_id);
        $this->api_response_arry['data'] = $league_list;
        $this->api_response();
    }

	public function get_contest_filter_post(){
        $this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $this->load->model('common/Common_model');
        $league_list = $this->Multigame_model->get_fixture_league_list($sports_id);
        $group_list = $this->Common_model->get_all_group_list($post_data);
        
        $status_list = array();
        $status_list[] = array("label"=>"Select Status","value"=>"");
        $status_list[] = array("label"=>"Current Contest","value"=>"current_game");
        $status_list[] = array("label"=>"Completed Contest","value"=>"completed_game");
        $status_list[] = array("label"=>"Cancelled Contest","value"=>"cancelled_game");
        $status_list[] = array("label"=>"Upcoming Contest","value"=>"upcoming_game");

        $result = array();
        $result['league_list'] = $league_list;
        $result['group_list'] = $group_list;
        $result['status_list'] = $status_list;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    public function get_league_fixture_post(){
        $this->form_validation->set_rules('league_id', 'league id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $fixture_list = $this->Multigame_model->get_league_fixture_list($post_data['league_id']);
        $this->api_response_arry['data'] = $fixture_list;
        $this->api_response();
    }

    public function get_contest_list_post()
    {
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $data = $this->Multigame_model->get_contest_list($post_data);
        //echo "<pre>";print_r($data);die;
        $data['match_list'] = array();
        if(isset($data['result']) && !empty($data['result'])){
        	$this->load->model('season/Season_model');
       		$season_ids = array_unique(explode(",",implode(",",array_column($data['result'],'season_ids'))));
       		$data['match_list'] = $this->Season_model->get_fixture_season_detail($season_ids);
        }
        $this->api_response_arry['data']= $data;
        $this->api_response();
    }
}
