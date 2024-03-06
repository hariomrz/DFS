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

        $this->config->set_item('language_list',$language_list);

       
        $config_app_language_list= array();

        foreach($app_language_list as $key => $value)
        {
            $config_app_language_list[] = array("value"=>$key,"label"=>$value);
        }
        $this->config->set_item('app_language_list',$config_app_language_list);
        
        $max_contest_bonus = isset($this->app_config['max_contest_bonus'])?$this->app_config['max_contest_bonus']['key_value']:0;
        define('MAX_CONTEST_BONUS', $max_contest_bonus);
    }
}