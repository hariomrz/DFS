<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Promo_code extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
        'mode' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'null' => FALSE,
            'default' => 0,
            'comment' => '0  - Public, 1 - Private'
        ),             
        'description' => array(
            'type' => 'text',
            'default'=>NULL,
        )
    );
    $this->dbforge->add_column(PROMO_CODE, $fields);

  }

  public function down()
  {
 	//down script
 	//$this->dbforge->drop_column(PROMO_CODE, 'mode');
  }
}