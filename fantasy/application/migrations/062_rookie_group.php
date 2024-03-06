<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rookie_group extends CI_Migration {

	public function up()
	{
			//up script
		if (!$this->db->field_exists('is_default', MASTER_GROUP))	
		{
        	$fields = array(
				'is_default' => array(
						'type' => 'TINYINT',
						'constraint' => 1,
						'default' => 0,
						'null' => FALSE
				),
			);
			  $this->dbforge->add_column(MASTER_GROUP,$fields);

			$insert_data = array(
				"group_name" => "Rookie Contest",
				"description" => "",
				"icon" => "rookie_contest.png",
				"is_private" => 0,
				"status" => 1,
				"is_default" => 1
			);
	
			$this->db->insert(MASTER_GROUP,$insert_data);
		}
	
	

       


	  	
    }

	public function down()
	{
		//down script 
		//$this->dbforge->drop_column(MASTER_GROUP, 'is_default');
	}

}
