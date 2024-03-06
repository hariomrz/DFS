<?php
class Coins extends Common_Api_Controller {
    var $redeem_coin_action_list = array();

    function __construct() {
        parent::__construct();

        $this->redeem_coin_action_list[1] =array('func' => 'redeem_coin_to_bonus','lang' => $this->lang->line('bonus_cash')) ;
        $this->redeem_coin_action_list[2] = array('func' => 'redeem_coin_to_real','lang' => $this->lang->line('real_cash'));
        $this->redeem_coin_action_list[3] = array('func' => 'redeem_coin_to_gift_card','lang' => $this->lang->line('gift_voucher'));
    }

    /**
     * @method get_daily_streak_coins_post
     * @uses method to get daily streak points
     * @since Nov 2019
     * @param NA
     * @return json array
    */
    function get_daily_streak_coins_post()
    {
        $this->load->model('Coins_model');

        $master_coin_data_key = "master_coin_data";
        $master_coin_data = $this->get_cache_data($master_coin_data_key);

        if(empty($master_coin_data))
        {
            $master_coin_data = $this->Coins_model->get_daily_streak_coins();
            $this->set_cache_data($master_coin_data_key, $master_coin_data, REDIS_2_DAYS);
        }
      
        $this->load->model("auth/Auth_nosql_model");
        $user_last_data = $this->Coins_model->get_last_daily_streak_coin();
        $user_data = array();

        if(!empty($user_last_data))
        {
            //$now = strtotime(format_date()); // or your date as well
            //$entry_date = strtotime($user_last_data['date_added']);

            // $now = date('Y-m-d',strtotime(format_date()));  // or your date as well
            // $entry_date = date('Y-m-d',strtotime($user_last_data['date_added'])); 
            $now =convert_to_client_timezone(format_date(),'Y-m-d');
            $entry_date = convert_to_client_timezone($user_last_data['date_added'],'Y-m-d');

            $days = $this->dateDiffInDays($entry_date,$now);

            if($days <= 1)
            {
                $daily_claimed_data =  json_decode($user_last_data['custom_data'],TRUE);
                if($daily_claimed_data['day_number']> 1)
                {
                    //get last entries
                    $last_entries = $this->Coins_model->get_last_coin_entries($daily_claimed_data['day_number']);
                    if(!empty($last_entries))
                    {
                        $user_data = array_column($last_entries,'points');
                    }
                }
                else{
                    $user_data[] = $user_last_data['points'];
                }
            }

            
        }
       
        $data = array();  
        $daily_streak_coins = json_decode($master_coin_data[0]['daily_coins_data'],TRUE);
       
        //overwrite claimed day with coins
        if(!empty($user_data) && count($user_data) < count($daily_streak_coins))
        {
            //sort($user_data);
            $user_data = array_reverse($user_data);
            foreach($user_data as $key => $used_day)
            {
                $daily_streak_coins[$key] = $used_day;
            }
        }

        $daily_streak_length =count($daily_streak_coins);
        $data['allow_claim'] =1;  
        $data['current_day'] =1;  
        if(!empty($user_last_data))
        {
            $daily_streak_coins_data = json_decode($user_last_data['custom_data'],TRUE);
        
            $new_day = $daily_streak_coins_data['day_number'] +1; 
    
             if($days < 1)
            {
                $data['allow_claim'] =0;
                $data['current_day'] =$daily_streak_coins_data['day_number']; 
            }
            else if($days == 1 && $new_day <= $daily_streak_length )
            {
                //consecutive benefit
                $data['current_day'] =$new_day; 
            }    
            else if($days > 1 || $new_day > $daily_streak_length)
            {
                $data['current_day'] =1; 
            }
            else{
                $data['allow_claim'] =0;
                $data['current_day'] =$daily_streak_coins_data['day_number']; 
            }

        }
       
        $day_number = 1;  
        $data['daily_streak_coins'] = array();  
        foreach($daily_streak_coins as $value)
        {
            $data['daily_streak_coins'][] = array('day_number' => $day_number,"coins" => $value                                 );
            $day_number++;
        }

        if(empty($data['daily_streak_coins']))
        {
            $data['allow_claim'] =0;
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data'] = $data;
        $this->api_response(); 
    }

    function dateDiffInDays($date1, $date2)  
    { 
        // Calulating the difference in timestamps 
        $diff = strtotime($date2) - strtotime($date1); 
        
        // 1 day = 24 hours 
        // 24 * 60 * 60 = 86400 seconds 
        return abs(round($diff / 86400)); 
    } 

    /**
     * @method claim_coins_post
     * @uses method to claim daily coins
     * @since Nov 2019
     * @param NA
     * @return json array
    */
    function claim_coins_post()
    {
        $this->load->model('Coins_model');

        $current_date = format_date();

        $master_coin_data_key = "master_coin_data";
        $master_coin_data = $this->get_cache_data($master_coin_data_key);
        if(empty($master_coin_data))
        {
            $master_coin_data = $this->Coins_model->get_daily_streak_coins();
            $this->set_cache_data($master_coin_data_key, $master_coin_data, REDIS_2_DAYS);
        }
        $daily_streak_coins = json_decode($master_coin_data[0]['daily_coins_data'],TRUE);
        $record = $this->Coins_model->get_last_daily_streak_coin();
        
        $daily_streak_length =count($daily_streak_coins);
        if(!empty($record))
        {
            $daily_streak_coins_data = json_decode($record['custom_data'],TRUE);
            
           // $now = date('Y-m-d',strtotime(format_date()));  // or your date as well
            // $entry_date = date('Y-m-d',strtotime($record['date_added']));
            $now =convert_to_client_timezone(format_date(),'Y-m-d');
            $entry_date = convert_to_client_timezone($record['date_added'],'Y-m-d');

             $days = $this->dateDiffInDays($entry_date,$now);

           
            //$days =round($datediff / (60 * 60 * 24));
            $new_day = $daily_streak_coins_data['day_number'] +1;
            $current_day_coins = 0;
            if($days == 1 && $new_day <= $daily_streak_length )
            {
                //consecutive benefit
                 //add to queue
                $this->rabbit_mq_push(array('user_id'=>$this->user_id,
                'new_day' => $new_day,
                "daily_streak_coins"  =>  $daily_streak_coins[$daily_streak_coins_data['day_number']],
                'daily_streak_length' => $daily_streak_length,
                'current_date' => $current_date
                    ),'coins');
      
                //$order_id= $this->new_record_entry($new_day,$daily_streak_coins[$daily_streak_coins_data['day_number']],$daily_streak_length); 
                $current_day_coins = $daily_streak_coins[$daily_streak_coins_data['day_number']];
            }
            else if($days >= 1 )
            {
                //reset from day number 1
                   //add to queue
                   $this->rabbit_mq_push(array('user_id'=>$this->user_id,
                   'new_day' => 1,
                   "daily_streak_coins"  =>  $daily_streak_coins[0],
                   'daily_streak_length' => $daily_streak_length,
                   'current_date' => $current_date
                       ),'coins');
                //$order_id=$this->new_record_entry(1,$daily_streak_coins[0],$daily_streak_length);
                $current_day_coins = $daily_streak_coins[0];
            }
            else
            {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
                $this->api_response_arry['message'] = $this->lang->line('coins_aleady_claimed_msg');
                $this->api_response(); 
            }
        }
        else{

             //add to queue
             $this->rabbit_mq_push(array('user_id'=>$this->user_id,
             'new_day' => 1,
             "daily_streak_coins"  =>  $daily_streak_coins[0],
             'daily_streak_length' => $daily_streak_length,
             'current_date' => $current_date
                 ),'coins');    
            //$order_id = $this->new_record_entry(1,$daily_streak_coins[0],$daily_streak_length);
            $current_day_coins = $daily_streak_coins[0];

            
        }

        $this->load->helper('queue_helper');
        $coin_data = array('oprator' => 'add', 'user_id' => $this->user_id, 'total_coins' => $current_day_coins, 'bonus_date' => format_date("today", "Y-m-d"));
        add_data_in_queue($coin_data, 'user_coins');

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = str_replace('#coins#',$current_day_coins, $this->lang->line('coin_claim_succ_msg'));
        $this->api_response(); 
  
    }

     /**
     * @method new_record_entry
     * @uses method to make a new entry for daily streak coins
     * @since Nov 2019
     * @param Int $day_number day for which entry has to be done
     * @param Int $coins amount of coins has to be claimed
     * @param Int $daily_streak_length number of days for daily streak coins
     * @return json array
    */
    function new_record_entry($day_number=1,$coins=0,$daily_streak_length=3)
    {
        $this->load->model("finance/Finance_model");
        $deposit_data_friend = array(
            "user_id" => $this->user_id,
            "amount" => $coins,
            "source" => 144, //daily_streak coins
            "source_id" => 0,
            "plateform" => 1,
            "cash_type" => 2, // for coins 
            "link" => FRONT_APP_PATH . 'my-wallet',
            "custom_data"=> json_encode(array(
                'day_number' => $day_number,
                'daily_streak_length' =>$daily_streak_length
            ))
        );

       return $this->Finance_model->deposit_fund($deposit_data_friend);
    }

     /**
     * @method earn_coins_post
     * @uses method to data for  how to earn coins section
     * @since Nov 2019
     * @return json array
    */
    function get_earn_coins_list_post()
    {

        $how_to_earn_coins_key = "how_to_earn_coins";
        $how_to_earn_coins = array();//$this->get_cache_data($how_to_earn_coins_key);

        if(!empty($how_to_earn_coins))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']['earn_coins'] = $how_to_earn_coins;
            $this->api_response(); 
        }

        $this->load->model("auth/Auth_nosql_model");
        $result =$this->Auth_nosql_model->select_nosql('earn_coins',array('status'=>1));
        
      
        $this->load->model("Coins_model");
        $master_data = $this->Coins_model->get_all_module_settings_with_status();
       
        $daily_benifit['daily_streak_bonus'] = array();
        $referral_data = $this->Coins_model->get_single_row('coin_amount',AFFILIATE_MASTER,array('affiliate_type' => 1));
        $enabled_modules = array();
        foreach($master_data as $row)
        {
            $enabled_modules[$row['submodule_key']] = $row['status'];              
        }
       
        $allow_prediction_system =  isset($this->app_config['allow_prediction_system'])?(int)$this->app_config['allow_prediction_system']['key_value']:0;
        
        foreach($result as $module_key =>  $module)
        {
            if(isset($module['module_key']) && $module['module_key']=='refer-a-friend')
            {
                foreach($module as $key =>  $row)
                {
                    if(is_object($module[$key]) && isset($module[$key]->description))
                    {
                        $module[$key]->description = str_replace("#coins#",$referral_data['coin_amount'],$module[$key]->description);

                    }
                }

              }
              
              if(isset($module['module_key']) && $module['module_key']=='daily_streak_bonus')
              {
                $daily_benifit['daily_streak_bonus'] = $module;
              }

              
            if(isset($module['module_key']) && $module['module_key']=='prediction' && !$allow_prediction_system)
            {
                unset($result[$module_key]);
            }

            if(isset($enabled_modules[$module['module_key']]))
            {
                if(!$enabled_modules[$module['module_key']])
                {
                    unset($result[$module_key]);
                }

            }
            
        }
       
        $result = array_values($result);
        //print_r($result);
      
        //get user day and coins
        $user_current_status = $this->get_daily_streak_status_post(1);

        $daily_benifit['daily_streak_bonus'] =array_merge($daily_benifit['daily_streak_bonus'],$user_current_status);
        //$this->set_cache_data($how_to_earn_coins_key, $result, REDIS_2_DAYS);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data']['earn_coins'] = $result;

        $this->load->model('quiz/Quiz_model');
        $user_id = $this->user_id;

        $quiz_dtl =$this->Quiz_model->get_quiz($user_id);
        $quiz_coins = explode(',',$quiz_dtl['qq_coins']);

        rsort($quiz_coins);
        $max_coins = array_slice($quiz_coins, 0, $quiz_dtl['visible_questions']); 
        $quiz_dtl['qq_coins'] = array_sum($max_coins);
        $this->api_response_arry['data']['quiz_dtl'] = $quiz_dtl;

        $this->api_response_arry['data']['daily_streak_bonus'] = $daily_benifit['daily_streak_bonus'];

        $this->load->model('spinthewheel/Spinthewheel_model');
        $get_slices_list = $this->Spinthewheel_model->get_slices_list(0, 10);

        $user_claimed = $this->Spinthewheel_model->spin_claimed($user_id);
        if(empty($user_claimed)){
          $user_claimed = 0;
        } else {
          $user_claimed = 1;
        }

        $setting_array = array();
        $this->api_response_arry['data']['spin_wheel'] = array(); 
        if(!empty($get_slices_list)) {
            foreach ($get_slices_list as $key => $value) {               
              if(isset($value['value']) && $value['value']!="") {
                if(strlen($value['value'])>10){
                  $arr = explode(' ', $value['value'], 3);
                  $wheel_data[$key]['value'] = intval($arr[0]).' '.$arr[1].'^'.$arr[2];
                } else {
                  $wheel_data[$key]['value'] = (int)$value['value'];
                }
              }

              $wheel_data[$key]['win']          = $value['win']==1 ? true : false;
              $wheel_data[$key]['probability']  = $value['probability'];
              $wheel_data[$key]['resultText']   = $value['resultText'];
              $wheel_data[$key]['type']         = $value['type'];
              $wheel_data[$key]['userData']     = array("spinthewheel_id"=>$value['spinthewheel_id']);
            }

            $setting_array['colorArray'] = array("#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5","#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5", "#41B6FE", "#A95BB5");

            $setting_array['segmentValuesArray'] = $wheel_data;

            $setting_array['svgWidth'] = 1024;
            $setting_array['svgHeight'] = 1024;
            $setting_array['wheelStrokeColor'] = "#FDE06E";
            $setting_array['wheelStrokeWidth'] = 18;
            $setting_array['wheelSize'] = 900;
            $setting_array['wheelTextOffsetY'] = 110;
            $setting_array['wheelTextColor'] = "#EDEDED";
            $setting_array['wheelTextSize'] = "2.3em";
            $setting_array['wheelImageOffsetY'] = 40;
            $setting_array['wheelImageSize'] = 50;
            $setting_array['wheelImageSize'] = 50;
            $setting_array['centerCircleSize'] = 360;
            $setting_array['centerCircleStrokeColor'] = "#F1DC15";
            $setting_array['centerCircleStrokeWidth'] = 12;
            $setting_array['centerCircleFillColor'] = "#EDEDED";
            $setting_array['centerCircleImageUrl'] = "./spin2wheel/center_wheel_logo.png";
            $setting_array['centerCircleImageWidth'] = 400;
            $setting_array['centerCircleImageHeight'] = 400;
            $setting_array['segmentStrokeColor'] = "#E2E2E2";
            $setting_array['segmentStrokeWidth'] = 4;
            $setting_array['centerX'] = 512;
            $setting_array['centerY'] = 512;
            $setting_array['hasShadows'] = false;
            $setting_array['numSpins'] = 1;
            $setting_array['spinDestinationArray'] = array();
            $setting_array['minSpinDuration'] = 3;
            $setting_array['gameOverText'] = "I HOPE YOU ENJOYED SPIN WHEEL. :)";
            $setting_array['invalidSpinText'] = "INVALID SPIN. PLEASE SPIN AGAIN.";
            $setting_array['introText'] = "YOU HAVE TO<br>SPIN IT <span style='color:#F282A9;'>1</span> WIN IT!";
            $setting_array['hasSound'] = true;
            $setting_array['gameId'] = "9a0232ec06bc431114e2a7f3aea03bbe2164f1aa";
            $setting_array['clickToSpin'] = true;
            $setting_array['spinDirection'] = "cw";

            $max_coins = $this->Spinthewheel_model->get_max_coins();

           
            $this->api_response_arry['data']['spin_wheel'] = array(
              'wheel_data' => $setting_array,
              "claimed"=>$user_claimed,
              "max_coins"=>(int)$max_coins
            );

            
        }

         //get download app coins clamied or not
         $download_app_claim= $this->Coins_model->get_download_app_coins_status();
         $download_app_claim_status= 0;

         if(!empty($download_app_claim))
         {
            $download_app_claim_status= 1;
         }
       
        $install_date = $this->Coins_model->get_app_install_date();
         $app_downloaded = 1;
        if(empty($install_date['install_date']))
        {
          $app_downloaded=0;
        }
        $this->api_response_arry['data']['download_app_claim_status'] = $download_app_claim_status;
        $this->api_response_arry['data']['download_app_coins'] = DOWNLOAD_APP_COINS;  
        $this->api_response_arry['data']['app_downloaded'] = $app_downloaded;  
        $this->api_response_arry['data']['feedback_coins'] = $this->get_feedback_question_coins();  

        $this->api_response_arry['data']['refer_coins'] = $this->Coins_model->get_affiliate_item_value(1,'coin_amount');
        $this->api_response(); 
    }

