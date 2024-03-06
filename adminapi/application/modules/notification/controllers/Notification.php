<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends MYREST_Controller {

	public function __construct()
	{
		parent::__construct();
		$_POST = $this->input->post();
		$this->load->model('user/User_model');		
		//Do your magic here
	}

	public function search_user_post()
	{
		$post_target_url         = 'invite/search_user';
		$post             = $this->input->post();
		$post_params['text'] = $post['search_key'];
		$post_api_response       = $this->http_post_request($post_target_url,$post_params,3);

		$user_list = array();
		if(!empty($post_api_response["data"]))
		{
			foreach ($post_api_response["data"] as $key => $value) {
				# code...
				$name = $value['first_name']. " ".$value["last_name"];
				$user_list[] = array(
					"id"=>$value['user_id'],
					"text"=>(
						(isset($name) && $name != "")?$name:(
						 (isset($value["user_name"]) && $value["user_name"] != "" )? $value["user_name"]:((isset($value["email"]) && $value["email"] != "")?$value["email"]:"" ) ) )
					);
			}
			$post_api_response["data"] = $user_list;

		}

		$this->api_response_arry = $post_api_response;
		$this->api_response();
	}

	public function get_all_contest_by_match_post()
	{
		$this->form_validation->set_rules('season_game_uid', 'season game uid', 'trim|required');
		$this->form_validation->set_rules('league_id', 'league id', 'trim');
        if (!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }

        $post_target_url         		= 'contest/get_all_contest_by_season';
        $post_params['season_game_uid'] = $this->input->post("season_game_uid");
        $post_params['league_id'] 		= $this->input->post("league_id");
        $post_params['is_admin'] 		= 1;

		$post_api_response       = $this->http_post_request($post_target_url,$post_params,2,1);
		$this->api_response_arry = $post_api_response;
		$this->api_response();


	}



	/**
	*@method send_notification_post
	*@uses function to send notification
	*/
	public function send_notification_post()
	{
		$selectOption = $this->input->post('selectOption'); // 0-selected users, 1-all users, 2-contest user

		if(!isset($selectOption))
        {
        	$selectOption = 0;
        }

		if(!isset($selectOption) || $selectOption == 0)
		{
			$this->form_validation->set_rules('users', 'users', 'trim|required');
		}

		if($selectOption == 2)
		{
			$this->form_validation->set_rules('sports_id', 'sports id', 'trim|required');
			$this->form_validation->set_rules('league_id', 'league id', 'trim|required');
			$this->form_validation->set_rules('collection_master_id', 'collection master id', 'trim|required');
			$this->form_validation->set_rules('contest_unique_id', 'contest unique id', 'trim|required');
			
		}

		$this->form_validation->set_rules('notification_text', 'notification text', 'trim|required');
		 
		 if (!$this->form_validation->run())
        {
            $this->send_validation_errors();
        }


        $post_data = $this->input->post();

        $notification_data = array();
        

        if($selectOption == 0)
        {
        	$user_ids = explode(",", $post_data['users']);

        }
        else if($selectOption == 1)
        {
        	//get all users
        	$result = $this->User_model->get_all_data("user_id",USER,array("status" => '1'));
	        $user_ids = array_column($result, "user_id");
	        $user_ids = array_values($user_ids);

        }
        else if($selectOption == 2)
        {
        	//get selected user ids
    		$post_game_para["contest_unique_id"]  = $post_data['contest_unique_id'];//fantasy
    		$post_game_para["collection_master_id"]  = $post_data['collection_master_id'];//fantasy
				//withdrawal request
			$post_user_url      = 'contest/get_contest_all_user_ids';
			$post_user_response = $this->http_post_request($post_user_url,$post_game_para,2);


			if(!empty($post_user_response['data']))
			{
				$user_ids = $post_user_response['data'];
				$user_ids = array_column($user_ids,'user_id');	
			}				

        }
        $temp_users = array();
        
        if(!empty($user_ids))
       		$temp_users = array_chunk($user_ids, 999);
	    $this->load->model('Notification_model');
	    foreach($temp_users as $users_id)
	    {
			$device_ids = $this->Notification_model->get_all_device_id($users_id);
			//echo '<pre>';print_r($device_ids);die;
			
			if(!empty($device_ids))
			{
				$this->send_notification($device_ids,$post_data['notification_text']);
			}

			//add for web
			$notification_batch =array();
			foreach ($users_id as $key => $value) 
			{
				$tmp = array();
				$input = array(
					'subject' => "Admin notification",
					'message' => $post_data['notification_text'] 
				);
				$content = json_encode($input);
				$tmp["notification_type"]        = 19; // admin notification
				$tmp["source_id"]                = 1;//dummy
				$tmp["notification_destination"] = 1; //web
				$tmp["notification_status"]      = 1;
				$tmp["user_id"]                  = $value;
				$tmp["added_date"]               = date("Y-m-d H:i:s");
				$tmp["modified_date"]            = date("Y-m-d H:i:s");
				$tmp["content"]                  = $content;
				$notification_batch[] = $tmp;
			}

			if(!empty($notification_batch))
			{
				$this->db->insert_batch( NOTIFICATION , $notification_batch );
			}

	    }
	 

    	$this->api_response_arry['message'] = "Notification sent successfully.";
    	$this->api_response_arry['response_code'] = 200;
		$this->api_response();
			
		 
	}
	 
	private function send_notification($device_ids,$message)
	{
		$dummy_payload = array();
		$target = array_unique($device_ids);

		$fields = array();
		$fields['data'] = $dummy_payload;
		$fields['data']['body'] = $message;
		$this->load->library("fcm_notification");
		$resp = $this->fcm_notification->send_fcm_message($fields,$target);
	}
}
