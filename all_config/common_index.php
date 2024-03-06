<?php


require_once __DIR__.'/../vendor/autoload.php';

define('DEFAULT_TIME_ZONE','UTC');
date_default_timezone_set(DEFAULT_TIME_ZONE);

$dotenv = new Dotenv\Dotenv(__DIR__."/../");
$dotenv->load();

$api_version = "v1";
if (php_sapi_name() == 'cli') {

    $environment = 'development';
    if (isset($argv)) {
        // grab the --env argument, and the one that comes next
        $key = (array_search('--env', $argv));
        $environment = $argv[$key + 1];
        // get rid of them so they don't get passed in to our method as parameter values
        unset($argv[$key], $argv[$key + 1]);
    }
    $_SERVER['CI_ENV'] = $environment;
}else{
	$all_headers = getallheaders();
	if(isset($all_headers['Apiversion']) && in_array($all_headers['Apiversion'], array("v1","v2"))){
		$api_version = $all_headers['Apiversion'];
	}else if(isset($all_headers['apiversion']) && in_array($all_headers['apiversion'], array("v1","v2"))){
		$api_version = $all_headers['apiversion'];
	}

	switch($_SERVER['SERVER_NAME'])
	{
		case 'local.framework.com':
		case '192.168.5.52':
			$_SERVER['CI_ENV'] = 'development';
		break;
		default:
			$_SERVER['CI_ENV'] = 'production';
		break;
	}
}

define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
defined('API_VERSION') OR define('API_VERSION', $api_version);