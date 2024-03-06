<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Basic_db extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'is_key' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'right_ids',
                'null' => FALSE
        )
	);
	$this->dbforge->add_column(ADMIN_ROLES_RIGHTS,$fields);
  }

  public function down()
  {
	//down script 
	$this->dbforge->drop_column(ADMIN_ROLES_RIGHTS, 'is_key');
  }
}