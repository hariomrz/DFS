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
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0777);

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

/*
  |---------------------------------------------
  | Define Constants for Tables which is in DB
  |---------------------------------------------
 */

//user logins
define('ACTIVITY', 'Activity');
define('ACTIVITYDETAILS', 'ActivityDetails');
define('ACTIVITYMEMBERS', 'ActivityMembers');

define('ACTIVITYLINKS', 'ActivityLinks');
define('ENTITYTAGS', 'EntityTags');
define('ENTITYTAGSCATEGORY', 'EntityTagsCategory');
define('ENTITYTAGSDUMMY', 'EntityTagsDummy');
define('ACTIVITYTYPE', 'ActivityTypes');
define('ACTIVITYUSER', 'ActivityUser');
define('ACTIVITYWALL', 'ActivityWall');
define('ACTIVITYHISTORY', 'ActivityHistory');
define('ACTIVITYHIDE', 'ActivityHide');

//Define necessary Tables Constants
define('ALLOWEDPOSTTYPE', 'AllowedPostType');
define('ALLOWEDGROUPTYPE', 'AllowedGroupType');
define('ACTIVELOGINS', 'ActiveLogins');
define('ACTIVE_LOGIN'				,'active_login');
define('AGEGROUPS', 'AgeGroups');
define('ALLOWEDIPS', 'AllowedIps');
define('ANALYTICLOGINCOUNTS', 'AnalyticLoginCounts');
define('ANALYTICLOGINERRORS', 'AnalyticLoginErrors');
define('ANALYTICLOGINFIRSTCOUNT', 'AnalyticLoginFirstCount');
define('ANALYTICLOGINGEOCOUNT', 'AnalyticLoginGeoCount');
define('ANALYTICLOGINS', 'AnalyticLogins');

define('ANALYTICSPROVIDERS', 'AnalyticsProviders');
define('ANALYTICSPROVIDERSDATA', 'AnalyticsProvidersData');
define('APPLICATIONS', 'Applications');
define('APPLICATIONRIGHTS', 'ApplicationRights');
define('ARCHIVEACTIVITY', 'ArchiveActivity');
define('ANNOUNCEMENTVISIBILITYSETTINGS', 'AnnouncementVisibilitySettings');

//Announcement Popup
define('ANNOUCEMENTPOPUPS','AnnouncementPopups');
define('USERIGNOREDPOPUPS','UserIgnoredPopups');

define('USERIGNOREDANNOUCEMENT','UserIgnoredAnnouncement');

define('BETAINVITES', 'BetaInvites');
define('BETAINVITELOGS', 'BetaInviteLogs');
define('BUSINESSUNITS', 'BusinessUnits');
define('BUSINESSUNITCONFIGS', 'BusinessUnitConfigs');
define('BLOCKUSER', 'BlockUser');
define('BROWSERS', 'Browsers');

define('CISESSIONS', 'CISessions');
define('CITIES', 'Cities');
define('CITYNEWS', 'CityNews');
define('CITYNEWSVISITBYUSER', 'CityNewsVisitByUser');

define('CLIENTERRORS', 'ClientErrors');
define('COMMUNICATIONS', 'Communications');
define('COMMUNICATIONS_ARCHIVE', 'Communications_Archive');

define('ADMINCOMMUNICATION', 'AdminCommunication');
define('ADMINCOMMUNICATIONHISTORY', 'AdminCommunicationHistory');

define('CONFIGS', 'Configs');
define('COUNTRYMASTER', 'CountryMaster');
define('COVERIMAGESTATE', 'CoverImageState');
define('CRONUPDATE', 'CronUpdate');
define('COMMENTHISTORY', 'CommentHistory');
define('CONTEST', 'Contest');

define('DATATYPES', 'DataTypes');
define('DIALYDIGEST', 'DailyDigest');

define('DELETEDMEDIA', 'DeletedMedia');
define('DEVICETYPES', 'DeviceTypes');
define('DIMDATE', 'DimDate');
define('DEFAULTACTIVITYRULE', 'DefaultActivityRule');
define('RULEQUESIONS','RuleQuestions');

define('EDUCATION', 'UserEducation');
define('EMAILSETTINGS', 'EmailSettings');
define('EMAILTYPES', 'EmailTypes');
define('ENGAGEMENT', 'Engagement');
define('ENTITYENDORSEMENT', 'EntityEndorsements');
define('ENTITYLOG', 'EntityLog');
define('ENTITYSKILLS', 'EntitySkills');
define('ENTITYVIEW', 'EntityView');
define('ENTITYVIEWLOG', 'EntityViewLog');
define('ERRORCODES', 'ErrorCodes');
define('ERRORLOGS', 'ErrorLogs');
define('ERRORLOGATTACHMENTS', 'ErrorLogAttachments');
define('ERRORTYPES', 'ErrorTypes');
define('EVENTS', 'Events');
define('EVENTGROUPS', 'EventGroups');
define('EVENTS_PLACES', 'EventPlaces');

