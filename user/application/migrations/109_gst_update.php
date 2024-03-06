<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_gst_update extends CI_Migration {

	public function up()
	{
		$fields = array(
	        'min_size' => array(
                'type' => 'INT',
				'constraint' => '255',
                'default' => NULL,
                'null' => TRUE,
	        ),
	        'max_size' => array(
                'type' => 'INT',
				'constraint' => '255',
                'default' => NULL,
                'null' => TRUE,
	        ),
	        'total_user_joined' => array(
                'type' => 'INT',
				'constraint' => '100',
                'default' => NULL,
                'null' => TRUE,
	        ),
	        'prize_pool' => array(
                'type' => 'FLOAT',
                'default' => 0,
                'null' => FALSE,
	        ),
		);

	  	$this->dbforge->add_column(GST_REPORT, $fields);
	}

	public function down()
	{
		//down script 
		$this->dbforge->drop_column(GST_REPORT, 'min_size');
		$this->dbforge->drop_column(GST_REPORT, 'max_size');
		$this->dbforge->drop_column(GST_REPORT, 'total_user_joined');
		$this->dbforge->drop_column(GST_REPORT, 'prize_pool');
	}

}
