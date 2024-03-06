<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Payout_process_filed extends CI_Migration {

  public function up() {
    //up script
    $fields = array(
            'payout_processed' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                    'after' => 'custom_data'
            )
    );
        if(!$this->db->field_exists('payout_processed', ORDER)){
            $this->dbforge->add_column(ORDER,$fields);
        }
	}

	public function down() {
    //down script
    // $this->dbforge->drop_column(ORDER, 'payout_processed');
  
	}

}