<?php

/**
 * GoogleAnalyticsAPI
 *
 */
class GoogleAnalyticsAPI {
	
    /**
     * Constructor
     * @access public
     */
    public function __construct() {
        require_once realpath(dirname(__FILE__) . '/Google/autoload.php');
        require_once realpath(dirname(__FILE__) . '/Google/Client.php');
        require_once realpath(dirname(__FILE__) . '/Google/Service/Analytics.php');
    }


}
?>
