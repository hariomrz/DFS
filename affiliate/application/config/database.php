<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['default'] = array(
					'dsn'	=> '',	
					'hostname' => getenv('AF_DBHOSTNAME'),
					'username' => getenv('AF_DBUSERNAME'),
					'password' => getenv('AF_DBPASSWORD'),
					'database' => getenv('AF_DBNAME'),
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
$db['default'] = $config['default'];