<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH."../../common_config/config.php";

/*
|--------------------------------------------------------------------------
| Error Logging Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/logs/ directory. Use a full server path with trailing slash.
|
*/
$config['log_path'] = APPPATH."../logs/";
$config['sess_save_path'] = APPPATH . '../sessions/';
