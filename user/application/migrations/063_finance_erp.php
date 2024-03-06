<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_finance_erp extends CI_Migration {

  public function up()
  {

    $fields = array(
      'category_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'name' => array(
        'type' => 'VARCHAR',
        'constraint' => 255,
        'null' => FALSE,
      ),
      'type' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 1,
        'null' => FALSE,
        'comment' => '0-Expenses,1-Income,2-Liabilities'
      ),
      'is_custom' => array(
        'type' => 'TINYINT',
        'constraint' => 1,
        'default' => 0,
        'null' => FALSE
      ),
      'added_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      ),
      'modified_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      )
    );

    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('category_id',TRUE);
    $this->dbforge->create_table(FINANCE_CATEGORY ,FALSE,$attributes);

    $sql = "ALTER TABLE ".$this->db->dbprefix(FINANCE_CATEGORY)." ADD INDEX(type)";
    $this->db->query($sql);

    //save default category
    $record_sql = "INSERT INTO ".$this->db->dbprefix(FINANCE_CATEGORY)." (`category_id`, `name`, `type`, `is_custom`, `added_date`, `modified_date`) VALUES (1, 'Amount Disbursed', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(2, 'Real Cash deposited by Admin', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(3, 'Winning Deposited by Admin', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(4, 'Real cash Referral Distributed', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(5, 'Coin Redeemed for Real Cash', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(6, 'Bots Joining Paid', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(7, 'By Promo Code discounted', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(8, 'By Deals Deposit', 0, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(9, 'Platform Fee (Site Rake)', 1, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(10, 'Bots Winning', 1, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(11, 'In App Purchase', 1, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(12, 'Private Contest (Site Rake)', 1, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(13, 'Winning Wallet Total', 2, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25'),(14, 'Total Deposits', 2, 0, '2021-02-10 08:23:25', '2021-02-10 08:23:25');";
    $this->db->query($record_sql);

    //finance dashboard
    $fields = array(
      'finance_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'auto_increment' => TRUE,
        'null' => FALSE
      ),
      'category_id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'null' => FALSE
      ),
      'amount' => array(
        'type' => 'DECIMAL',
        'constraint' => '10,2',
        'default'=>'0.00'
      ),
      'description' => array(
        'type' => 'TEXT',
        'null' => TRUE,
        'default'=>NULL,
      ),
      'record_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      ),
      'added_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      ),
      'modified_date' => array(
        'type' => 'DATETIME',
        'null' => FALSE
      )
    );

    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('finance_id',TRUE);
    $this->dbforge->create_table(FINANCE_DASHBOARD ,FALSE,$attributes);

    $sql = "ALTER TABLE ".$this->db->dbprefix(FINANCE_DASHBOARD)."  ADD INDEX(category_id)";
    $this->db->query($sql);
  }

  public function down()
  {
  	//down script 
  	$this->dbforge->drop_table(FINANCE_CATEGORY);
    $this->dbforge->drop_table(FINANCE_DASHBOARD);
  }
}