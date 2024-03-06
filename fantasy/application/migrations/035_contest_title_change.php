<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Contest_title_change extends CI_Migration {

	public function up() {
        //up script
       
  		$sql = "ALTER TABLE ".$this->db->dbprefix(CONTEST)." CHANGE `contest_title` `contest_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
		  $this->db->query($sql);
		  
  		$sql = "ALTER TABLE ".$this->db->dbprefix(CONTEST_TEMPLATE)." CHANGE `template_title` `template_title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
	  	$this->db->query($sql);
	}

	public function down() {
		
	}

}