define('FLAG', 'Flag');
define('FRIENDS', 'Friends');
define('FAVOURITE', 'Favourite');
define('FORUM', 'Forum');
define('FORUMMANAGER', 'ForumManager');
define('FORUMCATEGORY', 'ForumCategory');
define('FORUMCATEGORYMEMBER', 'ForumCategoryMember');
define('FORUMCATEGORYVISIBILITY', 'ForumCategoryVisibility');
define('FORUMUSERCATEGORY', 'ForumUserCategory');


define('GROUPS', 'Groups');
define('GROUPMEMBERS', 'GroupMembers');
define('GROUPALLMEMBERS', 'GroupAllMembers');

define('MOSTRECENTNOTIFICATIONDAYS', '15');
define('MOSTRECENTNOTIFICATIONCOUNT', '300');
define('MOSTRECENTCOMMUNICATIONDAYS', '15');
define('MOSTRECENTCOMMUNICATIONCOUNT', '300');

define('BLOG', 'Blog');
define('ADVERTISE', 'Advertise');
define('ADVERTISER', 'Advertiser');

define('IGNORE', 'EntityIgnore');
define('INVITATION', 'FriendInvitation');

define('LEVELMASTER', 'LevelMaster');
define('LOCALITY', 'Locality');
define('WARD', 'Ward');
define('ACTIVITYWARD', 'ActivityWard');
define('STORY', 'Story');
define('STORYWARD', 'StoryWard');
define('WARDUSERCOUNT', 'WardUserCount');
define('WARDTRENDINGTAGS', 'WardTrendingTags');
define('WARDENGAGEMENT', 'WardEngagement');
define('WARDFEATUREUSER', 'WardFeatureUser');


define('MEDIA', 'Media');
define('MEDIAABUSE', 'MediaAbuse');
define('MEDIADEVICECOUNTS', 'MediaDeviceCounts');
define('MEDIAEXTENSIONCOUNT', 'MediaExtensionCount');
define('MEDIAEXTENSIONS', 'MediaExtensions');
define('MEDIASECTIONCOUNT', 'MediaSectionCount');
define('MEDIASECTIONS', 'MediaSections');
define('MEDIASIZECOUNTS', 'MediaSizeCounts');
define('MEDIASIZES', 'MediaSizes');
define('MEDIASOURCECOUNT', 'MediaSourceCount');
define('MEDIATYPES', 'MediaTypes');
define('MEDIATYPE', 'MediaTypeId');
define('MODULES', 'Modules');
define('MODULEROLES', 'ModuleRoles');

define('MENTION', 'Mention');
define('MEDIA_KEYWORD', 'MediaKeyword');
define('MODULENOTIFICATION', 'ModuleNotification');

//Mandrill Messages tables
define('MANDRILLMESSAGES', 'MandrillMessages');
define('MANDRILLEVENTS', 'MandrillEvents');
define('MESSAGEEVENTS', 'MessageEvents');
define('MESSAGETAGS', 'MessageTags');
define('MUTESOURCE', 'MuteSource');
define('MANAGEOTP', 'ManageOtp');
define('ALBUMS', 'Albums');

define('NOTIFICATIONS', 'Notifications');
define('NOTIFICATIONS_ARCHIVE','Notifications_Archive');
define('NOTIFICATIONPARAMS_ARCHIVE', 'NotificationParams_Archive');
define('NOTIFICATIONPARAMS', 'NotificationParams');
define('NOTIFICATIONTYPES', 'NotificationTypes');
define('POSTNOTIFICATIONLOG', 'PostNotificationLog');

define('PAGES', 'Pages');
define('PAGEMEMBERS', 'PageMembers');
define('PARTICIPANTS', 'Participants');
define('PRIORITIZESOURCE', 'PrioritizeSource');
define('PLATEFORMDEVICES', 'PlatformDevices');
define('PLATEFORMTYPES', 'PlatformTypes');
define('POST', 'Post');
define('POSTCOMMENTS', 'PostComments');
define('COMMENTDETAILS', 'CommentDetails');
define('COMMENTLINKS', 'CommentLinks');
define('POSTLIKE', 'PostLike');
define('PROFILEURL', 'ProfileUrl');
define('PRIVACYLABEL', 'PrivacyLabel');
define('PRIVACYOPTION', 'PrivacyOption');
define('PINTOTOP', 'PinToTop');


define('REFERRERTYPES', 'ReferrerTypes');
define('REMINDER', 'Reminder');
define('RELATIONSHIPSCORE', 'RelationshipScore');
define('RIGHTS', 'Rights');
define('ROLERIGHTS', 'RoleRights');
define('ROLES', 'Roles');
define('RESOLUTION', 'Resolutions');
define('RATINGS', 'Ratings');
define('RATINGPARAMETER', 'RatingParameter');
define('RATINGPARAMETERVALUE', 'RatingParameterValue');
define('REVIEWS', 'Reviews');
define('REQUESTFORANSWER', 'RequestForAnswer');
define('RELATEDACTIVITY', 'RelatedActivity');


