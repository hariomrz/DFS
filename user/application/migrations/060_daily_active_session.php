<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Daily_active_session extends CI_Migration {

	public function up() {
		//up script
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'user_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'session_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			),
			'total_seconds' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'device_detail' => array(
				'type' => 'JSON',
				'null' => FALSE
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('daily_active_session');

		$sql = "ALTER TABLE ".$this->db->dbprefix('daily_active_session')." ADD UNIQUE (user_id, session_date);";
		$this->db->query($sql);
	}

	public function down() {
		$this->dbforge->drop_table('daily_active_session');
	}

}