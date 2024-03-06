<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Common_cron extends MY_Controller
{

	function __construct()
	{
		parent::__construct();
		//$this->load->model('common_model');
		//$this->load->model('mail_model');
		$this->load->model('common_cron_model');
		set_time_limit(0);
	}

	public function index()
	{
		redirect('');
	}

	public function contest_cancellation($uncapped = '0')
	{
		$this->common_cron_model->contest_cancellation($uncapped);
	}
	
		
	public function daily_prize_distribute_to_winner()
	{
		$this->common_cron_model->daily_prize_distribute_to_winner();
	}
	
	
	
	public function update_scores_in_lineup($sports_id = '')
	{
		if(is_numeric($sports_id) && $sports_id > 0)
		{
                        $post_target_url        = 'season/get_current_season_match';
                        $post_params            = array('sports_id' => $sports_id);
                        $response               = $this->http_post_request($post_target_url,$post_params,1);
                        
                        echo '<pre>';
                        
                        if($response['response_code'] == 200){
                            
                            if(!empty($response['data']))
                            {
                                $season_matches = $response['data'];
                                $this->common_cron_model->update_scores_in_lineup($season_matches);
                            }   
                        }
		}
        }

	public function update_node_client($league_name = 'Brasileiro Serie A')
	{
		if(is_numeric($league_name))
		{
			$league_id = $league_name;
		}
		else
		{
			$league = $this->common_model->get_single_row('league_id',LEAGUE,array('league_abbr' => strtoupper(urldecode($league_name)),'active'=> '1'));
			$league_id = $league['league_id']; 
		}
		//check if id exist
		($league_id) ? : redirect('');
			$this->common_cron_model->update_score_node_client($league_id);
			
	}

	public function update_contest_status($league_name = 'Brasileiro Serie A')
	{
		if(is_numeric($league_name))
		{
			$league_id = $league_name ;
		}
		else
		{

			$league = $this->common_model->get_single_row('league_id',LEAGUE,array('league_abbr' => strtoupper(urldecode($league_name)),'active'=> '1'));
			$league_id = $league['league_id']; 
		}
		//check if id exist
		($league_id) ? : redirect('');
			$this->common_cron_model->update_contest_status($league_id);
			
	}

	public function update_referral_bonus()
	{
                $this->load->model('Notification_model');
                
		$referral_data = $this->common_cron_model->get_referral_users();
		$referral_fund_data = $this->common_cron_model->get_referral_fund_detail();

		$history_data = array();
		foreach($referral_data as $referral)
		{
			$user_data = $this->common_cron_model->get_user_detail_by_id($referral['from_user_id']);
			$history_data = array();
			$history_data['payment_type']					= CREDIT;
			$history_data['master_description_id']			= TRANSACTION_HISTORY_DESCRIPTION_REFERRAL_FUND;
			$history_data['user_balance_at_transaction']	= $user_data['balance'];
			$history_data['transaction_amount']				= $referral_fund_data['referral_amount'];
			$history_data['user_id']						= $referral['from_user_id'];
			$history_data['is_processed']					= '1';
			$history_data['created_date']					= format_date();
			$this->common_cron_model->add_transaction($history_data);

			$balance = $user_data['balance']+$referral_fund_data['referral_amount'];

			$this->common_cron_model->update_user_balance($referral['from_user_id'] , $balance);

			$this->common_cron_model->update_referral_status($referral['referral_id'] );
                        
                        
                        ## Send Notification for REFER_FRIEND_BONUS ##
                        
                        $notification_type = $this->common_model->get_single_row( 'notification_param' , NOTIFICATION_TYPE , array('notification_type_id' => NOTIFY_REFER_FRIEND_BONUS));
                        
                        $param_value = NULL;
                        
                        if( !empty($notification_type['notification_param']) ) {

                                $notification_param = json_decode($notification_type['notification_param'], true);

                                $notification_param['user_id']      = $referral['user_id'];
                                $notification_param['bonus_amount'] = $referral_fund_data['referral_amount'];
                                
                                $param_value = json_encode($notification_param);
                        }

                        $notification_data = array(
                                'notification_type_id' => NOTIFY_REFERRED_USER_JOINED,
                                'receiver_user_id'     => $referral['from_user_id'],
                                'is_read'              => '0',
                                'created_date'         => format_date(),
                                'param_value'          => $param_value
                        );
                        $this->Notification_model->add_notification(NOTIFY_REFER_FRIEND_BONUS, $notification_data);

                        ## Send notification END ##
		}
		echo "done";
	}

	/*
	* Cron to updte limit activate duration
	*/
	public function update_user_limit_status()
	{
		$update_data =  $this->common_cron_model->get_user_limit_data();
		if ($update_data) 
		{		
			foreach ($update_data as $key => $value) 
			{
				$now_date_time = format_date();
				$limit_time = $value['date_added'];
				$hours = round(abs(strtotime($now_date_time)-strtotime($limit_time))/60/60);
				if ($hours >= 24) 
				{
					$this->db->update(USER_LIMIT, array('status'=>'0'),array('user_id'=>$value['user_id'],'limit_for'=>$value['limit_for']));
					$this->db->update(USER_LIMIT, array('status'=>'1'),array('limit_id'=>$value['limit_id']));
				}
			}
		}
	}


	public function prize_distribute_for_prediction($league_id = '1')
	{
		$this->common_cron_model->prize_distribute_for_prediction($league_id);
	}

	//function for process bonus conversion for user
	public function process_bonus_conversion()
	{
		$this->common_cron_model->process_bonus_conversion();
		echo 'Bonus conversion done';
	}
        
        /**
        * This method checks for player club changes and send notification about club change
        * @param integer $league_id
        */
        public function player_club_change_notification(){
            $this->common_cron_model->player_club_change_notification();
        }
        
//        public function distribute_big_game_bonus(){
//            $this->common_cron_model->distribute_big_game_bonus(BONUS_BIG_GAME_UNIQUE_ID, BONUS_ON_BIG_GAME_JOIN);
//        }
        
        /**
        * This method distribute bonus cash and points to users who eligible for that as per available offers
        */
        public function earn_bonus_and_points_by_offer(){
            $this->common_cron_model->private_game_create_bonus();
        }
        
        /**
        * This method set users rating as per their game_win_points 
        * It should run after game prize distribution or game close cron 
        */
        public function set_user_rating(){
            $this->common_cron_model->set_user_rating();
        }
        
}

/* End of file cron.php */
/* Location: ./application/controllers/cron.php */
