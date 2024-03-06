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

    public function delete_cache_and_bucket_cache($data_arr=array())
    {
        $bucket_data = isset($this->app_config['bucket_data']['key_value']) ? $this->app_config['bucket_data']['key_value'] : '0';
        if($bucket_data && !empty($data_arr)){
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

    /**
     * Used for push s3 data in queue
     * @param string $file_name json file name
     * @param array $data api file data
     * @return 
     */
    public function push_s3_data_in_queue($file_name, $data = array(), $action = "save") {
        $bucket_data = isset($this->app_config['bucket_data']['key_value']) ? $this->app_config['bucket_data']['key_value'] : '0';
        if ($bucket_data == "0" || $file_name == "") {
            return false;
        }
        $bucket_data = array("file_name" => $file_name, "data" => $data, "action" => $action);
        $this->load->helper('queue_helper');
        add_data_in_queue($bucket_data, 'bucket');
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