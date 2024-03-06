<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Private_contest_enhancement extends CI_Migration {

  public function up()
  {
  	$sql = "ALTER TABLE `vi_contest` CHANGE `contest_description` `contest_description` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";

  	$this->db->query($sql);


  	// $this->dbforge->query("ALTER TABLE `vi_pickem_master` CHANGE `result` `result` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
   //  $update_field = array(
   //    'contest_description' => array(
   //            'type' => 'varchar',
   //            'constraint' => 200,
   //    )
   //  );
    // $this->dbforge->modify_column(CONTEST, $update_field);
  }

  public function down()
  {
    // $update_field = array(
    //   'contest_description' => array(
    //           'type' => 'varchar',
    //           'constraint' => 100,
    //   )
    // );
    // $this->dbforge->modify_column(CONTEST, $update_field);
  }
}