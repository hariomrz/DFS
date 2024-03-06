<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Source_changes extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'master_source_id' => array(
                'type' => 'INT',
                'constraint' => 10,
                //'unsigned' => TRUE,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'source' => array(
          'type' => 'INT',
          'constraint' => 10,
          'null' => FALSE,
          'unique' => TRUE
        ),
        'name' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE,
          )
        );

      $attributes = array('ENGINE' => 'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('master_source_id',TRUE);
      $this->dbforge->create_table(MASTER_SOURCE ,FALSE,$attributes);   

      $source_arr = array(
      	array(
      		'source' => 0,
      		'name' => 'Admin'
      	),
      	array(
      		'source' => 1,
      		'name' => 'JoinGame'
      	),
      	array(
      		'source' => 2,
      		'name' => 'GameCancel'
      	),
      	array(
      		'source' => 3,
      		'name' => 'GameWon'
      	),
      	array(
      		'source' => 4,
      		'name' => 'FriendRefferalBonus'
      	),
      	array(
      		'source' => 5,
      		'name' => 'BonusExpired'
      	),
      	array(
      		'source' => 6,
      		'name' => 'Promocode'
      	),
      	array(
      		'source' => 7,
      		'name' => 'Deposit'
      	),
      	array(
      		'source' => 8,
      		'name' => 'Withdraw'
      	),
      	array(
      		'source' => 9,
      		'name' => 'BonusOnDeposit'
      	),
      	array(
      		'source' => 10,
      		'name' => 'DepositPoint'
      	),
      	array(
      		'source' => 11,
      		'name' => 'WithdrawCoins'
      	),
      	array(
      		'source' => 12,
      		'name' => 'Signup Bonus'
      	),
      	array(
      		'source' => 13,
      		'name' => 'Friend Phone Verified'
      	),
      	array(
      		'source' => 14,
      		'name' => 'Pancard Verified'
      	),
      	array(
      		'source' => 15,
      		'name' => 'Referral Contest Join'
      	),
      	array(
      		'source' => 16,
      		'name' => 'Referrral Collection Join'
      	),
      	array(
      		'source' => 17,
      		'name' => 'User phone verified'
      	),
      	array(
      		'source' => 40,
      		'name' => 'makePrediction'
      	),
      	array(
      		'source' => 41,
      		'name' => 'prediction won'
      	),
      	array(
      		'source' => 30,
      		'name' => 'First Deposit'
      	),
      	array(
      		'source' => 31,
      		'name' => 'Deposit Range'
      	),	
      	array(
      		'source' => 32,
      		'name' => 'PromoCode'
      	),		
      	array(
      		'source' => 50,
      		'name' => 'new signup(all) - bonus'
      	),	
      	array(
      		'source' => 51,
      		'name' => 'new signup(all) - real'
      	),	
      	array(
      		'source' => 52,
      		'name' => 'new signup(all) - coin'
      	),	
      	array(
      		'source' => 53,
      		'name' => 'new signupreferral - bonus'
      	),	
      	array(
      		'source' => 54,
      		'name' => 'new signupreferral - real'
      	),	
      	array(
      		'source' => 55,
      		'name' => 'new signupreferral - coin'
      	),	
      	array(
      		'source' => 56,
      		'name' => 'new signup(referred) - bonus'
      	),	
      	array(
      		'source' => 57,
      		'name' => 'new signup(referred) - real'
      	),	
      	array(
      		'source' => 58,
      		'name' => 'new signup(referred) - coin'
      	),	
      	array(
      		'source' => 59,
      		'name' => 'pan verification(all) - bonus'
      	),	
      	array(
      		'source' => 60,
      		'name' => 'pan verfication(all)  - real'
      	),	
      	array(
      		'source' => 61,
      		'name' => 'pan verfication(all)  - coin'
      	),	
      	array(
      		'source' => 62,
      		'name' => 'pan verificationreferral - bonus'
      	),	
      	array(
      		'source' => 63,
      		'name' => 'pan verificationreferral - real'
      	),	
      	array(
      		'source' => 64,
      		'name' => 'pan verificationreferral - coin'
      	),	
      	array(
      		'source' => 65,
      		'name' => 'pan verification(referred) - bonus'
      	),	
      	array(
      		'source' => 66,
      		'name' => 'pan verification(referred) - real'
      	),	
      	array(
      		'source' => 67,
      		'name' => 'pan verification(referred) - coin'
      	),	
      	array(
      		'source' => 68,
      		'name' => '1st cash contestreferral - bonus'
      	),	
      	array(
      		'source' => 69,
      		'name' => '1st cash contestreferral - real'
      	),	
      	array(
      		'source' => 70,
      		'name' => '1st cash contestreferral - coin'
      	),	
      	array(
      		'source' => 71,
      		'name' => '1st cash contest(referred) - bonus'
      	),	
      	array(
      		'source' => 72,
      		'name' => '1st cash contest(referred) - real'
      	),	
      	array(
      		'source' => 73,
      		'name' => '1st cash contest(referred) - coin'
      	),	
      	array(
      		'source' => 74,
      		'name' => '5th cash contestreferral - bonus'
      	),	
      	array(
      		'source' => 75,
      		'name' => '5th cash contestreferral - real'
      	),	
      	array(
      		'source' => 76,
      		'name' => '5th cash contestreferral - coin'
      	),	
      	array(
      		'source' => 77,
      		'name' => '5th cash contest(referred) - bonus'
      	),	
      	array(
      		'source' => 78,
      		'name' => '5th cash contest(referred) - real'
      	),	
      	array(
      		'source' => 79,
      		'name' => '5th cash contest(referred) - coin'
      	),	
      	array(
      		'source' => 80,
      		'name' => '10th cash contestreferral - bonus'
      	),	
      	array(
      		'source' => 81,
      		'name' => '10th cash contestreferral - real'
      	),	
      	array(
      		'source' => 82,
      		'name' => '10th cash contestreferral - coin'
      	),	
      	array(
      		'source' => 83,
      		'name' => '10th cash contest(referred) - bonus'
      	),	
      	array(
      		'source' => 84,
      		'name' => '10th cash contest(referred) - real'
      	),	
      	array(
      		'source' => 85,
      		'name' => '10th cash contest(referred) - coin'
      	),	
      	array(
      		'source' => 86,
      		'name' => 'Email Verification(referred) - bonus'
      	),	
      	array(
      		'source' => 87,
      		'name' => 'Email Verification(referred) - real'
      	),	
      	array(
      		'source' => 88,
      		'name' => 'Email Verification(referred) - coin'
      	),	
      	array(
      		'source' => 89,
      		'name' => 'Email Verificationreferral - bonus'
      	),	
      	array(
      		'source' => 90,
      		'name' => 'Email Verificationreferral - real'
      	),	
      	array(
      		'source' => 91,
      		'name' => 'Email Verificationreferral - coin'
      	),	
      	array(
      		'source' => 92,
      		'name' => 'Email Verification(referred) - bonus'
      	),	
      	array(
      		'source' => 93,
      		'name' => 'Email Verification(referred) - real'
      	),	
      	array(
      		'source' => 94,
      		'name' => 'Email Verification(referred) - coin'
      	),	
      	array(
      		'source' => 95,
      		'name' => 'First Deposit(referred) - bonus'
      	),	
      	array(
      		'source' => 96,
      		'name' => 'First Deposit(referred) - real'
      	),	
      	array(
      		'source' => 97,
      		'name' => 'First Deposit(referred) - coin'
      	),	
      	array(
      		'source' => 98,
      		'name' => 'First Depositreferral - bonus'
      	),	
      	array(
      		'source' => 99,
      		'name' => 'First Depositreferral - real'
      	),	
      	array(
      		'source' => 100,
      		'name' => 'First Depositreferral - coin'
      	),		
      	array(
      		'source' => 105,
      		'name' => 'First Deposit(referred) - bonus'
      	),	
      	array(
      		'source' => 106,
      		'name' => 'First Deposit(referred) - real'
      	),	
      	array(
      		'source' => 107,
      		'name' => 'First Deposit(referred) - coin'
      	),	
      	array(
      		'source' => 121,
      		'name' => 'Promotione for contest'
      	),	
      	array(
      		'source' => 122,
      		'name' => 'Fixture Promotion'
      	),	
      	array(
      		'source' => 123,
      		'name' => 'Refer a Friend'
      	),	
      	array(
      		'source' => 124,
      		'name' => 'Promocode for first deposit'
      	),	
      	array(
      		'source' => 125,
      		'name' => 'Contest cancelled by the admin'
      	),		
      	array(
      		'source' => 130,
      		'name' => 'amount deducted as TDS'
      	),	
      	array(
      		'source' => 132,
      		'name' => 'bank verify w/o referral -bonus'
      	),	
      	array(
      		'source' => 133,
      		'name' => 'bank verify w/o referral -real'
      	),	
      	array(
      		'source' => 134,
      		'name' => 'bank verify w/o referral -coin'
      	),	
      	array(
      		'source' => 135,
      		'name' => 'deal bonus on add fund'
      	),	
      	array(
      		'source' => 136,
      		'name' => 'deal real on add fund'
      	),	
      	array(
      		'source' => 137,
      		'name' => 'deal coins on add fund'
      	),	
      	array(
      		'source' => 138,
      		'name' => 'bank verificationreferral  -bonus'
      	),	
      	array(
      		'source' => 139,
      		'name' => 'bank verificationreferral -real'
      	),	
      	array(
      		'source' => 140,
      		'name' => 'bank verificationreferral -coin'
      	),	
      	array(
      		'source' => 141,
      		'name' => 'bank verification(referred) - bonus'
      	),	
      	array(
      		'source' => 142,
      		'name' => 'bank verification(referred) - real'
      	),	
      	array(
      		'source' => 143,
      		'name' => 'bank verification(referred) - coin'
      	),	
      	array(
      		'source' => 144,
      		'name' => 'daily streak coins'
      	),	
      	array(
      		'source' => 145,
      		'name' => 'Bonus received on coins redeem'
      	),	
      	array(
      		'source' => 146,
      		'name' => 'Real amount received on coins redeem'
      	),	
      	array(
      		'source' => 147,
      		'name' => 'coins deduct on redeem coins'
      	),	
      	array(
      		'source' => 151,
      		'name' => 'Coins added on feedback approved'
      	),	
      	array(
      		'source' => 152,
      		'name' => '{amount} coins added for constest participation'
      	),	
      	array(
      		'source' => 153,
      		'name' => 'Edit referral code reward -  {amount} bonus'
      	),	
      	array(
      		'source' => 154,
      		'name' => 'Edit referral code reward - {amount} real cash'
      	),	
      	array(
      		'source' => 155,
      		'name' => 'Edit referral code reward - {amount} coins'
      	),	
      	array(
      		'source' => 156,
      		'name' => '5th signup referral - bonus'
      	),	
      	array(
      		'source' => 157,
      		'name' => '5th sign up referral - real'
      	),	
      	array(
      		'source' => 158,
      		'name' => '5th sign up referral - coin'
      	),	
      	array(
      		'source' => 159,
      		'name' => '10th signup referral - bonus'
      	),	
      	array(
      		'source' => 160,
      		'name' => '10th sign up referral - real'
      	),	
      	array(
      		'source' => 161,
      		'name' => '10th sign up referral - coin'
      	),	
      	array(
      		'source' => 162,
      		'name' => '15th signup referral - bonus'
      	),	
      	array(
      		'source' => 163,
      		'name' => '15th sign up referral - real'
      	),	
      	array(
      		'source' => 164,
      		'name' => '15th sign up referral - coin'
      	),	
      	array(
      		'source' => 165,
      		'name' => 'Phone Verification - bonus'
      	),	
      	array(
      		'source' => 166,
      		'name' => 'Phone Verification - real'
      	),	
      	array(
      		'source' => 167,
      		'name' => 'Phone Verification - coin'
      	),	
      	array(
      		'source' => 168,
      		'name' => 'Phone Verificationreferral - bonus'
      	),	
      	array(
      		'source' => 169,
      		'name' => 'Phone Verificationreferral - real'
      	),	
      	array(
      		'source' => 170,
      		'name' => 'Phone Verificationreferral - coin'
      	),	
      	array(
      		'source' => 171,
      		'name' => 'Phone Verificationreferral - bonus'
      	),	
      	array(
      		'source' => 172,
      		'name' => 'Phone Verificationreferral - real'
      	),	
      	array(
      		'source' => 173,
      		'name' => 'Phone Verificationreferral - coin'
      	),	
      	array(
      		'source' => 174,
      		'name' => 'prediction refund'
      	),	
      	array(
      		'source' => 181,
      		'name' => 'Won Pickem Prize'
      	),	
      	array(
      		'source' => 184,
      		'name' => 'admin withdrawal'
      	),	
      
      	array(
      		'source' => 220,
      		'name' => 'Open predictor make prediction'
      	),		
      	array(
      		'source' => 225,
      		'name' => 'Prediction Leaderboard Winnings'
      	),		
      	array(
      		'source' => 226,
      		'name' => 'Prediction Leaderboard Winnings'
      	),		
      	array(
      		'source' => 227,
      		'name' => 'Prediction Leaderboard Winnings'
      	),		
      	array(
      		'source' => 230,
      		'name' => 'Mini-League Won'
      	),		
      	array(
      		'source' => 231,
      		'name' => 'Mini-League Join'
      	),		
      	array(
      		'source' => 240,
      		'name' => 'Network contest joined'
      	),		
      	array(
      		'source' => 241	,
      		'name' => 'Network contest won'
      	),		
      	array(
      		'source' => 242,
      		'name' => 'Network contest canceled/refund fee'
      	),		
      	array(
      		'source' => 250,
      		'name' => 'pickem bet coins'
      	),		
      	array(
      		'source' => 251,
      		'name' => 'Refunded for Pick’em of Game (details)'
      	),		
      	array(
      		'source' => 261,
      		'name' => 'Referral Leaderboard Daily Winner'
      	),		
      	array(
      		'source' => 262,
      		'name' => 'Referral Leaderboard Weekly Winner'
      	),		
      	array(
      		'source' => 263,
      		'name' => 'Referral Leaderboard Monthly Winner'
      	),		
      	array(
      		'source' => 270,
      		'name' => 'Every cash contest referral - real'
      	),		
      	array(
      		'source' => 271,
      		'name' => 'Every cash contest referral - bonus'
      	),		
      	array(
      		'source' => 272,
      		'name' => 'Every cash contest referral - coin'
      	),		
      	array(
      		'source' => 273,
      		'name' => 'Every cash contest (referred) - real'
      	),		
      	array(
      		'source' => 274,
      		'name' => 'Every cash contest (referred) - bonus'
      	),		
      	array(
      		'source' => 275,
      		'name' => 'Every cash contest (referred) - coin'
      	),		
      	array(
      		'source' => 276,
      		'name' => 'Weekly cash contest referral - real'
      	),		
      	array(
      		'source' => 277,
      		'name' => 'Weekly cash contest referral - bonus'
      	),		
      	array(
      		'source' => 278,
      		'name' => 'Weekly cash contest referral - coin'
      	),		
      	array(
      		'source' => 279,
      		'name' => 'Weekly cash contest (referred) - real'
      	),		
      	array(
      		'source' => 280,
      		'name' => 'Weekly cash contest (referred) - bonus'
      	),		
      	array(
      		'source' => 281,
      		'name' => 'Weekly cash contest (referred) - coin'
      	),		
      	array(
      		'source' => 282,
      		'name' => 'Purchase coin  - Coin'
      	),		
      	array(
      		'source' => 283,
      		'name' => 'Amount Deduct from coin purchase '
      	),		
      	array(
      		'source' => 301,
      		'name' => 'Entry fee for %s(Prop)'
      	),		
      	array(
      		'source' => 302,
      		'name' => 'Fee Refund For Contest(Prop)'
      	),		
      	array(
      		'source' => 303,
      		'name' => 'Won Contest Prize(Prop)'
      	),			
      );

	$this->db->insert_batch(MASTER_SOURCE,$source_arr);

  }

  public function down()
  {
	 //down script 
    $this->dbforge->drop_table(MASTER_SOURCE);
  }
}