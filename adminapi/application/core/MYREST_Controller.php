<?php defined('BASEPATH') OR exit('No direct script access allowed');
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MYREST_Controller extends REST_Controller {

	public $layout = "<br><br>Please don't forget to set a layout for this page. <br>Layout file must be kept in views/layout folder ";
	public $data = array();
	public $success_message = '';
	public $admin_role = '';
	public $admin_privilege = '';
	public $redirect_after_login = 'user';
	public $admin_id = '';
	public $admin_refer_code = '';
	public $admin_fullname = '';
	public $admin_email = '';
	public $auth_key_role=2;
	public $auth_key;
	public $roll_access="";
	public $first_name="";
	public $username="";
	public $allow_2nd_inning=0;
	public $tour_game_sports = [TENNIS_SPORTS_ID,MOTORSPORT_SPORTS_ID];
	public $api_response_arry = array(
		"response_code" => rest_controller::HTTP_OK,
		"service_name"  => '', 
		"message"       => '',
		"global_error"  => '', 
		"error"         => array(),
		"data"          => array()
	);
	public function __construct()
	{
		parent::__construct();
		$_POST = $this->post();
		//set service name
		$method = ($this->router->method == 'index') ? NULL : '/' . $this->router->method;
		$this->api_response_arry['service_name'] = $this->router->class.$method;

			 $_POST = $this->security->xss_clean($_POST,TRUE);
		if(isset($_POST['current_page'])) {
			$current_page = (int)$_POST['current_page'];
			$_POST['current_page'] = is_integer($current_page) ? $current_page : 1;
		}
		if(isset($_POST['items_perpage'])) {
			
			$item_perpage = (int)$_POST['items_perpage'];
			$_POST['items_perpage'] = is_integer($item_perpage) ? $item_perpage : 20;
		}

		$this->layout = 'layout/layout';
		$this->form_validation->CI = & $this; //this is required to run form validatin callbacks
		
		/**
		 * Set Default language in admin section 
		 */
		// if (!$this->session->userdata('language')) {
		// 	$this->session->set_userdata('language', $this->config->item('language'));
		// }

		//$this->config->set_item('language', $this->session->userdata('language'));
		
		
	
		if(!$this->auth_key){
        	$this->auth_key = $this->input->get_request_header(strtolower(AUTH_KEY));
		}
		if(!$this->auth_key){
			$this->auth_key = $this->input->get(AUTH_KEY);
		}
		
        //this condition for old session key
        if(!$this->auth_key){
        	$this->auth_key = $this->input->get_request_header(PREVIOUS_AUTH_KEY);
        }
		$this->headers[AUTH_KEY] = $this->auth_key;

		// validate subadmin privilege
		if ($this->admin_role == SUBADMIN_ROLE) {
			$this->check_subadmin_acess_privilege();
		}
		//$this->load->database();
		// $this->session->set_userdata('language', 'english');


		$this->_check_cors();
		$this->custom_auth_override = $this->_custom_auth_override_check();

		$this->get_app_config_data();
		$this->lang->load('general', 'english');
		$this->lang->load('form_validation', 'english', TRUE);
		//Do your magic here
		if ($this->custom_auth_override === FALSE)
		{
			$this->_custom_prepare_basic_auth();
		}

		$this->allow_2nd_inning =  isset($this->app_config['allow_2nd_inning'])?$this->app_config['allow_2nd_inning']['key_value']:0; 
	
		$this->_get_client_dates('Y-m-d H:i:s',1);
	}

	/**
     * get converted date acc to client time zone.
     * @return 
     */
	public function _get_client_dates($format = 'Y-m-d',$to_utc=2)
	{
		if(isset($_POST['from_date']) && $_POST['from_date'] != ""){
			$start_date_str = date('Y-m-d',strtotime($_POST['from_date'])).' 00:00:00';
			$temp_convert_start = get_timezone(strtotime($start_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['from_date'] = $temp_convert_start['date'];
		}else if(isset($_GET['from_date']) && $_GET['from_date'] != ""){
			$to_utc=1;
			$start_date_str = date('Y-m-d',strtotime($_GET['from_date'])).' 00:00:00';
			$temp_convert_start = get_timezone(strtotime($start_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['from_date'] = $_GET['from_date'] = $temp_convert_start['date'];
		}

		if(isset($_POST['to_date']) && $_POST['to_date'] != ""){
			$end_date_str = date('Y-m-d',strtotime($_POST['to_date'])).' 23:59:59';
			$temp_convert_end = get_timezone(strtotime($end_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['to_date'] = $temp_convert_end['date'];
		}else if(isset($_GET['to_date']) && $_GET['to_date'] != ""){
			$to_utc=1;
			$end_date_str = date('Y-m-d',strtotime($_GET['to_date'])).' 23:59:59';
			$temp_convert_end = get_timezone(strtotime($end_date_str),$format,$this->app_config['timezone'],1,$to_utc);
			$_POST['to_date'] = $_GET['to_date'] = $temp_convert_end['date'];
		}
		// echo "rest con";print_r($_POST);die;
		return;
	}





































































/** OLD METHOD NEED TO FLTER AKR */

	
	/**
     * Used for load cache driver
     * @return 
     */
    private function init_cache_driver() {
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
    }

    /**
     * Used for get cache data by key
     * @param string $cache_key cache key
     * @return array
     */
    public function get_cache_data($cache_key) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX . $cache_key;
        $cache_data = $this->cache->get($cache_key);
        return $cache_data;
    }

    /**
     * Used for save cache data by key
     * @param string $cache_key cache key
     * @param array $data_arr cache data
     * @param int $expire_time cache expire time
     * @return boolean
     */
    public function set_cache_data($cache_key, $data_arr, $expire_time = 3600) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $cache_key = CACHE_PREFIX . $cache_key;
        $this->cache->save($cache_key, $data_arr, $expire_time);
        return true;
    }

    /**
     * Used for delete cache data by key
     * @param string $cache_key cache key
     * @return boolean
     */
    public function delete_cache_data($cache_key) {
        if (!$cache_key || !CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $delete_cache_key = CACHE_PREFIX . $cache_key;
        $this->cache->delete($delete_cache_key);
        return true;
	}

	/**
	* Used for delete cache data by wildcard key / pattern
	* @param string $cache_key cache key
	* @return boolean
	*/
	public function delete_wildcard_cache_data($cache_key) {
		if (!$cache_key || !CACHE_ENABLE) {
			return false;
		}

		$this->init_cache_driver();
		$delete_cache_key = CACHE_PREFIX . $cache_key;
		$this->cache->delete_wildcard($delete_cache_key);
		return true;
	}
	

	/**
     * Used for delete all cache data
     * @return boolean
     */
	public function flush_cache_data() {
        if (!CACHE_ENABLE) {
            return false;
        }

        $this->init_cache_driver();
        $this->cache->clean();
        return true;
    }

	public function init_post_data() {
		$handle = fopen('php://input', 'r');
		$jsonInput = fgets($handle);
		$data = json_decode($jsonInput, true);
		$_POST = $data;
		return $_POST;
	}

	public function delete_lobby_cache_data($cache_key,$sports_id) {

			$this->delete_cache_data($cache_key.$sports_id);
			$allow_reverse_contest = $this->get_app_config_value('allow_reverse_contest');
			
			if($allow_reverse_contest == 1)
			{
				$this->delete_cache_data($cache_key.'reverse_'.$sports_id);
			}
			
			$allow_2nd_inning = $this->get_app_config_value('allow_2nd_inning');
			
			if($allow_2nd_inning == 1)
			{
				$this->delete_cache_data($cache_key.'2nd_inn_'.$sports_id);
			}


	}

	/**
	 * Used for push s3 data in queue
	 * @param string $file_name json file name
	 * @param array $data api file data
	 * @return 
	 */ 
    public function push_s3_data_in_queue($file_name,$data = array(),$action="save"){
    	if(BUCKET_STATIC_DATA_ALLOWED == "0" || $file_name == ""){
			return false;
		}
		$bucket_data = array("file_name"=>$file_name,"data"=>$data,"action"=>$action);
    	$this->push_data_in_queue($bucket_data, 'bucket');
    }

    /**
	 * Used for push data in queue
	 * @param string $file_name json file name
	 * @param array $data api file data
	 * @return 
	 */
    public function push_data_in_queue($data,$queue_name){
    	if(empty($queue_name)){
    		return true;
    	}

    	$this->load->helper('queue_helper');
    	rabbit_mq_push($data, $queue_name);
	}
	
	/**
	 * check cors data
	 * @return boolean
	 */
	protected function _check_cors()
	{
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization,role," . AUTH_KEY.",Version,Apiversion,User-Token,Device,RequestTime,Cookie,_ga_token,X-RefID");
		// If the request HTTP method is 'OPTIONS', kill the response and send it to the client
		if ($this->input->method() === 'options')
		{
			exit;
		}
	}

	public function check_subadmin_acess_privilege() {
		
		if($this->admin_role ==1)
		{
			return TRUE;
		}

		$controller = $this->router->fetch_class();
		$is_allowed = TRUE;

		switch ($controller) {
			case 'contest':
				break;
			case 'dashboard':
				$is_allowed = (empty($this->admin_privilege['dashboard'])) ? FALSE : TRUE;
				break;
			case 'user':
				$is_allowed = (empty($this->admin_privilege['manage_users'])) ? FALSE : TRUE;
				break;
			case 'contest':
				$is_allowed = (empty($this->admin_privilege['manage_contest'])) ? FALSE : TRUE;
				break;
			case 'roster':
				$is_allowed = (empty($this->admin_privilege['roster_management'])) ? FALSE : TRUE;
				break;
			case 'withdrawal':
				$is_allowed = (empty($this->admin_privilege['manage_finance'])) ? FALSE : TRUE;
				break;
			case 'payment_transaction':
				$is_allowed = (empty($this->admin_privilege['manage_finance'])) ? FALSE : TRUE;
				break;
			case 'report':
				$is_allowed = (empty($this->admin_privilege['reports'])) ? FALSE : TRUE;
				break;
			case 'teamroster':
				$is_allowed = (empty($this->admin_privilege['team_list'])) ? FALSE : TRUE;
				break;
			case 'season':
				$is_allowed = (empty($this->admin_privilege['season_schedule'])) ? FALSE : TRUE;
				break;
			case 'score':
				$is_allowed = (empty($this->admin_privilege['season_schedule'])) ? FALSE : TRUE;
				break;
			case 'advertisements':
				$is_allowed = (empty($this->admin_privilege['manage_advertisement'])) ? FALSE : TRUE;
				break;
			case 'promo_code':
				$is_allowed = (empty($this->admin_privilege['manage_promocode'])) ? FALSE : TRUE;
				break;
			case 'subadmin':
				$is_allowed = ($this->admin_role == 2) ? FALSE : TRUE;
				break;

			case 'distributor':	
				$is_allowed = !isset($this->admin_privilege['distributors']) ? FALSE : TRUE;	
			default:
				break;
		}
		if(!empty($this->admin_privilege)){
		if (in_array('manage_users', $this->admin_privilege)) {
			$next_uri = 'user';
		} elseif (in_array('manage_scoring', $this->admin_privilege)) {
			$next_uri = 'contest';
		} elseif (in_array('manage_advertisement', $this->admin_privilege)) {
			$next_uri = 'advertisement';
		} elseif (in_array('user_report', $this->admin_privilege)) {
			$next_uri = 'user_report';
		} elseif (in_array('reports', $this->admin_privilege)) {
			$next_uri = 'user_report';
		} elseif (in_array('payment_setting', $this->admin_privilege)) {
			$next_uri = 'promo_code';
		} elseif (in_array('roster_management', $this->admin_privilege)) {
			$next_uri = 'roster';
		}
		}else{
			$next_uri = '';
		}

		if ($controller == 'auth') {
			$this->redirect_after_login = $next_uri;
		}

		if (!$is_allowed) {
			$this->api_response_arry['response_code'] = 500;
			$this->api_response_arry['service_name'] = "login";
			$this->api_response_arry['message'] = 'You have no permission to access this control.';
			$this->api_response_arry['error'] = array('next_uri' => $next_uri);
			$this->api_response_arry['global_error'] = 'You have no permission to access this control.';
			$this->api_response();
		}
	}


	/**
	 * Retrieve the validation errors array and send as response.
	 * 26/12/2014 16:46
	 * @return none
	 */
	public function send_validation_errors($service_name='login_signup') {
		$error = $this->form_validation->error_array();
		$message = $error[array_keys($error)[0]];
		if (empty($error)) {
			$message = $this->lang->line('all_required');
		}

		$this->api_response_arry['response_code'] = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
		$this->api_response_arry['service_name'] = $service_name;
		$this->api_response_arry['message'] = $message;
		$this->api_response_arry['error'] = $error;
		$this->api_response_arry['global_error'] = $message;
		$this->api_response();
	}

	/**
	 * [send_email description]
	 * @MethodName send_email 
	 * @Summary This function used send email
	 * @param      string  to email
	 * @param      string  Email subject
	 * @param      string  Email messge
	 * @param      string  From Email 
	 * @param      string  From Name
	 * @return     Boolean
	 */
	function send_email($to, $subject = "", $message = "", $from_email = FROM_ADMIN_EMAIL, $from_name = FROM_EMAIL_NAME)
	{
		//return false;
		$this->load->library('email');

		$config['wordwrap']		= TRUE;
		$config['mailtype']		= 'html';
		$config['charset']		= "utf-8";
		$config['protocol']		= PROTOCOL;
		$config['smtp_user']	= SMTP_USER;
		$config['smtp_pass']	= SMTP_PASS;
		$config['smtp_host']	= SMTP_HOST;
		$config['smtp_port']	= SMTP_PORT;
		$config['bcc_batch_mode']	= TRUE;
		$config['smtp_crypto']	= SMTP_CRYPTO;
		$config['newline']		= "\r\n";  // SES hangs with just \n

		$this->email->initialize($config);

		$this->email->clear();
		$this->email->from($from_email, $from_name);
		$this->email->to(ADMIN_EMAIL);
		$this->email->bcc($to);
		$this->email->subject($subject);
		$this->email->message($message);
		$this->email->send();
		//echo $email->print_debugger();
		return true;
	}

	

	  public function generate_active_login_key($user_id = "", $device_type = "1", $device_id = "0") {
        $key = random_string('unique');
        $insert_data = array(
            'key' => $key,
            'role' => 2,
            'user_id' => $user_id,
            'device_type' => $device_type,
            'date_created' => format_date()
        );

        if (!empty($device_id)) {

            $this->db->where('device_id', $device_id)->delete(ACTIVE_LOGIN);
            $insert_data['device_id'] = $device_id;
        }

        $this->db->insert(ACTIVE_LOGIN, $insert_data);
        return $key;
    }



	public function delete_active_login_key($key, $device_type = "1")
	{
		$this->db->where('key', $key)->where('device_type', $device_type)->delete(ACTIVE_LOGIN);
	}

	/**
	 * [seesion_initialization description]
	 * @MethodName seesion_initialization
	 * @Summary This function used for initialize user session
	 * @param      [array]  [User Data Array]
	 * @return     [boolean]
	 */
	public function seesion_initialization($data_arr)
	{
		// $this->session->set_userdata($data_arr);
		// return true;
	}

	/**
	 * Check if there is a specific auth type set for the current class/method/HTTP-method being called
	 *
	 * @access protected
	 * @return bool
	 */
	protected function _custom_auth_override_check()
	{
		// Assign the class/method auth type override array from the config
		$auth_override_class_method = $this->config->item('auth_override_class_method');

		// Check to see if the override array is even populated
		if (!empty($auth_override_class_method))
		{
			// check for wildcard flag for rules for classes
			if (!empty($auth_override_class_method[$this->router->class]['*'])) // Check for class overrides
			{
				// None auth override found, prepare nothing but send back a TRUE override flag
				if ($auth_override_class_method[$this->router->class]['*'] === 'none')
				{
					return TRUE;
				}

				// Basic auth override found, prepare basic
				if ($auth_override_class_method[$this->router->class]['*'] === 'custom')
				{
					$this->_custom_prepare_basic_auth();

					return TRUE;
				}
			}

			// Check to see if there's an override value set for the current class/method being called
			if (!empty($auth_override_class_method[$this->router->class][$this->router->method]))
			{
				// None auth override found, prepare nothing but send back a TRUE override flag
				if ($auth_override_class_method[$this->router->class][$this->router->method] === 'none')
				{
					return TRUE;
				}

				// Basic auth override found, prepare basic
				if ($auth_override_class_method[$this->router->class][$this->router->method] === 'custom')
				{
					$this->_custom_prepare_basic_auth();

					return TRUE;
				}
			}
		}

		// Assign the class/method/HTTP-method auth type override array from the config
		$auth_override_class_method_http = $this->config->item('auth_override_class_method_http');

		// Check to see if the override array is even populated
		if (!empty($auth_override_class_method_http))
		{
			// check for wildcard flag for rules for classes
			if(!empty($auth_override_class_method_http[$this->router->class]['*'][$this->request->method]))
			{
				// None auth override found, prepare nothing but send back a TRUE override flag
				if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'none')
				{
					return TRUE;
				}

				// Basic auth override found, prepare basic
				if ($auth_override_class_method_http[$this->router->class]['*'][$this->request->method] === 'custom')
				{
					$this->_custom_prepare_basic_auth();

					return TRUE;
				}
			}

			// Check to see if there's an override value set for the current class/method/HTTP-method being called
			if(!empty($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method]))
			{
				// None auth override found, prepare nothing but send back a TRUE override flag
				if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'none')
				{
					return TRUE;
				}

				// Basic auth override found, prepare basic
				if ($auth_override_class_method_http[$this->router->class][$this->router->method][$this->request->method] === 'custom')
				{
					$this->_custom_prepare_basic_auth();

					return TRUE;
				}
			}
		}
		return FALSE;
	}



	 public function delete_user_sessions($user_id)
    {
    	$result = $this->db->select('key')
    	->from(ACTIVE_LOGIN)
    	->where("user_id",$user_id)
    	->get()
    	->result_array();

    	// if(!empty($result))
    	// {
    	// 	foreach ($result as $key => $value) {
    	// 		# code...
    	// 		$this->redis_cache->delete(array('fn'=>"session_key_validate_".$value["key"]));   
				  
    	// 	}
    	// }

    }


	/**
	 * Prepares for basic authentication
	 *
	 * @access protected
	 * @return void
	 */
	protected function _custom_prepare_basic_auth($auth_key=FALSE)
	{ 
        if(!$this->auth_key && $auth_key) $this->auth_key = $auth_key;

        $key = $this->auth_key;
        $role = 2;
        if($this->auth_key_role){
        	$role = $this->auth_key_role;
        }

        $this->load->model("auth/Auth_nosql_model");
        $key_detail = $this->Auth_nosql_model->select_one_nosql(ADMIN_ACTIVE_LOGIN,array( AUTH_KEY => $key));
	
		if(empty($key_detail))
        {
        	$this->load->model("auth/Auth_model");
            $key_detail = $this->Auth_model->check_user_key($key);
			if(!empty($key_detail)){
            	$sql = $this->db->select("AD.admin_id,AD.privilege,AD.status,AD.email,AD.role as admin_role,CONCAT_WS(' ', AD.firstname, AD.lastname) AS full_name,ARR.right_ids,AD.access_list,AD.firstname as first_name,AD.username", FALSE)
							->from(ADMIN . " AS AD")
							->join(ADMIN_ROLES_RIGHTS . " AS ARR", "ARR.role_id = AD.role", "inner")
							->where("AD.admin_id", $key_detail['user_id'])
							->where("AD.status", 1)
							->get();
				$data = $sql->row_array();
				
            
            	$key_detail[AUTH_KEY] = $key;
            	if(!empty($data)){
            		$key_detail = array_merge($key_detail,$data);
            	}
            	$this->Auth_nosql_model->insert_nosql(ADMIN_ACTIVE_LOGIN,$key_detail);
            }
        }

        if(ADMIN_AUTO_LOGOUT_TIME > 0 && !empty($key_detail)){
        	$current_date = format_date();
			$current_date_time = strtotime($current_date." -".ADMIN_AUTO_LOGOUT_TIME." minutes");
			if(strtotime($key_detail['date_created']) <= $current_date_time){
				$this->delete_active_login_key($key);

				$this->Auth_nosql_model->delete_nosql(ADMIN_ACTIVE_LOGIN, array(AUTH_KEY => $key));
				$key_detail = array();
			}
        }
	
        if(!empty($key_detail))
        {
			$this->admin_id = $key_detail['admin_id'];
          
			$this->admin_role = $key_detail['admin_role'];
			$this->access_list = $key_detail['access_list'];
			$this->admin_privilege = ($this->admin_role > 1) ? array("distributors"=>1) : '';
			$this->first_name = $key_detail['first_name'];
			$this->username = $key_detail['username'];

			$this->check_subadmin_acess_privilege();
			$this->admin_privilege = ($this->admin_role > 1) ? json_decode($key_detail['privilege']) : '';

			if(ADMIN_AUTO_LOGOUT_TIME > 0){
				$this->Auth_nosql_model->update_nosql(ADMIN_ACTIVE_LOGIN, array(AUTH_KEY => $key), array('date_created' => $current_date));
			}

            return TRUE;
        }
        else
        {
            if($this->custom_auth_override){
                return TRUE;
            }
            $this->response([
                    $this->config->item('rest_status_field_name') => FALSE,
                    "response_code" => self::HTTP_UNAUTHORIZED,
                    "global_error" => $this->lang->line('text_rest_unauthorized'),
                    $this->config->item('rest_message_field_name') => $this->lang->line('text_rest_unauthorized')
            ], self::HTTP_UNAUTHORIZED);
        }
	}

	/**
	 * [api_response description]
	 * Summary :-
	 * @return [type] [description]
	 */
	public function api_response() {
		$continue = FALSE;
		$output = array();
		
		$output['service_name'] = $this->api_response_arry['service_name'];
		$output['message'] = $this->api_response_arry['message'];
		$output['global_error'] = $this->api_response_arry['global_error'];
		$output['error'] = $this->api_response_arry['error'];
		$output['data'] = $this->api_response_arry['data'];
		$output['response_code'] = $this->api_response_arry['response_code'];
                
                if(isset($this->api_response_arry['collection_master_id'])) {
                    $output['collection_master_id'] = $this->api_response_arry['collection_master_id'];
		}
        if(isset($this->api_response_arry['show_cancel'])) {
            $output['show_cancel'] = $this->api_response_arry['show_cancel'];
		}
        if(isset($this->api_response_arry['show_revert'])) {
            $output['show_revert'] = $this->api_response_arry['show_revert'];
		}
		if(isset($this->api_response_arry['allow_freetoplay'])) {
			$output['allow_freetoplay'] = $this->api_response_arry['allow_freetoplay'];
		}
		if(isset($this->api_response_arry['max_match_system_user'])) {
                    $output['max_match_system_user'] = $this->api_response_arry['max_match_system_user'];
		}
		if(isset($this->api_response_arry['continue'])&&$this->api_response_arry['continue']===TRUE)
		{
			$continue = $this->api_response_arry['continue'];
		}

		if (method_exists($this, 'response')) {
			$this->response($output, $this->api_response_arry['response_code'], $continue);
		} else {
			http_response_code($this->api_response_arry['response_code']);
			// echo json_encode($output);
		}
	}

	public function check_season_game_array()
	{
		$season_games = $this->input->post('season_game_uid');

		if(empty($season_games))
		{
			$this->form_validation->set_message('check_season_game_array', "Please select atleast one match.");
        	return FALSE;	
		}

		return TRUE;
        
	}

	//ALLOW_NETWORK_FANTASY -> case 3

	function http_post_request($url, $params = array(), $api_type = 1, $debug = false)
    {
        switch ($api_type)
        {
            case 1 :
                $api_url = FANTASY_API_URL . $url;
                break;
            case 2 :
                $api_url = USER_API_URL . $url;
                break;
            case 3 :
            	$api_url = $url; 
            break;    
        }

        $post_data_json = json_encode($params);
        $header = array("Content-Type:application/json", "Accept:application/json");
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
        if ($debug)
        {
            echo '<pre>';
            echo $output;
            exit();
        }
        curl_close($ch);
        return json_decode($output, true);
    }

	  public function add_notification($data=array())
    {
        $contest_data = $this->http_post_request('notification/send_notification', $data, 3);
        return $contest_data;
    }

    
	function rabbit_mq_push($data, $queue_name, $exchange_name = '') {

		$connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USER, MQ_PASSWORD);
		$channel = $connection->channel();
		$push_data = json_encode($data);
		$message = new AMQPMessage($push_data, array('delivery_mode' => 1, 'content_type' => 'application/json')); # make message persistent as 2
		$channel->basic_publish($message, $exchange_name, $queue_name);
		$channel->close();
		$connection->close();
		return true;
	}

	public function validate_login_cutom_filter()
	{
		$post = $this->input->post();
		if(!empty($post) && $post['activity_id'] == 5)
		{

			if(empty($post['login_min']) && empty($post['login_max']))
			{
				$this->form_validation->set_message('validate_login_cutom_filter', "Please enter values for Min and Max.");
				return FALSE;
			}

			if(isset($post['login_min']) && isset($post['login_max']) && $post['login_min'] > $post['login_max'])
			{
				$this->form_validation->set_message('validate_login_cutom_filter', "Max value should be greater than Min value.");
				return FALSE;
			}

			if(empty($post['login_min']) && $post['login_max'] > 0) 	
			{
				$this->form_validation->set_message('validate_login_cutom_filter', "Please enter value for Min.");
				return FALSE;
			}

			if(empty($post['login_max']) && $post['login_min'] > 0)
				
			{
				$this->form_validation->set_message('validate_login_cutom_filter', "Please enter value for Max.");
				return FALSE;
			}
		}
	}

	public function validate_signup_cutom_filter()
	{
		$post = $this->input->post();


		if(!empty($post) && $post['activity_id'] == 10)
		{
			if(empty($post['signup_min']) && empty($post['signup_max']))
			{
				$this->form_validation->set_message('validate_signup_cutom_filter', "Please enter values for Min and Max.");
				return FALSE;
			}

			if(isset($post['signup_min']) && isset($post['signup_max']) && $post['signup_min'] > $post['signup_max'])
			{
				$this->form_validation->set_message('validate_signup_cutom_filter', "Max value should be greater than Min value.");
				return FALSE;
			}

			if(empty($post['signup_min']) && $post['signup_max'] > 0) 	
			{
				$this->form_validation->set_message('validate_signup_cutom_filter', "Please enter value for Min.");
				return FALSE;
			}

			if(empty($post['signup_max']) && $post['signup_min'] > 0)
				
			{
				$this->form_validation->set_message('validate_signup_cutom_filter', "Please enter value for Max.");
				return FALSE;
			}
		}
	}

	public function check_unique_sku_id()
	{
		$this->load->model('Product_model');
		
		//echo "<pre>";print_r($this->input->post());die;
		if(!empty($this->input->post("product_master_id")))
		{
			$sku_data = $this->db_store->select('product_unique_id')
								->from(PRODUCT_MASTER)
								->where("product_unique_id",$this->input->post("sku_id"))
								->where("product_master_id !=",$this->input->post("product_master_id"))
								->get()->row_array();
		}
		else
		{
			$sku_data = $this->db_store->select('product_unique_id')
								->from(PRODUCT_MASTER)
								->where("product_unique_id",$this->input->post("sku_id"))
								->get()->row_array();
		}
		if(empty($sku_data))
		{
			return TRUE;
		}

		$this->form_validation->set_message('check_unique_sku_id', $this->lang->line("sku_id_already_exists"));
		return FALSE;
	}

	public function get_last_active_users()
	{
		$current_date =format_date();
		$active_days = $this->input->post("active_days");
		if(empty($active_days))
		{
			$active_days = 10;
		}
		
		$result = $this->db->select("AUL.user_id,U.user_name,U.email,COUNT(AUL.user_id) as count,AUL.login_date_time")
		->from(ANALYTICS_USER_LOGIN ." as AUL")
		->join(USER ." as U","AUL.user_id=U.user_id","INNER")
		->where("AUL.login_date_time>DATE_SUB('{$current_date}',INTERVAL $active_days DAY)")
		->where("U.email IS NOT NULL")
		->group_by("AUL.user_id")
		->order_by("count","DESC")
		->get()
		->result_array();

		return $result;
	}

	public function get_user_by_fav_teams()
	{
		$team_ids = $this->post("fan_club_list");
		
		$result = $this->db->select("email,user_name,user_id")
		->from(USER)
		->where_in("team_id",$team_ids)
		->where("email IS NOT NULL")
		->get()
		->result_array();

		return $result;
	}

	public function get_top_investors()
	{
		$url = 'contest/get_top_inverstors';
		$post_param = $this->input->post();
		$investors_response = $this->http_post_request($url,$post_param,2);

		if($investors_response['response_code'] !== rest_controller::HTTP_OK)
		{
			$this->api_response_arry['response_code'] 	= 500;
			$this->api_response_arry['global_error']  	= "Problem while get Investors";
			$this->api_response();
		}

		return $investors_response['data'];

	}

	public function get_user_emails()
	{
		$result = $this->db->select("email,user_name,user_id")
		->from(USER)
		->where("email IS NOT NULL")
		->limit(10)
		->get()
		->result_array();
		return $result;
	}

	public function get_user_details_by_ids($user_ids)
	{
		$result = $this->db->select("email,user_name,user_id")
		->from(USER)
		->where("email IS NOT NULL")
		->where_in("user_id",$user_ids)
		//->limit(10)
		->get()
		->result_array();
		return $result;
	}


	public function check_notify_type()
	{
		$type = $this->input->post('type');

		if($type && in_array($type, array(1,2,3,4,5,6)))
		{
			return true;
		}

		$this->form_validation->set_message('check_notify_type', "Please select notification type.");
		return FALSE;
	}

	/* public function deleteRedisCache($cache_key){
        if(!$cache_key){
            return false;
        }

        $delete_cache_key = CACHE_PREFIX.$cache_key;
        $this->redis_cache->delete(array('fn'=>$delete_cache_key));
        return true;
    } */

    public function deleteS3BucketFile($file_name){

    	$json_file_name = BUCKET_STATIC_DATA_PATH.BUCKET_DATA_PREFIX.$file_name;
    	try{
            $data_arr = array();
            $data_arr['file_path'] = $json_file_name;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_deleted = @$upload_lib->delete_file($data_arr);
            if($is_deleted){
                return true;
            }else{
            	return false;
            }
        }catch(Exception $e){
            //$result = 'Caught exception: '.  $e->getMessage(). "\n";
            return false;
        }
	}
	
	public function deleteS3BucketImageFile($file_name){

    	$json_file_name = 'upload/setting/'.$file_name;
    	try{
            $data_arr = array();
            $data_arr['file_path'] = $json_file_name;
            $this->load->library('Uploadfile');
            $upload_lib = new Uploadfile();
            $is_deleted = $upload_lib->delete_file($data_arr);
            if($is_deleted){
                return true;
            }else{
            	return false;
            }
        }catch(Exception $e){
            //$result = 'Caught exception: '.  $e->getMessage(). "\n";
            return false;
        }
    }

	public function get_submodule_settings()
	{
		$cache_key = "submodule_settings";
		$data = $this->get_cache_data($cache_key);
		
		if(empty($data))
		{
			$this->load->model('auth/Auth_model');
			$data = $this->Auth_model->get_submodule_setting();
			$data = array_column($data,'status','submodule_key');
			$this->set_cache_data($cache_key,$data,REDIS_2_DAYS);
		}
		return $data;
	}

	public function get_contest_detail()
	{
		$post_params = $this->input->post();
		$post_target_url	= 'contest/get_contest_detail';
		$contest_detail		= $this->http_post_request($post_target_url,$post_params,2);
		return $contest_detail;
	}

	public function get_collection_detail()
	{
		$post_params = $this->input->post();
		$post_target_url	= 'lobby/get_collection_detail';
		$contest_detail		= $this->http_post_request($post_target_url,$post_params,2);
		return $contest_detail;
	}

	public function notify_prediction_to_client($url,$data)
	{
		 $curlUrl = NODE_BASE_URL.$url;

		 $data_string = json_encode($data);

		 try{

		 	$header = array("Content-Type:application/json",
		 	 "Accept:application/json",
		 	  "User-Agent:Mozilla/5.0 (Windows NT 6.3; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0"
		 	);

 			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,$curlUrl);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$server_output = curl_exec($ch);

		 	// Check the return value of curl_exec(), too
		    if ($server_output === false) {
		        throw new Exception(curl_error($ch), curl_errno($ch));
		    }
			curl_close ($ch);

		 }
		 catch(Exception $e){
		 	// var_dump($e);
		 	// die('dfdf');
		 }

            return true;
	}


	public function delete_cache_and_bucket_cache($data_arr=array())
	{
		if(BUCKET_STATIC_DATA_ALLOWED && !empty($data_arr)){
			$sports_ids = !empty($data_arr['sports_ids']) ? $data_arr['sports_ids'] : array(CRICKET_SPORTS_ID);
			$file_name = $data_arr['file_name'];
			if(isset($data_arr['lang_file']) && $data_arr['lang_file'] == 1){
				$languages = $this->config->item('language_list');
				foreach($languages as $lang_abbr => $lang_value)
				{
					//for delete s3 bucket file
					foreach($sports_ids as $sports_id){
						$this->push_s3_data_in_queue($file_name.$sports_id.'_'.$lang_abbr,array(),"delete");
						if(!isset($data_arr['ignore_cache']) || $data_arr['ignore_cache'] != "1"){
							$this->delete_cache_data($file_name.$sports_id.'_'.$lang_abbr);
						}
					}
				}
			}else{
				//for delete s3 bucket file
				foreach($sports_ids as $sports_id){
					$this->push_s3_data_in_queue($file_name.$sports_id,array(),"delete");
					if(!isset($data_arr['ignore_cache']) || $data_arr['ignore_cache'] != "1"){
						$this->delete_cache_data($file_name.$sports_id);
					}
				}
			}
			
		}
	}

	function get_master_setting()
	{
		$this->load->model('auth/Auth_model');
		//get module setting
        $modules_data = $this->Auth_model->get_all_table_data("module_setting_id,name,status",MODULE_SETTING);

        if(!empty($modules_data))
        {
            foreach($modules_data as $module)
            {
                $data_arr[$module['name']] = $module['status']; 
            }
		}
		return $data_arr;
       
	}

	/**
	 * [admin_roles_manage description]
	 * Summary :- User for get all admin role list
 	 * @param   : admin_id,module_name
	 * @return  [json]
	*/

	public function admin_roles_manage($admin_id,$module_name){
		$this->load->model('auth/Auth_model');
		
		$admin_access_list = isset($this->access_list) ? $this->access_list : "[]";
		if($admin_access_list=="null"){
			
		}else{ 
			//Ignore List (Add function method in array if function use comman)
			$ignore_list = array("change_password","get_setting","get_front_bg_image","get_affiliate_master_data","get_pending_counts","get_contest_filter","get_sport_leagues","get_system_user_league_list","get_system_user_reports","update_affiliate_master_data","get_game_stats","get_game_lineup_detail","get_all_transaction","get_all_upcoming_collections","get_cd_balance","get_segementation_template_list","get_filter_result_test","get_contest_detail","get_season_details","get_lineup_detail","front_bg_upload","get_system_users_for_contest","get_contest_joined_system_users","reset_front_bg_image","join_system_users","coin_distributed_graph","user_coin_redeem_graph","get_reward_list","get_reward_history","get_reward_list_by_status","approve_reward_request","add_reward","export_reward_list_by_status","update_reward_status","do_upload_reward_image","update_finance_data");

			$admin_access_json = json_decode($admin_access_list);
			//print_r($admin_access_json);exit;
			if(!in_array($module_name,$admin_access_json)){

				if(in_array($this->router->method,$ignore_list)){
					//echo "this is ignore";exit;
				}else{
					$this->api_response_arry['service_name']  = 'admin_roles_manage';
			        $this->api_response_arry['message']       = "You have not access for the ".ucwords($module_name)." module please contact admin.";
			        $this->api_response_arry['global_error']  = 'Module access';
			        $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
			        $this->api_response();
				}
			}
		}
	}

	function get_app_config_data()
    {
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $app_config_cache_key = 'app_config';
        $data = $this->get_cache_data($app_config_cache_key);
        if (!$data) {
            $this->load->model("user/User_model");
            $result = $this->User_model->get_all_table_data("*",APP_CONFIG);
    
            foreach($result as &$row)
            {
                if(!empty($row['custom_data']))
                {
                    $row['custom_data'] = json_decode($row['custom_data'],TRUE);
                }
            }

            $data = array_column($result,NULL,'key_name');
    
            $this->set_cache_data($app_config_cache_key, $data, REDIS_30_DAYS);
        }
       
		$this->app_config = $data;
		$this->define_app_constant();
        
	}

	
	 /**
     * @method define_app_constant
     * @uses this method defines constant from app config variable
     * @since Jan 2021
     * @param NA
    */
    function define_app_constant()
    {
        $site_title = isset($this->app_config['site_title'])?$this->app_config['site_title']['key_value']:'Fantasy Sports';
        define('SITE_TITLE', $site_title);

        $coins_balance_claim = isset($this->app_config['coins_balance_claim'])?$this->app_config['coins_balance_claim']['key_value']:'';
        define('COINS_BALANCE_CLAIM', $coins_balance_claim);

        $fb_link = isset($this->app_config['fb_link'])?$this->app_config['fb_link']['key_value']:'';
        define('FB_LINK', $fb_link);

        $twitter_link = isset($this->app_config['twitter_link'])?$this->app_config['twitter_link']['key_value']:'';
        define('TWITTER_LINK', $twitter_link);

        $instagram_link = isset($this->app_config['instagram_link'])?$this->app_config['instagram_link']['key_value']:'';
        define('INSTAGRAM_LINK', $instagram_link);

        $report_admin_email = isset($this->app_config['report_admin_email'])?$this->app_config['report_admin_email']['key_value']:'';
        define('REPORT_ADMIN_EMAIL', $report_admin_email);

        $fcm_key = isset($this->app_config['fcm_key'])?$this->app_config['fcm_key']['key_value']:'';
        define('FCM_KEY', $fcm_key);
        
        //all languages
        $allow_english = isset($this->app_config['allow_english'])?$this->app_config['allow_english']['key_value']:0;
        $allow_hindi = isset($this->app_config['allow_hindi'])?$this->app_config['allow_hindi']['key_value']:0;
        $allow_gujrati = isset($this->app_config['allow_gujrati'])?$this->app_config['allow_gujrati']['key_value']:0;
        $allow_french = isset($this->app_config['allow_french'])?$this->app_config['allow_french']['key_value']:0;
        $allow_bengali = isset($this->app_config['allow_bengali'])?$this->app_config['allow_bengali']['key_value']:0;
        $allow_punjabi = isset($this->app_config['allow_punjabi'])?$this->app_config['allow_punjabi']['key_value']:0;
        $allow_tamil = isset($this->app_config['allow_tamil'])?$this->app_config['allow_tamil']['key_value']:0;
		$allow_thai = isset($this->app_config['allow_thai'])?$this->app_config['allow_thai']['key_value']:0;
		$allow_russian = isset($this->app_config['allow_russian'])?$this->app_config['allow_russian']['key_value']:0;
		$allow_indonesian = isset($this->app_config['allow_indonesian'])?$this->app_config['allow_indonesian']['key_value']:0;
		$allow_tagalog = isset($this->app_config['allow_tagalog'])?$this->app_config['allow_tagalog']['key_value']:0;
		$allow_chinese = isset($this->app_config['allow_chinese'])?$this->app_config['allow_chinese']['key_value']:0;
		$allow_kannada = isset($this->app_config['allow_kannada'])?$this->app_config['allow_kannada']['key_value']:0;
		$allow_spanish = isset($this->app_config['allow_spanish'])?$this->app_config['allow_spanish']['key_value']:0;
		
      
        $language_list = array();
        $app_language_list = array();
        if($allow_english == 1){
            $language_list['en'] = 'english';
            $app_language_list['en'] = 'English';
        }
        if($allow_hindi == 1){
            $language_list['hi'] = 'hindi';
            $app_language_list['hi'] = 'हिंदी';
        }
        if($allow_gujrati == 1){
            $language_list['guj'] = 'gujrati';
            $app_language_list['guj'] = 'ગુજ્રાતી';
        }
        if($allow_french == 1){
            $language_list['fr'] = 'french';
            $app_language_list['fr'] = 'Français';
        }
        if($allow_bengali == 1){
            $language_list['ben'] = 'bengali';
            $app_language_list['ben'] = 'বাংলা';
        }
        if($allow_punjabi == 1){
            $language_list['pun'] = 'punjabi';
            $app_language_list['pun'] = 'ਪੰਜਾਬੀ';
        }
        if($allow_tamil == 1){
            $language_list['tam'] = 'tamil';
            $app_language_list['tam'] = 'தமிழ்';
        }
        if($allow_thai == 1){
            $language_list['th'] = 'thai';
            $app_language_list['th'] = 'ไทย';
		}
		if($allow_russian == 1){
            $language_list['ru'] = 'russian';
            $app_language_list['ru'] = 'Rusia';
		}
		if($allow_indonesian == 1){
            $language_list['id'] = 'indonesian';
            $app_language_list['id'] = 'Indonesia';
		}
		if($allow_tagalog == 1){
            $language_list['tl'] = 'tagalog';
            $app_language_list['tl'] = 'tagalog';
		}
		if($allow_chinese == 1){
            $language_list['zh'] = 'chinese';
            $app_language_list['zh'] = '中国人';
		}
		
		if($allow_kannada == 1){
            $language_list['kn'] = 'kannada';
            $app_language_list['kn'] = 'ಕನ್ನಡ';
        }
		if($allow_spanish == 1){
            $language_list['es'] = 'Spanish';
            $app_language_list['es'] = 'española';
        }  

        define('LANGUAGE_LIST',serialize($language_list));
        define('APP_LANGUAGE_LIST',serialize($app_language_list));

        $this->config->set_item('language_list',$language_list);

       
        $config_app_language_list= array();

        foreach($app_language_list as $key => $value)
        {
            $config_app_language_list[] = array("value"=>$key,"label"=>$value);
        }
        $this->config->set_item('app_language_list',$config_app_language_list);

        $android_app = (isset($this->app_config['android_app']['key_value']) && $this->app_config['android_app']['key_value']==1)?$this->app_config['android_app']['custom_data']:0;
        if(!empty($android_app))
        {
            if(!isset($android_app['android_app_page'])){
                $android_app['android_app_page'] = $android_app['android_app_link'];
            }
            define('ANDROID_APP_LINK', $android_app['android_app_link']);
            define('ANDROID_MIN_VER', $android_app['android_min_ver']);
            define('ANDROID_CURRENT_VER', $android_app['android_current_ver']);
            define('ANDROID_APP_PAGE', $android_app['android_app_page']);
        }else{
            define('ANDROID_APP_LINK'   , '');
            define('ANDROID_MIN_VER'    , '');
            define('ANDROID_CURRENT_VER', '');
            define('ANDROID_APP_PAGE'   , '');
        }

        $ios_app = (isset($this->app_config['ios_app']['key_value']) && $this->app_config['ios_app']['key_value']==1)?$this->app_config['ios_app']['custom_data']:0;
        if(!empty($ios_app))
        {
            define('IOS_APP_LINK', $ios_app['ios_app_link']);
            define('IOS_MIN_VER', $ios_app['ios_min_ver']);
            define('IOS_CURRENT_VER', $ios_app['ios_current_ver']);
        }else{
            define('IOS_APP_LINK'   , '');
            define('IOS_MIN_VER'    , '');
            define('IOS_CURRENT_VER', '');
        }

        $default_sports_id = isset($this->app_config['default_sports_id'])?$this->app_config['default_sports_id']['key_value']:7;
        define('DEFAULT_SPORTS_ID', $default_sports_id);

        $allow_bank_transfer = isset($this->app_config['allow_bank_transfer'])?$this->app_config['allow_bank_transfer']['key_value']:0;
        define('ALLOW_BANK_TRANSFER', $allow_bank_transfer);

        $allow_mpesa_withdraw = isset($this->app_config['allow_mpesa_withdraw'])?$this->app_config['allow_mpesa_withdraw']['key_value']:0;
        define('ALLOW_MPESA_WITHDRAW', $allow_mpesa_withdraw);

        $allow_private_contest = isset($this->app_config['allow_private_contest'])?$this->app_config['allow_private_contest']['key_value']:0;
        define('ALLOW_PRIVATE_CONTEST', $allow_private_contest);

        $bucket_static_data_allowed = isset($this->app_config['bucket_static_data_allowed'])?$this->app_config['bucket_static_data_allowed']['key_value']:0;
        define('BUCKET_STATIC_DATA_ALLOWED', $bucket_static_data_allowed);

        $bucket_data_prefix = isset($this->app_config['bucket_data_prefix'])?$this->app_config['bucket_data_prefix']['key_value']:0;
        define('BUCKET_DATA_PREFIX', $bucket_data_prefix);

        $int_version = isset($this->app_config['int_version'])?$this->app_config['int_version']['key_value']:0;
		define('INT_VERSION', $int_version);

		$coin_only = isset($this->app_config['coin_only'])?$this->app_config['coin_only']['key_value']:0;
		define('COIN_ONLY', $coin_only);
		
		$max_contest_bonus = isset($this->app_config['max_contest_bonus'])?$this->app_config['max_contest_bonus']['key_value']:0;
        define('MAX_CONTEST_BONUS', $max_contest_bonus);

        $currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:0;
        define('CURRENCY_CODE', $currency_code);
        define('CURRENCY_CODE_HTML', CURRENCY_CODE);
    }
	
	function filter_hub_data($hub_list,$module_data=array())
	{
		$data_arr = array();
		$allow_prediction_system =  isset($this->app_config['allow_prediction_system'])?$this->app_config['allow_prediction_system']['key_value']:0;
		$data_arr['allow_prediction'] = $allow_prediction_system;
		
		$allow_open_predictor =  isset($this->app_config['allow_open_predictor'])?$this->app_config['allow_open_predictor']['key_value']:0;
		$data_arr['allow_open_predictor'] = $allow_open_predictor;

		$allow_fixed_open_predictor =  isset($this->app_config['allow_fixed_open_predictor'])?$this->app_config['allow_fixed_open_predictor']['key_value']:0;
		$data_arr['allow_fixed_open_predictor'] = $allow_fixed_open_predictor;

		$allow_coin_system = isset($this->app_config['allow_coin_system'])?$this->app_config['allow_coin_system']['key_value']:0;
        if($allow_coin_system == 0 ){
            $data_arr['allow_coin'] = $allow_coin_system;
            $data_arr['allow_prediction'] = "0";
            $data_arr['allow_open_predictor'] = "0";
            $data_arr['allow_fixed_open_predictor'] = "0";
        }elseif($allow_coin_system == 1 ){
            $data_arr['allow_coin'] = $allow_coin_system;
            $data_arr['allow_prediction'] = $allow_prediction_system;
            $data_arr['allow_open_predictor'] = $allow_open_predictor;
            $data_arr['allow_fixed_open_predictor'] = $allow_fixed_open_predictor;
		}
		
		$allow_dfs =  isset($this->app_config['allow_dfs'])?$this->app_config['allow_dfs']['key_value']:0;
		$data_arr['allow_dfs'] = $allow_dfs;
		
		$allow_multigame =  isset($this->app_config['allow_multigame'])?$this->app_config['allow_multigame']['key_value']:0;
		$data_arr['allow_multigame'] = $allow_multigame;

		$allow_free2play =  isset($this->app_config['allow_free_to_play'])?$this->app_config['allow_free_to_play']['key_value']:0;
		$data_arr['allow_free2play'] = $allow_free2play;

		$allow_stock_fantasy = isset($this->app_config['allow_stock_fantasy'])?$this->app_config['allow_stock_fantasy']['key_value']:0;
		// $allow_live_fantasy = isset($this->app_config['live_fantasy'])?$this->app_config['live_fantasy']['key_value']:0;
		// $data_arr['allow_live_fantasy'] = $allow_live_fantasy;
		$allow_equity = isset($this->app_config['allow_equity'])?$this->app_config['allow_equity']['key_value']:0;

		$allow_stock_predict = isset($this->app_config['allow_stock_predict'])?$this->app_config['allow_stock_predict']['key_value']:0;

		$allow_live_stock_fantasy = isset($this->app_config['allow_live_stock_fantasy'])?$this->app_config['allow_live_stock_fantasy']['key_value']:0;
		$allow_livefantasy = isset($this->app_config['allow_livefantasy']) ? $this->app_config['allow_livefantasy']['key_value']:0;
      
		$allow_picks = isset($this->app_config['allow_picks'])?$this->app_config['allow_picks']['key_value']:0;

		$allow_pickem_tournament = isset($this->app_config['allow_pickem_tournament'])?$this->app_config['allow_pickem_tournament']['key_value']:0;

		$allow_props = isset($this->app_config['allow_props'])?$this->app_config['allow_props']['key_value']:0;

		foreach($hub_list as $key =>  &$hub)
        {
            if($hub['game_key'] == 'allow_prediction' && (!$data_arr['allow_prediction'] || !isset($module_data['allow_prediction'])))
            {
                unset($hub_list[$key]);
            }

			if($hub['game_key'] == 'live_fantasy' && $allow_livefantasy!=1)
            {
                unset($hub_list[$key]);
            }

            if($hub['game_key'] == 'allow_open_predictor' && (!$data_arr['allow_open_predictor'] || !$module_data['allow_open_predictor']))
            {
				unset($hub_list[$key]);
            }
			
			if($hub['game_key'] == 'allow_fixed_open_predictor' && (!$data_arr['allow_fixed_open_predictor'] || !$module_data['allow_fixed_open_predictor']))
            {
                unset($hub_list[$key]);
			}
			if(($hub['game_key'] == 'allow_dfs' && !$data_arr['allow_dfs'] ) )
            {
                unset($hub_list[$key]);
			}
			if(($hub['game_key'] == 'allow_multigame' && !$data_arr['allow_multigame'] ) )
            {
                unset($hub_list[$key]);
			}
			if(($hub['game_key'] == 'allow_free2play' && !$data_arr['allow_free2play'] ) )
            {
                unset($hub_list[$key]);
            }

			if($hub['game_key'] == 'allow_stock_fantasy' && $allow_stock_fantasy!=1)
            {
                unset($hub_list[$key]);
            }

			if($hub['game_key'] == 'allow_equity' && $allow_equity!=1)
            {
                unset($hub_list[$key]);
            }

            if($hub['game_key'] == 'allow_stock_predict' && $allow_stock_predict!=1)
            {
                unset($hub_list[$key]);
            }

            if($hub['game_key'] == 'allow_live_stock_fantasy' && $allow_live_stock_fantasy!=1)
            {
                unset($hub_list[$key]);
            }

            if($hub['game_key'] == 'allow_livefantasy' && $allow_livefantasy!=1)
            {
                unset($hub_list[$key]);
            }
			if($hub['game_key'] == 'picks_fantasy' && $allow_picks!=1)
            {
                unset($hub_list[$key]);
            }
			if($hub['game_key'] == 'pickem_tournament' && $allow_pickem_tournament!=1)
            {
                unset($hub_list[$key]);
            }
            if($hub['game_key'] == 'props_fantasy' && $allow_props!=1)
            {
                unset($hub_list[$key]);
            }

		}
		
		return $hub_list;
	}

	function check_module_enabled($module_key)
	{
		$enabled =  isset($this->app_config[$module_key])?$this->app_config[$module_key]['key_value']:0; 
		if($enabled ==0)
        {
            $this->api_response_arry['response_code'] = rest_controller::HTTP_INTERNAL_SERVER_ERROR;
            $this->api_response_arry['message']       = $this->lang->line("err_module_not_activated");
            $this->api_response();
        }
	}

	function get_app_config_value($module_key)
	{
		return  $enabled =  isset($this->app_config[$module_key])?$this->app_config[$module_key]['key_value']:0; 
	}
	
	public function get_leaderboard_type_list(){
        //leaderboard
        $leaderboard_key = 'leaderboard_list';
        $leaderboard = $this->get_cache_data($leaderboard_key);
        if (!$leaderboard) {
            $this->load->model("leaderboard/Leaderboard_model");
            $leaderboard = $this->Leaderboard_model->get_leaderboard_type();
            $this->set_cache_data($leaderboard_key, $leaderboard, REDIS_30_DAYS);
        }
        return $leaderboard;
    }

	public function pl_bot_config_details($key_name=''){
		$pl_allow = isset($this->app_config['pl_allow'])?$this->app_config['pl_allow']['key_value']:0;
		if($pl_allow == 0){
			return false;
		}
		$custom_data = isset($this->app_config['pl_allow']['custom_data']) ? $this->app_config['pl_allow']['custom_data'] : array();
		if(empty($custom_data)){
			return false;
		}

		if(isset($key_name) && $key_name != "" && isset($custom_data[$key_name])){
			return $custom_data[$key_name];
		}else{
			return $custom_data;
		}
	}

	
}
/* End of file MYREST_Controller.php */
/* Location: ./application/controllers/MYREST_Controller.php */