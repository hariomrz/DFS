<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Network_fantasy extends CI_Migration {

  public function up()
  {

    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    //up script
    $this->dbforge->add_field(array(
      'id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => TRUE,
        'auto_increment' => TRUE
      ),
      'network_collection_master_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'default' => 0
      ),
      'network_contest_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => TRUE,
        'default' => 0
      ),
      'collection_master_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => TRUE,
        'default' => NULL
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
        'comment' => "client league id"
      ),
      'network_prize_pool' => array(
          'type' => 'FLOAT',
          'null' => FALSE,
          'default' => 0
        ),    
      'active' => array(
        'type' => 'TINYINT',
        'null' => FALSE,
        'default' => 0,
        'comment' => "1=>Active network game"
      ),
      'status' => array(
        'type' => 'TINYINT',
        'null' => FALSE,
        'default' => 0,
        'comment' => "0-Open, 1-Cancel, 2-Complete/Close, 3-Prize Distributed"
      ),
      'season_scheduled_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      ),
      'contest_details' => array(
        'type' => 'JSON',
        'null' => TRUE,
        'default' => NULL
      ),
      'is_prize_distributed' => array(
        'type' => 'TINYINT',
        'null' => FALSE,
        'default' => 0,
        'comment' => "0-NO,1-Yes"
      ),
      'is_win_notify' => array(
        'type' => 'TINYINT',
        'null' => FALSE,
        'default' => 0,
        'comment' => "0-Pending,1-Sent"
      ),
      'is_fee_refunded' => array(
        'type' => 'TINYINT',
        'null' => FALSE,
        'default' => 0,
        'comment' => "0-NO,1-YES"
      ),
      'date_added' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      )
      
    ));
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table(NETWORK_CONTEST);
    $this->db->query('ALTER TABLE '.$this->db->dbprefix(NETWORK_CONTEST).' ADD CONSTRAINT unique_key UNIQUE (network_collection_master_id,network_contest_id)');

    //up script
    $this->dbforge->add_field(array(
      'network_lm_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => TRUE,
        'auto_increment' => TRUE
      ),
      'lineup_master_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'comment' => "client side id"
      ),
      'network_lineup_master_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE
      ),
      'collection_master_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE
      ),
      'league_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE,
        'comment' => "client side id"
      ),
      'network_collection_master_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => TRUE,
        'default' => NULL
      ),
      'network_league_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => TRUE,
        'default' => NULL
      )
    ));
    $this->dbforge->add_key('network_lm_id', TRUE);
    $this->dbforge->create_table(NETWORK_LINEUP_MASTER);
    $this->db->query('ALTER TABLE '.$this->db->dbprefix(NETWORK_LINEUP_MASTER).' ADD CONSTRAINT unique_key UNIQUE (lineup_master_id,network_lineup_master_id)');
    //Trasaction end
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
          $this->db->trans_rollback();
      }
      else
      {
          $this->db->trans_commit();
      }
  	
  }

  public function down()
  {
    //Trasaction start
      $this->db->trans_strict(TRUE);
      $this->db->trans_start();
      
        $this->dbforge->drop_table(NETWORK_CONTEST);
        $this->dbforge->drop_table(NETWORK_LINEUP_MASTER);

      //Trasaction end
      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE )
      {
          $this->db->trans_rollback();
      }
      else
      {
          $this->db->trans_commit();
      }  
	   
  }
}