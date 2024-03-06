<?php
define('CODE_VERSION', getenv('CODE_VERSION'));
define('LOG_TX',getenv('LOG_TX'));
define('PL_LOG_TX',getenv('PL_LOG_TX'));
define('MATCH_LIMIT', 5000);
define('OTP_LENGTH',4);
define('CLIENT_TIME_ZONE','Asia/Kolkata');
define('RECORD_LIMIT', 20);

define('REDIS_5_MINUTE',300);
define('REDIS_2_HOUR', 7200);
define('REDIS_24_HOUR', 86400);
define('REDIS_2_DAYS', 172800);
define('REDIS_7_DAYS', 604800);
define('REDIS_30_DAYS', 2592000);
define('SIGNUP_BONUS', 30);

define('TRACKING_URL', '');
define('TRACKING_SERVER_URL', '');
define('TRACKING_PROJECT', '');
define('TRACKING_AUTH_KEY', '');
define('REWARD_IMG_DIR', 'upload/rewards/');

define('SYSTEM_USER_MATCH_LIMIT','1000');
define('SYSTEM_USER_CONTEST_LIMIT','100');
define('SYSTEM_USER_CONTEST_DEADLINE','20');
define('SYSTEM_USER_REQUEST_LIMIT','1000');//max limit to add system users in single request for a contest.

define('DRAW_TEXT', 'Draw');

//communication module constants
define('CD_BALANCE_AUTH_KEY','HJ@#@HJ#*(');
//queue names
define('CD_BULK_EMAIL_QUEUE','cd_bulk_email');
define('CD_EMAIL_QUEUE','cd_email');
define('CD_PUSH_QUEUE','cd_push');
define('CD_SCHEDULED_PUSH_QUEUE','cd_scheduled_push');
define('CD_NORMAL_PUSH_QUEUE','cd_normal_push');
define('STOCK_PUSH_QUEUE','stock_push');
define('AUTO_PUSH_QUEUE','auto_push');
define('DFS_AUTO_PUSH_QUEUE','dfs_auto_push');
define('CD_SMS_QUEUE','cd_sms');
define('CD_ONE_EMAIL_RATE',0.05);
define('CD_ONE_NOTIFICATION_RATE',0);
define('FRONTEND_BITLY_URL','');
define('BITLY_ACCESS_TOKEN','');

define('CD_UTM_TRACKING_FIXTURE_SMS','utm_source=VtechAdmin&utm_medium=SMS&utm_campaign=TemplateFixturePromotion');
define('CD_UTM_TRACKING_FIXTURE_EMAIL','utm_source=VtechAdmin&utm_medium=EMAIL&utm_campaign=TemplateFixturePromotion');
define('CD_UTM_TRACKING_CONTEST_SMS','utm_source=VtechAdmin&utm_medium=SMS&utm_campaign=TemplateContestPromotion');
define('CD_UTM_TRACKING_CONTEST_EMAIL','utm_source=VtechAdmin&utm_medium=EMAIL&utm_campaign=TemplateContestPromotion');
define('CD_UTM_TRACKING_DEPOSIT_SMS','utm_source=VtechAdmin&utm_medium=SMS&utm_campaign=TemplateDepositPromotion');
define('CD_UTM_TRACKING_DEPOSIT_EMAIL','utm_source=VtechAdmin&utm_medium=EMAIL&utm_campaign=TemplateDepositPromotion');
define('CD_UTM_TRACKING_REFER_FRIEND_SMS','utm_source=VtechAdmin&utm_medium=SMS&utm_campaign=TemplateReferFriendpromo');
define('CD_UTM_TRACKING_REFER_FRIEND_EMAIL','utm_source=VtechAdmin&utm_medium=EMAIL&utm_campaign=TemplateReferFriendpromo');

define("SIGNUP_BANNER_AFF_ID","6");
define("VERIFY_EMAIL_AFF_TYPE","13");
define("LOBBY_REFER_BANNER_AFF_ID","1");
define("LOBBY_DEPOSIT_BANNER_AFF_ID","15");
define("LOBBY_WHYUS_BANNER_TYPE_ID","6");
define("VERIFY_EMAIL_AFF_WO_REF","7");
//Common constants for promotion 
define('INCLUDE_PRIVIOUS_USERS_DAYS', 15);
define('VS_TEXT', "vs");
define('SALARY_CAP', 100);
define('REQUIRE_OTP', FALSE);
define('ALLOW_POSITION_CHANGE', TRUE);
define('ALLOWED_PIN_CONTEST_COUNT', 50);
define('CONTEST_DISABLE_MINUTES', 1);//not in use
define('WITHDRAWAL_LIMIT', 50);
define('RELEASED_VERSION', 3.2);
define('SOCCER_SL', TRUE);
define('FOOTBALL_SL', TRUE);
define('SL_CAPTAIN_VC', FALSE);
define('ENABLE_MANUAL_FEED', FALSE); // manual feed for admin to put all feed data manually
define('ANDROID_UPDATE_MSG', 'A new version available for update');
define('IOS_UPDATE_MSG', 'A new version available for update');

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

define('MQ_HOST' ,getenv('MQ_HOST'));//159.203.161.102
define('MQ_PORT' , getenv('MQ_PORT'));
define('MQ_USER' ,getenv('MQ_USER'));
define('MQ_PASSWORD' ,getenv('MQ_PASSWORD'));

