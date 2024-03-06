<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "../all_config/common_constants.php";

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
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

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
define('SHOW_DEBUG_BACKTRACE', TRUE);

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
define('EXIT_SUCCESS', 0); // no errors
define('EXIT_ERROR', 1); // generic error
define('EXIT_CONFIG', 3); // configuration error
define('EXIT_UNKNOWN_FILE', 4); // file not found
define('EXIT_UNKNOWN_CLASS', 5); // unknown class
define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
define('EXIT_USER_INPUT', 7); // invalid user input
define('EXIT_DATABASE', 8); // database error
define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


/* TABLE NAMES */

define('ADMIN_ACTIVE_LOGIN'			,'admin_active_login');
define('LEAGUE_DURATION'            ,'league_duration');
define('LEAGUE_DRAFTING_STYLES'     ,'league_drafting_styles');
define('LEAGUE_CONTEST_TYPE'     	,'league_contest_type');
define('LEAGUE_SALARY_CAP'     		,'league_salary_cap');
define('LEAGUE_LINEUP_POSITION'     ,'league_lineup_position');
define('ROSTER_CATEGORY'     		,'roster_category');
define('PAYMENT_WITHDRAW_TRANSACTION'	,'payment_withdraw_transaction');
define('MASTERDESCRIPTION'	,'master_description');
define('RANGE_MASTER'	,'range_master');
define('CAMPAIGN'		,'campaign');



define('REFERRAL'						,'refferal');

define('ADMIN_PAN_VERIFY_NOTI','for Pan Card verification');

define('BONUS'						,'bonus');

define('ADS_POSITION'						,'ad_position');
define('ADS_MANAGEMENT'						,'ad_management');

define('SALES_PERSON'						,'sales_person');

define('PAYMENT_HISTORY_TRANSACTION'		,'payment_history_transaction');
define('PAYMENT_DEPOSIT_TRANSACTION'		,'payment_deposit_transaction');
define('TEMP_LINEUP_MASTER_CONTEST'         ,'TEMP_LINEUP_MASTER_CONTEST');

define('ADMIN_USER_NOTI','by admin');
// Login sessin key for logged in user 
define('SUPERADMIN_ROLE' , 1);
define('SUBADMIN_ROLE' , 2);
define('DISTRIBUTOR_ROLE' , 3);
define('SUB_DISTRIBUTOR_ROLE' , 4);
define('IS_LOCAL_TIME' , TRUE);
define('BACK_YEAR', '0 month');

