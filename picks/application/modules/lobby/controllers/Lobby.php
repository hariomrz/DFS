<?php
class Lobby extends Common_Api_Controller 
{
    var $self_exclusion_limit = 0;
	function __construct()
	{
        parent::__construct();
	}

   /**
	* Get Sports List
	* @method Get Sports List
	* @param void
	* @return json array with active sports list
	*/
	public function get_sports_list_post()
	{
		$sports_list_cache_key = 'picks_sports_list';
		$sports_list = $this->get_cache_data($sports_list_cache_key);
		if(!$sports_list){
			$this->load->model('Lobby_model');
			$sports_list = $this->Lobby_model->get_all_table_data('sports_id,name,is_default',SPORTS,['status'=>1]);
			$this->set_cache_data($sports_list_cache_key,$sports_list,REDIS_30_DAYS);
		}
		$this->api_response_arry['data'] = $sports_list;
        $this->api_response();
	}

   /**
	* Get Lobby fixture List 
	* @method get_lobby_fixture
	* @param sports_id
	* @return json array fixture list
	*/
	public function get_lobby_fixture_post()
	{
		$this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $final_result = array();
        $final_result['game_starts_in'] = 0;
        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $lobby_fixture_cache = 'picks_lobby_fixture_list_'.$sports_id;
        $final_result = $this->get_cache_data($lobby_fixture_cache);

        if(!$final_result)
        {
        	$this->load->model("lobby/Lobby_model");
            $final_result = $this->Lobby_model->get_lobby_fixture_list($post_data);
           	$this->set_cache_data($lobby_fixture_cache, $final_result,REDIS_5_MINUTE);
        }

        $this->api_response_arry['data'] = $final_result;
        $this->api_response();
	}

	/**
     * Used for get fixture(match) contest listing
     * @param int $sports_id
     * @param int $season_id
     * @return array
     */
    public function get_fixture_contest_post() 
    {
        //guaranteed_prize
        //$this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $season_id = $post_data['season_id'];
        $user_id = intval($this->user_id);

        $group_cache_key = "picks_group_list";
        $group_list = $this->get_cache_data($group_cache_key);
         $this->load->model("lobby/Lobby_model");
        if(!$group_list){
            $group_list = $this->Lobby_model->get_all_group_list();
            $this->set_cache_data($group_cache_key,$group_list,REDIS_2_HOUR);
        }
        $group_list_arr = array_column($group_list, NULL, "group_id");
        $user_contest = array();
        if($user_id != ""){
            $user_contest = $this->Lobby_model->get_user_joined_contest_data($season_id,$user_id);
        }
        
        $user_contest_ids = array_column($user_contest,"lm_count","contest_id");
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_season_contests($post_data);
        $group_list_data = array();
        $total_contest = 0;

        foreach ($result as $key => $value) 
        {
            if(!isset($user_contest_ids[$value['contest_id']]) || (isset($user_contest_ids[$value['contest_id']]) && $value['multiple_lineup'] > $user_contest_ids[$value['contest_id']])){
                $lm_count = 0;
                if(isset($user_contest_ids[$value['contest_id']])){
                    $lm_count = $user_contest_ids[$value['contest_id']];
                }
                $value['user_joined_count'] = $lm_count;
                if(!isset($group_list_data[$value['group_id']])){
                    $group_list_data[$value['group_id']] = $group_list_arr[$value['group_id']];
                }
                $value['is_confirmed'] = 0;
                if($value['guaranteed_prize'] != 2 && $value['size'] > $value['minimum_size'] && $value['entry_fee'] > 0){
                    $value['is_confirmed'] = 1;
                }
                $group_list_data[$value['group_id']]['contest_list'][] = $value;
                $group_list_data[$value['group_id']]['total'] = count($group_list_data[$value['group_id']]['contest_list']);
                $total_contest++;
            }
        }

        $final_contest_data = array();
        foreach($group_list as $group){
            if(isset($group_list_data[$group['group_id']])){
                $final_contest_data[] = $group_list_data[$group['group_id']];
            }
        }
        
        $season_pin_contest_cache_key = "picks_pin_contest_".$season_id;
         $pin_contest = $this->get_cache_data($season_pin_contest_cache_key);
        if(!$pin_contest){
            $this->load->model("lobby/Lobby_model");
            $post_data['pin_contest'] = 1;
            $pin_contest = $this->Lobby_model->get_season_contests($post_data);
            $this->set_cache_data($season_pin_contest_cache_key,$pin_contest,REDIS_2_HOUR);
        }
        
        $pin_contest_list = array();
        foreach ($pin_contest as $key => $value) 
        {
            if(!isset($user_contest_ids[$value['contest_id']]) || (isset($user_contest_ids[$value['contest_id']]) && $value['multiple_lineup'] > $user_contest_ids[$value['contest_id']])){
                $lm_count = 0;
                if(isset($user_contest_ids[$value['contest_id']])){
                    $lm_count = $user_contest_ids[$value['contest_id']];
                }
                
                if($value["prize_distibution_detail"] == null){
                    $value["prize_distibution_detail"] = array();
                }
                $value['is_confirmed'] = 0;
                if($value['guaranteed_prize'] != 2 && $value['size'] > $value['minimum_size'] && $value['entry_fee'] > 0){
                    $value['is_confirmed'] = 1;
                }
                $pin_contest_list[] = $value;
            }
        }

        $total_contest = $total_contest + count($pin_contest);
        //echo "<pre>";print_r($group_list_data);print_r($result);die;

        $this->api_response_arry['data']['pin_contest'] = $pin_contest_list;
        $this->api_response_arry['data']['contest'] = $final_contest_data;
        $this->api_response_arry['data']['total_contest'] = $total_contest;
        $this->api_response();
    }

