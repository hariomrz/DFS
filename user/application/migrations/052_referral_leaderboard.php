<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Referral_leaderboard extends CI_Migration{

  public function up(){

    $fields = array(
      'referral_prize_id' => array(
        'type' => 'INT',
        'constraint' => 10,
                          //'unsigned' => TRUE,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'prize_category' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => TRUE,
        'comment' => '1=> day, 2=> week, 3 =>month'
      ),
      'name' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => TRUE,
        'default' => NULL,
      ),
      'status' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => FALSE,
        'comment' => '0=>inactive,1=>Active'
      ),
      'allow_prize' => array(
        'type' => 'TINYINT',
        'null' => TRUE,
        'default' => 0,
      ),
      'prize_distribution_detail' => array(
        'type' => 'JSON',
        'null' => TRUE,
                    //'default' => '[]',
      ),
      'allow_sponsor' => array(
        'type' => 'TINYINT',
        'null' => TRUE,
        'default' => 0,
      ),
      'sponsor_logo' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => TRUE,
        'default' => NULL,
      ),
      'sponsor_link' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => TRUE,
        'default' => NULL,
      ),
      'sponsor_name' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => TRUE,
        'default' => NULL,
      ),
    );

    $attributes = array('ENGINE'=>'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('referral_prize_id', TRUE);
    $this->dbforge->create_table(REFERRAL_PRIZE,FALSE,$attributes);

    $prizes = 
    array(
      array(
                    'prize_category' => 1,//day
                    'name' => 'Daily',
                    'status'=> 1,
                    'allow_prize' => 0
                  ),
      array(
                    'prize_category' => 2,//week
                    'name' => 'Weekly',
                    'status'=> 1,
                    'allow_prize' => 0
                  ),
      array(
                    'prize_category' => 3,//month
                    'name' => 'Monthly',
                    'status'=> 1,
                    'allow_prize' => 0
                  )
    );

    $this->db->insert_batch(REFERRAL_PRIZE,$prizes);

   //leaderboard day  
    $fields = array(
      'referral_leaderboard_day_id' => array(
        'type' => 'INT',
        'constraint' => 10,
                          //'unsigned' => TRUE,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'user_id' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'is_winner' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => TRUE,
        'default' => 0
      ),
      'rank_value' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'prize_data' => array(
        'type' => 'JSON',
                    //'constraint' => 150,
        'null' => TRUE,
                    //'default' => '[]',
      ),
      'total_referral' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => FALSE
      ),
      'day_number' => array(
        'type' => 'INT',
        'null' => TRUE,
        'constraint' => 5,
        'default' => 0,
      ),
      'day_date' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL,
      ),
      'prize_distribution_history_id' => array(
        'type' => 'INT',
        'null' => TRUE,
        'constraint' => 11,
        'default' => NULL,
      )
    );

    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('referral_leaderboard_day_id',TRUE);
    $this->dbforge->create_table(REFERRAL_LEADERBOARD_DAY ,FALSE,$attributes);   
    $this->db->query('ALTER TABLE `vi_referral_leaderboard_day` ADD UNIQUE `unique_index` (`day_number`, `day_date`,`user_id`)');

   //leaderboard week
    $fields = array(
      'referral_leaderboard_week_id' => array(
        'type' => 'INT',
        'constraint' => 10,
                          //'unsigned' => TRUE,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'user_id' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'is_winner' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => TRUE,
        'default' => 0
      ),
      'rank_value' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'prize_data' => array(
        'type' => 'JSON',
                    //'constraint' => 150,
        'null' => TRUE,
                    //'default' => '[]',
      ),
      'total_referral' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => FALSE
      ),
      'week_number' => array(
        'type' => 'INT',
        'null' => TRUE,
        'constraint' => 5,
        'default' => 0,
      ),
      'week_start_date' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL,
      ),
      'week_end_date' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL,
      ),
      'prize_distribution_history_id' => array(
        'type' => 'INT',
        'null' => TRUE,
        'constraint' => 11,
        'default' => NULL,
      )
    );
    
    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('referral_leaderboard_week_id',TRUE);
    $this->dbforge->create_table(REFERRAL_LEADERBOARD_WEEK ,FALSE,$attributes);   
    $this->db->query('ALTER TABLE `vi_referral_leaderboard_week` ADD UNIQUE `unique_index` (`week_number`, `week_start_date`,`user_id`)');

    
  //leaderboard month
    $fields = array(
      'referral_leaderboard_month_id' => array(
        'type' => 'INT',
        'constraint' => 10,
                          //'unsigned' => TRUE,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'user_id' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'is_winner' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => TRUE,
        'default' => 0
      ),
      'rank_value' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'prize_data' => array(
        'type' => 'JSON',
                    //'constraint' => 150,
        'null' => TRUE,
                    //'default' => '[]',
      ),
      'total_referral' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => FALSE
      ),
      'month_number' => array(
        'type' => 'INT',
        'null' => TRUE,
        'constraint' => 5,
        'default' => 0,
      ),
      'month_start_date' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL,
      ),
      'month_end_date' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL,
      ),
      'prize_distribution_history_id' => array(
        'type' => 'INT',
        'null' => TRUE,
        'constraint' => 11,
        'default' => NULL,
      )
    );
    
    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('referral_leaderboard_month_id',TRUE);
    $this->dbforge->create_table(REFERRAL_LEADERBOARD_MONTH ,FALSE,$attributes); 
    $this->db->query('ALTER TABLE `vi_referral_leaderboard_month` ADD UNIQUE `unique_index` (`month_number`, `month_start_date`,`user_id`)');
    
//Prize
    $fields = array(
      'prize_distribution_history_id' => array(
        'type' => 'INT',
        'constraint' => 10,
                          //'unsigned' => TRUE,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'referral_prize_id' => array(
        'type' => 'INT',
        'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'name' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => TRUE,
        'default' => 0
      ),
      'prize_date' => array(
        'type' => 'DATETIME',
                    //'constraint' => 10,
        'null' => TRUE,
        'comment' => ''
      ),
      'status' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => TRUE,
        'default' => 0,
      ),
      'is_win_notify' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => FALSE,
        'default' => 0
      )
    );
    
    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('prize_distribution_history_id',TRUE);
    $this->dbforge->create_table(REFERRAL_PRIZE_DISTRIBUTION_HISTORY ,FALSE,$attributes);    
    //$this->db->query('ALTER TABLE `vi_referral_prize_distribution_history` ADD UNIQUE `unique_key` (`referral_prize_id`, `prize_date`);');

    
  }

  public function down(){
    $this->dbforge->drop_table(REFERRAL_PRIZE);
    $this->dbforge->drop_table(REFERRAL_LEADERBOARD_DAY);
    $this->dbforge->drop_table(REFERRAL_LEADERBOARD_WEEK);
    $this->dbforge->drop_table(REFERRAL_LEADERBOARD_MONTH);
    $this->dbforge->drop_table(REFERRAL_PRIZE_DISTRIBUTION_HISTORY);
  }
}