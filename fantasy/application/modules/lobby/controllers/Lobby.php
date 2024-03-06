<?php
class Lobby extends Common_Api_Controller 
{
	function __construct()
	{
        parent::__construct();
	}

    /**
     * Used for get lobby fixture list
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_fixture_post()
    {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_lobby_fixture_list($sports_id);
        $result_arr = array();
        if(!empty($result)){
            $season_ids = array_unique(explode(",",implode(",",array_column($result,"season_ids"))));
            if($result['0']['is_tour_game'] == 1){
                $match_list = $this->Lobby_model->get_tour_season_detail($season_ids);
            }else{
                $match_list = $this->Lobby_model->get_fixture_season_detail($season_ids);
            }

            //booster module
            $booster_list = array();
            $allow_booster = isset($this->app_config['allow_booster'])?$this->app_config['allow_booster']['key_value']:0;
            if($allow_booster == 1 && in_array($sports_id, array(BASEBALL_SPORTS_ID,NFL_SPORTS_ID,BASKETBALL_SPORTS_ID,SOCCER_SPORTS_ID,CRICKET_SPORTS_ID))){
                $cm_ids = array_column($result,"collection_master_id");
                $this->load->model("booster/Booster_model");
                $booster_list = $this->Booster_model->get_lobby_collection_booster($cm_ids);
                $booster_list = array_column($booster_list,"name","collection_master_id");
            }

            $tournament = $this->Lobby_model->get_season_tournament($season_ids);
            if(!empty($tournament)){
                $tournament = array_column($tournament,NULL,"season_id");
            }

            $result_arr['fixture'] = $result;
            $result_arr['match'] = $match_list;
            $result_arr['booster'] = $booster_list;
            $result_arr['tournament'] = $tournament;
        }
        if(isset($post_data['is_cron_data']) && $post_data['is_cron_data'] == 1){
            $this->push_s3_data_in_queue("lobby_fixture_list_".$sports_id, $result_arr);
        }
        $this->api_response_arry['data'] = $result_arr;
        $this->api_response();
    }

    /**
     * Used for get fixture(collection) details
     * @param int $cm_id
     * @return array
     */
    public function get_fixture_details_post() 
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $cache_key = "fixture_".$cm_id;
        $fixture = $this->get_cache_data($cache_key);
        if(!$fixture){
            $this->load->model("lobby/Lobby_model");
            $fixture = $this->Lobby_model->get_fixture_detail($cm_id);
            if(!empty($fixture)){
                $season_ids = array_unique(explode(",",$fixture['season_ids']));
                if($fixture['is_tour_game'] == 1){
                    $match_list = $this->Lobby_model->get_tour_season_detail($season_ids);
                }else{
                    $match_list = $this->Lobby_model->get_fixture_season_detail($season_ids);
                }
                $fixture['match'] = $match_list;

                //boster module
                $booster_list = array();
                $allow_booster = isset($this->app_config['allow_booster'])?$this->app_config['allow_booster']['key_value']:0;
                if($allow_booster == 1 && in_array($fixture['sports_id'], array(BASEBALL_SPORTS_ID,NFL_SPORTS_ID,BASKETBALL_SPORTS_ID,SOCCER_SPORTS_ID,CRICKET_SPORTS_ID))){
                    $cm_ids[] = $fixture["collection_master_id"];
                    $this->load->model("booster/Booster_model");
                    $booster_list = $this->Booster_model->get_lobby_collection_booster($cm_ids);
                    $booster_list = array_column($booster_list,"name","collection_master_id");
                }
                $fixture['booster'] = $booster_list;

                $fixture['tournament'] = array();
                if(count($season_ids) == 1){
                    $tournament = $this->Lobby_model->get_season_tournament($season_ids);
                    if(!empty($tournament)){
                        $fixture['tournament'] = array_column($tournament,NULL,"season_id");
                    }
                }
                $fixture['2nd_total'] = 0;
                if(isset($fixture['2nd_inning_date']) && $fixture['2nd_inning_date'] != ""){
                    $contest = $this->Lobby_model->get_single_row('count(contest_id) as total',CONTEST,array('collection_master_id'=>$cm_id,"is_2nd_inning"=>"1"));
                    $fixture['2nd_total'] = isset($contest['total']) ? $contest['total'] : 0;
                }
                unset($fixture['setting']);
            }
            $this->set_cache_data($cache_key,$fixture,300);
        }

