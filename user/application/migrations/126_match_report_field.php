<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_match_report_field extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'season_game_uid' => array(
                'type' => 'TINYINT',
                'constraint' => 12,
                'default' => NULL,
                'after' => 'collection_master_id'
              
        )
	);
	$this->dbforge->add_column(MATCH_REPORT,$fields);
  }

  public function down()
  {
	//down script 
	//$this->dbforge->drop_column(MATCH_REPORT, 'season_game_uid');
  }
}