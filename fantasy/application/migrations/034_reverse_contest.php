<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Reverse_contest extends CI_Migration {

public function up() {

	//up script
	$fields = array(
			'is_reverse' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => 0
			)
		);
	$this->dbforge->add_column(LINEUP_MASTER,$fields);
	
	//up script
	$fields = array(
            'is_reverse' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => 0
			)
	);
	$this->dbforge->add_column(CONTEST_TEMPLATE,$fields);

	//contest template
	$fields = array(
            'is_reverse' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => 0
			)
	);
	$this->dbforge->add_column(CONTEST,$fields);
}

public function down() {
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'is_reverse');
		$this->dbforge->drop_column(CONTEST, 'is_reverse');
		$this->dbforge->drop_column(LINEUP_MASTER, 'is_reverse');
	}

}