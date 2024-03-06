<?php

class Livestockfantasy extends Common_Api_Controller {

    public $stock_type = 4;
    public $deadline_time = 10;//time in minute
    public $call_from_api =true;
    public $market_id = 1;
    public $salary_cap = '';
    public function __construct() {
        parent::__construct();
        $this->contest_lang = $this->lang->line('contest');
         $this->salary_cap = isset($this->app_config['allow_live_stock_fantasy']['custom_data']['salary_cap'])?$this->app_config['allow_equity']['custom_data']['salary_cap']:500000;
       

    }   



     /**
     * Used for get lobby page contest list
     * @param int 
     * @return array
     */
    public function get_contest_list_post() 
    {
        $post_data = $this->input->post();
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $user_id = intval($this->user_id);
        $result = $this->Livestockfantasy_model->get_contests_list($post_data);
        $total_contest = 0;
        $contest_list = array();
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;

        foreach ($result as $key => $value) 
        {   
            $value['game_starts_in'] = (strtotime($value['scheduled_date']) - ($deadline_time * 60))*1000;
            $value['user_joined_count'] = $value['lm_count'];
            $value["prize_distibution_detail"] = json_decode($value['prize_distibution_detail'],true);
            $value['is_confirmed'] = 0;
            if($value['guaranteed_prize'] != 2 && $value['size'] > $value['minimum_size'] && $value['entry_fee'] > 0){
                $value['is_confirmed'] = 1;
            }
            unset($value['lm_count']);
            $contest_list[] = $value;
            $total_contest++;
        }

        $this->api_response_arry['data']['contest'] = $contest_list;
        $this->api_response_arry['data']['total_contest'] = $total_contest;
        $this->api_response();
    }

    /**
     * Used for get lobby filters
     * @param int $sports_id
     * @return array
     */
    public function get_lobby_filter_post() {
        $post_data = $this->input->post();
        $filter_list = array();
        //$filter_list['time'] =  array("9:15"=>"11:00","11:01"=>"12:00","12:01"=>"13:00","13:01"=>"14:00","14:01"=>"15:00","15:01"=>"15:30");
        $filter_list['entry_fee'] = array("1"=>"50","51"=>"100","101"=>"1000","1000"=>"1000000");
        $filter_list['entries'] = array("1"=>"50","51"=>"100","101"=>"1000","1000"=>"1000000");
        $filter_list['winning'] = array("1"=>"5000","5000"=>"10000","10000"=>"100000","100000"=>"10000000");
        
        $this->api_response_arry['data'] = $filter_list;
        $this->api_response();
    }


    /**
     * Used for get Joined contest on my lobby
     * @return array
     */
    function get_my_lobby_contest_post() {

        $post_data = $this->input->post();
        $this->load->model('Livestockfantasy_model');
        $result = $this->Livestockfantasy_model->get_my_joined_contests($post_data);
        //echo "<pre>";print_r($result);die;
        $final_data = array();
        $upcoming =array();
        $live =array();
        $completed =array();
        if (!empty($result)) { 
            foreach ($result as $fixture) {
                $collection_status = $fixture['collection_status'];
                $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
                unset($fixture['collection_status']);
                $fixture['game_starts_in'] = (strtotime($fixture['scheduled_date']) - ($deadline_time * 60)) * 1000;

                $fixture["prize_distibution_detail"] = json_decode($fixture['prize_distibution_detail'],true);
                               
                if($fixture['is_live'] == 1) {
                    $live[] =  $fixture;
                }

                if($fixture['is_upcoming'] == 1) {
                    $upcoming[] =  $fixture;
                }

                if($collection_status == 1) {
                    $completed[] =  $fixture;
                }
            }

//           Live order-> Recent live matches should show first => DESC
            $live_scheduled_date = array_column($live, 'scheduled_date');
            array_multisort($live_scheduled_date, SORT_DESC, $live);

//           Upcoming-> Recent Upcoming Matches should show first => ASC
            $up_scheduled_date = array_column($upcoming, 'scheduled_date');
            array_multisort($up_scheduled_date, SORT_ASC, $upcoming);

//           Completed-> Recent Completed Matches should show first. => DESC
            $c_scheduled_date = array_column($completed, 'scheduled_date');
            array_multisort($c_scheduled_date, SORT_DESC, $completed);

        }

        //Live, Upcoming and Last 7 days Completed Fixture
        $final_data = array_merge($live,$upcoming,$completed);
        
        $this->api_response_arry['data'] = $final_data;
        $this->api_response();        
    }

    /**
     * Trade API 
     * @param lineup_master_id,contest_id, trade_value,brockerage
     */ 

    public function user_trade_post()
    {
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('trade_value', 'Trade value', 'trim|required');
        $this->form_validation->set_rules('stock_id', 'Stock ID', 'trim|required');
        $this->form_validation->set_rules('lot_size',   $this->lang->line('lot_size'), 'trim|required');
        $this->form_validation->set_rules('brokerage', 'brokerage', 'trim|required');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required');

        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
       
        $this->load->model('Livestockfantasy_model');
        $lineup_details = $this->Livestockfantasy_model->get_lineup_details($post_data);
        
        $current_date = format_date();


        $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
        $hour = $d->format('H');
        $minute = $d->format('i');
        $day = $d->format('D');
      
        $post_data['status'] = 1;

        $time = $hour.'.'.$minute;
        $flag = 0;
        if($time > 15.30 || $time < 9.15 || $day == 'Sat' || $day == 'Sun') {
            $post_data['status'] = 0;
            $flag = 1;
        }  

        if(!empty($lineup_details)){
            try{
               
                if($current_date > $lineup_details['end_date']){
                    throw new Exception($this->contest_lang['contest_closed']);
                    
                }

                $this->db->trans_strict(TRUE);
                $this->db->trans_start();
                //if($post_data['type'] == 1){

                    if($post_data['trade_value'] > $lineup_details['total_score'] && $post_data['type'] == 1)
                    {
                        throw new Exception("Trade value can not be greater than total portfolio score", 1);
                        
                    }

                    $trade_array = array(
                        'lineup_master_id'=>$post_data['lineup_master_id'],
                        'contest_id'=>$post_data['contest_id'],
                        'stock_id'=>$post_data['stock_id'],
                        'trade_value'=>$post_data['trade_value'],
                        'lot_size'=>$post_data['lot_size'],
                        'price'=>$post_data['price'],
                        'type'=>$post_data['type'],
                        'added_date'=>$d->format('Y-m-d H:i:00'),
                        'status'=>$post_data['status']
                    );

                   
                   $lot_size[] = $post_data['lot_size'];
                   $insert = $this->db->insert(USER_TRADE, $trade_array);
                   $insert_id = $this->db->insert_id();

                   if($insert_id && $post_data['type'] == 1 && $post_data['brokerage'] > 0 ){
                        $brockerage_array = ['parent_id'=>$insert_id,'brokerage'=>$post_data['brokerage'],'added_date'=>date('Y-m-d H:i:00'),'stock_id'=>$post_data['stock_id'],'status'=>$post_data['status']];
                       $this->db->insert(USER_TRADE,$brockerage_array);
                   }

                   $team_data = json_decode($lineup_details['team_data'],1);
                   if($post_data['type'] == 1){
                        $total_trade_value  = $post_data['trade_value'] + $post_data['brokerage'];
                        $score = ['total_score'=>$lineup_details['total_score'] - $total_trade_value];
                        $score['last_score'] = $score['total_score'];                   
                    }   
                    else{
                        $score = ['total_score'=>$lineup_details['total_score'] + $post_data['trade_value']];
                        $score['last_score'] = $score['total_score'];
                        
                    }


                        if(!empty($team_data)){
                            foreach ($team_data['stocks'] as $key => $value) {

                                if($post_data['stock_id'] == $key){

                                    if($post_data['type'] == 1){
                                        if($time >= 9.15 && $time <= 15.30 && $day != 'Sat' && $day != 'Sun') {
                                            $lot_size = $value + $post_data['lot_size'];
                                            $team_data['stocks'][$key] = "$lot_size"; 
                                        }
                                    }elseif($post_data['type'] == 2){
                                        $lot_size =$value - $post_data['lot_size'];
                                        $team_data['stocks'][$key]  = "$lot_size";
                                    }else{
                                         unset($team_data['stocks'][$key]);
                                         unset($value);

                                          if(empty($team_data['stocks'])){
                                                $team_data = NULL;
                                          }
                                    }
                                    break;
                                }else{
                                    if($post_data['type'] == 1){
                                        $lot_size =$post_data['lot_size'];
                                        $team_data['stocks'][$post_data['stock_id']] = "$lot_size"; 
                                    }
                                }  
                            }
                        }else{
                                $team_data = ['stocks'=>$post_data['stocks']]; 
                        }

                     if(!is_null($team_data)){
                        $team_data = json_encode($team_data);
                     }
                     
                     if($time >= 9.15 && $time <= 15.30 && $day != 'Sat' && $day != 'Sun') {
                            $this->db->where('lineup_master_id',$lineup_details['lineup_master_id']);
                            $this->db->update(LINEUP_MASTER,['team_data'=>$team_data]);

                            $this->db->where('lineup_master_contest_id',$lineup_details['lineup_master_contest_id']);
                            $this->db->update(LINEUP_MASTER_CONTEST,$score);
                        }
                        if ($flag == 1 && $post_data['type'] != 1) {
                           $this->db->where('lineup_master_id',$lineup_details['lineup_master_id']);
                            $this->db->update(LINEUP_MASTER,['team_data'=>$team_data]);
                        }

                        if($flag == 1 && $post_data['type'] == 1)
                        {
                             $this->db->where('lineup_master_contest_id',$lineup_details['lineup_master_contest_id']);
                            $this->db->update(LINEUP_MASTER_CONTEST,$score);
                        }



                 $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE ){
                        $this->db->trans_rollback();
                    }
                    else{
                        $this->db->trans_commit();
                    }

                $this->api_response_arry['message'] = 'Trade has been done successfully';
                $this->api_response();

            }
            catch(Exception $e){
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $e->getMessage();
                $this->api_response();
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
            $this->api_response();
        }

    }

