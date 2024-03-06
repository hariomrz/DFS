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

define('CRON_PRIVATE_NOTI','for completion of your created private game');
define('CRON_REFERRAL_NOTI','for refferal');
define('CRON_PRIZE_MONEY_NOTI','of prize money');

// Login sessin key for logged in user 
define('SUPERADMIN_ROLE' , 1);
define('SUBADMIN_ROLE' , 2);
define('DISTRIBUTOR_ROLE' , 3);
define('SUB_DISTRIBUTOR_ROLE' , 4);
define('IS_LOCAL_TIME' , TRUE);
define('BACK_YEAR', '0 month');
define('API_CURRENT_YEAR', '2016');
define('API_SEASON_TYPE', 'REG');
define('DEFAULT_TIME_ZONE_ABBR', '');

define('CRON_WITHDRAWL_NOTI1', 'on withdrawal');
define('CRON_REFUND_GAME_BONUS','as Bonus Cash due to game Cancelletion');
define('CRON_REFUND_GAME_REAL','as Real Cash due to game Cancelletion');
define('CRON_REFUND_GAME_COINS','as Coins due to game Cancelletion');
define('CRON_REFUND_CONTEST_NOTI','on cash refund due to contest cancelletion');
define('CRON_ADD_POINT_NOTI', 'for logging in');
define('CRON_TDS_NOTI', 'due to TDS Deduction');
define('WEBISTE_URL','vinfotech.com');
define('MYSQL_DATE_FORMAT', '%b %d, %a %h:%i %p');
define('MYSQL_DATE_FORMAT_CONTEST', '%b %d, %a %Y');
define('PHP_DATE_FORMAT', 'd-M-Y h:i A');
define('DATE_FORMAT' ,'Y-m-d H:i:s');
define('DATE_ONLY_FORMAT' ,'Y-m-d');

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
define('PRIZE_ROLL_BACK'                                    , 14);
define('TRANSACTION_HISTORY_GAME_JOIN_POINT_CONVERT_BONUS'	, 15);

define('CASH_REAL'                      , '0');
define('CASH_BONUS'                     , '1');
define('CASH_REAL_BONUS'                , '2');
define('COINS'                			, '3');
define('PLATEFORM_FANTASY'              , '1');
define('PLATEFORM_PICKEM'              , '2');
define('USER_LIMIT_FOR_BADGE_PROCESS', 500);
define('BADGES_TOP_100_LEADERBOARD_PERCENT', 50);
// define('IS_TDS_APPLICABLE'         , 1);
// define('TDS_PERCENT'               , 31.2); // percentage of TDS amount
// define('TDS_APPLICABLE_ON'         , 10000); // Prize limit for TDS deduction

define('MYSQL_DATE_TIME_FORMAT', '%d-%b-%Y %H:%i');
//Feed Images url
define('FEED_IMAGE_URL','https://cricket-feed.s3.amazonaws.com');