define('DEFAULT_TIME_ZONE_ABBR', '');
define('MYSQL_DATE_TIME_FORMAT', '%d-%b-%Y %h:%i %p');
define('MYSQL_DATE_FORMAT', '%d-%b-%Y');
define('MYSQL_DATE_FORMAT_CONTEST', '%b %d, %a %Y');
define('PHP_DATE_FORMAT', 'd-M-Y h:i A');
define('DATE_FORMAT', 'Y-m-d');
define('STD_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('PROFILE', 'upload/');
define('ROSTER_DIR', 'upload/roster/');
define('LANGUAGE_DIR', 'upload/language/');
define('TEAMFEED_DIR', 'upload/team/');
define('LEAGUEFEED_DIR', 'upload/league/');
define('SEASONFEED_DIR', 'upload/season/');
define('FEATURE_CONTEST_DIR', 'upload/feature_contest/');
define('NOTIFICATION_IMG_DIR', 'upload/desktop_notification/');
define('SETTING_IMG_DIR', 'upload/setting/');
define('MERCHANDISE_CONTEST_DIR', 'upload/merchandise/');
define('AVATAR_CONTEST_DIR', 'upload/avatar/');
define('DEFAULT_PROFILE', 'assets/img/default-user.png');
define('DEFAULT_PROFILE_SMALL', 'assets/img/default_small.png');
define('PROFILE_THUMB','upload/logo/');
define('AD_IMAGE_DIR', 'upload/advertisement/');
define('BANNER_IMAGE_DIR', 'upload/banner/');
define('APP_BANNER_IMAGE_DIR', 'upload/app_banner/');
define('BLOG_IMAGE_DIR', 'upload/blog/');
define('FEEDBACK_IMAGE_DIR', 'upload/feedback/');
define('DEFAULT_BLOG_IMAGE','assets/img/blog-default.png');
define('SPONSOR_DIR', 'upload/sponsor/');
define('PLAYER_PHOTO_DIR', 'upload/player_photo/');
define('PLAYER_PHOTO_MIN_WIDTH', 150);
define('PLAYER_PHOTO_MIN_HEIGHT', 150);
define('LEAUGE_LOGO_DIR', 'upload/league_logo/');
define('LEAUGE_LOGO_MIN_WIDTH', 150);
define('LEAUGE_LOGO_MIN_HEIGHT', 150);
define('BOOSTER_IMAGE_DIR', 'upload/booster/');

define('CATEGORY_IMAGE_DIR', 'upload/category/');
define('OPEN_PREDICTOR_PROOF_IMAGE_DIR', 'upload/open_predictor/');
define('FIXED_OPEN_PREDICTOR_SPONSOR_IMAGE_DIR', 'upload/fixed_open_predictor/sponsor/');

// config contest for 
define('CONTEST_FOR'	,'GT');

//********************************//
define('TRANSACTION_HISTORY_DESCRIPTION_ENTRY_FEE'			, 1);
define('TRANSACTION_HISTORY_DESCRIPTION_ENTRY_FEE_REFUND'	, 2);
define('PRIZE_WON_DESCRIPTION'								, 3);
define('TRANSACTION_HISTORY_DESCRIPTION_REFERRAL_FUND'		, 4);
define('TRANSACTION_HISTORY_DESCRIPTION_ENTRY_FEE_FOR'		, 5);
define('TRANSACTION_HISTORY_DESCRIPTION_DEPOSIT_BY_ADMIN'	, 6);
define('TRANSACTION_HISTORY_DESCRIPTION_WITHDRAWAL_BY_ADMIN', 7);
define('TRANSACTION_HISTORY_DESCRIPTION_DEPOSIT'			, 8);
define('TRANSACTION_HISTORY_BONUS_DEBITED'					, 9);
define('TRANSACTION_HISTORY_BONUS_CONVERT_BALANCE'			, 10);
define('TRANSACTION_HISTORY_DESCRIPTION_WITHDRAWAL'			, 11);
define('TRANSACTION_HISTORY_BONUS_CREDITED'					, 12);
define('TRANSACTION_HISTORY_PRE_LAUNCH_BONUS'				, 13);
define('PRIZE_ROLL_BACK'                                                , 14);
define('TRANSACTION_HISTORY_GAME_JOIN_POINT_CONVERT_BONUS'		, 15);

define('CASH_REAL'                      , '0');
define('CASH_BONUS'                     , '1');
define('CASH_REAL_BONUS'                , '2');

define('PLATEFORM_FANTASY'                , '1');
define('PRO_TYPE'                , 'GT');

define( 'USER_INVITATION_MAIL_SUBJECT','Invitation From Fantasy Sport');
define( 'DISTRIBUTOR_INVITATION_MAIL_SUBJECT','Invitation From Fantasy Sport');

define( 'DRAFTING_STYLE_QUICK',3);
define( 'DRAFTING_STYLE_TURBO',2);
define( 'DRAFTING_STYLE_SALARY_CAP',1);


define('UNCAPPED_MAX_SIZE',200000);
define("ROSTER_LIMIT"					, 30);

define("FEEDBACK_EMAIL", "yash.bodane@vinfotech.com");
define("ACTIVE",1);

//define AMQP(RabbitMQ) credentials and contsnts


switch (ENVIRONMENT)
{
	case 'development':
 
		
		define('APP_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/adminapi/");
        define('APP_ADMIN_PATH', $_SERVER['SERVER_NAME']."/adminapi/");
		
		
		define('FCM_SERVER_KEY' ,'AIzaSyAbI2vJtkIilJ9SBZGSflvWXQgoruXea5k');

	break;

	case 'testing':

		define('APP_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/adminapi/");

        define('APP_ADMIN_PATH', $_SERVER['SERVER_NAME']."/adminapi/");
		

		define('FCM_SERVER_KEY' ,'AIzaSyAbI2vJtkIilJ9SBZGSflvWXQgoruXea5k');

	break;
	case 'demo':

		define('APP_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/adminapi/");

        define('APP_ADMIN_PATH', $_SERVER['SERVER_NAME']."/adminapi/");
		

		define('FCM_SERVER_KEY' ,'AIzaSyAbI2vJtkIilJ9SBZGSflvWXQgoruXea5k');

	break;

	case 'production':

		define('APP_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/adminapi/");
        define('APP_ADMIN_PATH', $_SERVER['SERVER_NAME']."/adminapi/");
		
		
		define('FCM_SERVER_KEY' ,'AIzaSyAbI2vJtkIilJ9SBZGSflvWXQgoruXea5k');

	break;

	default:
	break;
}


define('MASTER_DISTRIBUTOR_ROLE' , 2);
define('AGENT_ROLE' , 4);
define('RECHARE_SLIP_IMAGE_DIR', 'upload/recharge_slip/');
define('SELF_EXCLUSION_DOCUMENT_DIR', 'upload/self_exclusion/');

define('DFS_TR_LOGO_DIR', 'upload/dfs_tr_logo/');
define('DFS_TR_SPONSOR_DIR', 'upload/dfs_tr_sponsor/');
