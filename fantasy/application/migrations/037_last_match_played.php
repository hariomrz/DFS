<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Last_match_played extends CI_Migration {

	public function up() {
		//up script
		$fields = array(
			'last_match_played' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => 0
			)
		);
		$this->dbforge->add_column(PLAYER_TEAM,$fields);
	}

	public function down() {
		//$this->dbforge->drop_column(PLAYER_TEAM, 'last_match_played');
	}

}