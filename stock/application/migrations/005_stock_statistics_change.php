<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock_statistics_change extends CI_Migration {

	public function up()
	{
		$fields = array(
			'previous_close' => array(
				'type' => 'Decimal',
				'constraint' => '10,2',
				'null' => FALSE,
				'default' => 0.00,
				
			),
			
			
		);
		if(!$this->db->field_exists('previous_close', STOCK)){
			$this->dbforge->add_column(STOCK,$fields);
		}


    }

	public function down()
	{
		//$this->dbforge->drop_column(STOCK, 'previous_close');
	}

}
