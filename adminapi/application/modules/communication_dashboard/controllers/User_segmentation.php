<?php defined('BASEPATH') OR exit('No direct script access allowed');
class User_segmentation extends MYREST_Controller {
    public $user_lang;
    public $email_template_actions= array();
    public $preview_template_actions= array();
    public $template_source_id = NULL;
    public $prepare_tracking_url = NULL;
	public function __construct()
	{
		parent::__construct();
		ini_set('memory_limit', '-1');
		$this->load->model('User_segmentation_model');
		$_POST = $this->input->post();	

		define('DEFAULT_PROMOCODE','FIRSTDEPOSIT');
		define('TEST_EMAILS','ankitp.vinfotech@gmail.com');
		define('TEST_NUMBERS','9098021773');

		$this->email_template_actions[120] =array('detail' => "deposit_promotion_detail",
												  'preview' => 'deposit_promotion_preview',
												  'source_id_key' => 'promoCodeId',
												  'message_content' => 'prepare_content_for_deposit_promotion',
												  'notification_content' => 'prepare_noti_content_for_deposit_promotion',
												  'email_tracking_url'=>'prepare_email_tracking_url_deposit_promotion'
												  );//deposit promotion

		$this->email_template_actions[121] =array('detail' => "contest_promotion_detail",
												  'preview' => 'contest_promotion_preview',
												  'source_id_key' => 'contest_id',
												  'message_content' => 'prepare_content_for_contest_promotion',
												  'notification_content' => 'prepare_noti_content_for_contest_promotion',
												  'email_tracking_url'=>'prepare_email_tracking_url_contest_promotion'
												  );//promotion for contest
		
		$this->email_template_actions[123] =array('detail' => "refer_a_friend_detail",
												  'preview' => 'refer_a_friend_preview',
												  'source_id_key' => 'affiliate_master_id',
												  'message_content' => 'prepare_content_for_referral_promotion',
												  'notification_content' => 'prepare_noti_content_for_referral_promotion',
												  'email_tracking_url'=>'prepare_email_tracking_url_refer_a_friend'
												  );//admin refer a friend
		
		$this->email_template_actions[300] =array('detail' => "fixture_promotion_detail",
												  'preview' => 'fixture_promotion_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'prepare_content_for_fixture_promotion',
												  'notification_content' => 'prepare_noti_content_for_fixture_promotion',
												  'email_tracking_url'=>'prepare_email_tracking_url_fixture_promotion'
												  );//promotion for fixture

		$this->email_template_actions[131] =array('detail' => "fixture_delay_detail",
												  'preview' => 'fixture_delay_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'prepare_content_for_late_delay',
												  'notification_content' => 'prepare_noti_content_for_late_delay',
												  'email_tracking_url'=>'prepare_email_tracking_url_late_delay'
												  );//rain delay
		$this->email_template_actions[132] =array('detail' => "fixture_promotion_detail",
												  'preview' => 'fixture_promotion_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'prepare_content_for_lineup_out',
												  'notification_content' => 'prepare_noti_content_for_lineup_out',
												  'email_tracking_url'=>'prepare_email_tracking_url_lineup_out'
												  );//lineup announced

	   $this->email_template_actions[134] =array('detail' => "common_action",
												  'preview' => 'fixture_promotion_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'common_action',
												  'notification_content' => 'common_action',
												  'email_tracking_url'=>'common_action'
												  );//lineup announced										  

	   $this->email_template_actions[301] =array('detail' => "daily_login_earn_coin",
												  'preview' => 'common_action',
												  'source_id_key' => 'notification_type',
												  'message_content' => 'prepare_content_for_lineup_out',
												  'notification_content' => 'prepare_noti_content_for_daily_checkin',
												  'email_tracking_url'=>'prepare_email_tracking_url_lineup_out'
												  );//lineup announced

	   $this->email_template_actions[302] =array('detail' => "common_action",
												  'preview' => 'fixture_promotion_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'prepare_content_for_lineup_out',
												  'notification_content' => 'prepare_noti_content_for_lineup_out',
												  'email_tracking_url'=>'prepare_email_tracking_url_lineup_out'
												  );//lineup announced
	   $this->email_template_actions[0] =array('detail' => "redeem_coins",
												  'preview' => 'fixture_promotion_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'prepare_content_for_lineup_out',
												  'notification_content' => 'prepare_noti_content_for_custom_category',
												  'email_tracking_url'=>'prepare_email_tracking_url_lineup_out'
												  );//lineup announced

		$this->email_template_actions[135] =array('detail' => "common_action",
												  'preview' => 'common_action',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'common_action',
												  'notification_content' => 'common_action',
												  'email_tracking_url'=>'common_action'
												  );//lineup
		$this->email_template_actions[434] =array('detail' => "deal_detail",
												  'preview' => 'common_action',
												  'source_id_key' => 'deal_id',
												  'message_content' => 'common_action',
												  'notification_content' => 'common_action',
												  'email_tracking_url'=>'common_action'
												  );//Deal promotion 
		$this->email_template_actions[435] =array('detail' => "new_promocode_detail",
												  'preview' => 'common_action',
												  'source_id_key' => 'promoCodeId',
												  'message_content' => 'common_action',
												  'notification_content' => 'common_action',
												  'email_tracking_url'=>'common_action'
												  );//all prmocode promotion

	  	$this->admin_roles_manage($this->admin_id,'marketing');										  

	   $this->email_template_actions[301] =array('detail' => "daily_login_earn_coin",
												  'preview' => 'fixture_promotion_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'prepare_content_for_lineup_out',
												  'notification_content' => 'prepare_noti_content_for_lineup_out',
												  'email_tracking_url'=>'prepare_email_tracking_url_lineup_out'
												  );//lineup announced

	   $this->email_template_actions[302] =array('detail' => "redeem_coins",
												  'preview' => 'fixture_promotion_preview',
												  'source_id_key' => 'season_game_uid',
												  'message_content' => 'prepare_content_for_lineup_out',
												  'notification_content' => 'prepare_noti_content_for_lineup_out',
												  'email_tracking_url'=>'prepare_email_tracking_url_lineup_out'
												  );//lineup announced

	   $this->admin_roles_manage($this->admin_id,'marketing');
		
	}

	public function index()
	{
		$this->load->view('layout/layout', $this->data, FALSE);
	}

	public function common_action($input_data){
		return $input_data;
	}

	public function deal_detail($id="")
	{
		$post_data = $this->input->post();
		if(isset($post_data['email_template_id']) && $post_data['email_template_id']==14)
		{
			$this->template_source_id = $post_data['deal_id'];
			return $id;
		}
		else
		{
			$result = $this->User_segmentation_model->get_single_row('*',DEALS,array('deal_id' => $id));
			$this->template_source_id = $result; 
			return $result;
		}
		
	}

	public function new_promocode_detail($id = "")
	{
		$post_data = $this->input->post();
		if(isset($post_data['email_template_id']) && $post_data['email_template_id']==15)
		{
			$this->template_source_id = $post_data['promoCodeId'];
			return $id;
		}
		else
		{
			$result = $this->User_segmentation_model->get_single_row('*',PROMO_CODE,array('promo_code_id' =>$id,'status'=>'1'));
			$this->template_source_id = $result; 
			return $result;
		}

	}

	public function daily_login_earn_coin($id = "")
	{
			$result = $this->User_segmentation_model->get_single_row('*',CD_EMAIL_TEMPLATE,array('notification_type' =>$id,'message_type'=>2));
			$this->template_source_id = $result; 
			return $result;
	}


	public function prepare_email_tracking_url_refer_a_friend($input_data)
	{
		// $current_date = format_date('today','Y-m-d');
		$this->prepare_tracking_url =urlencode(WEBSITE_URL.'refer-friend');
		
	}

	public function prepare_email_tracking_url_deposit_promotion($input_data)
	{
		$current_date = format_date('today','Y-m-d');
		$this->prepare_tracking_url =urlencode(WEBSITE_URL.'add-funds');
	}

	public function prepare_email_tracking_url_contest_promotion($input_data)
	{
		$current_date = format_date('today','Y-m-d');
		 $contest_url = WEBSITE_URL.$input_data['sports_name'].'/contest/'.$input_data['contest_unique_id'].'?rcuid='.$input_data['recent_communication_unique_id'].'&'.CD_UTM_TRACKING_CONTEST_EMAIL.$current_date;

    	return $contest_url;

	}

	public function prepare_email_tracking_url_fixture_promotion($input_data)
	{
		$this->prepare_tracking_url =urlencode(WEBSITE_URL.'lobby#'.$input_data['sports_name']);
	}


	public function prepare_email_tracking_url_late_delay($input_data)
	{
		$this->prepare_tracking_url =urlencode(WEBSITE_URL.'lobby#'.$input_data['sports_name']);
		
	}

	public function prepare_email_tracking_url_lineup_out($input_data)
	{
		$current_date = format_date('today','Y-m-d');
 		$match_str =strtolower($input_data['home']).'-vs-'.strtolower($input_data['away']).'-'.date('d-m-Y',strtotime($input_data['season_scheduled_date'])).'?rcuid='.$input_data['recent_communication_unique_id'];
    	$fixture_url = WEBSITE_URL.$input_data['sports_name'].'/my-teams/'.$input_data["collection_master_id"].'/'.$match_str.'&'.CD_UTM_TRACKING_FIXTURE_EMAIL.$current_date;

		return $fixture_url;
	}
	public function prepare_content_for_deposit_promotion($message_body ,$template_data)
	{
		$message_body = str_replace('{{promo_code}}', $template_data['promo_code'],$message_body);
		$message_body = str_replace('{{offer_percentage}}', $template_data['discount'],$message_body);
		return $message_body;
	}

	public function prepare_content_for_contest_promotion($message_body ,$template_data)
	{
		$message_body = str_replace('{{contest_name}}', $template_data['contest_name'],$message_body);
		$message_body = str_replace('{{collection_name}}', $template_data['collection_name'],$message_body);
		return $message_body;
	}

	public function prepare_content_for_referral_promotion($message_body ,$template_data)
	{
		$message_body = str_replace('{{amount}}', $template_data['bonus_amount'],$message_body);
		return $message_body;
	}
	public function prepare_content_for_fixture_promotion($message_body ,$template_data)
	{
		$message_body = str_replace('{{collection_name}}', $template_data['collection_name'],$message_body);
		$message_body = str_replace('{{home}}', $template_data['home'],$message_body);
		$message_body = str_replace('{{away}}', $template_data['away'],$message_body);
		return $message_body;
	}

	public function prepare_content_for_late_delay($message_body ,$template_data)
	{
		$message_body = str_replace('{{collection_name}}', $template_data['collection_name'],$message_body);
		$message_body = str_replace('{{MINUTES}}', $template_data['delay_minute'],$message_body);
		$message_body = str_replace('{{season_scheduled_date}}', $template_data['season_scheduled_date'],$message_body);
		return $message_body;
	}

	public function prepare_content_for_lineup_out($message_body ,$template_data)
	{
		$message_body = str_replace('{{collection_name}}', $template_data['collection_name'],$message_body);
		return $message_body;
	}

	public function prepare_noti_content_for_deposit_promotion($content ,$template_data)
	{
		$content['promo_code'] = $template_data['promo_code'];
		$content['offer_percentage'] = $template_data['discount'];
		$content['notification_text'] = $template_data["message_body"];
		$content['notification_subject'] = $template_data["subject"];
		$content['template_data']['notification_landing_page'] = $template_data["redirect_to"];
		return $content;
		// $content['custom_notification_subject'] = $post["custom_notification_subject"];
	}

	public function prepare_noti_content_for_contest_promotion($content ,$template_data)
	{
		$content['contest_name'] = $template_data['contest_name'];
		$content['collection_name'] = $template_data['collection_name'];
		return $content;
	}

	public function prepare_noti_content_for_referral_promotion($content ,$template_data)
	{
		$content['amount'] = $template_data['bonus_amount'];
		$content['notification_text'] = $template_data["message_body"];
		$content['notification_subject'] = $template_data["subject"];
		$content['template_data']['notification_landing_page'] = $template_data["redirect_to"];
		return $content;
	}

