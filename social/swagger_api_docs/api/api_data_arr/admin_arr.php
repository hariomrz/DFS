<?php 

$swggeger_api_path = "/var/www/html/social/swagger_api_docs/";
$swggeger_run_path = "/var/www/html/social/vendor/bin/swagger";

$whitelist = array(
    '127.0.0.1',
    '::1',
    'localhost'
);

$base_path = '';
if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
    $base_path = '/framework';
    $swggeger_api_path = "D://xampp//htdocs//framework//social//swagger_api_docs//";
    $swggeger_run_path = "D://xampp//htdocs//framework//social//vendor//bin//swagger";
    
} 


return array(
    array(
        'api_name' => 'Login', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=login&from=admin',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}admin_api/Login.php  --output  {$swggeger_api_path}json/admin/login.json"
    ),
    array(
        'api_name' => 'Quiz', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=quiz&from=admin',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}admin_api/Quiz.php  --output  {$swggeger_api_path}json/admin/quiz.json"
    ),
    array(
        'api_name' => 'Users', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=users&from=admin',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}admin_api/Users.php  --output  {$swggeger_api_path}json/admin/users.json"
    )
    /* array(
        'api_name' => 'Admin Dashboard', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=dashboard&from=admin',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}admin_api/Dashboard.php  --output  {$swggeger_api_path}json/admin/dashboard.json"
    ),
    array(
        'api_name' => 'Rules', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=rules&from=admin',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}admin_api/Rules.php  --output  {$swggeger_api_path}json/admin/rules.json"
    ),
                
    array(
        'api_name' => 'CRM', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=admin_crm&from=admin',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}admin_api/Admin_crm.php  --output  {$swggeger_api_path}json/admin/admin_crm.json"
    ),
    array(
        'api_name' => 'Newsletter', 
        'api_url' => $base_path.'/swagger_api_docs/api/swagger.php?api_json=newsletter&from=admin',
        'JsonCreateCMD' => "$swggeger_run_path {$swggeger_api_path}admin_api/Newsletter.php  --output  {$swggeger_api_path}json/admin/newsletter.json"
    ),
    */
);
