<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_user_track extends CI_Migration {

	public function up() {
		$fields = array(
        'user_track_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
        ),
        'affiliate_reference_id' => array(
          'type' => 'VARCHAR',
          'constraint' => 60,
          'null' => FALSE,
        ),
        'user_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => TRUE
          ),
          'landing_date' => array(
            'type' => 'DATETIME',
            'null' => FALSE
          ),
          'signup_date' => array(
            'type' => 'DATETIME',
            'null' => TRUE
          ),
          'deposit_date' => array(
            'type' => 'DATETIME',
            'null' => TRUE 
          )
        );

      $attributes = array('ENGINE' => 'InnoDB');
	  $this->dbforge->add_field($fields);
	  $this->dbforge->add_key('user_track_id',TRUE);
	  $this->dbforge->create_table(USER_TRACK ,FALSE,$attributes);
    }

	public function down() {
		$this->dbforge->drop_table(USER_TRACK);
	}

}