	public function prepare_noti_content_for_fixture_promotion($content ,$template_data)
	{
		$content['collection_name'] = $template_data['collection_name'];
		$content['home'] = $template_data['home'];
		$content['away'] = $template_data['away'];
		$content['notification_text'] = $template_data["message_body"];
		$content['notification_subject'] = $template_data["subject"];
		$content['template_data']['notification_landing_page'] = $template_data["redirect_to"];

		return $content;
	}

	public function prepare_noti_content_for_late_delay($content ,$template_data)
	{
		$content['collection_name'] = $template_data['collection_name'];
		$content['minutes'] = $template_data['delay_minute'];
		$content['season_scheduled_date'] = $template_data['season_scheduled_date'];
		$content['notification_text'] = $template_data["message_body"];
		$content['notification_subject'] = $template_data["subject"];
		$content['template_data']['notification_landing_page'] = $template_data["redirect_to"];

		return $content;
	}

	public function prepare_noti_content_for_lineup_out($content ,$template_data)
	{
		$content['collection_name'] = $template_data['collection_name'];
		return $content;
	}
	public function prepare_noti_content_for_custom_category($content ,$template_data){
		$content['custom_notification_subject'] = $template_data["subject"];
		$content['custom_notification_text'] = $template_data["message_body"];
		$content['template_data']['notification_landing_page'] = $template_data["redirect_to"];
		$content['template_data']['custom_notification_landing_page'] = $template_data["redirect_to"];
		return $content;
	}

	public function _generate_key() 
	{
		$this->load->helper('security');
		do {
			$salt = do_hash(time() . mt_rand());
			$new_key = substr($salt, 0, 10);
		}

		// Already in the DB? Fail. Try again
		while (self::_key_exists($new_key));

		return $new_key;
	}

	public function _key_exists($key)
	{
		$this->db->select('recent_communication_unique_id');
        $this->db->where('recent_communication_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(CD_RECENT_COMMUNICATION);
        $num = $query->num_rows();
        if($num > 0){
            return true;
        }
		return false;

	}

	public function get_upcoming_fixtures($sports_id=7)
    {
		if(!empty($sports_id))
		{
			$sports_id = $this->input->post("sports_id");
		}

        $post_data = $this->input->post();
        $sports_id = $post_data["sports_id"];
        $this->load->model(array("contest/Contest_model","season/Season_model"));
		$collection_result = $this->Contest_model->get_lobby_fixture_list($post_data);
        $collection_result_arr = array();
        if(!empty($collection_result)){
            $season_ids = array_unique(array_column($collection_result,"season_id"));
			$match_list = $this->Season_model->get_fixture_season_detail($season_ids);
            $match_list = array_column($match_list, NULL, "season_id");
            foreach($collection_result as $collection){
                $match_info = $match_list[$collection['season_id']];
                if(!empty($match_info)){
	                $tmp_arr = array_merge($collection,$match_info);
	                $collection_result_arr[] = $tmp_arr;
                }
            }
		}
        return $collection_result_arr;
    }

	public function get_filter_list_post()
	{
		$master_data = $this->User_segmentation_model->get_filter_list();

		$result = array();
		if(!empty($master_data))
		{
			foreach ($master_data as $key => $value) {
					
				if(!empty($value['activity_type']) && $value['activity_type'] =='1')
				{
					$result['login_activity_filters'][] = $value;
				}	

				if(!empty($value['activity_type']) && $value['activity_type'] =='2')
				{
					$result['signup_activity_filters'][] = $value;
				}	

			}
		}

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	public function get_filter_result_test_post($call_from_api=true)
	{
		$post = $this->input->post();


		if(empty($post['all_user']) && empty($post['login']) && empty($post['signup']) && empty($post['user_base_list_id']) && empty($post['fixture_participation']))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select a activity type.";
			$this->api_response();
		}

		// if(empty($post['all_user']) && empty($post['custom']) && empty($post['last_7_days']))
		// {
		// 	$this->api_response_arry['response_code'] 	= 500;
		// 	$this->api_response_arry['data']  			= array();
		// 	$this->api_response_arry['global_error']    = "Please select a filter.";
		// 	$this->api_response();
		// }

		if(!empty($post['custom']) && (empty($post['from_date']) || empty($post['to_date'])))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select valid dates.";
			$this->api_response();
		}

		if(!empty($post['from_date']) && !empty($post['to_date']) && strtotime($post['from_date']) >  strtotime($post['to_date'])  )
			{
				$this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['data']  			= array();
				$this->api_response_arry['global_error']    = "Please select Valid date range.";
				$this->api_response();
			}



		if(empty($post['added_date']))
		{
			$post['added_date'] = format_date();
		}

		$result = array();
		if(!empty($post))
		{
			if(isset($post['last_7_days']) && $post['last_7_days'] == 1)
			{
				$post['activity_type'] =1;
				$post['from_date'] = date('Y-m-d h:i:s',strtotime($post['added_date'].' -7 days'));
				$post['to_date'] = $post['added_date'];
			}


			if(isset($post['login']) && $post['login'] == '1') //login activity
			{
				$result_data =  $this->User_segmentation_model->get_login_user_count_by_filter($post);
				$result['total_users'] = 0;

				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
					$result['user_ids'] = $user_ids;
				}
			}

			if(isset($post['signup']) && $post['signup'] == '1') //signup activity
			{
				$result_data =  $this->User_segmentation_model->get_signup_user_count_by_filter($post);
				$result['total_users'] = 0;
				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
					$result['user_ids'] = $user_ids;
				}
			}

