<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock_type extends CI_Migration {

    function __construct() {
      $this->db_stock =$this->load->database('stock_db',TRUE);
      $this->stock_forge = $this->load->dbforge($this->db_stock, TRUE);
    }

    public function up() {

        $fields = array(
            'stock_type_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
                'null' => FALSE
            ),             
            'type' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1
            ), 
             'name' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE

            ),                     
            'config_data' => array(
                'type' => 'json',
                'null' => TRUE,
                'default' => NULL
            ),
            'status' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => FALSE,
                'comment' => '0 - In Active, 1 - Active'
            ),
            'market_id' => array(
                'type' => 'INT',
                'constraint' => 9,
                'null' => FALSE
            ),   
            'stock_limit' => array(
                'type' => 'INT',
                'constraint' => 3,
                'null' => FALSE,
                'default' => 50
            ),   

           
        );

        $attributes = array('ENGINE' => 'InnoDB');
        $this->stock_forge->add_field($fields);
        $this->stock_forge->add_key('stock_type_id',TRUE);
        $this->stock_forge->create_table(STOCK_TYPE ,FALSE,$attributes); 

        $result = $this->db->select('*')->from(STOCK_TYPE)->where('type',1)->get()->row_array();
        if(empty($result)){
            $data = array(
                'type' => 1,
                'name'=>'Stock Fantasy',
                'config_data' => json_encode(array(
                                    'tc' => 10,
                                    'b' => 9,
                                    's' => 9,
                                )),
                'status' => 1,
                'stock_limit'=>100,
                'market_id' => 1
            );

            $this->db_stock->insert(STOCK_TYPE,$data);
        }

        $result = $this->db->select('*')->from(STOCK_TYPE)->where('type',2)->get()->row_array();
        if(empty($result)){
            $data = array(
                'type' => 2,
                'name'=>'Stock Equity',
                'config_data' => json_encode(array('tc' => 10,'b' => 9,'s' => 9,)),
                'status' => 1,
                'stock_limit'=>100,
                'market_id' => 1
            );
            $this->db_stock->insert(STOCK_TYPE,$data);
        }

        $result = $this->db->select('*')->from(STOCK_TYPE)->where('type',3)->get()->row_array();
        if(empty($result)){
            $data = array(
                'type' => 3,
                'name'=>'Stock Predict',
                 'stock_limit'=>100,
                'config_data' => json_encode(array('min' => 11,'max'=>11,'b' => 11,'s' => 11)),
                'status' => 1,
                'market_id' => 1
            );
            $this->db_stock->insert(STOCK_TYPE,$data);
        }


        //remove config data from market table
        if($this->db->field_exists('config_data', MARKET)){
          $sql="ALTER TABLE ".$this->db_stock->dbprefix(MARKET)." DROP `config_data`;";
          $this->db_stock->query($sql);
        }

    }
}