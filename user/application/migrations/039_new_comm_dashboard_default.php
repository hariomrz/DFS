<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_comm_dashboard_default extends CI_Migration{

	public function up(){
			
		$sql = "ALTER TABLE ".$this->db->dbprefix(CD_USER_BASED_LIST)." Change sport_id sport_id JSON";
		$this->db->query($sql);

		$sql = "INSERT into " .$this->db->dbprefix(CD_EMAIL_TEMPLATE)." (category_id,template_name,subject,notification_type,status,type,email_body,message_body,message_url,redirect_to,message_type,display_label,date_added) values (13,'admin-redeem-coins-sms','Redeem_coin',302,1,0,null,'Your coin balance is 580 as on {{Date}} click here URL to claim rewards like bonus cash, gift coupons and more','','',1,'Redeem Coin Message','".date('Y-m-d H:i:s')."')";
		$this->db->query($sql);

		$sql = "INSERT into " .$this->db->dbprefix(CD_EMAIL_TEMPLATE)." (category_id,template_name,subject,notification_type,status,type,email_body,message_body,message_url,redirect_to,message_type,display_label,date_added) values (13,'admin-redeem-coins-notification','Redeem_coin',302,1,0,null,'Your coin balancs is 580 claim your rewards like bonus cash, gift coupons and more','',2,2,'Redeem Coin Notification','".date('Y-m-d H:i:s')."')";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=1,
		template_name='promotion-for-deposit',
		subject='Here\'s a little extra for you',
		notification_type=120,
		status=1,
		type=1,
		message_body='Mega offer on {{SITE_TITLE}}! USE Code {{promo_code}}, and earn {{offer_percentage}}% cashback on your next deposit. Play Now.',
		message_url='{{FRONTEND_BITLY_URL}}',
		redirect_to=7,
		message_type=0,
		display_label='Promotion for Deposit',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 1";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=1,
		template_name='promotion-for-deposit-second',
		subject='Deposit and get more',
		notification_type=120,
		status=1,
		type=0,
		message_body='Get {{offer_percentage}}% extra on your Deposit. Use code {{promo_code}} ! Exclusively for you!',
		message_url='{{FRONTEND_BITLY_URL}}',
		redirect_to=7,
		message_type=0,
		display_label='Promotion for Deposit',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 2";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=3,
		template_name='admin-refer-a-friend',
		subject='Sharing is Caring, especially when it pays',
		notification_type=123,
		status=1,
		type=1,
		message_body='Team work makes the Dream work!! Invite your friends to {{SITE_TITLE}} now and earn Rs.{{amount}} in bonus money. ',
		message_url='{{FRONTEND_BITLY_URL}}',
		redirect_to=5,
		message_type=0,
		display_label='Refer a friend',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 3";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=4,
		template_name='promotion-for-fixture',
		subject='Don\'t drop this catch, today is a big match',
		notification_type=300,
		status=1,
		type=2,
		message_body='Don\'t drop this catch, Today\'s a big match. Play {{home}} vs {{away}} now and win big. ',
		message_url='{{FRONTEND_BITLY_URL}}',
		redirect_to=6,
		message_type=0,
		display_label='Promocode for Fixture',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 4";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=11,
		template_name='cd-email-buy-notify',
		subject='Email Buy Notification',
		notification_type=127,
		status=1,
		type=3,
		message_body=null,
		message_url='',
		redirect_to='',
		message_type=0,
		display_label='Email buy Notification',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 5";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=5,
		template_name='cd-sms-buy-notify',
		subject='SMS Buy Notification',
		notification_type=128,
		status=1,
		type=4,
		message_body=null,
		message_url='',
		redirect_to='',
		message_type=0,
		display_label='SMS buy Notification',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 6";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=6,
		template_name='cd-notification-buy-notify',
		subject='Notification Buy Notification',
		notification_type=129,
		status=1,
		type=5,
		message_body=null,
		message_url='',
		redirect_to='',
		message_type=0,
		display_label='Notification buy Notification',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 7";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=7,
		template_name='fixture-delay-info',
		subject='It is raining right now, the match is delayed by a bit.',
		notification_type=131,
		status=1,
		type=2,
		message_body='It is started to rain, and the {{collection_name}} match on {{season_scheduled_date}}(UTC) has been delayed by {{MINUTES}} mins. You can edit your teams till the match starts. ',
		message_url='{{FRONTEND_BITLY_URL}}',
		redirect_to='6',
		message_type=0,
		display_label='Fixture Delay',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 11";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=8,
		template_name='lineup-announced',
		subject=' The lineups for the {{collection_name}} match are announced. Hurry and edit your teams now! ',
		notification_type=132,
		status=0,
		type=2,
		message_body='The toss took place for the {{collection_name}} match, and the teams are announced. You can edit your team till the match starts on  . Game on!',
		message_url='{{FRONTEND_BITLY_URL}}',
		redirect_to='',
		message_type='0',
		display_label='Lineup Announced',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 12";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=9,
		template_name='custom-sms',
		subject='',
		notification_type=134,
		status=1,
		type=6,
		message_body=null,
		message_url='',
		redirect_to='',
		message_type=0,
		display_label='Custom SMS',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 13";
		$this->db->query($sql);

		$sql = "UPDATE ".$this->db->dbprefix(CD_EMAIL_TEMPLATE)." SET 
		category_id=10,
		template_name='custom-notification',
		subject='',
		notification_type=135,
		status=1,
		type=6,
		message_body=null,
		message_url='',
		redirect_to='',
		message_type=0,
		display_label='Custom Notification',
		modified_date='".date('Y-m-d H:i:s')."'
		WHERE cd_email_template_id = 14";
		$this->db->query($sql);

      
	}

	public function down(){

	}

}

?>