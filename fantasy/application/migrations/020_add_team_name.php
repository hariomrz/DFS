<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_team_name extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
        'team_short_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 100,
          'null' => TRUE,
          'default' => NULL,
        )
	);
	$this->dbforge->add_column(LINEUP_MASTER,$fields);
  }

  public function down()
  {
	//down script
  	$this->dbforge->drop_column(LINEUP_MASTER, 'team_short_name');
  }
}