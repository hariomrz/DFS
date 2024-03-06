<?php

$json_file_name = $_GET['json'];
$from_platform = $_GET['from'];

$json_data = file_get_contents("./$from_platform/".$json_file_name.'.json');


$whitelist = array(
    '127.0.0.1',
    '::1',
    'localhost'
);

if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
    $json_data = json_decode($json_data, true);
    $json_data['host'] = 'social.vinfotech.org';
    if($_SERVER['SERVER_NAME'] == 'social.vinfotech.org') {
        $json_data['host'] = 'social.vinfotech.org';
    }
    
    $json_data = json_encode($json_data);
} 


echo $json_data;
