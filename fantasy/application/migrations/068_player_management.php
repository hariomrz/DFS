<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Player_management extends CI_Migration {

	public function up()
	{
		$fields = array(
			'country' => array(
				'type' => 'VARCHAR',
				'constraint' => 150,
				'default' => NULL,
				
			),
			'image' => array(
				'type' => 'text',
				'default' => NULL
			),
			
		);
		if(!$this->db->field_exists('country', PLAYER)){
			$this->dbforge->add_column(PLAYER,$fields);
		}

		if(!$this->db->field_exists('image', PLAYER)){
			$this->dbforge->add_column(PLAYER,$fields);
		}


    }

	public function down()
	{
		//$this->dbforge->drop_column(PLAYER, 'country');
		//$this->dbforge->drop_column(PLAYER, 'image');
	}

}
