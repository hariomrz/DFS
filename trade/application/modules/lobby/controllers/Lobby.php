<?php
class Lobby extends Common_Api_Controller 
{
	function __construct()
	{
        parent::__construct();
		$this->load->model('Lobby_model');	
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
	* Get all match according to spport id
	* @param optional(sports_id)
	* @return json array
	*/
	public function get_loby_fixture_post()
	{	
		$post_data = $this->input->post();
		$sports_id = isset($post_data['sports_id']) ? $post_data['sports_id'] : "0";
		
		$match_list_cache_key = 'match_list_'.$sports_id;
        $match_list = $this->get_cache_data($match_list_cache_key);
		if(empty($match_list)){
			$match_list = $this->Lobby_model->get_match_list($sports_id);
			$this->set_cache_data($match_list_cache_key, $match_list, REDIS_5_MINUTE);
		}
		
		$this->api_response_arry['data'] = $match_list;
        $this->api_response();
	}

	/**
	* Get all question according to spport id and season id
	* @param sports_id and season id
	* @return json array
	*/
	public function get_question_list_post()
	{	
		$post_data = $this->input->post();
		$post_data['sports_id'] = isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
		$post_data['season_id'] = isset($post_data['season_id']) ? $post_data['season_id'] : "";
		$question_list = $this->Lobby_model->get_sports_question($post_data);
		
		$question_ids = isset($question_list['question'])?array_column($question_list['question'], 'question_id'):array();
		if($question_ids){
			$post_data['question_ids'] = $question_ids;
			$post_data['user_id'] = $this->user_id;
			$que_trade = $this->Lobby_model->question_trade_count($post_data);
			$trade_data = array_column($que_trade, null, 'question_id');
			$question_list['trade_data'] = $trade_data;
		}

		$this->api_response_arry['data'] = $question_list;
        $this->api_response();
	}


	/**
     * Used for save user team
     * @param void
     * @return string
     */
    public function save_team_post()
    { 
		$this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
		$this->form_validation->set_rules('question_id', $this->lang->line('question_id'), 'trim|required');
		$this->form_validation->set_rules('option_id', $this->lang->line('option_id'), 'trim|required');
		$this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required');
		$this->form_validation->set_rules('quantity', $this->lang->line('quantity'), 'trim|required');
		
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

		$post_data = $this->input->post(); 
		$current_date = format_date();
		$ques_details = $this->Lobby_model->get_single_row("question_id,scheduled_date,season_id,currency_type,cap,question",SEASON_QUESTION,array("question_id"=>$post_data['question_id']));
		if(empty($ques_details)){
            $this->api_response_arry['message'] = $this->lang->line('invalid_question');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
		}else if(strtotime($current_date) >= strtotime($ques_details['scheduled_date'])){
            $this->api_response_arry['message'] = $this->lang->line('question_already_started');
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
		$post_data['user_id'] = $this->user_id;
		$post_data['cap'] = $ques_details['cap'];
		$post_data['question'] = $ques_details['question'];
		$this->entry_fee = $post_data['entry_fee'];
        $this->currency_type = $ques_details['currency_type'];
        $this->contest_entry = array("real"=>"0","winning"=>"0","bonus"=>"0","coin"=>"0");
		$is_valid = $this->validation_for_save_team($post_data);
		if($is_valid){

			$match_data = $this->Lobby_model->get_fixture_details($ques_details['season_id']);
			$match_name = isset($match_data)?$match_data['home'].' vs '.$match_data['away']:'';
			$post_data['match_name'] = $match_name;
			
			$result = $this->Lobby_model->save_team($post_data);
			if(!empty($result) && isset($result['question_id'])){
				
				$user_teams_cache_key = 'user_teams_'.$ques_details['question_id'].'_'.$this->user_id;
				$this->delete_cache_data($user_teams_cache_key);
				
				$opinion = ($post_data['option_id']==1)?"Yes":"No";
				
				//Send Notification
				$input = array('match_name'=>$match_name,'amount'=>$this->entry_fee,'opinion'=>$opinion,'quantity'=>$post_data['quantity']);
				$notify_data = array();
				$notify_data["notification_type"] = TRADE_JOIN_NOTIFY; //JoinGame, 
				$notify_data["source_id"] = $result['question_id'];
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

				// process for matchup
				$this->load->helper('queue');
				$server_name = get_server_host_name();
				$content = array('action_type'=>'matchup','question_id'=>$post_data['question_id'],'user_id'=>$this->user_id,'type'=>$post_data['option_id'],'entry_fee'=>$post_data['entry_fee']);
				add_data_in_queue($content,'matchup');

				// node emitter
				$content = array('action_type'=>'trade_update','question_id'=>$post_data['question_id'],'user_id'=>$this->user_id);
				add_data_in_queue($content,'node_emitter');
				
				$this->api_response_arry['message'] = $this->lang->line('trade_save_success');
				$this->api_response_arry['data'] = $result;
				$this->api_response();
			}else{
				$this->api_response_arry['message'] = 'Something went wrong while save team.';
				$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response();
			}
		}else{
			$this->api_response_arry['message'] = $this->message;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
		}
        
    }

	/**
     * Used for validate data in save team
     * @param void
     * @return string
     */
	private function validation_for_save_team($post_data){
		$total_entry_fee = $post_data['entry_fee'] * $post_data['quantity'];
		//user wallet balance check
        $this->load->model("user/User_model");
		$balance = $this->User_model->get_user_balance($this->user_id);
        
		//for Coins
		if($this->currency_type == 2){
            if($total_entry_fee > $balance['point_balance']) {
                $this->message = $this->lang->line('not_enough_coins');
                return 0;
            }
            //$this->contest_entry['coin'] = $total_entry_fee;
        }elseif($this->currency_type == 1){

            if ($total_entry_fee > ($balance['balance'] + $balance['winning_balance'])) {
                $this->message = $this->lang->line('not_enough_balance');
                return 0;
            }

        }elseif($this->currency_type == 0){
			if($total_entry_fee > $balance['bonus_balance']) {
                $this->message = $this->lang->line('not_enough_bonus');
                return 0;
            }
		}

		return true;
	}

	/**
	* Get fixture question list according to spport id and season id
	* @param sports_id and season id
	* @return json array
	*/
	public function get_my_question_post()
	{	
		$this->form_validation->set_rules('question_id', $this->lang->line('question_id'), 'trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$question_id = $post_data['question_id'];
		
		$fixture = array();
		$question = $this->Lobby_model->get_question_detail($question_id);
		if(!empty($question) && !empty($this->user_id)){
			$season_id = $question['season_id'];
			$fixture = $this->Lobby_model->get_fixture_details($season_id);
			$fixture['question'] = $question;
			
			// order book
			$odr_book = $this->Lobby_model->get_order_book($question_id);
			$fixture['order_book'] = $odr_book;
			
			// trade data
			$post_data['question_ids'] = array($question_id);
			$que_trade = $this->Lobby_model->question_trade_count($post_data);

			$trade_data = isset($que_trade[0])?$que_trade[0]:array();
			$fixture['trade_data']['total_unmatched'] =  isset($trade_data['total_unmatched'])?$trade_data['total_unmatched']:0;
			$fixture['trade_data']['total_matched'] =  isset($trade_data['total_matched'])?$trade_data['total_matched']:0;
			$fixture['trade_data']['total_trade'] =  isset($trade_data['total_trade'])?$trade_data['total_trade']:0;

		}
		$this->api_response_arry['data'] = $fixture;
        $this->api_response();
	}

	/**
	* This function used for cancel all anwser
	* @param sports_id and season id
	* @return json array
	*/
	public function cancel_anwser_post()
	{	
		$this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required');
		$this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
		$this->form_validation->set_rules('question_id', $this->lang->line('question_id'), 'trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }

		$post_data = $this->input->post();
		$cancel_trade_count = $this->Lobby_model->cancel_awnser($post_data);
		$user_teams_cache_key = 'user_teams_'.$post_data['question_id'].'_'.$this->user_id;
        $this->delete_cache_data($user_teams_cache_key);
		

		// process for matchup
		$this->load->helper('queue');
		$server_name = get_server_host_name();
		$content = array('action_type'=>'cancel','question_id'=>$post_data['question_id'],'user_id'=>$this->user_id,'type'=>$post_data['type']);
		add_data_in_queue($content,'matchup');

		// node broadcast
		// $this->load->helper('queue');
		// $server_name = get_server_host_name();
		// $content = array('action_type'=>'trade_update','question_id'=>$post_data['question_id'],'user_id'=>$this->user_id);
		// add_data_in_queue($content,'node_emitter');
		

		if($cancel_trade_count > 0){
			$this->api_response_arry['message'] = str_replace("{trade_count}",$cancel_trade_count,$this->lang->line('trade_cancel'));
		}else{
			$this->api_response_arry['message'] = $this->lang->line('trade_not_cancel');
		}
		
        $this->api_response();
	}

	/**
	* Get question details
	* @param season id
	* @return json array
	*/
	public function get_question_details_post()
	{	
		$this->form_validation->set_rules('question_id', $this->lang->line('question_id'), 'trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$question_id = $post_data['question_id'];
		$question = $this->Lobby_model->get_question_detail($question_id);
		if($question && isset($question['status']) && ($question['status'] == 0)){
			$trade_value = create_trade_range();
			$trade_initial = array_fill_keys($trade_value,0);
			// get unmatched anwser with login user
			$unmatched_ans = $this->Lobby_model->get_question_unmatched_anwser($question_id);
			$trade_db[1]=array();
			$trade_db[2]=array();
			if($unmatched_ans){
				$trade_db = array_reduce($unmatched_ans, function ($carry, $item){
					$answer = $item['answer'];
					$entry_fee = $item['entry_fee'];
					$total = $item['total'];
					$carry[$answer][$entry_fee] = $total;
					return $carry;
				}, array());
			}
			
			$trade_yes = array_replace($trade_initial,isset($trade_db[1])?$trade_db[1]:array());
			$trade_no = array_replace($trade_initial,isset($trade_db[2])?$trade_db[2]:array());	
			ksort($trade_yes);
			ksort($trade_no);
			$question['trade'][1]=$trade_yes;
			$question['trade'][2]=$trade_no;
		}else{
			$question['trade'] = array();
		}

		$this->api_response_arry['data'] = $question;
        $this->api_response();
	}

	/**
	* Get my anwser list
	* @param sport_id,season id
	* @return json array
	*/
	public function get_my_entry_post()
	{
		$post_data = $this->input->post();
		$post_data['sports_id'] = isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
		$post_data['season_id'] = isset($post_data['season_id']) ? $post_data['season_id'] : "";
		
		if($this->user_id){
			$post_data['user_id'] = $this->user_id;
			$my_answer = $this->Lobby_model->get_my_answer($post_data);
			if(isset($my_answer['current_trade']) && $my_answer['current_trade']){
				unset($my_answer['current_trade']);
			}
			
			$my_answer['match'] = array();
			if($my_answer['answer']){
				$post_data['user_id'] = $this->user_id;
				$seasonIds = array_values(array_unique(array_column($my_answer['answer'], 'season_id')));
				$match = $this->Lobby_model->get_fixture_list($seasonIds);
				$my_answer['match'] = $match;
			}
		}
		
		$this->api_response_arry['data'] = $my_answer;
        $this->api_response();
	}


	/**
	* Get completed
	* @param
	* @return json array
	*/
	public function get_completed_list_post()
	{
		$post_data = $this->input->post();
		$sports_id = isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
		
		$completed = array();
		if($this->user_id){
			$completed = $this->Lobby_model->get_completed_list($post_data);
			$total = $this->Lobby_model->total_user_data($post_data);
			
			$completed['currency_real'] = isset($total['currency_real']) ? $total['currency_real'] : 1;	
			$completed['currency_coin'] = isset($total['currency_coin']) ? $total['currency_coin'] : 0;	
			// total
			$completed['total_real_invest'] = isset($total['total_real_invest']) ? $total['total_real_invest'] : 0;	
			$completed['total_coin_invest'] = isset($total['total_coin_invest']) ? $total['total_coin_invest'] : 0;	
			$completed['total_real_winning'] = isset($total['total_real_winning']) ? $total['total_real_winning'] : 0;	
			$completed['total_coin_winning'] = isset($total['total_coin_winning']) ? $total['total_coin_winning'] : 0;	
			
			// today
			$today = array();
			$today['real_invest'] = isset($total['today_real_invest']) ? $total['today_real_invest'] : 0;	
			$today['coin_invest'] = isset($total['today_coin_invest']) ? $total['today_coin_invest'] : 0;	
			$today['real_winning'] = isset($total['today_real_winning']) ? $total['today_real_winning'] : 0;	
			$today['coin_winning'] = isset($total['today_coin_winning']) ? $total['today_coin_winning'] : 0;
			
			// week
			$week = array();
			$week['real_invest'] = isset($total['week_real_invest']) ? $total['week_real_invest'] : 0;	
			$week['coin_invest'] = isset($total['week_coin_invest']) ? $total['week_coin_invest'] : 0;	
			$week['real_winning'] = isset($total['week_real_winning']) ? $total['week_real_winning'] : 0;	
			$week['coin_winning'] = isset($total['week_coin_winning']) ? $total['week_coin_winning'] : 0;

			// month
			$month = array();
			$month['real_invest'] = isset($total['month_real_invest']) ? $total['month_real_invest'] : 0;	
			$month['coin_invest'] = isset($total['month_coin_invest']) ? $total['month_coin_invest'] : 0;	
			$month['real_winning'] = isset($total['month_real_winning']) ? $total['month_real_winning'] : 0;	
			$month['coin_winning'] = isset($total['month_coin_winning']) ? $total['month_coin_winning'] : 0;

			$completed['today'] = $today;
			$completed['week'] = $week;
			$completed['month'] = $month;
			
		}
		
		$this->api_response_arry['data'] = $completed;
        $this->api_response();
	}

	/**
	* Get completed
	* @param
	* @return json array
	*/
	public function get_completed_question_post()
	{
		$this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		$post_data = $this->input->post();
		$season_id = $post_data['season_id'];
		$ques_list = $this->Lobby_model->get_completed_question($season_id);
		
		$this->api_response_arry['data'] = $ques_list;
        $this->api_response();
	}

	/**
	* Get activity 
	* @param question id
	* @return json array
	*/
	public function get_trade_activity_post()
	{	
		$this->form_validation->set_rules('question_id', $this->lang->line('question_id'), 'trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$question_id = $post_data['question_id'];
		$activity = $this->Lobby_model->get_trade_activity($post_data);
		$result = $activity['result'];
		
		// get user data
		$user_arr = array_values(array_unique(array_column($result,'user_id')));
		$m_user_arr = array_values(array_unique(array_column($result,'m_user_id')));
		if($m_user_arr){
			$m_user_arr = array_filter($m_user_arr, function ($value) {
				return !empty($value);
			});
		}
		$m_user_arr = ($m_user_arr)?$m_user_arr:array();
		$user_ids = array_values(array_unique(array_merge($user_arr,$m_user_arr)));
		if($user_ids){
			$user_data = $this->Lobby_model->get_participant_user_details($user_ids);
			$user_data_arr = array_column($user_data,NULL,"user_id");
			$activity['user_data'] = $user_data_arr;
		}
		
		$activity['result'] = $result;
		
		$this->api_response_arry['data'] = $activity;
        $this->api_response();
	}

	/**
	* Get my joined data 
	* @param question id
	* @return json array
	*/
	public function get_my_joined_post()
	{	
		$this->form_validation->set_rules('question_id', $this->lang->line('question_id'), 'trim|required');
		$this->form_validation->set_rules('season_id', $this->lang->line('season_id'), 'trim|required');
		if($this->form_validation->run() == FALSE){
            $this->send_validation_errors();
        }
		
		$post_data = $this->input->post();
		$question_id = $post_data['question_id'];
		$season_id = $post_data['season_id'];

		$post_data['user_id'] = $this->user_id; 
		// my joined data
		$my_joined = $this->Lobby_model->get_trade_activity($post_data);
		$result = $my_joined['result'];
		
		// get user data
		$user_arr = array_values(array_unique(array_column($result,'user_id')));
		$m_user_arr = array_values(array_unique(array_column($result,'m_user_id')));
		if($m_user_arr){
			$m_user_arr = array_filter($m_user_arr, function ($value) {
				return !empty($value);
			});
		}
		$m_user_arr = ($m_user_arr)?$m_user_arr:array();
		$user_ids = array_values(array_unique(array_merge($user_arr,$m_user_arr)));
		
		if($user_ids){
			$user_data = $this->Lobby_model->get_participant_user_details($user_ids);
			$user_data_arr = array_column($user_data,NULL,"user_id");
			$my_joined['user_data'] = $user_data_arr;
		}

		// invest data
		$user_teams_cache_key = 'user_teams_'.$question_id.'_'.$this->user_id;
		$invest_data = $this->get_cache_data($user_teams_cache_key);
		if(empty($invest_data)){
			$invest_data = $this->Lobby_model->get_trade_count($post_data);
			$this->set_cache_data($user_teams_cache_key, $invest_data, REDIS_2_HOUR);
		}
		$my_joined['invest_data'] = $invest_data;


		$this->api_response_arry['data'] = $my_joined;
        $this->api_response();
	}

	/**
	* Get live trade data
	* @param sport_id
	* @return json array
	*/
	public function get_live_trade_post()
	{
		$post_data = $this->input->post();
		$post_data['sports_id'] = isset($post_data['sports_id']) ? $post_data['sports_id'] : "";
		$my_answer = array();
		if($this->user_id){
			$post_data['user_id'] = $this->user_id;
			$my_answer = $this->Lobby_model->get_my_answer($post_data);
			$my_answer['match'] = array();
			if($my_answer['answer']){
				$post_data['user_id'] = $this->user_id;
				$seasonIds = array_values(array_unique(array_column($my_answer['answer'], 'season_id')));
				$match = $this->Lobby_model->get_fixture_list($seasonIds);
				$my_answer['match'] = $match;
			}
		}
		
		$this->api_response_arry['data'] = $my_answer;
        $this->api_response();
	}

}
