<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------------
  | DATABASE CONNECTIVITY SETTINGS
  | -------------------------------------------------------------------------
  | This file will contain the settings needed to access your Mongo database.
  |
  |
  | ------------------------------------------------------------------------
  | EXPLANATION OF VARIABLES
  | ------------------------------------------------------------------------
  |
  |	['hostname'] The hostname of your database server.
  |	['username'] The username used to connect to the database
  |	['password'] The password used to connect to the database
  |	['database'] The name of the database you want to connect to
  |	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
  |	['write_concerns'] Default is 1: acknowledge write operations.  ref(http://php.net/manual/en/mongo.writeconcerns.php)
  |	['journal'] Default is TRUE : journal flushed to disk. ref(http://php.net/manual/en/mongo.writeconcerns.php)
  |	['read_preference'] Set the read preference for this connection. ref (http://php.net/manual/en/mongoclient.setreadpreference.php)
  |	['read_preference_tags'] Set the read preference for this connection.  ref (http://php.net/manual/en/mongoclient.setreadpreference.php)
  |
  | The $config['mongo_db']['active'] variable lets you choose which connection group to
  | make active.  By default there is only one group (the 'default' group).
  |
 */

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