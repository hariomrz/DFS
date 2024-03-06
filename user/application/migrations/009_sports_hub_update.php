<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sports_hub_update extends CI_Migration {

    public function up() {
      //up script
      $fields = array(
              'display_order' => array(
                      'type' => 'INT',
                      'constraint' => 11,
                      'default' => 0,
                      'after' => 'status'
              )
      );
      $this->dbforge->add_column(SPORTS_HUB,$fields);
    }

    public function down() {
      //down script
      $this->dbforge->drop_column(SPORTS_HUB, 'display_order');
    }

}
