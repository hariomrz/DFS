<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Contest_sponsor_add extends CI_Migration {

public function up() {
//up script
		$fields = array(
                'sponsor_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE
				),
				'sponsor_logo' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE
				),
				'sponsor_link' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				),
				'video_link' => array(
					'type' => 'TEXT',
					'null' => TRUE
				)
		);
		$this->dbforge->add_column(CONTEST,$fields);

		//contest template
		$fields = array(
                'sponsor_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE
				),
				'sponsor_logo' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE
				),
				'sponsor_link' => array(
					'type' => 'VARCHAR',
					'constraint' => 255,
					'null' => TRUE
				)
		);
		$this->dbforge->add_column(CONTEST_TEMPLATE,$fields);
}

public function down() {
		$this->dbforge->drop_column(CONTEST, 'sponsor_name');
		$this->dbforge->drop_column(CONTEST, 'sponsor_logo');
		$this->dbforge->drop_column(CONTEST, 'sponsor_link');
		$this->dbforge->drop_column(CONTEST, 'video_link');
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'sponsor_link');
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'sponsor_logo');
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'sponsor_name');
	}

}