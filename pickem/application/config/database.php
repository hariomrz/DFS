<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['db_pickem'] = array(
					'dsn'	=> '',	
					'hostname' => getenv('PICKEM_DBHOSTNAME'),
					'username' => getenv('PICKEM_DBUSERNAME'),
					'password' => getenv('PICKEM_DBPASSWORD'),
					'database' => getenv('PICKEM_DBNAME'),
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

$active_group = 'db_pickem';
$query_builder = TRUE;

include APPPATH."../../all_config/common_db.php";
$db['db_pickem'] = $config["db_pickem"];
$db['user_db'] = $config["db_user"];
