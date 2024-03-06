<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Match_report_data extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
	    'sports_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
        ),
          'contest_type' => array(
            'type' => 'INT',
            'null' => TRUE,
            'default' => NULL,
            'comment' =>"1=>daily,2=>weekly,3=>monthly"
        ),
           'game_type' => array(
            'type' => 'INT',
            'null' => false, 
            'comment' =>"1=>DFS,2=>STOCK"         
        ),
        'league_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
          ),
	    'collection_master_id' => array(
	      'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
        ),
        'match_name' => array(
	      'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
        ),
        'schedule_date' => array(
            'type' => 'DATETIME',
            'null' => FALSE,
          ),
	    'total_user' => array(
	      'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
	      'default' => '0',
	    ),
	    'real_user' => array(
	      'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
          'default' => '0'
	    ),
	    'entry_real' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'entry_bonus' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'entry_coins' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
            'default' => 0,
        ),
        'site_rake' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'site_rake_private' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'prize_pool' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'prize_pool_real' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'prize_pool_bonus' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'prize_pool_coins' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
            'default' => 0,
        ),
        'promo_discount' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'bots_entry' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'bots_winning' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'revenue' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'profit' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00,
        ),
        'modified_date' => array(
            'type' => 'DATETIME',
            'null' => TRUE,
            'default' => NULL,
        ),
    );

	  $attributes = array('ENGINE' => 'InnoDB');
	  $this->dbforge->add_field($fields);
      $this->dbforge->create_table(MATCH_REPORT ,FALSE,$attributes); 
      
    $sql = "ALTER TABLE ".$this->db->dbprefix(MATCH_REPORT)." ADD UNIQUE KEY sports_id (sports_id,league_id,collection_master_id);";
    $this->db->query($sql);

  }

  public function down()
  {
 	//down script
 	$this->dbforge->drop_table(MATCH_REPORT);
  }
}