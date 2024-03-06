<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH."../../all_config/common_db.php";

$config['db_trade'] = array(
				'dsn'	=> '',	
				'hostname' => getenv('TRADE_DBHOSTNAME'),
				'username' => getenv('TRADE_DBUSERNAME'),
				'password' => getenv('TRADE_DBPASSWORD'),
				'database' => getenv('TRADE_DBNAME'),
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

$active_group = 'trade_db';
$query_builder = TRUE;
$db['trade_db'] = $config["db_trade"];
$db['user_db'] = $config["db_user"];
