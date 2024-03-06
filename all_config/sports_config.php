<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*Feed Provider List -  Please update your sports feed provide in feed config*/
$config['cricket_feed_providers'] 	= "vinfotech";
$config['kabaddi_feed_providers'] 	= "vinfotech";
$config['soccer_feed_providers']  	= "vinfotech";
$config['basketball_feed_providers']= "vinfotech";
$config['nfl_feed_providers']  		= "vinfotech";
$config['ncaa_feed_providers']  	= "goalserve";
$config['baseball_feed_providers'] 	= "vinfotech";
$config['cfl_feed_providers']  		= "goalserve";
$config['ncaa_basketball_feed_providers']  	= "goalserve";
$config['motorsport_feed_providers']= "goalserve";
$config['tennis_feed_providers']= "goalserve";
$config['football_feed_providers'] 	= "vinfotech";
$vinfotech_feed_url = getenv('VINFOTECH_FEED_URL');
$vinfotech_feed_access_token = getenv('VINFOTECH_FEED_ACCESS_TOKEN');

$config["cricket_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/cricket/cricket/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
							'all_fixture'	=> '0' //0 ->Publish from feed, 1->All fixture
						)
					);

$config["kabaddi_config"] = array(
							"vinfotech"=>array(
								'api_url'       => $vinfotech_feed_url.'/sports/kabaddi/kabaddi/',
								'year'     	 	=> date('Y'),
								'access_token' 	=> $vinfotech_feed_access_token,
								'all_fixture'	=> '0' //0 ->Publish from feed, 1->All fixture
							)
						);

$config["soccer_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/soccer/soccer/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
							'all_fixture'	=> '0' //0 ->Publish from feed, 1->All fixture
						)
					);

$config["basketball_config"] = array(
										"goalserve"=>array(
											'api_url' => 'http://www.goalserve.com/getfeed/',
											'api_url_abbr' => '',
											'league_abbr' => 'bsktbl',
											'api_league_id' => '1046',
											'format' => 'xml',
											'year' => date('Y'),
											'season' => 'REG',
											'stage_name' => array(),
											'subscription_key' => '3d9da7869f194cd7afdb7660771ce403'
										),
										"vinfotech"=>array(
										'api_url'       => $vinfotech_feed_url.'/sports/basketball/basketball/',
										'year'     	 	=> date('Y'),
										'access_token' 	=> $vinfotech_feed_access_token,
										'all_fixture'	=> '0' //0 ->Publish from feed, 1->All fixture
											)
								);		

$config["nfl_config"] = array(
								"goalserve" => 

								array(

										"NFL" => array(
													'api_url' => 'http://www.goalserve.com/getfeed/',
													'api_url_abbr' => 'football/nfl-shedule',
													'league_abbr' => 'nfl',
													'api_league_id' => '1',
													'format' => 'xml',
													'year' => date('Y'),
													'season' => 'REG',
													'stage_name' => array("Pre Season","Regular Season","Post Season"),
													'subscription_key'=> 'a28fc23ec9e947cc94fed2334b6ce52c'
												)					
											),
										"vinfotech"=>array(
											'api_url'       => $vinfotech_feed_url.'/sports/feed/football/',
											'year'     	 	=> date('Y'),
											'access_token' 	=> $vinfotech_feed_access_token,
										)

						);

$config["ncaa_config"] = array(
								"goalserve" => 

								array(
								"NCAA" => array(
													'api_url' => 'http://www.goalserve.com/getfeed/',
													'api_url_abbr' => 'football/fbs-shedule',
													'league_abbr' => 'fbs',
													'api_league_id' => '2',
													'format' => 'xml',
													'year' => date('Y'),
													'season' => 'REG',
													'stage_name' => array("FBS (Division I-A)"),
													'subscription_key'=> '8ee42d3935f2492cc52508d850c540b2'
												)					
											)
						);

$config["baseball_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/baseball/baseball/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
							'all_fixture'	=> '0' //0 ->Publish from feed, 1->All fixture
						)
					);

$config["cfl_config"] = array(
								"goalserve" => 

								array(

										"CFL" => array(
													'api_url' => 'http://www.goalserve.com/getfeed/',
													'api_url_abbr' => 'football/nfl-shedule',
													'league_abbr' => 'cfl',
													'api_league_id' => '1',
													'format' => 'xml',
													'year' => date('Y'),
													'season' => 'REG',
													'stage_name' => array("Pre Season","Regular Season","Post Season"),
													'subscription_key'=> 'a28fc23ec9e947cc94fed2334b6ce52c'
												)					
											)
						);

$config["ncaa_basketball_config"] = array(
										
										"goalserve"=>array(
											'api_url' => 'http://www.goalserve.com/getfeed/',
											'api_url_abbr' => 'bsktbl',
											'league_abbr' => 'ncaa',
											'api_league_id' => '1049',
											'format' => 'xml',
											'year' => date('Y'),
											'season' => 'REG',
											'stage_name' => array(),
											'subscription_key' => '63f4c97e11594a6e513308d9735aa0b1'
										)
								);

$config["motorsport_config"] = array(
						"goalserve"=>array(
							'api_url' => 'http://www.goalserve.com/getfeed/',
							'format' => 'xml',
							'year' => date('Y'),
							'season' => 'REG',
							'stage_name' => array(),
							'subscription_key' => 'a28fc23ec9e947cc94fed2334b6ce52c'
										)
					);

$config["tennis_config"] = array(
						"goalserve"=>array(
							'api_url' => 'http://www.goalserve.com/getfeed/',
							'format' => 'xml',
							'year' => date('Y'),
							'season' => 'REG',
							'stage_name' => array(),
							'subscription_key' => 'a28fc23ec9e947cc94fed2334b6ce52c'
										)
						);
$config["football_config"] = array(
						"vinfotech"=>array(
							'api_url'       => $vinfotech_feed_url.'/sports/feed/football/',
							'year'     	 	=> date('Y'),
							'access_token' 	=> $vinfotech_feed_access_token,
						)
					);
