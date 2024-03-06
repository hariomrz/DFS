<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Multibot_changes extends CI_Migration {

	public function up() {
		//up script
		$this->dbforge->add_field(array(
			'req_id' => array(
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
			'no_of_teams' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
				'default' => 0
			),
			'added_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			),
			'modified_date' => array(
				'type' => 'DATETIME',
				'null' => FALSE
			),
			'status' => array(
				'type' => 'TINYINT',
				'null' => FALSE,
				'constraint' => 1,
				'default' => 0,
				'comment' => "0-Pending,1-Success,2-Failed"
			),
			'pl_response' => array(
				'type' => 'JSON',
				'null' => TRUE
			)
		));
		$this->dbforge->add_key('req_id', TRUE);
		$this->dbforge->add_key('collection_master_id');
		$this->dbforge->create_table(BOT_REQ_HISTORY, TRUE);
		//$this->db->query('ALTER TABLE `vi_referral_leaderboard_day` ADD UNIQUE `unique_index` (`day_number`, `day_date`,`user_id`)');

		
	}

	public function down() {
		$this->dbforge->drop_table(BOT_REQ_HISTORY);
	}

}
