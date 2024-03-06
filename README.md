fantasysports
-----------------------------------------------
Project setup
Change folder name to : framework

-----------------------------------------------
run "composer install" command in below location
framework/

-----------------------------------------------
Setup environment setting
Step 1 : update composer
Step 2 : copy .env.example to .env at root
Step 3 : edit .env file and update all credential(db details, smtp details, s3 details rebbit mq details)

-----------------------------------------------

Create "logs" folder in below location with read write permission
framework/adminapi/application
framework/cron/application
framework/fantasy/application
framework/user/application

------------------------------------------------

Create "date_time.php" file in framework folder with content 
<?php $date_time="";
-----------------------------------------------
-----------------------------------------------
create upload folder and give write permission
create upload folder in admin and  give write permission
-----------------------------------------------


Configuration for new project:BACKEND
-----------------------------------------------
change files for all_config folder files with your project details
-----------------------------------------------



##Damaon process set up to process Queue
1) INSTALL : sudo apt-get install supervisor
2) GOTO DIRECTORY : cd /etc/supervisor/conf.d/
3) CREATE CONF FILE : touch script.conf
4) EDIT FILE : nano script.conf
5) ADD BELOW CODE
--------- Supervisor setting ------
[program:bucketscript]
command=php /var/www/html/cron/index.php worker process_bucket --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/bucketscript.err.log
stdout_logfile=/var/log/bucketscript.out.log

[program:smsscript]
command=php /var/www/html/cron/index.php worker process_sms --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/smsscript.err.log
stdout_logfile=/var/log/smsscript.out.log

[program:cronscript]
command=php /var/www/html/cron/index.php worker process_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/cronscript.err.log
stdout_logfile=/var/log/cronscript.out.log

[program:emailscript]
command=php /var/www/html/cron/index.php worker process_email --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/emailscript.err.log
stdout_logfile=/var/log/emailscript.out.log

[program:emailotpscript]
command=php /var/www/html/cron/index.php worker process_email_otp --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/emailotpscript.err.log
stdout_logfile=/var/log/emailotpscript.out.log

[program:pushscript]
command=php /var/www/html/cron/index.php worker push_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/pushscript.err.log
stdout_logfile=/var/log/pushscript.out.log

[program:inviteemailscript]
command=php /var/www/html/cron/index.php worker process_invite_email --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/inviteemailscript.err.log
stdout_logfile=/var/log/inviteemailscript.out.log

[program:contestscript]
command=php /var/www/html/cron/index.php worker process_contest --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/contestscript.err.log
stdout_logfile=/var/log/contestscript.out.log

[program:contestpdfscript]
command=php /var/www/html/cron/index.php worker process_contestpdf --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/contestpdfscript.err.log
stdout_logfile=/var/log/contestpdfscript.out.log

[program:gamecancelscript]
command=php /var/www/html/cron/index.php worker process_game_cancellation --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/gamecancelscript.err.log
stdout_logfile=/var/log/gamecancelscript.out.log

[program:reportscript]
command=php /var/www/html/cron/index.php worker report_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/reportscript.err.log
stdout_logfile=/var/log/reportscript.out.log

[program:minileague]
command=php /var/www/html/cron/index.php worker process_mini_league --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/minileaguescript.err.log
stdout_logfile=/var/log/minileaguescript.out.log

[program:plteamsscript]
command=php /var/www/html/cron/index.php worker process_pl_teams --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/plteamsscript.err.log
stdout_logfile=/var/log/plteamsscript.out.log

[program:recentleaguescript]
command=php /var/www/html/cron/index.php worker process_recent_league --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/recentleaguescript.err.log
stdout_logfile=/var/log/recentleaguescript.out.log

[program:teamscript]
command=php /var/www/html/cron/index.php worker process_team_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/teamscript.err.log
stdout_logfile=/var/log/teamscript.out.log

