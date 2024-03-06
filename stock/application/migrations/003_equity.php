<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Equity extends CI_Migration {

    function __construct() {
      $this->db_stock =$this->load->database('stock_db',TRUE);
      $this->stock_forge = $this->load->dbforge($this->db_stock, TRUE);
    }

    public function up() {
        $data = array(array(
            'type' => 2,//equity
            'config_data' => json_encode(array(
                                'min' => 6,
                                'max' => 11,
                                'b' => 9,
                                's' => 9,
                            )),
            'status' => 1
        ),array(
            'type' => 3,//equity
            'config_data' => json_encode(array(
                                'min' => 1,
                                'max' => 10
                            )),
            'status' => 1
        )
    );

        $this->db_stock->insert_batch(STOCK_TYPE,$data);

        //add stock type
        //ALTER TABLE `vi_collection` ADD `stock_type` TINYINT(1) NOT NULL DEFAULT '1' AFTER `category_id`;
        $sql="ALTER TABLE ".$this->db_stock->dbprefix(COLLECTION)." ADD `stock_type` TINYINT(1) NOT NULL DEFAULT '1' AFTER `category_id`;";
        $this->db_stock->query($sql);
        
        $sql="ALTER TABLE ".$this->db_stock->dbprefix(CONTEST_TEMPLATE)." ADD `stock_type` INT(9) NULL DEFAULT '1' AFTER `group_id`;";
          $this->db_stock->query($sql);

          $sql="ALTER TABLE ".$this->db_stock->dbprefix(LINEUP_MASTER)." ADD `remaining_cap` DECIMAL(10,2) NULL DEFAULT '0' COMMENT 'in case of equity';";
          $this->db_stock->query($sql);

          //ALTER TABLE ".$this->db_stock->dbprefix(LINEUP_MASTER_CONTEST)." ADD `percent_change` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `last_score`;
          $sql="ALTER TABLE ".$this->db_stock->dbprefix(LINEUP_MASTER_CONTEST)." ADD `percent_change` DECIMAL(10,4) NOT NULL DEFAULT '0.0000' AFTER `last_score`;";
          $this->db_stock->query($sql);

                    //ALTER TABLE ".$this->db_stock->dbprefix(LINEUP_MASTER_CONTEST)." ADD `percent_change` DECIMAL(10,2) NOT NULL DEFAULT '0' AFTER `last_score`;
          $sql="ALTER TABLE ".$this->db_stock->dbprefix(LINEUP_MASTER_CONTEST)." ADD `last_percent_change` DECIMAL(10,2) NOT NULL DEFAULT '0.0000' AFTER `percent_change`;";
          $this->db_stock->query($sql);

          $sql = "ALTER TABLE `vi_stock` ADD `cap_type` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1 => large cap, 2 => midcap and 3 => smallcap' AFTER `logo`";
          $this->db_stock->query($sql);

          $sql = "ALTER TABLE `vi_stock` ADD `industry_id` INT NULL AFTER `status`";
          $this->db_stock->query($sql);
    }

}