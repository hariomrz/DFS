<?php defined('BASEPATH') OR exit('No direct script access allowed');

$config['socket_type'] = getenv('REDIS_SOCKET_TYPE'); //`tcp` or `unix`
$config['socket'] = getenv('REDIS_SOCKET'); // in case of `unix` socket type
$config['timeout'] = getenv('REDIS_TIMEOUT');
$config['host'] = getenv('REDIS_HOST');
$config['password'] = getenv('REDIS_PASSWORD');
$config['port'] = getenv('REDIS_PORT');