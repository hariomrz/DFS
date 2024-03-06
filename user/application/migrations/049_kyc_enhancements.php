<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Kyc_enhancements extends CI_Migration {

  public function up()
  {
  	$fields = array(
        'auto_pan_attempted' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'pan_verified',
                'null' => FALSE
        ),
        'auto_bank_attempted' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'is_bank_verified',
                'null' => FALSE
        )
	);
  	$this->dbforge->add_column(USER,$fields);

    $sql = "UPDATE ".$this->db->dbprefix(USER)." SET `auto_pan_attempted` = 1 WHERE `pan_verified` = 1;";
    $this->db->query($sql);
    $sql = "UPDATE ".$this->db->dbprefix(USER)." SET `auto_bank_attempted` = 1 WHERE `is_bank_verified` = 1;";
    $this->db->query($sql);

  }

  public function down()
  {
	//down script 
	 $this->dbforge->drop_column(USER, 'auto_pan_attempted');
   $this->dbforge->drop_column(USER, 'auto_bank_attempted');
  }
}