//custom variables from env
define('ALLOW_CORS' ,'1');
define('HTTP_PROTOCOL' ,getenv('HTTP_PROTOCOL'));
define('SERVER_NAME' ,getenv('DOMAIN_NAME'));
define('NODE_BASE_URL',getenv('NODE_BASE_URL'));
define('WEBSITE_URL' ,HTTP_PROTOCOL.'://'.SERVER_NAME."/");
define('FRONT_APP_PATH',WEBSITE_URL);
define('WEBSITE_DOMAIN' ,SERVER_NAME);
define('SAVE_QUERIES' ,getenv('SAVE_QUERIES'));
define('CACHE_ADAPTER' ,'redis');
define('CACHE_ENABLE' ,getenv('CACHE_ENABLE'));
define('IS_MONGO_ENABLE' ,"1");
define('LOGIN_FLOW', getenv('LOGIN_FLOW'));

define('BUCKET_REPORTS_PATH', 'reports/');
define('BUCKET_STATIC_DATA_PATH', 'appstatic/');


define('CASHFREE_PROD_URL', 'https://api.cashfree.com/');

define('CASHFREE_TESTPAY_URL' ,'https://sandbox.cashfree.com/pg/');
define('CASHFREE_PRODPAY_URL' ,'https://api.cashfree.com/pg/');

define('CONTEST_DISABLE_INTERVAL_MINUTE',0);//time in minutes
define('MATCH_SCORE_CLOSE_DAYS',7);//time in minutes

define('DEFAULT_LANG', 'en');
define('DEFAULT_PHONE_CODE', '91');

// Max Percentage of bonus cash used on join game 
define('MAX_BONUS_PERCEN_USE', 10);
define('DEFAULT_SITE_RAKE', 10);
// Percentage for Bouns giving on cash deposit  
define('BONUS_PERCENT_ON_DEPOSIT', 100);

define('CACHE_PREFIX', getenv('CACHE_PREFIX'));
define('CRICKET_SPORTS_ID', '7');
define('SOCCER_SPORTS_ID', '5');
define('HOCKEY_SPORTS_ID', '6');
define('KABADDI_SPORTS_ID', '8');
define('FOOTBALL_SPORTS_ID', '2');
define('GOLF_SPORTS_ID', '9');
define('PBL_SPORTS_ID', '10');
define('TENNIS_SPORTS_ID', '11');
define('BASKETBALL_SPORTS_ID', '4');
define('NFL_SPORTS_ID', '2');
define('BASEBALL_SPORTS_ID', '1');
define('CREATE_AUTO_COLLECTION', '1');
define('NCAA_SPORTS_ID', '13');
define('CFL_SPORTS_ID', '17');
define('NCAA_BASKETBALL_SPORTS_ID', '18');
define('MOTORSPORT_SPORTS_ID', '15');

define('FIRST_DEPOSIT_TYPE', '0');
define('DEPOSIT_RANGE_TYPE', '1');
define('PROMO_CODE_TYPE', '2');
define('CONTEST_JOIN_TYPE', '3');
define('MYCONTEST_CONTEST_TEAMS_LIMIT', '500');//this limit for display on mycontest

define('ODI_FORMAT', '1');
define('TEST_FORMAT', '2');
define('T20_FORMAT', '3');
//draw setting
$pickem_allow_draw = array(
	CRICKET_SPORTS_ID => 0,
	SOCCER_SPORTS_ID => 1,
	KABADDI_SPORTS_ID => 0,
	FOOTBALL_SPORTS_ID => 0,
	HOCKEY_SPORTS_ID=> 1,
	BASKETBALL_SPORTS_ID => 0
);


define('CAPTAIN_POINT', '2');
define('VICE_CAPTAIN_POINT', '1.5');
define('ALLOW_DUPLICATE_TEAM', '0');//1-allowed,0-not_allowed

define('ALLOWED_USER_TEAM','50');

$league_formats = array(
'7' => array(1=>'One-Day', 2=>'Test', 3=> 'T20', 4=> 'T10'),
'10' => array(1=>'PBL', 2=>'Other')
);
define('LEAGUE_FORMATS',serialize($league_formats));


define('TWO_FACTOR_BY_CURL', '0');
define('CD_TWO_FACTOR_BY_CURL', '0');

//Query log send mail id here
define('QUERY_LOG_SENDER_EMAIL', '');//alert@vinfotech.com
define('QUERY_EXCUTION_TIME','500'); //time in millisecond

define('AUTH_KEY', 'Sessionkey');
define('AUTH_KEY_ROLE', 'role');
define('PREVIOUS_AUTH_KEY', 'session_key');

define('PREDICTION_WON_SOURCE'						, 41);
define('OPEN_PREDICTOR_MAKE_SOURCE'						, 220);
define('OPEN_PREDICTOR_WON_SOURCE'						, 221);

define('PICKEM_WON_SOURCE'						, 181);
define('PICKEM_MAKE_SOURCE'						, 250);


//For demo user
define('DEMO_USER_PHONE_NO', 9876598765);
define('DEMO_USER_OTP', '9876');
define('DEMO_ADMIN_ID', '100001');
define('DEMO_ADMIN_PASSWORD_EMAIL', 'girishp@vinfotech.com');
define('FRONT_BG_IMAGE_PATH', 'front_bg.png');
define('WRONG_OTP_LIMIT','5');
define('ADMIN_AUTO_LOGOUT_TIME','60');//time in minutes


