<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Prize_reset extends CI_Migration {

	public function up() {
		//up script
	  	$fields = array(
	            'is_prize_reset' => array(
	                    'type' => 'TINYINT',
	                    'constraint' => 1,
	                    'default' => 0,
	                    'after' => 'is_pdf_generated',
	                    'null' => FALSE
	            )
	  	);
	  	$this->dbforge->add_column(CONTEST,$fields);
	}

	public function down() {
		$this->dbforge->drop_column(CONTEST, 'is_prize_reset');
	}

}