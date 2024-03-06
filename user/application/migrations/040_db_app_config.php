<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_db_app_config extends CI_Migration {

  public function up()
  {

    $fields = array(
      'config_id' => array(
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
      'key_name' => array(
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => FALSE,
      ),
      'key_value' => array(
        'type' => 'VARCHAR',
        'constraint' => 255,
        'comment' => '0-Disable,1-Enable,custom data'
      ),
      'custom_data' => array(
        'type' => 'JSON',
        'null' => TRUE,
        'default' => NULL
      )
    );

    $attributes = array('ENGINE' => 'InnoDB');
    $this->dbforge->add_field($fields);
    $this->dbforge->add_key('config_id',TRUE);
    $this->dbforge->create_table(APP_CONFIG ,FALSE,$attributes);

    $sql = "ALTER TABLE ".$this->db->dbprefix(APP_CONFIG)." ADD UNIQUE(`key_name`)";
    $this->db->query($sql);
  }

  public function down()
  {
  	//down script 
  	$this->dbforge->drop_table(APP_CONFIG);
  }
}