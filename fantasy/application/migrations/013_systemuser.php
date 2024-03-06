<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Systemuser extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'is_systemuser' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'team_data',
                'null' => FALSE
        )
  	);
  	$this->dbforge->add_column(LINEUP_MASTER,$fields);

    $fields = array(
        'total_system_user' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'total_user_joined'
        )
    );
    $this->dbforge->add_column(CONTEST,$fields);

    $fields = array(
        'playing_eleven_confirm' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'playing_announce',
                'null' => FALSE
        )
    );
    $this->dbforge->add_column(SEASON,$fields);
  }

  public function down()
  {
	//down script
  	$this->dbforge->drop_column(LINEUP_MASTER, 'is_systemuser');
    $this->dbforge->drop_column(CONTEST, 'total_system_user');
    
    $this->dbforge->drop_column(SEASON, 'playing_eleven_confirm');
  }
}