switch (ENVIRONMENT)
{
	case 'development':
		define('PROJECT_FOLDER_NAME', '');
		define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].PROJECT_FOLDER_NAME."/");
		
		define('SERVER_IP', HTTP_PROTOCOL.'://'.SERVER_NAME);
		define('NODE_ADDR', SERVER_IP.':3500');
		define('LINEUP_NODE_ADDR', SERVER_IP.':4000/');
		define('GAME_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/sports/');
		define('FANTASY_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/fantasy/');
	    define('USER_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/user/');
	    define('PREDICTION_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/prediction/');
	    define('CURL_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/cron/');
	    define('CRON_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/cron/');
	    define('BASE_APP_PATH', SERVER_IP.PROJECT_FOLDER_NAME."/");
	    define('FRONT_APP_PATH_USER',HTTP_PROTOCOL."://".SERVER_IP."/user/");
 
		//upload media setting
		define('IMAGE_SERVER','remote');

		if(BUCKET_TYPE=='DO'){
			define('IMAGE_PATH','https://'.BUCKET.'.'.BUCKET_REGION.'.digitaloceanspaces.com/');
		}else if(BUCKET_TYPE=='CJ'){
			define('IMAGE_PATH','https://'.BUCKET_REGION.'.cloudjiffy.net/'.BUCKET.'/');
		}else{
			define('IMAGE_PATH','https://'.BUCKET.'.s3.amazonaws.com/');
		}
		define('CMS_DIR', 'upload/cms_images/');
		define('UPLOAD_DIR', 'upload/');
		define('BANNER_UPLOAD_DIR', 'upload/setting/');
		define('PRODUCT_IMAGE_UPLOAD_PATH', 'upload/product/');
		define('PRODUCT_IMAGE_THUMB_UPLOAD_PATH', 'upload/product/thumb/');

		define('PRODUCT_IMAGE_PATH', IMAGE_PATH.PRODUCT_IMAGE_UPLOAD_PATH);
		define('QR_IMAGE_PATH', 'https://'.BUCKET.'.s3.'.BUCKET_REGION.'.amazonaws.com/');
		define('PRODUCT_IMAGE_THUMB_PATH', IMAGE_PATH.PRODUCT_IMAGE_THUMB_UPLOAD_PATH);

		define('PROFILE_IMAGE_UPLOAD_PATH', 'upload/profile/');
		define('PROFILE_IMAGE_THUMB_UPLOAD_PATH', 'upload/profile/thumb/');

		define('PROFILE_IMAGE_PATH', IMAGE_PATH.PROFILE_IMAGE_UPLOAD_PATH);
		define('PROFILE_IMAGE_THUMB_PATH', IMAGE_PATH.PROFILE_IMAGE_THUMB_UPLOAD_PATH);

		define('PAN_IMAGE_UPLOAD_PATH', 'upload/pan/');
		define('PAN_IMAGE_PATH', IMAGE_PATH.PAN_IMAGE_UPLOAD_PATH);
		
		define('BANK_DOCUMENT_IMAGE_UPLOAD_PATH', 'upload/pan/');
		define('BANK_DOCUMENT_PATH', IMAGE_PATH.BANK_DOCUMENT_IMAGE_UPLOAD_PATH);

		define('CONTEST_CATEGORY_IMG_UPLOAD_PATH', 'assets/img/');
	    
		define('CD_NOTIFY_EMAILS','sumit.maheshwari@vinfotech.com,gaurav.choudhary@vinfotech.com');

	break;

	case 'testing':
		define('PROJECT_FOLDER_NAME', '');
		define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].PROJECT_FOLDER_NAME."/");
		
		define('SERVER_IP', HTTP_PROTOCOL.'://'.SERVER_NAME);
		define('NODE_ADDR', SERVER_IP.':3500');
		define('LINEUP_NODE_ADDR', SERVER_IP.':4000/');
		define('GAME_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/sports/');
		define('FANTASY_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/fantasy/');
	    define('USER_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/user/');
		define('PREDICTION_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/prediction/');
	    define('CURL_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/cron/');
	    define('CRON_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/cron/');
	    define('BASE_APP_PATH', SERVER_IP.PROJECT_FOLDER_NAME."/");
		define('CD_NOTIFY_EMAILS','sumit.maheshwari@vinfotech.com,gaurav.choudhary@vinfotech.com');
	    
		//upload media setting
		define('IMAGE_SERVER','remote');
		if(BUCKET_TYPE=='DO'){
			define('IMAGE_PATH','https://'.BUCKET.'.'.BUCKET_REGION.'.digitaloceanspaces.com/');
		}else if(BUCKET_TYPE=='CJ'){
			define('IMAGE_PATH','https://'.BUCKET_REGION.'.cloudjiffy.net/'.BUCKET.'/');
		}else{
			define('IMAGE_PATH','https://'.BUCKET.'.s3.amazonaws.com/');
		}
		define('UPLOAD_DIR', 'upload/');
		define('CMS_DIR', 'upload/cms_images/');
		define('BANNER_UPLOAD_DIR', 'upload/setting/');
		define('PRODUCT_IMAGE_UPLOAD_PATH', 'upload/product/');
		define('PRODUCT_IMAGE_THUMB_UPLOAD_PATH', 'upload/product/thumb/');

		define('PRODUCT_IMAGE_PATH', IMAGE_PATH.PRODUCT_IMAGE_UPLOAD_PATH);
		define('PRODUCT_IMAGE_THUMB_PATH', IMAGE_PATH.PRODUCT_IMAGE_THUMB_UPLOAD_PATH);

		define('PROFILE_IMAGE_UPLOAD_PATH', 'upload/profile/');
		define('PROFILE_IMAGE_THUMB_UPLOAD_PATH', 'upload/profile/thumb/');

		define('PROFILE_IMAGE_PATH', IMAGE_PATH.PROFILE_IMAGE_UPLOAD_PATH);
		define('PROFILE_IMAGE_THUMB_PATH', IMAGE_PATH.PROFILE_IMAGE_THUMB_UPLOAD_PATH);

		define('PAN_IMAGE_UPLOAD_PATH', 'upload/pan/');
		define('PAN_IMAGE_PATH', IMAGE_PATH.PAN_IMAGE_UPLOAD_PATH);

		define('BANK_DOCUMENT_IMAGE_UPLOAD_PATH', 'upload/pan/');
		define('BANK_DOCUMENT_PATH', IMAGE_PATH.BANK_DOCUMENT_IMAGE_UPLOAD_PATH);

		define('CONTEST_CATEGORY_IMG_UPLOAD_PATH', 'assets/img/');
	break;

	case 'production':
		define('PROJECT_FOLDER_NAME', '');
		define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].PROJECT_FOLDER_NAME."/");

		define('SERVER_IP', HTTP_PROTOCOL.'://'.SERVER_NAME);
		define('NODE_ADDR', SERVER_IP.':3500');
		define('LINEUP_NODE_ADDR', SERVER_IP.':4000/');
		define('GAME_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/sports/');
		define('FANTASY_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/fantasy/');
	    define('USER_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/user/');
		define('PREDICTION_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/prediction/');
	    define('CURL_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/cron/');
	    define('CRON_API_URL', SERVER_IP.PROJECT_FOLDER_NAME.'/cron/');
	    define('BASE_APP_PATH', SERVER_IP.PROJECT_FOLDER_NAME."/");
	    define('CD_NOTIFY_EMAILS','sumit.maheshwari@vinfotech.com,gaurav.choudhary@vinfotech.com');
	    
		//upload media setting
		define('IMAGE_SERVER','remote');

		if(BUCKET_TYPE=='DO'){
			define('IMAGE_PATH','https://'.BUCKET.'.'.BUCKET_REGION.'.digitaloceanspaces.com/');
		}else if(BUCKET_TYPE=='CJ'){
			define('IMAGE_PATH','https://'.BUCKET_REGION.'.cloudjiffy.net/'.BUCKET.'/');
		}else{
			define('IMAGE_PATH','https://'.BUCKET.'.s3.amazonaws.com/');
		}
		define('UPLOAD_DIR', 'upload/');
		define('CMS_DIR', 'upload/cms_images/');
		define('BANNER_UPLOAD_DIR', 'upload/setting/');
		define('PRODUCT_IMAGE_UPLOAD_PATH', 'upload/product/');
		define('PRODUCT_IMAGE_THUMB_UPLOAD_PATH', 'upload/product/thumb/');

		define('PRODUCT_IMAGE_PATH', IMAGE_PATH.PRODUCT_IMAGE_UPLOAD_PATH);
		define('PRODUCT_IMAGE_THUMB_PATH', IMAGE_PATH.PRODUCT_IMAGE_THUMB_UPLOAD_PATH);

		define('PROFILE_IMAGE_UPLOAD_PATH', 'upload/profile/');
		define('PROFILE_IMAGE_THUMB_UPLOAD_PATH', 'upload/profile/thumb/');

		define('PROFILE_IMAGE_PATH', IMAGE_PATH.PROFILE_IMAGE_UPLOAD_PATH);
		define('PROFILE_IMAGE_THUMB_PATH', IMAGE_PATH.PROFILE_IMAGE_THUMB_UPLOAD_PATH);

		define('PAN_IMAGE_UPLOAD_PATH', 'upload/pan/');
		define('PAN_IMAGE_PATH', IMAGE_PATH.PAN_IMAGE_UPLOAD_PATH);

		define('BANK_DOCUMENT_IMAGE_UPLOAD_PATH', 'upload/pan/');
		define('BANK_DOCUMENT_PATH', IMAGE_PATH.BANK_DOCUMENT_IMAGE_UPLOAD_PATH);

		define('CONTEST_CATEGORY_IMG_UPLOAD_PATH', 'assets/img/');
	break;

	default:
	break;
}

