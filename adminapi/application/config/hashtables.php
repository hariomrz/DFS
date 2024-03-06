<?php

//update balance 
$config['cd_balance_update']=array();
$config['cd_balance_update'][3]='email_bal_update';
// $config['cd_balance_update'][4]='sms_bal_update';
$config['cd_balance_update'][5]='notification_bal_update';

//get communication templates
$config['cd_balance_template']=array();
$config['cd_balance_template'][3]=array('template_name' => 'cd-email-buy-notify',
										'update_balance' => 'update_email_balance',
										'rate' => CD_ONE_EMAIL_RATE) ;
// $config['cd_balance_template'][4]=array('template_name' => 'cd-sms-buy-notify',
// 										'update_balance' => 'update_sms_balance',
// 										'rate' => CD_ONE_SMS_RATE) ;
$config['cd_balance_template'][5]=array('template_name' => 'cd-notification-buy-notify',
										'update_balance' => 'update_notification_balance',
										'rate' => CD_ONE_NOTIFICATION_RATE);


?>