     /**
     * Used for get lobby filters
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_filter_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];


        $lobby_filter_cache = 'picks_lobby_filters_'.$sports_id;

        $this->load->model("lobby/Lobby_model");

        $filter_list = $this->get_cache_data($lobby_filter_cache);
        if(!$filter_list){
            
            $filter_list = $this->Lobby_model->get_lobby_filter_slider_options($post_data);
            //get upcoming contest leagues
            $filter_list['league_list'] = $this->Lobby_model->get_lobby_filter_leagues($post_data);

            
            $this->set_cache_data($lobby_filter_cache,$filter_list,300);
        }

        $this->api_response_arry['data'] = $filter_list;
        $this->api_response();
    }

     /**
     * Used for get logged in user created team list
     * @param int $collection_master_id
     * @return array
     */
    public function get_user_lineup_list_post()
    {
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        $user_unique_id = $this->user_unique_id;

        $user_teams_cache_key = "picks_user_teams_".$season_id."_".$user_unique_id;
        $user_lineup_list = $this->get_cache_data($user_teams_cache_key);
         
        if(!$user_lineup_list){
            $user_lineup_list = array();
            $this->load->model("lobby/Lobby_model");
            $user_lineup_list = $this->Lobby_model->get_all_user_lineup_list($season_id);
   
            $this->set_cache_data($user_teams_cache_key,$user_lineup_list,REDIS_2_HOUR);
        }

        $this->api_response_arry['data'] = $user_lineup_list;
        $this->api_response();
    }

    /**
     * Used for get fixture(match) details
     * @param int $sports_id
     * @param int $collection_master_id
     * @return array
     */
    public function get_fixture_details_post() 
    {
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $season_id = $post_data['season_id'];
        
        $fixture_cache_key = "picks_fixture_".$season_id;
        $season_details = $this->get_cache_data($fixture_cache_key);
        if(!$season_details){
            $this->load->model("lobby/Lobby_model");
            $season_details = $this->Lobby_model->get_fixture_season_details($season_id);
            $this->set_cache_data($fixture_cache_key,$season_details,300);
        }

        $this->api_response_arry['data'] = $season_details;
        $this->api_response();
    }
}