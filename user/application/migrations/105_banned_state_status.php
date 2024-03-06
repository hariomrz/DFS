<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_banned_state_status extends CI_Migration 
{
	public function up() {
		//up script
		$fields = array(
			'bs_status' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
              	'default' => NULL
			)
		);
		if(!$this->db->field_exists('bs_status', USER)){
			$this->dbforge->add_column(USER,$fields);
		}	
	}

	public function down() {
		//$this->dbforge->drop_column(USER, 'bs_status');
	}
}
