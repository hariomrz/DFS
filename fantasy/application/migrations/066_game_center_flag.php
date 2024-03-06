<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Game_center_flag extends CI_Migration {

	public function up() {
		//up script
		$gc_fields = array(
	        'is_gc' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
	        )
		);
	  	$this->dbforge->add_column(COLLECTION_MASTER, $gc_fields);
	}

	public function down() {
		//$this->dbforge->drop_column(COLLECTION_MASTER, 'is_gc')
	}

}