<?php defined('BASEPATH') OR exit('No direct script access allowed');

$vinfotech_feed_url = getenv('VINFOTECH_FEED_URL');
$vinfotech_feed_access_token = getenv('VINFOTECH_FEED_ACCESS_TOKEN');

/*Feed Provider List -  Please update your sports feed provide in feed config*/
$config['cricket_feed_providers'] 		= "vinfotech";
$config['soccer_feed_providers'] 		= "vinfotech";
$config['basketball_feed_providers'] 	= "vinfotech";
$config['kabaddi_feed_providers'] 	    = "vinfotech";
$config['football_feed_providers'] 	= "vinfotech";

$config["cricket_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/cricket/cricket/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
						)
					);

$config["soccer_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/soccer/soccer/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
						)
					);

$config["basketball_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/basketball/basketball/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
						)
					);

$config["kabaddi_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/kabaddi/kabaddi/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
						)
					);
$config["football_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/feed/football/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
						)
					);

