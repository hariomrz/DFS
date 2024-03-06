<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;
$active_record = TRUE;
include APPPATH."../common_config/database.php";

$db['default'] = $config["db_default"];
$db['db_archive'] = $config["db_archive"];

