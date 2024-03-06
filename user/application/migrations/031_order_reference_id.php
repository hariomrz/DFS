<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_order_reference_id extends CI_Migration {

	public function up() {
		$fields = array(
	        'reference_id' => array(
	                'type' => 'INT',
	                'constraint' => 11,
	                'default' => 0,
	                'after' => 'user_id',
	                'comment' => 'contest id or etc'
	        )
	  	);
	  	$this->dbforge->add_column(ORDER,$fields);
	}

	public function down() {
		//down script
  		$this->dbforge->drop_column(ORDER, 'reference_id');
	}

}