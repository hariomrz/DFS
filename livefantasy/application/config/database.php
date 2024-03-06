<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH."../../all_config/common_db.php";
$config['db_livefantasy'] = array(
					'dsn'	=> '',	
					'hostname' => getenv('LF_DBHOSTNAME'),
					'username' => getenv('LF_DBUSERNAME'),
					'password' => getenv('LF_DBPASSWORD'),
					'database' => getenv('LF_DBNAME'),
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
$db['default'] = $config["db_livefantasy"];
$db['livefantasy_db'] = $config["db_livefantasy"];
$db['user_db'] = $config["db_user"];
