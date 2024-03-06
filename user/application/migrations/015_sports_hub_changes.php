<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sports_hub_changes extends CI_Migration {

  public function up() {
    //up script
    $fields = array(
            'is_featured' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'display_order'
            ),
            'allowed_sports' => array(
                    'type' => 'JSON',
                    'default' => NULL,
                    'after' => 'is_featured'
            )
    );
    $this->dbforge->add_column(SPORTS_HUB,$fields);
	}

	public function down() {
    //down script
    $this->dbforge->drop_column(SPORTS_HUB, 'is_featured');
    $this->dbforge->drop_column(SPORTS_HUB, 'allowed_sports');
	}

}