    public function get_collection_statics_post() {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $data = $this->input->post();
        $collection_id = $data['collection_id'];
        $collection_data = $this->Livestockfantasy_model->get_single_row('published_date, scheduled_date, end_date', COLLECTION, array('collection_id' => $collection_id));
        if(empty($collection_data)){
            $this->lineup_lang = $this->lang->line('lineup');
            $this->api_response_arry['message'] = $this->lineup_lang['match_detail_not_found'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response();
        }

        $type = isset($post_data['type']) ? $post_data['type'] : 0;
        $data = array_merge($data, $collection_data);
        $data['user_id'] = $this->user_id;
        $statics = array();
        if(empty($type)) {
            $data['type'] = 1;
            $statics['gainers'] = $this->Livestockfantasy_model->statics($data);

            $data['type'] = 2;
            $statics['losers'] = $this->Livestockfantasy_model->statics($data);
        } else {
            $data['page'] = 1;
            $statics = $this->Livestockfantasy_model->statics($data);
        }
        
        $this->api_response_arry['data'] = $statics;
        $this->api_response();
    }  


    /**
     * used for get user team player list
     * @param int $lineup_master_id
     * @param int $collection_id
     * @return array
    */
    public function get_user_lineup_post()
    {  
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $lineup_master_id = $post_data['lineup_master_id'];
        $collection_id = $post_data['collection_id'];

        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $lineup_result = $this->Livestockfantasy_model->get_user_lineup($post_data);       
     
        $collection_player_cache_key = "sp_collection_stocks_".$collection_id;
        $stock_list = $this->get_cache_data($collection_player_cache_key);
        if(!$stock_list)
        {
            $collection_data = $this->Livestockfantasy_model->get_single_row("category_id,published_date,end_date,scheduled_date", COLLECTION,array("collection_id" => $collection_id));
            $post_data['published_date'] = isset($collection_data['published_date']) ? $collection_data['published_date'] : "";
            $post_data['end_date'] = isset($collection_data['end_date']) ? $collection_data['end_date'] : "";
            $post_data['scheduled_date'] = $collection_data['scheduled_date'];
            $stock_list = $this->Livestockfantasy_model->get_all_stocks($post_data);
            //set collection team in cache for 2 hours
            $this->set_cache_data($collection_player_cache_key,$stock_list,REDIS_1_MINUTE);
        }
        $stock_list_array = array_column($stock_list,NULL,'stock_id');
        $final_stock_list = array();
        $pending_transacion_list = array();
        $team_data = json_decode($lineup_result['team_data'],TRUE);
        $stock_info = array();

        $this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($this->user_id, TRUE);

        $where = ['lineup_master_id'=>$lineup_master_id,'status'=>0,'type'=>1];
        $pending_transacion = $this->Livestockfantasy_model->get_pending_user_trade($where);

        if(!empty($team_data))
        {
            foreach($team_data['stocks'] as $stock_id => $stock_lot){
                $stock_info = $stock_list_array[$stock_id];
                $key = array_search($stock_id, array_column($pending_transacion, 'stock_id'));
                 
                if(is_numeric($key))
                {
                    $stock_info['status'] =$pending_transacion[$key]['status'];
                    $stock_info['lot_size_pending'] = $pending_transacion[$key]['lot_size_pending'];
                    $stock_info['total_trade_value'] = $pending_transacion[$key]['total_trade_value'];
                }else{

                    $stock_info['lot_size_pending'] = 0;
                    $stock_info['total_trade_value'] = 0;
                }

                if(!empty($stock_info)){
               
                    $stock_info['lot_size'] = $stock_lot;//buy
                    if(in_array($stock_id, $wishlist_stock_ids)) {
                        $stock_info['is_wish'] = 1;
                    }


                    $final_stock_list[] = $stock_info;
                }
            }
            
        }
            
        if(!empty($pending_transacion))
        {   
            $pending_transacion = array_column($pending_transacion,NULL, 'stock_id');
            foreach ($pending_transacion as $stock_id => $stock_value) {
                $stock_info = $stock_list_array[$stock_id];

                if(!empty($stock_info)){
                    $stock_info['lot_size_pending'] = $stock_value['lot_size_pending'];
                    $stock_info['total_trade_value'] = $stock_value['total_trade_value'];
                     $stock_info['status'] =  $stock_value['status'];

                    if(in_array($stock_id, $wishlist_stock_ids)) {
                        $stock_info['is_wish'] = 1;
                    }
              
                     $key = array_search($stock_id, array_column($final_stock_list, 'stock_id'));
                       if(is_numeric($key) && $stock_info['stock_id'] == $final_stock_list[$key]['stock_id'])
                      { 
                          unset($pending_transacion_list[0]['stock_id']);
                       }else{

                        $pending_transacion_list[] = $stock_info;
                       }   


                }   
            }

        }


        $this->api_response_arry['data']['lineup']              = array_merge( $final_stock_list, $pending_transacion_list);
        $this->api_response_arry['data']['team_name']           = $lineup_result['team_name'];
        $this->api_response_arry['data']['remaining_amount']    = $lineup_result['total_score'];
        $this->api_response();
    }


      /**
     * Used to get holiday
     */
    function get_holiday_post() {
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $post_data = $this->input->post();
        $year = isset($post_data['year']) ? $post_data['year'] : format_date('today', 'Y');
        
        $result = $this->Livestockfantasy_model->get_holiday($this->market_id, $year);
        $holiday_list = array();
        if(!empty($result)) {
            $holiday_list = array_column($result, 'holiday_date');
        }        
        $this->api_response_arry['data'] = $holiday_list;
        $this->api_response();          
    }
  

  
    public function get_user_contest_by_status_post() {
        $this->form_validation->set_rules('status', $this->lang->line('match_status'), 'trim|required|callback_check_collection_status');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $status = $post_data['status'];
        //get 
        $this->load->model("Livestockfantasy_model");
        $contest_list = $this->Livestockfantasy_model->get_user_fixture_contest($status);

        $users = array();
        if(!empty($contest_list)) {
            $user_ids = array_unique(array_column($contest_list,'contest_creater'));
            $this->load->model("user/User_model");
            $users = $this->User_model->get_users_by_ids($user_ids);
            if(!empty($users)) {
                $users = array_column($users,NULL,'user_id');
            }
        }

        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        $fixture_contest = array();
        foreach ($contest_list as $contest) {
            if (!array_key_exists($contest['contest_id'], $fixture_contest)) {
                $contest['is_confirmed'] = 0;
                if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
                    $contest['is_confirmed'] = 1;
                }
                $fixture_contest[$contest['contest_id']] = array(
                    "contest_id" => $contest["contest_id"],
                    "contest_unique_id" => $contest["contest_unique_id"],
                    "contest_name" => $contest["contest_name"],
                    "contest_creater" => $contest["contest_creater"],
                    "contest_title" => $contest["contest_title"],         
                    "size" => $contest["size"],
                    "minimum_size" => $contest["minimum_size"],
                    "total_user_joined" => $contest["total_user_joined"],
                    "entry_fee" => $contest["entry_fee"],
                    "prize_pool" => $contest["prize_pool"],
                    "prize_type" => $contest["prize_type"],
                    "prize_distibution_detail" => json_decode($contest["prize_distibution_detail"]),
                    "status" => $contest["status"],
                    "multiple_lineup" => $contest["multiple_lineup"],
                    "user_joined_count" => $contest["user_joined_count"],
                    "max_bonus_allowed" => $contest["max_bonus_allowed"],
                    "is_private_contest" => $contest["is_private_contest"],
                    "group_name" => $contest["group_name"],
                    "contest_access_type" => $contest["contest_access_type"],
                    "currency_type" => $contest["currency_type"],
                    "guaranteed_prize" => $contest["guaranteed_prize"],
                    "is_confirmed" => $contest["is_confirmed"],
                    "scheduled_date" => $contest["scheduled_date"],
                    "collection_id" => $contest["collection_id"],
                    "end_date" => $contest["end_date"],
                    'game_starts_in'=>  (strtotime($contest['scheduled_date']) - ($deadline_time * 60)) * 1000,
                     "brokerage" => $contest["brokerage"],
                );

                if(isset($users[$contest['contest_creater']])) {
                    $fixture_contest[$contest['contest_id']]['user_name'] =$users[$contest['contest_creater']]['user_name'];
                    $fixture_contest[$contest['contest_id']]['image'] =$users[$contest['contest_creater']]['image'];
                }
            }

            $is_winner = $contest["is_winner"];
            if ($status == 1 && !empty($contest["prize_distibution_detail"])) {//LIVE
                $prize_details = json_decode($contest["prize_distibution_detail"], TRUE);
                $last_element = end($prize_details);
                if (!empty($last_element['max']) && $last_element['max'] >= $contest["game_rank"]) {
                    $is_winner = 1;
                }
            }

            $prize_data = array();
            if(isset($contest["prize_data"]) && $contest["prize_data"]!='null'){
                $prize_data = json_decode($contest["prize_data"], TRUE);
            }
            $fixture_contest[$contest['contest_id']]["teams"][$contest['lineup_master_contest_id']] = array(
                "lineup_master_id" => $contest['lineup_master_id'],
                "team_name" => $contest["team_name"],
                "lineup_master_contest_id" => $contest["lineup_master_contest_id"],
                "total_score" => $contest["total_score"] ? $contest["total_score"] : 0,
                "last_score" => $contest["last_score"] ? $contest["last_score"] : 0,
                "game_rank" => $contest["game_rank"] ? $contest["game_rank"] : 0,
                "is_winner" => $is_winner,
                "prize_data" => $prize_data,
                "percent_change" => $contest["percent_change"] ? $contest["percent_change"] : 0,
                "last_percent_change" => $contest["last_percent_change"] ? $contest["last_percent_change"] : 0,
            );
        }

        if (!empty($fixture_contest)) {
            //array_multisort($fixture_contest, SORT_ASC);
            $fixture_contest = array_values($fixture_contest);
        }

        foreach($fixture_contest as &$contest) {
            ksort($contest['teams']);
            $contest['teams']  = array_values($contest['teams']);
        }
        $this->api_response_arry['data'] = $fixture_contest;
        $this->api_response();
    }

        /**
     * @method Get_portfoli_score
     * @param lineup_master_id
     * @return current portfolio score with remaining stock in portfolio
     */
    function get_portfolio_score_post()
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');

        if(!$this->form_validation->run()){
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();

        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $result = $this->Livestockfantasy_model->get_portfolio_score($post_data);

        $result['stock_data'] = json_decode($result['stock_data'],1);

        $this->api_response_arry['data'] = $result;
        $this->api_response();
        
    }