			if(isset($post['all_user']) && $post['all_user'] == '1')
			{
				$where = array("is_systemuser"=>0,"status"=>1);
				$result_data = $this->User_segmentation_model->get_all_table_data('user_id',USER,$where);
				$result['total_users'] = 0;

				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
					$result['user_ids'] = $user_ids;
				}

			}
			if(isset($post['fixture_participation']) && $post['fixture_participation'] == '1')
			{
				$users = $this->get_fixture_users($post['season_game_uid']);
				if(!empty($users))
				{
					$result['total_users'] = count($users);
					// $user_ids = array_column($users, 'user_id');
					$result['user_ids'] = array_unique($users);

				}

			}

			if(isset($post['user_base_list_id']) && !empty($post['user_base_list_id'])){
				$result_data =  $this->User_segmentation_model->get_single_user_base_list($post['user_base_list_id']);
				$result['total_users'] = 0;
				if(!empty($result_data))
				{
					$result['total_users'] = $result_data[0]['count'];
					$result['user_base_list_id'] = $result_data[0]['user_base_list_id'];
					$result['user_ids'] = explode(',',$result_data[0]['user_ids']);
				}
			}

			if(isset($post['user_base_list_id']) && !empty($post['user_base_list_id'])){
				$result_data =  $this->User_segmentation_model->get_single_user_base_list($post['user_base_list_id']);
				$result['total_users'] = 0;
				if(!empty($result_data))
				{
					$result['total_users'] = $result_data[0]['count'];
					$result['user_base_list_id'] = $result_data[0]['user_base_list_id'];
					$result['user_ids'] = explode(',',$result_data[0]['user_ids']);
				}
			}

		}


		if($call_from_api)
		{
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['data']  			= $result;
			$this->api_response();
		}
		else
		{
			return $result;
		}
	}


	public function get_deposit_promocodes_post()
	{
		$filters = $this->input->post();
		$template_names = ['contest_join_promocode','deposit_promocode','deposit_range_promocode','first_deposit_promocode'];
		$promocode_type = '';
		if(isset($filters['template_name']) && in_array($filters['template_name'],$template_names))
		{
			switch($filters['template_name'])
			{
				case "contest_join_promocode":
					$filters['promocode_type'] = 3;
				break;
				case "deposit_promocode":
					$filters['promocode_type'] = 2;
				break;
				case "deposit_range_promocode":
					$filters['promocode_type'] = 1;
				break;
				case "first_deposit_promocode":
					$filters['promocode_type'] = 0;
				break;
			}
		}
		$promocodes = $this->User_segmentation_model->get_deposit_promocodes($filters);

		foreach ($promocodes as $key => &$value) {
			$value['value'] = $value['promo_code_id'];
			if($value['value_type']==1)
			{
				$value['label'] = $value['promo_code'].' ('.$value['discount'].'%)';
			}else{
				$value['label'] = $value['promo_code'].' ('.$value['discount'].')';
			}
			

		}


		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['promocodes']  			= $promocodes;
		$this->api_response();

	}

	public function deposit_promotion_detail($promo_code_id ='')
	{
		// echo "<pre>";
		// var_dump($promo_code_id);
		// print_r($this->input->post());
		// die('dfd');
		if(empty($promo_code_id))
		{
			$this->form_validation->set_rules('promo_code_id', 'Promocode' , 'trim|required');
			if($this->form_validation->run() == FALSE)
			{
				$this->send_validation_errors();
			}

			$promo_code_id = $this->input->post('promo_code_id');
		}
		//get promocode details by Promocode
		$result = $this->User_segmentation_model->get_single_row('*',PROMO_CODE,array('promo_code_id' => $promo_code_id));
		$this->template_source_id =$result['promo_code_id']; 
		return $result ;
	}

	public function contest_promotion_detail($contest_id="")
	{
		if(empty($contest_id))
		{

			$this->form_validation->set_rules('contest_id', 'Contest' , 'trim|required');
			if($this->form_validation->run() == FALSE)
			{
				$this->send_validation_errors();
			}
			$contest_id = $this->input->post('contest_id');
		}
	
		$post_contest_para     = array("contest_id" => $contest_id );
		$this->load->model('contest/Contest_model');
		$post_contest_response= $this->Contest_model->get_contest_detail($post_contest_para);
		$this->template_source_id = $contest_id; 
		return $post_contest_response;	
	}

	public function refer_a_friend_detail($id="")
	{
		$result = $this->User_segmentation_model->get_single_row('*',AFFILIATE_MASTER,array('affiliate_type' => 1));
		$this->template_source_id = $result['affiliate_master_id']; 
		//use coin amount or user bonus in case of bonus

		return $result;
	}

	public function fixture_promotion_detail($season_game_uid ='')
	{
		if(empty($season_game_uid))
		{
			$this->form_validation->set_rules('season_game_uid', 'Match' , 'trim|required');
			if($this->form_validation->run() == FALSE)
			{
				$this->send_validation_errors();
			}
			$season_game_uid = $this->input->post('season_game_uid');
		}
		
        
        $this->load->model('contest/Contest_model');

        $match_data  =$this->Contest_model->get_match_detail_by_suid($season_game_uid);

        
        if(!empty($match_data)){
           
            $this->template_source_id = $match_data['season_game_uid']; 
	        $cm_data =$this->Contest_model->get_collection_master_by_suid($match_data['season_id']);
	        if(!empty($cm_data)){
            	$collection_data = $cm_data;   
             	$match_data = array_merge( $match_data ,$collection_data);
        	}
        }

		return $match_data;

	}

	public function fixture_delay_detail($season_game_uid ='')
	{
		if(empty($season_game_uid))
		{
			$this->form_validation->set_rules('season_game_uid', 'Match' , 'trim|required');
			if($this->form_validation->run() == FALSE)
			{
				$this->send_validation_errors();
			}
			$season_game_uid = $this->input->post('season_game_uid');
		}
		$this->load->model('contest/Contest_model');

        $match_data  =$this->Contest_model->get_match_detail_by_suid($season_game_uid);
		$fix_data = $this->User_segmentation_model->get_fixtureinfo_by_sguid($season_game_uid);
		if(!empty($fix_data)){
            	$collection_data = $fix_data[0];   
             	$match_data = array_merge( $match_data ,$collection_data);
        	}
		return $match_data;

	}

	function notify_by_selection_post()
	{
		$post= $this->input->post();

		$is_email = (!empty($post['email']) && $post['email'])?1:0;
		$is_message = (!empty($post['message']) && $post['message'])?1:0;
		$is_notification = (!empty($post['notification']) && $post['notification'])?1:0;

		if(empty($post['email']) && empty($post['message']) && empty($post['notification']))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select a notify type.";
			$this->api_response();
		}


		$users_result = $this->get_filter_result_test_post(false);
		$post['user_ids'] = $users_result['user_ids'];

		if(empty($post['email_template_id']))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']    = "Please select a template.";
			$this->api_response();
		}



		//send email and notifiction
		$result = $this->User_segmentation_model->get_users_by_ids($post['user_ids']);
		//get template

		if(getenv('CD_TEST_MODE')==1)
		{
			 $result  = array($result[0]);
		}

		$email_template = $this->User_segmentation_model->get_single_row('*',CD_EMAIL_TEMPLATE,array('cd_email_template_id' => $post['email_template_id'] ));

		//assign values season_game_uid,promo_code_id,affiliate_master_id,contest_id
		// if(!empty($post['source_id']))
		// {
		// 	$_POST[$this->email_template_actions[$email_template["notification_type"]]['source_id_key']] =$post['source_id'];
		// }

		//get user ids
		if($email_template['template_name'] == "promotion-for-deposit")
		{
			$user_ids = array_column($result, 'user_id');

			//get user ids with not deposits
			$result = $this->User_segmentation_model->get_users_by_ids_first_deposit($user_ids);

			if(empty($result))
			{
				$this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['data']  			= array();
				$this->api_response_arry['global_error']    = "No users for first deposit.";
				$this->api_response();
			}
		}


		$notification_desc = $this->User_segmentation_model->get_single_row('*',NOTIFICATION_DESCRIPTION,array('notification_type' => $email_template['notification_type']));

		$chunks = array_chunk($result, 999);
		$current_date = format_date();
		
		$message_numbers = array();

		$communication_data = array();
		$communication_data['added_date'] = format_date();
		if($post['all_user'] == '1')
		{
			$communication_data['user_details'] ="All Users";
			$communication_data['userbase'] =1;
			$communication_data['activity_type'] =0;
		}
		else
		{
			
			if($post['signup'] == '1')
			{
				$communication_data['userbase'] =3;
				$communication_data['user_details'] ="Signup";
			}
			elseif($post['login'] == '1')
			{
				$communication_data['userbase'] =2;
				$communication_data['user_details'] ="Login";
			}

			//save communication data
			

			if(!empty($post['from_date']))
			{
				$communication_data['from_date'] = $post['from_date'];
			}

			if(!empty($post['to_date']))
			{
				$communication_data['to_date'] = $post['to_date'];
			}

			$communication_data['activity_type'] =2;
			if($post['last_7_days'] == 1)
			{
				$communication_data['activity_type'] =1;

				$communication_data['from_date'] = date('Y-m-d h:i:s',strtotime($communication_data['added_date'].' -7 days'));
				$communication_data['to_date'] = $communication_data['added_date'];
			}

		}

		$communication_data['email_template_id'] =$post['email_template_id'];
		$communication_data['email_count'] =0;
		$communication_data['notification_count'] =0;

		$template_data = array();
		
		$function_name =$this->email_template_actions[$email_template["notification_type"]]['detail'];

		//add notification type data
		$template_data = $this->$function_name();
		$this->load->helper("cron");
		
		$cd_balance_deduct_history = array();


		foreach ($chunks as $key1 => $chunk) {

			$temp_array= array();
			$notification_list = array();
			foreach ($chunk as $key2 => $email) 
			{
				$deduct_history_change = array();
				if($is_email && !empty($email['email']))
				{

					$content = array("subject" => $email_template['subject'],
					"email_body" => $email_template['email_body'],
					"user_name" => $email['user_name'],
					"template_data" => $template_data ,
					'user_id' => $email['user_id'],
					);

                    $email_content 				= array();


                    if(getenv('CD_TEST_MODE')==1)
					{
						 $email_content['email'] = TEST_EMAILS;

					}
					else
					{
                    	$email_content['email'] 			= $email['email'];

					}
                    $email_content['subject'] 			= $email_template['subject'];
                    $email_content['user_name']			= "";
                    $email_content['content'] 			= $content;
                    $email_content['notification_type'] = $email_template['notification_type'];
                    $temp_array[] = $email_content;

                    $communication_data['email_content'] = $email_template['email_body'];

                    $communication_data['email_count']++;

                    if(!empty($email['email']))
                    {
                    	$deduct_history_change['email_id'] = $email['email'];
                    }
				}
				
				if($is_notification)
				{
					$content = array(
					"user_name" => $email['user_name'],
					"template_data" => $template_data ,
					'user_id' => $email['user_id'],
					'SITE_TITLE' => SITE_TITLE,
					'offer_percentage'=> 0,
					'amount' => 0,
					'contest_name'=> '',
					'home' => '',
					'away' => ''
					);

					if(!empty($template_data['promo_code']))
					{
						$content['promo_code'] = $template_data['promo_code'];
					}

					if(!empty($template_data['discount']))
					{
						$content['offer_percentage'] = $template_data['discount'];
					}

					if(!empty($template_data['bonus_amount']))
					{
						$content['amount'] = $template_data['bonus_amount'];
					}

					if(!empty($template_data['contest_name']))
					{
						$content['contest_name'] = $template_data['contest_name'];
					}

					if(!empty($template_data['home']))
					{
						$content['home'] = $template_data['home'];
					}

					if(!empty($template_data['away']))
					{
						$content['away'] = $template_data['away'];
					}
					$notification = array();
					$notification['notification_type']        = $email_template['notification_type'];
					$notification['source_id']                = $email_template['cd_email_template_id'];
					$notification['user_id']                  = $email['user_id'];
					$notification['notification_status']      = '1';
					$notification['content']                  = json_encode($content);
					$notification['notification_destination'] = 1;
					$notification['added_date']               = $current_date;
					$notification['modified_date']            = $current_date;
					$notification_list[] = $notification;

					$communication_data['notification_count']++;
					$communication_data['notification_content'] = $notification_desc["message"];
				} 

				if(!empty($deduct_history_change))
				{
					$deduct_history_change['added_date'] = $current_date;
					$cd_balance_deduct_history[] = $deduct_history_change;
				}
			}

			$communication_data['source_id'] =$this->template_source_id;
			$communication_data['recent_communication_unique_id'] =$this->_generate_key();

			$this->db->trans_start();
			$recent_communication_id = $this->User_segmentation_model->insert_recent_communication($communication_data);

			if(!empty($cd_balance_deduct_history))
			{
				foreach ($cd_balance_deduct_history as $key => &$value) {
					$value['recent_communication_id'] = $recent_communication_id;
				}
		    	$this->User_segmentation_model->add_balance_history($cd_balance_deduct_history);
			}


		    if(!empty($notification_list))
			{
				$this->User_segmentation_model->table_name = NOTIFICATION;
				$this->User_segmentation_model->insert_batch($notification_list);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE)
			{
			    $this->db->trans_rollback();
			    $this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['data']  			= array();
				$this->api_response_arry['global_error']    = "Something went Wrong.";
				$this->api_response();
			}
			else
			{
			        $this->db->trans_commit();
			}

			if(!empty($temp_array))
			{
				foreach($temp_array as &$emailObj)
				{
					$emailObj['recent_communication_id'] = $recent_communication_id;
					$emailObj['recent_communication_unique_id'] = $communication_data['recent_communication_unique_id'];
				}
				$this->rabbit_mq_push($temp_array, CD_BULK_EMAIL_QUEUE);
			}
		}

 
		$data_arr = array(
                                                    'email_count' => $communication_data['email_count'],
                                                    'notification_count' => $communication_data['notification_count'],   // 0: real amount
                                                    
                                                );

		$this->User_segmentation_model->update_cd_balance($data_arr);
	
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= array();
		$this->api_response_arry['message']  		= "The selected users have been succesfully notified.";
		$this->api_response();
	}

	function get_short_url($long_url)
    {

        $this->load->library('bitly');
        $params = array();
        $params['access_token'] = BITLY_ACCESS_TOKEN;
        $params['longUrl'] = $long_url;
        $params['domain'] = 'bit.ly';
        $results = $this->bitly->bitly_get('shorten', $params);

        if(isset($results['data']) && isset($results['data']['url']))
        {
         return $results['data']['url'];
        }
        else
        {
            log_message('error',json_encode($results));
            return $long_url;
        }
    }

	 public function validate_cd_balance_auth_key()
    {
    	$auth_key = $this->input->post('cd_balance_auth_key');
    	
    	if($auth_key == CD_BALANCE_AUTH_KEY)
    	{
    		return true;
    	}

		$this->form_validation->set_message('validate_cd_balance_auth_key', "Please provide a valid auth key.");
        return FALSE;
    }

	public function deduct_cd_balance_post()
	{
		$this->form_validation->set_rules('email_count', 'Email Count' , 'trim|required');
		$this->form_validation->set_rules('notification_count', 'Notification Count' , 'trim|required');
		$this->form_validation->set_rules('cd_balance_auth_key', 'Auth Key' , 'trim|required|callback_validate_cd_balance_auth_key');

		if($this->form_validation->run() == FALSE)
		{
			$this->send_validation_errors();
		}

		$post = $this->input->post();
		
		//update balance
		$this->User_segmentation_model->update_cd_balance($post);

		$this->api_response_arry["service_name"]  = "deduct_cd_balance";
        $this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
        $this->api_response_arry["message"] = "Balance Deducted.";
        $this->api_response();

	} 


	function notify_by_selection_counts_post()
	{
		$post= $this->input->post();

		$is_email = (!empty($post['email']) && $post['email'])?1:0;
		$is_message = (!empty($post['message']) && $post['message'])?1:0;
		$is_notification = (!empty($post['notification']) && $post['notification'])?1:0;

		$users_result = $this->get_filter_result_test_post(false);
		$post['user_ids'] = $users_result['user_ids'];

		//send email and notifiction
		$result = $this->User_segmentation_model->get_users_by_ids($post['user_ids']);
		//get template

		$email_template = $this->User_segmentation_model->get_single_row('*',CD_EMAIL_TEMPLATE,array('cd_email_template_id' => $post['email_template_id'] ));

		//get user ids
		if($email_template['template_name'] == "promotion-for-deposit")
		{
			$user_ids = array_column($result, 'user_id');

			//get user ids with not deposits
			$result = $this->User_segmentation_model->get_users_by_ids_first_deposit($user_ids);

			if(empty($result))
			{
				$this->api_response_arry['response_code'] 	= 500;
				$this->api_response_arry['data']  			= array();
				$this->api_response_arry['global_error']    = "No users for first deposit.";
				$this->api_response();
			}
		}


		$notification_desc = $this->User_segmentation_model->get_single_row('*',NOTIFICATION_DESCRIPTION,array('notification_type' => $email_template['notification_type']));

		$chunks = array_chunk($result, 999);
		$current_date = format_date();
		
		$message_numbers = array();

		$communication_data = array();

		if($post['all_user'] == '1')
		{
			$communication_data['user_details'] ="All user";
			$communication_data['userbase'] =1;
		}
		else
		{
			$communication_data['user_details'] =$email_template['display_label'];
			if($post['signup'] == '1')
			{
				$communication_data['userbase'] =3;
			}
			elseif($post['login'] == '1')
			{
				$communication_data['userbase'] =2;
			}
		}

		$communication_data['email_template_id'] =$post['email_template_id'];
		$communication_data['email_count'] =0;
		$communication_data['notification_count'] =0;

		$this->load->helper("cron");
		foreach ($chunks as $key1 => $chunk) {

			$temp_array= array();
			$notification_list = array();
			foreach ($chunk as $key2 => $email) 
			{
				if($is_email)
				{
                    $communication_data['email_count']++;
				}
				
				if($is_notification)
				{
					$communication_data['notification_count']++;
				} 
			}

		}

		//save communication data
		$communication_data['added_date'] = format_date();

		if(!empty($post['from_date']))
		{
			$communication_data['from_date'] = $post['from_date'];
		}

		if(!empty($post['to_date']))
		{
			$communication_data['to_date'] = $post['to_date'];
		}

		// echo "<pre>";
		// print_r($communication_data);
		// die('dfdf');
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $communication_data;
		$this->api_response_arry['message']  		= "";
		$this->api_response();
	}

	public function resent_communication_post()
	{
		$this->form_validation->set_rules('userbase', 'Userbase', 'trim|required');
		$this->form_validation->set_rules('recent_communication_id', 'recent communication id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post = $this->input->post();
		$recent_communication = $this->User_segmentation_model->get_single_row('*',RECENT_COMMUNICATION,array(
			"recent_communication_id" => $post['recent_communication_id']));

		if(empty($recent_communication))
		{
			$this->api_response_arry['response_code'] 	= 200;
			$this->api_response_arry['data']  			= array();
			$this->api_response_arry['global_error']   = "No record Found.";
			$this->api_response();
		}

		// echo "<pre>";
		// print_r($recent_communication);
		// die('dfdf');

	}

	public function get_cd_balance_post()
	{
		$this->api_response_arry['data']['cd_balance'] = $this->User_segmentation_model->get_cd_balance();
		$this->api_response_arry['response_code'] 	= 200;
		//$this->api_response_arry['message']  		= "User Notified.";
		$this->api_response();
	}

	public function get_delayed_fixtures_post(){
		$post = $this->input->post();
		$sports_id = 7;
		if(!empty($post['sports_id']))
		{
			$sports_id = $post['sports_id'];
		}
		$fixtures = $this->get_upcoming_fixtures($sports_id);
		$delayed_fixtures = array();
		foreach($fixtures as $key=>$fixture){
			if($fixture['delay_minute']>0){
				$delayed_fixtures[]=$fixture;
			}
		// print_r($fixture['delay_minute']);exit;
		}
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['fixtures']['data']  			= $delayed_fixtures;
		$this->api_response();
	}

	public function get_recent_communication_list_post()
	{
		$result_data =  $this->User_segmentation_model->get_recent_communication_list();
		$scheduled_data =  $this->User_segmentation_model->get_scheduled_list();

		$this->api_response_arry['data']['cd_sent_count'] = $this->User_segmentation_model->get_sent_count();

		$post = $this->input->post();

		$sports_id = 7;
		if(!empty($post['sports_id']))
		{
			$sports_id = $post['sports_id'];
		}

		foreach($result_data['result'] as &$row)
		{
			if(!empty($row['list_name']))
			{
				$row['user_details'] =$row['list_name']; 
			}
		}
		
		foreach($scheduled_data['result'] as &$sch)
		{
			if(!empty($sch['list_name']))
			{
				$sch['sch_user_detail'] =$sch['list_name']; 
			}
		}

		$fixtures = $this->get_upcoming_fixtures($sports_id);
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['recent_communication_list']  	= $result_data;
		$this->api_response_arry['data']['sch_comm_list']  				= $scheduled_data;
		$this->api_response_arry['data']['fixtures']['data']  			= $fixtures;
		$this->api_response_arry['data']['CD_ONE_EMAIL_RATE']  			= CD_ONE_EMAIL_RATE;
		$this->api_response_arry['data']['CD_ONE_NOTIFICATION_RATE']= CD_ONE_NOTIFICATION_RATE;
		$this->api_response_arry['data']['fixture_image_path']=IMAGE_PATH.FLAG_CONTEST_DIR;
		//$this->api_response_arry['message']  		= "User Notified.";
		$this->api_response();
	}

	function get_recent_communication_detail_post()
	{
		$this->form_validation->set_rules('recent_communication_id', 'recent communication id', 'trim|required');
		$this->form_validation->set_rules('noti_schedule', 'Scheduler Flag', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		if($this->input->post('noti_schedule')==1)
		{
			$result_data =  $this->User_segmentation_model->get_recent_communication_list();
		}elseif($this->input->post('noti_schedule')==2)
		{
			$result_data =  $this->User_segmentation_model->get_scheduled_list();
		}

		if(empty($result_data))
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['data']  			= [];
			$this->api_response_arry['global_error']  	= 'No record Found';
			$this->api_response_arry['message']  		= '';
			$this->api_response();
		}

		$recent_communication_detail = $result_data['result'][0];

		$function_name = $this->email_template_actions[$recent_communication_detail['notification_type']]['detail'];

		$template_data = $this->$function_name($recent_communication_detail['source_id']);

		$recent_communication_detail['email_body'] = str_replace("{{BUCKET_URL}}",IMAGE_PATH,$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace("{{WEBSITE_URL}}",WEBSITE_URL,$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace("{{SITE_URL}}",WEBSITE_URL,$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace("{{WEBSITE_DOMAIN}}",WEBSITE_DOMAIN,$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace("{{year}}",date('Y'),$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace("{{CURRENCY_CODE_HTML}}",CURRENCY_CODE_HTML,$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace('href="<?php echo ANDROID_APP_LINK; ?>"','href="'.ANDROID_APP_LINK.'"',$recent_communication_detail['email_body']);
		$recent_communication_detail['email_body'] = str_replace('href="<?php echo IOS_APP_LINK; ?>"','href="'.IOS_APP_LINK.'"',$recent_communication_detail['email_body']);
	
		$recent_communication_detail['email_body'] = str_replace("{{user_name}}",'Demo',$recent_communication_detail['email_body']);
	
		if(isset($template_data['delay_minute']))
		{
			$recent_communication_detail['email_body'] = str_replace("{{MINUTES}}",$template_data['delay_minute'],$recent_communication_detail['email_body']);
		}

		if(!empty($template_data['collection_name']))
		{
			$recent_communication_detail['email_body'] = str_replace("{{collection_name}}",$template_data['collection_name'],$recent_communication_detail['email_body']);
		}

		$recent_communication_detail['subject'] = str_replace("{{SITE_TITLE}}",'['.SITE_TITLE.']',$recent_communication_detail['subject']);

		if(!empty($template_data['collection_name']))
		{
			$recent_communication_detail['subject'] = str_replace("{{collection_name}}",$template_data['collection_name'],$recent_communication_detail['subject']);
		}
		
		if(isset($template_data['season_scheduled_date']) && $template_data['season_scheduled_date'] !="")
		{
			$converted_date = get_timezone(strtotime($template_data['season_scheduled_date']),'Y-m-d H:i:s',$this->app_config['timezone'],2);
			$template_data['season_scheduled_date'] = $converted_date['date'].' '.'('.$converted_date['tz'].')';
		}


		//preview_template_actions
		$preview_function = $this->email_template_actions[$recent_communication_detail['notification_type']]['preview'];
		$recent_communication_detail['email_body'] = $this->$preview_function($recent_communication_detail['email_body'],$template_data);
		
			$recent_communication_detail['message_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$recent_communication_detail['message_body']);
		$recent_communication_detail['message_body'] = str_replace("{{CURRENCY_CODE_HTML}}",CURRENCY_CODE_HTML,$recent_communication_detail['message_body']);


		//$recent_communication_detail['message_body'] = str_replace("{{username}}",'Demo',$recent_communication_detail['message_body']);

		if(!empty($template_data['bonus_amount']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{amount}}",$template_data['bonus_amount'],$recent_communication_detail['message_body']);
		}

		if(!empty($template_data['home']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{home}}",$template_data['home'],$recent_communication_detail['message_body']);
		}

		if(!empty($template_data['away']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{away}}",$template_data['away'],$recent_communication_detail['message_body']);
		}
		
		if(!empty($template_data['contest_name']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{contest_name}}",$template_data['contest_name'],$recent_communication_detail['message_body']);
		}

		if(!empty($template_data['collection_name']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{collection_name}}",$template_data['collection_name'],$recent_communication_detail['message_body']);
		}

		$recent_communication_detail['message_body'] = str_replace("{{FRONTEND_BITLY_URL}}",FRONTEND_BITLY_URL,$recent_communication_detail['message_body']);

		if(!empty($template_data['promo_code']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{promo_code}}",$template_data['promo_code']
			,$recent_communication_detail['message_body']);
		}

		if(!empty($template_data['season_scheduled_date']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{season_scheduled_date}}",$template_data['season_scheduled_date']
			,$recent_communication_detail['message_body']);
		}

		if(isset($template_data['delay_minute']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{MINUTES}}",$template_data['delay_minute']
			,$recent_communication_detail['message_body']);
		}

		if(!empty($template_data['discount']))
		{
			$recent_communication_detail['message_body'] = str_replace("{{offer_percentage}}",$template_data['discount']
			,$recent_communication_detail['message_body']);
		}	
		$recent_communication_detail['notification_message'] =$recent_communication_detail['message_body'];	

		if(empty($recent_communication_detail['notification_message']) && !empty($recent_communication_detail['notification_content']))
		{
			$recent_communication_detail['notification_message']= $recent_communication_detail['notification_content'];
		}

		if(empty($recent_communication_detail['message_body']) && !empty($recent_communication_detail['sms_content']))
		{
			$recent_communication_detail['message_body']= $recent_communication_detail['sms_content'];
		}

		if(isset($template_data['deal_id']))
		{
			$deal_benifit=0;
			$bonus_amount = ($template_data['bonus'] > 0) ? $template_data['bonus']." bonus cash, ":""; 
			$real_amount = ($template_data['cash'] > 0) ? 	$template_data['cash']." real cash, ":""; 
			$coin_amount = ($template_data['coin'] > 0) ? 	$template_data['coin']." coins  ":""; 
			if($this->app_config['int_version']['key_value']==1){
			$deal_benifit = $coin_amount;
			}
			else{
			$deal_benifit = $bonus_amount.$real_amount.$coin_amount;
			}
			$deal_benifit = substr($deal_benifit,0,-2);
			$recent_communication_detail['email_body']= "";
			$recent_communication_detail['notification_content']= "Deal Promotion";
			$recent_communication_detail['notification_message']= str_replace("{{amount}}",$template_data['amount'],$recent_communication_detail['notification_message']);
			$recent_communication_detail['notification_message']= str_replace("{{deal_benifit}}",$deal_benifit,$recent_communication_detail['notification_message']);
		}
		
		if(isset($template_data['promo_code_id']))
		{
			$prmocode_benifit = 0;
			$cash_type = '';
			$benefit_cap = '.';
			$deposit_range = '';

			if($template_data['cash_type']==0)
			{
			$cash_type="bonus cash";
			}
			elseif($template_data['cash_type']==1)
			{
			// $cash_type = $this->app_config['currency_code']['key_value'];
			$cash_type = 'real cash';
			}

			if($template_data['value_type']==0)
			{
			$prmocode_benifit = $template_data['discount'].' '.$cash_type;
			}
			elseif($template_data['value_type']==1)
			{
			$prmocode_benifit = $template_data['discount'].'% '.$cash_type;
			}

			if($template_data['value_type']==1 && $template_data['benefit_cap']>0)
			{
				$benefit_cap = " max upto ".$template_data['benefit_cap'].' '.$cash_type.'.';
			}

			if($template_data['min_amount']!=null && $template_data['max_amount']!=null && $template_data['min_amount']!=0 && $template_data['max_amount']!=0)
			{
				$deposit_range = ' on '.$this->app_config['currency_code']['key_value'].$template_data['min_amount'].'-'.$this->app_config['currency_code']['key_value'].$template_data['max_amount'];
			}
			$recent_communication_detail['notification_message'] = str_replace("{{new_promocode}}",$template_data['promo_code'],$recent_communication_detail['notification_message']);
			$recent_communication_detail['notification_message'] = str_replace("{{promocode_benifit}}",$prmocode_benifit,$recent_communication_detail['notification_message']);
			$recent_communication_detail['notification_message'] = str_replace("{{capping_text}}",$benefit_cap,$recent_communication_detail['notification_message']);
			$recent_communication_detail['notification_message'] = str_replace("{{deposit_range}}",$deposit_range,$recent_communication_detail['notification_message']);
		}

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['recent_communication_detail']  			= $recent_communication_detail;
		$this->api_response_arry['message']  		= '';
		$this->api_response();

	}

	function deposit_promotion_preview($email_body,$template_data)
	{
	    $email_body = str_replace("{{promo_code}}", $template_data['promo_code'], $email_body);
	    $email_body = str_replace("{{offer_percentage}}", $template_data['discount'] , $email_body);
	    return $email_body;
	}

	function contest_promotion_preview($email_body,$template_data)
	{
		$email_body = str_replace("{{CONTEST_NAME}}",$template_data['contest_name'],$email_body);
		return $email_body;
	}

	function refer_a_friend_preview($email_body,$template_data)
	{
		$email_body = str_replace("{{amount}}", $template_data['bonus_amount'], $email_body);
		return $email_body;
	}

	function fixture_promotion_preview($email_body,$template_data)
	{
		$email_body = str_replace("{{home}}", $template_data['home'], $email_body);
	    $email_body = str_replace("{{away}}", $template_data['away'], $email_body);
	    $email_body = str_replace("{{home_flag}}", $template_data['home_flag'], $email_body);
	    $email_body = str_replace("{{away_flag}}", $template_data['away_flag'], $email_body);
	    $email_body = str_replace("{{season_scheduled_date}}",$template_data['season_scheduled_date'] , $email_body);
	    return $email_body;
	}

	function fixture_delay_preview($email_body,$template_data)
	{
		$email_body = str_replace("{{collection_name}}", $template_data['collection_name'], $email_body);
	    $email_body = str_replace("{{season_scheduled_date}}",$template_data['season_scheduled_date'] , $email_body);
	    return $email_body;
	}

	public function get_filter_result_post()
	{
		$this->form_validation->set_rules('activity_id', 'activity id', 'trim|required');
		$this->form_validation->set_rules('login_min', 'Min', 'trim');
		$this->form_validation->set_rules('login_max', 'Max', 'trim|callback_validate_login_cutom_filter');

		$this->form_validation->set_rules('signup_min', 'Min', 'trim');
		$this->form_validation->set_rules('signup_max', 'Max', 'trim|callback_validate_signup_cutom_filter');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post = $this->input->post();

		$row = $this->User_segmentation_model->get_single_row("*",MASTER_USER_SEGMENTATION,array('master_user_segmentation_id' => $post['activity_id']));

		$result = array();
		if(!empty($row))
		{
			if($row['activity_type'] == '1') //login activity
			{

				if($row['master_user_segmentation_id'] == 5)
				{
					$row['min'] = $post['login_min'];
					$row['max'] = $post['login_max'];
				}
				$result_data =  $this->User_segmentation_model->get_login_user_count_by_filter($row);

				$result['total_users'] = 0;

				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
				}
				
			}

			if($row['activity_type'] == '2') //signup activity
			{
				if($row['master_user_segmentation_id'] == 10)
				{
					$row['min'] = $post['signup_min'];
					$row['max'] = $post['signup_max'];
				}
				$result_data =  $this->User_segmentation_model->get_signup_user_count_by_filter($row);

				$result['total_users'] = 0;
				if(!empty($result_data))
				{
					$user_ids =array_unique(array_column($result_data, 'user_id')) ;
					$result['total_users'] = count($user_ids);
				}
			}

		}

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']  			= $result;
		$this->api_response();
	}

	public function get_all_matchs_post()
	{
		$data_arr = $this->input->post();

		$post_target_url   = 'season/get_all_matchs';
		$post_api_response       = $this->http_post_request($post_target_url,$data_arr,1);

		

		$this->api_response_arry = $post_api_response;
		$this->api_response();
	}

	public function get_segementation_list_post()
	{

		$result = $this->User_segmentation_model->get_user_segmentation_list();
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['result'] = $result;
		$this->api_response();
	}

	public function get_segementation_template_list_post()
	{

		$result = $this->User_segmentation_model->get_segementation_template_list();


		foreach ($result as $key => &$value) {
			$value['email_body'] = str_replace("{{BUCKET_URL}}",IMAGE_PATH,$value['email_body']);
			$value['email_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$value['email_body']);

		}
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['result'] = $result;
		$this->api_response();
	}

	function delete_filter_post()
	{

		$this->form_validation->set_rules('filter_id', 'Segmentation', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post = $this->input->post();
		$result = $this->User_segmentation_model->delete_segment($post['filter_id']);
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data'] = array();
		$this->api_response();
	}

	function save_filter_post()
	{
		$this->form_validation->set_rules('activity_id', 'activity id', 'trim|required');
		$this->form_validation->set_rules('login_min', 'Min', 'trim');
		$this->form_validation->set_rules('login_max', 'Max', 'trim|callback_validate_login_cutom_filter');

		$this->form_validation->set_rules('signup_min', 'Min', 'trim');
		$this->form_validation->set_rules('signup_max', 'Max', 'trim|callback_validate_signup_cutom_filter');
		$this->form_validation->set_rules('name', 'Name', 'trim|required');
		$this->form_validation->set_rules('user_count', 'User count', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post= $this->input->post();

		$current_date = format_date();

		$filter_data = array();

		$filter_data['name'] = $post['name']; 
		$filter_data['added_date'] = $current_date; 
		$filter_data['user_count'] = $post['user_count']; 
		$filter_data['master_user_segmentation_id'] = $post['activity_id']; 
		$filter_data['updated_date'] = $current_date; 

		if(!empty($post['login_min']))
		{
			$filter_data['custom_min'] = $post['login_min'];
		}

		if(!empty($post['login_max']))
		{
			$filter_data['custom_max'] = $post['login_max'];
		}

		if(!empty($post['signup_min']))
		{
			$filter_data['custom_min'] = $post['signup_min'];
		}

		if(!empty($post['signup_max']))
		{
			$filter_data['custom_max'] = $post['signup_max'];
		}

		// echo "<pre>";
		// print_r($filter_data);
		// die('dfd');
		//save filter
		$last_id = $this->User_segmentation_model->insert_filter($filter_data);
		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['last_id'] = $last_id;
		$this->api_response();
	}


	public function add_notify_entity_post()
	{
		$post= $this->input->post();
		$this->form_validation->set_rules('type', 'Type', 'trim|required|callback_check_notify_type');
		$this->form_validation->set_rules('value', 'Value', 'trim|required|is_natural_no_zero');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post= $this->input->post();

		$current_date = format_date();

		$data = array();
		//get currect balance
		$row = $this->User_segmentation_model->get_single_row('*',CD_BALANCE);

		if(empty($row))
		{
			$this->User_segmentation_model->save_balance_entry();
			$row = $this->User_segmentation_model->get_single_row('*',CD_BALANCE);
		}

		$data['type'] = $post['type'];//Email
		
		//load hash table here
		$this->config->load('hashtables');
    	$cd_balance_update_ht    =$this->config->item("cd_balance_update"); 
    	$cd_balance_template_ht    =$this->config->item("cd_balance_template"); 

    	$row = $cd_balance_update_ht[$post['type']]($row,$post);
	
		$function_name =$cd_balance_template_ht[$post['type']]['update_balance']; 

		$data['value'] =  $post['value'];
		$data['added_date'] =  $current_date;
		$data['updated_date'] =  $current_date;

		$this->User_segmentation_model->update_notify_balance($function_name,$data['value'],array('cd_balance_id' => $row['cd_balance_id']  ));
		
		//save 
		$this->User_segmentation_model->save_balance_history($data);

		$template_name = "";
    	$template_name = $cd_balance_template_ht[$post['type']]['template_name'];
	    $template_data = $this->User_segmentation_model->get_single_row('*',CD_EMAIL_TEMPLATE,array('template_name' => $template_name));


	    $template_data['email_body'] =str_replace('{{SITE_TITLE}}', SITE_TITLE,$template_data['email_body'] );
	    $template_data['email_body'] =str_replace('{{entity_count}}', $post['value'],$template_data['email_body'] );
	    $template_data['email_body'] =str_replace('{{amount}}', $post['amount'],$template_data['email_body'] );
	    $template_data['email_body'] =str_replace('{{entity_value}}', $post['entity_name'],$template_data['email_body'] );
	    $template_data['email_body'] =str_replace('{{CURRENCY_CODE}}', CURRENCY_CODE_HTML,$template_data['email_body'] );
	    $template_data['email_body'] =str_replace('{{rate}}', $cd_balance_template_ht[$post['type']]['rate'],$template_data['email_body'] );
	    $template_data['email_body'] =str_replace('{{year}}', date('Y',strtotime(format_date())),$template_data['email_body'] );

	   


	    $content = array("subject" => $template_data['subject'],
					"email_body" => $template_data['email_body'],
					);
	    
        $email_content 				= array();
        $email_content['email'] 			= CD_NOTIFY_EMAILS;
        $email_content['subject'] 			= $template_data['subject'];
        $email_content['content'] 			= $content;
        $email_content['notification_type'] = $template_data['notification_type'];
        


		//send emails to support
        $this->rabbit_mq_push($email_content, 'cd_email');

		$this->api_response_arry['response_code'] 	= 200;
		$this->api_response_arry['data']['cd_balance'] = $row;
		$this->api_response_arry['message'] = "Thank You for purchasing. Please refresh the page if you can`t see it added.";

		$this->api_response();
	}

	public function get_cd_type_possible_counts_post()
	{

	}

	public function export_filter_data_get()
	{
		$post = $this->input->get();
		$user_ids = array();

		if(!empty($post['login']) && $post['login'] == '1') //login activity
		{
			$result_data =  $this->User_segmentation_model->get_login_user_count_by_filter($post);
			if(!empty($result_data))
			{
				$user_ids =array_unique(array_column($result_data, 'user_id')) ;	
			}
		}
	
		if(!empty($post['signup']) && $post['signup'] == '1') //signup activity
		{
			$result_data =  $this->User_segmentation_model->get_signup_user_count_by_filter($post);

			
			if(!empty($result_data))
			{
				$user_ids =array_unique(array_column($result_data, 'user_id')) ;
			}
		}

		if(!empty($post['all_user']) && $post['all_user'] == '1') //signup activity
		{
			$result_data = $this->User_segmentation_model->get_all_table_data('*',USER);

			
			if(!empty($result_data))
			{
				$user_ids =array_unique(array_column($result_data, 'user_id')) ;
			}
		}

		if(!empty($user_ids))
		{
			$users_data = $this->User_segmentation_model->export_filter_data($user_ids);
			// echo "<pre>";
			// print_r($users_data);
			// die('df');
		}


		$result = array();
		foreach ($users_data as $key => $value) {

			switch ($value['DeviceType']) {
				
				case 1:
					$device_type = "Android";
					break;
				case 2:
					$device_type = "ios";
					break;
				case 3:
					$device_type = "Web";
					break;
				case 4:
					$device_type = "Mobile";
					break;
			}

			$result[] = $value;
		}

		if(!empty($result)){
			$header = array_keys($result[0]);
			$result = array_merge(array($header),$result);
			$this->load->helper('csv');
			array_to_csv($result,'user_segmentation.csv');
		}

	}

	public function get_preference_list_post(){	
		$this->load->model('common/Common_model');

		$post_data = $this->input->post();
		$sports = $this->Common_model->get_all_sport($post_data);
		$preferences = $this->User_segmentation_model->get_preference_list();
		$sid = array_column($preferences,'sports_id');
		$list = array();
		foreach($sports as $key=>$value){
			$sports[$key]['status']=0;
			if(in_array($sports[$key]['sports_id'], $sid)){
				$list[$key]['sports_name']=$sports[$key]['sports_name'];
				$list[$key]['sports_id']=$sports[$key]['sports_id'];
				$list[$key]['status']=1;
				$list[$key]['min_value']='0';		
				$list[$key]['max_value']='0';
				$list[$key]['added_date']='';		
			}else{
				$list[$key]['sports_name']=$sports[$key]['sports_name'];
				$list[$key]['sports_id']=$sports[$key]['sports_id'];
				$list[$key]['status']=0;
				$list[$key]['min_value']='';		
				$list[$key]['max_value']='';
				// $list[$key]['sport_preferences_id']='';		
				$list[$key]['added_date']='';		
			}
			foreach($preferences as $prefer){
				if($prefer['sports_id']==$list[$key]['sports_id']){
				// $list[$key]['sport_preferences_id']=$prefer['sport_preferences_id'];		
					$list[$key]['min_value']=$prefer['min_value'];		
					$list[$key]['max_value']=$prefer['max_value'];		
					$list[$key]['added_date']=$prefer['added_date'];		
				}
			}
		}
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $list;
		$this->api_response();

	} 
	public function update_preference_list_post(){
		if(empty($this->input->post())){
		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['global_error']  		  = "Please provide preference list";
		$this->api_response();
		}
		// $this->form_validation->set_rules('sport_id',"Sports Id",'trim|required');
		//  if (!$this->form_validation->run()) 
		// {
		// 	$this->send_validation_errors();
		// }    
		$post_data = $this->input->post();
		foreach($post_data as $key=>$value){
			$post_data[$key]['added_date']=format_date();
		}
		$update = $this->User_segmentation_model->update_preference_list($post_data);

		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']  		  = 'Sports preference updated successfully';
		$this->api_response();
	}

	public function get_city_names_post(){
		$cities = $this->User_segmentation_model->get_city_names();
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $cities;
		$this->api_response();
	}

	private function prepare_list($post_data){
		$post_data = $this->input->post();

		//new function
		$list_param = array();
		$list_param['list_name']=$post_data['list_name'];
		// print_r($post_data);exit;
		
		if(isset($post_data['sport_preference']['status']))
			$list_param['sport_id']= json_encode($post_data['sport_preference']);

		if(isset($post_data['location']['status']))
			$list_param['location']=json_encode($post_data['location']);

		if(isset($post_data['age_group']['status']))
			$list_param['age_group']=json_encode($post_data['age_group']);
		if(isset($post_data['profile_status']['status']))
			$list_param['profile_status']=json_encode($post_data['profile_status']);

		if(isset($post_data['gender']['status']))
			$list_param['gender']=json_encode($post_data['gender']);

		if(isset($post_data['admin_created_contest_join']['status'])&& $post_data['admin_created_contest_join']['min_value'] <= $post_data['admin_created_contest_join']['max_value'])
			$list_param['admin_created_contest_join']=json_encode($post_data['admin_created_contest_join']);

		if(isset($post_data['admin_created_contest_won']['status'])&& $post_data['admin_created_contest_won']['min_value'] <= $post_data['admin_created_contest_won']['max_value'])
			$list_param['admin_created_contest_won']=json_encode($post_data['admin_created_contest_won']);

		if(isset($post_data['admin_created_contest_lost']['status'])&& $post_data['admin_created_contest_lost']['min_value'] <= $post_data['admin_created_contest_lost']['max_value'])
			$list_param['admin_created_contest_lost']=json_encode($post_data['admin_created_contest_lost']);

		if(isset($post_data['private_contest_join']['status']) && $post_data['private_contest_join']['min_value'] <= $post_data['private_contest_join']['max_value'])
			$list_param['private_contest_join']=json_encode($post_data['private_contest_join']);

		if(isset($post_data['private_contest_won']['status']) && $post_data['private_contest_won']['min_value'] <= $post_data['private_contest_won']['max_value'])
			$list_param['private_contest_won']=json_encode($post_data['private_contest_won']);

		if(isset($post_data['private_contest_lost']['status']) && $post_data['private_contest_lost']['min_value'] <= $post_data['private_contest_lost']['max_value'])
			$list_param['private_contest_lost']=json_encode($post_data['private_contest_lost']);

		if(isset($post_data['money_deposit']['status']) && $post_data['money_deposit']['min_value'] <= $post_data['money_deposit']['max_value'])
			$list_param['money_deposit']=json_encode($post_data['money_deposit']);

		if(isset($post_data['money_won']['status']) && $post_data['money_won']['min_value'] <= $post_data['money_won']['max_value'])
			$list_param['money_won']=json_encode($post_data['money_won']);

		if(isset($post_data['money_lost']['status']) && $post_data['money_lost']['min_value'] <= $post_data['money_lost']['max_value'])
			$list_param['money_lost']=json_encode($post_data['money_lost']);

		if(isset($post_data['coin_earn']['status']) && $post_data['coin_earn']['min_value'] <= $post_data['coin_earn']['max_value'])
			$list_param['coin_earn']=json_encode($post_data['coin_earn']);

		if(isset($post_data['coin_redeem']['status']) && $post_data['coin_redeem']['min_value'] <= $post_data['coin_redeem']['max_value'])
			$list_param['coin_redeem']=json_encode($post_data['coin_redeem']);

		if(isset($post_data['coin_lost']['status']) && $post_data['coin_lost']['min_value'] <= $post_data['coin_lost']['max_value'])
			$list_param['coin_lost']=json_encode($post_data['coin_lost']);

		if(isset($post_data['referral']['status']) && $post_data['referral']['min_value'] <= $post_data['referral']['max_value'])
			$list_param['referral']=json_encode($post_data['referral']);

		$list_param['status']=1;
		$list_param['count']=$post_data['count'];
		$list_param['user_ids']=implode(',',$post_data['user_ids']);
		

		//end of new function
		return $list_param;
		
	}

	public function get_user_count_post(){
		if(empty($this->input->post())){
			$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry['global_error']  		  = 'Please select atleast one filter';
			$this->api_response();
		}
		$post_data = $this->input->post();
		$count = $this->User_segmentation_model->get_user_count($post_data);
		$this->load->model('New_campaign_model');
		$filter_user_ids = $this->New_campaign_model->filter_system_users($count);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		// $this->api_response_arry['message']  		  = 'Userbase list created successfully';
		$this->api_response_arry['data']  		  = $filter_user_ids;
		$this->api_response();
	}

	public function update_user_base_list_post(){
		$this->form_validation->set_rules('user_base_list_id',"List Id",'trim|required');
		$this->form_validation->set_rules('list_name',"List Name",'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}  

		$post_data = $this->input->post();
		$list_param=array();
		$list_param =$this->prepare_list($post_data);
		$list_param['user_base_list_id']=$post_data['user_base_list_id'];
		$list = $this->User_segmentation_model->update_user_base_list($list_param);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']  		  = 'Userbase list updated successfully';
		$this->api_response_arry['data']  		  = $list;
		$this->api_response();
	}
	public function delete_user_base_list_post(){
		$this->form_validation->set_rules('user_base_list_id',"List Id",'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		} 
		$user_base_list_id = $this->input->post('user_base_list_id');
		$this->User_segmentation_model->delete_user_base_list($user_base_list_id);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']  		  = 'Userbase list deleted successfully';
		$this->api_response();

	}

	public function get_single_user_base_list_post(){
		$this->form_validation->set_rules('user_base_list_id',"List Id",'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		} 
		$user_base_list_id = $this->input->post('user_base_list_id');
		$list_data = $this->User_segmentation_model->get_single_user_base_list($user_base_list_id);
		$list_data= $list_data[0];
		if(isset($list_data['sport_id']) && !empty($list_data['sport_id'])){
			$list_data['sport_id']=  json_decode($list_data['sport_id'],TRUE);
			$list_data['sport_preference']= $list_data['sport_id'];
		}
		if(isset($list_data['age_group']) && !empty($list_data['age_group'])){
			$list_data['age_group']=  json_decode($list_data['age_group'],TRUE);
		}
		if(isset($list_data['profile_status']) && !empty($list_data['profile_status'])){
			$list_data['profile_status']=  json_decode($list_data['profile_status'],TRUE);
		}
		if(isset($list_data['location']) && !empty($list_data['location'])){
			$list_data['location']=  json_decode($list_data['location'],TRUE);
		}
		if(isset($list_data['gender']) && !empty($list_data['gender'])){
			$list_data['gender']=  json_decode($list_data['gender'],TRUE);
		}
		if(isset($list_data['admin_created_contest_join']) && !empty($list_data['admin_created_contest_join'])){
			$list_data['admin_created_contest_join']=  json_decode($list_data['admin_created_contest_join'],TRUE);
		}
		if(isset($list_data['admin_created_contest_won']) && !empty($list_data['admin_created_contest_won'])){
			$list_data['admin_created_contest_won']=  json_decode($list_data['admin_created_contest_won'],TRUE);
		}
		if(isset($list_data['admin_created_contest_lost']) && !empty($list_data['admin_created_contest_lost'])){
			$list_data['admin_created_contest_lost']=  json_decode($list_data['admin_created_contest_lost'],TRUE);
		}
		if(isset($list_data['private_contest_join']) && !empty($list_data['private_contest_join'])){
			$list_data['private_contest_join']=  json_decode($list_data['private_contest_join'],TRUE);
		}
		if(isset($list_data['private_contest_won']) && !empty($list_data['private_contest_won'])){
			$list_data['private_contest_won']=  json_decode($list_data['private_contest_won'],TRUE);
		}
		if(isset($list_data['private_contest_lost']) && !empty($list_data['private_contest_lost'])){
			$list_data['private_contest_lost']=  json_decode($list_data['private_contest_lost'],TRUE);
		}
		if(isset($list_data['money_deposit']) && !empty($list_data['money_deposit'])){
			$list_data['money_deposit']=  json_decode($list_data['money_deposit'],TRUE);
		}
		if(isset($list_data['money_won']) && !empty($list_data['money_won'])){
			$list_data['money_won']=  json_decode($list_data['money_won'],TRUE);
		}
		if(isset($list_data['money_lost']) && !empty($list_data['money_lost'])){
			$list_data['money_lost']=  json_decode($list_data['money_lost'],TRUE);
		}
		if(isset($list_data['coin_earn']) && !empty($list_data['coin_earn'])){
			$list_data['coin_earn']=  json_decode($list_data['coin_earn'],TRUE);
		}
		if(isset($list_data['coin_lost']) && !empty($list_data['coin_lost'])){
			$list_data['coin_lost']=  json_decode($list_data['coin_lost'],TRUE);
		}
		if(isset($list_data['coin_redeem']) && !empty($list_data['coin_redeem'])){
			$list_data['coin_redeem']=  json_decode($list_data['coin_redeem'],TRUE);
		}
		if(isset($list_data['referral']) && !empty($list_data['referral'])){
			$list_data['referral']=  json_decode($list_data['referral'],TRUE);
		}

		if($list_data['status']==0){
		$this->api_response_arry['response_code'] = 500;
		$this->api_response_arry['message']  		  = 'The list you required does not exits';
		$this->api_response();	
		}

		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $list_data;
		$this->api_response();
	}

	public function get_user_base_list_post(){
		$lists = $this->User_segmentation_model->get_user_base_list();
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data'] = $lists;
		$this->api_response();
	}

	public function create_new_category_post(){
		$this->form_validation->set_rules('category_name',"Category Name",'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

		$post_data = $this->input->post();
		// print_r($list_param);exit;
		$available_category = $this->User_segmentation_model->category_availablity($post_data['category_name']);
		if($available_category==FALSE){
		$is_created = $this->User_segmentation_model->create_new_category($post_data);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']  		  = 'Category created successfully';
		// $this->api_response_arry['data']  		  = $is_created;
		$this->api_response();   
		}
		else{
		$this->api_response_arry['response_code'] = 500;
		$this->api_response_arry['global_error']  		  = 'Category already exist with this name';
		$this->api_response();	
		}
	}

	public function get_template_category_post(){
		$category = $this->User_segmentation_model->get_template_category();
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data'] = $category;
		$this->api_response();
	}	

	public function create_new_template_post(){
		
		$this->form_validation->set_rules('message_type',"Template Type",'trim|required');
		$this->form_validation->set_rules('category_id',"Category",'trim|required');
		$this->form_validation->set_rules('template_name',"Template Name",'trim|required');
		$this->form_validation->set_rules('message_body',"",'trim|required');
		$this->form_validation->set_rules('header_image',"Header Image",'trim');
		$this->form_validation->set_rules('body_image',"Body image",'trim');
		$this->form_validation->set_rules('redirect_to',"Redirect To",'trim');
		$this->form_validation->set_rules('subject',"subject",'trim');
		
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		// $category = $this->User_segmentation_model->get_template_category($post_data['category_id']);
		// $category_name = $category[0]['category_name'];
		$save_data  =array();
		$save_data['message_type']=$post_data['message_type'];
		$save_data['category_id']=$post_data['category_id'];
		$save_data['template_name']=$post_data['template_name'];
		$save_data['message_body']=$post_data['message_body'];
		$save_data['notification_type']=0;
		$save_data['status']=1;
		$save_data['type']=0;
		$save_data['subject'] = !empty($post_data['subject'])?$post_data['subject']:"";
		$save_data['header_image']=!empty($post_data['header_image'])?$post_data['header_image']:NULL;
		$save_data['body_image']=!empty($post_data['body_image'])?$post_data['body_image']:NULL ;
		$save_data['redirect_to']=!empty($post_data['redirect_to'])?$post_data['redirect_to']:0 ;
		$save_data['date_added']=format_date();
			
		$list = $this->User_segmentation_model->create_new_template($save_data);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']  		  = 'Template created successfully';
		// $this->api_response_arry['data']  		  = $list;
		$this->api_response();
	}
	
	/**
    * [do_upload description]
    * Summary :- upload team's logo and jersey to s3 
    */
	public function do_upload_post()
	{
		$segment = $this->uri->segment(4);
		
		$file_field_name	= $this->post('name');
		$dir				= APP_ROOT_PATH.UPLOAD_DIR;
		
		$temp_file_image	= $_FILES['file']['tmp_name'];
		$ext				= pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		
		
		if( strtolower( IMAGE_SERVER ) == 'remote' ){
			$file_name = $this->do_upload_process($ext);
			$temp_file = $dir.$file_name;
		}
		//echo "file name : ".$temp_file;die;

		$vals = 			@getimagesize($temp_file_image);
		$width = $vals[0];
		$height = $vals[1];

		
		/*$ratio 			= $width/$height;
		$ratio 				= number_format((float)$ratio, 2, '.', '');
		$ratio 			    = ceil($ratio*2)/2;*/

		if($segment == 'header_image'){
			$subdir				= ROOT_PATH.CD_PUSH_HEADER_DIR;
			$s3_dir 			= CD_PUSH_HEADER_DIR;
			$header_h ='192';
			$header_w='192';
			if ($height != $header_h || $width != $header_w) {//72x120
			
				$invalid_size = str_replace("{max_height}",$header_h,$this->lang->line('push_header_image_invalid_size'));
				$invalid_size = str_replace("{max_width}",$header_w,$invalid_size);
				$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry["global_error"] = $invalid_size;
				$this->api_response();
			}

		}

		if($segment == 'body_image'){
			$subdir	= ROOT_PATH.CD_PUSH_BODY_DIR;
			$s3_dir = CD_PUSH_BODY_DIR;
			$body_h ='240';
			$body_w='720';
			if ($height != $body_h || $width != $body_w) {
				
				$invalid_size = str_replace("{max_height}",$body_h,$this->lang->line('push_body_image_invalid_size'));
				$invalid_size = str_replace("{max_width}",$body_w,$invalid_size);
				//$this->response(array(config_item('rest_status_field_name')=>FALSE, 'message'=>$invalid_size) , rest_controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry["global_error"] = $invalid_size;
				$this->api_response();

			}
		}

		if( strtolower( IMAGE_SERVER ) == 'local')
		{
			$this->check_folder_exist($dir);
			$this->check_folder_exist($subdir);
		}

		$file_name = time().".".$ext ;

		$team_image_arr= array();

		$filePath     = $s3_dir.$file_name;
		/*--Start amazon server upload code--*/
		if( strtolower( IMAGE_SERVER ) == 'remote' )
		{
			if(BUCKET_TYPE=='DO'){
				try{
					$configuration = ['key'=>BUCKET_ACCESS_KEY,'secret'=>BUCKET_SECRET_KEY,'region'=>BUCKET_REGION,'bucket'=>BUCKET ];
					$this->load->library('space');
					$space = new Space($configuration);
					$is_do_upload = $space->space_upload($filePath,$temp_file);

					if($is_do_upload){
						$team_image_arr['image'] = IMAGE_PATH.$filePath;
						unlink($temp_file);
						$data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
						$this->api_response_arry['response_code']   = rest_controller::HTTP_OK;
						$this->api_response_arry['data']            = $data;
						$this->api_response();
					}
				}catch(Exception $e){
					//$result = 'Caught exception: '.  $e->getMessage(). "\n";
					$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry['global_error'] = $this->admin_lang['ad_try_again'];
					$this->api_response(); 
				}
			}
			else{
				$this->load->library( 'S3' );

				$s3 = new S3(array("access_key"=>BUCKET_ACCESS_KEY,"secret_key"=>BUCKET_SECRET_KEY,"region"=>BUCKET_REGION,"use_ssl"=>BUCKET_USE_SSL,"verify_peer"=>BUCKET_VERIFY_PEER));
				$is_s3_upload = $s3->putObjectFile($temp_file, BUCKET, $filePath, S3::ACL_PUBLIC_READ);

				if ( $is_s3_upload )
				{ 

					$team_image_arr['image'] = IMAGE_PATH.$filePath;
					unlink($temp_file);

					$data = array( 'image_name' => $file_name ,'image_url'=> IMAGE_PATH.$filePath);
					$this->api_response_arry["data"] = $data;
					$this->api_response_arry["response_code"] = rest_controller::HTTP_OK;
					$this->api_response();

				} else {


					$error = $this->lang->line('image_upload_error');
					$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
					$this->api_response_arry["global_error"] = strip_tags($error);
					$this->api_response();
				}
			}
			/*--End amazon server upload code--*/
		} else {

			$config['allowed_types']	= 'jpg|png|jpeg|gif|PNG';
			$config['max_size']			= '1024';
			$config['max_width']		= '2400';
			$config['max_height']		= '1200';
			$config['min_width']		= '64';
			$config['min_height']		= '42';
			$config['upload_path']		= $dir;
			$config['file_name']		= time();

			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('file'))
			{
				$error = $this->upload->display_errors();
				$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
				$this->api_response_arry["global_error"] = strip_tags($error);
				$this->api_response();
			}
			else
			{
				$upload_data = $this->upload->data();
				$team_image_arr['image'] = IMAGE_PATH.$s3_dir.$file_name;
				$this->api_response_arry["data"] = array('image_name' =>IMAGE_PATH.$s3_dir.$file_name ,'image_url'=> $subdir);
				$this->api_response();
			}
		}		
	}

/**
    * [do_upload_process description]
    * Summary :- internal function used to upload and resize team's logo and jersey files to local folder.
    */
	public function do_upload_process($ext)
	{
		$dir						= APP_ROOT_PATH.UPLOAD_DIR;
		$config['image_library'] 	= 'gd2';
		$config['allowed_types']	= 'jpg|png|jpeg|gif|PNG';
		$config['max_size']			= '2000';
		$config['min_width']		= '36';//64
		$config['min_height']		= '36';//42
		$config['max_width']		= '2400';
		$config['max_height']		= '1200';
		$config['upload_path']		= $dir;
		$config['file_name']		= rand(1,1000).time().'.'.$ext;

		$this->load->library('upload', $config);

		
		if ( ! $this->upload->do_upload('file'))
		{
			
			$error = $this->upload->display_errors();
			$this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			$this->api_response_arry["global_error"] = strip_tags($error);
			$this->api_response();
		}
		else
		{
			// $upload_data = $this->upload->data();
			// $config1['image_library']	= 'gd2';
			// $config1['source_image']	= $dir.$config['file_name'];
			// //$config['create_thumb']		= TRUE;
			// $config1['maintain_ratio']	= TRUE;
			// $config1['width']			= 200;
			// $config1['height']			= 200;
			
			// $this->load->library('image_lib', $config1);
			// if ( !$this->image_lib->resize())
			// {
			// 	$error = $this->image_lib->display_errors();
		    //     $this->api_response_arry["response_code"] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		    //     $this->api_response_arry["message"] = strip_tags($error);
			// 	$this->api_response();
			// }
			return $config['file_name'];

		}
	}
	  /**
     * [check_folder_exist description]
     * Summary :-
     * @param  [type] $dir [description]
     * @return [type]      [description]
     */
    private function check_folder_exist($dir)
    {
        if(!is_dir($dir))
            return mkdir(getcwd().$dir, 0777);
        return TRUE;
	}
	
	public function get_custome_template_post(){
		
		if(empty($this->input->post())){
		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['global_error']  		  = 'Please provide template id';
		$this->api_response();

		}   
		$post_data = $this->input->post();
		
		$template = $this->User_segmentation_model->get_custome_template($this->input->post());

		$notification_types= array();
		$sms_map = array();
		if(!empty($template))
		{
			$notification_types = array_unique(array_column($template,'notification_type'));
			//get sms templates
			$sms_result = $this->User_segmentation_model->get_sms_template_list(array('notification_types' => $notification_types));
			if(!empty($sms_result))
			{
				$sms_map = array_column($sms_result,NULL,'reference_id');
			}
		}

		$details_for_sms = array();
		foreach ($template as $key => &$value) {
			if(!empty($post_data['template_id']) && ($post_data['template_id']==4 || $post_data['template_id']==11) && !empty($post_data['fixture_id'])){

			$season_game_uid = $post_data['fixture_id'];
			$this->load->model('season/season_model','season_model');
			//NOTE :here to filter out the fixture 1. season_game_uid and 2. league_id are necessary to get correct fixture. league_id is using internally in contest_model by POST ARGUMENT.
			$fixture_details = $this->fixture_promotion_detail($season_game_uid);
			$sports_name = $this->season_model->get_sport_id_by_cmid($fixture_details['collection_master_id']);
			$sports_name = strtolower($sports_name['sports_name']);
			$match_str =strtolower($fixture_details['home']).'-vs-'.strtolower($fixture_details['away']).'-'.date('d-m-Y',strtotime($fixture_details['season_scheduled_date'])).'?rcuid='.$fixture_details['recent_communication_unique_id'].'0';				
			$contest_url = WEBSITE_URL.$sports_name.'/contest-listing/'.$fixture_details['collection_master_id'].'/'.$match_str;

			$con_date = get_timezone(strtotime($fixture_details['season_scheduled_date']),'Y-m-d H:i:s',$tz_arr=$this->app_config['timezone'],2,2);
			$fixture_details['season_scheduled_date'] = $con_date['date'];
			
			$details_for_sms['fixture_details']	= $fixture_details;

			if($post_data['template_id']==11){
				$fixture_info = $this->User_segmentation_model->get_fixtureinfo_by_sguid($season_game_uid);
			$value['email_body'] = str_replace("{{collection_name}} match",$fixture_info[0]['collection_name'],$value['email_body']);
			$value['message_body'] = str_replace("{{MINUTES}}",$fixture_info[0]['delay_minute'],$value['message_body']);
			$value['message_body'] = str_replace("{{collection_name}}",$fixture_info[0]['collection_name'],$value['message_body']);

			$converted_date = get_timezone(strtotime($fixture_info[0]['season_scheduled_date']),'Y-m-d H:i:s',$this->app_config['timezone'],2);
			$contest_date = $converted_date['date'].' '.'('.$converted_date['tz'].')';
			$value['message_body'] = str_replace("{{season_scheduled_date}}",$contest_date,$value['message_body']);
			$details_for_sms['fixture_info']	= $fixture_info;
			$value['email_body'] = str_replace("{{season_scheduled_date}}",$contest_date,$value['email_body']);
			}
			
			if($post_data['template_id']==4){
			$value['message_body'] = str_replace("{{home}}",$fixture_details['home'],$value['message_body']);
			$value['message_body'] = str_replace("{{away}}",$fixture_details['away'],$value['message_body']);
			$value['message_body'] = str_replace("{{season_scheduled_date}}",$fixture_details['season_scheduled_date'],$value['message_body']);
			$value['email_body'] = str_replace("{{home}}",$fixture_details['home'],$value['email_body']);
			$value['email_body'] = str_replace("{{home}}",$fixture_details['home'],$value['email_body']);
			$value['email_body'] = str_replace("{{home_flag}}",$fixture_details['home_flag'],$value['email_body']);
			$value['email_body'] = str_replace("{{away_flag}}",$fixture_details['away_flag'],$value['email_body']);
			$value['email_body'] = str_replace("{{away}}",$fixture_details['away'],$value['email_body']);

			$converted_date = get_timezone(strtotime($fixture_details['season_scheduled_date']),'Y-m-d H:i:s',$this->app_config['timezone'],2);
			$contest_date = $converted_date['date'].' '.'('.$converted_date['tz'].')';
			$value['email_body'] = str_replace("{{season_scheduled_date}}",$contest_date,$value['email_body']);
			$value['email_body'] = str_replace("{{WEBSITE_URL}}",$contest_url,$value['email_body']);
			}
			
			}

			if(!empty($post_data['contest_id']))
			{
				$this->load->model('contest/Contest_model');
				$contest_data = $this->Contest_model->contest_promotion_detail($post_data['contest_id']);
				$value['email_body'] = str_replace("{{contest_name}}",$contest_data['contest_name'],$value['email_body']);
				$value['message_body'] = str_replace("{{contest_name}}",$contest_data['contest_name'],$value['message_body']);
				$value['message_body'] = str_replace("{{collection_name}}",$contest_data['collection_name'],$value['message_body']);

				$details_for_sms['contest_data']	= $contest_data;
				$sports_name = strtolower($contest_data['sports_name']);
				$contest_url = WEBSITE_URL.$sports_name.'/contest/'.$contest_data['contest_unique_id'];

				
				$value['email_body'] = str_replace("{{CONTEST_URL}}",$contest_url,$value['email_body']);
			}

			$value['email_body'] = str_replace("{{BUCKET_URL}}",IMAGE_PATH,$value['email_body']);
			$value['email_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$value['email_body']);
			$value['email_body'] = str_replace("{{WEBSITE_URL}}",WEBSITE_URL,$value['email_body']);
			$value['email_body'] = str_replace("{{SITE_URL}}",WEBSITE_URL,$value['email_body']);
			$value['email_body'] = str_replace("{{WEBSITE_DOMAIN}}",WEBSITE_DOMAIN,$value['email_body']);
			$value['email_body'] = str_replace("{{year}}",date('Y'),$value['email_body']);
			$value['email_body'] = str_replace("{{CURRENCY_CODE_HTML}}",CURRENCY_CODE_HTML,$value['email_body']);
			$value['email_body'] = str_replace("{{user_name}}","Demo",$value['email_body']);
			if(isset($fixture_info)){
				$value['email_body'] = str_replace("{{MINUTES}}",$fixture_info[0]['delay_minute'],$value['email_body']);
			}			
			
			$value['email_body'] = str_replace("{{FB_LINK}}",FB_LINK,$value['email_body']);
			$value['email_body'] = str_replace("{{TWITTER_LINK}}",TWITTER_LINK,$value['email_body']);
			$value['email_body'] = str_replace("{{INSTAGRAM_LINK}}",INSTAGRAM_LINK,$value['email_body']);
			$value['email_body'] = str_replace("{{app_name}}",SITE_TITLE,$value['email_body']);
			$value['email_body'] = str_replace("{{FRONTEND_BITLY_URL}}",WEBSITE_URL,$value['email_body']);
			$value['email_body'] = str_replace('href="<?php echo ANDROID_APP_LINK; ?>"','href="'.ANDROID_APP_LINK.'"',$value['email_body']);
			$value['email_body'] = str_replace('href="<?php echo IOS_APP_LINK; ?>"','href="'.IOS_APP_LINK.'"',$value['email_body']);

			if(isset($post_data['template_id']) && in_array($post_data['template_id'],[3,4,11,16]))
				{
						$cd_footer = $this->load->view('communication_dashboard/cd_footer','', true);
						$value['email_body'] = str_replace("{{Footer}}",$cd_footer,$value['email_body']);
				} 
			// $value['message_body'] = str_replace("{{Date}}",format_date('today','Y-m-d'),$value['message_body']);
			$value['message_body'] = str_replace("{{SITE_TITLE}}",SITE_TITLE,$value['message_body']);
			if($post_data['template_id']==1 || $post_data['template_id']==2)
			{
				$value['message_url'] = str_replace("{{FRONTEND_BITLY_URL}}",' Visit: '.WEBSITE_URL.'add-funds',$value['message_url']);
			}
			elseif($post_data['template_id']==3)
			{ 
				$refer_row= $this->User_segmentation_model->get_single_row('bonus_amount,real_amount,coin_amount',AFFILIATE_MASTER,array('affiliate_type' => 1));
				$benifit = "";
				$bonus_amount = ($refer_row['bonus_amount'] > 0) ? $refer_row['bonus_amount']." Bonus Cash, ":""; 
				$real_amount = ($refer_row['real_amount'] > 0) ? $refer_row['real_amount']." Real Cash, ":""; 
				$coin_amount = ($refer_row['coin_amount'] > 0) ? $refer_row['coin_amount']." Coins":""; 
				if($this->app_config['int_version']['key_value']==1){
					$benifit = $coin_amount;
				}
				else{
					$benifit = $bonus_amount.$real_amount.$coin_amount;
				}
				$value['email_body'] = str_replace("{{referral_benifits}}",$benifit,$value['email_body']);
				$value['message_body'] = str_replace("{{amount}}",$refer_row['bonus_amount'],$value['message_body']);
				$value['message_url'] = str_replace("{{FRONTEND_BITLY_URL}}",' Visit: '.WEBSITE_URL.'refer-friend',$value['message_url']);

				$details_for_sms['refer_row']	= $refer_row;
			}
			elseif($post_data['template_id']==11 || $post_data['template_id']==4){
				$value['message_url'] = str_replace("{{FRONTEND_BITLY_URL}}",' Visit: '.$contest_url,$value['message_url']);
			}
			elseif($post_data['template_id']==17)
			{
				$value['message_url'] = str_replace("{{FRONTEND_BITLY_URL}}",' Visit: '.WEBSITE_URL.'lobby#sports',$value['message_url']);
			}
			elseif($post_data['template_id']==13)
			{
				$value['message_url'] = str_replace("{{FRONTEND_BITLY_URL}}",' Visit: '.WEBSITE_URL.'lobby#rewards',$value['message_url']);
			}
			

			if(!empty($value['cd_email_template_id']) && ($value['cd_email_template_id'] == 1 || $value['cd_email_template_id'] == 2))
			{ 
				$refer_row= $this->User_segmentation_model->get_promocode_detail($post_data['promo_code_id']);
				// print_r($refer_row);exit;
				$value['email_body'] = str_replace("{{promo_code}}",$refer_row['promo_code'],$value['email_body']);
				$value['email_body'] = str_replace("{{offer_percentage}}",$refer_row['discount'],$value['email_body']);
				$value['message_body'] = str_replace("{{promo_code}}",$refer_row['promo_code'],$value['message_body']);
				$value['message_body'] = str_replace("{{offer_percentage}}",$refer_row['discount'],$value['message_body']);

				$details_for_sms['promocode_row']	= $refer_row;
			}

			if(!empty($post_data['category_id']) && $post_data['category_id']==14 && $value['template_name'] == 'deal_template')
			{
				$deal_info = $this->User_segmentation_model->get_single_row('*',DEALS,array('deal_id' =>$post_data['deal_id'],'status'=>'1'));
				$deal_benifit=0;
				$bonus_amount = ($deal_info['bonus'] > 0) ? $deal_info['bonus']." bonus cash, ":""; 
				$real_amount = ($deal_info['cash'] > 0) ? $deal_info['cash']." real cash, ":""; 
				$coin_amount = ($deal_info['coin'] > 0  && $this->app_config['allow_coin_system']['key_value']==1) ? $deal_info['coin']." coins  ":""; 
				if($this->app_config['int_version']['key_value']==1){
					$deal_benifit = $coin_amount;
				}
				else{
					$deal_benifit = $bonus_amount.$real_amount.$coin_amount;
				}
				$deal_benifit = substr($deal_benifit,0,-2);
				
				if($deal_info)
				{
					$value['message_body'] = str_replace("{{amount}}",$deal_info['amount'],$value['message_body']);
					$value['message_body'] = str_replace("{{deal_benifit}}",$deal_benifit,$value['message_body']);
				}
				
			}

			if(!empty($post_data['category_id']) && $post_data['category_id']==15 && in_array($value['template_name'] , ['new_promocode_template','contest_join_promocode','deposit_promocode','deposit_range_promocode','first_deposit_promocode']))
			{
				$promocode_info = $this->User_segmentation_model->get_single_row('*',PROMO_CODE,array('promo_code_id' =>$post_data['promo_code_id'],'status'=>'1'));
				$prmocode_benifit = 0;
				$benefit_cap = '.';
				$cash_type = '';
				$deposit_range = '';
				

				if($promocode_info['cash_type']==0)
				{
					$cash_type="bonus cash";
				}
				elseif($promocode_info['cash_type']==1)
				{
					// $cash_type = $this->app_config['currency_code']['key_value'];
					$cash_type = 'real cash';
				}

				if($promocode_info['value_type']==0)
				{
					$prmocode_benifit = $promocode_info['discount'].' '.$cash_type;
				}
				elseif($promocode_info['value_type']==1)
				{
					$prmocode_benifit = $promocode_info['discount'].'% '.$cash_type;
				}

				if($promocode_info['value_type']==1 && $promocode_info['benefit_cap']>0)
				{
					$benefit_cap = " max upto ".$promocode_info['benefit_cap'].' '.$cash_type.'.';
				}

				if($value['template_name']=='deposit_range_promocode' && $promocode_info['min_amount']!=null && $promocode_info['max_amount']!=null && $promocode_info['min_amount']!=0 && $promocode_info['max_amount']!=0)
				{
					$deposit_range = ' on '.$this->app_config['currency_code']['key_value'].$promocode_info['min_amount'].'-'.$this->app_config['currency_code']['key_value'].$promocode_info['max_amount'];
				}

				if($promocode_info)
				{
					$value['message_body'] = str_replace("{{new_promocode}}",$promocode_info['promo_code'],$value['message_body']);
					$value['message_body'] = str_replace("{{promocode_benifit}}",$prmocode_benifit,$value['message_body']);
					$value['message_body'] = str_replace("{{capping_text}}",$benefit_cap,$value['message_body']);
					$value['message_body'] = str_replace("{{deposit_range}}",$deposit_range,$value['message_body']);
				}
				
			}
		}

		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data'] = $template;
		$this->api_response();
	}

	public function create_user_base_list_post(){
		
		$this->form_validation->set_rules('list_name',"List Name",'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}      
		$post_data = $this->input->post();
		$list_param=array();
		$list_param =$this->prepare_list($post_data);
		$list_param['added_date']=format_date();
		
		// $list_param['count']=$count;
		// print_r($list_param);exit;
		$list = $this->User_segmentation_model->create_user_base_list($list_param);
		
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['message']  		  = 'Userbase list created successfully';
		$this->api_response_arry['data']  		  = $list;
		$this->api_response();
	}

	public function get_all_contest_post(){

		$post_data = $this->input->post();
		$sports_id='';
		if(!empty($post_data['sports_id'])){
			$sports_id = $post_data['sports_id'];
		}
		$contests = $this->User_segmentation_model->get_all_contest($sports_id);
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data']  		  = $contests;
		$this->api_response();	
	}

	function get_email_body_post()
	{
		$this->form_validation->set_rules('list_name',"List Name",'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}      
		$post_data = $this->input->post();
	}
	public function get_fixture_users($season_game_uid='')
	{
		if(empty($season_game_uid))
		{
			return array();
		}

		$user_id_arr = array();
		$this->load->model('contest/Contest_model');
		$data = $this->Contest_model->get_fixture_users($season_game_uid);
		$user_id = $this->Contest_model->get_fixture_users($season_game_uid);
		$this->load->model('New_campaign_model');
		$user_id_arr['user_ids']=array_column($user_id, 'user_id');
		$data  = $this->New_campaign_model->filter_system_users($user_id_arr);
		$data = $data['user_ids'];
		return $data;
	}

	public function get_scheduled_data_post()
	{
		$this->form_validation->set_rules('recent_communication_id',"Recent Communication ID",'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$post_data = $this->input->post();
		$result = $this->User_segmentation_model->get_scheduled_data($post_data);
		// print_r($result);exit;
		$result = json_decode($result['custom_data'],true);
		if($result)
		{
		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
		$this->api_response_arry['data'] = $result['req_data'];
		$this->api_response();
		}
		$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['global_error']  		  = 'No data found';
		$this->api_response();
	}


}