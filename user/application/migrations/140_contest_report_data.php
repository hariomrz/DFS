<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Contest_report_data extends CI_Migration {

  public function up()
  {
  	//up script
    if(!$this->db->table_exists(CONTEST_REPORT))
    {
      $fields = array(     
        'contest_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'collection_master_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'contest_unique_id' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
        ),
        'game_type' => array(
          'type' => 'INT',
          'null' => false, 
          'comment' =>"1=>DFS,2=>EQUITY,3=>STOCK PREDICT,4=>LIVE STOCK FANTASY,5=>STOCK FANTASY"         
        ),
        'contest_type' => array(
              'type' => 'INT',
              'null' => TRUE,
              'default' => NULL,
              'comment' =>"1=>Daily,2=>Weekly,3=>Monthly"
          ),
          'feature_type' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE,
          ),
        
        'sports_id' => array(
              'type' => 'INT',
              'constraint' => 11,
              'null' => FALSE
          ),
        'league_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
          ),
        'group_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
        ),
        'match_name' => array(
          'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => FALSE,
        ),
        'contest_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
        ),
        'group_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
        ),
        'schedule_date' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
        ),
        'minimum_size' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
        ),
        'size' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
        ), 
        'total_user_joined' => array(
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
        
        'bot_user' => array(
          'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE,
            'default' => '0'
        ),

        'entry_fee' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00
        ),

        'max_bonus_allowed' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
        ), 

        'site_rake' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00,
        ),

        'currency_type' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
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

        'promo_entry' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00,
      ),
      'bot_entry_fee' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'null' => FALSE,
        'default' => 0.00,
      ), 

      'system_teams' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0,
      ),

      'real_teams' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0,
      ),
      'prize_pool' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'null' => FALSE,
        'default' => 0.00,
      ),

      'real_prize' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'null' => FALSE,
        'default' => 0.00,
      ),
      'coin_prize' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0,
      ),
      'bonous_prize' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0,
      ),

      'profit' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'null' => FALSE,
        'default' => 0.00,
      ),

      'guaranteed_prize' => array(
        'type' => 'VARCHAR',
        'constraint' => 255,
        'null' => FALSE,
      ),
      'is_reverse' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 0,        
      ),
      'is_2nd_inning' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 0,      
      ),
      'created_date' => array(
        'type' => 'DATETIME',
        'null' => TRUE,
        'default' => NULL,
      ),  
        
      );

	  $attributes = array('ENGINE' => 'InnoDB');
	  $this->dbforge->add_field($fields);  
    $this->dbforge->create_table(CONTEST_REPORT ,FALSE,$attributes); 
      
    $sql = "ALTER TABLE ".$this->db->dbprefix(CONTEST_REPORT)." ADD UNIQUE KEY sports_id (sports_id,league_id,collection_master_id,contest_id);";
    $this->db->query($sql);
  }


    if(!$this->db->table_exists(USER_MATCH_REPORT))
    {

      $fields = array(     
        'id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'user_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'module_type' => array(
          'type' => 'INT',
          'null' => false, 
          'comment' =>"1=>DFS"         
        ),
        'entity_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE,
          'comment' =>"collection_master_id"
        ),
        'schedule_date' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
        ),
        'match_played' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => false,
          'default' =>0,
        ),
        'match_won' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => false,
          'default' =>0, 
        ),
        'match_lost' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => false,
          'default' =>0, 
        ),
        'total_entry_fee' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00
        ),
        'coin_entry' => array(
          'type' => 'INT',       
          'null' => FALSE,
          'default' =>0,        
        ),
       
        'total_bonus_used' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00
        ),
        'total_win_amt' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00
        ),
        'bonus_win' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00
        ),
        'coin_winning' => array(
          'type' => 'INT',       
          'null' => FALSE,
          'default' =>0,        
        ),
        'revenue' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00
        ),
        'created_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('id', TRUE);
      $this->dbforge->create_table(USER_MATCH_REPORT,FALSE,$attributes);
      //add unique key
      $sql = "ALTER TABLE ".$this->db->dbprefix(USER_MATCH_REPORT)." ADD UNIQUE KEY module_type(module_type,user_id,entity_id);";
      $this->db->query($sql);
    }
  }

  public function down()
  {
 	//down script
 	// $this->dbforge->drop_table(CONTEST_REPORT);
  }
}