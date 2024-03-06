<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Referral_db extends CI_Migration {

  public function up()
  {
  	$fields = array(
            'is_referral_code_edited' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'referral_code',
                    'null' => FALSE
            )
	);
	$this->dbforge->add_column(USER,$fields);
        
        $affiliate_setting = 
                array(
                    array(
                        'affiliate_type' => 18,
                        'amount_type'=> 1,
                        'affiliate_description' => 'Edit referral code reward',
                        'invest_money' => 0,
                        'bonus_amount' => 0,
                        'real_amount' => 0,
                        'coin_amount' => 0,
                        'user_bonus' => 20,
                        'user_real' => 20,
                        'user_coin' => 20,
                        'is_referral' => 0,
                        'max_earning_amount' => 0,
                        'status' => 1,
                        'order' => 18,
                        'last_update_date' => '2020-01-08 03:15:41',
                    ),
                    array(
                        'affiliate_type' => 19,
                        'amount_type'=> 1,
                        'affiliate_description' => '5th sign up',
                        'invest_money' => 0,
                        'bonus_amount' => 50,
                        'real_amount' => 50,
                        'coin_amount' => 50,
                        'user_bonus' => 0,
                        'user_real' => 0,
                        'user_coin' => 0,
                        'is_referral' => 1,
                        'max_earning_amount' => 0,
                        'status' => 1,
                        'order' => 3,
                        'last_update_date' => '2020-01-08 03:15:41',
                    ),
                    array(
                        'affiliate_type' => 20,
                        'amount_type'=> 1,
                        'affiliate_description' => '10th sign up',
                        'invest_money' => 0,
                        'bonus_amount' => 100,
                        'real_amount' => 100,
                        'coin_amount' => 100,
                        'user_bonus' => 0,
                        'user_real' => 0,
                        'user_coin' => 0,
                        'is_referral' => 1,
                        'max_earning_amount' => 0,
                        'status' => 1,
                        'order' => 4,
                        'last_update_date' => '2020-01-08 03:15:41',
                    ),
                    array(
                        'affiliate_type' => 21,
                        'amount_type'=> 1,
                        'affiliate_description' => '15th sign up',
                        'invest_money' => 0,
                        'bonus_amount' => 100,
                        'real_amount' => 100,
                        'coin_amount' => 100,
                        'user_bonus' => 0,
                        'user_real' => 0,
                        'user_coin' => 0,
                        'is_referral' => 1,
                        'max_earning_amount' => 0,
                        'status' => 1,
                        'order' => 5,
                        'last_update_date' => '2020-01-08 03:15:41',
                    )
                );
        $this->db->insert_batch(AFFILIATE_MASTER,$affiliate_setting);
        
        $notification_description = 
                array(
                    array(
                        'notification_type' => 153,
                        'message'=> 'You have recieved {{amount}} bonus for editing your referral code',
                        'en_message' => 'You have recieved {{amount}} bonus for editing your referral code',
                        'hi_message' => 'You have recieved {{amount}} bonus for editing your referral code',
                        'guj_message' => 'You have recieved {{amount}} bonus for editing your referral code'
                    ),
                    array(
                        'notification_type' => 154,
                        'message'=> 'You have recieved '.CURRENCY_CODE.'{{amount}} real cash for editing your referral code',
                        'en_message' => 'You have recieved '.CURRENCY_CODE.'{{amount}} real cash for editing your referral code',
                        'hi_message' => 'You have recieved '.CURRENCY_CODE.'{{amount}} real cash for editing your referral code',
                        'guj_message' => 'You have recieved '.CURRENCY_CODE.'{{amount}} real cash for editing your referral code'
                    ),
                    array(
                        'notification_type' => 155,
                        'message'=> 'You have recieved {{amount}} coins for editing your referral code',
                        'en_message' => 'You have recieved {{amount}} coins for editing your referral code',
                        'hi_message' => 'You have recieved {{amount}} coins for editing your referral code',
                        'guj_message' => 'You have recieved {{amount}} coins for editing your referral code'
                    ),
                    array(
                        'notification_type' => 156,
                        'message'=> 'Super! You have achieved milesone of 5th successful referral & received {{amount}} bonus',
                        'en_message' => 'Super! You have achieved milesone of 5th successful referral & received {{amount}} bonus',
                        'hi_message' => 'Super! You have achieved milesone of 5th successful referral & received {{amount}} bonus',
                        'guj_message' => 'Super! You have achieved milesone of 5th successful referral & received {{amount}} bonus'
                    ),
                    array(
                        'notification_type' => 157,
                        'message'=> 'Super! You have achieved milesone of 5th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'en_message' => 'Super! You have achieved milesone of 5th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'hi_message' => 'Super! You have achieved milesone of 5th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'guj_message' => 'Super! You have achieved milesone of 5th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash'
                    ),
                    array(
                        'notification_type' => 158,
                        'message'=> 'Super! You have achieved milesone of 5th successful referral & received {{amount}} coins',
                        'en_message' => 'Super! You have achieved milesone of 5th successful referral & received {{amount}} coins',
                        'hi_message' => 'Super! You have achieved milesone of 5th successful referral & received {{amount}} coins',
                        'guj_message' => 'Super! You have achieved milesone of 5th successful referral & received {{amount}} coins'
                    ),
                    array(
                        'notification_type' => 159,
                        'message'=> 'Super! You have achieved milesone of 10th successful referral & received {{amount}} bonus',
                        'en_message' => 'Super! You have achieved milesone of 10th successful referral & received {{amount}} bonus',
                        'hi_message' => 'Super! You have achieved milesone of 10th successful referral & received {{amount}} bonus',
                        'guj_message' => 'Super! You have achieved milesone of 10th successful referral & received {{amount}} bonus'
                    ),
                    array(
                        'notification_type' => 160,
                        'message'=> 'Super! You have achieved milesone of 10th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'en_message' => 'Super! You have achieved milesone of 10th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'hi_message' => 'Super! You have achieved milesone of 10th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'guj_message' => 'Super! You have achieved milesone of 10th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash'
                    ),
                    array(
                        'notification_type' => 161,
                        'message'=> 'Super! You have achieved milesone of 10th successful referral & received {{amount}} coins',
                        'en_message' => 'Super! You have achieved milesone of 10th successful referral & received {{amount}} coins',
                        'hi_message' => 'Super! You have achieved milesone of 10th successful referral & received {{amount}} coins',
                        'guj_message' => 'Super! You have achieved milesone of 10th successful referral & received {{amount}} coins'
                    ),
                    array(
                        'notification_type' => 162,
                        'message'=> 'Super! You have achieved milesone of 15th successful referral & received {{amount}} bonus',
                        'en_message' => 'Super! You have achieved milesone of 15th successful referral & received {{amount}} bonus',
                        'hi_message' => 'Super! You have achieved milesone of 15th successful referral & received {{amount}} bonus',
                        'guj_message' => 'Super! You have achieved milesone of 15th successful referral & received {{amount}} bonus'
                    ),
                    array(
                        'notification_type' => 163,
                        'message'=> 'Super! You have achieved milesone of 15th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'en_message' => 'Super! You have achieved milesone of 15th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'hi_message' => 'Super! You have achieved milesone of 15th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash',
                        'guj_message' => 'Super! You have achieved milesone of 15th successful referral & received '.CURRENCY_CODE.'{{amount}} real cash'
                    ),
                    array(
                        'notification_type' => 164,
                        'message'=> 'Super! You have achieved milesone of 15th successful referral & received {{amount}} coins',
                        'en_message' => 'Super! You have achieved milesone of 15th successful referral & received {{amount}} coins',
                        'hi_message' => 'Super! You have achieved milesone of 15th successful referral & received {{amount}} coins',
                        'guj_message' => 'Super! You have achieved milesone of 15th successful referral & received {{amount}} coins'
                    ),
                    array(
                        'notification_type' => 165,
                        'message'=> 'You have earned {{amount}} bonus cash by verifying your phone number',
                        'en_message' => 'You have earned {{amount}} bonus cash by verifying your phone number',
                        'hi_message' => 'You have earned {{amount}} bonus cash by verifying your phone number',
                        'guj_message' => 'You have earned {{amount}} bonus cash by verifying your phone number'
                    ),
                    array(
                        'notification_type' => 166,
                        'message'=> 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number',
                        'en_message' => 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number',
                        'hi_message' => 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number',
                        'guj_message' => 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number'
                    ),
                    array(
                        'notification_type' => 167,
                        'message'=> 'You have earned {{amount}} coins cash by verifying your phone number',
                        'en_message' => 'You have earned {{amount}} coins cash by verifying your phone number',
                        'hi_message' => 'You have earned {{amount}} coins cash by verifying your phone number',
                        'guj_message' => 'You have earned {{amount}} coins cash by verifying your phone number'
                    ),
                    array(
                        'notification_type' => 168,
                        'message'=> 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} bonus cash',
                        'en_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} bonus cash',
                        'hi_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} bonus cash',
                        'guj_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} bonus cash'
                    ),
                    array(
                        'notification_type' => 169,
                        'message'=> 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned '.CURRENCY_CODE.'{{amount}} real cash',
                        'en_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned '.CURRENCY_CODE.'{{amount}} real cash',
                        'hi_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned '.CURRENCY_CODE.'{{amount}} real cash',
                        'guj_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned '.CURRENCY_CODE.'{{amount}} real cash'
                    ),
                    array(
                        'notification_type' => 170,
                        'message'=> 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} coins',
                        'en_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} coins',
                        'hi_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} coins',
                        'guj_message' => 'Congratulations! {{friend_name}} referred by you has verified his/her phone number. You have earned {{amount}} coins'
                    ),
                    array(
                        'notification_type' => 171,
                        'message'=> 'You have earned {{amount}} bonus cash by verifying your phone number',
                        'en_message' => 'You have earned {{amount}} bonus cash by verifying your phone number',
                        'hi_message' => 'You have earned {{amount}} bonus cash by verifying your phone number',
                        'guj_message' => 'You have earned {{amount}} bonus cash by verifying your phone number'
                    ),
                    array(
                        'notification_type' => 172,
                        'message'=> 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number',
                        'en_message' => 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number',
                        'hi_message' => 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number',
                        'guj_message' => 'You have earned '.CURRENCY_CODE.'{{amount}} real cash by verifying your phone number'
                    ),
                    array(
                        'notification_type' => 173,
                        'message'=> 'You have earned {{amount}} coins cash by verifying your phone number',
                        'en_message' => 'You have earned {{amount}} coins cash by verifying your phone number',
                        'hi_message' => 'You have earned {{amount}} coins cash by verifying your phone number',
                        'guj_message' => 'You have earned {{amount}} coins cash by verifying your phone number'
                    ),
                );
        $this->db->insert_batch(NOTIFICATION_DESCRIPTION,$notification_description);
  }

  public function down()
  {
	//down script 
	$this->dbforge->drop_column(USER, 'is_referral_code_edited');
        
        $this->db->where_in('affiliate_type', array(18,19,20,21));
	$this->db->delete(AFFILIATE_MASTER);
        
        $this->db->where_in('notification_type', array(153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173));
	$this->db->delete(NOTIFICATION_DESCRIPTION);
  }
}