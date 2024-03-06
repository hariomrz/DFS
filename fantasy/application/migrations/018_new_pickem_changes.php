<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_pickem_changes extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'is_pickem_published' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => "0-Pending, 1- Published"
        )
	);
	$this->dbforge->add_column(SEASON,$fields);
  }

  public function down()
  {
	//down script
  	$this->dbforge->drop_column(SEASON, 'is_pickem_published');
  
  }
}