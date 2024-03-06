<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_pin_fixture extends CI_Migration {

	public function up()
	{
		$season_fields = array(
	        'is_pin_fixture' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
        		'comment' => "1 => pinned fixture, 0 => not pinned"
	        )
		);
	  	$this->dbforge->add_column(SEASON, $season_fields);
	}

	public function down()
	{
		//down script
		$this->dbforge->drop_column(SEASON, 'is_pin_fixture');
	}

}
