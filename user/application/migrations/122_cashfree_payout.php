<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_cashfree_payout extends CI_Migration 
{
	public function up() {
		//up script
		$fields = array(
			'beneficiary_id' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => TRUE,
              	'default' => NULL
			)
		);
		if(!$this->db->field_exists('campaign_code', USER_BANK_DETAIL)){
			$this->dbforge->add_column(USER_BANK_DETAIL,$fields);
		}	
	}

	public function down() {
	}
}
