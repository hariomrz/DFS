<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_User_self_exclusion extends CI_Migration {

    public function up() {
		$fields = array(
            'user_self_exclusion_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'max_limit' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE,
                'default' => 0
            ),
            'requested_max_limit' => array(
                'type' => 'INT',
                'constraint' => '11',
                'null' => FALSE,
                'default' => 0
            ), 
            'set_by' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => FALSE,
                'comment' => '1 - By User, 2 - By Admin'
            ),             
            'document' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            ),             
            'reason' => array(
                'type' => 'text',
                'default'=>NULL,
            ),
            'modified_date' => array(
                'type' => 'DATETIME',
                'null' => FALSE 
            )
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('user_self_exclusion_id',TRUE);
        $this->dbforge->create_table(USER_SELF_EXCLUSION ,FALSE,$attributes);   

    }

	public function down() {
        $this->dbforge->drop_table(USER_SELF_EXCLUSION);
	}

}