    /**
     * @method get_reward_list_post
     * @uses method to get reward list
     * @since Nov 2019
     * @return json array
    */
    public function get_reward_list_post()
    {
        $coin_rewards_key = "coin_rewards";
        $result = $this->get_cache_data($coin_rewards_key);

        if(!empty($result))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
            $this->api_response_arry['data']['rewards'] = $result;
            $this->api_response(); 
        }

        $this->load->model("auth/Auth_nosql_model");
        $result =$this->Auth_nosql_model->select_nosql('coin_rewards',array('status'=>1),NULL,NULL,'redeem_coins','ASC');

        $this->set_cache_data($coin_rewards_key, $result, REDIS_2_DAYS);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['data']['rewards'] = $result;
        $this->api_response(); 
    }

    /**
     * @method redeem_reward_post
     * @uses method to redeem reward
     * @since Nov 2019
     * @param Array $_POST coin reward id
     * @return json array
    */
    public function redeem_reward_post()
    {
        
        $post_data = $this->post();      
        $this->form_validation->set_rules('coin_reward_id', 'coin reward id', 'trim|required');
       
        if ($this->form_validation->run() == FALSE) {
            $this->send_validation_errors();
        }

        $this->load->model("auth/Auth_nosql_model");
        $result =$this->Auth_nosql_model->select_one_nosql('coin_rewards',array('coin_reward_id'=>new MongoDB\BSON\ObjectId($post_data['coin_reward_id'])));

        $this->load->model('finance/Finance_model');
        if (!$user_balance) {
            $user_balance = $this->Finance_model->get_user_balance($this->user_id);
        }

        if($user_balance['point_balance'] < $result['redeem_coins'] )
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('insufficent_coins');
            $this->api_response();  
        }

        if(!isset($this->redeem_coin_action_list[$result['type']]))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line('not_a_redeem_type');
            $this->api_response();  
        }

        $withdraw_coin = array(
            "user_id" => $this->user_id,
            "amount" => $result['redeem_coins'],
            "source" => 147, // bonus on redeem coins
            "source_id" => 0,
            "plateform" => 1,
            "status" => 1,
            "cash_type" => 1, // for bonus 
            "link" => FRONT_APP_PATH . 'my-wallet',
            "event" => $this->redeem_coin_action_list[$result['type']]['lang']
        );

       $withdraw_order_id = $this->Finance_model->withdraw_coins($withdraw_coin);


        $function_name = $this->redeem_coin_action_list[$result['type']]['func'];

        $order_id = $this->$function_name($result);

        $user_balance_cache_key = 'user_balance_' . $this->user_id;
        $this->delete_cache_data($user_balance_cache_key);
        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = $this->lang->line('coins_claimed_succ_msg');
        $this->api_response();  
      
    }

    /**
     * @method redeem_coin_to_bonus
     * @uses method to redeem coin to bonus
     * @since Nov 2019
     * @param Array $coin_reward coin reward record
     * @return Int order id
    */
    public function redeem_coin_to_bonus($coin_reward)
    {
        $this->load->model("finance/Finance_model");
        $deposit_data_friend = array(
            "user_id" => $this->user_id,
            "amount" => $coin_reward['value'],
            "source" => 145, // bonus on redeem coins
            "source_id" => 0,
            "plateform" => 1,
            "cash_type" => 1, // for bonus 
            "link" => FRONT_APP_PATH . 'my-wallet',
            "event" => $this->lang->line('bonus_cash'),
            "reward_detail" => $coin_reward,
            "reward_text" => $coin_reward['value'].' '.$this->lang->line('bonus_cash'),
            "coins" => $coin_reward['redeem_coins']
        );

       $order_id = $this->Finance_model->deposit_fund($deposit_data_friend);

       $this->add_coin_reward_history(1,$coin_reward['coin_reward_id']);

    return $order_id;
    }


    /**
     * @method redeem_coin_to_real
     * @uses method to redeem coin to real
     * @since Nov 2019
     * @param Array $coin_reward coin reward record
     * @return Int order id
    */
    public function redeem_coin_to_real($coin_reward)
    {
        $this->load->model("finance/Finance_model");
        $deposit_data_friend = array(
            "user_id" => $this->user_id,
            "amount" => $coin_reward['value'],
            "source" => 146, // bonus on redeem coins
            "source_id" => 0,
            "plateform" => 1,
            "cash_type" => 0, // for Real 
            "link" => FRONT_APP_PATH . 'my-wallet',
            "event" => $this->lang->line('real_cash'),
            "reward_detail" => $coin_reward,
            "reward_text" => $coin_reward['value'].' '.$this->lang->line('real_cash'),
            "coins" => $coin_reward['redeem_coins']
        );

       $order_id = $this->Finance_model->deposit_fund($deposit_data_friend);
       $this->add_coin_reward_history(1,$coin_reward['coin_reward_id']);
    }

     /**
     * @method redeem_coin_to_gift_card
     * @uses method to redeem coin to real
     * @since Nov 2019
     * @param Array $coin_reward coin reward record
     * @return Int order id
    */
    public function redeem_coin_to_gift_card($coin_reward)
    {
        $this->load->model('notification/Notify_nosql_model');
        $today = format_date();
        $tmp = array();
        $input = array();
        $input["user_name"] = $this->user_name;
        $input["source"] = 147;
        $input["amount"] = $coin_reward['value'];
        $input["event"] = $this->lang->line('gift_voucher');

        $tmp["subject"] = '';
        $tmp["source_id"] = 0;
        $tmp["notification_destination"] = (!empty($tmp["notification_destination"])) ? $tmp["notification_destination"] : 7; //  Web, Push, Email

        $tmp["user_id"] = $this->user_id;
        $tmp["to"] = $this->email;
        $tmp["user_name"] = $this->user_name;
        $tmp["added_date"] = $today;
        $tmp["modified_date"] = $today;
        $input['reward_detail'] = $coin_reward;
        $input["reward_text"] = $coin_reward['detail'].' '.$this->lang->line('reward_worth').' '.CURRENCY_CODE.' '.$coin_reward['value'];
        $input["coins"] = $coin_reward['redeem_coins'];
        $tmp["content"] = json_encode($input);
        $this->load->model("finance/Finance_model");
        $notify_data = $this->Finance_model->notify_type_by_source($input["source"]);

        $tmp['notification_type'] = $notify_data['notification_type'];
        $tmp['subject'] = $notify_data['subject'];
        $tmp['notification_destination'] = $notify_data['notification_destination'];

        $this->Notify_nosql_model->send_notification($tmp);

       $this->add_coin_reward_history(0,$coin_reward['coin_reward_id']);
       
    }

    /**
     * @method redeem_coin_to_gift_card
     * @uses method to redeem coin to real
     * @since Nov 2019
     * @param Int status 0 or 1 for history 
     * @param String $coin_reward_id
     * @return Boolean 
    */
    private function add_coin_reward_history($status,$coin_reward_id)
    {
        $coin_reward_history_data = array(
            'coin_reward_history_id' => new MongoDB\BSON\ObjectId(),
            'user_id' => $this->user_id,
            'coin_reward_id'=> $coin_reward_id,
            'status' => $status,
            'added_date' => new MongoDB\BSON\UTCDateTime(strtotime(format_date()) * 1000),
            'update_date' => new MongoDB\BSON\UTCDateTime(strtotime(format_date()) * 1000)
        );
    
        $this->Auth_nosql_model->insert_nosql('coin_reward_history',$coin_reward_history_data);
    }

    private function check_module_settings($module_key = '')
    {
        $this->load->model("Coins_model");
        $master_data = $this->Coins_model->get_all_module_settings_with_status();
        $master_coin_setting = array_column($master_data,'status','submodule_key');

        if(empty($master_coin_setting[$module_key]))
        {
            $this->api_response_arry['global_error'] = "Module not activated.";
            $this->api_response();  
        }
        
    }

    /**
     * @method get_feedback_question_list
     * @uses method to get feedback question list
     * @since Dec 2019
     * @param Array limit, offset 
     * @param String $coin_reward_id
     * @return Boolean 
    */
    function get_feedback_question_list_post()
    {
        $this->check_module_settings('feedback');
        $limit = 30;
        $offset = 0;

        $count = 0;
		if(isset($post['limit']))
		{
			$limit = $post['limit'];
		}

        $page = 0;

        if(!empty($post['offset']))
        {
            $offset = $post['offset'];
        }

        $this->load->model('auth/Auth_nosql_model');
         //get questions
        $question_cond = array('status' =>1 );
        $count = $this->Auth_nosql_model->count(COLL_FEEDBACK_QUESTIONS,$question_cond); 
        $questions= $this->Auth_nosql_model->select_nosql(COLL_FEEDBACK_QUESTIONS,$question_cond,$limit,$offset);

        $this->api_response_arry['data']['questions'] = $questions;
        $this->api_response_arry['data']['total'] 	= $count;
        $is_load_more = true;
        if(count($questions) < $limit)
        {
            $is_load_more = false;
        }

        $this->api_response_arry['data']['is_load_more'] = $is_load_more;
      
        $this->api_response();  
    }

    private function get_feedback_question_coins()
    {
        $this->check_module_settings('feedback');
        $this->load->model('auth/Auth_nosql_model');
         //get questions
       
        $result = $this->Auth_nosql_model->select_nosql(COLL_FEEDBACK_QUESTIONS,array('status' => 1)); 

        $coins = 0;
        if(!empty($result))
        {
          $coins = max(array_column($result,'coins')) ;
        }
      
        return $coins;
       
    }

     /**
     * @method save_feedback
     * @uses method to save feedback question list
     * @since Dec 2019
     * @param Array limit, offset 
     * @param String $coin_reward_id
     * @return Boolean 
    */

    function save_feedback_post()
    {
        $this->check_module_settings('feedback');
        $this->form_validation->set_rules('answer', 'Answer', 'trim|required');
        $this->form_validation->set_rules('feedback_question_id', 'feedback question id', 'trim|required');
        
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
        }   

        $post = $this->input->post();
       
        $data = array();
        $data['feedback_question_answer_id'] = new_mongo_id();
        $data['answer'] = $post['answer'];
        $data['feedback_question_id'] = new_mongo_id($post['feedback_question_id']);
        $data['user_id'] =$this->user_id;
        $data['status'] =0;
        $data['rating'] = 0;
        $data['added_date'] = convert_normal_to_mongo(format_date());
        $data['update_date'] = convert_normal_to_mongo(format_date());
        $this->load->model('auth/Auth_nosql_model');
        $this->Auth_nosql_model->insert_nosql(COLL_FEEDBACK_QUESTION_ANSWERS,$data);

        $this->api_response_arry['response_code'] 	= rest_controller::HTTP_OK;
        $this->api_response_arry['message']  	= $this->lang->line('user_feedback_added_success_msg');
        $this->api_response();
    }

    function sync_earn_coins_post() {
        $this->load->model('coins/Coins_model');
        $this->Coins_model->sync_earn_coins();

        $earn_coins =array (
            
            array (
              'module_key' => 'refer-a-friend',
              'en' => 
              array (
                'label' => 'REFER A FRIEND',
                'description' => 'Earn 100 coins for every friend\'s sign up',
                'button_text' => 'REWARD',
              ),
              'hi' => 
              array (
                'label' => 'मित्र को आमंत्रित करें',
                'description' => 'हर दोस्त के साइन अप के लिए 100 सिक्के कमाएँ',
                'button_text' => 'संदर्भ लें',
              ),
              'guj' => 
              array (
                'label' => 'મિત્રને નો સંદર્ભ લો',
                'description' => 'દરેક મિત્ર સાઇન અપ માટે 100 સિક્કા કમાઓ',
                'button_text' => 'સંદર્ભ',
              ),
              'tam' => 
              array (
                'label' => 'நண்பரைப் பரிந்துரைக்கவும்',
                'description' => 'ஒவ்வொரு நண்பரின் அடையாளம் வரை 100 நாணயங்கள் சம்பாதிக்க',
                'button_text' => 'பார்க்கவும்',
              ),
              'th' => 
              array (
                'label' => 'แนะนำเพื่อน',
                'description' => 'รับ 100 เหรียญสำหรับการสมัครของเพื่อนทุกคน',
                'button_text' => 'อ้างอิง',
              ),
              'ru' =>
              array (
                'label' => 'ПОСМОТРЕТЬ ДРУГУ',
                'description' => 'Зарабатывайте 100 монет за регистрацию каждого друга',
                'button_text' => 'ССЫЛКА',
              ),
              'id'=>
              array (
                'label' => 'REFER A FRIEND',
                'description' => 'Dapatkan 100 koin untuk setiap teman yang mendaftar',
                'button_text' => 'REFER',
              ),
              'tl'=>
              array (
                'label' => 'REFER A FRIEND',
                'description' => 'Kumita ng 100 barya para sa bawat pag-sign up ng kaibigan',
                'button_text' => 'REFER',
              ),
              'zh'=>
              array(
                'label' => '介绍个朋友',
                'description' => '每个朋友的签约可赚取100个硬币',
                'button_text' => '参考',
              ),
              'kn' =>
               array (
                 'label' => 'ಸ್ನೇಹಿತನನ್ನು ಉಲ್ಲೇಖಿಸಿ',
                 'description' => 'ಪ್ರತಿ ಸ್ನೇಹಿತನ ಸೈನ್ ಅಪ್‌ಗೆ 100 ನಾಣ್ಯಗಳನ್ನು ಸಂಪಾದಿಸಿ',
                 'button_text' => 'ನೋಡಿ',
               ),
               'es' =>
               array (
                 'label' => 'recomendar a un amigo',
                 'description' => 'Gana 100 monedas por registro de amigo',
                 'button_text' => 'Ver',
               ),
              'image_url' => 'refer-img1.png',
              'status' => 1,
              'url' => '',
            ),
            
           
            array (
              'module_key' => 'daily_streak_bonus',
              'en' => 
              array (
                'label' => 'DAILY CHECK-IN BONUS',
                'description' => 'Earn coins daily by logging in',
                'button_text' => 'Learn more',
              ),
              'hi' => 
              array (
                'label' => 'रोज चेक-इन बोनस',
                'description' => 'में दैनिक सिक्के कमाएँ प्रवेश द्वारा',
                'button_text' => 'और अधिक जानें',
              ),
              'guj' => 
              array (
                'label' => 'દૈનિક ચેક-ઇન બોનસ',
                'description' => 'દૈનિક સિક્કા કમાઓ લોગીંગ દ્વારા',
                'button_text' => 'વધુ શીખો',
              ),
              'tam' => 
              array (
                'label' => 'தினசரி சோதனை போனஸ்',
                'description' => 'மேலும் அறிக',
                'button_text' => 'மரம்வெட்டுதல் மூலம் தினசரி நாணயங்கள் சம்பாதிக்க',
              ),
              'th' => 
              array (
                'label' => 'โบนัสเช็คอินทุกวัน',
                'description' => 'รับเหรียญทุกวันโดยเข้าสู่ระบบ',
                'button_text' => 'เรียนรู้เพิ่มเติม',
              ),
              'ru' => 
              array (
                'label' => 'БОНУС ЕЖЕДНЕВНОЙ РЕГИСТРАЦИИ',
                'description' => 'Зарабатывайте монеты ежедневно, войдя в систему',
                'button_text' => 'Узнать больше',
              ),
              'id'=>
              array (
                'label' => 'BONUS CHECK-IN HARIAN',
                'description' => 'Dapatkan koin setiap hari dengan login',
                'button_text' => 'Pelajari lebih lanjut',
              ),
              'tl'=>
              array (
                'label' => 'DAILY CHECK-IN BONUS',
                'description' => 'Kumita ng mga barya araw-araw sa pamamagitan ng pag-log in',
                'button_text' => 'Matuto nang higit pa',
              ),
              'zh'=>
              array(
                'label' => '每日入住奖励',
                'description' => '每天通过登录赚钱',
                'button_text' => '了解更多',
              ),
              'kn' =>
               array (
                 'label' => 'ದೈನಂದಿನ ಚೆಕ್-ಇನ್ ಬೋನಸ್',
                 'description' => 'ಲಾಗ್ ಇನ್ ಮಾಡುವ ಮೂಲಕ ಪ್ರತಿದಿನ ನಾಣ್ಯಗಳನ್ನು ಸಂಪಾದಿಸಿ',
                 'button_text' => 'ಇನ್ನಷ್ಟು ತಿಳಿಯಿರಿ',
               ),
               'es' => 
                array (
                  'label' => 'BONO DE CHECK-IN DIARIO',
                  'description' => 'Gana monedas diariamente iniciando sesión',
                  'button_text' => 'Más información',
                ),
              'image_url' => 'checkins-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
            
            array (
              'module_key' => 'prediction',
              'en' => 
              array (
                'label' => 'PLAY PREDICTION',
                'description' => 'Predict and earn coins',
                'button_text' => 'PREDICT',
              ),
              'hi' => 
              array (
                'label' => 'खेलने पूर्वानुमान',
                'description' => 'भविष्यवाणी और सिक्कों कमाने',
                'button_text' => 'भविष्यवाणी',
              ),
              'guj' => 
              array (
                'label' => 'નાટક આગાહી',
                'description' => 'અનુમાન અને સિક્કા કમાઇ',
                'button_text' => 'આગાહી',
              ),
              'tam' => 
              array (
                'label' => 'விளையாடு கணிப்பை',
                'description' => 'கணிக்கவும் மற்றும் நாணயங்கள் சம்பாதிக்க',
                'button_text' => 'கணிக்கவும்',
              ),
              'th' => 
              array (
                'label' => 'เล่นคำทำนาย',
                'description' => 'ทำนายและรับเหรียญ',
                'button_text' => 'ทำนาย',
              ),
              'ru' => 
              array (
                'label' => 'ПРОГНОЗ ИГРЫ',
                'description' => 'Прогнозируй и зарабатывай монеты',
                'button_text' => 'ПРОГНОЗ',
              ),
              'id'=>
              array (
                'label' => 'PREDIKSI MAIN',
                'description' => 'Prediksi dan dapatkan koin',
                'button_text' => 'PREDICT',
              ),
              'tl'=>
              array (
                'label' => 'PLAY PREDICTION',
                'description' => 'Predict and earn coins',
                'button_text' => 'PREDICT',
              ),
              'zh'=>
              array (
                'label' => '播放预测',
                'description' => '预测并赚取金币',
                'button_text' => '预测',
              ),
              'kn' =>
               array (
                 'label' => 'ಪ್ಲೇ ಪ್ರಿಡಿಕ್ಷನ್',
                 'description' => 'ನಾಣ್ಯಗಳನ್ನು ict ಹಿಸಿ ಮತ್ತು ಸಂಪಾದಿಸಿ',
                 'button_text' => 'ಭವಿಷ್ಯ',
               ),
               'es' => 
                array (
                  'label' => 'PREDICCIÓN DEL JUEGO',
                  'description' => 'Predecir y ganar monedas',
                  'button_text' => 'PREDECIR',
                ),
              'image_url' => 'prediction-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
            
            array (
              'module_key' => 'promotions',
              'en' => 
              array (
                'label' => 'PROMOTIONS',
                'description' => 'Ran out of coins? Watch a video and refill your coin wallet',
                'button_text' => 'WATCH',
              ),
              'hi' => 
              array (
                'label' => 'प्रचार',
                'description' => 'कम सिक्के? एक वीडियो देखें और अपने सिक्का बटुआ फिर से भरना',
                'button_text' => 'देखें',
              ),
              'guj' => 
              array (
                'label' => 'બઢતી',
                'description' => 'ઓછી સિક્કા? વિડિઓ જુઓ અને તમારા સિક્કો વોલેટ રિફિલ',
                'button_text' => 'જો',
              ),
              'tam' => 
              array (
                'label' => 'விளம்பரங்கள்',
                'description' => 'ரான் நாணயங்கள் வெளியே வீடியோவைக் உங்கள் நாணயம் பணப்பையை நிரப்பித் தர?',
                'button_text' => 'கடிகாரம்',
              ),
              'th' => 
              array (
                'label' => 'โปรโมชั่น',
                'description' => 'หมดเหรียญ? ดูวิดีโอและเติมกระเป๋าเงินเหรียญของคุณ',
                'button_text' => 'ดู',
              ),
              'ru' =>
              array (
                'label' => 'АКЦИИ',
                'description' => 'Закончились монеты? Посмотрите видео и пополните свой кошелек для монет »',
                'button_text' => 'СМОТРЕТЬ',
              ),
              'id'=>
              array (
                'label' => 'PROMOSI',
                'description' => 'Kehabisan koin? Tonton video dan isi ulang dompet koin Anda ',
                'button_text' => 'WATCH',
              ),
              'tl'=>
              array (
                'label' => 'PROMOTIONS',
                'description' => 'Ran out of coins? Watch a video and refill your coin wallet',
                'button_text' => 'WATCH',
              ),
              'zh'=>
              array (
                'label' => '促销活动',
                'description' => '没钱了吗？ 观看视频并为硬币钱包充值',
                'button_text' => '手表',
              ),
              'kn' =>
               array (
                 'label' => 'ಪ್ರಚಾರಗಳು',
                 'description' => 'ನಾಣ್ಯಗಳಿಂದ ಹೊರಬಂದಿದೆಯೇ? ವೀಡಿಯೊ ನೋಡಿ ಮತ್ತು ನಿಮ್ಮ ನಾಣ್ಯ ಕೈಚೀಲವನ್ನು ಮತ್ತೆ ತುಂಬಿಸಿ ',
                 'button_text' => 'ವೀಕ್ಷಿಸಿ',
               ),
               'en' => 
                array (
                  'label' => 'PROMOCIONES',
                  'description' => '¿Te quedaste sin monedas? Mira un video y recarga tu monedero',
                  'button_text' => 'VER',
                ),
              'image_url' => 'promotion-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
           
            array (
              'module_key' => 'feedback',
              'en' => 
              array (
                'label' => 'Feedback',
                'description' => 'Genuine feedback will get coins after admin approval',
                'button_text' => 'Write Us',
              ),
              'hi' => 
              array (
                'label' => 'प्रतिपुष्टि',
                'description' => 'वास्तविक प्रतिक्रिया व्यवस्थापक अनुमोदन के बाद सिक्के मिल जाएगा',
                'button_text' => 'हमें लिखें',
              ),
              'guj' => 
              array (
                'label' => 'પ્રતિસાદ',
                'description' => 'જેન્યુઇન પ્રતિસાદ એડમિન મંજૂરી પછી સિક્કા મળશે',
                'button_text' => 'અમને લખો',
              ),
              'tam' => 
              array (
                'label' => 'கருத்து',
                'description' => 'உண்மையான கருத்துக்களை நிர்வாக ஒப்புதலுக்கு பின்னர் நாணயங்கள் கிடைக்கும்',
                'button_text' => 'எங்களை எழுது',
              ),
              'th' => 
              array (
                'label' => 'ข้อเสนอแนะ',
                'description' => 'ข้อเสนอแนะที่แท้จริงจะได้รับเหรียญหลังจากการอนุมัติของผู้ดูแลระบบ',
                'button_text' => 'เขียนถึงเรา',
              ),
              'ru' =>
              array (
                'label' => 'Отзыв',
                'description' => 'За подлинный отзыв монеты будут отправлены после одобрения администратором',
                'button_text' => 'Напишите нам',
              ),
              'id'=>
              array (
                'label' => 'Umpan Balik',
                'description' => 'Umpan balik asli akan mendapatkan koin setelah persetujuan admin',
                'button_text' => 'Tulis Kami',
              ),
              'tl'=>
              array (
                'label' => 'Feedback',
                'description' => 'Genuine feedback will get coins after admin approval',
                'button_text' => 'Write Us',
              ),
              'zh'=>
              array (
                'label' => '回馈',
                'description' => '真正的反馈将在管理员批准后获得硬币',
                'button_text' => '写信给我们',
              ),
              'kn' =>
               array (
                 'label' => 'ಪ್ರತಿಕ್ರಿಯೆ',
                 'description' => 'ನಿರ್ವಾಹಕ ಅನುಮೋದನೆಯ ನಂತರ ನಿಜವಾದ ಪ್ರತಿಕ್ರಿಯೆಯು ನಾಣ್ಯಗಳನ್ನು ಪಡೆಯುತ್ತದೆ',
                 'button_text' => 'ನಮ್ಮನ್ನು ಬರೆಯಿರಿ',
               ),
               'en' => 
              array (
                'label' => 'Comentarios',
                'description' => 'Los comentarios genuinos obtendrán monedas después de la aprobación del administrador',
                'button_text' => 'Escríbanos',
              ),
              'image_url' => 'feedback-img-ic.png',
              'status' => 1,
              'url' => '',
            ),
        );

        
       
    }

    public function get_daily_streak_status_post($api_call=0)
    {
        $this->load->model('Coins_model');
        $master_coin_data_key = "master_coin_data";
        $master_coin_data = $this->get_cache_data($master_coin_data_key);

        if(empty($master_coin_data))
        {
            $master_coin_data = $this->Coins_model->get_daily_streak_coins();
            $this->set_cache_data($master_coin_data_key, $master_coin_data, REDIS_2_DAYS);
        }
      
        $this->load->model("auth/Auth_nosql_model");
        $user_last_data = $this->Coins_model->get_last_daily_streak_coin();
        $user_data = array();

        if(!empty($user_last_data))
        {
            $now =convert_to_client_timezone(format_date(),'Y-m-d');
            $entry_date = convert_to_client_timezone($user_last_data['date_added'],'Y-m-d');

            $days = $this->dateDiffInDays($entry_date,$now);

            if($days <= 1)
            {
                $daily_claimed_data =  json_decode($user_last_data['custom_data'],TRUE);
                if($daily_claimed_data['day_number']> 1)
                {
                    //get last entries
                    $last_entries = $this->Coins_model->get_last_coin_entries($daily_claimed_data['day_number']);
                    if(!empty($last_entries))
                    {
                        $user_data = array_column($last_entries,'points');
                    }
                }
                else{
                    $user_data[] = $user_last_data['points'];
                }
            }

            
        }
       
        $data = array();  
        $daily_streak_coins = json_decode($master_coin_data[0]['daily_coins_data'],TRUE);
       
        //overwrite claimed day with coins
        if(!empty($user_data) && count($user_data) < count($daily_streak_coins))
        {
            //sort($user_data);
            $user_data = array_reverse($user_data);
            foreach($user_data as $key => $used_day)
            {
                $daily_streak_coins[$key] = $used_day;
            }
        }

        $daily_streak_length =count($daily_streak_coins);
        $data['allow_claim'] =1;  
        $data['current_day'] =1;  
        $data['current_day_coins'] =0;  
        if(!empty($user_last_data))
        {
            $daily_streak_coins_data = json_decode($user_last_data['custom_data'],TRUE);
        
            $new_day = $daily_streak_coins_data['day_number'] +1; 
    
             if($days < 1)
            {
                $data['allow_claim'] =0;
                $data['current_day'] =$daily_streak_coins_data['day_number']; 
            }
            else if($days == 1 && $new_day <= $daily_streak_length )
            {
                //consecutive benefit
                $data['current_day'] =$new_day; 
            }    
            else if($days > 1 || $new_day > $daily_streak_length)
            {
                $data['current_day'] =1; 
            }
            else{
                $data['allow_claim'] =0;
                $data['current_day'] =$daily_streak_coins_data['day_number']; 
            }
        }
       
        $day_number = 1;  
        foreach($daily_streak_coins as $value)
        {
            if($data['current_day'] == $day_number)
            {
              $data['current_day_coins'] = $value;
            }
            $day_number++;
        }

        if(empty($data['daily_streak_coins']))
        {
            $data['allow_claim'] =0;
        }

        if($api_call==0)
        {
          $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
          $this->api_response_arry['data'] = $data;
          $this->api_response(); 
        }
        else
        {
          return $data;
        }
    }

     /**
     * @method claim_download_app_coins_post
     * @param NA
     * @uses function to claim download app coins
     ***/

    function claim_download_app_coins_post()
    {
        $order_data = array();
        $user_id = $this->user_id;
        $this->load->model('coins/Coins_model');

        $install_date = $this->Coins_model->get_app_install_date();

        if(empty($install_date['install_date']))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("err_download_app_claim");
            $this->api_response();
        }
      
        $row = $this->Coins_model->get_download_app_coins_status();

        if(!empty($row))
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['global_error'] = $this->lang->line("err_already_coin_claimed");
            $this->api_response();
        }

        $this->load->model('finance/Finance_model');
        //$source_id = $quiz['quiz_id'];
        $order_data["order_unique_id"] = $this->Finance_model->_generate_order_key();
        $order_data["user_id"]        = $this->user_id;
        $order_data["source"]         = 471;
        $order_data["source_id"]      = 0;
        $order_data["reference_id"]   = 0;
        $order_data["season_type"]    = 1;
        $order_data["type"]           = 0;
        $order_data["status"]         = 1;
        $order_data["real_amount"]    = 0;
        $order_data["bonus_amount"]   = 0;
        $order_data["winning_amount"] = 0;
        $order_data["points"] = DOWNLOAD_APP_COINS;
        $order_data["plateform"]      = PLATEFORM_FANTASY;
        $order_data["date_added"]     = format_date();
        $order_data["modified_date"]  = format_date();
        
        if($order_data["points"] > 0 )
        {
            $order_id = $this->Finance_model->insert_order($order_data);
            if ($order_data["status"] == 1) {
                $this->Finance_model->update_user_balance($order_data["user_id"], $order_data, 'add');

                $this->load->helper('queue_helper');
                $coin_data = array(
                    'oprator' => 'add', 
                    'user_id' => $this->user_id, 
                    'total_coins' => DOWNLOAD_APP_COINS, 
                    'bonus_date' => format_date("today", "Y-m-d")
                );
                add_data_in_queue($coin_data, 'user_coins');

                $user_cache_key = "user_balance_" . $order_data["user_id"];
                $this->delete_cache_data($user_cache_key);        
            }
        }

        $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
        $this->api_response_arry['message'] = $this->lang->line('succ_claim_download_app_coin');
        $this->api_response(); 
  
    }



    
}
