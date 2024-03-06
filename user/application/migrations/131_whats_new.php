<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Whats_new extends CI_Migration {

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
        'name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE
        ),
        'description' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE
        ),
        'image' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => FALSE
        ),
        'status' => array(
          'type' => 'TINYINT',
          'constraint' => 1,
          'default' => 1,
          'comment' => '1-Active,0-Inactive'
        ),
        'added_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        ),
        'modified_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE
        )
      );
      $attributes = array('ENGINE'=>'InnoDB');
      $this->dbforge->add_field($fields);
      $this->dbforge->add_key('id', TRUE);
      $this->dbforge->create_table(WHATS_NEW,FALSE,$attributes);
      
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