    /**
     * Used for get collection stock list
     * @param int $sports_id
     * @param int $league_id
     * @param int $collection_master_id
     * @return array
    */
    public function get_all_stocks_post()
    {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) 
        {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $collection_id = $post_data['collection_id'];
        $collection_player_cache_key = "st_collection_player_".$collection_id;
        $players_list = $this->get_cache_data($collection_player_cache_key);
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        if(!$players_list)
        {
            //get collection
            $collection  = $this->Livestockfantasy_model->get_single_row('published_date,end_date,scheduled_date',COLLECTION,array(
                "collection_id" => $collection_id
            ));

            $post_data['published_date'] = $collection['published_date'];
            $post_data['end_date'] = $collection['end_date'];
            $post_data['scheduled_date'] = $collection['scheduled_date'];
            $players_list = $this->Livestockfantasy_model->get_all_stocks($post_data);
            //set collection team in cache for 2 hours
            $this->set_cache_data($collection_player_cache_key,$players_list,REDIS_1_MINUTE);
        }

        //for upload lineup data on s3 bucket
       // $this->push_s3_data_in_queue("st_collection_player_".$collection_id,$players_list);

        $this->load->model("wishlist/Wishlist_model");
        $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($this->user_id, TRUE);
        foreach($players_list as &$player) {
            if(in_array($player['stock_id'], $wishlist_stock_ids)) {
                $player['is_wish'] = 1;
            }
        }

        if(!empty($post_data['lineup_master_contest_id'])) {
            $game_rank  = $this->Livestockfantasy_model->get_single_row('game_rank',LINEUP_MASTER_CONTEST,array(
                "lineup_master_contest_id" => $post_data['lineup_master_contest_id']));
            $this->api_response_arry['data']['game_rank'] = $game_rank['game_rank'];
        }

        $this->api_response_arry['data']['stock_list'] = $players_list;
        $this->api_response_arry['data']['salary_cap'] = $this->salary_cap;
        $this->api_response();
    }

