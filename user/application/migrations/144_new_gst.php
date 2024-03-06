<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_gst extends CI_Migration{

  public function up(){
    //add invoice_type
    $fields = array(
      'invoice_type' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 0,
        'comment' => '0-Contest(Old),1-Deposit(New)',
        'after' => 'invoice_id',
        'null' => FALSE
      )
    );
    if(!$this->db->field_exists('invoice_type', GST_REPORT)){
      $this->dbforge->add_column(GST_REPORT,$fields);
    }

    //add gst_rate
    $fields = array(
      'gst_rate' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'default'=>'18.0',
        'after' => 'rake_amount',
        'comment' => 'GST percentage',
        'null' => FALSE
      )
    );
    if(!$this->db->field_exists('gst_rate', GST_REPORT)){
      $this->dbforge->add_column(GST_REPORT,$fields);
    }

    //add txn_type in report table
    $fields = array(
      'txn_type' => array(
        'type' => 'INT',
        'constraint' => 1,
        'default' => 1,
        'comment' => '1-Contest,2-Deposit(User),3-Deposit(Admin),4-Promocode,5-Deal',
        'after' => 'scheduled_date',
        'null' => FALSE
      )
    );
    if(!$this->db->field_exists('txn_type', GST_REPORT)){
      $this->dbforge->add_column(GST_REPORT,$fields);
    }

    //add gst_number in report table
    $fields = array(
      'gst_number' => array(
        'type' => 'VARCHAR',
        'constraint' => 255,
        'null' => true
      )
    );
    if(!$this->db->field_exists('gst_number', GST_REPORT)){
      $this->dbforge->add_column(GST_REPORT,$fields);
    }

    //add gst_number in user table
    $fields = array(
      'gst_number' => array(
        'type' => 'VARCHAR',
        'constraint' => 255,
        'null' => true
      )
    );
    if(!$this->db->field_exists('gst_number', USER)){
      $this->dbforge->add_column(USER,$fields);
    }

    

    //add is_process_gst in order table
    $fields = array(
      'is_process_gst' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 0,
        'comment' => '0:not process,1:processed,2:old gst data'
      )
    );
    if(!$this->db->field_exists('is_process_gst', ORDER)){
      $this->dbforge->add_column(ORDER,$fields);
    }
  }

  public function down(){
    //down script
  }
}
