<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_promo_for_contest extends CI_Migration {

	public function up()
	{
		$sql = "ALTER TABLE `vi_promo_code` CHANGE `start_date` `start_date` DATETIME NOT NULL";
        $this->db->query($sql);

        $sql = "ALTER TABLE `vi_promo_code` CHANGE `expiry_date` `expiry_date` DATETIME NOT NULL";
        $this->db->query($sql);

		$fields = array(
	        'contest_unique_id' => array(
                'type' => 'VARCHAR',
				'constraint' => '255',
                'default' => 0,
                'null' => FALSE,
                'after' => 'added_date',
        		'comment' => "o => common for all contests, else for a particular contest"
	        ),
	        'max_usage_limit' => array(
                'type' => 'INT',
				'constraint' => '11',
                'default' => 0,
                'null' => FALSE,
                'comment' => "maximum time a promo code can be used"
	        )
		);
	  	$this->dbforge->add_column(PROMO_CODE, $fields);

	  	$fields = array(
	        'lmc_id' => array(
                'type' => 'INT',
				'constraint' => '11',
                'default' => 0,
                'null' => TRUE,
                'comment' => "lineup master contest id in case of contest join type promo code."
	        )
		);
	  	$this->dbforge->add_column(PROMO_CODE_EARNING, $fields);
	}

	public function down()
	{
		//down script 
		// $this->dbforge->drop_column(PROMO_CODE, 'contest_unique_id');
		// $this->dbforge->drop_column(PROMO_CODE, 'max_usage_limit');
		// $this->dbforge->drop_column(PROMO_CODE_EARNING, 'lmc_id');
	}

}
