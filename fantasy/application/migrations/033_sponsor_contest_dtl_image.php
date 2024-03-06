<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sponsor_contest_dtl_image extends CI_Migration {

public function up() {
	//up script
	$fields = array(
            'sponsor_contest_dtl_image' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE
			)
	);
	$this->dbforge->add_column(CONTEST_TEMPLATE,$fields);

	//contest template
	$fields = array(
            'sponsor_contest_dtl_image' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE
			)
	);
	$this->dbforge->add_column(CONTEST,$fields);
}

public function down() {
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'sponsor_contest_dtl_image');
		$this->dbforge->drop_column(CONTEST, 'sponsor_contest_dtl_image');
	}

}