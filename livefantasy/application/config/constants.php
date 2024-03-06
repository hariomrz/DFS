<?php
defined('BASEPATH') OR exit('No direct script access allowed');

defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

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

define('SAVE_QUERIES' ,getenv('SAVE_QUERIES'));

//Database table constant
define('ACTIVE_LOGIN','active_login');
define('USER','user');
define('USER_SELF_EXCLUSION','user_self_exclusion');
define('NOTIFICATION','notification');
define('NOTIFICATION_DESCRIPTION'	,'notification_description');
define('ADMIN','admin');
define('ADMIN_ACTIVE_LOGIN','admin_active_login');
define('ADMIN_ROLES_RIGHTS','admin_roles_rights');
define('APP_CONFIG','app_config');
define('EMAIL_TEMPLATE','email_template');
define('MASTER_COUNTRY','master_country');
define('MASTER_STATE','master_state');
define('BANNED_STATE','banned_state');
define('INVITE','invite');
define('ORDER','order');
define('PROMO_CODE','promo_code');
define('PROMO_CODE_EARNING','promo_code_earning');
define('LEADERBOARD_CATEGORY','leaderboard_category');
define('LEADERBOARD_PRIZE','leaderboard_prize');
define('GST_REPORT','gst_report');
define('LEADERBOARD','leaderboard');
define('LEADERBOARD_HISTORY','leaderboard_history');
define('XP_USERS','xp_users');
define('XP_LEVEL_POINTS','xp_level_points');
define('XP_LEVEL_REWARDS','xp_level_rewards');
define('XP_BADGE_MASTER','xp_badge_master');
define('AFFILIATE','affiliate');
define('AFFILIATE_EARNING','affiliate_earning');
define('AFFILIATE_HISTORY','affiliate_history');
define('AFFILIATE_MASTER','affiliate_master');
define('AFFILIATE_USER','affiliate_user');
define('USER_AFFILIATE_HISTORY','user_affiliate_history');
define('USER_BONUS_CASH','user_bonus_cash');

//game table
define('COLLECTION', 'collection');
define('CONTEST', 'contest');
define('CONTEST_TEMPLATE', 'contest_template');
define('CONTEST_TEMPLATE_LEAGUE', 'contest_template_league');
define('LEAGUE', 'league');
define('MARKET_ODDS', 'market_odds');
define('MASTER_GROUP', 'master_group');
define('MASTER_ODDS', 'master_odds');
define('MASTER_SPORTS', 'master_sports');
define('MASTER_SPORTS_FORMAT', 'master_sports_format');
define('MERCHANDISE', 'merchandise');
define('PLAYER', 'player');
define('PLAYER_TEAM', 'player_team');
define('SEASON', 'season');
define('TEAM', 'team');
define('USER_CONTEST', 'user_contest');
define('USER_PREDICTION', 'user_prediction');
define('USER_TEAM', 'user_team');
define('ANALYTICS','analytics');

//custom
define('HTTP_PROTOCOL' ,getenv('HTTP_PROTOCOL'));
define('SERVER_NAME' ,getenv('SERVER_NAME'));
define('WEBSITE_URL' ,HTTP_PROTOCOL.'://'.SERVER_NAME."/");
define('WEBSITE_DOMAIN' ,SERVER_NAME);

define('ACTIVE'						,'1');
define('RECORD_LIMIT', 20);
define('CONTEST_LIMIT'				,'10');
define('RANK_LIMIT'					,'10');
define('IS_LOCAL_TIME' ,TRUE);
define('BACK_YEAR', '0 month');
define('DEFAULT_TIME_ZONE_ABBR', '');

define('DATE_FORMAT' ,'Y-m-d H:i:s');
define('DATE_ONLY_FORMAT' ,'Y-m-d');
define('MYSQL_DATE_TIME_FORMAT', '%d-%b-%Y %H:%i');
define('MYSQL_DATE_FORMAT', '%d-%b-%Y');
define('MYSQL_DATE_FORMAT_CONTEST', '%b %d, %a %Y');
define('PHP_DATE_FORMAT', 'd-M-Y h:i A');

define('UNCAPPED_MAX_SIZE',200000);
define('CONTEST_DISABLE_INTERVAL_MINUTE',0);//time in minutes
define('MYCONTEST_CONTEST_TEAMS_LIMIT', '500');//this limit for display on mycontest

define('CASH_REAL'                      , '0');
define('CASH_BONUS'                     , '1');
define('CASH_REAL_BONUS'                , '2');
define('PLATEFORM_FANTASY'              , '1');

//TDS
define('IS_TDS_APPLICABLE',1);
define('TDS_PERCENT',31.2);// percentage of TDS amount
define('TDS_APPLICABLE_ON',10000); // Prize limit for TDS deduction
define('CRON_TDS_NOTI','Due to TDS Deduction');
define('CRON_WITHDRAWL_NOTI1','on withdrawal');

