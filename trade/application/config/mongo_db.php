<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config['mongo_db']['active'] = 'default';
$config['mongo_db']['default'] = array(
    'no_auth' => MONGO_NO_AUTH,
    'hostname' => MONGO_DBHOSTNAME,
    'username' => MONGO_DBUSERNAME,
    'password' => MONGO_DBPASSWORD,
    'database' => MONGO_DBNAME,
    'port' => MONGO_PORT,
    'return_as' => 'array',
    'db_debug' => (ENVIRONMENT !== 'production'),
    'write_concerns' => (int) 1,
    'journal' => TRUE,
    'read_preference' => MONGO_RP,
    'read_preference_tags' => NULL,
    'read_concern' => MONGO_RC,
    'legacy_support' => TRUE,
    'SRV' => MONGO_SRV
);

/* End of file database.php */
/* Location: ./application/config/database.php */