[program:seasonscript]
command=php /var/www/html/cron/index.php worker process_season_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/seasonscript.err.log
stdout_logfile=/var/log/seasonscript.out.log

[program:playerscript]
command=php /var/www/html/cron/index.php worker process_player_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/playerscript.err.log
stdout_logfile=/var/log/playerscript.out.log

[program:cricketscorescript]
command=php /var/www/html/cron/index.php worker process_score_cricket --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/cricketscorescript.err.log
stdout_logfile=/var/log/cricketscorescript.out.log

[program:soccerscorescript]
command=php /var/www/html/cron/index.php worker process_score_soccer --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/soccerscorescript.err.log
stdout_logfile=/var/log/soccerscorescript.out.log

[program:scorescript]
command=php /var/www/html/cron/index.php worker process_score_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/scorescript.err.log
stdout_logfile=/var/log/scorescript.out.log

[program:scpointsscript]
command=php /var/www/html/cron/index.php worker process_sc_points_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/scpointsscript.err.log
stdout_logfile=/var/log/scpointsscript.out.log

[program:contestclosescript]
command=php /var/www/html/cron/index.php worker process_contest_close --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/contestclosescript.err.log
stdout_logfile=/var/log/contestclosescript.out.log

[program:prizecronscript]
command=php /var/www/html/cron/index.php worker process_prize_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/prizecronscript.err.log
stdout_logfile=/var/log/prizecronscript.out.log

[program:prizenotifyscript]
command=php /var/www/html/cron/index.php worker process_prize_notify --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/prizenotifyscript.err.log
stdout_logfile=/var/log/prizenotifyscript.out.log


[program:cdbuildemailscript]
command=php /var/www/html/cron/index.php cd_worker bulk_email_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/cdbuildemailscript.err.log
stdout_logfile=/var/log/cdbuildemailscript.out.log

[program:cdemailqueueprocessscript]
command=php /var/www/html/cron/index.php cd_worker email_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/cdemailqueueprocessscript.err.log
stdout_logfile=/var/log/cdemailqueueprocessscript.out.log

[program:cdpushscript]
command=php /var/www/html/cron/index.php cd_worker push_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/cdpushscript.err.log
stdout_logfile=/var/log/cdpushscript.out.log

[program:cdsmsscript]
command=php /var/www/html/cron/index.php cd_worker sms_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/cdsmsscript.err.log
stdout_logfile=/var/log/cdsmsscript.out.log


[program:coinsmsscript]
command=php /var/www/html/cron/index.php worker coin_sms_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/coinsmsscript.err.log
stdout_logfile=/var/log/coinsmsscript.out.log

[program:coinclaimscript]
command=php /var/www/html/cron/index.php coin_worker claim_coins --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/coinclaimscript.err.log
stdout_logfile=/var/log/coinclaimscript.out.log

[program:processpredictionscript]
command=php /var/www/html/cron/index.php prediction_worker process_prediction --env development
autostart=true
stderr_logfile=/var/log/pushscript.err.log
stdout_logfile=/var/log/pushscript.out.log

[program:processpredictionrefundscript]
command=php /var/www/html/cron/index.php prediction_worker process_prediction_refund --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/pushscript.err.log
stdout_logfile=/var/log/pushscript.out.log

[program:processopenpredictionscript]
command=php /var/www/html/cron/index.php open_predictor_worker process_prediction --env development
autostart=true
stderr_logfile=/var/log/processopenpredictionscript.err.log
stdout_logfile=/var/log/processopenpredictionscript.out.log

[program:processopenpredictionrefundscript]
command=php /var/www/html/cron/index.php open_predictor_worker process_prediction_refund --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/processopenpredictionrefundscript.err.log
stdout_logfile=/var/log/processopenpredictionrefundscript.out.log

[program:baseballscorescript]
command=php /var/www/html/cron/index.php worker process_score_baseball --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/baseballscorescript.err.log
stdout_logfile=/var/log/baseballscorescript.out.log

