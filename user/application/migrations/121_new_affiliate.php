<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_new_affiliate extends CI_Migration 
{
	public function up() {
		//up script
		$fields = array(
			'campaign_code' => array(
				'type' => 'VARCHAR',
				'constraint' => 12,
				'null' => TRUE,
              	'default' => NULL
			)
		);
		if(!$this->db->field_exists('campaign_code', USER)){
			$this->dbforge->add_column(USER,$fields);
		}	
	}

	public function down() {
		$this->dbforge->drop_column(USER, 'campaign_code');
	}
}
