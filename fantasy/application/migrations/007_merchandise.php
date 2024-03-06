<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Merchandise extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
	    'merchandise_id' => array(
            'type' => 'INT',
            'constraint' => 10,
            'auto_increment' => TRUE,
            'null' => FALSE
	    ),
	    'name' => array(
	      'type' => 'VARCHAR',
	      'constraint' => 100,
	      'null' => FALSE
	    ),
	    'price' => array(
	      'type' => 'FLOAT',
	      'null' => FALSE,
	      'default' => 0,
	    ),
	    'image_name' => array(
	      'type' => 'VARCHAR',
	      'constraint' => 150,
	      'default' => NULL,
	    ),
	    'status' => array(
	      'type' => 'TINYINT',
	      'constraint' => 1,
          'default' => 1
	    ),
	    'added_date' => array(
	      'type' => 'DATETIME',
	      'null' => TRUE,
	      'default' => NULL,
	    ),
	    'updated_date' => array(
	      'type' => 'DATETIME',
	      'null' => TRUE,
	      'default' => NULL,
	    )
    );

	  $attributes = array('ENGINE' => 'InnoDB');
	  $this->dbforge->add_field($fields);
	  $this->dbforge->add_key('merchandise_id',TRUE);
	  $this->dbforge->create_table(MERCHANDISE ,FALSE,$attributes); 

  }

  public function down()
  {
 	//down script
 	$this->dbforge->drop_table(MERCHANDISE);
  }
}