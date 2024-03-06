<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	
	public $data           = array();
	public $user_id        = FALSE;
	public $language       = FALSE;
	public $lang_abbr      = FALSE;
	public $user_unique_id = FALSE;

	function __construct()
	{
		parent::__construct();

		$this->get_app_config_data();
	}


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
        if (is_array($cache_data)) {
            return $cache_data;
        } else {
            return array();
        }
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

     

    function get_app_config_data()
    {
        //check if affiliate master entry availalbe for email verify bonus w/o referral
        $app_config_cache_key = 'app_config';
        $data = $this->get_cache_data($app_config_cache_key);
        if (!$data) {
            $this->load->model("user/User_model");
            $result = $this->User_model->get_app_config_data();
    
            foreach($result as &$row) {
                if(!empty($row['custom_data'])) {
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

        $coins_balance_claim = isset($this->app_config['coins_balance_claim'])?$this->app_config['site_title']['key_value']:'';
        define('COINS_BALANCE_CLAIM', $coins_balance_claim);

        $fb_link = isset($this->app_config['fb_link'])?$this->app_config['fb_link']['key_value']:'';
        define('FB_LINK', $fb_link);

        $twitter_link = isset($this->app_config['twitter_link'])?$this->app_config['twitter_link']['key_value']:'';
        define('TWITTER_LINK', $twitter_link);

        $instagram_link = isset($this->app_config['instagram_link'])?$this->app_config['instagram_link']['key_value']:'';
        define('INSTAGRAM_LINK', $instagram_link);

        $credit_debit_card = isset($this->app_config['credit_debit_card'])?$this->app_config['credit_debit_card']['key_value']:'payumoney';
        define('CREDIT_DEBIT_CARD', $credit_debit_card);

        $paytm_wallet = isset($this->app_config['paytm_wallet'])?$this->app_config['paytm_wallet']['key_value']:'payumoney';
        define('PAYTM_WALLET', $paytm_wallet);

        $other_wallet = isset($this->app_config['other_wallet'])?$this->app_config['other_wallet']['key_value']:'payumoney';
        define('OTHER_WALLET', $other_wallet);

        $payment_upi = isset($this->app_config['payment_upi'])?$this->app_config['payment_upi']['key_value']:'payumoney';
        define('PAYMENT_UPI', $payment_upi);

        $net_banking = isset($this->app_config['net_banking'])?$this->app_config['net_banking']['key_value']:'payumoney';
        define('NET_BANKING', $net_banking);

        $report_admin_email = isset($this->app_config['report_admin_email'])?$this->app_config['report_admin_email']['key_value']:'';
        define('REPORT_ADMIN_EMAIL', $report_admin_email);

        $fcm_key = isset($this->app_config['fcm_key'])?$this->app_config['fcm_key']['key_value']:'';
        define('FCM_KEY', $fcm_key);

        $pl_allow_data = isset($this->app_config['pl_allow']['key_value'])?$this->app_config['pl_allow']['custom_data']:0;
        if(!empty($pl_allow_data))
        {
            define('PL_ALLOW', $this->app_config['pl_allow']['key_value']);
            define('PL_WEBSITE_ID', $pl_allow_data['website_id']);
            define('PL_WEBSITE_TOKEN', $pl_allow_data['token']);
            define('PL_WEBSITE_API', $pl_allow_data['api']);
            define('PL_TEAM_ERROR_EMAIL', $pl_allow_data['error_email']);
        }
        
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

        $max_contest_bonus = isset($this->app_config['max_contest_bonus'])?$this->app_config['max_contest_bonus']['key_value']:0;
        define('MAX_CONTEST_BONUS', $max_contest_bonus);

        $currency_code = isset($this->app_config['currency_code'])?$this->app_config['currency_code']['key_value']:7;
        define('CURRENCY_CODE', $currency_code);
        define('CURRENCY_CODE_HTML', CURRENCY_CODE);
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
}
