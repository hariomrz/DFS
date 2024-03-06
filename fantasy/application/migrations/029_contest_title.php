<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Contest_title extends CI_Migration {

public function up() {
	//up script
	$fields = array(
            'template_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE,
				'after'=>'template_name'
			)
	);
	$this->dbforge->add_column(CONTEST_TEMPLATE,$fields);

	//contest template
	$fields = array(
            'contest_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE,
				'after'=>'contest_name'
			)
	);
	$this->dbforge->add_column(CONTEST,$fields);
}

public function down() {
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'template_title');
		$this->dbforge->drop_column(CONTEST, 'contest_title');
	}

}