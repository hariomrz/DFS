<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Admin_rol_manage extends CI_Migration {

	public function up() {
		//up script
	   	$sql = "ALTER TABLE ".$this->db->dbprefix(ADMIN)." ADD `access_list` JSON NOT NULL";
        $this->db->query($sql);
    }

	public function down() {
		$this->dbforge->drop_column(ADMIN, 'access_list');
	}

}