//custom
define('AUTH_KEY', 'Sessionkey');
define('AUTH_KEY_ROLE', 'role');
define('PREVIOUS_AUTH_KEY', 'session_key');
define('IMAGE_SERVER','remote');
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/");
define('UPLOAD_DIR', 'upload/');
define('MERCHANDISE_IMAGE_DIR', 'upload/merchandise/');
define('CONTEST_CATEGORY_IMG_UPLOAD_PATH','assets/img/');
define('SPONSOR_IMAGE_DIR', 'upload/sponsor/');

define('REDIS_1_MINUTE',60);
define('REDIS_5_MINUTE',300);
define('REDIS_2_HOUR', 7200);
define('REDIS_24_HOUR', 86400);
define('REDIS_2_DAYS', 172800);
define('REDIS_7_DAYS', 604800);
define('REDIS_30_DAYS', 2592000);

define('NODE_BASE_URL',getenv('NODE_BASE_URL'));

//env
define('CACHE_PREFIX', getenv('CACHE_PREFIX'));
define('CACHE_ADAPTER','redis');
define('CACHE_ENABLE',getenv('CACHE_ENABLE'));

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

//smtp Details
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

//bucket
define('BUCKET_ACCESS_KEY',getenv('BUCKET_ACCESS_KEY'));
define('BUCKET_SECRET_KEY',getenv('BUCKET_SECRET_KEY'));
define('BUCKET_REGION',getenv('BUCKET_REGION'));
define('BUCKET_TYPE',getenv('BUCKET_TYPE'));
define('BUCKET',getenv('BUCKET'));
define('BUCKET_USE_SSL',getenv('BUCKET_USE_SSL'));
define('BUCKET_VERIFY_PEER',getenv('BUCKET_VERIFY_PEER'));

define('BUCKET_REPORTS_PATH', 'reports/');
define('APP_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/livefantasy/");

if(BUCKET_TYPE=='DO'){
	define('IMAGE_PATH','https://'.BUCKET.'.'.BUCKET_REGION.'.digitaloceanspaces.com/');
}else if(BUCKET_TYPE=='CJ'){
	define('IMAGE_PATH','https://'.BUCKET_REGION.'.cloudjiffy.net/'.BUCKET.'/');
}else{
	define('IMAGE_PATH','https://'.BUCKET.'.s3.amazonaws.com/');
}

define('ALLOWED_USER_TEAM','50');
define('MAX_BONUS_PERCEN_USE','10');
define('DEFAULT_SITE_RAKE','10');
define('CRICKET_SPORTS_ID','7');

define('MQ_HOST' ,getenv('MQ_HOST'));
define('MQ_PORT' , getenv('MQ_PORT'));
define('MQ_USER' ,getenv('MQ_USER'));
define('MQ_PASSWORD' ,getenv('MQ_PASSWORD'));

define('ALLOW_DUPLICATE_TEAM', '0');//1-allowed,0-not_allowed
define('CAPTAIN_POINT',2);
define('VICE_CAPTAIN_POINT', 1.5);

define('FANTASY_CONTEST_NOTI1','for joining contest');
define('FANTASY_VIEW_ADVERT_NOTI','for viewing advertisement');
define('FANTASY_LINEUP_MOBILE_NOTI','for creating lineup and joining game from mobile');

define('CONTEST_JOIN_TYPE', '6');//live fantasy contest code

define('CONTEST_JOIN_SOURCE',500);
define('CONTEST_CANCEL_SOURCE',501);
define('CONTEST_WON_SOURCE',502);
define('CONTEST_TDS_SOURCE',504);
define('CONTEST_JOIN_NOTIFY',620);
define('CONTEST_WON_NOTIFY',622);

define('PROJECT_FOLDER_NAME', '');
		
define('SERVER_IP', HTTP_PROTOCOL.'://'.SERVER_NAME);
define('NODE_ADDR', SERVER_IP.':3500');
define('LINEUP_NODE_ADDR', SERVER_IP.':4000/');

define('ODI_FORMAT', '1');
define('TEST_FORMAT', '2');
define('T20_FORMAT', '3');
define('T10_FORMAT', '4');

define('JERSEY_CONTEST_DIR', 'upload/jersey/');
define('FLAG_CONTEST_DIR', 'upload/flag/');
define('LEAGUE_IMAGE_DIR', 'upload/league_logo/');
define('PLAYER_IMAGE_DIR', 'player/');


// Cricket Format Type
define('CRICKET_ONE_DAY'     				,1);
define('CRICKET_ONE_DAY_TEXT'     			,"ODI");
define('CRICKET_TEST'     					,2);
define('CRICKET_TEST_TEXT'     				,"TEST");
define('CRICKET_T20'     					,3);
define('CRICKET_T20_TEXT'     				,"T20");
define('CRICKET_T10'     					,4);
define('CRICKET_T10_TEXT'     				,"T10");