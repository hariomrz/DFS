<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH."../../all_config/common_db.php";
$config['db_picks'] = array(
					'dsn'	=> '',	
					'hostname' => getenv('PICKS_DBHOSTNAME'),
					'username' => getenv('PICKS_DBUSERNAME'),
					'password' => getenv('PICKS_DBPASSWORD'),
					'database' => getenv('PICKS_DBNAME'),
					'dbdriver' => 'mysqli',
					'dbprefix' => 'vi_',
					'pconnect' => FALSE,
					'db_debug' => (ENVIRONMENT !== 'production'),
					'cache_on' => FALSE,
					'cachedir' => '',
					'char_set' => 'utf8',
					'dbcollat' => 'utf8_general_ci',
					'swap_pre' => '',
					'encrypt' => FALSE,
					'compress' => FALSE,
					'stricton' => FALSE,
					'failover' => array(),
					'save_queries' => (SAVE_QUERIES == 1)
				);

$active_group = 'default';
$db['default'] = $config["db_picks"];
$db['picks_db'] = $config["db_picks"];
$db['user_db'] = $config["db_user"];
