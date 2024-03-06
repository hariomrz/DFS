<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Nodb_model extends CI_Model {

    public function __construct() 
    {
       	parent::__construct();
    }

    public function send_email($to, $subject = "", $message = "", $from_email = FROM_ADMIN_EMAIL, $from_name = FROM_EMAIL_NAME )
	{
        
        /* configuration and sending email */
        $config = array();
        $config['smtp_host'] = SMTP_HOST;
        $config['smtp_user'] = SMTP_USER;
        $config['smtp_pass'] = SMTP_PASS;
        $config['smtp_port'] = SMTP_PORT;
        $config['protocol']  = PROTOCOL;
        $config['smtp_crypto'] = SMTP_CRYPTO;
        $config['mailpath']  = '';
        $config['mailtype']  = 'html';
        $config['charset']   = 'utf-8';
        $config['wordwrap']  = TRUE;

		$this->load->library('email');
        $email = new CI_Email();
        //var_dump($email);
        $email->initialize($config);
        //echo '<pre>';
        //print_r($config);die;
        $email->set_newline("\r\n");
        $email->clear();
        $email->from($from_email, $from_name);
        $email->to(trim($to));
        $email->subject($subject);

        $email->reply_to(NO_REPLY_EMAIL, $from_name);
        $email->message($message);

        if ($email->send()) {
            return TRUE;
        } else {
        	//$email->print_debugger(); //for debuging
            return FALSE;
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
     * Used for delete cache data by key
     * @param string $cache_key cache key
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

   
}
