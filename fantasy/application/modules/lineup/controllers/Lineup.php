<?php 
class Lineup extends Common_Api_Controller {
	private $lineup_lang = array();
	public $salary_cap = SALARY_CAP;
	public $sports_id = CRICKET_SPORTS_ID;
	public $lineup_team_limit = 6;
	public $global_team_player_count = 22;
	public $max_player_per_team = 7;

	public function __construct()
	{
		parent::__construct();
		$this->lineup_lang = $this->lang->line('lineup');
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

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $sports_id = isset($post_data['sports_id']) ? $post_data['sports_id'] : 7;
        $user_id = $this->user_id;
        $teams_cache_key = "user_teams_".$cm_id."_".$user_id;
        $lineup_list = $this->get_cache_data($teams_cache_key);
        if(!$lineup_list){
            $this->load->model("lineup/Lineup_model");
            $lineup_list = $this->Lineup_model->get_user_lineup_list($cm_id);
            //echo "<pre>";print_r($lineup_list);die;
            if(!empty($lineup_list)){
                $roster_cache_key = "roster_list_".$cm_id;
                $roster_list = $this->get_cache_data($roster_cache_key);
                if(!$roster_list)
                {
                    $roster_list = $this->Lineup_model->get_fixture_rosters($cm_id);
                    //set collection team in cache for 2 hours
                    $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
                }
                $roster_list = array_column($roster_list,NULL,"player_team_id");
                $roster_position = array_column($roster_list,"position","player_team_id");
                $roster_team = array_column($roster_list,"team_abbr","player_team_id");
                $roster_name = array_column($roster_list,"display_name","player_team_id");
                foreach($lineup_list as &$row){
                    $other_pl = array();
                    $row['team_data'] = json_decode($row['team_data'],TRUE);
                    $c_id = $row['team_data']['c_id'];
                    $vc_id = $row['team_data']['vc_id'];
                    $pl_array = $row['team_data']['pl'];

                    $pl_fill_arr = array_fill_keys($pl_array,"0");
                    $pos_arr = array_intersect_key($roster_position,$pl_fill_arr);
                    $row['position'] = array_count_values($pos_arr);
                    $row['team'] = array_count_values(array_intersect_key($roster_team,$pl_fill_arr));

                    if($c_id != "" && $sports_id == MOTORSPORT_SPORTS_ID){
                        $pos_arr_cr = array_flip($pos_arr);
                        $vc_id = isset($pos_arr_cr['CR']) ? $pos_arr_cr['CR'] : "";
                    }

                    //return other player if c or vc not available
                    if($c_id == "" || $vc_id == ""){
                        $tmp_pl = array_flip($pl_array);
                        unset($tmp_pl[$c_id]);
                        unset($tmp_pl[$vc_id]);
                        $other_pl = array_values(array_intersect_key($roster_name,$tmp_pl));
                    }
                    $row['other_pl'] = $other_pl;
                    $row['c_data'] = $row['vc_data'] = array();
                    if(isset($roster_list[$c_id])){
                        $cdata = $roster_list[$c_id];
                        $row['c_data'] = array("name"=>$cdata['display_name'],"team"=>$cdata['team_abbr'],"position"=>$cdata['position'],"jersey"=>$cdata['jersey']);
                    }
                    if(isset($roster_list[$vc_id])){
                        $vcdata = $roster_list[$vc_id];
                        $row['vc_data'] = array("name"=>$vcdata['display_name'],"team"=>$vcdata['team_abbr'],"position"=>$vcdata['position'],"jersey"=>$vcdata['jersey']);
                    }
                    unset($row['team_data']);
                }
            }
            $this->set_cache_data($teams_cache_key,$lineup_list,REDIS_2_HOUR);
        }

        $this->api_response_arry['data'] = $lineup_list;
        $this->api_response();
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
		$this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        $is_2nd_inning = isset($post_data['is_2nd_inning']) ? $post_data['is_2nd_inning'] : 0;
        $this->load->model("lineup/Lineup_model");
		$fixture = $this->Lineup_model->get_fixture_detail($cm_id);
		if(empty($fixture)){
    		$this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
    	}else if($is_2nd_inning == "0" && strtotime($current_date) >= strtotime($fixture['season_scheduled_date'])){
    		$this->api_response_arry['message'] = $this->lineup_lang['contest_started'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
    	}else if($is_2nd_inning == "1" && strtotime($current_date) >= strtotime($fixture['2nd_inning_date'])){
            $this->api_response_arry['message'] = $this->lineup_lang['contest_started'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

    	$lm_data_key = "lineup_master_data_".$cm_id;
    	$lm_data = $this->get_cache_data($lm_data_key);
        if(!$lm_data){
	        $position_list = $this->get_position_list($fixture['sports_id']);
	        $sports_list = $this->get_sports_list();
	        $sports_list = array_column($sports_list,NULL,"sports_id");
			$player_limit = isset($sports_list[$fixture['sports_id']]['team_player_count']) ? $sports_list[$fixture['sports_id']]['team_player_count'] : 11;
			$team_player_limit = isset($sports_list[$fixture['sports_id']]['max_player_per_team']) ? $sports_list[$fixture['sports_id']]['max_player_per_team'] : 7;

			//get teams list
	        $this->load->model("lineup/Lineup_model");
            if($fixture['is_tour_game'] == 1){
                $season_ids = array();
                if($fixture['sports_id'] == TENNIS_SPORTS_ID){
                    $season_ids = explode(",",$fixture['season_ids']);
                }
                $team_list = $this->Lineup_model->get_tour_sport_teams($fixture['sports_id'],$season_ids);
            }else{
               $team_list = $this->Lineup_model->get_fixture_teams($cm_id);
            }

	        $lm_data = array();
			$lm_data['salary_cap'] = $this->salary_cap;
			$lm_data["team_player_count"] = $player_limit;
			$lm_data["max_player_per_team"] = $team_player_limit;
			$lm_data['all_position'] = $position_list;
			$lm_data["teams"] = $team_list;
            $lm_data['c_point'] = CAPTAIN_POINT;
            $lm_data['vc_point'] = VICE_CAPTAIN_POINT;

            //dynamic team setting
            $setting = json_decode($fixture['setting'],TRUE);
            if(!empty($setting)){
                if($setting['c'] == "0"){
                    $lm_data['c_point'] = "0";
                }
                if($setting['vc'] == "0"){
                    $lm_data['vc_point'] = "0";
                }
                $lm_data["team_player_count"] = $setting['team_player_count'];
                $lm_data["max_player_per_team"] = $setting['max_player_per_team'];
                foreach($lm_data['all_position'] as &$pos_row){
                    $pos_key = strtolower($pos_row['position']);
                    if(isset($setting['pos'][$pos_key.'_min'])){
                        $pos_row['number_of_players'] = $setting['pos'][$pos_key.'_min'];
                    }
                    if(isset($setting['pos'][$pos_key.'_max'])){
                        $pos_row['max_player_per_position'] = $setting['pos'][$pos_key.'_max'];
                    }
                }
            }
            if(in_array($fixture['sports_id'],[MOTORSPORT_SPORTS_ID,TENNIS_SPORTS_ID])){
                $lm_data['vc_point'] = 0;
            }
			$this->set_cache_data($lm_data_key, $lm_data, REDIS_2_HOUR);

		}
		//for upload lineup data on s3 bucket
        $this->push_s3_data_in_queue("lineup_master_data_".$cm_id,$lm_data);

		$this->api_response_arry['data'] = $lm_data;
		$this->api_response();
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

		$post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
		$this->load->model("lineup/Lineup_model");
		$team = $this->Lineup_model->get_single_row("COUNT(lineup_master_id) as total", LINEUP_MASTER,array("collection_master_id" => $cm_id,"user_id"=>$this->user_id));
		$team_name = "Team 1";
		if(!empty($team))
		{
			$team_name = "Team ".($team['total'] + 1);
		}
		$team_data = array("team_name"=>$team_name);
		$this->api_response_arry['data'] = $team_data;
		$this->api_response();
	}

    /**
     * Used for get fixture(collection) roster list
     * @param int $cm_id
     * @return array
    */
    public function get_all_roster_post()
    {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $cm_id = $post_data['collection_master_id'];
        $roster_cache_key = "roster_list_".$cm_id;
        $roster_list = $this->get_cache_data($roster_cache_key);
        if(!$roster_list)
        {
            $this->load->model("lineup/Lineup_model");
            $roster_list = $this->Lineup_model->get_fixture_rosters($cm_id);
            //set collection team in cache for 2 hours
            $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
        }
        //for upload lineup data on s3 bucket
        $this->push_s3_data_in_queue("collection_roster_list_".$cm_id,$roster_list);

        $this->api_response_arry['data'] = $roster_list;
        $this->api_response();
    }

    /**
     * Used for validate user selected team data
     * @param int $league_id
     * @param array $lineup
     * @param array $position_array
     * @return
    */
    private function validate_user_team($post_data)
    {
        $cm_id = $post_data['collection_master_id'];
        $team_name = $post_data['team_name'];
        $player_ids = $post_data['players'];
        $c_id = isset($post_data['c_id']) ? $post_data['c_id'] : "";
        $vc_id = isset($post_data['vc_id']) ? $post_data['vc_id'] : "";
        $pl_arr = array_fill_keys($player_ids,"0");
        $fixture_data = $post_data['fixture_data'];
        //echo "<pre>";print_r($fixture_data);die;
        $c_point = CAPTAIN_POINT;
        $vc_point = VICE_CAPTAIN_POINT;
        $sports_list = $this->get_sports_list();
        $sports_list = array_column($sports_list,NULL,"sports_id");
        $player_limit = isset($sports_list[$fixture_data['sports_id']]['team_player_count']) ? $sports_list[$fixture_data['sports_id']]['team_player_count'] : 11;
        $team_player_limit = isset($sports_list[$fixture_data['sports_id']]['max_player_per_team']) ? $sports_list[$fixture_data['sports_id']]['max_player_per_team'] : 7;

        //dynamic team setting
        $setting = json_decode($fixture_data['setting'],TRUE);
        if(!empty($setting)){
            if($setting['c'] == "0"){
                $c_point = "0";
            }
            if($setting['vc'] == "0"){
                $vc_point = "0";
            }
            $player_limit = $setting['team_player_count'];
            $team_player_limit = $setting['max_player_per_team'];
        }
        if(in_array($fixture_data['sports_id'],[MOTORSPORT_SPORTS_ID,TENNIS_SPORTS_ID])){
            $vc_point = 0;
        }
        

        //check captain id
        if($c_point > 0 && !in_array($c_id,$player_ids)){
            $this->api_response_arry['message'] = $this->lineup_lang['lineup_captain_error'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        //check vice-captain id
        if($vc_point > 0 && !in_array($vc_id,$player_ids)){
            $this->api_response_arry['message'] = $this->lineup_lang['lineup_vice_captain_error'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        
        //total lineup player limit validation
        if(count($player_ids) != $player_limit){
            $this->api_response_arry['message'] = str_replace("{player_limit}",$player_limit,$this->lang->line('lineup_player_limit'));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        //fixture roster
        $roster_cache_key = "roster_list_".$cm_id;
        $roster_list = $this->get_cache_data($roster_cache_key);
        if(!$roster_list)
        {
            $roster_list = $this->Lineup_model->get_fixture_rosters($cm_id);
            $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
        }
        
        $roster_list = array_column($roster_list,NULL,"player_team_id");
        $match_player_ids = array_keys($roster_list);
        $final_players_ids = array_intersect($player_ids,$match_player_ids);
        if(empty($final_players_ids) || count($final_players_ids) != count($player_ids)){
            $this->api_response_arry['message'] = $this->lineup_lang['invalid_collection_player'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        //max player per team limit check
        $roster_team = array_column($roster_list,"team_abbr","player_team_id");
        $team_player = array_count_values(array_intersect_key($roster_team,$pl_arr));
        if(max($team_player) > $team_player_limit){
            $this->api_response_arry['message'] = str_replace("{team_player_limit}",$team_player_limit,$this->lang->line('lineup_team_limit_exceeded'));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        
        //total salary check
        $roster_salary = array_column($roster_list,"salary","player_team_id");
        $total_salary = array_sum(array_intersect_key($roster_salary,$pl_arr));
        if($total_salary > $this->salary_cap){
            $this->api_response_arry['message'] = $this->lineup_lang['salary_cap_not_enough'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        //max allowed players per position check
        $roster_position = array_column($roster_list,"position","player_team_id");
        $pos_player = array_count_values(array_intersect_key($roster_position,$pl_arr));
        $position_list = $this->get_position_list($fixture_data['sports_id']);

        foreach($position_list as $row){
            $pos_abbr = $row['position_name'];
            //dynamic position limit setting
            if(!empty($setting)){
                $pos_key = strtolower($pos_abbr);
                if(isset($setting['pos'][$pos_key.'_min'])){
                    $row['number_of_players'] = $setting['pos'][$pos_key.'_min'];
                }
                if(isset($setting['pos'][$pos_key.'_max'])){
                    $row['max_player_per_position'] = $setting['pos'][$pos_key.'_max'];
                }
            }
            if(!isset($pos_player[$pos_abbr]) || $pos_player[$pos_abbr] < $row['number_of_players'] || $pos_player[$pos_abbr] > $row['max_player_per_position']){
                $this->api_response_arry['message'] = $this->lineup_lang['position_exceeded_invalid'];
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response();
            }
        }
        
        //duplicate team and team name check
        $team_list = $this->Lineup_model->get_all_table_data("lineup_master_id,team_name,team_data",LINEUP_MASTER,array("collection_master_id"=>$cm_id,"user_id"=>$this->user_id));
        if(!empty($team_list)){
            $lm_id = isset($post_data['lineup_master_id']) ? $post_data['lineup_master_id'] : "";
            //allowed team limit check
            if($lm_id == "" && count($team_list) >= ALLOWED_USER_TEAM){
                $this->api_response_arry['message'] = str_replace("{team_limit}",ALLOWED_USER_TEAM,$this->lineup_lang["allow_team_limit_error"]);
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response();
            }
            $team_data = array_column($team_list,NULL,"lineup_master_id");
            $team_names = array_column($team_list,"team_name","lineup_master_id");
            if($lm_id != ""){
                if(!in_array($lm_id,array_keys($team_data))){
                    $this->api_response_arry['message'] = $this->lineup_lang['lineup_not_exist'];
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response();
                }
                unset($team_names[$lm_id]);
            }
            if(in_array($team_name,$team_names)){
                $this->api_response_arry['message'] = $this->lineup_lang['team_name_already_exist'];
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response();
            }
            if(ALLOW_DUPLICATE_TEAM != 1){
                sort($player_ids);
                $pl_str = implode("_",$player_ids)."_c".$post_data['c_id']."_vc".$post_data['vc_id'];
                foreach($team_list as $team){
                    if($lm_id != "" && $lm_id == $team['lineup_master_id']){
                        continue;
                    }
                    $team['team_data'] = json_decode($team['team_data'],TRUE);
                    sort($team['team_data']['pl']);
                    $team_pl_str = implode("_",$team['team_data']['pl'])."_c".$team['team_data']['c_id']."_vc".$team['team_data']['vc_id'];
                    if($pl_str == $team_pl_str){
                        $this->api_response_arry['message'] = $this->lineup_lang["already_created_same_team"];
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response();
                    }
                }
            }
        }
    }

    /**
     * used for save user team
     * @param int $sports_id
     * @param int $league_id
     * @param int $collection_master_id
     * @param array $lineup
     * @return
     */
    public function save_team_post()
    {   
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        $this->form_validation->set_rules('team_name', $this->lang->line('team_name'), 'trim|required|max_length[20]');
        $this->form_validation->set_rules('players', $this->lang->line('players'), 'callback_validate_players');
        //$this->form_validation->set_rules('c_id', $this->lang->line('captain'), 'trim|required|max_length[20]');
        //$this->form_validation->set_rules('vc_id', $this->lang->line('vice_captain'), 'trim|required|max_length[20]');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        $player_ids = $post_data['players'];
        $c_id = isset($post_data['c_id']) ? $post_data['c_id'] : "";
        $vc_id = isset($post_data['vc_id']) ? $post_data['vc_id'] : "";
        $lm_id = isset($post_data['lineup_master_id']) ? $post_data['lineup_master_id'] : "";
        $is_pl_team = isset($post_data['is_pl_team']) ? $post_data['is_pl_team'] : "0";
        $is_2nd_inning = isset($post_data['is_2nd_inning']) ? $post_data['is_2nd_inning'] : "0";
        if($c_id != "" && $vc_id != "" && trim($c_id) == trim($vc_id)){
            $this->api_response_arry['message'] = $this->lineup_lang['c_vc_same_error'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        if($this->user_name == ""){
            $this->api_response_arry['message'] = $this->lineup_lang['username_empty_error'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $this->load->model("lineup/Lineup_model");
        $fixture_data = $this->Lineup_model->get_fixture_detail($cm_id);
        if(empty($fixture_data)){
            $this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if($is_2nd_inning == 0 && strtotime($current_date) >= strtotime($fixture_data['season_scheduled_date'])){
            $this->api_response_arry['message'] = $this->lang->line('match_started_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if($is_2nd_inning == 1 && strtotime($current_date) >= strtotime($fixture_data['2nd_inning_date'])){
            $this->api_response_arry['message'] = $this->lang->line('match_started_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $post_data['fixture_data'] = $fixture_data;
        $this->validate_user_team($post_data);

        //save team data
        $team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_ids);
        $team_name = trim($post_data['team_name']);
        if($lm_id != ""){
            $team_arr = array();
            $team_arr['team_name'] = $team_name;
            $team_arr['team_data'] = json_encode($team_data);
            $team_arr['date_modified'] = $current_date;
            $result = $this->Lineup_model->update(LINEUP_MASTER,$team_arr,array('lineup_master_id'=>$lm_id));
        }else{
            $team_arr = array();
            $team_arr['collection_master_id'] = $fixture_data['collection_master_id'];
            $team_arr['league_id'] = $fixture_data['league_id'];
            $team_arr['user_id'] = $this->user_id;
            $team_arr['user_name'] = $this->user_name;
            $team_arr['team_name'] = $team_name;
            $team_arr['team_data'] = json_encode($team_data);
            $team_arr['is_pl_team'] = $is_pl_team;
            $team_arr['is_2nd_inning'] = $is_2nd_inning;
            $team_arr['date_added'] = $current_date;
            $team_arr['date_modified'] = $current_date;
            $result = $this->Lineup_model->save_record(LINEUP_MASTER,$team_arr);
            $lm_id = $result;
        }
        
        if($result){
            //remove user team cache
            $teams_cache_key = "user_teams_".$cm_id."_".$this->user_id;
            $this->delete_cache_data($teams_cache_key);

            //remove user team cache
            $user_ct_cache = "user_ct_".$cm_id."_".$this->user_id."_".$is_2nd_inning;
            $this->delete_cache_data($user_ct_cache);

            $this->api_response_arry['message'] = $this->lang->line('team_save_success');
            $this->api_response_arry['data'] = array("lineup_master_id"=>$lm_id);
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = $this->lang->line('team_save_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

    function validate_players()
    {
        $lineup = $this->input->post("players");
        $msg = "";
        if(empty($lineup))
        {
            $msg = $this->lang->line("lineup_required") ;
        }

        if(!empty($msg))
        {
            $this->form_validation->set_message('validate_players', $msg);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * used for get user team player list
     * @param int $lineup_master_id
     * @param int $collection_master_id
     * @return array
    */
    public function get_team_detail_post()
    {
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $lm_id = $post_data['lineup_master_id'];
        $lmc_id = isset($post_data['lineup_master_contest_id']) ? $post_data['lineup_master_contest_id'] : "";
        $this->load->model("lineup/Lineup_model");
        $team_info = $this->Lineup_model->get_team_detail($lm_id,$lmc_id);
        if(empty($team_info)){
            $this->api_response_arry['message'] = $this->lang->line('team_detail_not_found');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if(strtotime($current_date) < strtotime($team_info['season_scheduled_date']) && $team_info['user_id'] != $this->user_id){
            $this->api_response_arry['message'] = $this->lang->line('team_view_not_allowed');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $cache_key = "user_team_".$lm_id."_".$lmc_id;
        $user_team = $this->get_cache_data($cache_key);
        if(!$user_team) 
        {
            $team_info['team_data'] = json_decode($team_info['team_data'],TRUE);
            $team_info = $this->Lineup_model->get_user_team_with_score($team_info);
            
            $roster_cache_key = "roster_list_".$team_info['collection_master_id'];
            $roster_list = $this->get_cache_data($roster_cache_key);
            if(!$roster_list)
            {
                $roster_list = $this->Lineup_model->get_fixture_rosters($team_info['collection_master_id']);
                //set collection team in cache for 2 hours
                $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
            }

            $roster_list = array_column($roster_list,NULL,"player_team_id");
            $roster_position = array_column($roster_list,"position","player_team_id");
            $roster_team = array_column($roster_list,"team_abbr","player_team_id");

            $pl_arr = $team_info['team_data']['pl'];
            $team_info['position'] = array_count_values(array_intersect_key($roster_position,$pl_arr));
            $team_info['team'] = array_count_values(array_intersect_key($roster_team,$pl_arr));

            $allow_bench = isset($this->app_config['bench_player']) ? $this->app_config['bench_player']['key_value'] : 0;
            $bench_players = array();
            $bench_pl_ids = array();
            if($allow_bench == "1"){
                $pl_list = $this->Lineup_model->get_all_table_data("*",BENCH_PLAYER,array("lineup_master_id"=>$lm_id),array("priority"=>"ASC"));
                //player_team_id stored in player_id column
                $bench_pl_ids = array_column($pl_list,"player_id");
                foreach($pl_list as $row){
                    $player_id = $row['player_id'];
                    if($row['status'] == "1" && $row['out_player_id'] != "0"){
                        $player_id = $row['out_player_id'];
                    }
                    $player_info = $roster_list[$player_id];
                    if(!empty($player_info)){
                        $tmp_arr = array();
                        $tmp_arr['player_id'] = $player_info['player_id'];
                        $tmp_arr['player_team_id'] = $player_info['player_team_id'];
                        $tmp_arr['team_id'] = $player_info['team_id'];
                        $tmp_arr['team_uid'] = $player_info['team_uid'];
                        $tmp_arr['full_name'] = $player_info['full_name'];
                        $tmp_arr['display_name'] = $player_info['display_name'];
                        $tmp_arr['jersey'] = $player_info['jersey'];
                        $tmp_arr['team_abbr'] = $player_info['team_abbr'];
                        $tmp_arr['position'] = $player_info['position'];
                        $tmp_arr['salary'] = $player_info['salary'];
                        $tmp_arr['is_playing'] = $player_info['is_playing'];
                        $tmp_arr['is_sub'] = $player_info['is_sub'];
                        $tmp_arr['priority'] = $row['priority'];
                        $tmp_arr['status'] = $row['status'];
                        $tmp_arr['reason'] = $row['reason'];
                        $tmp_arr['player_in_id'] = ($row['status'] == "1") ? $row['player_id'] : "0";
                        $bench_players[] = $tmp_arr;
                    }
                }
            }

            $final_player_list = array();
            $playing_announce = 0;
            if(!empty($team_info['team_data']['pl'])){
                foreach($team_info['team_data']['pl'] as $player_team_id=>$score) {
                    $player_info = $roster_list[$player_team_id];
                    if(!empty($player_info)){
                        if(isset($player_info['playing_announce']) && $player_info['playing_announce'] == "1"){
                            $playing_announce = $player_info['playing_announce'];
                        }
                        $captain = 0;
                        if($player_team_id == $team_info['team_data']['c_id']){
                            $captain = 1;
                        }else if($player_team_id == $team_info['team_data']['vc_id']){
                            $captain = 2;
                        }
                        $sub_in = 0;
                        if(!empty($bench_pl_ids) && in_array($player_team_id,$bench_pl_ids)){
                            $sub_in = 1;
                        }
                        $lineup = array();
                        $lineup['player_id'] = $player_info['player_id'];
                        $lineup['player_uid'] = $player_info['player_uid'];
                        $lineup['player_team_id'] = $player_team_id;
                        $lineup['team_id'] = $player_info['team_id'];
                        $lineup['team_uid'] = $player_info['team_uid'];
                        $lineup['full_name'] = $player_info['full_name'];
                        $lineup['display_name'] = $player_info['display_name'];
                        $lineup['jersey'] = $player_info['jersey'];
                        $lineup['team_abbr'] = $player_info['team_abbr'];
                        $lineup['position'] = $player_info['position'];
                        $lineup['salary'] = $player_info['salary'];
                        $lineup['captain'] = $captain;
                        $lineup['is_playing'] = $player_info['is_playing'];
                        $lineup['is_sub'] = $player_info['is_sub'];
                        $lineup['score'] = $score;
                        $lineup['fantasy_score'] = $player_info['fantasy_score'];
                        $lineup['sub_in'] = $sub_in;
                        $final_player_list[] = $lineup;
                    }
                }
            }
            $team_info['status'] = 0;
            if(strtotime($current_date) >= strtotime($team_info['season_scheduled_date'])){
                $team_info['status'] = 1;
            }
            $position_list = $this->get_position_list($team_info['sports_id']);
            $team_info['pos_list'] = array_column($position_list,"position_display_name","position_name");
            $team_info['lineup'] = $final_player_list;
            $team_info['playing_announce'] = $playing_announce;
            unset($team_info['team_data']);
            unset($team_info['is_lineup_processed']);
            unset($team_info['season_scheduled_date']);
            unset($team_info['user_id']);
            //echo "<pre>";print_r($team_info);die;

            $team_info['bench'] = $bench_players;
            $booster = array();
            if(isset($team_info["booster_id"]) && $team_info["booster_id"] > 0){
                $this->load->model("booster/Booster_model");
                $booster = $this->Booster_model->get_match_booster_detail($team_info['collection_master_id'],[$team_info["booster_id"]]);
                if(!empty($booster)){
                    $booster = array_column($booster,NULL,"booster_id");
                    $booster = $booster[$team_info["booster_id"]];
                    unset($booster['booster_id']);
                    unset($booster['points']);
                    $booster['score'] = isset($team_info["booster_points"]) ? $team_info["booster_points"] : '0.00';
                }
            }
            $team_info['booster'] = $booster;
            $user_team = $team_info;
            if($user_team['cm_status'] > 0){
                $this->set_cache_data($cache_key, $user_team, REDIS_2_HOUR);
            }
        }
        
        //echo "<pre>";print_r($user_team);die;
        $this->api_response_arry['data'] = $user_team;
        $this->api_response();
    }

    /**
    * Used for validate bench players
    * @param array
    * @return boolean
    */
    function validate_bench_players()
    {
        $post_data = $this->input->post();
        $msg = "";
        if($post_data['edit_bench'] != "1" && empty($post_data['players']))
        {
            $msg = $this->lang->line("lineup_required") ;
        }

        if(!empty($msg))
        {
            $this->form_validation->set_message('validate_players', $msg);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * used for save team bench players
     * @param int $sports_id
     * @param int $league_id
     * @param int $collection_master_id
     * @param array $lineup
     * @return
     */
    public function save_bench_player_post()
    {   
        $allow_bench = isset($this->app_config['bench_player'])?$this->app_config['bench_player']['key_value']:0;
        if($allow_bench != '1')
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('module_not_activated');
            $this->api_response();
        }

        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('players', $this->lang->line('players'), 'callback_validate_bench_players');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $lm_id = $post_data['lineup_master_id'];
        $player_team_ids = $post_data['players'];
        if(count($player_team_ids) > 4){
            $this->api_response_arry['message'] = $this->lang->line('max_bench_limit_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $this->load->model("lineup/Lineup_model");
        $team_info = $this->Lineup_model->get_single_row("*",LINEUP_MASTER,array("lineup_master_id" => $lm_id,"user_id" => $this->user_id));
        if(empty($team_info)){
            $this->api_response_arry['message'] = $this->lang->line('team_detail_not_found');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $cm_id = $team_info['collection_master_id'];
        $team_data = json_decode($team_info['team_data'],TRUE);
        $common_pl_ids = array_intersect($player_team_ids,$team_data['pl']);
        if(!empty($common_pl_ids) || count($common_pl_ids) > 0){
            $this->api_response_arry['message'] = $this->lang->line('bench_player_team_pl_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        //fixture roster
        $roster_cache_key = "roster_list_".$cm_id;
        $roster_list = $this->get_cache_data($roster_cache_key);
        if(!$roster_list)
        {
            $roster_list = $this->Lineup_model->get_fixture_rosters($cm_id);
            $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
        }

        $match_player_ids = array_column($roster_list,"player_team_id");
        $final_players_ids = array_intersect($player_team_ids,$match_player_ids);
        if(empty($final_players_ids) || count($final_players_ids) != count($player_team_ids)){
            $this->api_response_arry['message'] = $this->lang->line('bench_player_team_pl_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        //match start check
        $fixture_data = $this->Lineup_model->get_single_row("collection_master_id,season_scheduled_date", COLLECTION_MASTER,array("collection_master_id" => $cm_id));
        if(empty($fixture_data)){
            $this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if(strtotime($current_date) >= strtotime($fixture_data['season_scheduled_date'])){
            $this->api_response_arry['message'] = $this->lang->line('match_started_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $bench_players = $this->Lineup_model->get_single_row("GROUP_CONCAT(player_id) as ids",BENCH_PLAYER,array("lineup_master_id" => $lm_id));
        $bench_pl_ids = array();
        if(isset($bench_players['ids']) && $bench_players['ids'] != ""){
            $bench_pl_ids = explode(",",$bench_players['ids']);
        }

        $save_result = 1;
        $i = 1;
        foreach($player_team_ids as $player_id){
            if(!empty($bench_pl_ids) && in_array($player_id, $bench_pl_ids)){
                $data_arr = array();
                $data_arr['priority'] = $i;
                $data_arr['date_modified'] = $current_date;
                $result = $this->Lineup_model->update(BENCH_PLAYER,$data_arr,array("lineup_master_id"=>$lm_id,"player_id"=>$player_id));
                if(!$result){
                    $save_result = 0;
                }
            }else{
                $data_arr = array();
                $data_arr['lineup_master_id'] = $lm_id;
                $data_arr['priority'] = $i;
                $data_arr['player_id'] = $player_id;
                $data_arr['date_created'] = $current_date;
                $data_arr['date_modified'] = $current_date;
                $result = $this->Lineup_model->save_record(BENCH_PLAYER,$data_arr);
                if(!$result){
                    $save_result = 0;
                }
            }
            $i++;
        }

        if($save_result){
            //for delete extra players
            if(!empty($bench_pl_ids)){
                $extra_pl_ids = array_diff($bench_pl_ids,$player_team_ids);
                if(!empty($extra_pl_ids)){
                    foreach($extra_pl_ids as $player_id){
                        $this->Lineup_model->delete_row(BENCH_PLAYER,array("lineup_master_id"=>$lm_id,"player_id"=>$player_id));
                    }
                }
            }

            //remove user team cache
            $teams_cache_key = "user_teams_".$cm_id."_".$this->user_id;
            $this->delete_cache_data($teams_cache_key);

            $this->api_response_arry['message'] = $this->lang->line('save_bench_player_success');
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = $this->lang->line('bench_player_save_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

	/**
	 * used for save user team
	 * @param int $sports_id
	 * @param int $league_id
	 * @param int $collection_master_id
	 * @param array $lineup
	 * @return
	 */
	public function generate_team_post()
	{
        $allow_guru = (isset($this->app_config['allow_guru']['key_value']) && isset($this->app_config['allow_guru']['custom_data'])) ? $this->app_config['allow_guru']['custom_data'] : array();
        if(empty($allow_guru))
        {
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('module_not_activated');
            $this->api_response();
        }

        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $cm_id = $post_data['collection_master_id'];
        $this->load->model("lineup/Lineup_model");
        $fixture_data = $this->Lineup_model->get_fixture_detail($cm_id);
        if(empty($fixture_data)){
            $this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if(strtotime($current_date) >= strtotime($fixture_data['season_scheduled_date'])){
            $this->api_response_arry['message'] = $this->lang->line('match_started_error');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }else if($fixture_data['season_game_count'] > 1){
            $this->api_response_arry['message'] = $this->lang->line('guru_allowed_dfs');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        //fixture roster
        $roster_cache_key = "roster_list_".$cm_id;
        $roster_list = $this->get_cache_data($roster_cache_key);
        if(!$roster_list)
        {
            $roster_list = $this->Lineup_model->get_fixture_rosters($cm_id);
            $this->set_cache_data($roster_cache_key,$roster_list,REDIS_2_DAYS);
        }

        $locked = isset($post_data['locked']) ? $post_data['locked'] : array();
        $excluded = isset($post_data['excluded']) ? $post_data['excluded'] : array();
        $final_player = array();
        foreach($roster_list as $row){
            $is_locked = $is_excluded = 0;
            if(in_array($row['player_team_id'], $locked)){
                $is_locked = "1";
            }
            if(in_array($row['player_team_id'], $excluded)){
                $is_excluded = "1";
            }
            $tmp_arr = array();
            $tmp_arr['player_uid'] = $row['player_uid'];
            $tmp_arr['team_uid'] = $row['team_uid'];
            $tmp_arr['position'] = $row['position'];
            $tmp_arr['salary'] = $row['salary'];
            $tmp_arr['is_playing'] = $row['is_playing'];
            $tmp_arr['is_locked'] = $is_locked;
            $tmp_arr['is_excluded'] = $is_excluded;
            $final_player[] = $tmp_arr;
        }
        
      	$season = $this->Lineup_model->get_single_row("season_game_uid,playing_announce",SEASON,array("season_id" => $fixture_data['season_ids']));
        if(empty($season)){
            $this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        $season_game_uid = $season['season_game_uid'];
        $playing_announce = $season['playing_announce'];

        //team player limit
        $sports_list = $this->get_sports_list();
        $sports_list = array_column($sports_list,NULL,"sports_id");
        $team_player_limit = isset($sports_list[$fixture_data['sports_id']]['max_player_per_team']) ? $sports_list[$fixture_data['sports_id']]['max_player_per_team'] : 7;
        
        //position list
        $position_list = $this->get_position_list($fixture_data['sports_id']);
        $formation = array();
        foreach($position_list as $pos){
            $formation['min_'.strtolower($pos['position_name'])] = $pos['number_of_players'];
            $formation['max_'.strtolower($pos['position_name'])] = $pos['max_player_per_position'];
        }

        //prepare data for generate team
        $team_data = array();
        $team_data['website_id'] = $allow_guru['website_id'];
        $team_data['token'] = $allow_guru['website_token'];
        $team_data['team_player'] = $team_player_limit;
        $team_data['number_of_lineups'] = 1;
        $team_data['sports_id'] = $fixture_data['sports_id'];
        $team_data['season_game_uid'] = $season_game_uid;
        $team_data['formation'] = $formation;
        $team_data['fixture_players'] = $final_player;
        $api_url = rtrim($allow_guru['website_api'],'/')."/api/generate-client-lineups";
        $header = array("Content-Type:application/json", "Accept:application/json","token:".$team_data['token']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($team_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (ENVIRONMENT !== 'production'){
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output, true);
        //echo "<pre>";print_r($result);die;
        if(isset($result['lineups']) && !empty($result['lineups']) && isset($result['lineups']['0']))
        {
            $pl_result = $result['lineups']['0'];
            $roster_data_list = array_column($roster_list,NULL,"player_uid");
            $final_player_list = array();
        	foreach($pl_result['players'] as $player_uid)
            {
        		$player_info = $roster_data_list[$player_uid];
				if(!empty($player_info)){
					$captain = 0;
					if($player_uid == $pl_result['c_id']){
						$captain = 1;
					}else if($player_uid == $pl_result['vc_id']){
						$captain = 2;
					}
                    $is_locked = $is_excluded = 0;
                    if(in_array($player_info['player_team_id'],$locked)){
                        $is_locked = 1;
                    }
					$lineup = array();
                    $lineup['player_id'] = $player_info['player_id'];
                    $lineup['player_uid'] = $player_info['player_uid'];
                    $lineup['player_team_id'] = $player_info['player_team_id'];
                    $lineup['team_id'] = $player_info['team_id'];
                    $lineup['team_uid'] = $player_info['team_uid'];
                    $lineup['full_name'] = $player_info['full_name'];
                    $lineup['display_name'] = $player_info['display_name'];
                    $lineup['jersey'] = $player_info['jersey'];
                    $lineup['team_abbr'] = $player_info['team_abbr'];
                    $lineup['position'] = $player_info['position'];
                    $lineup['salary'] = $player_info['salary'];
                    $lineup['captain'] = $captain;
                    $lineup['is_playing'] = $player_info['is_playing'];
                    $lineup['is_sub'] = $player_info['is_sub'];
                    $lineup['score'] = 0;
                    $lineup['is_locked'] = $is_locked;
                    $lineup['is_excluded'] = $is_excluded;
					$final_player_list[] = $lineup;
				}
			}

            $team_info = array();
            $team_info['pos_list'] = array_column($position_list,"position_display_name","position_name");
            $team_info['lineup'] = $final_player_list;
            $team_info['playing_announce'] = $playing_announce;

	        $this->api_response_arry['data'] = $team_info;
			$this->api_response();
        }else{
        	$message = $this->lineup_lang['team_generate_error'];
        	if(isset($result['error']['0']) && $result['error']['0'] != ""){
        		$message = $result['error']['0'];
        	}
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        	$this->api_response_arry['message'] = $message;
			$this->api_response();
        }
        
	}
}
/* End of file  */
/* Location: ./application/controllers/ */