define('SESSIONLOGS', 'SessionLogs');
define('SEARCHLOG', 'SearchLog');
define('SIGNUPANALYTICGEOCOUNT', 'SignUpAnalyticGeoCount');
define('SIGNUPANALYTICLOGCOUNTS', 'SignUpAnalyticLogCounts');
define('SIGNUPANALYTICLOGERRORS', 'SignupAnalyticLogErrors');
define('SIGNUPANALYTICVISITCOUNT', 'SignUpAnalyticVisitCount');
define('SIGNUPANALYTICLOGS', 'SignupAnalyticLogs');
define('SKILLSMASTER', 'SkillsMaster');
define('SOURCES', 'Sources');
define('STATES', 'States');
define('STATUS', 'Status');
define('SIMILARSKILLS', 'SimilarSkills');
define('SHARELINKS', 'ShareLinks');
define('STICKYPOST', 'StickyPosts');

define('MANDRILLTAGS', 'MandrillTags');
define('TAGS', 'Tags');
define('TAGCATEGORY', 'TagCategory');
define('TAGSOFTAGCATEGORY', 'TagsOfTagCategory');
define('TAGWEIGHTAGELOG', 'TagWeightageLog');
define('TIMESLOTBREAKUPS', 'TimeSlotBreakups');
define('TIMESLOTS', 'TimeSlots');
define('TIMETAKENRANGE', 'TimeTakenRange');
define('TIMETAKENRANGECOUNT', 'TimeTakenRangeCount');
define('TIMEZONES', 'TimeZones');

define('USERTAGCATEGORY', 'UserTagCategory');
define('USERACTIVITYRANK', 'UserActivityRank');
define('USERDETAILS', 'UserDetails');
define('USERLOGINS', 'UserLogins');
define('USERNEWSFEEDSETTING', 'UserNewsFeedSetting');
define('USERNOTIFICATIONSETTINGS', 'UserNotificationSettings');
define('USERRESETPASSWORDS', 'UserResetPasswords');
define('USERROLES', 'UserRoles');
define('USERS', 'Users');
define('USERSESSIONES', 'UserSessiones');
define('USERSESSIONHISTORY', 'UserSessionHistory');
define('USERTYPES', 'UserTypes');
define('USERPROFILEPERCENTAGE', 'UserProfilePercentage');
define('USERPRIVACY', 'UserPrivacy');
define('USERSACTIVITYLOG', 'UsersActivityLog');
define('USERACTIVITYSCORES', 'UserActivityScores');
define('USERACTIVITYPOINTHISTORY', 'UserActivityPointHistory');
define('USERACTIVITYPOINTS', 'UserActivityPoints');


define('USERORIENTATION', 'UserOrientation');
define('ORIENTATIONCATEGORY', 'OrientationCategory');

define('PROFILEFIELDS', 'ProfileFields');
define('SEARCHFILTERS', 'SearchFilters');
define('USERFAMILYDETAILS', 'UserFamilyDetails');
define('SAMPLEDUMMYUSERS','SampleDummyUsers');

define('VOTES', 'Votes');

define('ENTITYNOTE', 'EntityNote');

define('VERIFICATION', 'Verification');
define('VERIFICATIONTYPE', 'VerificationType');
define('WEEKDAYS', 'Weekdays');
define('WORKEXPERIENCE', 'UserWorkExperience');

define('WATCHLIST','WatchList');
define('MYTASKSTATUS','MyTaskStatus');

define('CATEGORYMASTER', 'CategoryMaster');
define('ENTITYCATEGORY', 'EntityCategory');
define('EVENTUSERS', 'EventUsers');
define('EVENTINVITES', 'EventInvites');
define('MODULEUSERPERMISSIONS', 'ModuleUserPermissions');
define('LOCATIONS', 'Locations');

//New Message Module
define('N_MESSAGES', 'MessagesNew');
define('N_MESSAGE_THREAD', 'MessageThread');
define('N_MESSAGE_RECIPIENT', 'MessagesRecipients');
define('N_MESSAGE_DELETED', 'MessagesDeleted');
define('N_MESSAGES_LINKS', 'MessageLinks');
//For Newsletter Module
define('NEWSLETTERSUBSCRIBER', 'NewsLetterSubscriber');
define('NEWSLETTERGROUP', 'NewsLetterGroup');
define('NEWSLETTERGROUPMEMBER', 'NewsLetterGroupMember');
define('NEWSLETTER_COMPAIGN_REPORT', 'NewsLetterCompaignReport');

define('INTEREST', 'Interest');
define('USERINTEREST', 'UserInterest');

define('PROFESSION', 'Profession');
define('USERPROFESSION', 'UserProfession');

//End of Table constants
//Message Type
define('MESSAGE_121_SINGLE_THREAD', TRUE);

