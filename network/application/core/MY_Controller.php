<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	
	public $data           = array();
	public $email          = FALSE;
	public $user_id        = FALSE;
	public $language       = FALSE;
	public $lang_abbr      = FALSE;
	public $user_unique_id = FALSE;

	function __construct()
	{
		parent::__construct();
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization," . AUTH_KEY.",Version,Apiversion,User-Token,Device,RequestTime,Cookie,_ga_token,X-RefID");
		//$this->_check_cors();
		$this->get_app_config_data();
		$this->set_lang();
	}

	protected function _check_cors()
	{
		return true;
		// If the request HTTP method is 'OPTIONS', kill the response and send it to the client
		if ($this->input->method() === 'options')
		{
			exit;
		}
	}

	public function set_lang($lang=FALSE)
	{
		$language_list = $this->config->item('language_list');
		if(!$lang)
		{
			$header_language = $this->input->get_request_header('language');
			
			if ($header_language&&isset($language_list[$header_language]))
			{
				$lang = $language_list[$header_language];
			}
			else
			{
				$lang = $this->config->item('language');
			}
		}
		else
		{
			if($lang && isset($language_list[$lang]))
			{
				$lang = $language_list[$lang];
			}
			else if ($lang && in_array($lang, $language_list))
			{
				$lang = trim($lang);
			}
			else
			{
				$lang = $this->config->item('language');
			}
		}

		$this->language = $lang;
		$this->lang_abbr = array_search($lang,$language_list);
		$this->config->set_item('language', $this->language);
		$this->lang->load('general', $this->language);
		return TRUE;
	}

	public function master_response_array($temp_api_response)
    {
        $main_response = array();
        if(!is_array($temp_api_response))
        {
            $temp_api_response = json_decode($temp_api_response,true);
        }
        if(!empty($temp_api_response) && is_array($temp_api_response))
        {
             if(isset($temp_api_response['response_code']))
             {
                $main_response = $temp_api_response;
             }  
             else
             {
                $main_response['response_code'] = 500;
                $main_response['global_error']  = $this->lang->line('action_cant_completed_err');
                $main_response['error']  = $this->lang->line('action_cant_completed_err');

             } 
        } 
        else
        {
            $main_response['response_code'] = 500;
            $main_response['global_error']  = $this->lang->line('action_cant_completed_err');
            $main_response['error']  = $this->lang->line('action_cant_completed_err');
        }  

        return $main_response;  

    }

	function http_post_request($url, $params = array(), $api_type = 1, $debug = false)
    {
       
        $post_data_json = json_encode($params);
        $header = array("Content-Type:application/json", "Accept:application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
        return ($output);
    }

	/**
	 * @Summary: This function for use check URL hit by Webbrowser or schedular by cron(wget)
	 * @access: protected
	 * @param:
	 * @return: true or false
	 */
	protected function check_url_hit()
	{
		//Check url hit by server or manual
		/*if ( ENVIRONMENT == 'production'  )
		{
			$http_user_agent = substr($_SERVER['HTTP_USER_AGENT'],0,4);
			if(strtolower($http_user_agent) == 'wget' || strtolower($http_user_agent) == 'curl')
			{
				return TRUE;
			}else
			{
				redirect('');
				//exit('Direct access not allowed');
			}
		}*/
		return true;
	}

	function get_app_config_data()
    {
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $app_config_cache_key = 'app_config';
        $data = $this->get_cache_data($app_config_cache_key);
        if (!$data) {
            $this->load->model("user/User_model");
            $result = $this->User_model->get_all_config();
    
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
        if (is_array($cache_data)) {
            return $cache_data;
        } else {
            return array();
        }
    }

    /**
     * Used for load cache driver
     * @return 
     */
    private function init_cache_driver() {
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER, 'backup' => 'file'));
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

        $coins_balance_claim = isset($this->app_config['coins_balance_claim'])?$this->app_config['site_title']['key_value']:'';
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
        $allow_indonesian = isset($this->app_config['allow_indonesian'])?$this->app_config['allow_indonesian']['key_value']:0;
		$allow_chinese = isset($this->app_config['allow_chinese'])?$this->app_config['allow_chinese']['key_value']:0;
		$allow_tagalog = isset($this->app_config['allow_tagalog'])?$this->app_config['allow_tagalog']['key_value']:0;
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

        $android_app = isset($this->app_config['android_app']['key_value'])?$this->app_config['android_app']['custom_data']:0;
        if(!empty($android_app))
        {
            define('ANDROID_APP_LINK', $android_app['android_app_link']);
            define('ANDROID_MIN_VER', $android_app['android_min_ver']);
            define('ANDROID_CURRENT_VER', $android_app['android_current_ver']);
        }

        $ios_app = isset($this->app_config['ios_app']['key_value'])?$this->app_config['ios_app']['custom_data']:0;
        if(!empty($ios_app))
        {
            define('IOS_APP_LINK', $ios_app['ios_app_link']);
            define('IOS_MIN_VER', $ios_app['ios_min_ver']);
            define('IOS_CURRENT_VER', $ios_app['ios_current_ver']);
        }

        $default_sports_id = isset($this->app_config['default_sports_id'])?$this->app_config['default_sports_id']['key_value']:7;
        define('DEFAULT_SPORTS_ID', $default_sports_id);

        $allow_bank_transfer = isset($this->app_config['allow_bank_transfer'])?$this->app_config['allow_bank_transfer']['key_value']:7;
        define('ALLOW_BANK_TRANSFER', $allow_bank_transfer);

        $allow_mpesa_withdraw = isset($this->app_config['allow_mpesa_withdraw'])?$this->app_config['allow_mpesa_withdraw']['key_value']:7;
        define('ALLOW_MPESA_WITHDRAW', $allow_mpesa_withdraw);

        $allow_private_contest = isset($this->app_config['allow_private_contest'])?$this->app_config['allow_private_contest']['key_value']:7;
        define('ALLOW_PRIVATE_CONTEST', $allow_private_contest);

        $bucket_static_data_allowed = isset($this->app_config['bucket_static_data_allowed'])?$this->app_config['bucket_static_data_allowed']['key_value']:7;
        define('BUCKET_STATIC_DATA_ALLOWED', $bucket_static_data_allowed);

        $bucket_data_prefix = isset($this->app_config['bucket_data_prefix'])?$this->app_config['bucket_data_prefix']['key_value']:7;
        define('BUCKET_DATA_PREFIX', $bucket_data_prefix);

        $int_version = isset($this->app_config['int_version'])?$this->app_config['int_version']['key_value']:7;
        define('INT_VERSION', $int_version);

        $currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:7;
        define('CURRENCY_CODE', $currency_code);
        define('CURRENCY_CODE_HTML', CURRENCY_CODE);
    }
}
/* End of file MY_Controller.php */
/* Location: application/core/MY_Controller.php */