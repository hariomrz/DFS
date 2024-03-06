<?php
class MyOutput {
    private $CI;
    public function __construct()
    {
        $this->CI = &get_instance();
    }
    public function display_cache($ses) {
        echo "dsggggggg";
        print_r($this->CI);
        die;
        
        
        /* Simple Test for Ip Address */
        if ($_SERVER['REMOTE_ADDR'] == 'NOCACHE_IP' ) {
            parent::delete_cache();
            return FALSE;
        }
        /* Simple Test for a cookie value */
        if ( (isset($_COOKIE['nocache'])) && ( $_COOKIE['nocache'] > 0 ) )
        {
            return FALSE;
        }
        /* Call the parent function */
       return parent::_display_cache($CFG,$URI);
    }
}