//FOLLOW
define('ALLOW_USER_FOLLOW', 1); //1 - allows following, 2 - does not allow following
define('FOLLOW', 'Follow');
define('ALLOW_COURSE_FOLLOW', 1);
define('ALLOW_PAGE_FOLLOW', 1);
define('ALLOW_CATEGORY_FOLLOW', 1);

define('MULTISESSION', TRUE);
define('SOCIALTYPE', "Web");
define('DEVICETYPE', "Web");
define('SITE_NAME', 'Fantasy Social');
define('EMAIL_ANALYTICS', 1); //1 FOR ENABLE ELSE DISABLE
define('REGISTRATION_EMAIL_TYPE_ID', 2);
define('COMMUNICATION_EMAIL_TYPE_ID', 1);
define('FORGOT_PASSWORD_EMAIL_TYPE_ID', 3);
define('FRIEND_REQUEST_EMAIL_TYPE_ID', 4);
define('CHANGE_PASSWORD_EMAIL_TYPE_ID', 5);
define('ADMIN_ROLE_ID', "1");
define('ADMIN_USER_ID', getenv('ADMIN_USER_ID'));
define('ADMIN_EMAIL', "sureshp@vinfotech.com");
define('BETAINVITE_EMAIL_TYPE_ID', 4);
define('COPYRIGHT', '&copy Copyright ' . date("Y") . ' vinfotech. All Rights Reserved.');
define('DEFAULT_CITY_ID', '36');
define('GLOBAL_IP', '0.0.0.0');
define('POWERED_BY', 'http://www.vinfotech.com/');
define('SEND_EMAIL_BY_CRON', 0); //1 FOR ENABLE ELSE DISABLE

define('AUTO_LOGOUT', 1); //1 FOR ENABLE ELSE DISABLE
define('AUTO_LOGOUT_TIME', 1440); //Time in minutes, current time set for 1 Day(60*24)
define('GLOBALEMAILSENDING', 15);
define('SENDMAILVIAMANDRILL', 16);

define('WE_ARE_WORKING_TIME', 2); //Time in seconds
define('STILL_WE_ARE_WORKING_TIME', 5); //Time in seconds
define('SEEMS_SOMETHING_WRONG_REFRESH_TIME', 10); //Time in seconds
define("QUERY_EXECUTION_TIME", 1.0014010501861572); //Time in seconds

define('DEFAULT_SOCIAL_TYPE', 'Web');
define('DEFAULT_RESOLUTION', 'Low');
define('DEFAULT_DEVICE_TYPE', 'Native');
define('DEFAULT_ROLE', '2');
define('DEFAULT_DEVICE_ID', '1');
define('DEFAULT_IP_ADDRESS', '127.0.0.1');
define('DEFAULT_RECOVERY_TYPE', 'EmailWithResetUrl');
define('DEFAULT_SOURCE_ID', '1');

define('PROFILE_SECTION_ID', '1');
define('PAGE_NO', '1');
define('PAGE_SIZE', '10');
define('CONST_PAGE_SIZE', '10');

define('ACTIVITY_PAGE_SIZE', '3');
define('COMMENTPAGESIZE', '1');

define('SUBSCRIBE', 'Subscribe');
define('MAXSTICKYPOST', '2');

define('QUIZ', 'Quiz');
define('QUESION', 'Question');
define('QUESIONOPTION', 'QuestionOption');
define('USERANSWER', 'UserAnswer');
define('QUIZVISITBYUSER', 'QuizVisitByUser');
define('QUIZPRIZE', 'QuizPrize');
define('QUIZPRIZEDISTRIBUTIONHISTORY', 'QuizPrizeDistributionHistory');
define('QUIZFOLLOW', 'QuizFollow');
define('LEADERBOARD', 'LeaderBoard');
define('TOPCONTRIBUTORS', 'TopContributors');
define('TAGFOLLOW', 'TagFollow');
define('TAGMUTE', 'TagMute');

//ALLOWED LANGUAGES
define('ALLOWED_LANGUAGES', "english|hindi");

define('AUTH_KEY', 'Loginsessionkey');
define('DFS_AUTH_KEY', 'Sessionkey');
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

define('PATH_IMG_UPLOAD_FOLDER', 'upload/');
define('PATH_IMG_PROFILE', 'profile');

define('PATH_VID_UPLOAD_FOLDER', 'upload/');

define('ADMIN_THUMB_HEIGHT', '56');
define('ADMIN_THUMB_WIDTH', '56');
define('ADMIN_THUMB', ADMIN_THUMB_WIDTH . 'x' . ADMIN_THUMB_HEIGHT);

// Thumb
define('THUMB_profilebanner', '/1200x300/');

//For Media section 
define('IMAGE_MEDIA_TYPE_ID', 1);
define('VIDEO_MEDIA_TYPE_ID', 2);
define('YOUTUBE_MEDIA_TYPE_ID', 3);

define('MIN_CONNECTION', '5');
define('MIN_GROUP', '2');

define('NEW_ALGORITHM', '0');

