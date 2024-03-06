<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

define('IS_LOCAL_TIME' ,TRUE);
define('BACK_YEAR', '0 month');
define('DEFAULT_TIME_ZONE_ABBR', '');
define('DATE_FORMAT' ,'Y-m-d H:i:s');
define('MYSQL_DATE_TIME_FORMAT', '%d-%b-%Y %H:%i');
define('MYSQL_DATE_FORMAT', '%d-%b-%Y');
define('MYSQL_DATE_FORMAT_CONTEST', '%b %d, %a %Y');
define('PHP_DATE_FORMAT', 'd-M-Y h:i A');

define('REDIS_5_MINUTE',300);
define('REDIS_2_HOUR', 7200);
define('REDIS_24_HOUR', 86400);
define('REDIS_2_DAYS', 172800);
define('REDIS_7_DAYS', 604800);
define('REDIS_30_DAYS', 2592000);

define('AUTH_KEY', 'Sessionkey');
define('AUTH_KEY_ROLE', 'role');
define('ALLOW_CORS' ,'1');
define('CACHE_PREFIX', 'fw_');
define('CACHE_ADAPTER','redis');
define('CACHE_ENABLE',getenv('CACHE_ENABLE'));
define('SAVE_QUERIES' ,getenv('SAVE_QUERIES'));
define('HTTP_PROTOCOL' ,getenv('HTTP_PROTOCOL'));
define('SERVER_NAME' ,getenv('SERVER_NAME'));
define('WEBSITE_URL' ,HTTP_PROTOCOL.'://'.SERVER_NAME."/");
define('SERVER_IP', HTTP_PROTOCOL.'://'.SERVER_NAME);
define('WEBSITE_DOMAIN' ,SERVER_NAME);
define('IMAGE_SERVER','remote');
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/");
define('UPLOAD_DIR', 'upload/');
define('JERSEY_DIR', 'upload/jersey/');
define('FLAG_DIR', 'upload/flag/');
define('RECORD_LIMIT', 20);
define('CRICKET_SPORTS_ID','7');
define('SOCCER_SPORTS_ID','5');
define('BASKETBALL_SPORTS_ID','4');
define('FOOTBALL_SPORTS_ID','2');

//environment setting
define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_USER', getenv('SMTP_USER'));
define('SMTP_PASS', getenv('SMTP_PASS'));
define('SMTP_PORT', getenv('SMTP_PORT'));
define('PROTOCOL', getenv('PROTOCOL'));
define('SMTP_CRYPTO', '');
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL'));
define('FROM_ADMIN_EMAIL', getenv('FROM_ADMIN_EMAIL'));
define('FROM_EMAIL_NAME', getenv('FROM_EMAIL_NAME'));
define('NO_REPLY_EMAIL', getenv('NO_REPLY_EMAIL'));

define('MQ_HOST' ,getenv('MQ_HOST'));
define('MQ_PORT' , getenv('MQ_PORT'));
define('MQ_USER' ,getenv('MQ_USER'));
define('MQ_PASSWORD' ,getenv('MQ_PASSWORD'));

//Redis Server Details
define('REDIS_SOCKET_TYPE', getenv('REDIS_SOCKET_TYPE'));
define('REDIS_SOCKET', getenv('REDIS_SOCKET'));
define('REDIS_TIMEOUT', getenv('REDIS_TIMEOUT'));
define('REDIS_HOST', getenv('REDIS_HOST'));
define('REDIS_PASSWORD', getenv('REDIS_PASSWORD'));
define('REDIS_PORT', getenv('REDIS_PORT'));

//Mongo DB Details
define('MONGO_DBHOSTNAME', getenv('MONGO_DBHOSTNAME'));
define('MONGO_DBUSERNAME', getenv('MONGO_DBUSERNAME'));
define('MONGO_DBPASSWORD', getenv('MONGO_DBPASSWORD'));
define('MONGO_DBNAME', getenv('MONGO_DBNAME'));
define('MONGO_PORT', getenv('MONGO_PORT'));
define('MONGO_RP', getenv('MONGO_RP'));
define('MONGO_RC', getenv('MONGO_RC'));
define('MONGO_NO_AUTH', getenv('MONGO_NO_AUTH'));
define('MONGO_SRV', getenv('MONGO_SRV'));

define('BUCKET_ACCESS_KEY',getenv('BUCKET_ACCESS_KEY'));
define('BUCKET_SECRET_KEY',getenv('BUCKET_SECRET_KEY'));
define('BUCKET_REGION',getenv('BUCKET_REGION'));
define('BUCKET_TYPE',getenv('BUCKET_TYPE'));
define('BUCKET',getenv('BUCKET'));
define('BUCKET_USE_SSL',getenv('BUCKET_USE_SSL'));
define('BUCKET_VERIFY_PEER',getenv('BUCKET_VERIFY_PEER'));
if(BUCKET_TYPE=='DO'){
	define('IMAGE_PATH','https://'.BUCKET.'.'.BUCKET_REGION.'.digitaloceanspaces.com/');
}else if(BUCKET_TYPE=='CJ'){
	define('IMAGE_PATH','https://'.BUCKET_REGION.'.cloudjiffy.net/'.BUCKET.'/');
}else{
	define('IMAGE_PATH','https://'.BUCKET.'.s3.amazonaws.com/');
}
define('FEED_IMAGE_URL','https://cricket-feed.s3.amazonaws.com');

//tables
define('APP_CONFIG','app_config');
define('ACTIVE_LOGIN','active_login');
define('ADMIN_ACTIVE_LOGIN','admin_active_login');
define('ADMIN','admin');
define('ADMIN_ROLES_RIGHTS','admin_roles_rights');
define('USER','user');
define('MASTER_COUNTRY','master_country');
define('MASTER_STATE','master_state');
define('BANNED_STATE','banned_state');
define('ORDER','order');
define('NOTIFICATION'				,'notification');
define('NOTIFICATION_DESCRIPTION'	,'notification_description');
define('EMAIL_TEMPLATE'				,'email_template');

define('LEAGUE', 'league');
define('LINEUP', 'lineup');
define('MASTER_PAYOUT', 'master_payout');
define('MASTER_POSITION', 'master_position');
define('MASTER_PROPS', 'master_props');
define('MASTER_SPORTS', 'master_sports');
define('PLAYER', 'player');
define('SEASON', 'season');
define('SEASON_PROPS', 'season_props');
define('STATS_CRICKET', 'stats_cricket');
define('CRICKET_FOW', 'cricket_fow');
define('STATS_SOCCER', 'stats_soccer');
define('TEAM', 'team');
define('USER_SETTING', 'user_setting');
define('USER_TEAM', 'user_team');
define('STATS_BASKETBALL', 'stats_basketball');
define('STATS_FOOTBALL', 'stats_football');

define('JOIN_ENTRY_SOURCE', 537);
define('ADDITIONAL_ENTRY_SOURCE', 538);
define('REFUND_ENTRY_SOURCE', 539);
define('PICKS_WON_SOURCE', 540);
define('PICKS_TDS_SOURCE', 541);

define('PICKS_JOIN_NOTIFY', 654);
define('PICKS_WON_NOTIFY', 655);
define('PICKS_TDS_NOTIFY', 656);

define('ML_SERVER_API_URL', getenv('ML_SERVER_API_URL'));
define('PL_API_URL', getenv('PL_API_URL'));