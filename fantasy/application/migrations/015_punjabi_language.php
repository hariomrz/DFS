<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Punjabi_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `pun_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `pun_name` = 'ਇੱਕ ਦੋਸਤ ਨੂੰ ਵੇਖੋ' WHERE `banner_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `pun_name` = 'ਜਮ੍ਹਾ ਕਰੋ' WHERE `banner_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `pun_name` = 'ਸਾਇਨ ਅਪ' WHERE `banner_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." ADD `pun_scoring_category_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_scoring_category_name`;";
	  	$this->db->query($sql);

	  	$sql = "update ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set pun_scoring_category_name=scoring_category_name;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_RULES)." ADD `pun_score_position` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_score_position`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `pun_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `ben_display_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'ਬੇਸੈਬਲ' WHERE `sports_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'ਕ੍ਰਿਕਟ' WHERE `sports_id` = 7;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'SOCCER' WHERE `sports_id` = 5;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'ਫੁੱਟਬਾਲ' WHERE `sports_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'ਬਾਸਕਟਬਾਲ' WHERE `sports_id` = 4;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'ਕਬੱਡੀ' WHERE `sports_id` = 8;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'GOLF' WHERE `sports_id` = 9;";
	  	$this->db->query($sql);	  

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'ਬੈਡਮਿੰਟਨ' WHERE `sports_id` = 10;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `pun_display_name` = 'ਟੈਨਿਸ' WHERE `sports_id` = 11;";
	  	$this->db->query($sql);

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'pun_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'pun_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'pun_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'pun_display_name');
	}

}