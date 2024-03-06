<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Banner_management_filed extends CI_Migration {

  public function up() {
    //up script
    $fields = array(
            'game_type_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                    'after' => 'banner_type_id'
            )
    );
        if(!$this->db->field_exists('game_type_id', BANNER_MANAGEMENT)){
            $this->dbforge->add_column(BANNER_MANAGEMENT,$fields);
        }
	}

	public function down() {
    //down script
    $this->dbforge->drop_column(BANNER_MANAGEMENT, 'game_type_id');
  
	}

}