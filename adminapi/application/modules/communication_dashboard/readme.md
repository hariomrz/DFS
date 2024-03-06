#------------------------Communication module-------------------------------------------
#Paste controllers and models folder to admin ci and follow the below steps to configure module,use module_confuguration folder file to configue rest of things.


#Step 1.import mysql table from db_changes to user db


***********************************************************************************************
#Step 2. paste below constants to contants.php

	define('RECENT_COMMUNICATION'	,'cd_recent_communication');
	define('CD_BALANCE'		,'cd_balance');
	define('CD_BALANCE_HISTORY'	,'cd_balance_history');
	define('CD_EMAIL_TEMPLATE'	,'cd_email_template');


***********************************************************************************************
#Step 3.

copy helper communication_dashboard_helper.php and paste it to your admin helper folder
	

***********************************************************************************************	
#Step 4.paste below code to all_config/common_constants.php

		//communication module constants
		define('CD_BALANCE_AUTH_KEY','HJ@#@HJ#*(');
		//queue names
		define('CD_BULK_EMAIL_QUEUE','cd_bulk_email');
		define('CD_EMAIL_QUEUE','cd_email');
		define('CD_SMS_QUEUE','cd_sms');


	and create above queues from rabbit mq panel	

	and add below config file to supervisor

File Name =>   cd_bulk_email.conf
	
File Content =>

[program:cdbulkemailscript]
command=php /var/www/html/cron/application/controllers/CdBulkEmailProcess.php testing
autostart=true
autorestart=true
stderr_logfile=/var/log/cdscript.err.log
stdout_logfile=/var/log/cdscript.out.log

File Name =>   cd_email.conf
	
File Content =>

[program:cdemailscript]
command=php /var/www/html/cron/application/controllers/CdEmailQueueProcess.php testing
autostart=true
autorestart=true
stderr_logfile=/var/log/cdscript.err.log
stdout_logfile=/var/log/cdscript.out.log

File Name =>   cd_sms.conf

File Content =>

***********************************************************************************************
#Step 5. Paste Communication_dashboard_model to cron models folder
***********************************************************************************************
#Step 6. Paste below files to cron/application/controllers folder 

#       FROM                                                               		=> TO
		module_configuration/cron_code/controllers/CdBulkQueueProcess.php  		=>  cron/application/controllers/CdBulkQueueProcess.php
		module_configuration/cron_code/controllers/CdEmailQueueProcess.php 		=> cron/application/controllers/CdEmailQueueProcess.php
		
		module_configuration/cron_code/models/Communication_dashboard_model.php => cron/application/models/Communication_dashboard_model.php

		module_configuration/cron_code/models/Cron_model.php 					=> cron/application/models/Cron_model.php
		
		module_configuration/cron_code/helpers/cd_mail_helper.php 					=> cron/application/helpers/cd_mail_helper.php




#Step 7.

#Frontend Configuration
Copy reactadmin/src/views/Marketing and paste to your react folder for same location 		

#Step 8.
add below content to .env file on root

#communication module paramters
CD_SMTP_HOST=
CD_SMTP_USER=
CD_SMTP_PASS=
CD_SMTP_PORT=25
CD_PROTOCOL=smtp
CD_FROM_ADMIN_EMAIL=
CD_FROM_EMAIL_NAME=

CD_TWO_FACTOR_SMS_API_KEY=
CD_TWO_FACTOR_SMS_API_ENDPOINT=https://2factor.in/API/V1/
CD_TWO_FACTOR_TEMPLATE=
CD_TWO_FACTOR_SENDER_ID=
CD_TWO_FACTOR_BY_CURL=0

#bulksmspremium details
CD_BSP_AUTH_KEY=
CD_BSP_SENDER_ID=
CD_BSP_ROUTE_ID=1
CD_BSP_API_BASE_URL=http://websms.bulksmspremium.com/

CD_MSG91_AUTH_KEY=
CD_MSG91_SENDER_ID=
CD_MSG91_ROUTE_ID=4
CD_MSG91_API_BASE_URL=http://api.msg91.com/

#Step 10. add below function to sports/application/modules/season/Season.php