[program:gamecancelscript]
command=php /var/www/html/framework/cron/index.php worker process_game_cancellation --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/gamecancelscript.err.log
stdout_logfile=/var/log/gamecancelscript.out.log

[program:pushscript]
command=php /var/www/html/framework/cron/index.php worker report_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/pushscript.err.log
stdout_logfile=/var/log/pushscript.out.log

[program:minileague]
command=php /var/www/html/cron/index.php worker process_mini_league --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/minileaguescript.err.log
stdout_logfile=/var/log/minileaguescript.out.log

[program:coinclaimscript]
command=php /var/www/html/framework/cron/index.php coin_worker claim_coins --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/coinclaimscript.err.log
stdout_logfile=/var/log/coinclaimscript.out.log

[program:plteamsscript]
command=php /var/www/html/framework/cron/index.php worker process_pl_teams --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/plteamsscript.err.log
stdout_logfile=/var/log/plteamsscript.out.log

[program:lineupoutpushgamescript]
command=php /var/www/html/cron/index.php worker process_lineupout_game --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/lineupoutpushgamescript.err.log
stdout_logfile=/var/log/lineupoutpushgamescript.out.log

[program:lineupoutpushscript]
command=php /var/www/html/cron/index.php worker process_lineupout_push --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/lineupoutpushscript.err.log
stdout_logfile=/var/log/lineupoutpushscript.out.log

[program:processhostrakescript]
command=php /var/www/html/cron/index.php worker process_host_rake --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/processhostrakescript.err.log
stdout_logfile=/var/log/processhostrakescript.out.log

[program:lineupmovecronscript]
command=php /var/www/html/cron/index.php worker process_lineup_move_cron --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/lineupmovecronscript.err.log
stdout_logfile=/var/log/lineupmovecronscript.out.log

[program:lineupmovecronscript]
command=php /var/www/html/cron/index.php worker process_lineup_move_cron --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/lineupmovecronscript.err.log
stdout_logfile=/var/log/lineupmovecronscript.out.log

[program:lineupmovecronscript]
command=php /var/www/html/cron/index.php worker process_paytm_payout --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/processpaytmpayoutscript.err.log
stdout_logfile=/var/log/processpaytmpayoutscript.out.log

[program:taxinvoicescript]
command=php /var/www/html/cron/index.php worker process_tax_invoice --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/taxinvoicescript.err.log
stdout_logfile=/var/log/taxinvoicescript.out.log

[program:cdnormalpushqueueprocessscript]
command=php /var/www/html/cron/index.php cd_worker normal_push_queueu_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/cdnormalpushqueueprocessscript .err.log
stdout_logfile=/var/log/cdnormalpushqueueprocessscript .out.log

[program:cdscheduledpushqueueprocessscript]
command=php /var/www/html/cron/index.php cd_worker scheduled_push_queue_process --env development
autostart=true
autorestart=true
stderr_logfile=/var/log/ cdscheduledpushqueueprocessscript .err.log
stdout_logfile=/var/log/ cdscheduledpushqueueprocessscript .out.log
#supervisor
[program:gstscript]
command=php /var/www/html/cron/index.php worker process_gst --env production
autostart=true
autorestart=true
stderr_logfile=/var/log/gstscript.err.log
stdout_logfile=/var/log/gstscript.out.log

[program:autopushscript]
command=php /var/www/html/cron/index.php worker auto_push_process --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/autopushscript.err.log
stdout_logfile=/var/log/autopushscript.out.log

[program:dfsautopushscript]
command=php /var/www/html/cron/index.php worker dfs_auto_push_queue_process --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/dfsautopushscript.err.log
stdout_logfile=/var/log/dfsautopushscript.out.log


[program:nodescript]
command=php /var/www/html/cron/index.php worker process_notify_node --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/nodescript.err.log
stdout_logfile=/var/log/nodescript.out.log

