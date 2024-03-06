<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Contest extends Common_Api_Controller {
    public function __construct() {
        parent::__construct();
        $_POST = $this->post();
        $this->load->model('admin/Contest_model');
    } 

     /**
     * Create Contest
     * @param
     * @return string
     */

    public function create_contest_post() {
            if ($this->input->post()) {
                $game_data = $this->input->post();
                $this->form_validation->set_rules('sports_id', $this->lang->line('sport'), 'trim|required|is_natural_no_zero');
                $this->form_validation->set_rules('group_id', $this->lang->line('group'), 'trim|required|is_natural_no_zero');
                $this->form_validation->set_rules('league_id', $this->lang->line('league'), 'trim|required|is_natural_no_zero');           
                $this->form_validation->set_rules('contest_name', $this->lang->line('game_name'), 'trim|required|max_length[50]');
                $this->form_validation->set_rules('minimum_size', 'minimum size', 'trim|required|is_natural_no_zero');
                $this->form_validation->set_rules('size', 'size', 'trim|required|is_natural_no_zero');
                $this->form_validation->set_rules('multiple_lineup', 'multiple lineup', 'trim|required');
                $this->form_validation->set_rules('entry_fee', $this->lang->line('entry_fee'), 'trim|required');
                $this->form_validation->set_rules('site_rake', $this->lang->line('site_rake'), 'trim|required');
                $this->form_validation->set_rules('max_bonus_allowed', $this->lang->line('max_bonus_allowed'), 'trim|required');
                $this->form_validation->set_rules('prize_type', $this->lang->line('prize_type'), 'trim|required');
                $this->form_validation->set_rules('prize_pool', $this->lang->line('prize_pool'), 'trim|required');
                $this->form_validation->set_rules('entry_fee_type', 'entry fee type', 'trim|required');

                if (!$this->form_validation->run()) {
                    $this->send_validation_errors();
                 }           
        

                if ($this->input->post('prize_pool_type') == '0') {
                    $this->form_validation->set_rules('master_contest_type_id', $this->lang->line('number_of_winner_id'), 'trim|required|is_natural_no_zero');
                }

                if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
                {
                    $this->form_validation->set_rules('sponsor_name', $this->lang->line("sponsor_name"),'trim|max_length[60]');
                    $this->form_validation->set_rules('sponsor_logo', $this->lang->line("sponsor_logo"),'trim|required|max_length[50]');
                    $this->form_validation->set_rules('sponsor_contest_dtl_image', $this->lang->line("sponsor_contest_dtl_image"),'trim|required|max_length[50]');
                    $this->form_validation->set_rules('sponsor_link', $this->lang->line("sponsor_link"),'trim|max_length[255]');
                }
            
                $multiple_lineup = $this->input->post('multiple_lineup');
                $prize_pool_type = $this->input->post('prize_pool_type');
                $prize_pool = 0;
                $guaranteed_prize = '0';
                if ($game_data['prize_pool_type'] == '1') {
                    $guaranteed_prize = '1';
                    
                } else if ($game_data['prize_pool_type'] == '2') {
                    $guaranteed_prize = '2';
                    $game_data['site_rake'] = 0;

                }

                if ($multiple_lineup && $multiple_lineup > $game_data['size']) {
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line('invalid_game_multiple_lineup_size');
                    $this->api_response();
                }

                $prize_details_inputs['entry_fee'] = $game_data['entry_fee'];
                $prize_details_inputs['site_rake'] = $game_data['site_rake'];

                if ($prize_pool_type == '0') {
                    $game_data['is_custom_prize_pool'] = 0;
                } else if ($prize_pool_type == '1' || $prize_pool_type == '2') {
                    $game_data['is_custom_prize_pool'] = 1;
               
                }

                $prize_details_inputs['size'] = $game_data['minimum_size'];
         
                $payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
                $max_winners = array_column($payout_data, "max");
                $max_winners = max($max_winners);
                if($max_winners > $game_data['size']){
                    $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = "You can't define winner more then size.";
                    $this->api_response();
                }
                
                if($game_data['prize_pool_type'] == '1' || $game_data['prize_pool_type'] == '2')
                {
                    $prize_pool = $game_data['prize_pool'];
                    $payout_data = isset($game_data['payout_data']) ? $game_data['payout_data'] : array();
                }
            
                $season_games = $this->input->post('season_id');                
             
                $season_data = $this->Contest_model->get_single_row("*",SEASON,array("season_id" => $game_data['season_id']));

                 if (empty($season_data)) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = "Invalid Season ID";
                    $this->api_response();
                 }

                $schedule_date = $season_data['scheduled_date'];           

                $max_prize_pool = 0;
                //change values for percentage case
                foreach($payout_data as $key=>$prize){
                    $amount = $prize['amount'];
                    if(isset($game_data['prize_value_type']) && $game_data['prize_value_type'] == 1 && $prize['prize_type'] != "3"){
                        $payout_data[$key]['per'] = $prize['amount'];
                        $person_count = ($prize['max'] - $prize['min']) + 1;
                        $per_person = ((($prize_pool * $prize['amount']) / 100) / $person_count);
                        $amount = $per_person;
                        $payout_data[$key]["amount"] = number_format($per_person,"2",".","");
                    }
                    if(isset($prize['prize_type']) && $prize['prize_type'] == 1){
                        if(isset($prize['max_value'])){
                            $mx_amount = $prize['max_value'];
                        }else{
                            $mx_amount = $amount;
                        }
                        $max_prize_pool = $max_prize_pool + $mx_amount;
                    }
                }
                $payout_data = json_encode($payout_data);    

        
                $game_data['collection'] = array();
                $game_data['collection']['league_id'] = $game_data['league_id'];      
                $game_data['collection']['added_date'] = format_date();
                $game_data['collection']['modified_date'] = format_date();
              
                if(isset($game_data['contest_unique_id'])){
                    $contest_unique_id = $game_data['contest_unique_id'];
                }else{
                    $contest_unique_id = random_string('alnum', 9);
                }

                if(isset($game_data['contest_template_id'])){
                    $contest_template_id = $game_data['contest_template_id'];
                }else{
                    $contest_template_id = NULL;
                }

                $contest_data = array(
                        "contest_unique_id"         => $contest_unique_id,
                        "sports_id"                 => $game_data['sports_id'],
                        "league_id"                 => $game_data['league_id'],
                        "group_id"                  => $game_data['group_id'],
                        "contest_name"              => $game_data['contest_name'],
                        "contest_title"             => isset($game_data['contest_title']) ? $game_data['contest_title'] : "",
                        "contest_description"             => isset($game_data['contest_description']) ? $game_data['contest_description'] : "",
                        "minimum_size"              => $game_data['minimum_size'],
                        "size"                      => $game_data['size'],
                     
                        "contest_template_id"       => $contest_template_id,
                        "season_id"                 => $game_data['season_id'],
                        "multiple_lineup"           => $multiple_lineup,
                        "entry_fee"                 => $game_data['entry_fee'],
                        "site_rake"                 => $game_data['site_rake'],
                        "max_bonus_allowed"         => $game_data['max_bonus_allowed'],
                        "currency_type"             => $game_data['entry_fee_type'],
                        "prize_type"                => $game_data['prize_type'],
                        "prize_pool"                => $prize_pool,
                        "max_prize_pool"            => $max_prize_pool,
                        "prize_distibution_detail"  => $payout_data,
                        "scheduled_date"            => $schedule_date,                  
                        "guaranteed_prize"          => $guaranteed_prize,
                        "is_custom_prize_pool"      => $game_data['is_custom_prize_pool'],
                        "is_auto_recurring"         => isset($game_data['is_auto_recurring']) && $game_data['is_auto_recurring'] ? 1 : 0,                    
                        "is_pin_contest"            => isset($game_data['is_pin_contest']) && $game_data['is_pin_contest'] ? 1 : 0,
                        /*"is_tie_breaker"            => $game_data['is_tie_breaker'],*/
                        "prize_value_type"          => isset($game_data['prize_value_type']) ? $game_data['prize_value_type'] : 0,                    
                        "status"                    => 0,
                        "added_date"                => format_date()                 
                    );

                $tmp_game_data = $contest_data;
                $tmp_game_data['total_user_joined'] = $tmp_game_data['minimum_size'];        
                //add sponsor if checked 
                if(isset($game_data['set_sponsor']) && $game_data['set_sponsor'] == 1)
                {
                    $contest_data['sponsor_name'] = $game_data['sponsor_name'];
                    $contest_data['sponsor_logo'] = $game_data['sponsor_logo'];
                    // $contest_data['sponsor_contest_dtl_image'] = $game_data['sponsor_contest_dtl_image'];
                    $contest_data['sponsor_link'] = (isset($game_data['sponsor_link']) && $game_data['sponsor_link'] != '') ? $game_data['sponsor_link'] : NULL;
                }
                if ($contest_data['is_auto_recurring'] == 1)
                {
                    $contest_data['base_prize_details'] = json_encode(array("prize_pool" => $prize_pool, "prize_distibution_detail" => $payout_data));
                }

                $collection_data = $game_data['collection']; 

                $collection_data["league_id"]           = $game_data['league_id'];
                $data_array = array();
                $data_array['collection']               = $collection_data;          
                $data_array['contest']                  = $contest_data;

                if(is_array($season_games)){
                    $data_array['season_games'] = $season_games;
                }else{
                    $data_array['season_games'] = array($season_games);
                }
                
                if(!isset($game_data['contest_unique_id']))
                {
                    if($game_data['entry_fee'] == 0 && $prize_pool > 0 && $contest_data['is_auto_recurring']==1) 
                    {
                        $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['status']          = FALSE;
                        $this->api_response_arry['message']         = $this->lang->line('auto_recurrent_create_error');
                        $this->api_response();
                    }
                    
                    $contest_id = $this->Contest_model->save_contest($data_array);

                    $this->api_response_arry['data']            = $contest_id;
                    $this->api_response_arry['message']         = "Contest added successfully";

                }
                else
                {
                    $post  = $this->post();
            
                    $is_update  = $this->Contest_model->update_contest($post);
                    
                    if($is_update)
                    {
                        //delete contest cache
                        $contest = $this->Contest_model->get_single_row('contest_id',CONTEST,array('contest_unique_id' => $post['contest']["contest_unique_id"]));

                        $match_cache_key = 'contest_'.$contest['contest_id']."_match_list";
                        $this->delete_cache_data($match_cache_key);

                        $sports_id = 7;
                        if(isset($post['sports_id']) && $post['sports_id'] != ""){
                            $sports_id = $post['sports_id'];
                        }
                        $filter_cache_key = 'lobby_filters_'.$sports_id;
                        $this->delete_cache_data($filter_cache_key);

                        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                        $this->api_response_arry['data']          = $post['contest']['contest_unique_id'];
                        $this->api_response_arry['message']       = "Contest update successfully";
                    }
                    else
                    {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['data']          = $post['contest_unique_id'];
                        $this->api_response_arry['message']       = "Contest not update";
                    }
                }

                
                $this->api_response_arry['status']          = TRUE;
                $this->api_response();
            }
            else
            {
                $this->api_response_arry['data']            = array();
                $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['status']          = FALSE;
                $this->api_response_arry['message']         = $this->lang->line('invalid_parameter');
                $this->api_response();
            }
    }

    /**
     * Create template Contest
     * @param
     * @return string
     */
    public function create_template_contest_post() {
        $post_data = $this->input->post();
        $this->form_validation->set_rules('season_id', 'Season id', 'trim|required');  
 
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }      


        if (empty($post_data['selected_templates'])) {
            $this->response(array(config_item('rest_status_field_name') => FALSE, 'message' => "Please select atleast one template."), rest_controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->load->model('Contest_template_model');
   
        // $season_scheduled_date = $post_data['scheduled_date'];
        $template_list         = $this->Contest_template_model->get_template_details_for_create_contest($post_data); 

        if (empty($template_list)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['service_name']  = "create_template_contest";
            $this->api_response_arry['message']       = "Template details not found.";
            $this->api_response_arry['global_error']  = "Template details not found.";
            $this->api_response();
        }    

        $this->load->model('season_model');

        $season_data = $this->season_model->get_single_row("*",SEASON,array("season_id" => $post_data['season_id']));    

         if (empty($season_data)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid Season ID";
            $this->api_response();
         }

        $schedule_date = $season_data['scheduled_date'];

        $result = 0; 
      
        foreach ($template_list as $game_data) {        

            $game_data['contest_unique_id']  = random_string('alnum', 9);
            $game_data['league_id']          = $post_data['league_id'];
            $game_data['season_id']          = $post_data['season_id'];
            $game_data['contest_name']       = $game_data['template_name'];
            $game_data['contest_title']      = isset($game_data['template_title']) ? $game_data['template_title'] : "";         
            $game_data['scheduled_date']     = $schedule_date;
            $game_data['status'] = 0;
            $game_data['added_date']         = format_date();
            $game_data['modified_date']      = format_date();

            $payout_data = json_decode($game_data['prize_distibution_detail'],TRUE);
            $max_prize_pool = 0;
            //change values for percentage case
            if(isset($game_data['max_prize_pool']) && $game_data['max_prize_pool'] > 0){
                $max_prize_pool = $game_data['max_prize_pool'];
            }else{
                foreach($payout_data as $key=>$prize){

                    $amount = $prize['amount'];

                    if(isset($prize['prize_type']) && $prize['prize_type'] == 1){

                        if(isset($prize['max_value'])){

                            $mx_amount  = $prize['max_value'];

                        }else{

                            $mx_amount  = $amount;

                        }

                        $max_prize_pool = $max_prize_pool + $mx_amount;
                    }
                }
            }
            $game_data['max_prize_pool'] = $max_prize_pool;

            if ($game_data['is_auto_recurring'] == 1) {
                $game_data['base_prize_details'] = json_encode(array("prize_pool" => $game_data['prize_pool'], "prize_distibution_detail" => $game_data['prize_distibution_detail']));
            }

            $tmp_game_data = $game_data;
            $tmp_game_data['total_user_joined'] = $tmp_game_data['minimum_size'];           
            unset($game_data['template_name']);
            unset($game_data['template_title']);
            unset($game_data['template_description']);  

            $this->load->model('Contest_model');

            $result = $this->Contest_model->save_template_contest($game_data);
            //var_dump($result); die;
        }

        if ($result) {
          
            // push notification before 15 min to go live end

            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['message'] = "Contest created for selected template.";
            $this->api_response();
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('no_change');
            $this->api_response();
        }
    }


   /**
     * get fixture Contest
     * @param
     * @return string
     */

    public function get_fixture_contest_post() {

        $post_data = $this->input->post();
        $show_cancel = 0; 

        $data = $this->Contest_model->get_fixture_contest($post_data); 
        $contest_template = array();
        if(isset($data['result'])){
            $contest_status = array_column($data['result'], 'status');         
            if(in_array($show_cancel, $contest_status)) {
                $show_cancel = 1;
            }
            foreach($data['result'] as $template){
                if(!isset($contest_template[$template['group_id']]) || empty($contest_template[$template['group_id']])){                
                    $contest_template[$template['group_id']] = array("group_id"=>$template['group_id'],"group_name"=>$template['group_name'],"template_list"=>array());
                }
                $template['prize_distibution_detail'] = json_decode($template['prize_distibution_detail']);

                // $template['template_leagues'] = explode(",", $template['template_leagues']);
                $contest_template[$template['group_id']]['template_list'][] = $template;
            }
        }
        $contest_template = array_values($contest_template);
         $this->load->model('admin/season_model');
        $data['right_wrong'][]= $this->season_model->get_right_wrong($post_data['season_id']);

        if(!empty($data)){
             $this->api_response_arry['show_cancel'] = $show_cancel;
             $this->api_response_arry['data']['right_wrong'] = $data['right_wrong'];
            $this->api_response_arry['data']['contest_template'] = $contest_template;
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }   
       
    }


    /**
     * Game Detail
     * @param
     * @return string
     */
    public function get_game_detail_post()
    {       
        $match_list = array();
        $data_arr = $this->input->post();
        $contest_detail = $this->Contest_model->get_game_detail($data_arr['contest_unique_id']);

         if (empty($contest_detail)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid Contest unique ID";
            $this->api_response();
         }

        if($contest_detail)
        {          
            $league_id = $contest_detail['league_id'];
            $contest_detail['prize_distibution_detail'] = json_decode($contest_detail['prize_distibution_detail']);
      
                $match_detail = $this->Contest_model->get_match_by_season_id($contest_detail['season_id']);
                
                $match_list = $match_detail;        
        
            $user_data = [];
            if($contest_detail['user_id'])
            {
                $this->load->model('user/User_model');
                $user_data = $this->User_model->get_user_detail_by_user_id($contest_detail['user_id']);
            }

            $all_position['data'] = array();
            $contest_detail['feature_img_url']  = isset($contest_detail['feature_image']) && !empty($contest_detail['feature_image']) ? IMAGE_PATH.FEATURE_CONTEST_DIR.$contest_detail['feature_image'] : '';
            $contest_detail['feature_img']      = isset($contest_detail['feature_image']) && !empty($contest_detail['feature_image']) ?$contest_detail['feature_image'] : '';
            $post_api_response['contest_detail']        =$contest_detail;               
            $post_api_response['user_data']             =$user_data;         
            $post_api_response['match_list']            =$match_list;           

            $this->api_response_arry['data']            = $post_api_response;
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response();
        }

        $this->api_response_arry = $contest_detail;
        $this->api_response();
    }


    /**
    * [get_join_contest_user]
    * 
    */
    public function get_join_contest_user_post()
    {
        $this->form_validation->set_rules('contest_id', 'Contest Id', 'trim|required');
           
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();    
        $contest_id = $post_data['contest_id'];    
        $current_date = format_date();
        $this->load->model("Contest_model");        
        $users_data    = $this->Contest_model->get_join_contest_user($contest_id);
     

          if(isset($users_data)){
            foreach($users_data as $key => &$data){ 
                $this->load->model('user/User_model');
                $user_datas = $this->User_model->get_user_detail_by_user_id($data['user_id']);
                $users_data[$key]['user_unique_id'] = $user_datas['user_unique_id'];

                $data['prize_data'] = json_decode($data['prize_data']);                    
                $data['winning_amount'] = json_decode($data['winning_amount']);

            }
        } 

        $this->api_response_arry['data'] = $users_data;
        $this->api_response();
    }


    /**
    * [get_lineup_detail]
    * 
    */
    public function get_lineup_detail_post(){        
        $this->form_validation->set_rules('user_contest_id', 'Contest User Id', 'trim|required');
           
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->post();    
        $contest_user_id = $post_data['user_contest_id'];    
        $user_team_id = $this->Contest_model->get_single_row("user_team_id",USER_CONTEST,array("user_contest_id"=>$contest_user_id));      

        if(empty($user_team_id)){
             $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid User Contest ID";
            $this->api_response();
        }       
        $season_id = $this->Contest_model->get_single_row("season_id",USER_TEAM,array("user_team_id"=>$user_team_id['user_team_id']));
        $current_date = format_date();
        $this->load->model("Contest_model");        
        $users_data    = $this->Contest_model->get_lineup_detail($season_id['season_id'],$contest_user_id);     
        $this->api_response_arry['data'] = $users_data;
        $this->api_response();
    }

    /**
    * 
    *cancel_season
    * 
    */  
    public function cancel_season_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('season_id', 'season id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $this->load->helper('queue_helper');         
        $post_data['action'] = 'cancel_season';
        add_data_in_queue($post_data, 'picks_game_cancel');
        $this->api_response_arry['message'] = 'Contest deleted successfully.';
        $this->api_response();
    } 


    /**
    * 
    *cancel_contest
    * 
    */
    public function cancel_contest_post() {
        $post_data = $this->post();
        $this->form_validation->set_rules('contest_unique_id', 'contest unique id', 'trim|required');
        $this->form_validation->set_rules('cancel_reason', 'reason', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }     

        $this->load->helper('queue_helper');
        $post_data['action'] = 'cancel_game';    
        add_data_in_queue($post_data, 'picks_game_cancel');        
        $this->api_response_arry['message'] = "Contest has been successfully cancelled";        
        $this->api_response();
    }

    /**
    * [get_contest_status details]
    * 
    */
    public function get_contest_status_post()
    {     
        $this->form_validation->set_rules('season_id',  'Season Id', 'trim|required');
           
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
    
        $current_date = format_date();
        $post_data = $this->post();    



        $this->load->model("Contest_model");         
        $season_id = $post_data['season_id']; 
        $users_data    = $this->get_match_paid_free_users($season_id);
        $this->api_response_arry['data'] = $users_data;
        $this->api_response();
    }

    public function get_match_paid_free_users($season_id)
    {
        $this->load->model("Contest_model");
        $users_data = $this->Contest_model->get_match_paid_free_users($season_id);      
        return $users_data;
    }


    /**
    * [get_fixture_by_league_id]
    * 
    */
    public function get_fixture_by_league_id_post()
    {        
        $post_data = $this->post(); 
        $league_id =   $post_data['league_id'];     
        $this->load->model("season_model");        
        $fixture     = $this->season_model->get_fixture_by_league_id($league_id);

        $this->api_response_arry['data'] = $fixture;
        $this->api_response();
    }

    /**
    * [use for filter contest data]
    * Summary :- get contest filter
    */  

    public function get_contest_filter_post(){ 
        $this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();

        $this->load->model('league_model');
        $this->load->model('Season_model');
        
        $league_list = $this->league_model->get_league_list($post_data);

        $this->load->model('admin/Contest_template_model');
   
        
        $post   = $this->post();
        $group_list  = $this->Contest_template_model->get_group_list();
     
        $status_list = array();
        $status_list[] = array("label"=>"Select Status","value"=>"");
        $status_list[] = array("label"=>"Current Contest","value"=>"current_game");
        $status_list[] = array("label"=>"Completed Contest","value"=>"completed_game");
        $status_list[] = array("label"=>"Cancelled Contest","value"=>"cancelled_game");
        $status_list[] = array("label"=>"Upcoming Contest","value"=>"upcoming_game");       

        $result = array(
                    'league_list'       => $league_list,
                    'group_list'        => $group_list,
                    'status_list'       => $status_list                    
                );

        $this->api_response_arry['data']          = $result;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }


    /**
    * [get_contest_list]
    * Summary :- get contest list
    */  
    public function get_contest_list_post()
    {
        $post_data = $this->input->post();       
        
        $this->form_validation->set_rules('sports_id', 'Sports id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->post();
        // $season_game_uid = $post_data['season_game_uid'];
        $data = $this->Contest_model->get_contest_list($post_data);       

        $contest_list = array();
        $total = $data['total'];
       
        // $season_game_details = array();
        // if(isset($data['result']) && !empty($data['result'])){
        //     $season_game_uids = array_column($data['result'], "season_game_uid");
        //     $season_game_uids = array_unique($season_game_uids);
        //     $post_match_param = array("sports_id"=>$post_data['sports_id'],"ignore_schedule_date"=>1,"season_game_uids"=>$season_game_uids);         
          
        // }
        
        $contest_list = array();
        
        if(isset($data['result'])){
            foreach($data['result'] as $contest){ 
               
                $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail']);
                $contest_list[] = $contest;
               
            }
        }
        $data['total'] = $total;
        $data['result'] = $contest_list;
        $this->api_response_arry['data']= $data;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }

    /**
    * Use for export contest winner
    * 
    */ 
    public function export_contest_winners_get()
    {
        $_POST['contest_id']=$_GET['contest_id'];      
        $winners_data = $this->Contest_model->export_contest_winner_data();
        if(!empty($winners_data)){
            $header = array_keys($winners_data[0]);            
            $winners_data = array_merge(array($header),$winners_data);            
            $this->load->helper('csv');
            array_to_csv($winners_data,'contest_winner_data-'.$_POST['contest_id'].'.csv');
        }
    }

     /**
     * delete contest
     * @param  $contest_id,$collection_id
     * @return string
     */

    public function delete_contest_post()
    { 
        $this->form_validation->set_rules('contest_id', 'Contest Id', 'trim|required');
        // $this->form_validation->set_rules('collection_id', 'collection id', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $data_arr = $this->input->post();
        $contest = $this->Contest_model->delete_contest($data_arr); 
        
        if($contest)
        {   
            $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
            $this->api_response_arry['message']         = "Contest deleted successfully.";
            $this->api_response();
        }
        else
        {
            $this->api_response_arry['response_code']   = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']         = isset($contest['message']) ? $contest['message'] : "No Change";
            $this->api_response();
        }
    }
    
}
