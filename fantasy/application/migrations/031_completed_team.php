<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Completed_team extends CI_Migration {

	public function up() {
		//up script
		$this->dbforge->add_field(array(
			'team_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'collection_master_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'lineup_master_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'user_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'team_data' => array(
				'type' => 'JSON',
				'null' => FALSE
			),
			'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			)
		));
		$this->dbforge->add_key('team_id', TRUE);
		$this->dbforge->create_table(COMPLETED_TEAM);

		$sql = "ALTER TABLE ".$this->db->dbprefix(COMPLETED_TEAM)." ADD UNIQUE (collection_master_id,lineup_master_id,user_id);";
		$this->db->query($sql);
	}

	public function down() {
		$this->dbforge->drop_table(COMPLETED_TEAM);
	}

}