define('DEFAULT_PROFILE_ALBUM', 'Profile Photos');
define('DEFAULT_PROFILECOVER_ALBUM', 'Cover Photos');
define('DEFAULT_WALL_ALBUM', 'Wall Media');
define('DEFAULT_FILE_ALBUM', 'Files');

define('DEFAULT_RELATIONSHIP_SCORE', 11);

//Polls 
define('POLL_MAX_CHOICE', 4);
define('POLL_MIN_CHOICE', 2);
define('POLL', 'Poll');
define('POLLOPTION', 'PollOption');
define('POLLOPTIONVOTES', 'PollOptionVotes');
define('POLLINVITE', 'PollInvite');
define('POSTVISIBILITYSETTINGS','PostVisibilitySettings');
define('POLLANALYTICS', 'PollAnalytics');
define('ANNOUNCEMENT_DAYS',4);

//For Newsletter Constants
define('NEWSLETTER_SUBSCRIBER',2);
define('NEWSLETTER_UNSUBSCRIBER',1);
define('MC_CAMPAIGN_SUBJECT', "Welcome to ".SITE_NAME);
define('MC_LANGUAGE', "english");
define('MC_LIST_CONTACT_COMPANY', SITE_NAME);
define('MC_LIST_CONTACT_ADDRESS1', "31/2, Near Dhobi Ghat, New Palasia, Indore, Madhya Pradesh 452001");
define('MC_LIST_CONTACT_ADDRESS2', "");
define('MC_LIST_CONTACT_CITY', "Indore");
define('MC_LIST_CONTACT_STATE', "Madhya Pradesh");
define('MC_LIST_CONTACT_ZIP', "452001");
define('MC_LIST_CONTACT_COUNTRY', "India");
define('MC_LIST_CONTACT_PHONE', "");
define('MC_PERMISSION_REMINDER', "Subscribe from ".SITE_NAME);

define('ACTIVE_SMS_GATEWAY', getenv('ACTIVE_SMS_GATEWAY'));//blank for default or two factor
define('TWO_FACTOR_SMS_API_ENDPOINT', getenv('TWO_FACTOR_SMS_API_ENDPOINT'));
define('TWO_FACTOR_SMS_API_KEY', getenv('TWO_FACTOR_SMS_API_KEY'));

define('MSG91_AUTH_KEY', getenv('MSG91_AUTH_KEY'));
define('MSG91_SENDER_ID', getenv('MSG91_SENDER_ID'));
define('MSG91_ROUTE_ID', getenv('MSG91_ROUTE_ID'));
define('MSG91_API_BASE_URL', getenv('MSG91_API_BASE_URL'));
define('MSG91_DLT_TEMPLATE_ID', getenv('MSG91_DLT_TEMPLATE_ID'));
define('DEFAULT_PHONE_CODE', getenv('DEFAULT_PHONE_CODE'));

define('DBHOST', getenv('DBHOST'));
define('DBNAME', getenv('DBNAME'));
define('DBUSER', getenv('DBUSER'));
define('DBPASS', getenv('DBPASS'));
define('SAVE_QUERIES', getenv('SAVE_QUERIES'));

define('MDBHOST', getenv('MONGODB_HOSTNAME'));
define('MDBNAME', getenv('MONGODB_DBNAME'));
define('MDBUSER', getenv('MONGODB_USERNAME'));
define('MDBPASS', getenv('MONGODB_PASSWORD'));
define('MDBPORT', getenv('MONGODB_PORT'));
define('MDBNOAUTH', getenv('MONGODB_NO_AUTH'));
define('MONGO_RP', getenv('MONGO_RP'));
define('MONGO_RC', getenv('MONGO_RC'));
define('MONGO_SRV', getenv('MONGO_SRV'));
define('IS_MONGO_ENABLE' ,getenv('IS_MONGO_ENABLE'));

define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_USER', getenv('SMTP_USER'));
define('SMTP_PASS', getenv('SMTP_PASS'));
define('SMTP_PORT', getenv('SMTP_PORT'));
define('PROTOCOL', getenv('PROTOCOL')); 
define('MAILPATH', '');
define('MAILTYPE', 'html');

###### AWS CONSTANT ###########
define('BUCKET', getenv('BUCKET'));
define('BUCKET_ZONE', getenv('BUCKET_ZONE'));        
define('AWS_ACCESS_KEY', getenv('AWS_ACCESS_KEY'));
define('AWS_SECRET_KEY', getenv('AWS_SECRET_KEY'));

// ZENCODER API
define('ZENCODER_API_KEY', getenv('ZENCODER_API_KEY'));

 // Mandrill details #################
define('MANDRILL_API_KEY', getenv('MANDRILL_API_KEY'));
define('MANDRILL_FROM_EMAIL', getenv('MANDRILL_FROM_EMAIL'));
define('MANDRILL_FROM_NAME', getenv('MANDRILL_FROM_NAME'));

define('MQ_HOST', getenv('MQ_HOST')); 
define('MQ_PORT', getenv('MQ_PORT'));
define('MQ_USER', getenv('MQ_USER'));
define('MQ_PASSWORD', getenv('MQ_PASSWORD'));

