<?php

$whitelist = array(
    '127.0.0.1',
    '::1',
    'localhost'
);
$swggeger_api_path = "/var/www/html/social/swagger_api_docs/";
if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
    $swggeger_api_path= '../';
} 
//echo $swggeger_api_path;die;
//$swggeger_api_path = "/var/www/html/swagger_api_docs/";

$front_arr = $api_data = include $swggeger_api_path.'api/api_data_arr/front_arr.php';       //print_r($front_arr); die;
$admin_arr = $api_data = include $swggeger_api_path.'api/api_data_arr/admin_arr.php';

foreach($front_arr as $front_json_cmd) {
    echo exec($front_json_cmd['JsonCreateCMD']);
}

foreach($admin_arr as $admin_json_cmd) {
    echo exec($admin_json_cmd['JsonCreateCMD']);
}



//echo shell_exec($cmd);

//echo system($cmd);

echo 'done';
