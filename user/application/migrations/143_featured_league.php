<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Featured_league extends CI_Migration {

  public function up()
  {
    //Trasaction start
    $this->db->trans_strict(TRUE);
    $this->db->trans_start();

    $fields = array(
        'id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'auto_increment' => TRUE,
          'null' => FALSE
        ),
         'sports_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'league_uid' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => FALSE
        ),
        'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE
        ),
        'dfs_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'pickem_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => FALSE
        ),
        'modified_date' => array(
          'type' => 'DATETIME',
          'null' => FALSE
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('id', TRUE);
      $this->dbforge->create_table(FEATURED_LEAGUE,FALSE,$attributes);
      
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
     
  }
  
}
