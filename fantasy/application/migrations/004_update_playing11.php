<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_playing11 extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'is_updated_playing' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'delay_by_admin',
                'null' => FALSE
        )
        
	);
	$this->dbforge->add_column(SEASON,$fields);
  }

  public function down()
  {
	//down script
  	$this->dbforge->drop_column(SEASON, 'is_updated_playing');
  	
  }
}
