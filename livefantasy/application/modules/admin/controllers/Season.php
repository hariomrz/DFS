<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Season extends Common_Api_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Season_model');
        $_POST = $this->post();

        $allow_livefantasy =  isset($this->app_config['allow_livefantasy'])?$this->app_config['allow_livefantasy']['key_value']:0;
        if($allow_livefantasy == 0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Live fantasy not enabled";
            $this->api_response_arry['global_error'] = "Module Disable";
            $this->api_response();
        }
	}
     
    /**
    * used for get all seasons by sports and league
    * @param array
    * @return array
    */    
    public function get_all_season_schedule_post()
    {   
        $this->form_validation->set_rules('sports_id', 'Sports ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $fixture = $this->Season_model->get_all_season_schedule();
        //echo "<pre>";print_r($fixture);die;
        $live_fixture = $upcoming_fixture = array();
        if(!empty($fixture)){
            $current_date = format_date();
            foreach($fixture['result'] as $key => $res){
                $live_date = date('Y-m-d H:i:s',strtotime($res['season_scheduled_date']."-". CONTEST_DISABLE_INTERVAL_MINUTE." minute"));
                if($current_date >= $live_date){
                    $live_fixture[] = $res;
                }
                else{
                    $upcoming_fixture[] = $res;
                }
            }
        }
        $result['total'] = $fixture['total'];
        $result['result'] = array('live_fixture' => $live_fixture, 'upcoming_fixture' => $upcoming_fixture);
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    public function update_fixture_delay_post(){

        $this->form_validation->set_rules('season_game_uid','Season game uid','trim|required');
        $this->form_validation->set_rules('delay_hour','Hour','trim');
        $this->form_validation->set_rules('delay_minute','Minute','trim');
        $this->form_validation->set_rules('delay_message','Delay Message','trim|required|max_length[160]');
        $this->form_validation->set_rules('league_id','League ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        if(!isset($post_data['delay_hour']) || $post_data['delay_hour'] < 0 || $post_data['delay_hour'] > 47){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_delay_0_48_msg');
            $this->api_response();
        }
        if(!isset($post_data['delay_minute']) || $post_data['delay_minute'] < 0 || $post_data['delay_minute'] > 59){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_delay_0_59_msg');
            $this->api_response();
        }

        $delay_hour = $delay_minute = 0;
        if(isset($post_data['delay_hour']) && $post_data['delay_hour'] != ""){
            $delay_hour = $post_data['delay_hour'];
        }
        if(isset($post_data['delay_minute']) && $post_data['delay_minute'] != ""){
            $delay_minute = $post_data['delay_minute'];
        }

        $delay_minutes = convert_hour_minute_to_minute($delay_hour,$delay_minute);
        if($delay_minutes < 0){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line('match_delay_hour_minute_msg');
            $this->api_response();
        }

        $season_info = $this->Season_model->get_season_by_game_id($post_data['season_game_uid'],$post_data['league_id']);
        $current_date = strtotime(format_date());
        $fixture_schedule = strtotime($season_info['season_scheduled_date']);
        if($fixture_schedule <= $current_date){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_start_delay_msg');
            $this->api_response();
        }

        $delay_min_diff = $delay_minutes - $season_info['delay_minute'];
        if($delay_min_diff <= 0){
            $new_scheduled_date = date('Y-m-d H:i:s', strtotime('+'.$delay_min_diff.' minutes', strtotime($season_info['season_scheduled_date'])));
            if(strtotime($new_scheduled_date) <= $current_date){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('match_prepond_time_limit_msg');
                $this->api_response();
            }
        }
        
        $season_info['new_delay_minute'] = $delay_minutes;
        $season_info['delay_message'] = $post_data['delay_message'];
        //update match cache and bucket files
        $this->Season_model->update_match_delay_data($season_info);
        if(!empty($collection_info)){
            $this->flush_cache_data();
        }

        //update lobby fixture file
        $this->push_s3_data_in_queue("lobby_fixture_list_".$season_info['sports_id'],array(),"delete");
        $this->api_response_arry['message'] = $this->lang->line('match_delay_message');
        $this->api_response();
    }

    public function update_fixture_custom_message_post(){

        $this->form_validation->set_rules('season_game_uid','Season game uid','trim|required');
        $this->form_validation->set_rules('league_id','League ID','trim|required');
        $post_data = $this->input->post();
        if(empty($post_data['is_remove']) || !isset($post_data['is_remove']))
        {
            $this->form_validation->set_rules('custom_message','Custom Message','trim|required|max_length[160]');
        }
        
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $season_info = $this->Season_model->get_season_by_game_id($post_data['season_game_uid'],$post_data['league_id']);
        if(empty($season_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_not_found_msg');
            $this->api_response();
        }

        $result = $this->Season_model->update_match_custom_message($post_data);
        //update match cache and bucket files
        if($result){
            //update lobby fixture file
            $this->delete_cache_data('lobby_fixture_list_'.$season_info['sports_id']);
            $this->push_s3_data_in_queue("lobby_fixture_list_".$season_info['sports_id'],array(),"delete");
        }

        $this->api_response_arry['message'] = $this->lang->line('match_custom_msg_sent');
        $this->api_response();
    }

    public function get_season_to_publish_post(){

        $this->form_validation->set_rules('season_game_uid','Season game uid','trim|required');
        $this->form_validation->set_rules('league_id','League ID','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $season_info = $this->Season_model->get_season_by_game_id($post_data['season_game_uid'],$post_data['league_id']);
        //echo "<pre>";print_r($season_info);die;
        if(empty($season_info)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('match_not_found_msg');
            $this->api_response();
        }

        $season_info['collection'] = array();
        if($season_info['is_published'] == "1"){
            $season_info['collection'] = $this->Season_model->get_season_collections($post_data['season_game_uid'],$post_data['league_id']);
        }

        $season_info['innings'] = array("1"=>"Innings 1st","2"=>"Innings 2nd","0"=>"Both");
        $over = 20;
        if($season_info['format'] == ODI_FORMAT){
            $over = 50;
        }else if($season_info['format'] == T10_FORMAT){
            $over = 10;
        }
        $season_info['overs'] = array();
        for($i=1;$i<=$over;$i++){
            $season_info['overs'][] = $i;
        }
        $this->api_response_arry['data'] = $season_info;
        $this->api_response();
    }

    /**
    * Function used for publish fixture for create contest
    * @param int $league_id
    * @param string $season_game_uid
    * @param int $innings
    * @param array $overs
    * @return array
    */
    public function publish_fixture_post()
    {
        $this->form_validation->set_rules('league_id', 'league Id', 'trim|required');
        $this->form_validation->set_rules('season_game_uid', 'fixture  Id', 'trim|required');
        $this->form_validation->set_rules('innings', 'innings', 'trim|required');
        $this->form_validation->set_rules('multiplier', 'multiplier', 'trim|required|numeric');
        $this->form_validation->set_rules('capping', 'capping', 'trim|required|numeric');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        if(!isset($post_data['overs']) || empty($post_data['overs'])) {
            $this->api_response_arry['message'] = 'Please select atleast one over.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();

        }

        $season_info = $this->Season_model->get_season_by_game_id($post_data['season_game_uid'],$post_data['league_id']);
        if(empty($season_info)) {
            $this->api_response_arry['message'] = 'Fixture details not found';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();

        }
        $post_data['sports_id'] = $season_info['sports_id'];

        $this->load->model('Contest_template_model');
        $template_list = $this->Contest_template_model->get_template_details_for_create_contest($post_data); 
        if (empty($template_list)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Template details not found.";
            $this->api_response();
        }

        $current_date = format_date();
        $update_data = array("is_published"=>1,'modified_date'=>$current_date,'multiplier'=>$post_data['multiplier'],'capping'=>$post_data['capping']);
        $update_where = array("league_id"=>$season_info['league_id'],"season_game_uid"=>$season_info['season_game_uid']);
        $result = $this->Season_model->update_season_detail($update_data,$update_where);
        if(!$result)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line("publish_season_error");
            $this->api_response();
        }
        else
        {
            $innings = isset($post_data['innings']) ? $post_data['innings'] : 1;
            $inn_arr = array($innings);
            if($innings == "0"){
                $inn_arr = array("1","2");
            }
            $cls_ids = array();
            $over_time = isset($this->app_config['allow_livefantasy']['custom_data']['predict_time'])?$this->app_config['allow_livefantasy']['custom_data']['predict_time']:15;
            foreach($post_data['overs'] as $over){
                foreach($inn_arr as $inn){
                    $inn_over = $inn."_".$over;
                    $check_exist = $this->Season_model->get_single_row("collection_id",COLLECTION,array("league_id" => $season_info['league_id'],"season_game_uid" => $season_info['season_game_uid'],"inn_over" => $inn_over));
                    if(empty($check_exist)){
                        $tmp_arr = array();
                        $tmp_arr['league_id'] = $season_info['league_id'];
                        $tmp_arr['season_game_uid'] = $season_info['season_game_uid'];
                        $tmp_arr['inn_over'] = $inn_over;
                        $tmp_arr['collection_name'] = $season_info['home']." vs ".$season_info['away'];
                        $tmp_arr['season_scheduled_date'] = $season_info['season_scheduled_date'];
                        $tmp_arr['over_time'] = $over_time;
                        $tmp_arr['added_date'] = $current_date;
                        $tmp_arr['modified_date'] = $current_date;
                        $collection_id = $this->Season_model->save_collection($tmp_arr);
                        if($collection_id){
                            $cls_ids[] = $collection_id;
                        }
                    }else{
                        //for handeling add more new overs
                        if(!isset($post_data['is_add_more_over']) || $post_data['is_add_more_over']==0){
                            $cls_ids[] =$check_exist['collection_id'];
                        }
                    }
                }
            }
            if(!empty($cls_ids) && !empty($template_list)){
                $collection_templates =  $this->Season_model->get_over_contests($cls_ids);
                foreach($cls_ids as $collection_id){
                    foreach ($template_list as $game_data) {
                        $contest_temlate_id = $game_data['contest_template_id'];
                        //check template already not exit
                        if(array_key_exists($collection_id,$collection_templates)){
                            $pre_templates = $collection_templates[$collection_id]['template'];
                            $exits_templates = explode(",",$pre_templates);
                            if(in_array($contest_temlate_id, $exits_templates)){
                                continue;
                            }
                        }                       
                        $game_data['contest_unique_id'] = random_string('alnum', 9);
                        $game_data['league_id'] = $season_info['league_id'];
                        $game_data['contest_name'] = $game_data['template_name'];
                        $game_data['contest_title'] = isset($game_data['template_title']) ? $game_data['template_title'] : "";
                        $game_data['collection_id'] = $collection_id;
                        $game_data['season_scheduled_date'] = $season_info['season_scheduled_date'];
                        $game_data['status'] = 0;
                        $game_data['added_date'] = format_date();

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
                                        $mx_amount = $prize['max_value'];
                                    }else{
                                        $mx_amount = $amount;
                                    }
                                    $max_prize_pool = $max_prize_pool + $mx_amount;
                                }
                            }
                        }
                        $game_data['max_prize_pool'] = $max_prize_pool;

                        if ($game_data['is_auto_recurring'] == 1) {
                            $game_data['base_prize_details'] = json_encode(array("prize_pool" => $game_data['prize_pool'], "prize_distibution_detail" => $game_data['prize_distibution_detail']));
                        }

                        unset($game_data['template_name']);
                        unset($game_data['template_title']);
                        unset($game_data['template_description']);
                        $result = $this->Contest_template_model->save_template_contest($game_data);
                    }
                }
            }
            $this->api_response_arry['message'] = $this->lang->line("publish_season_success");
            $this->api_response();
        }
    }

    /*Get season details*/
    public function get_season_detail_post(){
        $this->form_validation->set_rules('season_game_uid', 'season game uid', 'trim|required');
        $this->form_validation->set_rules('league_id', 'league id', 'trim|required');
           
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $current_date = format_date();
        $post_param = $this->post();
        $season_game_uid = $post_param['season_game_uid'];
        $league_id  = $post_param['league_id'];
        $season_data = $this->Season_model->get_season_details($season_game_uid,$league_id);
        // print_r($season_data);die();
        if(empty($season_data))
        {
            $this->api_response_arry['service_name']  = 'get_season_detail_post';
            $this->api_response_arry['message']       = 'Season details not found';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
        if(isset($_POST['collection_id']) && $_POST['collection_id']!=""){
            $collection_ids = $_POST['collection_id'];
           $users_data = $this->Season_model->get_match_paid_free_users($collection_ids);
        }else{
            $users_data= $this->get_match_paid_free_users($season_game_uid,$season_data['league_id']);
        }

        $season_data = array_merge($season_data,$users_data);
        $season_data['match_started'] = 0;
        if(strtotime($current_date) >= strtotime($season_data['season_scheduled_date'])){
            $season_data['match_started'] = 1;
        }
        // $season_data['home_flag'] = get_image(0,$season_data['home_flag']);
        // $season_data['away_flag'] = get_image(0,$season_data['away_flag']);
        $this->api_response_arry['data'] = $season_data;
        $this->api_response();
    }

    public function get_match_paid_free_users($season_game_uid,$league_id)
    {
        $post_data = array();
        $post_data['season_game_uid'] = $season_game_uid;
        $post_data['league_id'] = $league_id;
        $collection_data = $this->Season_model->get_fixture_collection_details($post_data);
        // print_r($collection_data);die();
        $users_data = array();
        if(!empty($collection_data)){
            $collection_ids = array_column($collection_data,'collection_id');
            $users_data = $this->Season_model->get_match_paid_free_users($collection_ids);
        }
        return $users_data;
    } 
    /*Get fixture overs(collections and contest count)*/
    public function get_fixture_overs_post(){
        $this->form_validation->set_rules('season_game_uid', 'season game uid', 'trim|required');
        $this->form_validation->set_rules('league_id', 'league id', 'trim|required');
           
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $current_date = format_date();
        $post_param = $this->post();
        $season_game_uid = $post_param['season_game_uid'];
        $league_id  = $post_param['league_id'];        
        $season_overs = $this->Season_model->get_overs($season_game_uid,$league_id);
        if(!empty($season_overs)){
            $this->api_response_arry['data'] =  $season_overs;
            $this->api_response();
        }else{
            $this->api_response_arry['message']       = 'Season overs is not found';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        }
        $this->api_response();
    }

    /**
    * Function used to pin any fixture to list at top
    * @param int $sports_id. league_id, season_game_uid
    * @return array
    */
    public function pin_fixture_post()
    {
        // echo 'hiii';die();
        $this->form_validation->set_rules('league_id', 'League Id', 'trim|required');
        $this->form_validation->set_rules('season_game_uid', 'season game uid', 'trim|required');
        $this->form_validation->set_rules('sports_id', 'Sports ID', 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_param = $this->input->post();

        $this->Season_model->pin_fixture($post_param);
        //update lobby fixture file
        $this->delete_cache_data('lobby_fixture_list_'.$post_param['sports_id']);
        $this->push_s3_data_in_queue("lobby_fixture_list_".$post_param['sports_id'],array(),"delete");

        $this->api_response_arry['service_name']  = 'pin_fixture';
        $this->api_response_arry['message']       = "Fixture mark pin successfully";
        if(isset($post_param['is_pin_fixture']) && $post_param['is_pin_fixture'] == "0")
        {
            $this->api_response_arry['message']       = "Fixture removed from pin successfully";
        }
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }

    /**
    * function used for update match manual score update
    * @param array $post
    * @return array
    */
    public function update_match_status_post()
    {
        $this->form_validation->set_rules('season_game_uid','season game uid','trim|required');
        $this->form_validation->set_rules('match_status','Status','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        
        $post_data = $this->input->post();
        $result = $this->Season_model->update_season_status($post_data);
        if($result){
            $this->api_response_arry['message'] = $this->lang->line('status_upd_success');
            $this->api_response_arry['data'] = array();
            $this->api_response();
        }else{
            $this->api_response_arry['message'] = 'Status update failed. please try again.';
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }
    }

    public function cancel_fixture_post(){
        $this->form_validation->set_rules('season_game_uid','season game uid','trim|required');
        $this->form_validation->set_rules('league_id','Status','trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $season_game_uid = $this->input->post('season_game_uid');
        $league_id = $this->input->post('league_id');
        $collections = $this->Season_model->get_season_collections($season_game_uid,$league_id);
        if(!empty($collections)){
            $this->load->helper('queue_helper');
            foreach ($collections as $key => $collection) {
                $post_data['collection_id'] = $collection['collection_id'];
                $post_data['action'] = 'cancel_collection';
                add_data_in_queue($post_data, 'lf_game_cancel');
            }
            $this->api_response_arry['message'] = $this->lang->line('successfully_cancel_fixture');
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('no_contest_for_cancel');
            $this->api_response();
        }
    }

    public function cancel_fixture_over_post(){
        $this->form_validation->set_rules('collection_id');
        $this->form_validation->set_rules('league_id','Status','trim|required');
        $this->form_validation->set_rules('cancel_reason', 'cancel_reason', 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $season_game_uid = $this->input->post('collection_id');
        
        $league_id = $this->input->post('league_id');

        $this->load->helper('queue_helper');
        $post_data['action'] = 'cancel_collection';
        add_data_in_queue($post_data, 'lf_game_cancel');
        $this->api_response_arry['message'] = $this->lang->line('successfully_cancel_over');
        $this->api_response();
    }

    /*
       description :Get player list
    */

    public function manual_scoring_master_post(){
        $this->form_validation->set_rules('season_game_uid','season game uid','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }   
        $season_game_uid = $this->input->post('season_game_uid');

        $data['master_odds'] =  $this->Season_model->get_master_odds();
        $data['extra'] = manual_feed_extra_values();
        $data['others'] = manual_feed_other_values();
        $data['players'] = $this->Season_model->player_list($season_game_uid);
        $this->api_response_arry['data'] = $data;
        $this->api_response();
    }

    public function get_markets_odds_post(){
        $this->form_validation->set_rules('season_game_uid','season game uid','required');
        $this->form_validation->set_rules('league_id','league id','required');
        $this->form_validation->set_rules('inn_over','inning over','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data =  $this->input->post();
        //master data
        $season_game_uid = $this->input->post('season_game_uid');
        $market_odds = $this->Season_model->get_markets_odds($post_data);
        if(!empty($market_odds)){
            $over_ball = "";
            $last_close_ball = "";
            $is_last_extra = 0;
            foreach ($market_odds as $mk => $mv) {
                $market_odds[$mk]['market_odds'] = json_decode($mv['market_odds'],true);
                $over_ball = $mv['over_ball'];
                if($mv['market_status'] == "cls"){
                    $last_close_ball = $mv['over_ball'];
                    $is_last_extra = $mv['extra_score_id'];
                }
            }
            $sts = array_column($market_odds,"market_status");
            if(!in_array("stm",$sts) && !in_array("ctd",$sts) && $over_ball != ""){
                $collection = $this->Season_model->get_single_row("*",COLLECTION,array("league_id" => $post_data['league_id'],"season_game_uid" => $post_data['season_game_uid'],"inn_over" => $post_data['inn_over']));
                if(!empty($collection) && in_array($collection['status'],array("0","1"))){
                    $over_ball_arr = explode('.', $over_ball);
                    $last_close_ball = explode('.', $last_close_ball);
                    if($last_close_ball['1'] != '6'){
                        //create next ball
                        if($is_last_extra > 0){
                            $next_ball = number_format(number_format($over_ball,"1"),"2");
                        }else{
                            $next = $this->next_ball($over_ball);
                            $next_ball = (float)$over_ball+$next;
                        }
                        
                        $_POST['over_ball'] = $next_ball;
                        $new_market_odds = $this->create_new_market_odds_post(true);
                        if(!empty($new_market_odds)){
                            $market_odds = $new_market_odds;
                        }
                    }
                }
            }
            $data['market_odds'] = $market_odds;
            $this->api_response_arry['data'] = $data;
            $this->api_response();
        }else{
            //create new odds for 1st ball
            $over = explode("_",$post_data['inn_over']);
            if(count($over)==2 AND $over['1']>0){
                $_POST['over_ball'] = $over['1'].'.1';
                $market_odds = $this->create_new_market_odds_post(true);
                $data['market_odds'] = $market_odds;
                $this->api_response_arry['data'] = $data;
                $this->api_response();
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('invalid_inn_over');
                $this->api_response();
            }
        }
    }
    /* Function use to create new market odds */
    public function create_new_market_odds_post($api_call=false){
        $this->form_validation->set_rules('season_game_uid','season game uid','required');
        $this->form_validation->set_rules('league_id','league id','required');
        $this->form_validation->set_rules('inn_over','inning over','required');
        $this->form_validation->set_rules('over_ball','over ball','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $over_ball = explode('.', $post_data['over_ball']);
        $market_id = $this->Season_model->generate_market_id();
        $master_odds = $this->Season_model->get_master_odds();
        $check_odds_exist = $this->Season_model->get_odds_details();
        if(empty($check_odds_exist)){
            $display_order= 1;
            $last_odds_details = $this->Season_model->get_last_market_odds();
            $market_odds = array();
            $previous_odds = array();
            if(!empty($last_odds_details)){
                $previous_odds = json_decode($last_odds_details['market_odds'],true);
                if($over_ball['1']!=1){
                    $display_order = $last_odds_details['display_order']+1;
                }
            }
            $default_odds = get_default_odds($post_data['inn_over']);
            foreach ($master_odds as $mk => $mkd) {
                if(!empty($previous_odds) && isset($previous_odds[$mkd['odds_id']]) ){
                    $market_odds[$mkd['odds_id']] = $previous_odds[$mkd['odds_id']];
                }else{
                    if(isset($default_odds[$mkd['odds_id']])){
                        $market_odds[$mkd['odds_id']] = $default_odds[$mkd['odds_id']];
                    }else{
                        $market_odds[$mkd['odds_id']] = 0;
                    }
                }
            }
            $data[] = array(
                        "league_id"=>$post_data['league_id'],
                        "season_game_uid"=>$post_data['season_game_uid'],
                        "market_id"=>$market_id,
                        "inn_over" =>$post_data['inn_over'],
                        "over_ball"=>(double)$post_data['over_ball'],
                        "market_odds"=>json_encode($market_odds),
                        "bat_player_id"=>0,
                        "bow_player_id"=>0,
                        "updated_by"=>1,
                        "updated_date"=>format_date(),
                        "market_status"=>"ctd",
                        "display_order"=>$display_order
                    );
            $this->Season_model->replace_into_batch(MARKET_ODDS,$data);
        }
        $market_odds = $this->Season_model->get_markets_odds($post_data);
        if(!empty($market_odds)){
            foreach ($market_odds as $mk => $mv) {
                $market_odds[$mk]['market_odds'] = json_decode($mv['market_odds'],true);
            }
        }
        if($api_call){
            return $market_odds; 
        }else{
            $this->api_response_arry['market_odds'] = $market_odds;
            $this->api_response();
        }
    }

    public function update_scoring_points_post(){
        $this->form_validation->set_rules('league_id','league id','required');
        $this->form_validation->set_rules('season_game_uid','season game uid','required');
        $this->form_validation->set_rules('market_id','market id','required');
        $this->form_validation->set_rules('inn_over','inning over','required');
        $this->form_validation->set_rules('over_ball','over ball','required');
        $this->form_validation->set_rules('market_odds[]','market odds score','required');
        $this->form_validation->set_rules('bat_player_id','bat player id','required|numeric');
        $this->form_validation->set_rules('bow_player_id','bow player id','required|numeric');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $odds_details = $this->Season_model->get_odds_details();
        if(!empty($odds_details)){
            //market status should be ctd for update score
            if($odds_details['market_status']=='ctd'){
                $is_update = $this->Season_model->update_market_odds_points();
                if($is_update){
                    $this->api_response_arry['message'] = $this->lang->line('market_scoring_updated');
                    $this->api_response();
                }else{
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line('market_scoring_updated_error');
                    $this->api_response();
                }
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('ball_status_not_available_for_update');
                $this->api_response();
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_maket_odds');
            $this->api_response();
        }
    }

    /*
        Description:to update status of over play or undo
        param : status= 1=play,2=undo
    */
    public function change_ball_status_post(){
        $this->form_validation->set_rules('league_id','league id','required');
        $this->form_validation->set_rules('season_game_uid','season game uid','required');
        $this->form_validation->set_rules('market_id','market id','required');
        $this->form_validation->set_rules('inn_over','inning over','required');
        $this->form_validation->set_rules('over_ball','over ball','required');
        $this->form_validation->set_rules('status','over status','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $collection = $this->Season_model->get_single_row("*",COLLECTION,array("league_id" => $post_data['league_id'],"season_game_uid" => $post_data['season_game_uid'],"inn_over" => $post_data['inn_over']));
        if($collection['status'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, You can't publish ball odds without over Live.";
            $this->api_response();
        }
        $over_ball = explode('.', $post_data['over_ball']);
        $next = $this->next_ball($post_data['over_ball']);
        $next_ball = (float)$post_data['over_ball']+$next;
        $odds_details = $this->Season_model->get_odds_details();
        //echo "<pre>";print_r($odds_details);die;
        if(!empty($odds_details)){
            //call for play and previous status should ctd
            if($post_data['status']==1 && $odds_details['market_status']=='ctd' && ($odds_details['bat_player_id']==0 || $odds_details['bow_player_id']==0 ))
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('update_batsman_bowler');
                $this->api_response(); 
            }
            elseif($post_data['status']==1 && $odds_details['market_status']=='ctd')
            {
                //previous ball status should be cls
                $this->check_previous_ball_status();
                //market_odds because we will multiple of point with season multiplier and capping value
                $_POST['market_odds'] = $odds_details['market_odds'];
                $odds_details['market_date'] = format_date();
                $_POST['market_date'] = $odds_details['market_date'];
                $is_mark_play = $this->Season_model->change_to_play();
                if($is_mark_play){
                    if($over_ball['1']<6){
                        $correct_ball = $_POST['over_ball'];
                        //create next boll
                        $_POST['over_ball'] = $next_ball;
                        $market_odds = $this->create_new_market_odds_post(true);
                        $_POST['over_ball'] = $correct_ball;
                    }
                    $odds_details['market_odds'] = $this->Season_model->ball_point_multiply();
                    //broadcast ball odds to node client
                    if(!empty($collection)){
                        $master_odds = $this->Season_model->get_master_odds();
                        $master_odds = array_column($master_odds,NULL,'odds_id');
                        $odds_point = json_decode($odds_details['market_odds']);
                        $final_odds = array();
                        foreach($odds_point as $id=>$point){
                            $odds_arr = isset($master_odds[$id]) ? $master_odds[$id] : array();
                            if(!empty($odds_arr)){
                                $tmp = array();
                                $tmp['odds_id'] = $id;
                                $tmp['name'] = $odds_arr['name'];
                                $tmp['point'] = $point;
                                $final_odds[] = $tmp;
                            }
                        }
                        $odds_details['market_odds'] = $final_odds;
                        $odds_details['collection_id'] = $collection['collection_id'];
                        $odds_details['over_time'] = $collection['over_time'];
                        $odds_details['over_ball'] = trim_trailing_zeroes($odds_details['over_ball']);
                        $odds_details['time'] = strtotime(format_date());
                        $odds_details['market_date_time'] = strtotime($odds_details['market_date']);
                        unset($odds_details['market_name']);
                        unset($odds_details['score']);
                        unset($odds_details['extra_score_id']);
                        unset($odds_details['updated_date']);
                        unset($odds_details['updated_by']);
                        unset($odds_details['display_order']);
                        $this->load->library('Node');            
                        $node = new node(array("route" => 'updateMatchOverOdds', "postData" => array("data" => $odds_details)));
                    }                    
                    $this->api_response_arry['message'] = $this->lang->line('ball_status_change_successfully');
                    $this->api_response();
                }else{
                   $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line('ball_status_not_change_to_play');
                    $this->api_response(); 
                }
            }elseif ($post_data['status']==1 && $odds_details['market_status']!='ctd') {
                //if market_status is not ctd not allow to change play
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('invalid_status_for_play');
                $this->api_response(); 
            }elseif ($post_data['status']==2 && $odds_details['market_status']=='cls')
            {
                $collection = $this->Season_model->get_single_row("collection_id,league_id,inn_over,status",COLLECTION,array("league_id" => $odds_details['league_id'],"season_game_uid" => $odds_details['season_game_uid'],"inn_over" => $odds_details['inn_over']));
                if(!empty($collection)){
                    if(in_array($collection['status'],array(2,3))) {
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = "Over has already completed. Now you can not undo.";
                        if($collection['status']==3)
                        {
                            $this->api_response_arry['message'] = "Over has cancelled.Now you can not change.";
                        }                        
                        $this->api_response();
                    }
                    
                    //if market_status is cls allow to change play status stm
                    $this->check_next_ball_status();
                    $is_undo = $this->Season_model->change_to_play();
                    if($is_undo){
                        //remove correct points
                        $market_id = $post_data['market_id'];
                        $this->Season_model->update(USER_PREDICTION,array("is_correct"=>"0","points"=>"0","score"=>"0"),array("market_id"=>$market_id));

                        //points and rank
                        $this->Season_model->update_total_points_rank($collection['collection_id']);

                        //broadcast undo result to node client
                        $over_ball = trim_trailing_zeroes($odds_details['over_ball']);
                        $odds_result = array("collection_id"=>$collection['collection_id'],"league_id"=>$collection['league_id'],"inn_over"=>$collection['inn_over'],"market_id"=>$post_data['market_id'],"result"=>0,"score"=>0,"btext"=>"0","over_ball"=>$over_ball);
                        $odds_result['time'] = strtotime(format_date());
                        $this->load->library('Node');            
                        $node = new node(array("route" => 'updateMatchOddsResult', "postData" => array("data" => $odds_result)));

                        //point and rank event
                        $node = new node(array("route" => 'updateMatchRankLF', "postData" => array("data" => array("collection_id"=>$collection['collection_id']))));
                                           
                        $this->api_response_arry['message'] = $this->lang->line('ball_status_change_successfully');
                        $this->api_response();
                    }else{
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = $this->lang->line('ball_status_not_change_to_play');
                        $this->api_response(); 
                    }
                }else{
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = "Over details is not found";
                    $this->api_response();
                }
            }else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('invalid_ball_status');
                $this->api_response(); 
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_maket_odds');
            $this->api_response();
        }
    } 
    function next_ball($ball){
        $over_ball = explode('.', $ball);
        $next = .1;
        if( ((int)$over_ball['1']%10)>0 &&  $over_ball['1']>10){
            $next = .01;
        }
        return $next;
    }

    function previous_ball($ball){
        $over_ball = explode('.', $ball);
        $pre = .1;
        if( ((int)$over_ball['1']%10)>0 &&  $over_ball['1']>10){
            $pre = .01;
        }
        return $pre;
    }
    function check_next_ball_status(){
        $post_data = $this->input->post();
        $over_ball = explode('.', $post_data['over_ball']);

        $next = $this->next_ball($post_data['over_ball']);

        if((int)$over_ball['1']==6){
            return true;
        }
        $next_ball = (float)$post_data['over_ball']+$next;
        $_POST['over_ball'] = $next_ball;
        $ball_details = $this->Season_model->get_next_odds_details();
        //reset previous ball
        $_POST['over_ball'] = $post_data['over_ball'];
        if(!empty($ball_details) && ($ball_details['market_status']=='stm' || $ball_details['market_status']=='cls' )){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('ball_can_not_undo');
            $this->api_response();
        }else{
            return true;
        } 
    }

    function check_previous_ball_status(){
        $post_data = $this->input->post();
        $over_ball = explode('.', $post_data['over_ball']);
        $pre = $this->previous_ball($post_data['over_ball']);
        
        if((int)$over_ball['1']==1){
            return true;
        }
        $pre_ball = (float)$post_data['over_ball']-$pre;
        $_POST['over_ball'] = $pre_ball;
        $ball_details = $this->Season_model->get_next_odds_details();
        //reset previous ball
        $_POST['over_ball'] = $post_data['over_ball'];
        // if(!empty($ball_details) && ($ball_details['market_status']!='stm' || $ball_details['market_status']=='cls' )){
        if(!empty($ball_details) && ($ball_details['market_status']!='cls')){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('previous_ball_should_closed');
            $this->api_response();
        }else{
            return true;
        } 
    }

    public function update_ball_result_post(){
        $this->form_validation->set_rules('league_id','league id','required');
        $this->form_validation->set_rules('season_game_uid','season game uid','required');
        $this->form_validation->set_rules('market_id','market id','required');
        $this->form_validation->set_rules('inn_over','inning over','required');
        $this->form_validation->set_rules('over_ball','over ball','required');
        $this->form_validation->set_rules('result','result','required|numeric');
        $this->form_validation->set_rules('score','score','required|numeric');
        $this->form_validation->set_rules('market_name','market name','required'); //result name like 1 Run 2 Run Extra
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        if(in_array($post_data['result'],array(7,8)) ){
            $this->form_validation->set_rules('extra_score_id','extra_score_id','required|numeric');
            if (!$this->form_validation->run()) 
            {
                $this->send_validation_errors();
            }
        }
        $odds_details = $this->Season_model->get_odds_details();
        if(!empty($odds_details)){
            if($odds_details['result']==0){
                //don't check for undo ball result
                $this->check_previous_ball_status();
            }
            //market status should be stm for update score
            if($odds_details['market_status']=='stm')
            {
                $_POST['pre_result'] = $odds_details['result'];
                $_POST['pre_extra_score_id'] = $odds_details['extra_score_id'];
                $nb_wd = range(1,14);
                $is_update = $this->Season_model->update_ball_result();
                // $is_update =1;
                if($is_update){
                    // if previous status is extra and again update without nb and wide. remove over ball and mark extra ball as real ball
                    if(!in_array($post_data['extra_score_id'],$nb_wd ) && $odds_details['result']==7 && in_array($odds_details['extra_score_id'],$nb_wd) ){
                        $this->update_previous_ball_to_live();
                    }
                    // if previous status is extra  without nb and wide and now mark as NB or WD. Remove over ball and mark extra ball as real ball
                    elseif($odds_details['result']==7 && !in_array($odds_details['extra_score_id'],$nb_wd) && in_array($post_data['extra_score_id'],$nb_wd )){
                        $this->update_next_ball_to_live();
                    }

                    //creat new odds for same ball if noball or wideball
                    elseif($odds_details['result']==0 && $post_data['result'] == 7 && in_array($post_data['extra_score_id'],$nb_wd ) ) {
                        $this->update_next_ball_to_live();
                        // $this->create_new_market_odds_post(true);
                    }
                    elseif($odds_details['result']!=7 && $post_data['result'] == 7 && in_array($post_data['extra_score_id'],$nb_wd ) ) {
                        $this->update_next_ball_to_live();
                    }                    
                    //broadcast result to node client
                    $collection = $this->Season_model->get_single_row("collection_id,league_id,season_game_uid,inn_over",COLLECTION,array("league_id" => $odds_details['league_id'],"season_game_uid" => $odds_details['season_game_uid'],"inn_over" => $odds_details['inn_over']));
                    if(!empty($collection)){
                        $btext = $post_data['score'];
                        $over_ball = $odds_details['over_ball'];
                        if($post_data['result'] == "7" && isset($post_data['extra_score_id']) && $post_data['extra_score_id'] > 0){
                            $btext = get_extra_ball_name($post_data['extra_score_id']);
                            $m_odds = $this->Season_model->get_single_row("*",MARKET_ODDS,array("league_id" => $collection['league_id'],"season_game_uid" => $collection['season_game_uid'],"inn_over" => $collection['inn_over'],"market_id"=>$odds_details['market_id']));
                            $over_ball = $m_odds['over_ball'];
                        }else if($post_data['result'] == "6"){
                            $btext = "W";
                        }
                        //update odds result in user team
                        $score = $post_data['score'];
                        $odds_id = $post_data['result'];
                        $market_odds_arr = json_decode($odds_details['market_odds'],TRUE);
                        $points = isset($market_odds_arr[$odds_id]) ? $market_odds_arr[$odds_id] : 0;
                        $half_points = number_format(($points / 2),2,".","");
                        $sql = "UPDATE ".$this->db->dbprefix(USER_PREDICTION)." AS UP 
                            SET UP.score = ".$score.",
                            UP.is_correct = (CASE WHEN (UP.odds_id='".$odds_id."' OR UP.second_odds_id='".$odds_id."') THEN 1 WHEN (UP.odds_id!='".$odds_id."' AND UP.second_odds_id!='".$odds_id."') THEN 2 ELSE 0 END),
                            UP.points = (CASE WHEN (UP.odds_id='".$odds_id."' AND UP.second_odds_id=0) THEN ".$points." WHEN (UP.odds_id='".$odds_id."' OR UP.second_odds_id='".$odds_id."') THEN ".$half_points." ELSE 0 END)
                            WHERE UP.market_id='".$odds_details['market_id']."'
                            ";
                        $this->db->query($sql);

                        //points and rank
                        $this->Season_model->update_total_points_rank($collection['collection_id']);

                        $over_ball = trim_trailing_zeroes($over_ball);
                        $odds_result = array("collection_id"=>$collection['collection_id'],"league_id"=>$collection['league_id'],"inn_over"=>$collection['inn_over'],"market_id"=>$post_data['market_id'],"result"=>$post_data['result'],"score"=>$post_data['score'],"btext"=>$btext,"over_ball"=>$over_ball);
                        $odds_result['time'] = strtotime(format_date());
                        $this->load->library('Node');            
                        $node = new node(array("route" => 'updateMatchOddsResult', "postData" => array("data" => $odds_result)));

                        //point and rank event
                        $node = new node(array("route" => 'updateMatchRankLF', "postData" => array("data" => array("collection_id"=>$collection['collection_id']))));
                    }
                    $this->api_response_arry['message'] = $this->lang->line('ball_result_saved');
                    $this->api_response();
                }else{
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->lang->line('ball_result_updated_error');
                    $this->api_response();
                }
            }elseif($odds_details['market_status']=='cls')
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('ball_result_already_updated');
                $this->api_response();
            }
            else{
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->lang->line('previous_status_not_stm');
                $this->api_response();
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->lang->line('invalid_maket_odds');
            $this->api_response();
        }
    }
    //function use to update next ball as live when mark ball as noball and wide ball
    public function update_next_ball_to_live(){
        $post_data = $this->input->post();
        $over_ball = explode('.', $post_data['over_ball']);
        $next = $this->next_ball($post_data['over_ball']);
        $next_ball = (float)$post_data['over_ball']+$next;
        if($over_ball['1']<6){
            $this->db->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$next_ball));
            $this->db->update(MARKET_ODDS,array('over_ball'=>$post_data['over_ball']));
        }else{
            $this->create_new_market_odds_post(true);
        }
        return true;
    }
    //function use to update previous ball as live when noball and wide ball update as correct ball
    public function update_previous_ball_to_live(){
        $post_data = $this->input->post();
        $real_over_ball = number_format($post_data['over_ball'],1);
        $next = .1;
        $next_ball = (float)$real_over_ball+$next;
        $over_ball = explode('.', $next_ball);
        //update new over ball
        $this->db->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$real_over_ball));
        if($over_ball['1']<=6){
            $this->db->update(MARKET_ODDS,array('over_ball'=>$next_ball));
        }else{
            $this->db->delete(MARKET_ODDS);
        }
        //update extra ball as real
        $this->db->where(array('league_id'=>$post_data['league_id'],'season_game_uid'=>$post_data['season_game_uid'],'inn_over'=>$post_data['inn_over'],'over_ball'=>$post_data['over_ball']));
        $this->db->update(MARKET_ODDS,array('over_ball'=>$real_over_ball));

        return true;
    }

    public function get_user_predictions_post(){
        $this->form_validation->set_rules('user_team_id','user team id','required|numeric');
        $this->form_validation->set_rules('user_contest_id','user contest id','required|numeric');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $prediction = $this->Season_model->get_prediction_details();
        $this->api_response_arry['data'] = $prediction;
        $this->api_response();

    }

    public function update_over_status_post(){
        $this->form_validation->set_rules('collection_id','collection id','required');
        $this->form_validation->set_rules('status','status','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $current_date = format_date();
        $collection_id = $post_data['collection_id'];
        $status = $post_data['status'];
        $collection = $this->Season_model->get_single_row("*",COLLECTION,array("collection_id" => $collection_id));
        if(!in_array($status,array("1","2"))){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Status should be Live or Complete.";
            $this->api_response();
        }else if($status == "1" && $collection['status'] != "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, You can only move upcoming over into Live.";
            $this->api_response();
        }else if($status == "2" && $collection['status'] != "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, You can only move live over into Complete.";
            $this->api_response();
        }else if($status == "2" && strtotime($collection['season_scheduled_date']) > strtotime($current_date)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, You can't mark upcoming match over to complete.";
            $this->api_response();
        }
        //echo "<pre>";print_r($collection);die;
        if($status == "2"){
            /*$inover = explode("_",$collection['inn_over']);
            $ball_id = $inover['1'].".60";
            $last_ball = $this->Season_model->get_single_row("*",MARKET_ODDS,array("league_id" => $collection['league_id'],"season_game_uid" => $collection['season_game_uid'],"inn_over" => $collection['inn_over'],"over_ball" => $ball_id));
            if(empty($last_ball)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = "Sorry, You can't mark over complete without 6th ball complete.";
                $this->api_response();
            }else if(!empty($last_ball) && $last_ball['market_status'] != "cls"){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = "Sorry, You can't mark over complete without 6th ball complete.";
                $this->api_response();
            }*/
            $check_open_ball = $this->Season_model->get_single_row("*",MARKET_ODDS,array("league_id" => $collection['league_id'],"season_game_uid" => $collection['season_game_uid'],"inn_over" => $collection['inn_over'],"market_status" => "stm"));
            if(!empty($check_open_ball)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = "Sorry, You can't mark over complete without mark answer for open ball.";
                $this->api_response();
            }
        }
        //echo "<pre>";print_r($collection);die;
        $update_arr = array("status"=>$status,"modified_date"=>$current_date);
        $over_time = $collection['over_time'];
        if($status == "1" && isset($post_data['over_time']) && $post_data['over_time'] != ""){
            $update_arr['over_time'] = $post_data['over_time'];
            $over_time = $post_data['over_time'];
        }
        $result = $this->Season_model->update(COLLECTION,$update_arr,array("collection_id"=>$collection_id));
        if($result){
            if($status == "1"){
                $this->Season_model->update_total_points_rank($collection_id);
            }
            $next_over = $next_over_ft = array();
            if($status == "2"){
                $next_over = $this->Season_model->get_next_over($collection);
                if(!empty($next_over)){
                    $next_over_ft = $next_over;
                    unset($next_over['inn_over_val']);
                }

                //delete open ball for over on complete
                $this->Season_model->delete_row(MARKET_ODDS,array("league_id" => $collection['league_id'],"season_game_uid" => $collection['season_game_uid'],"inn_over" => $collection['inn_over'],"market_status" => "ctd"));
            }
            $this->load->library('Node');            
            $status_data = array("collection_id"=>$collection_id,"status"=>$status,"over_time"=>$over_time,"next_over"=>$next_over);
            $node = new node(array("route" => 'updateMatchOverStatus', "postData" => array("data" => $status_data)));

            if($status == "1"){
                $result = $this->Season_model->get_single_row("GROUP_CONCAT(DISTINCT user_id) as user_ids",USER_TEAM, array("collection_id"=>$collection_id));
                if(!empty($result) && isset($result['user_ids'])){
                    $user_ids = explode(",",$result['user_ids']);
                    $live_data = array("collection_id"=>$collection_id,"user_ids"=>$user_ids);
                    $node = new node(array("route" => 'updateMatchOverLive', "postData" => array("data" => $live_data)));
                }
            }

            $this->api_response_arry['data'] = $status_data;
            $this->api_response_arry['data']['next_over'] = $next_over;
            $this->api_response_arry['message'] = "Over status updated successfully.";
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Problem while update over status. Please try again.";
            $this->api_response();
        }

    }

    public function get_collection_detail_post(){
        $this->form_validation->set_rules('collection_id', 'collection id', 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $current_date = format_date();
        $post_param = $this->post();
        $collection_id = $post_param['collection_id'];
        $collection_data = $this->Season_model->get_collection_details($collection_id);
        $this->api_response_arry['data'] = $collection_data;
        $this->api_response();
    }

    public function update_match_score_status_post(){
        $this->form_validation->set_rules('season_game_uid','match id','required');
        $this->form_validation->set_rules('league_id','league id','required');
        $this->form_validation->set_rules('is_live_score','status','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $current_date = format_date();
        $season_game_uid = $post_data['season_game_uid'];
        $league_id = $post_data['league_id'];
        $is_live_score = $post_data['is_live_score'];
        $match = $this->Season_model->get_single_row("*",SEASON,array("season_game_uid" => $season_game_uid,"league_id"=>$league_id));
        if($is_live_score == "1" && $match['is_live_score'] == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, You have already enabled live score.";
            $this->api_response();
        }else if($is_live_score == "0" && $match['is_live_score'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, You have already disabled live score.";
            $this->api_response();
        }
        
        $update_arr = array("is_live_score"=>$is_live_score,"modified_date"=>$current_date);
        $result = $this->Season_model->update(SEASON,$update_arr,array("season_game_uid"=>$season_game_uid,"league_id"=>$league_id));
        if($result){
            $this->api_response_arry['data'] = array("is_live_score"=>$is_live_score);
            $this->api_response_arry['message'] = "Live score status updated successfully.";
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Problem while update live score status. Please try again.";
            $this->api_response();
        }
    }

    public function update_match_score_post(){
        $this->form_validation->set_rules('season_game_uid','match id','required');
        $this->form_validation->set_rules('league_id','league id','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        if(empty($post_data['score_data'])){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Please update score data.";
            $this->api_response();
        }
        foreach($post_data['score_data'] as $in=>$score){
            if((isset($score['home_wickets']) && $score['home_wickets'] > 10) || (isset($score['away_wickets']) && $score['away_wickets'] > 10)){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = "Wickets should be less then equal 10.";
                $this->api_response();
            }
        }
        //echo "<pre>";print_r($post_data);die;
        $current_date = format_date();
        $season_game_uid = $post_data['season_game_uid'];
        $league_id = $post_data['league_id'];
        $score_data = $post_data['score_data'];
        $match = $this->Season_model->get_single_row("*",SEASON,array("season_game_uid" => $season_game_uid,"league_id"=>$league_id));
        if($match['is_live_score'] == "0"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, Please enable live score before update score data.";
            $this->api_response();
        }
        
        $update_arr = array("score_data"=>json_encode($score_data),"modified_date"=>$current_date);
        $result = $this->Season_model->update(SEASON,$update_arr,array("season_game_uid"=>$season_game_uid,"league_id"=>$league_id));
        if($result){
            $collections = $this->Season_model->get_all_table_data("*",COLLECTION, array("season_game_uid"=>$season_game_uid,"league_id"=>$league_id, "status"=>"1"));
            $this->load->library('Node');
            foreach($collections as $collection){
                $match_scores = array("collection_id"=>$collection['collection_id'],"season_game_uid"=>$season_game_uid,"league_id"=>$league_id,"is_live_score"=>$match['is_live_score'],"score_data" => json_encode($score_data));
                $match_scores['time'] = strtotime(format_date());
                $node = new node(array("route" => 'updateMatchScoreLF', "postData" => array("data" => $match_scores)));
                
            }
            $this->api_response_arry['data'] = array("score_data"=>$post_data['score_data']);
            $this->api_response_arry['message'] = "Score updated successfully.";
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Problem while update live score status. Please try again.";
            $this->api_response();
        }
    }

    public function start_over_timer_post(){
        $this->form_validation->set_rules('collection_id','collection id','required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $current_date = format_date();
        $collection_id = $post_data['collection_id'];
        $status = $post_data['status'];
        $collection = $this->Season_model->get_single_row("*",COLLECTION,array("collection_id" => $collection_id));
        if(empty($collection)){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Invalid over id.";
            $this->api_response();
        }else if(!empty($collection) && $collection['status'] == "1"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, Over already started.";
            $this->api_response();
        }else if(!empty($collection) && $collection['status'] >= "2"){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, Over already completed.";
            $this->api_response();
        }else if(!is_null($collection['timer_date'])){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Sorry, You have already started timer for this over.";
            $this->api_response();
        }
        //echo "<pre>";print_r($collection);die;
        $timer_date = date('Y-m-d H:i:s', strtotime('+15 seconds', strtotime($current_date)));
        $update_arr = array("timer_date"=>$timer_date,"modified_date"=>$current_date);
        $result = $this->Season_model->update(COLLECTION,$update_arr,array("collection_id"=>$collection_id));
        if($result){
            $this->load->library('Node');            
            $collection_data = array("collection_id"=>$collection_id,"timer_date"=>strtotime($timer_date));
            $collection_data['time'] = strtotime(format_date());
            $node = new node(array("route" => 'updateMatchOverTimer', "postData" => array("data" => $collection_data)));

            $this->api_response_arry['data'] = $collection_data;
            $this->api_response_arry['message'] = "Over timer updated successfully.";
            $this->api_response();
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = "Problem while update over status. Please try again.";
            $this->api_response();
        }

    }
}

/* End of file Season.php */
/* Location: /admin/application/controllers/Season.php */
