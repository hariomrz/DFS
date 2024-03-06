<?php
class Lobby extends Common_Api_Controller 
{
	function __construct()
	{
        parent::__construct();
	}

	/**
	* Get Sports List
	* @param sports_id
	* @return json array
	*/
	public function get_sports_list_post()
	{
		$sports_list = $this->get_sports_list();
		$this->api_response_arry['data'] = $sports_list;
        $this->api_response();
	}

   /**
	* Get Player List
	* @param sports_id
	* @return json array
	*/
	public function get_lobby_player_list_post()
	{
		$this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required|is_natural_no_zero|max_length[2]');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $this->load->model("lobby/Lobby_model");
        $result = $this->Lobby_model->get_sports_match_props($sports_id);

        //Get Master props list
        $props_list = $this->get_props_list($sports_id);

        $this->api_response_arry['data']['picks'] = $result;
        $this->api_response_arry['data']['props'] = $props_list;
        $this->api_response();
	}

	/**
	 * Get picks payout Master data
	 * @param void
	 * @return json array
	 */
	public function get_payout_master_data_post()
	{	
		
        $payout_list = array();
        if($this->props_config['flexplay'] == 1){
        	$flexplay_list = $this->get_payout_list('1');
        	$payout_list = array_merge($payout_list,$flexplay_list);
        }
        if($this->props_config['powerplay'] == 1){
        	$powerplay_list = $this->get_payout_list('2');
        	$payout_list = array_merge($payout_list,$powerplay_list);
        }

        //echo $this->db->last_query();die;
        //Get user Setting
        $user_setting = [];
        if($this->user_id){
        	$this->load->model("lobby/Lobby_model");
        	$user_setting = $this->Lobby_model->check_winning_cap();
        }
		$picks = array_column($payout_list, 'picks');
		
        $this->api_response_arry['data']['payout'] = $payout_list;
        $this->api_response_arry['data']['user_setting'] = $user_setting;
        $this->api_response_arry['data']['picks_range'] =MIN($picks).'-'.MAX($picks);

        $this->api_response();
	}

	/**
	 * Get Player last 5 stats with projection
	 * @param season_prop_id
	 * @string 
	 */
	public function get_player_card_stats_post()
	{
		$this->form_validation->set_rules('season_prop_id', $this->lang->line('prop_id'), 'trim|required');
		$this->form_validation->set_rules('tournament_type', 'Tournament Type', 'trim|required');
		if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $this->load->model("lobby/Lobby_model");
        $player_detail = $this->Lobby_model->get_player_detail($post_data['season_prop_id']);

        if(!empty($player_detail)){
	        $props_sports_list = $this->get_sport_by_props();
	        $prop_sport = array_column($props_sports_list,NULL,'prop_id');

	        $post_data['player_id']  = $player_detail['player_id'];
	        $post_data['sports_id']  = $prop_sport[$player_detail['prop_id']]['sports_id'];
	        $post_data['field_name'] = $prop_sport[$player_detail['prop_id']]['fields_name'];
	        $post_data['format'] 	 = $player_detail['format'];
	        $post_data['prop_id'] 	 = $player_detail['prop_id'];

        	$stats = $this->Lobby_model->get_player_stats($post_data);
        	$player_detail['stats'] = $stats;
        }

        $this->api_response_arry['data'] = !empty($player_detail)?$player_detail:[];
        $this->api_response();
	}

