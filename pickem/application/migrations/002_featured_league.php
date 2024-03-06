<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Featured_league extends CI_Migration {

  public function up() {
    //up script
    $fields = array(
            'is_featured' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'updated_date'
            ),
          
    );
    if(!$this->db->field_exists('is_featured', LEAGUE)){
      $this->dbforge->add_column(LEAGUE,$fields);
    }
	}

	public function down() {

	}

}