<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Geo_location extends CI_Migration {

    public function up() {

    	$fields = array(
	        'pos_code' => array(
                'type' => 'VARCHAR',
				'constraint' => '3',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE,
                
	        )
		);

		if(!$this->db->field_exists('pos_code', MASTER_COUNTRY)) {
		  $this->dbforge->add_column(MASTER_COUNTRY, $fields);
		}

		$counrty_codes = array(
			array (
                'master_country_id' => '1',
                'pos_code' => 'AFG'
                ),array (
                'master_country_id' => '101',
                'pos_code' => 'IND'
                ), array (
                'master_country_id' => '18',
                'pos_code' => 'BGD'
                ), array (
                'master_country_id' => '21',
                'pos_code' => 'BEL'
                ), array (
                'master_country_id' => '231',
                'pos_code' => 'USA'
                ),  array (
                'master_country_id' => '230',
                'pos_code' => 'GBR'
                ),  array (
                'master_country_id' => '102',
                'pos_code' => 'IDN'
                ),  array (
                'master_country_id' => '75',
                'pos_code' => 'FRA'
                ), 
		);
		$this->db->update_batch(MASTER_COUNTRY,$counrty_codes,'master_country_id');

		$fields = array(
	        'pos_code' => array(
                'type' => 'VARCHAR',
				'constraint' => '3',
				'character_set' => 'utf8 COLLATE utf8_general_ci',
				'null' => FALSE
                
	        )
		);

		if(!$this->db->field_exists('pos_code', MASTER_STATE)) {
		  $this->dbforge->add_column(MASTER_STATE, $fields);
		}

		$state_codes = array(
			array (
                'master_state_id' => '1',
                'pos_code' => 'AN'
                ),array (
                'master_state_id' => '2',
                'pos_code' => 'AP'
                ), array (
                'master_state_id' => '4',
                'pos_code' => 'AS'
                ), array (
                'master_state_id' => '21',
                'pos_code' => 'MP'
                ), array (
                'master_state_id' => '3',
                'pos_code' => 'AR'
                ) 
		);
		$this->db->update_batch(MASTER_STATE,$state_codes,'master_state_id');



	}

	public function down()
	{
		 // $this->dbforge->drop_column(MASTER_COUNTRY, 'pos_code');
		 // $this->dbforge->drop_column(MASTER_STATE, 'pos_code');
	}


}