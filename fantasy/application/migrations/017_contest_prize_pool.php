<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Contest_prize_pool extends CI_Migration {

public function up() {
//up script
		$fields = array(
                'max_prize_pool' => array(
					'type' => 'DECIMAL',
					'constraint' => '10,2',
					'default' => '0.00',
					'null' => TRUE,
					'after'=>'prize_pool'
				)
		);
		$this->dbforge->add_column(CONTEST,$fields);

		//contest template
		$fields = array(
                'max_prize_pool' => array(
					'type' => 'DECIMAL',
					'constraint' => '10,2',
					'default' => '0.00',
					'null' => TRUE,
					'after'=>'prize_pool'
				)
		);
		$this->dbforge->add_column(CONTEST_TEMPLATE,$fields);
}

public function down() {
		$this->dbforge->drop_column(CONTEST, 'max_prize_pool');
		$this->dbforge->drop_column(CONTEST_TEMPLATE, 'max_prize_pool');
	}

}