[program:coinexpiryscript]
command=php /var/www/html/cron/index.php worker process_coinexpiry --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/coinexpiryscript.err.log
stdout_logfile=/var/log/coinexpiryscript.out.log

[program:paoutscript]
command=php /var/www/html/cron/index.php worker process_payout --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/processpaytmpayoutscript.err.log
stdout_logfile=/var/log/processpaytmpayoutscript.out.log

[program:processreportcronscript]
command=php /var/www/html/optimisation/cron/index.php worker process_report_cron --env testing
autostart=true
autorestart=true
stderr_logfile=/var/log/processreportcronscript.err.log
stdout_logfile=/var/log/processreportcronscript.out.log

---------------

service supervisor start

Once our configuration file is created and saved, we can inform Supervisor of our new program through the supervisorctl command. First we tell Supervisor to look for any new or changed program configurations in the /etc/supervisor/conf.d directory with:

supervisorctl reread
Followed by telling it to enact any changes with:

supervisorctl update

#### add staging IP or host value to demaon_hosts.php

CRON_DEVELOPMENT_HOST
CRON_TESTING_HOST
CRON_PRODUCTION_HOST
-----------------------------------------------

Configuration for new project:FRONTEND
-----------------------------------------------
-----------------------------------------------
In public folder run below command
npm install
--------------NODE JS SETUP FOR LIVE SCORING START----------------------
steps

1.pick node folder form framework to project other then node_modules folder and code from cron controller and model (update_node_client)

include socket.io.js on index.html
pick socker service from reactframework services.js
make front end change on leagecontroller.js for recive score and join group.

2. install nvm and node v7.4.0 https://gist.github.com/d2s/372b5943bce17b964a79
nvm install v7.4.0	

3.install pm2 to set run script in background run command
npm install pm2 -g

4.set envoirment run below command
export NODE_ENV=testing

5. set process run below command 
pm2 start app.js

allow port
--------------NODE JS SETUP FOR LIVE SCORING END----------------------

========================Onboarding Affiliated Branch Updates=======================
20 - Affiliate System Writeboard

Fantasy affiliate programs generally come in two flavors: CPA and revenue share. In a CPA (cost per action) model, you are paid a flat fee every time you send a real money player. In a revenue share model, you get paid a percentage of whatever that person earns for the fantasy site.

Sharing Affiliate System

User End:

- User can earn money in two forms
1. Bonus
2. Real Cash

Channels of earning Bonus Cash

- Inviting friends
- Sharing Collection
- Sharing Contest
- Promoting referrals to complete their profiles

Inviting a Friend
User can invite friends in four ways
- Sharing on Social Platforms (Facebook, Whatsapp or enter email to invite)
- Copy and share the direct link to signup. Need to track the signup referral if coming through unique link of user
- Copy and Share the unique code
- Sharing Banners

Sharing Collection
User can share a collection in four ways
- Sharing on Social Platforms (Facebook, Whatsapp or enter email to invite)
- Copy and share the direct link to signup. Need to track the referral if coming through unique link of user
- Sharing Banners

Sharing Contest
User can share the contest in four ways
- Sharing on Social Platforms (Facebook, Whatsapp or enter email to invite)
- Copy and share the direct link. Need to track the referral if coming through unique link of user
- Copy and Share the unique code
- Sharing Banners

Banner Management:
- System will create a default banner
- Contents of the banner will change depending on the options presented

Collection Banner
Divided into two sections

- Pre Sharing Banner (system will display a banner based on the collection user has selected)
- This is pre defined banner.
- It will display a Text, Real Money Value ($20), Collection Name, Timing, Entry Fee, Share Now Button. This value will appear dynamically

- Post Sharing Banner (display the banner when someone shared the Collection)
- This is a pre defined banner
- It will display a text, Winning Amount,Collection Name, Timing, Play Now

Contest Banner
- Post sharing banner (display the banner when someone shared the contest)
- This is a hard coded pre defined banner
- It will display contest name, entries, entry fee, prize, timing and play now

