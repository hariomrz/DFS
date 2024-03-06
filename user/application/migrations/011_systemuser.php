<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Systemuser extends CI_Migration {

  public function up()
  {
      $fields = array(
        'bot' => array(
                'name' => 'is_systemuser',
                'type' => 'TINYINT'
        )
      );
      $this->dbforge->modify_column(USER, $fields);
  }

  public function down()
  {
	  $fields = array(
      'is_systemuser' => array(
              'name' => 'bot',
              'type' => 'TINYINT'
      )
    );
    $this->dbforge->modify_column(USER, $fields);
  }
}