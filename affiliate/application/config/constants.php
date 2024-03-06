<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//Affiliate tables
define('AFFILIATE','affiliate');
define('CAMPAIGN','campaign');
define('CAMPAIGN_HISTORY','campaign_history');
define('CAMPAIGN_USERS','campaign_users');
define('VISIT','visit');
define('SITE_TITLE','Affiliate Admin Panel');
define('AFF_URL',getenv('AFF_URL'));

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
define('LEADERBOARD','leaderboard');
define('LEADERBOARD_HISTORY','leaderboard_history');
define('XP_USERS','xp_users');
define('XP_LEVEL_POINTS','xp_level_points');
define('XP_LEVEL_REWARDS','xp_level_rewards');
define('XP_BADGE_MASTER','xp_badge_master');
define('USER_BONUS_CASH','user_bonus_cash');




//custom
define('HTTP_PROTOCOL' ,getenv('HTTP_PROTOCOL'));
define('SERVER_NAME' ,getenv('DOMAIN_NAME'));
define('WEBSITE_URL' ,HTTP_PROTOCOL.'://'.SERVER_NAME."/");
define('WEBSITE_DOMAIN',SERVER_NAME);

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
define('MYSQL_DATE_FORMAT', '%Y-%m-%d');
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
define('APP_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/affiliate/");

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

define('MQ_HOST' ,getenv('MQ_HOST'));//159.203.161.102
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

define('PROJECT_FOLDER_NAME', '');
		
define('SERVER_IP', HTTP_PROTOCOL.'://'.SERVER_NAME);
define('NODE_ADDR', SERVER_IP.':3500');
define('LINEUP_NODE_ADDR', SERVER_IP.':4000/');