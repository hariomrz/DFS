<?php

class Contest extends Common_Api_Controller {

    
    var $message = "";
    var $entry_fee = 0;
    var $enough_balance = 1;
    var $self_exclusion_limit = 0;
    var $currency_type = 1;
    public function __construct() {
        parent::__construct();
        $this->contest_lang = $this->lang->line('contest');
    }

    /**
     * Used for get fixture(match) contest listing
     * @param int $sports_id
     * @param int $collection_master_id
     * @return array
     */
    public function get_all_contest_get() 
    {
        $sports_id = $this->uri->segment(3);
        if(empty($sports_id))
        {
            $sports_id = 7;
        }    

        $post_data['sports_id'] = $sports_id;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/lobby/get_all_contest";
        $api_response =  $this->http_post_request($url,$post_data);
        //echo "<pre>";print_r($api_response);die;
       if(!empty($api_response))
        {
            $contest_list = json_decode($api_response,true);
            //echo "<pre>";print_r($contest_list);die("contest list");
            if(!empty($contest_list['data']['contest']))
            {
                foreach ($contest_list['data']['contest'] as $ckey => $cvalue) 
                {
                    $this->create_network_contests($cvalue);
                    
                }    
            }    
            
        }    
        //Auto publish
        //if(isset($_GET['auto_publish']) && $_GET['auto_publish'] == '0')
        if(isset($this->app_config['network_game_auto_publish']) && $this->app_config['network_game_auto_publish']['key_value'] == '0')
        {    
           $this->network_api_response($api_response);
        }else{
            $this->contest_auto_publish($sports_id);
            $this->network_api_response($api_response);
        }    
        //die("okokokokokok");
    }

    private function create_network_contests($contestArr)
    {

        if(empty($contestArr))
        {
            return TRUE;
        } 
        

        $this->load->model("Contest_model");

        $league_exists = $this->Contest_model->check_league_exist($contestArr);
        if(!empty($league_exists))
        {
            
            $contest_insert_array = array();
            
            $pz =  json_decode($contestArr['prize_distibution_detail'],true);
            //print_r($pz);die;
            $contestArr['prize_distibution_detail'] = $pz;
           //unset($contestArr['prize_distibution_detail']);
            $contest_insert_array['network_collection_master_id'] = $contestArr['collection_master_id'];   
            $contest_insert_array['network_contest_id'] = $contestArr['contest_id'];   
            $contest_insert_array['sports_id'] = $contestArr['sports_id'];   
            $contest_insert_array['league_id'] = $league_exists['league_id'];;   
            //$contest_insert_array['collection_master_id'] = $collection_master_id;   
            $contest_insert_array['season_scheduled_date'] = $contestArr['season_scheduled_date'];   
            $contest_insert_array['contest_details'] = json_encode($contestArr);   
            $contest_insert_array['network_prize_pool'] = $contestArr['prize_pool'];
            $contest_insert_array['network_max_prize_pool'] = $contestArr['max_prize_pool'];    
           // $contest_insert_array['active'] = 0;//default inactive/unpublished.   
            $contest_insert_array['date_added'] = format_date();  


            $contest_insert_batch[] = $contest_insert_array; 
            //echo "<pre>";print_r($contest_insert_batch);
            $this->Contest_model->replace_into_batch(NETWORK_CONTEST,$contest_insert_batch); 
        }  


        return TRUE;
    

    }

    private function map_contests_with_collection($contestArr)
    {
        if(empty($contestArr))
        {
            return TRUE;
        }    
        $this->load->model("Contest_model");

        $league_exists = $this->Contest_model->check_league_exist($contestArr);
        if(!empty($league_exists))
        {
            $collection_check_arr = $contestArr;
            $collection_check_arr['league_id'] = $league_exists['league_id'];
            $collection_info = $this->Contest_model->check_collection_exist($collection_check_arr);
        
        
            if(empty($collection_info['collection_master_id']))
            {

                $collection_insert_array = array();
                $collection_insert_array['league_id'] = $league_exists['league_id'];
                $collection_insert_array['collection_name'] = $contestArr['collection_name'];
                $collection_insert_array['collection_salary_cap']=$contestArr['collection_salary_cap'];
                $collection_insert_array["season_scheduled_date"] = $contestArr['season_scheduled_date'];
                $collection_insert_array["deadline_time"] = $contestArr['deadline_time'];
                $collection_insert_array['added_date'] = format_date();
                $collection_insert_array['modified_date'] = format_date();
                $collection_master_id = $this->Contest_model->save_network_collection($collection_insert_array,$contestArr);
            } 
            else
            {
                $collection_master_id = $collection_info['collection_master_id'];
            }    

            //insert row in network contest 
            if(!empty($collection_master_id))
            {
                $contest_insert_array = array();
                unset($contestArr['prize_distibution_detail']);
                $contest_insert_array['network_collection_master_id'] = $contestArr['collection_master_id'];   
                $contest_insert_array['network_contest_id'] = $contestArr['contest_id'];   
                $contest_insert_array['collection_master_id'] = $collection_master_id;   
                $contest_insert_array['season_scheduled_date'] = $contestArr['season_scheduled_date'];   
                $contest_insert_array['contest_details'] = json_encode($contestArr);   
                $contest_insert_array['network_prize_pool'] = $contestArr['prize_pool'];   
                $contest_insert_array['active'] = 1;//default active/published.   
                $contest_insert_array['date_added'] = format_date();  


                $contest_insert_batch[] = $contest_insert_array; 
                //echo "<pre>";print_r($contest_insert_batch);
                $this->Contest_model->replace_into_batch(NETWORK_CONTEST,$contest_insert_batch); 
            } 
           
        }    


        



        return TRUE;
    }

