<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Upi_field extends CI_Migration{

	public function up(){

		$cms_custom_field = array(
				'upi_id' => array(
                'type' => 'varchar',
                'constraint' => 50,
				'default'=>NULL,
				'after'=>'branch_name'
			),
		); 
		$this->dbforge->add_column(USER_BANK_DETAIL,$cms_custom_field);
	

}
	public function down(){
		$this->dbforge->drop_column(USER_BANK_DETAIL, 'upi_id');
	}
}
