<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gst_report extends CI_Migration {

  public function up()
  {
  	//up script
  	$fields = array(
	    'invoice_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'auto_increment' => TRUE,
            'null' => FALSE
        ),
        'user_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => FALSE
        ),
	    'state_id' => array(
	      'type' => 'INT',
          'constraint' => 11,
          'null' => TRUE,
          'default' => 0
        ),
        'order_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'match_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'contest_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'lmc_id' => array(
          'type' => 'INT',
          'constraint' => 11,
          'null' => FALSE
        ),
        'user_name' => array(
	      'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
        ),
        'pan_no' => array(
          'type' => 'VARCHAR',
          'constraint' => 50,
          'null' => TRUE,
        ),
        'state_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => TRUE,
        ),
        'match_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
        ),
        'contest_name' => array(
          'type' => 'VARCHAR',
          'constraint' => 255,
          'null' => FALSE,
        ),
        'scheduled_date' => array(
          'type' => 'DATETIME',
          'null' => TRUE,
          'default' => NULL,
        ),
        'txn_date' => array(
            'type' => 'DATETIME',
            'null' => FALSE,
        ),
	    'txn_amount' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'site_rake' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'entry_fee' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'rake_amount' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'cgst' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'sgst' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'igst' => array(
            'type' => 'DECIMAL',
            'constraint' => '10,2',
            'null' => FALSE,
            'default' => 0.00
        ),
        'is_invoice_sent' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'null' => FALSE,
            'default' => '0'
        ),
        'status' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'null' => FALSE,
            'default' => '1'
        ),
        'date_added' => array(
            'type' => 'DATETIME',
            'null' => TRUE,
            'default' => NULL,
        ),
    );

    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('invoice_id', TRUE);
    $this->dbforge->create_table(GST_REPORT ,FALSE,$attributes); 
      
    $sql = "ALTER TABLE ".$this->db->dbprefix(GST_REPORT)." ADD UNIQUE KEY user_id (user_id,order_id);";
    $this->db->query($sql);

  }

  public function down()
  {
 	//down script
 	//$this->dbforge->drop_table(GST_REPORT);
  }
}