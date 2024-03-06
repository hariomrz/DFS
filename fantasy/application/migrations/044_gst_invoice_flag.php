<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gst_invoice_flag extends CI_Migration {

	public function up() {
		//add gst invoice sent flag
		$column = array(
			'is_invoice_sent' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => 0
			)
		);
		$this->dbforge->add_column(CONTEST,$column);

		$sql = "UPDATE ".$this->db->dbprefix(CONTEST)." SET is_invoice_sent = '1' 
		WHERE 
		status ='3' 
		and currency_type = '1' 
		and is_invoice_sent='0'";
	  	$this->db->query($sql);

	}

	public function down() {
		// $this->dbforge->drop_column(CONTEST, 'is_invoice_sent');
	}

}