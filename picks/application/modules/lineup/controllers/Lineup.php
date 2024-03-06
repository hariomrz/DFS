<?php

class Lineup extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->lineup_lang = $this->lang->line('lineup');
		$this->picks_master_data = $this->app_config['allow_picks']['custom_data'];

	}

	public function check_match_status($data)
	{	
		$current_date = format_date();
		$current_date = strtotime($current_date) * 1000;
		$contest_date = strtotime($data['scheduled_date']) * 1000;
		if($current_date > $contest_date)
		{
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->lineup_lang['contest_started'];
			$this->api_response();
		}

		return true;
	}

	/**
	 * Used for get lineup master data
	 * @param int $sports_id
	 * @param int $league_id
	 * @param int $season_id
	 * @return array
	*/
	public function get_lineup_master_data_post()
	{
		$this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
		$this->form_validation->set_rules('league_id', $this->lang->line('league_id'), 'trim|required');
		$this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
        $sports_id = $post_data['sports_id'];
        $league_id = $post_data['league_id'];
        $season_id = $post_data['season_id'];
        $current_date = format_date();
        $this->load->model("lineup/Lineup_model");


		$season_data = $this->Lineup_model->get_single_row("season_id,scheduled_date,question,correct,wrong", SEASON,array("season_id" => $season_id));

		if(empty($season_data)){
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $this->lineup_lang['match_detail_not_found'];
			$this->api_response();
		}

		$this->check_match_status($season_data);

		$picks_master_data = $this->picks_master_data;
		
        $records = array();
		$records["picks_data"]['question']	= isset($season_data['question'])?$season_data['question']: $picks_master_data['questions'];
		$records["picks_data"]['correct']	= isset($season_data['correct'])?$season_data['correct']: $picks_master_data['correct'];
		$records["picks_data"]['wrong']	    = isset($season_data['wrong'])?$season_data['wrong']: $picks_master_data['wrong'];

		/*Booster data*/
		$booster_cache_key = "picks_booster_".$sports_id;
		$booster = $this->get_cache_data($booster_cache_key);

        if(!$booster)
        {
        	if($records["picks_data"]['question'] >= 1 && $records["picks_data"]['question'] <=3){
        		$records["booster"]['2x'] = 1;
        		$records["booster"]['NN'] = 1;
        	}elseif($records["picks_data"]['question'] >= 4 && $records["picks_data"]['question'] <=7){
        		$records["booster"]['2x'] = 2;
        		$records["booster"]['NN'] = 2;
        	}elseif($records["picks_data"]['question'] >= 8 && $records["picks_data"]['question'] <=$picks_master_data['question']){
        		$records["booster"]['2x'] = 3;
        		$records["booster"]['NN'] = 3;
        	}


			//set master position in cache for 30 days
        	$this->set_cache_data($booster_cache_key,$booster,REDIS_30_DAYS);
        }



		$team_list = $this->Lineup_model->get_season_teams($season_id,$league_id);
		$records["team_list"] = $team_list;

		//for upload lineup data on s3 bucket
        $this->push_s3_data_in_queue("picks_lineup_master_data_".$season_id,$records);

		$this->api_response_arry['data'] = $records;
		$this->api_response();
	}

	/**
	 * Used for get collection players list
	 * @param int $sports_id
	 * @param int $league_id
	 * @param int $season_id
	 * @return array
	*/
	public function get_all_roster_post()
	{
		$this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$season_id = $post_data['season_id'];
		$season_player_cache_key = "picks_season_player_".$season_id;
		$players_list = $this->get_cache_data($season_player_cache_key);
        if(!$players_list)
        {
        	$this->load->model("lineup/Lineup_model");
			$questions = $this->Lineup_model->get_all_rosters($post_data);
        	
			$season_data = $this->Lineup_model->get_single_row("season_id,scheduled_date,correct,wrong,tie_breaker_question,tie_breaker_answer", SEASON,array("season_id" => $season_id));
			
			$players_list = array();
			
			$players_list['questions']=$questions;
			$players_list['season']=isset($season_data)?$season_data:(object) array() ;
			
			//set collection team in cache for 2 hours
        	$this->set_cache_data($season_player_cache_key,$players_list,REDIS_2_DAYS);
        }

		//for upload lineup data on s3 bucket
        $this->push_s3_data_in_queue("picks_season_roster_list_".$season_id,$players_list);

		$this->api_response_arry['data'] = $players_list;
		$this->api_response();
	}

	function validate_picks()
	{
		$lineup = $this->input->post("picks");
        $msg = "";
        if(empty($lineup['picks']))
        {
            $msg = $this->lang->line("lineup_required") ;
		}

		if(!empty($msg))
        {
            $this->form_validation->set_message('validate_picks', $msg);
            return FALSE;
        }
        return TRUE;

	}

	/**
	 * used for save user team
	 * @param int $sports_id
	 * @param int $league_id
	 * @param int $season_id
	 * @param array $lineup
	 * @return
	 */
	public function lineup_process_post()
	{	
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('league_id', $this->lang->line('league_id'), 'trim|required|numeric');
        $this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
        $this->form_validation->set_rules('picks', $this->lang->line('picks'), 'callback_validate_picks');
        $this->form_validation->set_rules('team_name', $this->lang->line('team_name'), 'trim|max_length[20]');
        $this->form_validation->set_rules('c_id[]', $this->lang->line('captain'), 'trim|required');
        $this->form_validation->set_rules('vc_id[]', $this->lang->line('vice_captain'), 'trim|required');
       
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $sports_id = $post_data['sports_id'];
        $league_id = $post_data['league_id'];
        $season_id = $post_data['season_id'];
		$picks = $post_data['picks']['picks'];
        $this->sports_id = $sports_id;

        if($this->user_name == ""){
        	$this->api_response_arry['message'] = $this->lineup_lang['username_empty_error'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
		$this->load->model("lineup/Lineup_model");
        $season_data = $this->Lineup_model->get_single_row("scheduled_date,question,correct,wrong", SEASON,array("season_id" => $season_id,'scheduled_date >='=>$current_date));

        if(empty($season_data)){
    		$this->api_response_arry['message'] = $this->lineup_lang['contest_started'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
    	}
 
		//$this->check_match_status($season_data);
    	//season picks set in cache
	    $season_player_cache_key = "picks_lineup_player_".$season_id;
		$picks_list = $this->get_cache_data($season_player_cache_key);

        if(!$picks_list)
        {
			$picks_list = $this->Lineup_model->get_all_rosters($post_data);
        	$this->set_cache_data($season_player_cache_key,$picks_list,REDIS_2_HOUR);
        }

        if(count($picks) < 1) {
         	$this->api_response_arry['message'] = $this->lineup_lang['invalid_collection_player'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $players_data_ids = array();
    	$lineup = array();
        if(!empty($picks_list)){
      	$picks_data_ids = array_column($picks_list, "pick_id");
      	$pick_team_id_list = array_column($picks_list,NULL, "pick_id");
      	
		  foreach($picks as $pick_id=>$pick_value)
		  {   
	        if(isset($pick_team_id_list[$pick_id]))
	        { 

	          $pick_team_id_list[$pick_id]['answer'] = $pick_value;
	          $lineup[] = $pick_team_id_list[$pick_id];
	        }
	      }
	    }

	
		$pl_ids = array_unique(array_column($lineup,"pick_id"));
		if(count($pl_ids) > $this->picks_master_data['question'] )
		{
			$msg = str_replace('%s', $this->picks_master_data['question'] , $this->lineup_lang["lineup_max_limit"]);
			$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['message']			= $msg;
			$this->api_response();
		}
		
		if(isset($post_data['user_team_id']) && !empty($post_data['user_team_id']))
        {
            $user_team_id = $post_data['user_team_id'];
            $lineup_exist = $this->Lineup_model->get_team_by_user_team_id($user_team_id,$season_id);
            if(empty($lineup_exist))
            {
                $this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']			= $this->lineup_lang['lineup_not_exist'];
                $this->api_response();	
            }

            $update_lineup_master_data = array();
            if(!empty($post_data['team_name']))
            {
            	$update_lineup_master_data['date_modified'] = $current_date;
                $update_lineup_master_data['team_name'] = $post_data['team_name'];
            }

            //check duplicate team name
            $check_team = $this->Lineup_model->get_single_row("user_team_id",USER_TEAM,array("season_id" => $season_id,"user_id" => $this->user_id,"LOWER(team_name)"=>strtolower($update_lineup_master_data['team_name']),"user_team_id != " => $user_team_id));
            if(!empty($check_team)){
            	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message']			= $this->lineup_lang['team_name_already_exist'];
                $this->api_response();
            }
            
            $this->save_lineup($post_data,$lineup);
        }


        //check duplicate team name
        $check_team = $this->Lineup_model->get_single_row("user_team_id",USER_TEAM,array("season_id" => $season_id,"user_id" => $this->user_id,"LOWER(team_name)" => strtolower($post_data['team_name'])));
        if(!empty($check_team)){
        	$this->api_response_arry['response_code']	= rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']			= $this->lineup_lang['team_name_already_exist'];
            $this->api_response();
        }

        $this->save_lineup($post_data,$lineup);
	}


	/**
	 * used for save user team
	 * @param array $save_data
	 * @param array $lineup
	 * @param array $position_array
	 * @return
	*/
	private function save_lineup($post_data,$lineup)
	{	
		//Trasaction start
	    $this->db->trans_strict(TRUE);
	    $this->db->trans_start();

		$user_team_data = array(
			'season_id'=>$post_data['season_id'],
			'team_name'=>$post_data['team_name'],
			'tie_breaker_answer'=>$post_data['tie_breaker_answer'],
			'user_name'=>$this->user_name,
			'user_id'=>$this->user_id,
			'date_added'=>format_date(),
			'date_modified'=>format_date(),
		);
		$picks = $post_data['picks']['picks'];
		$picks = array_keys($picks);
		

		$this->load->model('Lineup_model');
		$user_team_id = $this->Lineup_model->save_user_team($user_team_data,$post_data['user_team_id']);
		if(!empty($post_data['user_team_id'])){
			$user_team_id = $post_data['user_team_id'];
			$message = $this->lineup_lang['update_lineup_success'];
		}else{
			$message = $this->lineup_lang['lineup_success'];
		}
		$user_picks_data =[];
		$is_captain=$is_vc=0;
		
		foreach ($lineup as $key => $value) {
			if(in_array($value['pick_id'], $post_data['c_id'])) {
				$is_captain = 1;
			}

			if(in_array($value['pick_id'] ,$post_data['vc_id'])) {
				$is_vc= 1;
			}
			$user_picks_data[] = array(
				'user_team_id'=>$user_team_id,
				'pick_id'=>$value['pick_id'],
				'answer'=>$value['answer'],
				'is_captain'=>$is_captain,
				'is_vc'=>$is_vc,
				'created_date'=>format_date()
			);
			$is_captain=$is_vc=0;
		}
		
		if(!empty($post_data['user_team_id']))
		{	$this->Lineup_model->replace_into_batch(USER_TEAM_PICKS,$user_picks_data);
			$this->Lineup_model->delete_picks($picks,$post_data['user_team_id']);
			
		}else{

			$this->db->insert_batch(USER_TEAM_PICKS,$user_picks_data);
		}

		$user_teams_cache_key = "picks_user_teams_".$post_data['season_id']."_".$this->user_unique_id;
		$this->delete_cache_data($user_teams_cache_key);

	    //Trasaction end
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE )
        {
          $this->db->trans_rollback();
        }
        else
        {
          $this->db->trans_commit();
        }

		$this->api_response_arry['data']   = array('user_team_id'=>$user_team_id);
		$this->api_response_arry['message']       = $message;
		$this->api_response();	
	}


	/**
	 * used for get user team player list
	 * @param int $user_team_id
	 * @param int $season_id
	 * @return array
	*/
	public function get_user_lineup_post()
	{
		
		$this->form_validation->set_rules('user_team_id', $this->lang->line('user_team_id'), 'trim|required');
		$this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		$this->load->model("lineup/Lineup_model");
		$lineup_result = $this->Lineup_model->get_lineup_data($post_data);

		if(!empty($lineup_result['lineup']))
		{
			$team_name =  array_unique(array_column($lineup_result['lineup'], 'team_name'));
			$lineup_result['user_contest_data']['team_name'] = $team_name[0];
			$lineup_result['lineup'] = array_map(function (array $subArr) { unset($subArr['team_name']); return $subArr;},$lineup_result['lineup']);
		}
		
		$season_data = $this->Lineup_model->get_single_row("question,correct,wrong,tie_breaker_question,tie_breaker_answer",SEASON,array("season_id" => $post_data['season_id']));
		
		$this->api_response_arry['data']['lineup']= $lineup_result['lineup'];
		$this->api_response_arry['data']['contest']= $lineup_result['user_contest_data'];
		$this->api_response_arry['data']['picks_data']= $season_data;
		$this->api_response();
	}

   /**
	* @method get team name for particaular match
	* @param season_id
	* @return data array
	*/
	public function get_team_name_by_season_id_post()
	{
		$this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
		if (!$this->form_validation->run()) {
			$this->send_validation_errors();
		}

		$season_id = $this->input->post('season_id');
		$team_name = "Picks 1";
		$this->load->model("lineup/Lineup_model");
		$team_result = $this->Lineup_model->get_all_table_data("COUNT(user_team_id) as team_count",USER_TEAM,['season_id'=>$season_id,'user_id'=>$this->user_id]);
		
		if(!empty($team_result))
		{
			$team_name = "Picks ".($team_result[0]['team_count']+1);
		}
		$team_data = array("team_name"=>$team_name);
		$this->api_response_arry['data']=  $team_data;
		$this->api_response();
	}

}/*End of file*/