    function get_my_contest_team_count_post()
    {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_master_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $this->load->model('Livestockfantasy_model');
        
        $data = $this->Livestockfantasy_model->get_my_contest_team_count($post_data);

        //echo "<pre>";print_r($data);die;   
        $this->api_response_arry['data'] = $data;
        $this->api_response();
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

        

        //for manage collection wise deadline
        $current_date = format_date();
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        if (isset($contest['deadline_time']) && $contest['deadline_time'] >= 0) {
            $deadline_time = $contest['deadline_time'];
        }
        $current_time = date(DATE_FORMAT, strtotime($current_date . " +" . $deadline_time . " minute"));

        //check for match schedule date
        if (strtotime($contest['scheduled_date']) < strtotime($current_time)) {
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

        $user_join_data = $this->Livestockfantasy_model->get_user_contest_join_count($contest);
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


        if ($this->entry_fee == '0') {
            return 1;
        }

        $this->load->model("user/User_model");
        $balance_arr = $this->User_model->get_user_balance($this->user_id);
        $this->user_bonus_bal = $balance_arr['bonus_amount'];
        $this->user_bal = $balance_arr['real_amount'];
        $this->winning_bal = $balance_arr['winning_amount'];
        $this->point_balance = $balance_arr['point_balance'];
        
        if ($contest['prize_type'] == 2 || $contest['prize_type'] == 3) { // for Conins 
            if ($this->entry_fee > $this->point_balance) {
                $this->message = $this->contest_lang['not_enough_coins'];
                $this->enough_coins = 0;
                return 0;
            }
        } else {
            //get user balance
            $bonus_amount = $max_bonus = 0;
            
            if($this->currency_type == "2"){
                if ($this->entry_fee > $this->point_balance) {
                    $this->message = $this->contest_lang['not_enough_balance'];
                    $this->enough_balance = 0;
                    return 0;
                }
            }else{
                //$max_bonus_percentage = MAX_BONUS_PERCEN_USE;
                if ($contest['max_bonus_allowed']) {
                    $max_bonus_percentage = $contest['max_bonus_allowed'];
                    $max_bonus = ($this->entry_fee * $max_bonus_percentage) / 100;
                    $bonus_amount = $max_bonus;
                }
                if ($max_bonus > $this->user_bonus_bal) {
                    $bonus_amount = $this->user_bonus_bal;
                }
                
                if(MAX_CONTEST_BONUS > 0 && $bonus_amount > MAX_CONTEST_BONUS) {
                    $bonus_amount = MAX_CONTEST_BONUS;
                }
                if ($this->entry_fee > ($bonus_amount + $this->user_bal + $this->winning_bal)) {
                    $this->message = $this->contest_lang['not_enough_balance'];
                    $this->enough_balance = 0;
                    return 0;
                }
            }
        }
        if($this->currency_type == "1"){
            $this->self_exclusion_limit = 0;
        }
        return 1;
    }


     /**
     * used for validate contest promo code
     * @param array $data
     * @return array
     */
    private function apply_contest_promo_code($data) {
        $final_entry_fee = $this->entry_fee;
        if (isset($data['promo_code']) && $data['promo_code'] != "" && !empty($data['promo_code'])) {
            $this->load->model("user/User_model");
            $promo_code_detail = $this->User_model->check_promo_code_details($data['promo_code']);
            if ($promo_code_detail) {
                if ($promo_code_detail['type'] != CONTEST_JOIN_TYPE) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
                    $this->api_response();
                } else if ($promo_code_detail['total_used'] >= $promo_code_detail['per_user_allowed']) {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang["allowed_limit_exceed"];
                    $this->api_response();
                }

                if ($promo_code_detail['value_type'] == "1") {
                    $total_discount = ($this->entry_fee * $promo_code_detail['discount']) / 100;
                    if ($total_discount > $promo_code_detail['benefit_cap']) {
                        $total_discount = $promo_code_detail['benefit_cap'];
                    }
                } else {
                    $total_discount = $promo_code_detail['discount'];
                }

                $promo_code_id = $promo_code_detail['promo_code_id'];
                $final_entry_fee = $this->entry_fee - $total_discount;
                $final_entry_fee = truncate_number($final_entry_fee);
                if ($final_entry_fee < 0) {
                    $final_entry_fee = 0;
                }
                $this->entry_fee = $final_entry_fee;

                //Update promoce earning
                $config['promo_code_id'] = $promo_code_id;
                $config['source'] = 1;
                $config['source_id'] = $this->contest_unique_id;
                $config['user_id'] = $this->user_id;
                $config['amount_received'] = $total_discount;

                $where_condition = array("promo_code_id" => $promo_code_id, "user_id" => $this->user_id, "contest_unique_id" => $this->contest_unique_id);
                $earn_info = $this->User_model->get_user_promo_code_earn_info($where_condition);
                if (!empty($earn_info) && $earn_info["is_processed"] == 0) {
                    $code_arr = array("amount_received" => $total_discount);
                    $this->User_model->update_promo_code_earning_details($code_arr, $earn_info["promo_code_earning_id"]);
                } else if (empty($earn_info)) {
                    $promo_code = array();
                    $promo_code['promo_code_id'] = $promo_code_id;
                    $promo_code['contest_unique_id'] = $this->contest_unique_id;
                    $promo_code['user_id'] = $this->user_id;
                    $promo_code['order_id'] = 0;
                    $promo_code['amount_received'] = $total_discount;
                    $promo_code['added_date'] = format_date();
                    $this->User_model->save_promo_code_earning_details($promo_code);
                }
                $this->promocodeid = $promo_code_detail['promo_code_id'];
            } else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->contest_lang["promo_code_exp_used"];
                $this->api_response();
            }
        }
    }
    /**
     * Used for join contest
     * @param int $lineup_master_id
     * @param int $contest_id
     * @return array
     */
    public function join_game_post() { 
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $current_date = format_date();
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $contest = $this->Livestockfantasy_model->get_contest_detail($post_data);

        // Apply PromoCode 
        $this->entry_fee = $contest['entry_fee'];
        $this->contest_unique_id = $contest['contest_unique_id'];
        $this->currency_type = $contest['currency_type'];
        $is_promo_code = 0;
        if($this->currency_type != "2"){
            $this->apply_contest_promo_code($post_data);
            $is_promo_code = 1;
        }

        $is_valid = $this->validation_for_join_game($contest, $post_data);
      
        if ($is_valid) {
            if ($is_valid || $contest['entry_fee'] == 0) {
                $joined = $this->Livestockfantasy_model->join_game($contest, $post_data);
                $joined_status = 1;
                if (isset($joined['joined_count']) && $contest['entry_fee'] > 0) {
                    $cash_type = "2";
                    if($this->currency_type == "2"){
                        $cash_type = "3";
                    }
                    $withdraw = array();
                    $withdraw["source"] = CONTEST_JOIN_SOURCE; //460-join contest
                    $withdraw["source_id"] = $joined['lineup_master_contest_id'];
                    $withdraw["reason"] = FANTASY_CONTEST_NOTI1;
                    $withdraw["cash_type"] = $cash_type;
                    $withdraw["user_id"] = $this->user_id;
                    $withdraw["amount"] = $this->entry_fee;
                    $withdraw["currency_type"] = $this->currency_type;
                    $withdraw["max_bonus_allowed"] = $contest['max_bonus_allowed'];
                    $withdraw["site_rake"] = $contest['site_rake'];
                    $withdraw["reference_id"] = $contest['contest_id'];
                    $withdraw["is_promo_code"] = $is_promo_code;
                    $withdraw["custom_data"]['contest_name'] = $contest['contest_name'];
                    $withdraw["custom_data"]['contest_type'] = $contest['group_name'];
                    $withdraw["custom_data"]['match_date'] = date('d-M-Y H:i:s',strtotime($contest['scheduled_date']));
                    $this->load->model("user/User_model");
                    $join_result = $this->User_model->withdraw($withdraw);
                    if (empty($join_result) || $join_result['result'] == 0) {
                        //remove user joined entry
                        $this->Livestockfantasy_model->remove_joined_game($contest, $joined['lineup_master_contest_id']);

                        $joined_status = 0;
                        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                        $this->api_response_arry['message'] = isset($join_result['message']) ? $join_result['message'] : $this->contest_lang['problem_while_join_game'];
                        $this->api_response();
                    }

                    
                }

                //update contest promo code earning status
                    if($this->currency_type != "2"){
                        $where_condition = array("contest_unique_id" => $contest['contest_unique_id'], "user_id" => $this->user_id);
                        $this->load->model("user/User_model");
                        $earn_info = $this->User_model->get_user_promo_code_earn_info($where_condition);
                        if (!empty($earn_info) && $earn_info["is_processed"] == 0) {
                            $code_arr = array("is_processed" => "1","order_id"=>$join_result['order_id']);
                            $this->User_model->update_promo_code_earning_details($code_arr, $earn_info["promo_code_earning_id"]);
                        }
                    }
                //join game notification
                if (isset($joined['joined_count']) && $joined_status == 1) {
                    //create auto recurring contest
                    if (isset($joined['joined_count']) && $joined['joined_count'] == $contest['size'] && $contest['is_auto_recurring'] == '1') {
                        $this->load->helper('queue_helper');
                        $contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest['contest_unique_id']));
                        add_data_in_queue($contest_queue, 'stock_contest');
                    }

                    $input = array(
                        'contest_name' => $contest['contest_name'],
                        'contest_unique_id' => $contest['contest_unique_id'],
                        'contest_id' => $contest['contest_id'],
                        'entry_fee' => $contest['entry_fee'],
                        'prize_pool' => $contest['prize_pool'],
                        'prize_type' => $contest['prize_type'],
                        'currency_type' => $contest['currency_type'],
                        'prize_distibution_detail' => json_decode($contest['prize_distibution_detail'],TRUE),
                        'season_scheduled_date' => $contest["scheduled_date"],
                        'category_id' => $contest["category_id"],
                        'end_date' => $contest["end_date"],
                        "collection_name" => (!empty($contest['collection_name'])) ? $contest['collection_name'] : $contest['contest_name'],
                        "stock_type" => $contest['stock_type']
                    );

                    $input['collection_name'] = $this->get_rendered_collection_name($input);

                    $notify_data = array();
                    $notify_data["notification_type"] = CONTEST_JOIN_NOTIFY; //1-JoinGame, 
                    $notify_data["source_id"] = $joined['lineup_master_contest_id'];

                    $allow_join_email = isset($this->app_config['allow_join_email'])?$this->app_config['allow_join_email']['key_value']:0;
        
                    $notify_data["notification_destination"] = 3; //Web,Push,Email
                    if($allow_join_email)
                    {
                        $notify_data["notification_destination"] = 7; //Web,Push
                    }

                    $notify_data["user_id"] = $this->user_id;
                    $notify_data["to"] = $this->email;
                    $notify_data["user_name"] = $this->user_name;
                    $notify_data["added_date"] = $current_date;
                    $notify_data["modified_date"] = $current_date;
                    $input['int_version'] = $this->app_config['int_version']['key_value'];
                    $notify_data["content"] = json_encode($input);
                    $notify_data["subject"] = $this->contest_lang['join_game_email_subject'];
                    $this->load->model('user/User_nosql_model');
                    $this->User_nosql_model->send_notification($notify_data);

                    //delete user balance data
                    $user_balance_cache_key = 'user_balance_' . $this->user_id;
                    $this->delete_cache_data($user_balance_cache_key);
                    $contest_cache_key = "st_contest_" . $contest["contest_id"];
                    //delete contest cache
                    $this->delete_cache_data($contest_cache_key);

                    //delete joined data
                    $user_contest_cache_key = 'st_user_contest_' . $contest["collection_id"] . "_" . $this->user_id;
                    $this->delete_cache_data($user_contest_cache_key);

                    //collection contest
                    $collection_contest_cache_key = "st_collection_contest_" . $contest["collection_id"];
                    $this->delete_cache_data($collection_contest_cache_key);

                    //collection pin contest
                    if(isset($contest["is_pin_contest"]) && $contest["is_pin_contest"] == 1){
                        $collection_contest_cache_key = "collection_pin_contest_" . $contest["collection_id"];
                        $this->delete_cache_data($collection_contest_cache_key);
                      
                    }

                    //delete cache
                    $user_teams_cache_key = "st_user_teams_".$contest['collection_id']."_".$this->user_unique_id;
                    $this->delete_cache_data($user_teams_cache_key);

                    $user_device_ids = array();
                    if ($contest['is_private'] == 1 && isset($post_data['device_type']))
                    {
                        $device_data = $this->Livestockfantasy_model->get_user_device_ids($this->user_id);
                        if (!empty($device_data))
                        {
                            $user_device_ids = $device_data;
                        }
                    }
                    $this->api_response_arry['data']['user_device_ids'] = $user_device_ids;
                    $this->api_response_arry['message']                 = $this->contest_lang['join_game_success'];
                  
                    $this->api_response();
                } else {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
                    $this->api_response();
                }
            } else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->message;
                $this->api_response();
            }
        } else {
            
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
        }
    }


    private function get_rendered_collection_name($data)
    {
        
        $collection_name = ucfirst($data['collection_name']);
        $date  = date('d-M-Y',strtotime($data['season_scheduled_date']));
        $end_date  = date('d-M-Y',strtotime($data['end_date']));
          if($data['category_id'] =='1')
          {
            $collection_name.='-'.$date;
          }
          elseif($data['category_id'] =='2')
          {
            $collection_name.='-('.$date.'-'.$end_date.')';
          }
          else{
            $month = date('M',strtotime($date));
            $collection_name.='-('.$month.')';
          }

        if($data['stock_type'] == 3){
            
            $date      = date('d-M h:i A',strtotime('+330 minutes',strtotime($data['season_scheduled_date'])) );
            $end_date  = date('h:i A',strtotime('+330 minutes',strtotime($data['end_date'])));
            $collection_name = $date.' - '.$end_date;
           
         }

         if($data['stock_type'] == 4){
            
            $date      = date('d-M h:i A',strtotime('+330 minutes',strtotime($data['season_scheduled_date'])) );
            $end_date  = date('d-M h:i A',strtotime('+330 minutes',strtotime($data['end_date'])));
            $collection_name = $date.' - '.$end_date;
           
         }
    
      return $collection_name;
    }
     /**
     * Used for get contest details
     * @param int $contest_id
     * @param string $contest_unique_id
     * @return array
     */
    public function get_contest_detail_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $contest = $this->Livestockfantasy_model->get_contest_detail($post_data);
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        if ($contest['contest_creater'] != 0)
        {
            $this->load->model("user/User_model");
            $creator_details = $this->User_model->get_users_by_ids($contest['contest_creater']);
            if (!empty($creator_details))
            {
                $contest['creator_details'] = $creator_details[0];
            }
        }

        unset($contest['is_auto_recurring']);
        unset($contest['is_custom_prize_pool']);
      
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
        if (isset($contest['deadline_time']) && $contest['deadline_time'] >= 0) {
            $deadline_time = $contest['deadline_time'];
        }
        $contest['game_starts_in'] = (strtotime($contest['scheduled_date']) - ($deadline_time * 60)) * 1000;
        if (!empty($contest['prize_distibution_detail'])) {
            $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail'], true);
            if ($contest['prize_distibution_detail'] == null) {
                $contest['prize_distibution_detail'] = array();
            }
        }

        $contest['merchandise'] = array();
        if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
            $tmp_ids = array();
            foreach($contest['prize_distibution_detail'] as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
                    $tmp_ids[] = $prize['amount'];
                }
            }
            if(!empty($tmp_ids)){
                $this->load->model("livestockfantasy/Livestockfantasy_model");
                $merchandise_list = $this->Livestockfantasy_model->get_merchandise_list($tmp_ids);
                $contest['merchandise'] = $merchandise_list;
            }
        }

        $contest['user_join_count'] = 0;
        $contest['is_confirmed'] = 0;
        if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
            $contest['is_confirmed'] = 1;
        }

        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

     /**
     * Used for get contest joined users list
     * @param int $collection_id
     * @param int $contest_id
     * @return array
     */
    public function get_contest_users_post() {
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $participant_list = $this->Livestockfantasy_model->get_contest_joined_users($post_data);
        $joined_users_list = array();
        if (!empty($participant_list)) {
            $user_id_array = array_column($participant_list, "user_id");
            $user_with_join_count = array_column($participant_list, "user_count", "user_id");

            // $allow_xp_point = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;

            $this->load->model("user/User_model");

            $allow_xp_point = isset($this->app_config['allow_xp_point'])?$this->app_config['allow_xp_point']['key_value']:0;
            $user_list = $this->User_model->get_participant_user_details($user_id_array,$allow_xp_point);
            $user_data_array = array_column($user_list,NULL,"user_id");
            foreach ($participant_list as $value) {
                if (isset($user_data_array[$value['user_id']])) {
                    if(!isset($value['user_name']) || $value['user_name'] == ""){
                        $value['user_name'] = $user_data_array[$value['user_id']]['name'];
                    }
                    $value['user_join_count'] = $value['user_count'];
                    $value["name"] = $value['user_name'];
                    $value["image"] = $user_data_array[$value['user_id']]['image'];

                    if($allow_xp_point ==1)
                    {
                        $value['level_number'] = $user_data_array[$value['user_id']]['level_number'];
                        $value['badge_id'] = $user_data_array[$value['user_id']]['badge_id'];
                        $value['badge_name'] = $user_data_array[$value['user_id']]['badge_name'];
                        $value['badge_icon'] = $user_data_array[$value['user_id']]['badge_icon'];
                    }
                    unset($value['user_count']);
                    $joined_users_list[] = $value;
                }
            }
        }
        $return_arr = array("users" => $joined_users_list);
        if (isset($post_data['page_no']) && $post_data['page_no'] == 1) {
            $contest_info = $this->Livestockfantasy_model->get_single_row("total_user_joined,size", CONTEST, array("contest_id" => $post_data['contest_id']));
            $total_user_joined = 0;
            if(!empty($contest_info)){
                $total_user_joined = $contest_info['total_user_joined'];
                if($contest_info['total_user_joined'] > $contest_info['size']){
                    $total_user_joined = $contest_info['size'];
                }
            }
            
            if ($total_user_joined == 0) {
                $total_user_joined = count($joined_users_list);
            }
            $return_arr['total_user_joined'] = $total_user_joined;
        }

        $this->api_response_arry['data'] = $return_arr;
        $this->api_response();
    }

    public function get_compare_teams_post() {
        $this->form_validation->set_rules('u_lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        $this->form_validation->set_rules('o_lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $current_date = format_date();
        $post_data = $this->input->post();
        $lineup_master_contest_ids = array(
            "you"=>$post_data["u_lineup_master_contest_id"],
            "oponent"=>$post_data["o_lineup_master_contest_id"]
        );
        $final_data=array();
        foreach($lineup_master_contest_ids as $key=>$lmcid){
            $team_data = $this->_common_team_detail($lmcid);
            $final_data[$key]=$team_data;
        }
        $this->api_response_arry['data'] = $final_data;
        $this->api_response();
    }

    private function _common_team_detail($lmcid){
        $lineup_master_contest_id = $lmcid;
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $team_info = $this->Livestockfantasy_model->get_contest_collection_details_by_lmc_id($lineup_master_contest_id);
        if (empty($team_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }

        $team_data = json_decode($team_info['team_data'],TRUE);
        $collection_player_cache_key = "st_collection_stocks_" . $team_info['collection_id'];
        $stocks_list = $this->get_cache_data($collection_player_cache_key);
        if (!$stocks_list) {
            $this->load->model("lineup/Lineup_model");
            $post_data['collection_id'] = $team_info['collection_id'];
            $post_data['published_date'] = $team_info['published_date'];
            $post_data['end_date'] = $team_info['end_date'];
            $post_data['scheduled_date'] = $team_info['scheduled_date'];
            $post_data['category_id'] = isset($team_info['category_id']) ? $team_info['category_id'] : "";
            $stocks_list = $this->Lineup_model->get_all_stocks($post_data);
            //set collection players in cache for 2 days
            $this->set_cache_data($collection_player_cache_key, $stocks_list, REDIS_1_MINUTE);
        }
        $stock_list_array = array_column($stocks_list, NULL, 'stock_id');
        $final_player_list = array();
        if($team_info['is_lineup_processed'] == "1"){
            if(isset($team_data['captain_player_team_id'])){
                $team_data['c_id'] = $team_data['captain_player_team_id'];
            }
    
            $lineup_details = $this->Livestockfantasy_model->get_lineup_with_score($lineup_master_contest_id, $team_info);

            $team_data['sl'] = array_column($lineup_details,NULL,"stock_id");
        }else if(in_array($team_info['is_lineup_processed'],array("2","3"))){
            $completed_team = $this->Livestockfantasy_model->get_single_row("collection_id,lineup_master_id,team_data",COMPLETED_TEAM, array("collection_id" => $team_info['collection_id'], "lineup_master_id" => $team_info['lineup_master_id']));
            $team_data = json_decode($completed_team['team_data'],TRUE);

            $team_data['sl'] = $this->get_rendered_stock_array($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
			$team_data['sl'] = array_merge($team_data['sl'],$sell_data);
			$team_data['sl'] = array_column($team_data['sl'],NULL,"stock_id");
        }else{
            $team_data['b'] = array_fill_keys($team_data['b'],"0");
			$team_data['s'] = array_fill_keys($team_data['s'],"0");

			$team_data['sl'] = $this->get_rendered_stock_array($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
			$team_data['sl'] = array_merge($team_data['sl'],$sell_data);
			$team_data['sl'] = array_column($team_data['sl'],NULL,"stock_id");
        }

        if(!empty($team_data['sl'])){
            $this->load->model("wishlist/Wishlist_model");
            $wishlist_stock_ids  = $this->Wishlist_model->fetch_wishlist_stock_ids($this->user_id, TRUE);
		

            foreach ($team_data['sl'] as $stock_id=>$score_data) {
                $stock_info = $stock_list_array[$stock_id];
                if(!empty($stock_info)) {
                    $captain = 0;
                    if($stock_id == $team_data['c_id']){
                        $captain = 1;
                    }

                    if(isset($team_data['vc_id']) && $stock_id == $team_data['vc_id']){
                        $captain = 2;
                    }
                    $lineup = array();
                    $lineup['stock_id'] = $stock_info['stock_id'];
                    $lineup['stock_name'] = $stock_info['stock_name'];
                    $lineup['logo'] = $stock_info['logo'];
                    $lineup['player_role'] = $captain;
                    $lineup['score'] = $score_data['score'];
                    $lineup['type'] = $score_data['type'];
                    $lineup['current_price'] = $stock_info['current_price'];
                    $lineup['last_price'] = $stock_info['last_price'];
                    $lineup['price_diff'] = $stock_info['price_diff'];
                    $lineup['lot_size'] = $stock_info['lot_size'];
                    $lineup['percent_change'] = $stock_info['percent_change'];
                    $lineup['is_wish'] = 0;
                    if(in_array($stock_info['stock_id'], $wishlist_stock_ids)) {
                        $lineup['is_wish'] = 1;
                    }

                    $final_stock_list[] = $lineup;
                }
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['team_detail_not_found'];
            $this->api_response();
        }
        

        $result_data = array();
        $result_data["team_info"] = array(
            "total_score" => $team_info["total_score"],
            "team_name" => $team_info["team_name"],
            "lineup_master_id" => $team_info["lineup_master_id"]
        );
        $result_data["lineup"] = $final_stock_list;
        return $result_data;
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
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $contest = $this->Livestockfantasy_model->get_contest_detail($post_data);
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }
        //check for existence
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $exist = $this->Livestockfantasy_model->get_single_row("contest_id,code", INVITE, array("contest_id" => $contest['contest_id'], "user_id" => 0));
        if (empty($exist)) {
            $code = $this->Livestockfantasy_model->_generate_contest_code();
            $invite_data = array(
                "contest_id" => $contest['contest_id'],
                "contest_unique_id" => $contest["contest_unique_id"],
                "invite_from" => $this->user_id,
                "code" => $code,
                "expire_date" => date(DATE_FORMAT, strtotime($contest['season_scheduled_date'])),
                "created_date" => format_date(),
                "status" => 1
            );
            $this->Livestockfantasy_model->save_invites(array($invite_data));
        } else {
            $code = $exist['code'];
        }
//         echo $code;
// die('fdf');
        $this->api_response_arry['data'] = $code;
        $this->api_response();
    }

    /**
     * used for have a league code
     * @param int $join_code
     * @param int $contest_id
     * @return array
     */
    public function check_eligibility_for_contest_post() 
    {
        $this->form_validation->set_rules('join_code', $this->lang->line('join_code'), 'trim|required');
        $this->form_validation->set_rules('stock_type', $this->lang->line('stock_type'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }   

        $post_data = $this->input->post();
        $join_code = $post_data['join_code'];
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $where = "(code ='{$join_code}' AND email= '{$this->email}' ) OR (code='{$join_code}' AND user_id = 0 ) ";
        
        //ALLOW_NETWORK_FANTASY = 1
        
        $row = $this->Livestockfantasy_model->get_single_row("contest_id", INVITE, $where);

        //echo "<pre>";print_r($row);die;
        
        if (empty($row)) 
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest_code'];
            $this->api_response();
        }

            $contest_id = $row['contest_id'];
            $contest_cache_key = "st_contest_" . $contest_id;
            $contest = $this->get_cache_data($contest_cache_key);
            if (!$contest) {
                $post_data['contest_id'] = $contest_id;
                $contest = $this->Livestockfantasy_model->get_contest_detail($post_data);
                //set master position in cache for 2 hours
                $this->set_cache_data($contest_cache_key, $contest, REDIS_2_HOUR);
            }

            if (!empty($contest['stock_type']) && $contest['stock_type'] !=$post_data['stock_type'] ) 
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->contest_lang['invalid_contest_code'];
                $this->api_response();
            }

            $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
           
            $contest['game_starts_in'] = (strtotime($contest['scheduled_date']) - ($deadline_time * 60)) * 1000;
            if (!empty($contest['prize_distibution_detail'])) {
                $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail'], true);
                if ($contest['prize_distibution_detail'] == null) {
                    $contest['prize_distibution_detail'] = array();
                }
            }

            $contest['game_type'] = "stock";
            $contest['user_join_count'] = 0;
            $this->api_response_arry['data'] = $contest;
            $this->api_response();
        
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

        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $contest = $this->Livestockfantasy_model->get_single_row("contest_id,contest_unique_id", CONTEST, array("contest_unique_id" => $post_data['contest_unique_id']));
        if (empty($contest)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }
        $post_data['contest_id'] = $contest['contest_id'];
        $contest = $this->Livestockfantasy_model->get_contest_detail($post_data);
        //contest matches list data in cache
        $collection_id = $contest['collection_id'];
        $fixture_cache_key = "st_fixture_" . $collection_id;
        $collection_details = $this->get_cache_data($fixture_cache_key);
        if (!$collection_details) {
            $collection_details = $this->Livestockfantasy_model->get_single_row('name,scheduled_date,category_id',COLLECTION,array('collection_id' => $collection_id));
            unset($collection_details['scheduled_date']);
            $this->set_cache_data($fixture_cache_key, $collection_details, 28800);
        }
        $contest = array_merge($contest, $collection_details);
      
        unset($contest['is_auto_recurring']);
        unset($contest['is_custom_prize_pool']);
        
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
       
        $contest['game_starts_in'] = (strtotime($contest['scheduled_date']) - ($deadline_time * 60)) * 1000;
        if (!empty($contest['prize_distibution_detail'])) {
            $contest['prize_distibution_detail'] = json_decode($contest['prize_distibution_detail'], true);
            if ($contest['prize_distibution_detail'] == null) {
                $contest['prize_distibution_detail'] = array();
            }
        }

        $contest['merchandise'] = array();
        if(isset($contest['is_tie_breaker']) && $contest['is_tie_breaker'] == 1){
            $tmp_ids = array();
            foreach($contest['prize_distibution_detail'] as $prize){
                if(isset($prize['prize_type']) && $prize['prize_type'] == 3){
                    $tmp_ids[] = $prize['amount'];
                }
            }
            if(!empty($tmp_ids)){
                $this->load->model("livestockfantasy/Livestockfantasy_model");
                $merchandise_list = $this->Livestockfantasy_model->get_merchandise_list($tmp_ids);
                $contest['merchandise'] = $merchandise_list;
            }
        }

        $contest['is_confirmed'] = 0;
        if($contest['guaranteed_prize'] != 2 && $contest['size'] > $contest['minimum_size'] && $contest['entry_fee'] > 0){
            $contest['is_confirmed'] = 1;
        }

        $this->api_response_arry['data'] = $contest;
        $this->api_response();
    }

    /**
     * Used for get user joined count
     * @param int $contest_id
     * @return array
     */
    public function get_user_contest_join_count_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $user_joined_count = 0;
        $lineup_master_id = '';
        $team_name = '';
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $result = $this->Livestockfantasy_model->get_user_contest_join_count($post_data);
        if (isset($result['user_joined_count'])) {
            $user_joined_count = $result['user_joined_count'];
        }
        if (isset($result['lineup_master_id'])) {
            $lineup_master_id = $result['lineup_master_id'];
        }
        if (isset($result['team_name'])) {
            $team_name = $result['team_name'];
        }

        $this->api_response_arry['data'] = array("user_joined_count" => $user_joined_count,"lineup_master_id"=>$lineup_master_id,"team_name"=>$team_name);
        $this->api_response();
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
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('lineup_master_contest_id', $this->lang->line('lineup_master_contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['user_id'] = $this->user_id;
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $check_valid_previous_team = $this->Livestockfantasy_model->check_valid_user_previous_team($post_data);
        if (empty($check_valid_previous_team)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_previous_team_for_collecton'];
            $this->api_response();
        }

        $check_valid = $this->Livestockfantasy_model->get_single_row("lineup_master_contest_id", LINEUP_MASTER_CONTEST, array("contest_id" => $post_data['contest_id'], "lineup_master_id" => $post_data['lineup_master_id']));
        if (!empty($check_valid)) {
            $message = $this->contest_lang['you_already_joined_this_contest'];
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $message;
            $this->api_response();
        }
        $is_valid = $this->Livestockfantasy_model->check_valid_team_for_contest($post_data);
        if ($is_valid) {
            $team_arr = array();
            $team_arr['lineup_master_id'] = $post_data['lineup_master_id'];
            $this->Livestockfantasy_model->update(LINEUP_MASTER_CONTEST, $team_arr, array('lineup_master_contest_id' => $post_data['lineup_master_contest_id']));

            $this->api_response_arry['message'] = $this->contest_lang['team_switch_success'];
            $this->api_response();
        } else {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_team_for_collecton'];
            $this->api_response();
        }
    }

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
        $this->load->model('Livestockfantasy_model');


        $top_three= $this->Livestockfantasy_model->get_new_contest_leaderboard_top_three($post );

        $top_user_ids = array();
        if(!empty($top_three))
        {
            $top_user_ids = array_column($top_three,'user_id');
        }

        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->Livestockfantasy_model->get_new_contest_leaderboard($post ,$this->user_id);
            foreach($own_result['other_list'] as $key => $row)
            {
                if($row['user_id'] == $this->user_id)
                {
                    $own_leaderboard[] = $row;
                }
            }
        }

        $result = $this->Livestockfantasy_model->get_new_contest_leaderboard($post );
        
        if(!empty($result['other_list']))
        {
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result['other_list'],'user_id'));
            $user_ids = array_unique(array_merge($user_ids,$top_user_ids,array($this->user_id)));
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            $user_images = array();
            if(!empty($user_details))
            {
                $user_images = array_column($user_details,'image','user_id');
                $user_details = array_column($user_details,'user_name','user_id');
            }

            foreach($result['other_list'] as $key => & $val)
            {
                $val['username'] = '';
                if(isset($user_details[$val['user_id']]))
                {
                    $val['user_name'] =  $user_details[$val['user_id']];
                    $val['image'] =  $user_images[$val['user_id']];
                }

                if($val['user_id'] == $this->user_id)
                {
                    $own_leaderboard[] = $result['other_list'][$key];
                    unset($result['other_list'][$key]);
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }
            }

            $result['other_list'] = array_values($result['other_list']);
        }
        else
        {
            $this->load->model('user/User_model');
            $total_user_ids = array_unique(array_merge($top_user_ids,array($this->user_id)));
            $user_details = $this->User_model->get_users_by_ids($total_user_ids);
            $user_images = array();
            if(!empty($user_details))
            {
                $user_images = array_column($user_details,'image','user_id');
                $user_details = array_column($user_details,'user_name','user_id');
            }
        }

        foreach($top_three as $key => & $val)
        {
            $val['username'] = '';
            if(isset($user_details[$val['user_id']]))
            {
                $val['user_name'] =  $user_details[$val['user_id']];
                $val['image'] =  $user_images[$val['user_id']];
            }

            if(!empty($val['prize_data']))
            {
                $val['prize_data'] = json_decode($val['prize_data'],TRUE);
            }
        }

        if(!empty($own_leaderboard))
            {
                foreach($own_leaderboard as $key => &$val_own)
                {
                    if(!empty($val_own['prize_data']))
                    {
                        $val_own['prize_data'] = json_decode($val_own['prize_data'],TRUE);
                    }

                    $val_own['username'] = '';
                    if(isset($user_details[$val_own['user_id']]))
                    {
                        $val_own['user_name'] =  $user_details[$val_own['user_id']];
                        $val_own['image'] =  $user_images[$val_own['user_id']];
                    }
                }
                $result['own'] =$own_leaderboard;
            }


        $result['top_three'] =$top_three;

        $contest_details= $this->Livestockfantasy_model->get_single_row('prize_distibution_detail, collection_id',CONTEST,array('contest_id' => $post['contest_id']) );

        if(!empty($contest_details['prize_distibution_detail'])) {
            $result['prize_data'] = json_decode($contest_details['prize_distibution_detail'],TRUE);
        }

        $result['score_updated_date'] = '';
        if(!empty($contest_details['collection_id'])) {
            $collection_details= $this->Livestockfantasy_model->get_single_row('IFNULL(score_updated_date,"") as score_updated_date',COLLECTION,array('collection_id' => $contest_details['collection_id']) );
            $result['score_updated_date'] = $collection_details['score_updated_date'];
        }
        $this->api_response_arry['service_name'] = 'get_new_contest_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
      
    }

    private function get_rendered_stock_array($arr,$type)
	{
		$tmp = array();
		foreach($arr as $stock_id => $score)	{
			$tmp[] = array('stock_id' => $stock_id,'score' => $score,'type' => $type );
	  }
	  return $tmp;
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

        $current_date = format_date();
        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $lineup_master_contest_id = $post_data["lineup_master_contest_id"];
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $team_info = $this->Livestockfantasy_model->get_contest_collection_details_by_lmc_id($lineup_master_contest_id);
        if (empty($team_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }
        $team_data = json_decode($team_info['team_data'],TRUE);
        
        $this->load->model("lineup/Lineup_model");
        $post_data['collection_id'] = $team_info['collection_id'];
        $post_data['published_date'] = $team_info['published_date'];
        $post_data['end_date'] = $team_info['end_date'];
        $post_data['scheduled_date'] = $team_info['scheduled_date'];
        $stocks_list = $this->Lineup_model->get_all_stocks($post_data);
            
        $stock_list_array = array_column($stocks_list, NULL, 'stock_id');
        if($team_info['is_lineup_processed'] == "1"){
            $lineup_details = $this->Livestockfantasy_model->get_lineup_with_score($lineup_master_contest_id, $team_info);
            $team_data['pl'] = array_column($lineup_details,NULL,"stock_id");
        }else if(in_array($team_info['is_lineup_processed'],array("2","3"))){
            $completed_team = $this->Livestockfantasy_model->get_single_row("collection_id,lineup_master_id,team_data",COMPLETED_TEAM, array("collection_id" => $team_info['collection_id'], "lineup_master_id" => $team_info['lineup_master_id']));
            $team_data = json_decode($completed_team['team_data'],TRUE);
            $team_data['pl'] = $this->get_rendered_stock_array($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
			$team_data['pl'] = array_merge($team_data['pl'],$sell_data);
			$team_data['pl'] = array_column($team_data['pl'],NULL,"stock_id");
        }else{
            $team_data['b'] = array_fill_keys($team_data['b'],"0");
			$team_data['s'] = array_fill_keys($team_data['s'],"0");

			$team_data['pl'] = $this->get_rendered_stock_array($team_data['b'],1);
			$sell_data = $this->get_rendered_stock_array($team_data['s'],2);
			$team_data['pl'] = array_merge($team_data['pl'],$sell_data);
			$team_data['pl'] = array_column($team_data['pl'],NULL,"stock_id");
			
        }

        $final_stock_list = array();
        if(!empty($team_data['pl'])){
            foreach ($team_data['pl'] as $stock_id=>$score_data) {
                $player_info = $stock_list_array[$stock_id];
                if(!empty($player_info)) {
                    $captain = 0;
                    if($stock_id == $team_data['c_id']){
                        $captain = 1;
                    }

                    if(isset($team_data['vc_id']) && $stock_id == $team_data['vc_id']){
                        $captain = 2;
                    }
                    /*To Get Accuracy percent in case of Stock Predict*/
                    $stock_predict_accuracy_percent =  $this->Livestockfantasy_model->get_single_row('accuracy_percent',LINEUP,array('stock_id'=>$score_data['stock_id'],'lineup_master_id'=>$score_data['lineup_master_id']));

                    $lineup = array();
                    $lineup['stock_id'] = $player_info['stock_id'];
                    $lineup['stock_name'] = $player_info['stock_name'];
                    $lineup['logo'] = $player_info['logo'];
                    $lineup['current_price'] = $player_info['current_price'];
                    $lineup['last_price'] = $player_info['last_price'];
                    $lineup['price_diff'] = $player_info['price_diff'];
                    $lineup['lot_size'] = $player_info['lot_size'];
                    $lineup['percent_change'] = $player_info['percent_change'];
                    $lineup['player_role'] = $captain;
                    $lineup['type'] = $score_data['type'];
                    $lineup['score'] = $score_data['score']; 
                     $lineup['accuracy_percent'] = $stock_predict_accuracy_percent['accuracy_percent'];  //                                
                    $final_stock_list[] = $lineup;
                }
            }
        }else{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['team_detail_not_found'];
            $this->api_response();
        }
     
        $result_data = array();
        $result_data["team_info"] = array("total_score" => $team_info["total_score"], "team_name" => $team_info["team_name"]);
        $result_data["lineup"] = $final_stock_list;
        $this->api_response_arry['data'] = $result_data;
        $this->api_response();
    }

     /**
     * used for get user team list for switch
     * @param int $sports_id
     * @param int $contest_id
     * @return array
     */
    public function get_user_switch_team_list_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $post_data['user_id'] = $this->user_id;
        $this->load->model("livestockfantasy/Livestockfantasy_model");
        $team_data = $this->Livestockfantasy_model->get_collection_contest_free_teams($post_data);

        $this->api_response_arry['data'] = $team_data;
        $this->api_response();
    }

     /**
     * Used for join contest
     * @param int $lineup_master_id
     * @param int $contest_id
     * @return array
     */
    public function multiteam_join_game_post() {
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
        $current_date = format_date();
        $this->load->model("Livestockfantasy_model");
        $contest_cache_key = "st_contest_" . $post_data['contest_id'];
        $contest = $this->get_cache_data($contest_cache_key);
        if (!$contest) {
            $contest = $this->Livestockfantasy_model->get_contest_detail($post_data);
            //set master position in cache for 2 hours
            $this->set_cache_data($contest_cache_key, $contest, REDIS_2_HOUR);
        }
        $this->entry_fee = $contest['entry_fee'];
        $this->contest_unique_id = $contest['contest_unique_id'];
        $this->currency_type = $contest['currency_type'];
        $is_valid = $this->validation_for_multiple_join_game($contest, $post_data);
        if ($is_valid) {
            if ($is_valid || $contest['entry_fee'] == 0) {
                $is_success = 0;
                $team_error = array();
                foreach($lineup_master_ids as $lineup_master_id){
                    $post_data['lineup_master_id'] = $lineup_master_id;
                    $joined = $this->Livestockfantasy_model->join_game($contest, $post_data);
                    $joined_status = 1;
                    if (isset($joined['joined_count']) && $contest['entry_fee'] > 0) {
                        $cash_type = "2";   
                        if($this->currency_type == "2"){    
                            $cash_type = "3";   
                        }
                        $withdraw = array();
                        $withdraw["source"] = CONTEST_JOIN_SOURCE;//460 game join
                        $withdraw["source_id"] = $joined['lineup_master_contest_id'];
                        $withdraw["reason"] = FANTASY_CONTEST_NOTI1;
                        $withdraw["cash_type"] = $cash_type;
                        $withdraw["user_id"] = $this->user_id;
                        $withdraw["amount"] = $this->entry_fee;
                        $withdraw["currency_type"] = $this->currency_type;
                        $withdraw["max_bonus_allowed"] = $contest['max_bonus_allowed'];
                        $withdraw["site_rake"] = $contest['site_rake'];
                        $withdraw["reference_id"] = $contest['contest_id'];
                        $withdraw["custom_data"]['contest_name'] = $contest['contest_name'];
                        $withdraw["custom_data"]['contest_type'] = $contest['group_name'];
                        $withdraw["custom_data"]['match_date'] = date('d-M-Y H:i:s',strtotime($contest['scheduled_date']));
                        $this->load->model("user/User_model");
                        $join_result = $this->User_model->withdraw($withdraw);
                        if (empty($join_result) || $join_result['result'] == 0) {
                            //remove user joined entry
                            $this->Livestockfantasy_model->remove_joined_game($contest, $joined['lineup_master_contest_id']);

                            $joined_status = 0;
                        }
                    }

                    //join game notification
                    if (isset($joined['joined_count']) && $joined_status == 1) {
                        $is_success = 1;
                        //create auto recurring contest
                        if (isset($joined['joined_count']) && $joined['joined_count'] == $contest['size'] && $contest['is_auto_recurring'] == '1') {
                            $this->load->helper('queue_helper');
                            $contest_queue = array("action" => "auto_recurring", "data" => array("contest_unique_id" => $contest['contest_unique_id']));
                            add_data_in_queue($contest_queue, 'stock_contest');
                        }

                        $input = array(
                            'contest_name' => $contest['contest_name'],
                            'contest_unique_id' => $contest['contest_unique_id'],
                            'contest_id' => $contest['contest_id'],
                            'entry_fee' => $contest['entry_fee'],
                            'prize_pool' => $contest['prize_pool'],
                            'prize_type' => $contest['prize_type'],
                            'currency_type' => $contest['currency_type'],
                            'prize_distibution_detail' => json_decode($contest['prize_distibution_detail'],TRUE),
                            'season_scheduled_date' => $contest["scheduled_date"],
                            "collection_name" => (!empty($contest['collection_name'])) ? $contest['collection_name'] : $contest['contest_name']
                        );

                        $notify_data = array();
                        $notify_data["notification_type"] = CONTEST_JOIN_NOTIFY; //553-JoinGame, 
                        $notify_data["source_id"] = $joined['lineup_master_contest_id'];
                        $allow_join_email = isset($this->app_config['allow_join_email'])?$this->app_config['allow_join_email']['key_value']:0;  
                        $notify_data["notification_destination"] = 3; //Web,Push,Email  
                        if($allow_join_email)   
                        {   
                            $notify_data["notification_destination"] = 7; //Web,Push    
                        }
                        $notify_data["user_id"] = $this->user_id;
                        $notify_data["to"] = $this->email;
                        $notify_data["user_name"] = $this->user_name;
                        $notify_data["added_date"] = $current_date;
                        $notify_data["modified_date"] = $current_date;
                        $notify_data["content"] = json_encode($input);
                        $notify_data["subject"] = $this->contest_lang['join_game_email_subject'];
                        $this->load->model('user/User_nosql_model');
                        $this->User_nosql_model->send_notification($notify_data);
                    }else{
                        $team_error[] = $lineup_master_id;
                    }
                }

                if ($is_success == 1) {
                    //delete user balance data
                    $user_balance_cache_key = 'user_balance_' . $this->user_id;
                    $this->delete_cache_data($user_balance_cache_key);

                    //delete contest cache
                    $this->delete_cache_data($contest_cache_key);

                    //delete joined data
                    $user_contest_cache_key = 'st_user_contest_' . $contest["collection_id"] . "_" . $this->user_id;
                    $this->delete_cache_data($user_contest_cache_key);

                    //collection contest
                    $collection_contest_cache_key = "st_collection_contest_" . $contest["collection_id"];
                    $this->delete_cache_data($collection_contest_cache_key);

                    //collection pin contest
                    if(isset($contest["is_pin_contest"]) && $contest["is_pin_contest"] == 1){
                        $collection_contest_cache_key = "st_collection_pin_contest_" . $contest["collection_id"];
                        $this->delete_cache_data($collection_contest_cache_key);

                    }

                    //delete cache
                    $user_teams_cache_key = "st_user_teams_".$contest['collection_id']."_".$this->user_unique_id;
                    $this->delete_cache_data($user_teams_cache_key);

                    //for delete s3 bucket file
                    $this->push_s3_data_in_queue("st_user_teams_" . $contest["collection_id"] . "_" . $this->user_unique_id, array(), "delete");

                    //for delete s3 bucket file
                    $this->push_s3_data_in_queue("st_contest_detail_" . $contest["contest_id"], array(), "delete");
                    
                    $tm_count = count($lineup_master_ids) - count($team_error);
                    $join_msg = str_replace("{TEAM_COUNT}",$tm_count,$this->contest_lang['multiteam_join_game_success']);
                    $this->api_response_arry['message'] = $join_msg;
                    $this->api_response();
                } else {
                    $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                    $this->api_response_arry['message'] = $this->contest_lang['problem_while_join_game'];
                    $this->api_response();
                }
            } else {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['message'] = $this->message;
                $this->api_response();
            }
        } else {
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
    public function validation_for_multiple_join_game($contest, $post_data) {
        if (empty($contest)) {
            $this->message = $this->contest_lang['invalid_contest'];
            return 0;
        }

        //for manage collection wise deadline
        $lineup_master_ids = $post_data['lineup_master_id'];
        $current_date = format_date();
        $deadline_time = CONTEST_DISABLE_INTERVAL_MINUTE;
      
        $current_time = date(DATE_FORMAT, strtotime($current_date . " +" . $deadline_time . " minute"));

        //check for match schedule date
        if (strtotime($contest['scheduled_date']) < strtotime($current_time)) {
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

        $user_join_data = $this->Livestockfantasy_model->get_user_contest_join_count($contest);
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
        $check_team_join = $this->Livestockfantasy_model->get_single_row("count(lineup_master_contest_id) as total", LINEUP_MASTER_CONTEST, array("contest_id" => $post_data['contest_id'],"lineup_master_id IN(".implode(',',$lineup_master_ids).")"=>NULL));
        if(!empty($check_team_join) && $check_team_join['total'] > 0){
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['already_joined_with_teams'];
            $this->api_response();
        }

        //check for valid line up of user
        $user_lineup = $this->Livestockfantasy_model->get_single_row("count(lineup_master_id) as total,GROUP_CONCAT(DISTINCT collection_id) as collection_id", LINEUP_MASTER, array("lineup_master_id IN(".implode(',',$lineup_master_ids).")" => NULL, "user_id" => $this->user_id));
        if (empty($user_lineup) || $user_lineup['total'] != count($lineup_master_ids)) {
            $this->message = $this->contest_lang['provide_a_valid_lineup_master_id'];
            return 0;
        }
        $user_lineup['collection_id'] = explode(",",$user_lineup['collection_id']);
        if (count($user_lineup['collection_id']) != "1" || !in_array($contest['collection_id'],$user_lineup['collection_id'])) {
            $this->message = $this->contest_lang['not_a_valid_team_for_contest'];
            return 0;
        }
        
        if ($this->entry_fee == '0') {
            return 1;
        }

        $total_entry_fee = $this->entry_fee * count($lineup_master_ids);
        if ($contest['prize_type'] == 2 || $contest['prize_type'] == 3) { // for Conins 
            if ($total_entry_fee > $this->point_balance) {
                $this->message = $this->contest_lang['not_enough_coins'];
                $this->enough_coins = 0;
                return 0;
            }
        } else {
            //get user balance
            $this->load->model("user/User_model");
            $balance_arr = $this->User_model->get_user_balance($this->user_id);
            $this->user_bonus_bal = $balance_arr['bonus_amount'];
            $this->user_bal = $balance_arr['real_amount'];
            $this->winning_bal = $balance_arr['winning_amount'];
            $bonus_amount = $max_bonus = 0;
            //$max_bonus_percentage = MAX_BONUS_PERCEN_USE;
            if ($contest['max_bonus_allowed']) {
                $max_bonus_percentage = $contest['max_bonus_allowed'];
                $max_bonus = ($total_entry_fee * $max_bonus_percentage) / 100;
                $bonus_amount = $max_bonus;
            }
            if ($max_bonus > $this->user_bonus_bal) {
                $bonus_amount = $this->user_bonus_bal;
            }
            
            if(MAX_CONTEST_BONUS > 0 && $bonus_amount > MAX_CONTEST_BONUS) {
                $bonus_amount = MAX_CONTEST_BONUS;
            }
            if ($total_entry_fee > ($bonus_amount + $this->user_bal + $this->winning_bal)) {
                $this->message = $this->contest_lang['not_enough_balance'];
                $this->enough_balance = 0;
                return 0;
            }
        }
        // for self exclusion
        if($this->currency_type == "1"){
            $this->self_exclusion_limit = 0;
            // $this->get_app_config_data();
            // $allow_self_exclusion = isset($this->app_config['allow_self_exclusion'])?$this->app_config['allow_self_exclusion']['key_value']:0;        
            // if($allow_self_exclusion) {
            //     $custom_data = $this->app_config['allow_self_exclusion']['custom_data'];
            //     $default_max_limit = $custom_data['default_limit'];
            //     $contest_ids = $this->Livestockfantasy_model->user_join_contest_ids($this->user_id);
            //     //for network fantasy
            //     if(ALLOW_NETWORK_FANTASY == 1)
            //     {
            //         //check for network match also
            //         $post_data['client_id'] = NETWORK_CLIENT_ID;
            //         $post_data['user_id']   = $this->user_id;

            //         $url = NETWORK_FANTASY_URL."/fantasy/contest/user_join_contest_ids";
            //         $api_response =  $this->http_post_request($url,$post_data);
            //         $nw_data = json_decode($api_response,TRUE);
            //         if(!empty($nw_data['data']))
            //         {
            //            $contest_ids = array_merge($contest_ids,$nw_data['data']);
            //         }    
            //     }    

            //     if(!empty($contest_ids)) {
            //         $this->load->model("user/User_model");
            //         $self_exclusion_data = array('user_id' => $this->user_id, 'contest_ids' => $contest_ids, 'max_limit' => $default_max_limit, 'entry_fee' => $total_entry_fee);
            //         $this->message = $this->contest_lang['self_exclusion_limit_reached'];
            //         $flag =  $this->User_model->check_self_exclusion($self_exclusion_data);
            //         if(empty($flag)) {
            //             $this->self_exclusion_limit = 1;
            //         }
            //         return $flag;
            //     }
            // }
        }

        return 1;
 
    }

    /**
     * used for download joined users team list
     * @param int $sports_id
     * @param int $contest_id
     * @return array
     */
    public function download_contest_teams_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        if (!isset($post_data['limit'])) {
            $post_data['limit'] = 1000;
        }
        $filePath = "stock_lineup/" . $contest_id . ".pdf";
        try{
            $data_arr = array();
            $data_arr['file_path'] = $filePath;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_upload = $upload_lib->get_file_info($data_arr);
            if(!empty($is_upload)){
                $this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
                $this->api_response_arry['data'] = array('uploaded' => '1', 'file' => IMAGE_PATH . $filePath);
                $this->api_response();
            }
        }catch(Exception $e){
            //$result = 'Caught exception: '.  $e->getMessage(). "\n";
        }

        $this->load->model("Livestockfantasy_model");
        $contest_info = $this->Livestockfantasy_model->get_single_row("contest_id,is_pdf_generated", CONTEST, array("contest_id" => $contest_id));
        if (empty($contest_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['contest_not_found'];
            $this->api_response();
        }

        if ($contest_info['is_pdf_generated'] == 0) {
            $this->load->helper('queue_helper');
            $server_name = SERVER_IP . PROJECT_FOLDER_NAME;
            if (ENVIRONMENT == 'production') {
                $server_name = "http://localhost";
            }
            $content = array();
            $content['url'] = $server_name . "/stock/cron/generate_live_contest_pdf/" . $contest_info['contest_id'];
            add_data_in_queue($content, 'stock_contestpdf');

            //update push status
            $this->Livestockfantasy_model->update(CONTEST, array("is_pdf_generated" => "1"), array("contest_id" => $contest_info['contest_id']));
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
        $this->api_response_arry['message'] = $this->contest_lang['process_contest_pdf'];
        $this->api_response();
    }

    public function get_lineup_score_calculation_post()
    {
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');
        $this->form_validation->set_rules('collection_id', $this->lang->line('collection_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post_data = $this->input->post();
        $this->load->model("Livestockfantasy_model");

        $row = $this->Livestockfantasy_model->get_single_row('collection_id,published_date,end_date,scheduled_date',COLLECTION,array('collection_id' => $post_data['collection_id']));

        $post_data['published_date'] = $row['published_date'];
        $post_data['end_date'] = $row['end_date'];
        $post_data['scheduled_date'] = $row['scheduled_date'];
        $result = $this->Livestockfantasy_model->get_lineup_score_calculation($post_data);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $result;
        $this->api_response();
    }

    /**
     * used for validate contest promo code
     * @param int $contest_id
     * @param string $promo_code
     * @return array
     */
    public function validate_contest_promo_code_post() {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        $this->form_validation->set_rules('promo_code', $this->lang->line('promo_code'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
        $post_data = $this->input->post();
        $contest_id = $post_data['contest_id'];
        $this->load->model("Livestockfantasy_model");
        $contest_info = $this->Livestockfantasy_model->get_contest_detail($post_data);
        if (empty($contest_info)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang['invalid_contest'];
            $this->api_response();
        }

        $this->load->model("user/User_model");
        $code_detail = $this->User_model->check_promo_code_details($post_data['promo_code']);
        if (empty($code_detail)) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
            $this->api_response();
        } else if ($code_detail['type'] != CONTEST_JOIN_TYPE) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["invalid_promo_code"];
            $this->api_response();
        } else if ($code_detail['total_used'] >= $code_detail['per_user_allowed']) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->contest_lang["exceed_promo_used_count"];
            $this->api_response();
        } else {

            if ($code_detail['value_type'] == "1") {
                $bonus_amount = ($contest_info['entry_fee'] * $code_detail['discount']) / 100;
                if ($bonus_amount > $code_detail['benefit_cap']) {
                    $bonus_amount = $code_detail['benefit_cap'];
                }
            } else {
                $bonus_amount = $code_detail['discount'];
            }

            $result_data = array('promo_code_id' => $code_detail['promo_code_id'], 'discount' => $code_detail['discount'], "amount" => $bonus_amount, "promo_code" => $code_detail['promo_code'], "cash_type" => $code_detail['cash_type'], "value_type" => $code_detail['value_type']);
            $this->api_response_arry['data'] = $result_data;
            $this->api_response();
        }
    }

    /**
     * [get_new_contest_leaderboard description]
     * @uses :- get contest leaderboard
     * @param Number prediction master id
     */
    public function get_contest_leaderboard_post()
    {
        $this->form_validation->set_rules('contest_id', $this->lang->line('contest_id'), 'trim|required');
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }

        $post = $this->input->post();
        $current_date = format_date();
       
        $_POST = $post;
        $this->load->model('Livestockfantasy_model');

        $own_leaderboard = array();
        if($this->user_id)
        {
            $own_result= $this->Livestockfantasy_model->get_contest_leaderboard($post ,$this->user_id);
            foreach($own_result['other_list'] as $key => $row)
            {
                if($row['user_id'] == $this->user_id)
                {
                    $own_leaderboard[] = $row;
                }
            }
        }

        $result = $this->Livestockfantasy_model->get_contest_leaderboard($post);
        
        if(!empty($result['other_list']))
        {
            $this->load->model('user/User_model');
            $user_ids = array_unique( array_column($result['other_list'],'user_id'));
            $user_ids = array_unique(array_merge($user_ids,array($this->user_id)));
            $user_details = $this->User_model->get_users_by_ids($user_ids);
            $user_images = array();
            if(!empty($user_details))
            {
                $user_images = array_column($user_details,'image','user_id');
                $user_details = array_column($user_details,'user_name','user_id');
            }

            foreach($result['other_list'] as $key => & $val)
            {
                $val['username'] = '';
                if(isset($user_details[$val['user_id']]))
                {
                    $val['user_name'] =  $user_details[$val['user_id']];
                    $val['image'] =  $user_images[$val['user_id']];
                }

                if($val['user_id'] == $this->user_id)
                {
                    $own_leaderboard[] = $result['other_list'][$key];
                    unset($result['other_list'][$key]);
                }

                if(!empty($val['prize_data']))
                {
                    $val['prize_data'] = json_decode($val['prize_data'],TRUE);
                }
            }

            $result['other_list'] = array_values($result['other_list']);
        }
        else
        {
            $this->load->model('user/User_model');
            $total_user_ids = array($this->user_id);
            $user_details = $this->User_model->get_users_by_ids($total_user_ids);
            $user_images = array();
            if(!empty($user_details))
            {
                $user_images = array_column($user_details,'image','user_id');
                $user_details = array_column($user_details,'user_name','user_id');
            }
        }

     

        if(!empty($own_leaderboard))
            {
                foreach($own_leaderboard as $key => &$val_own)
                {
                    if(!empty($val_own['prize_data']))
                    {
                        $val_own['prize_data'] = json_decode($val_own['prize_data'],TRUE);
                    }

                    $val_own['username'] = '';
                    if(isset($user_details[$val_own['user_id']]))
                    {
                        $val_own['user_name'] =  $user_details[$val_own['user_id']];
                        $val_own['image'] =  $user_images[$val_own['user_id']];
                    }
                }
                $result['own'] =$own_leaderboard;
            }


        $contest_details= $this->Livestockfantasy_model->get_prize_and_updated_date($post['contest_id']);

        if(!empty($contest_details['prize_distibution_detail']))
        {
            $result['prize_data'] = json_decode($contest_details['prize_distibution_detail'],TRUE);
        }

        $result['score_updated_date']=$contest_details['score_updated_date'];
        $this->api_response_arry['service_name'] = 'get_new_contest_leaderboard';
        $this->api_response_arry['response_code'] = 200;
        $this->api_response_arry['data']          = $result;
        $this->api_response();
      
    }

    function get_user_transaction_post()
    { 
        $this->form_validation->set_rules('lineup_master_id', $this->lang->line('lineup_master_id'), 'trim|required');      
        if (!$this->form_validation->run()) {
            $this->send_validation_errors();
        }
         $post_data = $this->input->post();
        $this->load->model('Livestockfantasy/Livestockfantasy_model');
        $transaction_list= $this->Livestockfantasy_model->get_user_transaction($post_data);    
        $this->api_response_arry['data'] = $transaction_list;
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response();
    }

}