define('CACHE_HOST', getenv('CACHE_HOST')); // CACHE HOST
switch (ENVIRONMENT)
{
    case 'production':
    	define('SUBDIR', '/social/');
        define('ROOT_FOLDER', '');
        define('THEAM_PATH', 'assets/');

        define('IPINFODBKEY', 'b2e0bb044541b5d37eda7d925f78e2aa33e8d9969d7b8af8e5c094364d9fe41a');

        // FACEBOOK application credential.
        define('FACEBOOK_APP_ID', '8518311485008611');

        // only gmail data NOT AVAILABLE FOR IP ADDRESS  -: updated  gmail data : 18/March/2013
        // GOOGLE application credential.
        define('GOOGLE_API_KEY', 'ginHDeuCaBFKMAIzaSyAfXX4auDpNCFL_YtvQPU');
        define('CLIENT_ID', '91938060257-apps.607ocm6vt5f2lj48mi2soi9bnbpbpk7i.googleusercontent.com');
        define('CLIENT_SECRET', 'yg04rKz4OH2aUGv8mewmtN5F');

        // LINKEDIN application credential.
        define('LINKEDIN_SCRIPT', '');

        // TWITTER application credential.
        define('TWITTERAPIKEY', '');
        define('TWITTERAPISECRET', '');

        // YAHOO application credential.
        define('YAHOO_CONSUMER_KEY', '--');
        define('YAHOO_CONSUMER_SECRET', '');
        define('YAHOO_APP_ID', '');

        // OUTLOOK application credential.
        define('OUTLOOK_CLIENT_ID', '');

        define('COOKIE_NAME', 'ci_fs_pr');

        //Constants for used in email templates
        define('FROM_EMAIL', 'inclusify@vinfotech.com');
        define('FROM_EMAIL_TITLE', 'Bhopu');

        define('VCA_INFO_EMAIL', 'inclusify@vinfotech.com');
        define('VCA_SUPPORT_EMAIL', 'anupam.khare@vinfotech.com');

        define('EMAIL_NOREPLY_NAME', 'Bhopu');
        define('EMAIL_NOREPLY_FROM', "noreply@vinfotech.com");

        // Google Analytics Constants #############
        define('GA_CLIENT_ID', '74862946523-cubqvj07mjo9enb1f83gtqvi4tg2p9qe.apps.googleusercontent.com');
		define('GA_EMAIL', 'bhopu-382@inclusify-59a1f.iam.gserviceaccount.com'); //Email Address
		define('GA_ACCOUNT_ID', 'ga:120087299');

        //push notification
        define('FCM_KEY',getenv('FCM_KEY'));
        define('SERVER_API_KEY', FCM_KEY);
        define('LEGACY_API_KEY', FCM_KEY);
        
        define('SITE_HOST', 'http://social.vinfotech.org');
        define('DOMAIN', getenv('DOMAIN'));
        define('STOCK_URL', getenv('STOCK_URL'));

        define('NODE_ADDR', 'http://social.vinfotech.org:3301');
              
        //define('ASSET_BASE_URL','https://'.BUCKET.'.s3-us-west-2.amazonaws.com/'.THEAM_PATH);
        define('ASSET_BASE_URL', SITE_HOST . ROOT_FOLDER . '/'.THEAM_PATH);  
        define('GLOBALSETTINGS', 'GlobalSettings');
        break;    
    case 'testing':
    	define('SUBDIR', '/social/');
        define('ROOT_FOLDER', '');
        define('THEAM_PATH', 'assets/');

        define('IPINFODBKEY', 'b2e0bb044541b5d37eda7d925f78e2aa33e8d9969d7b8af8e5c094364d9fe41a');

        // FACEBOOK application credential.
        define('FACEBOOK_APP_ID', '');

        // only gmail data NOT AVAILABLE FOR IP ADDRESS  -: updated  gmail data : 18/March/2013
        // GOOGLE application credential.
        define('GOOGLE_API_KEY', '');
        define('CLIENT_ID', '91938060257-.apps..com');
        define('CLIENT_SECRET', '');

        // LINKEDIN application credential.
        define('LINKEDIN_SCRIPT', '');

        // TWITTER application credential.
        define('TWITTERAPIKEY', '');
        define('TWITTERAPISECRET', '');

        // YAHOO application credential.
        define('YAHOO_CONSUMER_KEY', '--');
        define('YAHOO_CONSUMER_SECRET', '');
        define('YAHOO_APP_ID', '');

        // OUTLOOK application credential.
        define('OUTLOOK_CLIENT_ID', '');

        define('COOKIE_NAME', 'ci_fs_qa');

        //Constants for used in email templates
        define('FROM_EMAIL', 'inclusify@vinfotech.com');
        define('FROM_EMAIL_TITLE', 'Bhopu');

        define('VCA_INFO_EMAIL', 'inclusify@vinfotech.com');
        define('VCA_SUPPORT_EMAIL', 'suresh.patidar@vinfotech.com');

        define('EMAIL_NOREPLY_NAME', 'Bhopu');
        define('EMAIL_NOREPLY_FROM', "noreply@vinfotech.com");

        // Google Analytics Constants #############
        define('GA_CLIENT_ID', '480561942883-si0ci6jdlf7q80ljhfefhuvoluef3jiv.apps.googleusercontent.com');
		define('GA_EMAIL', 'bhopu-analytic@braghouse-221210.iam.gserviceaccount.com'); //Email Address
		define('GA_ACCOUNT_ID', 'ga:176265377');

        //push notification
        define('SERVER_API_KEY', 'AAAASmQbZjc:APA91bH-EGvvl-slunyAHes4wVt0Kt3TTt44yhb3aT85BtkgwjwAx3deow2CTz1oHjmdqQqm5qihBHBwk4rclLSZL5XbKjlqYQKokfVJD-Jz6wKtuo4sp46XiqvdmNefcmvPseoHzqfq');
        define('LEGACY_API_KEY', 'AAAASmQbZjc:APA91bH-EGvvl-slunyAHes4wVt0Kt3TTt44yhb3aT85BtkgwjwAx3deow2CTz1oHjmdqQqm5qihBHBwk4rclLSZL5XbKjlqYQKokfVJD-Jz6wKtuo4sp46XiqvdmNefcmvPseoHzqfq');

        define('SITE_HOST', 'http://social.vinfotech.org');
        define('NODE_ADDR', 'http://social.vinfotech.org:3301');
        define('DOMAIN', getenv('DOMAIN'));
        define('STOCK_URL', getenv('STOCK_URL'));
        
       // define('ASSET_BASE_URL','https://'.BUCKET.'.s3-us-west-2.amazonaws.com/'.THEAM_PATH);
        define('ASSET_BASE_URL', SITE_HOST . ROOT_FOLDER . '/'.THEAM_PATH);  
        define('GLOBALSETTINGS', 'GlobalSettings');
        break;
    default :
    	define('SUBDIR', '/social/');
        define('ROOT_FOLDER', '/framework'); //inclusify
        define('THEAM_PATH', 'assets/');

        define('IPINFODBKEY', 'b2e0bb044541b5d37eda7d925f78e2aa33e8d9969d7b8af8e5c094364d9fe41a');

        // FACEBOOK application credential.
        define('FACEBOOK_APP_ID', '');

        // only gmail data NOT AVAILABLE FOR IP ADDRESS  -: updated  gmail data : 18/March/2013
        // GOOGLE application credential.
        define('GOOGLE_API_KEY', '');
        define('CLIENT_ID', '884950488316-.apps.googleusercontent.com');
        define('CLIENT_SECRET', '');

        // LINKEDIN application credential.
        define('LINKEDIN_SCRIPT', '');

        // TWITTER application credential.
        define('TWITTERAPIKEY', '');
        define('TWITTERAPISECRET', '');

        // YAHOO application credential.
        define('YAHOO_CONSUMER_KEY', '--');
        define('YAHOO_CONSUMER_SECRET', '');
        define('YAHOO_APP_ID', '');

        // OUTLOOK application credential.
        define('OUTLOOK_CLIENT_ID', '');

        define('COOKIE_NAME', 'ci_fs_dev');

        //Constants for used in email templates
        define('FROM_EMAIL', 'admin@vinfotech.com');
        define('FROM_EMAIL_TITLE', 'Administrator');

        define('VCA_INFO_EMAIL', 'vcainfo@vinfotech.com');
        define('VCA_SUPPORT_EMAIL', 'suresh.patidar@vinfotech.com');

        define('EMAIL_NOREPLY_NAME', 'Bhopu');
        define('EMAIL_NOREPLY_FROM', "noreply@vinfotech.com");

        // Google Analytics Constants #############
        define('GA_CLIENT_ID', '480561942883-si0ci6jdlf7q80ljhfefhuvoluef3jiv.apps.googleusercontent.com');
		define('GA_EMAIL', 'bhopu-analytic@braghouse-221210.iam.gserviceaccount.com'); //Email Address
		define('GA_ACCOUNT_ID', 'ga:176265377');
        
        //push notification
        //define('SERVER_API_KEY', 'AAAAINAvAa8:APA91bF04M6leXxu9POPsF54_FBwh_lY-sFlCRPDjMk6b3RxVLSZsOg18OTet-zjCAqrC0YuM1Ky7cvMyREB8F0vcqRULvm9IlpqbXioHTbnJGD1v0hH3x0uT92R6mbtB7a00D74YrqU');
        //define('LEGACY_API_KEY', 'AIzaSyDMpSZ9_nBB-HpMe2a8MdaT0IUP2QI-R7s');
         define('SERVER_API_KEY', 'AAAAEW4t6Ns:APA91bHSRkTj8R8l9Y4xWThBPr9DVBPjuf0uKx5ETqreJtNaSzI3irUDG8N9mf8wBOtd1Y8QsD40VyE3MbQ3Dv-LeqLoNsrMaqc7BVg8rBCgajntEjHyDkEtuggZo3zD6LYmeh2ol3P0');
        define('LEGACY_API_KEY', 'AIzaSyDoVUSVavhEsAtmmoMb5I06x_dhiK0XiQE');

        define('SITE_HOST', 'http://localhost');
        define('NODE_ADDR', 'http://localhost:3301');
        define('DOMAIN', getenv('DOMAIN'));
        define('STOCK_URL', getenv('STOCK_URL'));
        
        
        define('ASSET_BASE_URL', SITE_HOST . ROOT_FOLDER . '/'.THEAM_PATH);        
        define('GLOBALSETTINGS', 'GlobalSettings');
        break;
}