define('AADHAR_IMAGE_UPLOAD_PATH', 'upload/aadhar/');
define('AADHAR_IMAGE_PATH', IMAGE_PATH.AADHAR_IMAGE_UPLOAD_PATH);

//PAYMENT GATEWAYS

//Payumoney config 1
define('PAYU_TXN_VALIDATE_BASE_URL_PRO' ,'https://www.payumoney.com');
define('NEW_PAYU_TXN_VALIDATE_BASE_URL_PRO' ,'https://info.payu.in');
define('PAYU_TXN_VALIDATE_BASE_URL_TEST' ,'https://www.payumoney.com/sandbox');

//payu payout
define('PAYU_PAYOUT_TOKEN_URL',"https://{{mode}}.payu.in/oauth/token");
define('PAYU_PAYOUT_DEFAULT_URL', "https://{{mode}}.payumoney.com/");
define('TEST_CLIENT_ID',"6f8bb4951e030d4d7349e64a144a534778673585f86039617c167166e9154f7e");
define('PROD_CLIENT_ID',"ccbb70745faad9c06092bb5c79bfd919b6f45fd454f34619d83920893e90ae6b");

//PAYTM URLS 2
define('PAYTM_ORDER_STATUS_API_PRO', 'https://securegw.paytm.in/order/status');
define('PAYTM_ORDER_STATUS_API_TEST', 'https://securegw-stage.paytm.in/order/status');