     /**
     * Used for get public contest details
     * @param int $collection_master_id
     * @param int $contest_id
     * @return array
     */
    public function get_public_contest_post() {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('contest_unique_id', $this->lang->line('contest_unique_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;

        // echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/get_public_contest";
        $api_response =  $this->http_post_request($url,$post_data);
        $api_response = $this->master_response_array($api_response);
        if(!empty($api_response['data']['collection_master_id']))
        {
            $this->load->model("Contest_model");
            $condition_arr = array("collection_master_id"=>$api_response['data']['collection_master_id']);
            $network_collection = $this->Contest_model->check_network_collection_master_id($condition_arr);
            //echo "<pre>";print_r($network_collection);die;
            if(!empty($network_collection))
            {
               $api_response['data']['collection_master_id'] = $network_collection['collection_master_id'];
               $api_response['data']['league_id'] = $network_collection['league_id'];  
            }
        }    
        $this->network_api_response($api_response);
    }

    /**
     * Used for join contest
     * @param int $lineup_master_id
     * @param int $contest_id
     * @return array
     */
    public function join_game_post() 
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        //get network conest details
        $this->load->model("contest/Contest_model");
        $contest_cache_key = "network_contest_" . $post_data['contest_id'];
        $contest = $this->get_cache_data($contest_cache_key);
        if (!$contest) {
            $contest = $this->Contest_model->get_network_contest_detail($post_data);
            if(!empty($contest))
            {
                $contest['contest_details'] = json_decode($contest['contest_details'],true);
                //set master position in cache for 2 hours
                $this->set_cache_data($contest_cache_key, $contest, REDIS_2_HOUR);
            }    
        }

        if(empty($contest))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }    

        // echo "<pre>";print_r($contest);die;

        $network_lineup_master_id = $this->process_network_lineup($post_data,$contest);
        if(empty($network_lineup_master_id))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = ($this->message) ? $this->message : $this->contest_lang['problem_while_join_game'];
            $this->api_response();
        }    
        $post_data['lineup_master_id'] = $network_lineup_master_id; 
        ///exit("created....".$network_lineup_master_id);