define('ROOT_PATH', DOCUMENT_ROOT . ROOT_FOLDER);
define('DOC_PATH', DOCUMENT_ROOT);

define('ADMIN_BASE_URL', SITE_HOST . ROOT_FOLDER . '/admin/');

define('GOOGLE_REDIRECT_URI', SITE_HOST . ROOT_FOLDER . '/build_network/get_gmail_friends');
define('SCOPE', 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.login');

define('TWITTER_OAUTH_CALLBACK', SITE_HOST . ROOT_FOLDER . '/api/twitter/twitter_user_info');

define('GA_PRIVATE_KEY_LOCATION', DOC_PATH . SUBDIR . 'inclusify-59a1f-3dd0015e3d48.p12'); //working rtc account 


define('IMAGE_SERVER', 'remote'); //remote
define('JOBSERVER', getenv('JOBSERVER')); // Gearman or Rabbitmq

//define('ASSET_BASE_URL', SITE_HOST . ROOT_FOLDER . '/' . THEAM_PATH);
//define('ASSET_BASE_URL','https://'.BUCKET.'.s3-us-west-2.amazonaws.com/'.THEAM_PATH);
if (IMAGE_SERVER == 'remote')
{
    if (ENVIRONMENT == 'production') {
        //define('IMAGE_SERVER_PATH', 'https://' . BUCKET . '.s3-'.BUCKET_ZONE.'.amazonaws.com/');
        define('IMAGE_SERVER_PATH', getenv('BUCKET_URL'));
    } else {
        //define('IMAGE_SERVER_PATH', 'http://' . BUCKET . '.s3-'.BUCKET_ZONE.'.amazonaws.com/');
        define('IMAGE_SERVER_PATH', getenv('BUCKET_URL'));
    }    
}
else
{
    define('IMAGE_SERVER_PATH', SITE_HOST . ROOT_FOLDER . '/');
}

define('IMAGE_ROOT_PATH', ROOT_PATH . '/' . PATH_IMG_UPLOAD_FOLDER);
define('IMAGE_HTTP_PATH', SITE_HOST . ROOT_FOLDER . '/' . PATH_IMG_UPLOAD_FOLDER);
define('PASSWORD_RESETTOKEN_EXPIRE_SECONDS', 2 * 60 * 60);

define('CACHE_ENABLE',getenv('CACHE_ENABLE')); // CACHE ENABLE OR NOT
define('CACHE_EXPIRATION',172800); // Use 0 for never expire
define('CACHE_ADAPTER','redis'); // Cache driver name redis, memcached
define('CACHE_PASSWORD',getenv('CACHE_PASSWORD'));
define('GROUP_POPULARITY_ACTIVITY',5);
define('PAGE_POPULARITY_ACTIVITY',5);
define('MAX_FEATURE_POST',5);


define('FEATURE_CATEGORY',3);

// Rules config settings
define('NoOfFrndConfID',5);
define('NoOfPostConfID',10);

// Mongo config settings
define('DBDEBUG',1);
define('MASTER_DOMAIN','localhost');

define('PRIVACY_CHANGE_TIMELIMIT',15);
define('RADIUS_SEARCH_AREA',50);
define('MINIMUM_SELECTION',4);

define('COMMUNITY_ENABLED',0);

define('MODULE_SETTINGS_KEY','fc0788f381d7d64c1441f503a8a6357d');

define('ANDROID_VERSION',"9.6"); //9.6;
define('IOS_VERSION',"4.6"); //4.6
define('UP_REQUIRED',1); 
define('UP_OPTIONAL',0);
define('UPGRADE_REQUIRED',3); //1 - SHOW POPUP ONLY ONCE, 2 - SHOW OPTIONAL POPUP WHEN USER LAUNCH APP, 3 - SHOW POPUP EVERY TIME FOR FORCILY UPDATE


define('BUCKET_STATIC_DATA_ALLOWED', '1');
define('BUCKET_STATIC_DATA_PATH', 'static/');
define('BUCKET_DATA_PREFIX', 'b_');
define('BITLY_ACCESS_TOKEN',getenv('BITLY_ACCESS_TOKEN'));
define('SHARE_URL', getenv('SHARE_URL'));