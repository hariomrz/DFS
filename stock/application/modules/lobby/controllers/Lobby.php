<?php


class Lobby extends Common_Api_Controller 
{

	function __construct()
	{
        parent::__construct();
        
	}

    /**
     * Used for get lobby fixture list
     * @return array
     */
    public function get_lobby_fixture_post()
    {
        $post_data = $this->input->post();
        
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_lobby_fixture_list($post_data);

        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

     /**
     * Used for get fixture(match) contest listing
     * @param int $collection_id
     * @return array
     */
    public function get_fixture_contest_post() 
    {
        //guaranteed_prize
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("lobby/Lobby_model");
        $cm_id = $post_data['collection_id'];
        $user_id = intval($this->user_id);

        $group_cache_key = "st_group_list";
        $group_list = $this->get_cache_data($group_cache_key);
        if(!$group_list){
            $group_list = $this->Lobby_model->get_all_group_list();
            $this->set_cache_data($group_cache_key,$group_list,REDIS_2_HOUR);
        }

        $post_data['h2h_group_id'] = isset($this->app_config['h2h_challenge']['custom_data']['group_id']) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;
        $post_data['rookie_group_id'] = isset($this->app_config['rookie']['custom_data']['group_id']) ? $this->app_config['rookie']['custom_data']['group_id'] : 0;
        $result = $this->Lobby_model->get_fixture_contest($post_data);
        
        $user_data = array("contest"=>array(),"team"=>"0");
        if($user_id != ""){
            $user_cache = "user_ct_".$cm_id."_".$user_id;
            $user_ct_data = $this->get_cache_data($user_cache);
            if(!$user_ct_data){
                $post_data['user_id'] = $user_id;
                $user_contest = $this->Lobby_model->get_user_joined_contest_data($post_data);
                $user_contest = array_column($user_contest,"lm_count","contest_id");

                //user team and contest count
                $teams = $this->Lobby_model->get_single_row('count(lineup_master_id) as total',LINEUP_MASTER,array('collection_id'=>$cm_id,"user_id"=>$user_id));
                $user_data['contest'] = $user_contest;
                $user_data['team'] = isset($teams['total']) ? $teams['total'] : 0;
                $user_ct_data = $user_data;
                $this->set_cache_data($user_cache,$user_ct_data,300);
            }
            $user_data  = $user_ct_data;
        }

        $this->api_response_arry['data']['user_data'] = $user_data;
        $this->api_response_arry['data']['contest'] = $result;
        $this->api_response_arry['data']['group'] = $group_list;
        $this->api_response_arry['data']['total_contest'] = count($result);
        $this->api_response();
    }

    /**
     * Used for get lobby filters
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_filter_post() {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $collection_id = $post_data['collection_id'];
        $lobby_filter_cache = 'st_lobby_filters_'.$collection_id;
        $filter_list = $this->get_cache_data($lobby_filter_cache);
        if(!$filter_list){
            $this->load->model("lobby/Lobby_model");
            $filter_list = $this->Lobby_model->get_lobby_filter_slider_options($post_data);
            $this->set_cache_data($lobby_filter_cache,$filter_list,300);
        }

        $this->api_response_arry['data'] = $filter_list;
        $this->api_response();
    }

     /**
     * Used for get fixture(match) details
     * @param int $collection_id
     * @return array
     */
    public function get_fixture_details_post() {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data['collection_id'];
        
        $fixture_cache_key = "st_fixture_".$collection_id;
        $collection_details = $this->get_cache_data($fixture_cache_key);
        if(!$collection_details){
            $this->load->model("lobby/Lobby_model");
            $collection_details = $this->Lobby_model->get_fixture_details($collection_id);
            $this->set_cache_data($fixture_cache_key,$collection_details,28800);
        }

        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        $collection_details['game_starts_in'] = (strtotime($collection_details['scheduled_date']) - ($deadline_time * 60))*1000;

        $this->api_response_arry['data'] = $collection_details;
        $this->api_response();
    }

    /**
     * Used for get logged in user created team list
     * @param int $collection_id
     * @return array
     */
    public function get_user_lineup_list_post()
    {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data['collection_id'];
        $user_unique_id = $this->user_unique_id;

        $user_teams_cache_key = "st_user_teams_".$collection_id."_".$user_unique_id;
        $user_lineup_list = $this->get_cache_data($user_teams_cache_key);

        if(!$user_lineup_list){
            $user_lineup_list = array();
            $this->load->model("lobby/Lobby_model");

            $collection  = $this->Lobby_model->get_single_row('published_date,end_date,scheduled_date',COLLECTION,array(
                "collection_id" => $collection_id
            ));
            
            $lineup_data = $this->Lobby_model->get_all_user_lineup_list($collection_id);
            
            if(!empty($lineup_data))
            {
                //player team id array
                $c_id_array = array_column($lineup_data, 'c_id');
                $final_player_array = $c_id_array;

                $vc_id_array = array_column($lineup_data, 'vc_id');
                $final_player_array = array_merge($final_player_array,$vc_id_array);


                $post_param_array = array();
                $post_param_array['st_ids'] = array_unique($final_player_array);
                $post_param_array['published_date'] = $collection['published_date'];
                $post_param_array['end_date'] = $collection['end_date'];
                $post_param_array['scheduled_date'] = $collection['scheduled_date'];
                $post_param_array['collection_id'] = $collection_id;
                $player_result = $this->Lobby_model->get_stocks_detail_by_id($post_param_array);
                $stock_details_array = array_column($player_result,NULL,'stock_id');
                //newtwork allow
                $team_count_array = array();
              
                foreach ($lineup_data as $key => $value)
                {
                    if(CAPTAIN_POINT > 0 && !isset($stock_details_array[$value['c_id']])){
                        continue;
                    }

                    $captain_info      = $stock_details_array[$value['c_id']];
                    $value['c_id'] = $value['c_id'];
                   
                    //captain data
                    $value['c_name'] = @$captain_info['stock_name'];
                    $value['last_price'] = @$captain_info['last_price'];
                    $value['current_price'] = @$captain_info['current_price'];
                    $value['price_diff'] = @$captain_info['price_diff'];
                    $value['logo'] = @$captain_info['logo'];
                    unset($value['c_id']);
                   
                    //vc info
                    if(VICE_CAPTAIN_POINT > 0 && !isset($stock_details_array[$value['vc_id']])){
                        continue;
                    }

                    $vice_captain_info      = $stock_details_array[$value['vc_id']];
                    $value['vc_id'] = $value['vc_id'];
                   
                    //captain data
                    $value['vc_name'] = @$vice_captain_info['stock_name'];
                    $value['vc_last_price'] = @$vice_captain_info['last_price'];
                    $value['vc_current_price'] = @$vice_captain_info['current_price'];
                    $value['vc_price_diff'] = @$vice_captain_info['price_diff'];
                    $value['vc_logo'] = @$vice_captain_info['logo'];
                    unset($value['vc_id']);
                   

                    if(!empty($team_count_array) && array_key_exists($value['lineup_master_id'],$team_count_array))
                    {
                        $value['total_joined'] = $value['total_joined'] + $team_count_array[$value['lineup_master_id']];
                    }    

                    $user_lineup_list[] = $value;
                }

            }
            $this->set_cache_data($user_teams_cache_key,$user_lineup_list,REDIS_2_HOUR);
        }

        $this->api_response_arry['data'] = $user_lineup_list;
        $this->api_response();
    }

    function stock_setting_post()
    {
        $post_data = $this->input->post();
        $stock_type = isset($post_data['stock_type']) ? $post_data['stock_type'] : 1;
        if(empty($stock_type)) {
            $stock_type = 1;
        }

        $stock_setting_cache_key = "st_setting_".$stock_type;
        $stock_type_data = $this->get_cache_data($stock_setting_cache_key);
        if(!$stock_type_data)
        { 
            $this->load->model("lobby/Lobby_model");
            $stock_type_data  = $this->Lobby_model->get_single_row('stock_limit,config_data',STOCK_TYPE,array(
                "status" => 1,
                "type" => $stock_type,
                "market_id" => 1
            ));

            if(!empty($stock_type_data))
            {
                $stock_type_data['config_data'] = json_decode($stock_type_data['config_data'],TRUE);
            }

            $stock_type_data['c_point'] = CAPTAIN_POINT;
            $stock_type_data['vc_point'] = VICE_CAPTAIN_POINT;

             $this->set_cache_data($stock_setting_cache_key,$stock_type_data,REDIS_30_DAYS);
        }

        $this->api_response_arry['data'] = $stock_type_data;
        $this->api_response();
    }

}