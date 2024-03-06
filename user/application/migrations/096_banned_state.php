<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Banned_state extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
	    'id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'auto_increment' => TRUE,
            'null' => FALSE
        ),
      'master_state_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
        ),
	    'date_added' => array(
	          'type' => 'DATETIME',
            'null' => FALSE,
        ),
    );

    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('id', TRUE);
    $this->dbforge->create_table(BANNED_STATE ,FALSE,$attributes);
  }

  public function down()
  {
 	//down script
 	  $this->dbforge->drop_table(BANNED_STATE);
  }
}