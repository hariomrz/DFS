<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Nw_systemuser extends MYREST_Controller {
	public $bot_config = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Nw_systemuser_model','Systemuser_model');
		$this->admin_roles_manage($this->admin_id,'user_management');

		$this->bot_config = $this->pl_bot_config_details();
	}

	/**
     * function used for get system users with pagination and filter
     * @param array $post_data
     * @return array
     */
	public function get_users_post()
	{
		$result = $this->Systemuser_model->get_all_system_users();
		$this->api_response_arry['response_code'] = 200;
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}


	/**
	 * function to upload a csv file to add system users
	 * it will accept a .csv file
	 */

	 public function upload_systemuser_post(){
		if(!isset($_FILES)){
			$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message'] = 'Sorry, this username already exist.';
			$this->api_response();
		} 
		$tmp_name = $size = $type = '';
		$data = array();
		$skipped = array();
		$tmp_name = $_FILES['file']['tmp_name'];
        $size = $_FILES['file']['size'];
        $type = $_FILES['file']['type'];
        if(size <= 5242880 ){
            $this->load->library('Csvimport');
            $file_data = $this->csvimport->get_array($_FILES["file"]["tmp_name"]);
            // echo "<pre>";print_r($file_data);exit;
                foreach($file_data as $row)
                {
					$check_username =  $this->Systemuser_model->get_single_row("user_name",USER,array("LOWER(user_name)"=>strtolower($row["username"])));
					if(!empty($check_username)){
						$skipped[]= $row["username"];
					}
					else{
						$user_unique_id = $this->Systemuser_model->_generate_key();
						// $amount = number_format(0,2,".","");
						$image = 'avatar'.rand(1,10).'.png';
						$ref_code = $this->Systemuser_model->_generate_referral_code();
						$data[] = array(
							'user_unique_id' => $user_unique_id,
							'user_name' => $row["username"],
							'added_date'     => date('Y-m-d H:i:s'),
							'modified_date'  => date('Y-m-d H:i:s'),
							'status' => '1',
							'is_systemuser' => '1',
							'image' => $image,
							'referral_code'=>$ref_code
						);
					}
                }
                // echo "<pre>";print_r($data);exit;
				$user_id = $this->Systemuser_model->import_system_users($data);
				if($user_id)
				{
					$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
					$this->api_response_arry['message'] = 'system user added successfully.';
					$this->api_response_arry['data']['skipped'] = $skipped;
					// $this->api_response_arry['data']['inserted'] = $user_id;
					$this->api_response();
				}else{
					$this->api_response_arry['response_code']	= 500;
					$this->api_response_arry['message']			= 'The system user already existed, try with different usernames';
					$this->api_response();
				}
        }
	 }
	/**
     * function used for create new system user
     * @param array $post_data
     * @return array
     */
	public function create_user_post()
	{
		$this->form_validation->set_rules('user_name', 'user name', 'trim|required');
		//$this->form_validation->set_rules('balance', 'balance', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$user_name = trim($data_post['user_name']);
		$user_unique_id = $this->Systemuser_model->_generate_key();
		$amount = number_format($data_post['balance'],2,".","");
	  	$check_username =  $this->Systemuser_model->get_single_row("user_name",USER,array("LOWER(user_name)"=>strtolower($user_name)));
	  	if(!empty($check_username)){
	  		$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message'] = 'Sorry, this username already exist.';
			$this->api_response();
	  	}

		$data = array();
		$data["user_unique_id"] = $user_unique_id;
		$data["user_name"] = $user_name;
		$data['added_date']     = date('Y-m-d H:i:s');
		$data['modified_date']  = date('Y-m-d H:i:s');
		$data['status'] = '1';
		$data['is_systemuser'] = '1';
		if (!empty($data_post['image'])) {
            $data['image'] = $data_post['image'];
        }
		$user_id = $this->Systemuser_model->registration($data);
		if($user_id)
		{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = 'system user added successfully.';
			$this->api_response();
		}else{
			$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message']			= 'Error';
			$this->api_response();
		}
	}

	/**
     * function used for create new system user
     * @param array $post_data
     * @return array
     */
	public function update_user_post()
	{
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		$this->form_validation->set_rules('user_name', 'user name', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$user_name = trim($data_post['user_name']);
		$check_username =  $this->Systemuser_model->get_single_row("user_id,user_name",USER,array("LOWER(user_name)"=>strtolower($user_name)));
	  	if(!empty($check_username) && $check_username['user_id'] != $data_post['user_id']){
	  		$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message'] = 'Sorry, this username already exist.';
			$this->api_response();
	  	}

		$data = array();
		$data["user_name"] = $user_name;
		$data['modified_date'] = date('Y-m-d H:i:s');
		if (!empty($data_post['image'])) {
            $data['image'] = $data_post['image'];
        }
		$result = $this->Systemuser_model->update_user($data,$data_post['user_id']);
		if($result)
		{
			$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = 'system user updated successfully.';
			$this->api_response();
		}else{
			$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message']			= 'Error';
			$this->api_response();
		}
	}

	/**
     * function used for remove system user profile picture
     * @param array $post_data
     * @return array
     */
	public function remove_profile_image_post()
	{
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$user_info =  $this->Systemuser_model->get_single_row("user_id,image",USER,array("user_id"=>$data_post['user_id']));
	  	if(!empty($user_info) && $user_info['image'] != ""){
	  		$file_name = $user_info['image'];
	  		$image_path = PROFILE_IMAGE_UPLOAD_PATH.$file_name;
	  		$thumb_path = PROFILE_IMAGE_THUMB_PATH . $file_name;
	  		$data = array("image"=>"");
			$result = $this->Systemuser_model->update_user($data,$data_post['user_id']);
			if($result){
		  		@unlink($image_path);
		  		@unlink($thumb_path);

		  		$this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
				$this->api_response_arry['message'] = 'User profile picture removed successfully.';
				$this->api_response();
		  	}else{
		  		$this->api_response_arry['response_code']	= 500;
				$this->api_response_arry['message']			= 'Something went wrong in remove image.';
				$this->api_response();
		  	}
	  	}else{
	  		$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message']			= 'User image detail not found.';
			$this->api_response();
	  	}
	}

	/**
     * function used for create new system user
     * @param array $post_data
     * @return array
     */
	public function delete_user_post()
	{
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$check_user = $this->Systemuser_model->check_for_delete($data_post['user_id']);
	  	if(isset($check_user['total']) && $check_user['total'] > 0){
	  		$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message'] = "Sorry, you can't delete this user because of user already joined contest.";
			$this->api_response();
	  	}

		$result = $this->Systemuser_model->delete_user($data_post['user_id']);
		if($result)
		{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = 'system user deleted successfully.';
			$this->api_response();
		}else{
			$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message']			= 'Error';
			$this->api_response();
		}
	}

	/**
     * function used for add balance in system user account
     * @param array $post_data
     * @return array
     */
	public function add_balance_post()
	{
		$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
		$this->form_validation->set_rules('balance', 'balance', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}

	  	$data_post = $this->post();
	  	$amount = number_format($data_post['balance'],2,".","");
		$order_object = array();
		$order_object['user_id'] = $data_post['user_id'];
		$order_object['amount'] = $amount;
		$order_object['cash_type']  = 0;
        $order_object['source']		= 0;
        $order_object['source_id']	= 0;
        $order_object['plateform']	= 1;
        $this->load->model('userfinance/Userfinance_model');
        $order_id = $this->Userfinance_model->deposit_fund($order_object);
		if($order_id)
		{
            $this->api_response_arry['response_code'] = rest_controller::HTTP_OK;
			$this->api_response_arry['message'] = 'Balance added successfully.';
			$this->api_response();
		}else{
			$this->api_response_arry['response_code']	= 500;
			$this->api_response_arry['message']			= 'Error';
			$this->api_response();
		}
	}

	/**
     * function used for contest details for add bots
     * @param array $post_data
     * @return array
     */
	public function get_contest_detail_post()
	{
		$this->form_validation->set_rules('contest_unique_id', 'contestunique id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr = $this->input->post();
		//$contest_detail = $this->Systemuser_model->get_contest_detail($data_arr);
		$post_data['contest_unique_id'] = $data_arr['contest_unique_id'];
		$post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_contest_detail";
        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_contest_detail";
        $api_response =  $this->http_post_request($url,$post_data,3);
        //echo "<pre>";print_r($api_response);die;
        $contest_detail = $api_response['data']['contest_detail'];
		if(!empty($contest_detail)){
			$contest_detail['prize_distibution_detail'] = $api_response['data']['contest_detail']['prize_distibution_detail'];
		}
		$this->api_response_arry['data']['max_system_user'] = $this->pl_bot_config_details('contest_limit');
		$this->api_response_arry['data']['system_user_deadline'] = SYSTEM_USER_CONTEST_DEADLINE;
		$this->api_response_arry['data']['system_user_request_limit'] = SYSTEM_USER_REQUEST_LIMIT;
		$this->api_response_arry['data']['contest_detail'] = $contest_detail;
		$this->api_response_arry['response_code']	= rest_controller::HTTP_OK;
		$this->api_response();
	}

	/**
     * function used for get users list for add in contest
     * @param array $post_data
     * @return array
     */
	public function get_system_users_for_contest_post(){
		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		$this->form_validation->set_rules('multiple_lineup', 'multiple lineup', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr = $this->input->post();

		$user_list = $this->Systemuser_model->get_system_users_list();

		//$contest_user_list = $this->Systemuser_model->get_system_users_for_contest($data_arr);
		$post_data['contest_id'] = $data_arr['contest_id'];
		$post_data['multiple_lineup'] = $data_arr['multiple_lineup'];
		$post_data['client_id'] = NETWORK_CLIENT_ID;
		//echo "dfd<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_system_users_for_contest";
        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_system_users_for_contest";
        
        $api_response =  $this->http_post_request($url,$post_data,3);
        $contest_user_list = $api_response['data'];
		//echo "dfd<pre>";print_r($contest_user_list);die;
		$contest_user_list = array_column($contest_user_list,NULL,"user_id");
		$final_list = array();
		foreach($user_list as $user){
			$team_count = 0;
			if(isset($contest_user_list[$user['user_id']])){
				$team_count = $contest_user_list[$user['user_id']]['team_count'];
			}
			$user['team_count'] = $team_count;
			if($team_count < $data_arr['multiple_lineup']){
				$final_list[] = $user;
			}
		}

		$this->api_response_arry['data'] = $final_list;
		$this->api_response();
	}

	/**
     * function used for get contest joined users list with team count
     * @param array $post_data
     * @return array
     */
	public function get_contest_joined_system_users_post(){
		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr = $this->input->post();
		//$contest_user_list = $this->Systemuser_model->get_system_users_for_contest($data_arr);
		$post_data['contest_id'] = $data_arr['contest_id'];
		$post_data['client_id'] = NETWORK_CLIENT_ID;
		//echo "dfd<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_system_users_for_contest";
        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_system_users_for_contest";
        
        $api_response =  $this->http_post_request($url,$post_data,3);
        //echo "<pre>";print_r($api_response);die;
		$user_list = array();
        $contest_user_list = $api_response['data'];

		if(!empty($contest_user_list)){
			$data_arr['user_ids'] = array_column($contest_user_list,"user_id");
			$user_list = $this->Systemuser_model->get_system_users_list($data_arr);

		}
		$user_list = array_column($user_list,NULL,"user_id");
		$final_list = array();
		foreach($contest_user_list as $user){
			$user = array_merge($user,$user_list[$user['user_id']]);
			$final_list[] = $user;
		}

		$this->api_response_arry['data'] = $final_list;
		$this->api_response();
	}

	public function validate_match_with_pl($season_game_uid){
		$team_data = array();
        $team_data['website_id'] = PL_WEBSITE_ID;
        $team_data['season_game_uid'] = $season_game_uid;
        $api_url = PL_WEBSITE_API."/api/system-teams-test";
        $post_data_json = json_encode($team_data);
        $header = array("Content-Type:application/json", "Accept:application/json","token:".PL_WEBSITE_TOKEN);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (ENVIRONMENT !== 'production'){
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output, true);
        if(isset($result['test_status'])){
        	return $result['test_status'];
        }else{
        	return false;
        }
	}

	/**
     * function used for get contest joined users list with team count
     * @param array $post_data
     * @return array
     */
	public function join_system_users_post(){
		
		if(!in_array(NETWORK_CLIENT_ID, array(1,5,8,24)))
		{
			exit('No access allowed');
		}	

		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$this->message = "";
		$current_date = format_date();
		$data_arr = $this->input->post();
		$contest_id = $data_arr['contest_id'];
		if(empty($data_arr['user_list'])){
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Please select atleast 1 user.';
			$this->api_response();
		}
		$data_arr['user_list'] = array_column($data_arr['user_list'],NULL,"user_id");
		//echo "<pre>";print_r($data_arr);die;
		//$contest_detail = $this->Systemuser_model->get_contest_detail($data_arr);
		$post_data['contest_id'] = $contest_id;
		$post_data['client_id'] = NETWORK_CLIENT_ID;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_contest_detail";
        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_contest_detail";
        $api_response =  $this->http_post_request($url,$post_data,3);
        //echo "<pre>";print_r($api_response);die;
        $contest_detail = $api_response['data']['contest_detail'];
        //echo "<pre>";print_r($contest_detail);die;
		if(empty($contest_detail) || !isset($contest_detail['playing_eleven_confirm']) || $contest_detail['playing_eleven_confirm'] == 0){
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Sorry, system users not allowed for this fixture.';
			$this->api_response();
		}

		$total_team_request = 0;
		if(!empty($data_arr['user_list'])){
			$total_team_request = array_sum(array_column($data_arr['user_list'], "team_count"));
		}
		if($total_team_request > SYSTEM_USER_REQUEST_LIMIT){
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Total '.SYSTEM_USER_REQUEST_LIMIT.' teams allowed in single request!';
			$this->api_response();
		}

		$input_arr = array();
		$input_arr['sports_id'] = $contest_detail['sports_id'];
	
		$network_client_map = $this->Systemuser_model->check_network_collection_master_id($data_arr);
       //echo "<pre>";print_r($network_client_map);die;
       	$input_arr['contest_id'] = $contest_detail['contest_id'];
		//$input_arr['league_id'] = $contest_detail['league_id'];
		$input_arr['league_id'] = $network_client_map['league_id'];
		$input_arr['season_game_uid'] = $contest_detail['season_game_uid'];
		$input_arr['total_team_request'] = $total_team_request;
		//echo "<pre>";print_r($input_arr);die;
		$team_result = $this->Systemuser_model->generate_match_pl_team($input_arr);
		//echo "<pre>";print_r($team_result);die;
		if(!$team_result || empty($team_result)){
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Sorry, Team not generated. please contact technical team.';
			$this->api_response();
			exit();
		}
		//echo "<pre>";print_r($team_result);die;
		$collection_master_id = $contest_detail['collection_master_id'];
		$team_list = $team_result['lineups'];
		$player_list = array_column($team_result['player_list'],NULL,"player_uid");
		$position_post_array = array_column($team_result['position'], 'master_lineup_position_id','position_name');
		//echo "<pre>";print_r($team_list);die;

		$is_valid = $this->validation_for_join_game($contest_detail);
        if ($is_valid) {
        	$this->load->model("auth/Auth_nosql_model");
        	$total_teams = 0;
			if(!empty($data_arr['user_list'])){
				$total_teams = array_column($data_arr['user_list'], "team_count","user_id");
				$total_teams = array_sum($total_teams);
			}
			if($total_teams > ($contest_detail['size'] - $contest_detail['total_user_joined'])){
				$this->api_response_arry['response_code'] = 500;
				$this->api_response_arry['message'] = 'Number of system users you are trying to add, is more than available entries in this contest!';
				$this->api_response();
			}

			//$contest_user_list = $this->Systemuser_model->get_system_users_for_contest($data_arr);
			$post_data['contest_id'] = $data_arr['contest_id'];
			$post_data['client_id'] = NETWORK_CLIENT_ID;
			//echo "dfd<pre>";print_r($post_data);die;
	        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_system_users_for_contest";
	        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_system_users_for_contest";
	        $api_response =  $this->http_post_request($url,$post_data,3);
	        $contest_user_list = $api_response['data'];

			$total_bot_joined = 0;
			if(!empty($contest_user_list)){
				$total_bot_joined = array_sum(array_column($contest_user_list, "team_count"));
			}
			$total_joined_request = array_sum(array_column($data_arr['user_list'], "team_count"));
			$tmp_total = $total_bot_joined + $total_joined_request;
			$system_user_contest_limit= $this->bot_config['contest_limit'];
			if($tmp_total > $system_user_contest_limit){
				$this->api_response_arry['response_code'] = 500;
				$this->api_response_arry['message'] = 'Max '.$system_user_contest_limit." system users allowed per contest.";
				$this->api_response();
			}

			//fixture wise allowed count validation
			//$fixture_teams = $this->Systemuser_model->get_fixture_system_users_count($contest_detail['collection_master_id']);
			$post_data['collection_master_id'] = $contest_detail['collection_master_id'];
			$post_data['client_id'] = NETWORK_CLIENT_ID;
	        //echo "<pre>";print_r($post_data);die;
	        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_fixture_system_users_count";
	        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_fixture_system_users_count";
	        $api_response =  $this->http_post_request($url,$post_data,3);
	        //echo "<pre>";print_r($api_response);die;
	        $fixture_teams = $api_response['data'];
			$fixture_count = 0;
			$user_fixture_team = array();
			if(!empty($fixture_teams)){
				$fixture_count = array_sum(array_column($fixture_teams, "team_count"));
				$user_fixture_team = array_column($fixture_teams, "team_count","user_id");
			}
			$tmp_fixture_count = $fixture_count + $total_joined_request;
			$system_user_match_limit = $this->bot_config['match_limit'];
			if($tmp_fixture_count > $system_user_match_limit){
				$this->api_response_arry['response_code'] = 500;
				$this->api_response_arry['message'] = 'Max '.$system_user_match_limit." system users allowed per match.";
				$this->api_response();
			}

			$user_ids = array_column($contest_user_list, "team_count","user_id");
			$lineup_master_data = array();
			$is_valid = 1;
			$error_msg = array();
			$fixture_error_msg = array();
			$current_date = format_date();
			$nw_post_data_arr['collection_master_id'] = $contest_detail['collection_master_id'];
			$nw_collection_detail = $this->Systemuser_model->check_network_collection_master_id($nw_post_data_arr);	
			//echo "<pre>";print_r($nw_collection_detail);die;	
			foreach($data_arr['user_list'] as $user){
				$user_team_count = $user_fixture_team_count = $user['team_count'];
				if(isset($user_ids[$user['user_id']])){
					$user_team_count = $user_team_count + $user_ids[$user['user_id']];
				}
				if(isset($user_fixture_team[$user['user_id']])){
					$user_fixture_team_count = $user_fixture_team_count + $user_fixture_team[$user['user_id']];
				}
				if($user_team_count > $contest_detail['multiple_lineup']){
					$is_valid = 0;
					$error_msg[] = "(".$user['user_name'].")";
				}else if($user_fixture_team_count > 10){
					$is_valid = 0;
					$fixture_error_msg[] = "(".$user['user_name'].")";
				}else{
					for($i=1;$i<=$user['team_count'];$i++)
					{
						$team_name = "Team ".$i;
						if(isset($user_ids[$user['user_id']])){
							$tcount = $user_ids[$user['user_id']] + $i;
							$team_name = "Team ".$tcount;
						}
						
						$tmp_arr = array();
					
						$tmp_arr['nw_collection_master_id'] = $contest_detail['collection_master_id'];
						$tmp_arr['nw_league_id'] = $contest_detail['league_id'];
						//for client site detail;s
						$tmp_arr['collection_master_id'] = $nw_collection_detail['collection_master_id'];
						$tmp_arr['league_id'] = $nw_collection_detail['league_id'];

						$tmp_arr['user_id'] = $user['user_id'];
						$tmp_arr['user_name'] = $user['user_name'];
						$tmp_arr['team_name'] = $team_name;
						$tmp_arr['date_added'] = $current_date;
						$tmp_arr['date_modified'] = $current_date;
						$tmp_arr['team_data'] = json_encode(array());
						$tmp_arr['is_systemuser'] = 1;
						$lineup_master_data[] = $tmp_arr;
					}
				}
			}

			//echo "<pre>";print_r($lineup_master_data);die;
			if($is_valid == 0 && !empty($error_msg)){
				$msg = "Maximum lineup count (".$contest_detail['multiple_lineup'].") exceeded for the users: ".implode(",", $error_msg);
				$this->api_response_arry['response_code'] = 500;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}else if($is_valid == 0 && !empty($fixture_error_msg)){
				$msg = "Maximum fixture lineup count 10 exceeded for the users: ".implode(",", $fixture_error_msg);
				$this->api_response_arry['response_code'] = 500;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}
			//echo "<pre>";print_r($lineup_master_data);die;
			$error_arr = array();
			foreach($lineup_master_data as $key=>$team){
				if(isset($team_list[$key])){
            		
            		if($this->bot_config['version'] == "v2"){
								$tmp_data = $team_list[$key];
								//echo "<pre>";print_r($tmp_data);die;
								$c_id = $player_list[$tmp_data["c_id"]]['player_team_id'];
			            		$vc_id = $player_list[$tmp_data["vc_id"]]['player_team_id'];
			            		
			            		$nc_id = $tmp_data["c_id"];
			            		$nvc_id = $tmp_data["vc_id"];

			             		$player_arr = array();
			             		$nw_player_arr = array();
			            		foreach($tmp_data["pl"] as $player_uid){
			            			$player_arr[] = $player_list[$player_uid]['player_team_id'];
			            			$nw_player_arr[] = $player_uid;
			            		}
			            		$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_arr);
			            		$nw_team_data = array("nc_id"=>$nc_id,"nvc_id"=>$nvc_id,"pl"=>$nw_player_arr);
							}else{	
			            		$tm_data = array_column($team_list[$key],"player_uid","captain");
			            		//echo "<pre>";print_r($tm_data['1']);die;
			            		$c_id = $player_list[$tm_data['1']]['player_team_id'];
			            		$vc_id = $player_list[$tm_data['2']]['player_team_id'];

			            		$nc_id = $tm_data['1'];
			            		$nvc_id = $tm_data['2'];
			             		$player_arr = array();
			             		$nw_player_arr = array();
			            		foreach($team_list[$key] as $row)
			            		{
			            			$player_arr[] = $player_list[$row['player_uid']]['player_team_id'];
			            			$nw_player_arr[] = $row['player_uid'];
			            		}

			            		$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_arr);
			            		$nw_team_data = array("nc_id"=>$nc_id,"nvc_id"=>$nvc_id,"pl"=>$nw_player_arr);
			            	}
            		$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_arr);
            		$nw_team_data = array("nc_id"=>$nc_id,"nvc_id"=>$nvc_id,"pl"=>$nw_player_arr);
            		$team['team_data'] = json_encode($team_data);
            		$team['nw_team_data'] = json_encode($nw_team_data);

            		$c_team = $n_team = $team;
            		unset($c_team['nw_collection_master_id']);
            		unset($c_team['nw_league_id']);
            		unset($c_team['nw_team_data']);

            		unset($n_team['collection_master_id']);
            		unset($n_team['league_id']);
            		unset($n_team['team_data']);

            		//join game on network fantasy
            		$post_data['client_id'] = NETWORK_CLIENT_ID;
            		$post_data['team'] = $n_team;
			        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/join_game";
			        $api_response =  $this->http_post_request($url,$post_data,3);
			        $network_lineup_master_id = $api_response['data']['network_lineup_master_id'];
			        $network_lineup_master_contest_id = $api_response['data']['network_lineup_master_contest_id'];
			        
			        //client side
            		$client_lineup_master_id = $this->Systemuser_model->join_game($c_team,$contest_detail);
					
					//echo "<pre>";print_r($client_lineup_master_id);die;            		
					//map master server's lineup master with client.
		            $lm_insert_array = array(
		                "lineup_master_id"             => $client_lineup_master_id,
		                "network_lineup_master_id"     => $network_lineup_master_id,
		                "collection_master_id"         => $c_team['collection_master_id'],
		                "league_id"                    => $c_team['league_id'],
		                "network_collection_master_id" => $n_team['nw_collection_master_id'],
		                "network_league_id"            => $n_team['nw_league_id']

		            );

		        	$this->Systemuser_model->save_network_lineup_master($lm_insert_array);
		            //order table
		            if($client_lineup_master_id)
		            {	
		            	//deposit system amount
		            	$deposit_arr = array();
			        	$deposit_arr['user_id'] = $user['user_id'];
			        	$deposit_arr['amount'] = $contest_detail['entry_fee'];
			        	$this->Systemuser_model->contest_deposit($deposit_arr);

			        	//withdraw amount
			        	$withdraw = array();
			        	$withdraw['user_id'] = $team['user_id'];
			        	$withdraw['source'] = 240;
			        	$withdraw['source_id'] = $network_lineup_master_contest_id;
			        	$withdraw['reference_id'] = $contest_detail['contest_id'];
			        	$withdraw['amount'] = $contest_detail['entry_fee'];
			        	$this->Systemuser_model->contest_withdraw($withdraw);
			        }
			        if($client_lineup_master_id <= 0){
						$error_arr[] = $team['user_name'];
					}
            	}else{
					$error_arr[] = $team['user_name'];
				}
			}

			if(!empty($error_arr)){
				$msg = "Contest joining issue for ".implode(", ", $error_arr)." users";
				$this->api_response_arry['response_code'] = 500;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}else{
				$this->api_response_arry['response_code'] = 200;
				$this->api_response_arry['message'] = "Contest joined successfully.";
				$this->api_response();
			}

        }else{
        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message'] = $this->message;
            $this->api_response();
        }
	}

	 public function _generate_order_key() {
        $this->load->helper('security');
        do {
            $salt = do_hash(time() . mt_rand());
            $new_key = substr($salt, 0, 10);
        }

        // Already in the DB? Fail. Try again
        while (self::_order_key_exists($new_key));

        return $new_key;
    }

     private function _order_key_exists($key) {
        $this->db->select('order_id');
        $this->db->where('order_unique_id', $key);
        $this->db->limit(1);
        $query = $this->db->get(ORDER);
        $num = $query->num_rows();
        if ($num > 0) {
            return true;
        }
        return false;
    }

	/**
     * function used for validate contest data
     * @param array $contest
     * @return array
     */
	public function validation_for_join_game($contest) {
        if (empty($contest)) {
            $this->message = "Please select a valid contest.";
            return 0;
        }

        //for manage collection wise deadline
        $current_date = format_date();
        $current_time = date("Y-m-d H:i:s", strtotime($current_date . " +".SYSTEM_USER_CONTEST_DEADLINE." minute"));
        //check for match schedule date
        if (strtotime($contest['season_scheduled_date']) < strtotime($current_time)) {
            $this->message = "System users join deadline over.";
            return 0;
        }

        //check for full contest
        if ($contest['total_user_joined'] >= $contest['size']) {
            $this->message = "This contest already full.";
            return 0;
        }

        //if contest closed
        if ($contest['status'] !== '0') {
            $this->message = "Contest closed.";
            return 0;
        }

        return 1;
    }

    /**
     * do_upload used to upload profile image or pan card
     * @param
     * @return json array
     */
    public function do_upload_post() {
        $post_data = $this->post();

        $file_field_name = 'userfile';

        if (!isset($_FILES[$file_field_name])) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('file_not_found'));
            $this->api_response();
        }

        $dir = PROFILE_IMAGE_UPLOAD_PATH;
        $temp_file = $_FILES[$file_field_name]['tmp_name'];
        $ext = pathinfo($_FILES[$file_field_name]['name'], PATHINFO_EXTENSION);
        $vals = @getimagesize($temp_file);
        
        if (!empty($_FILES[$file_field_name]['size']) && $_FILES[$file_field_name]['size'] > 4194304) {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $this->lang->line('invalid_image_size'));
            $this->api_response();
        }

        $file_name = time() . "." . $ext;
        $allowed_ext = array('jpg', 'jpeg', 'png');
        if (!in_array(strtolower($ext), $allowed_ext)) {
            $error_msg = sprintf($this->lang->line('invalid_image_ext'), implode(', ', $allowed_ext));
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['error'] = array($file_field_name => $error_msg);
            $this->api_response();
        }

        /* --Start amazon server upload code-- */
        if (strtolower(IMAGE_SERVER) == 'remote') {
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['max_size'] = '204800'; //204800
            $config['upload_path'] = ROOT_PATH . $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PROFILE_IMAGE_PATH . $uploaded_data['file_name'];
                $config = array();
                $config['image_library'] = 'gd2';
                $config['source_image'] = ROOT_PATH . PROFILE_IMAGE_UPLOAD_PATH . $uploaded_data['file_name'];
                $config['new_image'] = ROOT_PATH . PROFILE_IMAGE_THUMB_UPLOAD_PATH;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 200;

                $this->load->library('image_lib', $config);

                $this->image_lib->resize();
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);

                $thumb_filePath = PROFILE_IMAGE_THUMB_UPLOAD_PATH . $file_name;
                $thumb_source_path = ROOT_PATH . PROFILE_IMAGE_THUMB_UPLOAD_PATH . $file_name;
                $filePath = $dir . $file_name;

                try{
	                $data_arr = array();
	                $data_arr['file_path'] = $filePath;
	                $data_arr['source_path'] = $temp_file;
	                $this->load->library('Uploadfile');
	                $upload_lib = new Uploadfile();
	                $is_uploaded = $upload_lib->upload_file($data_arr);
	                if($is_uploaded){
	                	$data_arr = array();
		                $data_arr['file_path'] = $thumb_filePath;
		                $data_arr['source_path'] = $thumb_source_path;
	                    $is_thumb_upload = $upload_lib->upload_file($data_arr);
	                    if($is_thumb_upload){
		                    @unlink($thumb_source_path);
		                    @unlink(ROOT_PATH . $filePath);
		                    $image_path = PROFILE_IMAGE_THUMB_PATH . $file_name;
		                    $return_array = array('image_path' => $image_path, 'file_name' => $file_name);
		                    $this->api_response_arry['data'] = $return_array;
	                    }
	                }
	            }catch(Exception $e){
	                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
	                $this->api_response_arry['global_error'] = $this->lang->line('file_upload_error');
	                $this->api_response();
	            }
            }
        } else {
            $config['allowed_types'] = 'jpg|png|jpeg|gif';
            $config['max_size'] = '4096'; //204800
            $config['upload_path'] = $dir;
            $config['file_name'] = $file_name;

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload($file_field_name)) {
                $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
                $this->api_response_arry['global_error'] = strip_tags($this->upload->display_errors());
                $this->api_response();
            } else {
                $uploaded_data = $this->upload->data();
                $thumb_path = PROFILE_IMAGE_PATH . $uploaded_data['file_name'];
                $this->api_response_arry['data'] = array('image_path' => $thumb_path, 'file_name' => $uploaded_data['file_name']);
            }
        }

        $this->api_response();
    }

    
    public function get_system_user_league_list_post()
    {
		$this->form_validation->set_rules('sports_id', 'sports_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr = $this->input->post();
		//echo "<pre>";print_r($data_arr);die;
		$result = $this->Systemuser_model->get_system_user_league_list($data_arr);
		$this->api_response_arry['response_code'] = 200;
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}	

    public function get_system_user_reports_post()
    {
		$this->form_validation->set_rules('sports_id', 'sports_id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$data_arr = $this->input->post();
		//echo "<pre>";print_r($data_arr);die;
		$result = $this->Systemuser_model->get_system_user_reports($data_arr);
		$this->api_response_arry['response_code'] = 200;
		$this->api_response_arry['data'] = $result;
		$this->api_response();
	}	

	function get_sample_csv_get( $filename='systemuser.csv', $attachment = true, $headers = true) {
			if($attachment) {
				// send response headers to the browser
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename='.$filename);
				$fp = fopen('php://output', 'w');
			} else {
				$fp = fopen($filename, 'w');
			}
		
					$data = array(
				   array('username'),
				   array('john'),
				);
		
			$output = fopen("php://output", "w");
				foreach ($data as $row) {
					fputcsv($output, $row);
				}
				fclose($output);
					exit;
		}


	public function join_multiple_system_users_post()
	{
		if(!in_array(NETWORK_CLIENT_ID, array(1,5,24)))
		{
			exit('No access allowed');
		}	

		$this->form_validation->set_rules('contest_id', 'contest id', 'trim|required');
		if (!$this->form_validation->run()) 
		{
			$this->send_validation_errors();
		}
		$this->message = "";
		$current_date = format_date();
		$data_arr = $this->input->post();
		$contest_id = $data_arr['contest_id'];
		$no_of_bots = $data_arr['no_of_bots'];
		$team_count = $data_arr['team_count'];

		$check_input_valid_request = fmod($data_arr['no_of_bots'],$data_arr['team_count']);
		if($check_input_valid_request != 0)
		{
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Please select correct Bot no and lineup.';
			$this->api_response();
		}

		//get user id which is already join this game
		if(!isset($data_arr['sports_id']) && @$data_arr['sports_id'] == '')
		{	
			$post_data['sports_id'] = CRICKET_SPORTS_ID;
		}else{
			$post_data['sports_id'] = $data_arr['sports_id'];;
		}	
    	$post_data['client_id'] = NETWORK_CLIENT_ID;
    	$post_data['contest_id'] = $contest_id;
        //echo "<pre>";print_r($post_data);die;
        $url = NETWORK_FANTASY_URL."/adminapi/index.php/contest/get_system_user_list_for_contest";
        $api_response =  $this->http_post_request($url,$post_data,3);
        //echo "<pre>";print_r($api_response);die;
        $existing_user = $api_response['data']['user_ids'];
        $already_join_users = array();
        if(!empty($existing_user))
        {	
			$existing_user = explode(",", $existing_user);
			$already_join_users = array_unique($existing_user);
		}
		$requred_no_of_user = $no_of_bots/$team_count;

		//Get contest detail
		$url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_contest_detail";
        $api_response =  $this->http_post_request($url,$post_data,3);
        //echo "<pre>";print_r($api_response);die;
        $contest_detail = $api_response['data']['contest_detail'];
        //echo "<pre>";print_r($contest_detail);die;
		if(empty($contest_detail) || !isset($contest_detail['playing_eleven_confirm']) || $contest_detail['playing_eleven_confirm'] == 0){
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Sorry, system users not allowed for this fixture.';
			$this->api_response();
		}


		$user_list = $this->Systemuser_model->get_system_users_list_not_join($already_join_users,$requred_no_of_user);
		//echo "<pre>";print_r($user_list);die;
		if(!empty($user_list))
		{
			foreach ($user_list as $key => $user) 
			{
				//echo "<pre>";print_r($user);die;
				$entry_data = array( $user['user_id'] => json_decode('{"row_id":0,"user_id":"'.$user['user_id'].'","team_count":'.$team_count.',"user_name":"'.$user['user_name'].'","slot_flag":0,"available_slot":'.$contest_detail['multiple_lineup'].',"value":"'.$user['user_id'].'"}',TRUE));

				$data_arr["user_list"] = $entry_data;
				//echo "<pre>";print_r($data_arr);die;

				$total_team_request = 0;
				if(!empty($data_arr['user_list'])){
					$total_team_request = array_sum(array_column($data_arr['user_list'], "team_count"));
				}
				if($total_team_request > SYSTEM_USER_REQUEST_LIMIT){
					$this->api_response_arry['response_code'] = 500;
					$this->api_response_arry['message'] = 'Total '.SYSTEM_USER_REQUEST_LIMIT.' teams allowed in single request!';
					$this->api_response();
				}

				$input_arr = array();
				$input_arr['sports_id'] = $contest_detail['sports_id'];
			
				$network_client_map = $this->Systemuser_model->check_network_collection_master_id($data_arr);
		        //echo "<pre>";print_r($network_client_map);die;
		       	$input_arr['contest_id'] = $contest_detail['contest_id'];
				//$input_arr['league_id'] = $contest_detail['league_id'];
				$input_arr['league_id'] = $network_client_map['league_id'];
				$input_arr['season_game_uid'] = $contest_detail['season_game_uid'];
				$input_arr['total_team_request'] = $total_team_request;
				//echo "<pre>";print_r($input_arr);die;
				$team_result = $this->Systemuser_model->generate_match_pl_team($input_arr);
				//echo "<pre>";print_r($team_result);die;
				if(!$team_result || empty($team_result)){
					$this->api_response_arry['response_code'] = 500;
					$this->api_response_arry['message'] = 'Sorry, Team not generated. please contact technical team.';
					$this->api_response();
					exit();
				}
				//echo "<pre>";print_r($team_result);die;
				$collection_master_id = $contest_detail['collection_master_id'];
				$team_list = $team_result['lineups'];
				$player_list = array_column($team_result['player_list'],NULL,"player_uid");
				$position_post_array = array_column($team_result['position'], 'master_lineup_position_id','position_name');
				//echo "<pre>";print_r($team_list);die;

				$is_valid = $this->validation_for_join_game($contest_detail);
		        if ($is_valid) {
		        	$this->load->model("auth/Auth_nosql_model");
		        	$total_teams = 0;
					if(!empty($data_arr['user_list'])){
						$total_teams = array_column($data_arr['user_list'], "team_count","user_id");
						$total_teams = array_sum($total_teams);
					}
					if($total_teams > ($contest_detail['size'] - $contest_detail['total_user_joined'])){
						$this->api_response_arry['response_code'] = 500;
						$this->api_response_arry['message'] = 'Number of system users you are trying to add, is more than available entries in this contest!';
						$this->api_response();
					}

					//$contest_user_list = $this->Systemuser_model->get_system_users_for_contest($data_arr);
					$post_data['contest_id'] = $data_arr['contest_id'];
					$post_data['client_id'] = NETWORK_CLIENT_ID;
					//echo "dfd<pre>";print_r($post_data);die;
			        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_system_users_for_contest";
			        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_system_users_for_contest";
			        $api_response =  $this->http_post_request($url,$post_data,3);
			        $contest_user_list = $api_response['data'];
			       // echo "dfd<pre>";print_r($contest_user_list);die;
					$total_bot_joined = 0;
					if(!empty($contest_user_list)){
						$total_bot_joined = array_sum(array_column($contest_user_list, "team_count"));
					}
					$total_joined_request = array_sum(array_column($data_arr['user_list'], "team_count"));
					//echo $tmp_total = $total_bot_joined + $total_joined_request;die;
					$system_user_contest_limit= $this->bot_config['contest_limit'];
					if($tmp_total > $system_user_contest_limit){
						$this->api_response_arry['response_code'] = 500;
						$this->api_response_arry['message'] = 'Max '.$system_user_contest_limit." system users allowed per contest.";
						$this->api_response();
					}

					//fixture wise allowed count validation
					//$fixture_teams = $this->Systemuser_model->get_fixture_system_users_count($contest_detail['collection_master_id']);
					$post_data['collection_master_id'] = $contest_detail['collection_master_id'];
					$post_data['client_id'] = NETWORK_CLIENT_ID;
			        //echo "<pre>";print_r($post_data);die;
			        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/get_fixture_system_users_count";
			        //$url = "http://local.networkserver.com"."/adminapi/index.php/systemuser/get_fixture_system_users_count";
			        $api_response =  $this->http_post_request($url,$post_data,3);
			        //echo "<pre>";print_r($api_response);die;
			        $fixture_teams = $api_response['data'];
					$fixture_count = 0;
					$user_fixture_team = array();
					if(!empty($fixture_teams)){
						$fixture_count = array_sum(array_column($fixture_teams, "team_count"));
						$user_fixture_team = array_column($fixture_teams, "team_count","user_id");
					}
					$tmp_fixture_count = $fixture_count + $total_joined_request;
					$system_user_match_limit = $this->bot_config['match_limit'];
					if($tmp_fixture_count > $system_user_match_limit){
						$this->api_response_arry['response_code'] = 500;
						$this->api_response_arry['message'] = 'Max '.$system_user_match_limit." system users allowed per match.";
						$this->api_response();
					}

					$user_ids = array_column($contest_user_list, "team_count","user_id");
					$lineup_master_data = array();
					$is_valid = 1;
					$error_msg = array();
					$fixture_error_msg = array();
					$current_date = format_date();
					$nw_post_data_arr['collection_master_id'] = $contest_detail['collection_master_id'];
					$nw_collection_detail = $this->Systemuser_model->check_network_collection_master_id($nw_post_data_arr);	
					//echo "<pre>";print_r($nw_collection_detail);die;	
					foreach($data_arr['user_list'] as $user)
					{
						$user_team_count = $user_fixture_team_count = $user['team_count'];
						if(isset($user_ids[$user['user_id']])){
							$user_team_count = $user_team_count + $user_ids[$user['user_id']];
						}
						if(isset($user_fixture_team[$user['user_id']])){
							$user_fixture_team_count = $user_fixture_team_count + $user_fixture_team[$user['user_id']];
						}
						if($user_team_count > $contest_detail['multiple_lineup']){
							$is_valid = 0;
							$error_msg[] = "(".$user['user_name'].")";
						}else if($user_fixture_team_count > 10){
							$is_valid = 0;
							$fixture_error_msg[] = "(".$user['user_name'].")";
						}else{
							for($i=1;$i<=$user['team_count'];$i++)
							{
								$team_name = "Team ".$i;
								if(isset($user_ids[$user['user_id']])){
									$tcount = $user_ids[$user['user_id']] + $i;
									$team_name = "Team ".$tcount;
								}
								
								$tmp_arr = array();
							
								$tmp_arr['nw_collection_master_id'] = $contest_detail['collection_master_id'];
								$tmp_arr['nw_league_id'] = $contest_detail['league_id'];
								//for client site detail;s
								$tmp_arr['collection_master_id'] = $nw_collection_detail['collection_master_id'];
								$tmp_arr['league_id'] = $nw_collection_detail['league_id'];

								$tmp_arr['user_id'] = $user['user_id'];
								$tmp_arr['user_name'] = $user['user_name'];
								$tmp_arr['team_name'] = $team_name;
								$tmp_arr['date_added'] = $current_date;
								$tmp_arr['date_modified'] = $current_date;
								$tmp_arr['team_data'] = json_encode(array());
								$tmp_arr['is_systemuser'] = 1;
								$lineup_master_data[] = $tmp_arr;
							}
						}
					}

					//echo "<pre>";print_r($lineup_master_data);die;
					if($is_valid == 0 && !empty($error_msg)){
						$msg = "Maximum lineup count (".$contest_detail['multiple_lineup'].") exceeded for the users: ".implode(",", $error_msg);
						$this->api_response_arry['response_code'] = 500;
						$this->api_response_arry['message'] = $msg;
						$this->api_response();
					}else if($is_valid == 0 && !empty($fixture_error_msg)){
						$msg = "Maximum fixture lineup count 10 exceeded for the users: ".implode(",", $fixture_error_msg);
						$this->api_response_arry['response_code'] = 500;
						$this->api_response_arry['message'] = $msg;
						$this->api_response();
					}
					//echo "<pre>";print_r($lineup_master_data);die;
					$error_arr = array();
					foreach($lineup_master_data as $key=>$team)
					{
						if(isset($team_list[$key])){
			            	if($this->bot_config['version'] == "v2"){
								$tmp_data = $team_list[$key];
								//echo "<pre>";print_r($tmp_data);die;
								$c_id = $player_list[$tmp_data["c_id"]]['player_team_id'];
			            		$vc_id = $player_list[$tmp_data["vc_id"]]['player_team_id'];
			            		
			            		$nc_id = $tmp_data["c_id"];
			            		$nvc_id = $tmp_data["vc_id"];

			             		$player_arr = array();
			             		$nw_player_arr = array();
			            		foreach($tmp_data["pl"] as $player_uid){
			            			$player_arr[] = $player_list[$player_uid]['player_team_id'];
			            			$nw_player_arr[] = $player_uid;
			            		}
			            		$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_arr);
			            		$nw_team_data = array("nc_id"=>$nc_id,"nvc_id"=>$nvc_id,"pl"=>$nw_player_arr);
							}else{	
			            		$tm_data = array_column($team_list[$key],"player_uid","captain");
			            		//echo "<pre>";print_r($tm_data['1']);die;
			            		$c_id = $player_list[$tm_data['1']]['player_team_id'];
			            		$vc_id = $player_list[$tm_data['2']]['player_team_id'];

			            		$nc_id = $tm_data['1'];
			            		$nvc_id = $tm_data['2'];
			             		$player_arr = array();
			             		$nw_player_arr = array();
			            		foreach($team_list[$key] as $row)
			            		{
			            			$player_arr[] = $player_list[$row['player_uid']]['player_team_id'];
			            			$nw_player_arr[] = $row['player_uid'];
			            		}

			            		$team_data = array("c_id"=>$c_id,"vc_id"=>$vc_id,"pl"=>$player_arr);
			            		$nw_team_data = array("nc_id"=>$nc_id,"nvc_id"=>$nvc_id,"pl"=>$nw_player_arr);
			            	}	
		            		$team['team_data'] = json_encode($team_data);
		            		$team['nw_team_data'] = json_encode($nw_team_data);
		            		$c_team = $n_team = $team;
		            		unset($c_team['nw_collection_master_id']);
		            		unset($c_team['nw_league_id']);
		            		unset($c_team['nw_team_data']);

		            		unset($n_team['collection_master_id']);
		            		unset($n_team['league_id']);
		            		unset($n_team['team_data']);

		            		//join game on network fantasy
		            		$post_data['client_id'] = NETWORK_CLIENT_ID;
		            		$post_data['team'] = $n_team;
					        $url = NETWORK_FANTASY_URL."/adminapi/index.php/systemuser/join_game";
					        $api_response =  $this->http_post_request($url,$post_data,3);
					        $network_lineup_master_id = $api_response['data']['network_lineup_master_id'];
					        $network_lineup_master_contest_id = $api_response['data']['network_lineup_master_contest_id'];
					        
					        //client side
		            		$client_lineup_master_id = $this->Systemuser_model->join_game($c_team,$contest_detail);
							
							//echo "<pre>";print_r($client_lineup_master_id);die;            		
							//map master server's lineup master with client.
				            $lm_insert_array = array(
				                "lineup_master_id"             => $client_lineup_master_id,
				                "network_lineup_master_id"     => $network_lineup_master_id,
				                "collection_master_id"         => $c_team['collection_master_id'],
				                "league_id"                    => $c_team['league_id'],
				                "network_collection_master_id" => $n_team['nw_collection_master_id'],
				                "network_league_id"            => $n_team['nw_league_id']

				            );

				        	$this->Systemuser_model->save_network_lineup_master($lm_insert_array);
				            //order table
				            if($client_lineup_master_id)
				            {	
				            	//deposit system amount
				            	$deposit_arr = array();
					        	$deposit_arr['user_id'] = $user['user_id'];
					        	$deposit_arr['amount'] = $contest_detail['entry_fee'];
					        	$this->Systemuser_model->contest_deposit($deposit_arr);

					        	//withdraw amount
					        	$withdraw = array();
					        	$withdraw['user_id'] = $team['user_id'];
					        	$withdraw['source'] = 240;
					        	$withdraw['source_id'] = $network_lineup_master_contest_id;
					        	$withdraw['reference_id'] = $contest_detail['contest_id'];
					        	$withdraw['amount'] = $contest_detail['entry_fee'];
					        	$this->Systemuser_model->contest_withdraw($withdraw);
					        }
					        if($client_lineup_master_id <= 0){
								$error_arr[] = $team['user_name'];
							}
		            	}else{
							$error_arr[] = $team['user_name'];
						}
					}

					/*if(!empty($error_arr)){
						$msg = "Contest joining issue for ".implode(", ", $error_arr)." users";
						$this->api_response_arry['response_code'] = 500;
						$this->api_response_arry['message'] = $msg;
						$this->api_response();
					}else{
						$this->api_response_arry['response_code'] = 200;
						$this->api_response_arry['message'] = "Contest joined successfully.";
						$this->api_response();
					}*/

		        }else{
		        	$this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
		            $this->api_response_arry['message'] = $this->message;
		            $this->api_response();
		        }

			}

			if(!empty($error_arr))
			{
				$msg = "Contest joining issue for ".implode(", ", $error_arr)." users";
				$this->api_response_arry['response_code'] = 500;
				$this->api_response_arry['message'] = $msg;
				$this->api_response();
			}else{
				$this->api_response_arry['response_code'] = 200;
				$this->api_response_arry['message'] = "Contest joined successfully.";
				$this->api_response();
			}
		}else{
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['message'] = 'Systemuser not available.';
			$this->api_response();
		}	


		exit();
	}	
		

}
/* End of file Systemuser.php */
/* Location: ./application/controllers/Systemuser.php */