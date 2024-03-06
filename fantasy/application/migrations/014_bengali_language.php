<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bengali_language extends CI_Migration {

	public function up() {
		//up script
  		$sql = "ALTER TABLE ".$this->db->dbprefix(BANNER_MANAGEMENT)." ADD `ben_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `ben_name` = 'একটা বন্ধু উল্লেখ কর' WHERE `banner_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `ben_name` = 'আমানত' WHERE `banner_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(BANNER_MANAGEMENT)." SET `ben_name` = 'নিবন্ধন করুন' WHERE `banner_id` = 3;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." ADD `ben_scoring_category_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_scoring_category_name`;";
	  	$this->db->query($sql);

	  	$sql = "update ".$this->db->dbprefix(MASTER_SCORING_CATEGORY)." set ben_scoring_category_name=scoring_category_name;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SCORING_RULES)." ADD `ben_score_position` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_score_position`;";
	  	$this->db->query($sql);

	  	$sql = "ALTER TABLE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." ADD `ben_display_name` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `fr_display_name`;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'বেসবল' WHERE `sports_id` = 1;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'ক্রিকেট' WHERE `sports_id` = 7;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'ফুটবল' WHERE `sports_id` = 5;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'ফুটবল' WHERE `sports_id` = 2;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'বাস্কেটবল' WHERE `sports_id` = 4;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'কাবাডি' WHERE `sports_id` = 8;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'গলফ' WHERE `sports_id` = 9;";
	  	$this->db->query($sql);	  

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'ব্যাড্মিন্টন-খেলা' WHERE `sports_id` = 10;";
	  	$this->db->query($sql);

	  	$sql = "UPDATE ".$this->db->dbprefix(MASTER_SPORTS_FORMAT)." SET `ben_display_name` = 'টেনিস' WHERE `sports_id` = 11;";
	  	$this->db->query($sql);

	}

	public function down() {
		$this->dbforge->drop_column(BANNER_MANAGEMENT, 'ben_name');
		$this->dbforge->drop_column(MASTER_SCORING_CATEGORY, 'ben_scoring_category_name');
		$this->dbforge->drop_column(MASTER_SCORING_RULES, 'ben_score_position');
		$this->dbforge->drop_column(MASTER_SPORTS_FORMAT, 'ben_display_name');
	}

}