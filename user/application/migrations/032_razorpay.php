<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_razorpay extends CI_Migration {

	public function up() {
		$fields = array(
	        'pg_order_id' => array(
	                'type' => 'VARCHAR',
	                'constraint' => 255,
	                'default' => NULL,
	                'after' => 'transaction_message',
	                'null' => TRUE
	        )
	  	);
	  	$this->dbforge->add_column(TRANSACTION,$fields);
	}

	public function down() {
		//down script
  		$this->dbforge->drop_column(TRANSACTION, 'pg_order_id');
	}

}