<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Referral_new_development extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
        'completed_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        )
	);
	$this->dbforge->add_column(CONTEST,$fields);
  }

  public function down()
  {
	//down script
  	$this->dbforge->drop_column(CONTEST, 'completed_date');
  }
}