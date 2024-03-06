<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_ManualPG extends CI_Migration{

  public function up(){

    //leaderboard category
    if(!$this->db->table_exists(DEPOSIT_TXN))
    {
      $fields = array(
        'ref_id' => array(
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
        'amount' => array(
          'type' => 'DECIMAL',
          'constraint' => '10,2',
          'null' => FALSE,
          'default' => 0.00
        ),
        'type_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'reason' => array(
          'type' => 'VARCHAR',
          'constraint' => 150,
          'null' => TRUE,
          'default' => NULL,
        ),
        'bank_ref' => array(
          'type' => 'VARCHAR',
          'constraint' => 150,
          'null' => TRUE,
          'default' => NULL,
          'unique' => TRUE
        ),
        'receipt_image' => array(
          'type' => 'VARCHAR',
          'null' => FALSE,
          'constraint' => 100
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 0,
          'comment' => '0-Pending,1-Approved,2-Rejected,'
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
        ),
        'modified_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default'=>NULL
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('ref_id', TRUE);
      $this->dbforge->create_table(DEPOSIT_TXN,FALSE,$attributes);
    }
    
    //leaderboard_prize
    if(!$this->db->table_exists(DEPOSIT_TYPE))
    {
      $fields = array(
        'type_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
        'key' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'unique' => TRUE
        ),
        'custom_data' => array(
          'type' => 'JSON',
          'null' => TRUE,
          'default' => NULL
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 1,
          'comment' => '1-Active,2-Inactive'
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => FALSE,
        ),
        'modified_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default'=>NULL
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('type_id', TRUE);
      $this->dbforge->create_table(DEPOSIT_TYPE,FALSE,$attributes);
      
    }
  }

  public function down(){
    //down script
  }
}
