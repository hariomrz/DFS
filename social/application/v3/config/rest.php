<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| HTTP protocol
|--------------------------------------------------------------------------
|
| Set to force the use of HTTPS for REST API calls
|
*/
$config['force_https'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Output Format
|--------------------------------------------------------------------------
|
| The default format of the response
|
| 'array':      Array data structure
| 'csv':        Comma separated file
| 'json':       Uses json_encode(). Note: If a GET query string
|               called 'callback' is passed, then jsonp will be returned
| 'html'        HTML using the table library in CodeIgniter
| 'php':        Uses var_export()
| 'serialized':  Uses serialize()
| 'xml':        Uses simplexml_load_string()
|
*/
$config['rest_default_format'] = 'json';

/*
|--------------------------------------------------------------------------
| REST Supported Output Formats
|--------------------------------------------------------------------------
|
| The following setting contains a list of the supported/allowed formats.
| You may remove those formats that you don't want to use.
| If the default format $config['rest_default_format'] is missing within
| $config['rest_supported_formats'], it will be added silently during
| REST_Controller initialization.
|
*/
$config['rest_supported_formats'] = [
    'json',
    'array',
    'csv',
    'html',
    'jsonp',
    'php',
    'serialized',
    'xml',
];

/*
|--------------------------------------------------------------------------
| REST Status Field Name
|--------------------------------------------------------------------------
|
| The field name for the status inside the response
|
*/
$config['rest_status_field_name'] = 'status';

/*
|--------------------------------------------------------------------------
| REST Message Field Name
|--------------------------------------------------------------------------
|
| The field name for the message inside the response
|
*/
$config['rest_message_field_name'] = 'error';

/*
|--------------------------------------------------------------------------
| Enable Emulate Request
|--------------------------------------------------------------------------
|
| Should we enable emulation of the request (e.g. used in Mootools request)
|
*/
$config['enable_emulate_request'] = TRUE;

/*
|--------------------------------------------------------------------------
| REST Realm
|--------------------------------------------------------------------------
|
| Name of the password protected REST API displayed on login dialogs
|
| e.g: My Secret REST API
|
*/
$config['rest_realm'] = 'REST API';

/*
|--------------------------------------------------------------------------
| REST Login
|--------------------------------------------------------------------------
|
| Set to specify the REST API requires to be logged in
|
| FALSE     No login required
| 'basic'   Unsecured login
| 'digest'  More secured login
| 'session' Check for a PHP session variable. See 'auth_source' to set the
|           authorization key
|
*/
$config['rest_auth'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Login Source
|--------------------------------------------------------------------------
|
| Is login required and if so, the user store to use
|
| ''        Use config based users or wildcard testing
| 'ldap'    Use LDAP authentication
| 'library' Use a authentication library
|
| Note: If 'rest_auth' is set to 'session' then change 'auth_source' to the name of the session variable
|
*/
$config['auth_source'] = 'ldap';

/*
|--------------------------------------------------------------------------
| Allow Authentication and API Keys
|--------------------------------------------------------------------------
|
| Where you wish to have Basic, Digest or Session login, but also want to use API Keys (for limiting
| requests etc), set to TRUE;
|
*/
$config['allow_auth_and_keys'] = TRUE;

/*
|--------------------------------------------------------------------------
| REST Login Class and Function
|--------------------------------------------------------------------------
|
| If library authentication is used define the class and function name
|
| The function should accept two parameters: class->function($username, $password)
| In other cases override the function _perform_library_auth in your controller
|
| For digest authentication the library function should return already a stored
| md5(username:restrealm:password) for that username
|
| e.g: md5('admin:REST API:1234') = '1e957ebc35631ab22d5bd6526bd14ea2'
|
*/
$config['auth_library_class'] = '';
$config['auth_library_function'] = '';

/*
|--------------------------------------------------------------------------
| Override auth types for specific class/method
|--------------------------------------------------------------------------
|
| Set specific authentication types for methods within a class (controller)
|
| Set as many config entries as needed.  Any methods not set will use the default 'rest_auth' config value.
|
| e.g:
|
|           $config['auth_override_class_method']['deals']['view'] = 'none';
|           $config['auth_override_class_method']['deals']['insert'] = 'digest';
|           $config['auth_override_class_method']['accounts']['user'] = 'basic';
|           $config['auth_override_class_method']['dashboard']['*'] = 'none|digest|basic';
|
| Here 'deals', 'accounts' and 'dashboard' are controller names, 'view', 'insert' and 'user' are methods within. An asterisk may also be used to specify an authentication method for an entire classes methods. Ex: $config['auth_override_class_method']['dashboard']['*'] = 'basic'; (NOTE: leave off the '_get' or '_post' from the end of the method name)
| Acceptable values are; 'none', 'digest' and 'basic'.
|
*/
// $config['auth_override_class_method']['deals']['view'] = 'none';
// $config['auth_override_class_method']['deals']['insert'] = 'digest';
// $config['auth_override_class_method']['accounts']['user'] = 'basic';
// $config['auth_override_class_method']['dashboard']['*'] = 'basic';


// ---Uncomment list line for the wildard unit test
// $config['auth_override_class_method']['wildcard_test_cases']['*'] = 'basic';

/*
|--------------------------------------------------------------------------
| Override auth types for specific 'class/method/HTTP method'
|--------------------------------------------------------------------------
|
| example:
|
|            $config['auth_override_class_method_http']['deals']['view']['get'] = 'none';
|            $config['auth_override_class_method_http']['deals']['insert']['post'] = 'none';
|            $config['auth_override_class_method_http']['deals']['*']['options'] = 'none';
*/

// ---Uncomment list line for the wildard unit test
// $config['auth_override_class_method_http']['wildcard_test_cases']['*']['options'] = 'basic';

$config['auth_override_class_method']['activity']['public_feed']    = 'none';
$config['auth_override_class_method']['login']['*']                 = 'none';
$config['auth_override_class_method']['recovery_password']['*']     = 'none';
$config['auth_override_class_method']['signup']['*']                = 'none';
$config['auth_override_class_method']['signout']['*']               = 'none';
$config['auth_override_class_method']['timezone']['*']              = 'none';
$config['auth_override_class_method']['betainvite']['*']            = 'none';
$config['auth_override_class_method']['users']['profile']           = 'custom';
$config['auth_override_class_method']['users']['get_location_id']   = 'custom';
$config['auth_override_class_method']['activity']['getAllComments']    = 'custom';
$config['auth_override_class_method']['activity']['city_news']    = 'custom';
$config['auth_override_class_method']['tag']['get_trending_tags']   = 'custom';
/* $config['auth_override_class_method']['page']['top_user_pages']     = 'none';
$config['auth_override_class_method']['group']['top_group']         = 'custom';
$config['auth_override_class_method']['group']['lists']             = 'custom';
$config['auth_override_class_method']['activity']['public_posts']   = 'none';
$config['auth_override_class_method']['group']['suggestions']       = 'custom';
$config['auth_override_class_method']['users']['get_popular_interest']           = 'custom';
$config['auth_override_class_method']['friends']['grow_user_network']           = 'custom';
$config['auth_override_class_method']['page']['suggestions']        = 'custom';


$config['auth_override_class_method']['events']['events_near_you']  = 'custom';
$config['auth_override_class_method']['page']['details']            = 'custom';
$config['auth_override_class_method']['forum']['category_details']            = 'custom';
$config['auth_override_class_method']['forum']['most_active_users']           = 'custom';
$config['auth_override_class_method']['forum']['category_members']            = 'custom';
$config['auth_override_class_method']['forum']['top_active_user_of_forum']    = 'custom';
$config['auth_override_class_method']['forum']['suggested_articles']            = 'custom';
$config['auth_override_class_method']['forum']['list']            = 'custom';
$config['auth_override_class_method']['activity']['articles']            = 'custom';
$config['auth_override_class_method']['activity']['article_widgets']            = 'custom';
$config['auth_override_class_method']['group']['details']           = 'custom';
*/
$config['auth_override_class_method']['activity']['profile_card']   = 'custom';
$config['auth_override_class_method']['search']['user']   = 'custom';
$config['auth_override_class_method']['upload_image']['index']      = 'custom';
/* $config['auth_override_class_method']['wallpost']['parseLinkData']  = 'custom';
$config['auth_override_class_method']['category']['get_categories']  = 'custom';
$config['auth_override_class_method']['search']['get_category']  = 'custom';
$config['auth_override_class_method']['group']['members']  = 'custom';
$config['auth_override_class_method']['activity_helper']['get_entity_bradcrumbs']  = 'none';
$config['auth_override_class_method']['events']['list']             	= 'custom';
$config['auth_override_class_method']['events']['get_event_categories'] = 'custom';
$config['auth_override_class_method']['events']['get_event_locations']  = 'custom';
$config['auth_override_class_method']['events']['details']  = 'custom';
$config['auth_override_class_method']['events']['event_user_detail']  = 'custom';
$config['auth_override_class_method']['events']['get_similar_event']  = 'custom';
$config['auth_override_class_method']['events']['get_past_event']  = 'custom';
$config['auth_override_class_method']['events']['event_owner_detail']  = 'custom';
$config['auth_override_class_method']['events']['event_attende_list']  = 'custom';
$config['auth_override_class_method']['events']['members']  = 'custom';
$config['auth_override_class_method']['events']['get_invitees_list']  = 'custom';
$config['auth_override_class_method']['media']['get_event_media']  = 'custom';
$config['auth_override_class_method']['media']['get_category_media']  = 'custom';
$config['auth_override_class_method']['group']['get_discover_list']  = 'custom';
$config['auth_override_class_method']['group']['category_group']  = 'custom';
$config['auth_override_class_method']['activity']['get_widgets']  = 'custom';
$config['auth_override_class_method']['forum']['get_categories']  = 'custom';
$config['auth_override_class_method']['forum_user_categories']['list']  = 'custom';
$config['auth_override_class_method']['album']['list']  = 'custom';
$config['auth_override_class_method']['events']['get_module_event']  = 'custom';
*/
$config['auth_override_class_method']['album']['details']  = 'custom';
$config['auth_override_class_method']['album']['list_media']  = 'custom';
$config['auth_override_class_method']['category']['get_categories']  = 'custom';
$config['auth_override_class_method']['search']['get_category']  = 'custom';
$config['auth_override_class_method']['search']['entity_home']  = 'custom';
$config['auth_override_class_method']['users']['get_latest_users']  = 'custom';

$config['auth_override_class_method']['activity']['get_announcement']  = 'custom';

$config['auth_override_class_method']['media']['details']  = 'custom';
$config['auth_override_class_method']['media']['comments']  = 'custom';
$config['auth_override_class_method']['media']['details_by_name']  = 'custom';
$config['auth_override_class_method']['locality']['*']            = 'none';
$config['auth_override_class_method']['ward']['*']            = 'custom';
$config['auth_override_class_method']['contact']['*']            = 'none';

$config['auth_override_class_method']['quiz']['details']   = 'custom';

//$config['auth_override_class_method']['upload_image']['index']  = 'custom';
/*
|--------------------------------------------------------------------------
| REST Login Usernames
|--------------------------------------------------------------------------
|
| Array of usernames and passwords for login, if ldap is configured this is ignored
|
*/
$config['rest_valid_logins'] = ['admin' => '1234'];

/*
|--------------------------------------------------------------------------
| Global IP White-listing
|--------------------------------------------------------------------------
|
| Limit connections to your REST server to White-listed IP addresses
|
| Usage:
| 1. Set to TRUE and select an auth option for extreme security (client's IP
|    address must be in white-list and they must also log in)
| 2. Set to TRUE with auth set to FALSE to allow White-listed IPs access with no login
| 3. Set to FALSE but set 'auth_override_class_method' to 'white-list' to
|    restrict certain methods to IPs in your white-list
|
*/
$config['rest_ip_whitelist_enabled'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Handle Exceptions
|--------------------------------------------------------------------------
|
| Handle exceptions caused by the controller
|
*/
$config['rest_handle_exceptions'] = TRUE;

/*
|--------------------------------------------------------------------------
| REST IP White-list
|--------------------------------------------------------------------------
|
| Limit connections to your REST server with a comma separated
| list of IP addresses
|
| e.g: '123.456.789.0, 987.654.32.1'
|
| 127.0.0.1 and 0.0.0.0 are allowed by default
|
*/
$config['rest_ip_whitelist'] = '';

/*
|--------------------------------------------------------------------------
| Global IP Blacklisting
|--------------------------------------------------------------------------
|
| Prevent connections to the REST server from blacklisted IP addresses
|
| Usage:
| 1. Set to TRUE and add any IP address to 'rest_ip_blacklist'
|
*/
$config['rest_ip_blacklist_enabled'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST IP Blacklist
|--------------------------------------------------------------------------
|
| Prevent connections from the following IP addresses
|
| e.g: '123.456.789.0, 987.654.32.1'
|
*/
$config['rest_ip_blacklist'] = '';

/*
|--------------------------------------------------------------------------
| REST Database Group
|--------------------------------------------------------------------------
|
| Connect to a database group for keys, logging, etc. It will only connect
| if you have any of these features enabled
|
*/
$config['rest_database_group'] = 'default';

/*
|--------------------------------------------------------------------------
| REST API Keys Table Name
|--------------------------------------------------------------------------
|
| The table name in your database that stores API keys
|
*/
$config['rest_keys_table'] = 'keys';

/*
|--------------------------------------------------------------------------
| REST Enable Keys
|--------------------------------------------------------------------------
|
| When set to TRUE, the REST API will look for a column name called 'key'.
| If no key is provided, the request will result in an error. To override the
| column name see 'rest_key_column'
|
| Default table schema:
|   CREATE TABLE `keys` (
|       `id` INT(11) NOT NULL AUTO_INCREMENT,
|       `user_id` INT(11) NOT NULL,
|       `key` VARCHAR(40) NOT NULL,
|       `level` INT(2) NOT NULL,
|       `ignore_limits` TINYINT(1) NOT NULL DEFAULT '0',
|       `is_private_key` TINYINT(1)  NOT NULL DEFAULT '0',
|       `ip_addresses` TEXT NULL DEFAULT NULL,
|       `date_created` INT(11) NOT NULL,
|       PRIMARY KEY (`id`)
|   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
|
*/
$config['rest_enable_keys'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Table Key Column Name
|--------------------------------------------------------------------------
|
| If not using the default table schema in 'rest_enable_keys', specify the
| column name to match e.g. my_key
|
*/
$config['rest_key_column'] = 'key';

/*
|--------------------------------------------------------------------------
| REST API Limits method
|--------------------------------------------------------------------------
|
| Specify the method used to limit the API calls
|
| Available methods are :
| $config['rest_limits_method'] = 'IP_ADDRESS'; // Put a limit per ip address
| $config['rest_limits_method'] = 'API_KEY'; // Put a limit per api key
| $config['rest_limits_method'] = 'METHOD_NAME'; // Put a limit on method calls
| $config['rest_limits_method'] = 'ROUTED_URL';  // Put a limit on the routed URL
|
*/
$config['rest_limits_method'] = 'ROUTED_URL';

/*
|--------------------------------------------------------------------------
| REST Key Length
|--------------------------------------------------------------------------
|
| Length of the created keys. Check your default database schema on the
| maximum length allowed
|
| Note: The maximum length is 40
|
*/
$config['rest_key_length'] = 40;

/*
|--------------------------------------------------------------------------
| REST API Key Variable
|--------------------------------------------------------------------------
|
| Custom header to specify the API key

| Note: Custom headers with the X- prefix are deprecated as of
| 2012/06/12. See RFC 6648 specification for more details
|
*/
$config['rest_key_name'] = 'X-API-KEY';

/*
|--------------------------------------------------------------------------
| REST Enable Logging
|--------------------------------------------------------------------------
|
| When set to TRUE, the REST API will log actions based on the column names 'key', 'date',
| 'time' and 'ip_address'. This is a general rule that can be overridden in the
| $this->method array for each controller
|
| Default table schema:
|   CREATE TABLE `logs` (
|       `id` INT(11) NOT NULL AUTO_INCREMENT,
|       `uri` VARCHAR(255) NOT NULL,
|       `method` VARCHAR(6) NOT NULL,
|       `params` TEXT DEFAULT NULL,
|       `api_key` VARCHAR(40) NOT NULL,
|       `ip_address` VARCHAR(45) NOT NULL,
|       `time` INT(11) NOT NULL,
|       `rtime` FLOAT DEFAULT NULL,
|       `authorized` VARCHAR(1) NOT NULL,
|       `response_code` smallint(3) DEFAULT '0',
|       PRIMARY KEY (`id`)
|   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
|
*/

if(ENVIRONMENT == "testing")
{
	$config['rest_enable_logging'] = FALSE;
}
else 
{
	$config['rest_enable_logging'] = FALSE;//TRUE
}

/*
|--------------------------------------------------------------------------
| REST API Logs Table Name
|--------------------------------------------------------------------------
|
| If not using the default table schema in 'rest_enable_logging', specify the
| table name to match e.g. my_logs
|
*/
$config['rest_logs_table'] = 'RestAPILogs';

/*
|--------------------------------------------------------------------------
| REST Method Access Control
|--------------------------------------------------------------------------
| When set to TRUE, the REST API will check the access table to see if
| the API key can access that controller. 'rest_enable_keys' must be enabled
| to use this
|
| Default table schema:
|   CREATE TABLE `access` (
|       `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
|       `key` VARCHAR(40) NOT NULL DEFAULT '',
|       `all_access` TINYINT(1) NOT NULL DEFAULT '0',
|       `controller` VARCHAR(50) NOT NULL DEFAULT '',
|       `date_created` DATETIME DEFAULT NULL,
|       `date_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
|       PRIMARY KEY (`id`)
|    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
|
*/
$config['rest_enable_access'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST API Access Table Name
|--------------------------------------------------------------------------
|
| If not using the default table schema in 'rest_enable_access', specify the
| table name to match e.g. my_access
|
*/
$config['rest_access_table'] = 'access';

/*
|--------------------------------------------------------------------------
| REST API Param Log Format
|--------------------------------------------------------------------------
|
| When set to TRUE, the REST API log parameters will be stored in the database as JSON
| Set to FALSE to log as serialized PHP
|
*/
$config['rest_logs_json_params'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Enable Limits
|--------------------------------------------------------------------------
|
| When set to TRUE, the REST API will count the number of uses of each method
| by an API key each hour. This is a general rule that can be overridden in the
| $this->method array in each controller
|
| Default table schema:
|   CREATE TABLE `limits` (
|       `id` INT(11) NOT NULL AUTO_INCREMENT,
|       `uri` VARCHAR(255) NOT NULL,
|       `count` INT(10) NOT NULL,
|       `hour_started` INT(11) NOT NULL,
|       `api_key` VARCHAR(40) NOT NULL,
|       PRIMARY KEY (`id`)
|   ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
|
| To specify the limits within the controller's __construct() method, add per-method
| limits with:
|
|       $this->method['METHOD_NAME']['limit'] = [NUM_REQUESTS_PER_HOUR];
|
| See application/controllers/api/example.php for examples
*/
$config['rest_enable_limits'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST API Limits Table Name
|--------------------------------------------------------------------------
|
| If not using the default table schema in 'rest_enable_limits', specify the
| table name to match e.g. my_limits
|
*/
$config['rest_limits_table'] = 'limits';

/*
|--------------------------------------------------------------------------
| REST Ignore HTTP Accept
|--------------------------------------------------------------------------
|
| Set to TRUE to ignore the HTTP Accept and speed up each request a little.
| Only do this if you are using the $this->rest_format or /format/xml in URLs
|
*/
$config['rest_ignore_http_accept'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST AJAX Only
|--------------------------------------------------------------------------
|
| Set to TRUE to allow AJAX requests only. Set to FALSE to accept HTTP requests
|
| Note: If set to TRUE and the request is not AJAX, a 505 response with the
| error message 'Only AJAX requests are accepted.' will be returned.
|
| Hint: This is good for production environments
|
*/
$config['rest_ajax_only'] = FALSE;

/*
|--------------------------------------------------------------------------
| REST Language File
|--------------------------------------------------------------------------
|
| Language file to load from the language directory
|
*/
$config['rest_language'] = 'english';

/*
|--------------------------------------------------------------------------
| CORS Check
|--------------------------------------------------------------------------
|
| Set to TRUE to enable Cross-Origin Resource Sharing (CORS). Useful if you
| are hosting your API on a different domain from the application that
| will access it through a browser
|
*/
$config['check_cors'] = FALSE;

/*
|--------------------------------------------------------------------------
| CORS Allowable Headers
|--------------------------------------------------------------------------
|
| If using CORS checks, set the allowable headers here
|
*/
$config['allowed_cors_headers'] = [
  'Origin',
  'X-Requested-With',
  'Content-Type',
  'Accept',
  'Access-Control-Request-Method'
];

/*
|--------------------------------------------------------------------------
| CORS Allowable Methods
|--------------------------------------------------------------------------
|
| If using CORS checks, you can set the methods you want to be allowed
|
*/
$config['allowed_cors_methods'] = [
  'GET',
  'POST',
  'OPTIONS',
  'PUT',
  'PATCH',
  'DELETE'
];

/*
|--------------------------------------------------------------------------
| CORS Allow Any Domain
|--------------------------------------------------------------------------
|
| Set to TRUE to enable Cross-Origin Resource Sharing (CORS) from any
| source domain
|
*/
$config['allow_any_cors_domain'] = FALSE;

/*
|--------------------------------------------------------------------------
| CORS Allowable Domains
|--------------------------------------------------------------------------
|
| Used if $config['check_cors'] is set to TRUE and $config['allow_any_cors_domain']
| is set to FALSE. Set all the allowable domains within the array
|
| e.g. $config['allowed_origins'] = ['http://www.example.com', 'https://spa.example.com']
|
*/
$config['allowed_cors_origins'] = [];
