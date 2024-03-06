<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Live_stock_fantasy extends CI_Migration {

	 function __construct() {
      $this->db_stock =$this->load->database('stock_db',TRUE);
      $this->stock_forge = $this->load->dbforge($this->db_stock, TRUE);
    }

	public function up()
	{
		 $data = array(
            'type' => 4,
            'config_data' => json_encode(array(
                                'min' => 1,
                                'max' => 100,
                                'b' => 100,
                                's' => 100,
                            )),
            'status' => 1,
            'market_id'=>1,
            'name'=>'Live Stock Fantasy',
            'stock_limit'=>100
        );
        $this->db_stock->insert(STOCK_TYPE,$data);

       $sql = "ALTER TABLE `vi_contest` ADD `brokerage` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Brokerage on every buy or sell, for STOCK_TYPE ' AFTER `status`";
       $this->db_stock->query($sql);

       $sql = "ALTER TABLE `vi_contest_template` ADD `brokerage` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Brokerage on every buy or sell, for STOCK_TYPE ' AFTER `currency_type`";
       $this->db_stock->query($sql);

        $fields = array(
                    'transaction_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'auto_increment' => TRUE,
                        'null' => FALSE
                    ),             
                    'lineup_master_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),
                    'stock_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE
                    ),                        
                    'contest_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE,
                    ),
                    'lot_size' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => TRUE,
                        'comment' => 'No of Shares'
                    ),
                    'trade_value' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => '0.00',
                        'comment' => 'Price which stock was trade'
                    ),
                   'brokerage' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => '0.00',
                        'comment' => 'In percentage'
                    ),
                    'price' => array(
                        'price' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => '0.00',
                        
                    ),
                    'parent_id' => array(
                        'type' => 'INT',
                        'constraint' => 11,
                        'null' => FALSE,
                        'default'=>0,
                        'comment' => 'Trade transaction_id in case of brockerage'
                    ),

                    'closing_balance' => array(
                        'type' => 'DECIMAL',
                        'constraint' => '10,2',
                        'default' => '0.00',
                    ),
                    'type' => array(
                        'type' => 'TINYINT',
                        'constraint' => '1',
                        'default' => '1',
                        'null' => FALSE,
                        'comment' => '1 -Trade,2-Exit Partial,3=Exitall'
                    ),
                    'status' => array(
                        'type' => 'TINYINT',
                        'constraint' => '1',
                        'default' => '1',
                        'null' => FALSE,
                        'comment' => '0 - Pending, 1 - Complete'
                    ),
                    'added_date' => array(
                        'type' => 'DATETIME',
                        'null' => TRUE,
                    ),
                    'modified_date' => array(
                        'type' => 'DATETIME',
                        'null' => TRUE,


                      
                    )

                );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('transaction_id',TRUE);
        $this->stock_forge->create_table(USER_TRADE ,FALSE,$attributes); 

        $sql = "ALTER TABLE `vi_user_trade` CHANGE `modified_date` `modified_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP";
         $this->db_stock->query($sql);

    }

	public function down()
	{
		//$this->dbforge->drop_column(CONTEST, 'brokerage');
        //$this->dbforge->drop_table(USER_TRADE);
        
	}

}