        //echo "<pre>";print_r($contest);die;
        $this->entry_fee = $contest['contest_details']['entry_fee'];
        //validate user balance from client side and 
         $is_valid = $this->validation_for_join_game($contest, $post_data);
         if($is_valid)
         {

            $withdraw = array();
            $withdraw["source"] = NW_JOIN_GAME_SOURCE;
            $withdraw["source_id"] = $post_data['lineup_master_id'];
            $withdraw["reference_id"] = $contest['contest_details']['contest_id'];
            $withdraw["reason"] = NW_FANTASY_CONTEST_REASON;
            $withdraw["cash_type"] = 2;
            $withdraw["user_id"] = $this->user_id;
            $withdraw["amount"] = $this->entry_fee;
            $withdraw["max_bonus_allowed"] = $contest['contest_details']['max_bonus_allowed'];
            $withdraw["site_rake"] = (isset($contest['contest_details']['site_rake'])) ? $contest['contest_details']['site_rake'] : 0 ;
            $withdraw['custom_data'] = $contest['contest_details'];
            $this->load->model("user/User_model");
            $join_result = $this->User_model->withdraw($withdraw);
            if(empty($join_result) || $join_result['result'] == 0) 
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = isset($join_result['message']) ? $join_result['message'] : $this->contest_lang['problem_while_join_game'];
                $this->api_response();
            }   
            else
            {
                $post_data['client_id'] = NETWORK_CLIENT_ID;
                $post_data['user_id']   = $this->user_id;
                $post_data['user_unique_id']   = $this->user_unique_id;
                $order_data = $join_result['order_data'];
                //echo "<pre>"; print_r($post_data);

                $url = NETWORK_FANTASY_URL."/fantasy/contest/join_game";
                $api_response =  $this->http_post_request($url,$post_data);
                $master_response = $this->master_response_array($api_response);   
                 //if response is ok from master then proceed other wise refund user entry fee and return back. 
                $current_date = format_date();
                if($master_response['response_code'] == 200)
                {
                    
                    //update order status of contest join transaction entry.
                    $order_update_arr = array(
                        "status" => 1,
                        "modified_date" => $current_date
                    );

                    if(!empty($master_response['data']['lineup_master_contest_id']))
                    {
                        $order_update_arr['source_id']=$master_response['data']['lineup_master_contest_id'];
                        $order_update_arr['reference_id']=$contest['contest_details']['contest_id'];
                    }    
                    
                    $this->User_model->update_network_order_status($order_data['order_id'],$order_update_arr);


                    if(!empty($master_response['data']['lineup_master_contest_id']) && !empty($master_response['data']['contest_detail']))
                    {

                        $master_contest_detail = $master_response['data']['contest_detail']; 
                         $input = array(
                            'contest_name' => $master_contest_detail['contest_name'],
                            'contest_unique_id' => $master_contest_detail['contest_unique_id'],
                            'contest_id' => $master_contest_detail['contest_id'],
                            'entry_fee' => $master_contest_detail['entry_fee'],
                            'prize_pool' => $master_contest_detail['prize_pool'],
                            'prize_type' => $master_contest_detail['prize_type'],
                            'season_scheduled_date' => $master_contest_detail["season_scheduled_date"],
                            "collection_name" => (!empty($master_contest_detail['collection_name'])) ? $master_contest_detail['collection_name'] : $master_contest_detail['contest_name']
                        );

                        $notify_data = array();
                        $notify_data["notification_type"] =NW_JOIN_GAME_NOTI_TYPE;// NW_JOIN_GAME_NOTI_TYPE; //250-JoinNetworkGame, 
                        $notify_data["source_id"] = $master_response['data']['lineup_master_contest_id'];
                        $notify_data["notification_destination"] = 7; //Web,Push,Email
                        $notify_data["user_id"] = $this->user_id;
                        $notify_data["to"] = $this->email;
                        $notify_data["user_name"] = $this->user_name;
                        $notify_data["added_date"] = $current_date;
                        $notify_data["modified_date"] = $current_date;
                        $notify_data["content"] = json_encode($input);
                        $notify_data["subject"] = $this->contest_lang['join_game_email_subject'];
                        $this->load->model('user/User_nosql_model');
                        $this->User_nosql_model->send_notification($notify_data);
                    }    

                    //delete user balance data
                    $user_balance_cache_key = 'user_balance_' . $this->user_id;
                    $this->delete_cache_data($user_balance_cache_key);

                }
                else
                {
                    
                    //refund user deducted amount if join game is failed from master server.
                    $this->User_model->refund_user_join_network_game($this->user_id,$order_data); 

                    //update join game order status in client side if join game failed from master.
                    //update order status of contest join transaction entry.
                    $order_update_arr = array(
                        "status" => 2,
                        "modified_date" => $current_date
                    );

                    $this->User_model->update_network_order_status($order_data['order_id'],$order_update_arr);
                }    

                $this->api_response_arry  = $master_response;
                $this->api_response();
            } 



         } 
         else
         {
            $this->api_response_arry['data']['self_exclusion_limit'] = $this->self_exclusion_limit;
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
         }  
    }


    public function process_network_lineup($post_data,$contest_detail)
    {
       // echo "<pre>";print_r($contest_detail);die;
        $this->load->model("Contest_model");
        $network_lineup_master_data = $this->Contest_model->get_network_lineup_master_detail($post_data);
        if(!empty($network_lineup_master_data['network_lineup_master_id']))
        {
            return $network_lineup_master_data['network_lineup_master_id'];
        }    

        //check for valid line up of user
        $user_lineup = $this->Contest_model->get_single_row("lineup_master_id,collection_master_id,league_id,team_name,team_data", LINEUP_MASTER, array("lineup_master_id" => $post_data['lineup_master_id'], "user_id" => $this->user_id));

        if (empty($user_lineup)) {
            $this->message = $this->contest_lang['provide_a_valid_lineup_master_id'];
            return 0;
        }

        $team_data = json_decode($user_lineup['team_data'],TRUE);
        $collection_player_cache_key = "roster_list_" . $user_lineup['collection_master_id'];
        $players_list = $this->get_cache_data($collection_player_cache_key);
        if (!$players_list) {
            $this->load->model("lineup/Lineup_model");
            $post_data['collection_master_id'] = $user_lineup['collection_master_id'];
            $post_data['league_id'] = isset($user_lineup['league_id']) ? $user_lineup['league_id'] : "";
            $players_list = $this->Lineup_model->get_all_rosters($post_data);
            //set collection players in cache for 2 days
            $this->set_cache_data($collection_player_cache_key, $players_list, REDIS_2_DAYS);
        }

        $player_list_array = array_column($players_list, NULL, 'player_team_id');
        $final_player_list = array();
        foreach ($team_data['pl'] as $player_team_id) {
            $player_info = $player_list_array[$player_team_id];
            if(!empty($player_info)) {
                $captain = 0;
                if($player_team_id == $team_data['c_id']){
                    $captain = 1;
                }else if($player_team_id == $team_data['vc_id']){
                    $captain = 2;
                }
                $lineup = array();
                $lineup['player_id'] = $player_info['player_id'];
                $lineup['player_team_id'] = $player_info['player_team_id'];
                $lineup['player_unique_id'] = $player_info['player_uid'];
                $lineup['position_name'] = $player_info['position'];
                $lineup['player_salary'] = $player_info['salary'];
                $lineup['captain'] = $captain;
                $final_player_list[] = $lineup;
            }
        }
        //prepare data to create lineup on master server.
        $user_lineup_data = array(

            "league_id"             => $contest_detail['contest_details']['league_id'],
            "sports_id"             => $contest_detail['sports_id'],
            "team_name"             => $user_lineup['team_name'],
            "collection_master_id"  => $contest_detail['network_collection_master_id'],
            "lineup"                => $final_player_list
        );

        $user_lineup_data['client_id']     = NETWORK_CLIENT_ID;
        $user_lineup_data['user_id']       = $this->user_id;
        $user_lineup_data['user_unique_id']= $this->user_unique_id;
        $user_lineup_data['user_name']     = $this->user_name;

        //echo json_encode($user_lineup_data);die;
        //echo "<pre>";print_r($user_lineup_data);die;   
        $url = NETWORK_FANTASY_URL."/fantasy/lineup/lineup_proccess_from_client";
        $api_response =  $this->http_post_request($url,$user_lineup_data);
        //echo "<pre>";print_r($api_response);die;
        $master_response = $this->master_response_array($api_response);   
         //if response is ok from master then proceed other wise refund user entry fee and return back. 
        if($master_response['response_code'] == 200 && !empty($master_response['data']['lineup_master_id']))
        { 
            //map master server's lineup master with client.
            $lm_insert_array = array(
                "lineup_master_id"             => $post_data['lineup_master_id'],
                "network_lineup_master_id"     => $master_response['data']['lineup_master_id'],
                "collection_master_id"         => $contest_detail['collection_master_id'],
                "league_id"                    => $contest_detail['league_id'],
                "network_collection_master_id" => $contest_detail['network_collection_master_id'],
                "network_league_id"            => $contest_detail['contest_details']['league_id']

            );

            $this->Contest_model->save_network_lineup_master($lm_insert_array);
            return $master_response['data']['lineup_master_id'];
        }    
        else
        {
            $this->api_response_arry  = $master_response;
            $this->api_response();
        }    



    }



    /**
     * used for validate contest join data
     * @param array $contest
     * @param array $post_data
     * @return array
     */
    public function validation_for_join_game($contest, $post_data) {
        if (empty($contest)) {
            $this->message = $this->contest_lang['invalid_contest'];
            return 0;
        }

        
        if ($this->entry_fee == '0') {
            return 1;
        }

        
        //get user balance
        $this->load->model("user/User_model");
        $balance_arr = $this->User_model->get_user_balance($this->user_id);
        //echo "<pre>";print_r($balance_arr);die;

        $this->user_bal = $balance_arr['real_amount'];
        $this->winning_bal = $balance_arr['winning_amount'];
        
        if ($this->entry_fee > ($this->user_bal + $this->winning_bal)) {
            $this->message = $this->contest_lang['not_enough_balance'];
            $this->enough_balance = 0;
            return 0;
        }else
        {
            $this->self_exclusion_limit = 0;
            //$this->get_app_config_data();
            $allow_self_exclusion = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;        
            //echo "<pre>";print_r($allow_self_exclusion);die;
            if($allow_self_exclusion) 
            {
                $custom_data = $this->app_config['allow_self_exclusion']['custom_data'];
                $default_max_limit = $custom_data['default_limit'];
                //echo "<pre>";print_r($default_max_limit);die;
                $contest_ids = $this->Contest_model->user_join_contest_ids($this->user_id);
                //check for network match also
                $post_data['client_id'] = NETWORK_CLIENT_ID;
                $post_data['user_id']   = $this->user_id;

                $url = NETWORK_FANTASY_URL."/fantasy/contest/user_join_contest_ids";
                $api_response =  $this->http_post_request($url,$post_data);
                $nw_data = json_decode($api_response,TRUE);
                if(!empty($nw_data['data']))
                {
                   $contest_ids = array_merge($contest_ids,$nw_data['data']);
                }    
                //echo "<pre>";print_r($nw_data['data']);die;
                //echo "<pre>";print_r($contest_ids);die;
                if(!empty($contest_ids)) 
                {
                    $this->load->model("user/User_model");
                    $self_exclusion_data = array('user_id' => $this->user_id, 'contest_ids' => $contest_ids, 'max_limit' => $default_max_limit, 'entry_fee' => $this->entry_fee);
                    $this->message = $this->contest_lang['self_exclusion_limit_reached'];
                    $flag =  $this->User_model->check_self_exclusion($self_exclusion_data);
                    //echo "<pre>";print_r($flag);die;
                    if(empty($flag)) 
                    {
                        $this->self_exclusion_limit = 1;
                    }
                    return $flag;
                }
            }

        }
        
        return 1;
    }


    /**
     * Used for get contest details
     * @param int $contest_id
     * @param string $contest_unique_id
     * @return array
     */
    public function get_contest_detail_post() 
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if(!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['client_collection_master_id']=$network_collection['collection_master_id'];
            $post_data['client_league_id']=$network_collection['league_id'];
            // echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_detail";
            $api_response =  $this->http_post_request($url,$post_data);

        }
        $this->network_api_response($api_response);
    }

     /**
     * Used for get user joined count
     * @param int $contest_id
     * @return array
     */
    public function get_user_contest_join_count_post() 
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data              = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {
            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_user_contest_join_count";
            $api_response =  $this->http_post_request($url,$post_data);
        }

        $this->network_api_response($api_response);
    }

    /**
     * used for validate contest promo code
     * @param int $contest_id
     * @param string $promo_code
     * @return array
     */
    public function validate_contest_promo_code_post()
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('promo_code', $this->lang->line('promo_code'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        
        $post_data              = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;

        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/validate_contest_promo_code";
        $api_response =  $this->http_post_request($url,$post_data);
        $this->network_api_response($api_response);
       
    }  

    /**
     * used for get contest invite code
     * @param int $contest_id
     * @return array
     */
    public function old_network_get_contest_invite_code_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_invite_code";
            $api_response =  $this->http_post_request($url,$post_data);
        }
        $this->network_api_response($api_response);
       
    }  

     /**
     * used for get contest invite code
     * @param int $contest_id
     * @return array
     */
    public function get_contest_invite_code_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $season_type = '1';
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("contest/Contest_model");
        $contest = $this->Contest_model->get_network_contest_detail($post_data);
        //echo "<pre>";print_r(json_decode($contest['contest_details'],TRUE));die; 
        $contest = json_decode($contest['contest_details'],TRUE);          
        if (empty($contest)) 
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }
        //check for existence
        $this->load->model("contest/Contest_model");
        $exist = $this->Contest_model->get_single_row("contest_id,code", INVITE, array("contest_id" => $contest['contest_id'], "season_type" => $season_type, "user_id" => 0,"network_contest" => 1));
        //echo "<pre>";print_r($exist);die;
        if (empty($exist)) 
        {
            $code = $this->Contest_model->_generate_contest_code();
            $invite_data = array(
                "contest_id" => $contest['contest_id'],
                "contest_unique_id" => $contest["contest_unique_id"],
                "invite_from" => $this->user_id,
                "code" => $code,
                "season_type" => $season_type,
                "expire_date" => date(DATE_FORMAT, strtotime($contest['season_scheduled_date'])),
                "created_date" => format_date(),
                "status" => 1,
                "network_contest" => 1
            );
            $this->Contest_model->save_invites(array($invite_data));
        } else {
            $code = $exist['code'];
        }

        $this->api_response_arry['data'] = $code;
        $this->api_response();
    }

     /**
     * used for get user joined match list
     * @param int $status
     * @param int $sports_id
     * @return array
     */
    public function get_user_joined_fixture_by_status_post() 
    {
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|callback_check_collection_status');
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {
            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_user_joined_fixture_by_status";
            $api_response =  $this->http_post_request($url,$post_data);
        }
        $this->network_api_response($api_response);
        
    }

    /**
     * used for get user joined contest list
     * @param int $status
     * @param int $collection_master_id
     * @param int $sports_id
     * @return array
     */
    public function get_user_contest_by_status_post() {
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|callback_check_collection_status');
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_user_contest_by_status";
            $api_response =  $this->http_post_request($url,$post_data);
        }    
        $this->network_api_response($api_response);
        
    }


     /**
     * used for get user team list for switch
     * @param int $sports_id
     * @param int $contest_id
     * @return array
     */
    public function get_user_switch_team_list_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            // echo "<pre>";print_r($post_data);die;

            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_user_switch_team_list";
            $api_response =  $this->http_post_request($url,$post_data);
        }

        $this->network_api_response($api_response);
       
    }

    /**
     * used for swith team in joined contest
     * @param int $sports_id
     * @param int $contest_id
     * @param int $lineup_master_id
     * @param int $lineup_master_contest_id
     * @return array
     */
    public function switch_team_contest_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data              = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/switch_team_contest";
            $api_response =  $this->http_post_request($url,$post_data);
        }

        $this->network_api_response($api_response);

    }


    /**
     * used for get contest leaderboard
     * @param int $contest_id
     * @return array
     */
    public function get_contest_leaderboard_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_leaderboard";
            $api_response =  $this->http_post_request($url,$post_data);
        }

        $this->network_api_response($api_response);



    }

    /**
     * used for get user joined contest leaderboard
     * @param int $contest_id
     * @return array
     */
    public function get_contest_user_leaderboard_teams_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
         $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_user_leaderboard_teams";
            $api_response =  $this->http_post_request($url,$post_data);
        }

        $this->network_api_response($api_response);
        
    }

    /**
     * used for get team details with score
     * @param int $lineup_master_contest_id
     * @return array
     */
    public function get_linpeup_with_score_post() {
        $this->form_validation->set_rules('lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_linpeup_with_score";
            $api_response =  $this->http_post_request($url,$post_data);
        }

        $this->network_api_response($api_response);
    }


     /**
     * Used for get contest joined users list
     * @param int $collection_master_id
     * @param int $contest_id
     * @return array
     */
    public function get_contest_users_post() {
        $this->form_validation->set_rules('collection_master_id', $this->lang->line('collection_master_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_users";
            $api_response =  $this->http_post_request($url,$post_data);
            $api_data = json_decode($api_response,TRUE);
            $result = $api_data['data'];
            //echo "<pre>";print_r($result);die;
            if(!empty($result['users']))
            {
                $this->load->model('user/User_model');
                $user_ids = array_unique( array_column($result['users'],'user_id'));
                $user_ids = array_unique($user_ids,$this->user_id);
                $user_details = $this->User_model->get_users_by_ids($user_ids);
                //echo "<pre>";print_r($user_details);die;
                $user_images = array();
                if(!empty($user_details))
                {
                    $user_images = array_column($user_details,'image','user_id');
                    $user_details = array_column($user_details,'user_name','user_id');
                }


                foreach($result['users'] as $key => & $val)
                {
                    //echo "23<pre>";print_r($val);die;
                    //$val['username'] = '';
                   if(isset($user_details[$val['user_id']]) && $val['client_id'] == NETWORK_CLIENT_ID )
                    {
                        $val['image'] =  $user_images[$val['user_id']];
                    }

                    $result['users'] = array_values($result['users']);
                    unset($result['users'][$key]['user_id']);
                    unset($result['users'][$key]['client_id']);
                }
               
                
            }
        }

        //$this->network_api_response($api_response);
        $this->api_response_arry['service_name'] = 'contest/get_contest_users';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
    }


     /**
     * used for download joined users team list
     * @param int $sports_id
     * @param int $contest_id
     * @return array
     */
    public function download_contest_teams_post() {
        $this->form_validation->set_rules('sports_id', $this->lang->line('sports_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/download_contest_teams";
            $api_response =  $this->http_post_request($url,$post_data);
        }

        $this->network_api_response($api_response);
    }

    public function update_nc_data_get()
    {
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->get_data_for_update();
        if(empty($network_collection))
        {
            exit("not found");
        }

        foreach ($network_collection as $key => $value) 
        {
            
            $update_arr = array(
                "sports_id" => $value['sports_id'],
                "league_id" => $value['league_id']
            );

            $where_arr = array(
                "id" => $value['id'],
                "network_collection_master_id" => $value['network_collection_master_id'],
                "collection_master_id" => $value['collection_master_id']

            );

            $this->Contest_model->update_network_contest_details($update_arr,$where_arr);    



        }

        exit("data updated.....");



    }

    /*************New leaderboard*******/

    /**
     * [get_new_contest_leaderboard description]
     * @uses :- get new contest leaderboard
     * @param Number prediction master id
     */
    public function get_new_contest_leaderboard_post()
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        $current_date = format_date();
       
        $_POST = $post;
        
        $post_data = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {

            //echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_new_contest_leaderboard";
            //$url = "local.networkserver.com/fantasy/contest/get_new_contest_leaderboard";
            //echo "<pre>";print_r($url);die;
            $api_response =  $this->http_post_request($url,$post_data);
            $api_data = json_decode($api_response,TRUE);
            //echo "<pre>";print_r($api_data);die;
            $result['total'] = $api_data['data']['total'];
            $result['prize_data'] = $api_data['data']['prize_data'];
            $top_three= $api_data['data']['top_three'];

            $top_user_ids = array();
            if(!empty($top_three))
            {
                $top_user_ids = array_column($top_three,'user_id');
            }

            $own_leaderboard = array();
            if($this->user_id)
            {
                $own_result= $api_data['data'];
                //echo "<pre>";print_r($own_result);die;
                foreach($own_result['own'] as $key => $row)
                {
                    
                    if($row['user_id'] == $this->user_id && $row['client_id'] == NETWORK_CLIENT_ID )
                    {
                        $own_leaderboard[] = $row;
                    }
                }
            }
            $result['other_list'] = $api_data['data']['other_list'];
            if(!empty($result['other_list']))
            {
                $this->load->model('user/User_model');
                $user_ids = array_unique( array_column($result['other_list'],'user_id'));
                //echo "<pre>";print_r($user_ids);die;
                $user_ids = array_unique(array_merge($user_ids,$top_user_ids,array($this->user_id)));
                $user_details = $this->User_model->get_users_by_ids($user_ids);
                $user_images = array();
                if(!empty($user_details))
                {
                    $user_images = array_column($user_details,'image','user_id');
                    $user_details = array_column($user_details,'user_name','user_id');
                }
                //echo "<pre>";print_r($user_images);die;
                foreach($result['other_list'] as $key => & $val)
                {
                    $val['username'] = '';
                    if(isset($user_details[$val['user_id']]) && $val['client_id'] == NETWORK_CLIENT_ID )
                    {
                        //$val['user_name'] =  $user_details[$val['user_id']];
                        $val['image'] =  $user_images[$val['user_id']];
                    }

                    if($val['user_id'] == $this->user_id && $val['client_id'] == NETWORK_CLIENT_ID)
                    {
                        //$own_leaderboard[] = $result['other_list'][$key];
                        unset($result['other_list'][$key]);
                    }

                    if(!empty($val['prize_data']))
                    {
                        $val['prize_data'] = $val['prize_data'];
                    }

                    unset($result['other_list'][$key]['user_id']);
                    unset($result['other_list'][$key]['client_id']);
                }

                $result['other_list'] = array_values($result['other_list']);

            }

            foreach($top_three as $key => & $val)
            {
                $val['username'] = '';
                if(isset($user_details[$val['user_id']]) && $val['client_id'] == NETWORK_CLIENT_ID)
                {
                    //$val['user_name'] =  $user_details[$val['user_id']];
                    $val['image'] =  $user_images[$val['user_id']];
                }

                if(!empty($val['prize_data']))
                {
                    
                    $val['prize_data'] = $val['prize_data'];
                }

                unset($top_three[$key]['user_id']);
                unset($top_three[$key]['client_id']);
            }

            if(!empty($own_leaderboard))
            {
                foreach($own_leaderboard as $key => &$val_own)
                {
                    if(!empty($val_own['prize_data']))
                    {
                        $val_own['prize_data'] = $val_own['prize_data'];
                    }

                    $val_own['username'] = '';
                    if(isset($user_details[$val_own['user_id']]))
                    {
                        //$val_own['user_name'] =  $user_details[$val_own['user_id']];
                        $val_own['image'] =  $user_images[$val_own['user_id']];
                    }
                    unset($own_leaderboard[$key]['user_id']);
                    unset($own_leaderboard[$key]['client_id']);
                }
                $result['own'] =$own_leaderboard;
            }
    
        
            $result['top_three'] =$top_three;
        }

        

        //echo "<pre>";print_r($result);die;

        $this->api_response_arry['service_name'] = 'contest/get_new_contest_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
    }


     /**
     * used for get contest leadrbord to show score card and score updateinformation
     * @param int $contest_id
     * @return array
     */
    public function get_contest_score_card_post() 
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $post_data['contest_id'] = $post_data['contest_id'];
        $post_data['sports_id'] = $post_data['sports_id'];
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        
        $api_response           = array();
        
        $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_score_card";
            $api_response =  $this->http_post_request($url,$post_data);
        $this->network_api_response($api_response);
        
    }

    /**
     * Used for join contest
     * @param int $lineup_master_id
     * @param int $contest_id
     * @return array
     */
    public function multiteam_join_game_post() 
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        if(!isset($post_data['lineup_master_id']) || empty($post_data['lineup_master_id'])){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['select_min_one_team'];
            $this->api_response();
        }
        $lineup_master_ids = $post_data['lineup_master_id'];
        //map network lineup master
        //echo "<pre>";print_r($lineup_master_ids);
        $this->load->model("Contest_model");
        $network_lineup_master_ids = $this->Contest_model->get_network_lineup_master_ids($lineup_master_ids);
        $network_lineup_master_ids = explode(",", $network_lineup_master_ids['network_lineup_master_id']);
        //echo "<pre>";print_r($network_lineup_master_ids);die;
        $lineup_master_ids = $network_lineup_master_ids;
        $current_date = format_date();
        
        $post_data = $this->input->post();
        $post_data['lineup_master_id'] = $lineup_master_ids;
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $network_collection = $this->Contest_model->check_network_collection_master_id($post_data);
        //echo "<pre>";print_r($network_collection);die;

        if(!empty($network_collection['network_collection_master_id']))
        {
            $post_data['client_collection_master_id']=$network_collection['collection_master_id'];
            $post_data['client_league_id']=$network_collection['league_id'];
            // echo "<pre>";print_r($post_data);die;
            $url = NETWORK_FANTASY_URL."/fantasy/contest/get_contest_detail";
            //$url = "http://local.networkserver.com"."/fantasy/contest/get_contest_detail";
            $api_response =  $this->http_post_request($url,$post_data);

        }
        $contest_data = json_decode($api_response,TRUE);
        $contest = $contest_data['data'];
        //echo "<pre>";print_r($contest);die;
        $this->entry_fee = $contest['entry_fee'];
        //echo "<pre>";print_r($this->entry_fee);die;
        $this->contest_unique_id = $contest['contest_unique_id'];
        $is_valid = $this->validation_for_multiple_join_game($contest, $post_data);
        //echo "<pre>";print_r($is_valid);die;
        if ($is_valid) 
        {
            
                $is_success = 0;
                $team_error = array();
                //echo "<pre>";print_r($contest);die;
                foreach($lineup_master_ids as $lineup_master_id)
                {
                    $post_data['lineup_master_id'] = $lineup_master_id;
                    $post_data['contest_id'] = $contest['contest_id'];

                    $withdraw = array();
                    $withdraw["source"] = NW_JOIN_GAME_SOURCE;
                    $withdraw["source_id"] = $post_data['lineup_master_id'];
                    $withdraw["reference_id"] = $contest['contest_id'];
                    $withdraw["reason"] = NW_FANTASY_CONTEST_REASON;
                    $withdraw["cash_type"] = 2;
                    $withdraw["user_id"] = $this->user_id;
                    $withdraw["amount"] = $this->entry_fee;
                    $withdraw["max_bonus_allowed"] = 0;
                    $withdraw["site_rake"] = (isset($contest['site_rake'])) ? $contest['site_rake'] : 0 ;
                    $withdraw['custom_data'] = $contest;
                    $this->load->model("user/User_model");
                    $join_result = $this->User_model->withdraw($withdraw);
                    if(empty($join_result) || $join_result['result'] == 0) 
                    {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = isset($join_result['message']) ? $join_result['message'] : $this->contest_lang['problem_while_join_game'];
                        $this->api_response();
                    }   
                    else
                    {
                        $post_data['client_id'] = NETWORK_CLIENT_ID;
                        $post_data['user_id']   = $this->user_id;
                        $post_data['user_unique_id']   = $this->user_unique_id;
                        $order_data = $join_result['order_data'];
                        //echo "<pre>"; print_r($post_data);

                        $url = NETWORK_FANTASY_URL."/fantasy/contest/join_game";
                        //$url = "http://local.networkserver.com"."/fantasy/contest/join_game";
                        $api_response =  $this->http_post_request($url,$post_data);
                        $master_response = $this->master_response_array($api_response);   
                         //if response is ok from master then proceed other wise refund user entry fee and return back. 
                        $current_date = format_date();
                        if($master_response['response_code'] == 200)
                        {
                            $is_success = 1;
                            //update order status of contest join transaction entry.
                            $order_update_arr = array(
                                "status" => 1,
                                "modified_date" => $current_date
                            );

                            if(!empty($master_response['data']['lineup_master_contest_id']))
                            {
                                $order_update_arr['source_id']=$master_response['data']['lineup_master_contest_id'];
                                $order_update_arr['reference_id']=$contest['contest_id'];
                            }    
                            
                            $this->User_model->update_network_order_status($order_data['order_id'],$order_update_arr);
                            if(!empty($master_response['data']['lineup_master_contest_id']) && !empty($master_response['data']['contest_detail']))
                            {

                                $master_contest_detail = $master_response['data']['contest_detail']; 
                                 $input = array(
                                    'contest_name' => $master_contest_detail['contest_name'],
                                    'contest_unique_id' => $master_contest_detail['contest_unique_id'],
                                    'contest_id' => $master_contest_detail['contest_id'],
                                    'entry_fee' => $master_contest_detail['entry_fee'],
                                    'prize_pool' => $master_contest_detail['prize_pool'],
                                    'prize_type' => $master_contest_detail['prize_type'],
                                    'season_scheduled_date' => $master_contest_detail["season_scheduled_date"],
                                    "collection_name" => (!empty($master_contest_detail['collection_name'])) ? $master_contest_detail['collection_name'] : $master_contest_detail['contest_name']
                                );

                                $notify_data = array();
                                $notify_data["notification_type"] =NW_JOIN_GAME_NOTI_TYPE;// NW_JOIN_GAME_NOTI_TYPE; //250-JoinNetworkGame, 
                                $notify_data["source_id"] = $master_response['data']['lineup_master_contest_id'];
                                $notify_data["notification_destination"] = 7; //Web,Push,Email
                                $notify_data["user_id"] = $this->user_id;
                                $notify_data["to"] = $this->email;
                                $notify_data["user_name"] = $this->user_name;
                                $notify_data["added_date"] = $current_date;
                                $notify_data["modified_date"] = $current_date;
                                $notify_data["content"] = json_encode($input);
                                $notify_data["subject"] = $this->contest_lang['join_game_email_subject'];
                                $this->load->model('user/User_nosql_model');
                                $this->User_nosql_model->send_notification($notify_data);
                            }    

                            //delete user balance data
                            $user_balance_cache_key = 'user_balance_' . $this->user_id;
                            $this->delete_cache_data($user_balance_cache_key);
                        }
                        else
                        {
                            //refund user deducted amount if join game is failed from master server.
                            $this->User_model->refund_user_join_network_game($this->user_id,$order_data); 

                            //update join game order status in client side if join game failed from master.
                            //update order status of contest join transaction entry.
                            $order_update_arr = array(
                                "status" => 2,
                                "modified_date" => $current_date
                            );

                            $this->User_model->update_network_order_status($order_data['order_id'],$order_update_arr);
                        }
                    }    
                }

                if ($is_success == 1) 
                {
                    //delete user balance data
                    $user_balance_cache_key = 'user_balance_' . $this->user_id;
                    $this->delete_cache_data($user_balance_cache_key);

                    //delete contest cache
                    $this->delete_cache_data($contest_cache_key);
                    
                    $tm_count = count($lineup_master_ids) - count($team_error);
                    $join_msg = str_replace("{TEAM_COUNT}",$tm_count,$this->contest_lang['multiteam_join_game_success']);
                    $this->api_response_arry['message'] = $join_msg;
                    $this->api_response();
                } else 
                {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
                    $this->api_response();
                }
            
        }
        else 
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
        }
    }

    /**
     * used for validate contest join data
     * @param array $contest
     * @param array $post_data
     * @return array
     */
    public function validation_for_multiple_join_game($contest, $post_data) 
    {
        //echo "<pre>";print_r($post_data);die;
        if (empty($contest)) {
            $this->message = $this->contest_lang['invalid_contest'];
            return 0;
        }

        //for manage collection wise deadline
        $lineup_master_ids = $post_data['lineup_master_id'];
        $current_date = format_date();
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        if (isset($contest['deadline_time']) && $contest['deadline_time'] >= 0) {
            $deadline_time = $contest['deadline_time'];
        }
        $current_time = date(DATE_FORMAT, strtotime($current_date . " +" . $deadline_time . " minute"));

        //check for match schedule date
        if (strtotime($contest['season_scheduled_date']) < strtotime($current_time)) {
            $this->message = $this->contest_lang['contest_already_started'];
            return 0;
        }

        //check for full contest
        if ($contest['total_user_joined'] >= $contest['size']) {
            $this->message = $this->contest_lang['contest_already_full'];
            return 0;
        }

        //if contest closed
        if ($contest['status'] !== '0') {
            $this->message = $this->contest_lang['contest_closed'];
            return 0;
        }

        //$user_join_data = $this->Contest_model->get_user_contest_join_count($contest);
        $post_data              = $this->input->post();
        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['user_id']   = $this->user_id;
        $post_data['user_unique_id']   = $this->user_unique_id;
        $api_response           = array();
        $this->load->model("Contest_model");
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/get_user_contest_join_count";
        //$url = "http://local.networkserver.com/fantasy/contest/get_user_contest_join_count";
       
        $api_response =  $this->http_post_request($url,$post_data);
        $response_data = json_decode($api_response,TRUE); 
        $user_join_data = $response_data['data'];
        //echo "<pre>";print_r($user_join_data);die;
        $user_join_count = 0;
        if (isset($user_join_data['user_joined_count'])) {
            $user_join_count = $user_join_data['user_joined_count'];
        }
        //check for multi lineup
        if ($contest['multiple_lineup'] > 0 && $contest['multiple_lineup'] == $user_join_count) {
            $this->message = $this->contest_lang['you_already_joined_to_max_limit'];
            return 0;
        } else if ($contest['multiple_lineup'] == 0 && $user_join_count > 0) {
            $this->message = $this->contest_lang['join_multiple_time_error'];
            return 0;
        }

        //check multi_entry limit
        if($contest['multiple_lineup'] < count($lineup_master_ids)){
            $ml_limit_msg = str_replace("{TEAM_LIMIT}",$contest['multiple_lineup'],$this->contest_lang['contest_max_allowed_team_limit_exceed']);
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $ml_limit_msg;
            $this->api_response();
        }

        //validate already joined
        //$check_team_join = $this->Contest_model->get_single_row("count(lineup_master_contest_id) as total", LINEUP_MASTER_CONTEST, array("contest_id" => $post_data['contest_id'],"lineup_master_id IN(".implode(',',$lineup_master_ids).")"=>NULL));

        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['lineup_master_ids'] = $lineup_master_ids;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/check_already_joined_contest";
        //$url = "http://local.networkserver.com/fantasy/contest/check_already_joined_contest";
      
        $api_response =  $this->http_post_request($url,$post_data);
        $response_data = json_decode($api_response,TRUE);
        $check_team_join = $response_data['data'];
        //echo "<pre>";print_r($check_team_join);die;
        if(!empty($check_team_join) && $check_team_join['total'] > 0){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['already_joined_with_teams'];
            $this->api_response();
        }

        //check for valid line up of user
        //$user_lineup = $this->Contest_model->get_single_row("count(lineup_master_id) as total,GROUP_CONCAT(DISTINCT collection_master_id) as collection_master_id", LINEUP_MASTER, array("lineup_master_id IN(".implode(',',$lineup_master_ids).")" => NULL, "user_id" => $this->user_id));

        $post_data['client_id'] = NETWORK_CLIENT_ID;
        $post_data['lineup_master_ids'] = $lineup_master_ids;
        $post_data['user_id'] = $this->user_id;
        $url = NETWORK_FANTASY_URL."/fantasy/contest/valid_lineup_for_user";
        //$url = "http://local.networkserver.com/fantasy/contest/valid_lineup_for_user";
      
        $api_response =  $this->http_post_request($url,$post_data,1);
        $response_data = json_decode($api_response,TRUE);
        $user_lineup = $response_data['data'];
        //echo "<pre>";print_r($user_lineup);die;
        if (empty($user_lineup) || $user_lineup['total'] != count($lineup_master_ids)) {
            $this->message = $this->contest_lang['provide_a_valid_lineup_master_id'];
            return 0;
        }
        $user_lineup['collection_master_id'] = explode(",",$user_lineup['collection_master_id']);
        //print_r($user_lineup);die;
        if (count($user_lineup['collection_master_id']) != "1" || !in_array($contest['network_collection_master_id'],$user_lineup['collection_master_id'])) 
        {
            $this->message = $this->contest_lang['not_a_valid_team_for_contest'];
            return 0;
        }
        
        if ($this->entry_fee == '0') {
            return 1;
        }

        $total_entry_fee = $this->entry_fee * count($lineup_master_ids);
        //echo $total_entry_fee;die;
       
        //get user balance
        $this->load->model("user/User_model");
        $balance_arr = $this->User_model->get_user_balance($this->user_id);
        
        $this->user_bal = $balance_arr['real_amount'];
        $this->winning_bal = $balance_arr['winning_amount'];
        
        if ($total_entry_fee > ($this->user_bal + $this->winning_bal)) {
            $this->message = $this->contest_lang['not_enough_balance'];
            $this->enough_balance = 0;
            return 0;
        }
        //echo "<pre>";print_r($this->currency_type);die;
        // for self exclusion
        if($this->currency_type == "1")
        {
            $this->self_exclusion_limit = 0;
            //$this->get_app_config_data();
            $allow_self_exclusion = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;
            //echo "<pre>";print_r($allow_self_exclusion);die;        
            if($allow_self_exclusion) {
                $custom_data = $this->app_config['allow_self_exclusion']['custom_data'];
                $default_max_limit = $custom_data['default_limit'];
                $contest_ids = $this->Contest_model->user_join_contest_ids($this->user_id);
                //for network fantasy
                if(ALLOW_NETWORK_FANTASY == 1)
                {
                    //check for network match also
                    $post_data['client_id'] = NETWORK_CLIENT_ID;
                    $post_data['user_id']   = $this->user_id;

                    $url = NETWORK_FANTASY_URL."/fantasy/contest/user_join_contest_ids";
                    $api_response =  $this->http_post_request($url,$post_data);
                    $nw_data = json_decode($api_response,TRUE);
                    if(!empty($nw_data['data']))
                    {
                       $contest_ids = array_merge($contest_ids,$nw_data['data']);
                    }    
                }    

                if(!empty($contest_ids)) {
                    $this->load->model("user/User_model");
                    $self_exclusion_data = array('user_id' => $this->user_id, 'contest_ids' => $contest_ids, 'max_limit' => $default_max_limit, 'entry_fee' => $total_entry_fee);
                    $this->message = $this->contest_lang['self_exclusion_limit_reached'];
                    $flag =  $this->User_model->check_self_exclusion($self_exclusion_data);
                    if(empty($flag)) {
                        $this->self_exclusion_limit = 1;
                    }
                    return $flag;
                }
            }
        }
        return 1;
    }


    //Network contest auto publish when create from network server
    private function contest_auto_publish($sports_id)
    {
        if(!isset($sports_id) && empty($sports_id))
        {
            $sports_id = 7;
        }  
        $this->load->model("Contest_model");
        $contest_data = $this->Contest_model->get_contest_auto_publish($sports_id);
        //echo "<pre>";print_r($contest_data);die;
        if(isset($contest_data) && !empty($contest_data))
        {    
            foreach ($contest_data as $key => $value) 
            {
                $post_data = array();
                $post_data["id"]                    = $value["id"];
                $post_data["network_contest_id"]    = $value["network_contest_id"];
                $post_data["sports_id"]             = $value["sports_id"];
                $post_data["league_id"]             = $value["league_id"];
                //echo "<pre>";print_r($post_data);die;
                $url = WEBSITE_URL."adminapi/nw_contest/publish_network_contest";
                $api_response =  $this->http_post_request($url,$post_data);
                //echo "<pre>";print_r($api_response);die;
            }
        }    
        return true;
    }





}
