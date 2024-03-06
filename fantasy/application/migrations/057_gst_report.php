<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gst_report extends CI_Migration {

	public function up()
	{
		//up script
		$fields = array(
	        'is_gst_report' => array(
	                'type' => 'TINYINT',
	                'constraint' => 1,
	                'default' => 0,
	                'null' => FALSE
	        ),
		);
	  	$this->dbforge->add_column(CONTEST,$fields);

	  	$this->dbforge->drop_column(CONTEST, 'is_invoice_sent');
    }

	public function down()
	{
		//down script 
		//$this->dbforge->drop_column(CONTEST, 'is_gst_report');
	}

}
