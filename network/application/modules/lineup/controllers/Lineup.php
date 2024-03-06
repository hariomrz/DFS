<?php 

class Lineup extends Common_Api_Controller {

	private $lineup_lang = array();
	

	public function __construct()
	{
		parent::__construct();
		$this->lineup_lang = $this->lang->line('lineup');
	}

	

	/**
	 * Used for get lineup master data
	 * @param int $sports_id
	 * @param int $league_id
	 * @param int $collection_master_id
	 * @return array
	*/
	public function get_lineup_master_data_post()
	{
		$this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
		$this->form_validation->set_rules('league_id', $this->lang->line('league_id'), 'trim|required');
		$this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response = array();

        $this->load->model("Lineup_model");
        $network_collection = $this->Lineup_model->check_network_collection_master_id($post_data);
        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id']=$network_collection['network_collection_master_id'];
            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/lineup/get_lineup_master_data";
            $api_response =  $this->http_post_request($url,$post_data);

        }    

        //echo "<pre>";print_r($api_response);die;
        $this->network_api_response($api_response);
    }

	/**
	 * Used for get user auto generated team name
	 * @param int $collection_master_id
	 * @return array
	*/
	public function get_user_match_team_data_post()
	{
		$this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data              = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response = array();

        $this->load->model("Lineup_model");
        $network_collection = $this->Lineup_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;
        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id']=$network_collection['network_collection_master_id'];
            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/lineup/get_user_match_team_data";
            $api_response =  $this->http_post_request($url,$post_data);
            //echo "<pre>";print_r($api_response);die;

        }

        //echo "<pre>";print_r($api_response);die;
        $this->network_api_response($api_response);
        
	}

    /**
     * Used for get collection players list
     * @param int $sports_id
     * @param int $league_id
     * @param int $collection_master_id
     * @return array
    */
    public function get_all_roster_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|integer');
        $this->form_validation->set_rules('league_id', $this->lang->line('league_id'), 'trim|required|integer');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data              = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        //echo "<pre>";print_r($post_data);die;
         $api_response = array();

        $this->load->model("Lineup_model");
        $network_collection = $this->Lineup_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;
        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id']=$network_collection['network_collection_master_id'];

            $url = NETWORK_FANTASY_URL."/fantasy/lineup/get_all_roster";
            $api_response =  $this->http_post_request($url,$post_data);

        }
        $this->network_api_response($api_response);
    }


    /**
     * used for save user team
     * @param int $sports_id
     * @param int $league_id
     * @param int $collection_master_id
     * @param array $lineup
     * @return
     */
    public function lineup_proccess_post()
    {   
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('league_id', $this->lang->line('league_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        $this->form_validation->set_rules('team_name', $this->lang->line('team_name'), 'trim|max_length[20]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }


        $post_data                  = $this->input->post();
        $post_data['client_id']     = NETWORK_CLIENT_ID;
        $post_data['user_id']       = $this->user_id;
        $post_data['user_unique_id']= $this->user_unique_id;
        $post_data['user_name']     = $this->user_name;
        $api_response               = array();

        $this->load->model("Lineup_model");
        $network_collection = $this->Lineup_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;
        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id']=$network_collection['network_collection_master_id'];
            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/lineup/lineup_proccess";
            $api_response =  $this->http_post_request($url,$post_data);

        }

        //echo "<pre>";print_r($api_response);die;
        $this->network_api_response($api_response);
        
    }

    /**
     * used for get user team player list
     * @param int $lineup_master_id
     * @param int $collection_master_id
     * @return array
    */
    public function get_user_lineup_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data              = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']= $this->user_unique_id;
        $post_data['user_name']     = $this->user_name;
        $api_response               = array();

        $this->load->model("Lineup_model");
        $network_collection = $this->Lineup_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;
        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id']=$network_collection['network_collection_master_id'];
            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/lineup/get_user_lineup";
            $api_response =  $this->http_post_request($url,$post_data);
        }    
        $this->network_api_response($api_response);
    }

	

}
/* End of file  */
/* Location: ./application/controllers/ */