//Ipay payment gateway configuration 5
define('IPAY_ACTION','https://payments.ipayafrica.com/v3/ke');
define('IPAY_CALLBACK_URL', USER_API_URL.'ipay/payment_callback');

//PAYSTACK
define('PAYSTACK_STATUS_URL','https://api.paystack.co/transaction/verify/');

//vPay config
define('VPAY_BASE_URL_PRO' ,'https://cricketme.in');
define('VPAY_BASE_URL_TEST' ,'https://vpay.vinfotech.org');

//MPESA URL
define('MPESA_ACCESS_TOKEN_URL','https://{{mode}}.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
define('MPESA_REQUEST_URL','https://{{mode}}.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
define('MPESA_STATUS_URL','https://{{mode}}.safaricom.co.ke/mpesa/transactionstatus/v1/query');
define('MPESA_TRANSACTION_TYPE','CustomerPayBillOnline');
define('MPESA_PAYOUT_URL','https://{{mode}}.safaricom.co.ke/mpesa/b2c/v1/paymentrequest');

//IFANTASY URL 
define('IFANTASY_TEST_URL','https://test.deluxepay365.com/api/StarPay/PayNow1');
define('IFANTASY_PROD_URL','https://prod.deluxepay365.com/api/StarPay/PayNow1');
define('IFANTASY_VERIFY_URL','https://prod.deluxepay365.com/api/StarPay/query');
define('IFANTASY_NOTIFY_URL', USER_API_URL.'ifantasy/notify');
define('IFANTASY_CALLBACK_URL', USER_API_URL.'ifantasy/callback');

//CASHIERPAY URL
define('CASHIERPAY_TBASE_URL','https://pqc.bebuoy.com/');
define('CASHIERPAY_PBASE_URL','https://pg.indi11.com/');
define('CASHIERPAY_PAY_URL','pgui/jsp/paymentrequest');
define('CASHIERPAY_STATUS_URL','pgws/transact');
define('CASHIERPAY_CALLBACK_URL', USER_API_URL.'cashierpay/callback');

//razorpayx payout
define('RAZORPAYX_STATUS_URL','https://api.razorpay.com/v1/payouts/');

//PHONEPE URLS
define('PHONEPE_PAY_TEST_URL', "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay");
define('PHONEPE_PAY_PROD_URL', "https://api.phonepe.com/apis/hermes/pg/v1/pay");
define('PHONEPE_STATUS_TEST_URL', "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/status/");
define('PHONEPE_STATUS_PROD_URL', "https://api.phonepe.com/apis/hermes/pg/v1/status/");

//JUSPAY URLS
define('JUSPAY_PAY_TEST_URL', "https://api.juspay.in/session");
define('JUSPAY_PAY_PROD_URL', "https://api.juspay.in/session");
define('JUSPAY_ORDER_TEST_URL', "https://api.juspay.in/orders/");
define('JUSPAY_ORDER_PROD_URL', "https://api.juspay.in/orders/");


//database tables
//user
define('ACTIVE_LOGIN'				,'active_login');
define('APP_CONFIG'				,'app_config');
define('SPORTS_HUB'					,'sports_hub');
define('ADMIN'						,'admin');
define('ADMIN_ROLES'				,'admin_roles');
define('ADMIN_ROLES_RIGHTS'			,'admin_roles_rights');

define('ADMIN_RECHARGE'		,'admin_recharge');
define('ADMIN_TRANSACTION'		,'admin_transaction');

define('AFFILIATE'					,'affiliate');
define('AFFILIATE_EARNING'			,'affiliate_earning');
define('AFFILIATE_HISTORY'			,'affiliate_history');
define('AFFILIATE_MASTER'			,'affiliate_master');
define('AFFILIATE_USER'				,'affiliate_user');
define('AFFILIATE_REPORT'			,'affiliate_report');
define('ANALYTICS_USER_LOGIN'		,'analytics_user_login');
define('SPIN_THE_WHEEL'				,'spinthewheel');
define('SPIN_CLAIMED'				,'spin_claimed');
define('COMMON_CONTENT'				,'common_content');
define('CASHFREE_WALLET_BANK'		,'cashfree_wallet_bank');
define('CD_EMAIL_SENT'		,'cd_email_sent');
define('SHORTENED_URLS'				,'shortened_urls');

//analytics table 
define('ANALYTICS','analytics');
define('APP_BANNER'					,'app_banner');
define('BONUS_FUND'					,'bonus_fund');
define('CD_BALANCE'					,'cd_balance');
define('CD_BALANCE_DEDUCT_HISTORY'	,'cd_balance_deduct_history');
define('CD_BALANCE_HISTORY'			,'cd_balance_history');
define('CD_EMAIL_TEMPLATE'			,'cd_email_template');
define('CD_RECENT_COMMUNICATION'	,'cd_recent_communication');
define('CD_SPORTS_PREFERENCE'		,'cd_sport_preferences');
define('CD_USER_BASED_LIST'			,'cd_user_based_list');
define('CD_EMAIL_CATEGORY'			,'cd_email_category');
define('SMS_TEMPLATE'			    ,'sms_template');

define('CMS_PAGES'					,'cms_pages');
define('EMAIL_TEMPLATE'				,'email_template');
define('MANAGE_OTP'					,'manage_otp');
define('MASTER_COUNTRY'				,'master_country');
define('MASTER_STATE'				,'master_state');
define('MASTER_SOURCE'				,'master_source');
define('NOTIFICATION'				,'notification');
define('NOTIFICATION_DESCRIPTION'	,'notification_description');
define('ORDER'						,'order');
define('PROMO_CODE'					,'promo_code');
define('PROMO_CODE_EARNING'			,'promo_code_earning');
define('PROMO_CODE_PAID_EARNING'	,'promo_code_paid_earnings');
define('RECENT_COMMUNICATION'		,'recent_communication');
define('REFERRAL_FUND'				,'referral_fund');
define('REFFERAL'					,'refferal');
define('SHORT_URLS'					,'short_urls');
define('TEST'						,'test');
define('TRANSACTION'				,'transaction');
define('USER'						,'user');
define('FAQ_QUESTIONS'				,'faq_questions');
define('FAQ_CATEGORY'				,'faq_category');
define('USER_BANK_DETAIL'			,'user_bank_detail');
define('USER_AADHAR'			,'user_aadhar');
define('USER_AFFILIATE_HISTORY'		,'user_affiliate_history');
define('DEALS'		,'deal');
define('DEALS_EARNING'		,'deal_earning');
define('MODULE_SETTING'		,'module_setting');
define('SUBMODULE_SETTING'		,'submodule_setting');
define('AVATARS'				,'avatars');
define('FINANCE_CATEGORY','finance_category');
define('FINANCE_DASHBOARD','finance_dashboard');
define('QUIZ','quiz');
define('QUIZ_QUESTION','quiz_question');
define('QUIZ_OPTIONS','quiz_options');
define('QUIZ_ANSWERS','quiz_answers');
define('QUIZ_VISIT_BY_USER','quiz_visit_by_user');
define('QUIZ_LEADERBOARD','quiz_leaderboard');
define('FEATURED_LEAGUE','featured_league');
define('TP_GAMES','tp_games');

define('TRANSACTION_MESSAGES'		,'transaction_messages');
define('USER_TRACK'		            ,'user_track');
define('USER_BONUS_CASH'		    ,'user_bonus_cash');
define('MATCH_REPORT','match_report');
define('CONTEST_REPORT','contest_report');
define('USER_MATCH_REPORT','user_match_report');
define('GST_REPORT','gst_report');
define('BANNED_STATE','banned_state');
define('WHATS_NEW','whats_new');
define('USER_TDS_CERTIFICATE','user_tds_certificate');
define('USER_TDS_REPORT','user_tds_report');
define('MASTER_PG','master_pg');

//Referral Leaderboard table names
define('REFERRAL_PRIZE','referral_prize');
define('REFERRAL_LEADERBOARD_DAY','referral_leaderboard_day');
define('REFERRAL_LEADERBOARD_WEEK','referral_leaderboard_week');
define('REFERRAL_LEADERBOARD_MONTH','referral_leaderboard_month');
define('REFERRAL_PRIZE_DISTRIBUTION_HISTORY','referral_prize_distribution_history');

//fantasy tables
define("BANNER_MANAGEMENT"			,"banner_management");
define("BANNER_TYPE"				,"banner_type");
define("BOOSTER"					,"booster");
define("BOOSTER_COLLECTION"			,"booster_collection");
define('COLLECTION_MASTER'			,'collection_master');
define('COLLECTION_SEASON'			,'collection_season');
define('COMPLETED_TEAM'				,'completed_team');
define('CONTEST'					,'contest');
define('CONTEST_TEMPLATE'			,'contest_template');
define('GAME_PLAYER_SCORING'		,'game_player_scoring');
define('GAME_STATISTICS_CRICKET'	,'game_statistics_cricket');
define('GAME_STATISTICS_KABADDI'	,'game_statistics_kabaddi');
define('GAME_STATISTICS_SOCCER'		,'game_statistics_soccer');
define('GAME_STATISTICS_BASKETBALL' , 'game_statistics_basketball');
define('GAME_STATISTICS_FOOTBALL' , 'game_statistics_football');
define('GAME_STATISTICS_BASEBALL' , 'game_statistics_baseball');
define('GAME_STATISTICS_NCAA' 	  , 'game_statistics_ncaa');
define('GAME_STATISTICS_CFL' 	  , 'game_statistics_cfl');
define('GAME_STATISTICS_NCAA_BASKETBALL' 	  , 'game_statistics_ncaa_basketball');
define('GAME_STATISTICS_MOTORSPORT','game_statistics_motorsport');
define('CRICKET_FOW' , 'cricket_fow');
define('GAME_STATISTICS_TENNIS' , 	'game_statistics_tennis');

define('INVITE'						,'invite');
define('LEAGUE'                     ,'league');
define('LINEUP'						,'lineup');
define('BENCH_PLAYER'				,'bench_player');
define('LINEUP_MASTER'				,'lineup_master');
define('LINEUP_MASTER_CONTEST'		,'lineup_master_contest');
define('MASTER_CONTEST_TYPE'     	,'master_contest_type');
define('MASTER_DATA_ENTRY'			,'master_data_entry');
define('MASTER_DRAFTING_STYLES'     ,'master_drafting_styles');
define('MASTER_DURATION'            ,'master_duration');
define('MASTER_FILTER'				,'master_filter');
define("MASTER_GROUP"				,'master_group');
define('MASTER_LINEUP_POSITION'		,'master_lineup_position');
define('MASTER_SCORING_CATEGORY'	,'master_scoring_category');
define('MASTER_SCORING_RULES'		,'master_scoring_rules');
define('MASTER_SPORTS'				,'master_sports');
define('MASTER_SPORTS_FORMAT'		,'master_sports_format');
define('PLAYER'						,'player');
define('PLAYER_TEAM'				,'player_team');
define('POINT_SYSTEM'				,'point_system');
define('SEASON'						,'season');
define('SEASON_WEEK'				,'season_week');
define('TEAM'						,'team');
define('TEAM_LEAGUE'				,'team_league');
define('TEAM_PLAYER_MAPPING', 'team_player_mapping');
define('MERCHANDISE'				,'merchandise');
define('MINI_LEAGUE'				,'mini_league');
define('MINI_LEAGUE_SEASON'			,'mini_league_season');
define('MINI_LEAGUE_LEADERBOARD'	,'mini_league_leaderboard');
define('SEASON_MATCH'				,'season_match');

//DFS tournament tables
define('TOURNAMENT'						,'tournament');
define('TOURNAMENT_COMPLETED_TEAM'	,'tournament_completed_team');
define('TOURNAMENT_LINEUP'				,'tournament_lineup');
define('TOURNAMENT_SEASON'				,'tournament_season');
define('TOURNAMENT_TEAM'				,'tournament_team');
define('TOURNAMENT_INVITE'				,'tournament_invite');
define('TOURNAMENT_BANNER'				,'tournament_banner');
define('USER_TOURNAMENT_SEASON'			,'user_tournament_season');
define('TOURNAMENT_SCORING_RULES'			,'tournament_scoring_rules');
define('TOURNAMENT_HISTORY','tournament_history');
define('TOURNAMENT_HISTORY_TEAMS'		,'tournament_history_teams');

//User xp points tables
define('XP_ACTIVITIES'							,'xp_activities');
define('XP_ACTIVITY_MASTER'						,'xp_activity_master');
define('XP_LEVEL_POINTS'						,'xp_level_points');
define('XP_LEVEL_REWARDS'						,'xp_level_rewards');
define('XP_BADGE_MASTER'						,'xp_badge_master');
define('XP_USER_HISTORY'						,'xp_user_history');
define('XP_REWARD_HISTORY'						,'xp_reward_history');
define('XP_USERS'								,'xp_users');

//leaderboard tables
define('LEADERBOARD_CATEGORY','leaderboard_category');
define('LEADERBOARD_PRIZE','leaderboard_prize');
define('LEADERBOARD','leaderboard');
define('LEADERBOARD_HISTORY','leaderboard_history');

//h2h challenge
define('COLLECTION_TEMPLATE','collection_template');
define('H2H_CMS','h2h_cms');
define('H2H_USERS','h2h_users');


//this is mongo db collections
define('MG_LINEUPS'					,'lineups');
define('MG_COMPLETED_LINEUPS'		,'completed_lineups');
define('MG_FEEDBACK'				,'feedback');

// Cricket Format Type
define('CRICKET_ONE_DAY'     				,1);
define('CRICKET_ONE_DAY_TEXT'     			,"ODI");
define('CRICKET_TEST'     					,2);
define('CRICKET_TEST_TEXT'     				,"TEST");
define('CRICKET_T20'     					,3);
define('CRICKET_T20_TEXT'     				,"T20");
define('CRICKET_T10'     					,4);
define('CRICKET_T10_TEXT'     				,"T10");
define('CONTEST_CLOSE_INTERVAL',15);//time in minutes

define('JERSEY_CONTEST_DIR', 'upload/jersey/');
define('FLAG_CONTEST_DIR', 'upload/flag/');
define('LEAGUE_IMAGE_DIR', 'upload/league_logo/');
define('PLAYER_IMAGE_DIR', 'player/');
define('MERCHANDISE_IMAGE_DIR', 'upload/merchandise/');
define('H2H_IMAGE_DIR', 'upload/h2h/');
define('AVATARS_IMAGE_DIR', 'upload/avatar/');
define('SPONSOR_IMAGE_DIR', 'upload/sponsor/');

define('CD_PUSH_HEADER_DIR', 'upload/cd/push_header/');
define('CD_PUSH_BODY_DIR', 'upload/cd/push_body/');

define('LANGUAGE_FILE_PATH',IMAGE_PATH.'assets/i18n/translations/#lang#.json');
define('LANGUAGE_FILE_UPLOAD_PATH','assets/i18n/translations/');

define('PREDICTION_MASTER','prediction_master');
define('PREDICTION_OPTION','prediction_option');
define('USER_PREDICTION','user_prediction');

define('DEPOSIT_TXN','deposit_txn');
define('DEPOSIT_TYPE','deposit_type');

//OPEN predictor, prediction same table names used in open predictor
define('CATEGORY','category');

//mongo collection
define('COLL_EARN_COINS','earn_coins');
define('COLL_COIN_REWARDS','coin_rewards');
define('COLL_COIN_REWARD_HISTORY','coin_reward_history');
define('COLL_FEEDBACK_QUESTIONS','feedback_questions');
define('COLL_FEEDBACK_QUESTION_ANSWERS','feedback_question_answers');
define('COLL_TRANSACTION_MESSAGES','transaction_messages');
define('SESSION_TRACK','session_track');

// new table for collecting daily active session time
define('DAILY_ACTIVE_SESSION','daily_active_session');

//user site access variables
define('SITE_ACCESS_INTERVAL',"0");
define('MEMORY_USAGE_LIMIT',"70");
define('CPU_LOAD_LIMIT',"80");
define('DISK_USAGE_LIMIT',"75");
//LINEUP OUT PUSH NOTIFICATION FLAG
define('LINEUP_OUT_PUSH_ENABLE',1);//0-Disabled,1-Enabled


/********************Coin Package tables*********************** */
define('COIN_PACKAGE','coin_package');
/********************Coin Package tables*********************** */

define('USER_SELF_EXCLUSION','user_self_exclusion');

//NETWORK FANTASY CONSTANTS
define('ALLOW_NETWORK_FANTASY',getenv('ALLOW_NETWORK_FANTASY'));
define('NETWORK_CLIENT_ID',getenv('NETWORK_CLIENT_ID'));
define('NETWORK_FANTASY_URL',getenv('NETWORK_FANTASY_URL'));
define('NETWORK_CONTEST', 'network_contest');
define('NETWORK_LINEUP_MASTER', 'network_lineup_master');

//scratch & win tables in user db
define('SCRATCH_WIN'		,'scratch_win');
define('SCRATCH_WIN_CLAIMED' ,'scratch_win_claimed');

//leaderboard
define('REFERRAL_LEADERBOARD_ID','1');
define('FANTASY_LEADERBOARD_ID','2');
define('STOCK_LEADERBOARD_ID','3');
define('STOCK_EQUITY_LEADERBOARD_ID','4');
define('STOCK_PREDICT_LEADERBOARD_ID','5');
define('LIVE_STOCK_FANTASY_LEADERBOARD_ID','6');
define('PICKS_FANTASY_LEADERBOARD_ID','8');


//Subscription table
define('SUBSCRIPTION',	'subscription');
define('USER_SUBSCRIPTION',	'user_subscription');

//LEAGUE PROVIDERS 
define('ENTITYSPORT_PROVIDER_ID',1);
define('GOALSERVE_PROVIDER_ID',2);
define('OPTA_PROVIDER_ID',3);
define('CRICKETAPI_PROVIDER_ID',4);
define('SPORTRADAR_PROVIDER_ID',5);

define('DOWNLOAD_APP_COINS',10);

//paylogic test url
define('PAYLOGIC_PAY_TURL'	, 'http://3.108.21.243:8080/paytest/payprocessorV2');
define('PAYLOGIC_STATUS_TURL', 'http://3.108.21.243:8080/paytest/getTxnDetails');

//directpay 
define('DIRECT_STATUS_TURL', 'https://test-gateway.directpay.lk/api/v3/checkPaymentStatus');
//production
define('PAYLOGIC_PAY_URL'	, 'https://pg1.paylogic.biz/pay/payprocessorV2');
define('PAYLOGIC_STATUS_URL', 'https://pg1.paylogic.biz/pay/getTxnDetails');

//BTCPAY
define('BTC_URL', 'https://btcpay680732.lndyn.com/api/v1/stores/%s/invoices');

//Allow DFS with Multigame
define('DFS_MULTI',getenv('DFS_MULTI'));

//bot request history table for multibot module
define('BOT_REQ_HISTORY','bot_req_history');

//encryption
define("ALLOW_ENC",getenv('ALLOW_ENC'));
define("ENC_KEY","QFZu7g9M38Im");
define("ENC_SALT","HF2D4xQFZu7g");
define("ENC_IV","5Xy85XHF2D4HF2D4");
define('ENC_SC_KEY',"PHPg1iYIbxskCS4");

define('OTP_EXPIRY_TIME',"5"); // value in minutes
//FCM Topic Prefix
define('FCM_TOPIC',getenv('FCM_TOPIC'));
define('ALLOW_FEATURED_LEAGUE','3');
//Sport Predictor feed option
define('SPORT_PREDICTOR_FEED',getenv('SPORT_PREDICTOR_FEED'));
define('FEED_MQ_HOST' ,getenv('FEED_MQ_HOST'));
define('FEED_MQ_PORT' , getenv('FEED_MQ_PORT'));
define('FEED_MQ_USER' ,getenv('FEED_MQ_USER'));
define('FEED_MQ_PASSWORD' ,getenv('FEED_MQ_PASSWORD'));

//TRADE tables
define("MASTER_TEMPLATE","master_template");