============================End Onboarding Affiliated==============================

##virtual host setup 
Step 1.
  locate 000-default.conf

Step 2.
cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/framework.conf

Step 3. edit conf file
nano /etc/apache2/sites-available/framework.conf

add below code

<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        ServerName local.framework.com
        DocumentRoot /var/www/html/framework
        ErrorLog ${APACHE_LOG_DIR}/error.framework.log
        CustomLog ${APACHE_LOG_DIR}/access.framework.log combined
</VirtualHost>

OR

<VirtualHost *:80>
ServerAdmin webmaster@localhost
ServerName local.framework.com
DocumentRoot /var/www/html/framework
<Directory "/var/www/html/framework">
Options Indexes FollowSymLinks MultiViews
AllowOverride All
Require local
</Directory>
ErrorLog ${APACHE_LOG_DIR}/error.framework.log
CustomLog ${APACHE_LOG_DIR}/access.framework.log combined
</VirtualHost>


Step 4.exec below commands

sudo a2ensite framework.conf 
sudo service apache2 restart

Step 5. edit host file

nano /etc/hosts

add below line 

YourIP local.framework.com

sudo a2enmod headers

enable mod rewrite
sudo a2enmod rewrite

sudo a2enmod headers
systemctl restart apache2


##windows virtual host setting

Step 1. Add your virtual host(local.framework.com) to c:/program files/system32/drivers/etc/hosts file

# Copyright (c) 1993-2009 Microsoft Corp.
#
# This is a sample HOSTS file used by Microsoft TCP/IP for Windows.
#
# This file contains the mappings of IP addresses to host names. Each
# entry should be kept on an individual line. The IP address should
# be placed in the first column followed by the corresponding host name.
# The IP address and the host name should be separated by at least one
# space.
#
# Additionally, comments (such as these) may be inserted on individual
# lines or following the machine name denoted by a '#' symbol.
#
# For example:
#
#      102.54.94.97     rhino.acme.com          # source server
#       38.25.63.10     x.acme.com              # x client host

# localhost name resolution is handled within DNS itself.
127.0.0.1       localhost
    ::1             localhost
127.0.0.1  local.framework.com

Step 2. add your host setting to xampp/apache/conf/httpd-vhost.conf file


<VirtualHost *:80>
    ServerAdmin localhost
    DocumentRoot "F:/Xampp/htdocs"
    ServerName localhost
    ErrorLog "logs/localhost.log"
    CustomLog "logs/localhost.log" common
</VirtualHost>

<VirtualHost *:80>
    ServerAdmin webmaster@local.framework.com
    DocumentRoot "F:/Xampp/htdocs/framework"
    ServerName local.framework.com
    ErrorLog "logs/local.framework.com.log"
    CustomLog "logs/local.framework.com.log" common
</VirtualHost>

Step 3. restart apache and access local.framework.com in your browser

----------Install mongodb on ubuntu Start-----------------
wget -qO - https://www.mongodb.org/static/pgp/server-5.0.asc | sudo apt-key add -
sudo apt-get install gnupg
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu bionic/mongodb-org/5.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-5.0.list
sudo apt-get update
sudo apt-get install -y mongodb-org
sudo service mongod start

----------Install mongodb on ubuntu End-----------------

------------php mongo extension Start Ubuntu------------
sudo apt-get update && sudo apt-get install php7.2-simplexml
sudo apt-get update && sudo apt-get install php7.2-bcmath
sudo apt-get update && sudo apt-get install php7.2-mbstring
pacman -S php-pear
sudo apt install pacman
sudo apt install php-dev
sudo pecl install mongodb
sudo apt install php7.2-curl
sudo composer update

sudo /etc/php/7.2/apache2/php.ini
add 
extension=mongodb.so

systemctl restart apache2

--------------php mongo extension End Ubuntu----------