	/**
	 * Save Team
	 * @param payout _type,currency,entry_fee,pl[],team_name
	 * @string message
	 */
	public function save_team_post()
	{	
		$this->form_validation->set_rules('payout_type', $this->lang->line('payout_type'), 'trim|required|in_list[1,2]');
		$this->form_validation->set_rules('currency_type', $this->lang->line('currency_type'), 'trim|required');
		$this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required');
		$this->form_validation->set_rules('pl[]', 'Player data', 'trim|required');
		$this->form_validation->set_rules('team_name', $this->lang->line('team_name'), 'trim|required');
		$this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');

		if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        try{
	        $post_data = $this->input->post();
	        if(empty($post_data['pl'])){
	        	throw new Exception(str_replace('{min_picks}', $this->props_config['min_picks'], $this->lang->line('select_min_picks')));
	        }
			$pl  = array_column($post_data['pl'], 'type','pid');
			if(count($pl) <> count($post_data['pl'])) {
				throw new Exception($this->lang->line('player_info'));
			} 
			$current_date = format_date();
	        //check if player are from differen sports
	        $this->load->model("lobby/Lobby_model");
	        $check_sports = $this->Lobby_model->check_player_sports($post_data['pl']);
	        if(!empty($check_sports['sport_cnt']) && $check_sports['sport_cnt'] > 1){
	        	throw new Exception($this->lang->line('multi_sports_pl_error'));
	        }
	        if(!empty($check_sports) && $check_sports['start_date'] < $current_date){
	        	throw new Exception($this->lang->line('contest_already_started'));
	        }

	        //check min and max bet
	        if($post_data['entry_fee'] < $this->props_config['min_bet'] || $post_data['entry_fee'] > $this->props_config['max_bet']){
	        	$min_max_bet_limit = str_replace('{min_bet}', $this->props_config['min_bet'], $this->lang->line('min_max_bet_limit'));
	        	$min_max_bet_limit = str_replace('{max_bet}', $this->props_config['max_bet'], $min_max_bet_limit);
	        	throw new Exception($min_max_bet_limit);
	        }

			$post_data['user_id']   = $this->user_id;
			$post_data['user_name'] = $this->user_name;
			$post_data['user_team_id'] = isset($post_data['user_team_id'])?$post_data['user_team_id']:'';
			
			//Check if payout is disabled from admin condition
			$payout_list = $this->get_payout_list($post_data['payout_type']);
			$key = array_search(count($post_data['pl']), array_column($payout_list, 'picks'));
			if(!is_numeric($key)){
				throw new Exception($this->lang->line('payout_disabled_error'));
			}

			//Check final and probable winning plus disable status	
			$current_bet_winning = $post_data['entry_fee'] * $payout_list[$key]['points']; //probable winning
			$post_data['probable_winning'] = $current_bet_winning;

			$winning_cap_data    = $this->Lobby_model->check_winning_cap($post_data['user_team_id']);
			if(!empty($winning_cap_data)){
				if($winning_cap_data['status'] == 0) {
					throw new Exception($winning_cap_data['reason']);
				}
				//check winning cap
				if($winning_cap_data['winning_cap'] > 0 )
				{

					$total_winning 		 = $winning_cap_data['winning'] + $winning_cap_data['probable_winning'];//total winning upto now
					$remaining_limit 	 = ABS($winning_cap_data['winning_cap'] - $total_winning);// remaining limit from total -current bet winning
					$total_winning = 	$total_winning + $current_bet_winning;
					
					if($winning_cap_data['winning'] >= $winning_cap_data['winning_cap'])
					{
						throw new Exception(str_replace('{amount}',$winning_cap_data['winning_cap'],$this->lang->line('winning_limit_exceed')));

					}elseif(($total_winning ) > $winning_cap_data['winning_cap'])
					{
						throw new Exception(str_replace('{remaining_limit}',$remaining_limit,$this->lang->line('remaining_winning_limit')));
					}
				}
			}

			if(!empty($post_data['user_team_id'])){
				$saved_team = $this->Lobby_model->get_single_row('entry_fee',USER_TEAM,array('user_team_id'=>$post_data['user_team_id']));
				if(empty($saved_team)){
					throw new Exception($this->lang->line('invalid_team'));
				}

				$this->Lobby_model->update_team($post_data,$saved_team);
				$this->api_response_arry['message'] = $this->lang->line('team_edit_success');
				$this->api_response();
			}else{//Insert User team entry
				
				$result = $this->Lobby_model->save_team($post_data);
				if(!empty($result['user_team_id']))
				{
					//Send Notification
					$input = array('team_name'=>$post_data['team_name'],'amount'=>$post_data['entry_fee'],'currency_type'=>$post_data['currency_type'],'sports_id'=>$post_data['sports_id']);
				 	$notify_data = array();
		            $notify_data["notification_type"] = PICKS_JOIN_NOTIFY; //JoinGame, 
		            $notify_data["source_id"] = $result['user_team_id'];
		            $notify_data["notification_destination"] = 7; //Web,Push
		            $notify_data["user_id"] = $this->user_id;
		            $notify_data["to"] = $this->email;
		            $notify_data["user_name"] = $this->user_name;
		            $notify_data["added_date"] = $current_date;
		            $notify_data["modified_date"] = $current_date;
		            $notify_data["content"] = json_encode($input);
		            $notify_data["subject"] = $this->lang->line('join_entry_email_subject');

		            $this->load->model('user/User_nosql_model');
		            $this->User_nosql_model->send_notification($notify_data);
				}else{
					throw new Exception("Something went wrong while saving team");	
				}

				$this->api_response_arry['data'] = $result['user_team_id'];
				$this->api_response_arry['message'] = $this->lang->line('team_success');
				$this->api_response();
				
			}
		}catch(Exception $e){
           $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
           $this->api_response_arry['message'] = $e->getMessage();
           $this->api_response();
        }

	
	}	

