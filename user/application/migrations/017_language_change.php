<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Language_change extends CI_Migration {

	public function up() {
		//up script
        $sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `en_subject` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `notification_type`;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `hi_subject` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `notification_type`;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `guj_subject` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `notification_type`;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `fr_subject` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `notification_type`;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `ben_subject` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `notification_type`;";
        $this->db->query($sql);

        $sql = "ALTER TABLE ".$this->db->dbprefix(NOTIFICATION_DESCRIPTION)." ADD `pun_subject` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `notification_type`;";
        $this->db->query($sql);
	}

	public function down() {
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'en_subject');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'hi_subject');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'guj_subject');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'fr_subject');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'ben_subject');
		$this->dbforge->drop_column(NOTIFICATION_DESCRIPTION, 'pun_subject');	
	}

}