============================New Module adding Front end side steps==============================
1. Create new module folder in public/app/app/module_new
2. Create new module.js, controller.js and index.html i.e. (module_new.module.js, module_new.controller.js)
3. Created module in module.js
    - angular.module('app.module_new', []);
4. Add module in app.js and grunt.js file
5. Add json file for translation module_name.json i.e. (public/app/assets/translation/en/module_name.json)
6. Add app.module.js module_new
============================/New Module adding Front end side steps==============================


[program:frameworkscriptpush]
command=php /var/www/html/framework/cron/application/controllers/Mnotificationque.php testing
autostart=true
autorestart=true
stderr_logfile=/var/log/pushscript.err.log
stdout_logfile=/var/log/pushscript.out.log


fb account
vitemple6@gmail.com
123456??

##################TEST LIVE SUB STRT#####################
Step 1: Check the contest match live time with system time, and set system time to one of the match from contest.
get time from contest detail.

Step 2: Get season game uid for Live match and check season table in game DB,for live set status => 1,status_overview => 0
    

##################TEST LIVE SUB END #####################

-----------------------GOOGLE LOGIN SOLUTION START-----------------------------------
https://www.digitalocean.com/community/tutorials/how-to-set-up-timezone-and-ntp-synchronization-on-ubuntu-14-04-quickstart


sudo timedatectl set-timezone America/New_York
timedatectl
sudo apt-get install ntp
sudo service ntp start
sudo timedatectl set-ntp on
sudo ntpq -p


https://www.digitalocean.com/community/tutorials/how-to-set-up-time-synchronization-on-ubuntu-16-04
-----------------------GOOGLE LOGIN SOLUTION END -----------------------------------


##################COMPLETE GAME START#####################
DB :game
table: vi_season

check column "status" and "status_overview"
on match complete status value should be 2 and status_overview should be 4 if not updated by cron than update manually. 

Get contest last match time,and set time according to below condition
For soccer,nfl:
    if last match time X then set current time between X + 8 and X + 11
    8 and 11 is hours

For cricket
    T20  : current time between X + 8 and X + 11
    ODI:  current time < X + 15 
    TEST : current time < X + 144 

    and run update_contest_status cron
    

##################COMPLETE GAME END #####################

SHOW FULL PROCESSLIST

#######On add new sport ######
Add sport condition to app.module.js stateChangeSuccess method

Enable header for .htaccess
a2enmod headers

################## SETUP FIREBASE ACCOUNT #####################

1. Create an account for Firebase and add a project with your project name.
2. Authentication->sign-in method->enable email/password and add domain in Authorized domains.
3. Database->create database->enable start in test mode.
4. settings->project settings->Add Firebase to your web app, copy the credentials to front_end.js.
5. Create seperate project for all the environments.

##################DASHBOARD END #####################
1. Create database "490_vfantasy_analytics"
2. Create table  "vi_analytics" alter query will be in alter_sql file 
3. Add Cron   http://{baseurl}/cron/analytics once in a day (24 hours)    (if you want to insert data of specific time interval you can  cron on browser http://{baseurl}/cron/analytics?StartDate=2019-06-24&EndDate=2019-06-25 )
4. create json file for google analytics and update path in constant  GA_PRIVATE_KEY_LOCATION and put it on root  from https://console.developers.google.com ->create credentails ->service account key ->select json
##################DASHBOARD END #####################

######### Global Assets(Team logo/jersey) and Fixture wise player and salary ###################

2. Please check and run database changes and queries from alter_sql.php file (find heading : Global Assets)
3. Cross check super admin(vinfotech) feed url in all_config/sports_config file
4. Setup cron for fetch leagues,teams,seasons,players,scores from super admin(see crontab file with heading : Cricket - From Super Admin)
5. Need to clear game db(leagues,teams,seasons,players) before enable this module.
6. Need to create following directory(if not exists) upload/flag,upload/jersey on root with 777 permission

######### END - GLOBAL ASSETS MODULE INFO ###################