        $this->api_response_arry['data'] = $fixture;
        $this->api_response();
    }

    /**
     * Used for get fixture(match) contest listing
     * @param int $collection_master_id
     * @return array
     */
    public function get_fixture_contest_post() 
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required|is_natural_no_zero');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("lobby/Lobby_model");
        $cm_id = $post_data['collection_master_id'];
        $is_tournament = isset($post_data['is_trnt']) ? $post_data['is_trnt'] : 0;
        $user_id = intval($this->user_id);
        $group_cache_key = "group_list";
        $group_list = $this->get_cache_data($group_cache_key);
        if(!$group_list){
            $group_list = $this->Lobby_model->get_all_group_list();
            $this->set_cache_data($group_cache_key,$group_list,REDIS_2_HOUR);
        }
        $group_list = array_column($group_list,NULL,"group_id");
        $is_2nd_inning = isset($post_data['is_2nd_inning']) ? $post_data['is_2nd_inning'] : 0;
        $post_data['h2h_group_id'] = isset($this->app_config['h2h_challenge']['custom_data']['group_id']) ? $this->app_config['h2h_challenge']['custom_data']['group_id'] : 0;
        $post_data['rookie_group_id'] = isset($this->app_config['rookie']['custom_data']['group_id']) ? $this->app_config['rookie']['custom_data']['group_id'] : 0;
        $result = $this->Lobby_model->get_fixture_contest($post_data);

        $user_data = array("contest"=>array(),"team"=>"0","2nd_team"=>0);
        if($user_id != ""){
            $user_cache = "user_ct_".$cm_id."_".$user_id."_".$is_2nd_inning;
            $user_ct_data = $this->get_cache_data($user_cache);
            if(!$user_ct_data){
                $post_data['user_id'] = $user_id;
                $user_contest = $this->Lobby_model->get_user_joined_contest_data($post_data);
                $user_contest = array_column($user_contest,"lm_count","contest_id");

                //user team and contest count
                $teams = $this->Lobby_model->get_single_row('SUM(CASE WHEN is_2nd_inning=1 THEN 1 ELSE 0 END) as 2nd_team, SUM(CASE WHEN is_2nd_inning=0 THEN 1 ELSE 0 END) as total',LINEUP_MASTER,array('collection_master_id'=>$cm_id,"user_id"=>$user_id));
                $user_data['contest'] = $user_contest;
                $user_data['team'] = isset($teams['total']) ? $teams['total'] : 0;
                $user_data['2nd_team'] = isset($teams['2nd_team']) ? $teams['2nd_team'] : 0;
                $user_ct_data = $user_data;
                $this->set_cache_data($user_cache,$user_ct_data,300);
            }
            $user_data = $user_ct_data;
        }

        $group_ids = array_fill_keys(array_unique(array_column($result,"group_id")),"0");
        $group_list = array_intersect_key($group_list, $group_ids);

        $tournament = array();
        if($is_tournament == 1){
            $trnt_cache = "fixture_trnt_".$cm_id;
            $tournament = $this->get_cache_data($trnt_cache);
            if(!$tournament){
                $this->load->model("tournament/Tournament_model");
                $tournament = $this->Tournament_model->get_fixture_tournament_list($cm_id);
                $this->set_cache_data($trnt_cache,$tournament,REDIS_24_HOUR);
            }
        }

        $this->api_response_arry['data']['user_data'] = $user_data;
        $this->api_response_arry['data']['contest'] = $result;
        $this->api_response_arry['data']['group'] = $group_list;
        $this->api_response_arry['data']['tournament'] = $tournament;
        $this->api_response_arry['data']['total'] = count($result);
        $this->api_response();
    }
}
