<?php


class Lobby extends Common_Api_Controller 
{

	function __construct()
	{
        parent::__construct();
        
	}


    /**
     * Used for get fixture(match) details
     * @param int $sports_id
     * @param int $collection_master_id
     * @return array
     */
    public function get_fixture_details_post() 
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $api_response = array();
        $this->load->model("Lobby_model");
        $network_collection = $this->Lobby_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id'] = $network_collection['network_collection_master_id'];
            $url = NETWORK_FANTASY_URL."/fantasy/lobby/get_fixture_details";
            //echo "<pre>";print_r($post_data);die;

            $api_response =  $this->http_post_request($url,$post_data);

        }  

        //echo "<pre>";print_r($api_response);die;
        $this->network_api_response($api_response);
    }


    /**
     * Used for get fixture(match) contest listing
     * @param int $sports_id
     * @param int $collection_master_id
     * @return array
     */
    public function get_fixture_contest_post() 
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data    = $this->input->post();
        $sports_id    = $post_data['sports_id']; 
        $api_response = array(); 
        $this->load->model("Lobby_model");
        $get_published_contests = $this->Lobby_model->get_collection_network_contests($post_data);
        if(!empty($get_published_contests))
        {
            $get_published_contests['sports_id'] = $sports_id; 
            //echo "<pre>";print_r($get_published_contests);die;
            $get_published_contests['client_id'] = NETWORK_CLIENT_ID; 
            $url = NETWORK_FANTASY_URL."/fantasy/lobby/get_fixture_contest";
            $api_response =  $this->http_post_request($url,$get_published_contests);
        }

        $this->network_api_response($api_response);
        
    }

    /**
     * Used for get logged in user created team list
     * @param int $collection_master_id
     * @return array
     */
    public function get_user_lineup_list_post()
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $api_response = array();
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $this->load->model("Lobby_model");
        $network_collection = $this->Lobby_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id'] = $network_collection['network_collection_master_id'];
            $url = NETWORK_FANTASY_URL."/fantasy/lobby/get_user_lineup_list";
            //echo "<pre>";print_r($post_data);die;

            $api_response =  $this->http_post_request($url,$post_data);

        }    
        //echo "<pre>";print_r($api_response);die;
        $this->network_api_response($api_response);
        
    }

    
}