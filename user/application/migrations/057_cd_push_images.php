<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cd_push_images extends CI_Migration {

	public function up() {

$field = array(
            'header_image' => array(
                'type' => 'VARCHAR',
                'null' => TRUE,
                'default' => NULL,
                'constraint' => 100
                
            ),
			'body_image' => array(
                'type' => 'VARCHAR',
                'null' => TRUE,
				'default' => NULL,
                'constraint' => 100
              ),
		);
        $this->dbforge->add_column(CD_EMAIL_TEMPLATE, $field);
    }


	public function down() {
		$this->dbforge->drop_column(CD_EMAIL_TEMPLATE, 'header_image');
		$this->dbforge->drop_column(CD_EMAIL_TEMPLATE, 'body_image');
	}

}