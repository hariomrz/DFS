<?php

/**
 * Description of MY_Log
 *
 * @author Nitins
 */
class MY_Log extends CI_Log {

    private $log_type; //DB or FILE
    private $tabl_name = 'ErrorLogs';
    
    public function __construct() {
        parent::__construct();
        $this->_levels = array('ERROR' => '1', 'DEBUG' => '2', 'INFO' => '3', 'ALL' => '4');
        if(ENVIRONMENT == "development")
        {
            //Uncomment below line to enable error log into db
            $this->log_type = 'db';            
        }
        
    }

    /**
     * Write Log File
     * Generally this function will be called using the global 
     * @param    string    the error level
     * @param    string    the error message
     * @param    bool    whether the error is a native PHP error
     * @return    bool
     */
    public function write_log($level = 'error', $msg, $php_error = FALSE) {

        if ($this->log_type == 'db' && $php_error) {
            $this->write_to_db($level, $msg, $php_error);
        } else {
            parent::write_log($level, $msg, $php_error);
        }
    }

    /**
     * Write error log into db TO use this function you should have to
     * create a table into db and define table name at $table_name variable
     *
     * @param type $level
     * @param type $msg
     * @param type $php_error
     * @return boolean
     */
    public function write_to_db($level = 'error', $msg, $php_error = FALSE) {
        try {
            
            if ($this->_enabled === FALSE) {
                return FALSE;
            }
            
            $level = strtoupper($level);

            if (!isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold)) {
                return FALSE;
            }
            
            if($php_error==1 && $level == "ERROR"){
                //Load DB
                $CI = & get_instance();
                $CI->load->database();

                $IPAddress = getRealIpAddr();            
                $errorArray = array(
                                    'ErrorTypeID' => 1,
                                    'Title' => substr($msg, 0,100),
                                    'Source' => 'Web',
                                    'ErrorDescription' => $msg,
                                    'BrowserDetail' => $this->getBrowserName(),
                                    'OperatingSystem' => $this->getClientOS(),
                                    'IPAddress' => $IPAddress,
                                    'City' => 'Indore',
                                    'State' => 'M.P.',
                                    'Country' => 'India',
                                    'Reporter' => '',
                                    'ReporterEmail' => '',
                                    'CreatedDate' => date("Y-m-d H:i:s"),
                                    'ModifiedDate' => date("Y-m-d H:i:s"),
                                    'StatusID' => 1
                                );
                if($_SERVER['HTTP_HOST'])
                {
                    $CI->db->insert($this->tabl_name, $errorArray);
                }
                $errorArray = array();
                return true;
            }

        } catch (Exception $e) {
            var_dump($e);
        }
    }
    
    /**
     * Function for get Client Operating system name
     * @return string
     */
    public function getClientOS() { 

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $os_platform    =   "Unknown OS Platform";
        $os_array       =   array(
                                '/windows nt 6.3/i'     =>  'Windows 8.1',
                                '/windows nt 6.2/i'     =>  'Windows 8',
                                '/windows nt 6.1/i'     =>  'Windows 7',
                                '/windows nt 6.0/i'     =>  'Windows Vista',
                                '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
                                '/windows nt 5.1/i'     =>  'Windows XP',
                                '/windows xp/i'         =>  'Windows XP',
                                '/windows nt 5.0/i'     =>  'Windows 2000',
                                '/windows me/i'         =>  'Windows ME',
                                '/win98/i'              =>  'Windows 98',
                                '/win95/i'              =>  'Windows 95',
                                '/win16/i'              =>  'Windows 3.11',
                                '/macintosh|mac os x/i' =>  'Mac OS X',
                                '/mac_powerpc/i'        =>  'Mac OS 9',
                                '/linux/i'              =>  'Linux',
                                '/ubuntu/i'             =>  'Ubuntu',
                                '/iphone/i'             =>  'iPhone',
                                '/ipod/i'               =>  'iPod',
                                '/ipad/i'               =>  'iPad',
                                '/android/i'            =>  'Android',
                                '/blackberry/i'         =>  'BlackBerry',
                                '/webos/i'              =>  'Mobile'
                            );

        foreach ($os_array as $regex => $value) { 
            if (preg_match($regex, $user_agent)) {
                $os_platform    =   $value;
            }
        }
        
        return $os_platform;
    }

    /**
     * Function for get user Browser Name
     * @return string
     */
    public function getBrowserName() {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser        =   "Unknown Browser";
        $browser_array  =   array(
                                '/msie/i'       =>  'Internet Explorer',
                                '/firefox/i'    =>  'Firefox',
                                '/safari/i'     =>  'Safari',
                                '/chrome/i'     =>  'Chrome',
                                '/opera/i'      =>  'Opera',
                                '/netscape/i'   =>  'Netscape',
                                '/maxthon/i'    =>  'Maxthon',
                                '/konqueror/i'  =>  'Konqueror',
                                '/mobile/i'     =>  'Handheld Browser'
                            );

        foreach ($browser_array as $regex => $value) { 

            if (preg_match($regex, $user_agent)) {
                $browser    =   $value;
            }
        }

        return $browser;
    }


}