   /**
	* Used to display My contest picks
	* @param status (0,1,2) upcoming,live,completed
	* @return json array
	*/
	public function get_my_joined_team_post()
	{
		$this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|in_list[0,1,2]');
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model('lobby/Lobby_model');
        $result = $this->Lobby_model->get_user_joined_teams($post_data);

    
        if(isset($post_data['is_props']) && $post_data['is_props'] ==1 ) {
        	$result['props'] = $this->get_props_list($post_data['sports_id']);
        }
       	$this->api_response_arry['data'] = $result;
        $this->api_response();
	}



   /**
	* Used to get user_lineup
	* @param user_team_id
	* @return json array
	*/

	public function get_user_lineup_post()
	{
		$this->form_validation->set_rules('user_team_id', $this->lang->line('user_team_id'), 'trim|required');
		if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_team_id = $post_data['user_team_id'];
        $this->load->model('lobby/Lobby_model');
        $team_detail= $this->Lobby_model->get_user_joined_player_detail($user_team_id);

        $props =[];
        if(isset($post_data['is_props']) && $post_data['is_props'] ==1 ) {
        	$props = $this->get_props_list($team_detail[0]['sports_id']);
        }
        $this->api_response_arry['data']['lineup'] = $team_detail;
        $this->api_response_arry['data']['props'] = $props;
        $this->api_response();

	}

	/**
	* Used to display My contest player detail
	* @param user_team_id 
	* @retur json array
	*/
	public function get_user_team_detail_post()
	{
		$this->form_validation->set_rules('user_team_id', $this->lang->line('user_team_id'), 'trim|required');
		$this->form_validation->set_rules('status', $this->lang->line('status'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_team_id = $post_data['user_team_id'];
        $this->load->model('lobby/Lobby_model');
        $team_detail = $this->Lobby_model->get_user_team_detail($user_team_id);
        if(!empty($team_detail)){
        	$player_detail = $this->Lobby_model->get_user_joined_player_detail($user_team_id);
        	//echo $this->db->last_query();die;
        }
         $props =[];
        if(isset($post_data['is_props']) && $post_data['is_props'] ==1 ) {
        	$props = $this->get_props_list($team_detail['sports_id']);
        }
       
        $this->api_response_arry['data']['team_detail']   = !empty($team_detail)?$team_detail:[];
        $this->api_response_arry['data']['player_detail'] = !empty($player_detail)?$player_detail:[];
        $this->api_response_arry['data']['props'] = $props;
        $this->api_response();
	}

}
