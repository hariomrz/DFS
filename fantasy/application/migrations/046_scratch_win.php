<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Scratch_win extends CI_Migration {

	public function up()
	{
		//up script
		$fields = array(
	        'is_scratchwin' => array(
	                'type' => 'TINYINT',
	                'constraint' => 1,
	                'default' => 0,
	                'null' => FALSE
	        ),
		);
	  	$this->dbforge->add_column(CONTEST,$fields);
	  	$this->dbforge->add_column(CONTEST_TEMPLATE,$fields);
    }

	public function down()
	{
		//down script 
		$this->dbforge->drop_column(CONTEST, 'is_scratchwin');
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'is_scratchwin');
	}

}
