<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_signup_banner extends CI_Migration {

	public function up() {
		$sql = "update ".$this->db->dbprefix(BANNER_TYPE)." set status=0 WHERE banner_type_id IN(3,5)";
		$this->db->query($sql);
	}

	public function down() {
		$sql = "update ".$this->db->dbprefix(BANNER_TYPE)." set status=1 WHERE banner_type_id IN(3,5)";
		$this->db->query($sql);
	}

}