<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['socket_type'] 		= REDIS_SOCKET_TYPE; //`tcp` or `unix`
$config['socket'] 			= REDIS_SOCKET; // in case of `unix` socket type
$config['timeout'] 			= REDIS_TIMEOUT;
$config['host'] 			= REDIS_HOST;
$config['password'] 		= REDIS_PASSWORD;
$config['port'] 			= REDIS_PORT;