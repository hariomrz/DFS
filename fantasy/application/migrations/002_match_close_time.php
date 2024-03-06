<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Match_close_time extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
        'match_closure_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        )
	);
	$this->dbforge->add_column(SEASON,$fields);
  }

  public function down()
  {
	//down script
  	$this->dbforge->drop_column(SEASON, 'match_closure_date');
  }
}