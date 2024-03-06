<?php
class Common extends Common_Api_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * used for get player card details
     * @param array $post_data
     * @return array
    */
    public function get_playercard_post()
    {        
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('league_id', $this->lang->line('league_id'), 'trim|numeric|required');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim');
        $this->form_validation->set_rules('player_uid', $this->lang->line('player_uid'), 'trim|required');
        $this->form_validation->set_rules('player_team_id', $this->lang->line('player_team_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('no_of_match', $this->lang->line('no_of_match'), 'trim|numeric');
        if($this->input->post('sports_id') != GOLF_SPORTS_ID)
        {
            $this->form_validation->set_rules('against_team', $this->lang->line('against_team'), 'trim|required');
        }
        if (!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;

        $this->load->model("Common_model");
        $network_collection = $this->Common_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;
        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['collection_master_id']=$network_collection['network_collection_master_id'];
            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/common/get_playercard";
            $api_response =  $this->http_post_request($url,$post_data);
            
        }        

        $this->network_api_response($api_response);
        
    }


    public function get_shortened_url_post() 
    {
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $url = NETWORK_FANTASY_URL."/fantasy/shorturl/get_shortened_url";
        $api_response =  $this->http_post_request($url,$post_data);
        $this->network_api_response($api_response);    
    }

    public function save_shortened_url_post() {
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $url = NETWORK_FANTASY_URL."/fantasy/shorturl/save_shortened_url";
        $api_response =  $this->http_post_request($url,$post_data);
        $this->network_api_response($api_response);   
    }


    public function get_player_breakdown_post() {
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $url = NETWORK_FANTASY_URL."/fantasy/common/get_player_breakdown";
        $api_response =  $this->http_post_request($url,$post_data);
        $this->network_api_response($api_response);   
    }




}

/* End of file  */
/* Location: ./application/controllers/ */