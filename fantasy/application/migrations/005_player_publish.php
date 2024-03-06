<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Player_publish extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'feed_verified' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_deleted',
                'null' => FALSE
        ),
        'is_published' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'feed_verified',
                'null' => FALSE
        )
	  );
	  $this->dbforge->add_column(PLAYER_TEAM,$fields);
  }

  public function down()
  {
	  //down script
  	$this->dbforge->drop_column(PLAYER_TEAM, 'feed_verified');
  	$this->dbforge->drop_column(PLAYER_TEAM, 'is_published');
  }
}