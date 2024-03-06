<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
set_time_limit(0);

class Cron extends Common_Api_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo "Welcome";die();
    }

    /**
     * It is used to get all stock list
     */
    public function stock_list_get() {
        die('Use stock common feed');
        $this->load->helper('queue');
        add_data_in_queue(array('action' => 'stock_list'),'stock_data');
        /*
        $this->load->model("cron/Cron_model");
        $this->Cron_model->stock_list();
        */
    }

    /**
     * It is used to get stock yearly history day wise
     */
    public function stock_historical_data_day_wise_get() {
        die('Use stock common feed');
        $data = array();
        $data['action'] =  'stock_historical_data_day_wise';
        $data['from_date'] =  format_date('today', 'Y-m-d 09:14:00');
        $data['to_date'] =  format_date('today', 'Y-m-d 15:40:00');

        $type = $this->uri->segment(4, 1);
        if($type == 2) {
            $data['type'] =  $type;
            $data['interval'] = '5min';
        }
        //echo 'from_date => '.$data['from_date'].' to_date => '.$data['to_date'];die;
       /* log_message('error', 'Stock Historical Data Day Wise Cron Executed at: '.format_date());
        $this->load->helper('queue');
        add_data_in_queue($data,'stock_data');
        / */
        $this->load->model("cron/Cron_model");
        $this->Cron_model->stock_historical_data_day_wise($data);
        
        
    }

     /**
     * It is used to get stock day history time wise
     */
    public function stock_historical_data_minute_wise_get() {
        die('Use stock common feed');
        $data = array();
        $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
        //$data['to_date'] =  $d->format('Y-m-d H:i:s');
        $to_hour = $d->format('H');
        $to_minute = $d->format('i');
        if($to_hour > 15) {
           return true;
        } else if($to_hour == 15 && $to_minute > 41) {
            return true;
        }
        
        $d->sub(new DateInterval('PT1M')); // 1 min before from current time
       // $data['from_date'] =  $d->format('Y-m-d H:i:s');
        $from_hour = $d->format('H');
        $from_minute = $d->format('i');
        if($from_hour < 9) {
            return true;
        } else if($from_hour == 9 && $from_minute < 14) {
            return true;
        }

        $data['action'] =  'stock_historical_data_minute_wise';        
        $this->load->helper('queue');
        add_data_in_queue($data,'stock_data');
      
         /*
        $this->load->model("cron/Cron_model");
        $this->Cron_model->stock_historical_data_minute_wise($data);     
          */   
    }

    /**
     * It is used to update stock live price
     */
    public function update_stock_latest_quote_get() {
        die('Use stock common feed');
        $type = $this->uri->segment(4, 1);
        if($type == 1) {
            $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
            $hour = $d->format('H');
            $minute = $d->format('i');
            if($hour > 15 || $hour < 9) {
                return true;
            } else if(($hour == 15 && $minute > 41) || ($hour == 9 && $minute < 14)) {
                return true;
            }
        }

        $this->load->helper('queue');
        add_data_in_queue(array('action' => 'update_stock_latest_quote'),'stock_data');
       /*
       $this->load->model("cron/Cron_model");
       $this->Cron_model->update_stock_latest_quote();
       */
   }

    /**
     * It is used to get all instruments
     */
    public function instrument_list_get() {
        die('Use stock common feed');
        $this->load->helper('queue');
        add_data_in_queue(array('action' => 'instrument_list'),'kite_connect');
        /*
        $this->load->model("cron/Cron_model");
        $this->Cron_model->instrument_list();
        */
    }

    /**
     * It is used to update stock live price
     */
    public function update_stock_price_get() {
        die('Use stock common feed');
         $this->load->helper('queue');
        add_data_in_queue(array('action' => 'update_stock_price'),'kite_connect');
        /*
        $this->load->model("cron/Cron_model");
        $this->Cron_model->update_stock_price();
        */
    }

     /**
     * It is used to get stock yearly history day wise
     */
    public function update_stock_historical_data_day_wise_get() {
        die('Use stock common feed');
        $data = array();
        $data['from_date'] =  format_date('today', 'Y-m-d 09:15:00');
        $data['to_date'] =  format_date('today', 'Y-m-d 03:30:00');
        $data['action'] =  'update_stock_historical_data_day_wise';
        //echo 'from_date => '.$data['from_date'].' to_date => '.$data['to_date'];die;
        $this->load->helper('queue');
        add_data_in_queue(array('action' => 'update_stock_historical_data_day_wise'),'kite_connect');
        
       /* $this->load->model("cron/Cron_model");
        $this->Cron_model->update_stock_historical_data_day_wise($data);
        */
    }


     /**
     * It is used to get stock day history time wise
     */
    public function update_stock_historical_data_minute_wise_get() {
        die('Use stock common feed');
        $data = array();
        $d = new DateTime(null, new DateTimeZone("Asia/Kolkata"));
        $data['to_date'] =  $d->format('Y-m-d H:i:s');
        $to_hour = $d->format('H');
        $to_minute = $d->format('i');
        if($to_hour > 15) {
           return true;
        } else if($to_hour == 15 && $to_minute > 35) {
            return true;
        }
        
        $d->sub(new DateInterval('PT1M')); // 1 min before from current time
        $data['from_date'] =  $d->format('Y-m-d H:i:s');
        $from_hour = $d->format('H');
        $from_minute = $d->format('i');
        if($from_hour < 9) {
            return true;
        } else if($from_hour == 9 && $from_minute < 14) {
            return true;
        }

        $data['action'] =  'update_stock_historical_data_minute_wise';
        //echo 'from_date => '.$data['from_date'].' to_date => '.$data['to_date'];die;

        
        $this->load->helper('queue');
        add_data_in_queue($data,'kite_connect');
        
        /*$this->load->model("cron/Cron_model");
        $this->Cron_model->update_stock_historical_data_minute_wise($data);
        */
    }

    /**
     * [game_cancellation description]
     * @Summary :- This function will cancell the games which is not full till drafting.
     * @return  [type]
     */
    public function game_cancellation_get() {  
        //$this->load->helper('queue');
        //add_data_in_queue(array('action' => 'game_cancellation'),'stock_game_cancel');       
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $this->Cron_model->game_cancellation();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }

    /**
    * Function used for send cancel notification
    * @param
    * @return string
    */
    public function match_cancel_notification_get() {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
            $this->Cron_model->match_cancel_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
     * Used for update match contest status
     * @param int 
     * @return string print output
     */
    public function update_contest_status_get()
    {   
        $this->benchmark->mark('code_start');
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        if($prize_cron == "1"){
            $this->load->model('Cron_model');
            
            $this->Cron_model->update_contest_status();
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }

    /**
     * Used for update score in user team lineup table
     * @param int $sports_id
     * @return string
     */
    public function update_scores_in_lineup_by_collection_get($collection_id="")
    {
            $this->load->model('Cron_model');
            $this->benchmark->mark('code_start');
            $this->Cron_model->update_scores_in_lineup_by_collection($collection_id);
            $this->benchmark->mark('code_end');
            echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for contest multi currency prize distribution
    * @param
    * @return string
    */
    public function prize_distribution_get()
    {   
        $this->benchmark->mark('code_start');
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        if($prize_cron == "1"){
            $this->load->model('Cron_model');
            $this->Cron_model->prize_distribution();
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

      /**
     * Used for contest prize distribution
     * @param int $contest_id
     * @return string
     */
    public function contest_prize_distribution_get($contest_id=''){
        $this->benchmark->mark('code_start');
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        if($prize_cron == "1"){
            if(isset($contest_id) && $contest_id != ""){
                $this->load->model('Cron_model');
                $this->Cron_model->contest_prize_distribution($contest_id);
            }
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for contest merchandise prize distribution
    * @param
    * @return string
    */
    public function contest_merchandise_distribution_get($contest_id='')
    {   
        $this->benchmark->mark('code_start');
         $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        if($prize_cron == "1"){
            if(isset($contest_id) && $contest_id != ""){
                $this->load->model('Cron_model');
                $this->Cron_model->contest_merchandise_distribution($contest_id);
            }
        }
        $this->benchmark->mark('code_end');
        echo "<br>Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    /**
    * Function used for send winning notification
    * @param
    * @return string
    */
    public function collection_prize_distribute_notification_get()
    {
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
            $this->Cron_model->collection_prize_distribute_notification();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

     /**
     * Update upcoming collection team data format
     * @param void
     * @return string
     */
    public function move_completed_collection_team_get(){
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->move_completed_collection_team();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function unit_test_get()
    {
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->unit_testing();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

      /**
     * Update upcoming collection team data format
     * @param void
     * @return string
     */
    public function move_completed_collection_team_equity_get(){
        $this->benchmark->mark('code_start');
        $this->load->model('Cron_model');
        $this->Cron_model->move_completed_collection_team_equity();
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

     /**
     * Used for generate contest pdf and upload on s3
     * @param int $contest_id
     * @return string
     */
    public function generate_live_contest_pdf_get($contest_id)
    {   
        $this->benchmark->mark('code_start');
        if($contest_id != ""){
            ini_set('memory_limit', '-1');
            $this->load->model('Cron_model');
            $this->Cron_model->generate_live_contest_pdf($contest_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function revert_completed_team($collection_id)
    {
        $this->benchmark->mark('code_start');
        if($collection_id != ""){
           
            $this->load->model('Cron_model');
            $this->Cron_model->revert_completed_team($collection_id);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function contest_tds_deduction_get($contest_id)
    {
        $this->benchmark->mark('code_start');
        $prize_cron =  isset($this->app_config['prize_cron']) ? $this->app_config['prize_cron']['key_value']:0;
        if($prize_cron == "1"){
            if($contest_id != ""){
               
                $this->load->model('Cron_model');
                $this->Cron_model->deduct_tds_from_winning($contest_id);
            }
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
    }

    public function test_get($template_path,$file)
    {
        $path = $template_path.'/'.$file;
       

        //$this->input->post();
        $data =array();
        $data['user_name']           ="smithka";
        $data['name']                ="Smith";
        $data['email']                ="smithka@demo.com";
        $data['refferal_code']       ="KdhD45d";
        $data['amount']              ="20";
        $data['referral_policy_url'] ="referral policy url";
        $data['contact_url']         ="contact url";
        $data['note']="Note";
        if($file == 'league-won'){
        $content[] = array(
                                'amount'            => 'Amount',
                                'contest_id'        => '123',
                                'contest_unique_id' => 'test123',
                                'contest_name'      => 'Demo Contest',
                                'user_rank'         => 1,
                                'size'              => 4,
                                'prize_pool'        => 400,
                                'entry_fee'         => 100,
                                'team_name'         => 'Team Name',
                                'prize_type'        => 2,
                                'home'              => 'IND',
                                'away'              => 'PAK',
                                'season_scheduled_date'              => '2018-12-28 02:25:15'
                            );

         $content[] = array(
                                'amount'            => 'Amount',
                                'contest_id'        => '123',
                                'contest_unique_id' => 'test123',
                                'contest_name'      => 'Demo Contest',
                                'user_rank'         => 1,
                                'size'              => 4,
                                'prize_pool'        => 400,
                                'entry_fee'         => 100,
                                'team_name'         => 'Team Name',
                                'prize_type'        => 2
                            );

          $content[] = array( 'amount'            => 'Amount',
                                'contest_id'        => '123',
                                'contest_unique_id' => 'test123',
                                'contest_name'      => 'Demo Contest',
                                'user_rank'         => 1,
                                'size'              => 4,
                                'prize_pool'        => 400,
                                'entry_fee'         => 100,
                                'team_name'         => 'Team Name',
                                'prize_type'        => 2
                            );

            $data['content'] = $content;
        }
       
        
        else{
            $data['content']['username']= "smithka";
            $data['content']['name']= "Smith";
            $data['content']['email']="smithka@demo.com";
            $data['content']['friend_name']="Jhon";
            $data['content']['friend_email']="friend@demo.com";
            $data['content']['amount']="20";
            $data['content']['contest_name']="Demo Contest";
            $data['content']['collection_name']="Collection Name";
            $data['content']['match_name']="Match Name";
            $data['content']['contest_unique_id']="contest_unique_id";
            $data['content']['entry_fee']="entry fee";
            $data['content']['amount']="20";
            $data['content']['payment_option']="payment option";
            $data['content']['refferal_code']="rKdhD45d";
            $data['content']['referral_code']="KdhD45d";
            $data['content']['user_name']="smithka";
            $data['content']['size']="4";
            $data['content']['prize_pool']="4";
            $data['content']['message']="Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";
            $data['content']['link']="https://www.google.com/";
            $data['content']['code']="Code";
            $data['content']['note']="Note";
            $data['content']['reason']="Lorem ipsum dolor sit amet, consectetur adipiscing elit.";
            $data['content']['prize_type']="2";
            $data['content']['entry_fee']="100";
            $data['content']['league_code']="dhd34vyv";
            $data['content']['int_version']=0;
        }

        $this->load->view($path,$data); //Done
    } 

    /**
     * A cron method which will be executed daily in morning to notify the users
     */
    public function morning_push_get() {  
        // public function morning_push() {  
            $this->load->model('Cron_model');
            $this->benchmark->mark('code_start');
            $excluding_days = array("Saturday","Sunday");
            $current_day = format_date('today','l');
            if(!in_array($current_day,$excluding_days))
            {
                $this->Cron_model->send_morning_push();
            }
            $this->benchmark->mark('code_end');
            echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
            exit();
    }
    
    /**
     * push that will be sent everyday in evening
     */
    public function evening_push_get(){
    // public function evening_push(){
        $data['from_date'] = $this->get_from_date(1,1);
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $excluding_days = array("Saturday","Sunday");
        $current_day = format_date('today','l');
        if(!in_array($current_day,$excluding_days))
        {
            $this->Cron_model->send_evening_push($data);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }

    /**send push notification 15 min before candel start to all user that have joined candel
     */
    public function candel_Start_notification_get(){
        $data['from_date'] = $this->get_from_date(1,1);
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $excluding_days = array("Saturday","Sunday");
        $current_day = format_date('today','l');
        if(!in_array($current_day,$excluding_days))
        {
            $this->Cron_model->send_candel_Start_notification($data);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }
    //Send accuracy percentage push notification at 4 PM
    public function notify_accuracy_percentage_get(){
        $data['from_date'] = $this->get_from_date(1,1);
        $this->load->model('Cron_model');
        $this->benchmark->mark('code_start');
        $excluding_days = array("Saturday","Sunday");
        $current_day = format_date('today','l');
        if(!in_array($current_day,$excluding_days))
        {
            $this->Cron_model->notify_accuracy_percentage($data);
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');   
        exit();
    }



    /**
     * Used for generate contest report
     * @param 
     * @return string
     */
	public function update_scontest_report_get(){
        $post_data = $_REQUEST;
		
		if(!isset($post_data['from_date']) || ! isset($post_data['to_date']))
		{           
			$post_data['start_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 00:00:00';
			$post_data['end_date_str'] = date('Y-m-d',strtotime(format_date('today'))).' 23:59:59';		
			
			$temp_convert_start = get_timezone(strtotime($post_data['start_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			
			$temp_convert_end = get_timezone(strtotime($post_data['end_date_str']),'Y-m-d H:i:s',$this->app_config['timezone'],1);
			$post_data['from_date'] = $temp_convert_start['date'];
			$post_data['to_date'] = $temp_convert_end['date'];
		}
        $this->benchmark->mark('code_start');
		$this->load->model('Cron_model');
       
		$result  = $this->Cron_model->get_completed_contest_report();

        // echo"<pre>";
        // print_r($result );die;

        $contest_ids = array_column($result["result"], 'contest_id');

        $report_data = array();
        foreach($result["result"] as $match){	
      
        	$input_arr = array('contest_ids'=>$match['contest_id'],'contest_unique_ids'=>$match['contest_unique_id']);
        	$match_entry = $this->Cron_model->get_match_entry_fee_details($input_arr);
			
        	// if(!empty($match_entry)){
        		$tmp_arr = array();
				$tmp_arr['contest_id']         =  $match['contest_id'];
				$tmp_arr['collection_master_id']         =  $match['collection_id'];
				$tmp_arr['contest_unique_id']  =  $match['contest_unique_id'];
				$tmp_arr['game_type']          =  $match['stock_type'];
				$tmp_arr['contest_type']       =  $match['category_id'];;
        		$tmp_arr['sports_id']          =  0;
        		$tmp_arr['league_id']          = 0;
				$tmp_arr['group_id']           =  $match['group_id'];
				$tmp_arr['match_name']         =  $match['contest_name'];
				$tmp_arr['contest_name']       =  $match['contest_name'];
				$tmp_arr['group_name']         =  $match['group_name'];
				$tmp_arr['schedule_date']      =  $match['scheduled_date'];
				$tmp_arr['minimum_size']       =  $match['minimum_size'];
				$tmp_arr['size']               =  $match['size'];
				$tmp_arr['total_user_joined']  =  $match['total_user_joined'];
				$tmp_arr['real_user']          =  $match['total_user_joined'];
				$tmp_arr['bot_user']           =  0;
				$tmp_arr['entry_fee']          =  $match['entry_fee'];			
				$tmp_arr['max_bonus_allowed']  =  $match['max_bonus_allowed'];
				$tmp_arr['site_rake']          =  $match['site_rake'];
				$tmp_arr['currency_type']      =  $match['currency_type'];
				$tmp_arr['entry_real']         =  $match_entry['entry_real'];
				$tmp_arr['entry_bonus']        =  $match_entry['entry_bonus'];
				$tmp_arr['entry_coins']        =  $match_entry['entry_coins'];
				$tmp_arr['promo_entry']        =  $match_entry['promo_discount'];
				// $tmp_arr['total_entry_fee']     = $match_entry['total_entry_fee'];
				$tmp_arr['bot_entry_fee']      =  0;

				$tmp_arr['system_teams']       = 0;
				$tmp_arr['real_teams']         =  $match['total_user_joined'];

				$tmp_arr['prize_pool']         = $match['prize_pool'];
				$tmp_arr['real_prize']         = $match_entry['prize_pool_real'];
				$tmp_arr['coin_prize']         = $match_entry['prize_pool_coins'];
				$tmp_arr['bonous_prize']       = $match_entry['prize_pool_bonus'];		
				$tmp_arr['profit']             = number_format($match_entry['entry_real'] + $match['site_rake']-$tmp_arr['real_prize'],2,".","");				
				$tmp_arr['guaranteed_prize']   =  $match['guaranteed_prize'];
				$tmp_arr['feature_type']       =      NULL;
				$tmp_arr['created_date']       = format_date();
			
        		// $tmp_arr['collection_master_id'] = $match['collection_master_id'];        		
        		// $tmp_arr['site_rake_private'] = $match['private_site_rake'];
        		// $tmp_arr['bots_winning'] = $match_entry['bots_winning'];
        		// $tmp_arr['revenue'] = number_format($match_entry['entry_real'] + $match['private_site_rake'],2,".","");
        		
        		$report_data[] = $tmp_arr;
        	// }
        }
        if(!empty($contest_ids)){
            if(!empty($report_data)){              
                $contest_data_arr = array_chunk($report_data, 999);
                foreach($contest_data_arr as $c_data){
                    $this->Cron_model->replace_into_batch_report(CONTEST_REPORT,$c_data);
                }  
            }
            $this->db->where_in('contest_id',$contest_ids)->update(CONTEST,['report_generated'=>1]); 
        }
        $this->benchmark->mark('code_end');
        echo "Execution Time: ".$this->benchmark->elapsed_time('code_start', 'code_end');
        exit();
	}





    
}