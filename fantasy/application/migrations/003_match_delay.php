<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Match_delay extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'scoring_alert' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'custom_message',
                'null' => FALSE
        ),
        'score_verified' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'scoring_alert',
                'null' => FALSE
        ),
        'squad_verified' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'score_verified',
                'null' => FALSE
        ),
        'delay_by_admin' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'squad_verified',
                'null' => FALSE
        ),
        'notify_by_admin' => array(
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
  	$this->dbforge->drop_column(SEASON, 'scoring_alert');
  	$this->dbforge->drop_column(SEASON, 'score_verified');
  	$this->dbforge->drop_column(SEASON, 'squad_verified');
    $this->dbforge->drop_column(SEASON, 'delay_by_admin');
    $this->dbforge->drop_column(SEASON, 'notify_by_admin');
  }
}