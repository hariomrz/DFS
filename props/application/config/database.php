<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH."../../all_config/common_db.php";

$config['db_props'] = array(
				'dsn'	=> '',	
				'hostname' => getenv('PROPS_DBHOSTNAME'),
				'username' => getenv('PROPS_DBUSERNAME'),
				'password' => getenv('PROPS_DBPASSWORD'),
				'database' => getenv('PROPS_DBNAME'),
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

$active_group = 'props_db';
$query_builder = TRUE;
$db['props_db'] = $config["db_props"];
$db['user_db'] = $config["db_user"];
