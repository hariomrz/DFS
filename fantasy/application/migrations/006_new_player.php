<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_player extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'is_new' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_published',
                'null' => FALSE
        )
	  );
	  $this->dbforge->add_column(PLAYER_TEAM,$fields);
  }

  public function down()
  {
	  //down script
  	$this->dbforge->drop_column(PLAYER_TEAM, 'is_new');
  }
}