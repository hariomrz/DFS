<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Batting_order extends CI_Migration {

	public function up()
	{
		//up script
		$fields = array(
	        'batting_team_uid' => array(
	                'type' => 'VARCHAR',
	                'constraint' => 100,
	                'default' => NULL
	        ),
            'team_batting_order' => array(
                'type' => 'JSON',
                'default' => NULL
        ),
		);
	  	$this->dbforge->add_column(SEASON,$fields);

	  
    }

	public function down()
	{
		//down script 
        //$this->dbforge->drop_column(SEASON, 'batting_team_uid');
       // $this->dbforge->drop_column(SEASON, 'team_batting_order');
	}

}
