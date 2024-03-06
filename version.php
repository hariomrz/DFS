<?php require_once __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__."/");
$dotenv->load();
echo "<h2>Version : ".getenv('CODE_VERSION')."</h2>";die;
?>