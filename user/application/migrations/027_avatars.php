<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Avatars extends CI_Migration{

  public function up(){

    $fields = array(
      'id' => array(
        'type' => 'INT',
        'constraint' => 10,
                          //'unsigned' => TRUE,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'name' => array(
        'type' => 'VARCHAR',
        'constraint' => 150,
        'null' => TRUE,
        'default' => NULL,
      ),
      'is_default' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => FALSE,
        'default' => 0,
        'comment' => '0=> added by admin, 1=> default'
      ),
      'status' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'null' => FALSE,
        'default' => 1,
        'comment' => '0=>hidden,1=>Active'
      ),
      'added_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE,
      ),
    );

    $attributes = array('ENGINE'=>'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table(AVATARS,FALSE,$attributes);

    $avatars = 
    array(
      array(
                    'name' => 'avatar1.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar2.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar3.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar4.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar5.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar6.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar7.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar8.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar9.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      array(
                    'name' => 'avatar10.png',
                    'is_default' => 1,
                    'status'=> 1,
                    'added_date' => date('Y-m-d H:i:s'),
                  ),
      
    );

    $this->db->insert_batch(AVATARS,$avatars);

  }

  public function down(){
    $this->dbforge->drop_table(AVATARS);
  }
}
